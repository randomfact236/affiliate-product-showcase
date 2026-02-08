import { Injectable, NotFoundException, ConflictException } from '@nestjs/common';
import { PrismaService } from '../prisma/prisma.service';
import { CreateProductDto, UpdateProductDto, ProductFilterDto } from './dto';
import { Prisma } from '@prisma/client';

@Injectable()
export class ProductService {
  constructor(private prisma: PrismaService) {}

  async create(dto: CreateProductDto, userId: string) {
    // Generate slug
    const slug = this.generateSlug(dto.name);
    
    // Check unique slug
    const existing = await this.prisma.product.findUnique({ where: { slug } });
    if (existing) {
      throw new ConflictException('Product with this name already exists');
    }
    
    return this.prisma.product.create({
      data: {
        name: dto.name,
        description: dto.description,
        shortDescription: dto.shortDescription,
        slug,
        status: dto.status,
        metaTitle: dto.metaTitle,
        metaDescription: dto.metaDescription,
        createdBy: userId,
        variants: {
          create: dto.variants?.map((v) => ({
            name: v.name,
            sku: v.sku || this.generateSku(slug, v),
            price: v.price,
            comparePrice: v.comparePrice,
            costPrice: v.costPrice,
            inventory: v.inventory,
            options: v.options ? JSON.stringify(v.options) : undefined,
            isDefault: v.isDefault,
          })),
        },
        categories: {
          create: dto.categoryIds?.map((id) => ({ categoryId: id })),
        },
        tags: {
          create: dto.tagIds?.map((id) => ({ tagId: id })),
        },
      },
      include: {
        variants: true,
        categories: { include: { category: true } },
        images: true,
      },
    });
  }

  async findAll(filters: ProductFilterDto) {
    const where: Prisma.ProductWhereInput = {};
    
    if (filters.status) where.status = filters.status;
    if (filters.categoryId) {
      where.categories = { some: { categoryId: filters.categoryId } };
    }
    if (filters.search) {
      where.OR = [
        { name: { contains: filters.search, mode: 'insensitive' } },
        { description: { contains: filters.search, mode: 'insensitive' } },
      ];
    }
    
    const skip = (filters.page - 1) * filters.limit;
    
    const [products, total] = await Promise.all([
      this.prisma.product.findMany({
        where,
        include: {
          variants: { where: { isDefault: true } },
          images: { where: { isPrimary: true } },
          categories: { include: { category: true } },
        },
        skip,
        take: filters.limit,
        orderBy: { [filters.sortBy]: filters.sortOrder },
      }),
      this.prisma.product.count({ where }),
    ]);
    
    return {
      data: products,
      meta: {
        page: filters.page,
        limit: filters.limit,
        total,
        totalPages: Math.ceil(total / filters.limit),
      },
    };
  }

  async findOne(id: string) {
    const product = await this.prisma.product.findUnique({
      where: { id },
      include: {
        variants: true,
        categories: { include: { category: true } },
        tags: { include: { tag: true } },
        attributes: { include: { attribute: true } },
        images: { orderBy: { sortOrder: 'asc' } },
        ribbons: { where: { OR: [{ endAt: null }, { endAt: { gt: new Date() } }] } },
      },
    });
    
    if (!product) {
      throw new NotFoundException('Product not found');
    }
    
    return product;
  }

  async findBySlug(slug: string) {
    const product = await this.prisma.product.findUnique({
      where: { slug },
      include: {
        variants: true,
        categories: { include: { category: true } },
        tags: { include: { tag: true } },
        attributes: { include: { attribute: true } },
        images: { orderBy: { sortOrder: 'asc' } },
        ribbons: { where: { OR: [{ endAt: null }, { endAt: { gt: new Date() } }] } },
      },
    });
    
    if (!product) {
      throw new NotFoundException('Product not found');
    }
    
    return product;
  }

  async update(id: string, dto: UpdateProductDto, userId: string) {
    await this.findOne(id); // Verify exists
    
    return this.prisma.product.update({
      where: { id },
      data: {
        name: dto.name,
        description: dto.description,
        shortDescription: dto.shortDescription,
        status: dto.status,
        metaTitle: dto.metaTitle,
        metaDescription: dto.metaDescription,
        updatedBy: userId,
      },
      include: {
        variants: true,
        categories: { include: { category: true } },
      },
    });
  }

  async remove(id: string) {
    await this.findOne(id);
    return this.prisma.product.delete({ where: { id } });
  }

  async incrementViewCount(id: string) {
    return this.prisma.product.update({
      where: { id },
      data: { viewCount: { increment: 1 } },
    });
  }

  private generateSlug(name: string): string {
    return name
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '');
  }

  private generateSku(slug: string, variant: { options?: Record<string, string> }): string {
    const variantSuffix = Object.values(variant.options || {})
      .join('-')
      .toUpperCase();
    return `${slug.toUpperCase()}-${variantSuffix || 'DEFAULT'}-${Date.now().toString(36)}`;
  }
}
