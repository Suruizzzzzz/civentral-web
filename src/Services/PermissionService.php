<?php

namespace App\Services;

class PermissionService
{
    private $permissionRepository;

    public function __construct($permissionRepository)
    {
        $this->permissionRepository = $permissionRepository;
    }

    public function getRolePermissions($roleId)
    {
        if (!$roleId) return ['actions' => [], 'resources' => []];

        $permissions = $this->permissionRepository->getPermissionsByRoleId($roleId);
        
        $grantedActions = [];
        $grantedResources = [];
        
        foreach ($permissions as $p) {
            if (!empty($p['action_name'])) {
                $grantedActions[] = strtoupper($p['action_name']);
            }
            if (!empty($p['resource_name'])) {
                $grantedResources[] = strtolower(trim($p['resource_name']));
            }
        }

        return [
            'actions' => array_values(array_unique($grantedActions)),
            'resources' => array_values(array_unique($grantedResources))
        ];
    }

    /**
     * Exact permission / resource slug verification without substring keyword matching.
     *
     * @param array $grantedResources Array of granted resource names from DB
     * @param string $requiredPermission Slug or resource name required by the page/action
     * @return bool
     */
    public function hasExactPermission(array $grantedResources, string $requiredPermission): bool
    {
        if (empty($grantedResources) || empty($requiredPermission)) {
            return false;
        }

        $targetSlug = $this->normalizeSlug($requiredPermission);

        // Map developer-friendly slugs to database resource names/slugs
        $slugAliasMap = [
            'permissions.manage' => ['permission.builder', 'permission builder', 'role.permission.matrix', 'role permission matrix'],
            'roles.manage'       => ['roles', 'role management', 'role.management'],
            'modules.manage'     => ['module.management', 'module management', 'modules'],
            'resources.manage'   => ['resource.management', 'resource management', 'resources'],
            'actions.manage'     => ['action.management', 'action management', 'actions'],
            'role.permission.matrix' => ['role.permission.matrix', 'role permission matrix', 'access.control', 'access control'],
            'users.view'         => ['user.directory', 'user directory', 'users.account', 'users account'],
            'users.create'       => ['users.account', 'users account', 'create.account', 'create account'],
            'users.status'       => ['account.status', 'account status', 'status.control', 'status control'],
            'departments.manage' => ['department.management', 'department management', 'departments'],
            'citizens.view'      => ['citizen.directory', 'citizen directory', 'citizens'],
            'citizens.account'   => ['citizen.account', 'citizen account', 'kyc'],
            'scholarships.manage'=> ['scholarship.types', 'scholarship types', 'scholarship.program', 'scholarship program', 'scholarships'],
            'audit.activities'   => ['user.activities', 'user activities', 'audit', 'audit logs system', 'audit logs'],
            'audit.login_history'=> ['login.history', 'login history', 'audit', 'audit logs system', 'audit logs'],
            'audit.data_changes' => ['data.changes', 'data changes', 'audit', 'audit logs system', 'audit logs'],
        ];

        // Normalize map keys to ensure matching regardless of dots/underscores
        $normalizedMap = [];
        foreach ($slugAliasMap as $k => $v) {
            $normalizedMap[$this->normalizeSlug($k)] = $v;
        }

        $acceptableSlugs = [$targetSlug];
        if (isset($normalizedMap[$targetSlug])) {
            foreach ($normalizedMap[$targetSlug] as $alias) {
                $acceptableSlugs[] = $this->normalizeSlug($alias);
            }
        }

        // Normalize granted resources
        $normalizedGranted = array_map([$this, 'normalizeSlug'], $grantedResources);

        // Exact comparison
        foreach ($acceptableSlugs as $slug) {
            if (in_array($slug, $normalizedGranted, true)) {
                return true;
            }
        }

        return false;
    }

    public function normalizeSlug(string $input): string
    {
        $lowered = strtolower(trim($input));
        $slug = preg_replace('/[\s\-_]+/', '.', $lowered);
        return trim($slug, '.');
    }
}

