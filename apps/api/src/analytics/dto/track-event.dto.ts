import { IsString, IsOptional, IsEnum, IsObject, IsBoolean, IsNumber } from "class-validator";
import { ApiProperty } from "@nestjs/swagger";
import { AnalyticsType } from "@prisma/client";

export class TrackEventDto {
  @ApiProperty({ enum: AnalyticsType, example: AnalyticsType.PRODUCT_VIEW })
  @IsEnum(AnalyticsType)
  type: AnalyticsType;

  @ApiProperty({ example: "view" })
  @IsString()
  event: string;

  @ApiProperty({ required: false, description: "Product ID for product events" })
  @IsString()
  @IsOptional()
  productId?: string;

  @ApiProperty({ required: false, description: "Category ID for category events" })
  @IsString()
  @IsOptional()
  categoryId?: string;

  @ApiProperty({ required: false, description: "Affiliate link ID for click events" })
  @IsString()
  @IsOptional()
  linkId?: string;

  @ApiProperty({ required: false })
  @IsString()
  @IsOptional()
  sessionId?: string;

  @ApiProperty({ required: false })
  @IsString()
  @IsOptional()
  url?: string;

  @ApiProperty({ required: false })
  @IsString()
  @IsOptional()
  referrer?: string;

  // Device Information
  @ApiProperty({ required: false, description: "Device type (mobile, tablet, desktop)" })
  @IsString()
  @IsOptional()
  deviceType?: string;

  @ApiProperty({ required: false })
  @IsBoolean()
  @IsOptional()
  isMobile?: boolean;

  @ApiProperty({ required: false })
  @IsBoolean()
  @IsOptional()
  isTablet?: boolean;

  @ApiProperty({ required: false })
  @IsBoolean()
  @IsOptional()
  isDesktop?: boolean;

  @ApiProperty({ required: false })
  @IsBoolean()
  @IsOptional()
  isTouch?: boolean;

  @ApiProperty({ required: false, description: "Browser name" })
  @IsString()
  @IsOptional()
  browser?: string;

  @ApiProperty({ required: false })
  @IsString()
  @IsOptional()
  browserVersion?: string;

  @ApiProperty({ required: false })
  @IsString()
  @IsOptional()
  browserEngine?: string;

  @ApiProperty({ required: false, description: "Operating system" })
  @IsString()
  @IsOptional()
  os?: string;

  @ApiProperty({ required: false })
  @IsString()
  @IsOptional()
  osVersion?: string;

  @ApiProperty({ required: false })
  @IsString()
  @IsOptional()
  platform?: string;

  @ApiProperty({ required: false, description: "Screen resolution (e.g., 1920x1080)" })
  @IsString()
  @IsOptional()
  screenResolution?: string;

  @ApiProperty({ required: false })
  @IsString()
  @IsOptional()
  viewport?: string;

  @ApiProperty({ required: false })
  @IsNumber()
  @IsOptional()
  devicePixelRatio?: number;

  @ApiProperty({ required: false })
  @IsNumber()
  @IsOptional()
  colorDepth?: number;

  @ApiProperty({ required: false })
  @IsString()
  @IsOptional()
  orientation?: string;

  // Location & Language
  @ApiProperty({ required: false })
  @IsString()
  @IsOptional()
  language?: string;

  @ApiProperty({ required: false })
  @IsString()
  @IsOptional()
  timezone?: string;

  @ApiProperty({ required: false })
  @IsNumber()
  @IsOptional()
  timezoneOffset?: number;

  // Connection
  @ApiProperty({ required: false })
  @IsString()
  @IsOptional()
  connectionType?: string;

  @ApiProperty({ required: false })
  @IsString()
  @IsOptional()
  connectionSpeed?: string;

  @ApiProperty({ required: false })
  @IsBoolean()
  @IsOptional()
  saveData?: boolean;

  // Capabilities
  @ApiProperty({ required: false })
  @IsBoolean()
  @IsOptional()
  cookieEnabled?: boolean;

  @ApiProperty({ required: false })
  @IsBoolean()
  @IsOptional()
  localStorageEnabled?: boolean;

  // Preferences
  @ApiProperty({ required: false })
  @IsString()
  @IsOptional()
  colorScheme?: string;

  @ApiProperty({ required: false })
  @IsBoolean()
  @IsOptional()
  reducedMotion?: boolean;

  // Attribution
  @ApiProperty({ required: false })
  @IsString()
  @IsOptional()
  source?: string;

  @ApiProperty({ required: false })
  @IsString()
  @IsOptional()
  medium?: string;

  @ApiProperty({ required: false })
  @IsString()
  @IsOptional()
  campaign?: string;

  @ApiProperty({ required: false })
  @IsString()
  @IsOptional()
  utmSource?: string;

  @ApiProperty({ required: false })
  @IsString()
  @IsOptional()
  utmMedium?: string;

  @ApiProperty({ required: false })
  @IsString()
  @IsOptional()
  utmCampaign?: string;

  // Session Info
  @ApiProperty({ required: false })
  @IsBoolean()
  @IsOptional()
  isNewVisitor?: boolean;

  @ApiProperty({ required: false })
  @IsNumber()
  @IsOptional()
  visitCount?: number;

  // Search
  @ApiProperty({ required: false })
  @IsString()
  @IsOptional()
  searchQuery?: string;

  @ApiProperty({ required: false })
  @IsString()
  @IsOptional()
  searchEngine?: string;

  @ApiProperty({ required: false })
  @IsString()
  @IsOptional()
  searchIntent?: string;

  @ApiProperty({ required: false })
  @IsObject()
  @IsOptional()
  metadata?: Record<string, any>;
}

export class TrackBatchEventsDto {
  @ApiProperty({ type: [TrackEventDto] })
  events: TrackEventDto[];
}
