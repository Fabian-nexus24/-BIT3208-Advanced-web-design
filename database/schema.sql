-- FarmConnect Kenya - Phase 1 Database Schema
-- Database: farmconnect_kenya

CREATE DATABASE IF NOT EXISTS farmconnect_kenya
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE farmconnect_kenya;

-- ---------------------------------------------------------------------------
-- admins (pre-seeded; no public registration)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    status ENUM('active', 'suspended') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_admins_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- farmers (public registration)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS farmers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    farm_name VARCHAR(150) NULL,
    county VARCHAR(100) NULL,
    farming_location VARCHAR(200) NULL,
    profile_image VARCHAR(255) NULL,
    status ENUM('active', 'suspended') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_farmers_status (status),
    INDEX idx_farmers_county (county)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- customers (public registration)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS customers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    delivery_address TEXT NULL,
    status ENUM('active', 'suspended') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_customers_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- products (schema only in Phase 1)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    farmer_id INT UNSIGNED NOT NULL,
    name VARCHAR(200) NOT NULL,
    category VARCHAR(100) NOT NULL DEFAULT 'Other',
    description TEXT NULL,
    price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    unit VARCHAR(50) NOT NULL DEFAULT 'kg',
    image_path VARCHAR(255) NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    stock_qty INT UNSIGNED NOT NULL DEFAULT 0,
    location VARCHAR(200) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_farmer
        FOREIGN KEY (farmer_id) REFERENCES farmers(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_products_farmer (farmer_id),
    INDEX idx_products_status (status),
    INDEX idx_products_category (category),
    INDEX idx_products_location (location)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- inquiries (schema only in Phase 1)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS inquiries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    farmer_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'closed') NOT NULL DEFAULT 'new',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_inquiries_customer
        FOREIGN KEY (customer_id) REFERENCES customers(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_inquiries_farmer
        FOREIGN KEY (farmer_id) REFERENCES farmers(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_inquiries_product
        FOREIGN KEY (product_id) REFERENCES products(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_inquiries_customer (customer_id),
    INDEX idx_inquiries_farmer (farmer_id),
    INDEX idx_inquiries_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- orders (Phase 6 — customer placement)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    farmer_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    delivery_notes TEXT NULL,
    payment_method ENUM('cash_on_delivery', 'mpesa') NOT NULL DEFAULT 'cash_on_delivery',
    status ENUM('pending', 'accepted', 'rejected', 'delivered') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_customer
        FOREIGN KEY (customer_id) REFERENCES customers(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_orders_farmer
        FOREIGN KEY (farmer_id) REFERENCES farmers(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_orders_product
        FOREIGN KEY (product_id) REFERENCES products(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_orders_customer (customer_id),
    INDEX idx_orders_farmer (farmer_id),
    INDEX idx_orders_product (product_id),
    INDEX idx_orders_status (status),
    INDEX idx_orders_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- notifications (Phase 7)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_role ENUM('admin', 'farmer', 'customer') NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    link_url VARCHAR(255) NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_notifications_user (user_role, user_id, is_read),
    INDEX idx_notifications_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Default admin: run after import:
--   php tools/seed_admin.php
-- Credentials: admin@farmconnect.co.ke / Admin@123
-- ---------------------------------------------------------------------------
