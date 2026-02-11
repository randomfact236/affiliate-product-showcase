import { Injectable, Inject } from "@nestjs/common";
import { PrismaService } from "../prisma/prisma.service";
import { AnalyticsType, MetricType, Prisma } from "@prisma/client";
import { TrackEventDto, QueryAnalyticsDto, QueryMetricsDto, QueryDashboardDto } from "./dto";
import { Redis } from "ioredis";
import { REDIS_CLIENT } from "../common/constants/injection-tokens";
import { UAParser } from "ua-parser-js";

const CACHE_PREFIX = "analytics:";
const CACHE_TTL = 300; // 5 minutes

@Injectable()
export class AnalyticsService {
  constructor(
    private prisma: PrismaService,
    @Inject(REDIS_CLIENT) private readonly redis: Redis,
  ) {}

  // ==================== EVENT TRACKING ====================

  async trackEvent(dto: TrackEventDto, requestMetadata: {
    ipAddress?: string;
    userAgent?: string;
  }) {
    // Parse user agent
    const uaInfo = this.parseUserAgent(requestMetadata.userAgent);
    
    // Get geo info (simplified - in production use geoip library)
    const geoInfo = await this.getGeoInfo(requestMetadata.ipAddress);

    const event = await this.prisma.analyticsEvent.create({
      data: {
        type: dto.type,
        event: dto.event,
        productId: dto.productId,
        categoryId: dto.categoryId,
        linkId: dto.linkId,
        sessionId: dto.sessionId,
        url: dto.url,
        referrer: dto.referrer,
        ipAddress: requestMetadata.ipAddress,
        userAgent: requestMetadata.userAgent,
        deviceType: uaInfo.deviceType,
        browser: uaInfo.browser,
        os: uaInfo.os,
        country: geoInfo.country,
        city: geoInfo.city,
        metadata: dto.metadata as Prisma.InputJsonValue,
      },
    });

    // Update session if exists
    if (dto.sessionId) {
      await this.updateSession(dto.sessionId, dto.url);
    }

    // Update product view count for PRODUCT_VIEW events
    if (dto.type === AnalyticsType.PRODUCT_VIEW && dto.productId) {
      await this.prisma.product.update({
        where: { id: dto.productId },
        data: { viewCount: { increment: 1 } },
      });
    }

    // Invalidate relevant caches
    await this.invalidateCache();

    return event;
  }

  async trackBatch(events: TrackEventDto[], requestMetadata: {
    ipAddress?: string;
    userAgent?: string;
  }) {
    const results = [];
    for (const event of events) {
      results.push(await this.trackEvent(event, requestMetadata));
    }
    return results;
  }

  // ==================== SESSION MANAGEMENT ====================

  async createSession(sessionData: {
    sessionId: string;
    userId?: string;
    ipAddress?: string;
    userAgent?: string;
    landingPage?: string;
    referrer?: string;
    source?: string;
    medium?: string;
    campaign?: string;
  }) {
    const uaInfo = this.parseUserAgent(sessionData.userAgent);
    const geoInfo = await this.getGeoInfo(sessionData.ipAddress);

    return this.prisma.analyticsSession.create({
      data: {
        ...sessionData,
        deviceType: uaInfo.deviceType,
        browser: uaInfo.browser,
        os: uaInfo.os,
        country: geoInfo.country,
      },
    });
  }

  async updateSession(sessionId: string, url?: string) {
    return this.prisma.analyticsSession.update({
      where: { sessionId },
      data: {
        lastActivity: new Date(),
        pageViews: { increment: 1 },
        events: { increment: 1 },
      },
    });
  }

  async endSession(sessionId: string) {
    return this.prisma.analyticsSession.update({
      where: { sessionId },
      data: { endedAt: new Date() },
    });
  }

  // ==================== QUERY METHODS ====================

  async findEvents(query: QueryAnalyticsDto) {
    const where: Prisma.AnalyticsEventWhereInput = {};

    if (query.startDate || query.endDate) {
      where.createdAt = {};
      if (query.startDate) where.createdAt.gte = new Date(query.startDate);
      if (query.endDate) where.createdAt.lte = new Date(query.endDate);
    }

    if (query.type) where.type = query.type;
    if (query.productId) where.productId = query.productId;
    if (query.categoryId) where.categoryId = query.categoryId;
    if (query.sessionId) where.sessionId = query.sessionId;

    const [items, total] = await Promise.all([
      this.prisma.analyticsEvent.findMany({
        where,
        orderBy: { createdAt: "desc" },
        skip: query.skip,
        take: query.limit,
        include: {
          product: { select: { id: true, name: true, slug: true } },
          category: { select: { id: true, name: true, slug: true } },
        },
      }),
      this.prisma.analyticsEvent.count({ where }),
    ]);

    return {
      items,
      meta: {
        total,
        page: Math.floor((query.skip || 0) / (query.limit || 50)) + 1,
        limit: query.limit || 50,
        totalPages: Math.ceil(total / (query.limit || 50)),
      },
    };
  }

  async getMetrics(query: QueryMetricsDto) {
    const where: Prisma.AnalyticsMetricWhereInput = {};

    if (query.startDate || query.endDate) {
      where.date = {};
      if (query.startDate) where.date.gte = new Date(query.startDate);
      if (query.endDate) where.date.lte = new Date(query.endDate);
    }

    if (query.type) where.type = query.type;
    if (query.productId) where.productId = query.productId;
    if (query.categoryId) where.categoryId = query.categoryId;

    const metrics = await this.prisma.analyticsMetric.findMany({
      where,
      orderBy: { date: "asc" },
    });

    return metrics;
  }

  // ==================== DASHBOARD STATS ====================

  async getDashboardStats(query: QueryDashboardDto) {
    const cacheKey = this.getCacheKey("dashboard", JSON.stringify(query));
    
    // Check cache
    const cached = await this.redis.get(cacheKey);
    if (cached) {
      return JSON.parse(cached);
    }

    const endDate = new Date();
    const startDate = new Date();
    startDate.setDate(startDate.getDate() - (query.period || 30));

    // Aggregate metrics
    const aggregated = await this.prisma.analyticsMetric.aggregate({
      where: {
        date: { gte: startDate, lte: endDate },
        ...(query.productId ? { productId: query.productId } : {}),
        ...(query.categoryId ? { categoryId: query.categoryId } : {}),
      },
      _sum: {
        views: true,
        clicks: true,
        conversions: true,
        revenue: true,
        uniqueVisitors: true,
      },
    });

    // Get trend data
    const trend = await this.prisma.analyticsMetric.findMany({
      where: {
        date: { gte: startDate, lte: endDate },
        ...(query.productId ? { productId: query.productId } : {}),
        ...(query.categoryId ? { categoryId: query.categoryId } : {}),
      },
      orderBy: { date: "asc" },
    });

    const totalViews = aggregated._sum.views || 0;
    const totalClicks = aggregated._sum.clicks || 0;
    const totalConversions = aggregated._sum.conversions || 0;
    const totalRevenue = aggregated._sum.revenue || 0;

    const result = {
      totalViews,
      totalClicks,
      totalConversions,
      conversionRate: totalViews > 0 ? (totalConversions / totalViews) * 100 : 0,
      totalRevenue,
      avgRevenuePerConversion: totalConversions > 0 ? totalRevenue / totalConversions : 0,
      uniqueVisitors: aggregated._sum.uniqueVisitors || 0,
      avgSessionDuration: await this.getAvgSessionDuration(startDate, endDate),
      bounceRate: await this.getBounceRate(startDate, endDate),
      trend: trend.map(t => ({
        date: t.date.toISOString().split("T")[0],
        views: t.views,
        clicks: t.clicks,
        conversions: t.conversions,
        revenue: t.revenue,
        uniqueVisitors: t.uniqueVisitors,
        directViews: t.directViews,
        searchViews: t.searchViews,
        socialViews: t.socialViews,
        referralViews: t.referralViews,
      })),
    };

    // Cache result
    await this.redis.setex(cacheKey, CACHE_TTL, JSON.stringify(result));

    return result;
  }

  async getTopProducts(query: QueryDashboardDto) {
    const cacheKey = this.getCacheKey("top-products", JSON.stringify(query));
    
    const cached = await this.redis.get(cacheKey);
    if (cached) {
      return JSON.parse(cached);
    }

    const endDate = new Date();
    const startDate = new Date();
    startDate.setDate(startDate.getDate() - (query.period || 30));

    // Get top products by views
    const topProducts = await this.prisma.analyticsEvent.groupBy({
      by: ["productId"],
      where: {
        type: AnalyticsType.PRODUCT_VIEW,
        createdAt: { gte: startDate, lte: endDate },
        productId: { not: null },
      },
      _count: { id: true },
      orderBy: { _count: { id: "desc" } },
      take: 10,
    });

    const results = [];
    for (const item of topProducts) {
      if (!item.productId) continue;

      const product = await this.prisma.product.findUnique({
        where: { id: item.productId },
        select: { id: true, name: true },
      });

      if (!product) continue;

      // Get clicks and conversions
      const clicks = await this.prisma.analyticsEvent.count({
        where: {
          type: AnalyticsType.CLICK,
          productId: item.productId,
          createdAt: { gte: startDate, lte: endDate },
        },
      });

      const conversions = await this.prisma.analyticsEvent.count({
        where: {
          type: AnalyticsType.CONVERSION,
          productId: item.productId,
          createdAt: { gte: startDate, lte: endDate },
        },
      });

      results.push({
        productId: item.productId,
        productName: product.name,
        views: item._count.id,
        clicks,
        conversions,
        revenue: 0, // Would calculate from actual sales data
        conversionRate: item._count.id > 0 ? (conversions / item._count.id) * 100 : 0,
      });
    }

    await this.redis.setex(cacheKey, CACHE_TTL, JSON.stringify(results));
    return results;
  }

  async getDeviceBreakdown(query: QueryDashboardDto) {
    const endDate = new Date();
    const startDate = new Date();
    startDate.setDate(startDate.getDate() - (query.period || 30));

    const breakdown = await this.prisma.analyticsEvent.groupBy({
      by: ["deviceType"],
      where: {
        createdAt: { gte: startDate, lte: endDate },
        deviceType: { not: null },
      },
      _count: { id: true },
    });

    const total = breakdown.reduce((sum, item) => sum + item._count.id, 0);

    return breakdown.map(item => ({
      deviceType: item.deviceType || "unknown",
      count: item._count.id,
      percentage: total > 0 ? (item._count.id / total) * 100 : 0,
    }));
  }

  async getSourceBreakdown(query: QueryDashboardDto) {
    const endDate = new Date();
    const startDate = new Date();
    startDate.setDate(startDate.getDate() - (query.period || 30));

    // Get sessions grouped by source
    const sessions = await this.prisma.analyticsSession.groupBy({
      by: ["source"],
      where: {
        startedAt: { gte: startDate, lte: endDate },
      },
      _sum: {
        pageViews: true,
      },
      _count: { id: true },
    });

    return sessions.map(item => ({
      source: item.source || "direct",
      views: item._sum.pageViews || 0,
      visitors: item._count.id,
    }));
  }

  async getRealTimeStats() {
    const cacheKey = this.getCacheKey("realtime");
    
    const cached = await this.redis.get(cacheKey);
    if (cached) {
      return JSON.parse(cached);
    }

    const now = new Date();
    const oneMinuteAgo = new Date(now.getTime() - 60 * 1000);
    const fiveMinutesAgo = new Date(now.getTime() - 5 * 60 * 1000);
    const fifteenMinutesAgo = new Date(now.getTime() - 15 * 60 * 1000);

    // Active users (sessions active in last 5 minutes)
    const activeUsers = await this.prisma.analyticsSession.count({
      where: {
        lastActivity: { gte: fiveMinutesAgo },
      },
    });

    // Page views in time windows
    const pageViewsLastMinute = await this.prisma.analyticsEvent.count({
      where: {
        type: AnalyticsType.PAGE_VIEW,
        createdAt: { gte: oneMinuteAgo },
      },
    });

    const pageViewsLast5Minutes = await this.prisma.analyticsEvent.count({
      where: {
        type: AnalyticsType.PAGE_VIEW,
        createdAt: { gte: fiveMinutesAgo },
      },
    });

    const pageViewsLast15Minutes = await this.prisma.analyticsEvent.count({
      where: {
        type: AnalyticsType.PAGE_VIEW,
        createdAt: { gte: fifteenMinutesAgo },
      },
    });

    // Top pages in last 15 minutes
    const topPages = await this.prisma.analyticsEvent.groupBy({
      by: ["url"],
      where: {
        type: AnalyticsType.PAGE_VIEW,
        createdAt: { gte: fifteenMinutesAgo },
        url: { not: null },
      },
      _count: { id: true },
      orderBy: { _count: { id: "desc" } },
      take: 5,
    });

    const result = {
      activeUsers,
      pageViewsLastMinute,
      pageViewsLast5Minutes,
      pageViewsLast15Minutes,
      topPages: topPages.map(p => ({ url: p.url, views: p._count.id })),
    };

    // Cache for 10 seconds (real-time data)
    await this.redis.setex(cacheKey, 10, JSON.stringify(result));

    return result;
  }

  // ==================== AGGREGATION JOBS ====================

  async aggregateDailyMetrics(date: Date) {
    const startOfDay = new Date(date);
    startOfDay.setHours(0, 0, 0, 0);
    const endOfDay = new Date(date);
    endOfDay.setHours(23, 59, 59, 999);

    // Aggregate by product
    const productStats = await this.prisma.analyticsEvent.groupBy({
      by: ["productId"],
      where: {
        productId: { not: null },
        createdAt: { gte: startOfDay, lte: endOfDay },
      },
      _count: {
        _all: true,
        type: true,
      },
    });

    // Create or update metrics
    for (const stat of productStats) {
      if (!stat.productId) continue;

      const views = await this.prisma.analyticsEvent.count({
        where: {
          productId: stat.productId,
          type: AnalyticsType.PRODUCT_VIEW,
          createdAt: { gte: startOfDay, lte: endOfDay },
        },
      });

      const clicks = await this.prisma.analyticsEvent.count({
        where: {
          productId: stat.productId,
          type: AnalyticsType.CLICK,
          createdAt: { gte: startOfDay, lte: endOfDay },
        },
      });

      const conversions = await this.prisma.analyticsEvent.count({
        where: {
          productId: stat.productId,
          type: AnalyticsType.CONVERSION,
          createdAt: { gte: startOfDay, lte: endOfDay },
        },
      });

      await this.prisma.analyticsMetric.upsert({
        where: {
          date_type_productId: {
            date: startOfDay,
            type: MetricType.PRODUCT,
            productId: stat.productId,
          },
        },
        create: {
          date: startOfDay,
          type: MetricType.PRODUCT,
          productId: stat.productId,
          views,
          clicks,
          conversions,
        },
        update: {
          views,
          clicks,
          conversions,
        },
      });
    }

    return { aggregated: productStats.length };
  }

  // ==================== PRIVATE HELPERS ====================

  private parseUserAgent(userAgent?: string) {
    if (!userAgent) {
      return { deviceType: "unknown", browser: "unknown", os: "unknown" };
    }

    try {
      const parser = new UAParser(userAgent);
      const result = parser.getResult();
      
      let deviceType = "desktop";
      if (result.device.type === "mobile") deviceType = "mobile";
      else if (result.device.type === "tablet") deviceType = "tablet";

      return {
        deviceType,
        browser: result.browser.name || "unknown",
        os: result.os.name || "unknown",
      };
    }
    catch {
      return { deviceType: "unknown", browser: "unknown", os: "unknown" };
    }
  }

  private async getGeoInfo(ipAddress?: string) {
    // In production, use a geoip library like geoip-lite or MaxMind
    // This is a simplified placeholder
    if (!ipAddress || ipAddress === "127.0.0.1" || ipAddress.startsWith("192.168.")) {
      return { country: null, city: null };
    }
    
    try {
      // Placeholder for geo lookup
      return { country: null, city: null };
    }
    catch {
      return { country: null, city: null };
    }
  }

  private async getAvgSessionDuration(startDate: Date, endDate: Date) {
    const sessions = await this.prisma.analyticsSession.findMany({
      where: {
        startedAt: { gte: startDate, lte: endDate },
        endedAt: { not: null },
      },
      select: {
        startedAt: true,
        endedAt: true,
      },
    });

    if (sessions.length === 0) return 0;

    const totalDuration = sessions.reduce((sum, session) => {
      if (session.endedAt) {
        return sum + (session.endedAt.getTime() - session.startedAt.getTime());
      }
      return sum;
    }, 0);

    return Math.round(totalDuration / sessions.length / 1000); // in seconds
  }

  private async getBounceRate(startDate: Date, endDate: Date) {
    const [totalSessions, singlePageSessions] = await Promise.all([
      this.prisma.analyticsSession.count({
        where: { startedAt: { gte: startDate, lte: endDate } },
      }),
      this.prisma.analyticsSession.count({
        where: {
          startedAt: { gte: startDate, lte: endDate },
          pageViews: 1,
        },
      }),
    ]);

    return totalSessions > 0 ? (singlePageSessions / totalSessions) * 100 : 0;
  }

  private getCacheKey(type: string, identifier?: string) {
    return identifier
      ? `${CACHE_PREFIX}${type}:${identifier}`
      : `${CACHE_PREFIX}${type}`;
  }

  private async invalidateCache() {
    const keys = await this.redis.keys(`${CACHE_PREFIX}*`);
    if (keys.length > 0) {
      await this.redis.del(...keys);
    }
  }

  // ==================== ADVANCED ANALYTICS - NEW METHODS ====================

  // Affiliate Link Click Tracking
  async trackAffiliateClick(data: {
    linkId: string;
    productId: string;
    pageUrl: string;
    clickPosition: string;
    clickType: string;
    sessionId: string;
    userId?: string;
    ipAddress?: string;
    userAgent?: string;
  }) {
    const uaInfo = this.parseUserAgent(data.userAgent);
    const geoInfo = await this.getGeoInfo(data.ipAddress);
    const now = new Date();

    const click = await this.prisma.affiliateLinkClick.create({
      data: {
        ...data,
        deviceType: uaInfo.deviceType,
        browser: uaInfo.browser,
        os: uaInfo.os,
        country: geoInfo.country,
        city: geoInfo.city,
        hourOfDay: now.getHours(),
        dayOfWeek: now.getDay(),
      },
    });

    // Update product click count
    await this.prisma.product.update({
      where: { id: data.productId },
      data: { clickCount: { increment: 1 } },
    });

    // Track event
    await this.trackEvent({
      type: AnalyticsType.CLICK,
      event: "affiliate_link_click",
      productId: data.productId,
      linkId: data.linkId,
      sessionId: data.sessionId,
      metadata: { clickId: click.id },
    }, { ipAddress: data.ipAddress, userAgent: data.userAgent });

    return click;
  }

  // Conversion Tracking
  async trackConversion(data: {
    linkId: string;
    productId: string;
    clickId?: string;
    orderValue: number;
    commission: number;
    orderId?: string;
    items?: any[];
    sessionId: string;
    userId?: string;
  }) {
    let clickToConvert = 0;
    let deviceType = "unknown";
    let country: string | null = null;

    if (data.clickId) {
      const click = await this.prisma.affiliateLinkClick.findUnique({
        where: { id: data.clickId },
      });

      if (click) {
        clickToConvert = Math.floor((Date.now() - click.createdAt.getTime()) / 60000);
        deviceType = click.deviceType;
        country = click.country;

        // Update click as converted
        await this.prisma.affiliateLinkClick.update({
          where: { id: data.clickId },
          data: {
            converted: true,
            conversionValue: data.orderValue,
            timeToConvert: clickToConvert,
          },
        });
      }
    }

    const conversion = await this.prisma.conversion.create({
      data: {
        ...data,
        clickToConvert,
        deviceType,
        country,
        sessionDuration: 0, // Will be calculated from session
      },
    });

    // Update affiliate link stats
    await this.prisma.affiliateLink.update({
      where: { id: data.linkId },
      data: { conversions: { increment: 1 } },
    });

    return conversion;
  }

  // Revenue Statistics
  async getRevenueStats(query: QueryDashboardDto) {
    const cacheKey = this.getCacheKey("revenue", JSON.stringify(query));
    const cached = await this.redis.get(cacheKey);
    if (cached) return JSON.parse(cached);

    const endDate = new Date();
    const startDate = new Date();
    startDate.setDate(startDate.getDate() - (query.period || 30));

    const [conversionStats, clickStats] = await Promise.all([
      this.prisma.conversion.aggregate({
        where: { createdAt: { gte: startDate, lte: endDate } },
        _sum: { orderValue: true, commission: true },
        _count: { id: true },
        _avg: { orderValue: true, commission: true },
      }),
      this.prisma.affiliateLinkClick.aggregate({
        where: { createdAt: { gte: startDate, lte: endDate } },
        _count: { id: true },
      }),
    ]);

    const totalClicks = clickStats._count.id || 0;
    const totalCommission = conversionStats._sum.commission || 0;
    const epc = totalClicks > 0 ? Math.round(totalCommission / totalClicks) : 0;

    const result = {
      totalRevenue: conversionStats._sum.orderValue || 0,
      totalCommission,
      totalConversions: conversionStats._count.id || 0,
      totalClicks,
      epc,
      avgOrderValue: Math.round(conversionStats._avg.orderValue || 0),
      avgCommission: Math.round(conversionStats._avg.commission || 0),
      conversionRate: totalClicks > 0 ? ((conversionStats._count.id || 0) / totalClicks) * 100 : 0,
    };

    await this.redis.setex(cacheKey, CACHE_TTL, JSON.stringify(result));
    return result;
  }

  // Funnel Analytics
  async getFunnelAnalytics(query: { productId?: string; startDate?: string; endDate?: string }) {
    const endDate = query.endDate ? new Date(query.endDate) : new Date();
    const startDate = query.startDate ? new Date(query.startDate) : new Date(endDate.getTime() - 30 * 24 * 60 * 60 * 1000);

    const where: Prisma.FunnelAnalyticsWhereInput = { date: { gte: startDate, lte: endDate } };
    if (query.productId) where.productId = query.productId;

    const aggregated = await this.prisma.funnelAnalytics.aggregate({
      where,
      _sum: {
        impressions: true,
        clicks: true,
        landings: true,
        addsToCart: true,
        checkouts: true,
        purchases: true,
        revenue: true,
      },
    });

    const s = aggregated._sum;
    const impressions = s.impressions || 0;
    const clicks = s.clicks || 0;
    const addsToCart = s.addsToCart || 0;
    const purchases = s.purchases || 0;

    return {
      stages: {
        impressions: impressions,
        clicks: clicks,
        landings: s.landings || 0,
        addsToCart: addsToCart,
        checkouts: s.checkouts || 0,
        purchases: purchases,
      },
      dropOffRates: {
        viewToClick: impressions > 0 ? ((impressions - clicks) / impressions) * 100 : 0,
        clickToCart: clicks > 0 ? ((clicks - addsToCart) / clicks) * 100 : 0,
        cartToPurchase: addsToCart > 0 ? ((addsToCart - purchases) / addsToCart) * 100 : 0,
        overall: impressions > 0 ? (purchases / impressions) * 100 : 0,
      },
      revenue: s.revenue || 0,
    };
  }

  // Campaign Analytics
  async getCampaignAnalytics(query: QueryDashboardDto) {
    const endDate = new Date();
    const startDate = new Date();
    startDate.setDate(startDate.getDate() - (query.period || 30));

    const campaigns = await this.prisma.campaignAnalytics.groupBy({
      by: ["utmSource", "utmMedium", "utmCampaign"],
      where: { date: { gte: startDate, lte: endDate } },
      _sum: {
        impressions: true,
        clicks: true,
        conversions: true,
        revenue: true,
        commission: true,
        cost: true,
      },
    });

    return campaigns.map(c => ({
      source: c.utmSource,
      medium: c.utmMedium,
      campaign: c.utmCampaign,
      impressions: c._sum.impressions || 0,
      clicks: c._sum.clicks || 0,
      conversions: c._sum.conversions || 0,
      revenue: c._sum.revenue || 0,
      commission: c._sum.commission || 0,
      cost: c._sum.cost || 0,
      ctr: c._sum.impressions ? ((c._sum.clicks || 0) / c._sum.impressions) * 100 : 0,
      roas: c._sum.cost ? (c._sum.revenue || 0) / c._sum.cost : 0,
    }));
  }

  // Top Earning Products
  async getTopEarners(query: QueryDashboardDto) {
    const cacheKey = this.getCacheKey("top-earners", JSON.stringify(query));
    const cached = await this.redis.get(cacheKey);
    if (cached) return JSON.parse(cached);

    const endDate = new Date();
    const startDate = new Date();
    startDate.setDate(startDate.getDate() - (query.period || 30));

    const topProducts = await this.prisma.conversion.groupBy({
      by: ["productId"],
      where: { createdAt: { gte: startDate, lte: endDate } },
      _sum: { commission: true, orderValue: true },
      _count: { id: true },
      orderBy: { _sum: { commission: "desc" } },
      take: 10,
    });

    const results = await Promise.all(
      topProducts.map(async (p) => {
        const product = await this.prisma.product.findUnique({
          where: { id: p.productId },
          select: { id: true, name: true, image: true },
        });

        const clicks = await this.prisma.affiliateLinkClick.count({
          where: { productId: p.productId, createdAt: { gte: startDate, lte: endDate } },
        });

        const commission = p._sum.commission || 0;
        const epc = clicks > 0 ? Math.round(commission / clicks) : 0;

        return {
          productId: p.productId,
          productName: product?.name || "Unknown",
          image: product?.image,
          clicks,
          conversions: p._count.id,
          revenue: p._sum.orderValue || 0,
          commission,
          epc,
          conversionRate: clicks > 0 ? (p._count.id / clicks) * 100 : 0,
        };
      })
    );

    await this.redis.setex(cacheKey, CACHE_TTL, JSON.stringify(results));
    return results;
  }

  // Commission Report
  async getCommissionReport(query: QueryDashboardDto) {
    const endDate = new Date();
    const startDate = new Date();
    startDate.setDate(startDate.getDate() - (query.period || 30));

    const daily = await this.prisma.conversion.groupBy({
      by: ["createdAt"],
      where: { createdAt: { gte: startDate, lte: endDate } },
      _sum: { commission: true, orderValue: true },
      _count: { id: true },
    });

    return daily.map(d => ({
      date: d.createdAt.toISOString().split("T")[0],
      commission: d._sum.commission || 0,
      revenue: d._sum.orderValue || 0,
      conversions: d._count.id,
    }));
  }

  // Link Performance
  async getLinkPerformance(linkId: string, query: QueryDashboardDto) {
    const endDate = new Date();
    const startDate = new Date();
    startDate.setDate(startDate.getDate() - (query.period || 30));

    const [clickStats, conversionStats] = await Promise.all([
      this.prisma.affiliateLinkClick.aggregate({
        where: { linkId, createdAt: { gte: startDate, lte: endDate } },
        _count: { id: true },
      }),
      this.prisma.conversion.aggregate({
        where: { linkId, createdAt: { gte: startDate, lte: endDate } },
        _sum: { commission: true, orderValue: true },
        _count: { id: true },
      }),
    ]);

    const clicks = clickStats._count.id || 0;
    const commission = conversionStats._sum.commission || 0;

    return {
      linkId,
      clicks,
      conversions: conversionStats._count.id || 0,
      revenue: conversionStats._sum.orderValue || 0,
      commission,
      epc: clicks > 0 ? Math.round(commission / clicks) : 0,
      conversionRate: clicks > 0 ? ((conversionStats._count.id || 0) / clicks) * 100 : 0,
    };
  }

  // Search Analytics
  async getSearchAnalytics(query: QueryDashboardDto) {
    const endDate = new Date();
    const startDate = new Date();
    startDate.setDate(startDate.getDate() - (query.period || 30));

    return this.prisma.searchAnalytics.findMany({
      where: { date: { gte: startDate, lte: endDate } },
      orderBy: { searchCount: "desc" },
      take: 20,
    });
  }

  // Geo Analytics
  async getGeoAnalytics(query: QueryDashboardDto) {
    const endDate = new Date();
    const startDate = new Date();
    startDate.setDate(startDate.getDate() - (query.period || 30));

    const countries = await this.prisma.geoAnalytics.groupBy({
      by: ["country"],
      where: { date: { gte: startDate, lte: endDate } },
      _sum: { views: true, clicks: true, conversions: true, revenue: true, commission: true },
    });

    return countries.map(c => ({
      country: c.country,
      views: c._sum.views || 0,
      clicks: c._sum.clicks || 0,
      conversions: c._sum.conversions || 0,
      revenue: c._sum.revenue || 0,
      commission: c._sum.commission || 0,
    }));
  }
}
