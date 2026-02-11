import {
  Injectable,
  NotFoundException,
  ConflictException,
  BadRequestException,
} from '@nestjs/common';
import { PrismaService } from '../prisma/prisma.service';
import {
  CreateTagDto,
  UpdateTagDto,
  QueryTagsDto,
  TagListResponseDto,
  TagResponseDto,
  MergeTagsDto,
} from './dto';
import { Prisma } from '@prisma/client';

@Injectable()
export class TagsService {
  constructor(private readonly prisma: PrismaService) {}

  /**
   * Create a new tag
   */
  async create(createDto: CreateTagDto, userId?: string): Promise<TagResponseDto> {
    // Check for duplicate slug
    const existing = await this.prisma.tag.findUnique({
      where: { slug: createDto.slug },
    });

    if (existing) {
      throw new ConflictException(`Tag with slug '${createDto.slug}' already exists`);
    }

    const tag = await this.prisma.tag.create({
      data: {
        ...createDto,
        createdBy: userId,
      },
      include: {
        _count: {
          select: { products: true },
        },
      },
    });

    return this.mapToResponseDto(tag);
  }

  /**
   * Find all tags with pagination and filtering
   */
  async findAll(query: QueryTagsDto): Promise<TagListResponseDto> {
    const { search, isActive, page = 1, limit = 20 } = query;

    const where: Prisma.TagWhereInput = {};

    if (search) {
      where.OR = [
        { name: { contains: search, mode: 'insensitive' } },
        { slug: { contains: search, mode: 'insensitive' } },
      ];
    }

    if (isActive !== undefined) {
      where.isActive = isActive;
    }

    const skip = (page - 1) * limit;

    const [items, total] = await Promise.all([
      this.prisma.tag.findMany({
        where,
        skip,
        take: limit,
        orderBy: [{ sortOrder: 'asc' }, { name: 'asc' }],
        include: {
          _count: {
            select: { products: true },
          },
        },
      }),
      this.prisma.tag.count({ where }),
    ]);

    return {
      items: items.map(this.mapToResponseDto),
      total,
      page,
      limit,
      totalPages: Math.ceil(total / limit),
    };
  }

  /**
   * Find all active tags (for public use)
   */
  async findActive(): Promise<TagResponseDto[]> {
    const tags = await this.prisma.tag.findMany({
      where: { isActive: true },
      orderBy: [{ sortOrder: 'asc' }, { name: 'asc' }],
      include: {
        _count: {
          select: { products: true },
        },
      },
    });

    return tags.map(this.mapToResponseDto);
  }

  /**
   * Find a tag by ID
   */
  async findOne(id: string): Promise<TagResponseDto> {
    const tag = await this.prisma.tag.findUnique({
      where: { id },
      include: {
        _count: {
          select: { products: true },
        },
      },
    });

    if (!tag) {
      throw new NotFoundException(`Tag with ID '${id}' not found`);
    }

    return this.mapToResponseDto(tag);
  }

  /**
   * Find a tag by slug
   */
  async findBySlug(slug: string): Promise<TagResponseDto | null> {
    const tag = await this.prisma.tag.findUnique({
      where: { slug },
      include: {
        _count: {
          select: { products: true },
        },
      },
    });

    return tag ? this.mapToResponseDto(tag) : null;
  }

  /**
   * Update a tag
   */
  async update(
    id: string,
    updateDto: UpdateTagDto,
    userId?: string,
  ): Promise<TagResponseDto> {
    const existing = await this.prisma.tag.findUnique({
      where: { id },
    });

    if (!existing) {
      throw new NotFoundException(`Tag with ID '${id}' not found`);
    }

    // Check slug uniqueness if slug is being updated
    const updateSlug = (updateDto as unknown as { slug?: string }).slug;
    if (updateSlug && updateSlug !== existing.slug) {
      const duplicate = await this.prisma.tag.findUnique({
        where: { slug: updateSlug },
      });

      if (duplicate) {
        throw new ConflictException(`Tag with slug '${updateSlug}' already exists`);
      }
    }

    const tag = await this.prisma.tag.update({
      where: { id },
      data: {
        ...updateDto,
        updatedBy: userId,
      },
      include: {
        _count: {
          select: { products: true },
        },
      },
    });

    return this.mapToResponseDto(tag);
  }

  /**
   * Delete a tag
   */
  async remove(id: string): Promise<void> {
    const existing = await this.prisma.tag.findUnique({
      where: { id },
      include: {
        _count: {
          select: { products: true },
        },
      },
    });

    if (!existing) {
      throw new NotFoundException(`Tag with ID '${id}' not found`);
    }

    // Check if tag is in use
    const productCount = (existing as unknown as { _count: { products: number } })._count.products;
    if (productCount > 0) {
      throw new BadRequestException(
        `Cannot delete tag '${existing.name}' because it is assigned to ${productCount} product(s). Remove the tag from all products first.`,
      );
    }

    await this.prisma.tag.delete({
      where: { id },
    });
  }

  /**
   * Toggle tag active status
   */
  async toggleActive(id: string, userId?: string): Promise<TagResponseDto> {
    const existing = await this.prisma.tag.findUnique({
      where: { id },
      include: {
        _count: {
          select: { products: true },
        },
      },
    });

    if (!existing) {
      throw new NotFoundException(`Tag with ID '${id}' not found`);
    }

    const tag = await this.prisma.tag.update({
      where: { id },
      data: {
        isActive: !existing.isActive,
        updatedBy: userId,
      },
      include: {
        _count: {
          select: { products: true },
        },
      },
    });

    return this.mapToResponseDto(tag);
  }

  /**
   * Merge multiple tags into one
   */
  async merge(mergeDto: MergeTagsDto, userId?: string): Promise<{ merged: number; target: TagResponseDto }> {
    const { sourceTagIds, targetTagId } = mergeDto;

    // Validate target tag exists
    const targetTag = await this.prisma.tag.findUnique({
      where: { id: targetTagId },
    });

    if (!targetTag) {
      throw new NotFoundException(`Target tag with ID '${targetTagId}' not found`);
    }

    // Remove target from source list if present
    const filteredSourceIds = sourceTagIds.filter(id => id !== targetTagId);

    if (filteredSourceIds.length === 0) {
      throw new BadRequestException('No source tags to merge');
    }

    // Validate all source tags exist
    const sourceTags = await this.prisma.tag.findMany({
      where: { id: { in: filteredSourceIds } },
    });

    if (sourceTags.length !== filteredSourceIds.length) {
      throw new NotFoundException('One or more source tags not found');
    }

    // Get all product-tag relationships for source tags
    const productTags = await this.prisma.productTag.findMany({
      where: { tagId: { in: filteredSourceIds } },
    });

    // Transaction to merge tags
    await this.prisma.$transaction(async (tx) => {
      // 1. Update existing product-tag relationships to point to target
      // Use upsert to avoid unique constraint violations
      for (const pt of productTags) {
        await tx.productTag.upsert({
          where: {
            productId_tagId: {
              productId: pt.productId,
              tagId: targetTagId,
            },
          },
          update: {}, // Already exists, do nothing
          create: {
            productId: pt.productId,
            tagId: targetTagId,
          },
        });
      }

      // 2. Delete old product-tag relationships for source tags
      await tx.productTag.deleteMany({
        where: { tagId: { in: filteredSourceIds } },
      });

      // 3. Delete source tags
      await tx.tag.deleteMany({
        where: { id: { in: filteredSourceIds } },
      });

      // 4. Update target tag's updatedBy
      await tx.tag.update({
        where: { id: targetTagId },
        data: { updatedBy: userId },
      });
    });

    // Fetch updated target tag with new product count
    const updatedTarget = await this.prisma.tag.findUnique({
      where: { id: targetTagId },
      include: {
        _count: {
          select: { products: true },
        },
      },
    });

    return {
      merged: filteredSourceIds.length,
      target: this.mapToResponseDto(updatedTarget!),
    };
  }

  /**
   * Map database tag to response DTO
   */
  private mapToResponseDto(tag: unknown): TagResponseDto {
    const t = tag as {
      id: string;
      slug: string;
      name: string;
      description: string | null;
      color: string | null;
      icon: string | null;
      sortOrder: number;
      isActive: boolean;
      createdAt: Date;
      updatedAt: Date;
      _count?: { products: number };
    };

    return {
      id: t.id,
      slug: t.slug,
      name: t.name,
      description: t.description,
      color: t.color,
      icon: t.icon,
      sortOrder: t.sortOrder,
      isActive: t.isActive,
      productCount: t._count?.products || 0,
      createdAt: t.createdAt,
      updatedAt: t.updatedAt,
    };
  }
}
