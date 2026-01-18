<?php
/**
 * Product Validator Service Tests
 *
 * @package AffiliateProductShowcase\Tests\Unit\Services
 */

namespace AffiliateProductShowcase\Tests\Unit\Services;

use AffiliateProductShowcase\Services\ProductValidator;
use PHPUnit\Framework\TestCase;

/**
 * Test ProductValidator service
 */
class ProductValidatorTest extends TestCase {

	private ProductValidator $validator;

	protected function setUp(): void {
		$this->validator = new ProductValidator();
	}

	/**
	 * Test valid product data
	 */
	public function testValidProductData() {
		$data = [
			'title'         => 'Valid Product',
			'price'         => 99.99,
			'affiliate_url'  => 'https://example.com/product',
		];

		$result = $this->validator->validate($data);

		$this->assertTrue($result['valid']);
		$this->assertEmpty($result['errors']);
	}

	/**
	 * Test missing required field - title
	 */
	public function testMissingRequiredTitle() {
		$data = [
			'price'         => 99.99,
			'affiliate_url'  => 'https://example.com/product',
		];

		$result = $this->validator->validate($data);

		$this->assertFalse($result['valid']);
		$this->assertArrayHasKey('title', $result['errors']);
		$this->assertStringContainsString('required', $result['errors']['title']);
	}

	/**
	 * Test missing required field - price
	 */
	public function testMissingRequiredPrice() {
		$data = [
			'title'         => 'Valid Product',
			'affiliate_url'  => 'https://example.com/product',
		];

		$result = $this->validator->validate($data);

		$this->assertFalse($result['valid']);
		$this->assertArrayHasKey('price', $result['errors']);
		$this->assertStringContainsString('required', $result['errors']['price']);
	}

	/**
	 * Test missing required field - affiliate_url
	 */
	public function testMissingRequiredAffiliateUrl() {
		$data = [
			'title' => 'Valid Product',
			'price' => 99.99,
		];

		$result = $this->validator->validate($data);

		$this->assertFalse($result['valid']);
		$this->assertArrayHasKey('affiliate_url', $result['errors']);
		$this->assertStringContainsString('required', $result['errors']['affiliate_url']);
	}

	/**
	 * Test title too short
	 */
	public function testTitleTooShort() {
		$data = [
			'title'         => 'AB',
			'price'         => 99.99,
			'affiliate_url'  => 'https://example.com/product',
		];

		$result = $this->validator->validate($data);

		$this->assertFalse($result['valid']);
		$this->assertArrayHasKey('title', $result['errors']);
		$this->assertStringContainsString('at least 3 characters', $result['errors']['title']);
	}

	/**
	 * Test title too long
	 */
	public function testTitleTooLong() {
		$long_title = str_repeat('A', 201);
		$data = [
			'title'         => $long_title,
			'price'         => 99.99,
			'affiliate_url'  => 'https://example.com/product',
		];

		$result = $this->validator->validate($data);

		$this->assertFalse($result['valid']);
		$this->assertArrayHasKey('title', $result['errors']);
		$this->assertStringContainsString('not exceed 200 characters', $result['errors']['title']);
	}

	/**
	 * Test title with spam keywords warning
	 */
	public function testTitleWithSpamKeywordsWarning() {
		$data = [
			'title'         => 'FREE Product Click Here Now',
			'price'         => 99.99,
			'affiliate_url'  => 'https://example.com/product',
		];

		$result = $this->validator->validate($data);

		$this->assertTrue($result['valid']); // Still valid, just a warning
		$this->assertArrayHasKey('title', $result['warnings']);
		$this->assertStringContainsString('suspicious keywords', $result['warnings']['title']);
	}

	/**
	 * Test negative price
	 */
	public function testNegativePrice() {
		$data = [
			'title'         => 'Valid Product',
			'price'         => -10.00,
			'affiliate_url'  => 'https://example.com/product',
		];

		$result = $this->validator->validate($data);

		$this->assertFalse($result['valid']);
		$this->assertArrayHasKey('price', $result['errors']);
		$this->assertStringContainsString('cannot be negative', $result['errors']['price']);
	}

	/**
	 * Test unusually high price warning
	 */
	public function testUnusuallyHighPriceWarning() {
		$data = [
			'title'         => 'Valid Product',
			'price'         => 1000000.00,
			'affiliate_url'  => 'https://example.com/product',
		];

		$result = $this->validator->validate($data);

		$this->assertTrue($result['valid']); // Still valid, just a warning
		$this->assertArrayHasKey('price', $result['warnings']);
		$this->assertStringContainsString('unusually high', $result['warnings']['price']);
	}

	/**
	 * Test invalid affiliate URL
	 */
	public function testInvalidAffiliateUrl() {
		$data = [
			'title'         => 'Valid Product',
			'price'         => 99.99,
			'affiliate_url'  => 'not-a-valid-url',
		];

		$result = $this->validator->validate($data);

		$this->assertFalse($result['valid']);
		$this->assertArrayHasKey('affiliate_url', $result['errors']);
		$this->assertStringContainsString('Invalid affiliate URL', $result['errors']['affiliate_url']);
	}

	/**
	 * Test valid affiliate URL
	 */
	public function testValidAffiliateUrl() {
		$data = [
			'title'         => 'Valid Product',
			'price'         => 99.99,
			'affiliate_url'  => 'https://example.com/product',
		];

		$result = $this->validator->validate($data);

		$this->assertTrue($result['valid']);
		$this->assertArrayNotHasKey('affiliate_url', $result['errors']);
	}

	/**
	 * Test invalid image URL
	 */
	public function testInvalidImageUrl() {
		$data = [
			'title'         => 'Valid Product',
			'price'         => 99.99,
			'affiliate_url'  => 'https://example.com/product',
			'image_url'     => 'not-a-valid-url',
		];

		$result = $this->validator->validate($data);

		$this->assertFalse($result['valid']);
		$this->assertArrayHasKey('image_url', $result['errors']);
		$this->assertStringContainsString('Invalid image URL', $result['errors']['image_url']);
	}

	/**
	 * Test invalid image extension
	 */
	public function testInvalidImageExtension() {
		$data = [
			'title'         => 'Valid Product',
			'price'         => 99.99,
			'affiliate_url'  => 'https://example.com/product',
			'image_url'     => 'https://example.com/image.pdf',
		];

		$result = $this->validator->validate($data);

		$this->assertTrue($result['valid']); // Still valid, just a warning
		$this->assertArrayHasKey('image_url', $result['warnings']);
		$this->assertStringContainsString('valid image', $result['warnings']['image_url']);
	}

	/**
	 * Test valid image URL with proper extension
	 */
	public function testValidImageUrl() {
		$data = [
			'title'         => 'Valid Product',
			'price'         => 99.99,
			'affiliate_url'  => 'https://example.com/product',
			'image_url'     => 'https://example.com/image.jpg',
		];

		$result = $this->validator->validate($data);

		$this->assertTrue($result['valid']);
		$this->assertArrayNotHasKey('image_url', $result['errors']);
		$this->assertArrayNotHasKey('image_url', $result['warnings']);
	}

	/**
	 * Test rating below minimum
	 */
	public function testRatingBelowMinimum() {
		$data = [
			'title'         => 'Valid Product',
			'price'         => 99.99,
			'affiliate_url'  => 'https://example.com/product',
			'rating'        => -1.0,
		];

		$result = $this->validator->validate($data);

		$this->assertFalse($result['valid']);
		$this->assertArrayHasKey('rating', $result['errors']);
		$this->assertStringContainsString('between 0 and 5', $result['errors']['rating']);
	}

	/**
	 * Test rating above maximum
	 */
	public function testRatingAboveMaximum() {
		$data = [
			'title'         => 'Valid Product',
			'price'         => 99.99,
			'affiliate_url'  => 'https://example.com/product',
			'rating'        => 5.5,
		];

		$result = $this->validator->validate($data);

		$this->assertFalse($result['valid']);
		$this->assertArrayHasKey('rating', $result['errors']);
		$this->assertStringContainsString('between 0 and 5', $result['errors']['rating']);
	}

	/**
	 * Test valid rating at boundaries
	 */
	public function testValidRatingAtBoundaries() {
		// Test 0.0
		$data = [
			'title'         => 'Valid Product',
			'price'         => 99.99,
			'affiliate_url'  => 'https://example.com/product',
			'rating'        => 0.0,
		];

		$result = $this->validator->validate($data);
		$this->assertTrue($result['valid']);

		// Test 5.0
		$data['rating'] = 5.0;
		$result = $this->validator->validate($data);
		$this->assertTrue($result['valid']);

		// Test 2.5 (middle value)
		$data['rating'] = 2.5;
		$result = $this->validator->validate($data);
		$this->assertTrue($result['valid']);
	}

	/**
	 * Test negative stock quantity
	 */
	public function testNegativeStockQuantity() {
		$data = [
			'title'          => 'Valid Product',
			'price'          => 99.99,
			'affiliate_url'   => 'https://example.com/product',
			'stock_quantity' => -10,
		];

		$result = $this->validator->validate($data);

		$this->assertFalse($result['valid']);
		$this->assertArrayHasKey('stock_quantity', $result['errors']);
		$this->assertStringContainsString('cannot be negative', $result['errors']['stock_quantity']);
	}

	/**
	 * Test valid stock quantity
	 */
	public function testValidStockQuantity() {
		$data = [
			'title'          => 'Valid Product',
			'price'          => 99.99,
			'affiliate_url'   => 'https://example.com/product',
			'stock_quantity' => 100,
		];

		$result = $this->validator->validate($data);

		$this->assertTrue($result['valid']);
		$this->assertArrayNotHasKey('stock_quantity', $result['errors']);
	}

	/**
	 * Test getErrorsHtml with empty errors
	 */
	public function testGetErrorsHtmlWithEmptyErrors() {
		$html = $this->validator->getErrorsHtml([]);

		$this->assertEquals('', $html);
	}

	/**
	 * Test getErrorsHtml with errors
	 */
	public function testGetErrorsHtmlWithErrors() {
		$errors = [
			'title' => 'Title is required',
			'price' => 'Price cannot be negative',
		];

		$html = $this->validator->getErrorsHtml($errors);

		$this->assertStringContainsString('notice-error', $html);
		$this->assertStringContainsString('Title:', $html);
		$this->assertStringContainsString('Title is required', $html);
		$this->assertStringContainsString('Price:', $html);
		$this->assertStringContainsString('Price cannot be negative', $html);
	}

	/**
	 * Test validateForCreation
	 */
	public function testValidateForCreation() {
		$data = [
			'title'         => 'New Product',
			'price'         => 99.99,
			'affiliate_url'  => 'https://example.com/product',
		];

		$result = $this->validator->validateForCreation($data);

		$this->assertTrue($result['valid']);
		$this->assertArrayHasKey('valid', $result);
		$this->assertArrayHasKey('errors', $result);
		$this->assertArrayHasKey('warnings', $result);
	}

	/**
	 * Test validateForUpdate
	 */
	public function testValidateForUpdate() {
		$data = [
			'title'         => 'Updated Product',
			'price'         => 199.99,
			'affiliate_url'  => 'https://example.com/product',
		];

		$result = $this->validator->validateForUpdate(1, $data);

		$this->assertTrue($result['valid']);
		$this->assertArrayHasKey('valid', $result);
		$this->assertArrayHasKey('errors', $result);
		$this->assertArrayHasKey('warnings', $result);
	}

	/**
	 * Test multiple validation errors
	 */
	public function testMultipleValidationErrors() {
		$data = [
			'title'         => 'AB', // Too short
			'price'         => -10, // Negative
			'affiliate_url'  => 'invalid-url', // Invalid
		];

		$result = $this->validator->validate($data);

		$this->assertFalse($result['valid']);
		$this->assertArrayHasKey('title', $result['errors']);
		$this->assertArrayHasKey('price', $result['errors']);
		$this->assertArrayHasKey('affiliate_url', $result['errors']);
	}

	/**
	 * Test valid image extensions
	 */
	public function testValidImageExtensions() {
		$valid_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];

		foreach ($valid_extensions as $ext) {
			$data = [
				'title'         => 'Valid Product',
				'price'         => 99.99,
				'affiliate_url'  => 'https://example.com/product',
				'image_url'     => "https://example.com/image.{$ext}",
			];

			$result = $this->validator->validate($data);
			$this->assertTrue($result['valid'], "Failed for extension: {$ext}");
		}
	}
}
