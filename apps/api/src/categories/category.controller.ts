import { Controller, Get, Post, Put, Delete, Body, Param, UseGuards } from '@nestjs/common';
import { ApiTags, ApiOperation, ApiBearerAuth, ApiParam } from '@nestjs/swagger';
import { CategoryService, CategoryTreeNode } from './category.service';
import { CreateCategoryDto, UpdateCategoryDto } from './dto/create-category.dto';
import { JwtAuthGuard, RolesGuard } from '../auth/guards';
import { Roles } from '../auth/decorators';

@ApiTags('Categories')
@Controller('categories')
export class CategoryController {
  constructor(private categoryService: CategoryService) {}

  @Get()
  @ApiOperation({ summary: 'Get all categories' })
  findAll() {
    return this.categoryService.findAll();
  }

  @Get('tree')
  @ApiOperation({ summary: 'Get category tree' })
  async findTree(): Promise<CategoryTreeNode[]> {
    return this.categoryService.findTree();
  }

  @Get(':id')
  @ApiOperation({ summary: 'Get category by ID' })
  @ApiParam({ name: 'id', description: 'Category ID' })
  findOne(@Param('id') id: string) {
    return this.categoryService.findOne(id);
  }

  @Get('slug/:slug')
  @ApiOperation({ summary: 'Get category by slug' })
  @ApiParam({ name: 'slug', description: 'Category slug' })
  findBySlug(@Param('slug') slug: string) {
    return this.categoryService.findBySlug(slug);
  }

  @Get(':id/descendants')
  @ApiOperation({ summary: 'Get category descendants' })
  @ApiParam({ name: 'id', description: 'Category ID' })
  findDescendants(@Param('id') id: string) {
    return this.categoryService.findDescendants(id);
  }

  @Get(':id/ancestors')
  @ApiOperation({ summary: 'Get category ancestors' })
  @ApiParam({ name: 'id', description: 'Category ID' })
  findAncestors(@Param('id') id: string) {
    return this.categoryService.findAncestors(id);
  }

  @Get(':id/products')
  @ApiOperation({ summary: 'Get products in category' })
  @ApiParam({ name: 'id', description: 'Category ID' })
  async findProducts(@Param('id') id: string) {
    const category = await this.categoryService.findOne(id);
    // Return category with products
    return this.categoryService['prisma'].category.findUnique({
      where: { id },
      include: {
        products: {
          include: {
            product: {
              include: {
                variants: { where: { isDefault: true } },
                images: { where: { isPrimary: true } },
              },
            },
          },
        },
      },
    });
  }

  @Post()
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles('ADMIN', 'EDITOR')
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Create category (Admin/Editor only)' })
  create(@Body() dto: CreateCategoryDto) {
    return this.categoryService.create(dto);
  }

  @Put(':id')
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles('ADMIN', 'EDITOR')
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Update category (Admin/Editor only)' })
  update(@Param('id') id: string, @Body() dto: UpdateCategoryDto) {
    return this.categoryService.update(id, dto);
  }

  @Delete(':id')
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles('ADMIN')
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Delete category (Admin only)' })
  remove(@Param('id') id: string) {
    return this.categoryService.remove(id);
  }
}
