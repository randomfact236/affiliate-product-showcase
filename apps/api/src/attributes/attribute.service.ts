import { Injectable, NotFoundException, ConflictException } from '@nestjs/common';
import { PrismaService } from '../prisma/prisma.service';
import { CreateAttributeDto, UpdateAttributeDto } from './dto/create-attribute.dto';

@Injectable()
export class AttributeService {
  constructor(private prisma: PrismaService) {}

  async create(dto: CreateAttributeDto) {
    const existing = await this.prisma.attribute.findUnique({
      where: { name: dto.name },
    });
    if (existing) {
      throw new ConflictException('Attribute with this name already exists');
    }

    return this.prisma.attribute.create({
      data: {
        name: dto.name,
        displayName: dto.displayName,
        type: dto.type,
        isFilterable: dto.isFilterable,
        isVisible: dto.isVisible,
        options: {
          create: dto.options,
        },
      },
      include: {
        options: true,
      },
    });
  }

  async findAll() {
    return this.prisma.attribute.findMany({
      orderBy: { name: 'asc' },
      include: {
        options: {
          orderBy: { sortOrder: 'asc' },
        },
        _count: {
          select: { values: true },
        },
      },
    });
  }

  async findOne(id: string) {
    const attribute = await this.prisma.attribute.findUnique({
      where: { id },
      include: {
        options: {
          orderBy: { sortOrder: 'asc' },
        },
        values: {
          include: {
            product: {
              select: {
                id: true,
                name: true,
                slug: true,
              },
            },
          },
        },
      },
    });

    if (!attribute) {
      throw new NotFoundException('Attribute not found');
    }

    return attribute;
  }

  async update(id: string, dto: UpdateAttributeDto) {
    await this.findOne(id);

    return this.prisma.attribute.update({
      where: { id },
      data: dto,
      include: {
        options: true,
      },
    });
  }

  async remove(id: string) {
    await this.findOne(id);

    return this.prisma.attribute.delete({ where: { id } });
  }

  // Product Attribute Value methods
  async setProductAttribute(productId: string, attributeId: string, value: string) {
    return this.prisma.productAttributeValue.upsert({
      where: {
        productId_attributeId: {
          productId,
          attributeId,
        },
      },
      create: {
        productId,
        attributeId,
        value,
      },
      update: {
        value,
      },
    });
  }

  async removeProductAttribute(productId: string, attributeId: string) {
    return this.prisma.productAttributeValue.delete({
      where: {
        productId_attributeId: {
          productId,
          attributeId,
        },
      },
    });
  }
}
