<?php
/**
 * Product Model Tests
 *
 * @package AffiliateProductShowcase\Tests\Unit
 */

namespace AffiliateProductShowcase\Tests\Unit\Models;

use AffiliateProductShowcase\Models\Product;
use PHPUnit\Framework\TestCase;

/**
 * Test Product model
 */
class ProductTest extends TestCase {

	/**
	 * Test product creation with all parameters
	 */
	public function testProductCreationWithAllParameters() {
		$product = new Product(
			1,
			'Test Product',
			'test-product',
			'Test Description',
			'USD',
			99.99,
			149.99,
			'https://example.com/product',
			'https://example.com/image.jpg',
			4.5,
			'Best Seller',
			['Electronics', 'Gadgets']
		);

		$this->assertEquals(1, $product->id);
		$this->assertEquals('Test Product', $product->title);
		$this->assertEquals('test-product', $product->slug);
		$this->assertEquals('Test Description', $product->description);
		$this->assertEquals('USD', $product->currency);
		$this->assertEquals(99.99, $product->price);
		$this->assertEquals(149.99, $product->original_price);
		$this->assertEquals('https://example.com/product', $product->affiliate_url);
		$this->assertEquals('https://example.com/image.jpg', $product->image_url);
		$this->assertEquals(4.5, $product->rating);
		$this->assertEquals('Best Seller', $product->badge);
		$this->assertEquals(['Electronics', 'Gadgets'], $product->categories);
	}

	/**
	 * Test product creation with minimal parameters
	 */
	public function testProductCreationWithMinimalParameters() {
		$product = new Product(
			2,
			'Minimal Product',
			'minimal-product',
			'Minimal Description',
			'EUR',
			49.99,
			null,
			'https://example.com/minimal'
		);

		$this->assertEquals(2, $product->id);
		$this->assertEquals('Minimal Product', $product->title);
		$this->assertNull($product->original_price);
		$this->assertNull($product->image_url);
		$this->assertNull($product->rating);
		$this->assertNull($product->badge);
		$this->assertEquals([], $product->categories);
	}

	/**
	 * Test to_array method
	 */
	public function testToArray() {
		$product = new Product(
			3,
			'Array Test Product',
			'array-test-product',
			'Array Description',
			'GBP',
			79.99,
			89.99,
			'https://example.com/array',
			'https://example.com/array-image.jpg',
			4.8,
			'New',
			['Books', 'Fiction']
		);

		$array = $product->to_array();

		$this->assertIsArray($array);
		$this->assertEquals(3, $array['id']);
		$this->assertEquals('Array Test Product', $array['title']);
		$this->assertEquals('array-test-product', $array['slug']);
		$this->assertEquals('Array Description', $array['description']);
		$this->assertEquals('GBP', $array['currency']);
		$this->assertEquals(79.99, $array['price']);
		$this->assertEquals(89.99, $array['original_price']);
		$this->assertEquals('https://example.com/array', $array['affiliate_url']);
		$this->assertEquals('https://example.com/array', $array['affiliate_link']); // Alias
		$this->assertEquals('https://example.com/array-image.jpg', $array['image_url']);
		$this->assertEquals(4.8, $array['rating']);
		$this->assertEquals('New', $array['badge']);
		$this->assertEquals(['Books', 'Fiction'], $array['categories']);
	}

	/**
	 * Test to_array method with null values
	 */
	public function testToArrayWithNullValues() {
		$product = new Product(
			4,
			'Null Test',
			'null-test',
			'Null Description',
			'JPY',
			1999.99
		);

		$array = $product->to_array();

		$this->assertIsArray($array);
		$this->assertNull($array['original_price']);
		$this->assertNull($array['image_url']);
		$this->assertNull($array['rating']);
		$this->assertNull($array['badge']);
		$this->assertEquals([], $array['categories']);
	}

	/**
	 * Test product with zero price
	 */
	public function testProductWithZeroPrice() {
		$product = new Product(
			5,
			'Free Product',
			'free-product',
			'Free Description',
			'USD',
			0.00,
			null,
			'https://example.com/free'
		);

		$this->assertEquals(0.00, $product->price);
	}

	/**
	 * Test product with empty categories
	 */
	public function testProductWithEmptyCategories() {
		$product = new Product(
			6,
			'Uncategorized Product',
			'uncategorized-product',
			'No categories',
			'USD',
			29.99
		);

		$this->assertEquals([], $product->categories);
	}

	/**
	 * Test product with single category
	 */
	public function testProductWithSingleCategory() {
		$product = new Product(
			7,
			'Single Category Product',
			'single-category-product',
			'Single category description',
			'USD',
			39.99,
			null,
			'https://example.com/single',
			null,
			null,
			null,
			['Toys']
		);

		$this->assertEquals(['Toys'], $product->categories);
	}

	/**
	 * Test product with maximum rating
	 */
	public function testProductWithMaximumRating() {
		$product = new Product(
			8,
			'Perfect Product',
			'perfect-product',
			'Perfect description',
			'USD',
			999.99,
			null,
			'https://example.com/perfect',
			null,
			5.0
		);

		$this->assertEquals(5.0, $product->rating);
	}

	/**
	 * Test product with zero rating
	 */
	public function testProductWithZeroRating() {
		$product = new Product(
			9,
			'Zero Rated Product',
			'zero-rated-product',
			'Zero rating description',
			'USD',
			49.99,
			null,
			'https://example.com/zero',
			null,
			0.0
		);

		$this->assertEquals(0.0, $product->rating);
	}
}
