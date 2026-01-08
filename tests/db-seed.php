<?php
// DB seeder for tests. Reads common env vars and is tolerant of container hosts.

function env_first(...$keys) {
    foreach ($keys as $k) {
        $v = getenv($k);
        if ($v !== false && $v !== '') return $v;
    }
    return null;
}

$dbHostRaw = env_first('DB_HOST', 'WORDPRESS_DB_HOST', 'MYSQL_HOST') ?: '127.0.0.1:3306';
$dbUser = env_first('DB_USER', 'MYSQL_USER') ?: 'wp';
$dbPass = env_first('DB_PASS', 'MYSQL_PASSWORD') ?: 'wp';
$dbName = env_first('DB_NAME', 'MYSQL_DATABASE') ?: 'wordpress';

// Parse host[:port] if provided
$dbPort = 3306;
$dbHost = $dbHostRaw;
if (strpos($dbHostRaw, ':') !== false) {
    [$h, $p] = explode(':', $dbHostRaw, 2);
    $dbHost = $h;
    if (is_numeric($p)) $dbPort = (int)$p;
}

$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName, (int)$dbPort);
if ($mysqli->connect_error) {
    fwrite(STDERR, "DB connection failed: " . $mysqli->connect_error . "\n");
    exit(1);
}

// Ensure the options table exists before attempting to seed
$res = $mysqli->query("SHOW TABLES LIKE 'wp_options'");
if (! $res || $res->num_rows === 0) {
    fwrite(STDERR, "wp_options table not found. Is the WP schema installed?\n");
    $mysqli->close();
    exit(1);
}

// Insert a marker option for tests. Use INSERT IGNORE to avoid duplicates.
$sql = "INSERT IGNORE INTO wp_options (option_name, option_value, autoload) VALUES ('aps_test_seed', '1', 'no')";
if (! $mysqli->query($sql)) {
    fwrite(STDERR, "Seed query failed: " . $mysqli->error . "\n");
    $mysqli->close();
    exit(1);
}

echo "Seed complete\n";

$mysqli->close();

// Additional sample content: posts, pages, and sample product posts.
function ensure_post($mysqli, $post_type, $post_name, $post_title, $post_content = '', $post_status = 'publish') {
    $prefix = 'wp_';
    $posts_table = $prefix . 'posts';
    $postmeta_table = $prefix . 'postmeta';

    // Check existing by post_name and post_type
    $stmt = $mysqli->prepare("SELECT ID FROM $posts_table WHERE post_name = ? AND post_type = ? LIMIT 1");
    $stmt->bind_param('ss', $post_name, $post_type);
    $stmt->execute();
    $stmt->bind_result($id);
    if ($stmt->fetch()) {
        $stmt->close();
        return (int)$id;
    }
    $stmt->close();

    $now = date('Y-m-d H:i:s');
    $stmt = $mysqli->prepare("INSERT INTO $posts_table (post_author, post_date, post_date_gmt, post_content, post_title, post_status, post_name, post_type, guid) VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?)");
    $guid = '';
    $stmt->bind_param('sssssss', $now, $now, $post_content, $post_title, $post_status, $post_name, $post_type, $guid);
    if (! $stmt->execute()) {
        fwrite(STDERR, "Failed to insert post $post_name: " . $stmt->error . "\n");
        $stmt->close();
        return 0;
    }
    $new_id = $mysqli->insert_id;
    $stmt->close();

    // Update GUID to be the post ID-based permalink placeholder
    $guid = "http://example.org/?p={$new_id}";
    $stmt = $mysqli->prepare("UPDATE $posts_table SET guid = ? WHERE ID = ?");
    $stmt->bind_param('si', $guid, $new_id);
    $stmt->execute();
    $stmt->close();

    return (int)$new_id;
}

function ensure_postmeta($mysqli, $post_id, $meta_key, $meta_value) {
    $prefix = 'wp_';
    $postmeta_table = $prefix . 'postmeta';
    $stmt = $mysqli->prepare("SELECT meta_id FROM $postmeta_table WHERE post_id = ? AND meta_key = ? LIMIT 1");
    $stmt->bind_param('is', $post_id, $meta_key);
    $stmt->execute();
    $stmt->bind_result($mid);
    if ($stmt->fetch()) { $stmt->close(); return; }
    $stmt->close();

    $stmt = $mysqli->prepare("INSERT INTO $postmeta_table (post_id, meta_key, meta_value) VALUES (?, ?, ?)");
    $stmt->bind_param('iss', $post_id, $meta_key, $meta_value);
    $stmt->execute();
    $stmt->close();
}

// Create sample post and page
$sample_post_id = ensure_post($mysqli, 'post', 'sample-seed-post', 'Sample Seed Post', 'This is a sample post inserted by the test DB seeder.');
$sample_page_id = ensure_post($mysqli, 'page', 'sample-seed-page', 'Sample Seed Page', 'This is a sample page inserted by the test DB seeder.');

// Create sample product as custom post type 'aps_product'
$product_id = ensure_post($mysqli, 'aps_product', 'sample-product-1', 'Sample Product 1', 'Sample product description inserted by seeder.');
ensure_postmeta($mysqli, $product_id, '_aps_product_price', '19.99');
ensure_postmeta($mysqli, $product_id, '_aps_product_affiliate_url', 'https://example.com/product/1');

fwrite(STDOUT, "Sample content ensured: post={$sample_post_id}, page={$sample_page_id}, product={$product_id}\n");

// Close connection (already closed above) - nothing further
