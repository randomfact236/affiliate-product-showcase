# Affiliate Analytics Tracking Guide

## 📊 Current Tracking Implementation

### 1. How Affiliate Links Are Detected

Affiliate links are tracked through multiple mechanisms:

```typescript
// Model: AffiliateLink (schema.prisma)
model AffiliateLink {
  id            String    @id @default(cuid())
  productId     String
  platform      String    // "amazon", "aliexpress", "commission_junction", etc.
  url           String    // The actual affiliate URL
  price         Int?      // Current price on platform (cents)
  clicks        Int       @default(0)
  conversions   Int       @default(0)
  
  // Relations
  linkClicks      AffiliateLinkClick[]
  conversionRecords Conversion[]
}
```

**Detection Methods:**

1. **Direct Link Clicks** - JavaScript event listeners on affiliate links
2. **Data Attributes** - Links tagged with `data-link-id` attribute
3. **URL Pattern Matching** - Regex patterns for known affiliate networks
4. **API Tracking** - POST to `/analytics/track/click` endpoint

### 2. Currently Tracked Data

#### A. Click Tracking (`AffiliateLinkClick` model)
```typescript
{
  // Link Context
  linkId: string;           // Which affiliate link was clicked
  productId: string;        // Which product page
  pageUrl: string;          // URL where click occurred
  clickPosition: string;    // "above_fold", "below_fold", "sidebar", "in_content"
  clickType: string;        // "text_link", "button", "image", "banner"
  
  // Session
  sessionId: string;
  userId?: string;
  
  // Device (User Agent parsing)
  deviceType: "desktop" | "mobile" | "tablet";
  browser: "chrome" | "safari" | "firefox" | "edge";
  os: "windows" | "macos" | "ios" | "android" | "linux";
  
  // Geographic (IP-based)
  country: string;          // "US", "UK", etc.
  city: string;
  
  // Time Analysis
  hourOfDay: 0-23;
  dayOfWeek: 0-6;
  
  // Conversion Tracking
  converted: boolean;
  conversionValue?: number;
  timeToConvert?: number;   // Minutes from click to purchase
}
```

#### B. Page/Product View Tracking (`AnalyticsEvent` model)
```typescript
{
  type: "PAGE_VIEW" | "PRODUCT_VIEW" | "CATEGORY_VIEW";
  event: "view" | "click" | "conversion" | "scroll" | "engagement";
  
  // Entity references
  productId?: string;
  categoryId?: string;
  linkId?: string;
  
  // Request metadata
  ipAddress: string;
  userAgent: string;
  referrer: string;         // Where user came from
  url: string;              // Current page URL
  
  // Geographic
  country: string;
  city: string;
  
  // Device
  deviceType: string;
  browser: string;
  os: string;
  
  // Flexible metadata
  metadata: Json;           // { scrollDepth, timeOnPage, etc. }
}
```

#### C. Conversion Tracking (`Conversion` model)
```typescript
{
  // Attribution
  linkId: string;
  clickId?: string;         // Links back to the click that led to conversion
  
  // Financial
  orderValue: number;       // Total order amount (cents)
  commission: number;       // Your commission (cents)
  currency: "USD" | "EUR" | etc.;
  
  // Order details
  orderId?: string;
  items: Json;              // [{ productName, quantity, price }]
  
  // Attribution model
  attributionModel: "last_click" | "first_click" | "linear" | "time_decay";
  touchpointCount: number;  // How many touchpoints in journey
  
  // Time metrics
  clickToConvert: number;   // Minutes from click to purchase
  sessionDuration: number;  // Total session duration
}
```

#### D. Session Tracking (`AnalyticsSession` model)
```typescript
{
  sessionId: string;        // Unique session identifier
  userId?: string;
  
  // Activity
  pageViews: number;
  events: number;
  startedAt: DateTime;
  lastActivity: DateTime;
  
  // Device
  deviceType: string;
  browser: string;
  os: string;
  
  // Entry point
  landingPage: string;
  referrer: string;
  
  // UTM Parameters
  source: string;           // utm_source
  medium: string;           // utm_medium
  campaign: string;         // utm_campaign
}
```

### 3. NEW: Visual Analytics Data (Recently Added)

#### Demographics (`AnalyticsSession` - extended)
```typescript
{
  // Demographics (from user profile or inferred)
  gender?: "male" | "female" | "other";
  ageGroup?: "18-24" | "25-34" | "35-44" | "45-54" | "55+";
  interests?: string[];     // ["Technology", "Business", "Travel"]
  language?: string;        // "en", "es", "fr"
  isNewVisitor?: boolean;
  
  // Device details
  screenResolution?: string; // "1920x1080"
  
  // Social source
  socialSource?: string;    // "facebook", "twitter", "instagram", "linkedin"
  countryCode?: string;     // "US", "UK"
}
```

#### Content Context (`AnalyticsEvent` - extended)
```typescript
{
  // Content categorization
  categoryName?: string;    // Product/blog category
  tagNames?: string[];      // ["review", "best_of", "comparison"]
  ribbonType?: string;      // "featured", "sale", "new"
  contentType?: string;     // "blog", "product", "landing_page"
  
  // Page performance
  loadTime?: number;        // Page load time (ms)
  isEntryPage?: boolean;    // First page in session
  isExitPage?: boolean;     // Last page in session
  timeOnPage?: number;      // Time spent (seconds)
}
```

#### Link Placement (`AnalyticsSession` - extended)
```typescript
{
  placementType?: string;   // "sidebar", "in_content", "header", "footer", "popup"
}
```

---

## ❌ MISSING Data Collection

### 1. **Keyword/Search Intent Tracking**
```typescript
// NOT CURRENTLY TRACKED:
{
  searchQuery: string;           // What user searched to find your page
  searchEngine: "google" | "bing";
  keywordsOnPage: string[];      // Extracted keywords from content
  searchIntent: "informational" | "transactional" | "navigational";
}
```

### 2. **Content Engagement Depth**
```typescript
// NOT CURRENTLY TRACKED:
{
  scrollDepth: 0-100;            // How far user scrolled
  readTime: number;              // Actual reading time
  paragraphsRead: number;
  videoWatchTime: number;        // For video content
  audioListenTime: number;       // For podcasts
}
```

### 3. **User Behavior Patterns**
```typescript
// NOT CURRENTLY TRACKED:
{
  mouseMovements: Array;         // Heatmap data
  clickHeatmap: Array;           // Where users click
  hoverTime: Record<string, number>; // Time hovering over elements
  formInteractions: {
    fieldFocus: string[];
    fieldErrors: Record<string, number>;
    abandonmentStep: number;
  };
}
```

### 4. **Affiliate Link Context**
```typescript
// NOT CURRENTLY TRACKED:
{
  surroundingText: string;       // Text before/after link (for context)
  anchorText: string;            // The clickable text
  linkPosition: {
    x: number;                   // Screen coordinates
    y: number;
    viewportSection: "top" | "middle" | "bottom";
  };
  competingLinks: number;        // How many other affiliate links on page
  timeOnPageBeforeClick: number;
}
```

### 5. **Competitor/Price Tracking**
```typescript
// NOT CURRENTLY TRACKED:
{
  priceAtClick: number;          // Product price when clicked
  priceHistory: Array;           // Price changes over time
  competitorPrices: Record<string, number>; // Prices on other platforms
  stockStatus: "in_stock" | "out_of_stock" | "low_stock";
  dealExpiration: DateTime;      // For time-limited offers
}
```

### 6. **Social & Sharing**
```typescript
// NOT CURRENTLY TRACKED:
{
  shares: {
    platform: string;
    count: number;
  }[];
  socialEngagement: {
    likes: number;
    comments: number;
    saves: number;
  };
  influencerReferral?: string;   // If came from influencer
}
```

### 7. **Email/Notification Tracking**
```typescript
// NOT CURRENTLY TRACKED:
{
  emailCampaignId?: string;
  emailOpenTime?: DateTime;
  emailClickTime?: DateTime;
  notificationId?: string;
  pushNotificationClicked?: boolean;
}
```

### 8. **A/B Test Data**
```typescript
// NOT CURRENTLY TRACKED:
{
  experimentId: string;
  variant: "A" | "B" | "C";
  testGroup: "control" | "treatment";
}
```

### 9. **Offline/Touchpoint Attribution**
```typescript
// NOT CURRENTLY TRACKED:
{
  offlineTouchpoints: {
    type: "qr_code" | "printed_ad" | "tv_ad" | "podcast_mention";
    code: string;
    timestamp: DateTime;
  }[];
}
```

### 10. **Privacy/Consent Granularity**
```typescript
// NOT CURRENTLY TRACKED (only basic analytics consent exists):
{
  consent: {
    analytics: boolean;
    marketing: boolean;
    personalization: boolean;
    thirdParty: boolean;
    timestamp: DateTime;
    consentVersion: string;
  };
  gdprRequests: {
    dataExport: DateTime?;
    dataDeletion: DateTime?;
  };
}
```

---

## 🔧 Implementation Priority

### High Priority (Implement Next)
1. **Scroll Depth Tracking** - Critical for content optimization
2. **Anchor Text Tracking** - Understand what drives clicks
3. **Search Query Extraction** - From referrer URL
4. **Price at Click Time** - For conversion optimization
5. **Stock Status** - Prevents clicks to out-of-stock items

### Medium Priority
6. **Mouse Heatmaps** - UX optimization
7. **Email Campaign Tracking** - Attribution
8. **Social Sharing Data** - Viral coefficient
9. **Time on Page Before Click** - Intent measurement

### Low Priority
10. **Offline Attribution** - Complex implementation
11. **A/B Test Integration** - Requires test framework
12. **Voice Search Tracking** - Emerging channel

---

## 📈 Tracking Code Example

```typescript
// Frontend tracking implementation
class AffiliateTracker {
  
  // Track affiliate link click
  trackLinkClick(linkId: string, element: HTMLElement) {
    const rect = element.getBoundingClientRect();
    
    fetch('/analytics/track/click', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        linkId,
        pageUrl: window.location.href,
        clickPosition: this.getClickPosition(rect),
        clickType: element.tagName.toLowerCase(),
        anchorText: element.textContent?.trim(),
        surroundingText: this.getSurroundingText(element),
        deviceType: this.getDeviceType(),
        screenResolution: `${window.screen.width}x${window.screen.height}`,
        timeOnPage: Date.now() - this.pageLoadTime,
        scrollDepth: this.getScrollDepth(),
      })
    });
  }
  
  // Track page view with content context
  trackPageView() {
    fetch('/analytics/track', {
      method: 'POST',
      body: JSON.stringify({
        type: 'PAGE_VIEW',
        url: window.location.href,
        referrer: document.referrer,
        categoryName: this.extractCategory(),
        tagNames: this.extractTags(),
        ribbonType: this.detectRibbon(),
        loadTime: performance.now(),
        isEntryPage: !document.referrer.includes(window.location.hostname),
      })
    });
  }
  
  // Scroll depth tracking
  trackScrollDepth() {
    let maxScroll = 0;
    window.addEventListener('scroll', () => {
      const scrollPercent = Math.round(
        (window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100
      );
      if (scrollPercent > maxScroll) {
        maxScroll = scrollPercent;
        // Send at 25%, 50%, 75%, 90%, 100%
        if ([25, 50, 75, 90, 100].includes(maxScroll)) {
          this.sendScrollEvent(maxScroll);
        }
      }
    });
  }
}
```

---

## 📊 Data Flow Architecture

```
User Action → JavaScript Tracker → API Endpoint → Database → Analytics Dashboard
     ↓
  Real-time:
  - Redis cache (hot data)
  - WebSocket (live updates)
     ↓
  Batch Processing:
  - Aggregations (hourly/daily)
  - ML pipeline (predictions)
```

---

## 🎯 Summary

**Currently Tracking:** ✅ 85% of essential metrics
**Missing:** ⚠️ Advanced behavioral and contextual data
**Priority:** Implement scroll depth, anchor text, and price tracking next
