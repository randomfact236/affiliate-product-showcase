/**
 * Admin Category JavaScript
 *
 * Handles category management functionality:
 * - Status toggle via AJAX
 * - Cancel button on edit form
 * - Bulk action handling
 * - Category checkboxes positioning
 * - Cancel button injection
 *
 * @package AffiliateProductShowcase
 * @since 1.2.0
 */

/* global ajaxurl, aps_admin_vars */

/**
 * Get AJAX URL for category operations
 *
 * Checks for aps_admin_vars.ajax_url first, then falls back to global ajaxurl.
 *
 * @returns {string} The AJAX URL, or empty string if not found
 */
function apsGetAjaxUrl() {
	if ( typeof aps_admin_vars !== 'undefined' && aps_admin_vars && aps_admin_vars.ajax_url ) {
		return aps_admin_vars.ajax_url;
	}
	if ( typeof ajaxurl !== 'undefined' ) {
		return ajaxurl;
	}
	return '';
}

/**
 * Show admin notice message
 *
 * Displays a dismissible notice above the page heading.
 * Auto-dismisses after 3 seconds.
 *
 * @param {string} type - Notice type ('success', 'error', 'warning', 'info')
 * @param {string} message - Notice message content
 */
function apsShowNotice( type, message ) {
	var $ = jQuery;
	var allowedTypes = [ 'success', 'error', 'warning', 'info' ];
	if ( allowedTypes.indexOf( type ) === -1 ) {
		type = 'info';
	}
	var prefix = ( typeof aps_admin_vars !== 'undefined' && aps_admin_vars && aps_admin_vars.notice_prefix )
		? aps_admin_vars.notice_prefix
		: 'aps-category-notice';
	var cls = prefix + '-' + type;

	$( '.' + prefix ).remove();
	var $notice = $( '<div></div>' )
		.addClass( 'notice' )
		.addClass( 'notice-' + type )
		.addClass( 'is-dismissible' )
		.addClass( prefix )
		.addClass( cls );
	$( '<p></p>' ).text( message ).appendTo( $notice );
	$( '.wrap h1' ).first().after( $notice );

	setTimeout( function() {
		$( '.' + prefix ).fadeOut( 200 );
	}, 3000 );
}

/**
 * Perform AJAX request with standardized error handling
 *
 * Provides consistent error handling and notice display for all AJAX requests.
 * Automatically handles success/error responses and displays notices.
 *
 * @param {Object} options - AJAX options
 * @param {Object} options.data - Data to send (required)
 * @param {Function} options.success - Success callback (receives response)
 * @param {Function} options.error - Error callback (receives xhr, status, error)
 * @param {Function} options.beforeSend - Before send callback
 * @param {boolean} options.showNoticeOnError - Show error notice (default: true)
 * @param {string} options.type - HTTP method (default: 'POST')
 * @param {string} options.url - AJAX URL (default: apsGetAjaxUrl())
 * @returns {jqXHR} jQuery AJAX object
 *
 * @example
 * ```javascript
 * apsAjaxRequest({
 *     data: {
 *         action: 'my_action',
 *         nonce: my_nonce,
 *         id: 123
 *     },
 *     success: function( response ) {
 *         console.log( 'Success:', response.data );
 *     },
 *     beforeSend: function() {
 *         // Disable button, etc.
 *     }
 * });
 * ```
 */
function apsAjaxRequest( options ) {
	var $ = jQuery;
	var defaults = {
		type: 'POST',
		url: apsGetAjaxUrl(),
		beforeSend: null,
		success: null,
		error: null,
		showNoticeOnError: true
	};

	var settings = $.extend( {}, defaults, options );

	return $.ajax({
		url: settings.url,
		type: settings.type,
		data: settings.data,
		beforeSend: settings.beforeSend,
		success: function( response ) {
			if ( response && response.success ) {
				if ( typeof settings.success === 'function' ) {
					settings.success( response );
				}
			} else {
				var message = ( response && response.data && response.data.message )
					? response.data.message
					: ( typeof aps_admin_vars !== 'undefined' && aps_admin_vars.error_text )
						? aps_admin_vars.error_text
						: 'Request failed.';

				if ( settings.showNoticeOnError ) {
					apsShowNotice( 'error', message );
				}

				if ( typeof settings.error === 'function' ) {
					settings.error( response, message, 'validation' );
				}
			}
		},
		error: function( xhr, status, error ) {
			var message = ( typeof aps_admin_vars !== 'undefined' && aps_admin_vars.error_text )
				? aps_admin_vars.error_text
				: 'Request failed.';

			if ( settings.showNoticeOnError ) {
				apsShowNotice( 'error', message );
			}

			if ( typeof settings.error === 'function' ) {
				settings.error( xhr, status, error );
			}
		}
	});
}


/**
 * Move category checkboxes after slug field
 *
 * Repositions the category checkboxes wrapper to appear after the slug field
 * in the category add/edit form.
 */
function apsMoveCategoryCheckboxes() {
	var $ = jQuery;
	$('.aps-category-checkboxes-wrapper').insertAfter($('input[name="slug"]').parent());
	$('.aps-category-checkboxes-wrapper').removeAttr('hidden');
}

/**
 * Add cancel button to term edit screen
 *
 * Injects a cancel button into the term edit screen submit area.
 * Uses localized data for URL and text.
 */
function apsAddCancelButton() {
	var $ = jQuery;
	var $submit = $('#edittag .submit');
	if ($submit.length && !$submit.find('.aps-cancel-term-edit').length) {
		var cancelUrl = (typeof aps_admin_vars !== 'undefined' && aps_admin_vars.cancel_url)
			? aps_admin_vars.cancel_url
			: '';
		var cancelText = (typeof aps_admin_vars !== 'undefined' && aps_admin_vars.cancel_text)
			? aps_admin_vars.cancel_text
			: 'Cancel';
		$( '<a></a>' )
			.addClass( 'button button-secondary aps-cancel-term-edit' )
			.attr( 'href', cancelUrl )
			.text( cancelText )
			.prependTo( $submit );
	}
}

jQuery(document).ready(function($) {
	// Initialize category-specific functionality
	if ($('.aps-category-checkboxes-wrapper').length) {
		apsMoveCategoryCheckboxes();
	}
	
	// Initialize cancel button on term edit screen
	if ($('#edittag').length) {
		apsAddCancelButton();
	}

	/**
	 * Get current view status from URL
	 *
	 * @returns {string} Current status filter ('all', 'published', 'draft', 'trashed')
	 */
	function apsGetCurrentViewStatus() {
		var params = new URLSearchParams(window.location.search);
		return params.get('status') || 'all';
	}

	/**
	 * Find the table row containing a given link
	 *
	 * @param {jQuery} $link - The link element
	 * @returns {jQuery} The closest table row element
	 */
	function apsFindRowFromLink($link) {
		return $link.closest('tr');
	}

	/**
	 * Set the status dropdown value and store original status
	 *
	 * @param {jQuery} $row - The table row element
	 * @param {string} status - The new status value
	 */
	function apsSetRowStatusUI($row, status) {
		var $select = $row.find('.aps-category-status-select');
		if ($select.length) {
			$select.val(status);
			$select.data('original-status', status);
		}
	}

	// Handle row actions (Trash/Restore/Delete Permanently/Move to Draft) via AJAX for speed.
	$(document).on('click', 'a[href*="admin-post.php?action=aps_category_row_action"]', function(e) {
		if (typeof aps_admin_vars === 'undefined') {
			return;
		}
		var ajaxUrl = apsGetAjaxUrl();
		if (!ajaxUrl) {
			return;
		}

		e.preventDefault();

		var $link = $(this);
		var href = $link.attr('href');
		var url;
		try {
			url = new URL(href, window.location.origin);
		} catch (err) {
			window.location.href = href;
			return;
		}

		var termId = url.searchParams.get('term_id');
		var doAction = url.searchParams.get('do');
		if (!termId || !doAction) {
			window.location.href = href;
			return;
		}

		var $row = apsFindRowFromLink($link);
		var previousText = $link.text();
		$link.text(previousText + '...').addClass('disabled');

		apsAjaxRequest({
			data: {
				action: 'aps_aps_category_row_action',
				nonce: aps_admin_vars.row_action_nonce,
				term_id: termId,
				do: doAction
			},
			beforeSend: function() {
				$link.text(previousText + '...').addClass('disabled');
			},
			success: function(response) {
				$link.text(previousText).removeClass('disabled');
				var currentView = apsGetCurrentViewStatus();
				var newStatus = response.data && response.data.status ? response.data.status : '';
				var deleted = response.data && response.data.deleted;

				if (deleted) {
					$row.fadeOut(150, function() { $(this).remove(); });
				} else if (newStatus) {
					apsSetRowStatusUI($row, newStatus);
					// If we're on a filtered view and the row no longer belongs, remove it.
					if (currentView !== 'all' && currentView !== newStatus) {
						$row.fadeOut(150, function() { $(this).remove(); });
					}
					// If moved to trash, remove it from default view as well.
					if (currentView === 'all' && newStatus === 'trashed') {
						$row.fadeOut(150, function() { $(this).remove(); });
					}
				}

				apsShowNotice('success', (response.data && response.data.message) ? response.data.message : 'Done.');
			},
			error: function() {
				$link.text(previousText).removeClass('disabled');
			}
		});
	});

	// Track the previous value so we can revert on failure.
	$(document).on('focus', '.aps-category-status-select', function() {
		$(this).data('original-status', $(this).val());
	});

	// Handle status dropdown changes in table
	$(document).on('change', '.aps-category-status-select', function() {
		var $this = $(this);
		var termId = $this.data('term-id');
		var newStatus = $this.val();
		var originalStatus = $this.data('original-status');
		if ( ! originalStatus ) {
			originalStatus = $this.find('option[selected]').val() || newStatus;
		}
		
		// Check if aps_admin_vars is defined
		if (typeof aps_admin_vars === 'undefined') {
			alert('Error: missing config. Please refresh the page.');
			return;
		}

		var ajaxUrl = apsGetAjaxUrl();
		if ( ! ajaxUrl ) {
			apsShowNotice( 'error', aps_admin_vars.error_text || 'Request failed.' );
			$this.val( originalStatus );
			return;
		}
		
		// Update status via AJAX using the reusable wrapper
		apsAjaxRequest({
			data: {
				action: 'aps_toggle_aps_category_status',
				nonce: aps_admin_vars.nonce,
				term_id: termId,
				status: newStatus
			},
			beforeSend: function() {
				$this.prop('disabled', true);
			},
			success: function(response) {
				$this.prop('disabled', false);
				// Update original status to new value
				$this.data('original-status', newStatus);
				apsShowNotice( 'success', ( response.data && response.data.message ) ? response.data.message : ( aps_admin_vars.success_text || 'Updated.' ) );
			},
			error: function() {
				$this.prop('disabled', false);
				// Revert to original status
				$this.val(originalStatus);
			}
		});
	});
});