-- =====================================================
-- MAMA NTILIE FOOD ORDERING SYSTEM
-- ADMIN MANAGEMENT SYSTEM DATABASE MIGRATION
-- Compatible with MariaDB 10.4+
-- =====================================================

USE food_ordering_db;

-- 1. ADD ADMIN ROLE
ALTER TABLE users
MODIFY COLUMN role ENUM('customer','vendor','admin')
NOT NULL DEFAULT 'customer';

-- 2. ADD ACCOUNT STATUS
ALTER TABLE users
ADD COLUMN account_status ENUM('active','suspended','blocked')
NOT NULL DEFAULT 'active';

-- 3. CREATE VENDOR VERIFICATION TABLE
CREATE TABLE IF NOT EXISTS vendor_verifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vendor_id INT UNSIGNED NOT NULL UNIQUE,

    business_name VARCHAR(180) NOT NULL,
    business_address VARCHAR(255) NULL,

    business_license_number VARCHAR(100) NULL,
    tin_number VARCHAR(100) NULL,
    brela_number VARCHAR(100) NULL,

    verification_status ENUM(
        'pending',
        'contact_verified',
        'business_verified',
        'approved',
        'rejected'
    ) NOT NULL DEFAULT 'pending',

    rejection_reason TEXT NULL,

    reviewed_by INT UNSIGNED NULL,
    submitted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL DEFAULT NULL,

    CONSTRAINT fk_verification_vendor
        FOREIGN KEY (vendor_id) REFERENCES users(id),

    CONSTRAINT fk_verification_admin
        FOREIGN KEY (reviewed_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- 4. CREATE CUSTOMER COMPLAINTS TABLE
CREATE TABLE IF NOT EXISTS complaints (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    customer_id INT UNSIGNED NOT NULL,
    vendor_id INT UNSIGNED NULL,
    order_id INT UNSIGNED NULL,

    subject VARCHAR(180) NOT NULL,
    description TEXT NOT NULL,

    status ENUM(
        'open',
        'under_review',
        'resolved',
        'rejected'
    ) NOT NULL DEFAULT 'open',

    admin_decision TEXT NULL,
    resolved_by INT UNSIGNED NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL DEFAULT NULL,

    CONSTRAINT fk_complaint_customer
        FOREIGN KEY (customer_id) REFERENCES users(id),

    CONSTRAINT fk_complaint_vendor
        FOREIGN KEY (vendor_id) REFERENCES users(id),

    CONSTRAINT fk_complaint_order
        FOREIGN KEY (order_id) REFERENCES orders(id),

    CONSTRAINT fk_complaint_admin
        FOREIGN KEY (resolved_by) REFERENCES users(id),

    INDEX idx_complaints_status (status),
    INDEX idx_complaints_customer (customer_id)
) ENGINE=InnoDB;

-- 5. CREATE ADMIN ACTIVITY LOGS TABLE
CREATE TABLE IF NOT EXISTS admin_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    admin_id INT UNSIGNED NOT NULL,
    action VARCHAR(120) NOT NULL,

    target_user_id INT UNSIGNED NULL,
    reason TEXT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_admin_logs_admin
        FOREIGN KEY (admin_id) REFERENCES users(id),

    CONSTRAINT fk_admin_logs_target
        FOREIGN KEY (target_user_id) REFERENCES users(id),

    INDEX idx_admin_logs_admin (admin_id),
    INDEX idx_admin_logs_date (created_at)
) ENGINE=InnoDB;