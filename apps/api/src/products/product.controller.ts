import { Controller, Get, Post, Put, Delete, Body, Param, Query, UseGuards, Req, HttpCode, HttpStatus } from '@nestjs/common';
import { ApiTags, ApiOperation, ApiBearerAuth, ApiParam } from '@nestjs/swagger';
import { ProductService } from './product.service';
import { CreateProductDto, UpdateProductDto, ProductFilterDto } from './dto';
import { JwtAuthGuard } from '../auth/guards';
import { Roles } from '../auth/decorators';
import { RolesGuard } from '../auth/guards';
import { Request } from 'express';

interface AuthRequest extends Request {
  user: {
    userId: string;
    email: string;
  };
}

@ApiTags('Products')
@Controller('products')
export class ProductController {
  constructor(private productService: ProductService) {}

  @Get()
  @ApiOperation({ summary: 'List products with filters' })
  findAll(@Query() filters: ProductFilterDto) {
    return this.productService.findAll(filters);
  }

  @Get(':id')
  @ApiOperation({ summary: 'Get product by ID' })
  @ApiParam({ name: 'id', description: 'Product ID' })
  async findOne(@Param('id') id: string) {
    const product = await this.productService.findOne(id);
    // Increment view count asynchronously
    this.productService.incrementViewCount(id).catch(() => {});
    return product;
  }

  @Get('slug/:slug')
  @ApiOperation({ summary: 'Get product by slug' })
  @ApiParam({ name: 'slug', description: 'Product slug' })
  async findBySlug(@Param('slug') slug: string) {
    const product = await this.productService.findBySlug(slug);
    this.productService.incrementViewCount(product.id).catch(() => {});
    return product;
  }

  @Post()
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles('ADMIN', 'EDITOR')
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Create product (Admin/Editor only)' })
  create(@Body() dto: CreateProductDto, @Req() req: AuthRequest) {
    return this.productService.create(dto, req.user.userId);
  }

  @Put(':id')
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles('ADMIN', 'EDITOR')
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Update product (Admin/Editor only)' })
  update(@Param('id') id: string, @Body() dto: UpdateProductDto, @Req() req: AuthRequest) {
    return this.productService.update(id, dto, req.user.userId);
  }

  @Delete(':id')
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles('ADMIN')
  @ApiBearerAuth()
  @HttpCode(HttpStatus.NO_CONTENT)
  @ApiOperation({ summary: 'Delete product (Admin only)' })
  async remove(@Param('id') id: string) {
    await this.productService.remove(id);
  }
}
