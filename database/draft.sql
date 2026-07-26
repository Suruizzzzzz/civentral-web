CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(20) NOT NULL UNIQUE,
    role_id INT NOT NULL,   
    position_id INT NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    last_name VARCHAR(100) NOT NULL,
    mobile_number VARCHAR(15) NOT NULL UNIQUE,
    profile_picture VARCHAR(255) DEFAULT 'default-avatar.png',
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    status ENUM(
        'Pending',
        'Active',
        'Inactive',
        'Locked',
        'Archived'
    ) DEFAULT 'Pending',
    failed_attempts INT DEFAULT 0 NOT NULL,
    last_login DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_first_login BOOLEAN DEFAULT TRUE,
    email_verified BOOLEAN DEFAULT FALSE,
    mobile_verified BOOLEAN DEFAULT FALSE,
    password_changed_at DATETIME NULL,
    last_password_reset DATETIME NULL,
);

CREATE TABLE user_otps (
    otp_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    otp_code CHAR(6) NOT NULL,
    purpose ENUM(
        'Login',
        'Password Reset',
        'Email Verification'
    ) NOT NULL,
    expires_at DATETIME NOT NULL,
    verified_at DATETIME NULL,
    is_used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    attempts INT DEFAULT 0,

    FOREIGN KEY(user_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE
);

CREATE TABLE roles (
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(100) NOT NULL UNIQUE,
    role_prefix VARCHAR(10) NOT NULL UNIQUE,
    is_global_access BOOLEAN DEFAULT FALSE,
    description VARCHAR(255),
    status ENUM('Active','Inactive') DEFAULT 'Active',
    is_system_role BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE departments (
    department_id INT AUTO_INCREMENT PRIMARY KEY,
    department_code VARCHAR(20) NOT NULL UNIQUE,
    department_name VARCHAR(150) NOT NULL UNIQUE,
    department_head_user_id INT NULL,
    description VARCHAR(255),
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE positions (
    position_id INT AUTO_INCREMENT PRIMARY KEY,
    department_id INT NOT NULL,
    position_name VARCHAR(100) NOT NULL,
    description VARCHAR(255),
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_position_department
        FOREIGN KEY (department_id)
        REFERENCES departments(department_id)
        ON DELETE CASCADE,
    UNIQUE (department_id, position_name)
);

CREATE TABLE modules (
    module_id INT AUTO_INCREMENT PRIMARY KEY,
    module_name VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(255),
    status ENUM('Active','Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE resources (
    resource_id INT AUTO_INCREMENT PRIMARY KEY,
    module_id INT NOT NULL,
    resource_name VARCHAR(100) NOT NULL,
    resource_route VARCHAR(255) NOT NULL UNIQUE,
    description VARCHAR(255),
    status ENUM('Active','Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (module_id)
        REFERENCES modules(module_id)
        ON DELETE CASCADE,

    UNIQUE (module_id, resource_name)
);

CREATE TABLE actions (
    action_id INT AUTO_INCREMENT PRIMARY KEY,
    action_name VARCHAR(30) NOT NULL UNIQUE
);

INSERT INTO actions(action_name)
VALUES
('View'),
('Create'),
('Edit'),
('Delete'),
('Export'),
('Approve');

CREATE TABLE permissions (
    permission_id INT AUTO_INCREMENT PRIMARY KEY,
    resource_id INT NOT NULL,
    action_id INT NOT NULL,
    permission_key VARCHAR(150) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Active','Inactive') DEFAULT 'Active',
    FOREIGN KEY(resource_id)
        REFERENCES resources(resource_id)
        ON DELETE CASCADE,

    FOREIGN KEY(action_id)
        REFERENCES actions(action_id)
        ON DELETE CASCADE,

    UNIQUE(resource_id, action_id)
);

CREATE TABLE role_permissions (
    role_permission_id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    granted_by INT,
    granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

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

CREATE TABLE role_department_access (
    access_id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    department_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY(role_id)
        REFERENCES roles(role_id)
        ON DELETE CASCADE,

    FOREIGN KEY(department_id)
        REFERENCES departments(department_id)
        ON DELETE CASCADE,

    UNIQUE(role_id,department_id)

);

CREATE TABLE user_sessions (
    session_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    access_token_hash CHAR(64),
    refresh_token_hash CHAR(64),
    login_ip VARCHAR(45),
    user_agent TEXT,
    expires_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY(user_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE

);

CREATE TABLE login_history (
    login_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    session_id INT,
    login_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    logout_time DATETIME,
    ip_address VARCHAR(45),
    device_info TEXT,
    login_status ENUM('Success', 'Failed'),
    browser VARCHAR(100),
    operating_system VARCHAR(100),
    failure_reason VARCHAR(255),

    FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON DELETE SET NULL,

    FOREIGN KEY (session_id)
        REFERENCES user_sessions(session_id)
        ON DELETE SET NULL
);

CREATE TABLE audit_logs (
    audit_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    actor_user_id INT NOT NULL,
    session_id INT NULL,
    department_id INT NULL,
    module_id INT NULL,
    resource_id INT NULL,
    action VARCHAR(50) NOT NULL,
    target_table VARCHAR(100),
    target_id VARCHAR(100),
    description VARCHAR(255),
    ip_address VARCHAR(45),
    request_method VARCHAR(10),
    request_uri VARCHAR(255),
    browser VARCHAR(100),
    operating_system VARCHAR(100),
    status ENUM('Success','Failed') DEFAULT 'Success',
    context_json JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

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

/* 

// FOR USERS
CONSTRAINT fk_user_role FOREIGN KEY (role_id) REFERENCES roles(role_id),
CONSTRAINT fk_user_position FOREIGN KEY (position_id) REFERENCES positions(position_id)


// FOR DEPARTMENTS
ALTER TABLE departments
ADD CONSTRAINT fk_department_head
FOREIGN KEY (department_head_user_id)
REFERENCES users(user_id)
ON DELETE SET NULL
ON UPDATE CASCADE; 

*/

-- CITIZEN
CREATE TABLE citizen_users (
    citizen_user_id INT AUTO_INCREMENT PRIMARY KEY,
    citizen_registry_id INT NULL UNIQUE,
    email VARCHAR(150) UNIQUE,
    mobile_number VARCHAR(20) UNIQUE,
    password VARCHAR(255) NOT NULL,
    status ENUM(
        'Pending',
        'Active',
        'Inactive',
        'Locked',
        'Archived'
    ) DEFAULT 'Pending',
    failed_attempts INT NOT NULL DEFAULT 0,
    last_login DATETIME NULL,
    is_first_login BOOLEAN DEFAULT TRUE,
    email_verified BOOLEAN DEFAULT FALSE,
    mobile_verified BOOLEAN DEFAULT FALSE,
    password_changed_at DATETIME NULL,
    last_password_reset DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CHECK (
        email IS NOT NULL
        OR mobile_number IS NOT NULL
    )
);

CREATE TABLE citizen_otps (
    otp_id INT AUTO_INCREMENT PRIMARY KEY,
    citizen_user_id INT NOT NULL,
    otp_code CHAR(6) NOT NULL,
    purpose ENUM(
        'Registration',
        'Login',
        'Password Reset',
        'Email Verification',
        'Mobile Verification'
    ) NOT NULL,
    expires_at DATETIME NOT NULL,
    verified_at DATETIME NULL,
    is_used BOOLEAN DEFAULT FALSE,
    attempts INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_citizen_otp_user
        FOREIGN KEY (citizen_user_id)
        REFERENCES citizen_users(citizen_user_id)
        ON DELETE CASCADE
);

CREATE TABLE citizen_sessions (
    session_id INT AUTO_INCREMENT PRIMARY KEY,
    citizen_user_id INT NOT NULL,
    access_token_hash CHAR(64),
    refresh_token_hash CHAR(64),
    login_ip VARCHAR(45),
    user_agent TEXT,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_citizen_session_user
        FOREIGN KEY (citizen_user_id)
        REFERENCES citizen_users(citizen_user_id)
        ON DELETE CASCADE
);

CREATE TABLE citizen_login_history (
    login_id INT AUTO_INCREMENT PRIMARY KEY,
    citizen_user_id INT NULL,
    session_id INT NULL,
    login_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    logout_time DATETIME NULL,
    ip_address VARCHAR(45),
    browser VARCHAR(100),
    operating_system VARCHAR(100),
    device_info TEXT,
    login_status ENUM(
        'Success',
        'Failed'
    ) NOT NULL,
    failure_reason VARCHAR(255),

    FOREIGN KEY (citizen_user_id)
        REFERENCES citizen_users(citizen_user_id)
        ON DELETE SET NULL,

    FOREIGN KEY (session_id)
        REFERENCES citizen_sessions(session_id)
        ON DELETE SET NULL
);

CREATE TABLE citizen_password_resets (
    reset_id INT AUTO_INCREMENT PRIMARY KEY,
    citizen_user_id INT NOT NULL,
    reset_token_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (citizen_user_id)
        REFERENCES citizen_users(citizen_user_id)
        ON DELETE CASCADE
);  