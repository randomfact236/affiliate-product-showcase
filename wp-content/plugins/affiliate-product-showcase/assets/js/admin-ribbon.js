/* global jQuery, aps_admin_vars, ajaxurl */

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

	/**
	 * Initialize ribbon preview functionality
	 */
	function initRibbonPreview() {
		var $colorInput = $('#aps_ribbon_color');
		var $bgColorPicker = $('#aps_ribbon_bg_color');
		var $bgColorText = $('#aps_ribbon_bg_color_text');
		var $presetButtons = $('.preset-color');
		var $preview = $('#ribbon-preview');
		
		// Update preview on text color change
		$colorInput.on('input', function() {
			updateRibbonPreview();
		});
		
		// Update preview on background color change (color picker)
		$bgColorPicker.on('input', function() {
			// Sync with text input
			$bgColorText.val($(this).val());
			updateRibbonPreview();
		});
		
		// Update preview on background color change (text input)
		$bgColorText.on('input', function() {
			// Validate hex color
			var color = $(this).val();
			if (/^#[0-9a-fA-F]{6}$/.test(color)) {
				// Sync with color picker
				$bgColorPicker.val(color);
				updateRibbonPreview();
			}
		});
		
		// Handle preset color clicks
		$presetButtons.on('click', function() {
			var color = $(this).data('color');
			
			// Update both inputs
			$bgColorPicker.val(color);
			$bgColorText.val(color);
			
			// Update preview
			updateRibbonPreview();
		});
		
		// Initialize preview on page load
		updateRibbonPreview();
	}

	/**
	 * Update ribbon preview with current colors
	 */
	function updateRibbonPreview() {
		var $colorInput = $('#aps_ribbon_color');
		var $bgColorInput = $('#aps_ribbon_bg_color');
		var $preview = $('#ribbon-preview');
		
		var textColor = $colorInput.val() || '#ff6b6b';
		var bgColor = $bgColorInput.val() || '#ff0000';
		
		// Calculate text contrast based on background brightness
		var textColorAuto = calculateContrastColor(bgColor);
		
		// Apply styles to preview
		$preview.css({
			'color': textColor,
			'background-color': bgColor,
			'border': '1px solid rgba(255,255,255,0.1)',
			'padding': '8px 16px',
			'border-radius': '4px',
			'font-weight': 'bold',
			'display': 'inline-block'
		});
	}

	/**
	 * Calculate contrast color for text readability
	 * Dark background -> light text, Light background -> dark text
	 */
	function calculateContrastColor(bgColor) {
		// Parse hex to RGB
		var r = parseInt(bgColor.substr(1, 2), 16);
		var g = parseInt(bgColor.substr(3, 2), 16);
		var b = parseInt(bgColor.substr(5, 2), 16);
		
		// Calculate luminance (perceived brightness)
		var luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
		
		// Return black or white based on luminance
		return luminance > 128 ? '#000000' : '#ffffff';
	}

	$(function () {
		$(document).on('focus', '.aps-ribbon-status-select', function () {
			$(this).data('aps-prev', $(this).val());
		});

		$(document).on('change', '.aps-ribbon-status-select', function () {
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
					action: 'aps_toggle_ribbon_status',
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
				apsShowNotice('success', (response.data && response.data.message) ? response.data.message : (aps_admin_vars.success_text || 'Ribbon updated.'));

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

		$(document).on('click', 'a[href*="admin-post.php?action=aps_ribbon_row_action"]', function (e) {
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
					action: 'aps_ribbon_row_action',
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

				if (data.status) {
					$row.find('.aps-ribbon-status-select').val(data.status);
				}

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

		// Initialize ribbon preview when on ribbon page
		$(document).ready(function() {
			if ($('body').hasClass('taxonomy-aps_ribbon')) {
				initRibbonPreview();
				
				// Apply dynamic colors to ribbon name badges in table
				applyRibbonNameColors();
			}
		});

	/**
	 * Apply dynamic colors to ribbon name badges in table
	 */
	function applyRibbonNameColors() {
			// Find all name spans with color data attributes
			$('span[data-ribbon-bg]').each(function() {
				var $span = $(this);
				var $link = $span.closest('td').find('a');
				
				var bgColor = $span.data('ribbon-bg');
				var textColor = $span.data('ribbon-text');
				
				// Apply styles to name link
				var styles = {
					'padding': '4px 12px',
					'border-radius': '4px',
					'font-weight': '600',
					'font-size': '12px',
					'text-transform': 'uppercase',
					'letter-spacing': '0.5px',
					'box-shadow': '0 1px 3px rgba(0, 0, 0, 0.1)',
					'text-decoration': 'none',
					'display': 'inline-block'
				};
				
				if (bgColor) {
					styles['background-color'] = bgColor;
				}
				
				if (textColor) {
					styles['color'] = textColor;
				}
				
				$link.css(styles);
			});
		}
	});
})(jQuery);