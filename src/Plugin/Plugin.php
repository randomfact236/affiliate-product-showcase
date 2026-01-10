<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Plugin;

use AffiliateProductShowcase\Core\Plugin as CorePlugin;

/**
 * Backwards-compatible plugin facade expected by the plugin bootstrap file.
 *
 * Provides the `instance()` and `init()` API the loader references while
 * delegating implementation to `AffiliateProductShowcase\Core\Plugin`.
 */
final class Plugin
{
    /**
     * Return the core plugin instance.
     *
     * Called `instance()` to match existing bootstrap expectations.
     *
     * @return CorePlugin
     */
    public static function instance(): CorePlugin
    {
        return CorePlugin::get_instance();
    }

    /**
     * Initialize plugin services. Delegates to core instance.
     *
     * Kept as a thin adapter for compatibility with older bootstrap code.
     */
    public function init(): void
    {
        $core = self::instance();
        if (method_exists($core, 'register_hooks')) {
            $core->register_hooks();
        }
    }
}
