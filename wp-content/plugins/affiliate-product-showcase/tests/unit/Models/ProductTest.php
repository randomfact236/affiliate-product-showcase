<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Tests\Unit\Models;

use AffiliateProductShowcase\Models\Product;
use PHPUnit\Framework\TestCase;

final class ProductTest extends TestCase {
    public function test_can_create_product_with_all_fields(): void {
        $product = new Product(
            'test-product',
            'Test Product',
            'https://example.com/product',
            99.99,
            'USD',
            'Test Description',
            'https://example.com/image.jpg',
            'Test Brand',
            ['electronics', 'gadgets'],
            'in-stock',
            '2024-01-01'
        );

        $this->assertSame('test-product', $product->get_id());
        $this->assertSame('Test Product', $product->get_title());
        $this->assertSame('https://example.com/product', $product->get_affiliate_url());
        $this->assertSame(99.99, $product->get_price());
        $this->assertSame('USD', $product->get_currency());
        $this->assertSame('Test Description', $product->get_description());
        $this->assertSame('https://example.com/image.jpg', $product->get_image_url());
        $this->assertSame('Test Brand', $product->get_brand());
        $this->assertSame(['electronics', 'gadgets'], $product->get_categories());
        $this->assertSame('in-stock', $product->get_stock_status());
        $this->assertSame('2024-01-01', $product->get_date_added());
    }

    public function test_can_create_product_with_minimal_fields(): void {
        $product = new Product(
            'minimal-product',
            'Minimal Product',
            'https://example.com/minimal'
        );

        $this->assertSame('minimal-product', $product->get_id());
        $this->assertSame('Minimal Product', $product->get_title());
        $this->assertSame('https://example.com/minimal', $product->get_affiliate_url());
        $this->assertNull($product->get_price());
        $this->assertNull($product->get_currency());
        $this->assertNull($product->get_description());
        $this->assertNull($product->get_image_url());
        $this->assertNull($product->get_brand());
        $this->assertEmpty($product->get_categories());
        $this->assertNull($product->get_stock_status());
        $this->assertNull($product->get_date_added());
    }

    public function test_product_to_array_conversion(): void {
        $product = new Product(
            'array-product',
            'Array Product',
            'https://example.com/array',
            75.50,
            'EUR',
            'Description',
            'https://example.com/img.jpg',
            'Brand',
            ['category1', 'category2'],
            'in-stock',
            '2024-01-15'
        );

        $array = $product->to_array();

        $this->assertIsArray($array);
        $this->assertSame('array-product', $array['id']);
        $this->assertSame('Array Product', $array['title']);
        $this->assertSame('https://example.com/array', $array['affiliate_url']);
        $this->assertSame(75.50, $array['price']);
        $this->assertSame('EUR', $array['currency']);
        $this->assertSame('Description', $array['description']);
        $this->assertSame('https://example.com/img.jpg', $array['image_url']);
        $this->assertSame('Brand', $array['brand']);
        $this->assertSame(['category1', 'category2'], $array['categories']);
        $this->assertSame('in-stock', $array['stock_status']);
        $this->assertSame('2024-01-15', $array['date_added']);
    }

    public function test_product_with_special_characters(): void {
        $product = new Product(
            'special-chars-123',
            'Product & "Special" <Chars>',
            'https://example.com/product?param=value&other=123',
            100.00,
            'USD',
            'Description with <html> & special chars',
            'https://example.com/image-with-dashes.jpg',
            'Brand & Co.',
            ['tech & gadgets', 'electronics'],
            'in-stock'
        );

        $this->assertSame('Product & "Special" <Chars>', $product->get_title());
        $this->assertStringContainsString('&', $product->get_affiliate_url());
        $this->assertStringContainsString('&', $product->get_description());
        $this->assertStringContainsString('&', $product->get_brand());
        $this->assertContains('tech & gadgets', $product->get_categories());
    }

    public function test_product_with_unicode_characters(): void {
        $product = new Product(
            'unicode-product',
            'Product with émojis 🚀 and ñoño',
            'https://example.com/unicode',
            50.00,
            'USD',
            'Description with 中文 characters',
            'https://example.com/image-🚀.jpg',
            'Brand™',
            ['tech', '日本語'],
            'in-stock'
        );

        $this->assertStringContainsString('🚀', $product->get_title());
        $this->assertStringContainsString('ñoño', $product->get_title());
        $this->assertStringContainsString('中文', $product->get_description());
        $this->assertStringContainsString('™', $product->get_brand());
        $this->assertContains('日本語', $product->get_categories());
    }

    public function test_product_with_empty_categories(): void {
        $product = new Product(
            'no-categories',
            'No Categories',
            'https://example.com/no-cats',
            25.00,
            'USD',
            null,
            null,
            null,
            []
        );

        $this->assertEmpty($product->get_categories());
        $this->assertCount(0, $product->get_categories());
    }

    public function test_product_with_zero_price(): void {
        $product = new Product(
            'free-product',
            'Free Product',
            'https://example.com/free',
            0.00,
            'USD'
        );

        $this->assertSame(0.00, $product->get_price());
    }

    public function test_product_with_negative_price(): void {
        $product = new Product(
            'negative-price',
            'Negative Price Product',
            'https://example.com/negative',
            -10.00,
            'USD'
        );

        $this->assertSame(-10.00, $product->get_price());
    }

    public function test_product_with_various_stock_statuses(): void {
        $statuses = ['in-stock', 'out-of-stock', 'on-backorder', 'pre-order', 'discontinued'];

        foreach ($statuses as $status) {
            $product = new Product(
                "product-$status",
                "Product $status",
                "https://example.com/$status",
                50.00,
                'USD',
                null,
                null,
                null,
                [],
                $status
            );

            $this->assertSame($status, $product->get_stock_status());
        }
    }

    public function test_product_with_multiple_currencies(): void {
        $currencies = ['USD', 'EUR', 'GBP', 'JPY', 'CAD', 'AUD'];

        foreach ($currencies as $currency) {
            $product = new Product(
                "product-$currency",
                "Product $currency",
                "https://example.com/$currency",
                50.00,
                $currency
            );

            $this->assertSame($currency, $product->get_currency());
        }
    }

    public function test_product_with_emoji_in_various_fields(): void {
        $product = new Product(
            'emoji-product',
            'Product 🚀 with Emojis 😊',
            'https://example.com/emoji',
            50.00,
            'USD',
            'Description with 🌟 and ⭐',
            'https://example.com/image-🚀.jpg',
            'Brand 🏢',
            ['tech 📱', 'gadgets 💡'],
            'in-stock'
        );

        $this->assertStringContainsString('🚀', $product->get_title());
        $this->assertStringContainsString('😊', $product->get_title());
        $this->assertStringContainsString('🌟', $product->get_description());
        $this->assertStringContainsString('⭐', $product->get_description());
        $this->assertStringContainsString('🏢', $product->get_brand());
        $this->assertContains('tech 📱', $product->get_categories());
        $this->assertContains('gadgets 💡', $product->get_categories());
    }

    public function test_product_with_html_in_fields(): void {
        $product = new Product(
            'html-product',
            '<strong>HTML Product</strong>',
            'https://example.com/html',
            50.00,
            'USD',
            '<p>Description with <a href="#">link</a></p>',
            'https://example.com/image.jpg',
            '<em>Brand</em>',
            ['<script>alert("xss")</script>', 'safe'],
            'in-stock'
        );

        $this->assertStringContainsString('<strong>', $product->get_title());
        $this->assertStringContainsString('<p>', $product->get_description());
        $this->assertStringContainsString('<a', $product->get_description());
        $this->assertStringContainsString('<em>', $product->get_brand());
        $this->assertContains('<script>alert("xss")</script>', $product->get_categories());
    }

    public function test_product_with_very_long_values(): void {
        $longTitle = str_repeat('Long Title ', 50);
        $longDescription = str_repeat('Long description. ', 100);
        $longUrl = 'https://example.com/' . str_repeat('path/', 20);

        $product = new Product(
            'long-product',
            $longTitle,
            $longUrl,
            999.99,
            'USD',
            $longDescription,
            null,
            'Very Long Brand Name That Is Quite Long',
            ['category1', 'category2', 'category3', 'category4', 'category5'],
            'in-stock'
        );

        $this->assertSame($longTitle, $product->get_title());
        $this->assertSame($longUrl, $product->get_affiliate_url());
        $this->assertSame($longDescription, $product->get_description());
        $this->assertCount(5, $product->get_categories());
    }

    public function test_product_with_special_price_formats(): void {
        $product = new Product(
            'special-price',
            'Special Price Product',
            'https://example.com/special',
            1234.56,
            'USD',
            null,
            null,
            null,
            [],
            'in-stock'
        );

        $this->assertSame(1234.56, $product->get_price());
    }

    public function test_product_with_very_large_price(): void {
        $product = new Product(
            'large-price',
            'Large Price Product',
            'https://example.com/large',
            999999999.99,
            'USD'
        );

        $this->assertSame(999999999.99, $product->get_price());
    }

    public function test_product_date_formats(): void {
        $dates = [
            '2024-01-01',
            '2024-12-31',
            '2023-02-28',
            '2024-02-29', // Leap year
            '1970-01-01',
        ];

        foreach ($dates as $date) {
            $product = new Product(
                "product-$date",
                "Product $date",
                "https://example.com/$date",
                50.00,
                'USD',
                null,
                null,
                null,
                [],
                'in-stock',
                $date
            );

            $this->assertSame($date, $product->get_date_added());
        }
    }

    public function test_product_with_very_long_url(): void {
        $longUrl = 'https://example.com/' . str_repeat('very-long-path-segment/', 20) . 'product';

        $product = new Product(
            'long-url',
            'Long URL Product',
            $longUrl
        );

        $this->assertSame($longUrl, $product->get_affiliate_url());
    }

    public function test_product_with_special_url_characters(): void {
        $specialUrl = 'https://example.com/product?param1=value%201&param2=value+2&param3=123&param4=test';

        $product = new Product(
            'special-url',
            'Special URL Product',
            $specialUrl
        );

        $this->assertStringContainsString('%20', $product->get_affiliate_url());
        $this->assertStringContainsString('+', $product->get_affiliate_url());
    }

    public function test_product_with_whitespace(): void {
        $product = new Product(
            'whitespace-product',
            '  Product with Spaces  ',
            '  https://example.com/spaces  ',
            50.00,
            'USD',
            '  Description with spaces  ',
            '  https://example.com/image.jpg  ',
            '  Brand with Spaces  ',
            ['  tech  ', '  gadgets  '],
            'in-stock'
        );

        $this->assertStringContainsString('  ', $product->get_title());
        $this->assertStringContainsString('  ', $product->get_affiliate_url());
        $this->assertStringContainsString('  ', $product->get_description());
        $this->assertStringContainsString('  ', $product->get_brand());
        $this->assertContains('  tech  ', $product->get_categories());
    }

    public function test_product_with_newlines_in_description(): void {
        $product = new Product(
            'newline-product',
            'Newline Product',
            'https://example.com/newline',
            50.00,
            'USD',
            "Line 1\nLine 2\nLine 3",
            null,
            null,
            [],
            'in-stock'
        );

        $this->assertStringContainsString("\n", $product->get_description());
        $this->assertStringContainsString('Line 1', $product->get_description());
        $this->assertStringContainsString('Line 2', $product->get_description());
        $this->assertStringContainsString('Line 3', $product->get_description());
    }

    public function test_product_with_mixed_case_categories(): void {
        $product = new Product(
            'mixed-case',
            'Mixed Case Product',
            'https://example.com/mixed',
            50.00,
            'USD',
            null,
            null,
            null,
            ['Electronics', 'GADGETS', 'tech', 'Tech']
        );

        $categories = $product->get_categories();
        $this->assertContains('Electronics', $categories);
        $this->assertContains('GADGETS', $categories);
        $this->assertContains('tech', $categories);
        $this->assertContains('Tech', $categories);
    }

    public function test_product_with_duplicate_categories(): void {
        $product = new Product(
            'duplicate-cats',
            'Duplicate Categories',
            'https://example.com/dup',
            50.00,
            'USD',
            null,
            null,
            null,
            ['tech', 'tech', 'electronics', 'tech']
        );

        $categories = $product->get_categories();
        $this->assertCount(4, $categories);
        $this->assertContains('tech', $categories);
        $this->assertContains('electronics', $categories);
    }

    public function test_product_with_numeric_id(): void {
        $product = new Product(
            '12345',
            'Numeric ID Product',
            'https://example.com/12345'
        );

        $this->assertSame('12345', $product->get_id());
    }

    public function test_product_with_empty_strings(): void {
        $product = new Product(
            '',
            '',
            '',
            0.00,
            '',
            '',
            '',
            '',
            '',
            [],
            '',
            ''
        );

        $this->assertEmpty($product->get_id());
        $this->assertEmpty($product->get_title());
        $this->assertEmpty($product->get_affiliate_url());
        $this->assertEmpty($product->get_currency());
        $this->assertEmpty($product->get_description());
        $this->assertEmpty($product->get_image_url());
        $this->assertEmpty($product->get_brand());
        $this->assertEmpty($product->get_stock_status());
    }

    public function test_product_with_null_values(): void {
        $product = new Product(
            'null-product',
            'Null Product',
            'https://example.com/null',
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null
        );

        $this->assertNull($product->get_price());
        $this->assertNull($product->get_currency());
        $this->assertNull($product->get_description());
        $this->assertNull($product->get_image_url());
        $this->assertNull($product->get_brand());
        $this->assertNull($product->get_stock_status());
        $this->assertNull($product->get_date_added());
        $this->assertEmpty($product->get_categories());
    }

    public function test_product_array_has_all_keys(): void {
        $product = new Product(
            'test',
            'Test',
            'https://example.com/test',
            10.00,
            'USD',
            'Description',
            'https://example.com/img.jpg',
            'Brand',
            ['cat1', 'cat2'],
            'in-stock',
            '2024-01-01'
        );

        $array = $product->to_array();

        $expectedKeys = ['id', 'title', 'affiliate_url', 'price', 'currency', 
                      'description', 'image_url', 'brand', 'categories', 
                      'stock_status', 'date_added'];
        
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $array);
        }
    }

    public function test_product_array_values_match_getters(): void {
        $product = new Product(
            'id123',
            'Test Title',
            'https://example.com/url',
            25.50,
            'EUR',
            'Test Description',
            'https://example.com/image.jpg',
            'Test Brand',
            ['cat1', 'cat2'],
            'in-stock',
            '2024-06-15'
        );

        $array = $product->to_array();

        $this->assertSame($product->get_id(), $array['id']);
        $this->assertSame($product->get_title(), $array['title']);
        $this->assertSame($product->get_affiliate_url(), $array['affiliate_url']);
        $this->assertSame($product->get_price(), $array['price']);
        $this->assertSame($product->get_currency(), $array['currency']);
        $this->assertSame($product->get_description(), $array['description']);
        $this->assertSame($product->get_image_url(), $array['image_url']);
        $this->assertSame($product->get_brand(), $array['brand']);
        $this->assertSame($product->get_categories(), $array['categories']);
        $this->assertSame($product->get_stock_status(), $array['stock_status']);
        $this->assertSame($product->get_date_added(), $array['date_added']);
    }
}
