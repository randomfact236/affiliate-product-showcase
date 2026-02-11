-- Complete Analytics Tracking Migration
-- Created: 2026-02-10

-- ============================================
-- 1. AFFILIATE LINK CLICK ENHANCEMENTS
-- ============================================

ALTER TABLE "AffiliateLinkClick" 
ADD COLUMN IF NOT EXISTS "anchorText" TEXT,
ADD COLUMN IF NOT EXISTS "surroundingText" TEXT,
ADD COLUMN IF NOT EXISTS "viewportSection" VARCHAR(20),
ADD COLUMN IF NOT EXISTS "scrollDepthAtClick" INTEGER DEFAULT 0,
ADD COLUMN IF NOT EXISTS "timeOnPageBeforeClick" INTEGER DEFAULT 0,
ADD COLUMN IF NOT EXISTS "competingLinksCount" INTEGER DEFAULT 0,
ADD COLUMN IF NOT EXISTS "priceAtClick" INTEGER,
ADD COLUMN IF NOT EXISTS "stockStatus" VARCHAR(20),
ADD COLUMN IF NOT EXISTS "positionX" INTEGER,
ADD COLUMN IF NOT EXISTS "positionY" INTEGER,
ADD COLUMN IF NOT EXISTS "emailCampaignId" VARCHAR(100),
ADD COLUMN IF NOT EXISTS "emailSentAt" TIMESTAMP,
ADD COLUMN IF NOT EXISTS "emailOpened" BOOLEAN DEFAULT FALSE,
ADD COLUMN IF NOT EXISTS "socialSource" VARCHAR(50),
ADD COLUMN IF NOT EXISTS "sharedByUserId" VARCHAR(100);

-- ============================================
-- 2. ANALYTICS EVENT ENHANCEMENTS
-- ============================================

ALTER TABLE "AnalyticsEvent"
ADD COLUMN IF NOT EXISTS "scrollDepth" INTEGER DEFAULT 0,
ADD COLUMN IF NOT EXISTS "readTime" INTEGER DEFAULT 0,
ADD COLUMN IF NOT EXISTS "paragraphsRead" INTEGER DEFAULT 0,
ADD COLUMN IF NOT EXISTS "videoWatchTime" INTEGER DEFAULT 0,
ADD COLUMN IF NOT EXISTS "searchQuery" TEXT,
ADD COLUMN IF NOT EXISTS "searchEngine" VARCHAR(50),
ADD COLUMN IF NOT EXISTS "keywordsOnPage" TEXT[],
ADD COLUMN IF NOT EXISTS "emailCampaignId" VARCHAR(100),
ADD COLUMN IF NOT EXISTS "emailOpenTime" TIMESTAMP,
ADD COLUMN IF NOT EXISTS "notificationId" VARCHAR(100),
ADD COLUMN IF NOT EXISTS "experimentId" VARCHAR(100),
ADD COLUMN IF NOT EXISTS "experimentVariant" VARCHAR(10);

-- ============================================
-- 3. ANALYTICS SESSION ENHANCEMENTS
-- ============================================

ALTER TABLE "AnalyticsSession"
ADD COLUMN IF NOT EXISTS "consentAnalytics" BOOLEAN DEFAULT FALSE,
ADD COLUMN IF NOT EXISTS "consentMarketing" BOOLEAN DEFAULT FALSE,
ADD COLUMN IF NOT EXISTS "consentVersion" VARCHAR(20),
ADD COLUMN IF NOT EXISTS "maxScrollDepth" INTEGER DEFAULT 0,
ADD COLUMN IF NOT EXISTS "totalReadTime" INTEGER DEFAULT 0;

-- ============================================
-- 4. NEW TABLE: Mouse Heatmap Data
-- ============================================

CREATE TABLE IF NOT EXISTS "MouseHeatmap" (
    id              SERIAL PRIMARY KEY,
    page_url        VARCHAR(500) NOT NULL,
    session_id      VARCHAR(100),
    x_coordinate    INTEGER NOT NULL,
    y_coordinate    INTEGER NOT NULL,
    event_type      VARCHAR(20) NOT NULL,
    element_tag     VARCHAR(50),
    element_class   VARCHAR(200),
    element_id      VARCHAR(100),
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS "idx_mouse_page" ON "MouseHeatmap"("page_url");
CREATE INDEX IF NOT EXISTS "idx_mouse_session" ON "MouseHeatmap"("session_id");

-- ============================================
-- 5. NEW TABLE: Content Engagement
-- ============================================

CREATE TABLE IF NOT EXISTS "ContentEngagement" (
    id                  SERIAL PRIMARY KEY,
    session_id          VARCHAR(100) NOT NULL,
    page_url            VARCHAR(500) NOT NULL,
    content_type        VARCHAR(50),
    max_scroll_depth    INTEGER DEFAULT 0,
    read_time_seconds   INTEGER DEFAULT 0,
    paragraphs_read     INTEGER DEFAULT 0,
    words_read          INTEGER DEFAULT 0,
    video_watch_time    INTEGER DEFAULT 0,
    video_progress_pct  INTEGER DEFAULT 0,
    audio_listen_time   INTEGER DEFAULT 0,
    highlighted_text    BOOLEAN DEFAULT FALSE,
    text_copied         BOOLEAN DEFAULT FALSE,
    printed             BOOLEAN DEFAULT FALSE,
    shared              BOOLEAN DEFAULT FALSE,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS "idx_engagement_session" ON "ContentEngagement"("session_id");
CREATE INDEX IF NOT EXISTS "idx_engagement_page" ON "ContentEngagement"("page_url");

-- ============================================
-- 6. NEW TABLE: Social Sharing
-- ============================================

CREATE TABLE IF NOT EXISTS "SocialShare" (
    id              SERIAL PRIMARY KEY,
    page_url        VARCHAR(500) NOT NULL,
    product_id      VARCHAR(100),
    session_id      VARCHAR(100),
    platform        VARCHAR(50) NOT NULL,
    share_type      VARCHAR(20),
    clicked_back    BOOLEAN DEFAULT FALSE,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS "idx_share_page" ON "SocialShare"("page_url");
CREATE INDEX IF NOT EXISTS "idx_share_product" ON "SocialShare"("product_id");

-- ============================================
-- 7. NEW TABLE: Form Interactions
-- ============================================

CREATE TABLE IF NOT EXISTS "FormInteraction" (
    id                  SERIAL PRIMARY KEY,
    session_id          VARCHAR(100) NOT NULL,
    page_url            VARCHAR(500) NOT NULL,
    form_id             VARCHAR(100),
    form_name           VARCHAR(200),
    fields_focused      TEXT[],
    fields_blurred      TEXT[],
    field_errors        JSONB,
    time_to_start       INTEGER,
    time_to_complete    INTEGER,
    submitted           BOOLEAN DEFAULT FALSE,
    abandonment_step    VARCHAR(100),
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS "idx_form_session" ON "FormInteraction"("session_id");

-- ============================================
-- 8. NEW TABLE: Price History
-- ============================================

CREATE TABLE IF NOT EXISTS "PriceHistory" (
    id              SERIAL PRIMARY KEY,
    product_id      VARCHAR(100) NOT NULL,
    link_id         VARCHAR(100) NOT NULL,
    platform        VARCHAR(50) NOT NULL,
    price           INTEGER NOT NULL,
    original_price  INTEGER,
    stock_status    VARCHAR(20),
    checked_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS "idx_price_product" ON "PriceHistory"("product_id");
CREATE INDEX IF NOT EXISTS "idx_price_link" ON "PriceHistory"("link_id");

-- ============================================
-- 9. NEW TABLE: Search Query Analytics
-- ============================================

CREATE TABLE IF NOT EXISTS "SearchQuery" (
    id                  SERIAL PRIMARY KEY,
    query               TEXT NOT NULL,
    normalized_query    TEXT NOT NULL,
    search_engine       VARCHAR(50),
    search_intent       VARCHAR(50),
    landing_page        VARCHAR(500),
    page_category       VARCHAR(100),
    clicked_result      BOOLEAN DEFAULT FALSE,
    search_count        INTEGER DEFAULT 1,
    unique_visitors     INTEGER DEFAULT 1,
    first_seen          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_seen           TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS "idx_search_query" ON "SearchQuery"("normalized_query");
CREATE INDEX IF NOT EXISTS "idx_search_engine" ON "SearchQuery"("search_engine");

-- ============================================
-- 10. INDEXES
-- ============================================

CREATE INDEX IF NOT EXISTS "idx_click_anchor" ON "AffiliateLinkClick"("anchorText");
CREATE INDEX IF NOT EXISTS "idx_click_price" ON "AffiliateLinkClick"("priceAtClick");
CREATE INDEX IF NOT EXISTS "idx_event_scroll" ON "AnalyticsEvent"("scrollDepth");
CREATE INDEX IF NOT EXISTS "idx_event_search" ON "AnalyticsEvent"("searchQuery");

COMMIT;
