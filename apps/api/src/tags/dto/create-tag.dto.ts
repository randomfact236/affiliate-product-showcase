import {
  IsString,
  IsOptional,
  IsBoolean,
  IsInt,
  Min,
  Max,
  Length,
  Matches,
} from 'class-validator';
import { ApiProperty, ApiPropertyOptional } from '@nestjs/swagger';

export class CreateTagDto {
  @ApiProperty({ description: 'Unique slug for the tag', example: 'wireless' })
  @IsString()
  @Length(1, 50)
  @Matches(/^[a-z0-9-]+$/, {
    message: 'Slug must be lowercase alphanumeric with hyphens only',
  })
  slug: string;

  @ApiProperty({ description: 'Display name for the tag', example: 'Wireless' })
  @IsString()
  @Length(1, 100)
  name: string;

  @ApiPropertyOptional({ description: 'Tag description', example: 'Wireless products and accessories' })
  @IsString()
  @IsOptional()
  @Length(0, 255)
  description?: string;

  @ApiPropertyOptional({ description: 'Tag color in hex format', example: '#3B82F6' })
  @IsString()
  @IsOptional()
  @Length(7, 7)
  @Matches(/^#[0-9A-Fa-f]{6}$/, { message: 'Color must be a valid hex color (e.g., #3B82F6)' })
  color?: string;

  @ApiPropertyOptional({ description: 'Lucide icon name', example: 'wifi' })
  @IsString()
  @IsOptional()
  @Length(0, 50)
  icon?: string;

  @ApiPropertyOptional({ description: 'Sort order', default: 0 })
  @IsInt()
  @Min(0)
  @Max(9999)
  @IsOptional()
  sortOrder?: number = 0;

  @ApiPropertyOptional({ description: 'Whether the tag is active', default: true })
  @IsBoolean()
  @IsOptional()
  isActive?: boolean = true;
}
