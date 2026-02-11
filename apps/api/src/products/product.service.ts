import {
  Injectable,
  NotFoundException,
  ConflictException,
  Inject,
} from "@nestjs/common";
import { PrismaService } from "../prisma/prisma.service";
import { CreateProductDto, UpdateProductDto, ProductFilterDto } from "./dto";
import { Prisma, RibbonPosition, ProductStatus } from "@prisma/client";
import { REDIS_CLIENT } from "../common/constants/injection-tokens";
import type { Redis } from "ioredis";

// Pagination limits to prevent DoS
const DEFAULT_PAGE = 1;
const DEFAULT_LIMIT = 10;
const MAX_LIMIT = 100;

// Reserved slugs that cannot be used
const RESERVED_SLUGS = [
  "admin",
  "api",
  "auth",
  "login",
  "logout",
  "register",
  "health",
  "docs",
  "swagger",
];

@Injectable()
export class ProductService {
  constructor(
    private prisma: PrismaService,
    @Inject(REDIS_CLIENT) private readonly redis: Redis,
  ) {}

  async create(dto: CreateProductDto, userId: string) {
    return this.prisma.$transaction(async (tx) => {
      // Generate unique slug with collision handling
      const slug = await this.generateUniqueSlug(dto.name, tx);

      // Fetch ribbons if ribbonIds provided (for denormalized data)
      let ribbonData: Map<string, { name: string; label: string; textColor: string; bgColor: string; position: string; sortOrder: number }> = new Map();
      if (dto.ribbonIds?.length) {
        const ribbons = await tx.ribbon.findMany({
          where: { id: { in: dto.ribbonIds } },
        });
        ribbonData = new Map(ribbons.map(r => [r.id, r]));
      }

      return tx.product.create({
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
              options: v.options || undefined,
              isDefault: v.isDefault,
            })),
          },
          categories: {
            create: dto.categoryIds?.map((id) => ({ categoryId: id })),
          },
          tags: {
            create: dto.tagIds?.map((id) => ({ tagId: id })),
          },
          ribbons: {
            create: dto.ribbonIds
              ?.filter(id => ribbonData.has(id))
              .map((ribbonId) => {
                const ribbon = ribbonData.get(ribbonId)!;
                return {
                  ribbon: { connect: { id: ribbonId } },
                  name: ribbon.label,
                  color: ribbon.textColor,
                  bgColor: ribbon.bgColor,
                  position: ribbon.position as RibbonPosition,
                  priority: ribbon.sortOrder ?? 0,
                };
              }),
          },
        },
        include: {
          variants: true,
          categories: { include: { category: true } },
          images: true,
        },
      });
    });
  }

  async findAll(filters: ProductFilterDto) {
    const where: Prisma.ProductWhereInput = {
      deletedAt: null, // Only return non-deleted products
    };

    if (filters.status) where.status = filters.status;
    if (filters.categoryId) {
      where.categories = { some: { categoryId: filters.categoryId } };
    }
    if (filters.search) {
      where.OR = [
        { name: { contains: filters.search, mode: "insensitive" } },
        { description: { contains: filters.search, mode: "insensitive" } },
      ];
    }

    // Enforce pagination limits to prevent DoS
    const page = Math.max(1, filters.page || DEFAULT_PAGE);
    const limit = Math.min(
      MAX_LIMIT,
      Math.max(1, filters.limit || DEFAULT_LIMIT),
    );
    const skip = (page - 1) * limit;

    const [products, total] = await Promise.all([
      this.prisma.product.findMany({
        where,
        include: {
          variants: { where: { isDefault: true } },
          images: { where: { isPrimary: true } },
          categories: { include: { category: true } },
          ribbons: {
            where: {
              OR: [{ endAt: null }, { endAt: { gt: new Date() } }],
            },
            include: { ribbon: true },
          },
        },
        skip,
        take: limit,
        orderBy: this.buildOrderBy(filters.sortBy, filters.sortOrder),
      }),
      this.prisma.product.count({ where }),
    ]);

    return {
      data: products,
      meta: {
        page,
        limit,
        total,
        totalPages: Math.ceil(total / limit),
      },
    };
  }

  async findOne(id: string) {
    const cacheKey = `product:${id}`;
    const cached = await this.redis.get(cacheKey);

    if (cached) {
      return JSON.parse(cached);
    }

    const product = await this.prisma.product.findUnique({
      where: { id, deletedAt: null },
      include: {
        variants: true,
        categories: { include: { category: true } },
        tags: { include: { tag: true } },
        attributes: { include: { attribute: true } },
        images: { orderBy: { sortOrder: "asc" } },
        ribbons: {
          where: { OR: [{ endAt: null }, { endAt: { gt: new Date() } }] },
          include: { ribbon: true },
        },
      },
    });

    if (!product) {
      throw new NotFoundException("Product not found");
    }

    // Cache for 5 minutes
    await this.redis.setex(cacheKey, 300, JSON.stringify(product));

    return product;
  }

  async findBySlug(slug: string) {
    const cacheKey = `product:slug:${slug}`;
    const cached = await this.redis.get(cacheKey);

    if (cached) {
      return JSON.parse(cached);
    }

    const product = await this.prisma.product.findUnique({
      where: { slug, deletedAt: null },
      include: {
        variants: true,
        categories: { include: { category: true } },
        tags: { include: { tag: true } },
        attributes: { include: { attribute: true } },
        images: { orderBy: { sortOrder: "asc" } },
        ribbons: {
          where: { OR: [{ endAt: null }, { endAt: { gt: new Date() } }] },
          include: { ribbon: true },
        },
      },
    });

    if (!product) {
      throw new NotFoundException("Product not found");
    }

    await this.redis.setex(cacheKey, 300, JSON.stringify(product));

    return product;
  }

  async update(id: string, dto: UpdateProductDto, userId: string) {
    // Get existing product first to check slug changes
    const existing = await this.findOne(id);

    // Invalidate caches BEFORE updating to prevent stale data
    await this.invalidateProductCache(id, existing.slug);

    // Build update data with only provided fields
    const updateData: Prisma.ProductUpdateInput = {
      updater: { connect: { id: userId } },
    };

    const dtoAny = dto as unknown as Record<string, unknown>;
    if (dtoAny.name !== undefined) updateData.name = dtoAny.name as string;
    if (dtoAny.description !== undefined) updateData.description = dtoAny.description as string;
    if (dtoAny.shortDescription !== undefined) updateData.shortDescription = dtoAny.shortDescription as string;
    if (dtoAny.status !== undefined) updateData.status = dtoAny.status as ProductStatus;
    if (dtoAny.metaTitle !== undefined) updateData.metaTitle = dtoAny.metaTitle as string;
    if (dtoAny.metaDescription !== undefined) updateData.metaDescription = dtoAny.metaDescription as string;

    const updated = await this.prisma.product.update({
      where: { id },
      data: updateData,
      include: {
        variants: true,
        categories: { include: { category: true } },
      },
    });

    return updated;
  }

  async remove(id: string) {
    const product = await this.findOne(id);

    // Invalidate cache before soft delete
    await this.invalidateProductCache(id, product.slug);

    // Soft delete instead of hard delete
    await this.prisma.product.update({
      where: { id },
      data: {
        deletedAt: new Date(),
        status: "ARCHIVED",
      },
    });

    return { message: "Product deleted successfully" };
  }

  async incrementViewCount(id: string) {
    // Use atomic increment - Phase 4 will move this to RabbitMQ
    return this.prisma.product.update({
      where: { id },
      data: { viewCount: { increment: 1 } },
    });
  }

  /**
   * Invalidate product caches
   */
  private async invalidateProductCache(id: string, slug: string) {
    const pipeline = this.redis.pipeline();
    pipeline.del(`product:${id}`);
    pipeline.del(`product:slug:${slug}`);
    // Also invalidate list caches if implemented
    await pipeline.exec();
  }

  /**
   * Generate a URL-friendly slug from a name
   */
  private slugify(name: string): string {
    return name
      .toLowerCase()
      .trim()
      .replace(/[^\w\s-]/g, "") // Remove special characters
      .replace(/[\s_-]+/g, "-") // Replace spaces and underscores with hyphens
      .replace(/^-+|-+$/g, ""); // Remove leading/trailing hyphens
  }

  /**
   * Generate unique slug with collision handling and reserved word check
   */
  private async generateUniqueSlug(
    name: string,
    tx: Prisma.TransactionClient,
  ): Promise<string> {
    const baseSlug = this.slugify(name);

    // Check for reserved slugs
    if (RESERVED_SLUGS.includes(baseSlug)) {
      throw new ConflictException(`The name "${name}" uses a reserved word`);
    }

    let slug = baseSlug;
    let counter = 1;

    // Find unique slug
    while (await tx.product.findUnique({ where: { slug } })) {
      slug = `${baseSlug}-${counter}`;
      counter++;

      // Prevent infinite loops with suspiciously high collision counts
      if (counter > 1000) {
        throw new ConflictException("Unable to generate unique slug");
      }
    }

    return slug;
  }

  /**
   * Generate SKU from slug and variant options
   */
  private generateSku(
    slug: string,
    variant: { options?: Record<string, string> },
  ): string {
    const variantSuffix = Object.values(variant.options || {})
      .join("-")
      .toUpperCase();
    const timestamp = Date.now().toString(36).slice(-4); // Last 4 chars of base36 timestamp
    return `${slug.toUpperCase().slice(0, 10)}-${variantSuffix || "STD"}-${timestamp}`;
  }

  /**
   * Build safe orderBy clause with field validation
   */
  private buildOrderBy(
    sortBy?: string,
    sortOrder: "asc" | "desc" = "desc",
  ): Prisma.ProductOrderByWithRelationInput {
    const allowedSortFields: Record<
      string,
      Prisma.ProductOrderByWithRelationInput
    > = {
      name: { name: sortOrder },
      createdAt: { createdAt: sortOrder },
      updatedAt: { updatedAt: sortOrder },
      status: { status: sortOrder },
      viewCount: { viewCount: sortOrder },
    };

    // Only allow explicitly defined sort fields
    if (sortBy && allowedSortFields[sortBy]) {
      return allowedSortFields[sortBy];
    }

    // Default sort by createdAt
    return { createdAt: "desc" };
  }
}
