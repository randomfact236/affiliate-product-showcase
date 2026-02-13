// Simple auth utilities using localStorage
const TOKEN_KEY = "auth_token"
const USER_KEY = "auth_user"

export interface AuthUser {
  id: string
  email: string
  firstName?: string
  lastName?: string
  roles: string[]
}

export const auth = {
  // Get token from localStorage
  getToken(): string | null {
    if (typeof window === "undefined") return null
    return localStorage.getItem(TOKEN_KEY)
  },

  // Set token in localStorage
  setToken(token: string): void {
    if (typeof window === "undefined") return
    localStorage.setItem(TOKEN_KEY, token)
  },

  // Remove token (logout)
  removeToken(): void {
    if (typeof window === "undefined") return
    localStorage.removeItem(TOKEN_KEY)
    localStorage.removeItem(USER_KEY)
  },

  // Get current user
  getUser(): AuthUser | null {
    if (typeof window === "undefined") return null
    const user = localStorage.getItem(USER_KEY)
    return user ? JSON.parse(user) : null
  },

  // Set current user
  setUser(user: AuthUser): void {
    if (typeof window === "undefined") return
    localStorage.setItem(USER_KEY, JSON.stringify(user))
  },

  // Check if user is authenticated
  isAuthenticated(): boolean {
    return !!this.getToken()
  },

  // Check if user has role
  hasRole(role: string): boolean {
    const user = this.getUser()
    return user?.roles?.includes(role) ?? false
  },

  // Check if user is admin
  isAdmin(): boolean {
    return this.hasRole("ADMIN")
  },
}

// API helper with auth header
export async function fetchWithAuth(
  url: string,
  options: RequestInit = {}
): Promise<Response> {
  const token = auth.getToken()
  
  const headers: Record<string, string> = {
    "Content-Type": "application/json",
    ...((options.headers as Record<string, string>) || {}),
  }

  if (token) {
    headers["Authorization"] = `Bearer ${token}`
  }

  return fetch(url, {
    ...options,
    headers,
  })
}

// Parse API error response
export async function parseApiError(response: Response): Promise<string> {
  try {
    const data = await response.json()
    return data.message || `Error ${response.status}: ${response.statusText}`
  } catch {
    return `Error ${response.status}: ${response.statusText}`
  }
}
