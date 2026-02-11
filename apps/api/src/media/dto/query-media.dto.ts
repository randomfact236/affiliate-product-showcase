import { IsString, IsOptional, IsEnum, IsInt, Min, IsBoolean } from 'class-validator';
import { ConversionStatus } from '@prisma/client';
import { Type } from 'class-transformer';
import { ApiPropertyOptional } from '@nestjs/swagger';

export class QueryMediaDto {
  @ApiPropertyOptional({ description: 'Search by filename' })
  @IsString()
  @IsOptional()
  search?: string;

  @ApiPropertyOptional({ description: 'Filter by MIME type (image/jpeg, image/png, etc.)' })
  @IsString()
  @IsOptional()
  mimeType?: string;

  @ApiPropertyOptional({ description: 'Filter by conversion status', enum: ConversionStatus })
  @IsEnum(ConversionStatus)
  @IsOptional()
  conversionStatus?: ConversionStatus;

  @ApiPropertyOptional({ description: 'Filter by conversion status' })
  @IsBoolean()
  @Type(() => Boolean)
  @IsOptional()
  isConverted?: boolean;

  @ApiPropertyOptional({ description: 'Page number', default: 1 })
  @IsInt()
  @Min(1)
  @Type(() => Number)
  @IsOptional()
  page?: number = 1;

  @ApiPropertyOptional({ description: 'Items per page', default: 20 })
  @IsInt()
  @Min(1)
  @Type(() => Number)
  @IsOptional()
  limit?: number = 20;
}

export class ScanUnconvertedDto {
  @ApiPropertyOptional({ description: 'Only show images missing WebP', default: false })
  @IsBoolean()
  @Type(() => Boolean)
  @IsOptional()
  missingWebpOnly?: boolean = false;

  @ApiPropertyOptional({ description: 'Only show images missing AVIF', default: false })
  @IsBoolean()
  @Type(() => Boolean)
  @IsOptional()
  missingAvifOnly?: boolean = false;
}
