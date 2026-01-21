<?php
/**
 * Tag Archive Template
 *
 * Displays archive page for products with a specific tag.
 *
 * @package AffiliateProductShowcase
 * @since 1.0.0
 */

get_header();

/**
 * Output breadcrumb navigation
 */
if ( function_exists( '\AffiliateProductShowcase\Public\Templates' ) ) {
	$templates = new \AffiliateProductShowcase\Public\Templates();
	echo $templates->render_breadcrumbs();
}

// Get current term
$term = get_queried_object();

?>

<div class="aps-tag-archive">
	<header class="aps-tag-header">
		<h1 class="aps-tag-title">
			<?php
				printf(
					/* translators: %s: tag name */
					esc_html__( 'Tag: %s', 'affiliate-product-showcase' ),
					esc_html( $term->name )
				);
			?>
		</h1>

		<?php
		// Product count
		if ( $term->count > 0 ) {
			printf(
				'<div class="aps-tag-count">%s</div>',
				esc_html( number_format_i18n( $term->count ) ),
				esc_html__( 'products with this tag', 'affiliate-product-showcase' )
			);
		}
		?>
	</header>

	<?php
	// Output products shortcode with tag filter
	if ( shortcode_exists( 'affiliate_products' ) ) {
		$tag_slug = $term->slug;

		// Display products with this tag
		echo do_shortcode(
			'affiliate_products',
			sprintf(
				'tag=%s&columns=3&limit=12&search=true&sort=true&filter=true&pagination=true',
				$tag_slug
			)
		);
	}
	?>

	<aside class="aps-tag-sidebar">
		<?php
		// Get popular tags for sidebar
		$popular_tags = get_terms( [
			'taxonomy' => 'product_tag',
			'number'   => 10,
			'orderby'  => 'count',
			'order'    => 'DESC',
			'hide_empty' => false,
		] );

		if ( $popular_tags && ! is_wp_error( $popular_tags ) && ! empty( $popular_tags ) ) {
			if ( shortcode_exists( 'affiliate_tags' ) ) {
				echo do_shortcode( 'affiliate_tags', 'count=10&orderby=count&order=DESC&show_count=true' );
			}
		}
	?>
	</aside>

</div>

<?php
get_footer();
