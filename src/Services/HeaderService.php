<?php

namespace App\Services;

class HeaderService
{
    private $userService;
    private $permissionService;
    private $authService;

    public function __construct($userService, $permissionService, $authService)
    {
        $this->userService = $userService;
        $this->permissionService = $permissionService;
        $this->authService = $authService;
    }

    public function buildHeaderUser()
    {
        $headerUser = [
            'full_name' => 'System User',
            'initials' => 'SU',
            'role' => 'Staff',
            'role_prefix' => 'STF',
            'profile_picture' => 'default-avatar.png',
            'is_superadmin' => false,
            'is_global_access' => false,
            'granted_actions' => [],
            'granted_resources' => []
        ];

        if (!$this->authService->isLoggedIn()) {
            return $headerUser;
        }

        $userId = $_SESSION['user_id'] ?? null;
        $employeeId = $_SESSION['employee_id'] ?? null;

        $user = $this->userService->getCurrentUserDetails($userId, $employeeId);

        if ($user) {
            $mid = !empty($user['middle_name']) ? $user['middle_name'] . ' ' : '';
            $headerUser['full_name'] = trim(($user['first_name'] ?? '') . ' ' . $mid . ($user['last_name'] ?? ''));
            $headerUser['initials'] = strtoupper(substr($user['first_name'] ?? 'U', 0, 1) . substr($user['last_name'] ?? 'S', 0, 1));
            $headerUser['profile_picture'] = $user['profile_picture'] ?? 'default-avatar.png';
            
            $headerUser['position_id'] = $user['position_id'] ?? null;
            $headerUser['position_name'] = $user['position_name'] ?? '';
            $headerUser['department_id'] = $user['department_id'] ?? ($user['role_dept_id'] ?? null);
            $headerUser['department_name'] = $user['department_name'] ?? '';
            $headerUser['department_code'] = $user['department_code'] ?? '';

            $headerUser['role'] = $user['role_name'] ?? 'Staff';
            $headerUser['role_prefix'] = $user['role_prefix'] ?? 'STF';
            $headerUser['is_global_access'] = filter_var($user['is_global_access'] ?? false, FILTER_VALIDATE_BOOLEAN);

            $roleNameLower = strtolower($headerUser['role']);
            $rolePrefixUpper = strtoupper($headerUser['role_prefix']);

            if (!empty($user['is_superadmin']) || $rolePrefixUpper === 'SA' || $rolePrefixUpper === 'SADM' || $roleNameLower === 'super administrator' || $roleNameLower === 'superadmin') {
                $headerUser['is_superadmin'] = true;
            } else {
                $headerUser['is_superadmin'] = false;
            }

            // Permissions
            if (!empty($user['role_id'])) {
                $perms = $this->permissionService->getRolePermissions($user['role_id']);
                $headerUser['granted_actions'] = $perms['actions'];
                $headerUser['granted_resources'] = $perms['resources'];
            }
        }

        return $headerUser;
    }
}
