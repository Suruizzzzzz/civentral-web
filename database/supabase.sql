-- 1. ENUM TYPES
CREATE TYPE status_type AS ENUM ('Active', 'Inactive');
CREATE TYPE user_status_type AS ENUM ('Pending', 'Active', 'Inactive', 'Locked', 'Archived');
CREATE TYPE otp_purpose_type AS ENUM ('Login', 'Password Reset', 'Email Verification');
CREATE TYPE login_status_type AS ENUM ('Success', 'Failed');

-- 2. REUSABLE TRIGGER FOR updated_at
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
   NEW.updated_at = NOW();
   RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- 3. TABLES CREATION

-- Roles
CREATE TABLE roles (
    role_id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    role_name TEXT NOT NULL UNIQUE,
    role_prefix VARCHAR(10) NOT NULL UNIQUE,
    is_global_access BOOLEAN DEFAULT FALSE,
    description TEXT,
    status status_type DEFAULT 'Active',
    is_system_role BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Departments
CREATE TABLE departments (
    department_id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    department_code VARCHAR(20) NOT NULL UNIQUE,
    department_name TEXT NOT NULL UNIQUE,
    department_head_user_id BIGINT NULL, -- Foreign key added below after users table
    description TEXT,
    status status_type DEFAULT 'Active',
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Positions
CREATE TABLE positions (
    position_id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    department_id BIGINT NOT NULL,
    position_name TEXT NOT NULL,
    description TEXT,
    status status_type DEFAULT 'Active',
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_position_department
        FOREIGN KEY (department_id)
        REFERENCES departments(department_id)
        ON DELETE CASCADE,
    UNIQUE (department_id, position_name)
);

-- Users
CREATE TABLE users (
    user_id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    employee_id VARCHAR(20) NOT NULL UNIQUE,
    role_id BIGINT NOT NULL, 
    position_id BIGINT NOT NULL,
    first_name TEXT NOT NULL,
    middle_name TEXT,
    last_name TEXT NOT NULL,
    mobile_number VARCHAR(15) NOT NULL UNIQUE,
    profile_picture TEXT DEFAULT 'default-avatar.png',
    email TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    status user_status_type DEFAULT 'Pending',
    failed_attempts INT DEFAULT 0 NOT NULL,
    last_login TIMESTAMPTZ NULL,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    is_first_login BOOLEAN DEFAULT TRUE,
    email_verified BOOLEAN DEFAULT FALSE,
    mobile_verified BOOLEAN DEFAULT FALSE,
    password_changed_at TIMESTAMPTZ NULL,
    last_password_reset TIMESTAMPTZ NULL,

    CONSTRAINT fk_user_role FOREIGN KEY (role_id) REFERENCES roles(role_id),
    CONSTRAINT fk_user_position FOREIGN KEY (position_id) REFERENCES positions(position_id)
);

-- Add cyclic Foreign Key constraint back to Departments
ALTER TABLE departments
ADD CONSTRAINT fk_department_head
FOREIGN KEY (department_head_user_id)
REFERENCES users(user_id)
ON DELETE SET NULL
ON UPDATE CASCADE;

-- User OTPs
CREATE TABLE user_otps (
    otp_id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    user_id BIGINT NOT NULL,
    otp_code CHAR(6) NOT NULL,
    purpose otp_purpose_type NOT NULL,
    expires_at TIMESTAMPTZ NOT NULL,
    verified_at TIMESTAMPTZ NULL,
    is_used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    attempts INT DEFAULT 0,

    FOREIGN KEY(user_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE
);

-- Modules
CREATE TABLE modules (
    module_id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    module_name TEXT NOT NULL UNIQUE,
    description TEXT,
    status status_type DEFAULT 'Active',
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Resources
CREATE TABLE resources (
    resource_id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    module_id BIGINT NOT NULL,
    resource_name TEXT NOT NULL,
    resource_route TEXT NOT NULL UNIQUE,
    description TEXT,
    status status_type DEFAULT 'Active',
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (module_id)
        REFERENCES modules(module_id)
        ON DELETE CASCADE,

    UNIQUE (module_id, resource_name)
);

-- Actions
CREATE TABLE actions (
    action_id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    action_name VARCHAR(30) NOT NULL UNIQUE,
    description TEXT,
    status status_type DEFAULT 'Active',
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO actions(action_name, description)
VALUES
('View', 'Grants read-only privilege to query and inspect resource records.'),
('Create', 'Grants privilege to register and submit new resource entries.'),
('Edit', 'Grants privilege to edit and modify existing transactional details.'),
('Delete', 'Grants privilege to permanently remove or soft-delete records.'),
('Export', 'Grants privilege to extract CSV, Excel, or PDF report outputs.'),
('Approve', 'Grants privilege to validate and approve submitted citizen applications & permits.');

-- Permissions
CREATE TABLE permissions (
    permission_id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    resource_id BIGINT NOT NULL,
    action_id BIGINT NOT NULL,
    permission_key TEXT NOT NULL UNIQUE,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    status status_type DEFAULT 'Active',
    
    FOREIGN KEY(resource_id)
        REFERENCES resources(resource_id)
        ON DELETE CASCADE,

    FOREIGN KEY(action_id)
        REFERENCES actions(action_id)
        ON DELETE CASCADE,

    UNIQUE(resource_id, action_id)
);

-- Role Permissions
CREATE TABLE role_permissions (
    role_permission_id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    role_id BIGINT NOT NULL,
    permission_id BIGINT NOT NULL,
    granted_by BIGINT,
    granted_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY(role_id)
        REFERENCES roles(role_id)
        ON DELETE CASCADE,

    FOREIGN KEY(permission_id)
        REFERENCES permissions(permission_id)
        ON DELETE CASCADE,

    FOREIGN KEY(granted_by)
        REFERENCES users(user_id),

    UNIQUE(role_id, permission_id)
);

-- Role Department Access
CREATE TABLE role_department_access (
    access_id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    role_id BIGINT NOT NULL,
    department_id BIGINT NOT NULL,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY(role_id)
        REFERENCES roles(role_id)
        ON DELETE CASCADE,

    FOREIGN KEY(department_id)
        REFERENCES departments(department_id)
        ON DELETE CASCADE,

    UNIQUE(role_id, department_id)
);

-- User Sessions
CREATE TABLE user_sessions (
    session_id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    user_id BIGINT NOT NULL,
    access_token_hash CHAR(64),
    refresh_token_hash CHAR(64),
    login_ip VARCHAR(45),
    user_agent TEXT,
    expires_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY(user_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE
);

-- Login History
CREATE TABLE login_history (
    login_id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    user_id BIGINT,
    session_id BIGINT,
    login_time TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    logout_time TIMESTAMPTZ,
    ip_address VARCHAR(45),
    device_info TEXT,
    login_status login_status_type,
    browser TEXT,
    operating_system TEXT,
    failure_reason TEXT,

    FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON DELETE SET NULL,

    FOREIGN KEY (session_id)
        REFERENCES user_sessions(session_id)
        ON DELETE SET NULL
);

-- Audit Logs
CREATE TABLE audit_logs (
    audit_id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    actor_user_id BIGINT NOT NULL,
    session_id BIGINT NULL,
    department_id BIGINT NULL,
    module_id BIGINT NULL,
    resource_id BIGINT NULL,
    action VARCHAR(50) NOT NULL,
    target_table TEXT,
    target_id TEXT,
    description TEXT,
    ip_address VARCHAR(45),
    request_method VARCHAR(10),
    request_uri TEXT,
    browser TEXT,
    operating_system TEXT,
    status login_status_type DEFAULT 'Success',
    context_json JSONB, -- Converted from JSON to JSONB for better Postgres performance
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY(actor_user_id)
        REFERENCES users(user_id),

    FOREIGN KEY(session_id)
        REFERENCES user_sessions(session_id),

    FOREIGN KEY(department_id)
        REFERENCES departments(department_id),

    FOREIGN KEY(module_id)
        REFERENCES modules(module_id),

    FOREIGN KEY(resource_id)
        REFERENCES resources(resource_id)
);

-- 4. ATTACH UPDATED_AT TRIGGERS
CREATE TRIGGER update_roles_updated_at BEFORE UPDATE ON roles FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_departments_updated_at BEFORE UPDATE ON departments FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_positions_updated_at BEFORE UPDATE ON positions FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_users_updated_at BEFORE UPDATE ON users FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_modules_updated_at BEFORE UPDATE ON modules FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_resources_updated_at BEFORE UPDATE ON resources FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- 5. INITIAL SEED DATA

-- Insert Default Roles
INSERT INTO roles (role_name, role_prefix, is_global_access, description, status, is_system_role)
VALUES 
('Super Admin', 'SADM', TRUE, 'Global access super administrator role with full system privileges', 'Active', TRUE)
ON CONFLICT (role_name) DO NOTHING;

-- Insert Default Department
INSERT INTO departments (department_code, department_name, description, status)
VALUES 
('ITD', 'Information Technology Department', 'ICT operations, software management, and infrastructure', 'Active')
ON CONFLICT (department_code) DO NOTHING;

-- Insert Default Position
INSERT INTO positions (department_id, position_name, description, status)
VALUES 
((SELECT department_id FROM departments WHERE department_code = 'ITD'), 'System Administrator', 'Head system engineer and administrator', 'Active')
ON CONFLICT (department_id, position_name) DO NOTHING;

-- Insert Superadmin User Account
INSERT INTO users (
    employee_id,
    role_id,
    position_id,
    first_name,
    middle_name,
    last_name,
    mobile_number,
    profile_picture,
    email,
    password,
    status,
    is_first_login,
    email_verified,
    mobile_verified
) VALUES (
    'SADM-2026-001',
    (SELECT role_id FROM roles WHERE role_prefix = 'SADM'),
    (SELECT position_id FROM positions WHERE position_name = 'System Administrator'),
    'Joshua',
    'S.',
    'Suruiz',
    '09123456789',
    'default-avatar.png',
    'superadmin@civentral.gov.ph',
    '1234', -- Replace with hashed password if using password_hash() in PHP
    'Active',
    FALSE,
    TRUE,
    TRUE
)
ON CONFLICT (employee_id) DO NOTHING;

-- Update Department Head
UPDATE departments
SET department_head_user_id = (SELECT user_id FROM users WHERE employee_id = 'SADM-2026-001')
WHERE department_code = 'ITD';
