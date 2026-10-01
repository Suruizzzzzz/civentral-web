<?php
declare(strict_types=1);

use App\Services\DriverInvitationError;
use App\Services\AuditLogger;

function driverInvitationSetting(string $name): string
{
    $value = getenv($name);
    if ($value !== false) return trim($value);
    if (isset($_ENV[$name])) return trim((string)$_ENV[$name]);
    $file = dirname(__DIR__) . '/.env';
    if (is_file($file)) foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        if (trim($key) === $name) return trim($value, " \t\n\r\0\x0B\"'");
    }
    return '';
}

function driverInvitationPayload(array $payload, string $key, string $id): string
{
    $iv = random_bytes(12); $tag = '';
    $encrypted = openssl_encrypt(json_encode($payload, JSON_THROW_ON_ERROR), 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag, $id);
    if ($encrypted === false) throw new RuntimeException('Invitation encryption failed.');
    return 'v1.' . base64_encode($iv . $tag . $encrypted);
}

function driverInvitationEncryptionKey(): string
{
    $key = base64_decode(driverInvitationSetting('CIVENTRAL_DRIVER_INVITATION_ENCRYPTION_KEY'), true);
    if ($key === false || strlen($key) !== 32) {
        throw new DriverInvitationError(503, 'integration_unconfigured', 'Configure invitation encryption.');
    }
    return $key;
}

function driverInvitationConfig(string $method = 'POST'): array
{
    $config = ['service_key' => driverInvitationSetting('CIVENTRAL_TRANSPORT_INVITATION_SERVICE_KEY')];
    if ($method !== 'POST') return $config;
    $ttl = filter_var(driverInvitationSetting('CIVENTRAL_DRIVER_INVITATION_TTL_SECONDS'), FILTER_VALIDATE_INT);
    $hourlyLimit = filter_var(driverInvitationSetting('CIVENTRAL_DRIVER_INVITATION_HOURLY_LIMIT'), FILTER_VALIDATE_INT);
    $retrySeconds = filter_var(driverInvitationSetting('CIVENTRAL_DRIVER_INVITATION_RETRY_SECONDS'), FILTER_VALIDATE_INT);
    if (!$ttl || $ttl < 1 || !$hourlyLimit || $hourlyLimit < 1 || !$retrySeconds || $retrySeconds < 1) {
        throw new DriverInvitationError(503, 'integration_unconfigured', 'Configure invitation expiry and limits.');
    }
    return $config + [
        'encryption_key' => driverInvitationEncryptionKey(),
        'invitation_ttl_seconds' => $ttl,
        'hourly_limit' => $hourlyLimit,
        'retry_cooldown_seconds' => $retrySeconds,
        'claim_url' => driverInvitationSetting('CIVENTRAL_DRIVER_INVITATION_CLAIM_URL'),
    ];
}

function driverInvitationResolveCitizen(PDO $db, array $contact): array
{
    if ($contact['type'] === 'email') {
        $query = $db->prepare('SELECT citizen_user_id,status,deleted_at FROM citizen_users WHERE LOWER(email)=:email LIMIT 2 FOR UPDATE');
        $query->execute(['email' => $contact['value']]);
    } else {
        $query = $db->prepare('SELECT citizen_user_id,status,deleted_at FROM citizen_users WHERE mobile_number IN (:international,:local,:plus) LIMIT 2 FOR UPDATE');
        $query->execute(['international' => $contact['value'], 'local' => '0'.substr($contact['value'],2), 'plus' => '+'.$contact['value']]);
    }
    $rows = $query->fetchAll(PDO::FETCH_ASSOC);
    if (count($rows) > 1 || (isset($rows[0]) && ($rows[0]['deleted_at'] !== null || !in_array($rows[0]['status'], ['Active','Pending'], true)))) {
        throw new DriverInvitationError(409, 'account_resolution_conflict', 'This citizen account cannot receive a driver invitation.');
    }
    if (!$rows) return ['citizen_user_id' => null, 'resolution' => 'new_pending', 'account_status' => 'Pending'];
    return ['citizen_user_id' => (int)$rows[0]['citizen_user_id'],
        'resolution' => $rows[0]['status'] === 'Active' ? 'existing_active' : 'existing_pending', 'account_status' => $rows[0]['status']];
}

function driverInvitationAudit(string $action, array $metadata, string $outcome): void
{
    $result = AuditLogger::log(['action' => $action, 'target_table' => 'citizen_driver_invitations',
        'target_id' => $metadata['invitation_id'] ?? null, 'description' => 'Transport driver invitation request.',
        'status' => $outcome === 'success' ? 'Success' : 'Failed',
        'context_json' => ['source' => 'transport', 'outcome' => $outcome] + $metadata]);
    if ($result === false) throw new RuntimeException('Invitation audit failed.');
}
