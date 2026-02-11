import {
  IsString,
  IsOptional,
  IsEnum,
  IsDateString,
  IsArray,
  IsUUID,
} from "class-validator";
import { ApiProperty, ApiPropertyOptional } from "@nestjs/swagger";
import { BlogPostStatus } from "@prisma/client";

export class CreateBlogPostDto {
  @ApiProperty({ description: "Blog post title", example: "Top 10 Gadgets for 2024" })
  @IsString()
  title: string;

  @ApiPropertyOptional({ description: "URL-friendly slug", example: "top-10-gadgets-2024" })
  @IsString()
  @IsOptional()
  slug?: string;

  @ApiPropertyOptional({ description: "Short excerpt/summary" })
  @IsString()
  @IsOptional()
  excerpt?: string;

  @ApiProperty({ description: "Full content (HTML or Markdown)" })
  @IsString()
  content: string;

  @ApiPropertyOptional({ description: "Content type", enum: ["html", "markdown"], default: "html" })
  @IsString()
  @IsOptional()
  contentType?: string;

  @ApiPropertyOptional({ description: "Post status", enum: BlogPostStatus, default: BlogPostStatus.DRAFT })
  @IsEnum(BlogPostStatus)
  @IsOptional()
  status?: BlogPostStatus;

  @ApiPropertyOptional({ description: "Publish date" })
  @IsDateString()
  @IsOptional()
  publishedAt?: string;

  @ApiPropertyOptional({ description: "SEO meta title" })
  @IsString()
  @IsOptional()
  metaTitle?: string;

  @ApiPropertyOptional({ description: "SEO meta description" })
  @IsString()
  @IsOptional()
  metaDescription?: string;

  @ApiPropertyOptional({ description: "SEO keywords (comma-separated)" })
  @IsString()
  @IsOptional()
  keywords?: string;

  @ApiPropertyOptional({ description: "Featured image media ID" })
  @IsUUID()
  @IsOptional()
  featuredImageId?: string;

  @ApiPropertyOptional({ description: "Category IDs", type: [String] })
  @IsArray()
  @IsUUID("4", { each: true })
  @IsOptional()
  categoryIds?: string[];

  @ApiPropertyOptional({ description: "Tag IDs", type: [String] })
  @IsArray()
  @IsUUID("4", { each: true })
  @IsOptional()
  tagIds?: string[];

  @ApiPropertyOptional({ description: "Related product IDs", type: [String] })
  @IsArray()
  @IsUUID("4", { each: true })
  @IsOptional()
  relatedProductIds?: string[];

  @ApiPropertyOptional({ description: "Author ID" })
  @IsUUID()
  @IsOptional()
  authorId?: string;

  @ApiPropertyOptional({ description: "Estimated reading time in minutes", default: 0 })
  @IsOptional()
  readingTime?: number;
}
