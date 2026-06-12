<?php
declare(strict_types=1);

/**
 * Role-based access control middleware.
 */

/** Block non-admins from admin area — redirect to their dashboard. */
function require_admin(): void
{
    require_role(ROLE_ADMIN);
}

function require_farmer(): void
{
    require_role(ROLE_FARMER);
}

function require_customer(): void
{
    require_role(ROLE_CUSTOMER);
}

/** Prevent farmers/customers from accessing another role's URL prefix. */
function deny_cross_role_access(): void
{
    require_login();

    $role = current_role();
    if ($role === null) {
        redirect(LOGIN_PATH);
    }

    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $isAdminPath = str_contains($script, '/admin/');
    $isFarmerPath = str_contains($script, '/farmer/');
    $isCustomerPath = str_contains($script, '/customer/');

    if ($isAdminPath && $role !== ROLE_ADMIN) {
        flash_set('danger', 'Admin access only.');
        redirect_to_dashboard($role);
    }
    if ($isFarmerPath && $role !== ROLE_FARMER) {
        flash_set('danger', 'Farmer access only.');
        redirect_to_dashboard($role);
    }
    if ($isCustomerPath && $role !== ROLE_CUSTOMER) {
        flash_set('danger', 'Customer access only.');
        redirect_to_dashboard($role);
    }
}
