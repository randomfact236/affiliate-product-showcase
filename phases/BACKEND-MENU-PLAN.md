# Backend Admin Menu Plan - Complete Feature List

## Overview
This document outlines all remaining backend menus and features needed for the Affiliate Product Showcase platform, including the new Auto Image Converter functionality.

---

## 📊 Dashboard
**Route:** `/admin`
**Status:** ✅ Basic layout exists

### Features Needed:
- [ ] Analytics Overview Cards
  - Total Products Count
  - Total Categories Count
  - Total Views (Last 30 Days)
  - Total Affiliate Clicks (Last 30 Days)
  - Revenue Estimates
- [ ] Recent Activity Feed
  - Recently Added Products
  - Recent User Registrations
  - Recent Comments/Reviews
- [ ] Performance Charts
  - Views over time (Line chart)
  - Clicks by category (Bar chart)
  - Top performing products (Pie chart)
- [ ] Quick Actions
  - Add New Product
  - Add New Category
  - Upload Media
- [ ] System Health Status
  - API Status
  - Database Status
  - Storage Status

---

## 📦 Products Management
**Route:** `/admin/products`
**Status:** ✅ Page exists, needs API connection

### Features Needed:
- [ ] Product List View
  - DataTable with sorting/filtering
  - Bulk actions (Delete, Change Status)
  - Search by name/sku
  - Filter by category/status
  - Pagination
- [ ] Add/Edit Product Form
  - Basic Info (Name, Slug, Description)
  - Pricing (Base Price, Sale Price)
  - Categories & Tags (Multi-select)
  - Images Upload with Preview (Auto-converted)
  - Affiliate Links (Multiple platforms)
  - SEO Fields (Meta Title, Meta Description)
  - Product Attributes
  - Status (Draft, Published, Archived)
- [ ] Product Detail View
  - View analytics (Views, Clicks)
  - Edit history
  - Related products

---

## 📁 Categories Management
**Route:** `/admin/categories`
**Status:** ✅ Page exists, needs API connection

### Features Needed:
- [ ] Category Tree View
  - Hierarchical display
  - Drag & drop reordering
  - Expand/Collapse
- [ ] Add/Edit Category Form
  - Name, Slug, Description
  - Parent Category (Dropdown)
  - Category Image
  - SEO Fields
- [ ] Category Stats
  - Product count per category
  - Views per category

---

## 🏷️ Tags Management
**Route:** `/admin/tags`
**Status:** ❌ Not created

### Features Needed:
- [ ] Tags List
  - All tags with product counts
  - Search/Filter
  - Bulk delete
- [ ] Add/Edit Tag
  - Name, Slug
  - Tag color/icon
- [ ] Merge Tags
  - Combine duplicate tags

---

## 🖼️ Media Library
**Route:** `/admin/media`
**Status:** ❌ Not created

### Features Needed:
- [ ] Media Grid View
  - Thumbnails with metadata
  - Filter by type (Image, Video, Document)
  - Sort by date/size/name
  - Infinite scroll or pagination
  - Conversion status indicator (Original/WebP/AVIF)
- [ ] Upload Media (Auto-Conversion)
  - **NO drag & drop** - Traditional file picker
  - **Auto-convert on upload** to WebP/AVIF
  - Preserve original + create optimized versions
  - Progress indicator
  - Upload to MinIO/S3
- [ ] Media Details
  - File info (Size, Dimensions, Type)
  - Multiple versions (Original, WebP, AVIF)
  - URL/Path for each version
  - Usage (Which products use this)
  - Delete with confirmation
- [ ] Bulk Operations
  - Select multiple files
  - Bulk delete
  - Bulk re-convert

---

## 🖼️ Image Converter (Auto-Conversion System)
**Route:** `/admin/tools/image-converter`
**Status:** ❌ Not created

### Core Concept: ZERO Manual Work
**No drag & drop. No manual conversion. Everything is automatic.**

### Features:

#### 1. Auto-Convert on Upload
```
User Uploads JPG/PNG → System instantly creates:
  ├── original-image.jpg (preserved)
  ├── original-image.webp (auto-generated)
  └── original-image.avif (auto-generated)
```

- [ ] **Upload Hook**: Intercept all image uploads
- [ ] **Background Processing**: Convert in background queue
- [ ] **Multiple Formats**: Generate WebP + AVIF simultaneously
- [ ] **Quality Presets**: 
  - High (90%) - For hero images
  - Medium (80%) - Default for product images  
  - Low (60%) - For thumbnails
- [ ] **Size Variants**:
  - Original (full size)
  - Large (1200px width)
  - Medium (600px width)
  - Thumbnail (300px width)

#### 2. Auto-Scan Existing Images
```
System scans Media Library → Finds non-converted images → 
Shows list → One-click convert all
```

- [ ] **Scan Button**: "Scan for Unconverted Images"
- [ ] **Smart Detection**: Identify images without WebP/AVIF versions
- [ ] **Batch List**: Display all images needing conversion
  - Image thumbnail
  - Current format
  - Missing formats (WebP, AVIF)
  - Estimated size savings
- [ ] **Bulk Convert**: "Convert All" button with progress
- [ ] **Individual Convert**: Convert single image

#### 3. Conversion Dashboard
```
┌─────────────────────────────────────────┐
│  🖼️ Image Converter Dashboard           │
├─────────────────────────────────────────┤
│  Stats:                                 │
│  • Total Images: 1,247                  │
│  • Fully Optimized: 892 (71%)           │
│  • Need Conversion: 355 (29%)           │
│  • Storage Saved: 456 MB                │
├─────────────────────────────────────────┤
│  [🔍 Scan for Unconverted Images]       │
├─────────────────────────────────────────┤
│  Recent Conversions:                    │
│  • product-001.jpg → WebP ✓ AVIF ✓      │
│  • banner-hero.png → WebP ✓ AVIF ✓      │
└─────────────────────────────────────────┘
```

- [ ] **Stats Cards**:
  - Total images in library
  - Fully optimized count
  - Images needing conversion
  - Storage space saved
- [ ] **Conversion Queue**: Real-time progress
- [ ] **Settings Panel**:
  - Default quality preset
  - Auto-convert on upload (toggle)
  - Size variants to generate
  - Formats to generate (WebP, AVIF, both)

#### 4. Image Scanner Results Page
```
┌─────────────────────────────────────────┐
│  355 Images Need Conversion             │
│  Estimated savings: 245 MB              │
├─────────────────────────────────────────┤
│  [☑️] Select All                        │
│                                         │
│  ☐ product-001.jpg                      │
│     Missing: WebP, AVIF                 │
│     Savings: ~450 KB                    │
│                                         │
│  ☐ banner-hero.png                      │
│     Missing: AVIF                       │
│     Savings: ~1.2 MB                    │
│                                         │
│  [Convert Selected (24)]                │
│  [Convert All (355)]                    │
└─────────────────────────────────────────┘
```

- [ ] **Filter Options**:
  - Show only missing WebP
  - Show only missing AVIF
  - Show all unoptimized
- [ ] **Sort Options**:
  - By file size (largest first)
  - By upload date
  - By potential savings
- [ ] **Preview**: Click to see image

#### 5. Conversion Settings
```
┌─────────────────────────────────────────┐
│  ⚙️ Auto-Conversion Settings            │
├─────────────────────────────────────────┤
│  ☑️ Enable auto-conversion on upload    │
│                                         │
│  Formats to Generate:                   │
│  ☑️ WebP                                │
│  ☑️ AVIF                                │
│                                         │
│  Quality Presets:                       │
│  • Thumbnail (300w): Low (60%)          │
│  • Medium (600w): Medium (80%)          │
│  • Large (1200w): High (90%)            │
│                                         │
│  Size Variants:                         │
│  ☑️ Original (preserve)                 │
│  ☑️ Large (1200px)                      │
│  ☑️ Medium (600px)                      │
│  ☑️ Thumbnail (300px)                   │
│                                         │
│  [💾 Save Settings]                     │
└─────────────────────────────────────────┘
```

---

## 🔄 Background Job Queue (Bull/Redis)
**Route:** Internal system
**Status:** ❌ Not created

### Features:
- [ ] **Image Conversion Queue**
  - Process conversions in background
  - Retry failed conversions
  - Progress tracking per job
- [ ] **Upload Processing Queue**
  - Handle multiple uploads
  - Priority queue (urgent first)
- [ ] **Scheduled Scan**
  - Daily scan for unconverted images
  - Auto-convert if enabled
- [ ] **Job Monitor Dashboard**
  - Active jobs
  - Completed jobs
  - Failed jobs with retry

---

## 👥 Users Management
**Route:** `/admin/users`
**Status:** ❌ Not created

### Features Needed:
- [ ] Users List
  - All registered users
  - Filter by role/status
  - Search by name/email
  - Pagination
- [ ] Add/Edit User
  - Profile info (Name, Email, Avatar)
  - Role assignment (Admin, Editor, User)
  - Status (Active, Inactive, Banned)
  - Password reset
- [ ] User Details
  - Activity log
  - Products created (if admin/editor)
  - Login history
- [ ] Roles & Permissions
  - Manage roles
  - Assign permissions
  - Permission matrix

---

## 📊 Analytics
**Route:** `/admin/analytics`
**Status:** ❌ Not created

### Features Needed:
- [ ] Traffic Analytics
  - Page views over time
  - Unique visitors
  - Top pages
  - Referrer sources
- [ ] Product Analytics
  - Most viewed products
  - Most clicked affiliate links
  - Conversion rates
  - Revenue by product
- [ ] Category Analytics
  - Views by category
  - Clicks by category
- [ ] Image Optimization Analytics
  - Storage saved by conversion
  - Conversion success rate
  - Average compression ratio
- [ ] Export Reports
  - CSV, PDF, Excel export
  - Date range selection
  - Scheduled reports

---

## ⚙️ Settings
**Route:** `/admin/settings`
**Status:** ❌ Not created

### Features Needed:
- [ ] General Settings
  - Site Name, Logo, Favicon
  - Contact Email, Phone
  - Social Media Links
  - Default SEO settings
- [ ] Image Optimization Settings
  - Auto-conversion toggle
  - Default quality presets
  - Format preferences (WebP/AVIF)
  - Size variant settings
- [ ] Appearance Settings
  - Theme colors
  - Logo upload
  - Custom CSS
- [ ] Email Settings
  - SMTP configuration
  - Email templates
  - Test email
- [ ] API Settings
  - API keys management
  - Webhook URLs
  - Rate limiting
- [ ] Storage Settings
  - MinIO/S3 configuration
  - CDN settings
- [ ] Backup Settings
  - Automated backups
  - Manual backup trigger
  - Restore from backup

---

## 📝 Content Management
**Route:** `/admin/content`
**Status:** ❌ Not created

### Features Needed:
- [ ] Blog Posts
  - Create/Edit posts
  - Rich text editor
  - Featured image (auto-converted)
  - Tags, Categories
  - SEO settings
  - Publish/Schedule/Draft
- [ ] Pages
  - Static pages (About, Contact, Privacy)
  - Custom landing pages
- [ ] Comments Moderation
  - Pending comments
  - Approved comments
  - Spam detection
  - Bulk actions

---

## 🔗 Affiliate Links
**Route:** `/admin/affiliate-links`
**Status:** ❌ Not created

### Features Needed:
- [ ] Links Overview
  - All affiliate links
  - Click tracking
  - Revenue per link
- [ ] Link Health Check
  - Verify links are working
  - Alert for broken links
- [ ] Link Analytics
  - Clicks over time
  - CTR by position
  - Platform comparison

---

## 🔔 Notifications
**Route:** `/admin/notifications`
**Status:** ❌ Not created

### Features:
- [ ] Notification Center
  - System alerts
  - User activity
  - Product updates
  - Image conversion complete
- [ ] Email Notifications
  - Configure triggers
  - Template management

---

## 📋 Implementation Priority

### Phase 1 (Critical - Week 1)
1. ✅ Dashboard (Basic)
2. ✅ Products (Connect API)
3. ✅ Categories (Connect API)
4. 🖼️ Media Library (with auto-convert on upload)

### Phase 2 (Image Optimization - Week 2)
5. 🔄 Background Job Queue (Bull + Redis)
6. 🖼️ **Image Converter Scanner** (Find non-converted)
7. 🖼️ **Bulk Auto-Conversion** (One-click convert all)
8. 🖼️ **Conversion Dashboard** (Stats & settings)

### Phase 3 (Management - Week 3)
9. 👥 Users Management
10. 📝 Blog Posts Management
11. 🏷️ Tags Management

### Phase 4 (Analytics & Settings - Week 4)
12. 📊 Analytics Dashboard
13. ⚙️ Settings Pages
14. 🔗 Affiliate Links
15. 🔔 Notifications

---

## 🖼️ Auto Image Converter Technical Specs

### System Flow
```
┌─────────────┐    ┌─────────────────┐    ┌─────────────────┐
│ User Upload │───→│ Background Job  │───→│ Generate Variants│
│    Image    │    │ Queue (Bull)    │    │                 │
└─────────────┘    └─────────────────┘    └────────┬────────┘
                                                   │
                    ┌──────────────────────────────┘
                    ↓
┌──────────────────────────────────────────────────────────┐
│ Generated Files:                                         │
│ ├── original-image.jpg (preserved)                       │
│ ├── original-image.webp (auto-generated)                 │
│ ├── original-image.avif (auto-generated)                 │
│ ├── original-image-1200.webp (large variant)             │
│ ├── original-image-600.webp (medium variant)             │
│ └── original-image-300.webp (thumbnail variant)          │
└──────────────────────────────────────────────────────────┘
```

### Database Schema Update
```prisma
model ProductImage {
  id          String   @id @default(cuid())
  productId   String
  originalUrl String   // Original file path
  webpUrl     String?  // Auto-generated WebP
  avifUrl     String?  // Auto-generated AVIF
  variants    Json?    // { "1200": "...", "600": "...", "300": "..." }
  isConverted Boolean  @default(false)
  fileSize    Int      // Original size
  webpSize    Int?     // WebP size (for savings calc)
  avifSize    Int?     // AVIF size (for savings calc)
  createdAt   DateTime @default(now())
  updatedAt   DateTime @updatedAt
}
```

### API Endpoints
```
# Upload with auto-convert
POST /api/v1/media/upload
Headers: { "X-Auto-Convert": "true" }
Response: {
  "id": "...",
  "originalUrl": "...",
  "webpUrl": "...",
  "avifUrl": "...",
  "conversionStatus": "processing" | "completed" | "failed"
}

# Scan for unconverted images
GET /api/v1/media/unconverted
Response: {
  "total": 355,
  "images": [...]
}

# Bulk convert images
POST /api/v1/media/convert-batch
Body: {
  "imageIds": ["id1", "id2", ...],
  "formats": ["webp", "avif"],
  "quality": 80,
  "variants": [1200, 600, 300]
}
Response: {
  "jobId": "...",
  "status": "queued"
}

# Get conversion job status
GET /api/v1/media/convert-status/:jobId
Response: {
  "status": "processing",
  "progress": 45,
  "completed": 160,
  "total": 355
}

# Get conversion stats
GET /api/v1/media/conversion-stats
Response: {
  "totalImages": 1247,
  "fullyOptimized": 892,
  "needsConversion": 355,
  "storageSaved": "456 MB"
}
```

### Libraries
```bash
# Backend
npm install sharp bull @nestjs/bull ioredis

# sharp: Image processing (WebP, AVIF support)
# bull: Job queue for background processing
# ioredis: Redis client
```

### Frontend Component Structure
```
admin/tools/image-converter/
├── page.tsx                    # Main converter page
├── components/
│   ├── StatsCards.tsx          # Conversion stats display
│   ├── ScanButton.tsx          # Trigger scan action
│   ├── UnconvertedList.tsx     # List of images to convert
│   ├── ConversionProgress.tsx  # Progress bar for batch jobs
│   ├── SettingsPanel.tsx       # Auto-convert settings
│   └── RecentConversions.tsx   # Recent conversion history
```

---

## 🎯 Next Steps

1. **Set up Background Queue** - Install Bull + Redis
2. **Create Media Library** - Basic upload + auto-convert
3. **Build Scanner** - Find unconverted images
4. **Create Conversion Dashboard** - Stats + bulk actions
5. **Connect Products API** - Real data from backend

**Estimated Time:** 3-4 weeks for complete system
