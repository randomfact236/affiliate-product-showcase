# Phase 3: Backend Core - Product Catalog

**Duration**: 2 weeks  
**Goal**: Complete product management API with taxonomy  
**Prerequisites**: Phase 2 complete (Auth & RBAC)

---

## Week 1: Database & Core Product API

### Day 1-2: Database Schema Expansion

#### Tasks
- [ ] Design product entity schema
- [ ] Design category schema (nested)
- [ ] Design tag and attribute schemas
- [ ] Create Prisma migrations

#### prisma/schema.prisma (Additions)
```prisma
// Product Catalog
model Product {
  id          String   @id @default(cuid())
  slug        String   @unique
  name        String
  description String?
  shortDescription String?
  
  // Status
  status      ProductStatus @default(DRAFT)
  publishedAt DateTime?
  
  // SEO
  metaTitle       String?
  metaDescription String?
  
  // Relations
  variants    ProductVariant[]
  categories  ProductCategory[]
  tags        ProductTag[]
  attributes  ProductAttributeValue[]
  images      ProductImage[]
  ribbons     ProductRibbon[]
  
  // Analytics
  viewCount   Int      @default(0)
  
  // Timestamps
  createdAt   DateTime @default(now())
  updatedAt   DateTime @updatedAt
  createdBy   String
  updatedBy   String?
  
  @@index([slug])
  @@index([status])
  @@index([createdAt])
  @@map("products")
}

model ProductVariant {
  id          String   @id @default(cuid())
  productId   String
  sku         String   @unique
  name        String
  
  // Pricing (in cents)
  price       Int
  comparePrice Int?    // Original price for "sale" display
  costPrice   Int?     // For margin calculations
  
  // Inventory
  inventory   Int      @default(0)
  inventoryPolicy InventoryPolicy @default(DENY)
  
  // Options (e.g., {"color": "red", "size": "L"})
  options     Json?
  
  // Relations
  product     Product  @relation(fields: [productId], references: [id], onDelete: Cascade)
  
  // Status
  isDefault   Boolean  @default(false)
  status      VariantStatus @default(ACTIVE)
  
  createdAt   DateTime @default(now())
  updatedAt   DateTime @updatedAt
  
  @@index([productId])
  @@index([sku])
  @@map("product_variants")
}

model Category {
  id          String   @id @default(cuid())
  slug        String   @unique
  name        String
  description String?
  
  // Tree structure (Nested Set Model)
  left        Int
  right       Int
  depth       Int      @default(0)
  parentId    String?
  
  // SEO
  metaTitle       String?
  metaDescription String?
  
  // Media
  image       String?
  
  // Relations
  parent      Category? @relation("CategoryTree", fields: [parentId], references: [id])
  children    Category[] @relation("CategoryTree")
  products    ProductCategory[]
  
  // Status
  isActive    Boolean  @default(true)
  sortOrder   Int      @default(0)
  
  createdAt   DateTime @default(now())
  updatedAt   DateTime @updatedAt
  
  @@index([left, right])
  @@index([parentId])
  @@index([slug])
  @@map("categories")
}

model ProductCategory {
  productId  String
  categoryId String
  
  product  Product  @relation(fields: [productId], references: [id], onDelete: Cascade)
  category Category @relation(fields: [categoryId], references: [id], onDelete: Cascade)
  
  @@id([productId, categoryId])
  @@map("product_categories")
}

model Tag {
  id        String   @id @default(cuid())
  slug      String   @unique
  name      String
  
  products  ProductTag[]
  
  createdAt DateTime @default(now())
  
  @@map("tags")
}

model ProductTag {
  productId String
  tagId     String
  
  product Product @relation(fields: [productId], references: [id], onDelete: Cascade)
  tag     Tag     @relation(fields: [tagId], references: [id], onDelete: Cascade)
  
  @@id([productId, tagId])
  @@map("product_tags")
}

model Attribute {
  id          String   @id @default(cuid())
  name        String   @unique
  displayName String
  type        AttributeType
  
  // For SELECT type
  options     AttributeOption[]
  
  // Configuration
  isFilterable Boolean @default(false)
  isVisible   Boolean @default(true)
  
  values      ProductAttributeValue[]
  
  createdAt   DateTime @default(now())
  
  @@map("attributes")
}

model AttributeOption {
  id          String @id @default(cuid())
  attributeId String
  value       String
  displayValue String
  sortOrder   Int    @default(0)
  
  attribute Attribute @relation(fields: [attributeId], references: [id], onDelete: Cascade)
  
  @@map("attribute_options")
}

model ProductAttributeValue {
  id          String @id @default(cuid())
  productId   String
  attributeId String
  value       String // Can be JSON for complex types
  
  product   Product   @relation(fields: [productId], references: [id], onDelete: Cascade)
  attribute Attribute @relation(fields: [attributeId], references: [id], onDelete: Cascade)
  
  @@unique([productId, attributeId])
  @@map("product_attribute_values")
}

model ProductImage {
  id        String @id @default(cuid())
  productId String
  url       String
  alt       String?
  sortOrder Int    @default(0)
  
  // Variants
  isPrimary Boolean @default(false)
  
  product Product @relation(fields: [productId], references: [id], onDelete: Cascade)
  
  createdAt DateTime @default(now())
  
  @@index([productId])
  @@map("product_images")
}

model ProductRibbon {
  id          String @id @default(cuid())
  productId   String
  name        String
  color       String    // CSS color or tailwind class
  bgColor     String
  position    RibbonPosition @default(TOP_RIGHT)
  priority    Int       @default(0)
  startAt     DateTime?
  endAt       DateTime?
  
  product Product @relation(fields: [productId], references: [id], onDelete: Cascade)
  
  @@map("product_ribbons")
}

enum ProductStatus {
  DRAFT
  PENDING_REVIEW
  PUBLISHED
  ARCHIVED
}

enum VariantStatus {
  ACTIVE
  INACTIVE
  OUT_OF_STOCK
}

enum InventoryPolicy {
  DENY      // Don't allow purchase if out of stock
  CONTINUE  // Allow backorders
}

enum AttributeType {
  TEXT
  NUMBER
  BOOLEAN
  SELECT
  MULTI_SELECT
  DATE
}

enum RibbonPosition {
  TOP_LEFT
  TOP_RIGHT
  BOTTOM_LEFT
  BOTTOM_RIGHT
}
```

### Day 3-4: Product Module Structure

#### Tasks
- [ ] Create ProductModule
- [ ] Create ProductService with CRUD
- [ ] Create ProductController
- [ ] Implement DTOs with validation

#### apps/api/src/products/product.service.ts
```typescript
import { Injectable, NotFoundException } from '@nestjs/common';
import { PrismaService } from '../prisma/prisma.service';
import { CreateProductDto, UpdateProductDto, ProductFilterDto } from './dto';

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
        ...dto,
        slug,
        createdBy: userId,
        variants: {
          create: dto.variants?.map((v) => ({
            ...v,
            sku: v.sku || this.generateSku(slug, v),
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
    
    const [products, total] = await Promise.all([
      this.prisma.product.findMany({
        where,
        include: {
          variants: { where: { isDefault: true } },
          images: { where: { isPrimary: true } },
          categories: { include: { category: true } },
        },
        skip: (filters.page - 1) * filters.limit,
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

  async update(id: string, dto: UpdateProductDto, userId: string) {
    await this.findOne(id); // Verify exists
    
    return this.prisma.product.update({
      where: { id },
      data: {
        ...dto,
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

  private generateSlug(name: string): string {
    return name
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '');
  }

  private generateSku(slug: string, variant: CreateVariantDto): string {
    const variantSuffix = Object.values(variant.options || {})
      .join('-')
      .toUpperCase();
    return `${slug.toUpperCase()}-${variantSuffix || 'DEFAULT'}-${Date.now().toString(36)}`;
  }
}
```

### Day 5: Category Tree Implementation

#### Tasks
- [ ] Implement nested set model operations
- [ ] Create CategoryService with tree methods
- [ ] Add tree traversal utilities

#### apps/api/src/categories/category.service.ts
```typescript
import { Injectable } from '@nestjs/common';
import { PrismaService } from '../prisma/prisma.service';
import { CreateCategoryDto, UpdateCategoryDto } from './dto';

@Injectable()
export class CategoryService {
  constructor(private prisma: PrismaService) {}

  async create(dto: CreateCategoryDto) {
    const { parentId, ...data } = dto;
    
    if (parentId) {
      // Insert as child using nested set
      return this.insertAsChild(parentId, data);
    }
    
    // Insert as root
    const maxRight = await this.prisma.category.aggregate({
      _max: { right: true },
    });
    
    const left = (maxRight._max.right || 0) + 1;
    
    return this.prisma.category.create({
      data: {
        ...data,
        left,
        right: left + 1,
        depth: 0,
      },
    });
  }

  async findTree() {
    const categories = await this.prisma.category.findMany({
      where: { isActive: true },
      orderBy: { left: 'asc' },
    });
    
    return this.buildTree(categories);
  }

  async findDescendants(categoryId: string) {
    const category = await this.prisma.category.findUnique({
      where: { id: categoryId },
    });
    
    if (!category) return [];
    
    return this.prisma.category.findMany({
      where: {
        left: { gt: category.left },
        right: { lt: category.right },
      },
      orderBy: { left: 'asc' },
    });
  }

  private async insertAsChild(parentId: string, data: CreateCategoryDto) {
    const parent = await this.prisma.category.findUnique({
      where: { id: parentId },
    });
    
    if (!parent) throw new NotFoundException('Parent category not found');
    
    // Make space in the tree
    await this.prisma.$transaction([
      this.prisma.category.updateMany({
        where: { right: { gte: parent.right } },
        data: { right: { increment: 2 } },
      }),
      this.prisma.category.updateMany({
        where: { left: { gt: parent.right } },
        data: { left: { increment: 2 } },
      }),
    ]);
    
    // Insert new node
    return this.prisma.category.create({
      data: {
        ...data,
        left: parent.right,
        right: parent.right + 1,
        depth: parent.depth + 1,
        parentId,
      },
    });
  }

  private buildTree(categories: Category[]): CategoryTreeNode[] {
    const tree: CategoryTreeNode[] = [];
    const stack: CategoryTreeNode[] = [];
    
    for (const category of categories) {
      const node: CategoryTreeNode = { ...category, children: [] };
      
      while (stack.length > 0 && stack[stack.length - 1].right < category.left) {
        stack.pop();
      }
      
      if (stack.length === 0) {
        tree.push(node);
      } else {
        stack[stack.length - 1].children.push(node);
      }
      
      stack.push(node);
    }
    
    return tree;
  }
}
```

---

## Week 2: Advanced Features & Import/Export

### Day 6-7: Import/Export System

#### Tasks
- [ ] Create CSV import service
- [ ] Implement bulk operations
- [ ] Add validation and error reporting
- [ ] Create background job structure

#### apps/api/src/products/import/product-import.service.ts
```typescript
import { Injectable } from '@nestjs/common';
import { parse } from 'csv-parse/sync';
import { PrismaService } from '../../prisma/prisma.service';
import { QueueService } from '../../queue/queue.service';

@Injectable()
export class ProductImportService {
  constructor(
    private prisma: PrismaService,
    private queue: QueueService,
  ) {}

  async queueImport(fileBuffer: Buffer, userId: string): Promise<string> {
    const jobId = `import-${Date.now()}`;
    
    await this.queue.add('product-import', {
      jobId,
      fileBuffer: fileBuffer.toString('base64'),
      userId,
    });
    
    return jobId;
  }

  async processImport(data: ImportJobData): Promise<ImportResult> {
    const { fileBuffer, userId } = data;
    const csv = Buffer.from(fileBuffer, 'base64').toString();
    
    const records = parse(csv, {
      columns: true,
      skip_empty_lines: true,
    });
    
    const results: ImportResult = {
      total: records.length,
      created: 0,
      updated: 0,
      errors: [],
    };
    
    for (let i = 0; i < records.length; i++) {
      try {
        await this.processRow(records[i], userId);
        results.created++;
      } catch (error) {
        results.errors.push({
          row: i + 2, // +2 for header and 0-index
          message: error.message,
          data: records[i],
        });
      }
    }
    
    return results;
  }

  private async processRow(row: Record<string, string>, userId: string) {
    // Validation
    if (!row.name || !row.price) {
      throw new Error('Name and price are required');
    }
    
    const price = parseInt(row.price, 10);
    if (isNaN(price) || price < 0) {
      throw new Error('Invalid price');
    }
    
    // Create or update
    const slug = this.generateSlug(row.name);
    
    await this.prisma.product.upsert({
      where: { slug },
      create: {
        name: row.name,
        slug,
        description: row.description,
        status: row.status as ProductStatus || 'DRAFT',
        createdBy: userId,
        variants: {
          create: [{
            name: 'Default',
            sku: row.sku || slug.toUpperCase(),
            price,
            isDefault: true,
          }],
        },
      },
      update: {
        description: row.description,
        variants: {
          updateMany: {
            where: { isDefault: true },
            data: { price },
          },
        },
      },
    });
  }
}
```

### Day 8-9: Tag & Attribute Management

#### Tasks
- [ ] Tag CRUD API
- [ ] Attribute CRUD API
- [ ] Product-tag association
- [ ] Product-attribute value management

### Day 10: Testing & Documentation

#### Tasks
- [ ] Unit tests for ProductService
- [ ] Integration tests for ProductController
- [ ] API documentation updates
- [ ] Sample data seeding

---

## Deliverables Checklist

- [ ] Product CRUD API with variants
- [ ] Category tree management (nested set)
- [ ] Tag system
- [ ] Attribute system with multiple types
- [ ] Product ribbons/badges
- [ ] CSV import/export (with background jobs)
- [ ] Image association structure
- [ ] Comprehensive tests

## API Endpoints

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | /api/v1/products | No | List products (with filters) |
| GET | /api/v1/products/:id | No | Get product detail |
| POST | /api/v1/products | Admin | Create product |
| PUT | /api/v1/products/:id | Admin | Update product |
| DELETE | /api/v1/products/:id | Admin | Delete product |
| POST | /api/v1/products/import | Admin | Bulk import |
| GET | /api/v1/categories | No | List category tree |
| GET | /api/v1/categories/:id/products | No | Products by category |
| GET | /api/v1/tags | No | List tags |
| GET | /api/v1/attributes | No | List attributes |

## Success Metrics

| Metric | Target | Measurement |
|--------|--------|-------------|
| Product list API | < 100ms | p95 response time |
| Category tree | < 50ms | Full tree load |
| Import speed | < 30s | 1000 products |
| Test coverage | > 80% | Product module |

## Next Phase Handoff

**Phase 4 Prerequisites:**
- [ ] Product API fully functional
- [ ] Category tree working
- [ ] Import system ready
- [ ] RBAC controls tested on product endpoints
