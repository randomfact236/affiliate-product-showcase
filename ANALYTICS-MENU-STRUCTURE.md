# Suggested Analytics Menu Structure

## Current Structure (8 Flat Tabs)
```
[Overview] [Revenue] [Links] [Traffic] [Audience] [Content] [Devices] [SEO]
```

## Recommended Structure (Hierarchical)

```
📊 DASHBOARD
   ├── 📈 Overview (with Real-time Widget)
   ├── ⚡ Real-time
   └── 🎯 Goals

💰 MONETIZATION
   ├── 💵 Revenue
   │   ├── Overview
   │   ├── By Product
   │   ├── By Category
   │   └── Trends
   ├── 💸 Commissions
   ├── 📊 EPC Analysis
   └── 💳 Payouts

🔗 PERFORMANCE
   ├── 🔗 Link Performance
   ├── 📍 Placement Analysis
   ├── 🖱️ Click Heatmaps
   ├── 🧪 A/B Tests
   └── ⚡ Page Speed

👥 AUDIENCE
   ├── 👤 Demographics
   │   ├── Age & Gender
   │   ├── Location
   │   └── Languages
   ├── 🔄 Behavior
   │   ├── New vs Returning
   │   ├── Frequency
   │   └── Engagement
   ├── 📱 Technology
   │   ├── Devices
   │   ├── Browsers
   │   └── OS
   └── 🛤️ User Journey

🌐 ACQUISITION
   ├── 🚦 Traffic Sources
   │   ├── Overview
   │   ├── Organic Search
   │   ├── Social Media
   │   ├── Email
   │   └── Referrals
   ├── 🎯 Campaigns
   │   ├── UTM Tracking
   │   ├── ROI Analysis
   │   └── Attribution
   ├── 🔍 Search Console
   │   ├── Keywords
   │   ├── Rankings
   │   └── CTR
   └── 📧 Email Analytics

📄 CONTENT
   ├── 📝 Top Pages
   ├── 📊 Engagement
   │   ├── Scroll Depth
   │   ├── Time on Page
   │   └── Heatmaps
   ├── 🚪 Entry/Exit Pages
   └── 🏷️ Categories & Tags

🎯 CONVERSIONS
   ├── 🔄 Funnel Analysis
   ├── 🎁 Attribution Models
   │   ├── First Click
   │   ├── Last Click
   │   ├── Linear
   │   └── Time Decay
   ├── 💰 Conversion Paths
   └── 🔔 Goals & Events

⚙️ SETTINGS
   ├── 🔔 Alerts
   ├── 📤 Scheduled Reports
   ├── 📊 Custom Dashboards
   └── 🔗 Integrations
```

## Implementation Priority

### Phase 1 (Immediate)
```
✅ Overview (Enhanced with Real-time)
✅ Revenue
✅ Links
✅ Traffic
✅ Audience
✅ Content
✅ Devices
✅ SEO
```

### Phase 2 (Next Sprint)
```
🔄 Move "Devices" under "Audience > Technology"
🔄 Create "Acquisition" parent menu
🔄 Add "Conversions" section
🔄 Add "Settings" for alerts & reports
```

### Phase 3 (Future)
```
🔄 Add sub-pages for each section
🔄 Implement drill-down navigation
🔄 Add breadcrumbs
🔄 Create saved views
```

## Component Placement

### Overview Tab
```
┌──────────────────────────────────────────────────────────────┐
│ Search & Filter Bar                                          │
├──────────────────────────────────────────────────────────────┤
│ ┌───────────────────┐ ┌─────────┐ ┌─────────┐ ┌──────────┐  │
│ │ Real-time Widget  │ │ Revenue │ │ Clicks  │ │ Conv.    │  │
│ │                   │ │ Card    │ │ Card    │ │ Card     │  │
│ └───────────────────┘ └─────────┘ └─────────┘ └──────────┘  │
├──────────────────────────────────────────────────────────────┤
│ ┌─────────────┐ ┌─────────────┐ ┌────────────────────────┐  │
│ │ Gender      │ │ New vs      │ │ Device Breakdown       │  │
│ │ Split       │ │ Returning   │ │ Pie Chart              │  │
│ └─────────────┘ └─────────────┘ └────────────────────────┘  │
├──────────────────────────────────────────────────────────────┤
│ ┌────────────────────────┐ ┌─────────────────────────────┐  │
│ │ Age Distribution       │ │ Top Interests               │  │
│ └────────────────────────┘ └─────────────────────────────┘  │
└──────────────────────────────────────────────────────────────┘
```

### Revenue Tab
```
┌──────────────────────────────────────────────────────────────┐
│ Export [▼]                                      [Date Range] │
├──────────────────────────────────────────────────────────────┤
│ ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐             │
│ │ Total   │ │ EPC     │ │ AOV     │ │ Conv.   │             │
│ │ Revenue │ │         │ │         │ │ Rate    │             │
│ └─────────┘ └─────────┘ └─────────┘ └─────────┘             │
├──────────────────────────────────────────────────────────────┤
│ Revenue Trend Chart                                          │
├──────────────────────────────────────────────────────────────┤
│ Commission by Category │ Daily Revenue                       │
└──────────────────────────────────────────────────────────────┘
```

## Navigation Components Needed

### 1. Sidebar Navigation
```tsx
<AnalyticsSidebar
  items={[
    { 
      id: 'dashboard', 
      label: 'Dashboard', 
      icon: 'LayoutDashboard',
      children: [
        { id: 'overview', label: 'Overview' },
        { id: 'realtime', label: 'Real-time' },
      ]
    },
    { 
      id: 'monetization', 
      label: 'Monetization', 
      icon: 'DollarSign',
      children: [
        { id: 'revenue', label: 'Revenue' },
        { id: 'commissions', label: 'Commissions' },
      ]
    },
  ]}
/>
```

### 2. Breadcrumb Navigation
```tsx
<AnalyticsBreadcrumb
  items={[
    { label: 'Analytics', href: '/admin/analytics' },
    { label: 'Audience', href: '/admin/analytics/audience' },
    { label: 'Demographics', active: true }
  ]}
/>
```

### 3. Quick Navigation
```tsx
<QuickNav
  items={[
    { icon: 'Zap', label: 'Real-time', shortcut: '⌘1' },
    { icon: 'Bell', label: 'Alerts', badge: 3 },
    { icon: 'Download', label: 'Exports' },
    { icon: 'Settings', label: 'Settings' },
  ]}
/>
```

## URL Structure

```
/admin/analytics
  /overview
  /realtime
  /revenue
    /products
    /categories
    /trends
  /links
    /performance
    /placements
    /heatmaps
  /traffic
    /sources
    /campaigns
    /search
  /audience
    /demographics
    /behavior
    /technology
    /journey
  /content
    /pages
    /engagement
    /categories
  /conversions
    /funnel
    /attribution
    /goals
  /settings
    /alerts
    /reports
    /dashboards
```

## Mobile Navigation

```
┌─────────────────────────────────┐
│ ☰ Analytics          [🔍] [👤] │
├─────────────────────────────────┤
│ 📈 Overview                     │
│ ⚡ Real-time                     │
│ 💰 Monetization              >  │
│ 🔗 Performance               >  │
│ 👥 Audience                  >  │
│ 🌐 Acquisition               >  │
│ 📄 Content                   >  │
│ 🎯 Conversions               >  │
├─────────────────────────────────┤
│ ⚙️ Settings                     │
└─────────────────────────────────┘
```

## Recommended Next Steps

1. **Implement Sidebar Navigation**
   - Collapsible menu structure
   - Active state highlighting
   - Badge notifications for alerts

2. **Add Breadcrumbs**
   - Show current location
   - Enable quick navigation up

3. **Create Drill-down Pages**
   - Revenue → By Product
   - Audience → By Location
   - Links → By Placement

4. **Add Quick Actions**
   - Floating action button
   - Keyboard shortcuts
   - Context menus
