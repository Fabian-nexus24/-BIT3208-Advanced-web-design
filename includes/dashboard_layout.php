<?php
declare(strict_types=1);

/**
 * Shared dashboard placeholder sections (Phase 1).
 *
 * @param string $roleLabel Display label for the role
 */
function render_dashboard_placeholders(string $roleLabel): void
{
    ?>
    <div class="alert alert-info">
        <strong>Phase 1 — Foundation only.</strong>
        Marketplace, inquiries, and payments are not built yet.
    </div>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card h-100 border-secondary">
                <div class="card-header bg-light">Products</div>
                <div class="card-body">
                    <p class="text-muted mb-0">Product management for <?= e($roleLabel) ?> will be available in a later phase.</p>
                    <ul class="list-group list-group-flush mt-3 opacity-50">
                        <li class="list-group-item disabled">No products yet</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100 border-secondary">
                <div class="card-header bg-light">Inquiries</div>
                <div class="card-body">
                    <p class="text-muted mb-0">Inquiry messaging will be available in a later phase.</p>
                    <ul class="list-group list-group-flush mt-3 opacity-50">
                        <li class="list-group-item disabled">No inquiries yet</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?php
}
