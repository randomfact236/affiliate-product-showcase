<?php

declare(strict_types=1);

namespace AffiliateProductShowcase\Security;

use AffiliateProductShowcase\Helpers\Logger;

/**
 * Permission Manager
 *
 * Handles permission checks and capabilities management
 * for the affiliate product showcase plugin.
 *
 * @package AffiliateProductShowcase\Security
 * @since 1.0.0
 */
class PermissionManager {

    /**
     * Custom capabilities for the plugin
     *
     * @var array
     */
    private const CAPABILITIES = [
        'manage_affiliate_products' => 'Manage affiliate products',
        'edit_affiliate_products'   => 'Edit affiliate products',
        'delete_affiliate_products' => 'Delete affiliate products',
        'view_affiliate_analytics'  => 'View affiliate analytics',
        'manage_affiliate_settings' => 'Manage affiliate settings',
    ];

    /**
     * Check if current user has a specific capability
     *
     * @param string $capability Capability to check
     * @param int|null $user_id Optional user ID (defaults to current user)
     * @return bool
     */
    public function can( string $capability, ?int $user_id = null ): bool {
        // Check if it's a custom capability
        if ( isset( self::CAPABILITIES[ $capability ] ) ) {
            return user_can( $user_id ?? get_current_user_id(), $capability );
        }

        // Check standard WordPress capability
        return user_can( $user_id ?? get_current_user_id(), $capability );
    }

    /**
     * Check if current user can edit a specific product
     *
     * @param int $product_id Product ID
     * @param int|null $user_id Optional user ID
     * @return bool
     */
    public function canEditProduct( int $product_id, ?int $user_id = null ): bool {
        // Admin can edit everything
        if ( $this->can( 'manage_options', $user_id ) ) {
            return true;
        }

        // Check custom capability
        if ( ! $this->can( 'edit_affiliate_products', $user_id ) ) {
            return false;
        }

        // Check if user is the author (if post author data exists)
        $post = get_post( $product_id );
        if ( $post && $post->post_author === ( $user_id ?? get_current_user_id() ) ) {
            return true;
        }

        return false;
    }

    /**
     * Check if current user can delete a specific product
     *
     * @param int $product_id Product ID
     * @param int|null $user_id Optional user ID
     * @return bool
     */
    public function canDeleteProduct( int $product_id, ?int $user_id = null ): bool {
        // Admin can delete everything
        if ( $this->can( 'manage_options', $user_id ) ) {
            return true;
        }

        // Check custom capability
        if ( ! $this->can( 'delete_affiliate_products', $user_id ) ) {
            return false;
        }

        return $this->canEditProduct( $product_id, $user_id );
    }

    /**
     * Check if current user can view analytics
     *
     * @param int|null $user_id Optional user ID
     * @return bool
     */
    public function canViewAnalytics( ?int $user_id = null ): bool {
        return $this->can( 'view_affiliate_analytics', $user_id );
    }

    /**
     * Check if current user can manage settings
     *
     * @param int|null $user_id Optional user ID
     * @return bool
     */
    public function canManageSettings( ?int $user_id = null ): bool {
        return $this->can( 'manage_affiliate_settings', $user_id );
    }

    /**
     * Register custom capabilities
     *
     * @param string $role Role to add capabilities to (defaults to administrator)
     * @return void
     */
    public function registerCapabilities( string $role = 'administrator' ): void {
        $role_obj = get_role( $role );

        if ( ! $role_obj ) {
            Logger::error( "Role not found: {$role}" );
            return;
        }

        foreach ( self::CAPABILITIES as $cap => $description ) {
            $role_obj->add_cap( $cap );
            Logger::info( "Added capability {$cap} to role {$role}" );
        }
    }

    /**
     * Remove custom capabilities
     *
     * @param string $role Role to remove capabilities from (defaults to administrator)
     * @return void
     */
    public function removeCapabilities( string $role = 'administrator' ): void {
        $role_obj = get_role( $role );

        if ( ! $role_obj ) {
            return;
        }

        foreach ( self::CAPABILITIES as $cap => $description ) {
            $role_obj->remove_cap( $cap );
            Logger::info( "Removed capability {$cap} from role {$role}" );
        }
    }

    /**
     * Get all custom capabilities
     *
     * @return array
     */
    public static function getAllCapabilities(): array {
        return self::CAPABILITIES;
    }

    /**
     * Verify nonce for AJAX requests
     *
     * @param string $nonce Nonce value
     * @param string $action Nonce action name
     * @return bool
     */
    public function verifyNonce( string $nonce, string $action ): bool {
        return wp_verify_nonce( $nonce, $action ) !== false;
    }

    /**
     * Check if request is an AJAX request
     *
     * @return bool
     */
    public function isAjaxRequest(): bool {
        return wp_doing_ajax();
    }

    /**
     * Check if request is a REST API request
     *
     * @return bool
     */
    public function isRestRequest(): bool {
        return defined( 'REST_REQUEST' ) && REST_REQUEST;
    }

    /**
     * Get current user roles
     *
     * @param int|null $user_id Optional user ID
     * @return array
     */
    public function getUserRoles( ?int $user_id = null ): array {
        $user_id = $user_id ?? get_current_user_id();
        $user    = get_userdata( $user_id );

        if ( ! $user ) {
            return [];
        }

        return $user->roles ?? [];
    }

    /**
     * Check if current user has any of the specified roles
     *
     * @param array $roles Array of role names
     * @param int|null $user_id Optional user ID
     * @return bool
     */
    public function hasRole( array $roles, ?int $user_id = null ): bool {
        $user_roles = $this->getUserRoles( $user_id );
        return ! empty( array_intersect( $roles, $user_roles ) );
    }
}
