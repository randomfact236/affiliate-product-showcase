# Phase 7: Integration & Performance

**Duration**: 2 weeks  
**Goal**: Everything works together, performance optimized  
**Prerequisites**: Phase 6 complete (Frontend Features)

---

## Week 1: E2E Testing & Bug Fixes

### Day 1-3: E2E Test Suite

#### Tasks
- [ ] Set up Playwright
- [ ] Create critical path tests
- [ ] Add auth flow tests
- [ ] Add product management tests
- [ ] Add affiliate flow tests

#### e2e/critical-paths.spec.ts
```typescript
import { test, expect } from '@playwright/test';

test.describe('Critical User Flows', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
  });

  test('visitor can browse and view product', async ({ page }) => {
    // Navigate to products
    await page.click('text=Products');
    await expect(page).toHaveURL(/\/products/);

    // Filter by category
    await page.click('text=Electronics');
    await expect(page.locator('[data-testid="product-card"]')).toHaveCountGreaterThan(0);

    // View product detail
    await page.click('[data-testid="product-card"]:first-child');
    await expect(page.locator('h1')).toBeVisible();
    await expect(page.locator('[data-testid="affiliate-button"]')).toBeVisible();
  });

  test('user can register, login, and access profile', async ({ page }) => {
    // Register
    await page.click('text=Sign up');
    await page.fill('[name="email"]', `test-${Date.now()}@example.com`);
    await page.fill('[name="password"]', 'Password123!');
    await page.fill('[name="confirmPassword"]', 'Password123!');
    await page.click('button[type="submit"]');

    // Should redirect to home
    await expect(page).toHaveURL('/');

    // Access profile
    await page.click('[data-testid="user-menu"]');
    await page.click('text=Profile');
    await expect(page).toHaveURL('/profile');
  });

  test('admin can create and publish product', async ({ page }) => {
    // Login as admin
    await page.goto('/login');
    await page.fill('[name="email"]', 'admin@example.com');
    await page.fill('[name="password"]', 'admin123');
    await page.click('button[type="submit"]');

    // Navigate to admin
    await page.click('[data-testid="user-menu"]');
    await page.click('text=Admin Dashboard');

    // Create product
    await page.click('text=New Product');
    await page.fill('[name="name"]', 'Test Product E2E');
    await page.fill('[name="slug"]', `test-product-${Date.now()}`);
    await page.fill('[name="description"]', 'This is a test product');
    await page.fill('[name="price"]', '99.99');
    
    // Publish
    await page.selectOption('[name="status"]', 'PUBLISHED');
    await page.click('button:has-text("Create")');

    // Verify success
    await expect(page.locator('text=Product created successfully')).toBeVisible();
  });

  test('affiliate link click tracks and redirects', async ({ page }) => {
    // Use test affiliate link
    await page.goto('/l/ABC123');
    
    // Should redirect to product
    await expect(page).toHaveURL(/\/products\//);
    
    // Verify tracking event was fired (check console or API)
    // This would be mocked or checked via API
  });
});
```

#### playwright.config.ts
```typescript
import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './e2e',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 1 : undefined,
  reporter: 'html',
  use: {
    baseURL: process.env.BASE_URL || 'http://localhost:3000',
    trace: 'on-first-retry',
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
    {
      name: 'firefox',
      use: { ...devices['Desktop Firefox'] },
    },
    {
      name: 'webkit',
      use: { ...devices['Desktop Safari'] },
    },
    {
      name: 'Mobile Chrome',
      use: { ...devices['Pixel 5'] },
    },
    {
      name: 'Mobile Safari',
      use: { ...devices['iPhone 12'] },
    },
  ],
  webServer: {
    command: 'pnpm dev',
    url: 'http://localhost:3000',
    reuseExistingServer: !process.env.CI,
  },
});
```

### Day 4-5: Bug Fixing & Integration Issues

#### Tasks
- [ ] Fix API integration issues
- [ ] Resolve auth state sync issues
- [ ] Fix image loading issues
- [ ] Address responsive design bugs
- [ ] Fix form validation edge cases

#### Common Issues & Fixes

**Issue: Auth state not syncing between tabs**
```typescript
// hooks/use-auth-sync.ts
import { useEffect } from 'react';
import { useSession } from 'next-auth/react';

export function useAuthSync() {
  const { update } = useSession();

  useEffect(() => {
    const handleStorageChange = (e: StorageEvent) => {
      if (e.key === 'next-auth.session-token') {
        // Session changed in another tab, refresh
        update();
      }
    };

    window.addEventListener('storage', handleStorageChange);
    return () => window.removeEventListener('storage', handleStorageChange);
  }, [update]);
}
```

**Issue: Image loading failures**
```typescript
// components/image/with-fallback.tsx
import { useState } from 'react';
import Image from 'next/image';

export function ImageWithFallback({
  src,
  alt,
  fallback = '/images/placeholder.png',
  ...props
}) {
  const [imgSrc, setImgSrc] = useState(src);

  return (
    <Image
      {...props}
      src={imgSrc}
      alt={alt}
      onError={() => setImgSrc(fallback)}
    />
  );
}
```

---

## Week 2: Performance Optimization

### Day 6-7: Frontend Performance

#### Tasks
- [ ] Analyze bundle size
- [ ] Implement code splitting
- [ ] Optimize images
- [ ] Add prefetching
- [ ] Configure caching headers

#### next.config.js
```javascript
/** @type {import('next').NextConfig} */
const nextConfig = {
  // Image optimization
  images: {
    formats: ['image/webp', 'image/avif'],
    deviceSizes: [640, 750, 828, 1080, 1200, 1920],
    imageSizes: [16, 32, 48, 64, 96, 128, 256],
  },

  // Code splitting
  experimental: {
    optimizePackageImports: [
      'lodash',
      'date-fns',
      '@radix-ui/react-icons',
    ],
  },

  // Headers for caching
  async headers() {
    return [
      {
        source: '/:path*',
        headers: [
          {
            key: 'X-DNS-Prefetch-Control',
            value: 'on',
          },
        ],
      },
      {
        source: '/api/:path*',
        headers: [
          {
            key: 'Cache-Control',
            value: 'no-store, must-revalidate',
          },
        ],
      },
      {
        source: '/_next/static/:path*',
        headers: [
          {
            key: 'Cache-Control',
            value: 'public, max-age=31536000, immutable',
          },
        ],
      },
    ];
  },

  // Redirects
  async redirects() {
    return [
      {
        source: '/old-products',
        destination: '/products',
        permanent: true,
      },
    ];
  },
};

module.exports = nextConfig;
```

#### Dynamic Imports for Heavy Components
```typescript
// app/(store)/products/[slug]/page.tsx
import { Suspense } from 'react';
import dynamic from 'next/dynamic';

// Heavy components loaded dynamically
const ProductReviews = dynamic(
  () => import('@/components/products/product-reviews'),
  { ssr: false, loading: () => <ReviewsSkeleton /> }
);

const RelatedProducts = dynamic(
  () => import('@/components/products/related-products'),
  { loading: () => <ProductSkeleton count={4} /> }
);

const ImageGallery = dynamic(
  () => import('@/components/products/image-gallery'),
  { ssr: true }
);
```

### Day 8-9: Backend Performance

#### Tasks
- [ ] Add Redis caching layer
- [ ] Optimize database queries
- [ ] Add request compression
- [ ] Configure connection pooling
- [ ] Add rate limiting

#### apps/api/src/cache/cache.interceptor.ts
```typescript
import {
  Injectable,
  NestInterceptor,
  ExecutionContext,
  CallHandler,
} from '@nestjs/common';
import { Observable, of } from 'rxjs';
import { tap } from 'rxjs/operators';
import { RedisService } from '../redis/redis.service';

@Injectable()
export class CacheInterceptor implements NestInterceptor {
  constructor(private redis: RedisService) {}

  async intercept(
    context: ExecutionContext,
    next: CallHandler,
  ): Promise<Observable<unknown>> {
    const request = context.switchToHttp().getRequest();
    const cacheKey = this.generateCacheKey(request);
    const ttl = this.getCacheTTL(context);

    // Try to get from cache
    const cached = await this.redis.get(cacheKey);
    if (cached) {
      return of(JSON.parse(cached));
    }

    // Execute handler and cache result
    return next.handle().pipe(
      tap(async (data) => {
        await this.redis.setex(cacheKey, ttl, JSON.stringify(data));
      }),
    );
  }

  private generateCacheKey(request: Request): string {
    const url = request.url;
    const userId = request.user?.id || 'anonymous';
    return `cache:${userId}:${url}`;
  }

  private getCacheTTL(context: ExecutionContext): number {
    const handler = context.getHandler();
    // Use decorator metadata or default
    return Reflect.getMetadata('cache_ttl', handler) || 300; // 5 minutes default
  }
}
```

#### Rate Limiting Configuration
```typescript
// apps/api/src/throttle/throttle.config.ts
import { ThrottlerModule } from '@nestjs/throttler';

export const ThrottleConfig = ThrottlerModule.forRoot([
  {
    name: 'default',
    ttl: 60000, // 1 minute
    limit: 100, // 100 requests per minute
  },
  {
    name: 'auth',
    ttl: 60000,
    limit: 5, // 5 auth attempts per minute
  },
  {
    name: 'api',
    ttl: 1000, // 1 second
    limit: 10, // 10 requests per second
  },
]);
```

### Day 10: Load Testing & Final Checks

#### Tasks
- [ ] Run load tests with k6
- [ ] Analyze performance metrics
- [ ] Fix bottlenecks
- [ ] Verify all critical paths work
- [ ] Create performance baseline

#### Load Test Script
```javascript
// load-tests/smoke-test.js
import http from 'k6/http';
import { check, sleep } from 'k6';
import { Rate, Trend } from 'k6/metrics';

const errorRate = new Rate('errors');
const apiLatency = new Trend('api_latency');

export const options = {
  vus: 50,
  duration: '5m',
  thresholds: {
    http_req_duration: ['p(95)<200'],
    http_req_failed: ['rate<0.001'],
    errors: ['rate<0.001'],
  },
};

const BASE_URL = __ENV.BASE_URL || 'https://staging.example.com';

export default function () {
  const start = Date.now();

  // Homepage
  const home = http.get(`${BASE_URL}/`);
  check(home, {
    'homepage status is 200': (r) => r.status === 200,
    'homepage load time < 500ms': (r) => r.timings.duration < 500,
  });

  sleep(1);

  // Products list
  const products = http.get(`${BASE_URL}/api/v1/products?limit=24`);
  check(products, {
    'products status is 200': (r) => r.status === 200,
    'products response time < 200ms': (r) => r.timings.duration < 200,
  });

  apiLatency.add(Date.now() - start);

  sleep(Math.random() * 2 + 1);
}
```

---

## Performance Checklist

### Frontend
- [ ] Bundle size < 200KB (initial JS)
- [ ] Images use Next/Image with proper sizes
- [ ] Fonts optimized with next/font
- [ ] Critical CSS inlined
- [ ] Lazy loading for below-fold content
- [ ] Prefetching for likely navigation

### Backend
- [ ] API p95 < 200ms
- [ ] Database queries < 50ms
- [ ] Redis caching for hot data
- [ ] Connection pooling configured
- [ ] Rate limiting active
- [ ] Compression enabled

### Infrastructure
- [ ] CDN configured for static assets
- [ ] Database read replicas (if needed)
- [ ] Auto-scaling policies set
- [ ] Health checks configured

---

## Success Metrics

| Metric | Before | After | Target |
|--------|--------|-------|--------|
| Homepage LCP | - | < 1.5s | < 1.5s |
| Product page LCP | - | < 2s | < 2s |
| API p95 | - | < 200ms | < 200ms |
| Bundle size | - | < 200KB | < 200KB |
| E2E tests passing | 0% | 100% | 100% |
| Lighthouse score | - | > 90 | > 90 |

## Bug Tracking

| ID | Description | Severity | Status | Assigned |
|----|-------------|----------|--------|----------|
| BUG-001 | Auth token not refreshing | High | Fixed | @dev1 |
| BUG-002 | Image upload fails on large files | Medium | Fixed | @dev2 |
| BUG-003 | Category tree not draggable on mobile | Low | Open | @dev3 |

## Next Phase Handoff

**Phase 8 Prerequisites:**
- [ ] All E2E tests passing
- [ ] Performance targets met
- [ ] Critical bugs resolved
- [ ] Security scan clean
- [ ] Monitoring configured
