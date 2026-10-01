<?php
declare(strict_types=1);
namespace App\Services;
use PDO;
use RuntimeException;
use Throwable;
use DateTimeImmutable;
use DateTimeZone;

final class DriverInvitationError extends RuntimeException
{
    public function __construct(public readonly int $httpStatus, public readonly string $errorCode, string $message)
    { parent::__construct($message); }
}

final class DriverInvitations
{
    public function __construct(private readonly PDO $db, private readonly array $config) {}

    /** Returns [HTTP status, Civentral-style response envelope]. */
    public function handle(string $method, string $serviceKey, array $input): array
    {
        try {
            $configuredKey = (string) ($this->config['service_key'] ?? '');
            if ($configuredKey === '') throw new DriverInvitationError(503, 'integration_unconfigured', 'Invitation integration is not configured.');
            if ($serviceKey === '' || !hash_equals($configuredKey, $serviceKey)) {
                throw new DriverInvitationError(401, 'service_authentication_required', 'Service authentication required.');
            }
            if (!in_array($method, ['GET', 'POST', 'DELETE'], true)) {
                throw new DriverInvitationError(405, 'method_not_allowed', 'Use GET, POST, or DELETE.');
            }
            if ($method === 'POST') $this->rateLimit($input);
            if ($method === 'POST') return $this->create($input);
            if ($method === 'GET' && isset($input['source_request_id'])) {
                $request = self::uuid($input['source_request_id']);
                $query = $this->db->prepare('SELECT id FROM citizen_driver_invitations WHERE source_request_id=:request LIMIT 1');
                $query->execute(['request' => $request]);
                $found = $query->fetchColumn();
                if ($found === false) throw new DriverInvitationError(404, 'invitation_not_found', 'Invitation not found.');
                return [200, ['status' => 'success', 'data' => $this->project($this->find((string)$found))]];
            }
            $id = self::uuid($input['invitation_id'] ?? null);
            if ($method === 'DELETE') {
                $this->db->beginTransaction();
                $row = $this->find($id, true);
                if ($row['claim_status'] !== 'revoked') {
                    $statement = $this->db->prepare('UPDATE citizen_driver_invitations SET claim_status="revoked",revoked_at=UTC_TIMESTAMP(6) WHERE id=:id');
                    $statement->execute(['id' => $id]);
                    $this->db->prepare('UPDATE citizen_driver_invitations SET encrypted_payload=NULL,delivery_status=CASE WHEN delivery_status="accepted" THEN delivery_status ELSE "cancelled" END WHERE id=:id')->execute(['id' => $id]);
                    \driverInvitationAudit('driver_invitation_revoked', ['invitation_id' => $id], 'success');
                }
                $this->db->commit();
            }
            return [200, ['status' => 'success', 'data' => $this->project($this->find($id))]];
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $known = $exception instanceof DriverInvitationError;
            $code = $known ? $exception->errorCode : 'invitation_request_failed';
            // The audit adapter must also log unauthenticated requests safely.
            if (function_exists('driverInvitationAudit')) {
                try { \driverInvitationAudit('driver_invitation_request', ['reason' => $code], $known ? 'rejected' : 'failed'); }
                catch (Throwable) { error_log('Civentral invitation audit failed.'); }
            }
            if (!$known) error_log('Civentral invitation request failed.');
            return [$known ? $exception->httpStatus : 500, ['status' => 'error', 'code' => $code,
                'message' => $known ? $exception->getMessage() : 'Unable to process the invitation request.']];
        }
    }

    private function rateLimit(array $input): void
    {
        if (is_string($input['source_request_id']??null)) {
            $existing=$this->db->prepare('SELECT id FROM citizen_driver_invitations WHERE source_request_id=:request LIMIT 1');
            $existing->execute(['request'=>$input['source_request_id']]);
            if ($existing->fetchColumn()!==false) return;
        }
        $count = (int)$this->db->query('SELECT COUNT(*) FROM citizen_driver_invitations WHERE created_at>=NOW()-INTERVAL 1 HOUR')->fetchColumn();
        if ($count >= $this->config['hourly_limit']) throw new DriverInvitationError(429, 'rate_limited', 'Invitation request limit reached. Try again later.');
    }

    private function create(array $input): array
    {
        if (($input['source'] ?? null) !== 'transport' || ($input['approval_status'] ?? null) !== 'approved') {
            throw new DriverInvitationError(422, 'approved_registration_required', 'An approved Transport driver registration is required.');
        }
        $request = self::uuid($input['source_request_id'] ?? null);
        $driver = self::uuid($input['driver_reference'] ?? null);
        $approved = $input['approved_at'] ?? null;
        $date = is_string($approved) ? DateTimeImmutable::createFromFormat('!Y-m-d H:i:s.u', $approved, new DateTimeZone('UTC')) : false;
        if ($date === false || $date->format('Y-m-d H:i:s.u') !== $approved || $date > new DateTimeImmutable('now', new DateTimeZone('UTC'))) {
            throw new DriverInvitationError(422, 'invalid_approval_time', 'Supply the actual approval time in UTC with microseconds.');
        }
        $contact = self::contact($input['contact'] ?? null);
        $name = [];
        foreach (['first_name', 'middle_name', 'last_name'] as $field) {
            $value = $input[$field] ?? '';
            if (!is_string($value) || strlen(trim($value)) > 320 || ($field !== 'middle_name' && trim($value) === '')) {
                throw new DriverInvitationError(422, 'invalid_name', 'Supply valid driver names.');
            }
            $name[$field] = trim($value);
        }
        $canonical = ['source_request_id' => $request, 'driver_reference' => $driver, 'approved_at' => $approved,
            'contact' => $contact, 'name' => $name];
        $hash = hash('sha256', json_encode($canonical, JSON_THROW_ON_ERROR));
        $ttl = $this->config['invitation_ttl_seconds'] ?? null;
        $claimUrl = (string) ($this->config['claim_url'] ?? '');
        $url = parse_url($claimUrl);
        if (!is_int($ttl) || $ttl < 1 || !is_array($url) || ($url['scheme'] ?? '') !== 'https'
            || empty($url['host']) || isset($url['user']) || isset($url['pass']) || isset($url['query']) || isset($url['fragment'])) {
            throw new DriverInvitationError(503, 'integration_unconfigured', 'Invitation expiry and HTTPS claim URL must be configured.');
        }
        $this->db->beginTransaction();
        $id = self::newUuid();
        $token = bin2hex(random_bytes(32));
        $expiry = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->modify('+' . $ttl . ' seconds')->format('Y-m-d H:i:s.u');
        $existing = $this->db->prepare('SELECT * FROM citizen_driver_invitations WHERE source_request_id=:request OR (driver_reference=:driver AND approved_at=:approved) LIMIT 1 FOR UPDATE');
        $existing->execute(['request' => $request, 'driver' => $driver, 'approved' => $approved]);
        $row = $existing->fetch(PDO::FETCH_ASSOC);
        if (is_array($row)) {
            if (!hash_equals($row['payload_hash'], $hash)) {
                throw new DriverInvitationError(409, 'idempotency_conflict', 'This request reference already has different driver details.');
            }
            if (($input['retry_delivery'] ?? false) === true) {
                if (($input['confirm_retry'] ?? false) !== true) throw new DriverInvitationError(422, 'retry_confirmation_required', 'Confirm the delivery retry; an earlier message may already have been accepted.');
                if (in_array($row['delivery_status'], ['failed','processing'], true)) {
                    if ($row['claim_status'] !== 'pending' || strtotime($row['expires_at'].' UTC') <= time() || $row['encrypted_payload'] === null) {
                        throw new DriverInvitationError(409, 'invitation_not_retryable', 'This invitation cannot be resent.');
                    }
                    $cooldown=(int)($this->config['retry_cooldown_seconds']??60);
                    $last=$row['delivery_finished_at']??$row['delivery_started_at'];
                    if ($last !== null && time()-strtotime($last.' UTC') < $cooldown) throw new DriverInvitationError(429,'retry_cooldown','Wait before retrying this invitation.');
                    $this->db->prepare('UPDATE citizen_driver_invitations SET delivery_status="queued" WHERE id=:id')->execute(['id'=>$row['id']]);
                    \driverInvitationAudit('driver_invitation_retry',['invitation_id'=>$row['id']],'success');
                    $row['delivery_status']='queued';
                }
            }
            $this->db->commit();
            return [200, ['status' => 'success', 'data' => $this->project($row)]];
        }
        $account = \driverInvitationResolveCitizen($this->db, $contact);
        if (!is_array($account) || (($account['resolution'] ?? null) === 'new_pending'
                ? ($account['citizen_user_id'] ?? null) !== null
                : !is_int($account['citizen_user_id'] ?? null) || $account['citizen_user_id'] < 1)
            || !in_array($account['resolution'] ?? null, ['existing_active', 'existing_pending', 'new_pending'], true)
            || !in_array($account['account_status'] ?? null, ['Active', 'Pending'], true)
            || ($account['resolution'] === 'existing_active' && $account['account_status'] !== 'Active')
            || ($account['resolution'] !== 'existing_active' && $account['account_status'] !== 'Pending')) {
            throw new DriverInvitationError(409, 'account_resolution_conflict', 'The citizen account cannot be invited through this request.');
        }
        $statement = $this->db->prepare('INSERT INTO citizen_driver_invitations
            (id,source_request_id,driver_reference,approved_at,payload_hash,citizen_user_id,resolution,contact_type,contact_value,token_hash,expires_at)
            VALUES (:id,:request,:driver,:approved,:hash,:citizen,:resolution,:type,:contact,:token,:expiry)');
        $statement->execute(['id' => $id, 'request' => $request, 'driver' => $driver, 'approved' => $approved,
            'hash' => $hash, 'citizen' => $account['citizen_user_id'], 'resolution' => $account['resolution'],
            'type' => $contact['type'], 'contact' => $contact['value'], 'token' => hash('sha256', $token), 'expiry' => $expiry]);
        $payload = [
            'invitation_id' => $id, 'contact' => $contact, 'expires_at' => $expiry,
            'invitation_url' => $claimUrl . '#' . http_build_query(['invitation_id' => $id, 'token' => $token]),
            'message' => 'You have been registered as a PUV driver. Verify your account to access Civentral.',
        ];
        $this->db->prepare('UPDATE citizen_driver_invitations SET encrypted_payload=:payload WHERE id=:id')
            ->execute(['payload' => \driverInvitationPayload($payload, $this->config['encryption_key'], $id), 'id' => $id]);
        \driverInvitationAudit('driver_invitation_created', ['invitation_id' => $id, 'driver_reference' => $driver], 'success');
        $this->db->commit();
        return [201, ['status' => 'success', 'data' => $this->project($this->find($id))]];
    }

    private function find(string $id, bool $lock = false): array
    {
        $statement = $this->db->prepare('SELECT * FROM citizen_driver_invitations WHERE id=:id LIMIT 1' . ($lock ? ' FOR UPDATE' : ''));
        $statement->execute(['id' => $id]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if (!is_array($row)) throw new DriverInvitationError(404, 'invitation_not_found', 'Invitation not found.');
        return $row;
    }

    private function project(array $row): array
    {
        $expiry = new DateTimeImmutable($row['expires_at'], new DateTimeZone('UTC'));
        $expired = $expiry <= new DateTimeImmutable('now', new DateTimeZone('UTC')) && $row['claim_status'] === 'pending';
        return ['invitation_id' => $row['id'], 'source_request_id' => $row['source_request_id'],
            'driver_reference' => $row['driver_reference'], 'approved_at' => $row['approved_at'],
            'citizen_user_id' => $row['citizen_user_id'] === null ? null : (int)$row['citizen_user_id'], 'resolution' => $row['resolution'],
            'claim_status' => $expired ? 'expired' : $row['claim_status'], 'delivery_status' => $row['delivery_status'],
            'expires_at' => $expiry->format('Y-m-d\TH:i:s.u\Z'), 'verified_at' => $row['verified_at']];
    }

    private static function uuid(mixed $value): string
    {
        if (!is_string($value) || preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[1-5][a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/i', $value) !== 1) {
            throw new DriverInvitationError(422, 'invalid_reference', 'Supply a valid request or driver reference.');
        }
        return strtolower($value);
    }

    private static function newUuid(): string
    {
        $bytes = random_bytes(16); $bytes[6] = chr((ord($bytes[6]) & 15) | 64); $bytes[8] = chr((ord($bytes[8]) & 63) | 128);
        $hex = bin2hex($bytes);
        return substr($hex,0,8).'-'.substr($hex,8,4).'-'.substr($hex,12,4).'-'.substr($hex,16,4).'-'.substr($hex,20);
    }

    private static function contact(mixed $value): array
    {
        if (!is_string($value) || strlen($value) > 254) throw new DriverInvitationError(422, 'invalid_contact', 'Supply an email or Philippine mobile number.');
        $value = trim($value);
        if (filter_var($value, FILTER_VALIDATE_EMAIL) !== false) return ['type' => 'email', 'value' => strtolower($value)];
        $phone = preg_replace('/[\s()-]/', '', $value);
        if (preg_match('/^09\d{9}$/', $phone) === 1) $phone = '63' . substr($phone, 1);
        elseif (preg_match('/^\+639\d{9}$/', $phone) === 1) $phone = substr($phone, 1);
        if (preg_match('/^639\d{9}$/', $phone) !== 1) throw new DriverInvitationError(422, 'invalid_contact', 'Supply an email or Philippine mobile number.');
        return ['type' => 'phone', 'value' => $phone];
    }
}
