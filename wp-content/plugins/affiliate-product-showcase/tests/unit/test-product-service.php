<?php
declare(strict_types=1);

use AffiliateProductShowcase\Exceptions\PluginException;
use AffiliateProductShowcase\Models\Product;
use AffiliateProductShowcase\Services\ProductService;
use PHPUnit\Framework\TestCase;
use Mockery;

final class Test_Product_Service extends TestCase {
    private ProductService $service;
    private $mock_repository;
    private $mock_validator;
    private $mock_factory;
    private $mock_formatter;

    protected function setUp(): void {
        parent::setUp();
        
        // Create mocks for dependencies
        $this->mock_repository = Mockery::mock('overload:AffiliateProductShowcase\Repositories\ProductRepository');
        $this->mock_validator = Mockery::mock('overload:AffiliateProductShowcase\Validators\ProductValidator');
        $this->mock_factory = Mockery::mock('overload:AffiliateProductShowcase\Factories\ProductFactory');
        $this->mock_formatter = Mockery::mock('overload:AffiliateProductShowcase\Formatters\PriceFormatter');
        
        $this->service = new ProductService();
    }

    protected function tearDown(): void {
        Mockery::close();
        parent::tearDown();
    }

    public function test_boot_method_exists_and_returns_void(): void {
        $this->assertNull($this->service->boot());
    }

    public function test_get_product_returns_product_when_found(): void {
        $product = new Product(
            1,
            'Test Product',
            'test-product',
            'Test Description',
            'USD',
            99.99,
            'https://example.com/product'
        );

        $this->mock_repository->shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($product);

        $result = $this->service->get_product(1);

        $this->assertInstanceOf(Product::class, $result);
        $this->assertSame(1, $result->id);
        $this->assertSame('Test Product', $result->title);
    }

    public function test_get_product_returns_null_when_not_found(): void {
        $this->mock_repository->shouldReceive('find')
            ->once()
            ->with(999)
            ->andReturn(null);

        $result = $this->service->get_product(999);

        $this->assertNull($result);
    }

    public function test_get_products_returns_array_of_products(): void {
        $products = [
            new Product(1, 'Product 1', 'product-1', 'Desc 1', 'USD', 10.00, 'url1'),
            new Product(2, 'Product 2', 'product-2', 'Desc 2', 'USD', 20.00, 'url2'),
        ];

        $this->mock_repository->shouldReceive('list')
            ->once()
            ->with(['limit' => 10])
            ->andReturn($products);

        $result = $this->service->get_products(['limit' => 10]);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertInstanceOf(Product::class, $result[0]);
        $this->assertSame(1, $result[0]->id);
    }

    public function test_get_products_with_empty_args(): void {
        $products = [];

        $this->mock_repository->shouldReceive('list')
            ->once()
            ->with([])
            ->andReturn($products);

        $result = $this->service->get_products([]);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_create_or_update_creates_new_product(): void {
        $data = [
            'title' => 'New Product',
            'slug' => 'new-product',
            'description' => 'Description',
            'currency' => 'USD',
            'price' => 99.99,
            'affiliate_url' => 'https://example.com/new'
        ];

        $cleanData = $data;
        $product = new Product(
            0,
            $data['title'],
            $data['slug'],
            $data['description'],
            $data['currency'],
            $data['price'],
            $data['affiliate_url']
        );

        $this->mock_validator->shouldReceive('validate')
            ->once()
            ->with($data)
            ->andReturn($cleanData);

        $this->mock_factory->shouldReceive('from_array')
            ->once()
            ->with($cleanData)
            ->andReturn($product);

        $this->mock_repository->shouldReceive('save')
            ->once()
            ->with($product)
            ->andReturn(123);

        $this->mock_repository->shouldReceive('find')
            ->once()
            ->with(123)
            ->andReturn($product);

        $result = $this->service->create_or_update($data);

        $this->assertInstanceOf(Product::class, $result);
        $this->assertSame('New Product', $result->title);
    }

    public function test_create_or_update_throws_exception_on_save_failure(): void {
        $data = [
            'title' => 'Failed Product',
            'slug' => 'failed-product',
            'description' => 'Description',
            'currency' => 'USD',
            'price' => 99.99,
            'affiliate_url' => 'https://example.com/failed'
        ];

        $cleanData = $data;
        $product = new Product(
            0,
            $data['title'],
            $data['slug'],
            $data['description'],
            $data['currency'],
            $data['price'],
            $data['affiliate_url']
        );

        $this->mock_validator->shouldReceive('validate')
            ->once()
            ->with($data)
            ->andReturn($cleanData);

        $this->mock_factory->shouldReceive('from_array')
            ->once()
            ->with($cleanData)
            ->andReturn($product);

        $this->mock_repository->shouldReceive('save')
            ->once()
            ->with($product)
            ->andReturn(false);

        $this->expectException(PluginException::class);
        $this->expectExceptionMessage('Unable to save product.');

        $this->service->create_or_update($data);
    }

    public function test_delete_returns_true_on_success(): void {
        $this->mock_repository->shouldReceive('delete')
            ->once()
            ->with(1)
            ->andReturn(true);

        $result = $this->service->delete(1);

        $this->assertTrue($result);
    }

    public function test_delete_returns_false_on_failure(): void {
        $this->mock_repository->shouldReceive('delete')
            ->once()
            ->with(999)
            ->andReturn(false);

        $result = $this->service->delete(999);

        $this->assertFalse($result);
    }

    public function test_format_price_with_usd(): void {
        $this->mock_formatter->shouldReceive('format')
            ->once()
            ->with(99.99, 'USD')
            ->andReturn('$99.99');

        $result = $this->service->format_price(99.99, 'USD');

        $this->assertSame('$99.99', $result);
    }

    public function test_format_price_with_eur(): void {
        $this->mock_formatter->shouldReceive('format')
            ->once()
            ->with(49.50, 'EUR')
            ->andReturn('€49.50');

        $result = $this->service->format_price(49.50, 'EUR');

        $this->assertSame('€49.50', $result);
    }

    public function test_format_price_with_gbp(): void {
        $this->mock_formatter->shouldReceive('format')
            ->once()
            ->with(199.99, 'GBP')
            ->andReturn('£199.99');

        $result = $this->service->format_price(199.99, 'GBP');

        $this->assertSame('£199.99', $result);
    }

    public function test_format_price_with_zero_price(): void {
        $this->mock_formatter->shouldReceive('format')
            ->once()
            ->with(0.00, 'USD')
            ->andReturn('$0.00');

        $result = $this->service->format_price(0.00, 'USD');

        $this->assertSame('$0.00', $result);
    }

    public function test_format_price_with_very_large_price(): void {
        $this->mock_formatter->shouldReceive('format')
            ->once()
            ->with(999999.99, 'USD')
            ->andReturn('$999,999.99');

        $result = $this->service->format_price(999999.99, 'USD');

        $this->assertSame('$999,999.99', $result);
    }

    public function test_create_or_update_with_product_categories(): void {
        $data = [
            'title' => 'Product with Categories',
            'slug' => 'product-categories',
            'description' => 'Description',
            'currency' => 'USD',
            'price' => 79.99,
            'affiliate_url' => 'https://example.com/product',
            'categories' => ['electronics', 'gadgets']
        ];

        $cleanData = $data;
        $product = new Product(
            0,
            $data['title'],
            $data['slug'],
            $data['description'],
            $data['currency'],
            $data['price'],
            $data['affiliate_url'],
            null,
            null,
            null,
            $data['categories']
        );

        $this->mock_validator->shouldReceive('validate')
            ->once()
            ->with($data)
            ->andReturn($cleanData);

        $this->mock_factory->shouldReceive('from_array')
            ->once()
            ->with($cleanData)
            ->andReturn($product);

        $this->mock_repository->shouldReceive('save')
            ->once()
            ->with($product)
            ->andReturn(456);

        $this->mock_repository->shouldReceive('find')
            ->once()
            ->with(456)
            ->andReturn($product);

        $result = $this->service->create_or_update($data);

        $this->assertInstanceOf(Product::class, $result);
        $this->assertSame(['electronics', 'gadgets'], $result->categories);
    }

    public function test_get_products_with_limit_and_offset(): void {
        $products = [
            new Product(1, 'Product 1', 'product-1', 'Desc 1', 'USD', 10.00, 'url1'),
        ];

        $this->mock_repository->shouldReceive('list')
            ->once()
            ->with(['limit' => 5, 'offset' => 10])
            ->andReturn($products);

        $result = $this->service->get_products(['limit' => 5, 'offset' => 10]);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
    }

    public function test_create_or_update_with_rating(): void {
        $data = [
            'title' => 'Rated Product',
            'slug' => 'rated-product',
            'description' => 'Description',
            'currency' => 'USD',
            'price' => 59.99,
            'affiliate_url' => 'https://example.com/rated',
            'rating' => 4.5
        ];

        $cleanData = $data;
        $product = new Product(
            0,
            $data['title'],
            $data['slug'],
            $data['description'],
            $data['currency'],
            $data['price'],
            $data['affiliate_url'],
            null,
            $data['rating']
        );

        $this->mock_validator->shouldReceive('validate')
            ->once()
            ->with($data)
            ->andReturn($cleanData);

        $this->mock_factory->shouldReceive('from_array')
            ->once()
            ->with($cleanData)
            ->andReturn($product);

        $this->mock_repository->shouldReceive('save')
            ->once()
            ->with($product)
            ->andReturn(789);

        $this->mock_repository->shouldReceive('find')
            ->once()
            ->with(789)
            ->andReturn($product);

        $result = $this->service->create_or_update($data);

        $this->assertInstanceOf(Product::class, $result);
        $this->assertSame(4.5, $result->rating);
    }

    public function test_create_or_update_with_image_url(): void {
        $data = [
            'title' => 'Product with Image',
            'slug' => 'product-image',
            'description' => 'Description',
            'currency' => 'USD',
            'price' => 69.99,
            'affiliate_url' => 'https://example.com/product',
            'image_url' => 'https://example.com/image.jpg'
        ];

        $cleanData = $data;
        $product = new Product(
            0,
            $data['title'],
            $data['slug'],
            $data['description'],
            $data['currency'],
            $data['price'],
            $data['affiliate_url'],
            $data['image_url']
        );

        $this->mock_validator->shouldReceive('validate')
            ->once()
            ->with($data)
            ->andReturn($cleanData);

        $this->mock_factory->shouldReceive('from_array')
            ->once()
            ->with($cleanData)
            ->andReturn($product);

        $this->mock_repository->shouldReceive('save')
            ->once()
            ->with($product)
            ->andReturn(321);

        $this->mock_repository->shouldReceive('find')
            ->once()
            ->with(321)
            ->andReturn($product);

        $result = $this->service->create_or_update($data);

        $this->assertInstanceOf(Product::class, $result);
        $this->assertSame('https://example.com/image.jpg', $result->image_url);
    }

    public function test_get_product_with_invalid_id_returns_null(): void {
        $this->mock_repository->shouldReceive('find')
            ->once()
            ->with(0)
            ->andReturn(null);

        $result = $this->service->get_product(0);

        $this->assertNull($result);
    }

    public function test_delete_with_non_existent_id(): void {
        $this->mock_repository->shouldReceive('delete')
            ->once()
            ->with(99999)
            ->andReturn(false);

        $result = $this->service->delete(99999);

        $this->assertFalse($result);
    }

    public function test_format_price_with_negative_price(): void {
        $this->mock_formatter->shouldReceive('format')
            ->once()
            ->with(-10.00, 'USD')
            ->andReturn('-$10.00');

        $result = $this->service->format_price(-10.00, 'USD');

        $this->assertSame('-$10.00', $result);
    }

    public function test_format_price_with_special_currencies(): void {
        $currencies = ['JPY', 'CAD', 'AUD', 'CHF', 'INR'];

        foreach ($currencies as $currency) {
            $this->mock_formatter->shouldReceive('format')
                ->once()
                ->with(100.00, $currency)
                ->andReturn($currency . ' 100.00');

            $result = $this->service->format_price(100.00, $currency);
            $this->assertStringContainsString($currency, $result);
        }
    }
}
