<?php
/**
 * CategoryRepository Unit Tests
 *
 * Tests for category repository CRUD operations
 *
 * @package AffiliateProductShowcase\Tests\Unit\Repositories
 * @since 2.1.0
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Tests\Unit\Repositories;

use AffiliateProductShowcase\Repositories\CategoryRepository;
use AffiliateProductShowcase\Models\Category;
use AffiliateProductShowcase\Exceptions\PluginException;
use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;

/**
 * CategoryRepository Test Class
 *
 * @coversDefaultClass \AffiliateProductShowcase\Repositories\CategoryRepository
 */
final class CategoryRepositoryTest extends TestCase {
	private CategoryRepository $repository;

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		$this->repository = new CategoryRepository();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * @test
	 * @covers ::__construct
	 */
	public function it_can_be_instantiated(): void {
		$this->assertInstanceOf(CategoryRepository::class, $this->repository);
	}

	/**
	 * @test
	 * @covers ::find
	 */
	public function it_returns_null_for_invalid_category_id(): void {
		$result = $this->repository->find(-1);
		$this->assertNull($result);

		$result = $this->repository->find(0);
		$this->assertNull($result);
	}

	/**
	 * @test
	 * @covers ::find
	 */
	public function it_returns_null_when_taxonomy_not_registered(): void {
		Functions\when('taxonomy_exists')->justReturn(false);
		
		$result = $this->repository->find(1);
		$this->assertNull($result);
	}

	/**
	 * @test
	 * @covers ::find
	 */
	public function it_returns_null_when_term_not_found(): void {
		Functions\when('taxonomy_exists')->justReturn(true);
		Functions\when('get_term')->justReturn(false);
		
		$result = $this->repository->find(999);
		$this->assertNull($result);
	}

	/**
	 * @test
	 * @covers ::all
	 */
	public function it_returns_empty_array_when_no_categories_exist(): void {
		Functions\when('get_terms')->justReturn([]);
		
		$result = $this->repository->all();
		$this->assertIsArray($result);
		$this->assertEmpty($result);
	}

	/**
	 * @test
	 * @covers ::paginate
	 */
	public function it_returns_pagination_structure(): void {
		Functions\when('get_terms')->justReturn([]);
		Functions\when('wp_count_terms')->justReturn(0);
		Functions\when('wp_parse_args')->returnArg();
		
		$result = $this->repository->paginate(['number' => 10]);
		
		$this->assertIsArray($result);
		$this->assertArrayHasKey('categories', $result);
		$this->assertArrayHasKey('total', $result);
		$this->assertArrayHasKey('pages', $result);
		$this->assertSame(0, $result['total']);
	}

	/**
	 * @test
	 * @covers ::count
	 */
	public function it_returns_zero_count_when_no_categories(): void {
		Functions\when('wp_count_terms')->justReturn(0);
		
		$result = $this->repository->count();
		$this->assertSame(0, $result);
	}

	/**
	 * @test
	 * @covers ::delete_permanently
	 */
	public function it_throws_exception_when_deleting_invalid_category_id(): void {
		$this->expectException(PluginException::class);
		$this->expectExceptionMessage('Category ID is required.');
		
		$this->repository->delete_permanently(0);
	}

	/**
	 * @test
	 * @covers ::delete_permanently
	 */
	public function it_throws_exception_when_deleting_default_category(): void {
		$mockCategory = $this->createMock(Category::class);
		$mockCategory->is_default = true;
		
		// Mock find to return default category
		$repository = $this->getMockBuilder(CategoryRepository::class)
			->onlyMethods(['find'])
			->getMock();
		$repository->method('find')->willReturn($mockCategory);
		
		$this->expectException(PluginException::class);
		$this->expectExceptionMessage('Cannot delete default category');
		
		$repository->delete_permanently(1);
	}

	/**
	 * @test
	 * @covers ::get_featured
	 */
	public function it_returns_featured_categories(): void {
		Functions\when('get_terms')->justReturn([]);
		
		$result = $this->repository->get_featured();
		$this->assertIsArray($result);
	}

	/**
	 * @test
	 * @covers ::get_top_level
	 */
	public function it_returns_top_level_categories(): void {
		Functions\when('get_terms')->justReturn([]);
		
		$result = $this->repository->get_top_level();
		$this->assertIsArray($result);
	}

	/**
	 * @test
	 * @covers ::search
	 */
	public function it_searches_categories_by_term(): void {
		Functions\when('get_terms')->justReturn([]);
		
		$result = $this->repository->search('electronics');
		$this->assertIsArray($result);
	}

	/**
	 * @test
	 * @covers ::remove_default_from_all_categories
	 */
	public function it_removes_default_flag_from_all_categories(): void {
		Functions\when('get_terms')->justReturn([]);
		Functions\expect('delete_term_meta')->times(0);
		
		$this->repository->remove_default_from_all_categories();
		$this->assertTrue(true); // Assert no exceptions thrown
	}
}
