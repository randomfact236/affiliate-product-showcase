-- Analytics Visual Features Migration
-- Created: 2026-02-10

-- Add visitor demographics to AnalyticsSession
ALTER TABLE "AnalyticsSession" 
ADD COLUMN IF NOT EXISTS "gender" VARCHAR(10),
ADD COLUMN IF NOT EXISTS "ageGroup" VARCHAR(20),
ADD COLUMN IF NOT EXISTS "interests" TEXT[],
ADD COLUMN IF NOT EXISTS "language" VARCHAR(10),
ADD COLUMN IF NOT EXISTS "isNewVisitor" BOOLEAN DEFAULT true,
ADD COLUMN IF NOT EXISTS "placementType" VARCHAR(50);

-- Add device breakdown fields
ALTER TABLE "AnalyticsSession"
ADD COLUMN IF NOT EXISTS "deviceType" VARCHAR(20) DEFAULT 'desktop',
ADD COLUMN IF NOT EXISTS "browser" VARCHAR(50),
ADD COLUMN IF NOT EXISTS "os" VARCHAR(50),
ADD COLUMN IF NOT EXISTS "screenResolution" VARCHAR(20);

-- Add traffic source details
ALTER TABLE "AnalyticsSession"
ADD COLUMN IF NOT EXISTS "socialSource" VARCHAR(50),
ADD COLUMN IF NOT EXISTS "countryCode" VARCHAR(5),
ADD COLUMN IF NOT EXISTS "city" VARCHAR(100);

-- Add content tracking to AnalyticsEvent
ALTER TABLE "AnalyticsEvent"
ADD COLUMN IF NOT EXISTS "categoryName" VARCHAR(100),
ADD COLUMN IF NOT EXISTS "tagNames" TEXT[],
ADD COLUMN IF NOT EXISTS "ribbonType" VARCHAR(50),
ADD COLUMN IF NOT EXISTS "contentType" VARCHAR(50);

-- Add page performance tracking
ALTER TABLE "AnalyticsEvent"
ADD COLUMN IF NOT EXISTS "loadTime" INTEGER,
ADD COLUMN IF NOT EXISTS "isEntryPage" BOOLEAN DEFAULT false,
ADD COLUMN IF NOT EXISTS "isExitPage" BOOLEAN DEFAULT false,
ADD COLUMN IF NOT EXISTS "timeOnPage" INTEGER;

-- Create indexes for performance
CREATE INDEX IF NOT EXISTS "idx_session_gender" ON "AnalyticsSession"("gender");
CREATE INDEX IF NOT EXISTS "idx_session_age" ON "AnalyticsSession"("ageGroup");
CREATE INDEX IF NOT EXISTS "idx_session_device" ON "AnalyticsSession"("deviceType");
CREATE INDEX IF NOT EXISTS "idx_session_country" ON "AnalyticsSession"("countryCode");
CREATE INDEX IF NOT EXISTS "idx_session_social" ON "AnalyticsSession"("socialSource");
CREATE INDEX IF NOT EXISTS "idx_session_new_visitor" ON "AnalyticsSession"("isNewVisitor");
CREATE INDEX IF NOT EXISTS "idx_event_category" ON "AnalyticsEvent"("categoryName");
CREATE INDEX IF NOT EXISTS "idx_event_entry" ON "AnalyticsEvent"("isEntryPage");
CREATE INDEX IF NOT EXISTS "idx_event_exit" ON "AnalyticsEvent"("isExitPage");
