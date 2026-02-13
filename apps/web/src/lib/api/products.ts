// Products API client
const API_URL = process.env.NEXT_PUBLIC_API_URL || "http://localhost:3003";

export interface Product {
  id: string;
  name: string;
  slug: string;
  shortDescription: string;
  description?: string;
  status: "DRAFT" | "PUBLISHED" | "ARCHIVED";
  price: number;
  comparePrice?: number;
  image?: string;
  images?: Array<{
    id: string;
    url: string;
    alt?: string;
    isPrimary?: boolean;
  }>;
  category?: {
    id: string;
    name: string;
    slug: string;
  };
  tags?: Array<{
    id: string;
    name: string;
    slug: string;
  }>;
  ribbon?: {
    id: string;
    name: string;
    label: string;
    bgColor: string;
    color: string;
  };
  isFeatured?: boolean;
  affiliateLinks?: Array<{
    id: string;
    platform: string;
    url: string;
    currentPrice: number;
    originalPrice?: number;
    inStock: boolean;
  }>;
  viewCount?: number;
  clickCount?: number;
  createdAt: string;
  updatedAt: string;
}

export interface ProductsResponse {
  items: Product[];
  total: number;
  page: number;
  limit: number;
  totalPages: number;
}

export interface ProductQueryParams {
  page?: number;
  limit?: number;
  search?: string;
  status?: string;
  category?: string;
  featured?: boolean;
  sortBy?: string;
  sortOrder?: "asc" | "desc";
}

export async function getProducts(params: ProductQueryParams = {}): Promise<ProductsResponse> {
  const queryParams = new URLSearchParams();
  
  if (params.page) queryParams.set("page", params.page.toString());
  if (params.limit) queryParams.set("limit", params.limit.toString());
  if (params.search) queryParams.set("search", params.search);
  if (params.status) queryParams.set("status", params.status);
  if (params.category) queryParams.set("category", params.category);
  if (params.featured) queryParams.set("featured", "true");
  if (params.sortBy) queryParams.set("sortBy", params.sortBy);
  if (params.sortOrder) queryParams.set("sortOrder", params.sortOrder);

  const response = await fetch(`${API_URL}/products?${queryParams}`, {
    next: { revalidate: 60 }, // Revalidate every 60 seconds
  });

  if (!response.ok) {
    throw new Error("Failed to fetch products");
  }

  return response.json();
}

export async function getProductBySlug(slug: string): Promise<Product> {
  const response = await fetch(`${API_URL}/products/${slug}`, {
    next: { revalidate: 60 },
  });

  if (!response.ok) {
    if (response.status === 404) {
      throw new Error("Product not found");
    }
    throw new Error("Failed to fetch product");
  }

  return response.json();
}

export async function getProductById(id: string): Promise<Product> {
  const response = await fetch(`${API_URL}/products/${id}`, {
    next: { revalidate: 60 },
  });

  if (!response.ok) {
    if (response.status === 404) {
      throw new Error("Product not found");
    }
    throw new Error("Failed to fetch product");
  }

  return response.json();
}

export async function getFeaturedProducts(limit: number = 5): Promise<ProductsResponse> {
  const response = await fetch(`${API_URL}/products?featured=true&limit=${limit}`, {
    next: { revalidate: 60 },
  });

  if (!response.ok) {
    throw new Error("Failed to fetch featured products");
  }

  return response.json();
}

export async function getLatestProducts(limit: number = 5): Promise<ProductsResponse> {
  const response = await fetch(`${API_URL}/products?sortBy=createdAt&sortOrder=desc&limit=${limit}`, {
    next: { revalidate: 60 },
  });

  if (!response.ok) {
    throw new Error("Failed to fetch latest products");
  }

  return response.json();
}

// CRUD Operations
export async function createProduct(data: Partial<Product>): Promise<Product> {
  const response = await fetch(`${API_URL}/products`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(data),
  });

  if (!response.ok) {
    throw new Error("Failed to create product");
  }

  return response.json();
}

export async function updateProduct(id: string, data: Partial<Product>): Promise<Product> {
  const response = await fetch(`${API_URL}/products/${id}`, {
    method: "PUT",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(data),
  });

  if (!response.ok) {
    throw new Error("Failed to update product");
  }

  return response.json();
}

export async function deleteProduct(id: string): Promise<void> {
  const response = await fetch(`${API_URL}/products/${id}`, {
    method: "DELETE",
  });

  if (!response.ok) {
    throw new Error("Failed to delete product");
  }
}

// Bulk Operations
export async function bulkDeleteProducts(ids: string[]): Promise<{ deleted: number }> {
  const response = await fetch(`${API_URL}/products/bulk-delete`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ ids }),
  });

  if (!response.ok) {
    throw new Error("Failed to delete products");
  }

  return response.json();
}

export async function bulkUpdateProducts(
  ids: string[],
  data: Partial<Pick<Product, "status" | "isFeatured" | "price">>
): Promise<{ updated: number }> {
  const response = await fetch(`${API_URL}/products/bulk-update`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ ids, data }),
  });

  if (!response.ok) {
    throw new Error("Failed to update products");
  }

  return response.json();
}
