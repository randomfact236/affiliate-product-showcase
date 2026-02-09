"use client"

import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"
import { cn, formatPrice } from "@/lib/utils"
import type { Product } from "@/types"
import Link from "next/link"

interface ProductInfoProps {
  product: Product
  className?: string
}

export function ProductInfo({ product, className }: ProductInfoProps) {
  const primaryLink = product.affiliateLinks?.[0]
  const discount =
    primaryLink?.originalPrice && primaryLink.currentPrice
      ? Math.round(
          ((primaryLink.originalPrice - primaryLink.currentPrice) /
            primaryLink.originalPrice) *
            100
        )
      : null

  const brandAttribute = product.attributes?.find(
    (attr) => attr.attribute.name.toLowerCase() === "brand"
  )

  return (
    <div className={cn("space-y-6", className)}>
      {/* Breadcrumbs */}
      {product.categories?.[0] && (
        <nav aria-label="Breadcrumb">
          <ol className="flex items-center space-x-2 text-sm text-muted-foreground">
            <li>
              <Link href="/" className="hover:text-foreground">
                Home
              </Link>
            </li>
            <li>/</li>
            <li>
              <Link
                href={`/categories/${product.categories[0].category.slug}`}
                className="hover:text-foreground"
              >
                {product.categories[0].category.name}
              </Link>
            </li>
            <li>/</li>
            <li className="text-foreground" aria-current="page">
              {product.name}
            </li>
          </ol>
        </nav>
      )}

      {/* Title */}
      <div>
        {brandAttribute && (
          <p className="text-sm text-muted-foreground">{brandAttribute.value}</p>
        )}
        <h1 className="text-3xl font-bold tracking-tight">{product.name}</h1>
      </div>

      {/* Price */}
      <div className="flex items-center gap-4">
        <span className="text-3xl font-bold text-primary">
          {formatPrice(primaryLink?.currentPrice || product.basePrice)}
        </span>
        {primaryLink?.originalPrice && (
          <>
            <span className="text-lg text-muted-foreground line-through">
              {formatPrice(primaryLink.originalPrice)}
            </span>
            {discount && (
              <Badge variant="destructive">Save {discount}%</Badge>
            )}
          </>
        )}
      </div>

      {/* Short Description */}
      <p className="text-muted-foreground">{product.shortDescription}</p>

      {/* Affiliate Links */}
      {product.affiliateLinks && product.affiliateLinks.length > 0 && (
        <div className="space-y-3">
          <h3 className="font-semibold">Available at:</h3>
          <div className="flex flex-wrap gap-2">
            {product.affiliateLinks.map((link) => (
              <Button
                key={link.id}
                variant={link === primaryLink ? "default" : "outline"}
                size="sm"
                asChild
                onClick={() => {
                  if (typeof window !== "undefined") {
                    window.dispatchEvent(
                      new CustomEvent("affiliate-click", {
                        detail: {
                          productId: product.id,
                          productName: product.name,
                          platform: link.platform,
                          price: link.currentPrice,
                        },
                      })
                    )
                  }
                }}
              >
                <a
                  href={link.url}
                  target="_blank"
                  rel="noopener noreferrer sponsored"
                  aria-label={`Buy ${product.name} from ${link.platform} for ${formatPrice(link.currentPrice)}`}
                >
                  {link.platform} - {formatPrice(link.currentPrice)}
                  {!link.inStock && " (Out of Stock)"}
                </a>
              </Button>
            ))}
          </div>
        </div>
      )}

      {/* Product Details Tabs */}
      <Tabs defaultValue="description" className="pt-6">
        <TabsList>
          <TabsTrigger value="description">Description</TabsTrigger>
          {product.attributes && product.attributes.length > 0 && (
            <TabsTrigger value="specifications">Specifications</TabsTrigger>
          )}
        </TabsList>
        <TabsContent value="description" className="pt-4">
          <div className="prose prose-sm max-w-none">
            {product.description ? (
              <div dangerouslySetInnerHTML={{ __html: product.description }} />
            ) : (
              <p className="text-muted-foreground">
                No description available for this product.
              </p>
            )}
          </div>
        </TabsContent>
        {product.attributes && product.attributes.length > 0 && (
          <TabsContent value="specifications" className="pt-4">
            <dl className="grid grid-cols-1 gap-4 sm:grid-cols-2">
              {product.attributes.map((attr) => (
                <div key={attr.id}>
                  <dt className="text-sm font-medium text-muted-foreground">
                    {attr.attribute.name}
                  </dt>
                  <dd className="mt-1 text-sm">{attr.value}</dd>
                </div>
              ))}
            </dl>
          </TabsContent>
        )}
      </Tabs>
    </div>
  )
}
