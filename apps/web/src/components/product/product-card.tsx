"use client"

import Image from "next/image"
import Link from "next/link"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Card } from "@/components/ui/card"
import { cn, formatPrice } from "@/lib/utils"
import type { Product } from "@/types"
import { Bookmark, Check, Users, ExternalLink, Star } from "lucide-react"

interface ProductCardProps {
  product: Product
  variant?: "default" | "compact" | "featured"
  className?: string
}

export function ProductCard({
  product,
  variant = "default",
  className,
}: ProductCardProps) {
  const primaryImage = product.images?.find((img) => img.isPrimary) || product.images?.[0]
  const primaryLink = product.affiliateLinks?.[0]
  const discount =
    primaryLink?.originalPrice && primaryLink.currentPrice
      ? Math.round(
          ((primaryLink.originalPrice - primaryLink.currentPrice) /
            primaryLink.originalPrice) *
            100
        )
      : null

  // Mock data for features and reviews (would come from API in production)
  const features = product.features?.slice(0, 4) || [
    "Easy Setup",
    "Analytics",
    "Responsive",
    "API Access"
  ]
  const rating = product.rating || 4.5
  const reviewCount = product.reviewCount || 1234
  const userCount = "1K+"

  if (variant === "compact") {
    return (
      <Link
        href={`/products/${product.slug}`}
        className={cn("group block", className)}
        aria-label={`View ${product.name}`}
      >
        <div className="relative aspect-square overflow-hidden rounded-lg bg-muted">
          {primaryImage ? (
            <Image
              src={primaryImage.url}
              alt={primaryImage.alt || product.name}
              fill
              className="object-cover transition-transform duration-300 group-hover:scale-105"
              sizes="(max-width: 768px) 50vw, 25vw"
            />
          ) : (
            <div className="flex h-full items-center justify-center text-muted-foreground">
              No image
            </div>
          )}
          {discount && (
            <Badge className="absolute left-2 top-2 bg-destructive text-destructive-foreground">
              -{discount}%
            </Badge>
          )}
        </div>
        <div className="mt-3">
          <h3 className="text-sm font-medium text-foreground line-clamp-1">
            {product.name}
          </h3>
          <div className="mt-1 flex items-center gap-2">
            <span className="text-base font-semibold text-primary">
              {formatPrice(primaryLink?.currentPrice || product.basePrice)}
            </span>
            {primaryLink?.originalPrice && (
              <span className="text-xs text-muted-foreground line-through">
                {formatPrice(primaryLink.originalPrice)}
              </span>
            )}
          </div>
        </div>
      </Link>
    )
  }

  return (
    <Card
      className={cn(
        "group flex flex-col overflow-hidden transition-shadow hover:shadow-lg border border-gray-200",
        className
      )}
    >
      {/* Featured Tag - Top */}
      {product.isFeatured && (
        <div className="bg-blue-600 text-white text-xs font-bold px-4 py-1.5 flex items-center justify-center gap-1">
          <Star className="h-3 w-3 fill-current" />
          FEATURED
        </div>
      )}

      {/* Image Container - Larger */}
      <div className="relative aspect-[16/10] overflow-hidden bg-gradient-to-br from-blue-50 to-purple-50">
        {/* Bookmark Icon */}
        <button className="absolute top-3 left-3 z-10 p-2 bg-white rounded-full shadow-sm hover:shadow-md transition-shadow">
          <Bookmark className="h-4 w-4 text-gray-600" />
        </button>

        {/* Viewed Badge */}
        <div className="absolute top-3 right-3 z-10 px-3 py-1 bg-white/90 backdrop-blur rounded-full text-xs font-medium text-gray-700 flex items-center gap-1">
          <span className="w-2 h-2 bg-orange-400 rounded-full"></span>
          {product.viewCount || Math.floor(product.id.charCodeAt(0) * 10 + 100)} viewed
        </div>

        <Link
          href={`/products/${product.slug}`}
          aria-label={`View ${product.name}`}
        >
          {primaryImage ? (
            <Image
              src={primaryImage.url}
              alt={primaryImage.alt || product.name}
              fill
              className="object-cover transition-transform duration-500 group-hover:scale-105"
              sizes="(max-width: 768px) 100vw, 33vw"
              priority={variant === "featured"}
            />
          ) : (
            <div className="flex h-full items-center justify-center text-gray-400">
              <span className="text-lg">Preview</span>
            </div>
          )}
        </Link>
      </div>

      {/* Content */}
      <div className="flex flex-1 flex-col p-5">
        {/* Header: Logo + Title + Price */}
        <div className="flex items-start justify-between gap-2 mb-2">
          <div className="flex items-center gap-2 flex-1 min-w-0">
            {/* Logo placeholder */}
            <div className="w-6 h-6 bg-orange-500 rounded flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
              {product.name.charAt(0)}
            </div>
            <h3 className="text-lg font-bold text-gray-900 line-clamp-1">
              {product.name}
            </h3>
          </div>
          
          {/* Price Section */}
          <div className="text-right flex-shrink-0">
            {primaryLink?.originalPrice && (
              <span className="block text-xs text-gray-400 line-through">
                {formatPrice(primaryLink.originalPrice)}/mo
              </span>
            )}
            <div className="flex items-center gap-2">
              <span className="text-xl font-bold text-gray-900">
                {formatPrice(primaryLink?.currentPrice || product.basePrice)}
              </span>
              <span className="text-xs text-gray-500">/mo</span>
            </div>
            {discount && (
              <Badge className="bg-green-100 text-green-700 hover:bg-green-100 text-xs font-medium">
                {discount}% OFF
              </Badge>
            )}
          </div>
        </div>

        {/* Description */}
        <p className="text-sm text-gray-600 line-clamp-2 mb-3">
          {product.shortDescription || "A powerful tool to help you grow your business with advanced features and analytics."}
        </p>

        {/* Featured Tag */}
        {product.isFeatured && (
          <div className="flex items-center gap-1 text-amber-500 text-sm mb-3">
            <Star className="h-4 w-4 fill-current" />
            <span className="font-medium">Featured</span>
          </div>
        )}

        {/* Feature List - 2 Column Grid */}
        <div className="grid grid-cols-2 gap-x-4 gap-y-2 mb-4">
          {features.map((feature, index) => (
            <div key={index} className="flex items-center gap-2">
              <Check className="h-4 w-4 text-green-500 flex-shrink-0" />
              <span className="text-sm text-gray-700 truncate">{feature}</span>
            </div>
          ))}
        </div>

        {/* Reviews Section */}
        <div className="flex items-center gap-3 mb-4 pt-3 border-t border-gray-100">
          {/* Star Rating */}
          <div className="flex items-center gap-1">
            {[1, 2, 3, 4, 5].map((star) => (
              <Star
                key={star}
                className={cn(
                  "h-4 w-4",
                  star <= Math.floor(rating)
                    ? "text-amber-400 fill-amber-400"
                    : star - 0.5 <= rating
                    ? "text-amber-400 fill-amber-400/50"
                    : "text-gray-300"
                )}
              />
            ))}
          </div>
          
          {/* Rating Score */}
          <span className="text-sm font-bold text-gray-900">{rating.toFixed(1)}/5</span>
          
          {/* Review Count - Written out */}
          <span className="text-sm text-gray-500">
            {reviewCount.toLocaleString()} reviews
          </span>

          {/* Users Badge */}
          <div className="flex items-center gap-1 ml-auto text-red-500">
            <Users className="h-4 w-4" />
            <span className="text-xs font-medium">{userCount} users</span>
          </div>
        </div>

        {/* CTA Button */}
        {primaryLink && (
          <Button
            className="w-full bg-gray-900 hover:bg-gray-800 text-white"
            size="lg"
            asChild
            onClick={() => {
              if (typeof window !== "undefined") {
                window.dispatchEvent(
                  new CustomEvent("affiliate-click", {
                    detail: {
                      productId: product.id,
                      productName: product.name,
                      platform: primaryLink.platform,
                      price: primaryLink.currentPrice,
                    },
                  })
                )
              }
            }}
          >
            <a
              href={primaryLink.url}
              target="_blank"
              rel="noopener noreferrer sponsored"
              aria-label={`Buy ${product.name} from ${primaryLink.platform}`}
              className="flex items-center justify-center gap-2"
            >
              {discount ? "Claim Discount" : "Explore Now"}
              <ExternalLink className="h-4 w-4" />
            </a>
          </Button>
        )}

        {/* Free Trial Note */}
        {product.hasFreeTrial && (
          <p className="text-center text-xs text-gray-500 mt-2">
            14-day free trial available
          </p>
        )}
      </div>
    </Card>
  )
}
