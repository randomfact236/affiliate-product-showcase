# Implementation Plan V2 - Analytics + Ribbon + Blog Features

## Based on analytics-8 Reference Implementation

---

## 📊 PART 1: ANALYTICS SYSTEM (Detailed Implementation)

### 1.1 Database Schema (Prisma)

```prisma
// Analytics Tables
model AnalyticsVisitor {
  id              String   @id @default(cuid())
  visitorId       String   @unique @map("visitor_id")
  fingerprint     String?
  firstSeen       DateTime @default(now()) @map("first_seen")
  lastSeen        DateTime @default(now()) @map("last_seen")
  
  // Geographic
  countryCode     String?  @map("country_code")
  countryName     String?  @map("country_name")
  region          String?
  city            String?
  timezone        String?
  
  // Device
  deviceType      String?  @map("device_type") // desktop, mobile, tablet
  osName          String?  @map("os_name")
  osVersion       String?  @map("os_version")
  browserName     String?  @map("browser_name")
  browserVersion  String?  @map("browser_version")
  screenWidth     Int?     @map("screen_width")
  screenHeight    Int?     @map("screen_height")
  language        String?
  
  // Relations
  sessions        AnalyticsSession[]
  pageViews       AnalyticsPageView[]
  events          AnalyticsEvent[]
  webVitals       AnalyticsWebVital[]
  
  @@index([visitorId])
  @@index([countryCode])
  @@index([deviceType])
  @@map("analytics_visitors")
}

model AnalyticsSession {
  id              String   @id @default(cuid())
  sessionId       String   @unique @map("session_id")
  visitorId       String   @map("visitor_id")
  visitor         AnalyticsVisitor @relation(fields: [visitorId], references: [id])
  
  startedAt       DateTime @default(now()) @map("started_at")
  endedAt         DateTime? @map("ended_at")
  durationSeconds Int      @default(0) @map("duration_seconds")
  pageViews       Int      @default(0) @map("page_views")
  isBounce        Boolean  @default(true) @map("is_bounce")
  
  // Traffic Source
  referrerUrl     String?  @map("referrer_url")
  referrerType    String?  @map("referrer_type") // direct, search, social, referral
  utmSource       String?  @map("utm_source")
  utmMedium       String?  @map("utm_medium")
  utmCampaign     String?  @map("utm_campaign")
  utmTerm         String?  @map("utm_term")
  utmContent      String?  @map("utm_content")
  
  landingPage     String?  @map("landing_page")
  exitPage        String?  @map("exit_page")
  
  // Relations
  pageViews       AnalyticsPageView[]
  events          AnalyticsEvent[]
  webVitals       AnalyticsWebVital[]
  
  @@index([sessionId])
  @@index([startedAt])
  @@map("analytics_sessions")
}

model AnalyticsPageView {
  id            String   @id @default(cuid())
  sessionId     String   @map("session_id")
  session       AnalyticsSession @relation(fields: [sessionId], references: [id])
  visitorId     String   @map("visitor_id")
  visitor       AnalyticsVisitor @relation(fields: [visitorId], references: [id])
  
  pagePath      String   @map("page_path")
  pageTitle     String?  @map("page_title")
  referrer      String?
  queryParams   Json?    @map("query_params")
  
  timeOnPage    Int      @default(0) @map("time_on_page") // seconds
  scrollDepth   Int      @default(0) @map("scroll_depth") // percentage
  
  timestamp     DateTime @default(now())
  createdAt     DateTime @default(now()) @map("created_at")
  
  @@index([timestamp])
  @@index([pagePath])
  @@index([sessionId])
  @@map("analytics_page_views")
}

model AnalyticsEvent {
  id              String   @id @default(cuid())
  sessionId       String   @map("session_id")
  session         AnalyticsSession @relation(fields: [sessionId], references: [id])
  visitorId       String   @map("visitor_id")
  visitor         AnalyticsVisitor @relation(fields: [visitorId], references: [id])
  
  eventName       String   @map("event_name")
  eventCategory   String?  @map("event_category") // affiliate, engagement, conversion
  eventProperties Json?    @map("event_properties")
  
  pagePath        String?  @map("page_path")
  timestamp       DateTime @default(now())
  createdAt       DateTime @default(now()) @map("created_at")
  
  @@index([eventName])
  @@index([timestamp])
  @@map("analytics_events")
}

model AnalyticsWebVital {
  id            String   @id @default(cuid())
  sessionId     String   @map("session_id")
  session       AnalyticsSession @relation(fields: [sessionId], references: [id])
  visitorId     String   @map("visitor_id")
  visitor       AnalyticsVisitor @relation(fields: [visitorId], references: [id])
  
  pagePath      String   @map("page_path")
  metricName    String   @map("metric_name") // LCP, INP, CLS, TTFB, FCP
  metricValue   Decimal  @map("metric_value") @db.Decimal(10, 3)
  metricRating  String?  @map("metric_rating") // good, needs-improvement, poor
  
  timestamp     DateTime @default(now())
  createdAt     DateTime @default(now()) @map("created_at")
  
  @@index([metricName, timestamp])
  @@map("analytics_web_vitals")
}
```

### 1.2 API Endpoints

```typescript
// Tracking Endpoints
POST /api/analytics/track/pageview
POST /api/analytics/track/event
POST /api/analytics/track/web-vital
POST /api/analytics/track/heartbeat

// Dashboard Endpoints
GET  /api/analytics/dashboard/overview?period=7d
GET  /api/analytics/dashboard/realtime
GET  /api/analytics/dashboard/pages?period=7d
GET  /api/analytics/dashboard/geo?period=7d
GET  /api/analytics/dashboard/devices?period=7d
GET  /api/analytics/dashboard/performance?period=7d
GET  /api/analytics/dashboard/events?period=7d
GET  /api/analytics/dashboard/affiliate?period=7d

// Export Endpoints
GET  /api/analytics/export/csv?period=30d
GET  /api/analytics/export/pdf?period=30d
```

### 1.3 Frontend Tracking Script

```typescript
// lib/analytics.ts
interface AnalyticsConfig {
  apiUrl: string;
  enabled: boolean;
}

class Analytics {
  private sessionId: string | null = null;
  private visitorId: string | null = null;
  private heartbeatInterval: NodeJS.Timeout | null = null;
  private startTime: number = Date.now();
  private maxScrollDepth: number = 0;

  async init() {
    // Generate or retrieve visitor ID
    this.visitorId = this.getOrCreateVisitorId();
    
    // Track initial page view
    await this.trackPageView();
    
    // Setup event listeners
    this.setupScrollTracking();
    this.setupHeartbeat();
    this.setupWebVitals();
    
    // Track before unload
    window.addEventListener('beforeunload', () => {
      this.sendHeartbeat(true);
    });
  }

  private async trackPageView() {
    const response = await fetch('/api/analytics/track/pageview', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        page_path: window.location.pathname,
        page_title: document.title,
        referrer: document.referrer,
        screen_width: window.screen.width,
        screen_height: window.screen.height,
        language: navigator.language,
        utm_source: this.getQueryParam('utm_source'),
        utm_medium: this.getQueryParam('utm_medium'),
        utm_campaign: this.getQueryParam('utm_campaign'),
      }),
    });
    
    const data = await response.json();
    this.sessionId = data.sessionId;
  }

  trackEvent(name: string, properties?: Record<string, any>) {
    fetch('/api/analytics/track/event', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        event_name: name,
        event_properties: properties,
        page_path: window.location.pathname,
        session_id: this.sessionId,
      }),
    });
  }

  trackAffiliateClick(productId: string, platform: string, revenue?: number) {
    this.trackEvent('affiliate_click', {
      product_id: productId,
      platform,
      revenue,
      click_position: this.getClickPosition(),
    });
  }

  trackProductView(productId: string, productName: string, category: string) {
    this.trackEvent('product_view', {
      product_id: productId,
      product_name: productName,
      category,
    });
  }

  trackSearch(query: string, resultsCount: number) {
    this.trackEvent('search', {
      query,
      results_count: resultsCount,
    });
  }

  private setupScrollTracking() {
    let ticking = false;
    
    window.addEventListener('scroll', () => {
      if (!ticking) {
        window.requestAnimationFrame(() => {
          const scrollPercent = Math.round(
            (window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100
          );
          if (scrollPercent > this.maxScrollDepth) {
            this.maxScrollDepth = scrollPercent;
          }
          ticking = false;
        });
        ticking = true;
      }
    });
  }

  private setupHeartbeat() {
    this.heartbeatInterval = setInterval(() => {
      this.sendHeartbeat();
    }, 30000); // Every 30 seconds
  }

  private async sendHeartbeat(final: boolean = false) {
    if (!this.sessionId) return;
    
    await fetch('/api/analytics/track/heartbeat', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        session_id: this.sessionId,
        time_on_page: Math.round((Date.now() - this.startTime) / 1000),
        scroll_depth: this.maxScrollDepth,
        is_final: final,
      }),
    });
  }

  private setupWebVitals() {
    import('web-vitals').then(({ onLCP, onINP, onCLS, onTTFB, onFCP }) => {
      const sendWebVital = (metric: any) => {
        fetch('/api/analytics/track/web-vital', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            metric_name: metric.name,
            metric_value: metric.value,
            metric_rating: metric.rating,
            page_path: window.location.pathname,
            session_id: this.sessionId,
          }),
        });
      };

      onLCP(sendWebVital);
      onINP(sendWebVital);
      onCLS(sendWebVital);
      onTTFB(sendWebVital);
      onFCP(sendWebVital);
    });
  }

  private getOrCreateVisitorId(): string {
    let id = localStorage.getItem('analytics_visitor_id');
    if (!id) {
      id = this.generateFingerprint();
      localStorage.setItem('analytics_visitor_id', id);
    }
    return id;
  }

  private generateFingerprint(): string {
    const components = [
      navigator.userAgent,
      navigator.language,
      screen.colorDepth,
      screen.width + 'x' + screen.height,
      new Date().getTimezoneOffset(),
      !!window.sessionStorage,
      !!window.localStorage,
      navigator.hardwareConcurrency,
    ];
    return btoa(components.join('')).slice(0, 32);
  }

  private getQueryParam(name: string): string | null {
    const url = new URL(window.location.href);
    return url.searchParams.get(name);
  }

  private getClickPosition(): string {
    // Determine if click was above fold, mid-page, or below fold
    const scrollY = window.scrollY;
    const viewportHeight = window.innerHeight;
    const pageHeight = document.body.scrollHeight;
    
    if (scrollY < viewportHeight * 0.5) return 'above_fold';
    if (scrollY > pageHeight - viewportHeight * 1.5) return 'below_fold';
    return 'mid_page';
  }
}

export const analytics = new Analytics();
```

---

## 🎀 PART 2: RIBBON MANAGEMENT SYSTEM

### 2.1 Database Schema

```prisma
model Ribbon {
  id          String    @id @default(cuid())
  name        String    // "Featured", "New", "Sale"
  label       String    // Display text
  bgColor     String    @map("bg_color") // Background color (hex)
  textColor   String    @map("text_color") // Text color (hex)
  icon        String?   // Lucide icon name
  sortOrder   Int       @default(0) @map("sort_order")
  isActive    Boolean   @default(true) @map("is_active")
  
  // Relations
  products    ProductRibbon[]
  
  createdAt   DateTime  @default(now()) @map("created_at")
  updatedAt   DateTime  @updatedAt @map("updated_at")
  
  @@index([isActive, sortOrder])
  @@map("ribbons")
}

model ProductRibbon {
  id          String   @id @default(cuid())
  productId   String   @map("product_id")
  product     Product  @relation(fields: [productId], references: [id], onDelete: Cascade)
  ribbonId    String   @map("ribbon_id")
  ribbon      Ribbon   @relation(fields: [ribbonId], references: [id], onDelete: Cascade)
  
  createdAt   DateTime @default(now()) @map("created_at")
  
  @@unique([productId, ribbonId])
  @@index([productId])
  @@map("product_ribbons")
}
```

### 2.2 Admin Pages

```
/admin/ribbons
├── /page.tsx              # Ribbon list
├── /new/page.tsx          # Create ribbon
├── /[id]/edit/page.tsx    # Edit ribbon
```

### 2.3 Default Ribbons

```typescript
const DEFAULT_RIBBONS = [
  { name: 'Featured', label: 'Featured', bgColor: '#3B82F6', textColor: '#FFFFFF', icon: 'Star' },
  { name: 'New', label: 'New Arrival', bgColor: '#10B981', textColor: '#FFFFFF', icon: 'Sparkles' },
  { name: 'Sale', label: 'Sale', bgColor: '#EF4444', textColor: '#FFFFFF', icon: 'Tag' },
  { name: 'Popular', label: 'Popular', bgColor: '#F59E0B', textColor: '#FFFFFF', icon: 'TrendingUp' },
  { name: 'Verified', label: 'Verified', bgColor: '#8B5CF6', textColor: '#FFFFFF', icon: 'CheckCircle' },
];
```

### 2.4 Ribbon Component

```typescript
// components/ribbon/ribbon-badge.tsx
interface RibbonBadgeProps {
  ribbon: Ribbon;
  className?: string;
}

export function RibbonBadge({ ribbon, className }: RibbonBadgeProps) {
  return (
    <span
      className={cn(
        "inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold",
        className
      )}
      style={{
        backgroundColor: ribbon.bgColor,
        color: ribbon.textColor,
      }}
    >
      {ribbon.icon && <Icon name={ribbon.icon} className="h-3 w-3" />}
      {ribbon.label}
    </span>
  );
}
```

---

## 📰 PART 3: BLOG "DON'T MISS" CONTAINER

### 3.1 Database Schema

```prisma
model BlogPost {
  id            String   @id @default(cuid())
  title         String
  slug          String   @unique
  excerpt       String?
  content       String   @db.Text
  featuredImage String?  @map("featured_image")
  
  // Relations
  categoryId    String?  @map("category_id")
  category      BlogCategory? @relation(fields: [categoryId], references: [id])
  tags          BlogTag[]
  
  // Status & Visibility
  status        BlogStatus @default(DRAFT)
  publishedAt   DateTime?  @map("published_at")
  
  // Don't Miss Section
  isDontMiss    Boolean   @default(false) @map("is_dont_miss")
  dontMissOrder Int       @default(0) @map("dont_miss_order")
  dontMissImage String?   @map("dont_miss_image") // Custom image for Don't Miss section
  
  // Analytics
  viewCount     Int       @default(0) @map("view_count")
  
  // Author
  authorId      String    @map("author_id")
  author        User      @relation(fields: [authorId], references: [id])
  
  createdAt     DateTime  @default(now()) @map("created_at")
  updatedAt     DateTime  @updatedAt @map("updated_at")
  
  @@index([isDontMiss, dontMissOrder])
  @@index([status, publishedAt])
  @@map("blog_posts")
}

model BlogCategory {
  id          String     @id @default(cuid())
  name        String
  slug        String     @unique
  color       String     @default("#3B82F6")
  
  posts       BlogPost[]
  
  @@map("blog_categories")
}

model BlogTag {
  id     String     @id @default(cuid())
  name   String
  slug   String     @unique
  
  posts  BlogPost[]
  
  @@map("blog_tags")
}

enum BlogStatus {
  DRAFT
  PUBLISHED
  ARCHIVED
}
```

### 3.2 Don't Miss Component

```typescript
// components/blog/dont-miss-section.tsx
interface DontMissSectionProps {
  posts: BlogPost[];
  categories: BlogCategory[];
}

export function DontMissSection({ posts, categories }: DontMissSectionProps) {
  const [activeCategory, setActiveCategory] = useState('all');
  
  const filteredPosts = activeCategory === 'all' 
    ? posts 
    : posts.filter(post => post.category?.slug === activeCategory);

  return (
    <section className="py-8 bg-white">
      <div className="container mx-auto px-4">
        {/* Header with Tabs */}
        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
          <h2 className="text-xl font-bold text-gray-900 flex items-center gap-2">
            <span className="w-1 h-6 bg-red-600 rounded-full"></span>
            DON&apos;T MISS
          </h2>
          
          {/* Category Tabs */}
          <ResponsiveTabs
            tabs={[
              { id: 'all', label: 'All' },
              ...categories.map(cat => ({ id: cat.slug, label: cat.name }))
            ]}
            activeTab={activeCategory}
            onTabChange={setActiveCategory}
          />
        </div>

        {/* Posts Grid - Newspaper Style */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
          {filteredPosts.slice(0, 5).map((post, index) => (
            <article
              key={post.id}
              className={`group ${index === 0 ? 'md:col-span-2 md:row-span-2' : ''}`}
            >
              <Link href={`/blog/${post.slug}`} className="block">
                <div className={`relative overflow-hidden rounded-lg ${
                  index === 0 ? 'aspect-[4/3]' : 'aspect-[16/10]'
                }`}>
                  <Image
                    src={post.dontMissImage || post.featuredImage}
                    alt={post.title}
                    fill
                    className="object-cover transition-transform duration-500 group-hover:scale-105"
                  />
                  <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent" />
                  
                  {/* Category Badge */}
                  {post.category && (
                    <span 
                      className="absolute top-3 left-3 px-3 py-1 text-white text-xs font-medium rounded"
                      style={{ backgroundColor: post.category.color }}
                    >
                      {post.category.name}
                    </span>
                  )}
                  
                  {/* Content */}
                  <div className="absolute bottom-0 left-0 right-0 p-4">
                    <h3 className={`font-bold text-white leading-tight ${
                      index === 0 ? 'text-xl md:text-2xl' : 'text-sm'
                    }`}>
                      {post.title}
                    </h3>
                    {index === 0 && post.excerpt && (
                      <p className="text-gray-300 text-sm mt-2 line-clamp-2 hidden md:block">
                        {post.excerpt}
                      </p>
                    )}
                    <div className="flex items-center gap-4 mt-2 text-gray-400 text-xs">
                      <span className="flex items-center gap-1">
                        <Clock className="h-3 w-3" />
                        {formatDate(post.publishedAt)}
                      </span>
                      <span className="flex items-center gap-1">
                        <Eye className="h-3 w-3" />
                        {post.viewCount.toLocaleString()}
                      </span>
                    </div>
                  </div>
                </div>
              </Link>
            </article>
          ))}
        </div>
      </div>
    </section>
  );
}
```

---

## ⚙️ PART 4: DYNAMIC BLOG SETTINGS

### 4.1 Database Schema

```prisma
model BlogSettings {
  id                    String   @id @default(cuid())
  
  // General Settings
  blogTitle           String   @default("Blog") @map("blog_title")
  blogDescription     String?  @map("blog_description")
  postsPerPage        Int      @default(12) @map("posts_per_page")
  
  // Homepage Settings
  showHeroSection     Boolean  @default(true) @map("show_hero_section")
  heroTitle           String?  @map("hero_title")
  heroSubtitle        String?  @map("hero_subtitle")
  heroImage           String?  @map("hero_image")
  
  // Don't Miss Section
  showDontMiss        Boolean  @default(true) @map("show_dont_miss")
  dontMissTitle       String   @default("DON'T MISS") @map("dont_miss_title")
  dontMissPostCount   Int      @default(5) @map("dont_miss_post_count")
  
  // Sidebar Settings
  showSidebar         Boolean  @default(true) @map("show_sidebar")
  showRecentPosts     Boolean  @default(true) @map("show_recent_posts")
  recentPostsCount    Int      @default(5) @map("recent_posts_count")
  showCategories      Boolean  @default(true) @map("show_categories")
  showTags            Boolean  @default(true) @map("show_tags")
  showNewsletter      Boolean  @default(true) @map("show_newsletter")
  newsletterTitle     String   @default("Stay Updated") @map("newsletter_title")
  newsletterText      String?  @map("newsletter_text")
  
  // SEO Settings
  metaTitle           String?  @map("meta_title")
  metaDescription     String?  @map("meta_description")
  ogImage             String?  @map("og_image")
  
  // Social Sharing
  showShareButtons    Boolean  @default(true) @map("show_share_buttons")
  facebookShare       Boolean  @default(true) @map("facebook_share")
  twitterShare        Boolean  @default(true) @map("twitter_share")
  linkedinShare       Boolean  @default(true) @map("linkedin_share")
  
  // Comments
  enableComments      Boolean  @default(false) @map("enable_comments")
  commentModeration   Boolean  @default(true) @map("comment_moderation")
  
  updatedAt           DateTime @updatedAt @map("updated_at")
  
  @@map("blog_settings")
}
```

### 4.2 Admin Settings Page

```typescript
// app/admin/settings/blog/page.tsx
export default function BlogSettingsPage() {
  return (
    <div className="max-w-4xl">
      <h1 className="text-2xl font-bold mb-6">Blog Settings</h1>
      
      <Tabs defaultValue="general">
        <TabsList>
          <TabsTrigger value="general">General</TabsTrigger>
          <TabsTrigger value="homepage">Homepage</TabsTrigger>
          <TabsTrigger value="sidebar">Sidebar</TabsTrigger>
          <TabsTrigger value="seo">SEO</TabsTrigger>
          <TabsTrigger value="social">Social</TabsTrigger>
          <TabsTrigger value="comments">Comments</TabsTrigger>
        </TabsList>
        
        <TabsContent value="general">
          <GeneralSettingsForm />
        </TabsContent>
        
        <TabsContent value="homepage">
          <HomepageSettingsForm />
        </TabsContent>
        
        <TabsContent value="sidebar">
          <SidebarSettingsForm />
        </TabsContent>
        
        <TabsContent value="seo">
          <SEOSettingsForm />
        </TabsContent>
        
        <TabsContent value="social">
          <SocialSettingsForm />
        </TabsContent>
        
        <TabsContent value="comments">
          <CommentsSettingsForm />
        </TabsContent>
      </Tabs>
    </div>
  );
}
```

### 4.3 Settings Form Components

```typescript
// components/settings/blog/general-settings.tsx
export function GeneralSettingsForm() {
  const { settings, updateSettings } = useBlogSettings();
  
  return (
    <Card className="p-6">
      <h3 className="text-lg font-semibold mb-4">General Settings</h3>
      
      <div className="space-y-4">
        <div>
          <Label>Blog Title</Label>
          <Input
            value={settings.blogTitle}
            onChange={(e) => updateSettings({ blogTitle: e.target.value })}
          />
        </div>
        
        <div>
          <Label>Blog Description</Label>
          <Textarea
            value={settings.blogDescription}
            onChange={(e) => updateSettings({ blogDescription: e.target.value })}
          />
        </div>
        
        <div>
          <Label>Posts Per Page</Label>
          <Input
            type="number"
            value={settings.postsPerPage}
            onChange={(e) => updateSettings({ postsPerPage: parseInt(e.target.value) })}
            min={1}
            max={50}
          />
        </div>
      </div>
    </Card>
  );
}

// components/settings/blog/homepage-settings.tsx
export function HomepageSettingsForm() {
  const { settings, updateSettings } = useBlogSettings();
  
  return (
    <Card className="p-6">
      <h3 className="text-lg font-semibold mb-4">Homepage Settings</h3>
      
      <div className="space-y-4">
        <Switch
          checked={settings.showHeroSection}
          onCheckedChange={(checked) => updateSettings({ showHeroSection: checked })}
          label="Show Hero Section"
        />
        
        {settings.showHeroSection && (
          <>
            <div>
              <Label>Hero Title</Label>
              <Input
                value={settings.heroTitle}
                onChange={(e) => updateSettings({ heroTitle: e.target.value })}
              />
            </div>
            
            <div>
              <Label>Hero Subtitle</Label>
              <Input
                value={settings.heroSubtitle}
                onChange={(e) => updateSettings({ heroSubtitle: e.target.value })}
              />
            </div>
            
            <div>
              <Label>Hero Image</Label>
              <ImageUpload
                value={settings.heroImage}
                onChange={(url) => updateSettings({ heroImage: url })}
              />
            </div>
          </>
        )}
        
        <hr />
        
        <Switch
          checked={settings.showDontMiss}
          onCheckedChange={(checked) => updateSettings({ showDontMiss: checked })}
          label="Show Don't Miss Section"
        />
        
        {settings.showDontMiss && (
          <>
            <div>
              <Label>Don't Miss Title</Label>
              <Input
                value={settings.dontMissTitle}
                onChange={(e) => updateSettings({ dontMissTitle: e.target.value })}
              />
            </div>
            
            <div>
              <Label>Number of Posts</Label>
              <Input
                type="number"
                value={settings.dontMissPostCount}
                onChange={(e) => updateSettings({ dontMissPostCount: parseInt(e.target.value) })}
                min={3}
                max={10}
              />
            </div>
          </>
        )}
      </div>
    </Card>
  );
}
```

---

## 📅 IMPLEMENTATION TIMELINE

### Week 1: Analytics Foundation
- Day 1-2: Database schema & migrations
- Day 3-4: API endpoints (tracking)
- Day 5-7: Frontend tracking script

### Week 2: Analytics Dashboard
- Day 8-9: Dashboard overview page
- Day 10-11: Real-time & geo pages
- Day 12-14: Performance & events pages

### Week 3: Ribbon Management
- Day 15: Database schema
- Day 16-17: Admin CRUD pages
- Day 18-19: Ribbon component integration
- Day 20-21: Product assignment

### Week 4: Blog Don't Miss + Settings
- Day 22-23: Don't Miss database & component
- Day 24-25: Settings database & admin
- Day 26-28: Settings integration & testing

**Total: 4 weeks for all features**

---

## 🎯 SUMMARY

| Feature | Files | Status |
|---------|-------|--------|
| **Analytics System** | 6 tables, 10+ endpoints, tracking lib | Planned |
| **Ribbon Management** | 2 tables, 3 admin pages, component | Planned |
| **Blog Don't Miss** | 3 tables, component, tabs | Planned |
| **Blog Settings** | 1 table, 6 setting forms | Planned |

**Total New Features: 172 (analytics) + Ribbon + Don't Miss + Settings**
