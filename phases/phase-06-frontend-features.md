# Phase 6: Frontend Features

**Duration**: 3 weeks  
**Goal**: Complete admin and storefront UI  
**Prerequisites**: Phase 5 complete (Frontend Foundation)

---

## Week 1: Public Storefront

### Day 1-2: Homepage

#### Tasks
- [ ] Create hero section
- [ ] Build featured products carousel
- [ ] Add category showcase grid
- [ ] Implement trending products section

#### app/(marketing)/page.tsx
```typescript
import { Suspense } from 'react';
import { HeroSection } from '@/components/home/hero-section';
import { FeaturedProducts } from '@/components/home/featured-products';
import { CategoryShowcase } from '@/components/home/category-showcase';
import { TrendingProducts } from '@/components/home/trending-products';
import { ProductSkeleton } from '@/components/skeletons/product-skeleton';

export default function HomePage() {
  return (
    <main className="flex-1">
      <HeroSection />
      
      <section className="container py-12">
        <h2 className="mb-6 text-2xl font-bold">Featured Products</h2>
        <Suspense fallback={<ProductSkeleton count={4} />}>
          <FeaturedProducts />
        </Suspense>
      </section>
      
      <section className="bg-muted py-12">
        <div className="container">
          <h2 className="mb-6 text-2xl font-bold">Shop by Category</h2>
          <Suspense fallback={<CategorySkeleton />}>
            <CategoryShowcase />
          </Suspense>
        </div>
      </section>
      
      <section className="container py-12">
        <h2 className="mb-6 text-2xl font-bold">Trending Now</h2>
        <Suspense fallback={<ProductSkeleton count={4} />}>
          <TrendingProducts />
        </Suspense>
      </section>
    </main>
  );
}
```

#### components/home/featured-products.tsx
```typescript
import { api } from '@/lib/api';
import { ProductCard } from '@/components/products/product-card';

export async function FeaturedProducts() {
  const { data: products } = await api.getProducts({
    status: 'PUBLISHED',
    limit: 8,
    sortBy: 'date',
    sortOrder: 'desc',
  });

  return (
    <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
      {products.map((product) => (
        <ProductCard key={product.id} product={product} />
      ))}
    </div>
  );
}
```

### Day 3-4: Product Listing

#### Tasks
- [ ] Create product grid with pagination
- [ ] Build filter sidebar
- [ ] Add sorting options
- [ ] Implement grid/list view toggle

#### app/(store)/products/page.tsx
```typescript
'use client';

import { useState } from 'react';
import { useSearchParams } from 'next/navigation';
import { useQuery } from '@tanstack/react-query';
import { api } from '@/lib/api';
import { ProductGrid } from '@/components/products/product-grid';
import { ProductFilters } from '@/components/products/product-filters';
import { SortDropdown } from '@/components/products/sort-dropdown';
import { ViewToggle } from '@/components/products/view-toggle';
import { Pagination } from '@/components/ui/pagination';

export default function ProductsPage() {
  const searchParams = useSearchParams();
  const [view, setView] = useState<'grid' | 'list'>('grid');
  
  const page = parseInt(searchParams.get('page') || '1');
  const category = searchParams.get('category');
  const sortBy = searchParams.get('sortBy') || 'date';
  const search = searchParams.get('q');

  const { data, isLoading } = useQuery({
    queryKey: ['products', { page, category, sortBy, search }],
    queryFn: () =>
      api.getProducts({
        page,
        limit: 24,
        categoryId: category || undefined,
        sortBy,
        search: search || undefined,
      }),
  });

  return (
    <div className="container py-8">
      <div className="flex flex-col gap-8 lg:flex-row">
        {/* Filters Sidebar */}
        <aside className="w-full lg:w-64">
          <ProductFilters />
        </aside>

        {/* Main Content */}
        <main className="flex-1">
          {/* Toolbar */}
          <div className="mb-6 flex items-center justify-between">
            <p className="text-sm text-muted-foreground">
              {data?.meta?.total || 0} products found
            </p>
            <div className="flex items-center gap-4">
              <SortDropdown value={sortBy} />
              <ViewToggle value={view} onChange={setView} />
            </div>
          </div>

          {/* Product Grid */}
          <ProductGrid
            products={data?.data || []}
            isLoading={isLoading}
            view={view}
          />

          {/* Pagination */}
          {data?.meta && (
            <Pagination
              currentPage={data.meta.page}
              totalPages={data.meta.totalPages}
              total={data.meta.total}
            />
          )}
        </main>
      </div>
    </div>
  );
}
```

### Day 5: Product Detail Page

#### Tasks
- [ ] Create image gallery with zoom
- [ ] Build variant selector
- [ ] Add price display with ribbons
- [ ] Implement related products

#### app/(store)/products/[slug]/page.tsx
```typescript
import { notFound } from 'next/navigation';
import { api } from '@/lib/api';
import { ImageGallery } from '@/components/products/image-gallery';
import { VariantSelector } from '@/components/products/variant-selector';
import { ProductInfo } from '@/components/products/product-info';
import { RelatedProducts } from '@/components/products/related-products';
import { AffiliateButton } from '@/components/affiliate/affiliate-button';

interface ProductPageProps {
  params: { slug: string };
}

export async function generateMetadata({ params }: ProductPageProps) {
  const product = await api.getProductBySlug(params.slug);
  if (!product) return { title: 'Product Not Found' };
  
  return {
    title: `${product.name} | Affiliate Store`,
    description: product.metaDescription || product.shortDescription,
    openGraph: {
      images: product.images[0]?.url,
    },
  };
}

export default async function ProductPage({ params }: ProductPageProps) {
  const product = await api.getProductBySlug(params.slug);
  
  if (!product) {
    notFound();
  }

  return (
    <div className="container py-8">
      <div className="grid gap-8 lg:grid-cols-2">
        {/* Image Gallery */}
        <ImageGallery images={product.images} />

        {/* Product Info */}
        <div className="space-y-6">
          <ProductInfo product={product} />
          
          <VariantSelector 
            variants={product.variants}
            defaultVariant={product.variants.find((v) => v.isDefault)}
          />
          
          {/* Affiliate Link Button */}
          <AffiliateButton productId={product.id} />
          
          {/* Product Description */}
          <div className="prose max-w-none">
            <div dangerouslySetInnerHTML={{ __html: product.description }} />
          </div>
        </div>
      </div>

      {/* Related Products */}
      <section className="mt-16">
        <h2 className="mb-6 text-2xl font-bold">You May Also Like</h2>
        <RelatedProducts 
          categoryId={product.categories[0]?.categoryId}
          excludeId={product.id}
        />
      </section>
    </div>
  );
}
```

---

## Week 2: Search & Admin Shell

### Day 6-7: Search Implementation

#### Tasks
- [ ] Build search input with autocomplete
- [ ] Create search results page
- [ ] Add filter chips
- [ ] Implement search analytics

#### components/search/search-input.tsx
```typescript
'use client';

import { useState, useCallback } from 'react';
import { useRouter } from 'next/navigation';
import { useQuery } from '@tanstack/react-query';
import { debounce } from 'lodash';
import { api } from '@/lib/api';
import { Input } from '@/components/ui/input';
import {
  Command,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
  CommandList,
} from '@/components/ui/command';
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover';
import { Icons } from '@/components/icons';

export function SearchInput() {
  const router = useRouter();
  const [open, setOpen] = useState(false);
  const [query, setQuery] = useState('');

  const debouncedSearch = useCallback(
    debounce((value: string) => {
      setQuery(value);
    }, 300),
    [],
  );

  const { data: suggestions } = useQuery({
    queryKey: ['search-suggestions', query],
    queryFn: () => api.searchSuggestions(query),
    enabled: query.length >= 2,
  });

  const handleSelect = (value: string) => {
    setOpen(false);
    router.push(`/search?q=${encodeURIComponent(value)}`);
  };

  return (
    <Popover open={open} onOpenChange={setOpen}>
      <PopoverTrigger asChild>
        <div className="relative w-full max-w-sm">
          <Icons.search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
          <Input
            placeholder="Search products..."
            className="pl-9"
            onFocus={() => setOpen(true)}
          />
        </div>
      </PopoverTrigger>
      <PopoverContent className="w-[400px] p-0" align="start">
        <Command>
          <CommandInput
            placeholder="Type to search..."
            onValueChange={debouncedSearch}
          />
          <CommandList>
            <CommandEmpty>No results found.</CommandEmpty>
            {suggestions && suggestions.length > 0 && (
              <CommandGroup heading="Suggestions">
                {suggestions.map((suggestion) => (
                  <CommandItem
                    key={suggestion}
                    onSelect={() => handleSelect(suggestion)}
                  >
                    <Icons.search className="mr-2 h-4 w-4" />
                    {suggestion}
                  </CommandItem>
                ))}
              </CommandGroup>
            )}
          </CommandList>
        </Command>
      </PopoverContent>
    </Popover>
  );
}
```

### Day 8-9: Admin Dashboard Shell

#### Tasks
- [ ] Create admin layout with sidebar
- [ ] Build admin navigation
- [ ] Implement admin route guards
- [ ] Create dashboard stats cards

#### app/(admin)/layout.tsx
```typescript
import { redirect } from 'next/navigation';
import { getServerSession } from 'next-auth';
import { authOptions } from '@/app/api/auth/[...nextauth]/route';
import { AdminSidebar } from '@/components/admin/admin-sidebar';
import { AdminHeader } from '@/components/admin/admin-header';

export default async function AdminLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  const session = await getServerSession(authOptions);
  
  if (!session?.user?.roles?.includes('ADMIN')) {
    redirect('/login?callbackUrl=/admin');
  }

  return (
    <div className="flex min-h-screen">
      <AdminSidebar />
      <div className="flex flex-1 flex-col">
        <AdminHeader user={session.user} />
        <main className="flex-1 bg-muted/50 p-6">{children}</main>
      </div>
    </div>
  );
}
```

#### app/(admin)/page.tsx
```typescript
import { api } from '@/lib/api';
import { StatsCards } from '@/components/admin/dashboard/stats-cards';
import { RecentActivity } from '@/components/admin/dashboard/recent-activity';
import { SalesChart } from '@/components/admin/dashboard/sales-chart';
import { TopProducts } from '@/components/admin/dashboard/top-products';

export default async function AdminDashboard() {
  const stats = await api.getDashboardStats();

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold">Dashboard</h1>
        <p className="text-muted-foreground">
          Welcome back! Here&apos;s what&apos;s happening with your store.
        </p>
      </div>

      <StatsCards data={stats.overview} />

      <div className="grid gap-6 lg:grid-cols-2">
        <SalesChart data={stats.salesChart} />
        <TopProducts products={stats.topProducts} />
      </div>

      <RecentActivity activities={stats.recentActivity} />
    </div>
  );
}
```

### Day 10: Admin Product Management

#### Tasks
- [ ] Create product data table
- [ ] Implement bulk operations
- [ ] Add product filters
- [ ] Build export functionality

---

## Week 3: Admin Features Completion

### Day 11-13: Product Editor & Category Management

#### Tasks
- [ ] Build rich text editor (TipTap)
- [ ] Create image upload with gallery
- [ ] Implement variant management
- [ ] Build category tree with drag-drop
- [ ] Add SEO metadata editor

#### components/admin/products/product-form.tsx
```typescript
'use client';

import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { api } from '@/lib/api';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { RichTextEditor } from '@/components/admin/rich-text-editor';
import { ImageUpload } from '@/components/admin/image-upload';
import { VariantManager } from '@/components/admin/variant-manager';
import { CategorySelect } from '@/components/admin/category-select';
import { SeoFields } from '@/components/admin/seo-fields';

const productSchema = z.object({
  name: z.string().min(1, 'Name is required'),
  slug: z.string().min(1, 'Slug is required'),
  description: z.string(),
  shortDescription: z.string().max(500),
  status: z.enum(['DRAFT', 'PENDING_REVIEW', 'PUBLISHED', 'ARCHIVED']),
  metaTitle: z.string().optional(),
  metaDescription: z.string().optional(),
  categoryIds: z.array(z.string()),
  variants: z.array(variantSchema).min(1, 'At least one variant required'),
});

type ProductFormData = z.infer<typeof productSchema>;

interface ProductFormProps {
  product?: Product;
  onSubmit: (data: ProductFormData) => Promise<void>;
}

export function ProductForm({ product, onSubmit }: ProductFormProps) {
  const {
    register,
    handleSubmit,
    control,
    watch,
    formState: { errors, isSubmitting },
  } = useForm<ProductFormData>({
    resolver: zodResolver(productSchema),
    defaultValues: product || {
      status: 'DRAFT',
      variants: [{ name: 'Default', price: 0, isDefault: true }],
    },
  });

  return (
    <form onSubmit={handleSubmit(onSubmit)} className="space-y-8">
      <div className="grid gap-6 lg:grid-cols-3">
        {/* Main Content */}
        <div className="lg:col-span-2 space-y-6">
          <div className="rounded-lg border bg-card p-6">
            <h3 className="mb-4 text-lg font-semibold">Basic Information</h3>
            <div className="space-y-4">
              <div>
                <Label htmlFor="name">Product Name</Label>
                <Input id="name" {...register('name')} />
                {errors.name && (
                  <p className="text-sm text-red-500">{errors.name.message}</p>
                )}
              </div>
              
              <div>
                <Label htmlFor="slug">Slug</Label>
                <Input id="slug" {...register('slug')} />
              </div>
              
              <div>
                <Label>Description</Label>
                <RichTextEditor name="description" control={control} />
              </div>
              
              <div>
                <Label htmlFor="shortDescription">Short Description</Label>
                <Textarea id="shortDescription" {...register('shortDescription')} />
              </div>
            </div>
          </div>

          <div className="rounded-lg border bg-card p-6">
            <h3 className="mb-4 text-lg font-semibold">Variants</h3>
            <VariantManager control={control} />
          </div>

          <div className="rounded-lg border bg-card p-6">
            <h3 className="mb-4 text-lg font-semibold">Images</h3>
            <ImageUpload
              productId={product?.id}
              images={product?.images}
            />
          </div>
        </div>

        {/* Sidebar */}
        <div className="space-y-6">
          <div className="rounded-lg border bg-card p-6">
            <h3 className="mb-4 text-lg font-semibold">Publishing</h3>
            <div className="space-y-4">
              <div>
                <Label htmlFor="status">Status</Label>
                <select
                  id="status"
                  {...register('status')}
                  className="w-full rounded-md border p-2"
                >
                  <option value="DRAFT">Draft</option>
                  <option value="PENDING_REVIEW">Pending Review</option>
                  <option value="PUBLISHED">Published</option>
                  <option value="ARCHIVED">Archived</option>
                </select>
              </div>
              
              <Button type="submit" disabled={isSubmitting} className="w-full">
                {isSubmitting ? 'Saving...' : product ? 'Update' : 'Create'}
              </Button>
            </div>
          </div>

          <div className="rounded-lg border bg-card p-6">
            <h3 className="mb-4 text-lg font-semibold">Categories</h3>
            <CategorySelect control={control} name="categoryIds" />
          </div>

          <SeoFields register={register} />
        </div>
      </div>
    </form>
  );
}
```

### Day 14-15: Admin Features Completion

#### Tasks
- [ ] User management interface
- [ ] Analytics dashboard integration
- [ ] Affiliate management pages
- [ ] Settings pages

---

## Deliverables Checklist

### Public Storefront
- [ ] Homepage with sections
- [ ] Product listing with filters
- [ ] Product detail page
- [ ] Search with autocomplete
- [ ] Category browsing
- [ ] Mobile responsive

### Admin Dashboard
- [ ] Dashboard with stats
- [ ] Product CRUD with rich editor
- [ ] Category tree management
- [ ] Image upload and gallery
- [ ] Variant management
- [ ] User management
- [ ] Analytics views
- [ ] Affiliate management

## Success Metrics

| Metric | Target | Measurement |
|--------|--------|-------------|
| Homepage load | < 1.5s | Lighthouse LCP |
| Product page load | < 2s | Lighthouse LCP |
| Search response | < 200ms | Autocomplete API |
| Admin form save | < 1s | Form submission |
| Mobile usability | 100 | Lighthouse score |

## Next Phase Handoff

**Phase 7 Prerequisites:**
- [ ] All storefront pages functional
- [ ] Admin CRUD working
- [ ] Image upload working
- [ ] Search functional
- [ ] Forms with validation
