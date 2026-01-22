/**
 * Products Admin Enhancer JavaScript
 *
 * Enhances the default WordPress "All Products" page with:
 * - Advanced filters
 * - Bulk actions
 * - Custom table columns
 * - Pagination improvements
 * - Quick actions
 *
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 */

( function( $ ) {
    'use strict';

    /**
     * Initialize on document ready
     */
    $( document ).ready( function() {
        apsInitFilters();
        apsInitBulkActions();
        apsInitQuickActions();
        apsInitPagination();
    });

    /**
     * Initialize advanced filters
     */
    function apsInitFilters() {
        // Category dropdown with URL update
        $( 'input[name="aps_category"]' ).on( 'change', function() {
            const category = $( this ).val();
            const url = new URL( window.location.href );
            if ( category ) {
                url.searchParams.set( 'aps_category', category );
            } else {
                url.searchParams.delete( 'aps_category' );
            }
            window.location.href = url.toString();
        } );

        // Sort dropdown with URL update
        $( 'select[name="aps_sort"]' ).on( 'change', function() {
            const sort = $( this ).val();
            const url = new URL( window.location.href );
            if ( sort ) {
                url.searchParams.set( 'aps_sort', sort );
            } else {
                url.searchParams.delete( 'aps_sort' );
            }
            window.location.href = url.toString();
        } );

        // Show Featured checkbox with URL update
        $( 'input[name="aps_featured"]' ).on( 'change', function() {
            const featured = $( this ).prop( 'checked' ) ? '1' : '';
            const url = new URL( window.location.href );
            if ( featured ) {
                url.searchParams.set( 'aps_featured', featured );
            } else {
                url.searchParams.delete( 'aps_featured' );
            }
            window.location.href = url.toString();
        } );

        // Clear Filters button
        $( '.aps-filter-clear' ).on( 'click', function() {
            const url = new URL( window.location.href );
            url.searchParams.delete( 'aps_category' );
            url.searchParams.delete( 'aps_sort' );
            url.searchParams.delete( 'aps_featured' );
            window.location.href = url.toString();
        } );
    }

    /**
     * Initialize bulk actions
     */
    function apsInitBulkActions() {
        // Apply button
        $( '.aps-bulk-apply' ).on( 'click', function( e ) {
            e.preventDefault();
            apsApplyBulkAction();
        } );

        // Update button visibility based on selection
        $( document ).on( 'change', '.aps-product-checkbox', function() {
            apsUpdateBulkActionsVisibility();
        } );
    }

    /**
     * Initialize pagination
     */
    function apsInitPagination() {
        // Per page dropdown
        $( 'select[name="per_page"]' ).on( 'change', function() {
            const perPage = $( this ).val();
            const url = new URL( window.location.href );
            if ( perPage ) {
                url.searchParams.set( 'per_page', perPage );
            } else {
                url.searchParams.delete( 'per_page' );
            }
            window.location.href = url.toString();
        } );
    }

    /**
     * Apply bulk action to selected products
     */
    function apsApplyBulkAction() {
        const selectedIds = [];
        $( '.aps-product-checkbox:checked' ).each( function() {
            selectedIds.push( $( this ).val() );
        } );

        if ( selectedIds.length === 0 ) {
            apsShowNotice( 'error', apsData.strings.pleaseSelect );
            return;
        }

        const action = $( 'select[name="aps_action"]' ).val();
        if ( ! action ) {
            apsShowNotice( 'error', apsData.strings.selectAction );
            return;
        }

        // Show loading
        apsShowLoading();

        // Send AJAX request
        $.ajax( {
            url: apsData.ajaxurl,
            type: 'POST',
            data: {
                action: 'aps_bulk_action',
                nonce: apsData.nonce,
                action_type: action,
                product_ids: selectedIds
            },
            success: function( response ) {
                apsHideLoading();
                if ( response.success ) {
                    apsShowNotice( 'success', response.data.message );
                    // Reload page after delay
                    setTimeout( function() {
                        window.location.reload();
                    }, 1500 );
                } else {
                    apsShowNotice( 'error', response.data.message || apsData.strings.actionFailed );
                }
            },
            error: function( xhr, status, error ) {
                apsHideLoading();
                apsShowNotice( 'error', apsData.strings.requestFailed + error );
            }
        } );
    }

    /**
     * Update bulk actions button visibility
     */
    function apsUpdateBulkActionsVisibility() {
        const selectedCount = $( '.aps-product-checkbox:checked' ).length;
        const action = $( 'select[name="aps_action"]' ).val();
        
        if ( selectedCount > 0 && action ) {
            $( '.aps-bulk-apply' ).prop( 'disabled', false ).removeClass( 'disabled' );
        } else {
            $( '.aps-bulk-apply' ).prop( 'disabled', true ).addClass( 'disabled' );
        }
    }

    /**
     * Show notice
     */
    function apsShowNotice( type, message ) {
        const noticeClass = type === 'error' ? 'notice-error' : 'notice-success';
        const notice = $( '<div class="notice ' + noticeClass + ' is-dismissible"><p>' + message + '</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">' + apsData.strings.dismissNotice + '</span></button></div>' );
        
        $( '.wrap' ).first().prepend( notice );
        
        // Auto-dismiss after 5 seconds
        setTimeout( function() {
            notice.fadeOut( function() {
                $( this ).remove();
            } );
        }, 5000 );
        
        // Dismiss button handler
        notice.find( '.notice-dismiss' ).on( 'click', function() {
            notice.fadeOut( function() {
                $( this ).remove();
            } );
        } );
    }

    /**
     * Show loading overlay
     */
    function apsShowLoading() {
        if ( ! $( '.aps-loading-overlay' ).length ) {
            const overlay = $( '<div class="aps-loading-overlay"><div class="aps-loading-spinner"></div><p>' + apsData.strings.processing + '</p></div>' );
            $( 'body' ).append( overlay );
        }
    }

    /**
     * Hide loading overlay
     */
    function apsHideLoading() {
        $( '.aps-loading-overlay' ).fadeOut( function() {
            $( this ).remove();
        } );
    }

    /**
     * Quick action handlers
     */
    function apsInitQuickActions() {
        // Quick edit button
        $( document ).on( 'click', '.aps-quick-edit', function( e ) {
            e.preventDefault();
            const productId = $( this ).data( 'product-id' );
            window.location.href = apsData.restUrl + 'products/' + productId;
        } );

        // Quick delete button
        $( document ).on( 'click', '.aps-quick-delete', function( e ) {
            e.preventDefault();
            const productId = $( this ).data( 'product-id' );
            const productName = $( this ).data( 'product-name' );
            
            if ( confirm( apsData.strings.confirmDelete.replace( '%s', productName ) ) ) {
                apsShowLoading();
                
                $.ajax( {
                    url: apsData.restUrl + 'products/' + productId,
                    type: 'DELETE',
                    beforeSend: function( xhr ) {
                        xhr.setRequestHeader( 'X-WP-Nonce', apsData.restNonce );
                    },
                    success: function( response ) {
                        apsHideLoading();
                        if ( response.deleted ) {
                            apsShowNotice( 'success', apsData.strings.productDeleted );
                            // Remove row
                            $( 'tr[data-product-id="' + productId + '"]' ).fadeOut( function() {
                                $( this ).remove();
                            } );
                        } else {
                            apsShowNotice( 'error', response.message || apsData.strings.deleteFailed );
                        }
                    },
                    error: function( xhr, status, error ) {
                        apsHideLoading();
                        apsShowNotice( 'error', apsData.strings.requestFailed + error );
                    }
                } );
            }
        } );

    // Make functions available globally
    window.apsInitFilters = apsInitFilters;
    window.apsInitBulkActions = apsInitBulkActions;
    window.apsInitQuickActions = apsInitQuickActions;
    window.apsInitPagination = apsInitPagination;
    window.apsApplyBulkAction = apsApplyBulkAction;

} )( jQuery );
