<?php

declare(strict_types=1);

namespace AffiliateProductShowcase\Admin\Config;

/**
 * Product Form Constants
 *
 * Defines all configuration constants for the product form.
 * Replaces magic numbers with named constants for better maintainability.
 *
 * @package AffiliateProductShowcase\Admin\Config
 * @since 1.0.0
 */
class ProductFormConstants {

	// ============================================
	// Validation Limits
	// ============================================
	
	/** Maximum length for short description */
	public const SHORT_DESCRIPTION_MAX_LENGTH = 200;
	
	/** Maximum word count for short description */
	public const SHORT_DESCRIPTION_MAX_WORDS = 40;

	// ============================================
	// Rating Constraints
	// ============================================
	
	/** Minimum rating value */
	public const RATING_MIN = 0;
	
	/** Maximum rating value */
	public const RATING_MAX = 5;
	
	/** Rating step increment */
	public const RATING_STEP = 0.1;

	// ============================================
	// UI Constants
	// ============================================
	
	/** Scroll offset for anchor navigation */
	public const SCROLL_OFFSET = 50;
	
	/** Animation duration in milliseconds */
	public const ANIMATION_DURATION = 300;
	
	/** Debounce delay in milliseconds */
	public const DEBOUNCE_DELAY = 300;

	// ============================================
	// Grid Layouts
	// ============================================
	
	/** Number of columns for 2-column grid */
	public const GRID_2_COLUMNS = 2;
	
	/** Number of columns for 3-column grid */
	public const GRID_3_COLUMNS = 3;

	// ============================================
	// Media Upload
	// ============================================
	
	/** Default title for media uploader */
	public const MEDIA_UPLOAD_TITLE = 'Select Image';
	
	/** Text for media upload button */
	public const MEDIA_UPLOAD_BUTTON_TEXT = 'Use This Image';

	// ============================================
	// Form Actions
	// ============================================
	
	/** Action name for saving new product */
	public const ACTION_SAVE_PRODUCT = 'aps_save_product';
	
	/** Action name for updating existing product */
	public const ACTION_UPDATE_PRODUCT = 'aps_update_product';
	
	/** Nonce action for saving */
	public const NONCE_ACTION_SAVE = 'aps_save_product';
	
	/** Nonce action for updating */
	public const NONCE_ACTION_UPDATE = 'aps_update_product';

	// ============================================
	// Post Type
	// ============================================
	
	/** Custom post type for products */
	public const POST_TYPE = 'aps_product';

	// ============================================
	// Taxonomies
	// ============================================
	
	/** Category taxonomy slug */
	public const TAXONOMY_CATEGORY = 'aps_category';
	
	/** Tag taxonomy slug */
	public const TAXONOMY_TAG = 'aps_tag';
	
	/** Ribbon taxonomy slug */
	public const TAXONOMY_RIBBON = 'aps_ribbon';

	// ============================================
	// Meta Keys
	// ============================================
	
	/** Meta key for product logo */
	public const META_LOGO = '_aps_logo';
	
	/** Meta key for brand image */
	public const META_BRAND_IMAGE = '_aps_brand_image';
	
	/** Meta key for affiliate URL */
	public const META_AFFILIATE_URL = '_aps_affiliate_url';
	
	/** Meta key for button name */
	public const META_BUTTON_NAME = '_aps_button_name';
	
	/** Meta key for regular price */
	public const META_PRICE = '_aps_price';
	
	/** Meta key for original price */
	public const META_ORIGINAL_PRICE = '_aps_original_price';
	
	/** Meta key for currency */
	public const META_CURRENCY = '_aps_currency';
	
	/** Meta key for featured status */
	public const META_FEATURED = '_aps_featured';
	
	/** Meta key for rating */
	public const META_RATING = '_aps_rating';
	
	/** Meta key for views count */
	public const META_VIEWS = '_aps_views';
	
	/** Meta key for user count */
	public const META_USER_COUNT = '_aps_user_count';
	
	/** Meta key for reviews count */
	public const META_REVIEWS = '_aps_reviews';
	
	/** Meta key for features list */
	public const META_FEATURES = '_aps_features';
	
	/** Meta key for video URL */
	public const META_VIDEO_URL = '_aps_video_url';
	
	/** Meta key for platform requirements */
	public const META_PLATFORM_REQUIREMENTS = '_aps_platform_requirements';
	
	/** Meta key for version number */
	public const META_VERSION_NUMBER = '_aps_version_number';
	
	/** Meta key for stock status */
	public const META_STOCK_STATUS = '_aps_stock_status';
	
	/** Meta key for SEO title */
	public const META_SEO_TITLE = '_aps_seo_title';
	
	/** Meta key for SEO description */
	public const META_SEO_DESCRIPTION = '_aps_seo_description';

	// ============================================
	// Term Meta Keys
	// ============================================
	
	/** Term meta key for category image */
	public const TERM_META_CATEGORY_IMAGE = '_aps_category_image';
	
	/** Term meta key for category featured status */
	public const TERM_META_CATEGORY_FEATURED = '_aps_category_featured';
	
	/** Term meta key for ribbon color */
	public const TERM_META_RIBBON_COLOR = '_aps_ribbon_color';
	
	/** Term meta key for ribbon background color */
	public const TERM_META_RIBBON_BG_COLOR = '_aps_ribbon_bg_color';
	
	/** Term meta key for ribbon icon */
	public const TERM_META_RIBBON_ICON = '_aps_ribbon_icon';

	// ============================================
	// Default Values
	// ============================================
	
	/** Default currency */
	public const DEFAULT_CURRENCY = 'USD';
	
	/** Default stock status */
	public const DEFAULT_STOCK_STATUS = 'instock';
	
	/** Default featured value */
	public const DEFAULT_FEATURED = false;

	// ============================================
	// Helper Methods
	// ============================================

	/**
	 * Get all meta keys as array
	 * 
	 * @return array All meta keys
	 */
	public static function getAllMetaKeys(): array {
		return [
			self::META_LOGO,
			self::META_BRAND_IMAGE,
			self::META_AFFILIATE_URL,
			self::META_BUTTON_NAME,
			self::META_PRICE,
			self::META_ORIGINAL_PRICE,
			self::META_CURRENCY,
			self::META_FEATURED,
			self::META_RATING,
			self::META_VIEWS,
			self::META_USER_COUNT,
			self::META_REVIEWS,
			self::META_FEATURES,
			self::META_VIDEO_URL,
			self::META_PLATFORM_REQUIREMENTS,
			self::META_VERSION_NUMBER,
			self::META_STOCK_STATUS,
			self::META_SEO_TITLE,
			self::META_SEO_DESCRIPTION,
		];
	}

	/**
	 * Get all taxonomy slugs as array
	 * 
	 * @return array All taxonomy slugs
	 */
	public static function getAllTaxonomies(): array {
		return [
			self::TAXONOMY_CATEGORY,
			self::TAXONOMY_TAG,
			self::TAXONOMY_RIBBON,
		];
	}
}
