import { IsString, IsOptional, IsEnum, IsInt, Min, IsUUID, IsArray } from "class-validator";
import { ApiPropertyOptional } from "@nestjs/swagger";
import { BlogPostStatus } from "@prisma/client";
import { Type } from "class-transformer";

export class QueryBlogPostsDto {
  @ApiPropertyOptional({ description: "Page number", default: 1 })
  @IsInt()
  @Min(1)
  @IsOptional()
  @Type(() => Number)
  page?: number = 1;

  @ApiPropertyOptional({ description: "Items per page", default: 10 })
  @IsInt()
  @Min(1)
  @IsOptional()
  @Type(() => Number)
  limit?: number = 10;

  @ApiPropertyOptional({ description: "Search query" })
  @IsString()
  @IsOptional()
  search?: string;

  @ApiPropertyOptional({ description: "Filter by status", enum: BlogPostStatus })
  @IsEnum(BlogPostStatus)
  @IsOptional()
  status?: BlogPostStatus;

  @ApiPropertyOptional({ description: "Filter by author ID" })
  @IsUUID()
  @IsOptional()
  authorId?: string;

  @ApiPropertyOptional({ description: "Filter by category IDs", type: [String] })
  @IsArray()
  @IsUUID("4", { each: true })
  @IsOptional()
  categoryIds?: string[];

  @ApiPropertyOptional({ description: "Filter by tag IDs", type: [String] })
  @IsArray()
  @IsUUID("4", { each: true })
  @IsOptional()
  tagIds?: string[];

  @ApiPropertyOptional({ description: "Sort field", default: "createdAt" })
  @IsString()
  @IsOptional()
  sortBy?: string = "createdAt";

  @ApiPropertyOptional({ description: "Sort order", enum: ["asc", "desc"], default: "desc" })
  @IsEnum(["asc", "desc"])
  @IsOptional()
  sortOrder?: "asc" | "desc" = "desc";
}
