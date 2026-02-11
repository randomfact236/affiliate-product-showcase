import {
  Injectable,
  NotFoundException,
  ConflictException,
  Inject,
} from "@nestjs/common";
import { PrismaService } from "../prisma/prisma.service";
import {
  CreateAttributeDto,
  UpdateAttributeDto,
} from "./dto/create-attribute.dto";
import { QueryAttributesDto } from "./dto/query-attributes.dto";
import { Redis } from "ioredis";
import { REDIS_CLIENT } from "../common/constants/injection-tokens";

const CACHE_PREFIX = "attributes:";
const CACHE_TTL = 300; // 5 minutes

@Injectable()
export class AttributeService {
  constructor(
    private prisma: PrismaService,
    @Inject(REDIS_CLIENT) private readonly redis: Redis,
  ) {}

  async create(dto: CreateAttributeDto) {
    const existing = await this.prisma.attribute.findUnique({
      where: { name: dto.name },
    });
    if (existing) {
      throw new ConflictException("Attribute with this name already exists");
    }

    const attribute = await this.prisma.attribute.create({
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
        options: {
          orderBy: { sortOrder: "asc" },
        },
        _count: {
          select: { values: true },
        },
      },
    });

    // Invalidate cache
    await this.invalidateCache();

    return attribute;
  }

  async findAll(query: QueryAttributesDto = {}) {
    const cacheKey = this.getCacheKey("list", JSON.stringify(query));

    // Check cache
    const cached = await this.redis.get(cacheKey);
    if (cached) {
      return JSON.parse(cached);
    }

    const where: any = {};

    if (query.search) {
      where.OR = [
        { name: { contains: query.search, mode: "insensitive" } },
        { displayName: { contains: query.search, mode: "insensitive" } },
      ];
    }

    if (query.type) {
      where.type = query.type;
    }

    if (query.isFilterable !== undefined) {
      where.isFilterable = query.isFilterable;
    }

    const includeOptions = query.includeOptions !== false;

    const [items, total] = await Promise.all([
      this.prisma.attribute.findMany({
        where,
        orderBy: { name: "asc" },
        skip: query.skip || 0,
        take: query.limit || 50,
        include: {
          options: includeOptions
            ? {
                orderBy: { sortOrder: "asc" },
              }
            : false,
          _count: {
            select: { values: true },
          },
        },
      }),
      this.prisma.attribute.count({ where }),
    ]);

    const result = {
      items,
      meta: {
        total,
        page: Math.floor((query.skip || 0) / (query.limit || 50)) + 1,
        limit: query.limit || 50,
        totalPages: Math.ceil(total / (query.limit || 50)),
      },
    };

    // Cache result
    await this.redis.setex(cacheKey, CACHE_TTL, JSON.stringify(result));

    return result;
  }

  async findOne(id: string) {
    const cacheKey = this.getCacheKey("detail", id);

    // Check cache
    const cached = await this.redis.get(cacheKey);
    if (cached) {
      return JSON.parse(cached);
    }

    const attribute = await this.prisma.attribute.findUnique({
      where: { id },
      include: {
        options: {
          orderBy: { sortOrder: "asc" },
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
      throw new NotFoundException("Attribute not found");
    }

    // Cache result
    await this.redis.setex(cacheKey, CACHE_TTL, JSON.stringify(attribute));

    return attribute;
  }

  async findByName(name: string) {
    const attribute = await this.prisma.attribute.findUnique({
      where: { name },
      include: {
        options: {
          orderBy: { sortOrder: "asc" },
        },
      },
    });

    if (!attribute) {
      throw new NotFoundException("Attribute not found");
    }

    return attribute;
  }

  async update(id: string, dto: UpdateAttributeDto) {
    await this.findOne(id);

    const attribute = await this.prisma.attribute.update({
      where: { id },
      data: dto,
      include: {
        options: true,
        _count: {
          select: { values: true },
        },
      },
    });

    // Invalidate cache
    await this.invalidateCache(id);

    return attribute;
  }

  async remove(id: string) {
    await this.findOne(id);

    await this.prisma.attribute.delete({ where: { id } });

    // Invalidate cache
    await this.invalidateCache(id);

    return { success: true, message: "Attribute deleted successfully" };
  }

  async getStats() {
    const cacheKey = this.getCacheKey("stats");

    // Check cache
    const cached = await this.redis.get(cacheKey);
    if (cached) {
      return JSON.parse(cached);
    }

    const [total, filterable, visible, byType] = await Promise.all([
      this.prisma.attribute.count(),
      this.prisma.attribute.count({ where: { isFilterable: true } }),
      this.prisma.attribute.count({ where: { isVisible: true } }),
      this.prisma.attribute.groupBy({
        by: ["type"],
        _count: {
          id: true,
        },
      }),
    ]);

    const result = {
      total,
      filterable,
      visible,
      byType: byType.reduce(
        (acc, curr) => ({
          ...acc,
          [curr.type]: curr._count.id,
        }),
        {},
      ),
    };

    // Cache result
    await this.redis.setex(cacheKey, CACHE_TTL, JSON.stringify(result));

    return result;
  }

  // Product Attribute Value methods
  async setProductAttribute(
    productId: string,
    attributeId: string,
    value: string,
  ) {
    // Verify attribute exists
    await this.findOne(attributeId);

    const result = await this.prisma.productAttributeValue.upsert({
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

    // Invalidate product cache
    await this.redis.del(`products:detail:${productId}`);

    return result;
  }

  async removeProductAttribute(productId: string, attributeId: string) {
    await this.prisma.productAttributeValue.delete({
      where: {
        productId_attributeId: {
          productId,
          attributeId,
        },
      },
    });

    // Invalidate product cache
    await this.redis.del(`products:detail:${productId}`);

    return { success: true, message: "Attribute value removed successfully" };
  }

  async getProductAttributes(productId: string) {
    return this.prisma.productAttributeValue.findMany({
      where: { productId },
      include: {
        attribute: {
          include: {
            options: true,
          },
        },
      },
    });
  }

  // Cache helper methods
  private getCacheKey(type: string, identifier?: string) {
    return identifier
      ? `${CACHE_PREFIX}${type}:${identifier}`
      : `${CACHE_PREFIX}${type}`;
  }

  private async invalidateCache(id?: string) {
    const keys = [
      `${CACHE_PREFIX}list:*`,
      `${CACHE_PREFIX}stats`,
      ...(id ? [`${CACHE_PREFIX}detail:${id}`] : []),
    ];

    for (const pattern of keys) {
      const matchingKeys = await this.redis.keys(pattern);
      if (matchingKeys.length > 0) {
        await this.redis.del(...matchingKeys);
      }
    }
  }
}
