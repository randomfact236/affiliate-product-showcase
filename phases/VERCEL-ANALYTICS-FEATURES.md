# Vercel Analytics - Complete Tracking Features

## Overview
Vercel provides built-in analytics for projects deployed on their platform. This document lists all tracking capabilities available.

---

## 📊 Vercel Analytics (Web Analytics)

### 1. Page Views
- **Total Page Views** - Number of times pages were viewed
- **Unique Visitors** - Distinct visitors (based on browser fingerprinting)
- **Views per Page** - Breakdown by individual pages
- **Trends Over Time** - Hourly, daily, weekly, monthly views

### 2. Visitor Information
- **Geographic Location**
  - Country
  - Region/State
  - City
- **Device Information**
  - Device Type (Desktop, Mobile, Tablet)
  - Operating System (Windows, macOS, iOS, Android, Linux)
  - Browser (Chrome, Safari, Firefox, Edge, etc.)
- **Screen Resolution** - Viewport sizes

### 3. Traffic Sources
- **Referrers**
  - Direct traffic
  - Search engines (Google, Bing, etc.)
  - Social media (Facebook, Twitter, LinkedIn, etc.)
  - Other websites
- **UTM Parameters**
  - utm_source
  - utm_medium
  - utm_campaign
  - utm_term
  - utm_content

### 4. Performance Metrics (Core Web Vitals)
- **LCP (Largest Contentful Paint)** - Loading performance
- **FID (First Input Delay)** - Interactivity (now INP)
- **CLS (Cumulative Layout Shift)** - Visual stability
- **INP (Interaction to Next Paint)** - New interactivity metric
- **TTFB (Time to First Byte)** - Server response time
- **FCP (First Contentful Paint)** - First content render

### 5. Visitor Behavior
- **Bounce Rate** - Single page visits
- **Visit Duration** - Time spent on site
- **Pages per Visit** - Average page views per session

---

## 🔧 Vercel Speed Insights

### Performance Monitoring
- **Real User Monitoring (RUM)** - Actual visitor data
- **Core Web Vitals Scores**
  - Good, Needs Improvement, Poor ratings
- **Performance Distribution**
  - Percentile breakdowns (p50, p75, p90, p99)

### Metrics Tracked
- Performance Score (0-100)
- LCP, INP, CLS, TTFB, FCP
- Good/Needs Improvement/Poor ratings

---

## 📈 Vercel Audience Analytics

### Visitor Demographics
- **Top Countries** - Where visitors are located
- **Top Regions** - States/provinces
- **Top Cities** - Most popular cities

### Technology Breakdown
- **Browsers**: Chrome, Safari, Firefox, Edge, Opera, Samsung Internet
- **Operating Systems**: Windows, macOS, iOS, Android, Linux
- **Device Categories**: Desktop (~60%), Mobile (~35%), Tablet (~5%)

### Traffic Patterns
- **Hourly Distribution** - Peak hours
- **Daily Distribution** - Day of week patterns
- **Traffic Sources**: Direct, Organic Search, Referral, Social

---

## 🛠️ Custom Event Tracking

### Using @vercel/analytics
```bash
npm install @vercel/analytics
```

### Automatic Tracking
```tsx
import { Analytics } from '@vercel/analytics/react';

export default function RootLayout({ children }) {
  return (
    <html>
      <body>
        {children}
        <Analytics />
      </body>
    </html>
  );
}
```

### Custom Events
```typescript
import { track } from '@vercel/analytics';

// Track button click
track('Product Clicked', {
  productId: '123',
  productName: 'SEMrush Pro',
  price: 119,
});

// Track conversion
track('Affiliate Link Clicked', {
  productId: '123',
  platform: 'SEMrush',
  revenue: 50,
});

// Track search
track('Search', {
  query: 'web hosting',
  resultsCount: 15,
});
```

---

## 📊 Affiliate-Specific Events to Track

| Event Name | Properties | Purpose |
|------------|------------|---------|
| product_view | productId, productName, category | Track product page views |
| product_click | productId, platform, position | Track affiliate link clicks |
| category_view | categoryId, categoryName | Track category page views |
| search_query | query, resultsCount, filters | Track search behavior |
| filter_applied | filterType, filterValue | Track filter usage |
| blog_post_view | postId, postTitle, category | Track blog engagement |
| scroll_depth | page, depth | Track content engagement |
| cta_click | ctaType, ctaText, location | Track call-to-action clicks |

---

## 🔍 Server-Side Analytics

### Vercel Logs
- **Request Logs**: HTTP method, URL, status code, response time
- **Function Logs**: Serverless invocations, cold starts, duration
- **Edge Function Logs**: Middleware execution, cache hits/misses

### Log Drains
Export logs to external services: Datadog, Splunk, LogDNA, Custom HTTP

---

## 📱 Dashboard Views

### 1. Overview Dashboard
- Last 30 Days stats
- Visitors and Page Views
- Top Pages and Countries
- Growth percentages

### 2. Real-Time View
- Active visitors right now
- Pages being viewed
- Recent referrers
- Live traffic graph

### 3. Performance View
- Core Web Vitals over time
- Performance score trends
- Slowest/fastest pages

### 4. Audience View
- Device, Browser, OS breakdown
- Geographic heatmap

---

## 🎯 Analytics Integration Options

### 1. Vercel Native (Simple)
**Pros:** Easy setup, fast, privacy-friendly
**Cons:** Limited custom events on free tier

### 2. Vercel + GA4 (Full-featured)
**Pros:** Comprehensive, free, industry standard
**Cons:** Cookie consent required

### 3. Privacy-First (Plausible)
**Pros:** No cookies, GDPR compliant
**Cons:** Paid service ($9/month)

---

## 🔧 Implementation

### Current Setup
- No analytics installed yet

### Recommended
```bash
npm install @vercel/analytics
```

Add to layout.tsx and track custom events for affiliate clicks, product views, etc.

---

## Summary

| Analytics Type | Provider | Cost |
|----------------|----------|------|
| Web Analytics | Vercel | Free tier |
| Speed Insights | Vercel | Free |
| Custom Events | Vercel | Free tier |
| Advanced Analytics | GA4 | Free |

**Total Vercel Tracking:** 20+ metrics automatically
**With Custom Events:** Unlimited tracking potential
