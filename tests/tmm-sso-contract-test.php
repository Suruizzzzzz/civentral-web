<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/Services/TmmSsoService.php';
require_once dirname(__DIR__) . '/src/Services/TmmAccountPolicy.php';

use App\Services\TmmAccountPolicy;
use App\Services\TmmSsoService;

function expectSso(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

expectSso(TmmSsoService::normalizeRole('TMMADMIN', false) === 'system_admin', 'Admin role mapping failed.');
expectSso(TmmSsoService::normalizeRole('TA', false) === 'system_admin', 'Transport Administrator mapping failed.');
expectSso(TmmSsoService::normalizeRole('TMMTRAFFIC', false) === 'traffic_officer', 'Traffic role mapping failed.');
expectSso(TmmSsoService::normalizeRole('TMMRECORD', false) === 'records_officer', 'Records role mapping failed.');
expectSso(TmmSsoService::normalizeRole('TMMFRANCH', false) === 'franchise_officer', 'Franchise role mapping failed.');
expectSso(TmmSsoService::normalizeRole('TMMINSPECT', false) === 'vehicle_inspector', 'Inspector role mapping failed.');
expectSso(TmmSsoService::normalizeRole('TMMPARK', false) === 'terminal_parking_officer', 'Parking role mapping failed.');
expectSso(TmmSsoService::normalizeRole('anything', true) === 'system_admin', 'Super Administrator mapping failed.');
expectSso(TmmSsoService::normalizeRole('unknown', false) === null, 'Unknown role was accepted.');

expectSso(
    TmmSsoService::authorizationCodeUsable(['consumed_at' => null, 'is_expired' => 0]),
    'Fresh authorization code was rejected.',
);
expectSso(
    !TmmSsoService::authorizationCodeUsable(['consumed_at' => '2026-07-27 00:00:00', 'is_expired' => 0]),
    'Consumed authorization code was accepted.',
);
expectSso(
    !TmmSsoService::authorizationCodeUsable(['consumed_at' => null, 'is_expired' => 1]),
    'Expired authorization code was accepted.',
);
expectSso(!TmmSsoService::authorizationCodeUsable(null), 'Missing authorization code was accepted.');

expectSso(
    TmmAccountPolicy::visibleRolePrefixes('TA', false) === TmmAccountPolicy::OPERATIONAL_ROLE_PREFIXES,
    'Transport Administrator role list is not restricted.',
);
foreach (TmmAccountPolicy::OPERATIONAL_ROLE_PREFIXES as $rolePrefix) {
    expectSso(
        TmmAccountPolicy::canAssignRole('TA', false, $rolePrefix, false, false),
        "Transport Administrator cannot assign {$rolePrefix}.",
    );
}
expectSso(
    !TmmAccountPolicy::canAssignRole('TA', false, 'TMMADMIN', false, false),
    'Transport Administrator can assign TMM System Admin.',
);
expectSso(
    !TmmAccountPolicy::canAssignRole('TMMADMIN', false, 'TA', false, false),
    'TMM System Admin can assign Transport Administrator.',
);
expectSso(
    TmmAccountPolicy::canAssignRole('SA', true, 'TMMADMIN', false, false),
    'Global Super Administrator cannot assign TMM System Admin.',
);

echo "Civentral TMM SSO contract tests passed.\n";
