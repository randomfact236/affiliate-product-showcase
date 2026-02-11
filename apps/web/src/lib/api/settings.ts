const API_URL = process.env.NEXT_PUBLIC_API_URL || "http://localhost:3003"

export interface Setting {
  id: string
  key: string
  value: string
  type: string
  group: string
  label?: string
  description?: string
  isPublic: boolean
  createdAt: string
  updatedAt: string
}

export interface ShortcodeDefinition {
  tag: string
  description: string
  attributes?: Record<string, { type: string; default?: any; description?: string }>
}

export interface DontMissConfig {
  enabled: boolean
  title: string
  subtitle: string
  layout: "mixed" | "blogs_only" | "products_only"
  blogCount: number
  productCount: number
  showViewAll: boolean
  blogCategory?: string
  productCategory?: string
  backgroundColor?: string
  textColor?: string
}

// Get all settings
export async function getSettings(group?: string): Promise<Setting[]> {
  const url = group ? `${API_URL}/settings?group=${group}` : `${API_URL}/settings`
  const response = await fetch(url)
  if (!response.ok) throw new Error("Failed to fetch settings")
  return response.json()
}

// Get public settings
export async function getPublicSettings(): Promise<Setting[]> {
  const response = await fetch(`${API_URL}/settings/public`)
  if (!response.ok) throw new Error("Failed to fetch public settings")
  return response.json()
}

// Get single setting
export async function getSetting(key: string): Promise<Setting> {
  const response = await fetch(`${API_URL}/settings/${key}`)
  if (!response.ok) throw new Error(`Failed to fetch setting: ${key}`)
  return response.json()
}

// Get setting value
export async function getSettingValue<T = any>(key: string, defaultValue?: T): Promise<T> {
  const response = await fetch(`${API_URL}/settings/${key}/value?default=${defaultValue ?? ""}`)
  if (!response.ok) throw new Error(`Failed to fetch setting value: ${key}`)
  const data = await response.json()
  return data.value
}

// Update setting
export async function updateSetting(key: string, value: any): Promise<Setting> {
  const response = await fetch(`${API_URL}/settings/${key}`, {
    method: "PUT",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ value }),
  })
  if (!response.ok) throw new Error(`Failed to update setting: ${key}`)
  return response.json()
}

// Bulk update settings
export async function bulkUpdateSettings(settings: Record<string, any>): Promise<{ message: string; count: number }> {
  const response = await fetch(`${API_URL}/settings`, {
    method: "PUT",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ settings }),
  })
  if (!response.ok) throw new Error("Failed to update settings")
  return response.json()
}

// Get shortcodes
export async function getShortcodes(): Promise<ShortcodeDefinition[]> {
  const response = await fetch(`${API_URL}/settings/shortcodes`)
  if (!response.ok) throw new Error("Failed to fetch shortcodes")
  return response.json()
}

// Get Don't Miss section config
export async function getDontMissConfig(): Promise<DontMissConfig> {
  const response = await fetch(`${API_URL}/settings/dont-miss`)
  if (!response.ok) throw new Error("Failed to fetch Don't Miss config")
  return response.json()
}

// Update Don't Miss section config
export async function updateDontMissConfig(config: Partial<DontMissConfig>): Promise<Setting> {
  const response = await fetch(`${API_URL}/settings/dont-miss/config`, {
    method: "PUT",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(config),
  })
  if (!response.ok) throw new Error("Failed to update Don't Miss config")
  return response.json()
}

// Initialize default settings
export async function initializeDefaultSettings(): Promise<{ message: string }> {
  const response = await fetch(`${API_URL}/settings/initialize`, {
    method: "POST",
  })
  if (!response.ok) throw new Error("Failed to initialize settings")
  return response.json()
}
