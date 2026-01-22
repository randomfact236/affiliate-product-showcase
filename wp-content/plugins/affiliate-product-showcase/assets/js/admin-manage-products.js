/**
 * Manage Products Admin Page JavaScript
 *
 * Handles table interactions, filters, bulk actions, and pagination.
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
        apsInitTable();
        apsInitFilters();
        apsInitBulkActions();
        apsInitPagination();
    });

    /**
     * Initialize table functionality
     */
    function apsInitTable() {
        // Select all checkbox
        $( '.aps-select-all' ).on( 'change', function() {
            const isChecked = $( this ).prop( 'checked' );
            $( '.aps-product-checkbox' ).prop( 'checked', isChecked );
        });

        // Row hover effect
        $( '.aps-product-row' ).on( 'mouseenter', function() {
            $( this ).addClass( 'row-hover' );
        } ).on( 'mouseleave', function() {
            $( this ).removeClass( 'row-hover' );
        });

        // Category tag remove
        $( '.aps-category-tag-remove' ).on( 'click', function( e ) {
            e.preventDefault();
            e.stopPropagation();
            const category = $( this ).data( 'category' );
            apsRemoveFilter( 'category', category );
        } );
    }

    /**
     * Initialize filters
     */
    function apsInitFilters() {
        // Category dropdown
        $( '#aps-category-select' ).on( 'change', function() {
            const category = $( this ).val();
            if ( category ) {
                window.location.href = apsUpdateUrlParam( 'category', category );
            }
        } );

        // Sort dropdown
        $( '#aps-sort-select' ).on( 'change', function() {
            const sort = $( this ).val();
            if ( sort ) {
                window.location.href = apsUpdateUrlParam( 'sort', sort );
            }
        } );

        // Show Featured checkbox
        $( '#aps-featured-checkbox' ).on( 'change', function() {
            const featured = $( this ).prop( 'checked' ) ? '1' : '';
            window.location.href = apsUpdateUrlParam( 'featured', featured );
        } );

        // Search input with debouncing
        let searchTimeout;
        $( '#aps-search-input' ).on( 'input', function() {
            const search = $( this ).val();
            clearTimeout( searchTimeout );
            
            searchTimeout = setTimeout( function() {
                if ( search && search.length >= 2 ) {
                    window.location.href = apsUpdateUrlParam( 's', search );
                }
            }, 300 );
        } );

        // Clear Filters button
        $( '.aps-filter-clear' ).on( 'click', function() {
            apsClearAllFilters();
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
        $( '.aps-per-page-select' ).on( 'change', function() {
            const perPage = $( this ).val();
            window.location.href = apsUpdateUrlParam( 'per_page', perPage );
        } );

        // Previous page button
        $( '.aps-prev-page' ).on( 'click', function( e ) {
            e.preventDefault();
            const currentPage = parseInt( $( this ).data( 'page' ) );
            window.location.href = apsUpdateUrlParam( 'paged', currentPage - 1 );
        } );

        // Next page button
        $( '.aps-next-page' ).on( 'click', function( e ) {
            e.preventDefault();
            const currentPage = parseInt( $( this ).data( 'page' ) );
            window.location.href = apsUpdateUrlParam( 'paged', currentPage + 1 );
        } );

        // Page number links
        $( '.aps-page-link' ).on( 'click', function( e ) {
            e.preventDefault();
            const page = parseInt( $( this ).data( 'page' ) );
            window.location.href = apsUpdateUrlParam( 'paged', page );
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
            apsShowNotice( 'error', 'Please select at least one product.' );
            return;
        }

        const action = $( '#aps-action-select' ).val();
        if ( ! action ) {
            apsShowNotice( 'error', 'Please select an action.' );
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
                    apsShowNotice( 'error', response.data.message || 'Action failed.' );
                }
            },
            error: function( xhr, status, error ) {
                apsHideLoading();
                apsShowNotice( 'error', 'Request failed: ' + error );
            }
        } );
    }

    /**
     * Update URL parameter
     */
    function apsUpdateUrlParam( key, value ) {
        const url = new URL( window.location.href );
        if ( value ) {
            url.searchParams.set( key, value );
        } else {
            url.searchParams.delete( key );
        }
        return url.toString();
    }

    /**
     * Remove single filter
     */
    function apsRemoveFilter( type, value ) {
        const url = new URL( window.location.href );
        url.searchParams.delete( type );
        
        // Rebuild other filters
        const params = url.searchParams;
        let newParams = {};
        
        params.forEach( function( key, value ) {
            if ( key === 's' ) {
                newParams[ key ] = value;
            } else if ( key === 'category' && type !== 'category' ) {
                newParams[ key ] = value;
            } else if ( key === 'tag' && type !== 'tag' ) {
                newParams[ key ] = value;
            } else if ( key === 'ribbon' && type !== 'ribbon' ) {
                newParams[ key ] = value;
            } else if ( key === 'featured' && type !== 'featured' ) {
                newParams[ key ] = value;
            }
        } );
        
        // Update URL
        Object.keys( newParams ).forEach( function( key ) {
            if ( newParams[ key ] ) {
                url.searchParams.set( key, newParams[ key ] );
            }
        } );
        
        window.location.href = url.toString();
    }

    /**
     * Clear all filters
     */
    function apsClearAllFilters() {
        const url = new URL( window.location.href );
        url.searchParams.delete( 's' );
        url.searchParams.delete( 'category' );
        url.searchParams.delete( 'tag' );
        url.searchParams.delete( 'ribbon' );
        url.searchParams.delete( 'featured' );
        url.searchParams.delete( 'sort' );
        url.searchParams.set( 'paged', '1' );
        window.location.href = url.toString();
    }

    /**
     * Update bulk actions button visibility
     */
    function apsUpdateBulkActionsVisibility() {
        const selectedCount = $( '.aps-product-checkbox:checked' ).length;
        const action = $( '#aps-action-select' ).val();
        
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
        const notice = $( '<div class="notice ' + noticeClass + ' is-dismissible"><p>' + message + '</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>' );
        
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
            const overlay = $( '<div class="aps-loading-overlay"><div class="aps-loading-spinner"></div><p>Processing...</p></div>' );
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
    $( document ).on( 'click', '.aps-quick-edit', function( e ) {
        e.preventDefault();
        const productId = $( this ).data( 'product-id' );
        window.location.href = apsData.restUrl + 'products/' + productId;
    } );

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
                        apsShowNotice( 'success', 'Product deleted successfully.' );
                        // Remove row
                        $( '.aps-product-row[data-product-id="' + productId + '"]' ).fadeOut( function() {
                            $( this ).remove();
                        } );
                    } else {
                        apsShowNotice( 'error', response.message || 'Failed to delete product.' );
                    }
                },
                error: function( xhr, status, error ) {
                    apsHideLoading();
                    apsShowNotice( 'error', 'Request failed: ' + error );
                }
            } );
        }
    } );

    // Make functions available globally
    window.apsInitTable = apsInitTable;
    window.apsInitFilters = apsInitFilters;
    window.apsInitBulkActions = apsInitBulkActions;
    window.apsInitPagination = apsInitPagination;
    window.apsUpdateUrlParam = apsUpdateUrlParam;
    window.apsRemoveFilter = apsRemoveFilter;
    window.apsClearAllFilters = apsClearAllFilters;
    window.apsApplyBulkAction = apsApplyBulkAction;

} )( jQuery );
