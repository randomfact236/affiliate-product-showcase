# Phase 3: Frontend Public & Showcase

**Objective:** Build a stunning, high-performance public interface using Next.js 15 with App Router, delivering exceptional user experience and conversion-optimized product showcases.

**Framework:** Next.js 15 + NestJS 10 + PostgreSQL + Redis  
**Estimated Duration:** 14 days  
**Prerequisites:** Phase 2 completed, API endpoints available

**Quality Target:** Enterprise Grade (10/10) - 95+ Lighthouse, SEO-optimized, accessible

---

## 1. Design System & UI Foundation

### 1.1 Tailwind Configuration
```typescript
// tailwind.config.ts
import type { Config } from 'tailwindcss';

const config: Config = {
  darkMode: ['class'],
  content: [
    './pages/**/*.{js,ts,jsx,tsx,mdx}',
    './components/**/*.{js,ts,jsx,tsx,mdx}',
    './app/**/*.{js,ts,jsx,tsx,mdx}',
  ],
  theme: {
    extend: {
      colors: {
        // Brand colors
        primary: {
          50: '#eff6ff',
          100: '#dbeafe',
          200: '#bfdbfe',
          300: '#93c5fd',
          400: '#60a5fa',
          500: '#3b82f6',
          600: '#2563eb',
          700: '#1d4ed8',
          800: '#1e40af',
          900: '#1e3a8a',
        },
        // Semantic colors
        success: {
          50: '#f0fdf4',
          500: '#22c55e',
          700: '#15803d',
        },
        warning: {
          50: '#fffbeb',
          500: '#f59e0b',
          700: '#b45309',
        },
        danger: {
          50: '#fef2f2',
          500: '#ef4444',
          700: '#b91c1c',
        },
        // Neutral scale
        gray: {
          50: '#f9fafb',
          100: '#f3f4f6',
          200: '#e5e7eb',
          300: '#d1d5db',
          400: '#9ca3af',
          500: '#6b7280',
          600: '#4b5563',
          700: '#374151',
          800: '#1f2937',
          900: '#111827',
          950: '#030712',
        },
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
        display: ['Inter', 'system-ui', 'sans-serif'],
      },
      fontSize: {
        'display-1': ['4.5rem', { lineHeight: '1.1', letterSpacing: '-0.02em' }],
        'display-2': ['3.75rem', { lineHeight: '1.1', letterSpacing: '-0.02em' }],
        'heading-1': ['3rem', { lineHeight: '1.2', letterSpacing: '-0.02em' }],
        'heading-2': ['2.25rem', { lineHeight: '1.2', letterSpacing: '-0.02em' }],
        'heading-3': ['1.875rem', { lineHeight: '1.3', letterSpacing: '-0.01em' }],
        'heading-4': ['1.5rem', { lineHeight: '1.4' }],
        'heading-5': ['1.25rem', { lineHeight: '1.5' }],
        'heading-6': ['1.125rem', { lineHeight: '1.5' }],
        'body-lg': ['1.125rem', { lineHeight: '1.75' }],
        'body': ['1rem', { lineHeight: '1.75' }],
        'body-sm': ['0.875rem', { lineHeight: '1.75' }],
        'caption': ['0.75rem', { lineHeight: '1.5' }],
      },
      spacing: {
        '18': '4.5rem',
        '22': '5.5rem',
        '30': '7.5rem',
      },
      borderRadius: {
        '4xl': '2rem',
      },
      boxShadow: {
        'soft': '0 2px 15px -3px rgba(0, 0, 0, 0.07), 0 10px 20px -2px rgba(0, 0, 0, 0.04)',
        'soft-lg': '0 10px 40px -10px rgba(0, 0, 0, 0.1)',
        'glow': '0 0 40px -10px rgba(59, 130, 246, 0.3)',
      },
      animation: {
        'fade-in': 'fadeIn 0.5s ease-out',
        'slide-up': 'slideUp 0.5s ease-out',
        'slide-down': 'slideDown 0.3s ease-out',
        'scale-in': 'scaleIn 0.3s ease-out',
      },
      keyframes: {
        fadeIn: {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        slideUp: {
          '0%': { opacity: '0', transform: 'translateY(20px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
        slideDown: {
          '0%': { opacity: '0', transform: 'translateY(-10px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
        scaleIn: {
          '0%': { opacity: '0', transform: 'scale(0.95)' },
          '100%': { opacity: '1', transform: 'scale(1)' },
        },
      },
    },
  },
  plugins: [require('tailwindcss-animate')],
};

export default config;
```

### 1.2 Global Styles
```css
/* app/globals.css */
@tailwind base;
@tailwind components;
@tailwind utilities;

@layer base {
  :root {
    --background: 0 0% 100%;
    --foreground: 222.2 84% 4.9%;
    --card: 0 0% 100%;
    --card-foreground: 222.2 84% 4.9%;
    --popover: 0 0% 100%;
    --popover-foreground: 222.2 84% 4.9%;
    --primary: 221.2 83.2% 53.3%;
    --primary-foreground: 210 40% 98%;
    --secondary: 210 40% 96.1%;
    --secondary-foreground: 222.2 47.4% 11.2%;
    --muted: 210 40% 96.1%;
    --muted-foreground: 215.4 16.3% 46.9%;
    --accent: 210 40% 96.1%;
    --accent-foreground: 222.2 47.4% 11.2%;
    --destructive: 0 84.2% 60.2%;
    --destructive-foreground: 210 40% 98%;
    --border: 214.3 31.8% 91.4%;
    --input: 214.3 31.8% 91.4%;
    --ring: 221.2 83.2% 53.3%;
    --radius: 0.75rem;
  }

  .dark {
    --background: 222.2 84% 4.9%;
    --foreground: 210 40% 98%;
    --card: 222.2 84% 4.9%;
    --card-foreground: 210 40% 98%;
    --popover: 222.2 84% 4.9%;
    --popover-foreground: 210 40% 98%;
    --primary: 217.2 91.2% 59.8%;
    --primary-foreground: 222.2 47.4% 11.2%;
    --secondary: 217.2 32.6% 17.5%;
    --secondary-foreground: 210 40% 98%;
    --muted: 217.2 32.6% 17.5%;
    --muted-foreground: 215 20.2% 65.1%;
    --accent: 217.2 32.6% 17.5%;
    --accent-foreground: 210 40% 98%;
    --destructive: 0 62.8% 30.6%;
    --destructive-foreground: 210 40% 98%;
    --border: 217.2 32.6% 17.5%;
    --input: 217.2 32.6% 17.5%;
    --ring: 224.3 76.3% 48%;
  }
}

@layer base {
  * {
    @apply border-border;
  }
  body {
    @apply bg-background text-foreground antialiased;
    font-feature-settings: "rlig" 1, "calt" 1;
  }
  html {
    scroll-behavior: smooth;
  }
}

@layer utilities {
  .text-balance {
    text-wrap: balance;
  }
  .animation-delay-200 {
    animation-delay: 200ms;
  }
  .animation-delay-400 {
    animation-delay: 400ms;
  }
}
```

---

## 2. Component Architecture

### 2.1 Core UI Components
```typescript
// components/ui/button.tsx
import * as React from 'react';
import { cva, type VariantProps } from 'class-variance-authority';
import { cn } from '@/lib/utils';

const buttonVariants = cva(
  'inline-flex items-center justify-center whitespace-nowrap rounded-lg text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50',
  {
    variants: {
      variant: {
        default: 'bg-primary-600 text-white hover:bg-primary-700 shadow-sm',
        destructive: 'bg-danger-500 text-white hover:bg-danger-600',
        outline: 'border border-gray-300 bg-white hover:bg-gray-50 text-gray-700',
        secondary: 'bg-gray-100 text-gray-900 hover:bg-gray-200',
        ghost: 'hover:bg-gray-100 text-gray-700',
        link: 'text-primary-600 underline-offset-4 hover:underline',
      },
      size: {
        default: 'h-10 px-4 py-2',
        sm: 'h-8 px-3 text-xs',
        lg: 'h-12 px-6 text-base',
        icon: 'h-10 w-10',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'default',
    },
  }
);

export interface ButtonProps
  extends React.ButtonHTMLAttributes<HTMLButtonElement>,
    VariantProps<typeof buttonVariants> {
  asChild?: boolean;
  loading?: boolean;
}

const Button = React.forwardRef<HTMLButtonElement, ButtonProps>(
  ({ className, variant, size, loading, children, disabled, ...props }, ref) => {
    return (
      <button
        className={cn(buttonVariants({ variant, size, className }))}
        ref={ref}
        disabled={disabled || loading}
        {...props}
      >
        {loading && (
          <svg
            className="mr-2 h-4 w-4 animate-spin"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
          >
            <circle
              className="opacity-25"
              cx="12"
              cy="12"
              r="10"
              stroke="currentColor"
              strokeWidth="4"
            />
            <path
              className="opacity-75"
              fill="currentColor"
              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            />
          </svg>
        )}
        {children}
      </button>
    );
  }
);
Button.displayName = 'Button';

export { Button, buttonVariants };
```

### 2.2 Product Card Component
```typescript
// components/product/product-card.tsx
'use client';

import Image from 'next/image';
import Link from 'next/link';
import { Product } from '@affiliate-showcase/shared';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { formatPrice } from '@/lib/utils';
import { useAnalytics } from '@/hooks/use-analytics';

interface ProductCardProps {
  product: Product;
  variant?: 'default' | 'compact' | 'featured';
}

export function ProductCard({ product, variant = 'default' }: ProductCardProps) {
  const { trackEvent } = useAnalytics();
  const primaryImage = product.images?.find(img => img.isPrimary) || product.images?.[0];
  const primaryLink = product.affiliateLinks?.[0];
  const discount = primaryLink?.originalPrice && primaryLink.currentPrice
    ? Math.round(((primaryLink.originalPrice - primaryLink.currentPrice) / primaryLink.originalPrice) * 100)
    : null;

  const handleAffiliateClick = () => {
    trackEvent('click_outbound', {
      product_id: product.id,
      product_name: product.name,
      platform: primaryLink?.platform,
      price: primaryLink?.currentPrice,
    });
  };

  if (variant === 'compact') {
    return (
      <Link href={`/products/${product.slug}`} className="group">
        <div className="relative aspect-square overflow-hidden rounded-lg bg-gray-100">
          {primaryImage ? (
            <Image
              src={primaryImage.url}
              alt={primaryImage.alt || product.name}
              fill
              className="object-cover transition-transform duration-300 group-hover:scale-105"
              sizes="(max-width: 768px) 50vw, 25vw"
            />
          ) : (
            <div className="flex h-full items-center justify-center text-gray-400">
              No image
            </div>
          )}
          {discount && (
            <Badge className="absolute left-2 top-2 bg-danger-500 text-white">
              -{discount}%
            </Badge>
          )}
        </div>
        <div className="mt-3">
          <h3 className="text-body-sm font-medium text-gray-900 line-clamp-1">
            {product.name}
          </h3>
          <div className="mt-1 flex items-center gap-2">
            <span className="text-body font-semibold text-primary-600">
              {formatPrice(primaryLink?.currentPrice || product.basePrice)}
            </span>
            {primaryLink?.originalPrice && (
              <span className="text-caption text-gray-400 line-through">
                {formatPrice(primaryLink.originalPrice)}
              </span>
            )}
          </div>
        </div>
      </Link>
    );
  }

  return (
    <div className="group relative flex flex-col overflow-hidden rounded-xl bg-white shadow-soft transition-shadow hover:shadow-soft-lg">
      <Link href={`/products/${product.slug}`} className="relative aspect-[4/3] overflow-hidden">
        {primaryImage ? (
          <Image
            src={primaryImage.url}
            alt={primaryImage.alt || product.name}
            fill
            className="object-cover transition-transform duration-500 group-hover:scale-105"
            sizes="(max-width: 768px) 100vw, 33vw"
            priority={variant === 'featured'}
          />
        ) : (
          <div className="flex h-full items-center justify-center bg-gray-100 text-gray-400">
            No image available
          </div>
        )}
        {product.ribbons?.map(ribbon => (
          <Badge
            key={ribbon.id}
            className="absolute left-3 top-3"
            style={{ backgroundColor: ribbon.bgColor, color: ribbon.color }}
          >
            {ribbon.name}
          </Badge>
        ))}
      </Link>
      
      <div className="flex flex-1 flex-col p-4">
        <div className="flex-1">
          {product.categories?.[0] && (
            <span className="text-caption font-medium uppercase tracking-wider text-gray-500">
              {product.categories[0].category.name}
            </span>
          )}
          <Link href={`/products/${product.slug}`}>
            <h3 className="mt-1 text-heading-6 font-semibold text-gray-900 transition-colors hover:text-primary-600 line-clamp-2">
              {product.name}
            </h3>
          </Link>
          <p className="mt-2 text-body-sm text-gray-600 line-clamp-2">
            {product.shortDescription}
          </p>
        </div>
        
        <div className="mt-4 flex items-center justify-between">
          <div className="flex flex-col">
            <span className="text-heading-5 font-bold text-primary-600">
              {formatPrice(primaryLink?.currentPrice || product.basePrice)}
            </span>
            {primaryLink?.originalPrice && (
              <span className="text-body-sm text-gray-400 line-through">
                {formatPrice(primaryLink.originalPrice)}
              </span>
            )}
          </div>
          
          {primaryLink && (
            <Button
              asChild
              size="sm"
              onClick={handleAffiliateClick}
            >
              <a
                href={primaryLink.url}
                target="_blank"
                rel="noopener noreferrer sponsored"
              >
                Buy Now
              </a>
            </Button>
          )}
        </div>
      </div>
    </div>
  );
}
```

### 2.3 Product Grid with Virtualization
```typescript
// components/product/product-grid.tsx
'use client';

import { useCallback, useRef, useState } from 'react';
import { useInView } from 'react-intersection-observer';
import { ProductCard } from './product-card';
import { Product } from '@affiliate-showcase/shared';
import { Skeleton } from '@/components/ui/skeleton';

interface ProductGridProps {
  initialProducts: Product[];
  totalCount: number;
  fetchMore: (page: number) => Promise<Product[]>;
}

export function ProductGrid({ initialProducts, totalCount, fetchMore }: ProductGridProps) {
  const [products, setProducts] = useState(initialProducts);
  const [page, setPage] = useState(1);
  const [loading, setLoading] = useState(false);
  const [hasMore, setHasMore] = useState(products.length < totalCount);
  
  const { ref, inView } = useInView({
    threshold: 0,
    rootMargin: '200px',
  });

  const loadMore = useCallback(async () => {
    if (loading || !hasMore) return;
    
    setLoading(true);
    try {
      const nextPage = page + 1;
      const newProducts = await fetchMore(nextPage);
      
      if (newProducts.length === 0) {
        setHasMore(false);
      } else {
        setProducts(prev => [...prev, ...newProducts]);
        setPage(nextPage);
        setHasMore(products.length + newProducts.length < totalCount);
      }
    } finally {
      setLoading(false);
    }
  }, [page, loading, hasMore, products.length, totalCount, fetchMore]);

  // Auto-load when scrolling
  if (inView && hasMore && !loading) {
    loadMore();
  }

  return (
    <div className="space-y-6">
      <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        {products.map(product => (
          <ProductCard key={product.id} product={product} />
        ))}
        
        {loading && (
          <>
            {[...Array(4)].map((_, i) => (
              <ProductCardSkeleton key={i} />
            ))}
          </>
        )}
      </div>
      
      {/* Infinite scroll trigger */}
      {hasMore && <div ref={ref} className="h-10" />}
      
      {!hasMore && products.length > 0 && (
        <p className="text-center text-gray-500">No more products</p>
      )}
    </div>
  );
}

function ProductCardSkeleton() {
  return (
    <div className="space-y-3">
      <Skeleton className="aspect-[4/3] rounded-xl" />
      <Skeleton className="h-4 w-2/3" />
      <Skeleton className="h-4 w-full" />
      <Skeleton className="h-8 w-24" />
    </div>
  );
}
```

---

## 3. Page Structure

### 3.1 Root Layout
```typescript
// app/layout.tsx
import type { Metadata } from 'next';
import { Inter } from 'next/font/google';
import './globals.css';
import { AnalyticsProvider } from '@/components/analytics/provider';
import { Navbar } from '@/components/layout/navbar';
import { Footer } from '@/components/layout/footer';
import { Toaster } from '@/components/ui/toaster';

const inter = Inter({ subsets: ['latin'], variable: '--font-inter' });

export const metadata: Metadata = {
  title: {
    default: 'Affiliate Showcase - Discover Amazing Products',
    template: '%s | Affiliate Showcase',
  },
  description: 'Discover the best deals and products from top affiliate partners.',
  metadataBase: new URL(process.env.NEXT_PUBLIC_SITE_URL || 'http://localhost:3000'),
  openGraph: {
    type: 'website',
    locale: 'en_US',
    siteName: 'Affiliate Showcase',
  },
  twitter: {
    card: 'summary_large_image',
  },
  robots: {
    index: true,
    follow: true,
  },
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="en" className={inter.variable}>
      <body className="min-h-screen bg-gray-50 font-sans">
        <AnalyticsProvider>
          <div className="flex min-h-screen flex-col">
            <Navbar />
            <main className="flex-1">{children}</main>
            <Footer />
          </div>
          <Toaster />
        </AnalyticsProvider>
      </body>
    </html>
  );
}
```

### 3.2 Home Page
```typescript
// app/(public)/page.tsx
import { Metadata } from 'next';
import { Hero } from '@/components/home/hero';
import { FeaturedCategories } from '@/components/home/featured-categories';
import { ProductGrid } from '@/components/product/product-grid';
import { api } from '@/lib/api';

export const metadata: Metadata = {
  title: 'Discover Amazing Products | Affiliate Showcase',
  description: 'Find the best deals on electronics, fashion, home goods, and more from trusted affiliate partners.',
  alternates: {
    canonical: '/',
  },
};

async function getFeaturedProducts() {
  const res = await api.get('/products?status=PUBLISHED&limit=8&sortBy=popularity');
  return res.data;
}

async function getNewArrivals() {
  const res = await api.get('/products?status=PUBLISHED&limit=8&sortBy=createdAt&sortOrder=desc');
  return res.data;
}

export default async function HomePage() {
  const [featured, newArrivals] = await Promise.all([
    getFeaturedProducts(),
    getNewArrivals(),
  ]);

  return (
    <>
      <Hero />
      
      <section className="py-16">
        <div className="container mx-auto px-4">
          <FeaturedCategories />
        </div>
      </section>

      <section className="bg-white py-16">
        <div className="container mx-auto px-4">
          <div className="mb-8 flex items-center justify-between">
            <div>
              <h2 className="text-heading-3 font-bold text-gray-900">Featured Products</h2>
              <p className="mt-2 text-body text-gray-600">Hand-picked deals you don&apos;t want to miss</p>
            </div>
            <a href="/products?sortBy=popularity" className="text-primary-600 hover:underline">
              View all
            </a>
          </div>
          <ProductGrid 
            initialProducts={featured.data} 
            totalCount={featured.meta.total}
            fetchMore={(page) => api.get(`/products?status=PUBLISHED&limit=8&sortBy=popularity&page=${page}`).then(r => r.data.data)}
          />
        </div>
      </section>

      <section className="py-16">
        <div className="container mx-auto px-4">
          <div className="mb-8">
            <h2 className="text-heading-3 font-bold text-gray-900">New Arrivals</h2>
            <p className="mt-2 text-body text-gray-600">The latest products added to our collection</p>
          </div>
          <ProductGrid 
            initialProducts={newArrivals.data} 
            totalCount={newArrivals.meta.total}
            fetchMore={(page) => api.get(`/products?status=PUBLISHED&limit=8&sortBy=createdAt&sortOrder=desc&page=${page}`).then(r => r.data.data)}
          />
        </div>
      </section>
    </>
  );
}
```

### 3.3 Product Detail Page
```typescript
// app/(public)/products/[slug]/page.tsx
import { Metadata } from 'next';
import { notFound } from 'next/navigation';
import { api } from '@/lib/api';
import { ProductGallery } from '@/components/product/product-gallery';
import { ProductInfo } from '@/components/product/product-info';
import { ProductTabs } from '@/components/product/product-tabs';
import { SimilarProducts } from '@/components/product/similar-products';
import { Breadcrumbs } from '@/components/ui/breadcrumbs';
import { JsonLd } from '@/components/seo/json-ld';

interface ProductPageProps {
  params: { slug: string };
}

async function getProduct(slug: string) {
  try {
    const res = await api.get(`/products/${slug}`);
    return res.data;
  } catch {
    return null;
  }
}

export async function generateMetadata({ params }: ProductPageProps): Promise<Metadata> {
  const product = await getProduct(params.slug);
  
  if (!product) {
    return { title: 'Product Not Found' };
  }

  return {
    title: product.metaTitle || product.name,
    description: product.metaDescription || product.shortDescription,
    alternates: {
      canonical: `/products/${product.slug}`,
    },
    openGraph: {
      title: product.name,
      description: product.shortDescription,
      images: product.images?.map((img: { url: string; alt?: string }) => ({
        url: img.url,
        alt: img.alt,
      })),
    },
  };
}

export default async function ProductPage({ params }: ProductPageProps) {
  const product = await getProduct(params.slug);

  if (!product) {
    notFound();
  }

  const structuredData = {
    '@context': 'https://schema.org',
    '@type': 'Product',
    name: product.name,
    description: product.description,
    image: product.images?.map((img: { url: string }) => img.url),
    brand: {
      '@type': 'Brand',
      name: product.attributes?.find((a: { attribute: { name: string } }) => a.attribute.name === 'brand')?.value,
    },
    offers: product.affiliateLinks?.map((link: { currentPrice: number; originalPrice: number; url: string }) => ({
      '@type': 'Offer',
      price: (link.currentPrice / 100).toFixed(2),
      priceCurrency: 'USD',
      availability: link.inStock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
      url: link.url,
      priceValidUntil: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString(),
    })),
  };

  return (
    <>
      <JsonLd data={structuredData} />
      
      <div className="container mx-auto px-4 py-8">
        <Breadcrumbs
          items={[
            { label: 'Home', href: '/' },
            ...(product.categories?.[0] ? [
              { label: product.categories[0].category.name, href: `/categories/${product.categories[0].category.slug}` },
            ] : []),
            { label: product.name, href: '#' },
          ]}
        />

        <div className="mt-8 grid gap-8 lg:grid-cols-2">
          <ProductGallery images={product.images} />
          <ProductInfo product={product} />
        </div>

        <div className="mt-16">
          <ProductTabs product={product} />
        </div>

        <div className="mt-16">
          <SimilarProducts 
            categoryId={product.categories?.[0]?.categoryId} 
            currentProductId={product.id}
          />
        </div>
      </div>
    </>
  );
}
```

---

## 4. SEO Implementation

### 4.1 Sitemap Generation
```typescript
// app/sitemap.ts
import { MetadataRoute } from 'next';
import { api } from '@/lib/api';

export default async function sitemap(): Promise<MetadataRoute.Sitemap> {
  const baseUrl = process.env.NEXT_PUBLIC_SITE_URL || 'http://localhost:3000';
  
  // Static pages
  const staticPages = [
    { url: baseUrl, lastModified: new Date(), changeFrequency: 'daily' as const, priority: 1 },
    { url: `${baseUrl}/products`, lastModified: new Date(), changeFrequency: 'daily' as const, priority: 0.9 },
  ];
  
  // Dynamic product pages
  const productsRes = await api.get('/products?status=PUBLISHED&limit=1000');
  const productPages = productsRes.data.data.map((product: { slug: string; updatedAt: string }) => ({
    url: `${baseUrl}/products/${product.slug}`,
    lastModified: new Date(product.updatedAt),
    changeFrequency: 'weekly' as const,
    priority: 0.8,
  }));
  
  // Category pages
  const categoriesRes = await api.get('/categories');
  const categoryPages = categoriesRes.data.map((category: { slug: string; updatedAt: string }) => ({
    url: `${baseUrl}/categories/${category.slug}`,
    lastModified: new Date(category.updatedAt),
    changeFrequency: 'weekly' as const,
    priority: 0.7,
  }));

  return [...staticPages, ...productPages, ...categoryPages];
}
```

### 4.2 Robots.txt
```typescript
// app/robots.ts
import { MetadataRoute } from 'next';

export default function robots(): MetadataRoute.Robots {
  const baseUrl = process.env.NEXT_PUBLIC_SITE_URL || 'http://localhost:3000';
  
  return {
    rules: [
      {
        userAgent: '*',
        allow: '/',
        disallow: ['/admin/', '/api/', '/_next/', '/private/'],
      },
      {
        userAgent: 'GPTBot',
        disallow: '/',
      },
    ],
    sitemap: `${baseUrl}/sitemap.xml`,
    host: baseUrl,
  };
}
```

---

## 5. Performance Optimization

### 5.1 Image Optimization Strategy
```typescript
// lib/image-loader.ts
import { ImageLoaderProps } from 'next/image';

export function customImageLoader({ src, width, quality }: ImageLoaderProps): string {
  // If using external CDN with image optimization
  if (src.startsWith('https://cdn.')) {
    const params = new URLSearchParams({
      url: src,
      w: width.toString(),
      q: (quality || 75).toString(),
    });
    return `${process.env.NEXT_PUBLIC_IMAGE_OPTIMIZER_URL}/?${params}`;
  }
  
  // Local images
  return src;
}

// next.config.js
module.exports = {
  images: {
    loader: 'custom',
    loaderFile: './lib/image-loader.ts',
    deviceSizes: [640, 750, 828, 1080, 1200, 1920, 2048],
    imageSizes: [16, 32, 48, 64, 96, 128, 256, 384],
    formats: ['image/webp', 'image/avif'],
    minimumCacheTTL: 60 * 60 * 24 * 30, // 30 days
  },
};
```

### 5.2 API Response Caching
```typescript
// lib/api.ts
import { unstable_cache } from 'next/cache';

export const api = {
  async get(url: string, options?: RequestInit) {
    const res = await fetch(`${process.env.API_URL}${url}`, {
      ...options,
      next: {
        revalidate: 60, // ISR: revalidate every 60 seconds
        tags: [url],
      },
    });
    
    if (!res.ok) throw new Error(`API error: ${res.status}`);
    return res.json();
  },
  
  // Cached product fetch for static generation
  getProduct: unstable_cache(
    async (slug: string) => {
      const res = await fetch(`${process.env.API_URL}/products/${slug}`);
      return res.json();
    },
    ['product'],
    { revalidate: 300, tags: ['products'] }
  ),
};
```

---

## 6. Verification Checklist

| Metric | Target | Status |
|--------|--------|--------|
| Lighthouse Performance | 95+ | ⬜ |
| Lighthouse Accessibility | 100 | ⬜ |
| Lighthouse SEO | 100 | ⬜ |
| Core Web Vitals (LCP) | < 2.5s | ⬜ |
| Core Web Vitals (FID) | < 100ms | ⬜ |
| Core Web Vitals (CLS) | < 0.1 | ⬜ |
| Mobile Responsive | All breakpoints | ⬜ |
| Schema.org validation | Pass | ⬜ |
| Analytics tracking | Working | ⬜ |

---

[← Back to Master Plan](./master-plan.md) | [Previous: Phase 2 - Backend Core](./phase-02-backend-core.md) | [Next: Phase 4 - Analytics Engine →](./phase-04-analytics-engine.md)
