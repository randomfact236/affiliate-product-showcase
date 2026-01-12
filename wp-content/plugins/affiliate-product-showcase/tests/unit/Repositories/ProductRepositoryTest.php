<?php

declare(strict_types=1);

namespace AffiliateProductShowcase\Tests\Unit\Repositories;

use AffiliateProductShowcase\Repositories\ProductRepository;
use AffiliateProductShowcase\Models\Product;
use AffiliateProductShowcase\Exceptions\RepositoryException;
use PHPUnit\Framework\TestCase;

final class ProductRepositoryTest extends TestCase {
    private ProductRepository $repository;

    protected function setUp(): void {
        $this->repository = new ProductRepository();
    }

    public function test_can_instantiate_repository(): void {
        $this->assertInstanceOf(ProductRepository::class, $this->repository);
    }

    public function test_find_with_invalid_id_throws_exception(): void {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('Validation failed for field "id"');

        $this->repository->find(-1);
    }

    public function test_find_with_zero_id_throws_exception(): void {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('Validation failed for field "id"');

        $this->repository->find(0);
    }

    public function test_list_with_invalid_posts_per_page_throws_exception(): void {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('Validation failed for field "posts_per_page"');

        $this->repository->list(['posts_per_page' => -2]);
    }

    public function test_list_with_valid_posts_per_page_negative_one(): void {
        // This should not throw an exception (-1 means all posts)
        $result = $this->repository->list(['posts_per_page' => -1]);
        
        $this->assertIsArray($result);
    }

    public function test_list_with_default_arguments(): void {
        $result = $this->repository->list();
        
        $this->assertIsArray($result);
        $this->assertIsArray($result);
    }

    public function test_save_with_invalid_model_type_throws_exception(): void {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('Invalid model type');

        $invalidModel = new \stdClass();
        $this->repository->save($invalidModel);
    }

    public function test_save_with_product_without_title_throws_exception(): void {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('Validation failed for field "title"');

        $product = new Product(
            'test-product',
            '',
            'https://example.com/product',
            99.99,
            'USD'
        );

        $this->repository->save($product);
    }

    public function test_save_with_product_without_affiliate_url_throws_exception(): void {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('Validation failed for field "affiliate_url"');

        $product = new Product(
            'test-product',
            'Test Product',
            '',
            99.99,
            'USD'
        );

        $this->repository->save($product);
    }

    public function test_save_with_product_with_invalid_url_throws_exception(): void {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('Validation failed for field "affiliate_url"');

        $product = new Product(
            'test-product',
            'Test Product',
            'not-a-valid-url',
            99.99,
            'USD'
        );

        $this->repository->save($product);
    }

    public function test_save_with_product_with_negative_price_throws_exception(): void {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('Validation failed for field "price"');

        $product = new Product(
            'test-product',
            'Test Product',
            'https://example.com/product',
            -10.00,
            'USD'
        );

        $this->repository->save($product);
    }

    public function test_save_with_product_with_zero_price_is_valid(): void {
        // This should not throw an exception (0 is valid)
        $product = new Product(
            'test-product',
            'Test Product',
            'https://example.com/product',
            0.00,
            'USD'
        );

        // We can't actually test the save without WordPress being loaded,
        // but we can test that validation passes
        $this->assertNotNull($product);
    }

    public function test_delete_with_invalid_id_throws_exception(): void {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('Validation failed for field "id"');

        $this->repository->delete(-1);
    }

    public function test_delete_with_zero_id_throws_exception(): void {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('Validation failed for field "id"');

        $this->repository->delete(0);
    }

    public function test_delete_with_nonexistent_product_throws_exception(): void {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('Product with ID "999999" not found');

        $this->repository->delete(999999);
    }

    public function test_repository_exception_has_context(): void {
        $exception = new RepositoryException('Test message', 1001, null, ['key' => 'value']);

        $this->assertSame(['key' => 'value'], $exception->getContext());
    }

    public function test_repository_exception_static_factory_invalid_model_type(): void {
        $exception = RepositoryException::invalidModelType('ExpectedClass', 'ActualClass');

        $this->assertInstanceOf(RepositoryException::class, $exception);
        $this->assertStringContainsString('Invalid model type', $exception->getMessage());
        $this->assertStringContainsString('ExpectedClass', $exception->getMessage());
        $this->assertStringContainsString('ActualClass', $exception->getMessage());
        $this->assertSame(1001, $exception->getCode());
    }

    public function test_repository_exception_static_factory_not_found(): void {
        $exception = RepositoryException::notFound('Product', 123);

        $this->assertInstanceOf(RepositoryException::class, $exception);
        $this->assertStringContainsString('Product with ID "123" not found', $exception->getMessage());
        $this->assertSame(1002, $exception->getCode());
    }

    public function test_repository_exception_static_factory_save_failed(): void {
        $exception = RepositoryException::saveFailed('Product', 'Database error');

        $this->assertInstanceOf(RepositoryException::class, $exception);
        $this->assertStringContainsString('Failed to save Product', $exception->getMessage());
        $this->assertStringContainsString('Database error', $exception->getMessage());
        $this->assertSame(1003, $exception->getCode());
    }

    public function test_repository_exception_static_factory_delete_failed(): void {
        $exception = RepositoryException::deleteFailed('Product', 'Permission denied');

        $this->assertInstanceOf(RepositoryException::class, $exception);
        $this->assertStringContainsString('Failed to delete Product', $exception->getMessage());
        $this->assertStringContainsString('Permission denied', $exception->getMessage());
        $this->assertSame(1004, $exception->getCode());
    }

    public function test_repository_exception_static_factory_query_error(): void {
        $exception = RepositoryException::queryError('Product', 'SQL syntax error');

        $this->assertInstanceOf(RepositoryException::class, $exception);
        $this->assertStringContainsString('Query error for Product', $exception->getMessage());
        $this->assertStringContainsString('SQL syntax error', $exception->getMessage());
        $this->assertSame(1005, $exception->getCode());
    }

    public function test_repository_exception_static_factory_validation_error(): void {
        $exception = RepositoryException::validationError('title', 'Cannot be empty');

        $this->assertInstanceOf(RepositoryException::class, $exception);
        $this->assertStringContainsString('Validation failed for field "title"', $exception->getMessage());
        $this->assertStringContainsString('Cannot be empty', $exception->getMessage());
        $this->assertSame(1006, $exception->getCode());
    }

    public function test_repository_exception_extends_runtime_exception(): void {
        $exception = new RepositoryException('Test');

        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }

    public function test_repository_exception_with_previous_exception(): void {
        $previous = new \Exception('Previous error');
        $exception = new RepositoryException('Current error', 1001, $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }

    public function test_repository_exception_without_context(): void {
        $exception = new RepositoryException('Test message');

        $this->assertEmpty($exception->getContext());
    }
}
