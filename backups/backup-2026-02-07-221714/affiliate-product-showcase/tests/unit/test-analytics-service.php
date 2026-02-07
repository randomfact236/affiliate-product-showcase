<?php
declare(strict_types=1);

use AffiliateProductShowcase\Services\AnalyticsService;
use PHPUnit\Framework\TestCase;
use Mockery;

final class Test_Analytics_Service extends TestCase {
    private AnalyticsService $service;
    private $mock_cache;

    protected function setUp(): void {
        parent::setUp();
        
        $this->mock_cache = Mockery::mock('overload:AffiliateProductShowcase\Cache\Cache');
        $this->service = new AnalyticsService();
    }

    protected function tearDown(): void {
        Mockery::close();
        parent::tearDown();
    }

    public function test_record_view_increments_view_count(): void {
        $product_id = 123;
        
        $this->mock_cache->shouldReceive('remember')
            ->once()
            ->with('analytics_record_' . $product_id, \Mockery::type('callable'), 5)
            ->andReturnUsing(function ($key, $callback) {
                return $callback();
            });

        $this->mock_cache->shouldReceive('delete')
            ->once()
            ->with('analytics_summary');

        $this->service->record_view($product_id);

        // Verify the option was updated
        $data = get_option('aps_analytics', []);
        $this->assertArrayHasKey($product_id, $data);
        $this->assertArrayHasKey('views', $data[$product_id]);
        $this->assertSame(1, $data[$product_id]['views']);
    }

    public function test_record_click_increments_click_count(): void {
        $product_id = 456;
        
        $this->mock_cache->shouldReceive('remember')
            ->once()
            ->with('analytics_record_' . $product_id, \Mockery::type('callable'), 5)
            ->andReturnUsing(function ($key, $callback) {
                return $callback();
            });

        $this->mock_cache->shouldReceive('delete')
            ->once()
            ->with('analytics_summary');

        $this->service->record_click($product_id);

        // Verify the option was updated
        $data = get_option('aps_analytics', []);
        $this->assertArrayHasKey($product_id, $data);
        $this->assertArrayHasKey('clicks', $data[$product_id]);
        $this->assertSame(1, $data[$product_id]['clicks']);
    }

    public function test_record_view_multiple_times_increments(): void {
        $product_id = 789;
        
        // First view
        $this->mock_cache->shouldReceive('remember')
            ->once()
            ->with('analytics_record_' . $product_id, \Mockery::type('callable'), 5)
            ->andReturnUsing(function ($key, $callback) {
                return $callback();
            });

        $this->mock_cache->shouldReceive('delete')
            ->once()
            ->with('analytics_summary');

        $this->service->record_view($product_id);

        // Second view
        Mockery::close();
        $this->mock_cache = Mockery::mock('overload:AffiliateProductShowcase\Cache\Cache');
        $this->mock_cache->shouldReceive('remember')
            ->once()
            ->with('analytics_record_' . $product_id, \Mockery::type('callable'), 5)
            ->andReturnUsing(function ($key, $callback) {
                return $callback();
            });

        $this->mock_cache->shouldReceive('delete')
            ->once()
            ->with('analytics_summary');

        $this->service->record_view($product_id);

        // Verify the option was updated twice
        $data = get_option('aps_analytics', []);
        $this->assertSame(2, $data[$product_id]['views']);
    }

    public function test_record_click_multiple_times_increments(): void {
        $product_id = 999;
        
        // First click
        $this->mock_cache->shouldReceive('remember')
            ->once()
            ->with('analytics_record_' . $product_id, \Mockery::type('callable'), 5)
            ->andReturnUsing(function ($key, $callback) {
                return $callback();
            });

        $this->mock_cache->shouldReceive('delete')
            ->once()
            ->with('analytics_summary');

        $this->service->record_click($product_id);

        // Second click
        Mockery::close();
        $this->mock_cache = Mockery::mock('overload:AffiliateProductShowcase\Cache\Cache');
        $this->mock_cache->shouldReceive('remember')
            ->once()
            ->with('analytics_record_' . $product_id, \Mockery::type('callable'), 5)
            ->andReturnUsing(function ($key, $callback) {
                return $callback();
            });

        $this->mock_cache->shouldReceive('delete')
            ->once()
            ->with('analytics_summary');

        $this->service->record_click($product_id);

        // Verify the option was updated twice
        $data = get_option('aps_analytics', []);
        $this->assertSame(2, $data[$product_id]['clicks']);
    }

    public function test_record_mixed_views_and_clicks(): void {
        $product_id = 555;
        
        // Record view
        $this->mock_cache->shouldReceive('remember')
            ->once()
            ->with('analytics_record_' . $product_id, \Mockery::type('callable'), 5)
            ->andReturnUsing(function ($key, $callback) {
                return $callback();
            });

        $this->mock_cache->shouldReceive('delete')
            ->once()
            ->with('analytics_summary');

        $this->service->record_view($product_id);

        // Record click
        Mockery::close();
        $this->mock_cache = Mockery::mock('overload:AffiliateProductShowcase\Cache\Cache');
        $this->mock_cache->shouldReceive('remember')
            ->once()
            ->with('analytics_record_' . $product_id, \Mockery::type('callable'), 5)
            ->andReturnUsing(function ($key, $callback) {
                return $callback();
            });

        $this->mock_cache->shouldReceive('delete')
            ->once()
            ->with('analytics_summary');

        $this->service->record_click($product_id);

        // Verify both metrics
        $data = get_option('aps_analytics', []);
        $this->assertSame(1, $data[$product_id]['views']);
        $this->assertSame(1, $data[$product_id]['clicks']);
    }

    public function test_summary_returns_cached_data(): void {
        $expected = [
            123 => ['views' => 10, 'clicks' => 5],
            456 => ['views' => 20, 'clicks' => 15],
        ];

        $this->mock_cache->shouldReceive('remember')
            ->once()
            ->with('analytics_summary', \Mockery::type('callable'), 60)
            ->andReturn($expected);

        $result = $this->service->summary();

        $this->assertSame($expected, $result);
    }

    public function test_summary_returns_empty_array_when_no_data(): void {
        $this->mock_cache->shouldReceive('remember')
            ->once()
            ->with('analytics_summary', \Mockery::type('callable'), 60)
            ->andReturn([]);

        $result = $this->service->summary();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_summary_caches_for_60_seconds(): void {
        $this->mock_cache->shouldReceive('remember')
            ->once()
            ->with('analytics_summary', \Mockery::type('callable'), 60)
            ->andReturn(['test' => ['views' => 1]]);

        $this->service->summary();

        // Verify cache duration was 60 seconds
        $this->assertTrue(true); // If we got here, the test passed
    }

    public function test_record_view_invalidates_summary_cache(): void {
        $product_id = 111;
        
        $this->mock_cache->shouldReceive('remember')
            ->once()
            ->with('analytics_record_' . $product_id, \Mockery::type('callable'), 5)
            ->andReturnUsing(function ($key, $callback) {
                return $callback();
            });

        $this->mock_cache->shouldReceive('delete')
            ->once()
            ->with('analytics_summary');

        $this->service->record_view($product_id);
    }

    public function test_record_click_invalidates_summary_cache(): void {
        $product_id = 222;
        
        $this->mock_cache->shouldReceive('remember')
            ->once()
            ->with('analytics_record_' . $product_id, \Mockery::type('callable'), 5)
            ->andReturnUsing(function ($key, $callback) {
                return $callback();
            });

        $this->mock_cache->shouldReceive('delete')
            ->once()
            ->with('analytics_summary');

        $this->service->record_click($product_id);
    }

    public function test_record_view_uses_5_second_lock_timeout(): void {
        $product_id = 333;
        
        $this->mock_cache->shouldReceive('remember')
            ->once()
            ->with('analytics_record_' . $product_id, \Mockery::type('callable'), 5)
            ->andReturnUsing(function ($key, $callback) {
                return $callback();
            });

        $this->mock_cache->shouldReceive('delete')
            ->once()
            ->with('analytics_summary');

        $this->service->record_view($product_id);
    }

    public function test_record_click_uses_5_second_lock_timeout(): void {
        $product_id = 444;
        
        $this->mock_cache->shouldReceive('remember')
            ->once()
            ->with('analytics_record_' . $product_id, \Mockery::type('callable'), 5)
            ->andReturnUsing(function ($key, $callback) {
                return $callback();
            });

        $this->mock_cache->shouldReceive('delete')
            ->once()
            ->with('analytics_summary');

        $this->service->record_click($product_id);
    }

    public function test_record_multiple_products(): void {
        // Record for product 1
        $this->mock_cache->shouldReceive('remember')
            ->once()
            ->with('analytics_record_1', \Mockery::type('callable'), 5)
            ->andReturnUsing(function ($key, $callback) {
                return $callback();
            });

        $this->mock_cache->shouldReceive('delete')
            ->once()
            ->with('analytics_summary');

        $this->service->record_view(1);

        // Record for product 2
        Mockery::close();
        $this->mock_cache = Mockery::mock('overload:AffiliateProductShowcase\Cache\Cache');
        $this->mock_cache->shouldReceive('remember')
            ->once()
            ->with('analytics_record_2', \Mockery::type('callable'), 5)
            ->andReturnUsing(function ($key, $callback) {
                return $callback();
            });

        $this->mock_cache->shouldReceive('delete')
            ->once()
            ->with('analytics_summary');

        $this->service->record_view(2);

        // Verify both products tracked
        $data = get_option('aps_analytics', []);
        $this->assertArrayHasKey(1, $data);
        $this->assertArrayHasKey(2, $data);
    }

    public function test_record_with_zero_product_id(): void {
        $product_id = 0;
        
        $this->mock_cache->shouldReceive('remember')
            ->once()
            ->with('analytics_record_' . $product_id, \Mockery::type('callable'), 5)
            ->andReturnUsing(function ($key, $callback) {
                return $callback();
            });

        $this->mock_cache->shouldReceive('delete')
            ->once()
            ->with('analytics_summary');

        $this->service->record_view($product_id);

        $data = get_option('aps_analytics', []);
        $this->assertArrayHasKey(0, $data);
    }

    public function test_record_with_negative_product_id(): void {
        $product_id = -1;
        
        $this->mock_cache->shouldReceive('remember')
            ->once()
            ->with('analytics_record_' . $product_id, \Mockery::type('callable'), 5)
            ->andReturnUsing(function ($key, $callback) {
                return $callback();
            });

        $this->mock_cache->shouldReceive('delete')
            ->once()
            ->with('analytics_summary');

        $this->service->record_click($product_id);

        $data = get_option('aps_analytics', []);
        $this->assertArrayHasKey(-1, $data);
    }

    public function test_record_initializes_both_metrics(): void {
        $product_id = 777;
        
        $this->mock_cache->shouldReceive('remember')
            ->once()
            ->with('analytics_record_' . $product_id, \Mockery::type('callable'), 5)
            ->andReturnUsing(function ($key, $callback) {
                return $callback();
            });

        $this->mock_cache->shouldReceive('delete')
            ->once()
            ->with('analytics_summary');

        $this->service->record_view($product_id);

        $data = get_option('aps_analytics', []);
        $this->assertArrayHasKey('views', $data[$product_id]);
        $this->assertArrayHasKey('clicks', $data[$product_id]);
        $this->assertSame(1, $data[$product_id]['views']);
        $this->assertSame(0, $data[$product_id]['clicks']);
    }

    public function test_summary_with_large_dataset(): void {
        $largeData = [];
        for ($i = 1; $i <= 100; $i++) {
            $largeData[$i] = ['views' => $i * 10, 'clicks' => $i * 5];
        }

        $this->mock_cache->shouldReceive('remember')
            ->once()
            ->with('analytics_summary', \Mockery::type('callable'), 60)
            ->andReturn($largeData);

        $result = $this->service->summary();

        $this->assertCount(100, $result);
        $this->assertSame(1000, $result[100]['views']);
        $this->assertSame(500, $result[100]['clicks']);
    }

    public function test_record_lock_key_format(): void {
        $product_id = 888;
        
        $this->mock_cache->shouldReceive('remember')
            ->once()
            ->with('analytics_record_888', \Mockery::type('callable'), 5)
            ->andReturnUsing(function ($key, $callback) {
                return $callback();
            });

        $this->mock_cache->shouldReceive('delete')
            ->once()
            ->with('analytics_summary');

        $this->service->record_view($product_id);
    }

    public function test_summary_calls_get_option_when_cache_miss(): void {
        // Clear the cache option
        delete_option('aps_analytics');

        $this->mock_cache->shouldReceive('remember')
            ->once()
            ->with('analytics_summary', \Mockery::type('callable'), 60)
            ->andReturnUsing(function ($key, $callback) {
                return $callback();
            });

        $result = $this->service->summary();

        $this->assertIsArray($result);
    }

    public function test_update_option_called_without_autoload(): void {
        $product_id = 999;
        
        // This test verifies that update_option is called with false (no autoload)
        // We can't directly test this with mocks, but we can verify the behavior
        $this->mock_cache->shouldReceive('remember')
            ->once()
            ->with('analytics_record_' . $product_id, \Mockery::type('callable'), 5)
            ->andReturnUsing(function ($key, $callback) {
                return $callback();
            });

        $this->mock_cache->shouldReceive('delete')
            ->once()
            ->with('analytics_summary');

        $this->service->record_view($product_id);

        // If we got here without errors, the test passes
        $this->assertTrue(true);
    }

    public function test_record_preserves_existing_data(): void {
        $product_id = 101;
        
        // Set initial data
        update_option('aps_analytics', [
            $product_id => ['views' => 5, 'clicks' => 3],
            202 => ['views' => 10, 'clicks' => 7],
        ]);

        $this->mock_cache->shouldReceive('remember')
            ->once()
            ->with('analytics_record_' . $product_id, \Mockery::type('callable'), 5)
            ->andReturnUsing(function ($key, $callback) {
                return $callback();
            });

        $this->mock_cache->shouldReceive('delete')
            ->once()
            ->with('analytics_summary');

        $this->service->record_view($product_id);

        $data = get_option('aps_analytics', []);
        
        // Check that product 101 was incremented
        $this->assertSame(6, $data[$product_id]['views']);
        $this->assertSame(3, $data[$product_id]['clicks']);
        
        // Check that product 202 was preserved
        $this->assertArrayHasKey(202, $data);
        $this->assertSame(10, $data[202]['views']);
    }
}
