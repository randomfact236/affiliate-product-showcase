# Phase 4: Advanced Analytics & Intelligence (Core Requirement)

**Objective:** Build a proprietary, enterprise-grade analytics suite to capture granular user behavior. This replaces third-party tools to own the data and bypass tracker blockers where ethical.

## 1. Data Capture (The "Tracker")
- [ ] **Custom Hook:** `useAnalytics` hook in Next.js.
- [ ] **Events to Track:**
    - `page_view` (Duration, Scroll Depth %, Entry/Exit).
    - `click_outbound` (Specifically tracking affiliate link clicks).
    - `interaction` (Hover over image, Gallery swipe, Read more).
    - `conversion_signal` (Copy coupon code, Click "Go to Store").
- [ ] **Session Fingerprinting:**
    - Generate distinct `session_id` and `visitor_id` (hashed).
    - Track Referrer, UTM Parameters, User Agent (Device Type).

## 2. Ingestion Pipeline (High Throughput)
- [ ] **Endpoint:** `POST /api/collect` (Lightweight, non-blocking).
- [ ] **Queue Strategy:**
    - API pushes raw event to **Redis List** (or Pub/Sub) immediately.
    - Return `204 No Content` instantly to frontend.
- [ ] **Worker Service:**
    - NestJS Microservice or Cron Job enabling batch processing.
    - De-duplicate and validate events.
    - Enrich data (GeoIP, User Agent parsing).

## 3. Storage & Schema
- [ ] **Postgres Partitioning:**
    - Use TimescaleDB (if available) or standard Postgres Partitioning by date for the `events` table to ensure query speed as data grows.
- [ ] **Aggregates Tables:**
    - `daily_stats` (Pre-calculated counts for faster dashboards).
    - `product_performance` (Clicks vs Views ratio).

## 4. Analytics Dashboard (Admin)
- [ ] **Overview:** Real-time active users window.
- [ ] **Reports:**
    - Top Performing Products (CTR).
    - Traffic Sources (Organic vs Direct vs Social).
    - User Journey Flow (Entry Page -> Product -> Outbound).
- [ ] **Export:** CSV export for external analysis.

## 5. Verification
- [ ] **Data Integrity:** Verify that a click on frontend results in a row in the DB.
- [ ] **Load Test:** Simulate 100 concurrent users generating events; ensure no data loss in Redis.
- [ ] **AdBlock Test:** Ensure critical core tracking works (first-party domain tracking usually bypasses basic blocking).
