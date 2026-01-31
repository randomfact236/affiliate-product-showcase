(function ($) {
	'use strict';

	function apsGetAjaxUrl() {
		if (typeof aps_admin_vars !== 'undefined' && aps_admin_vars && aps_admin_vars.ajax_url) {
			return aps_admin_vars.ajax_url;
		}
		if (typeof ajaxurl !== 'undefined' && ajaxurl) {
			return ajaxurl;
		}
		return '/wp-admin/admin-ajax.php';
	}

	function apsShowNotice(type, message) {
		var $wrap = $('.wrap');
		var $target = $wrap.find('h1').first();
		if (!$target.length) {
			$target = $wrap;
		}

		var cls = type === 'error' ? 'notice-error' : 'notice-success';
		var $notice = $(
			'<div class="notice ' + cls + ' is-dismissible aps-inline-notice"><p></p></div>'
		);
		$notice.find('p').text(message);
		$target.after($notice);

		setTimeout(function () {
			$notice.fadeOut(250, function () {
				$(this).remove();
			});
		}, 3500);
	}

	function getCurrentStatusView() {
		var params = new URLSearchParams(window.location.search);
		return params.get('status') || 'all';
	}

	function shouldKeepRowInCurrentView(newStatus) {
		var current = getCurrentStatusView();
		if (current === 'all') {
			return newStatus !== 'trashed';
		}
		return current === newStatus;
	}

	function parseQueryParamsFromUrl(url) {
		try {
			var u = new URL(url, window.location.origin);
			return u.searchParams;
		} catch (e) {
			return new URLSearchParams('');
		}
	}

	$(function () {
		// Track previous status value so we can revert on error.
		$(document).on('focus', '.aps-tag-status-select', function () {
			$(this).data('aps-prev', $(this).val());
		});

		// Inline status changes.
		$(document).on('change', '.aps-tag-status-select', function () {
			var $select = $(this);
			var termId = $select.data('term-id');
			var newStatus = $select.val();
			var prevStatus = $select.data('aps-prev');

			$select.prop('disabled', true);

			$.ajax({
				url: apsGetAjaxUrl(),
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'aps_toggle_tag_status',
					nonce: aps_admin_vars && aps_admin_vars.nonce ? aps_admin_vars.nonce : '',
					term_id: termId,
					status: newStatus
				}
			})
				.done(function (response) {
					if (!response || !response.success) {
						throw response;
					}

					$select.prop('disabled', false);
					apsShowNotice('success', (response.data && response.data.message) ? response.data.message : (aps_admin_vars.success_text || 'Tag updated.'));

					// If the row no longer belongs in this view, remove it.
					if (!shouldKeepRowInCurrentView(newStatus)) {
						$select.closest('tr').fadeOut(150, function () { $(this).remove(); });
					}
				})
				.fail(function (xhr) {
					$select.prop('disabled', false);
					if (typeof prevStatus !== 'undefined') {
						$select.val(prevStatus);
					}
					var msg = (xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message)
						? xhr.responseJSON.data.message
						: (aps_admin_vars.error_text || 'Request failed.');
					apsShowNotice('error', msg);
				});
		});

		// Row actions (AJAX): Draft / Trash / Restore / Delete Permanently.
		$(document).on('click', 'a[href*="admin-post.php?action=aps_tag_row_action"]', function (e) {
			var $link = $(this);
			var href = $link.attr('href');
			if (!href) {
				return;
			}

			e.preventDefault();

			var params = parseQueryParamsFromUrl(href);
			var termId = params.get('term_id');
			var rowAction = params.get('row_action');

			if (!termId || !rowAction) {
				window.location.href = href;
				return;
			}

			$link.addClass('disabled');

			$.ajax({
				url: apsGetAjaxUrl(),
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'aps_tag_row_action',
					nonce: aps_admin_vars && aps_admin_vars.row_action_nonce ? aps_admin_vars.row_action_nonce : '',
					term_id: termId,
					row_action: rowAction
				}
			})
				.done(function (response) {
					$link.removeClass('disabled');
					if (!response || !response.success) {
						throw response;
					}

					var data = response.data || {};
					apsShowNotice('success', data.message || 'Done.');

					var $row = $('#tag-' + termId);
					if (!$row.length) {
						$row = $link.closest('tr');
					}

					if (rowAction === 'delete_permanently') {
						$row.fadeOut(150, function () { $(this).remove(); });
						return;
					}

					// Update status select if present.
					if (data.status) {
						$row.find('.aps-tag-status-select').val(data.status);
					}

					// Remove row if it no longer matches current view.
					if (data.status && !shouldKeepRowInCurrentView(data.status)) {
						$row.fadeOut(150, function () { $(this).remove(); });
					}
				})
				.fail(function (xhr) {
					$link.removeClass('disabled');
					var msg = (xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message)
						? xhr.responseJSON.data.message
						: (aps_admin_vars.error_text || 'Request failed.');
					apsShowNotice('error', msg);
				});
		});
	});
})(jQuery);
