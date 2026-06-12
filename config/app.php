<?php
declare(strict_types=1);

/**
 * Application configuration constants.
 */

require_once __DIR__ . '/../includes/env.php';

define('APP_NAME', 'FarmConnect Kenya');
define('APP_ENV', env_string('APP_ENV', 'local'));

// Override via config/env.local.php → BASE_URL
define('BASE_URL', env_string('BASE_URL', 'http://localhost/farmconnect/'));

define('ROLE_ADMIN', 'admin');
define('ROLE_FARMER', 'farmer');
define('ROLE_CUSTOMER', 'customer');

/** @var array<string, string> Role => dashboard path relative to BASE_URL */
define('DASHBOARD_PATHS', [
    ROLE_ADMIN    => 'admin/dashboard.php',
    ROLE_FARMER   => 'farmer/dashboard.php',
    ROLE_CUSTOMER => 'customer/dashboard.php',
]);

define('LOGIN_PATH', 'login.php');
define('LOGOUT_PATH', 'logout.php');
define('INDEX_PATH', 'index.php');

define('MIN_PASSWORD_LENGTH', 8);

define('UPLOAD_PROFILE_DIR', dirname(__DIR__) . '/uploads/profiles/');
define('UPLOAD_PROFILE_URL', 'uploads/profiles/');
define('UPLOAD_MAX_BYTES', 2 * 1024 * 1024); // 2 MB
define('UPLOAD_ALLOWED_MIME', ['image/jpeg', 'image/png', 'image/webp']);

define('REGISTER_CUSTOMER_PATH', 'register_customer.php');
define('REGISTER_CHOICE_PATH', 'register_choice.php');
define('REGISTER_FARMER_PATH', 'register_farmer.php');

define('PRODUCTS_PATH', 'products.php');
define('CONTACT_INQUIRY_PATH', 'contact_inquiry.php');
define('PLACE_ORDER_PATH', 'place_order.php');
define('ORDER_STATUS_PENDING', 'pending');
define('ORDER_STATUS_ACCEPTED', 'accepted');
define('ORDER_STATUS_REJECTED', 'rejected');
define('ORDER_STATUS_DELIVERED', 'delivered');
define('ORDER_PAYMENT_COD', 'cash_on_delivery');
define('ORDER_PAYMENT_MPESA', 'mpesa');
define('ORDER_NOTES_MAX_LENGTH', 500);
define('PRODUCT_FALLBACK_IMAGE', 'assets/img/product-fallback.svg');

define('UPLOAD_PRODUCT_DIR', dirname(__DIR__) . '/uploads/products/');
define('UPLOAD_PRODUCT_URL', 'uploads/products/');

/** @var list<string> */
define('PRODUCT_CATEGORIES', [
    'Vegetables',
    'Fruits',
    'Tubers & Roots',
    'Grains & Cereals',
    'Legumes',
    'Herbs & Spices',
    'Dairy',
    'Poultry & Eggs',
    'Other',
]);

define('PER_PAGE_PRODUCTS', 12);
define('PER_PAGE_ORDERS', 10);
define('PER_PAGE_INQUIRIES', 10);
define('PER_PAGE_NOTIFICATIONS', 15);

define('UPLOAD_MAX_WIDTH', 2000);
define('UPLOAD_MAX_HEIGHT', 2000);
define('UPLOAD_PRODUCT_MAX_WIDTH', 1200);
