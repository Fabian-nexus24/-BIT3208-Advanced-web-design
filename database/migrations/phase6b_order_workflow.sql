-- FarmConnect Kenya - Phase 6B: order status workflow
-- Run once: mysql -u root farmconnect_kenya < database/migrations/phase6b_order_workflow.sql
-- Or: php tools/migrate_phase6b_orders.php

USE farmconnect_kenya;

-- Map legacy statuses from Phase 6 Part 1 (if present)
UPDATE orders SET status = 'accepted' WHERE status = 'confirmed';
UPDATE orders SET status = 'rejected' WHERE status = 'cancelled';
UPDATE orders SET status = 'delivered' WHERE status = 'completed';

ALTER TABLE orders
    MODIFY COLUMN status ENUM('pending', 'accepted', 'rejected', 'delivered')
    NOT NULL DEFAULT 'pending';
