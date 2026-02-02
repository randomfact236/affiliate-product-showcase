<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Blocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Plugin\Constants;
use AffiliateProductShowcase\Services\ProductService;
use AffiliateProductShowcase\Repositories\SettingsRepository;
use AffiliateProductShowcase\Formatters\PriceFormatter;

final class Blocks {
	private SettingsRepository $settings_repository;
	private PriceFormatter $price_formatter;

	public function __construct( private ProductService $product_service ) {
		$this->settings_repository = new SettingsRepository();
		$this->price_formatter = new PriceFormatter();
	}

	public function register(): void {
		$blocks = [
			'blocks/product-showcase',
			'blocks/product-grid',
		];

		foreach ( $blocks as $block ) {
			register_block_type(
				Constants::viewPath( $block ),
				[
					'render_callback' => [ $this, 'render_block' ],
				]
			);
		}
	}

	/**
	 * Render block based on type
	 *
	 * @param array<string, mixed> $attributes Block attributes
	 * @param string $content Block content
	 * @param \WP_Block $block Block instance
	 * @return string Rendered HTML
	 */
	public function render_block( array $attributes, string $content, \WP_Block $block ): string {
		$type     = $block->name;
		$per_page = $attributes['perPage'] ?? 6;

		$products = $this->product_service->get_products( [ 
			'per_page' => $per_page,
		] );

		if ( false !== strpos( $type, 'product-grid' ) ) {
			return $this->render_product_grid( $products, $attributes );
		}

		if ( false !== strpos( $type, 'product-showcase' ) ) {
			return $this->render_product_showcase( $products, $attributes );
		}

		return '';
	}

	/**
	 * Render product grid block
	 *
	 * @param array<int, \AffiliateProductShowcase\Models\Product> $products Products to display
	 * @param array<string, mixed> $attributes Block attributes
	 * @return string Rendered HTML
	 */
	private function render_product_grid( array $products, array $attributes ): string {
		if ( empty( $products ) ) {
			return '<div class="aps-block--grid is-empty"><p>No products found.</p></div>';
		}

		$columns      = $attributes['columns'] ?? 3;
		$gap          = $attributes['gap'] ?? 16;
		$show_price   = $attributes['showPrice'] ?? true;
		$show_rating  = $attributes['showRating'] ?? true;
		$show_badge   = $attributes['showBadge'] ?? true;
		$hover_effect = $attributes['hoverEffect'] ?? 'lift';

		ob_start();
		?>
		<div class="aps-block aps-block--grid" 
		     data-hover-effect="<?php echo esc_attr( $hover_effect ); ?>"
		     style="--aps-grid-columns: <?php echo esc_attr( $columns ); ?>; --aps-grid-gap: <?php echo esc_attr( $gap ); ?>px;">
			<?php foreach ( $products as $product ) : ?>
				<article class="aps-grid-item" id="aps-product-<?php echo esc_attr( $product->id ); ?>">
					<?php if ( $product->image_url ) : ?>
						<img src="<?php echo esc_url( $product->image_url ); ?>" 
						     alt="<?php echo esc_attr( $product->title ); ?>" 
						     class="aps-product-image"
						     loading="lazy" />
					<?php endif; ?>
					
					<?php if ( $show_badge && $product->badge ) : ?>
						<span class="aps-product-badge"><?php echo esc_html( $product->badge ); ?></span>
					<?php endif; ?>
					
					<div class="aps-product-content">
						<h3 class="aps-product-title">
							<a href="<?php echo esc_url( $product->affiliate_url ); ?>" target="_blank" rel="nofollow sponsored">
								<?php echo esc_html( $product->title ); ?>
							</a>
						</h3>
						
						<?php if ( $show_rating && $product->rating ) : ?>
							<div class="aps-product-rating">
								<?php echo $this->render_stars( $product->rating ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</div>
						<?php endif; ?>
						
						<?php if ( $show_price && $product->price ) : ?>
							<div class="aps-product-price">
								<span class="aps-current-price"><?php echo esc_html( $this->price_formatter->format( $product->price, $product->currency ) ); ?></span>
								<?php if ( $product->original_price ) : ?>
									<span class="aps-original-price"><?php echo esc_html( $this->price_formatter->format( $product->original_price, $product->currency ) ); ?></span>
									<?php 
									$discount = ( ( $product->original_price - $product->price ) / $product->original_price ) * 100;
									if ( $discount > 0 ) :
									?>
										<span class="aps-discount">-<?php echo esc_html( number_format( $discount, 0 ) ); ?>%</span>
									<?php endif; ?>
								<?php endif; ?>
							</div>
						<?php endif; ?>
						
						<p class="aps-product-description">
							<?php echo wp_kses_post( wp_trim_words( $product->description, 20, '...' ) ); ?>
						</p>
						
						<a href="<?php echo esc_url( $product->affiliate_url ); ?>" 
						   target="_blank" 
						   rel="nofollow sponsored"
						   class="aps-product-button"
						   aria-label="<?php echo esc_attr( sprintf( 'View deal for %s', $product->title ) ); ?>">
							View Deal
						</a>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render product showcase block
	 *
	 * @param array<int, \AffiliateProductShowcase\Models\Product> $products Products to display
	 * @param array<string, mixed> $attributes Block attributes
	 * @return string Rendered HTML
	 */
	private function render_product_showcase( array $products, array $attributes ): string {
		if ( empty( $products ) ) {
			return '<div class="aps-block--showcase is-empty"><p>No products found.</p></div>';
		}

		$layout          = $attributes['layout'] ?? 'grid';
		$columns         = $attributes['columns'] ?? 3;
		$gap             = $attributes['gap'] ?? 16;
		$show_price     = $attributes['showPrice'] ?? true;
		$show_description = $attributes['showDescription'] ?? true;
		$show_button     = $attributes['showButton'] ?? true;
		$button_text     = $attributes['buttonText'] ?? 'View Details';

		ob_start();
		?>
		<div class="aps-block aps-block--showcase" 
		     data-layout="<?php echo esc_attr( $layout ); ?>"
		     data-show-price="<?php echo $show_price ? 'true' : 'false'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"
		     data-show-description="<?php echo $show_description ? 'true' : 'false'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"
		     data-show-button="<?php echo $show_button ? 'true' : 'false'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"
		     style="--aps-showcase-columns: <?php echo esc_attr( $columns ); ?>; --aps-showcase-gap: <?php echo esc_attr( $gap ); ?>px;">
			<?php 
			// Determine word limit based on layout
			$word_limit = $layout === 'list' ? 40 : 25;
			
			foreach ( $products as $product ) : 
			?>
				<article class="aps-showcase-item" id="aps-product-<?php echo esc_attr( $product->id ); ?>">
					<?php if ( $product->image_url ) : ?>
						<img src="<?php echo esc_url( $product->image_url ); ?>" 
						     alt="<?php echo esc_attr( $product->title ); ?>" 
						     class="aps-product-image"
						     loading="lazy" />
					<?php endif; ?>
					
					<?php if ( $product->badge ) : ?>
						<span class="aps-product-badge"><?php echo esc_html( $product->badge ); ?></span>
					<?php endif; ?>
					
					<div class="aps-product-content">
						<h3 class="aps-product-title">
							<a href="<?php echo esc_url( $product->affiliate_url ); ?>" target="_blank" rel="nofollow sponsored">
								<?php echo esc_html( $product->title ); ?>
							</a>
						</h3>
						
						<?php if ( $show_price && $product->price ) : ?>
							<div class="aps-product-price">
								<span class="aps-current-price"><?php echo esc_html( $this->price_formatter->format( $product->price, $product->currency ) ); ?></span>
								<?php if ( $product->original_price ) : ?>
									<span class="aps-original-price"><?php echo esc_html( $this->price_formatter->format( $product->original_price, $product->currency ) ); ?></span>
								<?php endif; ?>
							</div>
						<?php endif; ?>
						
						<?php if ( $show_description && $product->description ) : ?>
							<p class="aps-product-description">
								<?php echo wp_kses_post( wp_trim_words( $product->description, $word_limit, '...' ) ); ?>
							</p>
						<?php endif; ?>
						
						<?php if ( $show_button ) : ?>
							<a href="<?php echo esc_url( $product->affiliate_url ); ?>" 
							   target="_blank" 
							   rel="nofollow sponsored"
							   class="aps-product-button"
							   aria-label="<?php echo esc_attr( sprintf( '%s for %s', $button_text, $product->title ) ); ?>">
								<?php echo esc_html( $button_text ); ?>
							</a>
						<?php endif; ?>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render star rating HTML
	 *
	 * @param float $rating Rating value (0-5)
	 * @return string Star rating HTML
	 */
	private function render_stars( float $rating ): string {
		$full_stars  = floor( $rating );
		$has_half_star = ( $rating - $full_stars ) >= 0.5 ? 1 : 0;
		$empty_stars = 5 - $full_stars - $has_half_star;

		$stars  = '<div class="aps-stars">';
		
		for ( $i = 0; $i < $full_stars; $i++ ) {
			$stars .= '<span class="aps-star">★</span>';
		}
		if ( $has_half_star ) {
			$stars .= '<span class="aps-star">★</span>';
		}
		for ( $i = 0; $i < $empty_stars; $i++ ) {
			$stars .= '<span class="aps-star empty">★</span>';
		}
		
		$stars .= '</div>';

		return $stars;
	}
}
