<?php
declare(strict_types=1);

/**
 * Reusable empty state block.
 */
function render_empty_state(string $icon, string $title, string $message, ?string $buttonLabel = null, ?string $buttonUrl = null): void
{
    ?>
    <div class="empty-state text-center py-5 px-3">
        <div class="empty-state-icon mb-3">
            <i class="bi <?= e($icon) ?>"></i>
        </div>
        <h3 class="h5 fw-bold mb-2"><?= e($title) ?></h3>
        <p class="text-muted mb-4 mx-auto empty-state-message"><?= e($message) ?></p>
        <?php if ($buttonLabel !== null && $buttonUrl !== null): ?>
            <a href="<?= e(url($buttonUrl)) ?>" class="btn btn-marketplace"><?= e($buttonLabel) ?></a>
        <?php endif; ?>
    </div>
    <?php
}
