import { fetchWithAuth, parseApiError } from "@/lib/auth"

export interface Ribbon {
  id: string
  name: string
  label: string
  description?: string
  color: string
  bgColor: string
  icon?: string
  position: string
  priority: number
  isActive: boolean
  createdAt: string
  updatedAt: string
}

export interface CreateRibbonInput {
  name: string
  label?: string
  description?: string
  color?: string
  bgColor?: string
  icon?: string
  position?: string
  priority?: number
  isActive?: boolean
}

export interface UpdateRibbonInput {
  name?: string
  label?: string
  description?: string
  color?: string
  bgColor?: string
  icon?: string
  position?: string
  priority?: number
  isActive?: boolean
}

const API_URL = process.env.NEXT_PUBLIC_API_URL || "http://localhost:3003"

export async function getRibbons(): Promise<Ribbon[]> {
  const response = await fetch(`${API_URL}/api/v1/ribbons`, {
    cache: "no-cache",
  })
  if (!response.ok) {
    throw new Error("Failed to fetch ribbons")
  }
  return response.json()
}

export async function getActiveRibbons(): Promise<Ribbon[]> {
  const response = await fetch(`${API_URL}/api/v1/ribbons/active`, {
    cache: "no-cache",
  })
  if (!response.ok) {
    throw new Error("Failed to fetch active ribbons")
  }
  return response.json()
}

export async function getRibbon(id: string): Promise<Ribbon> {
  const response = await fetch(`${API_URL}/api/v1/ribbons/${id}`, {
    cache: "no-cache",
  })
  if (!response.ok) {
    throw new Error("Failed to fetch ribbon")
  }
  return response.json()
}

export async function createRibbon(data: CreateRibbonInput): Promise<Ribbon> {
  const response = await fetchWithAuth(`${API_URL}/api/v1/ribbons`, {
    method: "POST",
    body: JSON.stringify(data),
  })
  
  if (!response.ok) {
    const errorMessage = await parseApiError(response)
    throw new Error(errorMessage)
  }
  
  return response.json()
}

export async function updateRibbon(id: string, data: UpdateRibbonInput): Promise<Ribbon> {
  const response = await fetchWithAuth(`${API_URL}/api/v1/ribbons/${id}`, {
    method: "PUT",
    body: JSON.stringify(data),
  })
  
  if (!response.ok) {
    const errorMessage = await parseApiError(response)
    throw new Error(errorMessage)
  }
  
  return response.json()
}

export async function deleteRibbon(id: string): Promise<void> {
  const response = await fetchWithAuth(`${API_URL}/api/v1/ribbons/${id}`, {
    method: "DELETE",
  })
  
  if (!response.ok) {
    const errorMessage = await parseApiError(response)
    throw new Error(errorMessage)
  }
}

export async function toggleRibbonActive(id: string): Promise<Ribbon> {
  const response = await fetchWithAuth(`${API_URL}/api/v1/ribbons/${id}/toggle-active`, {
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

export async function bulkDeleteRibbons(ids: string[]): Promise<BulkDeleteResult> {
  const response = await fetchWithAuth(`${API_URL}/api/v1/ribbons/bulk-delete`, {
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

export async function bulkUpdateRibbons(
  ids: string[],
  data: Partial<UpdateRibbonInput>
): Promise<BulkUpdateResult> {
  const response = await fetchWithAuth(`${API_URL}/api/v1/ribbons/bulk-update`, {
    method: "POST",
    body: JSON.stringify({ ids, data }),
  })
  
  if (!response.ok) {
    const errorMessage = await parseApiError(response)
    throw new Error(errorMessage)
  }
  
  return response.json()
}
