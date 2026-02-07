/**
 * Admin Category JavaScript
 * 
 * Handles category management functionality:
 * - Status toggle via AJAX
 * - Cancel button on edit form
 * - Category checkboxes positioning
 * 
 * Uses APS_Utils for shared functionality.
 * 
 * @package AffiliateProductShowcase
 * @since 1.2.0
 */

(function ($) {
	'use strict';

	/**
	 * @typedef {Object} CategoryConfig
	 * @property {string} nonce - Security nonce for AJAX requests
	 * @property {string} row_action_nonce - Nonce for row actions
	 * @property {string} cancel_url - URL for cancel action
	 * @property {string} cancel_text - Cancel button text
	 * @property {string} success_text - Success message text
	 * @property {string} error_text - Error message text
	 */

	const { showNotice, ajax, getAjaxUrl, getCurrentStatusView } = window.APS_Utils;

	/** @type {CategoryConfig} */
	const config = typeof apsAdminVars !== 'undefined' ? apsAdminVars : {};

	/**
	 * Move category checkboxes after slug field
	 */
	function apsMoveCategoryCheckboxes() {
		const $wrapper = $('.aps-category-checkboxes-wrapper');
		const $slugParent = $('input[name="slug"]').parent();

		if ($wrapper.length && $slugParent.length) {
			$wrapper.insertAfter($slugParent);
			$wrapper.removeAttr('hidden');
		}
	}

	/**
	 * Add cancel button to term edit screen
	 */
	function apsAddCancelButton() {
		const $submit = $('#edittag .submit');
		if ($submit.length && !$submit.find('.aps-cancel-term-edit').length) {
			const cancelUrl = config.cancel_url || '';
			const cancelText = config.cancel_text || 'Cancel';

			$('<a></a>')
				.addClass('button button-secondary aps-cancel-term-edit')
				.attr('href', cancelUrl)
				.text(cancelText)
				.prependTo($submit);
		}
	}

	/**
	 * Set the status dropdown value and store original status
	 */
	function apsSetRowStatusUI($row, status) {
		const $select = $row.find('.aps-category-status-select');
		if ($select.length) {
			$select.val(status);
			$select.data('original-status', status);
		}
	}

	$(function () {
		// Initialize UI
		apsMoveCategoryCheckboxes();
		if ($('#edittag').length) apsAddCancelButton();

		// Track previous status
		$(document).on('focus', '.aps-category-status-select', function () {
			$(this).data('original-status', $(this).val());
		});

		// Handle status dropdown changes
		$(document).on('change', '.aps-category-status-select', function () {
			const $this = $(this);
			const termId = $this.data('term-id');
			const newStatus = $this.val();
			let originalStatus = $this.data('original-status');

			if (!originalStatus) {
				originalStatus = $this.find('option[selected]').val() || newStatus;
			}

			if (typeof apsAdminVars === 'undefined') {
				alert('Error: missing config. Please refresh the page.');
				return;
			}

			$this.prop('disabled', true);

			ajax({
				data: {
					action: 'aps_toggle_aps_category_status',
					nonce: config.nonce,
					term_id: termId,
					status: newStatus
				}
			})
				.done((response) => {
					$this.prop('disabled', false);
					$this.data('original-status', newStatus);
					showNotice('success', response.data?.message || config.success_text || 'Updated.');
				})
				.fail(() => {
					$this.prop('disabled', false);
					$this.val(originalStatus);
					showNotice('error', config.error_text || 'Request failed.');
				});
		});

		// Handle Row Actions
		$(document).on('click', 'a[href*="admin-post.php?action=aps_category_row_action"]', function (e) {
			if (typeof apsAdminVars === 'undefined') return;
			if (!getAjaxUrl()) return;

			e.preventDefault();

			const $link = $(this);
			const href = $link.attr('href');
			let url;

			try {
				url = new URL(href, window.location.origin);
			} catch (err) {
				window.location.href = href;
				return;
			}

			const termId = url.searchParams.get('term_id');
			const doAction = url.searchParams.get('do');

			if (!termId || !doAction) {
				window.location.href = href;
				return;
			}

			const $row = $link.closest('tr');
			const previousText = $link.text();
			$link.text(previousText + '...').addClass('disabled');

			ajax({
				data: {
					action: 'aps_aps_category_row_action',
					nonce: config.row_action_nonce,
					term_id: termId,
					do: doAction
				}
			})
				.done((response) => {
					$link.text(previousText).removeClass('disabled');

					const currentView = getCurrentStatusView();
					const newStatus = response.data?.status || '';
					const deleted = response.data?.deleted;

					if (deleted) {
						$row.fadeOut(150, function () { $(this).remove(); });
					} else if (newStatus) {
						apsSetRowStatusUI($row, newStatus);

						if (currentView !== 'all' && currentView !== newStatus) {
							$row.fadeOut(150, function () { $(this).remove(); });
						}
						if (currentView === 'all' && newStatus === 'trashed') {
							$row.fadeOut(150, function () { $(this).remove(); });
						}
					}

					showNotice('success', response.data?.message || 'Done.');
				})
				.fail(() => {
					$link.text(previousText).removeClass('disabled');
					showNotice('error', 'Request failed.');
				});
		});
	});

})(jQuery);
