<?php

declare(strict_types=1);

namespace App\Services;

use Database;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

final class TmmSsoService
{
    private const ROLE_MAP = [
        'TA' => 'system_admin',
        'TMMADMIN' => 'system_admin',
        'TMMRECORD' => 'records_officer',
        'TMMFRANCH' => 'franchise_officer',
        'TMMTRAFFIC' => 'traffic_officer',
        'TMMINSPECT' => 'vehicle_inspector',
        'TMMPARK' => 'terminal_parking_officer',
    ];

    private const SUPER_ADMIN_PERMISSIONS = [
        'tmm.dashboard:view',
        'tmm.puv:view', 'tmm.puv:create', 'tmm.puv:edit', 'tmm.puv:delete', 'tmm.puv:export', 'tmm.puv:approve',
        'tmm.franchise:view', 'tmm.franchise:create', 'tmm.franchise:edit', 'tmm.franchise:delete', 'tmm.franchise:export', 'tmm.franchise:approve',
        'tmm.traffic:view', 'tmm.traffic:create', 'tmm.traffic:edit', 'tmm.traffic:delete', 'tmm.traffic:export', 'tmm.traffic:approve',
        'tmm.inspection:view', 'tmm.inspection:create', 'tmm.inspection:edit', 'tmm.inspection:delete', 'tmm.inspection:export', 'tmm.inspection:approve',
        'tmm.parking:view', 'tmm.parking:create', 'tmm.parking:edit', 'tmm.parking:delete', 'tmm.parking:export', 'tmm.parking:approve',
        'tmm.reports:view', 'tmm.reports:export',
        'civentral.users:view', 'civentral.users:create',
    ];

    public function __construct(private readonly Database $database)
    {
    }

    public static function normalizeRole(string $rolePrefix, bool $isSuperAdmin): ?string
    {
        if ($isSuperAdmin) {
            return 'system_admin';
        }

        return self::ROLE_MAP[strtoupper(trim($rolePrefix))] ?? null;
    }

    public static function configuredClient(): array
    {
        $client = [
            'client_id' => trim((string) getenv('TMM_SSO_CLIENT_ID')),
            'client_secret' => (string) getenv('TMM_SSO_CLIENT_SECRET'),
            'redirect_uri' => trim((string) getenv('TMM_SSO_REDIRECT_URI')),
        ];

        foreach ($client as $value) {
            if ($value === '') {
                throw new RuntimeException('TMM SSO is not configured.');
            }
        }

        if (
            !str_starts_with($client['redirect_uri'], 'https://')
            && strtolower((string) getenv('APP_ENV')) === 'production'
        ) {
            throw new RuntimeException('TMM SSO redirect URI must use HTTPS.');
        }

        return $client;
    }

    public static function validateClient(array $input, bool $requireSecret): array
    {
        $configured = self::configuredClient();
        $clientId = trim((string) ($input['client_id'] ?? ''));
        $redirectUri = trim((string) ($input['redirect_uri'] ?? ''));

        if (!hash_equals($configured['client_id'], $clientId) || !hash_equals($configured['redirect_uri'], $redirectUri)) {
            throw new InvalidArgumentException('Unknown SSO client or redirect URI.');
        }

        if ($requireSecret && !hash_equals($configured['client_secret'], (string) ($input['client_secret'] ?? ''))) {
            throw new InvalidArgumentException('Invalid SSO client credentials.');
        }

        return $configured;
    }

    public static function authorizationCodeUsable(?array $record): bool
    {
        return $record !== null
            && $record['consumed_at'] === null
            && (int) ($record['is_expired'] ?? 1) === 0;
    }

    public function issueCode(int $userId, string $clientId, string $redirectUri): string
    {
        $this->claimsForUser($userId);
        $code = bin2hex(random_bytes(32));
        $this->database->insert('sso_authorization_codes', [
            'client_id' => $clientId,
            'user_id' => $userId,
            'code_hash' => hash('sha256', $code),
            'redirect_uri' => $redirectUri,
            'expires_at' => gmdate('Y-m-d H:i:s', time() + 60),
        ]);

        try {
            $this->database->exec(
                'DELETE FROM sso_authorization_codes
                 WHERE expires_at < UTC_TIMESTAMP() - INTERVAL 1 DAY'
            );
        } catch (Throwable) {
        }

        return $code;
    }

    public function exchangeCode(string $code, string $clientId, string $redirectUri): array
    {
        if (preg_match('/^[a-f0-9]{64}$/', $code) !== 1) {
            throw new InvalidArgumentException('Authorization code is invalid.');
        }

        $pdo = $this->database->getPdo();
        $pdo->beginTransaction();

        try {
            $rows = $this->database->query(
                'SELECT authorization_code_id, user_id, expires_at, consumed_at,
                        expires_at < UTC_TIMESTAMP(6) is_expired
                 FROM sso_authorization_codes
                 WHERE code_hash = :code_hash
                   AND client_id = :client_id
                   AND redirect_uri = :redirect_uri
                 LIMIT 1
                 FOR UPDATE',
                [
                    'code_hash' => hash('sha256', $code),
                    'client_id' => $clientId,
                    'redirect_uri' => $redirectUri,
                ],
            );

            $record = $rows[0] ?? null;
            if (!self::authorizationCodeUsable($record)) {
                throw new InvalidArgumentException('Authorization code is expired, used, or invalid.');
            }

            $updated = $this->database->exec(
                'UPDATE sso_authorization_codes
                 SET consumed_at = UTC_TIMESTAMP(6)
                 WHERE authorization_code_id = :id AND consumed_at IS NULL',
                ['id' => $record['authorization_code_id']],
            );
            if ($updated !== 1) {
                throw new InvalidArgumentException('Authorization code has already been used.');
            }

            $pdo->commit();
            return $this->claimsForUser((int) $record['user_id']);
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function claimsForUser(int $userId): array
    {
        $rows = $this->database->query(
            'SELECT
                user.user_id, user.employee_id, user.first_name, user.middle_name,
                user.last_name, user.mobile_number, user.status account_status,
                user.updated_at source_updated_at,
                role.role_id, role.role_name, role.role_prefix,
                role.is_superadmin, role.is_global_access, role.department_id role_department_id,
                position.position_id, position.position_name,
                department.department_id user_department_id,
                department.department_code, department.department_name
             FROM users user
             JOIN roles role ON role.role_id = user.role_id
             JOIN positions position ON position.position_id = user.position_id
             JOIN departments department ON department.department_id = position.department_id
             WHERE user.user_id = :user_id
             LIMIT 1',
            ['user_id' => $userId],
        );

        $user = $rows[0] ?? null;
        if ($user === null || strcasecmp((string) $user['account_status'], 'Active') !== 0) {
            throw new InvalidArgumentException('Civentral account is not active.');
        }

        $isSuperAdmin = (int) $user['is_superadmin'] === 1
            || in_array(strtoupper((string) $user['role_prefix']), ['SA', 'SADM'], true);
        if (!$isSuperAdmin && strtoupper((string) $user['department_code']) !== 'TMM') {
            throw new InvalidArgumentException('User does not belong to the TMM department.');
        }
        if (!$isSuperAdmin && (int) $user['role_department_id'] !== (int) $user['user_department_id']) {
            throw new InvalidArgumentException('User role and department are inconsistent.');
        }

        $roleCode = self::normalizeRole((string) $user['role_prefix'], $isSuperAdmin);
        if ($roleCode === null) {
            throw new InvalidArgumentException('User does not have a supported TMM role.');
        }

        $permissions = $isSuperAdmin
            ? self::SUPER_ADMIN_PERMISSIONS
            : $this->permissionsForRole((int) $user['role_id']);
        if (!in_array('tmm.dashboard:view', $permissions, true)) {
            throw new InvalidArgumentException('User does not have access to TMM.');
        }

        $now = time();
        return [
            'user_id' => (int) $user['user_id'],
            'employee_id' => (string) $user['employee_id'],
            'first_name' => (string) $user['first_name'],
            'middle_name' => $user['middle_name'] !== null ? (string) $user['middle_name'] : null,
            'last_name' => (string) $user['last_name'],
            'mobile_number' => $user['mobile_number'] !== null ? (string) $user['mobile_number'] : null,
            'position_id' => (int) $user['position_id'],
            'position_title' => (string) $user['position_name'],
            'department_code' => 'TMM',
            'department_name' => (string) $user['department_name'],
            'account_status' => 'active',
            'role_code' => $roleCode,
            'role_name' => $isSuperAdmin ? 'Civentral Super Administrator' : (string) $user['role_name'],
            'permissions' => array_values(array_unique($permissions)),
            'source_updated_at' => (string) $user['source_updated_at'],
            'issued_at' => gmdate(DATE_ATOM, $now),
            'expires_at' => gmdate(DATE_ATOM, $now + 1800),
        ];
    }

    private function permissionsForRole(int $roleId): array
    {
        $rows = $this->database->query(
            'SELECT DISTINCT permission.permission_key, resource.resource_name, action.action_name
             FROM role_permissions assignment
             JOIN permissions permission ON permission.permission_id = assignment.permission_id
             JOIN resources resource ON resource.resource_id = permission.resource_id
             JOIN actions action ON action.action_id = permission.action_id
             WHERE assignment.role_id = :role_id
               AND permission.status = "Active"
             ORDER BY permission.permission_key',
            ['role_id' => $roleId],
        );

        $permissions = [];
        foreach ($rows as $row) {
            $permissions[] = strtolower((string) $row['permission_key']);
            $resourceName = strtolower(trim((string) ($row['resource_name'] ?? '')));
            $actionName = strtolower(trim((string) ($row['action_name'] ?? '')));
            if (in_array($resourceName, ['users account', 'user account'], true)) {
                if ($actionName === 'view') {
                    $permissions[] = 'civentral.users:view';
                }
                if ($actionName === 'create') {
                    $permissions[] = 'civentral.users:create';
                }
            }
        }

        return array_values(array_unique($permissions));
    }
}
