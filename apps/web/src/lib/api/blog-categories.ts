"use client";

import { fetchWithAuth, parseApiError } from "@/lib/auth";

const API_URL = process.env.NEXT_PUBLIC_API_URL || "http://localhost:3003";

export interface BlogCategory {
  id: string;
  slug: string;
  name: string;
  description: string | null;
  parentId: string | null;
  depth: number;
  isActive: boolean;
  sortOrder: number;
  postCount: number;
  createdAt: string;
  updatedAt: string;
}

export interface CreateBlogCategoryInput {
  name: string;
  slug: string;
  description?: string;
  parentId?: string | null;
  isActive?: boolean;
  sortOrder?: number;
}

export interface UpdateBlogCategoryInput {
  name?: string;
  slug?: string;
  description?: string;
  parentId?: string | null;
  isActive?: boolean;
  sortOrder?: number;
}

export async function getBlogCategories(): Promise<BlogCategory[]> {
  const response = await fetchWithAuth(`${API_URL}/api/v1/blog/categories`);
  if (!response.ok) {
    throw new Error("Failed to fetch blog categories");
  }
  return response.json();
}

export async function getBlogCategory(id: string): Promise<BlogCategory> {
  const response = await fetchWithAuth(`${API_URL}/api/v1/blog/categories/${id}`);
  if (!response.ok) {
    throw new Error("Failed to fetch blog category");
  }
  return response.json();
}

export async function createBlogCategory(data: CreateBlogCategoryInput): Promise<BlogCategory> {
  const response = await fetchWithAuth(`${API_URL}/api/v1/blog/categories`, {
    method: "POST",
    body: JSON.stringify(data),
  });
  if (!response.ok) {
    const error = await response.json();
    throw new Error(error.message || "Failed to create blog category");
  }
  return response.json();
}

export async function updateBlogCategory(
  id: string,
  data: UpdateBlogCategoryInput
): Promise<BlogCategory> {
  const response = await fetchWithAuth(`${API_URL}/api/v1/blog/categories/${id}`, {
    method: "PUT",
    body: JSON.stringify(data),
  });
  if (!response.ok) {
    const error = await response.json();
    throw new Error(error.message || "Failed to update blog category");
  }
  return response.json();
}

export async function deleteBlogCategory(id: string): Promise<void> {
  const response = await fetchWithAuth(`${API_URL}/api/v1/blog/categories/${id}`, {
    method: "DELETE",
  });
  if (!response.ok) {
    throw new Error("Failed to delete blog category");
  }
}

export async function bulkDeleteBlogCategories(ids: string[]): Promise<void> {
  const response = await fetchWithAuth(`${API_URL}/api/v1/blog/categories/bulk-delete`, {
    method: "POST",
    body: JSON.stringify({ ids }),
  });
  if (!response.ok) {
    throw new Error("Failed to delete blog categories");
  }
}

export async function bulkUpdateBlogCategories(
  ids: string[],
  data: UpdateBlogCategoryInput
): Promise<void> {
  const response = await fetchWithAuth(`${API_URL}/api/v1/blog/categories/bulk-update`, {
    method: "POST",
    body: JSON.stringify({ ids, data }),
  });
  if (!response.ok) {
    throw new Error("Failed to update blog categories");
  }
}
