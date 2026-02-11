import {
  Controller,
  Get,
  Post,
  Body,
  Query,
  Req,
  UseGuards,
  Ip,
  Headers,
} from "@nestjs/common";
import { ApiTags, ApiOperation, ApiBearerAuth, ApiQuery } from "@nestjs/swagger";
import { AnalyticsService } from "./analytics.service";
import {
  TrackEventDto,
  TrackBatchEventsDto,
  QueryAnalyticsDto,
  QueryMetricsDto,
  QueryDashboardDto,
} from "./dto";
import { JwtAuthGuard, RolesGuard } from "../auth/guards";
import { Roles } from "../auth/decorators";
import { Request } from "express";

@ApiTags("Analytics")
@Controller("analytics")
export class AnalyticsController {
  constructor(private readonly analyticsService: AnalyticsService) {}

  // ==================== PUBLIC TRACKING ENDPOINTS ====================

  @Post("track")
  @ApiOperation({ summary: "Track a single analytics event" })
  async trackEvent(
    @Body() dto: TrackEventDto,
    @Ip() ip: string,
    @Headers("user-agent") userAgent: string,
  ) {
    return this.analyticsService.trackEvent(dto, {
      ipAddress: ip,
      userAgent,
    });
  }

  @Post("track/batch")
  @ApiOperation({ summary: "Track multiple analytics events" })
  async trackBatch(
    @Body() dto: TrackBatchEventsDto,
    @Ip() ip: string,
    @Headers("user-agent") userAgent: string,
  ) {
    return this.analyticsService.trackBatch(dto.events, {
      ipAddress: ip,
      userAgent,
    });
  }

  @Post("session/start")
  @ApiOperation({ summary: "Start a new analytics session" })
  async startSession(
    @Body() sessionData: {
      sessionId: string;
      userId?: string;
      landingPage?: string;
      referrer?: string;
      source?: string;
      medium?: string;
      campaign?: string;
    },
    @Ip() ip: string,
    @Headers("user-agent") userAgent: string,
  ) {
    return this.analyticsService.createSession({
      ...sessionData,
      ipAddress: ip,
      userAgent,
    });
  }

  @Post("session/:sessionId/activity")
  @ApiOperation({ summary: "Record session activity" })
  async recordActivity(
    @Body() data: { sessionId: string; url?: string },
  ) {
    return this.analyticsService.updateSession(data.sessionId, data.url);
  }

  @Post("session/:sessionId/end")
  @ApiOperation({ summary: "End an analytics session" })
  async endSession(@Body() data: { sessionId: string }) {
    return this.analyticsService.endSession(data.sessionId);
  }

  // ==================== ADMIN DASHBOARD ENDPOINTS ====================

  @Get("dashboard")
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles("ADMIN", "EDITOR")
  @ApiBearerAuth()
  @ApiOperation({ summary: "Get dashboard statistics" })
  @ApiQuery({ name: "period", required: false, type: Number, description: "Period in days" })
  @ApiQuery({ name: "productId", required: false, type: String })
  @ApiQuery({ name: "categoryId", required: false, type: String })
  async getDashboardStats(@Query() query: QueryDashboardDto) {
    return this.analyticsService.getDashboardStats(query);
  }

  @Get("realtime")
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles("ADMIN", "EDITOR")
  @ApiBearerAuth()
  @ApiOperation({ summary: "Get real-time statistics" })
  async getRealTimeStats() {
    return this.analyticsService.getRealTimeStats();
  }

  @Get("events")
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles("ADMIN", "EDITOR")
  @ApiBearerAuth()
  @ApiOperation({ summary: "Query analytics events" })
  async getEvents(@Query() query: QueryAnalyticsDto) {
    return this.analyticsService.findEvents(query);
  }

  @Get("metrics")
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles("ADMIN", "EDITOR")
  @ApiBearerAuth()
  @ApiOperation({ summary: "Get aggregated metrics" })
  async getMetrics(@Query() query: QueryMetricsDto) {
    return this.analyticsService.getMetrics(query);
  }

  @Get("top-products")
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles("ADMIN", "EDITOR")
  @ApiBearerAuth()
  @ApiOperation({ summary: "Get top performing products" })
  @ApiQuery({ name: "period", required: false, type: Number, description: "Period in days" })
  async getTopProducts(@Query() query: QueryDashboardDto) {
    return this.analyticsService.getTopProducts(query);
  }

  @Get("devices")
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles("ADMIN", "EDITOR")
  @ApiBearerAuth()
  @ApiOperation({ summary: "Get device breakdown" })
  async getDeviceBreakdown(@Query() query: QueryDashboardDto) {
    return this.analyticsService.getDeviceBreakdown(query);
  }

  @Get("sources")
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles("ADMIN", "EDITOR")
  @ApiBearerAuth()
  @ApiOperation({ summary: "Get traffic source breakdown" })
  async getSourceBreakdown(@Query() query: QueryDashboardDto) {
    return this.analyticsService.getSourceBreakdown(query);
  }

  // ==================== REVENUE & COMMISSION ENDPOINTS ====================

  @Get("revenue")
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles("ADMIN", "EDITOR")
  @ApiBearerAuth()
  @ApiOperation({ summary: "Get revenue and commission statistics" })
  async getRevenueStats(@Query() query: QueryDashboardDto) {
    return this.analyticsService.getRevenueStats(query);
  }

  @Get("commissions")
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles("ADMIN", "EDITOR")
  @ApiBearerAuth()
  @ApiOperation({ summary: "Get commission report" })
  async getCommissionReport(@Query() query: QueryDashboardDto) {
    return this.analyticsService.getCommissionReport(query);
  }

  @Get("top-earners")
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles("ADMIN", "EDITOR")
  @ApiBearerAuth()
  @ApiOperation({ summary: "Get top earning products" })
  async getTopEarners(@Query() query: QueryDashboardDto) {
    return this.analyticsService.getTopEarners(query);
  }

  // ==================== FUNNEL & CAMPAIGN ENDPOINTS ====================

  @Get("funnel")
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles("ADMIN", "EDITOR")
  @ApiBearerAuth()
  @ApiOperation({ summary: "Get conversion funnel analytics" })
  async getFunnelAnalytics(@Query() query: { productId?: string; startDate?: string; endDate?: string }) {
    return this.analyticsService.getFunnelAnalytics(query);
  }

  @Get("campaigns")
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles("ADMIN", "EDITOR")
  @ApiBearerAuth()
  @ApiOperation({ summary: "Get campaign analytics" })
  async getCampaignAnalytics(@Query() query: QueryDashboardDto) {
    return this.analyticsService.getCampaignAnalytics(query);
  }

  // ==================== LINK PERFORMANCE ENDPOINTS ====================

  @Get("links/:linkId/performance")
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles("ADMIN", "EDITOR")
  @ApiBearerAuth()
  @ApiOperation({ summary: "Get affiliate link performance" })
  async getLinkPerformance(@Param("linkId") linkId: string, @Query() query: QueryDashboardDto) {
    return this.analyticsService.getLinkPerformance(linkId, query);
  }

  // ==================== SEARCH & GEO ENDPOINTS ====================

  @Get("search-terms")
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles("ADMIN", "EDITOR")
  @ApiBearerAuth()
  @ApiOperation({ summary: "Get search analytics" })
  async getSearchAnalytics(@Query() query: QueryDashboardDto) {
    return this.analyticsService.getSearchAnalytics(query);
  }

  @Get("geo")
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles("ADMIN", "EDITOR")
  @ApiBearerAuth()
  @ApiOperation({ summary: "Get geographic analytics" })
  async getGeoAnalytics(@Query() query: QueryDashboardDto) {
    return this.analyticsService.getGeoAnalytics(query);
  }

  // ==================== TRACKING ENDPOINTS (Public) ====================

  @Post("track/click")
  @ApiOperation({ summary: "Track affiliate link click" })
  async trackAffiliateClick(
    @Body() data: {
      linkId: string;
      productId: string;
      pageUrl: string;
      clickPosition: string;
      clickType: string;
      sessionId: string;
      userId?: string;
    },
    @Ip() ip: string,
    @Headers("user-agent") userAgent: string,
  ) {
    return this.analyticsService.trackAffiliateClick({
      ...data,
      ipAddress: ip,
      userAgent,
    });
  }

  @Post("track/conversion")
  @ApiOperation({ summary: "Track conversion" })
  async trackConversion(@Body() data: {
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
    return this.analyticsService.trackConversion(data);
  }
}
