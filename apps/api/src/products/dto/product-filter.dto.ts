import { IsString, IsOptional, IsEnum, IsNumber, IsIn } from "class-validator";
import { ApiProperty } from "@nestjs/swagger";
import { ProductStatus } from "@prisma/client";
import { Type } from "class-transformer";

export class ProductFilterDto {
  @ApiProperty({ required: false })
  @IsString()
  @IsOptional()
  search?: string;

  @ApiProperty({ enum: ProductStatus, required: false })
  @IsEnum(ProductStatus)
  @IsOptional()
  status?: ProductStatus;

  @ApiProperty({ required: false })
  @IsString()
  @IsOptional()
  categoryId?: string;

  @ApiProperty({ default: 1, required: false })
  @Type(() => Number)
  @IsNumber()
  @IsOptional()
  page?: number = 1;

  @ApiProperty({ default: 20, required: false })
  @Type(() => Number)
  @IsNumber()
  @IsOptional()
  limit?: number = 20;

  @ApiProperty({
    default: "createdAt",
    enum: ["name", "createdAt", "updatedAt", "status", "viewCount"],
    required: false,
  })
  @IsIn(["name", "createdAt", "updatedAt", "status", "viewCount"])
  @IsOptional()
  sortBy?: string = "createdAt";

  @ApiProperty({ default: "desc", enum: ["asc", "desc"], required: false })
  @IsIn(["asc", "desc"])
  @IsOptional()
  sortOrder?: "asc" | "desc" = "desc";
}
