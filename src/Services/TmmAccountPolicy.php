<?php

declare(strict_types=1);

namespace App\Services;

final class TmmAccountPolicy
{
    public const OPERATIONAL_ROLE_PREFIXES = [
        'TMMRECORD',
        'TMMFRANCH',
        'TMMTRAFFIC',
        'TMMINSPECT',
        'TMMPARK',
    ];

    public static function visibleRolePrefixes(string $actorRolePrefix, bool $isGlobalAdministrator): ?array
    {
        if ($isGlobalAdministrator) {
            return null;
        }

        return in_array(strtoupper(trim($actorRolePrefix)), ['TA', 'TMMADMIN'], true)
            ? self::OPERATIONAL_ROLE_PREFIXES
            : null;
    }

    public static function canAssignRole(
        string $actorRolePrefix,
        bool $isGlobalAdministrator,
        string $targetRolePrefix,
        bool $targetIsSuperAdmin,
        bool $targetIsGlobal,
    ): bool {
        $targetRolePrefix = strtoupper(trim($targetRolePrefix));

        if ($isGlobalAdministrator) {
            return !$targetIsSuperAdmin && !in_array($targetRolePrefix, ['SA', 'SADM'], true);
        }

        if ($targetIsSuperAdmin || $targetIsGlobal || in_array($targetRolePrefix, ['SA', 'SADM', 'TA', 'TMMADMIN'], true)) {
            return false;
        }

        if (in_array(strtoupper(trim($actorRolePrefix)), ['TA', 'TMMADMIN'], true)) {
            return in_array($targetRolePrefix, self::OPERATIONAL_ROLE_PREFIXES, true);
        }

        return true;
    }
}
