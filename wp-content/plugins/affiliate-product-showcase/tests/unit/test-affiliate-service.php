<?php
declare(strict_types=1);

use AffiliateProductShowcase\Services\AffiliateService;
use AffiliateProductShowcase\Models\AffiliateLink;
use PHPUnit\Framework\TestCase;
use Mockery;

final class Test_Affiliate_Service extends TestCase {
    private AffiliateService $service;
    private $mock_settings_repository;

    protected function setUp(): void {
        parent::setUp();
        
        $this->mock_settings_repository = Mockery::mock('overload:AffiliateProductShowcase\Repositories\SettingsRepository');
        $this->service = new AffiliateService();
    }

    protected function tearDown(): void {
        Mockery::close();
        parent::tearDown();
    }

    public function test_build_link_with_valid_url(): void {
        $this->mock_settings_repository->shouldReceive('get_settings')
            ->once()
            ->andReturn(['affiliate_id' => '']);

        $link = $this->service->build_link('https://example.com/product');

        $this->assertInstanceOf(AffiliateLink::class, $link);
        $this->assertSame('https://example.com/product', $link->get_url());
    }

    public function test_build_link_with_tracking_id(): void {
        $this->mock_settings_repository->shouldReceive('get_settings')
            ->once()
            ->andReturn(['affiliate_id' => 'my-affiliate-123']);

        $link = $this->service->build_link('https://example.com/product');

        $this->assertInstanceOf(AffiliateLink::class, $link);
        $this->assertStringContainsString('aff_id=my-affiliate-123', $link->get_url());
    }

    public function test_build_link_with_existing_query_params(): void {
        $this->mock_settings_repository->shouldReceive('get_settings')
            ->once()
            ->andReturn(['affiliate_id' => 'aff-123']);

        $link = $this->service->build_link('https://example.com/product?param1=value1');

        $this->assertInstanceOf(AffiliateLink::class, $link);
        $this->assertStringContainsString('param1=value1', $link->get_url());
        $this->assertStringContainsString('aff_id=aff-123', $link->get_url());
        $this->assertStringContainsString('&aff_id=', $link->get_url());
    }

    public function test_build_link_throws_exception_for_empty_url(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Affiliate URL cannot be empty.');

        $this->service->build_link('');
    }

    public function test_build_link_throws_exception_for_invalid_url(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid URL format.');

        $this->service->build_link('not-a-valid-url');
    }

    public function test_build_link_throws_exception_for_blocked_domain_google_analytics(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('URL contains blocked domain: google-analytics.com');

        $this->mock_settings_repository->shouldReceive('get_settings')
            ->once()
            ->andReturn(['affiliate_id' => '']);

        $this->service->build_link('https://google-analytics.com/tracking');
    }

    public function test_build_link_throws_exception_for_blocked_domain_facebook(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('URL contains blocked domain: facebook.com');

        $this->mock_settings_repository->shouldReceive('get_settings')
            ->once()
            ->andReturn(['affiliate_id' => '']);

        $this->service->build_link('https://facebook.com/tracker');
    }

    public function test_build_link_throws_exception_for_blocked_domain_doubleclick(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('URL contains blocked domain: doubleclick.net');

        $this->mock_settings_repository->shouldReceive('get_settings')
            ->once()
            ->andReturn(['affiliate_id' => '']);

        $this->service->build_link('https://doubleclick.net/ad');
    }

    public function test_build_link_throws_exception_for_blocked_domain_amazon_ads(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('URL contains blocked domain: adsystem.amazon.com');

        $this->mock_settings_repository->shouldReceive('get_settings')
            ->once()
            ->andReturn(['affiliate_id' => '']);

        $this->service->build_link('https://adsystem.amazon.com/ads');
    }

    public function test_build_link_throws_exception_for_invalid_scheme(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid URL scheme');

        $this->mock_settings_repository->shouldReceive('get_settings')
            ->once()
            ->andReturn(['affiliate_id' => '']);

        $this->service->build_link('ftp://example.com/file');
    }

    public function test_build_link_throws_exception_for_protocol_relative_url(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Protocol-relative URLs are not allowed');

        $this->mock_settings_repository->shouldReceive('get_settings')
            ->once()
            ->andReturn(['affiliate_id' => '']);

        $this->service->build_link('//example.com/product');
    }

    public function test_validate_image_url_with_local_url(): void {
        $url = 'https://example.com/wp-content/uploads/2024/01/image.jpg';
        
        $result = $this->service->validate_image_url($url);
        
        $this->assertTrue($result);
    }

    public function test_validate_image_url_throws_exception_for_external_url(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('External image URLs are not allowed');

        $this->service->validate_image_url('https://external-site.com/image.jpg');
    }

    public function test_validate_image_url_throws_exception_for_invalid_format(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid image URL format');

        $this->service->validate_image_url('not-a-url');
    }

    public function test_validate_image_url_returns_false_for_empty_url(): void {
        $result = $this->service->validate_image_url('');
        
        $this->assertFalse($result);
    }

    public function test_validate_image_url_throws_exception_for_blocked_domain(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('URL contains blocked domain');

        $this->service->validate_image_url('https://facebook.com/image.jpg');
    }

    public function test_validate_js_url_throws_exception_for_external_js(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('External JavaScript URLs are strictly prohibited');

        $this->service->validate_js_url('https://example.com/script.js');
    }

    public function test_validate_js_url_allows_relative_paths(): void {
        $result = $this->service->validate_js_url('/js/app.js');
        
        $this->assertTrue($result);
    }

    public function test_validate_js_url_allows_dot_notation(): void {
        $result = $this->service->validate_js_url('./js/app.js');
        
        $this->assertTrue($result);
    }

    public function test_validate_js_url_allows_parent_directory(): void {
        $result = $this->service->validate_js_url('../js/app.js');
        
        $this->assertTrue($result);
    }

    public function test_validate_js_url_returns_false_for_empty_url(): void {
        $result = $this->service->validate_js_url('');
        
        $this->assertFalse($result);
    }

    public function test_validate_js_url_throws_exception_for_invalid_local_path(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid JavaScript URL');

        $this->service->validate_js_url('invalid-path.js');
    }

    public function test_build_link_sanitizes_query_parameters(): void {
        $this->mock_settings_repository->shouldReceive('get_settings')
            ->once()
            ->andReturn(['affiliate_id' => '']);

        $link = $this->service->build_link('https://example.com/product?param1=<script>alert(1)</script>&param2=value2');

        $this->assertInstanceOf(AffiliateLink::class, $link);
        $this->assertStringNotContainsString('<script>', $link->get_url());
    }

    public function test_build_link_preserves_fragment(): void {
        $this->mock_settings_repository->shouldReceive('get_settings')
            ->once()
            ->andReturn(['affiliate_id' => '']);

        $link = $this->service->build_link('https://example.com/product#section');

        $this->assertInstanceOf(AffiliateLink::class, $link);
        $this->assertStringContainsString('#section', $link->get_url());
    }

    public function test_build_link_with_multiple_query_params(): void {
        $this->mock_settings_repository->shouldReceive('get_settings')
            ->once()
            ->andReturn(['affiliate_id' => 'aff-123']);

        $link = $this->service->build_link('https://example.com/product?param1=value1&param2=value2');

        $this->assertInstanceOf(AffiliateLink::class, $link);
        $this->assertStringContainsString('param1=value1', $link->get_url());
        $this->assertStringContainsString('param2=value2', $link->get_url());
        $this->assertStringContainsString('aff_id=aff-123', $link->get_url());
    }

    public function test_build_link_trims_whitespace(): void {
        $this->mock_settings_repository->shouldReceive('get_settings')
            ->once()
            ->andReturn(['affiliate_id' => '']);

        $link = $this->service->build_link('  https://example.com/product  ');

        $this->assertInstanceOf(AffiliateLink::class, $link);
        $this->assertSame('https://example.com/product', $link->get_url());
    }

    public function test_build_link_with_special_characters_in_params(): void {
        $this->mock_settings_repository->shouldReceive('get_settings')
            ->once()
            ->andReturn(['affiliate_id' => '']);

        $link = $this->service->build_link('https://example.com/product?name=Product%20%26%20Co.');

        $this->assertInstanceOf(AffiliateLink::class, $link);
        $this->assertStringContainsString('name=Product', $link->get_url());
    }

    public function test_build_link_does_not_duplicate_tracking_id(): void {
        $this->mock_settings_repository->shouldReceive('get_settings')
            ->once()
            ->andReturn(['affiliate_id' => 'aff-123']);

        $link = $this->service->build_link('https://example.com/product?aff_id=aff-123');

        $this->assertInstanceOf(AffiliateLink::class, $link);
        // Count occurrences of aff_id - should only be once
        $count = substr_count($link->get_url(), 'aff_id=aff-123');
        $this->assertSame(1, $count);
    }

    public function test_validate_image_url_with_path_only(): void {
        $result = $this->service->validate_image_url('/wp-content/uploads/image.jpg');
        
        $this->assertTrue($result);
    }

    public function test_validate_js_url_with_data_uri(): void {
        $result = $this->service->validate_js_url('data:text/javascript;base64,abc123');
        
        $this->assertTrue($result);
    }

    public function test_build_link_with_empty_tracking_id(): void {
        $this->mock_settings_repository->shouldReceive('get_settings')
            ->once()
            ->andReturn(['affiliate_id' => '']);

        $link = $this->service->build_link('https://example.com/product');

        $this->assertInstanceOf(AffiliateLink::class, $link);
        $this->assertStringNotContainsString('aff_id', $link->get_url());
    }

    public function test_build_link_with_special_tracking_id(): void {
        $this->mock_settings_repository->shouldReceive('get_settings')
            ->once()
            ->andReturn(['affiliate_id' => 'aff-123_ABC-XYZ']);

        $link = $this->service->build_link('https://example.com/product');

        $this->assertInstanceOf(AffiliateLink::class, $link);
        $this->assertStringContainsString('aff_id=aff-123_ABC-XYZ', $link->get_url());
    }

    public function test_build_link_throws_exception_for_url_without_host(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('URL must have a valid host');

        $this->mock_settings_repository->shouldReceive('get_settings')
            ->once()
            ->andReturn(['affiliate_id' => '']);

        $this->service->build_link('https:///path');
    }

    public function test_validate_js_url_throws_exception_for_http(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('External JavaScript URLs are strictly prohibited');

        $this->service->validate_js_url('http://example.com/script.js');
    }

    public function test_build_link_with_fragment_and_query(): void {
        $this->mock_settings_repository->shouldReceive('get_settings')
            ->once()
            ->andReturn(['affiliate_id' => 'aff-xyz']);

        $link = $this->service->build_link('https://example.com/product?param=value#section');

        $this->assertInstanceOf(AffiliateLink::class, $link);
        $this->assertStringContainsString('param=value', $link->get_url());
        $this->assertStringContainsString('#section', $link->get_url());
        $this->assertStringContainsString('aff_id=aff-xyz', $link->get_url());
    }

    public function test_build_link_url_encodes_tracking_id(): void {
        $this->mock_settings_repository->shouldReceive('get_settings')
            ->once()
            ->andReturn(['affiliate_id' => 'aff id with spaces']);

        $link = $this->service->build_link('https://example.com/product');

        $this->assertInstanceOf(AffiliateLink::class, $link);
        $this->assertStringContainsString('aff_id=aff%20id%20with%20spaces', $link->get_url());
    }

    public function test_validate_image_url_returns_false_for_whitespace_only(): void {
        $result = $this->service->validate_image_url('   ');
        
        $this->assertFalse($result);
    }

    public function test_validate_js_url_returns_false_for_whitespace_only(): void {
        $result = $this->service->validate_js_url('   ');
        
        $this->assertFalse($result);
    }

    public function test_build_link_with_encoded_characters_in_url(): void {
        $this->mock_settings_repository->shouldReceive('get_settings')
            ->once()
            ->andReturn(['affiliate_id' => '']);

        $link = $this->service->build_link('https://example.com/product%20name');

        $this->assertInstanceOf(AffiliateLink::class, $link);
        $this->assertStringContainsString('product%20name', $link->get_url());
    }
}
