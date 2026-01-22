<?php
/**
 * Dashboard Page Template
 *
 * Main dashboard for Affiliate Manager plugin.
 *
 * @package AffiliateProductShowcase
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="wrap">
	<h1><?php esc_html_e( 'Affiliate Manager Dashboard', 'affiliate-product-showcase' ); ?></h1>

	<div class="aps-dashboard-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
		
		<!-- Products Overview -->
		<div class="aps-dashboard-card" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px;">
			<h2><?php esc_html_e( 'Products', 'affiliate-product-showcase' ); ?></h2>
			<p style="font-size: 2em; color: #2271b1; margin: 10px 0;">
				<?php echo esc_html( wp_count_posts( 'aps_product' )->publish ); ?>
			</p>
			<p><?php esc_html_e( 'Published products', 'affiliate-product-showcase' ); ?></p>
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=aps_product' ) ); ?>" class="button" style="margin-top: 10px;">
				<?php esc_html_e( 'View All Products', 'affiliate-product-showcase' ); ?>
			</a>
		</div>

		<!-- Quick Actions -->
		<div class="aps-dashboard-card" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px;">
			<h2><?php esc_html_e( 'Quick Actions', 'affiliate-product-showcase' ); ?></h2>
			<ul style="list-style: none; padding: 0; margin-top: 15px;">
				<li style="margin-bottom: 10px;">
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=aps_product&page=add-product' ) ); ?>" class="button button-primary">
						<?php esc_html_e( 'Add New Product', 'affiliate-product-showcase' ); ?>
					</a>
				</li>
				<li style="margin-bottom: 10px;">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=affiliate-manager-settings' ) ); ?>" class="button">
						<?php esc_html_e( 'Settings', 'affiliate-product-showcase' ); ?>
					</a>
				</li>
				<li>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=affiliate-manager-help' ) ); ?>" class="button">
						<?php esc_html_e( 'Help & Support', 'affiliate-product-showcase' ); ?>
					</a>
				</li>
			</ul>
		</div>

		<!-- Taxonomies Overview -->
		<div class="aps-dashboard-card" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px;">
			<h2><?php esc_html_e( 'Taxonomies', 'affiliate-product-showcase' ); ?></h2>
			<ul style="list-style: none; padding: 0; margin-top: 15px;">
				<li style="margin-bottom: 8px;">
					<strong><?php esc_html_e( 'Categories:', 'affiliate-product-showcase' ); ?></strong>
					<?php echo esc_html( wp_count_terms( 'aps_category' ) ); ?>
				</li>
				<li style="margin-bottom: 8px;">
					<strong><?php esc_html_e( 'Tags:', 'affiliate-product-showcase' ); ?></strong>
					<?php echo esc_html( wp_count_terms( 'aps_tag' ) ); ?>
				</li>
				<li>
					<strong><?php esc_html_e( 'Ribbons:', 'affiliate-product-showcase' ); ?></strong>
					<?php echo esc_html( wp_count_terms( 'aps_ribbon' ) ); ?>
				</li>
			</ul>
		</div>

	</div>

	<!-- Plugin Info -->
	<div class="aps-dashboard-info" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px; margin-top: 20px;">
		<h2><?php esc_html_e( 'Plugin Information', 'affiliate-product-showcase' ); ?></h2>
		<table class="widefat" style="margin-top: 15px;">
			<tbody>
				<tr>
					<td><strong><?php esc_html_e( 'Version', 'affiliate-product-showcase' ); ?></strong></td>
					<td><?php echo esc_html( AFFILIATE_PRODUCT_SHOWCASE_VERSION ); ?></td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'Documentation', 'affiliate-product-showcase' ); ?></strong></td>
					<td><a href="https://github.com/randomfact236/affiliate-product-showcase" target="_blank">GitHub Repository</a></td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'Support', 'affiliate-product-showcase' ); ?></strong></td>
					<td><a href="<?php echo esc_url( admin_url( 'admin.php?page=affiliate-manager-help' ) ); ?>"><?php esc_html_e( 'Help Page', 'affiliate-product-showcase' ); ?></a></td>
				</tr>
			</tbody>
		</table>
	</div>

</div>
