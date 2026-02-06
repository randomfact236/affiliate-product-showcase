/**
 * Admin Add Product JavaScript
 *
 * Handles all frontend functionality for the add/edit product form.
 * Includes media upload, multi-select components, and form validation.
 *
 * @package Affiliate_Product_Showcase
 * @since 1.0.0
 */

(function ($) {
	'use strict';

	const { __ } = wp.i18n;

	// ============================================
	// Configuration Constants
	// ============================================
	const CONFIG = {
		SCROLL_OFFSET: 50,
		ANIMATION_DURATION: 300,
		DEBOUNCE_DELAY: 300,
		SHORT_DESCRIPTION_MAX_WORDS: 40,
		KEY_ENTER: 13,
		KEY_ESCAPE: 27
	};

	/**
	 * @typedef {Object} MediaUploadConfig
	 * @property {string} uploadBtnId
	 * @property {string} urlInputId
	 * @property {string} hiddenUrlId
	 * @property {string} previewId
	 * @property {string} placeholderId
	 * @property {string} removeBtnId
	 * @property {string} [mediaTitle]
	 */

	/**
	 * Create reusable media upload handler
	 *
	 * @param {MediaUploadConfig} config - Configuration object
	 * @since 1.0.0
	 */
	function createMediaUploadHandler(config) {
		const uploadBtnId = config.uploadBtnId;
		const urlInputId = config.urlInputId;
		const hiddenUrlId = config.hiddenUrlId;
		const previewId = config.previewId;
		const placeholderId = config.placeholderId;
		const removeBtnId = config.removeBtnId;
		const mediaTitle = config.mediaTitle || __('Select Image', 'affiliate-product-showcase');

		// Store media uploader instance on the button element to prevent recreation
		// and avoid closure memory issues
		const $uploadBtn = $(`#${uploadBtnId}`);

		// Upload button click handler
		$uploadBtn.on('click', function (e) {
			e.preventDefault();
			e.stopPropagation();

			if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
				APS_Utils.showNotice('error', __('WordPress media library is not loaded. Please refresh page.', 'affiliate-product-showcase'));
				return;
			}

			// Get or create media uploader instance stored on the button
			let mediaUploader = $uploadBtn.data('mediaUploader');

			// Create media uploader only once and reuse it
			if (!mediaUploader) {
				mediaUploader = wp.media({
					title: mediaTitle,
					button: { text: __('Use This Image', 'affiliate-product-showcase') },
					multiple: false
				});

				mediaUploader.on('select', function () {
					const attachment = mediaUploader.state().get('selection').first().toJSON();
					// Validate and sanitize URL before using
					const safeUrl = APS_Utils.sanitizeUrl(attachment.url);
					if (!safeUrl) {
						APS_Utils.showNotice('error', __('Invalid image URL from media library. Please try a different image.', 'affiliate-product-showcase'));
						return;
					}
					$(`#${hiddenUrlId}`).val(safeUrl);
					$(`#${urlInputId}`).val(safeUrl);
					// Use img tag to match HTML structure - escape URL for safety
					$(`#${previewId}`).html('<img src="' + APS_Utils.escapeHtml(safeUrl) + '" alt="">').addClass('has-image').show();
					$(`${placeholderId}`).hide();
					$(`#${removeBtnId}`).removeClass('aps-hidden');
				});

				// Store reference on button element instead of closure
				$uploadBtn.data('mediaUploader', mediaUploader);
			}

			mediaUploader.open();
		});

		// URL input change handler
		$(`#${urlInputId}`).on('change', function () {
			const url = $(this).val().trim();
			const safeUrl = APS_Utils.sanitizeUrl(url);
			if (safeUrl) {
				$(`#${hiddenUrlId}`).val(safeUrl);
				// Use img tag to match HTML structure - sanitize URL for safety
				$(`#${previewId}`).html('<img src="' + APS_Utils.escapeHtml(safeUrl) + '" alt="">').addClass('has-image').show();
				$(`${placeholderId}`).hide();
				$(`#${removeBtnId}`).removeClass('aps-hidden');
			} else if (url) {
				// URL was entered but is invalid
				APS_Utils.showNotice('error', __('Invalid URL. Only http, https, and data URLs are allowed.', 'affiliate-product-showcase'));
				// Reset the input
				$(this).val('');
			}
		});

		// Remove button click handler
		$(`#${removeBtnId}`).on('click', function () {
			$(`#${hiddenUrlId}`).val('');
			$(`#${urlInputId}`).val('');
			$(`#${previewId}`).html('').removeClass('has-image').hide();
			$(`${placeholderId}`).show();
			$(this).addClass('aps-hidden');
		});
	}

	/**
	 * @typedef {Object} MultiSelectConfig
	 * @property {string} dropdownId
	 * @property {string} selectedContainerId
	 * @property {string} hiddenInputId
	 * @property {string} [itemSelector]
	 * @property {Function} [renderItem]
	 */

	/**
	 * Multi-Select Component Class
	 *
	 * @since 1.0.0
	 */
	class MultiSelect {
		constructor(config) {
			this.selectedItems = [];
			this.config = {
				dropdownId: '',
				selectedContainerId: '',
				hiddenInputId: '',
				itemSelector: '.dropdown-item',
				renderItem: (item) => item.text(),
				...config
			};

			// Cache jQuery selectors
			this.$dropdown = $(`#${this.config.dropdownId}`);
			this.$selectedContainer = $(`#${this.config.selectedContainerId}`);
			this.$hiddenInput = $(`#${this.config.hiddenInputId}`);

			this.init();
		}

		init() {
			this.bindEvents();
			this.renderSelected();
		}

		bindEvents() {
			const self = this;

			// Dropdown item click - use container delegation
			this.$dropdown.on('click.aps', this.config.itemSelector, function (e) {
				const value = $(this).data('value');
				if (typeof value !== 'undefined') {
					self.addItem(value);
				}
			});

			// Remove tag click - use container delegation
			this.$selectedContainer.on('click.aps', '.remove-tag', function (e) {
				e.preventDefault();
				const index = $(this).data('index');
				if (typeof index !== 'undefined') {
					self.removeItem(index);
				}
			});

			// Keyboard support for remove tag buttons (Enter/Space)
			this.$selectedContainer.on('keydown.aps', '.remove-tag', function (e) {
				if (e.key === 'Enter' || e.key === ' ') {
					e.preventDefault();
					const index = $(this).data('index');
					if (typeof index !== 'undefined') {
						self.removeItem(index);
					}
				}
			});
		}

		destroy() {
			this.$dropdown.off('.aps');
			this.$selectedContainer.off('.aps');
		}

		addItem(value) {
			if (!this.selectedItems.includes(value)) {
				this.selectedItems.push(value);
				this.renderSelected();
				this.updateHiddenInput();
			}
		}

		removeItem(index) {
			this.selectedItems.splice(index, 1);
			this.renderSelected();
			this.updateHiddenInput();
		}

		renderSelected() {
			const self = this;
			this.$selectedContainer.empty();

			this.selectedItems.forEach(function (item, index) {
				const text = self.getItemText(item);
				// Escape the text content and use data attribute for index
				const $tag = $('<span class="aps-tag"></span>');
				const $removeBtn = $('<span class="remove-tag" role="button" tabindex="0"></span>')
					.attr('aria-label', __('Remove item', 'affiliate-product-showcase'))
					.attr('data-index', index)
					.text('\u00D7'); // × symbol

				$tag.html(text).append($removeBtn);
				self.$selectedContainer.append($tag);
			});
		}

		getItemText(item) {
			// Escape item value for use in attribute selector
			const escapedItem = String(item).replace(/"/g, '\\"');
			const dropdownItem = this.$dropdown.find(`${this.config.itemSelector}[data-value="${escapedItem}"]`);
			return this.config.renderItem(dropdownItem);
		}

		updateHiddenInput() {
			this.$hiddenInput.val(this.selectedItems.join(','));
		}

		setItems(items) {
			this.selectedItems = [...items];
			this.renderSelected();
			this.updateHiddenInput();
		}
	}

	// ============================================
	// Document Ready Handler
	// ============================================
	$(document).ready(function () {

		// Consolidated global data check
		const appData = (typeof apsAddProductData !== 'undefined') ? apsAddProductData : null;
		// ============================================
		// Quick Navigation
		// ============================================
		$('.aps-quick-nav .nav-link').on('click', function (e) {
			e.preventDefault();
			const target = $(this).attr('href');
			$('html, body').animate({ scrollTop: $(target).offset().top - CONFIG.SCROLL_OFFSET }, CONFIG.ANIMATION_DURATION);
		});

		// ============================================
		// Word Counter (with debouncing and color feedback)
		// ============================================
		const $wordCount = $('#aps-word-count');
		$('#aps-short-description').on('input', APS_Utils.debounce(function () {
			const text = $(this).val().trim();
			const words = text === '' ? 0 : text.split(/\s+/).length;
			const displayCount = Math.min(words, CONFIG.SHORT_DESCRIPTION_MAX_WORDS);
			$wordCount.text(displayCount);

			// Color feedback: red if over limit, default otherwise
			if (words > CONFIG.SHORT_DESCRIPTION_MAX_WORDS) {
				$wordCount.removeClass('u-text-muted').addClass('u-text-danger');
			} else {
				$wordCount.removeClass('u-text-danger').addClass('u-text-muted');
			}
		}, CONFIG.DEBOUNCE_DELAY));

		// Initialize word count on page load
		(function initWordCount() {
			const text = $('#aps-short-description').val().trim();
			const words = text === '' ? 0 : text.split(/\s+/).length;
			$wordCount.text(Math.min(words, CONFIG.SHORT_DESCRIPTION_MAX_WORDS));
			if (words > CONFIG.SHORT_DESCRIPTION_MAX_WORDS) {
				$wordCount.removeClass('u-text-muted').addClass('u-text-danger');
			}
		})();

		// ============================================
		// Discount Calculator (with color feedback)
		// ============================================
		const $discountInput = $('#aps-discount');
		const $currentPrice = $('#aps-current-price');
		const $originalPrice = $('#aps-original-price');

		function calculateDiscount() {
			const current = parseFloat($currentPrice.val()) || 0;
			const original = parseFloat($originalPrice.val()) || 0;

			// Calculate discount: (original - current) / original * 100
			// Only show discount if original price is greater than current price
			if (current > 0 && original > 0 && original > current) {
				const discount = Math.round(((original - current) / original) * 100);
				$discountInput.val(discount + '% ' + __('OFF', 'affiliate-product-showcase'));
				$discountInput.removeClass('u-text-muted').addClass('u-text-success'); // Green for discount
			} else {
				$discountInput.val('0% ' + __('OFF', 'affiliate-product-showcase'));
				$discountInput.removeClass('u-text-success').addClass('u-text-muted'); // Default color
			}
		}

		$currentPrice.on('input', calculateDiscount);
		$originalPrice.on('input', calculateDiscount);
		calculateDiscount(); // Initialize on load

		// ============================================
		// Multi-Select Dropdowns
		// ============================================

		// Categories multi-select
		const categoriesSelect = new MultiSelect({
			dropdownId: 'aps-categories-dropdown',
			selectedContainerId: 'aps-selected-categories',
			hiddenInputId: 'aps-categories-input',
			renderItem: function (item) {
				return APS_Utils.escapeHtml(item.find('.taxonomy-name').text());
			}
		});

		// Safely check for global data with existence validation
		if (appData &&
			appData.isEditing &&
			appData.productData &&
			appData.productData.categories &&
			Array.isArray(appData.productData.categories)) {
			categoriesSelect.setItems(appData.productData.categories);
		}

		// Ribbons multi-select
		const ribbonsSelect = new MultiSelect({
			dropdownId: 'aps-ribbons-dropdown',
			selectedContainerId: 'aps-selected-ribbons',
			hiddenInputId: 'aps-ribbons-input',
			renderItem: function (item) {
				const preview = item.find('.ribbon-badge-preview');
				const color = preview.css('color');
				const bgColor = preview.css('background-color');
				const text = item.find('.ribbon-name').text();
				const icon = item.find('.ribbon-icon').text();

				// Use jQuery builder pattern for safe DOM construction
				const $span = $('<span>')
					.addClass('ribbon-tag-preview')
					.css({
						color: color,
						backgroundColor: bgColor
					});

				if (icon) {
					$span.append($('<span>').addClass('ribbon-icon').text(icon));
				}

				$span.append(document.createTextNode(text));

				// Return the outer HTML
				return $span.prop('outerHTML');
			}
		});

		// Safely check for global data with existence validation
		if (appData &&
			appData.isEditing &&
			appData.productData &&
			appData.productData.ribbons &&
			Array.isArray(appData.productData.ribbons)) {
			ribbonsSelect.setItems(appData.productData.ribbons);
		}

		// Multi-select dropdown toggle
		$('.aps-multi-select .aps-multiselect-input').on('focus click', function () {
			$(this).siblings('.aps-dropdown').slideDown(CONFIG.ANIMATION_DURATION);
			$(this).closest('.aps-multi-select').attr('aria-expanded', 'true');
		});

		// Close dropdown when clicking outside - use single delegated handler
		$(document).on('click.aps', function (e) {
			if (!$(e.target).closest('.aps-multi-select').length) {
				$('.aps-dropdown').slideUp(CONFIG.ANIMATION_DURATION);
				$('.aps-multi-select').attr('aria-expanded', 'false');
			}
		});

		// ============================================
		// Keyboard Navigation for Multi-Select Dropdowns
		// ============================================
		$('.aps-multiselect-input').on('keydown', function (e) {
			const dropdown = $(this).siblings('.aps-dropdown');
			const items = dropdown.find('.dropdown-item');
			const currentIndex = items.index(items.filter('.focused'));

			switch (e.key) {
				case 'ArrowDown':
					e.preventDefault();
					{
						const nextIndex = Math.min(currentIndex + 1, items.length - 1);
						items.removeClass('focused').eq(nextIndex).addClass('focused');
					}
					break;
				case 'ArrowUp':
					e.preventDefault();
					{
						const prevIndex = Math.max(currentIndex - 1, 0);
						items.removeClass('focused').eq(prevIndex).addClass('focused');
					}
					break;
				case 'Enter':
				case ' ':
					e.preventDefault();
					items.filter('.focused').click();
					break;
				case 'Escape':
					e.preventDefault();
					dropdown.slideUp(CONFIG.ANIMATION_DURATION);
					$(this).closest('.aps-multi-select').attr('aria-expanded', 'false');
					break;
			}
		});

		// ============================================
		// Media Upload Handlers
		// ============================================
		createMediaUploadHandler({
			uploadBtnId: 'aps-upload-image-btn',
			urlInputId: 'aps-image-url-input',
			hiddenUrlId: 'aps-image-url',
			previewId: 'aps-image-preview',
			placeholderId: '#aps-image-upload .upload-placeholder',
			removeBtnId: 'aps-remove-image-btn',
			mediaTitle: __('Select Image', 'affiliate-product-showcase')
		});

		createMediaUploadHandler({
			uploadBtnId: 'aps-upload-brand-btn',
			urlInputId: 'aps-brand-url-input',
			hiddenUrlId: 'aps-brand-image-url',
			previewId: 'aps-brand-preview',
			placeholderId: '#aps-brand-upload .upload-placeholder',
			removeBtnId: 'aps-remove-brand-btn',
			mediaTitle: __('Select Brand Image', 'affiliate-product-showcase')
		});

		// ============================================
		// Initialize Image Previews from Data Attributes
		// ============================================
		$('[data-image-url]').each(function () {
			const url = $(this).data('image-url');
			const safeUrl = APS_Utils.sanitizeUrl(url);
			if (safeUrl) {
				// Use CSS class with custom property - escape URL for CSS context
				$(this).addClass('has-bg-image').css('--bg-image-url', 'url(' + safeUrl + ')');
			}
		});

		// ============================================
		// Populate Statistics Fields (Edit Mode)
		// ============================================
		// Safely check for global data with existence validation
		if (appData && appData.isEditing && appData.productData) {
			const data = appData.productData;
			if (data.rating) {
				$('#aps-rating').val(data.rating);
			}
			if (data.views) {
				$('#aps-views').val(data.views);
			}
			if (data.reviews) {
				$('#aps-reviews').val(data.reviews);
			}
			if (data.user_count) {
				$('#aps-user-count').val(data.user_count);
			}
		}

		// ============================================
		// Feature List Management
		// ============================================
		(function initFeatureList() {
			const $addFeatureBtn = $('#aps-add-feature');
			const $newFeatureInput = $('#aps-new-feature');
			const $featuresList = $('#aps-features-list');
			const $featuresInput = $('#aps-features-input');

			// Add ARIA live region for accessibility announcements
			const $a11yStatus = $('<div id="aps-features-a11y-status" class="screen-reader-text" aria-live="polite"></div>');
			$featuresList.before($a11yStatus);

			function announce(message) {
				$a11yStatus.text(message);
				// Clear after delay so identical messages can be read again if needed
				setTimeout(() => $a11yStatus.text(''), 1000);
			}

			// Get initial features from hidden input or empty array
			let features = [];
			try {
				const inputVal = $featuresInput.val();
				if (inputVal) {
					features = JSON.parse(inputVal);
				}
			} catch (e) {
				console.error('APS: Failed to parse features JSON', e);
				features = [];
			}

			function updateFeaturesInput() {
				$featuresInput.val(JSON.stringify(features));
			}

			function renderFeatures() {
				$featuresList.empty();
				features.forEach(function (feature, index) {
					const text = typeof feature === 'object' ? feature.text : feature;
					const isBold = typeof feature === 'object' ? feature.bold : false;

					// Use jQuery element creation instead of string concatenation for better security
					const $item = $('<div>')
						.addClass('feature-item')
						.attr('data-index', index)
						.attr('data-bold', isBold ? '1' : '0');

					// Content wrapper
					const $content = $('<div>').addClass('feature-item-content');

					// Drag handle
					$content.append(
						$('<span>')
							.addClass('dashicons dashicons-menu drag-handle')
							.attr('title', __('Drag to reorder', 'affiliate-product-showcase'))
							.attr('aria-hidden', 'true')
					);

					// Text
					$content.append(
						$('<span>')
							.addClass('feature-text')
							.toggleClass('is-bold', isBold)
							.text(text) // Safe: .text() escapes HTML automatically
					);

					// Actions wrapper
					const $actions = $('<div>').addClass('feature-actions');

					// Move Up Button
					const $moveUpBtn = $('<button>')
						.attr('type', 'button')
						.addClass('feature-btn move-btn move-up')
						.attr('title', __('Move up', 'affiliate-product-showcase'))
						.attr('aria-label', __('Move feature up', 'affiliate-product-showcase'))
						.prop('disabled', index === 0);
					$moveUpBtn.append($('<span>').addClass('dashicons dashicons-arrow-up-alt2 aps-icon-sm'));

					// Move Down Button
					const $moveDownBtn = $('<button>')
						.attr('type', 'button')
						.addClass('feature-btn move-btn move-down')
						.attr('title', __('Move down', 'affiliate-product-showcase'))
						.attr('aria-label', __('Move feature down', 'affiliate-product-showcase'))
						.prop('disabled', index === features.length - 1);
					$moveDownBtn.append($('<span>').addClass('dashicons dashicons-arrow-down-alt2 aps-icon-sm'));

					// Bold Button
					const $boldBtn = $('<button>')
						.attr('type', 'button')
						.addClass('feature-btn bold-btn')
						.toggleClass('active', isBold)
						.attr('title', __('Toggle bold', 'affiliate-product-showcase'))
						.attr('aria-label', __('Toggle bold style', 'affiliate-product-showcase'))
						.attr('aria-pressed', isBold);
					$boldBtn.append($('<span>').addClass('dashicons dashicons-editor-bold aps-icon-sm'));

					// Remove Button
					const $removeBtn = $('<button>')
						.attr('type', 'button')
						.addClass('remove-feature')
						.attr('aria-label', __('Remove feature', 'affiliate-product-showcase'))
						.html('&times;');

					$actions.append($moveUpBtn, $moveDownBtn, $boldBtn, $removeBtn);
					$item.append($content, $actions);

					$featuresList.append($item);
				});
				updateFeaturesInput();
			}

			function addFeature() {
				const text = $newFeatureInput.val().trim();
				if (text) {
					// Check if already exists
					const exists = features.some(function (f) {
						const fText = typeof f === 'object' ? f.text : f;
						return fText === text;
					});
					if (!exists) {
						features.push({ text: text, bold: false });
						renderFeatures();
						$newFeatureInput.val('');
						announce(__('Feature added:', 'affiliate-product-showcase') + ' ' + text);
					}
				}
			}

			$addFeatureBtn.on('click', addFeature);

			$newFeatureInput.on('keypress', function (e) {
				if (e.which === CONFIG.KEY_ENTER) {
					e.preventDefault();
					addFeature();
				}
			});

			// Remove feature
			$featuresList.on('click', '.remove-feature', function () {
				const index = $(this).closest('.feature-item').data('index');
				const removedText = typeof features[index] === 'object' ? features[index].text : features[index];
				features.splice(index, 1);
				renderFeatures();
				announce(__('Feature removed:', 'affiliate-product-showcase') + ' ' + removedText);
			});

			// Toggle bold
			$featuresList.on('click', '.bold-btn', function () {
				const $item = $(this).closest('.feature-item');
				const index = $item.data('index');
				const $textSpan = $item.find('.feature-text');

				if (typeof features[index] === 'object') {
					features[index].bold = !features[index].bold;
				} else {
					features[index] = { text: features[index], bold: true };
				}

				$textSpan.toggleClass('is-bold');
				$(this).toggleClass('active');
				$item.attr('data-bold', features[index].bold ? '1' : '0');
				updateFeaturesInput();
			});

			// Move up
			$featuresList.on('click', '.move-up:not(:disabled)', function () {
				const $item = $(this).closest('.feature-item');
				const index = $item.data('index');

				if (index > 0) {
					const temp = features[index];
					features[index] = features[index - 1];
					features[index - 1] = temp;
					renderFeatures();

					// Maintain focus on the moved item's up button
					$featuresList.find('.feature-item[data-index="' + (index - 1) + '"] .move-up').focus();
					announce(__('Moved up to position', 'affiliate-product-showcase') + ' ' + index);
				}
			});

			// Move down
			$featuresList.on('click', '.move-down:not(:disabled)', function () {
				const $item = $(this).closest('.feature-item');
				const index = $item.data('index');

				if (index < features.length - 1) {
					const temp = features[index];
					features[index] = features[index + 1];
					features[index + 1] = temp;
					renderFeatures();

					// Maintain focus on the moved item's down button
					$featuresList.find('.feature-item[data-index="' + (index + 1) + '"] .move-down').focus();
					announce(__('Moved down to position', 'affiliate-product-showcase') + ' ' + (index + 2));
				}
			});

			// Drag and drop functionality
			let draggedItem = null;

			$featuresList.on('dragstart', '.feature-item', function (e) {
				draggedItem = $(this);
				$(this).addClass('dragging');
				e.originalEvent.dataTransfer.effectAllowed = 'move';
			});

			$featuresList.on('dragend', '.feature-item', function () {
				$(this).removeClass('dragging');
				draggedItem = null;
			});

			$featuresList.on('dragover', '.feature-item', function (e) {
				e.preventDefault();
				if (!draggedItem || draggedItem[0] === this) return;

				const targetIndex = $(this).data('index');
				const draggedIndex = draggedItem.data('index');

				if (draggedIndex < targetIndex) {
					$(this).after(draggedItem);
				} else {
					$(this).before(draggedItem);
				}

				// Update array
				const item = features.splice(draggedIndex, 1)[0];
				features.splice(targetIndex, 0, item);

				renderFeatures();
				announce(__('Item reordered', 'affiliate-product-showcase'));
			});

			// Edit feature inline
			$featuresList.on('click', '.feature-text', function (e) {
				e.preventDefault();
				const $textSpan = $(this);
				const $item = $textSpan.closest('.feature-item');
				const index = $item.data('index');
				const currentText = typeof features[index] === 'object' ? features[index].text : features[index];

				// Create input field
				const $input = $('<input>')
					.attr('type', 'text')
					.addClass('aps-input aps-input-flex')
					.val(currentText);

				// Replace text with input
				$textSpan.replaceWith($input);
				$input.focus().select();

				// Save on blur or enter key
				function saveEdit() {
					const newText = $input.val().trim();
					if (newText && newText !== currentText) {
						if (typeof features[index] === 'object') {
							features[index].text = newText;
						} else {
							features[index] = newText;
						}
						updateFeaturesInput();
					}
					renderFeatures();
				}

				$input.on('blur', saveEdit);

				$input.on('keypress', function (e) {
					if (e.which === CONFIG.KEY_ENTER) {
						e.preventDefault();
						$input.off('blur');
						saveEdit();
					}
				});

				$input.on('keydown', function (e) {
					if (e.which === CONFIG.KEY_ESCAPE) {
						e.preventDefault();
						$input.off('blur');
						renderFeatures();
					}
				});
			});

			// Make feature items draggable via handle
			$featuresList.on('mousedown', '.drag-handle', function () {
				$(this).closest('.feature-item').attr('draggable', 'true');
			});

			$featuresList.on('mouseup', '.drag-handle', function () {
				$(this).closest('.feature-item').attr('draggable', 'false');
			});

			// Initial render
			renderFeatures();
		})();
	});

})(jQuery);
