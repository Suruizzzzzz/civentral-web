-- ============================================================
-- CIVENTRAL DATABASE SCHEMA FOR MYSQL
-- ============================================================

CREATE DATABASE IF NOT EXISTS `civentral` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `civentral`;

-- ------------------------------------------------------------
-- 1. ROLES
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `roles` (
    `role_id` INT AUTO_INCREMENT PRIMARY KEY,
    `department_id` INT NULL DEFAULT NULL,
    `role_name` VARCHAR(100) NOT NULL UNIQUE,
    `role_prefix` VARCHAR(10) NOT NULL UNIQUE,
    `is_global_access` TINYINT(1) DEFAULT 0,
    `description` VARCHAR(255),
    `status` ENUM('Active','Inactive') DEFAULT 'Active',
    `is_system_role` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_roles_department` FOREIGN KEY (`department_id`) REFERENCES `departments`(`department_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 2. DEPARTMENTS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `departments` (
    `department_id` INT AUTO_INCREMENT PRIMARY KEY,
    `department_code` VARCHAR(20) NOT NULL UNIQUE,
    `department_name` VARCHAR(150) NOT NULL UNIQUE,
    `department_head_user_id` INT NULL,
    `description` VARCHAR(255),
    `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 3. POSITIONS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `positions` (
    `position_id` INT AUTO_INCREMENT PRIMARY KEY,
    `department_id` INT NOT NULL,
    `position_name` VARCHAR(100) NOT NULL,
    `description` VARCHAR(255),
    `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT `fk_pos_dept`
        FOREIGN KEY (`department_id`)
        REFERENCES `departments`(`department_id`)
        ON DELETE CASCADE,
    UNIQUE (`department_id`, `position_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 4. USERS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `user_id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` VARCHAR(20) NOT NULL UNIQUE,
    `role_id` INT NOT NULL,   
    `position_id` INT NOT NULL,
    `first_name` VARCHAR(100) NOT NULL,
    `middle_name` VARCHAR(100),
    `last_name` VARCHAR(100) NOT NULL,
    `mobile_number` VARCHAR(15) NOT NULL UNIQUE,
    `profile_picture` VARCHAR(255) DEFAULT 'default-avatar.png',
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `status` ENUM('Pending', 'Active', 'Inactive', 'Locked', 'Archived') DEFAULT 'Pending',
    `failed_attempts` INT DEFAULT 0 NOT NULL,
    `last_login` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `is_first_login` TINYINT(1) DEFAULT 1,
    `email_verified` TINYINT(1) DEFAULT 0,
    `mobile_verified` TINYINT(1) DEFAULT 0,
    `password_changed_at` DATETIME NULL,
    `last_password_reset` DATETIME NULL,

    CONSTRAINT `fk_u_role` FOREIGN KEY (`role_id`) REFERENCES `roles`(`role_id`),
    CONSTRAINT `fk_u_pos` FOREIGN KEY (`position_id`) REFERENCES `positions`(`position_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add Department Head Foreign Key to Departments
ALTER TABLE `departments`
    ADD CONSTRAINT `fk_dept_head_user`
    FOREIGN KEY (`department_head_user_id`)
    REFERENCES `users`(`user_id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;

-- ------------------------------------------------------------
-- 5. USER OTPS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `user_otps` (
    `otp_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `otp_code` CHAR(6) NOT NULL,
    `purpose` ENUM('Login', 'Password Reset', 'Email Verification') NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `verified_at` DATETIME NULL,
    `is_used` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `attempts` INT DEFAULT 0,

    CONSTRAINT `fk_uo_user`
        FOREIGN KEY (`user_id`)
        REFERENCES `users`(`user_id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 6. MODULES
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `modules` (
    `module_id` INT AUTO_INCREMENT PRIMARY KEY,
    `module_name` VARCHAR(100) NOT NULL UNIQUE,
    `description` VARCHAR(255),
    `status` ENUM('Active','Inactive') DEFAULT 'Active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 7. RESOURCES
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `resources` (
    `resource_id` INT AUTO_INCREMENT PRIMARY KEY,
    `module_id` INT NOT NULL,
    `resource_name` VARCHAR(100) NOT NULL,
    `resource_route` VARCHAR(255) NOT NULL UNIQUE,
    `description` VARCHAR(255),
    `status` ENUM('Active','Inactive') DEFAULT 'Active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT `fk_res_mod`
        FOREIGN KEY (`module_id`)
        REFERENCES `modules`(`module_id`)
        ON DELETE CASCADE,

    UNIQUE (`module_id`, `resource_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 8. ACTIONS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `actions` (
    `action_id` INT AUTO_INCREMENT PRIMARY KEY,
    `action_name` VARCHAR(30) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 9. PERMISSIONS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `permissions` (
    `permission_id` INT AUTO_INCREMENT PRIMARY KEY,
    `resource_id` INT NOT NULL,
    `action_id` INT NOT NULL,
    `permission_key` VARCHAR(150) NOT NULL UNIQUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `status` ENUM('Active','Inactive') DEFAULT 'Active',
    
    CONSTRAINT `fk_perm_res`
        FOREIGN KEY (`resource_id`)
        REFERENCES `resources`(`resource_id`)
        ON DELETE CASCADE,

    CONSTRAINT `fk_perm_act`
        FOREIGN KEY (`action_id`)
        REFERENCES `actions`(`action_id`)
        ON DELETE CASCADE,

    UNIQUE (`resource_id`, `action_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 10. ROLE PERMISSIONS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `role_permissions` (
    `role_permission_id` INT AUTO_INCREMENT PRIMARY KEY,
    `role_id` INT NOT NULL,
    `permission_id` INT NOT NULL,
    `granted_by` INT,
    `granted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT `fk_rp_role`
        FOREIGN KEY (`role_id`)
        REFERENCES `roles`(`role_id`)
        ON DELETE CASCADE,

    CONSTRAINT `fk_rp_perm`
        FOREIGN KEY (`permission_id`)
        REFERENCES `permissions`(`permission_id`)
        ON DELETE CASCADE,

    CONSTRAINT `fk_rp_grantor`
        FOREIGN KEY (`granted_by`)
        REFERENCES `users`(`user_id`),

    UNIQUE (`role_id`, `permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 11. ROLE DEPARTMENT ACCESS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `role_department_access` (
    `access_id` INT AUTO_INCREMENT PRIMARY KEY,
    `role_id` INT NOT NULL,
    `department_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT `fk_rda_role`
        FOREIGN KEY (`role_id`)
        REFERENCES `roles`(`role_id`)
        ON DELETE CASCADE,

    CONSTRAINT `fk_rda_dept`
        FOREIGN KEY (`department_id`)
        REFERENCES `departments`(`department_id`)
        ON DELETE CASCADE,

    UNIQUE (`role_id`, `department_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 12. USER SESSIONS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `user_sessions` (
    `session_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `access_token_hash` CHAR(64),
    `refresh_token_hash` CHAR(64),
    `login_ip` VARCHAR(45),
    `user_agent` TEXT,
    `expires_at` DATETIME,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT `fk_us_user`
        FOREIGN KEY (`user_id`)
        REFERENCES `users`(`user_id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 13. LOGIN HISTORY
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `login_history` (
    `login_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT,
    `session_id` INT,
    `login_time` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `logout_time` DATETIME,
    `ip_address` VARCHAR(45),
    `device_info` TEXT,
    `login_status` ENUM('Success', 'Failed'),
    `browser` VARCHAR(100),
    `operating_system` VARCHAR(100),
    `failure_reason` VARCHAR(255),

    CONSTRAINT `fk_lh_user`
        FOREIGN KEY (`user_id`)
        REFERENCES `users`(`user_id`)
        ON DELETE SET NULL,

    CONSTRAINT `fk_lh_sess`
        FOREIGN KEY (`session_id`)
        REFERENCES `user_sessions`(`session_id`)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 14. AUDIT LOGS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `audit_id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `actor_user_id` INT NOT NULL,
    `session_id` INT NULL,
    `department_id` INT NULL,
    `module_id` INT NULL,
    `resource_id` INT NULL,
    `action` VARCHAR(50) NOT NULL,
    `target_table` VARCHAR(100),
    `target_id` VARCHAR(100),
    `description` VARCHAR(255),
    `ip_address` VARCHAR(45),
    `request_method` VARCHAR(10),
    `request_uri` VARCHAR(255),
    `browser` VARCHAR(100),
    `operating_system` VARCHAR(100),
    `status` ENUM('Success','Failed') DEFAULT 'Success',
    `context_json` JSON,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT `fk_al_actor`
        FOREIGN KEY (`actor_user_id`)
        REFERENCES `users`(`user_id`),

    CONSTRAINT `fk_al_sess`
        FOREIGN KEY (`session_id`)
        REFERENCES `user_sessions`(`session_id`),

    CONSTRAINT `fk_al_dept`
        FOREIGN KEY (`department_id`)
        REFERENCES `departments`(`department_id`),

    CONSTRAINT `fk_al_mod`
        FOREIGN KEY (`module_id`)
        REFERENCES `modules`(`module_id`),

    CONSTRAINT `fk_al_res`
        FOREIGN KEY (`resource_id`)
        REFERENCES `resources`(`resource_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 15. CITIZEN USERS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `citizen_users` (
    `citizen_user_id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- Basic Information
    `first_name` VARCHAR(100) NOT NULL,
    `middle_name` VARCHAR(100) NULL,
    `has_no_middle_name` TINYINT(1) NOT NULL DEFAULT 0,
    `last_name` VARCHAR(100) NOT NULL,
    `suffix` VARCHAR(20) NULL,

    -- Login Credentials
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `mobile_number` VARCHAR(20) UNIQUE,
    `password` VARCHAR(255) NOT NULL,

    -- Account Status
    `status` ENUM(
        'Pending',
        'Active',
        'Inactive',
        'Locked',
        'Archived'
    ) NOT NULL DEFAULT 'Pending',

    -- Registry Progress
    `registry_completed` TINYINT(1) NOT NULL DEFAULT 0,

    -- Security
    `failed_attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `last_login` DATETIME NULL,
    `biometric_enabled` TINYINT(1) NOT NULL DEFAULT 0,

    -- Audit
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME NULL,

    INDEX `idx_name` (`last_name`, `first_name`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 16. CITIZEN OTPS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `citizen_otps` (
    `otp_id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `citizen_user_id` BIGINT UNSIGNED NOT NULL,
    `otp_code` CHAR(6) NOT NULL,
    `purpose` ENUM(
        'Registration',
        'Login',
        'Password Reset'
    ) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `verified_at` DATETIME NULL,
    `is_used` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT `fk_otp_user`
        FOREIGN KEY (`citizen_user_id`)
        REFERENCES `citizen_users`(`citizen_user_id`)
        ON DELETE CASCADE,

    INDEX `idx_otp_lookup`
    (
        `citizen_user_id`,
        `otp_code`,
        `purpose`,
        `is_used`,
        `expires_at`
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 17. CITIZEN SESSIONS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `citizen_sessions` (
    `session_id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `citizen_user_id` BIGINT UNSIGNED NOT NULL,
    `device_id` VARCHAR(100) NULL,
    `refresh_token_hash` CHAR(64) NOT NULL UNIQUE,
    `push_token` VARCHAR(255) NULL,
    `platform` ENUM(
        'Android',
        'iOS',
        'Web'
    ) NOT NULL,
    `login_ip` VARCHAR(45) NULL,
    `is_revoked` TINYINT(1) NOT NULL DEFAULT 0,
    `last_active_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `expires_at` DATETIME NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT `fk_session_user`
        FOREIGN KEY (`citizen_user_id`)
        REFERENCES `citizen_users`(`citizen_user_id`)
        ON DELETE CASCADE,

    INDEX `idx_refresh` (`refresh_token_hash`, `is_revoked`),
    INDEX `idx_user_sessions` (`citizen_user_id`, `is_revoked`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 18. CITIZEN LOGIN HISTORY
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `citizen_login_history` (
    `login_id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `citizen_user_id` BIGINT UNSIGNED NULL,
    `session_id` BIGINT UNSIGNED NULL,
    `login_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `ip_address` VARCHAR(45) NULL,
    `login_status` ENUM('Success', 'Failed') NOT NULL,
    `failure_reason` VARCHAR(255) NULL,

    CONSTRAINT `fk_history_user`
        FOREIGN KEY (`citizen_user_id`)
        REFERENCES `citizen_users`(`citizen_user_id`)
        ON DELETE SET NULL,

    CONSTRAINT `fk_history_session`
        FOREIGN KEY (`session_id`)
        REFERENCES `citizen_sessions`(`session_id`)
        ON DELETE SET NULL,

    INDEX `idx_login_history` (`citizen_user_id`, `login_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 19. CITIZEN PASSWORD RESETS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `citizen_password_resets` (
    `reset_id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `citizen_user_id` BIGINT UNSIGNED NOT NULL,
    `reset_token_hash` CHAR(64) NOT NULL UNIQUE,
    `expires_at` DATETIME NOT NULL,
    `used_at` DATETIME NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT `fk_reset_user`
        FOREIGN KEY (`citizen_user_id`)
        REFERENCES `citizen_users`(`citizen_user_id`)
        ON DELETE CASCADE,

    INDEX `idx_reset_token` (`reset_token_hash`, `expires_at`, `used_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- SEED DATA (SUPERADMIN ONLY)
-- ============================================================

-- Actions Seed
INSERT IGNORE INTO `actions` (`action_id`, `action_name`) VALUES
(1, 'View'),
(2, 'Create'),
(3, 'Edit'),
(4, 'Delete'),
(5, 'Export'),
(6, 'Approve');

-- Super Administrator Role Seed
INSERT IGNORE INTO `roles` (`role_id`, `role_name`, `role_prefix`, `is_global_access`, `description`, `status`, `is_system_role`) VALUES
(1, 'Super Administrator', 'SA', 1, 'Full system administration access', 'Active', 1);

-- Default Department Seed for Superadmin
INSERT IGNORE INTO `departments` (`department_id`, `department_code`, `department_name`, `description`, `status`) VALUES
(1, 'IT', 'Information Technology Department', 'IT Systems and Infrastructure', 'Active');

-- Default Position Seed for Superadmin
INSERT IGNORE INTO `positions` (`position_id`, `department_id`, `position_name`, `description`, `status`) VALUES
(1, 1, 'IT Administrator', 'Manages IT infrastructure and security', 'Active');

-- Super Administrator User Seed (Password: Admin123!)
INSERT IGNORE INTO `users` (`user_id`, `employee_id`, `role_id`, `position_id`, `first_name`, `middle_name`, `last_name`, `mobile_number`, `email`, `password`, `status`, `email_verified`, `mobile_verified`, `is_first_login`) VALUES
(1, 'SA-2026-001', 1, 1, 'System', 'Super', 'Admin', '09123456789', 'admin@civentral.gov.ph', '$2y$10$suNPB0fehidZejZZ8l3UkeRdq9aIga.3rA.toE7teJ5MNIrwI3v8u', 'Active', 1, 1, 0);

