import { IsString, IsOptional, IsEnum, IsDateString, IsInt, Min } from "class-validator";
import { ApiProperty } from "@nestjs/swagger";
import { Transform, Type } from "class-transformer";
import { AnalyticsType, MetricType } from "@prisma/client";

export class QueryAnalyticsDto {
  @ApiProperty({ required: false, description: "Start date (ISO format)" })
  @IsDateString()
  @IsOptional()
  startDate?: string;

  @ApiProperty({ required: false, description: "End date (ISO format)" })
  @IsDateString()
  @IsOptional()
  endDate?: string;

  @ApiProperty({ enum: AnalyticsType, required: false })
  @IsEnum(AnalyticsType)
  @IsOptional()
  type?: AnalyticsType;

  @ApiProperty({ required: false })
  @IsString()
  @IsOptional()
  productId?: string;

  @ApiProperty({ required: false })
  @IsString()
  @IsOptional()
  categoryId?: string;

  @ApiProperty({ required: false })
  @IsString()
  @IsOptional()
  sessionId?: string;

  @ApiProperty({ required: false, default: 50 })
  @IsInt()
  @Min(1)
  @IsOptional()
  @Type(() => Number)
  limit?: number = 50;

  @ApiProperty({ required: false, default: 0 })
  @IsInt()
  @Min(0)
  @IsOptional()
  @Type(() => Number)
  skip?: number = 0;
}

export class QueryMetricsDto {
  @ApiProperty({ required: false, description: "Start date (ISO format)" })
  @IsDateString()
  @IsOptional()
  startDate?: string;

  @ApiProperty({ required: false, description: "End date (ISO format)" })
  @IsDateString()
  @IsOptional()
  endDate?: string;

  @ApiProperty({ enum: MetricType, required: false })
  @IsEnum(MetricType)
  @IsOptional()
  type?: MetricType;

  @ApiProperty({ required: false })
  @IsString()
  @IsOptional()
  productId?: string;

  @ApiProperty({ required: false })
  @IsString()
  @IsOptional()
  categoryId?: string;

  @ApiProperty({ required: false, default: "day", enum: ["hour", "day", "week", "month"] })
  @IsString()
  @IsOptional()
  groupBy?: "hour" | "day" | "week" | "month" = "day";
}

export class QueryDashboardDto {
  @ApiProperty({ required: false, description: "Period in days", default: 30 })
  @IsInt()
  @Min(1)
  @IsOptional()
  @Type(() => Number)
  period?: number = 30;

  @ApiProperty({ required: false })
  @IsString()
  @IsOptional()
  productId?: string;

  @ApiProperty({ required: false })
  @IsString()
  @IsOptional()
  categoryId?: string;
}
