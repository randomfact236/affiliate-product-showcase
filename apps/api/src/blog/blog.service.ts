import { Injectable, NotFoundException } from "@nestjs/common";
import { PrismaService } from "../prisma/prisma.service";
import { CreateBlogPostDto, UpdateBlogPostDto, QueryBlogPostsDto } from "./dto";
import { BlogPostStatus, Prisma } from "@prisma/client";
import { slugify } from "../common/utils/slugify.util";

@Injectable()
export class BlogService {
  constructor(private prisma: PrismaService) {}

  async findAll(query: QueryBlogPostsDto) {
    const {
      page = 1,
      limit = 10,
      search,
      status,
      authorId,
      categoryIds,
      tagIds,
      sortBy = "createdAt",
      sortOrder = "desc",
    } = query;

    const where: Prisma.BlogPostWhereInput = {};

    // Status filter
    if (status) {
      where.status = status;
    } else {
      // Default to showing only published posts for public API
      where.status = BlogPostStatus.PUBLISHED;
    }

    // Author filter
    if (authorId) {
      where.authorId = authorId;
    }

    // Search filter
    if (search) {
      where.OR = [
        { title: { contains: search, mode: "insensitive" } },
        { excerpt: { contains: search, mode: "insensitive" } },
        { content: { contains: search, mode: "insensitive" } },
      ];
    }

    // Category filter
    if (categoryIds && categoryIds.length > 0) {
      where.categories = {
        some: {
          categoryId: { in: categoryIds },
        },
      };
    }

    // Tag filter
    if (tagIds && tagIds.length > 0) {
      where.tags = {
        some: {
          tagId: { in: tagIds },
        },
      };
    }

    const skip = (page - 1) * limit;

    const [posts, total] = await Promise.all([
      this.prisma.blogPost.findMany({
        where,
        skip,
        take: limit,
        orderBy: { [sortBy]: sortOrder },
        include: {
          author: {
            select: {
              id: true,
              firstName: true,
              lastName: true,
              avatar: true,
            },
          },
          featuredImage: {
            select: {
              id: true,
              originalUrl: true,
              thumbnailUrl: true,
              mediumUrl: true,
              alt: true,
            },
          },
          categories: {
            include: {
              category: {
                select: {
                  id: true,
                  name: true,
                  slug: true,
                },
              },
            },
          },
          tags: {
            include: {
              tag: {
                select: {
                  id: true,
                  name: true,
                  slug: true,
                  color: true,
                },
              },
            },
          },
          _count: {
            select: {
              views: true,
            },
          },
        },
      }),
      this.prisma.blogPost.count({ where }),
    ]);

    // Transform posts to flatten categories and tags
    const transformedPosts = posts.map((post) => ({
      ...post,
      categories: post.categories.map((c) => c.category),
      tags: post.tags.map((t) => t.tag),
      viewCount: post._count.views,
    }));

    return {
      data: transformedPosts,
      meta: {
        total,
        page,
        limit,
        totalPages: Math.ceil(total / limit),
      },
    };
  }

  async findOne(id: string) {
    const post = await this.prisma.blogPost.findUnique({
      where: { id },
      include: {
        author: {
          select: {
            id: true,
            firstName: true,
            lastName: true,
            avatar: true,
          },
        },
        featuredImage: {
          select: {
            id: true,
            originalUrl: true,
            thumbnailUrl: true,
            mediumUrl: true,
            largeUrl: true,
            alt: true,
          },
        },
        images: {
          include: {
            media: {
              select: {
                id: true,
                originalUrl: true,
                thumbnailUrl: true,
                alt: true,
              },
            },
          },
          orderBy: { sortOrder: "asc" },
        },
        categories: {
          include: {
            category: {
              select: {
                id: true,
                name: true,
                slug: true,
                description: true,
              },
            },
          },
        },
        tags: {
          include: {
            tag: {
              select: {
                id: true,
                name: true,
                slug: true,
                color: true,
              },
            },
          },
        },
        relatedProducts: {
          include: {
            product: {
              select: {
                id: true,
                name: true,
                slug: true,
                shortDescription: true,
                images: {
                  take: 1,
                  select: {
                    url: true,
                    alt: true,
                  },
                },
                variants: {
                  select: {
                    price: true,
                    comparePrice: true,
                  },
                  take: 1,
                },
              },
            },
          },
        },
        _count: {
          select: {
            views: true,
          },
        },
      },
    });

    if (!post) {
      throw new NotFoundException("Blog post not found");
    }

    return {
      ...post,
      categories: post.categories.map((c) => c.category),
      tags: post.tags.map((t) => t.tag),
      relatedProducts: post.relatedProducts.map((p) => ({
        ...p,
        product: {
          ...p.product,
          price: p.product.variants[0]?.price || 0,
          comparePrice: p.product.variants[0]?.comparePrice || null,
        },
      })),
      viewCount: post._count.views,
    };
  }

  async findBySlug(slug: string) {
    const post = await this.prisma.blogPost.findUnique({
      where: { slug },
      include: {
        author: {
          select: {
            id: true,
            firstName: true,
            lastName: true,
            avatar: true,
          },
        },
        featuredImage: {
          select: {
            id: true,
            originalUrl: true,
            thumbnailUrl: true,
            mediumUrl: true,
            largeUrl: true,
            alt: true,
          },
        },
        images: {
          include: {
            media: {
              select: {
                id: true,
                originalUrl: true,
                thumbnailUrl: true,
                alt: true,
              },
            },
          },
          orderBy: { sortOrder: "asc" },
        },
        categories: {
          include: {
            category: {
              select: {
                id: true,
                name: true,
                slug: true,
                description: true,
              },
            },
          },
        },
        tags: {
          include: {
            tag: {
              select: {
                id: true,
                name: true,
                slug: true,
                color: true,
              },
            },
          },
        },
        relatedProducts: {
          include: {
            product: {
              select: {
                id: true,
                name: true,
                slug: true,
                shortDescription: true,
                images: {
                  take: 1,
                  select: {
                    url: true,
                    alt: true,
                  },
                },
                variants: {
                  select: {
                    price: true,
                    comparePrice: true,
                  },
                  take: 1,
                },
              },
            },
          },
        },
        _count: {
          select: {
            views: true,
          },
        },
      },
    });

    if (!post) {
      throw new NotFoundException("Blog post not found");
    }

    return {
      ...post,
      categories: post.categories.map((c) => c.category),
      tags: post.tags.map((t) => t.tag),
      relatedProducts: post.relatedProducts.map((p) => ({
        ...p,
        product: {
          ...p.product,
          price: p.product.variants[0]?.price || 0,
          comparePrice: p.product.variants[0]?.comparePrice || null,
        },
      })),
      viewCount: post._count.views,
    };
  }

  async create(dto: CreateBlogPostDto, userId: string) {
    // Generate slug if not provided
    const slug = dto.slug || slugify(dto.title);

    // Check if slug exists
    const existingPost = await this.prisma.blogPost.findUnique({
      where: { slug },
    });

    if (existingPost) {
      throw new Error("A blog post with this slug already exists");
    }

    // Calculate reading time if not provided
    const readingTime = dto.readingTime || this.calculateReadingTime(dto.content);

    const post = await this.prisma.blogPost.create({
      data: {
        title: dto.title,
        slug,
        excerpt: dto.excerpt,
        content: dto.content,
        contentType: dto.contentType || "html",
        status: dto.status || BlogPostStatus.DRAFT,
        publishedAt: dto.publishedAt ? new Date(dto.publishedAt) : null,
        metaTitle: dto.metaTitle,
        metaDescription: dto.metaDescription,
        keywords: dto.keywords,
        featuredImageId: dto.featuredImageId,
        authorId: dto.authorId,
        readingTime,
        createdBy: userId,
        categories: dto.categoryIds
          ? {
              create: dto.categoryIds.map((categoryId) => ({
                category: { connect: { id: categoryId } },
              })),
            }
          : undefined,
        tags: dto.tagIds
          ? {
              create: dto.tagIds.map((tagId) => ({
                tag: { connect: { id: tagId } },
              })),
            }
          : undefined,
        relatedProducts: dto.relatedProductIds
          ? {
              create: dto.relatedProductIds.map((productId, index) => ({
                product: { connect: { id: productId } },
                sortOrder: index,
              })),
            }
          : undefined,
      },
      include: {
        author: {
          select: {
            id: true,
            firstName: true,
            lastName: true,
            avatar: true,
          },
        },
        featuredImage: true,
        categories: {
          include: {
            category: true,
          },
        },
        tags: {
          include: {
            tag: true,
          },
        },
      },
    });

    return {
      ...post,
      categories: post.categories.map((c) => c.category),
      tags: post.tags.map((t) => t.tag),
    };
  }

  async update(id: string, dto: UpdateBlogPostDto, userId: string) {
    const existingPost = await this.prisma.blogPost.findUnique({
      where: { id },
    });

    if (!existingPost) {
      throw new NotFoundException("Blog post not found");
    }

    // Check slug uniqueness if being updated
    if (dto.slug && dto.slug !== existingPost.slug) {
      const slugExists = await this.prisma.blogPost.findUnique({
        where: { slug: dto.slug },
      });
      if (slugExists) {
        throw new Error("A blog post with this slug already exists");
      }
    }

    // Calculate new reading time if content changed
    const readingTime =
      dto.content && !dto.readingTime
        ? this.calculateReadingTime(dto.content)
        : dto.readingTime;

    // Delete existing relations if updating
    if (dto.categoryIds !== undefined) {
      await this.prisma.blogPostCategory.deleteMany({
        where: { blogPostId: id },
      });
    }
    if (dto.tagIds !== undefined) {
      await this.prisma.blogPostTag.deleteMany({
        where: { blogPostId: id },
      });
    }
    if (dto.relatedProductIds !== undefined) {
      await this.prisma.blogPostProduct.deleteMany({
        where: { blogPostId: id },
      });
    }

    const post = await this.prisma.blogPost.update({
      where: { id },
      data: {
        title: dto.title,
        slug: dto.slug,
        excerpt: dto.excerpt,
        content: dto.content,
        contentType: dto.contentType,
        status: dto.status,
        publishedAt: dto.publishedAt ? new Date(dto.publishedAt) : undefined,
        metaTitle: dto.metaTitle,
        metaDescription: dto.metaDescription,
        keywords: dto.keywords,
        featuredImageId: dto.featuredImageId,
        authorId: dto.authorId,
        readingTime,
        updatedBy: userId,
        categories:
          dto.categoryIds !== undefined
            ? {
                create: dto.categoryIds.map((categoryId) => ({
                  category: { connect: { id: categoryId } },
                })),
              }
            : undefined,
        tags:
          dto.tagIds !== undefined
            ? {
                create: dto.tagIds.map((tagId) => ({
                  tag: { connect: { id: tagId } },
                })),
              }
            : undefined,
        relatedProducts:
          dto.relatedProductIds !== undefined
            ? {
                create: dto.relatedProductIds.map((productId, index) => ({
                  product: { connect: { id: productId } },
                  sortOrder: index,
                })),
              }
            : undefined,
      },
      include: {
        author: {
          select: {
            id: true,
            firstName: true,
            lastName: true,
            avatar: true,
          },
        },
        featuredImage: true,
        categories: {
          include: {
            category: true,
          },
        },
        tags: {
          include: {
            tag: true,
          },
        },
      },
    });

    return {
      ...post,
      categories: post.categories.map((c) => c.category),
      tags: post.tags.map((t) => t.tag),
    };
  }

  async remove(id: string) {
    const existingPost = await this.prisma.blogPost.findUnique({
      where: { id },
    });

    if (!existingPost) {
      throw new NotFoundException("Blog post not found");
    }

    // Delete related records first
    await this.prisma.$transaction([
      this.prisma.blogPostCategory.deleteMany({ where: { blogPostId: id } }),
      this.prisma.blogPostTag.deleteMany({ where: { blogPostId: id } }),
      this.prisma.blogPostProduct.deleteMany({ where: { blogPostId: id } }),
      this.prisma.blogPostImage.deleteMany({ where: { blogPostId: id } }),
      this.prisma.blogPostView.deleteMany({ where: { blogPostId: id } }),
      this.prisma.blogPost.delete({ where: { id } }),
    ]);
  }

  async incrementViewCount(id: string) {
    await this.prisma.blogPost.update({
      where: { id },
      data: { viewCount: { increment: 1 } },
    });
  }

  async recordView(blogPostId: string, ipAddress?: string, userAgent?: string, userId?: string) {
    await this.prisma.blogPostView.create({
      data: {
        blogPostId,
        ipAddress,
        userAgent,
        userId,
      },
    });
  }

  private calculateReadingTime(content: string): number {
    const wordsPerMinute = 200;
    const text = content.replace(/<[^>]*>/g, ""); // Remove HTML tags
    const wordCount = text.split(/\s+/).length;
    return Math.ceil(wordCount / wordsPerMinute);
  }
}
