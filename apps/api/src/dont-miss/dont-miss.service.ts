import { Injectable, NotFoundException } from '@nestjs/common';
import { PrismaService } from '../prisma/prisma.service';
import { CreateDontMissSectionDto, UpdateDontMissSectionDto } from './dto/dont-miss.dto';

@Injectable()
export class DontMissService {
  constructor(private prisma: PrismaService) {}

  // Generate unique shortcode
  private generateShortcode(name: string): string {
    const base = name
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, '_')
      .replace(/(^_|_$)/g, '');
    const random = Math.random().toString(36).substring(2, 6);
    return `dont_miss_${base}_${random}`;
  }

  // Create new Don't Miss section
  async create(dto: CreateDontMissSectionDto, userId?: string) {
    const shortcode = this.generateShortcode(dto.name);

    // Get max sort order
    const maxOrder = await this.prisma.dontMissSection.findFirst({
      orderBy: { sortOrder: 'desc' },
    });

    return this.prisma.dontMissSection.create({
      data: {
        name: dto.name,
        shortcode,
        title: dto.title,
        subtitle: dto.subtitle,
        layout: dto.layout || 'mixed',
        blogCount: dto.blogCount || 3,
        productCount: dto.productCount || 2,
        blogCategoryId: dto.blogCategoryId,
        productCategoryId: dto.productCategoryId,
        showViewAll: dto.showViewAll ?? true,
        sortBy: dto.sortBy || 'latest',
        backgroundColor: dto.backgroundColor,
        textColor: dto.textColor,
        isActive: dto.isActive ?? true,
        sortOrder: (maxOrder?.sortOrder || 0) + 1,
        createdBy: userId,
      },
    });
  }

  // Find all sections
  async findAll(includeInactive = false) {
    return this.prisma.dontMissSection.findMany({
      where: includeInactive ? {} : { isActive: true },
      orderBy: { sortOrder: 'asc' },
    });
  }

  // Find by ID
  async findById(id: string) {
    const section = await this.prisma.dontMissSection.findUnique({
      where: { id },
    });
    if (!section) {
      throw new NotFoundException(`Don't Miss section with id "${id}" not found`);
    }
    return section;
  }

  // Find by shortcode
  async findByShortcode(shortcode: string) {
    const section = await this.prisma.dontMissSection.findUnique({
      where: { shortcode },
    });
    if (!section) {
      throw new NotFoundException(`Don't Miss section with shortcode "${shortcode}" not found`);
    }
    return section;
  }

  // Update section
  async update(id: string, dto: UpdateDontMissSectionDto) {
    await this.findById(id); // Verify exists

    return this.prisma.dontMissSection.update({
      where: { id },
      data: {
        name: dto.name,
        title: dto.title,
        subtitle: dto.subtitle,
        layout: dto.layout,
        blogCount: dto.blogCount,
        productCount: dto.productCount,
        blogCategoryId: dto.blogCategoryId,
        productCategoryId: dto.productCategoryId,
        showViewAll: dto.showViewAll,
        sortBy: dto.sortBy,
        backgroundColor: dto.backgroundColor,
        textColor: dto.textColor,
        isActive: dto.isActive,
        sortOrder: dto.sortOrder,
      },
    });
  }

  // Delete section
  async remove(id: string) {
    await this.findById(id); // Verify exists
    return this.prisma.dontMissSection.delete({
      where: { id },
    });
  }

  // Reorder sections
  async reorder(ids: string[]) {
    const updates = ids.map((id, index) =>
      this.prisma.dontMissSection.update({
        where: { id },
        data: { sortOrder: index },
      })
    );
    await this.prisma.$transaction(updates);
    return { message: 'Sections reordered' };
  }

  // Duplicate section
  async duplicate(id: string, userId?: string) {
    const original = await this.findById(id);
    
    return this.create({
      name: `${original.name} (Copy)`,
      title: `${original.title} (Copy)`,
      subtitle: original.subtitle || undefined,
      layout: original.layout,
      blogCount: original.blogCount,
      productCount: original.productCount,
      blogCategoryId: original.blogCategoryId || undefined,
      productCategoryId: original.productCategoryId || undefined,
      showViewAll: original.showViewAll,
      sortBy: original.sortBy,
      backgroundColor: original.backgroundColor || undefined,
      textColor: original.textColor || undefined,
      isActive: false, // Inactive by default
    }, userId);
  }

  // Get content for a section (blogs and products)
  async getSectionContent(shortcode: string) {
    const section = await this.findByShortcode(shortcode);
    
    // This would fetch actual blogs and products based on section config
    // For now, return the section config
    return {
      section,
      blogs: [], // Would fetch based on blogCategoryId, blogCount, sortBy
      products: [], // Would fetch based on productCategoryId, productCount, sortBy
    };
  }

  // Get all shortcodes for shortcode registry
  async getAllShortcodes() {
    const sections = await this.findAll(true);
    return sections.map((section) => ({
      tag: section.shortcode,
      description: `Don't Miss section: ${section.name}`,
      sectionId: section.id,
    }));
  }
}
