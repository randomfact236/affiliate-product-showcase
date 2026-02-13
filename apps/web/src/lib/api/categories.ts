import { fetchWithAuth, parseApiError } from "@/lib/auth"

export interface Category {
  id: string
  slug: string
  name: string
  description?: string
  parentId?: string
  parent?: Category
  metaTitle?: string
  metaDescription?: string
  image?: string
  isActive: boolean
  sortOrder: number
  left: number
  right: number
  depth: number
  createdAt: string
  updatedAt: string
}

export interface CreateCategoryInput {
  slug: string
  name: string
  description?: string
  parentId?: string
  metaTitle?: string
  metaDescription?: string
  image?: string
  isActive?: boolean
  sortOrder?: number
}

export interface UpdateCategoryInput {
  name?: string
  description?: string
  metaTitle?: string
  metaDescription?: string
  image?: string
  isActive?: boolean
  sortOrder?: number
}

const API_URL = process.env.NEXT_PUBLIC_API_URL || "http://localhost:3003"

export async function getCategories(): Promise<Category[]> {
  const response = await fetch(`${API_URL}/api/v1/categories`, {
    cache: "no-cache",
  })
  if (!response.ok) {
    throw new Error("Failed to fetch categories")
  }
  return response.json()
}

export async function getCategoryTree(): Promise<Category[]> {
  const response = await fetch(`${API_URL}/api/v1/categories/tree`, {
    cache: "no-cache",
  })
  if (!response.ok) {
    throw new Error("Failed to fetch category tree")
  }
  return response.json()
}

export async function getCategory(id: string): Promise<Category> {
  const response = await fetch(`${API_URL}/api/v1/categories/${id}`, {
    cache: "no-cache",
  })
  if (!response.ok) {
    throw new Error("Failed to fetch category")
  }
  return response.json()
}

export async function createCategory(
  data: CreateCategoryInput
): Promise<Category> {
  const response = await fetchWithAuth(`${API_URL}/api/v1/categories`, {
    method: "POST",
    body: JSON.stringify(data),
  })
  
  if (!response.ok) {
    const errorMessage = await parseApiError(response)
    throw new Error(errorMessage)
  }
  
  return response.json()
}

export async function updateCategory(
  id: string,
  data: UpdateCategoryInput
): Promise<Category> {
  const response = await fetchWithAuth(`${API_URL}/api/v1/categories/${id}`, {
    method: "PUT",
    body: JSON.stringify(data),
  })
  
  if (!response.ok) {
    const errorMessage = await parseApiError(response)
    throw new Error(errorMessage)
  }
  
  return response.json()
}

export async function deleteCategory(id: string): Promise<void> {
  const response = await fetchWithAuth(`${API_URL}/api/v1/categories/${id}`, {
    method: "DELETE",
  })
  
  if (!response.ok) {
    const errorMessage = await parseApiError(response)
    throw new Error(errorMessage)
  }
}

export interface BulkDeleteResult {
  deleted: number
  failed: number
  total: number
}

export async function bulkDeleteCategories(ids: string[]): Promise<BulkDeleteResult> {
  const response = await fetchWithAuth(`${API_URL}/api/v1/categories/bulk-delete`, {
    method: "POST",
    body: JSON.stringify({ ids }),
  })
  
  if (!response.ok) {
    const errorMessage = await parseApiError(response)
    throw new Error(errorMessage)
  }
  
  return response.json()
}

export interface BulkUpdateResult {
  updated: number
  total: number
}

export async function bulkUpdateCategories(
  ids: string[],
  data: Partial<UpdateCategoryInput>
): Promise<BulkUpdateResult> {
  const response = await fetchWithAuth(`${API_URL}/api/v1/categories/bulk-update`, {
    method: "POST",
    body: JSON.stringify({ ids, data }),
  })
  
  if (!response.ok) {
    const errorMessage = await parseApiError(response)
    throw new Error(errorMessage)
  }
  
  return response.json()
}
