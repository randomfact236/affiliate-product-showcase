// Blog API client
const API_URL = process.env.NEXT_PUBLIC_API_URL || "http://localhost:3003";

export interface BlogPost {
  id: string;
  title: string;
  slug: string;
  excerpt: string | null;
  content: string;
  contentType: string;
  status: string;
  publishedAt: string | null;
  metaTitle: string | null;
  metaDescription: string | null;
  keywords: string | null;
  readingTime: number;
  viewCount: number;
  likeCount: number;
  shareCount: number;
  createdAt: string;
  updatedAt: string;
  author: {
    id: string;
    firstName: string | null;
    lastName: string | null;
    avatar: string | null;
  } | null;
  featuredImage: {
    id: string;
    originalUrl: string;
    thumbnailUrl: string | null;
    mediumUrl: string | null;
    alt: string | null;
  } | null;
  categories: Array<{
    id: string;
    name: string;
    slug: string;
  }>;
  tags: Array<{
    id: string;
    name: string;
    slug: string;
    color: string | null;
  }>;
  images?: Array<{
    id: string;
    media: {
      id: string;
      originalUrl: string;
      thumbnailUrl: string | null;
      alt: string | null;
    };
    caption: string | null;
    alt: string | null;
    sortOrder: number;
  }>;
  relatedProducts?: Array<{
    id: string;
    product: {
      id: string;
      name: string;
      slug: string;
      shortDescription: string | null;
      images: Array<{
        url: string;
        alt: string | null;
      }>;
      price: number;
      comparePrice: number | null;
    };
    position: string | null;
    sortOrder: number;
  }>;
}

export interface BlogPostsResponse {
  data: BlogPost[];
  meta: {
    total: number;
    page: number;
    limit: number;
    totalPages: number;
  };
}

export interface BlogQueryParams {
  page?: number;
  limit?: number;
  search?: string;
  status?: string;
  authorId?: string;
  categoryIds?: string[];
  tagIds?: string[];
  sortBy?: string;
  sortOrder?: "asc" | "desc";
}

export async function getBlogPosts(params: BlogQueryParams = {}): Promise<BlogPostsResponse> {
  const queryParams = new URLSearchParams();
  
  if (params.page) queryParams.set("page", params.page.toString());
  if (params.limit) queryParams.set("limit", params.limit.toString());
  if (params.search) queryParams.set("search", params.search);
  if (params.status) queryParams.set("status", params.status);
  if (params.authorId) queryParams.set("authorId", params.authorId);
  if (params.sortBy) queryParams.set("sortBy", params.sortBy);
  if (params.sortOrder) queryParams.set("sortOrder", params.sortOrder);
  if (params.categoryIds) {
    params.categoryIds.forEach(id => queryParams.append("categoryIds", id));
  }
  if (params.tagIds) {
    params.tagIds.forEach(id => queryParams.append("tagIds", id));
  }

  const response = await fetch(`${API_URL}/api/v1/blog?${queryParams}`, {
    next: { revalidate: 60 }, // Revalidate every 60 seconds
  });

  if (!response.ok) {
    throw new Error("Failed to fetch blog posts");
  }

  return response.json();
}

export async function getBlogPostBySlug(slug: string): Promise<BlogPost> {
  const response = await fetch(`${API_URL}/api/v1/blog/slug/${slug}`, {
    next: { revalidate: 60 },
  });

  if (!response.ok) {
    if (response.status === 404) {
      throw new Error("Blog post not found");
    }
    throw new Error("Failed to fetch blog post");
  }

  return response.json();
}

export async function getBlogPostById(id: string): Promise<BlogPost> {
  const response = await fetch(`${API_URL}/api/v1/blog/${id}`, {
    next: { revalidate: 60 },
  });

  if (!response.ok) {
    if (response.status === 404) {
      throw new Error("Blog post not found");
    }
    throw new Error("Failed to fetch blog post");
  }

  return response.json();
}

export async function getFeaturedPosts(limit: number = 5): Promise<BlogPostsResponse> {
  const response = await fetch(`${API_URL}/api/v1/blog/featured?limit=${limit}`, {
    next: { revalidate: 60 },
  });

  if (!response.ok) {
    throw new Error("Failed to fetch featured posts");
  }

  return response.json();
}

export async function getLatestPosts(limit: number = 5): Promise<BlogPostsResponse> {
  const response = await fetch(`${API_URL}/api/v1/blog/latest?limit=${limit}`, {
    next: { revalidate: 60 },
  });

  if (!response.ok) {
    throw new Error("Failed to fetch latest posts");
  }

  return response.json();
}
