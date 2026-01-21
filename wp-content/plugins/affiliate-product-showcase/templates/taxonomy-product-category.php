<?php
/**
 * Category Archive Template
 *
 * Displays archive page for a specific product category.
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

<div class="aps-category-archive">
	<header class="aps-category-header">
		<?php
		// Category image
		$image_id = get_term_meta( $term->term_id, 'category_image', true );
		if ( $image_id ) {
			echo '<div class="aps-category-banner">';
			echo wp_get_attachment_image( $image_id, 'large', [ 'class' => 'aps-category-banner-image' ] );
			echo '</div>';
		}
		?>

		<div class="aps-category-info">
			<h1 class="aps-category-title">
				<?php echo esc_html( $term->name ); ?>
			</h1>

			<?php
			// Category description
			if ( ! empty( $term->description ) ) {
				echo '<div class="aps-category-description">';
				echo '<p>' . wp_kses_post( $term->description ) . '</p>';
				echo '</div>';
			}
			?>

			<?php
			// Product count
			if ( $term->count > 0 ) {
				printf(
					'<div class="aps-category-count">%s: <span>%d %s</span></div>',
					esc_html__( 'Products', 'affiliate-product-showcase' ),
					absint( $term->count ),
					esc_html__( 'in this category', 'affiliate-product-showcase' )
				);
			}
			?>
		</div>

		<?php
		// Subcategories
		$children = get_terms( [
			'taxonomy'   => 'product_category',
			'parent'     => $term->term_id,
			'hide_empty' => true,
			'orderby'    => 'name',
			'number'     => 0,
		] );

		if ( $children && ! is_wp_error( $children ) && ! empty( $children ) ) {
			echo '<div class="aps-category-subcategories">';
			echo '<h2 class="aps-category-subtitle">' . esc_html__( 'Subcategories', 'affiliate-product-showcase' ) . '</h2>';
			echo '<ul class="aps-category-list">';

			foreach ( $children as $child ) {
				$child_image_id = get_term_meta( $child->term_id, 'category_image', true );
				$child_count = absint( $child->count );

				echo '<li class="aps-category-item">';

				// Image
				if ( $child_image_id ) {
					echo '<div class="aps-category-item-image">';
					echo wp_get_attachment_image( $child_image_id, 'thumbnail', [ 'loading' => 'lazy' ] );
					echo '</div>';
				}

				// Link
				$link = get_term_link( $child );
				echo '<div class="aps-category-item-info">';
				echo '<a href="' . esc_url( $link ) . '" class="aps-category-link">' . esc_html( $child->name ) . '</a>';

				// Count
				if ( $child_count > 0 ) {
					echo '<span class="aps-category-item-count">(' . $child_count . ')</span>';
				}

				echo '</div>';
				echo '</li>';
			}

			echo '</ul>';
			echo '</div>';
		}
		?>
	</header>

	<?php
	// Output products shortcode
	if ( shortcode_exists( 'affiliate_products' ) ) {
		$category_slug = $term->slug;

		// Display products in this category
		echo do_shortcode(
			'affiliate_products',
			sprintf(
				'category=%s&columns=3&limit=12&search=true&sort=true&filter=true&pagination=true',
				$category_slug
			)
		);
	}
	?>

	<aside class="aps-category-sidebar">
		<?php
		// Get parent categories for sidebar
		$parent_categories = get_terms( [
			'taxonomy'   => 'product_category',
			'parent'     => 0,
			'hide_empty' => true,
			'number'     => 0,
			'orderby'    => 'name',
		] );

		if ( $parent_categories && ! is_wp_error( $parent_categories ) ) {
			if ( ! in_array( $term->term_id, wp_list_pluck( $parent_categories, 'term_id' ), true ) ) {
				echo '<div class="aps-widget-area">';
				dynamic_sidebar( 'aps-category-sidebar' );
				echo '</div>';
			}
		}
		?>
</aside>

</div>

<?php
get_footer();
