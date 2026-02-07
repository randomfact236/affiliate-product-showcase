# Add Product Form - HTML Structure

## 📋 Complete HTML Structure

```html
<div class="wrap affiliate-product-showcase">
    <!-- Page Header -->
    <h1>Add New Affiliate Product</h1>
    
    <!-- Navigation Tabs -->
    <h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
        <a href="..." class="nav-tab nav-tab-active">General</a>
        <a href="..." class="nav-tab">Product Details</a>
        <a href="..." class="nav-tab">Advanced</a>
    </h2>
    
    <!-- Main Form Container -->
    <div class="woocommerce-product-form-container">
        <form method="post" id="aps-product-form" action="admin-post.php" enctype="multipart/form-data">
            
            <!-- Security: Nonce Field -->
            <input type="hidden" name="aps_product_nonce" value="...">
            
            <!-- Tab Content Area -->
            <div class="product-tab-content">
                
                <!-- ========================================== -->
                <!-- TAB 1: GENERAL -->
                <!-- ========================================== -->
                
                <!-- Product Information Panel -->
                <div class="woocommerce-data-panel">
                    <div class="panel-header">
                        📝 Product Information
                    </div>
                    <div class="panel-body">
                        
                        <!-- Product Name Field -->
                        <div class="woocommerce-input-wrapper">
                            <label for="aps_title">
                                Product Name <span class="required">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="aps_title" 
                                name="aps_title" 
                                class="woocommerce-input"
                                placeholder="e.g., Apple MacBook Pro 14-inch"
                                required
                            >
                            <span class="description">
                                The name of your affiliate product
                            </span>
                        </div>
                        
                        <!-- Product Description Field -->
                        <div class="woocommerce-input-wrapper">
                            <label for="aps_description">
                                Product Description
                            </label>
                            <textarea 
                                id="aps_description" 
                                name="aps_description" 
                                class="woocommerce-input woocommerce-textarea"
                                rows="6"
                                placeholder="Describe your product features and benefits..."
                            ></textarea>
                            <span class="description">
                                Detailed description for product page and SEO
                            </span>
                        </div>
                        
                    </div>
                </div>
                
                <!-- Pricing Panel -->
                <div class="woocommerce-data-panel">
                    <div class="panel-header">
                        💰 Pricing
                    </div>
                    <div class="panel-body">
                        
                        <div class="woocommerce-grid-2">
                            
                            <!-- Regular Price Field -->
                            <div class="woocommerce-input-wrapper">
                                <label for="aps_regular_price">
                                    Regular Price <span class="required">*</span>
                                </label>
                                <div class="woocommerce-price-wrapper">
                                    <input 
                                        type="number" 
                                        id="aps_regular_price" 
                                        name="aps_regular_price" 
                                        class="woocommerce-input"
                                        placeholder="0.00"
                                        step="0.01"
                                        min="0"
                                        required
                                    >
                                    <div class="woocommerce-price-symbol">USD</div>
                                </div>
                                <span class="description">
                                    Standard price for this product
                                </span>
                            </div>
                            
                            <!-- Sale Price Field -->
                            <div class="woocommerce-input-wrapper">
                                <label for="aps_sale_price">
                                    Sale Price
                                </label>
                                <div class="woocommerce-price-wrapper">
                                    <input 
                                        type="number" 
                                        id="aps_sale_price" 
                                        name="aps_sale_price" 
                                        class="woocommerce-input"
                                        placeholder="0.00"
                                        step="0.01"
                                        min="0"
                                    >
                                    <div class="woocommerce-price-symbol">USD</div>
                                </div>
                                <span class="description">
                                    Discounted price (optional)
                                </span>
                            </div>
                            
                        </div>
                        
                        <!-- Currency Field -->
                        <div class="woocommerce-input-wrapper">
                            <label for="aps_currency">
                                Currency
                            </label>
                            <select 
                                id="aps_currency" 
                                name="aps_currency" 
                                class="woocommerce-select"
                            >
                                <option value="USD" selected>USD - US Dollar</option>
                                <option value="EUR">EUR - Euro</option>
                                <option value="GBP">GBP - British Pound</option>
                                <option value="JPY">JPY - Japanese Yen</option>
                            </select>
                            <span class="description">
                                Select currency for this product
                            </span>
                        </div>
                        
                    </div>
                </div>
                
                <!-- Affiliate Link Panel -->
                <div class="woocommerce-data-panel">
                    <div class="panel-header">
                        🔗 Affiliate Link
                    </div>
                    <div class="panel-body">
                        
                        <div class="woocommerce-input-wrapper">
                            <label for="aps_affiliate_url">
                                Affiliate URL <span class="required">*</span>
                            </label>
                            <input 
                                type="url" 
                                id="aps_affiliate_url" 
                                name="aps_affiliate_url" 
                                class="woocommerce-input"
                                placeholder="https://amazon.com/dp/..."
                                required
                            >
                            <span class="description">
                                Your affiliate tracking link for this product
                            </span>
                        </div>
                        
                    </div>
                </div>
                
            </div>
            <!-- End Tab Content -->
            
            <!-- ========================================== -->
            <!-- ACTION BUTTONS -->
            <!-- ========================================== -->
            
            <div class="product-form-actions">
                
                <!-- Hidden Action Field -->
                <input type="hidden" name="action" value="aps_save_product">
                <input type="hidden" name="current_tab" value="general">
                
                <!-- Publish Button -->
                <button type="submit" class="button button-primary button-large" name="publish">
                    <span class="dashicons dashicons-saved"></span>
                    Publish Product
                </button>
                
                <!-- Save Draft Button -->
                <button type="submit" class="button button-large" name="draft">
                    <span class="dashicons dashicons-list-view"></span>
                    Save Draft
                </button>
                
                <!-- Cancel Link -->
                <a href="admin.php?page=affiliate-manager" class="button">
                    Cancel
                </a>
                
            </div>
            
        </form>
    </div>
</div>
```

---

## 📋 Tab 2: Product Details HTML Structure

```html
<div class="product-tab-content">
    
    <!-- Image Panel -->
    <div class="woocommerce-data-panel">
        <div class="panel-header">
            🖼️ Product Image
        </div>
        <div class="panel-body">
            
            <div class="woocommerce-input-wrapper">
                <label for="aps_image_url">
                    Image URL
                </label>
                <input 
                    type="url" 
                    id="aps_image_url" 
                    name="aps_image_url" 
                    class="woocommerce-input"
                    placeholder="https://example.com/product-image.jpg"
                >
                <span class="description">
                    Direct link to product image (will download to media library)
                </span>
            </div>
            
            <!-- Image Preview Area -->
            <div class="image-preview-container">
                <div class="image-preview-placeholder">
                    [IMAGE PREVIEW]
                </div>
            </div>
            
        </div>
    </div>
    
    <!-- Gallery Panel -->
    <div class="woocommerce-data-panel">
        <div class="panel-header">
            🖼️ Gallery Images
        </div>
        <div class="panel-body">
            
            <div class="woocommerce-input-wrapper">
                <label for="aps_gallery">
                    Gallery Images
                </label>
                <textarea 
                    id="aps_gallery" 
                    name="aps_gallery" 
                    class="woocommerce-input woocommerce-textarea"
                    rows="5"
                    placeholder="https://example.com/image1.jpg&#10;https://example.com/image2.jpg&#10;https://example.com/image3.jpg"
                ></textarea>
                <span class="description">
                    One URL per line, max 10 images
                </span>
            </div>
            
        </div>
    </div>
    
    <!-- Video Panel -->
    <div class="woocommerce-data-panel">
        <div class="panel-header">
            🎥 Product Video
        </div>
        <div class="panel-body">
            
            <div class="woocommerce-input-wrapper">
                <label for="aps_video_url">
                    Video URL
                </label>
                <input 
                    type="url" 
                    id="aps_video_url" 
                    name="aps_video_url" 
                    class="woocommerce-input"
                    placeholder="https://youtube.com/watch?v=..."
                >
                <span class="description">
                    YouTube or Vimeo video URL (optional)
                </span>
            </div>
            
        </div>
    </div>
    
    <!-- Rating Panel -->
    <div class="woocommerce-data-panel">
        <div class="panel-header">
            ⭐ Product Rating
        </div>
        <div class="panel-body">
            
            <div class="woocommerce-input-wrapper">
                <label for="aps_rating">
                    Rating (0-5 stars)
                </label>
                <input 
                    type="number" 
                    id="aps_rating" 
                    name="aps_rating" 
                    class="woocommerce-input"
                    placeholder="4.5"
                    min="0"
                    max="5"
                    step="0.1"
                >
                <span class="description">
                    Average customer rating
                </span>
            </div>
            
        </div>
    </div>
    
</div>
```

---

## 📋 Tab 3: Advanced HTML Structure

```html
<div class="product-tab-content">
    
    <!-- Status Panel -->
    <div class="woocommerce-data-panel">
        <div class="panel-header">
            📊 Product Status
        </div>
        <div class="panel-body">
            
            <div class="woocommerce-input-wrapper">
                <label for="aps_status">
                    Status
                </label>
                <select 
                    id="aps_status" 
                    name="aps_status" 
                    class="woocommerce-select"
                >
                    <option value="draft">Draft</option>
                    <option value="publish" selected>Published</option>
                </select>
                <span class="description">
                    Product visibility status
                </span>
            </div>
            
            <div class="woocommerce-checkbox-wrapper">
                <label for="aps_featured">
                    <input 
                        type="checkbox" 
                        id="aps_featured" 
                        name="aps_featured" 
                        value="1"
                    >
                    Featured Product
                </label>
                <span class="description">
                    Show in featured products section
                </span>
            </div>
            
        </div>
    </div>
    
    <!-- Stock Status Panel -->
    <div class="woocommerce-data-panel">
        <div class="panel-header">
            📦 Stock Status
        </div>
        <div class="panel-body">
            
            <div class="woocommerce-input-wrapper">
                <label for="aps_stock_status">
                    Availability
                </label>
                <select 
                    id="aps_stock_status" 
                    name="aps_stock_status" 
                    class="woocommerce-select"
                >
                    <option value="instock" selected>In Stock</option>
                    <option value="outofstock">Out of Stock</option>
                    <option value="preorder">Pre-Order</option>
                </select>
                <span class="description">
                    Product availability status
                </span>
            </div>
            
        </div>
    </div>
    
    <!-- Categories & Tags Panel -->
    <div class="woocommerce-data-panel">
        <div class="panel-header">
            🏷️ Categories & Tags
        </div>
        <div class="panel-body">
            
            <div class="woocommerce-input-wrapper">
                <label for="aps_categories">
                    Categories
                </label>
                <input 
                    type="text" 
                    id="aps_categories" 
                    name="aps_categories" 
                    class="woocommerce-input"
                    placeholder="Electronics, Computers, Laptops"
                >
                <span class="description">
                    Comma-separated category names
                </span>
            </div>
            
            <div class="woocommerce-input-wrapper">
                <label for="aps_tags">
                    Tags
                </label>
                <input 
                    type="text" 
                    id="aps_tags" 
                    name="aps_tags" 
                    class="woocommerce-input"
                    placeholder="new, sale, popular"
                >
                <span class="description">
                    Comma-separated tag names
                </span>
            </div>
            
        </div>
    </div>
    
    <!-- SEO Settings Panel -->
    <div class="woocommerce-data-panel">
        <div class="panel-header">
            🔍 SEO Settings
        </div>
        <div class="panel-body">
            
            <div class="woocommerce-input-wrapper">
                <label for="aps_seo_title">
                    SEO Title
                </label>
                <input 
                    type="text" 
                    id="aps_seo_title" 
                    name="aps_seo_title" 
                    class="woocommerce-input"
                    placeholder="Product Name - Your Site"
                >
                <span class="description">
                    Custom SEO title (leave empty to use product name)
                </span>
            </div>
            
            <div class="woocommerce-input-wrapper">
                <label for="aps_seo_description">
                    SEO Description
                </label>
                <textarea 
                    id="aps_seo_description" 
                    name="aps_seo_description" 
                    class="woocommerce-input woocommerce-textarea"
                    rows="3"
                    maxlength="160"
                    placeholder="Meta description for search engines..."
                ></textarea>
                <span class="description">
                    Max 160 characters for search engines
                </span>
            </div>
            
        </div>
    </div>
    
</div>
```

---

## 🏗️ HTML Structure Overview

### Root Elements
```html
<div class="wrap affiliate-product-showcase">        <!-- Main container -->
    <h1>...</h1>                                 <!-- Page title -->
    <h2 class="nav-tab-wrapper">...</h2>           <!-- Tabs navigation -->
    <div class="woocommerce-product-form-container">  <!-- Form container -->
        <form>...</form>                            <!-- Main form -->
    </div>
</div>
```

### Panel Structure (Repeated for each section)
```html
<div class="woocommerce-data-panel">
    <div class="panel-header">
        Icon + Title
    </div>
    <div class="panel-body">
        Input fields...
    </div>
</div>
```

### Input Field Structure
```html
<div class="woocommerce-input-wrapper">
    <label for="field_id">
        Label Text <span class="required">*</span>
    </label>
    <input type="..." id="field_id" name="field_name" class="woocommerce-input">
    <span class="description">Help text</span>
</div>
```

### Price Input Structure
```html
<div class="woocommerce-price-wrapper">
    <input type="number" class="woocommerce-input" />
    <div class="woocommerce-price-symbol">USD</div>
</div>
```

### Action Buttons Structure
```html
<div class="product-form-actions">
    <button type="submit" class="button button-primary">Publish</button>
    <button type="submit" class="button">Save Draft</button>
    <a href="..." class="button">Cancel</a>
</div>
```

---

## 📊 Element Classes Reference

| Class Name | Purpose |
|-------------|----------|
| `wrap affiliate-product-showcase` | Main container |
| `nav-tab-wrapper` | Tab navigation container |
| `nav-tab` | Individual tab |
| `nav-tab-active` | Active tab state |
| `woocommerce-product-form-container` | Form container |
| `product-tab-content` | Tab content area |
| `woocommerce-data-panel` | Section panel |
| `panel-header` | Panel header with icon |
| `panel-body` | Panel content area |
| `woocommerce-input-wrapper` | Input field wrapper |
| `woocommerce-input` | Text input styling |
| `woocommerce-textarea` | Textarea styling |
| `woocommerce-select` | Select dropdown styling |
| `woocommerce-price-wrapper` | Price input group |
| `woocommerce-price-symbol` | Currency symbol |
| `woocommerce-checkbox-wrapper` | Checkbox wrapper |
| `product-form-actions` | Button container |
| `button button-primary` | Primary action button |
| `button` | Secondary action button |
| `required` | Required field indicator |
| `description` | Field help text |

---

## 🎯 Form Submission Data

When form is submitted, POST data includes:

```php
// General Tab
$_POST['aps_title']              // Product Name
$_POST['aps_description']        // Product Description
$_POST['aps_regular_price']     // Regular Price
$_POST['aps_sale_price']        // Sale Price
$_POST['aps_currency']          // Currency
$_POST['aps_affiliate_url']     // Affiliate URL

// Product Details Tab
$_POST['aps_image_url']        // Image URL
$_POST['aps_gallery']          // Gallery Images (newline-separated)
$_POST['aps_video_url']        // Video URL
$_POST['aps_rating']           // Rating

// Advanced Tab
$_POST['aps_status']           // Status (draft/publish)
$_POST['aps_featured']         // Featured (1 or empty)
$_POST['aps_stock_status']     // Stock Status
$_POST['aps_categories']       // Categories (comma-separated)
$_POST['aps_tags']            // Tags (comma-separated)
$_POST['aps_seo_title']       // SEO Title
$_POST['aps_seo_description'] // SEO Description

// Hidden Fields
$_POST['action']              // 'aps_save_product'
$_POST['current_tab']         // Current tab name
$_POST['aps_product_nonce']    // Security nonce
```

---

## 🔒 Security Elements

```html
<!-- Nonce for CSRF protection -->
<input type="hidden" name="aps_product_nonce" value="...">

<!-- Action identifier -->
<input type="hidden" name="action" value="aps_save_product">

<!-- Form destination -->
<form action="admin-post.php" method="post">

<!-- Enctype for file uploads -->
<form enctype="multipart/form-data">
```

---

*Generated on: 2026-01-21*
*Complete HTML Structure Documentation*
