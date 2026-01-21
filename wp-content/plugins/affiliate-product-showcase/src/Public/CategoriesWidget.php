<?php
/**
 * Categories Widget
 *
 * Displays product categories in sidebar.
 * Supports customization of display options.
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
use AffiliateProductShowcase\Repositories\SettingsRepository;
use WP_Widget;

/**
 * Categories Widget
 *
 * Displays product categories in sidebar with customization options.
 *
 * @package AffiliateProductShowcase\Public
 * @since 1.0.0
 * @author Development Team
 */
final class CategoriesWidget extends WP_Widget {
	/**
	 * Widget base ID
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public const ID = 'affiliate_showcase_categories';

	/**
	 * Constructor
	 *
	 * @param array<string, mixed> $args Widget arguments
	 * @since 1.0.0
	 */
	public function __construct( array $args = [] ) {
		parent::__construct(
			self::ID,
			__( 'Product Categories', Constants::TEXTDOMAIN ),
			[
				'description' => __( 'Display product categories in sidebar with customization options.', Constants::TEXTDOMAIN ),
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
		$limit = isset( $instance['limit'] ) ? absint( $instance['limit'] ) : 10;
		$orderby = isset( $instance['orderby'] ) ? esc_attr( $instance['orderby'] ) : 'name';
		$order = isset( $instance['order'] ) ? esc_attr( $instance['order'] ) : 'ASC';
		$show_count = isset( $instance['show_count'] ) ? rest_sanitize_boolean( $instance['show_count'] ) : true;
		$show_image = isset( $instance['show_image'] ) ? rest_sanitize_boolean( $instance['show_image'] ) : false;
		$show_icon = isset( $instance['show_icon'] ) ? rest_sanitize_boolean( $instance['show_icon'] ) : true;
		$empty_message = isset( $instance['empty_message'] ) ? esc_textarea_field( $instance['empty_message'] ) : __( 'No categories found', Constants::TEXTDOMAIN );
		$parent = isset( $instance['parent'] ) ? absint( $instance['parent'] ) : 0;

		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
				<?php esc_html_e( 'Title', Constants::TEXTDOMAIN ); ?>:
			</label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>">
				<?php esc_html_e( 'Number of categories', Constants::TEXTDOMAIN ); ?>:
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
				<option value="slug" <?php selected( $orderby, 'slug' ); ?>><?php esc_html_e( 'Slug', Constants::TEXTDOMAIN ); ?></option>
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
			<label for="<?php echo esc_attr( $this->get_field_id( 'parent' ) ); ?>">
				<?php esc_html_e( 'Parent category', Constants::TEXTDOMAIN ); ?>:
			</label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'parent' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'parent' ) ); ?>">
				<option value="0" <?php selected( $parent, 0 ); ?>><?php esc_html_e( 'All categories', Constants::TEXTDOMAIN ); ?></option>
				<?php
				$parents = get_terms( [
					'taxonomy'   => Constants::TAX_CATEGORY,
					'parent'     => 0,
					'hide_empty' => false,
					'number'     => 100,
				] );
				foreach ( $parents as $parent_cat ) {
					echo '<option value="' . esc_attr( $parent_cat->term_id ) . '" ' . selected( $parent, $parent_cat->term_id, false ) . '>' . esc_html( $parent_cat->name ) . '</option>';
				}
				?>
			</select>
		</p>
		<p>
			<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_count' ) ); ?>" <?php checked( $show_count ); ?> />
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_count' ) ); ?>">
				<?php esc_html_e( 'Show product count', Constants::TEXTDOMAIN ); ?>
			</label>
		</p>
		<p>
			<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_image' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_image' ) ); ?>" <?php checked( $show_image ); ?> />
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_image' ) ); ?>">
				<?php esc_html_e( 'Show category image', Constants::TEXTDOMAIN ); ?>
			</label>
		</p>
		<p>
			<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_icon' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_icon' ) ); ?>" <?php checked( $show_icon ); ?> />
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_icon' ) ); ?>">
				<?php esc_html_e( 'Show category icon', Constants::TEXTDOMAIN ); ?>
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
		$instance['limit'] = absint( $new_instance['limit'] ?? 10 );
		$instance['limit'] = max( 1, min( 100, $instance['limit'] ) );
		$instance['orderby'] = in_array( $new_instance['orderby'] ?? '', [ 'name', 'count', 'id', 'slug' ] ) ? $new_instance['orderby'] : 'name';
		$instance['order'] = in_array( $new_instance['order'] ?? '', [ 'ASC', 'DESC' ] ) ? $new_instance['order'] : 'ASC';
		$instance['parent'] = absint( $new_instance['parent'] ?? 0 );
		$instance['show_count'] = rest_sanitize_boolean( $new_instance['show_count'] ?? true );
		$instance['show_image'] = rest_sanitize_boolean( $new_instance['show_image'] ?? false );
		$instance['show_icon'] = rest_sanitize_boolean( $new_instance['show_icon'] ?? true );
		$instance['empty_message'] = sanitize_textarea_field( $new_instance['empty_message'] ?? '' );

		return $instance;
	}

	/**
	 * Render widget
	 *
	 * @param array<string, mixed> $args Widget display arguments
	 * @param array<string, mixed> $instance Widget instance settings
	 * @return void
	 * @since 1.0.0
	 *
	 * @method WP_Widget::widget
	 */
	public function widget( $args, $instance ) {
		$atts = [
			'limit'     => intval( $instance['limit'] ?? 10 ),
			'orderby'   => $instance['orderby'] ?? 'name',
			'order'     => $instance['order'] ?? 'ASC',
			'show_count' => rest_sanitize_boolean( $instance['show_count'] ?? true ),
			'show_image' => rest_sanitize_boolean( $instance['show_image'] ?? false ),
			'show_icon' => rest_sanitize_boolean( $instance['show_icon'] ?? true ),
			'empty_message' => $instance['empty_message'] ?? __( 'No categories found', Constants::TEXTDOMAIN ),
			'parent'    => absint( $instance['parent'] ?? 0 ),
		];

		$terms_args = [
			'taxonomy'   => Constants::TAX_CATEGORY,
			'orderby'    => $atts['orderby'],
			'order'      => $atts['order'],
			'number'     => intval( $atts['limit'] ),
			'hide_empty' => false,
			'parent'     => $atts['parent'],
			'meta_query' => [
				'relation' => 'AND',
				[
					'key'     => 'category_hide_from_menu',
					'value'   => '1',
					'compare' => '!=',
					'type'    => 'NUMERIC',
				],
			],
		];

		$categories = get_terms( $terms_args );

		ob_start();
		echo $args['before_widget'];

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		echo '<ul class="aps-categories-list">';

		foreach ( $categories as $category ) {
			$icon = get_term_meta( $category->term_id, 'category_icon', true );
			$image = get_term_meta( $category->term_id, 'category_image', true );
			$color = get_term_meta( $category->term_id, 'category_color', true );
			$count = absint( $category->count );
			$link = get_term_link( $category );

			echo '<li class="aps-category-item">';

			if ( $atts['show_image'] && $image ) {
				echo '<div class="aps-category-image">';
				echo '<a href="' . esc_url( $link ) . '" rel="nofollow">';
				echo wp_get_attachment_image( $image, 'thumbnail' );
				echo '</a>';
				echo '</div>';
			}

			echo '<div class="aps-category-info">';

			if ( $atts['show_icon'] && $icon ) {
				$is_svg = strpos( $icon, '<svg' ) !== false || strpos( $icon, '<path' ) !== false;

				if ( $is_svg ) {
					echo '<span class="aps-category-icon">' . $icon . '</span>';
				} else {
					echo '<span class="aps-category-icon-emoji">' . esc_html( $icon ) . '</span>';
				}
			}

			echo '<a href="' . esc_url( $link ) . '" class="aps-category-link" rel="nofollow">';
			echo esc_html( $category->name );
			echo '</a>';

			if ( $atts['show_count'] ) {
				echo '<span class="aps-category-count">(' . $count . ')</span>';
			}

			echo '</div>';
			echo '</li>';
		}

		echo '</ul>';

		if ( empty( $categories ) ) {
			echo '<p class="aps-widget-empty-message">' . esc_html( $atts['empty_message'] ) . '</p>';
		}

		echo $args['after_widget'];
	}
}
