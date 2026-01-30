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
	var prefix = ( typeof aps_admin_vars !== 'undefined' && aps_admin_vars && aps_admin_vars.notice_prefix )
		? aps_admin_vars.notice_prefix
		: 'aps-category-notice';
	var cls = prefix + '-' + type;

	$( '.' + prefix ).remove();
	var html = '<div class="notice notice-' + type + ' is-dismissible ' + prefix + ' ' + cls + '"><p>' + message + '</p></div>';
	$( '.wrap h1' ).first().after( html );

	setTimeout( function() {
		$( '.' + prefix ).fadeOut( 200 );
	}, 3000 );
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
		$submit.prepend('<a class="button button-secondary aps-cancel-term-edit" href="' + cancelUrl + '">' + cancelText + '</a>');
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

		$.ajax({
			url: ajaxUrl,
			type: 'POST',
			data: {
				action: 'aps_category_row_action',
				nonce: aps_admin_vars.row_action_nonce,
				term_id: termId,
				do: doAction
			},
			success: function(response) {
				$link.text(previousText).removeClass('disabled');
				if (response && response.success) {
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
				} else {
					var message = (response && response.data && response.data.message) ? response.data.message : (aps_admin_vars.error_text || 'Request failed.');
					apsShowNotice('error', message);
				}
			},
			error: function() {
				$link.text(previousText).removeClass('disabled');
				apsShowNotice('error', aps_admin_vars.error_text || 'Request failed.');
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
		
		// Update status via AJAX
		$.ajax({
			url: ajaxUrl,
			type: 'POST',
			data: {
				action: 'aps_toggle_category_status',
				nonce: aps_admin_vars.nonce,
				term_id: termId,
				status: newStatus
			},
			beforeSend: function() {
				$this.prop('disabled', true);
			},
			success: function(response) {
				if (response.success) {
					$this.prop('disabled', false);
					// Update original status to new value
					$this.data('original-status', newStatus);
					apsShowNotice( 'success', ( response.data && response.data.message ) ? response.data.message : ( aps_admin_vars.success_text || 'Updated.' ) );
				} else if (response.data && response.data.message) {
					$this.prop('disabled', false);
					// Revert to original status
					$this.val(originalStatus);
					apsShowNotice( 'error', response.data.message );
				} else {
					$this.prop('disabled', false);
					$this.val(originalStatus);
					apsShowNotice( 'error', aps_admin_vars.error_text || 'Request failed.' );
				}
			},
			error: function(xhr, status, error) {
				$this.prop('disabled', false);
				// Revert to original status
				$this.val(originalStatus);
				apsShowNotice( 'error', aps_admin_vars.error_text || 'Request failed.' );
			}
		});
	});
});