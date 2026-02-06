/**
 * Admin Tag JavaScript
 * 
 * Handles tag management functionality:
 * - Status toggle via AJAX
 * - Row actions
 * 
 * Uses APS_Utils for shared functionality.
 * 
 * @package AffiliateProductShowcase
 * @since 2.0.0
 */

(function ($) {
	'use strict';

	const { showNotice, ajax, shouldKeepRowInCurrentView, parseQueryParamsFromUrl } = window.APS_Utils;

	// Localize config
	const config = typeof apsAdminVars !== 'undefined' ? apsAdminVars : {};

	$(function () {
		// Status Toggle
		$(document).on('focus', '.aps-tag-status-select', function () {
			$(this).data('aps-prev', $(this).val());
		});

		$(document).on('change', '.aps-tag-status-select', function () {
			const $select = $(this);
			const termId = $select.data('term-id');
			const newStatus = $select.val();
			const prevStatus = $select.data('aps-prev');

			$select.prop('disabled', true);

			ajax({
				data: {
					action: 'aps_toggle_tag_status',
					nonce: config.nonce || '',
					term_id: termId,
					status: newStatus
				}
			})
				.done((response) => {
					if (!response?.success) throw response;

					$select.prop('disabled', false);
					showNotice('success', response.data?.message || config.success_text || 'Tag updated.');

					if (!shouldKeepRowInCurrentView(newStatus)) {
						$select.closest('tr').fadeOut(150, function () { $(this).remove(); });
					}
				})
				.fail((xhr) => {
					$select.prop('disabled', false);
					if (prevStatus !== undefined) $select.val(prevStatus);

					const msg = xhr.responseJSON?.data?.message || config.error_text || 'Request failed.';
					showNotice('error', msg);
				});
		});

		// Row Actions
		$(document).on('click', 'a[href*="admin-post.php?action=aps_tag_row_action"]', function (e) {
			const $link = $(this);
			const href = $link.attr('href');
			if (!href) return;

			e.preventDefault();

			const params = parseQueryParamsFromUrl(href);
			const termId = params.get('term_id');
			const rowAction = params.get('row_action');

			if (!termId || !rowAction) {
				window.location.href = href;
				return;
			}

			$link.addClass('disabled');

			ajax({
				data: {
					action: 'aps_tag_row_action',
					nonce: config.row_action_nonce || '',
					term_id: termId,
					row_action: rowAction
				}
			})
				.done((response) => {
					$link.removeClass('disabled');
					if (!response?.success) throw response;

					const data = response.data || {};
					showNotice('success', data.message || 'Done.');

					let $row = $(`#tag-${termId}`);
					if (!$row.length) $row = $link.closest('tr');

					if (rowAction === 'delete_permanently') {
						$row.fadeOut(150, function () { $(this).remove(); });
						return;
					}

					if (data.status) {
						$row.find('.aps-tag-status-select').val(data.status);
						if (!shouldKeepRowInCurrentView(data.status)) {
							$row.fadeOut(150, function () { $(this).remove(); });
						}
					}
				})
				.fail((xhr) => {
					$link.removeClass('disabled');
					const msg = xhr.responseJSON?.data?.message || config.error_text || 'Request failed.';
					showNotice('error', msg);
				});
		});
	});

})(jQuery);
