# Custom Analytics System - Complete Implementation Plan

## Overview
Build a self-hosted analytics system that replicates all Vercel Analytics features without relying on third-party services.

---

## 🎯 Features to Replicate from Vercel

### 1. Page Views & Visitors Tracking
| Feature | Vercel | Custom Implementation |
|---------|--------|----------------------|
| Total Page Views | Auto | Custom tracking script |
| Unique Visitors | Fingerprinting | Cookie + Fingerprint hybrid |
| Views per Page | Auto | Database aggregation |
| Trends Over Time | Dashboard | Time-series queries |

### 2. Geographic Tracking
| Feature | Vercel | Custom Implementation |
|---------|--------|----------------------|
| Country | IP Geolocation | MaxMind GeoIP2 |
| Region/State | IP Geolocation | MaxMind GeoIP2 |
| City | IP Geolocation | MaxMind GeoIP2 |

### 3. Device & Browser Detection
| Feature | Vercel | Custom Implementation |
|---------|--------|----------------------|
| Device Type | User-Agent | ua-parser-js library |
| Operating System | User-Agent | ua-parser-js library |
| Browser | User-Agent | ua-parser-js library |
| Screen Resolution | JavaScript | window.screen API |

### 4. Traffic Sources
| Feature | Vercel | Custom Implementation |
|---------|--------|----------------------|
| Referrer URL | Header | document.referrer |
| UTM Parameters | URL | URLSearchParams API |
| Direct/Organic/Referral | Logic | Classification rules |

### 5. Core Web Vitals
| Metric | Vercel | Custom Implementation |
|--------|--------|----------------------|
| LCP | Chrome API | PerformanceObserver |
| INP | Chrome API | PerformanceObserver |
| CLS | Chrome API | PerformanceObserver |
| TTFB | Chrome API | Navigation Timing API |
| FCP | Chrome API | PerformanceObserver |

---

## 🏗️ System Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                     FRONTEND (Next.js)                      │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │ Analytics    │  │ Performance  │  │ Custom       │      │
│  │ Tracker      │  │ Observer     │  │ Events       │      │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘      │
└─────────┼─────────────────┼─────────────────┼──────────────┘
          │                 │                 │
          └─────────────────┼─────────────────┘
                            │
                    ┌───────▼────────┐
                    │   Analytics    │
                    │   API Layer    │
                    │   (NestJS)     │
                    └───────┬────────┘
                            │
          ┌─────────────────┼─────────────────┐
          │                 │                 │
   ┌──────▼──────┐  ┌──────▼──────┐  ┌──────▼──────┐
   │  PostgreSQL │  │    Redis    │  │  ClickHouse │
   │  (Metadata) │  │   (Queue)   │  │ (Time-Series)│
   └─────────────┘  └─────────────┘  └─────────────┘
```

---

## 📊 Database Schema

### PostgreSQL - Metadata Storage
```sql
-- Visitors table (unique users)
CREATE TABLE analytics_visitors (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  visitor_id VARCHAR(64) UNIQUE NOT NULL, -- Fingerprint hash
  first_seen TIMESTAMP DEFAULT NOW(),
  last_seen TIMESTAMP DEFAULT NOW(),
  country_code CHAR(2),
  country_name VARCHAR(100),
  region VARCHAR(100),
  city VARCHAR(100),
  device_type VARCHAR(20), -- desktop, mobile, tablet
  os_name VARCHAR(50),
  os_version VARCHAR(50),
  browser_name VARCHAR(50),
  browser_version VARCHAR(50),
  screen_width INTEGER,
  screen_height INTEGER,
  language VARCHAR(10),
  created_at TIMESTAMP DEFAULT NOW()
);

-- Sessions table
CREATE TABLE analytics_sessions (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  session_id VARCHAR(64) UNIQUE NOT NULL,
  visitor_id UUID REFERENCES analytics_visitors(id),
  started_at TIMESTAMP DEFAULT NOW(),
  ended_at TIMESTAMP,
  duration_seconds INTEGER DEFAULT 0,
  page_views INTEGER DEFAULT 0,
  is_bounce BOOLEAN DEFAULT true,
  referrer_url TEXT,
  referrer_type VARCHAR(20), -- direct, search, social, referral
  utm_source VARCHAR(100),
  utm_medium VARCHAR(100),
  utm_campaign VARCHAR(100),
  landing_page TEXT,
  exit_page TEXT,
  device_fingerprint VARCHAR(64),
  ip_hash VARCHAR(64), -- Hashed IP for privacy
  created_at TIMESTAMP DEFAULT NOW()
);

-- Page views table
CREATE TABLE analytics_page_views (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  session_id UUID REFERENCES analytics_sessions(id),
  visitor_id UUID REFERENCES analytics_visitors(id),
  page_path TEXT NOT NULL,
  page_title VARCHAR(255),
  referrer TEXT,
  query_params JSONB,
  time_on_page INTEGER DEFAULT 0, -- seconds
  scroll_depth INTEGER DEFAULT 0, -- percentage
  timestamp TIMESTAMP DEFAULT NOW(),
  created_at TIMESTAMP DEFAULT NOW()
);

-- Events table (custom tracking)
CREATE TABLE analytics_events (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  session_id UUID REFERENCES analytics_sessions(id),
  visitor_id UUID REFERENCES analytics_visitors(id),
  event_name VARCHAR(100) NOT NULL,
  event_properties JSONB,
  page_path TEXT,
  timestamp TIMESTAMP DEFAULT NOW(),
  created_at TIMESTAMP DEFAULT NOW()
);

-- Web Vitals table
CREATE TABLE analytics_web_vitals (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  session_id UUID REFERENCES analytics_sessions(id),
  visitor_id UUID REFERENCES analytics_visitors(id),
  page_path TEXT,
  metric_name VARCHAR(20) NOT NULL, -- LCP, FID, CLS, INP, TTFB, FCP
  metric_value DECIMAL(10,3) NOT NULL, -- in milliseconds or score
  metric_rating VARCHAR(20), -- good, needs-improvement, poor
  timestamp TIMESTAMP DEFAULT NOW(),
  created_at TIMESTAMP DEFAULT NOW()
);

-- Indexes for performance
CREATE INDEX idx_page_views_timestamp ON analytics_page_views(timestamp);
CREATE INDEX idx_page_views_session ON analytics_page_views(session_id);
CREATE INDEX idx_page_views_path ON analytics_page_views(page_path);
CREATE INDEX idx_events_name ON analytics_events(event_name);
CREATE INDEX idx_events_timestamp ON analytics_events(timestamp);
CREATE INDEX idx_sessions_started ON analytics_sessions(started_at);
CREATE INDEX idx_web_vitals_metric ON analytics_web_vitals(metric_name, timestamp);
```

### ClickHouse - Time-Series Aggregation (Optional)
```sql
-- For high-volume real-time analytics
CREATE TABLE page_views_agg (
  timestamp DateTime,
  page_path String,
  country_code String,
  device_type String,
  views UInt64,
  unique_visitors UInt64
) ENGINE = SummingMergeTree()
ORDER BY (timestamp, page_path, country_code, device_type);
```

### Redis - Real-time Counters
```
analytics:realtime:visitors:2024-01-15:14 -> 245
analytics:realtime:page:/products -> 56
analytics:session:{session_id} -> {visitor_id, start_time, page_count}
```

---

## 🔌 API Endpoints

### Tracking Endpoints
```typescript
// 1. Page View Tracking
POST /api/analytics/pageview
Body: {
  page_path: "/products",
  page_title: "All Products",
  referrer: "https://google.com",
  screen_width: 1920,
  screen_height: 1080,
  language: "en-US",
  utm_source?: "newsletter",
  utm_medium?: "email",
  utm_campaign?: "summer_sale"
}
Response: { session_id: "...", visitor_id: "..." }

// 2. Event Tracking
POST /api/analytics/event
Body: {
  event_name: "affiliate_click",
  properties: {
    product_id: "semrush-pro",
    platform: "SEMrush",
    price: 119
  },
  page_path: "/products/semrush-pro"
}

// 3. Web Vitals Tracking
POST /api/analytics/web-vitals
Body: {
  metric_name: "LCP",
  metric_value: 1200, // milliseconds
  metric_rating: "good", // good | needs-improvement | poor
  page_path: "/products"
}

// 4. Heartbeat (keep session alive)
POST /api/analytics/heartbeat
Body: {
  session_id: "...",
  time_on_page: 45, // seconds
  scroll_depth: 75 // percentage
}
```

### Dashboard API
```typescript
// Get overview stats
GET /api/analytics/stats?period=7d&from=2024-01-01&to=2024-01-07
Response: {
  visitors: { total: 12345, unique: 8900, change: 12.5 },
  pageviews: { total: 45678, change: 8.3 },
  avg_session_duration: 245, // seconds
  bounce_rate: 42.5, // percentage
  pages_per_session: 3.2
}

// Get top pages
GET /api/analytics/pages?period=7d&limit=10
Response: [
  { path: "/products", views: 1234, unique_visitors: 890 },
  { path: "/blog/hosting-guide", views: 987, unique_visitors: 765 }
]

// Get real-time stats
GET /api/analytics/realtime
Response: {
  active_visitors: 45,
  active_pages: [
    { path: "/products", count: 12 },
    { path: "/blog", count: 8 }
  ],
  last_30_minutes: [
    { minute: "14:00", visitors: 23 },
    { minute: "14:01", visitors: 25 }
  ]
}

// Get geographic data
GET /api/analytics/geo?period=7d
Response: {
  countries: [
    { code: "US", name: "United States", visitors: 4567, percentage: 45.2 },
    { code: "GB", name: "United Kingdom", visitors: 1234, percentage: 12.3 }
  ],
  cities: [
    { name: "New York", country: "US", visitors: 567 },
    { name: "London", country: "GB", visitors: 432 }
  ]
}

// Get device/browser stats
GET /api/analytics/devices?period=7d
Response: {
  devices: [
    { type: "desktop", percentage: 62.5, count: 7890 },
    { type: "mobile", percentage: 32.1, count: 4056 },
    { type: "tablet", percentage: 5.4, count: 684 }
  ],
  browsers: [
    { name: "Chrome", percentage: 58.2, count: 7345 },
    { name: "Safari", percentage: 22.1, count: 2789 }
  ],
  os: [
    { name: "Windows", percentage: 45.2, count: 5700 },
    { name: "macOS", percentage: 28.5, count: 3596 }
  ]
}

// Get Core Web Vitals
GET /api/analytics/web-vitals?period=7d&page=/products
Response: {
  lcp: { avg: 1200, p75: 1450, p95: 2100, good: 78, poor: 8 },
  inp: { avg: 85, p75: 120, p95: 180, good: 85, poor: 5 },
  cls: { avg: 0.05, p75: 0.08, p95: 0.15, good: 92, poor: 3 }
}

// Get custom events
GET /api/analytics/events?event_name=affiliate_click&period=7d
Response: {
  total_events: 456,
  unique_visitors: 234,
  breakdown: [
    { product_id: "semrush-pro", count: 123, revenue: 6150 },
    { product_id: "ahrefs", count: 89, revenue: 4450 }
  ]
}
```

---

## 💻 Frontend Implementation

### 1. Analytics Provider Component
```typescript
// components/analytics/analytics-provider.tsx
"use client";

import { createContext, useContext, useEffect, useRef } from 'react';
import { usePathname, useSearchParams } from 'next/navigation';

interface AnalyticsContextType {
  trackEvent: (name: string, properties?: Record<string, any>) => void;
  trackWebVital: (metric: string, value: number, rating: string) => void;
}

const AnalyticsContext = createContext<AnalyticsContextType | null>(null);

export function AnalyticsProvider({ children }: { children: React.ReactNode }) {
  const pathname = usePathname();
  const searchParams = useSearchParams();
  const sessionRef = useRef<string | null>(null);
  const heartbeatRef = useRef<NodeJS.Timeout | null>(null);
  const startTimeRef = useRef<number>(Date.now());

  // Track page view
  useEffect(() => {
    const trackPageView = async () => {
      const response = await fetch('/api/analytics/pageview', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          page_path: pathname,
          page_title: document.title,
          referrer: document.referrer,
          screen_width: window.screen.width,
          screen_height: window.screen.height,
          language: navigator.language,
          utm_source: searchParams.get('utm_source'),
          utm_medium: searchParams.get('utm_medium'),
          utm_campaign: searchParams.get('utm_campaign'),
        }),
      });
      
      const data = await response.json();
      sessionRef.current = data.session_id;
      startTimeRef.current = Date.now();
      
      // Start heartbeat
      if (heartbeatRef.current) {
        clearInterval(heartbeatRef.current);
      }
      
      heartbeatRef.current = setInterval(() => {
        sendHeartbeat();
      }, 30000); // Every 30 seconds
    };

    trackPageView();
    
    // Track scroll depth
    let maxScroll = 0;
    const handleScroll = () => {
      const scrollPercent = Math.round(
        (window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100
      );
      if (scrollPercent > maxScroll) {
        maxScroll = scrollPercent;
      }
    };
    
    window.addEventListener('scroll', handleScroll);
    
    return () => {
      window.removeEventListener('scroll', handleScroll);
      if (heartbeatRef.current) {
        clearInterval(heartbeatRef.current);
      }
      // Send final heartbeat on unmount
      sendHeartbeat(maxScroll);
    };
  }, [pathname, searchParams]);

  // Track Web Vitals
  useEffect(() => {
    if ('web-vitals' in window) {
      import('web-vitals').then(({ onLCP, onINP, onCLS, onTTFB, onFCP }) => {
        onLCP(sendWebVital);
        onINP(sendWebVital);
        onCLS(sendWebVital);
        onTTFB(sendWebVital);
        onFCP(sendWebVital);
      });
    }
  }, []);

  const sendHeartbeat = async (scrollDepth?: number) => {
    if (!sessionRef.current) return;
    
    await fetch('/api/analytics/heartbeat', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        session_id: sessionRef.current,
        time_on_page: Math.round((Date.now() - startTimeRef.current) / 1000),
        scroll_depth: scrollDepth || 0,
      }),
    });
  };

  const sendWebVital = (metric: any) => {
    fetch('/api/analytics/web-vitals', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        metric_name: metric.name,
        metric_value: metric.value,
        metric_rating: metric.rating,
        page_path: pathname,
      }),
    });
  };

  const trackEvent = (name: string, properties?: Record<string, any>) => {
    fetch('/api/analytics/event', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        event_name: name,
        properties,
        page_path: pathname,
      }),
    });
  };

  return (
    <AnalyticsContext.Provider value={{ trackEvent, trackWebVital: sendWebVital }}>
      {children}
    </AnalyticsContext.Provider>
  );
}

export const useAnalytics = () => {
  const context = useContext(AnalyticsContext);
  if (!context) {
    throw new Error('useAnalytics must be used within AnalyticsProvider');
  }
  return context;
};
```

### 2. Hook for Tracking
```typescript
// hooks/use-analytics.ts
export function useAnalytics() {
  const trackProductView = (product: Product) => {
    trackEvent('product_view', {
      product_id: product.id,
      product_name: product.name,
      category: product.category,
      price: product.price,
    });
  };

  const trackAffiliateClick = (product: Product, platform: string) => {
    trackEvent('affiliate_click', {
      product_id: product.id,
      product_name: product.name,
      platform,
      price: product.price,
    });
  };

  const trackSearch = (query: string, resultsCount: number) => {
    trackEvent('search', {
      query,
      results_count: resultsCount,
    });
  };

  const trackFilter = (filterType: string, filterValue: string) => {
    trackEvent('filter_applied', {
      filter_type: filterType,
      filter_value: filterValue,
    });
  };

  return { trackProductView, trackAffiliateClick, trackSearch, trackFilter };
}
```

### 3. Usage in Components
```typescript
// In product card
function ProductCard({ product }: { product: Product }) {
  const { trackAffiliateClick } = useAnalytics();

  return (
    <a
      href={product.affiliateUrl}
      onClick={() => trackAffiliateClick(product, product.platform)}
    >
      Buy Now
    </a>
  );
}

// In search component
function SearchBar() {
  const { trackSearch } = useAnalytics();
  
  const handleSearch = (query: string) => {
    const results = performSearch(query);
    trackSearch(query, results.length);
  };
}
```

---

## 📱 Admin Dashboard UI

### Analytics Dashboard Pages

```
/admin/analytics
├── /overview          → Main dashboard with key metrics
├── /realtime          → Live visitor tracking
├── /pages             → Top pages breakdown
├── /geo               → Geographic data
├── /devices           → Device/browser stats
├── /performance       → Core Web Vitals
├── /events            → Custom events tracking
└── /settings          → Analytics configuration
```

### Dashboard Components
```typescript
// Key metrics cards
<StatCard
  title="Total Visitors"
  value="12,456"
  change={12.5}
  icon={<UsersIcon />}
/>

// Real-time map
<RealTimeMap data={geoData} />

// Time-series chart
<LineChart
  data={pageViewsOverTime}
  xAxis="date"
  yAxis="views"
/>

// Performance score
<WebVitalsScore
  lcp={{ value: 1200, rating: 'good' }}
  inp={{ value: 85, rating: 'good' }}
  cls={{ value: 0.05, rating: 'good' }}
/>
```

---

## 🔒 Privacy & GDPR Compliance

### Data Collection Consent
```typescript
// components/analytics/consent-banner.tsx
function ConsentBanner() {
  const [consent, setConsent] = useLocalStorage('analytics-consent', null);
  
  if (consent !== null) return null;
  
  return (
    <div className="fixed bottom-0 left-0 right-0 bg-gray-900 text-white p-4">
      <p>We use analytics to improve your experience. No personal data is stored.</p>
      <button onClick={() => setConsent(true)}>Accept</button>
      <button onClick={() => setConsent(false)}>Decline</button>
    </div>
  );
}
```

### Privacy Features
- IP addresses hashed (not stored raw)
- No cookies for tracking (use fingerprinting)
- No cross-site tracking
- Data retention: 90 days (configurable)
- Right to be forgotten: Delete visitor data on request

---

## 📦 Installation & Setup

### 1. Install Dependencies
```bash
# Backend (NestJS)
npm install @nestjs/bull ioredis ua-parser-js maxmind

# Frontend (Next.js)
npm install web-vitals

# Database
# PostgreSQL + Redis + ClickHouse (optional)
```

### 2. Environment Variables
```env
# Analytics
ANALYTICS_ENABLED=true
ANALYTICS_RETENTION_DAYS=90
GEOIP_DB_PATH=/path/to/GeoLite2-City.mmdb

# Redis
REDIS_URL=redis://localhost:6379

# ClickHouse (optional for high volume)
CLICKHOUSE_URL=http://localhost:8123
```

### 3. Database Migration
```bash
npx prisma migrate dev --name add_analytics_tables
```

### 4. Download GeoIP Database
```bash
# MaxMind GeoLite2 (free)
wget https://download.maxmind.com/app/geoip_download?...
```

---

## 📊 Performance Considerations

### Optimization Strategies
1. **Batch Insert**: Queue page views, flush every 5 seconds
2. **Aggregation Jobs**: Hourly/daily rollup of stats
3. **Redis Caching**: Cache real-time counters
4. **Materialized Views**: Pre-computed reports
5. **Partitioning**: Partition by date for large tables

### Resource Usage Estimates
| Traffic | Storage/Month | Cost |
|---------|--------------|------|
| 10K visits | ~500 MB | $5 |
| 100K visits | ~5 GB | $15 |
| 1M visits | ~50 GB | $50 |

---

## 🎯 Implementation Phases

### Phase 1: Basic Tracking (Week 1)
- Page views
- Unique visitors
- Session tracking
- Basic dashboard

### Phase 2: Enhanced Tracking (Week 2)
- Geographic data
- Device detection
- Custom events
- Real-time dashboard

### Phase 3: Performance (Week 3)
- Core Web Vitals
- Performance dashboard
- Optimization suggestions

### Phase 4: Advanced (Week 4)
- Funnel analysis
- Cohort analysis
- Custom reports
- Export functionality

---

## Summary

| Feature | Implementation | Status |
|---------|---------------|--------|
| Page Views | PostgreSQL + API | Planned |
| Unique Visitors | Fingerprinting | Planned |
| Geographic | MaxMind GeoIP | Planned |
| Device/Browser | ua-parser-js | Planned |
| Core Web Vitals | PerformanceObserver | Planned |
| Real-time | Redis + WebSocket | Planned |
| Custom Events | Event API | Planned |
| Dashboard | React + Charts | Planned |

**Total Development Time:** 3-4 weeks
**Total Cost:** Self-hosted (~$20-50/month)
