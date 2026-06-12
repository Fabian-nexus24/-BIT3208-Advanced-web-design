-- =============================================================================
-- FarmConnect Kenya — Phase 6 Part 1: orders table
-- =============================================================================
-- Database: farmconnect_kenya (must match config/db.php)
--
-- Run once (CLI):
--   mysql -u root farmconnect_kenya < database/migrations/phase6_orders.sql
--
-- Or from project root:
--   php tools/migrate_phase6_orders.php
--
-- Fresh installs: orders is also defined in database/schema.sql
-- =============================================================================

USE farmconnect_kenya;

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
