/**
 * Admin Ribbon JavaScript
 * 
 * Handles ribbon management functionality:
 * - Status toggle via AJAX
 * - Ribbon preview
 * - Row actions
 * 
 * Uses APS_Utils for shared functionality.
 * Removes inline styles in favor of CSS classes.
 * 
 * @package AffiliateProductShowcase
 * @since 2.0.0
 */

(function ($) {
	'use strict';

	/**
	 * @typedef {Object} RibbonConfig
	 * @property {string} nonce - Security nonce for AJAX requests
	 * @property {string} row_action_nonce - Nonce for row actions
	 * @property {string} success_text - Success message text
	 * @property {string} error_text - Error message text
	 */

	const { showNotice, ajax, getCurrentStatusView, shouldKeepRowInCurrentView, parseQueryParamsFromUrl } = window.APS_Utils;

	/** @type {RibbonConfig} */
	const config = typeof apsAdminVars !== 'undefined' ? apsAdminVars : {};

	/**
	 * Initialize ribbon preview functionality
	 */
	function initRibbonPreview() {
		const $colorInput = $('#aps_ribbon_color');
		const $bgColorPicker = $('#aps_ribbon_bg_color');
		const $bgColorText = $('#aps_ribbon_bg_color_text');
		const $presetButtons = $('.preset-color');

		// Update preview on text color change
		$colorInput.on('input', updateRibbonPreview);

		// Update preview on background color change (color picker)
		$bgColorPicker.on('input', function () {
			$bgColorText.val($(this).val());
			updateRibbonPreview();
		});

		// Update preview on background color change (text input)
		$bgColorText.on('input', function () {
			const color = $(this).val();
			if (/^#[0-9a-fA-F]{6}$/.test(color)) {
				$bgColorPicker.val(color);
				updateRibbonPreview();
			}
		});

		// Handle preset color clicks
		$presetButtons.on('click', function () {
			const color = $(this).data('color');
			$bgColorPicker.val(color);
			$bgColorText.val(color);
			updateRibbonPreview();
		});

		// Initialize
		updateRibbonPreview();
	}

	/**
	 * Update ribbon preview variables -> CSS Custom Properties
	 * Instead of inline styles, we set CSS variables on the container to keep logic in CSS.
	 */
	function updateRibbonPreview() {
		const $colorInput = $('#aps_ribbon_color');
		const $bgColorInput = $('#aps_ribbon_bg_color');
		const $preview = $('#ribbon-preview');

		const bgColor = $bgColorInput.val() || '#ff0000';
		const textColorAuto = calculateContrastColor(bgColor);
		const textColor = $colorInput.val() || textColorAuto;

		// Use CSS Custom Properties for dynamic values, keeping layout in CSS class
		$preview.css({
			'--aps-ribbon-text': textColor,
			'--aps-ribbon-bg': bgColor
		}).addClass('aps-ribbon-preview--active');
	}

	/**
	 * Calculate contrast color for text readability
	 * @param {string} bgColor - Hex color
	 * @returns {string} Hex color (white or black)
	 */
	function calculateContrastColor(bgColor) {
		const r = parseInt(bgColor.substr(1, 2), 16);
		const g = parseInt(bgColor.substr(3, 2), 16);
		const b = parseInt(bgColor.substr(5, 2), 16);
		const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
		return luminance > 0.5 ? '#000000' : '#ffffff';
	}

	/**
	 * Apply dynamic colors to ribbon name badges in table
	 */
	function applyRibbonNameColors() {
		$('#the-list tr').each(function () {
			const $row = $(this);
			const $colorSwatch = $row.find('.column-color .aps-ribbon-color-swatch');
			const $bgSwatch = $row.find('.column-bg_color .aps-ribbon-bg-color-swatch');
			const $iconDisplay = $row.find('.column-ribbon_icon .aps-ribbon-icon-display');
			const $nameLink = $row.find('.column-name .row-title');

			if ($nameLink.length && ($colorSwatch.length || $bgSwatch.length)) {
				const textColor = $colorSwatch.length ? $colorSwatch.css('background-color') : '';
				const bgColor = $bgSwatch.length ? $bgSwatch.css('background-color') : '';
				const icon = $iconDisplay.length ? $iconDisplay.text().trim() : '';

				if (icon && $nameLink.find('.ribbon-icon-prefix').length === 0) {
					// Safe DOM construction using jQuery to prevent XSS
					const escapedIcon = APS_Utils.escapeHtml ? APS_Utils.escapeHtml(icon) : icon;
					const originalText = $nameLink.text();
					$nameLink.empty()
						.append($('<span>').addClass('ribbon-icon-prefix').text(escapedIcon))
						.append(document.createTextNode(' ' + originalText));
				}

				// Set CSS variables for the specific row item
				$nameLink.css({
					'--aps-ribbon-text': textColor,
					'--aps-ribbon-bg': bgColor
				}).addClass('aps-ribbon-badge');
			}
		});
	}

	// Document Ready
	$(function () {
		// Status Toggle
		$(document).on('focus', '.aps-ribbon-status-select', function () {
			$(this).data('aps-prev', $(this).val());
		});

		$(document).on('change', '.aps-ribbon-status-select', function () {
			const $select = $(this);
			const termId = $select.data('term-id');
			const newStatus = $select.val();
			const prevStatus = $select.data('aps-prev');

			$select.prop('disabled', true);

			ajax({
				data: {
					action: 'aps_toggle_ribbon_status',
					nonce: config.nonce || '',
					term_id: termId,
					status: newStatus
				}
			})
				.done((response) => {
					$select.prop('disabled', false);
					if (!response?.success) throw response;

					showNotice('success', response.data?.message || config.success_text || 'Ribbon updated.');

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
		$(document).on('click', 'a[href*="admin-post.php?action=aps_ribbon_row_action"]', function (e) {
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
					action: 'aps_ribbon_row_action',
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
						$row.find('.aps-ribbon-status-select').val(data.status);
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

		// Initialize ribbon specific UI
		if ($('body').hasClass('taxonomy-aps_ribbon')) {
			initRibbonPreview();
			applyRibbonNameColors();
		}
	});

})(jQuery);
