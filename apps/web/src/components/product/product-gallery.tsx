"use client"

import { useState } from "react"
import Image from "next/image"
import { cn } from "@/lib/utils"
import type { ProductImage } from "@/types"

interface ProductGalleryProps {
  images: ProductImage[]
  productName: string
  className?: string
}

export function ProductGallery({
  images,
  productName,
  className,
}: ProductGalleryProps) {
  const [selectedIndex, setSelectedIndex] = useState(0)
  const sortedImages = [...images].sort((a, b) => (a.order || 0) - (b.order || 0))
  const selectedImage = sortedImages[selectedIndex] || sortedImages[0]

  if (sortedImages.length === 0) {
    return (
      <div
        className={cn(
          "flex aspect-square items-center justify-center rounded-lg bg-muted",
          className
        )}
      >
        <p className="text-muted-foreground">No images available</p>
      </div>
    )
  }

  return (
    <div className={cn("space-y-4", className)}>
      {/* Main Image */}
      <div className="relative aspect-square overflow-hidden rounded-lg bg-muted">
        {selectedImage ? (
          <Image
            src={selectedImage.url}
            alt={selectedImage.alt || `${productName} - Image ${selectedIndex + 1}`}
            fill
            className="object-cover"
            sizes="(max-width: 768px) 100vw, 50vw"
            priority
          />
        ) : (
          <div className="flex h-full items-center justify-center text-muted-foreground">
            No image
          </div>
        )}
      </div>

      {/* Thumbnail Grid */}
      {sortedImages.length > 1 && (
        <div className="grid grid-cols-4 gap-2">
          {sortedImages.map((image, index) => (
            <button
              key={image.id}
              onClick={() => setSelectedIndex(index)}
              className={cn(
                "relative aspect-square overflow-hidden rounded-md",
                selectedIndex === index
                  ? "ring-2 ring-primary ring-offset-2"
                  : "hover:opacity-75"
              )}
              aria-label={`View ${productName} image ${index + 1}`}
              aria-current={selectedIndex === index ? "true" : undefined}
            >
              <Image
                src={image.url}
                alt={image.alt || `${productName} thumbnail ${index + 1}`}
                fill
                className="object-cover"
                sizes="(max-width: 768px) 25vw, 10vw"
              />
            </button>
          ))}
        </div>
      )}
    </div>
  )
}
