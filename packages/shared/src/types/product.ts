export interface Product {
    id: string;
    slug: string;
    name: string;
    description?: string;
    shortDescription?: string;
    status: 'DRAFT' | 'PENDING_REVIEW' | 'PUBLISHED' | 'ARCHIVED';
    // Pricing
    price: number;
    comparePrice?: number;
    currency: string;
    // Images
    images?: ProductImage[];
    // Relations
    categories?: Category[];
    tags?: Tag[];
    // Analytics
    viewCount?: number;
    createdAt: Date;
    updatedAt: Date;
}

export interface ProductImage {
    id: string;
    url: string;
    alt?: string;
    isPrimary: boolean;
    sortOrder: number;
}

export interface Category {
    id: string;
    name: string;
    slug: string;
    parentId?: string;
}

export interface Tag {
    id: string;
    name: string;
    slug: string;
}
