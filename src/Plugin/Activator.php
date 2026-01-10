<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Plugin;

use AffiliateProductShowcase\Core\Plugin as CorePlugin;

/**
 * Activation handler for the plugin.
 */
final class Activator
{
    /**
     * Activation entry point called by WordPress.
     */
    public static function activate(): void
    {
        CorePlugin::activate();
    }
}
