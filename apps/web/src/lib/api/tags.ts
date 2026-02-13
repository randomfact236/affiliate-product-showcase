import { fetchWithAuth, parseApiError } from "@/lib/auth"

export interface Tag {
  id: string
  slug: string
  name: string
  description?: string
  color?: string
  icon?: string
  productCount: number
  isActive: boolean
  sortOrder: number
  createdAt: string
  updatedAt: string
}

export interface CreateTagInput {
  slug: string
  name: string
  description?: string
  color?: string
  icon?: string
  isActive?: boolean
  sortOrder?: number
}

export interface UpdateTagInput {
  name?: string
  description?: string
  color?: string
  icon?: string
  isActive?: boolean
  sortOrder?: number
  slug?: string
}

const API_URL = process.env.NEXT_PUBLIC_API_URL || "http://localhost:3003"

export async function getTags(): Promise<Tag[]> {
  const response = await fetch(`${API_URL}/api/v1/tags`, {
    cache: "no-cache",
  })
  if (!response.ok) {
    throw new Error("Failed to fetch tags")
  }
  return response.json()
}

export async function getActiveTags(): Promise<Tag[]> {
  const response = await fetch(`${API_URL}/api/v1/tags/active`, {
    cache: "no-cache",
  })
  if (!response.ok) {
    throw new Error("Failed to fetch active tags")
  }
  return response.json()
}

export async function getTag(id: string): Promise<Tag> {
  const response = await fetch(`${API_URL}/api/v1/tags/${id}`, {
    cache: "no-cache",
  })
  if (!response.ok) {
    throw new Error("Failed to fetch tag")
  }
  return response.json()
}

export async function createTag(data: CreateTagInput): Promise<Tag> {
  const response = await fetchWithAuth(`${API_URL}/api/v1/tags`, {
    method: "POST",
    body: JSON.stringify(data),
  })
  
  if (!response.ok) {
    const errorMessage = await parseApiError(response)
    throw new Error(errorMessage)
  }
  
  return response.json()
}

export async function updateTag(id: string, data: UpdateTagInput): Promise<Tag> {
  const response = await fetchWithAuth(`${API_URL}/api/v1/tags/${id}`, {
    method: "PUT",
    body: JSON.stringify(data),
  })
  
  if (!response.ok) {
    const errorMessage = await parseApiError(response)
    throw new Error(errorMessage)
  }
  
  return response.json()
}

export async function deleteTag(id: string): Promise<void> {
  const response = await fetchWithAuth(`${API_URL}/api/v1/tags/${id}`, {
    method: "DELETE",
  })
  
  if (!response.ok) {
    const errorMessage = await parseApiError(response)
    throw new Error(errorMessage)
  }
}

export async function toggleTagActive(id: string): Promise<Tag> {
  const response = await fetchWithAuth(`${API_URL}/api/v1/tags/${id}/toggle-active`, {
    method: "PATCH",
  })
  
  if (!response.ok) {
    const errorMessage = await parseApiError(response)
    throw new Error(errorMessage)
  }
  
  return response.json()
}

export interface BulkDeleteResult {
  deleted: number
  total: number
}

export async function bulkDeleteTags(ids: string[]): Promise<BulkDeleteResult> {
  const response = await fetchWithAuth(`${API_URL}/api/v1/tags/bulk-delete`, {
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

export async function bulkUpdateTags(
  ids: string[],
  data: Partial<UpdateTagInput>
): Promise<BulkUpdateResult> {
  const response = await fetchWithAuth(`${API_URL}/api/v1/tags/bulk-update`, {
    method: "POST",
    body: JSON.stringify({ ids, data }),
  })
  
  if (!response.ok) {
    const errorMessage = await parseApiError(response)
    throw new Error(errorMessage)
  }
  
  return response.json()
}
