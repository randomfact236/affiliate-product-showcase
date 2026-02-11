import { IsString, IsOptional, IsBoolean, IsInt, IsIn, Min, Max } from 'class-validator';
import { Type } from 'class-transformer';

export class CreateDontMissSectionDto {
  @IsString()
  name: string;

  @IsString()
  title: string;

  @IsOptional()
  @IsString()
  subtitle?: string;

  @IsOptional()
  @IsString()
  @IsIn(['mixed', 'blogs_only', 'products_only'])
  layout?: string = 'mixed';

  @IsOptional()
  @Type(() => Number)
  @IsInt()
  @Min(1)
  @Max(12)
  blogCount?: number = 3;

  @IsOptional()
  @Type(() => Number)
  @IsInt()
  @Min(1)
  @Max(12)
  productCount?: number = 2;

  @IsOptional()
  @IsString()
  blogCategoryId?: string;

  @IsOptional()
  @IsString()
  productCategoryId?: string;

  @IsOptional()
  @IsBoolean()
  showViewAll?: boolean = true;

  @IsOptional()
  @IsString()
  @IsIn(['latest', 'popular', 'featured'])
  sortBy?: string = 'latest';

  @IsOptional()
  @IsString()
  backgroundColor?: string;

  @IsOptional()
  @IsString()
  textColor?: string;

  @IsOptional()
  @IsBoolean()
  isActive?: boolean = true;

  @IsOptional()
  @Type(() => Number)
  @IsInt()
  sortOrder?: number = 0;
}

export class UpdateDontMissSectionDto {
  @IsOptional()
  @IsString()
  name?: string;

  @IsOptional()
  @IsString()
  title?: string;

  @IsOptional()
  @IsString()
  subtitle?: string;

  @IsOptional()
  @IsString()
  @IsIn(['mixed', 'blogs_only', 'products_only'])
  layout?: string;

  @IsOptional()
  @Type(() => Number)
  @IsInt()
  @Min(1)
  @Max(12)
  blogCount?: number;

  @IsOptional()
  @Type(() => Number)
  @IsInt()
  @Min(1)
  @Max(12)
  productCount?: number;

  @IsOptional()
  @IsString()
  blogCategoryId?: string;

  @IsOptional()
  @IsString()
  productCategoryId?: string;

  @IsOptional()
  @IsBoolean()
  showViewAll?: boolean;

  @IsOptional()
  @IsString()
  @IsIn(['latest', 'popular', 'featured'])
  sortBy?: string;

  @IsOptional()
  @IsString()
  backgroundColor?: string;

  @IsOptional()
  @IsString()
  textColor?: string;

  @IsOptional()
  @IsBoolean()
  isActive?: boolean;

  @IsOptional()
  @Type(() => Number)
  @IsInt()
  sortOrder?: number;
}
