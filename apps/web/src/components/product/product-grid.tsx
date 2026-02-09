"use client"

import { useCallback, useState } from "react"
import { useInView } from "react-intersection-observer"
import { ProductCard } from "./product-card"
import type { Product } from "@/types"
import { Skeleton } from "@/components/ui/skeleton"
import { cn } from "@/lib/utils"

interface ProductGridProps {
  initialProducts: Product[]
  totalCount: number
  fetchMore: (page: number) => Promise<Product[]>
  className?: string
}

export function ProductGrid({
  initialProducts,
  totalCount,
  fetchMore,
  className,
}: ProductGridProps) {
  const [products, setProducts] = useState<Product[]>(initialProducts)
  const [page, setPage] = useState(1)
  const [loading, setLoading] = useState(false)
  const [hasMore, setHasMore] = useState(products.length < totalCount)

  const { ref, inView } = useInView({
    threshold: 0,
    rootMargin: "200px",
  })

  const loadMore = useCallback(async () => {
    if (loading || !hasMore) return

    setLoading(true)
    try {
      const nextPage = page + 1
      const newProducts = await fetchMore(nextPage)

      if (newProducts.length === 0) {
        setHasMore(false)
      } else {
        setProducts((prev) => [...prev, ...newProducts])
        setPage(nextPage)
        setHasMore(products.length + newProducts.length < totalCount)
      }
    } finally {
      setLoading(false)
    }
  }, [page, loading, hasMore, products.length, totalCount, fetchMore])

  // Auto-load when scrolling
  if (inView && hasMore && !loading) {
    loadMore()
  }

  return (
    <div className={cn("space-y-6", className)}>
      <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        {products.map((product) => (
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
      {hasMore && <div ref={ref} className="h-10" aria-hidden="true" />}

      {!hasMore && products.length > 0 && (
        <p className="text-center text-muted-foreground">No more products</p>
      )}

      {!loading && products.length === 0 && (
        <div className="flex h-64 items-center justify-center">
          <p className="text-muted-foreground">No products found</p>
        </div>
      )}
    </div>
  )
}

function ProductCardSkeleton() {
  return (
    <div className="space-y-3">
      <Skeleton className="aspect-[4/3] rounded-xl" />
      <Skeleton className="h-4 w-2/3" />
      <Skeleton className="h-4 w-full" />
      <Skeleton className="h-8 w-24" />
    </div>
  )
}
