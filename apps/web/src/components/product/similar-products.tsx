"use client"

import { useEffect, useState } from "react"
import { ProductCard } from "./product-card"
import type { Product } from "@/types"
import { Skeleton } from "@/components/ui/skeleton"
import { cn } from "@/lib/utils"

interface SimilarProductsProps {
  categoryId?: string
  currentProductId: string
  className?: string
}

export function SimilarProducts({
  categoryId,
  currentProductId,
  className,
}: SimilarProductsProps) {
  const [products, setProducts] = useState<Product[]>([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    async function fetchSimilar() {
      try {
        // This would be replaced with actual API call
        // const res = await fetch(`/api/products?categoryId=${categoryId}&limit=4&exclude=${currentProductId}`)
        // const data = await res.json()
        // setProducts(data.data)
        
        // For now, simulate empty state
        setProducts([])
      } catch {
        // Silently fail - similar products are not critical
      } finally {
        setLoading(false)
      }
    }

    if (categoryId) {
      fetchSimilar()
    } else {
      setLoading(false)
    }
  }, [categoryId, currentProductId])

  if (!loading && products.length === 0) {
    return null
  }

  return (
    <section className={cn("py-8", className)}>
      <h2 className="mb-6 text-2xl font-bold">Similar Products</h2>
      
      {loading ? (
        <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
          {[...Array(4)].map((_, i) => (
            <div key={i} className="space-y-3">
              <Skeleton className="aspect-[4/3] rounded-xl" />
              <Skeleton className="h-4 w-2/3" />
              <Skeleton className="h-4 w-full" />
            </div>
          ))}
        </div>
      ) : (
        <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
          {products.map((product) => (
            <ProductCard key={product.id} product={product} variant="compact" />
          ))}
        </div>
      )}
    </section>
  )
}
