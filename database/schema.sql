-- database/schema.sql

CREATE DATABASE IF NOT EXISTS ndis_db;
USE ndis_db;

CREATE TABLE IF NOT EXISTS organisations (
    organisation_id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    abn VARCHAR(20),
    email VARCHAR(255),
    phone VARCHAR(20),
    address TEXT
);

CREATE TABLE IF NOT EXISTS roles (
    role_id VARCHAR(36) PRIMARY KEY,
    role_name VARCHAR(100) NOT NULL,
    description TEXT
);

CREATE TABLE IF NOT EXISTS users (
    user_id VARCHAR(36) PRIMARY KEY,
    organisation_id VARCHAR(36),
    role_id VARCHAR(36),
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    phone VARCHAR(20),
    reset_token VARCHAR(64) DEFAULT NULL,
    reset_expires DATETIME DEFAULT NULL,
    status VARCHAR(50) DEFAULT 'Active',
    FOREIGN KEY (organisation_id) REFERENCES organisations(organisation_id),
    FOREIGN KEY (role_id) REFERENCES roles(role_id)
);

CREATE TABLE IF NOT EXISTS user_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    token_hash VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS participants (
    participant_id VARCHAR(36) PRIMARY KEY,
    organisation_id VARCHAR(36),
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    ndis_number VARCHAR(50) UNIQUE NOT NULL,
    date_of_birth DATE NOT NULL,
    address TEXT,
    emergency_contact VARCHAR(255) NOT NULL,
    support_needs TEXT NOT NULL,
    FOREIGN KEY (organisation_id) REFERENCES organisations(organisation_id)
);

CREATE TABLE IF NOT EXISTS shifts (
    shift_id VARCHAR(36) PRIMARY KEY,
    participant_id VARCHAR(36),
    user_id VARCHAR(36),
    shift_start DATETIME NOT NULL,
    shift_end DATETIME NOT NULL,
    location VARCHAR(255) NOT NULL,
    status VARCHAR(50) DEFAULT 'Scheduled',
    check_in_time DATETIME,
    check_out_time DATETIME,
    FOREIGN KEY (participant_id) REFERENCES participants(participant_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

CREATE TABLE IF NOT EXISTS progress_notes (
    note_id VARCHAR(36) PRIMARY KEY,
    participant_id VARCHAR(36),
    user_id VARCHAR(36),
    created_date DATE NOT NULL,
    note_text TEXT NOT NULL,
    wellbeing_status VARCHAR(100),
    attachments TEXT,
    FOREIGN KEY (participant_id) REFERENCES participants(participant_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

CREATE TABLE IF NOT EXISTS incident_reports (
    incident_id VARCHAR(36) PRIMARY KEY,
    participant_id VARCHAR(36),
    user_id VARCHAR(36),
    incident_date DATE NOT NULL,
    incident_type VARCHAR(100),
    description TEXT NOT NULL,
    severity VARCHAR(50),
    evidence_file TEXT,
    status VARCHAR(50) DEFAULT 'Reported',
    FOREIGN KEY (participant_id) REFERENCES participants(participant_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

CREATE TABLE IF NOT EXISTS messages (
    message_id VARCHAR(36) PRIMARY KEY,
    sender_id VARCHAR(36),
    participant_id VARCHAR(36),
    subject VARCHAR(255),
    body TEXT NOT NULL,
    sent_at DATETIME NOT NULL,
    status VARCHAR(50) DEFAULT 'Sent',
    FOREIGN KEY (sender_id) REFERENCES users(user_id),
    FOREIGN KEY (participant_id) REFERENCES participants(participant_id)
);

-- Insert initial data
INSERT IGNORE INTO organisations (organisation_id, name, abn, email, phone, address) 
VALUES ('org-1', 'GridLink Care Sydney', '12345678901', 'info@gridlinkcare.com.au', '02 1234 5678', '123 George St, Sydney NSW 2000');

INSERT IGNORE INTO roles (role_id, role_name, description) 
VALUES 
('role-admin', 'Provider Administrator', 'Full access to the system'),
('role-worker', 'Support Worker', 'Access to assigned clients and shifts');

-- Password is 'password123'
INSERT IGNORE INTO users (user_id, organisation_id, role_id, first_name, last_name, email, password_hash, phone, status)
VALUES ('user-1', 'org-1', 'role-admin', 'Admin', 'User', 'admin@gridlink.com', '$2y$10$WVUqdtDH/6o7UEhx5giYF.SFAqwKUSbb0qT.0qd.4eXJdQB/MLeq.', '0400000000', 'Active');
