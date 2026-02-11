import {
  Controller,
  Get,
  Post,
  Put,
  Delete,
  Body,
  Param,
  Query,
  UseGuards,
  Req,
  HttpCode,
  HttpStatus,
} from "@nestjs/common";
import {
  ApiTags,
  ApiOperation,
  ApiBearerAuth,
  ApiParam,
} from "@nestjs/swagger";
import { BlogService } from "./blog.service";
import { CreateBlogPostDto, UpdateBlogPostDto, QueryBlogPostsDto } from "./dto";
import { JwtAuthGuard, RolesGuard } from "../auth/guards";
import { Roles } from "../auth/decorators";
import { Request } from "express";
import { Throttle } from "@nestjs/throttler";

interface AuthRequest extends Request {
  user: {
    userId: string;
    email: string;
  };
}

@ApiTags("Blog")
@Controller({ path: "blog", version: "1" })
@Throttle({ default: { limit: 100, ttl: 60000 } })
export class BlogController {
  constructor(private blogService: BlogService) {}

  @Get()
  @ApiOperation({ summary: "List blog posts with filters" })
  findAll(@Query() query: QueryBlogPostsDto) {
    return this.blogService.findAll(query);
  }

  @Get("featured")
  @ApiOperation({ summary: "Get featured blog posts" })
  async getFeatured(@Query("limit") limit: string = "5") {
    return this.blogService.findAll({
      limit: parseInt(limit, 10),
      sortBy: "viewCount",
      sortOrder: "desc",
    });
  }

  @Get("latest")
  @ApiOperation({ summary: "Get latest blog posts" })
  async getLatest(@Query("limit") limit: string = "5") {
    return this.blogService.findAll({
      limit: parseInt(limit, 10),
      sortBy: "publishedAt",
      sortOrder: "desc",
    });
  }

  @Get(":id")
  @ApiOperation({ summary: "Get blog post by ID" })
  @ApiParam({ name: "id", description: "Blog post ID" })
  async findOne(@Param("id") id: string, @Req() req: Request) {
    const post = await this.blogService.findOne(id);
    // Record view asynchronously
    this.blogService.recordView(
      id,
      req.ip,
      req.headers["user-agent"] as string,
    ).catch(() => {});
    this.blogService.incrementViewCount(id).catch(() => {});
    return post;
  }

  @Get("slug/:slug")
  @ApiOperation({ summary: "Get blog post by slug" })
  @ApiParam({ name: "slug", description: "Blog post slug" })
  async findBySlug(@Param("slug") slug: string, @Req() req: Request) {
    const post = await this.blogService.findBySlug(slug);
    // Record view asynchronously
    this.blogService.recordView(
      post.id,
      req.ip,
      req.headers["user-agent"] as string,
    ).catch(() => {});
    this.blogService.incrementViewCount(post.id).catch(() => {});
    return post;
  }

  @Post()
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles("ADMIN", "EDITOR")
  @ApiBearerAuth()
  @ApiOperation({ summary: "Create blog post (Admin/Editor only)" })
  create(@Body() dto: CreateBlogPostDto, @Req() req: AuthRequest) {
    return this.blogService.create(dto, req.user.userId);
  }

  @Put(":id")
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles("ADMIN", "EDITOR")
  @ApiBearerAuth()
  @ApiOperation({ summary: "Update blog post (Admin/Editor only)" })
  update(
    @Param("id") id: string,
    @Body() dto: UpdateBlogPostDto,
    @Req() req: AuthRequest,
  ) {
    return this.blogService.update(id, dto, req.user.userId);
  }

  @Delete(":id")
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles("ADMIN")
  @ApiBearerAuth()
  @HttpCode(HttpStatus.NO_CONTENT)
  @ApiOperation({ summary: "Delete blog post (Admin only)" })
  async remove(@Param("id") id: string) {
    await this.blogService.remove(id);
  }
}
