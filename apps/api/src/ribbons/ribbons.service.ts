import {
  Injectable,
  NotFoundException,
  ConflictException,
  BadRequestException,
} from '@nestjs/common';
import { PrismaService } from '../prisma/prisma.service';
import {
  CreateRibbonDto,
  UpdateRibbonDto,
  QueryRibbonsDto,
  RibbonListResponseDto,
  RibbonResponseDto,
} from './dto';
import { Prisma } from '@prisma/client';

@Injectable()
export class RibbonsService {
  constructor(private readonly prisma: PrismaService) {}

  /**
   * Create a new ribbon template
   */
  async create(createDto: CreateRibbonDto, userId?: string): Promise<RibbonResponseDto> {
    // Check for duplicate name
    const existing = await this.prisma.ribbon.findUnique({
      where: { name: createDto.name },
    });

    if (existing) {
      throw new ConflictException(`Ribbon with name '${createDto.name}' already exists`);
    }

    const ribbon = await this.prisma.ribbon.create({
      data: {
        ...createDto,
        createdBy: userId,
      },
    });

    return this.mapToResponseDto(ribbon);
  }

  /**
   * Find all ribbons with pagination and filtering
   */
  async findAll(query: QueryRibbonsDto): Promise<RibbonListResponseDto> {
    const { search, position, isActive, page = 1, limit = 20 } = query;

    const where: Prisma.RibbonWhereInput = {};

    if (search) {
      where.OR = [
        { name: { contains: search, mode: 'insensitive' } },
        { label: { contains: search, mode: 'insensitive' } },
      ];
    }

    if (position) {
      where.position = position;
    }

    if (isActive !== undefined) {
      where.isActive = isActive;
    }

    const skip = (page - 1) * limit;

    const [items, total] = await Promise.all([
      this.prisma.ribbon.findMany({
        where,
        skip,
        take: limit,
        orderBy: [{ sortOrder: 'asc' }, { name: 'asc' }],
      }),
      this.prisma.ribbon.count({ where }),
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
   * Find all active ribbons (for public use)
   */
  async findActive(): Promise<RibbonResponseDto[]> {
    const ribbons = await this.prisma.ribbon.findMany({
      where: { isActive: true },
      orderBy: [{ sortOrder: 'asc' }, { name: 'asc' }],
    });

    return ribbons.map(this.mapToResponseDto);
  }

  /**
   * Find a ribbon by ID
   */
  async findOne(id: string): Promise<RibbonResponseDto> {
    const ribbon = await this.prisma.ribbon.findUnique({
      where: { id },
      include: {
        _count: {
          select: { products: true },
        },
      },
    });

    if (!ribbon) {
      throw new NotFoundException(`Ribbon with ID '${id}' not found`);
    }

    return this.mapToResponseDto(ribbon);
  }

  /**
   * Find a ribbon by name
   */
  async findByName(name: string): Promise<RibbonResponseDto | null> {
    const ribbon = await this.prisma.ribbon.findUnique({
      where: { name },
    });

    return ribbon ? this.mapToResponseDto(ribbon) : null;
  }

  /**
   * Update a ribbon
   */
  async update(
    id: string,
    updateDto: UpdateRibbonDto,
    userId?: string,
  ): Promise<RibbonResponseDto> {
    const existing = await this.prisma.ribbon.findUnique({
      where: { id },
    });

    if (!existing) {
      throw new NotFoundException(`Ribbon with ID '${id}' not found`);
    }

    // Check name uniqueness if name is being updated
    const updateName = (updateDto as unknown as { name?: string }).name;
    if (updateName && updateName !== existing.name) {
      const duplicate = await this.prisma.ribbon.findUnique({
        where: { name: updateName },
      });

      if (duplicate) {
        throw new ConflictException(`Ribbon with name '${updateName}' already exists`);
      }
    }

    const ribbon = await this.prisma.ribbon.update({
      where: { id },
      data: {
        ...updateDto,
        updatedBy: userId,
      },
    });

    return this.mapToResponseDto(ribbon);
  }

  /**
   * Delete a ribbon (soft delete not applicable for config table)
   */
  async remove(id: string): Promise<void> {
    const existing = await this.prisma.ribbon.findUnique({
      where: { id },
      include: {
        _count: {
          select: { products: true },
        },
      },
    });

    if (!existing) {
      throw new NotFoundException(`Ribbon with ID '${id}' not found`);
    }

    // Check if ribbon is in use
    const productCount = (existing as unknown as { _count: { products: number } })._count.products;
    if (productCount > 0) {
      throw new BadRequestException(
        `Cannot delete ribbon '${existing.name}' because it is assigned to ${productCount} product(s). Remove the ribbon from all products first.`,
      );
    }

    await this.prisma.ribbon.delete({
      where: { id },
    });
  }

  /**
   * Toggle ribbon active status
   */
  async toggleActive(id: string, userId?: string): Promise<RibbonResponseDto> {
    const existing = await this.prisma.ribbon.findUnique({
      where: { id },
    });

    if (!existing) {
      throw new NotFoundException(`Ribbon with ID '${id}' not found`);
    }

    const ribbon = await this.prisma.ribbon.update({
      where: { id },
      data: {
        isActive: !existing.isActive,
        updatedBy: userId,
      },
    });

    return this.mapToResponseDto(ribbon);
  }

  /**
   * Map database ribbon to response DTO
   */
  private mapToResponseDto(ribbon: unknown): RibbonResponseDto {
    const r = ribbon as {
      id: string;
      name: string;
      label: string;
      description: string | null;
      bgColor: string;
      textColor: string;
      icon: string | null;
      position: string;
      sortOrder: number;
      isActive: boolean;
      createdAt: Date;
      updatedAt: Date;
      createdBy: string | null;
      updatedBy: string | null;
    };

    return {
      id: r.id,
      name: r.name,
      label: r.label,
      description: r.description,
      bgColor: r.bgColor,
      textColor: r.textColor,
      icon: r.icon,
      position: r.position as RibbonResponseDto['position'],
      sortOrder: r.sortOrder,
      isActive: r.isActive,
      createdAt: r.createdAt,
      updatedAt: r.updatedAt,
      createdBy: r.createdBy,
      updatedBy: r.updatedBy,
    };
  }
}
