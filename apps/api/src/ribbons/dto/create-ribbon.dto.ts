import {
  IsString,
  IsOptional,
  IsEnum,
  IsBoolean,
  IsInt,
  Min,
  Max,
  Length,
  Matches,
} from 'class-validator';
import { RibbonPosition } from '@prisma/client';
import { ApiProperty, ApiPropertyOptional } from '@nestjs/swagger';

export class CreateRibbonDto {
  @ApiProperty({ description: 'Unique name for the ribbon', example: 'Featured' })
  @IsString()
  @Length(1, 50)
  name: string;

  @ApiProperty({ description: 'Display text on the ribbon', example: 'Featured Product' })
  @IsString()
  @Length(1, 100)
  label: string;

  @ApiPropertyOptional({ description: 'Optional description', example: 'Highlight featured products' })
  @IsString()
  @IsOptional()
  @Length(0, 255)
  description?: string;

  @ApiProperty({ description: 'Background color in hex format', example: '#3B82F6' })
  @IsString()
  @Length(7, 7)
  @Matches(/^#[0-9A-Fa-f]{6}$/, { message: 'bgColor must be a valid hex color (e.g., #3B82F6)' })
  bgColor: string;

  @ApiProperty({ description: 'Text color in hex format', example: '#FFFFFF' })
  @IsString()
  @Length(7, 7)
  @Matches(/^#[0-9A-Fa-f]{6}$/, { message: 'textColor must be a valid hex color (e.g., #FFFFFF)' })
  textColor: string;

  @ApiPropertyOptional({ description: 'Lucide icon name', example: 'star' })
  @IsString()
  @IsOptional()
  @Length(0, 50)
  icon?: string;

  @ApiPropertyOptional({ description: 'Position on the product card', enum: RibbonPosition, default: RibbonPosition.TOP_RIGHT })
  @IsEnum(RibbonPosition)
  @IsOptional()
  position?: RibbonPosition;

  @ApiPropertyOptional({ description: 'Sort order for display', default: 0 })
  @IsInt()
  @Min(0)
  @Max(9999)
  @IsOptional()
  sortOrder?: number;

  @ApiPropertyOptional({ description: 'Whether the ribbon is active', default: true })
  @IsBoolean()
  @IsOptional()
  isActive?: boolean;
}
