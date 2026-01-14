<?php



if ( ! defined( 'ABSPATH' ) ) {
	exit;
}/** @var array $settings */
?>
<div class="wrap">
	<h1><?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}esc_html_e( 'Affiliate Product Showcase Settings', 'affiliate-product-showcase' ); ?></h1>
	<form method="post" action="options.php">
		<?php
		

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}settings_fields( AffiliateProductShowcase\Plugin\Constants::SLUG );
		do_settings_sections( AffiliateProductShowcase\Plugin\Constants::SLUG );
		submit_button();
		?>
	</form>
</div>
