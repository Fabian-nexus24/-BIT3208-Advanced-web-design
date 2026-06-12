-- FarmConnect Kenya - Phase 3: Marketplace
USE farmconnect_kenya;

ALTER TABLE products
    ADD COLUMN category VARCHAR(100) NOT NULL DEFAULT 'Other' AFTER name,
    ADD COLUMN location VARCHAR(200) NULL AFTER stock_qty;

ALTER TABLE products
    ADD INDEX idx_products_category (category),
    ADD INDEX idx_products_location (location);
