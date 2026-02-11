import { IsString, IsOptional, IsBoolean, IsInt, IsEnum, Min, Max } from 'class-validator';
import { ApiProperty, ApiPropertyOptional } from '@nestjs/swagger';
import { ConversionStatus } from '@prisma/client';

export class CreateMediaDto {
  @ApiProperty({ description: 'Original filename' })
  @IsString()
  filename: string;

  @ApiProperty({ description: 'Original file URL/path' })
  @IsString()
  originalUrl: string;

  @ApiPropertyOptional({ description: 'Alt text for accessibility' })
  @IsString()
  @IsOptional()
  alt?: string;

  @ApiProperty({ description: 'MIME type' })
  @IsString()
  mimeType: string;

  @ApiProperty({ description: 'File size in bytes' })
  @IsInt()
  @Min(0)
  fileSize: number;

  @ApiPropertyOptional({ description: 'Image width in pixels' })
  @IsInt()
  @IsOptional()
  width?: number;

  @ApiPropertyOptional({ description: 'Image height in pixels' })
  @IsInt()
  @IsOptional()
  height?: number;
}

export class MediaConversionDto {
  @ApiPropertyOptional({ description: 'Generate WebP format', default: true })
  @IsBoolean()
  @IsOptional()
  generateWebp?: boolean = true;

  @ApiPropertyOptional({ description: 'Generate AVIF format', default: true })
  @IsBoolean()
  @IsOptional()
  generateAvif?: boolean = true;

  @ApiPropertyOptional({ description: 'Generate size variants', default: true })
  @IsBoolean()
  @IsOptional()
  generateVariants?: boolean = true;

  @ApiPropertyOptional({ description: 'Quality for WebP (1-100)', default: 80 })
  @IsInt()
  @Min(1)
  @Max(100)
  @IsOptional()
  webpQuality?: number = 80;

  @ApiPropertyOptional({ description: 'Quality for AVIF (1-100)', default: 70 })
  @IsInt()
  @Min(1)
  @Max(100)
  @IsOptional()
  avifQuality?: number = 70;
}

export class BulkConvertDto {
  @ApiProperty({ description: 'Media IDs to convert', type: [String] })
  mediaIds: string[];

  @ApiPropertyOptional({ description: 'Conversion options' })
  @IsOptional()
  options?: MediaConversionDto;
}
