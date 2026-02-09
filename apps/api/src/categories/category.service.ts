import {
  Injectable,
  NotFoundException,
  ConflictException,
} from "@nestjs/common";
import { PrismaService } from "../prisma/prisma.service";
import {
  CreateCategoryDto,
  UpdateCategoryDto,
} from "./dto/create-category.dto";
import { Category } from "@prisma/client";

export interface CategoryTreeNode extends Category {
  children: CategoryTreeNode[];
}

@Injectable()
export class CategoryService {
  constructor(private prisma: PrismaService) {}

  async create(dto: CreateCategoryDto) {
    return this.prisma.$transaction(async (tx) => {
      // Check unique slug within transaction to prevent race conditions
      const existing = await tx.category.findUnique({
        where: { slug: dto.slug },
      });
      if (existing) {
        throw new ConflictException("Category with this slug already exists");
      }

      const { parentId, ...data } = dto;

      if (parentId) {
        // Insert as child using nested set logic
        const parent = await tx.category.findUnique({
          where: { id: parentId },
        });

        if (!parent) throw new NotFoundException("Parent category not found");

        // Make space in the tree
        // UPDATE categories SET right = right + 2 WHERE right >= parent.right
        await tx.category.updateMany({
          where: { right: { gte: parent.right } },
          data: { right: { increment: 2 } },
        });

        // UPDATE categories SET left = left + 2 WHERE left > parent.right
        await tx.category.updateMany({
          where: { left: { gt: parent.right } },
          data: { left: { increment: 2 } },
        });

        // Insert new node
        return tx.category.create({
          data: {
            ...data,
            left: parent.right,
            right: parent.right + 1,
            depth: parent.depth + 1,
            parentId,
          },
        });
      }

      // Insert as root
      const maxRight = await tx.category.aggregate({
        _max: { right: true },
      });

      const left = (maxRight._max.right || 0) + 1;

      return tx.category.create({
        data: {
          ...data,
          left,
          right: left + 1,
          depth: 0,
        },
      });
    });
  }

  async findAll() {
    return this.prisma.category.findMany({
      orderBy: { left: "asc" },
    });
  }

  async findTree() {
    const categories = await this.prisma.category.findMany({
      where: { isActive: true },
      orderBy: { left: "asc" },
    });

    return this.buildTree(categories);
  }

  async findOne(id: string) {
    const category = await this.prisma.category.findUnique({
      where: { id },
      include: {
        parent: true,
        children: {
          orderBy: { sortOrder: "asc" },
        },
      },
    });

    if (!category) {
      throw new NotFoundException("Category not found");
    }

    return category;
  }

  async findBySlug(slug: string) {
    const category = await this.prisma.category.findUnique({
      where: { slug },
      include: {
        parent: true,
        children: {
          orderBy: { sortOrder: "asc" },
        },
      },
    });

    if (!category) {
      throw new NotFoundException("Category not found");
    }

    return category;
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
      orderBy: { left: "asc" },
    });
  }

  async findAncestors(categoryId: string) {
    const category = await this.prisma.category.findUnique({
      where: { id: categoryId },
    });

    if (!category) return [];

    return this.prisma.category.findMany({
      where: {
        left: { lt: category.left },
        right: { gt: category.right },
      },
      orderBy: { left: "asc" },
    });
  }

  async update(id: string, dto: UpdateCategoryDto) {
    await this.findOne(id);

    return this.prisma.category.update({
      where: { id },
      data: dto,
    });
  }

  async remove(id: string) {
    const category = await this.findOne(id);

    // Check if category has products
    const productCount = await this.prisma.productCategory.count({
      where: { categoryId: id },
    });

    if (productCount > 0) {
      throw new ConflictException(
        "Cannot delete category with associated products",
      );
    }

    // Check if category has children
    if (category.children && category.children.length > 0) {
      throw new ConflictException("Cannot delete category with subcategories");
    }

    return this.prisma.category.delete({ where: { id } });
  }

  private async insertAsChild(
    parentId: string,
    data: Omit<CreateCategoryDto, "parentId">,
  ) {
    const parent = await this.prisma.category.findUnique({
      where: { id: parentId },
    });

    if (!parent) throw new NotFoundException("Parent category not found");

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

      while (
        stack.length > 0 &&
        stack[stack.length - 1].right < category.left
      ) {
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
