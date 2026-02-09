"use client"

import Image from "next/image"
import Link from "next/link"
import { ArrowRight } from "lucide-react"
import { Button } from "@/components/ui/button"

export function HeroBanner() {
  return (
    <section className="relative">
      <div className="container mx-auto px-4 py-4">
        <div className="relative aspect-[21/9] md:aspect-[3/1] lg:aspect-[21/8] overflow-hidden rounded-xl">
          {/* Background Image */}
          <Image
            src="https://images.unsplash.com/photo-1497366216548-37526070297c?w=1200&h=600&fit=crop"
            alt="Affiliate Showcase"
            fill
            priority
            className="object-cover"
          />
          <div className="absolute inset-0 bg-gradient-to-r from-black/70 via-black/50 to-transparent" />

          {/* Content - Above Image */}
          <div className="absolute inset-0 flex flex-col justify-center p-6 md:p-8 lg:p-12">
            <div className="max-w-2xl">
              <span className="inline-block px-3 py-1 bg-red-600 text-white text-sm font-medium rounded mb-4">
                Welcome to Affiliate Showcase
              </span>
              <h1 className="text-2xl md:text-3xl lg:text-4xl xl:text-5xl font-bold text-white leading-tight mb-4">
                Discover the Best Products & Deals
              </h1>
              <p className="text-gray-200 text-base md:text-lg mb-6 hidden md:block">
                Curated affiliate products from trusted partners. Find quality recommendations you can count on.
              </p>
              <div className="flex flex-wrap gap-3">
                <Button asChild className="bg-red-600 hover:bg-red-700 text-white">
                  <Link href="/products">
                    Browse Products
                    <ArrowRight className="ml-2 h-4 w-4" />
                  </Link>
                </Button>
                <Button variant="outline" asChild className="border-white text-white hover:bg-white/10 bg-transparent">
                  <Link href="/blog">
                    Read Blog
                  </Link>
                </Button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  )
}
