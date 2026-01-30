<?php
/**
 * Category Model Unit Tests
 *
 * Tests for category model properties and methods
 *
 * @package AffiliateProductShowcase\Tests\Unit\Models
 * @since 2.1.0
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Tests\Unit\Models;

use AffiliateProductShowcase\Models\Category;
use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;

/**
 * Category Test Class
 *
 * @coversDefaultClass \AffiliateProductShowcase\Models\Category
 */
final class CategoryTest extends TestCase {
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
	 * @covers ::__construct
	 */
	public function it_can_be_instantiated_with_all_properties(): void {
		$category = new Category(
			1,
			'Electronics',
			'electronics',
			'Electronic products',
			0,
			10,
			true,
			'https://example.com/image.jpg',
			'date',
			'2024-01-01 00:00:00',
			'published',
			false
		);

		$this->assertSame(1, $category->id);
		$this->assertSame('Electronics', $category->name);
		$this->assertSame('electronics', $category->slug);
		$this->assertSame('Electronic products', $category->description);
		$this->assertSame(0, $category->parent_id);
		$this->assertSame(10, $category->count);
		$this->assertTrue($category->featured);
		$this->assertSame('https://example.com/image.jpg', $category->image_url);
		$this->assertSame('date', $category->sort_order);
		$this->assertSame('2024-01-01 00:00:00', $category->created_at);
		$this->assertSame('published', $category->status);
		$this->assertFalse($category->is_default);
	}

	/**
	 * @test
	 * @covers ::__construct
	 */
	public function it_can_be_instantiated_with_minimal_properties(): void {
		Functions\when('current_time')->justReturn('2024-01-15 12:00:00');
		
		$category = new Category(
			0,
			'New Category',
			'new-category'
		);

		$this->assertSame(0, $category->id);
		$this->assertSame('New Category', $category->name);
		$this->assertSame('new-category', $category->slug);
		$this->assertSame('', $category->description);
		$this->assertSame(0, $category->parent_id);
		$this->assertSame(0, $category->count);
		$this->assertFalse($category->featured);
		$this->assertNull($category->image_url);
		$this->assertSame('date', $category->sort_order);
		$this->assertSame('published', $category->status);
		$this->assertFalse($category->is_default);
	}

	/**
	 * @test
	 * @covers ::to_array
	 */
	public function it_converts_to_array_properly(): void {
		$category = new Category(
			5,
			'Books',
			'books',
			'Book collection',
			0,
			25,
			true,
			'https://example.com/books.jpg',
			'name',
			'2024-02-01 10:30:00',
			'published',
			false
		);

		$array = $category->to_array();

		$this->assertIsArray($array);
		$this->assertSame(5, $array['id']);
		$this->assertSame('Books', $array['name']);
		$this->assertSame('books', $array['slug']);
		$this->assertSame('Book collection', $array['description']);
		$this->assertSame(0, $array['parent_id']);
		$this->assertSame(25, $array['count']);
		$this->assertTrue($array['featured']);
		$this->assertSame('https://example.com/books.jpg', $array['image_url']);
		$this->assertSame('name', $array['sort_order']);
		$this->assertSame('2024-02-01 10:30:00', $array['created_at']);
		$this->assertSame('published', $array['status']);
		$this->assertFalse($array['is_default']);
		$this->assertArrayHasKey('taxonomy', $array);
	}

	/**
	 * @test
	 * @covers ::from_array
	 */
	public function it_creates_instance_from_array(): void {
		Functions\when('sanitize_text_field')->returnArg();
		Functions\when('sanitize_title')->returnArg();
		Functions\when('sanitize_textarea_field')->returnArg();
		Functions\when('esc_url_raw')->returnArg();
		Functions\when('wp_unique_term_slug')->returnArg();
		
		$data = [
			'id' => 3,
			'name' => 'Gaming',
			'slug' => 'gaming',
			'description' => 'Gaming products',
			'parent_id' => 1,
			'count' => 15,
			'featured' => true,
			'image_url' => 'https://example.com/gaming.jpg',
			'sort_order' => 'date',
			'status' => 'published',
			'is_default' => false
		];

		$category = Category::from_array($data);

		$this->assertInstanceOf(Category::class, $category);
		$this->assertSame(3, $category->id);
		$this->assertSame('Gaming', $category->name);
		$this->assertSame('gaming', $category->slug);
	}

	/**
	 * @test
	 * @covers ::from_array
	 */
	public function it_throws_exception_when_name_is_missing(): void {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Category name is required.');
		
		Category::from_array([]);
	}

	/**
	 * @test
	 * @covers ::from_array
	 */
	public function it_generates_slug_from_name_when_missing(): void {
		Functions\when('sanitize_text_field')->returnArg();
		Functions\when('sanitize_title')->justReturn('auto-generated-slug');
		Functions\when('sanitize_textarea_field')->returnArg();
		Functions\when('wp_unique_term_slug')->returnArg();
		
		$category = Category::from_array(['name' => 'Auto Generated Slug']);
		
		$this->assertSame('auto-generated-slug', $category->slug);
	}

	/**
	 * @test
	 * @covers ::has_parent
	 */
	public function it_checks_if_category_has_parent(): void {
		$topLevel = new Category(1, 'Top Level', 'top-level', '', 0);
		$child = new Category(2, 'Child', 'child', '', 1);
		
		$this->assertFalse($topLevel->has_parent());
		$this->assertTrue($child->has_parent());
	}

	/**
	 * @test
	 * @covers ::get_children
	 */
	public function it_returns_empty_array_when_no_children(): void {
		Functions\when('get_terms')->justReturn([]);
		
		$category = new Category(1, 'Parent', 'parent');
		$children = $category->get_children();
		
		$this->assertIsArray($children);
		$this->assertEmpty($children);
	}
}
