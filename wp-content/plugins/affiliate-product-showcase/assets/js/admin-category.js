/**
 * Admin Category JavaScript
 *
 * Handles category management functionality:
 * - Status toggle via AJAX
 * - Cancel button on edit form
 * - Bulk action handling
 *
 * @package AffiliateProductShowcase
 * @since 1.2.0
 */

/* global ajaxurl, aps_admin_vars */

function apsGetAjaxUrl() {
	if ( typeof aps_admin_vars !== 'undefined' && aps_admin_vars && aps_admin_vars.ajax_url ) {
		return aps_admin_vars.ajax_url;
	}
	if ( typeof ajaxurl !== 'undefined' ) {
		return ajaxurl;
	}
	return '';
}

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


jQuery(document).ready(function($) {
	function apsGetCurrentViewStatus() {
		var params = new URLSearchParams(window.location.search);
		return params.get('status') || 'all';
	}

	function apsFindRowFromLink($link) {
		return $link.closest('tr');
	}

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