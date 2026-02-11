import { Controller, Post, Get, Body, Query, UseGuards, Headers } from '@nestjs/common';
import { ApiTags, ApiOperation, ApiBearerAuth } from '@nestjs/swagger';
import { JwtAuthGuard } from '../auth/guards/jwt-auth.guard';
import { AdvancedTrackingService } from './services/advanced-tracking.service';
import { UAParser } from 'ua-parser-js';

@ApiTags('Advanced Analytics Tracking')
@Controller('analytics/track')
export class AdvancedTrackingController {
  constructor(private readonly trackingService: AdvancedTrackingService) {}

  // ==========================================
  // BATCH TRACKING
  // ==========================================
  @Post('batch')
  @ApiOperation({ summary: 'Track multiple events in batch' })
  async trackBatch(
    @Body() data: {
      sessionId: string;
      events: Array<{
        event: string;
        data: any;
        timestamp: number;
      }>;
    },
    @Headers('user-agent') userAgent: string,
  ) {
    const parser = new UAParser(userAgent);
    const deviceInfo = parser.getResult();

    // Process each event
    for (const event of data.events) {
      await this.processEvent(event.event, event.data, data.sessionId, deviceInfo);
    }

    return { processed: data.events.length };
  }

  private async processEvent(eventType: string, data: any, sessionId: string, deviceInfo: any) {
    switch (eventType) {
      case 'affiliate_click':
        await this.trackingService.trackEnhancedClick({
          ...data,
          sessionId: data.sessionId || sessionId,
        });
        break;
      
      case 'scroll_milestone':
        // Update session max scroll depth
        break;
      
      case 'pageview':
        // Track page view with search query extraction
        if (data.searchQuery) {
          await this.trackingService.trackSearchQuery({
            query: data.searchQuery,
            searchEngine: data.searchEngine,
            searchIntent: data.searchIntent,
            landingPage: data.url,
            pageCategory: data.categoryName,
          });
        }
        break;

      case 'social_share':
        await this.trackingService.trackSocialShare({
          pageUrl: data.pageUrl,
          productId: data.productId,
          sessionId: data.sessionId || sessionId,
          platform: data.platform,
          shareType: data.shareType,
        });
        break;

      case 'email_click':
        // Update click with email campaign info
        break;

      case 'form_interaction':
        await this.trackingService.trackFormInteraction({
          ...data,
          sessionId: data.sessionId || sessionId,
        });
        break;

      case 'text_highlight':
      case 'text_copied':
        // Engagement tracking
        break;
    }
  }

  // ==========================================
  // HEATMAP ENDPOINTS
  // ==========================================
  @Post('heatmap')
  @ApiOperation({ summary: 'Track mouse heatmap data' })
  async trackHeatmap(@Body() data: {
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
    return this.trackingService.trackHeatmap(data);
  }

  @Get('heatmap')
  @UseGuards(JwtAuthGuard)
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Get heatmap data for a page' })
  async getHeatmap(
    @Query('pageUrl') pageUrl: string,
    @Query('days') days?: number,
  ) {
    return this.trackingService.getHeatmapData(pageUrl, days || 30);
  }

  // ==========================================
  // ENGAGEMENT ENDPOINTS
  // ==========================================
  @Post('engagement')
  @ApiOperation({ summary: 'Track content engagement metrics' })
  async trackEngagement(@Body() data: {
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
    return this.trackingService.trackEngagement(data);
  }

  @Get('engagement/stats')
  @UseGuards(JwtAuthGuard)
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Get engagement statistics' })
  async getEngagementStats(
    @Query('pageUrl') pageUrl?: string,
    @Query('days') days?: number,
  ) {
    return this.trackingService.getEngagementStats(pageUrl, days || 30);
  }

  // ==========================================
  // SOCIAL SHARING ENDPOINTS
  // ==========================================
  @Post('social-share')
  @ApiOperation({ summary: 'Track social share event' })
  async trackSocialShare(@Body() data: {
    pageUrl: string;
    productId?: string;
    sessionId: string;
    platform: string;
    shareType: 'button' | 'copy_link' | 'native';
  }) {
    return this.trackingService.trackSocialShare(data);
  }

  @Get('social-share/stats')
  @UseGuards(JwtAuthGuard)
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Get social sharing statistics' })
  async getSocialShareStats(@Query('days') days?: number) {
    return this.trackingService.getSocialShareStats(days || 30);
  }

  // ==========================================
  // FORM ANALYTICS ENDPOINTS
  // ==========================================
  @Post('form')
  @ApiOperation({ summary: 'Track form interaction' })
  async trackFormInteraction(@Body() data: {
    sessionId: string;
    pageUrl: string;
    formId?: string;
    formName?: string;
    fieldName?: string;
    fieldType?: string;
    event: 'focus' | 'blur' | 'change' | 'error' | 'submit' | 'abandon';
    errorMessage?: string;
    timeToStart?: number;
    timeToComplete?: number;
    abandonmentStep?: string;
  }) {
    return this.trackingService.trackFormInteraction(data);
  }

  @Get('form/analytics')
  @UseGuards(JwtAuthGuard)
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Get form analytics' })
  async getFormAnalytics(
    @Query('formId') formId?: string,
    @Query('days') days?: number,
  ) {
    return this.trackingService.getFormAnalytics(formId, days || 30);
  }

  // ==========================================
  // PRICE TRACKING ENDPOINTS
  // ==========================================
  @Post('price')
  @ApiOperation({ summary: 'Record product price' })
  async recordPrice(@Body() data: {
    productId: string;
    linkId: string;
    platform: string;
    price: number;
    originalPrice?: number;
    stockStatus?: string;
  }) {
    return this.trackingService.recordPrice(data);
  }

  @Get('price/history')
  @UseGuards(JwtAuthGuard)
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Get price history for a product' })
  async getPriceHistory(
    @Query('productId') productId: string,
    @Query('days') days?: number,
  ) {
    return this.trackingService.getPriceHistory(productId, days || 30);
  }

  // ==========================================
  // SEARCH QUERY ENDPOINTS
  // ==========================================
  @Post('search-query')
  @ApiOperation({ summary: 'Track search query (usually auto-extracted from referrer)' })
  async trackSearchQuery(@Body() data: {
    query: string;
    searchEngine: string;
    searchIntent: string;
    landingPage: string;
    pageCategory?: string;
  }) {
    return this.trackingService.trackSearchQuery(data);
  }

  @Get('search-queries')
  @UseGuards(JwtAuthGuard)
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Get top search queries' })
  async getTopSearchQueries(
    @Query('limit') limit?: number,
    @Query('days') days?: number,
  ) {
    return this.trackingService.getTopSearchQueries(limit || 50, days || 30);
  }

  @Get('search-intent')
  @UseGuards(JwtAuthGuard)
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Get search intent distribution' })
  async getSearchIntentDistribution(@Query('days') days?: number) {
    return this.trackingService.getSearchIntentDistribution(days || 30);
  }

  // ==========================================
  // TIME ON PAGE
  // ==========================================
  @Post('time-on-page')
  @ApiOperation({ summary: 'Track time spent on page (beacon API)' })
  async trackTimeOnPage(@Body() data: {
    url: string;
    timeOnPage: number;
    timestamp: number;
  }) {
    // Store or aggregate time on page data
    return { received: true };
  }
}
