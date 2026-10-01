<?php
declare(strict_types=1);
namespace App\Services;
use PDO;
use Throwable;
use RuntimeException;

final class DriverInvitationDelivery
{
    public function __construct(private readonly PDO $pdo, private readonly string $key,
        private readonly mixed $sender, private readonly mixed $audit) {}

    public function run(int $limit = 10): array
    {
        $result = ['accepted' => 0, 'failed' => 0, 'cancelled' => 0];
        $query = $this->pdo->query('SELECT id FROM citizen_driver_invitations WHERE delivery_status="queued" ORDER BY created_at,id LIMIT '.max(1,min(100,$limit)));
        foreach ($query->fetchAll(PDO::FETCH_COLUMN) as $id) {
            try {
                $this->pdo->beginTransaction();
                $row = $this->row($id);
                if ($row['delivery_status'] !== 'queued') { $this->pdo->rollBack(); continue; }
                if ($row['claim_status'] !== 'pending' || strtotime($row['expires_at'].' UTC') <= time()) {
                    $this->finish($id, 'cancelled', true); $this->pdo->commit(); $result['cancelled']++; continue;
                }
                $this->pdo->prepare('UPDATE citizen_driver_invitations SET delivery_status="processing",delivery_started_at=UTC_TIMESTAMP(6) WHERE id=:id')->execute(['id'=>$id]);
                $this->pdo->commit();
                // Persist processing before external I/O: a crash cannot silently
                // requeue an email/SMS that the provider may already have accepted.
                $this->pdo->beginTransaction();
                $row = $this->row($id);
                if ($row['claim_status'] !== 'pending' || strtotime($row['expires_at'].' UTC') <= time()) {
                    $this->finish($id,'cancelled',true); $this->pdo->commit(); $result['cancelled']++; continue;
                }
                if ($row['resolution'] !== 'new_pending') {
                    // Read only: invitation provider calls must not lock existing citizen accounts.
                    $account = $this->pdo->prepare('SELECT status,deleted_at FROM citizen_users WHERE citizen_user_id=:id');
                    $account->execute(['id'=>$row['citizen_user_id']]); $citizen=$account->fetch(PDO::FETCH_ASSOC);
                    if (!$citizen || $citizen['deleted_at'] !== null || !in_array($citizen['status'],['Active','Pending'],true)) {
                        $this->finish($id,'cancelled',true); $this->pdo->commit(); $result['cancelled']++; continue;
                    }
                }
                $payload = $this->decrypt($row['encrypted_payload'], $id);
                $accepted = ($this->sender)($payload) === true;
                $this->finish($id, $accepted ? 'accepted' : 'failed', $accepted);
                $this->pdo->commit(); $result[$accepted ? 'accepted' : 'failed']++;
            } catch (Throwable) {
                if ($this->pdo->inTransaction()) $this->pdo->rollBack();
                // Processing remains durable if external I/O outcome is unknown.
                // An explicit confirmed retry is required; never auto-resend.
                try { ($this->audit)('driver_invitation_delivery', ['invitation_id'=>$id,'reason'=>'delivery_outcome_unknown'], 'failed'); }
                catch (Throwable) { error_log('Driver invitation delivery audit unavailable.'); }
                $result['failed']++;
            }
        }
        return $result;
    }

    private function row(string $id): array
    {
        $query=$this->pdo->prepare('SELECT * FROM citizen_driver_invitations WHERE id=:id FOR UPDATE');
        $query->execute(['id'=>$id]); $row=$query->fetch(PDO::FETCH_ASSOC);
        if (!$row) throw new RuntimeException('Invitation missing.');
        return $row;
    }

    private function finish(string $id, string $status, bool $clear): void
    {
        $statement=$this->pdo->prepare('UPDATE citizen_driver_invitations SET delivery_status=:status,delivery_finished_at=UTC_TIMESTAMP(6),encrypted_payload=CASE WHEN :clear=1 THEN NULL ELSE encrypted_payload END WHERE id=:id');
        $statement->execute(['status'=>$status,'clear'=>$clear?1:0,'id'=>$id]);
        ($this->audit)('driver_invitation_delivery',['invitation_id'=>$id,'delivery_status'=>$status],$status==='failed'?'failed':'success');
    }

    private function decrypt(?string $value, string $id): array
    {
        if (!is_string($value) || !str_starts_with($value,'v1.')) throw new RuntimeException('Invitation payload unavailable.');
        $bytes=base64_decode(substr($value,3),true);
        if ($bytes===false || strlen($bytes)<29) throw new RuntimeException('Invalid invitation payload.');
        $plain=openssl_decrypt(substr($bytes,28),'aes-256-gcm',$this->key,OPENSSL_RAW_DATA,substr($bytes,0,12),substr($bytes,12,16),$id);
        if ($plain===false) throw new RuntimeException('Invitation payload authentication failed.');
        $payload=json_decode($plain,true,32,JSON_THROW_ON_ERROR);
        if (!is_array($payload) || ($payload['invitation_id']??null)!==$id) throw new RuntimeException('Invalid invitation payload.');
        return $payload;
    }
}
