<?php

namespace AffiliateProductShowcase\Public;

use AffiliateProductShowcase\Services\ProductService;
use AffiliateProductShowcase\Repositories\SettingsRepository;

final class Widgets {
	public function __construct( private ProductService $product_service, private SettingsRepository $settings_repository ) {}

	public function register(): void {
		if ( ! class_exists( 'APS_Product_Widget' ) ) {
			class APS_Product_Widget extends \WP_Widget {
				private static ProductService $service;

				public static function set_service( ProductService $service ): void {
					self::$service = $service;
				}

				public function __construct() {
					parent::__construct( 'aps_product_widget', __( 'Affiliate Products', 'affiliate-product-showcase' ) );
				}

				public function widget( $args, $instance ): void {
					$limit    = isset( $instance['count'] ) ? (int) $instance['count'] : 3;
					$products = self::$service->get_products( [ 'per_page' => $limit ] );
					$settings = ( new SettingsRepository() )->get_settings();

					echo $args['before_widget'];
					if ( ! empty( $instance['title'] ) ) {
						echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
					}

					echo aps_view( 'src/Public/partials/product-grid.php', [ 'products' => $products, 'settings' => $settings ] );
					echo $args['after_widget'];
				}

				public function form( $instance ): void {
					$title = $instance['title'] ?? __( 'Featured Products', 'affiliate-product-showcase' );
					$count = $instance['count'] ?? 3;
					?>
					<p>
						<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'affiliate-product-showcase' ); ?></label>
						<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
					</p>
					<p>
						<label for="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>"><?php esc_html_e( 'Number of products', 'affiliate-product-showcase' ); ?></label>
						<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'count' ) ); ?>" type="number" min="1" max="12" value="<?php echo esc_attr( $count ); ?>" />
					</p>
					<?php
				}

				public function update( $new_instance, $old_instance ): array {
					return [
						'title' => sanitize_text_field( $new_instance['title'] ?? '' ),
						'count' => (int) ( $new_instance['count'] ?? 3 ),
					];
				}
			}
		}

		APS_Product_Widget::set_service( $this->product_service );
		register_widget( APS_Product_Widget::class );
	}
}
