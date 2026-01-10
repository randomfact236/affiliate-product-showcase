<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Plugin;

use AffiliateProductShowcase\Core\Plugin as CorePlugin;

/**
 * Deactivation handler for the plugin.
 */
final class Deactivator
{
    /**
     * Deactivation entry point called by WordPress.
     */
    public static function deactivate(): void
    {
        CorePlugin::deactivate();
    }
}
