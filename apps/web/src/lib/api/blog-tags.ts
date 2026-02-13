"use client";

import { fetchWithAuth, parseApiError } from "@/lib/auth";

const API_URL = process.env.NEXT_PUBLIC_API_URL || "http://localhost:3003";

export interface BlogTag {
  id: string;
  slug: string;
  name: string;
  description: string | null;
  color: string | null;
  isActive: boolean;
  postCount: number;
  createdAt: string;
  updatedAt: string;
}

export interface CreateBlogTagInput {
  name: string;
  slug: string;
  description?: string;
  color?: string;
  isActive?: boolean;
}

export interface UpdateBlogTagInput {
  name?: string;
  slug?: string;
  description?: string;
  color?: string;
  isActive?: boolean;
}

export async function getBlogTags(): Promise<BlogTag[]> {
  const response = await fetchWithAuth(`${API_URL}/api/v1/blog/tags`);
  if (!response.ok) {
    throw new Error("Failed to fetch blog tags");
  }
  return response.json();
}

export async function getBlogTag(id: string): Promise<BlogTag> {
  const response = await fetchWithAuth(`${API_URL}/api/v1/blog/tags/${id}`);
  if (!response.ok) {
    throw new Error("Failed to fetch blog tag");
  }
  return response.json();
}

export async function createBlogTag(data: CreateBlogTagInput): Promise<BlogTag> {
  const response = await fetchWithAuth(`${API_URL}/api/v1/blog/tags`, {
    method: "POST",
    body: JSON.stringify(data),
  });
  if (!response.ok) {
    const error = await response.json();
    throw new Error(error.message || "Failed to create blog tag");
  }
  return response.json();
}

export async function updateBlogTag(
  id: string,
  data: UpdateBlogTagInput
): Promise<BlogTag> {
  const response = await fetchWithAuth(`${API_URL}/api/v1/blog/tags/${id}`, {
    method: "PUT",
    body: JSON.stringify(data),
  });
  if (!response.ok) {
    const error = await response.json();
    throw new Error(error.message || "Failed to update blog tag");
  }
  return response.json();
}

export async function deleteBlogTag(id: string): Promise<void> {
  const response = await fetchWithAuth(`${API_URL}/api/v1/blog/tags/${id}`, {
    method: "DELETE",
  });
  if (!response.ok) {
    throw new Error("Failed to delete blog tag");
  }
}

export async function bulkDeleteBlogTags(ids: string[]): Promise<void> {
  const response = await fetchWithAuth(`${API_URL}/api/v1/blog/tags/bulk-delete`, {
    method: "POST",
    body: JSON.stringify({ ids }),
  });
  if (!response.ok) {
    throw new Error("Failed to delete blog tags");
  }
}

export async function bulkUpdateBlogTags(
  ids: string[],
  data: UpdateBlogTagInput
): Promise<void> {
  const response = await fetchWithAuth(`${API_URL}/api/v1/blog/tags/bulk-update`, {
    method: "POST",
    body: JSON.stringify({ ids, data }),
  });
  if (!response.ok) {
    throw new Error("Failed to update blog tags");
  }
}
