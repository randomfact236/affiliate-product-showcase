<?php
/**
 * Term Meta Handler
 *
 * Registers and renders term meta fields for product categories,
 * tags, and ribbons in WordPress admin screens.
 *
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 * @author Development Team
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Plugin\Constants;
use AffiliateProductShowcase\Admin\TermUI;
use AffiliateProductShowcase\Services\ProductService;

/**
 * Term Meta Handler
 *
 * Handles term meta registration and rendering for product taxonomies.
 * Supports custom fields with color pickers, image uploads, and date selectors.
 *
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 * @author Development Team
 */
final class TermMeta {
	/**
	 * Product service
	 *
	 * @var ProductService
	 * @since 1.0.0
	 */
	private ProductService $product_service;

	/**
	 * TermUI instance
	 *
	 * @var TermUI
	 * @since 1.0.0
	 */
	private TermUI $term_ui;

	/**
	 * Constructor
	 *
	 * @param ProductService $product_service Product service instance
	 * @param TermUI $term_ui Term UI instance
	 * @since 1.0.0
	 */
	public function __construct(
		ProductService $product_service,
		TermUI $term_ui
	) {
		$this->product_service = $product_service;
		$this->term_ui = $term_ui;
	}

	/**
	 * Register term meta boxes
	 *
	 * @return void
	 * @since 1.0.0
	 * @action product_category_add_form_fields
	 * @action product_category_edit_form_fields
	 * @action product_category_add_form
	 * @action product_category_edit_form
	 * @action product_tag_add_form_fields
	 * @action product_tag_edit_form_fields
	 * @action product_tag_add_form
	 * @action product_tag_edit_form
	 * @action product_ribbon_add_form_fields
	 * @action product_ribbon_edit_form_fields
	 * @action product_ribbon_add_form
	 * @action product_ribbon_edit_form
	 * @action created_term
	 * @action edited_term
	 * @action deleted_term
	 */
	public function register(): void {
		// Category meta boxes
		add_action( 'product_category_add_form_fields', [ $this, 'add_category_meta_fields' ], 10, 1 );
		add_action( 'product_category_edit_form_fields', [ $this, 'add_category_meta_fields' ], 10, 1 );
		add_action( 'product_category_add_form', [ $this, 'save_category_meta' ], 10, 1 );
		add_action( 'product_category_edit_form', [ $this, 'save_category_meta' ], 10, 1 );

		// Tag meta boxes
		add_action( 'product_tag_add_form_fields', [ $this, 'add_tag_meta_fields' ], 10, 1 );
		add_action( 'product_tag_edit_form_fields', [ $this, 'add_tag_meta_fields' ], 10, 1 );
		add_action( 'product_tag_add_form', [ $this, 'save_tag_meta' ], 10, 1 );
		add_action( 'product_tag_edit_form', [ $this, 'save_tag_meta' ], 10, 1 );

		// Ribbon meta boxes
		add_action( 'product_ribbon_add_form_fields', [ $this, 'add_ribbon_meta_fields' ], 10, 1 );
		add_action( 'product_ribbon_edit_form_fields', [ $this, 'add_ribbon_meta_fields' ], 10, 1 );
		add_action( 'product_ribbon_add_form', [ $this, 'save_ribbon_meta' ], 10, 1 );
		add_action( 'product_ribbon_edit_form', [ $this, 'save_ribbon_meta' ], 10, 1 );

		// Save hooks
		add_action( 'created_' . Constants::TAX_CATEGORY, [ $this, 'save_category_meta' ], 10, 1 );
		add_action( 'edited_' . Constants::TAX_CATEGORY, [ $this, 'save_category_meta' ], 10, 1 );
		add_action( 'created_' . Constants::TAX_TAG, [ $this, 'save_tag_meta' ], 10, 1 );
		add_action( 'edited_' . Constants::TAX_TAG, [ $this, 'save_tag_meta' ], 10, 1 );
		add_action( 'created_' . Constants::TAX_RIBBON, [ $this, 'save_ribbon_meta' ], 10, 1 );
		add_action( 'edited_' . Constants::TAX_RIBBON, [ $this, 'save_ribbon_meta' ], 10, 1 );
	}

	/**
	 * Add category meta fields
	 *
	 * Adds custom meta fields to category add/edit screens.
	 *
	 * @param \WP_Term $term Term object
	 * @return void
	 * @since 1.0.0
	 * @action product_category_add_form_fields
	 * @action product_category_edit_form_fields
	 */
	public function add_category_meta_fields( \WP_Term $term ): void {
		// Only show on category taxonomy
		if ( $term->taxonomy !== Constants::TAX_CATEGORY ) {
			return;
		}

		// Icon field
		add_settings_field(
			'category_icon',
			__( 'Icon', Constants::TEXTDOMAIN ),
			[ $this, 'render_category_icon_field' ],
			Constants::TAX_CATEGORY
		);

		// Color field
		add_settings_field(
			'category_color',
			__( 'Color', Constants::TEXTDOMAIN ),
			[ $this, 'render_category_color_field' ],
			Constants::TAX_CATEGORY
		);

		// Image field
		add_settings_field(
			'category_image',
			__( 'Image', Constants::TEXTDOMAIN ),
			[ $this, 'render_category_image_field' ],
			Constants::TAX_CATEGORY
		);

		// Order field
		add_settings_field(
			'category_order',
			__( 'Order', Constants::TEXTDOMAIN ),
			[ $this, 'render_category_order_field' ],
			Constants::TAX_CATEGORY
		);

		// Featured checkbox
		add_settings_field(
			'category_featured',
			__( 'Featured', Constants::TEXTDOMAIN ),
			[ $this, 'render_category_featured_field' ],
			Constants::TAX_CATEGORY
		);

		// Hide from menu checkbox
		add_settings_field(
			'category_hide_from_menu',
			__( 'Hide from menu', Constants::TEXTDOMAIN ),
			[ $this, 'render_category_hide_menu_field' ],
			Constants::TAX_CATEGORY
		);

		// SEO title field
		add_settings_field(
			'category_seo_title',
			__( 'SEO Title', Constants::TEXTDOMAIN ),
			[ $this, 'render_category_seo_title_field' ],
			Constants::TAX_CATEGORY
		);

		// SEO description field
		add_settings_field(
			'category_seo_description',
			__( 'SEO Description', Constants::TEXTDOMAIN ),
			[ $this, 'render_category_seo_description_field' ],
			Constants::TAX_CATEGORY
		);
	}

	/**
	 * Add tag meta fields
	 *
	 * Adds custom meta fields to tag add/edit screens.
	 *
	 * @param \WP_Term $term Term object
	 * @return void
	 * @since 1.0.0
	 * @action product_tag_add_form_fields
	 * @action product_tag_edit_form_fields
	 */
	public function add_tag_meta_fields( \WP_Term $term ): void {
		// Only show on tag taxonomy
		if ( $term->taxonomy !== Constants::TAX_TAG ) {
			return;
		}

		// Color field
		add_settings_field(
			'tag_color',
			__( 'Color', Constants::TEXTDOMAIN ),
			[ $this, 'render_tag_color_field' ],
			Constants::TAX_TAG
		);

		// Icon field
		add_settings_field(
			'tag_icon',
			__( 'Icon', Constants::TEXTDOMAIN ),
			[ $this, 'render_tag_icon_field' ],
			Constants::TAX_TAG
		);

		// Featured checkbox
		add_settings_field(
			'tag_featured',
			__( 'Featured', Constants::TEXTDOMAIN ),
			[ $this, 'render_tag_featured_field' ],
			Constants::TAX_TAG
		);
	}

	/**
	 * Add ribbon meta fields
	 *
	 * Adds custom meta fields to ribbon add/edit screens.
	 *
	 * @param \WP_Term $term Term object
	 * @return void
	 * @since 1.0.0
	 * @action product_ribbon_add_form_fields
	 * @action product_ribbon_edit_form_fields
	 */
	public function add_ribbon_meta_fields( \WP_Term $term ): void {
		// Only show on ribbon taxonomy
		if ( $term->taxonomy !== Constants::TAX_RIBBON ) {
			return;
		}

		// Text field
		add_settings_field(
			'ribbon_text',
			__( 'Display Text', Constants::TEXTDOMAIN ),
			[ $this, 'render_ribbon_text_field' ],
			Constants::TAX_RIBBON
		);

		// Background color field
		add_settings_field(
			'ribbon_bg_color',
			__( 'Background Color', Constants::TEXTDOMAIN ),
			[ $this, 'render_ribbon_bg_color_field' ],
			Constants::TAX_RIBBON
		);

		// Text color field
		add_settings_field(
			'ribbon_text_color',
			__( 'Text Color', Constants::TEXTDOMAIN ),
			[ $this, 'render_ribbon_text_color_field' ],
			Constants::TAX_RIBBON
		);

		// Position field
		add_settings_field(
			'ribbon_position',
			__( 'Position', Constants::TEXTDOMAIN ),
			[ $this, 'render_ribbon_position_field' ],
			Constants::TAX_RIBBON
		);

		// Style field
		add_settings_field(
			'ribbon_style',
			__( 'Style', Constants::TEXTDOMAIN ),
			[ $this, 'render_ribbon_style_field' ],
			Constants::TAX_RIBBON
		);

		// Icon field
		add_settings_field(
			'ribbon_icon',
			__( 'Icon', Constants::TEXTDOMAIN ),
			[ $this, 'render_ribbon_icon_field' ],
			Constants::TAX_RIBBON
		);

		// Priority field
		add_settings_field(
			'ribbon_priority',
			__( 'Display Priority', Constants::TEXTDOMAIN ),
			[ $this, 'render_ribbon_priority_field' ],
			Constants::TAX_RIBBON
		);

		// Start date field
		add_settings_field(
			'ribbon_start_date',
			__( 'Start Date', Constants::TEXTDOMAIN ),
			[ $this, 'render_ribbon_start_date_field' ],
			Constants::TAX_RIBBON
		);

		// Expiration date field
		add_settings_field(
			'ribbon_expiration_date',
			__( 'Expiration Date', Constants::TEXTDOMAIN ),
			[ $this, 'render_ribbon_expiration_date_field' ],
			Constants::TAX_RIBBON
		);
	}

	/**
	 * Render category icon field
	 *
	 * @param \WP_Term $term Term object
	 * @return void
	 * @since 1.0.0
	 */
	public function render_category_icon_field( \WP_Term $term ): void {
		$icon = get_term_meta( $term->term_id, 'category_icon', true );

		?>
		<tr class="form-field">
			<th scope="row">
				<label for="category-icon"><?php esc_html_e( 'Icon', Constants::TEXTDOMAIN ); ?></label>
			</th>
			<td>
				<input type="text" id="category-icon" name="category_icon" class="regular-text" value="<?php echo esc_attr( $icon ); ?>" placeholder="<?php esc_attr_e( 'SVG icon or emoji', Constants::TEXTDOMAIN ); ?>" />
				<p class="description"><?php esc_html_e( 'Enter SVG icon code or emoji character.', Constants::TEXTDOMAIN ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render category color field
	 *
	 * @param \WP_Term $term Term object
	 * @return void
	 * @since 1.0.0
	 */
	public function render_category_color_field( \WP_Term $term ): void {
		$color = get_term_meta( $term->term_id, 'category_color', true );

		?>
		<tr class="form-field">
			<th scope="row">
				<label for="category-color"><?php esc_html_e( 'Color', Constants::TEXTDOMAIN ); ?></label>
			</th>
			<td>
				<input type="color" id="category-color" name="category_color" class="color-picker" value="<?php echo esc_attr( $color ); ?>" />
				<p class="description"><?php esc_html_e( 'Select a category color for visual identification.', Constants::TEXTDOMAIN ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render category image field
	 *
	 * @param \WP_Term $term Term object
	 * @return void
	 * @since 1.0.0
	 */
	public function render_category_image_field( \WP_Term $term ): void {
		$image_id = get_term_meta( $term->term_id, 'category_image', true );

		?>
		<tr class="form-field">
			<th scope="row">
				<label for="category-image"><?php esc_html_e( 'Image', Constants::TEXTDOMAIN ); ?></label>
			</th>
			<td>
				<input type="hidden" id="category-image-id" name="category_image_id" value="<?php echo absint( $image_id ); ?>" />
				<div class="category-image-preview">
					<?php if ( $image_id ): ?>
						<?php echo wp_get_attachment_image( $image_id, 'thumbnail' ); ?>
					<?php else: ?>
						<div class="no-image"><?php esc_html_e( 'No image', Constants::TEXTDOMAIN ); ?></div>
					<?php endif; ?>
				</div>
				<button type="button" class="button upload-image-button">
					<?php esc_html_e( 'Upload Image', Constants::TEXTDOMAIN ); ?>
				</button>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render category order field
	 *
	 * @param \WP_Term $term Term object
	 * @return void
	 * @since 1.0.0
	 */
	public function render_category_order_field( \WP_Term $term ): void {
		$order = get_term_meta( $term->term_id, 'category_order', true );

		?>
		<tr class="form-field">
			<th scope="row">
				<label for="category-order"><?php esc_html_e( 'Order', Constants::TEXTDOMAIN ); ?></label>
			</th>
			<td>
				<input type="number" id="category-order" name="category_order" class="small-text" value="<?php echo absint( $order ); ?>" min="0" max="9999" />
				<p class="description"><?php esc_html_e( 'Display order in category lists (lower numbers appear first).', Constants::TEXTDOMAIN ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render category featured field
	 *
	 * @param \WP_Term $term Term object
	 * @return void
	 * @since 1.0.0
	 */
	public function render_category_featured_field( \WP_Term $term ): void {
		$featured = (bool) get_term_meta( $term->term_id, 'category_featured', true );

		?>
		<tr class="form-field">
			<th scope="row">
				<label for="category-featured"><?php esc_html_e( 'Featured', Constants::TEXTDOMAIN ); ?></label>
			</th>
			<td>
				<input type="checkbox" id="category-featured" name="category_featured" <?php checked( $featured ); ?> />
				<p class="description"><?php esc_html_e( 'Show in featured product sections and widgets.', Constants::TEXTDOMAIN ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render category hide from menu field
	 *
	 * @param \WP_Term $term Term object
	 * @return void
	 * @since 1.0.0
	 */
	public function render_category_hide_menu_field( \WP_Term $term ): void {
		$hide = (bool) get_term_meta( $term->term_id, 'category_hide_from_menu', true );

		?>
		<tr class="form-field">
			<th scope="row">
				<label for="category-hide-from-menu"><?php esc_html_e( 'Hide from Menu', Constants::TEXTDOMAIN ); ?></label>
			</th>
			<td>
				<input type="checkbox" id="category-hide-from-menu" name="category_hide_from_menu" <?php checked( $hide ); ?> />
				<p class="description"><?php esc_html_e( 'Hide this category from navigation menus.', Constants::TEXTDOMAIN ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render category SEO title field
	 *
	 * @param \WP_Term $term Term object
	 * @return void
	 * @since 1.0.0
	 */
	public function render_category_seo_title_field( \WP_Term $term ): void {
		$seo_title = get_term_meta( $term->term_id, 'category_seo_title', true );

		?>
		<tr class="form-field">
			<th scope="row">
				<label for="category-seo-title"><?php esc_html_e( 'SEO Title', Constants::TEXTDOMAIN ); ?></label>
			</th>
			<td>
				<input type="text" id="category-seo-title" name="category_seo_title" class="regular-text" value="<?php echo esc_attr( $seo_title ); ?>" placeholder="<?php esc_attr_e( 'Custom SEO title (overrides term name)', Constants::TEXTDOMAIN ); ?>" />
				<p class="description"><?php esc_html_e( 'Custom title for SEO purposes. Leave empty to use term name.', Constants::TEXTDOMAIN ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render category SEO description field
	 *
	 * @param \WP_Term $term Term object
	 * @return void
	 * @since 1.0.0
	 */
	public function render_category_seo_description_field( \WP_Term $term ): void {
		$seo_description = get_term_meta( $term->term_id, 'category_seo_description', true );

		?>
		<tr class="form-field">
			<th scope="row">
				<label for="category-seo-description"><?php esc_html_e( 'SEO Description', Constants::TEXTDOMAIN ); ?></label>
			</th>
			<td>
				<textarea id="category-seo-description" name="category_seo_description" rows="3" class="large-text"><?php echo esc_textarea( $seo_description ); ?></textarea>
				<p class="description"><?php esc_html_e( 'Custom SEO description for search engines.', Constants::TEXTDOMAIN ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render tag color field
	 *
	 * @param \WP_Term $term Term object
	 * @return void
	 * @since 1.0.0
	 */
	public function render_tag_color_field( \WP_Term $term ): void {
		$color = get_term_meta( $term->term_id, 'tag_color', true );

		?>
		<tr class="form-field">
			<th scope="row">
				<label for="tag-color"><?php esc_html_e( 'Color', Constants::TEXTDOMAIN ); ?></label>
			</th>
			<td>
				<input type="color" id="tag-color" name="tag_color" class="color-picker" value="<?php echo esc_attr( $color ); ?>" />
				<p class="description"><?php esc_html_e( 'Select a tag color for visual identification.', Constants::TEXTDOMAIN ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render tag icon field
	 *
	 * @param \WP_Term $term Term object
	 * @return void
	 * @since 1.0.0
	 */
	public function render_tag_icon_field( \WP_Term $term ): void {
		$icon = get_term_meta( $term->term_id, 'tag_icon', true );

		?>
		<tr class="form-field">
			<th scope="row">
				<label for="tag-icon"><?php esc_html_e( 'Icon', Constants::TEXTDOMAIN ); ?></label>
			</th>
			<td>
				<input type="text" id="tag-icon" name="tag_icon" class="regular-text" value="<?php echo esc_attr( $icon ); ?>" placeholder="<?php esc_attr_e( 'SVG icon or emoji', Constants::TEXTDOMAIN ); ?>" />
				<p class="description"><?php esc_html_e( 'Enter SVG icon code or emoji character.', Constants::TEXTDOMAIN ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render tag featured field
	 *
	 * @param \WP_Term $term Term object
	 * @return void
	 * @since 1.0.0
	 */
	public function render_tag_featured_field( \WP_Term $term ): void {
		$featured = (bool) get_term_meta( $term->term_id, 'tag_featured', true );

		?>
		<tr class="form-field">
			<th scope="row">
				<label for="tag-featured"><?php esc_html_e( 'Featured', Constants::TEXTDOMAIN ); ?></label>
			</th>
			<td>
				<input type="checkbox" id="tag-featured" name="tag_featured" <?php checked( $featured ); ?> />
				<p class="description"><?php esc_html_e( 'Show in featured product sections and widgets.', Constants::TEXTDOMAIN ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render ribbon text field
	 *
	 * @param \WP_Term $term Term object
	 * @return void
	 * @since 1.0.0
	 */
	public function render_ribbon_text_field( \WP_Term $term ): void {
		$text = get_term_meta( $term->term_id, 'ribbon_text', true );

		?>
		<tr class="form-field">
			<th scope="row">
				<label for="ribbon-text"><?php esc_html_e( 'Display Text', Constants::TEXTDOMAIN ); ?></label>
			</th>
			<td>
				<input type="text" id="ribbon-text" name="ribbon_text" class="regular-text" value="<?php echo esc_attr( $text ); ?>" placeholder="<?php esc_attr_e( 'Best Seller, New, Sale', Constants::TEXTDOMAIN ); ?>" />
				<p class="description"><?php esc_html_e( 'Text to display on product images.', Constants::TEXTDOMAIN ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render ribbon background color field
	 *
	 * @param \WP_Term $term Term object
	 * @return void
	 * @since 1.0.0
	 */
	public function render_ribbon_bg_color_field( \WP_Term $term ): void {
		$bg_color = get_term_meta( $term->term_id, 'ribbon_bg_color', true );

		?>
		<tr class="form-field">
			<th scope="row">
				<label for="ribbon-bg-color"><?php esc_html_e( 'Background Color', Constants::TEXTDOMAIN ); ?></label>
			</th>
			<td>
				<input type="color" id="ribbon-bg-color" name="ribbon_bg_color" class="color-picker" value="<?php echo esc_attr( $bg_color ); ?>" />
				<p class="description"><?php esc_html_e( 'Background color for ribbon badge.', Constants::TEXTDOMAIN ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render ribbon text color field
	 *
	 * @param \WP_Term $term Term object
	 * @return void
	 * @since 1.0.0
	 */
	public function render_ribbon_text_color_field( \WP_Term $term ): void {
		$text_color = get_term_meta( $term->term_id, 'ribbon_text_color', true );

		?>
		<tr class="form-field">
			<th scope="row">
				<label for="ribbon-text-color"><?php esc_html_e( 'Text Color', Constants::TEXTDOMAIN ); ?></label>
			</th>
			<td>
				<input type="color" id="ribbon-text-color" name="ribbon_text_color" class="color-picker" value="<?php echo esc_attr( $text_color ); ?>" />
				<p class="description"><?php esc_html_e( 'Text color for ribbon.', Constants::TEXTDOMAIN ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render ribbon position field
	 *
	 * @param \WP_Term $term Term object
	 * @return void
	 * @since 1.0.0
	 */
	public function render_ribbon_position_field( \WP_Term $term ): void {
		$position = get_term_meta( $term->term_id, 'ribbon_position', true );

		?>
		<tr class="form-field">
			<th scope="row">
				<label for="ribbon-position"><?php esc_html_e( 'Position', Constants::TEXTDOMAIN ); ?></label>
			</th>
			<td>
				<select id="ribbon-position" name="ribbon_position" class="postform">
					<option value="top-left" <?php selected( $position, 'top-left' ); ?>><?php esc_html_e( 'Top Left', Constants::TEXTDOMAIN ); ?></option>
					<option value="top-right" <?php selected( $position, 'top-right' ); ?>><?php esc_html_e( 'Top Right', Constants::TEXTDOMAIN ); ?></option>
					<option value="bottom-left" <?php selected( $position, 'bottom-left' ); ?>><?php esc_html_e( 'Bottom Left', Constants::TEXTDOMAIN ); ?></option>
					<option value="bottom-right" <?php selected( $position, 'bottom-right' ); ?>><?php esc_html_e( 'Bottom Right', Constants::TEXTDOMAIN ); ?></option>
				</select>
				<p class="description"><?php esc_html_e( 'Position on product image.', Constants::TEXTDOMAIN ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render ribbon style field
	 *
	 * @param \WP_Term $term Term object
	 * @return void
	 * @since 1.0.0
	 */
	public function render_ribbon_style_field( \WP_Term $term ): void {
		$style = get_term_meta( $term->term_id, 'ribbon_style', true );

		?>
		<tr class="form-field">
			<th scope="row">
				<label for="ribbon-style"><?php esc_html_e( 'Style', Constants::TEXTDOMAIN ); ?></label>
			</th>
			<td>
				<select id="ribbon-style" name="ribbon_style" class="postform">
					<option value="badge" <?php selected( $style, 'badge' ); ?>><?php esc_html_e( 'Badge', Constants::TEXTDOMAIN ); ?></option>
					<option value="corner" <?php selected( $style, 'corner' ); ?>><?php esc_html_e( 'Corner', Constants::TEXTDOMAIN ); ?></option>
					<option value="banner" <?php selected( $style, 'banner' ); ?>><?php esc_html_e( 'Banner', Constants::TEXTDOMAIN ); ?></option>
					<option value="diagonal" <?php selected( $style, 'diagonal' ); ?>><?php esc_html_e( 'Diagonal', Constants::TEXTDOMAIN ); ?></option>
				</select>
				<p class="description"><?php esc_html_e( 'Visual style of ribbon.', Constants::TEXTDOMAIN ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render ribbon icon field
	 *
	 * @param \WP_Term $term Term object
	 * @return void
	 * @since 1.0.0
	 */
	public function render_ribbon_icon_field( \WP_Term $term ): void {
		$icon = get_term_meta( $term->term_id, 'ribbon_icon', true );

		?>
		<tr class="form-field">
			<th scope="row">
				<label for="ribbon-icon"><?php esc_html_e( 'Icon', Constants::TEXTDOMAIN ); ?></label>
			</th>
			<td>
				<input type="text" id="ribbon-icon" name="ribbon_icon" class="regular-text" value="<?php echo esc_attr( $icon ); ?>" placeholder="<?php esc_attr_e( 'SVG icon or Heroicon name', Constants::TEXTDOMAIN ); ?>" />
				<p class="description"><?php esc_html_e( 'SVG icon path or Heroicon name.', Constants::TEXTDOMAIN ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render ribbon priority field
	 *
	 * @param \WP_Term $term Term object
	 * @return void
	 * @since 1.0.0
	 */
	public function render_ribbon_priority_field( \WP_Term $term ): void {
		$priority = get_term_meta( $term->term_id, 'ribbon_priority', true );

		?>
		<tr class="form-field">
			<th scope="row">
				<label for="ribbon-priority"><?php esc_html_e( 'Priority', Constants::TEXTDOMAIN ); ?></label>
			</th>
			<td>
				<input type="number" id="ribbon-priority" name="ribbon_priority" class="small-text" value="<?php echo absint( $priority ); ?>" min="0" max="999" />
				<p class="description"><?php esc_html_e( 'Display priority (higher numbers appear first).', Constants::TEXTDOMAIN ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render ribbon start date field
	 *
	 * @param \WP_Term $term Term object
	 * @return void
	 * @since 1.0.0
	 */
	public function render_ribbon_start_date_field( \WP_Term $term ): void {
		$start_date = get_term_meta( $term->term_id, 'ribbon_start_date', true );

		?>
		<tr class="form-field">
			<th scope="row">
				<label for="ribbon-start-date"><?php esc_html_e( 'Start Date', Constants::TEXTDOMAIN ); ?></label>
			</th>
			<td>
				<input type="datetime-local" id="ribbon-start-date" name="ribbon_start_date" value="<?php echo esc_attr( $start_date ); ?>" />
				<p class="description"><?php esc_html_e( 'Scheduled start date for ribbon display.', Constants::TEXTDOMAIN ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render ribbon expiration date field
	 *
	 * @param \WP_Term $term Term object
	 * @return void
	 * @since 1.0.0
	 */
	public function render_ribbon_expiration_date_field( \WP_Term $term ): void {
		$expiration_date = get_term_meta( $term->term_id, 'ribbon_expiration_date', true );

		?>
		<tr class="form-field">
			<th scope="row">
				<label for="ribbon-expiration-date"><?php esc_html_e( 'Expiration Date', Constants::TEXTDOMAIN ); ?></label>
			</th>
			<td>
				<input type="datetime-local" id="ribbon-expiration-date" name="ribbon_expiration_date" value="<?php echo esc_attr( $expiration_date ); ?>" />
				<p class="description"><?php esc_html_e( 'Expiration date for scheduled ribbons.', Constants::TEXTDOMAIN ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Save category meta
	 *
	 * Saves category meta fields when term is created or updated.
	 *
	 * @param int $term_id Term ID
	 * @return void
	 * @since 1.0.0
	 * @action created_product_category
	 * @action edited_product_category
	 */
	public function save_category_meta( int $term_id ): void {
		// Sanitize and save category meta
		if ( isset( $_POST['category_icon'] ) ) {
			update_term_meta( $term_id, 'category_icon', sanitize_text_field( $_POST['category_icon'] ) );
		}
		if ( isset( $_POST['category_color'] ) ) {
			update_term_meta( $term_id, 'category_color', sanitize_hex_color( $_POST['category_color'] ) );
		}
		if ( isset( $_POST['category_image'] ) ) {
			update_term_meta( $term_id, 'category_image', absint( $_POST['category_image'] ) );
		}
		if ( isset( $_POST['category_order'] ) ) {
			update_term_meta( $term_id, 'category_order', absint( $_POST['category_order'] ) );
		}
		if ( isset( $_POST['category_featured'] ) ) {
			update_term_meta( $term_id, 'category_featured', rest_sanitize_boolean( $_POST['category_featured'] ) ? '1' : '0' );
		}
		if ( isset( $_POST['category_hide_from_menu'] ) ) {
			update_term_meta( $term_id, 'category_hide_from_menu', rest_sanitize_boolean( $_POST['category_hide_from_menu'] ) ? '1' : '0' );
		}
		if ( isset( $_POST['category_seo_title'] ) ) {
			update_term_meta( $term_id, 'category_seo_title', sanitize_text_field( $_POST['category_seo_title'] ) );
		}
		if ( isset( $_POST['category_seo_description'] ) ) {
			update_term_meta( $term_id, 'category_seo_description', sanitize_textarea_field( $_POST['category_seo_description'] ) );
		}
	}

	/**
	 * Save tag meta
	 *
	 * Saves tag meta fields when term is created or updated.
	 *
	 * @param int $term_id Term ID
	 * @return void
	 * @since 1.0.0
	 * @action created_product_tag
	 * @action edited_product_tag
	 */
	public function save_tag_meta( int $term_id ): void {
		// Sanitize and save tag meta
		if ( isset( $_POST['tag_color'] ) ) {
			update_term_meta( $term_id, 'tag_color', sanitize_hex_color( $_POST['tag_color'] ) );
		}
		if ( isset( $_POST['tag_icon'] ) ) {
			update_term_meta( $term_id, 'tag_icon', sanitize_text_field( $_POST['tag_icon'] ) );
		}
		if ( isset( $_POST['tag_featured'] ) ) {
			update_term_meta( $term_id, 'tag_featured', rest_sanitize_boolean( $_POST['tag_featured'] ) ? '1' : '0' );
		}
	}

	/**
	 * Save ribbon meta
	 *
	 * Saves ribbon meta fields when term is created or updated.
	 *
	 * @param int $term_id Term ID
	 * @return void
	 * @since 1.0.0
	 * @action created_product_ribbon
	 * @action edited_product_ribbon
	 */
	public function save_ribbon_meta( int $term_id ): void {
		// Sanitize and save ribbon meta
		if ( isset( $_POST['ribbon_text'] ) ) {
			update_term_meta( $term_id, 'ribbon_text', sanitize_text_field( $_POST['ribbon_text'] ) );
		}
		if ( isset( $_POST['ribbon_bg_color'] ) ) {
			update_term_meta( $term_id, 'ribbon_bg_color', sanitize_hex_color( $_POST['ribbon_bg_color'] ) );
		}
		if ( isset( $_POST['ribbon_text_color'] ) ) {
			update_term_meta( $term_id, 'ribbon_text_color', sanitize_hex_color( $_POST['ribbon_text_color'] ) );
		}
		if ( isset( $_POST['ribbon_position'] ) ) {
			$valid_positions = [ 'top-left', 'top-right', 'bottom-left', 'bottom-right' ];
			$position = in_array( $_POST['ribbon_position'], $valid_positions ) ? $_POST['ribbon_position'] : 'top-left';
			update_term_meta( $term_id, 'ribbon_position', $position );
		}
		if ( isset( $_POST['ribbon_style'] ) ) {
			$valid_styles = [ 'badge', 'corner', 'banner', 'diagonal' ];
			$style = in_array( $_POST['ribbon_style'], $valid_styles ) ? $_POST['ribbon_style'] : 'badge';
			update_term_meta( $term_id, 'ribbon_style', $style );
		}
		if ( isset( $_POST['ribbon_icon'] ) ) {
			update_term_meta( $term_id, 'ribbon_icon', sanitize_text_field( $_POST['ribbon_icon'] ) );
		}
		if ( isset( $_POST['ribbon_priority'] ) ) {
			update_term_meta( $term_id, 'ribbon_priority', absint( $_POST['ribbon_priority'] ) );
		}
		if ( isset( $_POST['ribbon_start_date'] ) ) {
			update_term_meta( $term_id, 'ribbon_start_date', sanitize_text_field( $_POST['ribbon_start_date'] ) );
		}
		if ( isset( $_POST['ribbon_expiration_date'] ) ) {
			update_term_meta( $term_id, 'ribbon_expiration_date', sanitize_text_field( $_POST['ribbon_expiration_date'] ) );
		}
	}
}
