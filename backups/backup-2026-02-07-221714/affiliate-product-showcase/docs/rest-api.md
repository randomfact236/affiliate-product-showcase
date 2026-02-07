# REST API Reference

Complete documentation for the Affiliate Product Showcase REST API endpoints.

## Overview

The plugin provides a RESTful API for managing affiliate products programmatically. All endpoints are prefixed with `/wp-json/affiliate-product-showcase/v1/`.

## Authentication

### Application Passwords (Recommended)

For secure API access, use WordPress application passwords:

```bash
# Generate application password in WordPress Admin → Users → Profile
# Then use Basic authentication:

curl -X GET https://yoursite.com/wp-json/affiliate-product-showcase/v1/products \
  -u username:application_password
```

### Cookie Authentication (Admin)

When making requests from WordPress admin (AJAX):

```javascript
// Automatic when using fetch() from admin
fetch('/wp-json/affiliate-product-showcase/v1/products', {
  credentials: 'same-origin'
});
```

## Endpoints

### Products

   - List Products

Get a paginated list of products.

**Endpoint:** `GET /wp-json/affiliate-product-showcase/v1/products`

**Query Parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `page` | integer | `1` | Page number (1-indexed) |
| `per_page` | integer | `10` | Items per page (1-100) |
| `category` | string | `null` | Filter by category slug |
| `search` | string | `null` | Search in title and description |
| `orderby` | string | `date` | Sort by: `date`, `title`, `price` |
| `order` | string | `DESC` | Sort direction: `ASC`, `DESC` |

**Example Request:**

```bash
curl -X GET "https://yoursite.com/wp-json/affiliate-product-showcase/v1/products?per_page=5&page=2&category=electronics"
```

**Example Response:**

```json
{
  "data": [
    {
      "id": 123,
      "title": "Premium Headphones",
      "slug": "premium-headphones",
      "description": "High-quality wireless headphones with noise cancellation",
      "currency": "USD",
      "price": 199.99,
      "original_price": 249.99,
      "affiliate_url": "https://example.com/headphones",
      "image_url": "https://yoursite.com/wp-content/uploads/2026/01/headphones.jpg",
      "rating": 4.5,
      "badge": "Best Seller",
      "categories": ["electronics", "audio"],
      "date": "2026-01-15T10:30:00",
      "modified": "2026-01-15T12:00:00"
    }
  ],
  "meta": {
    "total": 25,
    "pages": 3,
    "current_page": 2,
    "per_page": 5
  }
}
```

---

   - Get Single Product

Retrieve details of a specific product.

**Endpoint:** `GET /wp-json/affiliate-product-showcase/v1/products/{id}`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | Product ID |

**Example Request:**

```bash
curl -X GET https://yoursite.com/wp-json/affiliate-product-showcase/v1/products/123
```

**Example Response:**

```json
{
  "id": 123,
  "title": "Premium Headphones",
  "slug": "premium-headphones",
  "description": "High-quality wireless headphones with noise cancellation",
  "currency": "USD",
  "price": 199.99,
  "original_price": 249.99,
  "affiliate_url": "https://example.com/headphones",
  "image_url": "https://yoursite.com/wp-content/uploads/2026/01/headphones.jpg",
  "rating": 4.5,
  "badge": "Best Seller",
  "categories": ["electronics", "audio"],
  "date": "2026-01-15T10:30:00",
  "modified": "2026-01-15T12:00:00"
}
```

**Error Response:**

```json
{
  "code": "rest_product_invalid_id",
  "message": "Invalid product ID",
  "data": {
    "status": 404
  }
}
```

---

   - Create Product

Create a new product. Requires authentication.

**Endpoint:** `POST /wp-json/affiliate-product-showcase/v1/products`

**Authentication Required:** Yes  
**Required Capability:** `edit_posts`

**Request Body:**

```json
{
  "title": "Product Name",
  "description": "Product description",
  "affiliate_url": "https://example.com/product",
  "price": 99.99,
  "currency": "USD",
  "image_url": "https://yoursite.com/wp-content/uploads/image.jpg",
  "rating": 4.5,
  "badge": "Best Seller",
  "categories": ["electronics", "gadgets"],
  "original_price": 149.99
}
```

**Field Requirements:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `title` | string | ✅ Yes | Product title |
| `description` | string | ❌ No | Product description |
| `affiliate_url` | string | ✅ Yes | Valid URL (must be local or allowed domain) |
| `price` | number | ✅ Yes | Product price |
| `currency` | string | ❌ No | Currency code (default: "USD") |
| `image_url` | string | ❌ No | Valid image URL |
| `rating` | number | ❌ No | Rating 1-5 |
| `badge` | string | ❌ No | Badge text (e.g., "Sale", "New") |
| `categories` | array | ❌ No | Array of category slugs |
| `original_price` | number | ❌ No | Original price (for discount display) |

**Example Request:**

```bash
curl -X POST https://yoursite.com/wp-json/affiliate-product-showcase/v1/products \
  -u username:app_password \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Wireless Earbuds",
    "description": "True wireless earbuds with 24-hour battery life",
    "affiliate_url": "https://example.com/earbuds",
    "price": 79.99,
    "currency": "USD",
    "image_url": "https://yoursite.com/wp-content/uploads/earbuds.jpg",
    "rating": 4.8,
    "badge": "New",
    "categories": ["electronics", "audio"]
  }'
```

**Example Response:**

```json
{
  "id": 124,
  "title": "Wireless Earbuds",
  "slug": "wireless-earbuds",
  "description": "True wireless earbuds with 24-hour battery life",
  "currency": "USD",
  "price": 79.99,
  "affiliate_url": "https://example.com/earbuds",
  "image_url": "https://yoursite.com/wp-content/uploads/earbuds.jpg",
  "rating": 4.8,
  "badge": "New",
  "categories": ["electronics", "audio"],
  "date": "2026-01-15T14:30:00",
  "modified": "2026-01-15T14:30:00"
}
```

**Error Response:**

```json
{
  "code": "rest_forbidden",
  "message": "Sorry, you are not allowed to create products.",
  "data": {
    "status": 403
  }
}
```

---

   - Update Product

Update an existing product. Requires authentication.

**Endpoint:** `PUT /wp-json/affiliate-product-showcase/v1/products/{id}`

**Authentication Required:** Yes  
**Required Capability:** `edit_posts`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | Product ID |

**Request Body:**

All fields are optional. Include only fields you want to update.

```json
{
  "title": "Updated Title",
  "price": 89.99,
  "badge": "Sale"
}
```

**Example Request:**

```bash
curl -X PUT https://yoursite.com/wp-json/affiliate-product-showcase/v1/products/124 \
  -u username:app_password \
  -H "Content-Type: application/json" \
  -d '{
    "price": 69.99,
    "badge": "Sale",
    "original_price": 79.99
  }'
```

**Example Response:**

```json
{
  "id": 124,
  "title": "Wireless Earbuds",
  "slug": "wireless-earbuds",
  "description": "True wireless earbuds with 24-hour battery life",
  "currency": "USD",
  "price": 69.99,
  "original_price": 79.99,
  "affiliate_url": "https://example.com/earbuds",
  "image_url": "https://yoursite.com/wp-content/uploads/earbuds.jpg",
  "rating": 4.8,
  "badge": "Sale",
  "categories": ["electronics", "audio"],
  "date": "2026-01-15T14:30:00",
  "modified": "2026-01-15T15:00:00"
}
```

---

   - Delete Product

Delete a product. Requires authentication.

**Endpoint:** `DELETE /wp-json/affiliate-product-showcase/v1/products/{id}`

**Authentication Required:** Yes  
**Required Capability:** `delete_posts`

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | Product ID |

**Example Request:**

```bash
curl -X DELETE https://yoursite.com/wp-json/affiliate-product-showcase/v1/products/124 \
  -u username:app_password
```

**Example Response:**

```json
{
  "deleted": true,
  "previous": {
    "id": 124,
    "title": "Wireless Earbuds"
  }
}
```

**Error Response:**

```json
{
  "code": "rest_product_invalid_id",
  "message": "Invalid product ID",
  "data": {
    "status": 404
  }
}
```

---

### Analytics

   - Get Analytics Summary

Retrieve view and click statistics for products. Requires authentication.

**Endpoint:** `GET /wp-json/affiliate-product-showcase/v1/analytics`

**Authentication Required:** Yes  
**Required Capability:** `manage_options`

**Query Parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `product_id` | integer | `null` | Get analytics for specific product |
| `start_date` | string | `null` | Start date (Y-m-d format) |
| `end_date` | string | `null` | End date (Y-m-d format) |

**Example Request (All Products):**

```bash
curl -X GET https://yoursite.com/wp-json/affiliate-product-showcase/v1/analytics \
  -u username:app_password
```

**Example Response:**

```json
{
  "123": {
    "views": 150,
    "clicks": 45,
    "click_rate": 30.0,
    "last_viewed": "2026-01-15T10:30:00",
    "last_clicked": "2026-01-15T12:45:00"
  },
  "124": {
    "views": 200,
    "clicks": 60,
    "click_rate": 30.0,
    "last_viewed": "2026-01-15T11:20:00",
    "last_clicked": "2026-01-15T13:30:00"
  },
  "summary": {
    "total_views": 350,
    "total_clicks": 105,
    "average_click_rate": 30.0
  }
}
```

**Example Request (Single Product):**

```bash
curl -X GET "https://yoursite.com/wp-json/affiliate-product-showcase/v1/analytics?product_id=123&start_date=2026-01-01&end_date=2026-01-15" \
  -u username:app_password
```

**Example Response:**

```json
{
  "123": {
    "views": 150,
    "clicks": 45,
    "click_rate": 30.0,
    "daily_stats": {
      "2026-01-01": {"views": 10, "clicks": 3},
      "2026-01-02": {"views": 12, "clicks": 4},
      "2026-01-15": {"views": 15, "clicks": 5}
    },
    "last_viewed": "2026-01-15T10:30:00",
    "last_clicked": "2026-01-15T12:45:00"
  }
}
```

**Error Response:**

```json
{
  "code": "rest_forbidden",
  "message": "Sorry, you are not allowed to view analytics.",
  "data": {
    "status": 403
  }
}
```

---

   - Record View

Record a product view (public endpoint).

**Endpoint:** `POST /wp-json/affiliate-product-showcase/v1/analytics/view`

**Authentication Required:** No

**Request Body:**

```json
{
  "product_id": 123
}
```

**Example Request:**

```bash
curl -X POST https://yoursite.com/wp-json/affiliate-product-showcase/v1/analytics/view \
  -H "Content-Type: application/json" \
  -d '{"product_id": 123}'
```

**Example Response:**

```json
{
  "success": true,
  "product_id": 123,
  "view_count": 151
}
```

---

   - Record Click

Record a product click (public endpoint).

**Endpoint:** `POST /wp-json/affiliate-product-showcase/v1/analytics/click`

**Authentication Required:** No

**Request Body:**

```json
{
  "product_id": 123
}
```

**Example Request:**

```bash
curl -X POST https://yoursite.com/wp-json/affiliate-product-showcase/v1/analytics/click \
  -H "Content-Type: application/json" \
  -d '{"product_id": 123}'
```

**Example Response:**

```json
{
  "success": true,
  "product_id": 123,
  "click_count": 46
}
```

---

### Health Check

Check plugin health and system status.

**Endpoint:** `GET /wp-json/affiliate-product-showcase/v1/health`

**Authentication Required:** No

**Example Request:**

```bash
curl -X GET https://yoursite.com/wp-json/affiliate-product-showcase/v1/health
```

**Example Response:**

```json
{
  "status": "healthy",
  "timestamp": "2026-01-15T15:00:00Z",
  "version": "1.0.0",
  "checks": {
    "database": {
      "status": "ok",
      "message": "Database connection successful"
    },
    "cache": {
      "status": "ok",
      "message": "Object cache is active",
      "type": "Redis"
    },
    "plugin": {
      "status": "ok",
      "message": "Plugin is active"
    }
  }
}
```

**Degraded Response:**

```json
{
  "status": "degraded",
  "timestamp": "2026-01-15T15:00:00Z",
  "version": "1.0.0",
  "checks": {
    "database": {
      "status": "ok",
      "message": "Database connection successful"
    },
    "cache": {
      "status": "warning",
      "message": "Object cache not available, using database fallback"
    },
    "plugin": {
      "status": "ok",
      "message": "Plugin is active"
    }
  }
}
```

---

## Rate Limiting

Public API endpoints are rate-limited to prevent abuse.

### Rate Limits

| Endpoint | Limit | Window |
|----------|-------|--------|
| Products (list) | 100 requests | per minute per IP |
| Products (single) | 200 requests | per minute per IP |
| Analytics (view/click) | 200 requests | per minute per IP |
| Health check | 60 requests | per minute per IP |

### Rate Limit Response

When rate limit is exceeded:

```json
{
  "code": "rest_rate_limit_exceeded",
  "message": "Rate limit exceeded. Please try again later.",
  "data": {
    "status": 429,
    "retry_after": 45
  }
}
```

Headers included in response:

```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 0
X-RateLimit-Reset: 1705350000
Retry-After: 45
```

---

## Error Responses

All endpoints return consistent error responses.

### Standard Error Format

```json
{
  "code": "error_code",
  "message": "Human-readable error message",
  "data": {
    "status": 400,
    "details": {}
  }
}
```

### Common Error Codes

| Code | HTTP Status | Description |
|-------|-------------|-------------|
| `rest_product_invalid_id` | 404 | Product ID not found |
| `rest_forbidden` | 403 | Insufficient permissions |
| `rest_unauthorized` | 401 | Authentication required |
| `rest_rate_limit_exceeded` | 429 | Rate limit exceeded |
| `rest_invalid_param` | 400 | Invalid parameter |
| `rest_server_error` | 500 | Internal server error |
| `rest_invalid_url` | 400 | Invalid URL format |
| `rest_external_url_blocked` | 400 | External URL not allowed in standalone mode |

---

## URL Validation

The plugin validates all URLs for security:

### Allowed URLs

✅ **Local URLs** (always allowed):
- URLs from your own domain
- URLs from WordPress media library
- Relative URLs

❌ **Blocked Domains** (always blocked):
- google-analytics.com
- facebook.com
- doubleclick.net
- All known tracking and ad domains

### Standalone Mode

In standalone mode (default), only local URLs are allowed:

```json
{
  "code": "rest_external_url_blocked",
  "message": "External URLs are not allowed in standalone mode. Please use URLs from your media library.",
  "data": {
    "status": 400
  }
}
```

### External Mode

If enabled in settings, external URLs from specific domains are allowed. Contact administrator for configuration.

---

## SDK Examples

### JavaScript/TypeScript

```javascript
class AffiliateProductAPI {
  constructor(baseUrl, username, password) {
    this.baseUrl = baseUrl;
    this.auth = btoa(`${username}:${password}`);
  }

  async getProducts(params = {}) {
    const queryString = new URLSearchParams(params).toString();
    const response = await fetch(`${this.baseUrl}/products?${queryString}`, {
      headers: {
        'Authorization': `Basic ${this.auth}`
      }
    });
    return response.json();
  }

  async createProduct(product) {
    const response = await fetch(`${this.baseUrl}/products`, {
      method: 'POST',
      headers: {
        'Authorization': `Basic ${this.auth}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(product)
    });
    return response.json();
  }

  async recordView(productId) {
    const response = await fetch(`${this.baseUrl}/analytics/view`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ product_id: productId })
    });
    return response.json();
  }
}

// Usage
const api = new AffiliateProductAPI(
  'https://yoursite.com/wp-json/affiliate-product-showcase/v1',
  'username',
  'app_password'
);

const products = await api.getProducts({ per_page: 5 });
await api.recordView(123);
```

### PHP

```php
class AffiliateProductAPI {
    private $base_url;
    private $username;
    private $app_password;

    public function __construct($base_url, $username, $app_password) {
        $this->base_url = rtrim($base_url, '/');
        $this->username = $username;
        $this->app_password = $app_password;
    }

    private function request($endpoint, $method = 'GET', $data = []) {
        $url = $this->base_url . $endpoint;
        
        $args = [
            'method' => $method,
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($this->username . ':' . $this->app_password),
                'Content-Type' => 'application/json'
            ]
        ];

        if ($method === 'POST' || $method === 'PUT') {
            $args['body'] = json_encode($data);
        }

        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            throw new Exception($response->get_error_message());
        }

        return json_decode(wp_remote_retrieve_body($response), true);
    }

    public function get_products($params = []) {
        $query = !empty($params) ? '?' . http_build_query($params) : '';
        return $this->request('/products' . $query);
    }

    public function create_product($product) {
        return $this->request('/products', 'POST', $product);
    }
}

// Usage
$api = new AffiliateProductAPI(
    'https://yoursite.com/wp-json/affiliate-product-showcase/v1',
    'username',
    'app_password'
);

$products = $api->get_products(['per_page' => 10]);
$new_product = $api->create_product([
    'title' => 'New Product',
    'price' => 99.99,
    'affiliate_url' => 'https://example.com/product'
]);
```

### Python

```python
import requests
from typing import Dict, List

class AffiliateProductAPI:
    def __init__(self, base_url: str, username: str, app_password: str):
        self.base_url = base_url.rstrip('/')
        self.auth = (username, app_password)
        self.session = requests.Session()
        self.session.auth = self.auth
        self.session.headers.update({
            'Content-Type': 'application/json'
        })

    def get_products(self, params: Dict = None) -> Dict:
        response = self.session.get(
            f'{self.base_url}/products',
            params=params or {}
        )
        response.raise_for_status()
        return response.json()

    def create_product(self, product: Dict) -> Dict:
        response = self.session.post(
            f'{self.base_url}/products',
            json=product
        )
        response.raise_for_status()
        return response.json()

    def record_view(self, product_id: int) -> Dict:
        response = self.session.post(
            f'{self.base_url}/analytics/view',
            json={'product_id': product_id}
        )
        response.raise_for_status()
        return response.json()

# Usage
api = AffiliateProductAPI(
    'https://yoursite.com/wp-json/affiliate-product-showcase/v1',
    'username',
    'app_password'
)

products = api.get_products({'per_page': 5})
api.record_view(123)
```

---

## Best Practices

### 1. Use Application Passwords

Always use application passwords for authentication:

```bash
# ✅ GOOD: Application password
curl -u username:app_password

# ❌ BAD: Regular password (insecure)
curl -u username:regular_password
```

### 2. Handle Rate Limits

Implement exponential backoff:

```javascript
async function fetchWithRetry(url, maxRetries = 3) {
  for (let i = 0; i < maxRetries; i++) {
    try {
      const response = await fetch(url);
      
      if (response.status === 429) {
        const retryAfter = parseInt(response.headers.get('Retry-After')) || 60;
        await new Promise(resolve => setTimeout(resolve, retryAfter * 1000));
        continue;
      }
      
      return response;
    } catch (error) {
      if (i === maxRetries - 1) throw error;
      await new Promise(resolve => setTimeout(resolve, Math.pow(2, i) * 1000));
    }
  }
}
```

### 3. Validate URLs

Always validate URLs before sending:

```javascript
function isValidUrl(string) {
  try {
    new URL(string);
    return true;
  } catch (_) {
    return false;
  }
}

if (!isValidUrl(product.affiliate_url)) {
  throw new Error('Invalid affiliate URL');
}
```

### 4. Cache Responses

Cache public endpoints to reduce API calls:

```javascript
const cache = new Map();

async function getCachedProducts(params) {
  const cacheKey = JSON.stringify(params);
  
  if (cache.has(cacheKey)) {
    return cache.get(cacheKey);
  }
  
  const products = await fetchProducts(params);
  cache.set(cacheKey, products);
  
  // Clear cache after 5 minutes
  setTimeout(() => cache.delete(cacheKey), 5 * 60 * 1000);
  
  return products;
}
```

### 5. Use Pagination

For large datasets, use pagination:

```javascript
async function getAllProducts(perPage = 100) {
  let allProducts = [];
  let page = 1;
  
  while (true) {
    const response = await fetchProducts({ page, per_page: perPage });
    allProducts = allProducts.concat(response.data);
    
    if (response.data.length < perPage) break;
    
    page++;
  }
  
  return allProducts;
}
```

---

## Troubleshooting

### 401 Unauthorized

**Problem:** API returns 401 error.

**Solutions:**
1. Verify you're logged into WordPress
2. Check user has `edit_posts` capability
3. Generate new application password in **Users → Profile**
4. Use correct authentication header:
   ```
   Authorization: Basic base64(username:app_password)
   ```

### 403 Forbidden

**Problem:** API returns 403 error.

**Solutions:**
1. Check user capabilities
2. Verify user has required permission
3. For analytics, user must have `manage_options`

### 404 Not Found

**Problem:** Product ID not found.

**Solutions:**
1. Verify product ID is correct
2. Check product is published (not draft)
3. Ensure product exists

### 429 Rate Limit Exceeded

**Problem:** Too many requests.

**Solutions:**
1. Implement exponential backoff
2. Reduce request frequency
3. Check `Retry-After` header

### 500 Internal Server Error

**Problem:** Server error.

**Solutions:**
1. Check WordPress error logs
2. Verify plugin is active
3. Check database connection
4. Review plugin settings

---

## Additional Resources

- [WordPress REST API Handbook](https://developer.wordpress.org/rest-api/)
- [Application Passwords Guide](https://make.wordpress.org/core/2020/11/05/application-passwords-integration-guide/)
- [Plugin Main Documentation](../README.md)
- [Developer Guide](developer-guide.md)

---

**Version:** 1.0.0  
**Last Updated:** January 2026  
**API Version:** v1
