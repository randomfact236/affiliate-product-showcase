# Analytics System - Tenancy Model

## Current Design: Single Website (Single-Tenant)

The 172 features are designed for **ONE website** - the Affiliate Product Showcase platform.

---

## Architecture Comparison

### Single-Tenant (Current Design)
```
┌─────────────────────────────────────────┐
│         ONE WEBSITE                     │
│  ┌─────────────────────────────────┐   │
│  │  Analytics Database             │   │
│  │  • Visitors: 10,000             │   │
│  │  • Page Views: 100,000          │   │
│  │  • Events: 50,000               │   │
│  └─────────────────────────────────┘   │
│                                         │
│  172 Features ✓                         │
└─────────────────────────────────────────┘
```

**Use Case:** Your affiliate platform only

---

### Multi-Tenant (SaaS Model)
```
┌──────────────────────────────────────────────┐
│         MULTIPLE WEBSITES (SaaS)             │
│                                              │
│  Website A        Website B        Website C │
│  (Client 1)       (Client 2)       (Client 3)│
│                                              │
│  ┌─────────┐     ┌─────────┐     ┌─────────┐│
│  │Tenant A │     │Tenant B │     │Tenant C ││
│  │Data     │     │Data     │     │Data     ││
│  │Isolated │     │Isolated │     │Isolated ││
│  └─────────┘     └─────────┘     └─────────┘│
│                                              │
│  172 Features × N Websites                   │
└──────────────────────────────────────────────┘
```

**Use Case:** Analytics as a service (like Google Analytics)

---

## Required Changes for Multi-Website Support

### Database Schema Changes

#### Add `websites` Table
```sql
CREATE TABLE websites (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  name VARCHAR(255) NOT NULL,
  domain VARCHAR(255) UNIQUE NOT NULL,
  tracking_id VARCHAR(32) UNIQUE NOT NULL, -- Public ID
  api_key VARCHAR(64) UNIQUE NOT NULL,     -- Private key
  owner_id UUID REFERENCES users(id),
  settings JSONB DEFAULT '{}',
  plan VARCHAR(20) DEFAULT 'free',         -- free, pro, enterprise
  is_active BOOLEAN DEFAULT true,
  created_at TIMESTAMP DEFAULT NOW()
);
```

#### Update All Analytics Tables
```sql
-- Add website_id to every table
ALTER TABLE analytics_visitors ADD COLUMN website_id UUID REFERENCES websites(id);
ALTER TABLE analytics_sessions ADD COLUMN website_id UUID REFERENCES websites(id);
ALTER TABLE analytics_page_views ADD COLUMN website_id UUID REFERENCES websites(id);
ALTER TABLE analytics_events ADD COLUMN website_id UUID REFERENCES websites(id);
ALTER TABLE analytics_web_vitals ADD COLUMN website_id UUID REFERENCES websites(id);

-- Update indexes
CREATE INDEX idx_visitors_website ON analytics_visitors(website_id, visitor_id);
CREATE INDEX idx_pageviews_website ON analytics_page_views(website_id, timestamp);
```

### API Changes

#### Current (Single Website)
```typescript
POST /api/analytics/pageview
Body: { page_path: "/products", ... }
```

#### Multi-Website Version
```typescript
POST /api/analytics/pageview
Headers: { "X-Tracking-ID": "abc123xyz" }
Body: { page_path: "/products", ... }

// OR using API key for private endpoints
GET /api/analytics/stats
Headers: { "X-API-Key": "secret_key_here" }
```

### Frontend Tracking Script

#### Current (Single)
```html
<script src="/analytics.js"></script>
<script>
  Analytics.init(); // Auto-configured
</script>
```

#### Multi-Website
```html
<script src="https://analytics.yourdomain.com/track.js"></script>
<script>
  Analytics.init({
    trackingId: "abc123xyz",  // Unique per website
    domain: "client-website.com"
  });
</script>
```

---

## Feature Comparison

| Feature | Single Website | Multi-Website (SaaS) |
|---------|---------------|---------------------|
| **Cost** | $50/month infra | $200+/month infra |
| **Complexity** | Simple | Complex |
| **Data Isolation** | N/A (one site) | Critical (tenant separation) |
| **User Auth** | Admin only | Multi-user per site |
| **Billing** | None | Usage-based billing |
| **API Rate Limits** | Optional | Required per tenant |
| **Data Retention** | Your choice | Configurable per plan |
| **Custom Branding** | Your branding | White-label option |
| **Feature Count** | 172 | 172 per site |

---

## Multi-Website Feature Additions

If converting to multi-tenant, add these features:

### 1. Tenant Management
| # | Feature | Description |
|---|---------|-------------|
| 173 | Website Registration | Add new website to track |
| 174 | Website Settings | Per-site configuration |
| 175 | User Invitations | Add team members per site |
| 176 | Role Management | Admin, Editor, Viewer roles per site |
| 177 | API Key Management | Rotate/revoke keys |

### 2. Billing & Plans
| # | Feature | Description |
|---|---------|-------------|
| 178 | Usage Tracking | Events per website |
| 179 | Plan Limits | Enforce free/pro limits |
| 180 | Billing Dashboard | Invoices, payments |
| 181 | Overage Alerts | Notify when exceeding limits |

### 3. Data Isolation
| # | Feature | Description |
|---|---------|-------------|
| 182 | Tenant Verification | Ensure data separation |
| 183 | Cross-Tenant Prevention | Block unauthorized access |
| 184 | Data Export per Site | Individual site export |
| 185 | Data Deletion | GDPR per-tenant deletion |

### 4. Super Admin
| # | Feature | Description |
|---|---------|-------------|
| 186 | All Sites Dashboard | Overview of all tenants |
| 187 | Tenant Health Monitor | Check status of all sites |
| 188 | Global Analytics | Aggregate across all sites |
| 189 | Platform Usage Stats | Total events, revenue, etc. |

**Total with Multi-Tenant: 189 Features**

---

## Recommendation

### For Your Current Project (Single Website)
✅ **Keep Single-Tenant Design**
- Simpler architecture
- Lower cost
- Faster development
- 172 features are sufficient

### Future: If You Want to Sell Analytics as Service
📈 **Then Add Multi-Tenant**
- Additional 2-3 weeks development
- Requires billing system
- More infrastructure
- Compliance requirements (SOC2, etc.)

---

## Decision Matrix

| Question | If YES | If NO |
|----------|--------|-------|
| Tracking only YOUR site? | Single-tenant ✓ | - |
| Selling analytics to others? | Multi-tenant | - |
| Multiple sites you own? | Single-tenant with site_id | - |
| SaaS business model? | Multi-tenant | - |

---

## Current Status: Single Website

Your 172 features are for **one website**: `affiliate-product-showcase`

All data is stored in one database, no tenant isolation needed.

**Estimated traffic:**
- Visitors: 1,000 - 10,000/month
- Page views: 10,000 - 100,000/month
- Storage: ~1-5 GB/month

This is perfect for single-tenant architecture.
