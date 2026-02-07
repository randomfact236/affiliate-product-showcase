<?php
/**
 * CategoryFactory Unit Tests
 *
 * Tests for category factory pattern implementations
 *
 * @package AffiliateProductShowcase\Tests\Unit\Factories
 * @since 2.1.0
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Tests\Unit\Factories;

use AffiliateProductShowcase\Factories\CategoryFactory;
use AffiliateProductShowcase\Models\Category;
use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;

/**
 * CategoryFactory Test Class
 *
 * @coversDefaultClass \AffiliateProductShowcase\Factories\CategoryFactory
 */
final class CategoryFactoryTest extends TestCase {
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * @test
	 * @covers ::from_array
	 */
	public function it_creates_category_from_array(): void {
		Functions\when('sanitize_text_field')->returnArg();
		Functions\when('sanitize_title')->returnArg();
		Functions\when('sanitize_textarea_field')->returnArg();
		Functions\when('esc_url_raw')->returnArg();
		Functions\when('wp_unique_term_slug')->returnArg();
		
		$data = [
			'id' => 1,
			'name' => 'Test Category',
			'slug' => 'test-category',
			'description' => 'Test Description',
		];

		$category = CategoryFactory::from_array($data);

		$this->assertInstanceOf(Category::class, $category);
		$this->assertSame(1, $category->id);
		$this->assertSame('Test Category', $category->name);
		$this->assertSame('test-category', $category->slug);
		$this->assertSame('Test Description', $category->description);
	}

	/**
	 * @test
	 * @covers ::from_arrays
	 */
	public function it_creates_multiple_categories_from_arrays(): void {
		Functions\when('sanitize_text_field')->returnArg();
		Functions\when('sanitize_title')->returnArg();
		Functions\when('sanitize_textarea_field')->returnArg();
		Functions\when('wp_unique_term_slug')->returnArg();
		
		$data = [
			['id' => 1, 'name' => 'Category 1', 'slug' => 'category-1'],
			['id' => 2, 'name' => 'Category 2', 'slug' => 'category-2'],
			['id' => 3, 'name' => 'Category 3', 'slug' => 'category-3'],
		];

		$categories = CategoryFactory::from_arrays($data);

		$this->assertIsArray($categories);
		$this->assertCount(3, $categories);
		$this->assertContainsOnlyInstancesOf(Category::class, $categories);
		$this->assertSame('Category 1', $categories[0]->name);
		$this->assertSame('Category 2', $categories[1]->name);
		$this->assertSame('Category 3', $categories[2]->name);
	}

	/**
	 * @test
	 * @covers ::from_arrays
	 */
	public function it_skips_invalid_categories_when_creating_from_arrays(): void {
		Functions\when('sanitize_text_field')->returnArg();
		Functions\when('sanitize_title')->returnArg();
		Functions\when('sanitize_textarea_field')->returnArg();
		Functions\when('wp_unique_term_slug')->returnArg();
		
		$data = [
			['id' => 1, 'name' => 'Valid Category', 'slug' => 'valid'],
			['invalid' => 'data'], // Missing required 'name' field
			['id' => 2, 'name' => 'Another Valid', 'slug' => 'another-valid'],
		];

		$categories = CategoryFactory::from_arrays($data);

		$this->assertIsArray($categories);
		$this->assertCount(2, $categories); // Should skip the invalid one
	}

	/**
	 * @test
	 * @covers ::from_wp_terms
	 */
	public function it_creates_categories_from_wp_terms(): void {
		// Mock WP_Term objects
		$term1 = (object) [
			'term_id' => 1,
			'name' => 'Electronics',
			'slug' => 'electronics',
			'description' => 'Electronic products',
			'taxonomy' => 'aps_category',
			'parent' => 0,
			'count' => 10,
			'term_group' => 0,
		];

		$term2 = (object) [
			'term_id' => 2,
			'name' => 'Books',
			'slug' => 'books',
			'description' => 'Book collection',
			'taxonomy' => 'aps_category',
			'parent' => 0,
			'count' => 5,
			'term_group' => 0,
		];

		Functions\when('get_term_meta')->justReturn(false);
		Functions\when('get_option')->justReturn(0);
		Functions\when('current_time')->justReturn('2024-01-15 12:00:00');
		
		$categories = CategoryFactory::from_wp_terms([$term1, $term2]);

		$this->assertIsArray($categories);
		$this->assertCount(2, $categories);
		$this->assertContainsOnlyInstancesOf(Category::class, $categories);
	}

	/**
	 * @test
	 * @covers ::from_wp_terms
	 */
	public function it_skips_invalid_terms_when_creating_from_wp_terms(): void {
		$validTerm = (object) [
			'term_id' => 1,
			'name' => 'Valid',
			'slug' => 'valid',
			'description' => '',
			'taxonomy' => 'aps_category',
			'parent' => 0,
			'count' => 0,
			'term_group' => 0,
		];

		$invalidTerm = (object) [
			'term_id' => 2,
			'name' => 'Invalid',
			'slug' => 'invalid',
			'description' => '',
			'taxonomy' => 'wrong_taxonomy', // Wrong taxonomy
			'parent' => 0,
			'count' => 0,
			'term_group' => 0,
		];

		Functions\when('get_term_meta')->justReturn(false);
		Functions\when('get_option')->justReturn(0);
		Functions\when('current_time')->justReturn('2024-01-15 12:00:00');
		
		$categories = CategoryFactory::from_wp_terms([$validTerm, $invalidTerm]);

		$this->assertIsArray($categories);
		$this->assertCount(1, $categories); // Should skip the invalid taxonomy
	}

	/**
	 * @test
	 * @covers ::sort_by_name
	 */
	public function it_sorts_categories_by_name_ascending(): void {
		$cat1 = new Category(1, 'Zebra', 'zebra');
		$cat2 = new Category(2, 'Apple', 'apple');
		$cat3 = new Category(3, 'Mango', 'mango');
		
		$sorted = CategoryFactory::sort_by_name([$cat1, $cat2, $cat3], 'ASC');
		
		$this->assertSame('Apple', $sorted[0]->name);
		$this->assertSame('Mango', $sorted[1]->name);
		$this->assertSame('Zebra', $sorted[2]->name);
	}

	/**
	 * @test
	 * @covers ::sort_by_name
	 */
	public function it_sorts_categories_by_name_descending(): void {
		$cat1 = new Category(1, 'Apple', 'apple');
		$cat2 = new Category(2, 'Zebra', 'zebra');
		$cat3 = new Category(3, 'Mango', 'mango');
		
		$sorted = CategoryFactory::sort_by_name([$cat1, $cat2, $cat3], 'DESC');
		
		$this->assertSame('Zebra', $sorted[0]->name);
		$this->assertSame('Mango', $sorted[1]->name);
		$this->assertSame('Apple', $sorted[2]->name);
	}

	/**
	 * @test
	 * @covers ::sort_by_count
	 */
	public function it_sorts_categories_by_count(): void {
		$cat1 = new Category(1, 'Low', 'low', '', 0, 5);
		$cat2 = new Category(2, 'High', 'high', '', 0, 50);
		$cat3 = new Category(3, 'Medium', 'medium', '', 0, 20);
		
		$sorted = CategoryFactory::sort_by_count([$cat1, $cat2, $cat3], 'DESC');
		
		$this->assertSame(50, $sorted[0]->count);
		$this->assertSame(20, $sorted[1]->count);
		$this->assertSame(5, $sorted[2]->count);
	}

	/**
	 * @test
	 * @covers ::filter_by_featured
	 */
	public function it_filters_featured_categories(): void {
		$cat1 = new Category(1, 'Featured 1', 'featured-1', '', 0, 0, true);
		$cat2 = new Category(2, 'Not Featured', 'not-featured', '', 0, 0, false);
		$cat3 = new Category(3, 'Featured 2', 'featured-2', '', 0, 0, true);
		
		$featured = CategoryFactory::filter_by_featured([$cat1, $cat2, $cat3], true);
		
		$this->assertCount(2, $featured);
		$this->assertTrue($featured[0]->featured);
		$this->assertTrue($featured[2]->featured);
	}

	/**
	 * @test
	 * @covers ::filter_by_parent
	 */
	public function it_filters_categories_by_parent(): void {
		$cat1 = new Category(1, 'Top Level', 'top-level', '', 0);
		$cat2 = new Category(2, 'Child 1', 'child-1', '', 1);
		$cat3 = new Category(3, 'Child 2', 'child-2', '', 1);
		$cat4 = new Category(4, 'Another Top', 'another-top', '', 0);
		
		$topLevel = CategoryFactory::filter_by_parent([$cat1, $cat2, $cat3, $cat4], 0);
		$children = CategoryFactory::filter_by_parent([$cat1, $cat2, $cat3, $cat4], 1);
		
		$this->assertCount(2, $topLevel);
		$this->assertCount(2, $children);
	}
}
