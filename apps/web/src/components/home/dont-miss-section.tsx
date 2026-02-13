"use client"

import { useEffect, useState } from "react"
import Link from "next/link"
import { ArrowRight, Sparkles } from "lucide-react"
import { Button } from "@/components/ui/button"
import { BlogCard } from "@/components/blog/BlogCard"
import { ProductCard } from "@/components/product/product-card"
import { getLatestPosts } from "@/lib/api/blog"
import { getProducts } from "@/lib/api/products"

interface DontMissConfig {
  enabled: boolean
  title: string
  subtitle: string
  layout: "mixed" | "blogs_only" | "products_only"
  blogCount: number
  productCount: number
  showViewAll: boolean
  blogCategory?: string
  productCategory?: string
  backgroundColor?: string
  textColor?: string
}

const defaultConfig: DontMissConfig = {
  enabled: true,
  title: "Don't Miss",
  subtitle: "Latest updates and featured products you should check out",
  layout: "mixed",
  blogCount: 3,
  productCount: 2,
  showViewAll: true,
}

export function DontMissSection() {
  const [config, setConfig] = useState<DontMissConfig>(defaultConfig)
  const [blogs, setBlogs] = useState<any[]>([])
  const [products, setProducts] = useState<any[]>([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    // Fetch config from settings API
    const fetchConfig = async () => {
      try {
        const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/settings/dont-miss`)
        if (response.ok) {
          const data = await response.json()
          setConfig({ ...defaultConfig, ...data })
        }
      } catch (error) {
        console.error("Failed to fetch Don't Miss config:", error)
      }
    }

    fetchConfig()
  }, [])

  useEffect(() => {
    if (!config.enabled) return

    const fetchContent = async () => {
      setLoading(true)
      try {
        // Fetch blogs
        if (config.layout !== "products_only") {
          const blogResponse = await getLatestPosts(config.blogCount)
          setBlogs(blogResponse.data)
        }

        // Fetch products
        if (config.layout !== "blogs_only") {
          const productResponse = await getProducts({ 
            limit: config.productCount,
            featured: true 
          })
          setProducts(productResponse.items)
        }
      } catch (error) {
        console.error("Failed to fetch content:", error)
      } finally {
        setLoading(false)
      }
    }

    fetchContent()
  }, [config])

  if (!config.enabled) return null
  if (loading) return <DontMissSkeleton />

  const sectionStyle: React.CSSProperties = {}
  if (config.backgroundColor) {
    sectionStyle.backgroundColor = config.backgroundColor
  }

  const textStyle: React.CSSProperties = {}
  if (config.textColor) {
    textStyle.color = config.textColor
  }

  return (
    <section className="py-16" style={sectionStyle}>
      <div className="container mx-auto px-4">
        {/* Header */}
        <div className="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-10">
          <div>
            <div className="flex items-center gap-2 mb-2">
              <Sparkles className="h-5 w-5 text-primary" />
              <span className="text-sm font-medium text-primary uppercase tracking-wide">
                Featured
              </span>
            </div>
            <h2 className="text-3xl font-bold" style={textStyle}>
              {config.title}
            </h2>
            <p className="text-muted-foreground mt-1" style={config.textColor ? { color: config.textColor, opacity: 0.8 } : {}}>
              {config.subtitle}
            </p>
          </div>
          {config.showViewAll && (
            <div className="flex gap-3">
              {config.layout !== "products_only" && (
                <Button variant="outline" asChild>
                  <Link href="/blog">
                    View All Posts
                    <ArrowRight className="ml-2 h-4 w-4" />
                  </Link>
                </Button>
              )}
              {config.layout !== "blogs_only" && (
                <Button asChild>
                  <Link href="/products">
                    View All Products
                    <ArrowRight className="ml-2 h-4 w-4" />
                  </Link>
                </Button>
              )}
            </div>
          )}
        </div>

        {/* Content Grid */}
        {config.layout === "mixed" && (
          <div className="grid lg:grid-cols-5 gap-8">
            {/* Blog Posts - Takes 3 columns */}
            <div className="lg:col-span-3 space-y-6">
              <h3 className="text-lg font-semibold flex items-center gap-2" style={textStyle}>
                <span className="w-1 h-5 bg-primary rounded-full" />
                Latest Articles
              </h3>
              <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                {blogs.map((blog) => (
                  <BlogCard key={blog.id} post={blog} />
                ))}
              </div>
            </div>

            {/* Products - Takes 2 columns */}
            <div className="lg:col-span-2 space-y-6">
              <h3 className="text-lg font-semibold flex items-center gap-2" style={textStyle}>
                <span className="w-1 h-5 bg-primary rounded-full" />
                Featured Products
              </h3>
              <div className="space-y-4">
                {products.map((product) => (
                  <ProductCard key={product.id} product={product} variant="compact" />
                ))}
              </div>
            </div>
          </div>
        )}

        {config.layout === "blogs_only" && (
          <div className="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            {blogs.map((blog) => (
              <BlogCard key={blog.id} post={blog} />
            ))}
          </div>
        )}

        {config.layout === "products_only" && (
          <div className="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            {products.map((product) => (
              <ProductCard key={product.id} product={product} />
            ))}
          </div>
        )}
      </div>
    </section>
  )
}

function DontMissSkeleton() {
  return (
    <section className="py-16 bg-muted/30">
      <div className="container mx-auto px-4">
        <div className="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-10">
          <div>
            <div className="h-4 w-20 bg-muted rounded animate-pulse mb-2" />
            <div className="h-8 w-48 bg-muted rounded animate-pulse mb-1" />
            <div className="h-4 w-64 bg-muted rounded animate-pulse" />
          </div>
        </div>
        <div className="grid lg:grid-cols-5 gap-8">
          <div className="lg:col-span-3 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {[1, 2, 3].map((i) => (
              <div key={i} className="h-64 bg-muted rounded-lg animate-pulse" />
            ))}
          </div>
          <div className="lg:col-span-2 space-y-4">
            {[1, 2].map((i) => (
              <div key={i} className="h-32 bg-muted rounded-lg animate-pulse" />
            ))}
          </div>
        </div>
      </div>
    </section>
  )
}
