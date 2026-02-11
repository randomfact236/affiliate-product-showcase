import { ApiProperty } from "@nestjs/swagger";
import { AnalyticsType } from "@prisma/client";

export class EventResponseDto {
  @ApiProperty()
  id: string;

  @ApiProperty({ enum: AnalyticsType })
  type: AnalyticsType;

  @ApiProperty()
  event: string;

  @ApiProperty({ required: false })
  productId?: string;

  @ApiProperty({ required: false })
  categoryId?: string;

  @ApiProperty({ required: false })
  linkId?: string;

  @ApiProperty({ required: false })
  sessionId?: string;

  @ApiProperty({ required: false })
  url?: string;

  @ApiProperty({ required: false })
  referrer?: string;

  @ApiProperty({ required: false })
  country?: string;

  @ApiProperty({ required: false })
  deviceType?: string;

  @ApiProperty()
  createdAt: Date;
}

export class MetricsResponseDto {
  @ApiProperty()
  date: string;

  @ApiProperty()
  views: number;

  @ApiProperty()
  clicks: number;

  @ApiProperty()
  conversions: number;

  @ApiProperty()
  revenue: number;

  @ApiProperty()
  uniqueVisitors: number;

  @ApiProperty()
  directViews: number;

  @ApiProperty()
  searchViews: number;

  @ApiProperty()
  socialViews: number;

  @ApiProperty()
  referralViews: number;
}

export class DashboardStatsDto {
  @ApiProperty()
  totalViews: number;

  @ApiProperty()
  totalClicks: number;

  @ApiProperty()
  totalConversions: number;

  @ApiProperty()
  conversionRate: number;

  @ApiProperty()
  totalRevenue: number;

  @ApiProperty()
  avgRevenuePerConversion: number;

  @ApiProperty()
  uniqueVisitors: number;

  @ApiProperty()
  avgSessionDuration: number;

  @ApiProperty()
  bounceRate: number;

  @ApiProperty({ type: [MetricsResponseDto] })
  trend: MetricsResponseDto[];
}

export class TopProductsDto {
  @ApiProperty()
  productId: string;

  @ApiProperty()
  productName: string;

  @ApiProperty()
  views: number;

  @ApiProperty()
  clicks: number;

  @ApiProperty()
  conversions: number;

  @ApiProperty()
  revenue: number;

  @ApiProperty()
  conversionRate: number;
}

export class DeviceBreakdownDto {
  @ApiProperty()
  deviceType: string;

  @ApiProperty()
  count: number;

  @ApiProperty()
  percentage: number;
}

export class SourceBreakdownDto {
  @ApiProperty()
  source: string;

  @ApiProperty()
  views: number;

  @ApiProperty()
  clicks: number;

  @ApiProperty()
  conversions: number;
}

export class RealTimeStatsDto {
  @ApiProperty()
  activeUsers: number;

  @ApiProperty()
  pageViewsLastMinute: number;

  @ApiProperty()
  pageViewsLast5Minutes: number;

  @ApiProperty()
  pageViewsLast15Minutes: number;

  @ApiProperty()
  topPages: { url: string; views: number }[];
}
