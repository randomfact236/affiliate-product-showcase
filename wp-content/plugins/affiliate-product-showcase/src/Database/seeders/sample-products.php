<?php
/**
 * Sample Products Seeder
 *
 * This file contains the SampleProductsSeeder class which generates
 * sample data for testing the Affiliate Product Showcase plugin.
 *
 * @package AffiliateProductShowcase
 * @subpackage Database\Seeders
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Database\Seeders;

use AffiliateProductShowcase\Database\Database;

/**
 * SampleProductsSeeder Class
 *
 * Generates sample products and affiliate links for testing purposes.
 * Used by integration tests and the db-seed.sh script.
 *
 * @package AffiliateProductShowcase
 * @subpackage Database\Seeders
 */
class SampleProductsSeeder {
    
    /**
     * @var Database Database instance
     */
    private Database $db;
    
    /**
     * @var string Option name for tracking if seed data exists
     */
    private string $seed_option = 'affiliate_products_seeded';
    
    /**
     * Sample product categories
     *
     * @var array<string>
     */
    private array $categories = [
        'Electronics',
        'Books',
        'Fashion',
        'Home & Garden',
        'Sports',
        'Beauty',
        'Toys',
        'Health',
    ];
    
    /**
     * Sample product data templates
     *
     * @var array<mixed>
     */
    private array $product_templates = [
        [
            'name' => 'Wireless Bluetooth Headphones',
            'url' => 'https://example.com/headphones',
            'price' => '79.99',
            'image' => 'https://example.com/images/headphones.jpg',
        ],
        [
            'name' => 'Smart Fitness Watch',
            'url' => 'https://example.com/watch',
            'price' => '149.99',
            'image' => 'https://example.com/images/watch.jpg',
        ],
        [
            'name' => 'Portable Power Bank',
            'url' => 'https://example.com/powerbank',
            'price' => '29.99',
            'image' => 'https://example.com/images/powerbank.jpg',
        ],
        [
            'name' => 'USB-C Hub Adapter',
            'url' => 'https://example.com/usb-hub',
            'price' => '34.99',
            'image' => 'https://example.com/images/usb-hub.jpg',
        ],
        [
            'name' => 'Ergonomic Office Chair',
            'url' => 'https://example.com/chair',
            'price' => '299.99',
            'image' => 'https://example.com/images/chair.jpg',
        ],
        [
            'name' => 'LED Desk Lamp',
            'url' => 'https://example.com/lamp',
            'price' => '45.99',
            'image' => 'https://example.com/images/lamp.jpg',
        ],
        [
            'name' => 'Wireless Mouse',
            'url' => 'https://example.com/mouse',
            'price' => '24.99',
            'image' => 'https://example.com/images/mouse.jpg',
        ],
        [
            'name' => 'Mechanical Keyboard',
            'url' => 'https://example.com/keyboard',
            'price' => '89.99',
            'image' => 'https://example.com/images/keyboard.jpg',
        ],
    ];
    
    /**
     * Constructor
     *
     * Initialize the seeder with database instance.
     *
     * @since 1.0.0
     * @param Database $db Database instance
     */
    public function __construct(Database $db) {
        $this->db = $db;
    }
    
    /**
     * Run the seeder
     *
     * Generates and inserts sample products into the database.
     * This method is idempotent - it can be run multiple times safely.
     *
     * @since 1.0.0
     * @param int $count Number of products to generate. Default 10
     * @return int Number of products seeded
     */
    public function run(int $count = 10): int {
        // Check if seed data already exists
        if ($this->is_seeded()) {
            $this->clear_seed_data();
        }
        
        $seeded = 0;
        
        for ($i = 0; $i < $count; $i++) {
            $template = $this->product_templates[$i % count($this->product_templates)];
            $product_id = $this->create_product($template, $i);
            
            if ($product_id) {
                // Add meta data for this product
                $this->create_product_meta($product_id);
                $seeded++;
            }
        }
        
        // Mark as seeded
        update_option($this->seed_option, [
            'count' => $seeded,
            'timestamp' => current_time('mysql'),
        ]);
        
        return $seeded;
    }
    
    /**
     * Create a sample product
     *
     * Creates a single product as a custom post type.
     *
     * @since 1.0.0
     * @param array<mixed> $template Product template data
     * @param int $index Product index for generating unique data
     * @return int|false Product ID or false on failure
     */
    private function create_product(array $template, int $index) {
        $category = $this->categories[$index % count($this->categories)];
        $name = $template['name'];
        
        // Create product post
        $post_id = wp_insert_post([
            'post_title' => $name,
            'post_content' => $this->generate_description($name, $category),
            'post_type' => 'affiliate_product',
            'post_status' => 'publish',
            'post_author' => get_current_user_id() ?: 1,
        ]);
        
        if (is_wp_error($post_id) || $post_id === 0) {
            return false;
        }
        
        // Add product as a submission for testing
        $this->create_submission($template, $category, $post_id);
        
        return $post_id;
    }
    
    /**
     * Create product meta data
     *
     * Adds sample metadata to a product.
     *
     * @since 1.0.0
     * @param int $product_id Product ID
     * @return void
     */
    private function create_product_meta(int $product_id): void {
        $meta_data = [
            'affiliate_url' => 'https://example.com/product/' . $product_id . '?ref=test',
            'price' => $this->generate_random_price(),
            'old_price' => $this->generate_random_price(),
            'discount' => rand(5, 30),
            'rating' => rand(35, 50) / 10,
            'reviews_count' => rand(10, 500),
            'brand' => $this->generate_random_brand(),
            'availability' => rand(0, 10) > 2 ? 'in_stock' : 'out_of_stock',
            'sku' => 'SKU-' . strtoupper(substr(md5((string) $product_id), 0, 8)),
            'is_featured' => rand(0, 10) > 7,
            'is_best_seller' => rand(0, 10) > 8,
            'is_new_arrival' => rand(0, 10) > 9,
        ];
        
        foreach ($meta_data as $key => $value) {
            // Store in post meta (WordPress standard)
            update_post_meta($product_id, 'affiliate_product_' . $key, $value);
            
            // Also store in custom meta table
            $this->db->insert('meta', [
                'product_id' => $product_id,
                'meta_key' => $key,
                'meta_value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value,
            ]);
        }
    }
    
    /**
     * Create a product submission
     *
     * Creates a sample submission for testing the submissions table.
     *
     * @since 1.0.0
     * @param array<mixed> $template Product template
     * @param string $category Product category
     * @param int $product_id Associated product ID
     * @return int|false Submission ID or false on failure
     */
    private function create_submission(array $template, string $category, int $product_id) {
        $statuses = ['pending', 'approved', 'rejected'];
        $status = $statuses[array_rand($statuses, 1)];
        
        $data = [
            'product_name' => $template['name'],
            'product_url' => $template['url'],
            'product_image' => $template['image'],
            'price' => $template['price'],
            'description' => $this->generate_description($template['name'], $category),
            'category' => $category,
            'status' => $status,
            'submitted_by' => get_current_user_id() ?: 1,
            'notes' => $status === 'rejected' ? 'Does not meet quality standards' : '',
        ];
        
        // Add reviewed timestamps if not pending
        if ($status !== 'pending') {
            $data['reviewed_at'] = current_time('mysql');
            $data['reviewed_by'] = get_current_user_id() ?: 1;
        }
        
        return $this->db->insert('submissions', $data);
    }
    
    /**
     * Generate a product description
     *
     * Creates a realistic product description.
     *
     * @since 1.0.0
     * @param string $name Product name
     * @param string $category Product category
     * @return string Product description
     */
    private function generate_description(string $name, string $category): string {
        $descriptions = [
            "Experience premium quality with this {name}. Designed for {category} enthusiasts, this product combines exceptional performance with modern aesthetics.",
            "Discover the perfect {name} for your {category} needs. Featuring cutting-edge technology and superior craftsmanship.",
            "Elevate your {category} experience with this {name}. Built to last and designed to impress with its stunning features.",
        ];
        
        $template = $descriptions[array_rand($descriptions)];
        return str_replace(['{name}', '{category}'], [$name, $category], $template);
    }
    
    /**
     * Generate a random price
     *
     * Returns a random price between $10 and $500.
     *
     * @since 1.0.0
     * @return string Formatted price
     */
    private function generate_random_price(): string {
        return number_format(rand(1000, 50000) / 100, 2);
    }
    
    /**
     * Generate a random brand name
     *
     * Returns a random brand name for sample data.
     *
     * @since 1.0.0
     * @return string Brand name
     */
    private function generate_random_brand(): string {
        $brands = [
            'TechPro', 'SmartLife', 'PrimeQuality', 'EliteGear',
            'ValueMax', 'TopBrand', 'PremiumPlus', 'ExpertChoice',
        ];
        return $brands[array_rand($brands)];
    }
    
    /**
     * Check if seed data exists
     *
     * Returns whether sample data has been seeded.
     *
     * @since 1.0.0
     * @return bool True if seeded, false otherwise
     */
    public function is_seeded(): bool {
        $seeded = get_option($this->seed_option, false);
        return $seeded !== false;
    }
    
    /**
     * Clear all seed data
     *
     * Removes all sample data from the database.
     *
     * @since 1.0.0
     * @return int Number of items removed
     */
    public function clear_seed_data(): int {
        $removed = 0;
        
        // Remove sample submissions
        $removed += $this->db->query(
            "DELETE FROM {$this->db->get_table_name('submissions')} 
             WHERE submitted_by = 1"
        );
        
        // Remove sample meta
        $removed += $this->db->query(
            "DELETE FROM {$this->db->get_table_name('meta')} 
             WHERE product_id IN (
                 SELECT ID FROM {$this->db->wpdb->posts} 
                 WHERE post_type = 'affiliate_product' 
                 AND post_author = 1
             )"
        );
        
        // Remove sample products
        $removed += $this->db->wpdb->query(
            "DELETE FROM {$this->db->wpdb->posts} 
             WHERE post_type = 'affiliate_product' 
             AND post_author = 1"
        );
        
        // Clear seed option
        delete_option($this->seed_option);
        
        return $removed;
    }
    
    /**
     * Get fixtures for testing
     *
     * Returns an array of test data for use in integration tests.
     *
     * @since 1.0.0
     * @param int $count Number of fixtures to return
     * @return array<mixed> Test fixtures
     */
    public function get_fixtures(int $count = 3): array {
        $fixtures = [];
        
        for ($i = 0; $i < $count; $i++) {
            $template = $this->product_templates[$i % count($this->product_templates)];
            $category = $this->categories[$i % count($this->categories)];
            
            $fixtures[] = [
                'name' => $template['name'],
                'url' => $template['url'],
                'price' => $template['price'],
                'image' => $template['image'],
                'category' => $category,
                'description' => $this->generate_description($template['name'], $category),
                'meta' => [
                    'affiliate_url' => $template['url'],
                    'discount' => rand(5, 30),
                    'rating' => rand(35, 50) / 10,
                ],
            ];
        }
        
        return $fixtures;
    }
}
