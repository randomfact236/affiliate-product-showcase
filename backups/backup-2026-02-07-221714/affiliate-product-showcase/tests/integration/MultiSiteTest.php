<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Tests\Integration;

use PHPUnit\Framework\TestCase;

class MultiSiteTest extends TestCase {
	private int $original_site_id;
	private ?int $test_site_id = null;

	protected function setUp(): void {
		parent::setUp();

		// Skip tests if not in multi-site
		if ( ! is_multisite() ) {
			$this->markTestSkipped( 'This test requires WordPress MultiSite to be enabled.' );
		}

		// Save original site ID
		$this->original_site_id = get_current_blog_id();

		// Create test site if multisite functions available
		if ( function_exists( 'wpmu_create_blog' ) ) {
			$this->test_site_id = wpmu_create_blog(
				'test',
				'/multisite-test/',
				'Test Site',
				1,
				'admin@example.com',
				'public',
				1
			);

			if ( is_wp_error( $this->test_site_id ) ) {
				$this->test_site_id = null;
				$this->markTestSkipped( 'Could not create test site for testing.' );
			}

			// Switch to test site
			switch_to_blog( $this->test_site_id );
		}
	}

	protected function tearDown(): void {
		// Restore original site
		if ( $this->original_site_id ) {
			switch_to_blog( $this->original_site_id );
		}

		// Delete test site
		if ( $this->test_site_id && function_exists( 'wpmu_delete_blog' ) ) {
			wpmu_delete_blog( $this->test_site_id, true );
		}

		parent::tearDown();
	}

	public function test_product_creation_isolated_per_site(): void {
		// Create product in test site
		$post_id = wp_insert_post( [
			'post_title'  => 'Test Product',
			'post_type'   => 'aps_product',
			'post_status' => 'publish',
		] );

		$this->assertIsInt( $post_id );
		$this->assertGreaterThan( 0, $post_id );

		// Verify product exists in current site only
		$products = get_posts( [
			'post_type'      => 'aps_product',
			'posts_per_page' => -1,
		] );

		$this->assertCount( 1, $products );
		$this->assertEquals( $post_id, $products[0]->ID );
	}

	public function test_settings_isolated_per_site(): void {
		// Set settings in test site
		$test_settings = [
			'test_key' => 'test_value_' . uniqid(),
		];
		update_option( 'aps_settings', $test_settings );
		$test_site_value = get_option( 'aps_settings' );

		$this->assertIsArray( $test_site_value );
		$this->assertArrayHasKey( 'test_key', $test_site_value );
		$this->assertEquals( $test_settings['test_key'], $test_site_value['test_key'] );

		// Switch to original site
		switch_to_blog( $this->original_site_id );
		$original_site_value = get_option( 'aps_settings' );

		// Settings should be different or not exist in original site
		$this->assertNotEquals( $test_site_value, $original_site_value );
	}

	public function test_analytics_isolated_per_site(): void {
		// Create test product
		$post_id = wp_insert_post( [
			'post_title'  => 'Test Product',
			'post_type'   => 'aps_product',
			'post_status' => 'publish',
		] );

		// Record analytics in test site
		$test_analytics = [
			$post_id => [
				'views'   => rand( 10, 100 ),
				'clicks'  => rand( 5, 50 ),
				'updated' => time(),
			],
		];
		update_option( 'aps_analytics', $test_analytics );
		$test_site_analytics = get_option( 'aps_analytics' );

		$this->assertIsArray( $test_site_analytics );
		$this->assertArrayHasKey( $post_id, $test_site_analytics );

		// Switch to original site
		switch_to_blog( $this->original_site_id );
		$original_site_analytics = get_option( 'aps_analytics' );

		// Analytics should be isolated or not exist in original site
		$this->assertNotEquals( $test_site_analytics, $original_site_analytics );
	}

	public function test_rest_api_respects_site_context(): void {
		// Create product in test site
		$post_id = wp_insert_post( [
			'post_title'  => 'Test Product',
			'post_type'   => 'aps_product',
			'post_status' => 'publish',
		] );

		// Get products via REST API
		$request = new \WP_REST_Request( 'GET', '/affiliate-product-showcase/v1/products' );
		$response = rest_get_server()->dispatch( $request );
		$products = $response->get_data();

		// Should only return products from current site
		$this->assertIsArray( $products );
		$this->assertGreaterThanOrEqual( 1, count( $products ) );

		// Verify test product is in results
		$found = false;
		foreach ( $products as $product ) {
			if ( isset( $product['id'] ) && $product['id'] === $post_id ) {
				$found = true;
				break;
			}
		}
		$this->assertTrue( $found, 'Test product should be in API response' );
	}

	public function test_shortcode_execution_in_correct_site(): void {
		// Create product in test site
		$post_id = wp_insert_post( [
			'post_title'  => 'Test Product',
			'post_type'   => 'aps_product',
			'post_status' => 'publish',
			'meta_input' => [
				'aps_product_price'     => '99.99',
				'aps_product_currency'  => 'USD',
				'aps_product_affiliate_url' => 'https://example.com',
			],
		] );

		// Render shortcode
		$shortcode = '[aps_product id="' . $post_id . '"]';
		ob_start();
		do_shortcode( $shortcode );
		$output = ob_get_clean();

		// Should render product from current site
		$this->assertStringContainsString( 'Test Product', $output );
		$this->assertStringContainsString( '99.99', $output );
	}

	public function test_widget_data_isolated_per_site(): void {
		// Create widget data in test site
		$test_widget_data = [
			'title'     => 'Test Widget',
			'count'     => 5,
			'show_price' => true,
		];
		update_option( 'widget_aps_products', $test_widget_data );
		$test_site_widget = get_option( 'widget_aps_products' );

		$this->assertIsArray( $test_site_widget );

		// Switch to original site
		switch_to_blog( $this->original_site_id );
		$original_site_widget = get_option( 'widget_aps_products' );

		// Widget data should be isolated or not exist
		$this->assertNotEquals( $test_site_widget, $original_site_widget );
	}
}
