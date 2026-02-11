import { ConversionStatus } from '@prisma/client';
import { ApiProperty, ApiPropertyOptional } from '@nestjs/swagger';

export class MediaVariantDto {
  @ApiProperty({ description: 'Format type (webp, avif)' })
  format: string;

  @ApiProperty({ description: 'URL to the converted file' })
  url: string;

  @ApiProperty({ description: 'File size in bytes' })
  fileSize: number;
}

export class MediaResponseDto {
  @ApiProperty()
  id: string;

  @ApiProperty()
  filename: string;

  @ApiProperty()
  originalUrl: string;

  @ApiProperty({ required: false })
  alt: string | null;

  @ApiProperty()
  mimeType: string;

  @ApiProperty()
  fileSize: number;

  @ApiProperty({ required: false })
  width: number | null;

  @ApiProperty({ required: false })
  height: number | null;

  @ApiProperty({ enum: ConversionStatus })
  conversionStatus: ConversionStatus;

  @ApiProperty()
  isConverted: boolean;

  @ApiProperty({ type: [MediaVariantDto], required: false })
  variants: MediaVariantDto[] | null;

  @ApiProperty({ required: false })
  thumbnailUrl: string | null;

  @ApiProperty({ required: false })
  mediumUrl: string | null;

  @ApiProperty({ required: false })
  largeUrl: string | null;

  @ApiProperty()
  createdAt: Date;

  @ApiProperty()
  updatedAt: Date;
}

export class MediaListResponseDto {
  @ApiProperty({ type: [MediaResponseDto] })
  items: MediaResponseDto[];

  @ApiProperty()
  total: number;

  @ApiProperty()
  page: number;

  @ApiProperty()
  limit: number;

  @ApiProperty()
  totalPages: number;
}

export class ConversionStatsDto {
  @ApiProperty({ description: 'Total number of media files' })
  totalImages: number;

  @ApiProperty({ description: 'Number of fully optimized images' })
  fullyOptimized: number;

  @ApiProperty({ description: 'Number of images needing conversion' })
  needsConversion: number;

  @ApiProperty({ description: 'Total storage saved in bytes' })
  storageSaved: number;

  @ApiProperty({ description: 'Storage saved as human readable string' })
  storageSavedFormatted: string;

  @ApiProperty({ description: 'Percentage of optimized images' })
  optimizationPercentage: number;
}

export class ConversionJobResponseDto {
  @ApiProperty()
  jobId: string;

  @ApiProperty()
  status: string;

  @ApiProperty({ description: 'Number of images queued for conversion' })
  queued: number;
}
