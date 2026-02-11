import {
  IsString,
  IsOptional,
  IsEnum,
  IsNumber,
  IsArray,
  Min,
  IsJSON,
} from "class-validator";
import { ApiProperty } from "@nestjs/swagger";
import { ProductStatus } from "@prisma/client";

export class CreateVariantDto {
  @ApiProperty({ example: "Default" })
  @IsString()
  name: string;

  @ApiProperty({ example: "PROD-001-RED", required: false })
  @IsString()
  @IsOptional()
  sku?: string;

  @ApiProperty({ example: 9999 })
  @IsNumber()
  @Min(0)
  price: number;

  @ApiProperty({ example: 12999, required: false })
  @IsNumber()
  @IsOptional()
  comparePrice?: number;

  @ApiProperty({ example: 5000, required: false })
  @IsNumber()
  @IsOptional()
  costPrice?: number;

  @ApiProperty({ example: 100 })
  @IsNumber()
  @Min(0)
  inventory: number = 0;

  @ApiProperty({ example: { color: "red", size: "L" }, required: false })
  @IsOptional()
  options?: Record<string, string>;

  @ApiProperty({ default: true })
  @IsOptional()
  isDefault?: boolean = true;
}

export class CreateProductDto {
  @ApiProperty({ example: "Premium Wireless Headphones" })
  @IsString()
  name: string;

  @ApiProperty({
    example: "High-quality wireless headphones...",
    required: false,
  })
  @IsString()
  @IsOptional()
  description?: string;

  @ApiProperty({
    example: "Best wireless headphones with noise cancellation",
    required: false,
  })
  @IsString()
  @IsOptional()
  shortDescription?: string;

  @ApiProperty({ enum: ProductStatus, default: ProductStatus.DRAFT })
  @IsEnum(ProductStatus)
  @IsOptional()
  status?: ProductStatus = ProductStatus.DRAFT;

  @ApiProperty({ example: "Premium Wireless Headphones", required: false })
  @IsString()
  @IsOptional()
  metaTitle?: string;

  @ApiProperty({
    example: "Buy the best wireless headphones...",
    required: false,
  })
  @IsString()
  @IsOptional()
  metaDescription?: string;

  @ApiProperty({ type: [CreateVariantDto], required: false })
  @IsArray()
  @IsOptional()
  variants?: CreateVariantDto[];

  @ApiProperty({ type: [String], required: false })
  @IsArray()
  @IsOptional()
  categoryIds?: string[];

  @ApiProperty({ type: [String], required: false })
  @IsArray()
  @IsOptional()
  tagIds?: string[];

  @ApiProperty({ type: [String], required: false, description: 'Ribbon IDs to assign to product' })
  @IsArray()
  @IsOptional()
  ribbonIds?: string[];
}
