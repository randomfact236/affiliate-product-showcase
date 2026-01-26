<?php
/**
 * Test Form Submission
 * 
 * This script tests the form submission to debug the issue
 */

// Test if the form is being submitted
if (isset($_POST['action']) && $_POST['action'] === 'aps_save_product') {
    error_log('Form submitted with action: ' . $_POST['action']);
    error_log('POST data: ' . print_r($_POST, true));
    
    // Check if title is set
    if (isset($_POST['aps_title'])) {
        error_log('Title is set: ' . $_POST['aps_title']);
    } else {
        error_log('Title is NOT set');
    }
    
    // Check if nonce is set
    if (isset($_POST['aps_product_nonce'])) {
        error_log('Nonce is set: ' . $_POST['aps_product_nonce']);
    } else {
        error_log('Nonce is NOT set');
    }
    
    // Check if nonce is valid
    if (isset($_POST['aps_product_nonce'])) {
        $nonce_valid = wp_verify_nonce($_POST['aps_product_nonce'], 'aps_save_product');
        error_log('Nonce valid: ' . ($nonce_valid ? 'Yes' : 'No'));
    }
    
    // Check user capabilities
    $can_publish = current_user_can('publish_posts');
    error_log('Can publish posts: ' . ($can_publish ? 'Yes' : 'No'));
    
    // Check if title is empty after sanitization
    $title = isset($_POST['aps_title']) ? sanitize_text_field(wp_unslash($_POST['aps_title'])) : '';
    error_log('Sanitized title: ' . $title);
    error_log('Title empty: ' . (empty($title) ? 'Yes' : 'No'));
    
    exit;
}

// Display test form
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Form Submission</title>
</head>
<body>
    <h1>Test Form Submission</h1>
    
    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data">
        <?php wp_nonce_field('aps_save_product', 'aps_product_nonce'); ?>
        
        <input type="hidden" name="action" value="aps_save_product">
        
        <div>
            <label>Title:</label>
            <input type="text" name="aps_title" value="Test Product" required>
        </div>
        
        <div>
            <label>Description:</label>
            <textarea name="aps_description">Test description</textarea>
        </div>
        
        <div>
            <label>Short Description:</label>
            <textarea name="aps_short_description">Test short description</textarea>
        </div>
        
        <div>
            <label>Affiliate URL:</label>
            <input type="url" name="aps_affiliate_url" value="https://example.com" required>
        </div>
        
        <div>
            <label>Regular Price:</label>
            <input type="number" name="aps_regular_price" value="29.99" step="0.01">
        </div>
        
        <div>
            <label>Status:</label>
            <select name="aps_status">
                <option value="draft">Draft</option>
                <option value="publish">Published</option>
            </select>
        </div>
        
        <div>
            <label>Categories (comma-separated):</label>
            <input type="text" name="aps_categories" value="test-category">
        </div>
        
        <div>
            <label>Tags (comma-separated):</label>
            <input type="text" name="aps_tags" value="test-tag">
        </div>
        
        <button type="submit">Submit Test</button>
    </form>
    
    <h2>Test Results:</h2>
    <pre id="results"></pre>
    
    <script>
    document.querySelector('form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('<?php echo admin_url('admin-post.php'); ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            document.getElementById('results').textContent = data;
        })
        .catch(error => {
            document.getElementById('results').textContent = 'Error: ' + error;
        });
    });
    </script>
</body>
</html>