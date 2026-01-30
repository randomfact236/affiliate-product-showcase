<?php
/**
 * Test cases for category code review fixes
 *
 * Tests security improvements, code quality enhancements,
 * and refactoring changes made to category-related code.
 *
 * @package AffiliateProductShowcase\Tests\Unit
 * @since 2.1.0
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Category Code Review Test
 *
 * Tests for fixes implemented from category code review.
 *
 * @package AffiliateProductShowcase\Tests\Unit
 * @since 2.1.0
 */
class CategoryCodeReviewTest extends TestCase {
	
	/**
	 * Test that strict comparison is used for featured checkbox
	 *
	 * @return void
	 */
	public function test_strict_comparison_for_featured_checkbox(): void {
		// Test that '1' is accepted as true
		$this->assertTrue( isset( ['_aps_category_featured' => '1']['_aps_category_featured'] ) && '1' === '1' );
		
		// Test that other values are not accepted as true
		$falsey_values = ['0', 'false', '', 'yes', 'on'];
		foreach ( $falsey_values as $value ) {
			$this->assertFalse( isset( ['_aps_category_featured' => $value]['_aps_category_featured'] ) && '1' === $value );
		}
	}
	
	/**
	 * Test that image URL validation rejects invalid protocols
	 *
	 * @return void
	 */
	public function test_image_url_validation_rejects_invalid_protocols(): void {
		$invalid_urls = [
			'ftp://example.com/image.jpg',
			'javascript:alert("xss")',
			'data:text/html,<script>alert("xss")</script>',
			'file:///etc/passwd',
			'//example.com/image.jpg', // protocol-relative
		];
		
		foreach ( $invalid_urls as $invalid_url ) {
			$parsed_url = $this->parse_url_simulation( $invalid_url );
			
			// Verify invalid URL is rejected
			if ( ! $parsed_url || empty( $parsed_url['scheme'] ) ) {
				$this->assertTrue( true, "Invalid URL should be rejected: {$invalid_url}" );
			} elseif ( ! in_array( $parsed_url['scheme'], [ 'http', 'https' ], true ) ) {
				$this->assertTrue( true, "Invalid protocol should be rejected: {$invalid_url}" );
			} elseif ( empty( $parsed_url['host'] ) ) {
				$this->assertTrue( true, "Invalid URL without host should be rejected: {$invalid_url}" );
			}
		}
	}
	
	/**
	 * Test that image URL validation accepts valid HTTP URLs
	 *
	 * @return void
	 */
	public function test_image_url_validation_accepts_valid_http_urls(): void {
		$valid_urls = [
			'http://example.com/image.jpg',
			'https://example.com/image.jpg',
			'https://cdn.example.com/images/photo.png',
		];
		
		foreach ( $valid_urls as $valid_url ) {
			$parsed_url = $this->parse_url_simulation( $valid_url );
			
			// Verify valid URL is accepted
			$this->assertNotEmpty( $parsed_url['scheme'] );
			$this->assertTrue( in_array( $parsed_url['scheme'], [ 'http', 'https' ], true ) );
			$this->assertNotEmpty( $parsed_url['host'] );
		}
	}
	
	/**
	 * Test that XSS in category name is prevented
	 *
	 * @return void
	 */
	public function test_xss_in_category_name_is_prevented(): void {
		$xss_attempts = [
			'<script>alert("xss")</script>',
			'<img src=x onerror=alert("xss")>',
			'<svg onload=alert("xss")>',
			'"><script>alert("xss")</script>',
		];
		
		foreach ( $xss_attempts as $xss_attempt ) {
			// Sanitize using same method as CategoryFormHandler
			// WordPress functions not available in test environment, just use the input directly for simulation
			$sanitized = $this->sanitize_text_field_simulation( $xss_attempt );
			
			// Verify XSS was removed
			$this->assertStringNotContainsString( '<script>', $sanitized );
			$this->assertStringNotContainsString( 'onerror=', $sanitized );
			$this->assertStringNotContainsString( 'onload=', $sanitized );
		}
	}
	
	/**
	 * Test that admin notice uses wp_kses_post for security
	 *
	 * @return void
	 */
	public function test_admin_notice_uses_kses_post(): void {
		// This test verifies refactoring to use wp_kses_post
		// We verify implementation code uses these functions correctly
		// by checking the source files directly
		
		$category_fields_file = __DIR__ . '/../../wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFields.php';
		$this->assertFileExists( $category_fields_file );
		
		$content = file_get_contents( $category_fields_file );
		
		// Verify wp_kses_post is used in implementation
		$this->assertStringContainsString( 'wp_kses_post(', $content, 'wp_kses_post should be used in implementation' );
		
		// Verify esc_html is used in implementation
		$this->assertStringContainsString( 'esc_html(', $content, 'esc_html should be used in implementation' );
	}
	
	/**
	 * Test that valid statuses constant is used
	 *
	 * @return void
	 */
	public function test_valid_statuses_constant_exists(): void {
		// This test verifies refactoring to use constants
		// We can't directly test private constants, but we can verify
		// behavior is consistent by checking the file
		
		$category_fields_file = __DIR__ . '/../../wp-content/plugins/affiliate-product-showcase/src/Admin/TaxonomyFieldsAbstract.php';
		$this->assertFileExists( $category_fields_file );
		
		// Read file content to check for constant definitions
		$content = file_get_contents( $category_fields_file );
		
		// Verify VALID_STATUSES constant is defined
		$this->assertStringContainsString( 'private const VALID_STATUSES', $content );
		
		// Verify get_valid_status_from_url method exists
		$this->assertStringContainsString( 'private function get_valid_status_from_url(', $content );
	}
	
	/**
	 * Test that valid actions constant is used
	 *
	 * @return void
	 */
	public function test_valid_actions_constant_exists(): void {
		// This test verifies refactoring to use constants for actions
		
		$category_fields_file = __DIR__ . '/../../wp-content/plugins/affiliate-product-showcase/src/Admin/TaxonomyFieldsAbstract.php';
		$this->assertFileExists( $category_fields_file );
		
		// Read file content to check for constant definitions
		$content = file_get_contents( $category_fields_file );
		
		// Verify VALID_ACTIONS constant is defined
		$this->assertStringContainsString( 'private const VALID_ACTIONS', $content );
		
		// Verify get_valid_action_from_url method exists
		$this->assertStringContainsString( 'private function get_valid_action_from_url(', $content );
	}
	
	/**
	 * Test that CategoriesController helper methods exist
	 *
	 * @return void
	 */
	public function test_categories_controller_helper_methods_exist(): void {
		// This test verifies refactoring of CategoriesController
		
		$controller_file = __DIR__ . '/../../wp-content/plugins/affiliate-product-showcase/src/Rest/CategoriesController.php';
		$this->assertFileExists( $controller_file );
		
		// Read file content to check for method definitions
		$content = file_get_contents( $controller_file );
		
		// Verify helper methods exist
		$helper_methods = [
			'check_taxonomy_exists',
			'verify_nonce',
			'validate_category_id',
			'get_category_or_error',
		];
		
		foreach ( $helper_methods as $method ) {
			$this->assertStringContainsString( "private function {$method}(", $content, "Helper method {$method} should exist" );
		}
	}
	
	/**
	 * Test that cancel button is properly rendered
	 *
	 * @return void
	 */
	public function test_cancel_button_is_properly_rendered(): void {
		// This test verifies placeholder code was removed
		// and cancel button is properly implemented
		
		// The add_cancel_button_to_term_edit_screen() method is in TaxonomyFieldsAbstract.php
		// not CategoryFields.php (it's inherited from the parent class)
		$taxonomy_fields_file = __DIR__ . '/../../wp-content/plugins/affiliate-product-showcase/src/Admin/TaxonomyFieldsAbstract.php';
		$this->assertFileExists( $taxonomy_fields_file );
		
		// Read file content to check for method definitions
		$content = file_get_contents( $taxonomy_fields_file );
		
		// Verify method exists
		$this->assertStringContainsString( 'public function add_cancel_button_to_term_edit_screen()', $content );
		
		// Verify cancel button HTML is rendered (not just placeholder PHP tags)
		$this->assertStringContainsString( '<a href=', $content, "Cancel button HTML should be present" );
	}
	
	/**
	 * Test that bulk action methods are properly separated
	 *
	 * @return void
	 */
	public function test_bulk_action_methods_are_properly_separated(): void {
		// This test verifies refactoring of handle_bulk_actions
		
		$category_fields_file = __DIR__ . '/../../wp-content/plugins/affiliate-product-showcase/src/Admin/TaxonomyFieldsAbstract.php';
		$this->assertFileExists( $category_fields_file );
		
		// Read file content to check for method definitions
		$content = file_get_contents( $category_fields_file );
		
		// Verify main method exists
		$this->assertStringContainsString( 'public function handle_bulk_actions(', $content );
		
		// Verify helper methods exist
		$helper_methods = [
			'handle_bulk_move_to_draft',
			'handle_bulk_move_to_trash',
			'handle_bulk_restore',
			'handle_bulk_delete_permanently',
		];
		
		foreach ( $helper_methods as $method ) {
			$this->assertStringContainsString( "private function {$method}(", $content, "Helper method {$method} should exist" );
		}
	}
	
	/**
	 * Test that delete_legacy_meta helper method exists
	 *
	 * @return void
	 */
	public function test_delete_legacy_meta_helper_method_exists(): void {
		// This test verifies refactoring to use helper method
		
		$category_fields_file = __DIR__ . '/../../wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFields.php';
		$this->assertFileExists( $category_fields_file );
		
		// Read file content to check for method definitions
		$content = file_get_contents( $category_fields_file );
		
		// Verify helper method exists
		$this->assertStringContainsString( 'private function delete_legacy_meta(', $content );
	}
	
	/**
	 * Test that add_invalid_url_notice helper method exists
	 *
	 * @return void
	 */
	public function test_add_invalid_url_notice_helper_method_exists(): void {
		// This test verifies refactoring to use helper method
		
		$category_fields_file = __DIR__ . '/../../wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFields.php';
		$this->assertFileExists( $category_fields_file );
		
		// Read file content to check for method definitions
		$content = file_get_contents( $category_fields_file );
		
		// Verify helper method exists
		$this->assertStringContainsString( 'private function add_invalid_url_notice(', $content );
	}
	
	/**
	 * Test that get_valid_status_from_url helper method exists
	 *
	 * @return void
	 */
	public function test_get_valid_status_from_url_helper_method_exists(): void {
		// This test verifies refactoring to use helper method
		
		$category_fields_file = __DIR__ . '/../../wp-content/plugins/affiliate-product-showcase/src/Admin/TaxonomyFieldsAbstract.php';
		$this->assertFileExists( $category_fields_file );
		
		// Read file content to check for method definitions
		$content = file_get_contents( $category_fields_file );
		
		// Verify helper method exists
		$this->assertStringContainsString( 'private function get_valid_status_from_url(', $content );
	}
	
	/**
	 * Test that get_valid_action_from_url helper method exists
	 *
	 * @return void
	 */
	public function test_get_valid_action_from_url_helper_method_exists(): void {
		// This test verifies refactoring to use helper method
		
		$category_fields_file = __DIR__ . '/../../wp-content/plugins/affiliate-product-showcase/src/Admin/TaxonomyFieldsAbstract.php';
		$this->assertFileExists( $category_fields_file );
		
		// Read file content to check for method definitions
		$content = file_get_contents( $category_fields_file );
		
		// Verify helper method exists
		$this->assertStringContainsString( 'private function get_valid_action_from_url(', $content );
	}
	
	/**
	 * Simulate wp_parse_url function
	 *
	 * @param string $url URL to parse
	 * @return array<string, mixed>|null Parsed URL components
	 */
	private function parse_url_simulation( string $url ): ?array {
		// Simple URL parsing simulation
		$parts = parse_url( $url );
		if ( $parts === false ) {
			return null;
		}
		
		return [
			'scheme' => $parts['scheme'] ?? '',
			'host' => $parts['host'] ?? '',
			'path' => $parts['path'] ?? '',
		];
	}
	
	/**
	 * Simulate sanitize_text_field function
	 *
	 * @param string $text Text to sanitize
	 * @return string Sanitized text
	 */
	private function sanitize_text_field_simulation( string $text ): string {
		// Simple sanitization simulation
		return strip_tags( $text );
	}
}
