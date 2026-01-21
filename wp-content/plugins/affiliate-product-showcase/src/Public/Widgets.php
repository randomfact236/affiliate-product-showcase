<?php
/**
 * Widgets
 *
 * Registers WordPress widgets for displaying affiliate products,
 * categories, tags, and ribbons in sidebars.
 *
 * @package AffiliateProductShowcase\Public
 * @since 1.0.0
 * @author Development Team
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Public;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Plugin\Constants;
use AffiliateProductShowcase\Services\ProductService;
use AffiliateProductShowcase\Services\AffiliateService;
use AffiliateProductShowcase\Repositories\SettingsRepository;
use WP_Widget;

/**
 * Tags Widget
 *
 * Displays product tags in tag cloud format.
 * Supports customization of display options.
 *
 * @package AffiliateProductShowcase\Public
 * @since 1.0.0
 * @author Development Team
 */
final class TagsWidget extends WP_Widget {
	/**
	 * Widget base ID
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public const ID = 'affiliate_showcase_tags';

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct(
			self::ID,
			__( 'Product Tags', Constants::TEXTDOMAIN ),
			[
				'description' => __( 'Display product tags in tag cloud format with customization options.', Constants::TEXTDOMAIN ),
				'customize_selective_refresh' => true,
			]
		);
	}

	/**
	 * Widget form
	 *
	 * @param array<string, mixed> $instance Current widget instance settings
	 * @return void
	 * @since 1.0.0
	 *
	 * @method WP_Widget::form
	 */
	public function form( $instance ) {
		$limit = isset( $instance['limit'] ) ? absint( $instance['limit'] ) : 20;
		$orderby = isset( $instance['orderby'] ) ? esc_attr( $instance['orderby'] ) : 'count';
		$order = isset( $instance['order'] ) ? esc_attr( $instance['order'] ) : 'DESC';
		$smallest = isset( $instance['smallest'] ) ? absint( $instance['smallest'] ) : 8;
		$largest = isset( $instance['largest'] ) ? absint( $instance['largest'] ) : 32;
		$unit = isset( $instance['unit'] ) ? esc_attr( $instance['unit'] ) : 'pt';
		$show_count = isset( $instance['show_count'] ) ? rest_sanitize_boolean( $instance['show_count'] ) : true;
		$empty_message = isset( $instance['empty_message'] ) ? esc_textarea_field( $instance['empty_message'] ) : __( 'No tags found', Constants::TEXTDOMAIN );
		$taxonomy = isset( $instance['taxonomy'] ) ? esc_attr( $instance['taxonomy'] ) : Constants::TAX_TAG;

		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
				<?php esc_html_e( 'Title', Constants::TEXTDOMAIN ); ?>:
			</label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ?? '' ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>">
				<?php esc_html_e( 'Number of tags', Constants::TEXTDOMAIN ); ?>:
			</label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'limit' ) ); ?>" type="number" min="1" max="50" value="<?php echo esc_attr( $limit ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>">
				<?php esc_html_e( 'Order by', Constants::TEXTDOMAIN ); ?>:
			</label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'orderby' ) ); ?>">
				<option value="name" <?php selected( $orderby, 'name' ); ?>><?php esc_html_e( 'Name', Constants::TEXTDOMAIN ); ?></option>
				<option value="count" <?php selected( $orderby, 'count' ); ?>><?php esc_html_e( 'Product count', Constants::TEXTDOMAIN ); ?></option>
				<option value="id" <?php selected( $orderby, 'id' ); ?>><?php esc_html_e( 'ID', Constants::TEXTDOMAIN ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'order' ) ); ?>">
				<?php esc_html_e( 'Order', Constants::TEXTDOMAIN ); ?>:
			</label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'order' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'order' ) ); ?>">
				<option value="ASC" <?php selected( $order, 'ASC' ); ?>><?php esc_html_e( 'Ascending', Constants::TEXTDOMAIN ); ?></option>
				<option value="DESC" <?php selected( $order, 'DESC' ); ?>><?php esc_html_e( 'Descending', Constants::TEXTDOMAIN ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'smallest' ) ); ?>">
				<?php esc_html_e( 'Minimum font size', Constants::TEXTDOMAIN ); ?>:
			</label>
			<input class="smallfat" id="<?php echo esc_attr( $this->get_field_id( 'smallest' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'smallest' ) ); ?>" type="number" min="6" max="24" value="<?php echo esc_attr( $smallest ); ?>" /> pt
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'largest' ) ); ?>">
				<?php esc_html_e( 'Maximum font size', Constants::TEXTDOMAIN ); ?>:
			</label>
			<input class="smallfat" id="<?php echo esc_attr( $this->get_field_id( 'largest' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'largest' ) ); ?>" type="number" min="6" max="96" value="<?php echo esc_attr( $largest ); ?>" /> pt
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'unit' ) ); ?>">
				<?php esc_html_e( 'Unit', Constants::TEXTDOMAIN ); ?>:
			</label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'unit' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'unit' ) ); ?>">
				<option value="pt" <?php selected( $unit, 'pt' ); ?>><?php esc_html_e( 'Points', Constants::TEXTDOMAIN ); ?></option>
				<option value="px" <?php selected( $unit, 'px' ); ?>><?php esc_html_e( 'Pixels', Constants::TEXTDOMAIN ); ?></option>
				<option value="em" <?php selected( $unit, 'em' ); ?>><?php esc_html_e( 'Ems', Constants::TEXTDOMAIN ); ?></option>
				<option value="rem" <?php selected( $unit, 'rem' ); ?>><?php esc_html_e( 'Rems', Constants::TEXTDOMAIN ); ?></option>
			</select>
		</p>
		<p>
			<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_count' ) ); ?>" <?php checked( $show_count ); ?> />
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_count' ) ); ?>">
				<?php esc_html_e( 'Show tag count', Constants::TEXTDOMAIN ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'empty_message' ) ); ?>">
				<?php esc_html_e( 'Empty message', Constants::TEXTDOMAIN ); ?>:
			</label>
			<textarea class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'empty_message' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'empty_message' ) ); ?>"><?php echo esc_textarea( $empty_message ); ?></textarea>
		</p>
		<?php
	}

	/**
	 * Update widget instance
	 *
	 * @param array<string, mixed> $new_instance New widget instance settings
	 * @param array<string, mixed> $old_instance Old widget instance settings
	 * @return array<string, mixed> Updated instance
	 * @since 1.0.0
	 *
	 * @method WP_Widget::update
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = [];
		$instance['title'] = sanitize_text_field( $new_instance['title'] ?? '' );
		$instance['limit'] = absint( $new_instance['limit'] ?? 20 );
		$instance['limit'] = max( 1, min( 100, $instance['limit'] ) );
		$instance['orderby'] = in_array( $new_instance['orderby'] ?? '', [ 'name', 'count', 'id' ] ) ? $new_instance['orderby'] : 'name';
		$instance['order'] = in_array( $new_instance['order'] ?? '', [ 'ASC', 'DESC' ] ) ? $new_instance['order'] : 'DESC';
		$instance['smallest'] = absint( $new_instance['smallest'] ?? 8 );
		$instance['smallest'] = max( 6, min( 24, $instance['smallest'] ) );
		$instance['largest'] = absint( $new_instance['largest'] ?? 32 );
		$instance['largest'] = max( 12, min( 96, $instance['largest'] ) );
		$instance['unit'] = in_array( $new_instance['unit'] ?? '', [ 'pt', 'px', 'em', 'rem' ] ) ? $new_instance['unit'] : 'pt';
		$instance['show_count'] = rest_sanitize_boolean( $new_instance['show_count'] ?? true );
		$instance['empty_message'] = sanitize_textarea_field( $new_instance['empty_message'] ?? '' );
		$instance['taxonomy'] = esc_attr( $new_instance['taxonomy'] ?? Constants::TAX_TAG );

		return $instance;
	}

	/**
	 * Render widget
	 *
	 * @param array<string, mixed> $args Widget display arguments
	 * @param array<string, mixed> $instance Current widget instance settings
	 * @return void
	 * @since 1.0.0
	 *
	 * @method WP_Widget::widget
	 */
	public function widget( $args, $instance ) {
		$atts = [
			'limit'         => intval( $instance['limit'] ?? 20 ),
			'orderby'       => $instance['orderby'] ?? 'count',
			'order'         => $instance['order'] ?? 'DESC',
			'smallest'      => absint( $instance['smallest'] ?? 8 ),
			'largest'       => absint( $instance['largest'] ?? 32 ),
			'unit'          => $instance['unit'] ?? 'pt',
			'show_count'    => rest_sanitize_boolean( $instance['show_count'] ?? true ),
			'empty_message' => $instance['empty_message'] ?? __( 'No tags found', Constants::TEXTDOMAIN ),
			'taxonomy'      => esc_attr( $instance['taxonomy'] ?? Constants::TAX_TAG ),
		];

		$term_args = [
			'taxonomy'   => $atts['taxonomy'] ?? Constants::TAX_TAG,
			'orderby'    => $atts['orderby'],
			'order'      => $atts['order'],
			'number'     => intval( $atts['limit'] ),
			'hide_empty' => false,
		];

		$tags = get_terms( $term_args );

		ob_start();
		echo $args['before_widget'];

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Product Tags', Constants::TEXTDOMAIN ) : $instance['title'], $instance, $this->id_base );

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		echo '<div class="aps-tags-cloud">';

		if ( ! empty( $tags ) && ! is_wp_error( $tags ) ) {
			$min_count = PHP_INT_MAX;
			$max_count = 0;

			foreach ( $tags as $tag ) {
				$count = absint( $tag->count );
				$min_count = min( $min_count, $count );
				$max_count = max( $max_count, $count );

				$font_size = 0.8 + ( ( $count - $min_count ) / ( $max_count - $min_count ) ) * 1.4;
				$font_size = min( 2.5, max( $font_size, 0.8 ) );

				if ( $atts['unit'] === 'pt' ) {
					$font_size_px = number_format( $font_size, 1 );
				} elseif ( $atts['unit'] === 'px' ) {
					$font_size_px = $font_size * 16;
				} elseif ( $atts['unit'] === 'em' ) {
					$font_size_px = $font_size * 16;
				} else {
					$font_size_px = $font_size * 16;
				}

				$color = get_term_meta( $tag->term_id, 'tag_color', true );
				$link = get_term_link( $tag );

				$color_style = $color ? 'color: ' . esc_attr( $color ) . ';' : '';
				$font_style = 'font-size: ' . number_format( $font_size_px, 2 ) . 'px;';

				echo '<span class="aps-tag-link" style="' . esc_attr( $font_style . $color_style ) . '" rel="nofollow">';
				echo esc_html( $tag->name );
				echo '</span>';

				if ( $atts['show_count'] ) {
					echo '<span class="aps-tag-count">(' . absint( $count ) . ')</span>';
				}
			}
		}

		echo '</div>';

		if ( empty( $tags ) || is_wp_error( $tags ) ) {
			echo '<p class="aps-widget-empty-message">' . esc_html( $atts['empty_message'] ) . '</p>';
		}

		echo $args['after_widget'];
	}
}

/**
 * Featured Widget
 *
 * Displays featured products in sidebar.
 * Supports customization of display options.
 *
 * @package AffiliateProductShowcase\Public
 * @since 1.0.0
 * @author Development Team
 */
final class FeaturedWidget extends WP_Widget {
	/**
	 * Widget base ID
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public const ID = 'affiliate_showcase_featured';

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct(
			self::ID,
			__( 'Featured Products', Constants::TEXTDOMAIN ),
			[
				'description' => __( 'Display featured products in sidebar with customization options.', Constants::TEXTDOMAIN ),
				'customize_selective_refresh' => true,
			]
		);
	}

	/**
	 * Widget form
	 *
	 * @param array<string, mixed> $instance Current widget instance settings
	 * @return void
	 * @since 1.0.0
	 *
	 * @method WP_Widget::form
	 */
	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$limit = isset( $instance['limit'] ) ? absint( $instance['limit'] ) : 6;
		$columns = isset( $instance['columns'] ) ? absint( $instance['columns'] ) : 3;
		$template = isset( $instance['template'] ) ? esc_attr( $instance['template'] ) : 'grid';
		$show_image = isset( $instance['show_image'] ) ? rest_sanitize_boolean( $instance['show_image'] ) : true;
		$show_price = isset( $instance['show_price'] ) ? rest_sanitize_boolean( $instance['show_price'] ) : true;
		$show_features = isset( $instance['show_features'] ) ? rest_sanitize_boolean( $instance['show_features'] ) : true;
		$show_rating = isset( $instance['show_rating'] ) ? rest_sanitize_boolean( $instance['show_rating'] ) : true;
		$show_cta = isset( $instance['show_cta'] ) ? rest_sanitize_boolean( $instance['show_cta'] ) : true;
		$cta_text = isset( $instance['cta_text'] ) ? sanitize_text_field( $instance['cta_text'] ) : __( 'View Deal', Constants::TEXTDOMAIN );

		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
				<?php esc_html_e( 'Title', Constants::TEXTDOMAIN ); ?>:
			</label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>">
				<?php esc_html_e( 'Number of products', Constants::TEXTDOMAIN ); ?>:
			</label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'limit' ) ); ?>" type="number" min="1" max="20" value="<?php echo esc_attr( $limit ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'columns' ) ); ?>">
				<?php esc_html_e( 'Columns', Constants::TEXTDOMAIN ); ?>:
			</label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'columns' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'columns' ) ); ?>">
				<option value="1" <?php selected( $columns, '1' ); ?>><?php esc_html_e( '1 Column', Constants::TEXTDOMAIN ); ?></option>
				<option value="2" <?php selected( $columns, '2' ); ?>><?php esc_html_e( '2 Columns', Constants::TEXTDOMAIN ); ?></option>
				<option value="3" <?php selected( $columns, '3' ); ?>><?php esc_html_e( '3 Columns', Constants::TEXTDOMAIN ); ?></option>
				<option value="4" <?php selected( $columns, '4' ); ?>><?php esc_html_e( '4 Columns', Constants::TEXTDOMAIN ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'template' ) ); ?>">
				<?php esc_html_e( 'Template', Constants::TEXTDOMAIN ); ?>:
			</label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'template' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'template' ) ); ?>">
				<option value="grid" <?php selected( $template, 'grid' ); ?>><?php esc_html_e( 'Grid', Constants::TEXTDOMAIN ); ?></option>
				<option value="list" <?php selected( $template, 'list' ); ?>><?php esc_html_e( 'List', Constants::TEXTDOMAIN ); ?></option>
				<option value="table" <?php selected( $template, 'table' ); ?>><?php esc_html_e( 'Table', Constants::TEXTDOMAIN ); ?></option>
			</select>
		</p>
		<p>
			<label>
				<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_image' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_image' ) ); ?>" <?php checked( $show_image ); ?> />
				<?php esc_html_e( 'Show product image', Constants::TEXTDOMAIN ); ?>
			</label>
		</p>
		<p>
			<label>
				<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_price' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_price' ) ); ?>" <?php checked( $show_price ); ?> />
				<?php esc_html_e( 'Show price', Constants::TEXTDOMAIN ); ?>
			</label>
		</p>
		<p>
			<label>
				<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_features' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_features' ) ); ?>" <?php checked( $show_features ); ?> />
				<?php esc_html_e( 'Show product features', Constants::TEXTDOMAIN ); ?>
			</label>
		</p>
		<p>
			<label>
				<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_rating' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_rating' ) ); ?>" <?php checked( $show_rating ); ?> />
				<?php esc_html_e( 'Show rating', Constants::TEXTDOMAIN ); ?>
			</label>
		</p>
		<p>
			<label>
				<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_cta' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_cta' ) ); ?>" <?php checked( $show_cta ); ?> />
				<?php esc_html_e( 'Show call-to-action button', Constants::TEXTDOMAIN ); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'cta_text' ) ); ?>">
				<?php esc_html_e( 'Call-to-action button text', Constants::TEXTDOMAIN ); ?>:
			</label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'cta_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'cta_text' ) ); ?>" type="text" value="<?php echo esc_attr( $cta_text ); ?>" />
		</p>
		<?php
	}

	/**
	 * Update widget instance
	 *
	 * @param array<string, mixed> $new_instance New widget instance settings
	 * @param array<string, mixed> $old_instance Old widget instance settings
	 * @return array<string, mixed> Updated instance
	 * @since 1.0.0
	 *
	 * @method WP_Widget::update
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = [];
		$instance['title'] = sanitize_text_field( $new_instance['title'] ?? '' );
		$instance['limit'] = absint( $new_instance['limit'] ?? 6 );
		$instance['limit'] = max( 1, min( 20, $instance['limit'] ) );
		$instance['columns'] = absint( $new_instance['columns'] ?? 3 );
		$instance['columns'] = max( 1, min( 4, $instance['columns'] ) );
		$instance['template'] = in_array( $new_instance['template'] ?? '', [ 'grid', 'list', 'table' ] ) ? $new_instance['template'] : 'grid';
		$instance['show_image'] = rest_sanitize_boolean( $new_instance['show_image'] ?? true );
		$instance['show_price'] = rest_sanitize_boolean( $new_instance['show_price'] ?? true );
		$instance['show_features'] = rest_sanitize_boolean( $new_instance['show_features'] ?? true );
		$instance['show_rating'] = rest_sanitize_boolean( $new_instance['show_rating'] ?? true );
		$instance['show_cta'] = rest_sanitize_boolean( $new_instance['show_cta'] ?? true );
		$instance['cta_text'] = sanitize_text_field( $new_instance['cta_text'] ?? '' );

		return $instance;
	}

	/**
	 * Render widget
	 *
	 * @param array<string, mixed> $args Widget display arguments
	 * @param array<string, mixed> $instance Current widget instance settings
	 * @return void
	 * @since 1.0.0
	 *
	 * @method WP_Widget::widget
	 */
	public function widget( $args, $instance ) {
		$atts = [
			'featured'      => true,
			'limit'        => intval( $instance['limit'] ?? 6 ),
			'columns'      => absint( $instance['columns'] ?? 3 ),
			'template'      => $instance['template'] ?? 'grid',
			'show_image'    => rest_sanitize_boolean( $instance['show_image'] ?? true ),
			'show_price'    => rest_sanitize_boolean( $instance['show_price'] ?? true ),
			'show_features' => rest_sanitize_boolean( $instance['show_features'] ?? true ),
			'show_rating'   => rest_sanitize_boolean( $instance['show_rating'] ?? true ),
			'show_cta'      => rest_sanitize_boolean( $instance['show_cta'] ?? true ),
			'cta_text'      => $instance['cta_text'] ?? __( 'View Deal', Constants::TEXTDOMAIN ),
		];

		$products_args = [
			'post_type'      => Constants::CPT_PRODUCT,
			'post_status'    => 'publish',
			'posts_per_page' => $atts['limit'],
			'paged'         => 1,
			'meta_query'    => [
				[
					'key'     => 'product_featured',
					'value'   => '1',
					'compare' => '=',
					'type'    => 'NUMERIC',
				],
			],
			's'              => [
				'date'       => 'DESC',
				'menu_order' => 'DESC',
			],
		];

		$products = get_posts( $products_args );

		ob_start();
		echo $args['before_widget'];

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Featured Products', Constants::TEXTDOMAIN ) : $instance['title'], $instance, $this->id_base );

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		$template_class = 'aps-products-template aps-grid-' . $atts['columns'] . '-cols';
		$template_class .= $atts['template'] === 'list' ? ' aps-products-list' : ' aps-products-grid';
		$template_class .= $atts['template'] === 'table' ? ' aps-products-table' : '';

		echo '<div class="' . esc_attr( $template_class ) . '">';

		if ( ! empty( $products ) ) {
			foreach ( $products as $product ) {
				$this->render_product( $product, $atts );
			}
		}

		echo '</div>';

		if ( empty( $products ) ) {
			echo '<p class="aps-widget-empty-message">' . esc_html__( 'No featured products found', Constants::TEXTDOMAIN ) . '</p>';
		}

		echo $args['after_widget'];
	}

	/**
	 * Render product card
	 *
	 * @param \WP_Post $product Post object
	 * @param array<string, mixed> $atts Display attributes
	 * @return void
	 * @since 1.0.0
	 */
	private function render_product( \WP_Post $product, array $atts ): void {
		$product_id = $product->ID;
		$title = get_the_title( $product );
		$price = get_post_meta( $product_id, 'product_price', true );
		$sale_price = get_post_meta( $product_id, 'product_sale_price', true );
		$rating = get_post_meta( $product_id, 'product_rating', true );
		$features = get_post_meta( $product_id, 'product_features', true );
		$affiliate_url = get_post_meta( $product_id, 'product_affiliate_url', true );

		if ( $sale_price && floatval( $sale_price ) > 0 ) {
			$price_html = '<span class="aps-price-original">' . number_format( floatval( $price ), 2 ) . '</span> ';
			$price_html .= '<span class="aps-price-sale">' . number_format( floatval( $sale_price ), 2 ) . '</span>';
		} else {
			$price_html = '<span class="aps-price-current">' . number_format( floatval( $price ), 2 ) . '</span>';
		}

		echo '<div class="aps-widget-product-item">';

		if ( $atts['show_image'] && has_post_thumbnail( $product ) ) {
			echo '<div class="aps-widget-product-image">';
			echo '<a href="' . esc_url( $affiliate_url ) . '" rel="nofollow sponsored" target="_blank">';
			the_post_thumbnail( $product, 'medium', [ 'loading' => 'lazy' ] );
			echo '</a>';
			echo '</div>';
		}

		echo '<div class="aps-widget-product-content">';

		if ( $atts['show_features'] && ! empty( $features ) ) {
			$features_array = is_array( $features ) ? $features : [ $features ];
			echo '<ul class="aps-widget-product-features">';
			foreach ( array_slice( $features_array, 0, 5 ) as $index => $feature ) {
				echo '<li class="aps-widget-feature-item">' . esc_html( $feature ) . '</li>';
			}
			echo '</ul>';
		}

		echo '<h3 class="aps-widget-product-title"><a href="' . esc_url( $affiliate_url ) . '" rel="nofollow sponsored" target="_blank">' . esc_html( $title ) . '</a></h3>';

		if ( $atts['show_rating'] && $rating ) {
			echo '<div class="aps-widget-product-rating">';
			echo '<span class="aps-stars" style="--rating: ' . esc_attr( $rating ) . ';">★★★★★</span>';
			echo '<span class="aps-rating-value">' . number_format( $rating, 1 ) . '</span>';
			echo '</div>';
		}

		if ( $atts['show_price'] ) {
			echo '<div class="aps-widget-product-price">' . $price_html . '</div>';
		}

		if ( $atts['show_cta'] && $affiliate_url ) {
			echo '<div class="aps-widget-product-cta">';
			$cta_text = $atts['cta_text'] ?? __( 'View Deal', Constants::TEXTDOMAIN );
			echo '<a href="' . esc_url( $affiliate_url ) . '" class="aps-cta-button" rel="nofollow sponsored" target="_blank">' . esc_html( $cta_text ) . '</a>';
			echo '</div>';
		}

		echo '</div>';

		echo '</div>';
	}
}

/**
 * Widgets Register Class
 *
 * Registers all plugin widgets with WordPress.
 *
 * @package AffiliateProductShowcase\Public
 * @since 1.0.0
 * @author Development Team
 */
final class Widgets {
	/**
	 * Register widgets
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 * @action widgets_init
	 */
	public function register(): void {
		register_widget( CategoriesWidget::class );
		register_widget( TagsWidget::class );
		register_widget( FeaturedWidget::class );
	}
}
