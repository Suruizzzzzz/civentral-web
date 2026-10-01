<?php
declare(strict_types=1);
namespace App\Services;
use PDO;
use Throwable;
use RuntimeException;

final class DriverInvitationClaims
{
    public function __construct(private readonly PDO $db, private readonly array $config,
        private readonly mixed $emailSender) {}

    public function handle(array $input, string $sessionToken = ''): array
    {
        try {
            $id=$input['invitation_id']??''; $token=$input['token']??'';
            if (!is_string($id) || !preg_match('/^[a-f0-9-]{36}$/i',$id)
                || !is_string($token) || !preg_match('/^[a-f0-9]{64}$/',$token)) $this->invalid();
            $this->db->beginTransaction();
            $query=$this->db->prepare('SELECT * FROM citizen_driver_invitations WHERE id=:id FOR UPDATE');
            $query->execute(['id'=>$id]); $row=$query->fetch(PDO::FETCH_ASSOC);
            if (!$row || !hash_equals($row['token_hash'],hash('sha256',$token))) $this->invalid();
            if ($row['claim_status']!=='pending' || strtotime($row['expires_at'].' UTC')<=time()) {
                throw new DriverInvitationError(410,'invitation_unavailable','This invitation is expired, revoked or already used.');
            }
            $legal=$this->legal();
            $account=\driverInvitationResolveCitizen($this->db,
                ['type'=>$row['contact_type'],'value'=>$row['contact_value']]);
            // Re-resolve at claim time: never overwrite an account created after invitation dispatch.
            if ($row['citizen_user_id']!==null && (int)$row['citizen_user_id']!==($account['citizen_user_id']??null)) {
                throw new DriverInvitationError(409,'account_resolution_conflict','The invited account has changed. Ask for a new invitation.');
            }
            $sessionCitizen=null;
            if ($sessionToken!=='') {
                $session=$this->db->prepare('SELECT citizen_user_id FROM citizen_sessions WHERE refresh_token_hash=:hash AND is_revoked=0 AND expires_at>NOW() LIMIT 1');
                $session->execute(['hash'=>hash('sha256',$sessionToken)]); $signed=$session->fetchColumn();
                if ($signed===false || (int)$signed!==($account['citizen_user_id']??null)) {
                    throw new DriverInvitationError(409,'wrong_signed_in_account','Sign out of the other account before verifying this invitation.');
                }
                $sessionCitizen=(int)$signed;
            }
            $action=$input['action']??'inspect';
            if ($action==='inspect') {
                $this->db->commit();
                return [200,['status'=>'success','data'=>['account_mode'=>$account['citizen_user_id']===null?'new':'existing',
                    'contact_type'=>$row['contact_type'],'contact'=>$row['contact_value'],'legal'=>$legal,
                    'email_code_required'=>$account['citizen_user_id']===null && $row['contact_type']==='phone']]];
            }
            if (!in_array($action,['send_email_code','complete'],true)) throw new DriverInvitationError(422,'invalid_action','Choose a supported verification action.');
            // Limit guesses without changing any existing citizen login counters/status.
            $recent=$row['claim_window_at']!==null && strtotime($row['claim_window_at'].' UTC')>time()-900;
            $attempts=$recent?(int)$row['claim_attempts']:0;
            if ($attempts>=10) throw new DriverInvitationError(429,'claim_rate_limited','Too many verification attempts. Try again in 15 minutes.');
            $this->db->prepare('UPDATE citizen_driver_invitations SET claim_attempts=:attempts,claim_window_at=CASE WHEN :reset=1 THEN UTC_TIMESTAMP(6) ELSE claim_window_at END WHERE id=:id')
                ->execute(['attempts'=>$attempts+1,'reset'=>$recent?0:1,'id'=>$id]);
            // Persist failed-attempt accounting even when later validation rejects the action.
            $this->db->commit(); $this->db->beginTransaction();
            $query->execute(['id'=>$id]); $row=$query->fetch(PDO::FETCH_ASSOC);
            if ($row['claim_status']!=='pending' || strtotime($row['expires_at'].' UTC')<=time()) throw new DriverInvitationError(410,'invitation_unavailable','This invitation is no longer available.');
            $account=\driverInvitationResolveCitizen($this->db,['type'=>$row['contact_type'],'value'=>$row['contact_value']]);
            if ($sessionCitizen!==null && $sessionCitizen!==($account['citizen_user_id']??null)) throw new DriverInvitationError(409,'wrong_signed_in_account','Sign out before verifying this invitation.');
            if ($row['citizen_user_id']!==null && (int)$row['citizen_user_id']!==($account['citizen_user_id']??null)) throw new DriverInvitationError(409,'account_resolution_conflict','Ask for a new invitation.');
            if ($action==='send_email_code') {
                if ($account['citizen_user_id']!==null || $row['contact_type']!=='phone') throw new DriverInvitationError(422,'email_code_not_required','This invitation does not require an additional email code.');
                $email=$this->email($input['email']??null);
                $this->availableEmail($email);
                if ($row['email_code_sent_at']!==null && strtotime($row['email_code_sent_at'].' UTC')>time()-60) throw new DriverInvitationError(429,'email_code_cooldown','Wait 60 seconds before sending another code.');
                $code=(string)random_int(100000,999999);
                $hash=hash_hmac('sha256',$code,$token);
                $this->db->prepare('UPDATE citizen_driver_invitations SET setup_email=:email,email_code_hash=:hash,email_code_expires_at=UTC_TIMESTAMP(6)+INTERVAL 10 MINUTE,email_code_sent_at=UTC_TIMESTAMP(6) WHERE id=:id')
                    ->execute(['email'=>$email,'hash'=>$hash,'id'=>$id]);
                \driverInvitationAudit('driver_invitation_email_code',['invitation_id'=>$id],'success');
                $this->db->commit();
                // Keep the durable cooldown even if delivery has an unknown outcome.
                if (($this->emailSender)($email,$code)!==true) throw new DriverInvitationError(502,'email_delivery_failed','The email code could not be sent. Wait 60 seconds and try again.');
                return [200,['status'=>'success','message'=>'Verification code sent.']];
            }
            if (($input['accept_terms']??false)!==true || ($input['accept_privacy']??false)!==true
                || ($input['terms_version']??null)!==$legal['terms_version'] || ($input['privacy_version']??null)!==$legal['privacy_version']) {
                throw new DriverInvitationError(422,'consent_required','Read and accept the current Terms of Use and Privacy Notice.');
            }
            $password=$input['password']??null;
            if (!is_string($password) || strlen($password)>4096 || $password==='') {
                throw new DriverInvitationError(422,'invalid_password','Enter your password.');
            }
            if ($account['citizen_user_id']===null && (strlen($password)<8 || strlen($password)>72 || trim($password)!==$password)) {
                throw new DriverInvitationError(422,'invalid_password','Use a password with 8 to 72 bytes and no spaces at the beginning or end.');
            }
            $citizen=$account['citizen_user_id'];
            if ($citizen!==null) {
                $userQuery=$this->db->prepare('SELECT password,status,deleted_at FROM citizen_users WHERE citizen_user_id=:id FOR UPDATE');
                $userQuery->execute(['id'=>$citizen]); $user=$userQuery->fetch(PDO::FETCH_ASSOC);
                if (!$user || $user['deleted_at']!==null || !in_array($user['status'],['Active','Pending'],true) || !password_verify($password,$user['password'])) {
                    throw new DriverInvitationError(401,'account_confirmation_failed','Confirm with the invited account’s current password.');
                }
                // Link contact proof plus existing password finishes a pending account without resetting credentials.
                if ($user['status']==='Pending') $this->db->prepare("UPDATE citizen_users SET status='Active' WHERE citizen_user_id=:id")->execute(['id'=>$citizen]);
            } else {
                $email=$row['contact_type']==='email'?$this->email($row['contact_value']):$this->email($input['email']??null);
                if ($row['contact_type']==='phone') {
                    $code=$input['email_code']??'';
                    if (!is_string($code) || !preg_match('/^\d{6}$/',$code) || $row['setup_email']!==$email
                        || $row['email_code_hash']===null || !hash_equals($row['email_code_hash'],hash_hmac('sha256',$code,$token))
                        || strtotime($row['email_code_expires_at'].' UTC')<=time()) {
                        throw new DriverInvitationError(422,'email_verification_required','Enter the current verification code sent to your email.');
                    }
                }
                $this->availableEmail($email);
                $first=$this->name($input['first_name']??null); $last=$this->name($input['last_name']??null);
                $insert=$this->db->prepare("INSERT INTO citizen_users (first_name,last_name,email,mobile_number,password,status) VALUES (:first,:last,:email,:phone,:password,'Active')");
                // Match the mobile app's canonical 639 format for the existing login API.
                $insert->execute(['first'=>$first,'last'=>$last,'email'=>$email,
                    'phone'=>$row['contact_type']==='phone'?$row['contact_value']:null,
                    'password'=>password_hash($password,PASSWORD_BCRYPT)]);
                $citizen=(int)$this->db->lastInsertId();
            }
            $this->db->prepare("UPDATE citizen_driver_invitations SET citizen_user_id=:citizen,claim_status='verified',verified_at=UTC_TIMESTAMP(6),terms_version=:terms,privacy_version=:privacy,consent_at=UTC_TIMESTAMP(6),email_code_hash=NULL,encrypted_payload=NULL,delivery_status=CASE WHEN delivery_status='accepted' THEN delivery_status ELSE 'cancelled' END WHERE id=:id")
                ->execute(['citizen'=>$citizen,'terms'=>$legal['terms_version'],'privacy'=>$legal['privacy_version'],'id'=>$id]);
            \driverInvitationAudit('driver_invitation_verified',['invitation_id'=>$id,'citizen_user_id'=>$citizen,
                'terms_version'=>$legal['terms_version'],'privacy_version'=>$legal['privacy_version']],'success');
            $this->db->commit();
            return [200,['status'=>'success','message'=>'Driver invitation verified. Sign in to Civentral to continue.',
                'data'=>['invitation_id'=>$id,'citizen_user_id'=>$citizen,'claim_status'=>'verified']]];
        } catch (Throwable $error) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $known=$error instanceof DriverInvitationError;
            try { \driverInvitationAudit('driver_invitation_claim',['reason'=>$known?$error->errorCode:'claim_failed'],'failed'); }
            catch (Throwable) { error_log('Driver invitation claim audit unavailable.'); }
            return [$known?$error->httpStatus:500,['status'=>'error','code'=>$known?$error->errorCode:'claim_failed',
                'message'=>$known?$error->getMessage():'Unable to verify the invitation. Try again.']];
        }
    }
    private function invalid(): never { throw new DriverInvitationError(404,'invalid_invitation','This invitation link is invalid.'); }
    private function email(mixed $value): string {
        if (!is_string($value) || strlen($value)>150 || !filter_var(trim($value),FILTER_VALIDATE_EMAIL)) throw new DriverInvitationError(422,'invalid_email','Enter a valid email address.');
        return strtolower(trim($value));
    }
    private function name(mixed $value): string {
        if (!is_string($value) || trim($value)==='' || preg_match('//u',$value)!==1 || preg_match('/^.{1,100}$/us',trim($value))!==1) throw new DriverInvitationError(422,'invalid_name','Enter your first and last names (up to 100 characters each).');
        return trim($value);
    }
    private function availableEmail(string $email): void {
        $query=$this->db->prepare('SELECT citizen_user_id FROM citizen_users WHERE LOWER(email)=:email LIMIT 1 FOR UPDATE');
        $query->execute(['email'=>$email]);
        if ($query->fetchColumn()!==false) throw new DriverInvitationError(409,'email_account_exists','This email already has an account. Use that account’s invitation or ask the officer to correct your contact.');
    }
    private function legal(): array {
        $legal=$this->config['legal']??[];
        foreach (['terms_version','privacy_version'] as $field) {
            if (!is_string($legal[$field]??null) || $legal[$field]==='' || strlen($legal[$field])>100) {
                throw new DriverInvitationError(503,'legal_unconfigured','Configure legal version identifiers of 1 to 100 characters.');
            }
        }
        return $legal;
    }
}
