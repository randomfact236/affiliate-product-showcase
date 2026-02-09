export interface ProductImage {
  id: string
  url: string
  alt?: string
  isPrimary?: boolean
  order?: number
}

export interface AffiliateLink {
  id: string
  platform: string
  url: string
  currentPrice: number
  originalPrice?: number
  inStock: boolean
}

export interface Category {
  id: string
  name: string
  slug: string
  description?: string
  image?: string
  parentId?: string | null
  children?: Category[]
}

export interface Ribbon {
  id: string
  name: string
  bgColor: string
  color: string
}

export interface ProductAttribute {
  id: string
  attribute: {
    id: string
    name: string
    type: string
  }
  value: string
}

export interface Product {
  id: string
  name: string
  slug: string
  shortDescription: string
  description?: string
  basePrice: number
  images: ProductImage[]
  affiliateLinks: AffiliateLink[]
  categories: { category: Category }[]
  attributes?: ProductAttribute[]
  ribbons?: Ribbon[]
  tags?: { tag: { id: string; name: string; slug: string } }[]
  metaTitle?: string
  metaDescription?: string
  status: "DRAFT" | "PUBLISHED" | "ARCHIVED"
  viewCount: number
  clickCount: number
  createdAt: string
  updatedAt: string
  // Additional fields for enhanced product cards
  features?: string[]
  rating?: number
  reviewCount?: number
  isFeatured?: boolean
  hasFreeTrial?: boolean
}

export interface PaginatedResponse<T> {
  data: T[]
  meta: {
    total: number
    page: number
    limit: number
    totalPages: number
  }
}

export interface User {
  id: string
  email: string
  firstName?: string
  lastName?: string
  avatar?: string
  roles: string[]
}

export interface AnalyticsEvent {
  id: string
  eventType: "page_view" | "product_view" | "affiliate_click" | "search"
  sessionId: string
  userId?: string
  productId?: string
  metadata?: Record<string, unknown>
  createdAt: string
}
