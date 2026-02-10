# Backend Admin Menu Plan - Complete Feature List

## Overview
This document outlines all remaining backend menus and features needed for the Affiliate Product Showcase platform, including the new Image Converter functionality.

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
  - Images Upload with Preview
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
- [ ] Upload Media
  - Drag & drop upload
  - Multiple file upload
  - Progress indicator
  - Upload to MinIO/S3
- [ ] **Image Converter** ⭐ NEW FEATURE
  - Convert to WebP
  - Convert to AVIF
  - Batch conversion
  - Quality settings (Low/Medium/High)
  - Resize options
  - Preserve original or replace
  - Download converted images
- [ ] Media Details
  - File info (Size, Dimensions, Type)
  - URL/Path
  - Usage (Which products use this)
  - Delete with confirmation
- [ ] Bulk Operations
  - Select multiple files
  - Bulk delete
  - Bulk download

---

## 🖼️ Image Converter (Dedicated Page)
**Route:** `/admin/tools/image-converter`
**Status:** ❌ Not created

### Features:
- [ ] Upload Interface
  - Drag & drop zone
  - Multiple file support
  - Preview uploaded images
- [ ] Conversion Options
  - **Target Format:** WebP, AVIF, JPEG, PNG
  - **Quality:** Slider (1-100) or Presets (Low/Med/High)
  - **Resize:** Original, Custom dimensions, Preset sizes
  - **Preserve Aspect Ratio:** Checkbox
- [ ] Batch Processing
  - Process all uploaded images
  - Individual image conversion
  - Progress bar
- [ ] Output Settings
  - Download individually
  - Download as ZIP
  - Save to Media Library
- [ ] Comparison View
  - Before/After file size
  - Before/After preview
  - Quality comparison

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
  - Featured image
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
- [ ] Email Notifications
  - Configure triggers
  - Template management

---

## 📋 Implementation Priority

### Phase 1 (Critical - Week 1)
1. ✅ Dashboard (Basic)
2. ✅ Products (Connect API)
3. ✅ Categories (Connect API)
4. 🖼️ Media Library (Basic upload)

### Phase 2 (Important - Week 2)
5. 🖼️ **Image Converter** (WebP/AVIF)
6. 👥 Users Management
7. 📝 Blog Posts Management

### Phase 3 (Nice to have - Week 3)
8. 📊 Analytics Dashboard
9. ⚙️ Settings Pages
10. 🔗 Affiliate Links
11. 🔔 Notifications

---

## 🖼️ Image Converter Technical Specs

### Supported Formats
| Input | Output |
|-------|--------|
| JPEG | WebP, AVIF, PNG |
| PNG | WebP, AVIF, JPEG |
| WebP | AVIF, JPEG, PNG |
| AVIF | WebP, JPEG, PNG |
| GIF | WebP (animated) |

### Libraries to Use
```bash
# Backend (NestJS)
sharp - Image processing library
# Supports: WebP, AVIF, JPEG, PNG, GIF

# Installation
npm install sharp
```

### API Endpoint
```
POST /api/v1/media/convert
Body: {
  "files": ["file-id-1", "file-id-2"],
  "format": "webp" | "avif",
  "quality": 80,
  "resize": {
    "width": 1200,
    "height": null,  // null = auto
    "fit": "cover" | "contain" | "fill"
  }
}
```

### Frontend Component Structure
```
admin/tools/image-converter/
├── page.tsx                 # Main converter page
├── components/
│   ├── UploadZone.tsx       # Drag & drop upload
│   ├── ImagePreview.tsx     # Preview with comparison
│   ├── ConversionOptions.tsx # Format/quality settings
│   ├── ProgressBar.tsx      # Conversion progress
│   └── DownloadButton.tsx   # Download results
```

---

## 🎯 Next Steps

1. **Start Media Library** - Basic upload functionality
2. **Add Image Converter** - WebP/AVIF conversion tool
3. **Connect Products API** - Real data from backend
4. **Add Users Management** - Role-based access control
5. **Build Analytics** - Charts and reporting

**Estimated Time:** 2-3 weeks for complete backend admin
