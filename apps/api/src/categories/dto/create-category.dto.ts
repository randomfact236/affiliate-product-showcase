import { IsString, IsOptional, IsNumber, IsBoolean } from "class-validator";
import { ApiProperty } from "@nestjs/swagger";

export class CreateCategoryDto {
  @ApiProperty({ example: "electronics" })
  @IsString()
  slug: string;

  @ApiProperty({ example: "Electronics" })
  @IsString()
  name: string;

  @ApiProperty({ example: "Electronic devices and gadgets", required: false })
  @IsString()
  @IsOptional()
  description?: string;

  @ApiProperty({ required: false })
  @IsString()
  @IsOptional()
  parentId?: string;

  @ApiProperty({ required: false })
  @IsString()
  @IsOptional()
  metaTitle?: string;

  @ApiProperty({ required: false })
  @IsString()
  @IsOptional()
  metaDescription?: string;

  @ApiProperty({ required: false })
  @IsString()
  @IsOptional()
  image?: string;

  @ApiProperty({ default: true, required: false })
  @IsBoolean()
  @IsOptional()
  isActive?: boolean = true;

  @ApiProperty({ default: 0, required: false })
  @IsNumber()
  @IsOptional()
  sortOrder?: number = 0;
}

export class UpdateCategoryDto {
  @ApiProperty({ required: false })
  @IsString()
  @IsOptional()
  name?: string;

  @ApiProperty({ required: false })
  @IsString()
  @IsOptional()
  description?: string;

  @ApiProperty({ required: false })
  @IsString()
  @IsOptional()
  metaTitle?: string;

  @ApiProperty({ required: false })
  @IsString()
  @IsOptional()
  metaDescription?: string;

  @ApiProperty({ required: false })
  @IsString()
  @IsOptional()
  image?: string;

  @ApiProperty({ required: false })
  @IsBoolean()
  @IsOptional()
  isActive?: boolean;

  @ApiProperty({ required: false })
  @IsNumber()
  @IsOptional()
  sortOrder?: number;
}
