/**
 * Admin Add Product JavaScript
 *
 * Handles all frontend functionality for the add/edit product form.
 * Includes media upload, multi-select components, and form validation.
 *
 * @package Affiliate_Product_Showcase
 * @since 1.0.0
 */

(function($) {
	'use strict';

	// ============================================
	// Configuration Constants
	// ============================================
	const CONFIG = {
		SCROLL_OFFSET: 50,
		ANIMATION_DURATION: 300,
		DEBOUNCE_DELAY: 300,
		SHORT_DESCRIPTION_MAX_WORDS: 40
	};

	// ============================================
	// Utility Functions
	// ============================================

	/**
	 * Debounce function to limit execution rate
	 * @param {Function} func - Function to debounce
	 * @param {number} wait - Wait time in milliseconds
	 * @returns {Function} Debounced function
	 */
	function debounce(func, wait) {
		let timeout;
		return function executedFunction(...args) {
			const later = () => {
				clearTimeout(timeout);
				func(...args);
			};
			clearTimeout(timeout);
			timeout = setTimeout(later, wait);
		};
	}

	/**
	 * Create reusable media upload handler
	 * @param {Object} config - Configuration object
	 */
	function createMediaUploadHandler(config) {
		const {
			uploadBtnId,
			urlInputId,
			hiddenUrlId,
			previewId,
			placeholderId,
			removeBtnId,
			mediaTitle = 'Select Image'
		} = config;

		// Upload button click handler
		$(`#${uploadBtnId}`).on('click', function(e) {
			e.preventDefault();
			e.stopPropagation();

			if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
				alert('WordPress media library is not loaded. Please refresh page.');
				return;
			}

			const mediaUploader = wp.media({
				title: mediaTitle,
				button: { text: 'Use This Image' },
				multiple: false
			});

			mediaUploader.on('select', function() {
				const attachment = mediaUploader.state().get('selection').first().toJSON();
				$(`#${hiddenUrlId}`).val(attachment.url);
				$(`#${urlInputId}`).val(attachment.url);
				$(`#${previewId}`)
					.css('background-image', `url(${attachment.url})`)
					.removeClass('no-image')
					.addClass('has-image');
				$(`${placeholderId}`).hide();
				$(`#${removeBtnId}`).removeClass('aps-hidden');
			});

			mediaUploader.open();
		});

		// URL input blur handler
		$(`#${urlInputId}`).on('blur', function() {
			const url = $(this).val();
			if (url) {
				$(`#${hiddenUrlId}`).val(url);
				$(`#${previewId}`)
					.css('background-image', `url(${url})`)
					.removeClass('no-image')
					.addClass('has-image');
				$(`${placeholderId}`).hide();
				$(`#${removeBtnId}`).removeClass('aps-hidden');
			} else {
				$(`#${hiddenUrlId}`).val('');
				$(`#${previewId}`)
					.css('background-image', 'none')
					.removeClass('has-image')
					.addClass('no-image');
				$(`${placeholderId}`).show();
				$(`#${removeBtnId}`).addClass('aps-hidden');
			}
		});

		// Remove button click handler
		$(`#${removeBtnId}`).on('click', function() {
			$(`#${hiddenUrlId}`).val('');
			$(`#${urlInputId}`).val('');
			$(`#${previewId}`)
				.css('background-image', 'none')
				.removeClass('has-image')
				.addClass('no-image');
			$(`${placeholderId}`).show();
			$(this).addClass('aps-hidden');
		});
	}

	/**
	 * Multi-Select Component Class
	 */
	class MultiSelect {
		constructor(config) {
			this.selectedItems = [];
			this.config = {
				containerId: '',
				dropdownId: '',
				selectedContainerId: '',
				hiddenInputId: '',
				itemSelector: '.dropdown-item',
				renderItem: (item) => item.text(),
				...config
			};

			this.init();
		}

		init() {
			this.bindEvents();
			this.renderSelected();
		}

		bindEvents() {
			// Dropdown item click
			$(document).on('click', `#${this.config.dropdownId} ${this.config.itemSelector}`, (e) => {
				const value = $(e.currentTarget).data('value');
				this.addItem(value);
			});

			// Remove tag click
			$(document).on('click', `#${this.config.selectedContainerId} .remove-tag`, (e) => {
				const index = $(e.currentTarget).data('index');
				this.removeItem(index);
			});
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
			const container = $(`#${this.config.selectedContainerId}`);
			container.empty();

			this.selectedItems.forEach((item, index) => {
				const text = this.getItemText(item);
				container.append(`<span class="aps-tag">${text}<span class="remove-tag" data-index="${index}">&times;</span></span>`);
			});
		}

		getItemText(item) {
			const dropdownItem = $(`#${this.config.dropdownId} ${this.config.itemSelector}[data-value="${item}"]`);
			return this.config.renderItem(dropdownItem);
		}

		updateHiddenInput() {
			$(`#${this.config.hiddenInputId}`).val(this.selectedItems.join(','));
		}

		setItems(items) {
			this.selectedItems = [...items];
			this.renderSelected();
			this.updateHiddenInput();
		}
	}

	// ============================================
	// Features List Handler
	// ============================================
	const features = [];

	function renderFeatures() {
		const container = $('#aps-features-list');
		container.empty();
		features.forEach((feature, index) => {
			const html = `<div class="aps-feature-item ${feature.highlighted ? 'highlighted' : ''}" data-index="${index}">
				<span class="feature-text">${feature.text.replace(/</g, '<')}</span>
				<div class="feature-actions">
					<button type="button" class="highlight-btn" title="Highlight"><i class="fas fa-bold"></i></button>
					<button type="button" class="move-up" title="Move Up"><i class="fas fa-arrow-up"></i></button>
					<button type="button" class="move-down" title="Move Down"><i class="fas fa-arrow-down"></i></button>
					<button type="button" class="delete-btn" title="Delete"><i class="fas fa-trash"></i></button>
				</div>`;
			container.append(html);
		});
		$('#aps-features-input').val(JSON.stringify(features));
	}

	// ============================================
	// Document Ready Handler
	// ============================================
	$(document).ready(function() {
		// ============================================
		// Quick Navigation
		// ============================================
		$('.aps-quick-nav .nav-link').on('click', function(e) {
			e.preventDefault();
			const target = $(this).attr('href');
			$('html, body').animate({ scrollTop: $(target).offset().top - CONFIG.SCROLL_OFFSET }, CONFIG.ANIMATION_DURATION);
		});

		// ============================================
		// Word Counter (with debouncing)
		// ============================================
		$('#aps-short-description').on('input', debounce(function() {
			const text = $(this).val().trim();
			const words = text === '' ? 0 : text.split(/\s+/).length;
			$('#aps-word-count').text(Math.min(words, CONFIG.SHORT_DESCRIPTION_MAX_WORDS));
		}, CONFIG.DEBOUNCE_DELAY));

		// ============================================
		// Discount Calculator
		// ============================================
		$('#aps-current-price, #aps-original-price').on('input', function() {
			const current = parseFloat($('#aps-current-price').val()) || 0;
			const original = parseFloat($('#aps-original-price').val()) || 0;

			// Calculate discount: (original - current) / original * 100
			// Only show discount if original price is greater than current price
			if (current > 0 && original > 0 && original > current) {
				const discount = ((original - current) / original * 100).toFixed(0);
				$('#aps-discount').val(discount + '% OFF');
			} else {
				$('#aps-discount').val('0% OFF');
			}
		});

		// ============================================
		// Features List
		// ============================================
		if (apsAddProductData.isEditing && apsAddProductData.productData.features && Array.isArray(apsAddProductData.productData.features)) {
			features.push(...apsAddProductData.productData.features);
			renderFeatures();
		}

		$('#aps-add-feature').on('click', function() {
			const input = $('#aps-new-feature');
			const featureText = input.val().trim();
			if (featureText) {
				features.push({ text: featureText, highlighted: false });
				input.val('');
				renderFeatures();
			}
		});

		$('#aps-new-feature').on('keypress', function(e) {
			if (e.which === 13) {
				e.preventDefault();
				$('#aps-add-feature').click();
			}
		});

		// Feature item actions
		$(document).on('click', '.aps-feature-item .highlight-btn', function() {
			const index = $(this).closest('.aps-feature-item').data('index');
			features[index].highlighted = !features[index].highlighted;
			renderFeatures();
		});

		$(document).on('click', '.aps-feature-item .move-up', function() {
			const index = $(this).closest('.aps-feature-item').data('index');
			if (index > 0) {
				[features[index], features[index - 1]] = [features[index - 1], features[index]];
				renderFeatures();
			}
		});

		$(document).on('click', '.aps-feature-item .move-down', function() {
			const index = $(this).closest('.aps-feature-item').data('index');
			if (index < features.length - 1) {
				[features[index], features[index + 1]] = [features[index + 1], features[index]];
				renderFeatures();
			}
		});

		$(document).on('click', '.aps-feature-item .delete-btn', function() {
			const index = $(this).closest('.aps-feature-item').data('index');
			features.splice(index, 1);
			renderFeatures();
		});

		// ============================================
		// Multi-Select Dropdowns
		// ============================================

		// Categories multi-select
		const categoriesSelect = new MultiSelect({
			containerId: 'aps-categories-select',
			dropdownId: 'aps-categories-dropdown',
			selectedContainerId: 'aps-selected-categories',
			hiddenInputId: 'aps-categories-input',
			renderItem: (item) => item.find('.taxonomy-name').text()
		});

		if (apsAddProductData.isEditing && apsAddProductData.productData.categories && Array.isArray(apsAddProductData.productData.categories)) {
			categoriesSelect.setItems(apsAddProductData.productData.categories);
		}

		// Ribbons multi-select
		const ribbonsSelect = new MultiSelect({
			containerId: 'aps-ribbons-select',
			dropdownId: 'aps-ribbons-dropdown',
			selectedContainerId: 'aps-selected-ribbons',
			hiddenInputId: 'aps-ribbons-input',
			renderItem: (item) => {
				const preview = item.find('.ribbon-badge-preview');
				const color = preview.css('color');
				const bgColor = preview.css('background-color');
				const text = item.find('.ribbon-name').text();
				const icon = item.find('.ribbon-icon').text();
				const iconHtml = icon ? `<span class="ribbon-icon">${icon}</span>` : '';
				return `<span style="color: ${color}; background-color: ${bgColor};">${iconHtml}${text}</span>`;
			}
		});

		if (apsAddProductData.isEditing && apsAddProductData.productData.ribbons && Array.isArray(apsAddProductData.productData.ribbons)) {
			ribbonsSelect.setItems(apsAddProductData.productData.ribbons);
		}

		// Multi-select dropdown toggle
		$('.aps-multi-select .aps-multiselect-input').on('focus click', function() {
			$(this).siblings('.aps-dropdown').slideDown(CONFIG.ANIMATION_DURATION);
			$(this).closest('.aps-multi-select').attr('aria-expanded', 'true');
		});

		// Close dropdown when clicking outside
		$(document).on('click', function(e) {
			if (!$(e.target).closest('.aps-multi-select').length) {
				$('.aps-dropdown').slideUp(CONFIG.ANIMATION_DURATION);
				$('.aps-multi-select').attr('aria-expanded', 'false');
			}
		});

		// ============================================
		// Keyboard Navigation for Multi-Select Dropdowns
		// ============================================
		$('.aps-multiselect-input').on('keydown', function(e) {
			const dropdown = $(this).siblings('.aps-dropdown');
			const items = dropdown.find('.dropdown-item');
			const currentIndex = items.index(items.filter('.focused'));

			switch(e.key) {
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
			mediaTitle: 'Select Image'
		});

		createMediaUploadHandler({
			uploadBtnId: 'aps-upload-brand-btn',
			urlInputId: 'aps-brand-url-input',
			hiddenUrlId: 'aps-brand-image-url',
			previewId: 'aps-brand-preview',
			placeholderId: '#aps-brand-upload .upload-placeholder',
			removeBtnId: 'aps-remove-brand-btn',
			mediaTitle: 'Select Brand Image'
		});

		// ============================================
		// Initialize Image Previews from Data Attributes
		// ============================================
		$('[data-image-url]').each(function() {
			const url = $(this).data('image-url');
			if (url) {
				$(this).css('background-image', `url(${url})`);
			}
		});

		// ============================================
		// Populate Statistics Fields (Edit Mode)
		// ============================================
		if (apsAddProductData.isEditing) {
			const data = apsAddProductData.productData;
			if (data.rating) $('#aps-rating').val(data.rating);
			if (data.views) $('#aps-views').val(data.views);
			if (data.reviews) $('#aps-reviews').val(data.reviews);
			if (data.user_count) $('#aps-user-count').val(data.user_count);
		}
	});

})(jQuery);
