-- Civentral to Transport & Mobility Management SSO and RBAC integration.
-- Apply once to the Civentral MySQL 8.0+ database.

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sso_authorization_codes (
    authorization_code_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    client_id VARCHAR(64) NOT NULL,
    user_id INT NOT NULL,
    code_hash CHAR(64) NOT NULL,
    redirect_uri VARCHAR(255) NOT NULL,
    expires_at DATETIME(6) NOT NULL,
    consumed_at DATETIME(6) NULL,
    created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    PRIMARY KEY (authorization_code_id),
    UNIQUE KEY uq_sso_authorization_codes_hash (code_hash),
    KEY idx_sso_authorization_codes_expiry (client_id, expires_at, consumed_at),
    CONSTRAINT fk_sso_authorization_codes_user
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO modules (module_name, description, status)
SELECT
    'Transport & Mobility Management',
    'PSTMD transport operations and role-protected submodules.',
    'Active'
WHERE NOT EXISTS (
    SELECT 1 FROM modules WHERE module_name = 'Transport & Mobility Management'
);

UPDATE modules
SET description = 'PSTMD transport operations and role-protected submodules.',
    status = 'Active'
WHERE module_name = 'Transport & Mobility Management';

INSERT INTO resources (module_id, resource_name, resource_route, description, status)
SELECT module_id, resource_name, resource_route, description, 'Active'
FROM (
    SELECT 'TMM Dashboard' resource_name, '/dashboard.php' resource_route, 'Transport and Mobility dashboard.' description
    UNION ALL SELECT 'TMM PUV Database', '/puv', 'PUV vehicle and operator records.'
    UNION ALL SELECT 'TMM Franchise Management', '/franchise', 'Franchise applications, approvals, and renewals.'
    UNION ALL SELECT 'TMM Traffic Violation Ticketing', '/traffic', 'Traffic detection, enforcement, evidence, and tickets.'
    UNION ALL SELECT 'TMM Vehicle Inspection Registration', '/inspection', 'Vehicle inspection and registration workflows.'
    UNION ALL SELECT 'TMM Parking Terminal Management', '/parking', 'Parking and terminal operations.'
    UNION ALL SELECT 'TMM System Reports', '/reports.php', 'Cross-submodule management reports and exports.'
) resource_seed
CROSS JOIN (
    SELECT module_id
    FROM modules
    WHERE module_name = 'Transport & Mobility Management'
    LIMIT 1
) tmm_module
WHERE NOT EXISTS (
    SELECT 1
    FROM resources existing
    WHERE existing.module_id = tmm_module.module_id
      AND existing.resource_name = resource_seed.resource_name
);

UPDATE resources resource
JOIN modules module ON module.module_id = resource.module_id
SET resource.status = 'Active'
WHERE module.module_name = 'Transport & Mobility Management';

INSERT INTO permissions (resource_id, action_id, permission_key, status)
SELECT
    resource.resource_id,
    action.action_id,
    CONCAT(
        CASE resource.resource_name
            WHEN 'TMM Dashboard' THEN 'tmm.dashboard'
            WHEN 'TMM PUV Database' THEN 'tmm.puv'
            WHEN 'TMM Franchise Management' THEN 'tmm.franchise'
            WHEN 'TMM Traffic Violation Ticketing' THEN 'tmm.traffic'
            WHEN 'TMM Vehicle Inspection Registration' THEN 'tmm.inspection'
            WHEN 'TMM Parking Terminal Management' THEN 'tmm.parking'
            WHEN 'TMM System Reports' THEN 'tmm.reports'
        END,
        ':',
        LOWER(action.action_name)
    ),
    'Active'
FROM resources resource
JOIN modules module ON module.module_id = resource.module_id
CROSS JOIN actions action
WHERE module.module_name = 'Transport & Mobility Management'
  AND action.action_name IN ('View', 'Create', 'Edit', 'Delete', 'Export', 'Approve')
  AND NOT EXISTS (
      SELECT 1
      FROM permissions existing
      WHERE existing.resource_id = resource.resource_id
        AND existing.action_id = action.action_id
  );

UPDATE permissions permission
JOIN resources resource ON resource.resource_id = permission.resource_id
JOIN modules module ON module.module_id = resource.module_id
JOIN actions action ON action.action_id = permission.action_id
SET permission.permission_key = CONCAT(
        CASE resource.resource_name
            WHEN 'TMM Dashboard' THEN 'tmm.dashboard'
            WHEN 'TMM PUV Database' THEN 'tmm.puv'
            WHEN 'TMM Franchise Management' THEN 'tmm.franchise'
            WHEN 'TMM Traffic Violation Ticketing' THEN 'tmm.traffic'
            WHEN 'TMM Vehicle Inspection Registration' THEN 'tmm.inspection'
            WHEN 'TMM Parking Terminal Management' THEN 'tmm.parking'
            WHEN 'TMM System Reports' THEN 'tmm.reports'
        END,
        ':',
        LOWER(action.action_name)
    ),
    permission.status = 'Active'
WHERE module.module_name = 'Transport & Mobility Management';

INSERT INTO roles (
    role_name, role_prefix, is_global_access, is_superadmin,
    description, status, is_system_role, department_id
)
SELECT role_name, role_prefix, 0, 0, description, 'Active', 0, department_id
FROM (
    SELECT 'TMM System Admin' role_name, 'TMMADMIN' role_prefix, 'Full administration of the five TMM submodules.' description
    UNION ALL SELECT 'Records Officer', 'TMMRECORD', 'PUV Database operations.'
    UNION ALL SELECT 'Franchise Officer', 'TMMFRANCH', 'Franchise Management operations.'
    UNION ALL SELECT 'Traffic Officer', 'TMMTRAFFIC', 'Traffic Violation Ticketing operations.'
    UNION ALL SELECT 'Vehicle Inspector', 'TMMINSPECT', 'Vehicle Inspection and Registration operations.'
    UNION ALL SELECT 'Terminal / Parking Officer', 'TMMPARK', 'Parking and Terminal operations.'
) role_seed
CROSS JOIN (
    SELECT department_id
    FROM departments
    WHERE department_code = 'TMM' AND status = 'Active'
    LIMIT 1
) tmm_department
WHERE NOT EXISTS (
    SELECT 1 FROM roles existing WHERE existing.role_prefix = role_seed.role_prefix
);

UPDATE roles role
JOIN departments department ON department.department_code = 'TMM'
SET role.department_id = department.department_id,
    role.status = 'Active',
    role.is_global_access = 0,
    role.is_superadmin = 0
WHERE role.role_prefix IN (
    'TMMADMIN', 'TMMRECORD', 'TMMFRANCH',
    'TMMTRAFFIC', 'TMMINSPECT', 'TMMPARK'
);

-- The existing Transport Administrator is the TMM department administrator.
UPDATE roles role
JOIN departments department ON department.department_code = 'TMM'
SET role.department_id = department.department_id,
    role.status = 'Active',
    role.is_global_access = 0,
    role.is_superadmin = 0
WHERE role.role_prefix = 'TA';

INSERT INTO positions (department_id, position_name, status)
SELECT department.department_id, 'Transport Administrator', 'Active'
FROM departments department
WHERE department.department_code = 'TMM'
  AND NOT EXISTS (
      SELECT 1
      FROM positions existing
      WHERE existing.department_id = department.department_id
        AND existing.position_name = 'Transport Administrator'
  );

UPDATE users user
JOIN roles role ON role.role_id = user.role_id AND role.role_prefix = 'TA'
JOIN departments department ON department.department_code = 'TMM'
JOIN positions position
  ON position.department_id = department.department_id
 AND position.position_name = 'Transport Administrator'
SET user.position_id = position.position_id,
    user.updated_at = CURRENT_TIMESTAMP;

-- Every TMM role can open the dashboard.
INSERT INTO role_permissions (role_id, permission_id, granted_by)
SELECT role.role_id, permission.permission_id, NULL
FROM roles role
JOIN permissions permission ON permission.permission_key = 'tmm.dashboard:view'
WHERE role.role_prefix IN (
    'TMMADMIN', 'TMMRECORD', 'TMMFRANCH',
    'TMMTRAFFIC', 'TMMINSPECT', 'TMMPARK'
)
AND NOT EXISTS (
    SELECT 1 FROM role_permissions existing
    WHERE existing.role_id = role.role_id
      AND existing.permission_id = permission.permission_id
);

-- TMM System Admin receives every TMM permission.
INSERT INTO role_permissions (role_id, permission_id, granted_by)
SELECT role.role_id, permission.permission_id, NULL
FROM roles role
CROSS JOIN permissions permission
JOIN resources resource ON resource.resource_id = permission.resource_id
JOIN modules module ON module.module_id = resource.module_id
WHERE role.role_prefix IN ('TMMADMIN', 'TA')
  AND module.module_name = 'Transport & Mobility Management'
  AND NOT EXISTS (
      SELECT 1 FROM role_permissions existing
      WHERE existing.role_id = role.role_id
        AND existing.permission_id = permission.permission_id
  );

-- Department-scoped account viewing and creation for the TMM System Admin.
INSERT INTO role_permissions (role_id, permission_id, granted_by)
SELECT role.role_id, permission.permission_id, NULL
FROM roles role
CROSS JOIN permissions permission
JOIN resources resource ON resource.resource_id = permission.resource_id
JOIN actions action ON action.action_id = permission.action_id
WHERE role.role_prefix IN ('TMMADMIN', 'TA')
  AND LOWER(resource.resource_name) IN ('users account', 'account status')
  AND action.action_name IN ('View', 'Create', 'Edit')
  AND NOT EXISTS (
      SELECT 1 FROM role_permissions existing
      WHERE existing.role_id = role.role_id
        AND existing.permission_id = permission.permission_id
  );

-- Operational roles receive non-delete actions for their assigned submodule.
INSERT INTO role_permissions (role_id, permission_id, granted_by)
SELECT role.role_id, permission.permission_id, NULL
FROM roles role
CROSS JOIN permissions permission
JOIN actions action ON action.action_id = permission.action_id
WHERE action.action_name IN ('View', 'Create', 'Edit', 'Export', 'Approve')
  AND permission.permission_key LIKE CONCAT(
      CASE role.role_prefix
          WHEN 'TMMRECORD' THEN 'tmm.puv'
          WHEN 'TMMFRANCH' THEN 'tmm.franchise'
          WHEN 'TMMTRAFFIC' THEN 'tmm.traffic'
          WHEN 'TMMINSPECT' THEN 'tmm.inspection'
          WHEN 'TMMPARK' THEN 'tmm.parking'
      END,
      ':%'
  )
  AND role.role_prefix IN (
      'TMMRECORD', 'TMMFRANCH', 'TMMTRAFFIC', 'TMMINSPECT', 'TMMPARK'
  )
  AND NOT EXISTS (
      SELECT 1 FROM role_permissions existing
      WHERE existing.role_id = role.role_id
        AND existing.permission_id = permission.permission_id
  );
