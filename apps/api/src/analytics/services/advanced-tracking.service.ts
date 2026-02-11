import { Injectable } from '@nestjs/common';
import { PrismaService } from '../../prisma/prisma.service';

@Injectable()
export class AdvancedTrackingService {
  constructor(private prisma: PrismaService) {}

  // ==========================================
  // HEATMAP TRACKING
  // ==========================================
  async trackHeatmap(data: {
    sessionId: string;
    pageUrl: string;
    points: Array<{
      x: number;
      y: number;
      type: string;
      elementTag?: string;
      elementClass?: string;
      elementId?: string;
    }>;
  }) {
    const records = data.points.map(point => ({
      page_url: data.pageUrl,
      session_id: data.sessionId,
      x_coordinate: point.x,
      y_coordinate: point.y,
      event_type: point.type,
      element_tag: point.elementTag,
      element_class: point.elementClass,
      element_id: point.elementId,
    }));

    // Use raw query for bulk insert
    await this.prisma.$executeRaw`
      INSERT INTO "MouseHeatmap" (
        page_url, session_id, x_coordinate, y_coordinate, 
        event_type, element_tag, element_class, element_id
      )
      ${records.map(r => `
        (${r.page_url}, ${r.session_id}, ${r.x_coordinate}, 
         ${r.y_coordinate}, ${r.event_type}, ${r.element_tag}, 
         ${r.element_class}, ${r.element_id})
      `).join(',')}
    `;

    return { tracked: records.length };
  }

  async getHeatmapData(pageUrl: string, days: number = 30) {
    const since = new Date(Date.now() - days * 24 * 60 * 60 * 1000);
    
    return this.prisma.$queryRaw`
      SELECT 
        x_coordinate as x,
        y_coordinate as y,
        event_type as type,
        COUNT(*) as count
      FROM "MouseHeatmap"
      WHERE page_url = ${pageUrl}
        AND created_at > ${since}
      GROUP BY x_coordinate, y_coordinate, event_type
      ORDER BY count DESC
      LIMIT 1000
    `;
  }

  // ==========================================
  // CONTENT ENGAGEMENT
  // ==========================================
  async trackEngagement(data: {
    sessionId: string;
    pageUrl: string;
    maxScrollDepth: number;
    readTime: number;
    paragraphsRead: number;
    wordsRead: number;
    videoWatchTime?: number;
    audioListenTime?: number;
    highlightedText: boolean;
    textCopied: boolean;
    shared: boolean;
  }) {
    return this.prisma.$executeRaw`
      INSERT INTO "ContentEngagement" (
        session_id, page_url, max_scroll_depth, read_time_seconds,
        paragraphs_read, words_read, video_watch_time, audio_listen_time,
        highlighted_text, text_copied, shared, updated_at
      ) VALUES (
        ${data.sessionId}, ${data.pageUrl}, ${data.maxScrollDepth},
        ${data.readTime}, ${data.paragraphsRead}, ${data.wordsRead},
        ${data.videoWatchTime || 0}, ${data.audioListenTime || 0},
        ${data.highlightedText}, ${data.textCopied}, ${data.shared},
        NOW()
      )
      ON CONFLICT (session_id, page_url) DO UPDATE SET
        max_scroll_depth = GREATEST("ContentEngagement".max_scroll_depth, ${data.maxScrollDepth}),
        read_time_seconds = "ContentEngagement".read_time_seconds + ${data.readTime},
        paragraphs_read = GREATEST("ContentEngagement".paragraphs_read, ${data.paragraphsRead}),
        words_read = GREATEST("ContentEngagement".words_read, ${data.wordsRead}),
        video_watch_time = "ContentEngagement".video_watch_time + ${data.videoWatchTime || 0},
        audio_listen_time = "ContentEngagement".audio_listen_time + ${data.audioListenTime || 0},
        highlighted_text = "ContentEngagement".highlighted_text OR ${data.highlightedText},
        text_copied = "ContentEngagement".text_copied OR ${data.textCopied},
        shared = "ContentEngagement".shared OR ${data.shared},
        updated_at = NOW()
    `;
  }

  async getEngagementStats(pageUrl?: string, days: number = 30) {
    const since = new Date(Date.now() - days * 24 * 60 * 60 * 1000);
    
    const whereClause = pageUrl 
      ? `WHERE page_url = '${pageUrl}' AND created_at > '${since.toISOString()}'`
      : `WHERE created_at > '${since.toISOString()}'`;

    return this.prisma.$queryRaw`
      SELECT 
        AVG(max_scroll_depth) as avg_scroll_depth,
        AVG(read_time_seconds) as avg_read_time,
        AVG(paragraphs_read) as avg_paragraphs,
        SUM(CASE WHEN highlighted_text THEN 1 ELSE 0 END)::float / COUNT(*) * 100 as highlight_rate,
        SUM(CASE WHEN text_copied THEN 1 ELSE 0 END)::float / COUNT(*) * 100 as copy_rate,
        SUM(CASE WHEN shared THEN 1 ELSE 0 END)::float / COUNT(*) * 100 as share_rate,
        COUNT(*) as total_sessions
      FROM "ContentEngagement"
      ${this.prisma.$queryRawUnsafe(whereClause)}
    `;
  }

  // ==========================================
  // SOCIAL SHARING
  // ==========================================
  async trackSocialShare(data: {
    pageUrl: string;
    productId?: string;
    sessionId: string;
    platform: string;
    shareType: string;
  }) {
    return this.prisma.$executeRaw`
      INSERT INTO "SocialShare" (
        page_url, product_id, session_id, platform, share_type
      ) VALUES (
        ${data.pageUrl}, ${data.productId || null}, ${data.sessionId},
        ${data.platform}, ${data.shareType}
      )
    `;
  }

  async getSocialShareStats(days: number = 30) {
    const since = new Date(Date.now() - days * 24 * 60 * 60 * 1000);
    
    return this.prisma.$queryRaw`
      SELECT 
        platform,
        share_type,
        COUNT(*) as share_count,
        SUM(CASE WHEN clicked_back THEN 1 ELSE 0 END) as return_count
      FROM "SocialShare"
      WHERE created_at > ${since}
      GROUP BY platform, share_type
      ORDER BY share_count DESC
    `;
  }

  // ==========================================
  // FORM ANALYTICS
  // ==========================================
  async trackFormInteraction(data: {
    sessionId: string;
    pageUrl: string;
    formId?: string;
    formName?: string;
    fieldName?: string;
    fieldType?: string;
    event: string;
    errorMessage?: string;
    timeToStart?: number;
    timeToComplete?: number;
    abandonmentStep?: string;
  }) {
    // For simple events, insert directly
    if (['focus', 'blur', 'error'].includes(data.event)) {
      return this.prisma.$executeRaw`
        INSERT INTO "FormInteraction" (
          session_id, page_url, form_id, form_name,
          fields_focused, fields_blurred, field_errors,
          time_to_start, submitted, abandonment_step
        ) VALUES (
          ${data.sessionId}, ${data.pageUrl}, ${data.formId || null}, ${data.formName || null},
          ${data.event === 'focus' ? [data.fieldName] : []},
          ${data.event === 'blur' ? [data.fieldName] : []},
          ${data.errorMessage ? JSON.stringify({ [data.fieldName]: data.errorMessage }) : {}},
          ${data.timeToStart || 0}, ${false}, ${data.abandonmentStep || null}
        )
        ON CONFLICT (session_id, page_url, form_id) DO UPDATE SET
          fields_focused = CASE 
            WHEN ${data.event === 'focus'} 
            THEN array_append(COALESCE("FormInteraction".fields_focused, ARRAY[]::text[]), ${data.fieldName})
            ELSE "FormInteraction".fields_focused
          END,
          fields_blurred = CASE 
            WHEN ${data.event === 'blur'} 
            THEN array_append(COALESCE("FormInteraction".fields_blurred, ARRAY[]::text[]), ${data.fieldName})
            ELSE "FormInteraction".fields_blurred
          END,
          field_errors = CASE 
            WHEN ${data.errorMessage !== undefined}
            THEN jsonb_set(
              COALESCE("FormInteraction".field_errors, '{}'::jsonb),
              ARRAY[${data.fieldName}],
              to_jsonb(${data.errorMessage})
            )
            ELSE "FormInteraction".field_errors
          END,
          time_to_start = COALESCE("FormInteraction".time_to_start, ${data.timeToStart}),
          abandonment_step = COALESCE(${data.abandonmentStep}, "FormInteraction".abandonment_step)
      `;
    }

    // For submit/abandon, update the record
    if (data.event === 'submit' || data.event === 'abandon') {
      return this.prisma.$executeRaw`
        UPDATE "FormInteraction"
        SET 
          submitted = ${data.event === 'submit'},
          time_to_complete = ${data.timeToComplete || 0},
          abandonment_step = ${data.abandonmentStep || null}
        WHERE session_id = ${data.sessionId}
          AND page_url = ${data.pageUrl}
          AND form_id = ${data.formId || ''}
      `;
    }
  }

  async getFormAnalytics(formId?: string, days: number = 30) {
    const since = new Date(Date.now() - days * 24 * 60 * 60 * 1000);
    
    const whereClause = formId 
      ? `WHERE form_id = '${formId}' AND created_at > '${since.toISOString()}'`
      : `WHERE created_at > '${since.toISOString()}'`;

    return this.prisma.$queryRaw`
      SELECT 
        form_id,
        form_name,
        COUNT(*) as total_interactions,
        SUM(CASE WHEN submitted THEN 1 ELSE 0 END)::float / COUNT(*) * 100 as completion_rate,
        AVG(time_to_start) as avg_time_to_start,
        AVG(time_to_complete) as avg_completion_time,
        MODE() WITHIN GROUP (ORDER BY abandonment_step) as top_abandon_field
      FROM "FormInteraction"
      ${this.prisma.$queryRawUnsafe(whereClause)}
      GROUP BY form_id, form_name
    `;
  }

  // ==========================================
  // PRICE TRACKING
  // ==========================================
  async recordPrice(data: {
    productId: string;
    linkId: string;
    platform: string;
    price: number;
    originalPrice?: number;
    stockStatus?: string;
  }) {
    return this.prisma.$executeRaw`
      INSERT INTO "PriceHistory" (
        product_id, link_id, platform, price, original_price, stock_status
      ) VALUES (
        ${data.productId}, ${data.linkId}, ${data.platform},
        ${data.price}, ${data.originalPrice || null}, ${data.stockStatus || null}
      )
    `;
  }

  async getPriceHistory(productId: string, days: number = 30) {
    const since = new Date(Date.now() - days * 24 * 60 * 60 * 1000);
    
    return this.prisma.$queryRaw`
      SELECT 
        platform,
        price,
        original_price,
        stock_status,
        checked_at
      FROM "PriceHistory"
      WHERE product_id = ${productId}
        AND checked_at > ${since}
      ORDER BY checked_at DESC
    `;
  }

  async getLatestPrices(productIds: string[]) {
    return this.prisma.$queryRaw`
      SELECT DISTINCT ON (product_id, platform)
        product_id,
        platform,
        price,
        stock_status,
        checked_at
      FROM "PriceHistory"
      WHERE product_id IN (${productIds.join(',')})
      ORDER BY product_id, platform, checked_at DESC
    `;
  }

  // ==========================================
  // SEARCH QUERY TRACKING
  // ==========================================
  async trackSearchQuery(data: {
    query: string;
    searchEngine: string;
    searchIntent: string;
    landingPage: string;
    pageCategory?: string;
  }) {
    const normalized = data.query.toLowerCase().trim();
    
    return this.prisma.$executeRaw`
      INSERT INTO "SearchQuery" (
        query, normalized_query, search_engine, search_intent,
        landing_page, page_category, search_count, unique_visitors, last_seen
      ) VALUES (
        ${data.query}, ${normalized}, ${data.searchEngine}, ${data.searchIntent},
        ${data.landingPage}, ${data.pageCategory || null}, 1, 1, NOW()
      )
      ON CONFLICT (normalized_query, landing_page) DO UPDATE SET
        search_count = "SearchQuery".search_count + 1,
        unique_visitors = "SearchQuery".unique_visitors + 1,
        last_seen = NOW()
    `;
  }

  async getTopSearchQueries(limit: number = 50, days: number = 30) {
    const since = new Date(Date.now() - days * 24 * 60 * 60 * 1000);
    
    return this.prisma.$queryRaw`
      SELECT 
        query,
        normalized_query,
        search_engine,
        search_intent,
        SUM(search_count) as total_searches,
        SUM(unique_visitors) as total_visitors,
        MAX(last_seen) as last_seen
      FROM "SearchQuery"
      WHERE last_seen > ${since}
      GROUP BY query, normalized_query, search_engine, search_intent
      ORDER BY total_searches DESC
      LIMIT ${limit}
    `;
  }

  async getSearchIntentDistribution(days: number = 30) {
    const since = new Date(Date.now() - days * 24 * 60 * 60 * 1000);
    
    return this.prisma.$queryRaw`
      SELECT 
        search_intent,
        SUM(search_count) as count,
        SUM(search_count)::float / (SELECT SUM(search_count) FROM "SearchQuery" WHERE last_seen > ${since}) * 100 as percentage
      FROM "SearchQuery"
      WHERE last_seen > ${since}
      GROUP BY search_intent
    `;
  }

  // ==========================================
  // ENHANCED CLICK TRACKING
  // ==========================================
  async trackEnhancedClick(data: {
    linkId: string;
    sessionId: string;
    pageUrl: string;
    productId?: string;
    anchorText?: string;
    surroundingText?: string;
    viewportSection?: string;
    scrollDepthAtClick?: number;
    timeOnPageBeforeClick?: number;
    competingLinksCount?: number;
    priceAtClick?: number;
    stockStatus?: string;
    positionX?: number;
    positionY?: number;
    emailCampaignId?: string;
    socialSource?: string;
  }) {
    return this.prisma.affiliateLinkClick.create({
      data: {
        linkId: data.linkId,
        sessionId: data.sessionId,
        pageUrl: data.pageUrl,
        productId: data.productId,
        anchorText: data.anchorText,
        surroundingText: data.surroundingText,
        viewportSection: data.viewportSection,
        scrollDepthAtClick: data.scrollDepthAtClick,
        timeOnPageBeforeClick: data.timeOnPageBeforeClick,
        competingLinksCount: data.competingLinksCount,
        priceAtClick: data.priceAtClick,
        stockStatus: data.stockStatus,
        positionX: data.positionX,
        positionY: data.positionY,
        emailCampaignId: data.emailCampaignId,
        socialSource: data.socialSource,
        deviceType: 'desktop', // Will be updated by middleware
        browser: 'unknown',
        hourOfDay: new Date().getHours(),
        dayOfWeek: new Date().getDay(),
        clickPosition: data.viewportSection || 'unknown',
        clickType: 'link',
      },
    });
  }
}
