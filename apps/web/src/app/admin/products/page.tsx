"use client"

import { useEffect, useState } from "react"
import Link from "next/link"
import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Badge } from "@/components/ui/badge"
import { Checkbox } from "@/components/ui/checkbox"
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select"
import {
  Plus,
  Trash2,
  Upload,
  Link2,
  Search,
  Star,
  Edit2,
  ChevronLeft,
  ChevronRight,
} from "lucide-react"

interface Product {
  id: string
  name: string
  slug: string
  description: string
  shortDescription: string
  status: "PUBLISHED" | "DRAFT" | "ARCHIVED"
  price: number
  comparePrice: number | null
  image: string | null
  category: { id: string; name: string; slug: string } | null
  tags: { id: string; name: string }[]
  ribbon: { id: string; name: string; label: string; bgColor: string; color: string } | null
  isFeatured: boolean
  createdAt: string
  updatedAt: string
}

interface ProductStats {
  all: number
  published: number
  draft: number
  trash: number
}

export default function ProductsPage() {
  const [products, setProducts] = useState<Product[]>([])
  const [stats, setStats] = useState<ProductStats>({ all: 0, published: 0, draft: 0, trash: 0 })
  const [loading, setLoading] = useState(true)
  const [activeTab, setActiveTab] = useState<"ALL" | "PUBLISHED" | "DRAFT" | "TRASH">("ALL")
  const [searchQuery, setSearchQuery] = useState("")
  const [selectedCategory, setSelectedCategory] = useState("all")
  const [sortOrder, setSortOrder] = useState("latest")
  const [featuredFilter, setFeaturedFilter] = useState("all")
  const [selectedProducts, setSelectedProducts] = useState<string[]>([])
  const [currentPage, setCurrentPage] = useState(1)

  useEffect(() => {
    fetchProducts()
    fetchStats()
  }, [activeTab, currentPage])

  const fetchProducts = async () => {
    setLoading(true)
    try {
      const params = new URLSearchParams()
      if (activeTab !== "ALL" && activeTab !== "TRASH") params.append("status", activeTab)
      if (activeTab === "TRASH") params.append("status", "ARCHIVED")
      if (searchQuery) params.append("search", searchQuery)
      if (selectedCategory !== "all") params.append("category", selectedCategory)
      if (featuredFilter === "featured") params.append("featured", "true")
      params.append("page", currentPage.toString())

      const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/products?${params}`)
      if (response.ok) {
        const data = await response.json()
        setProducts(data.items || [])
      }
    } catch (error) {
      console.error("Failed to fetch products:", error)
    } finally {
      setLoading(false)
    }
  }

  const fetchStats = async () => {
    try {
      const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/products/stats`)
      if (response.ok) {
        const data = await response.json()
        setStats(data)
      }
    } catch (error) {
      console.error("Failed to fetch stats:", error)
    }
  }

  const formatPrice = (cents: number) => {
    return `$${(cents / 100).toFixed(0)}`
  }

  const calculateDiscount = (price: number, comparePrice: number | null) => {
    if (!comparePrice || comparePrice <= price) return null
    const discount = Math.round(((comparePrice - price) / comparePrice) * 100)
    return discount
  }

  const handleSelectAll = (checked: boolean) => {
    if (checked) {
      setSelectedProducts(products.map((p) => p.id))
    } else {
      setSelectedProducts([])
    }
  }

  const handleSelectProduct = (productId: string, checked: boolean) => {
    if (checked) {
      setSelectedProducts([...selectedProducts, productId])
    } else {
      setSelectedProducts(selectedProducts.filter((id) => id !== productId))
    }
  }

  const clearFilters = () => {
    setSearchQuery("")
    setSelectedCategory("all")
    setSortOrder("latest")
    setFeaturedFilter("all")
    setActiveTab("ALL")
    setCurrentPage(1)
  }

  const tabs = [
    { key: "ALL", label: "ALL", count: stats.all },
    { key: "PUBLISHED", label: "PUBLISHED", count: stats.published },
    { key: "DRAFT", label: "DRAFT", count: stats.draft },
    { key: "TRASH", label: "TRASH", count: stats.trash },
  ] as const

  return (
    <div className="space-y-6">
      {/* Gradient Header */}
      <div className="relative overflow-hidden rounded-xl bg-gradient-to-r from-violet-600 via-blue-600 to-blue-500 px-8 py-8 text-white">
        <div className="relative z-10">
          <h1 className="text-3xl font-bold">Manage Products</h1>
          <p className="mt-2 text-blue-100">
            Quick overview of your catalog with actions, filters, and bulk selection.
          </p>
        </div>
        {/* Decorative circles */}
        <div className="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10" />
        <div className="absolute -bottom-10 -right-10 h-32 w-32 rounded-full bg-white/5" />
      </div>

      {/* Action Buttons */}
      <div className="flex flex-wrap gap-3">
        <Button className="bg-blue-600 hover:bg-blue-700" asChild>
          <Link href="/admin/products/new">
            <Plus className="mr-2 h-4 w-4" />
            Add New Product
          </Link>
        </Button>
        <Button variant="destructive">
          <Trash2 className="mr-2 h-4 w-4" />
          Trash
        </Button>
        <Button variant="outline">
          <Upload className="mr-2 h-4 w-4" />
          Bulk Upload
        </Button>
        <Button variant="outline">
          <Link2 className="mr-2 h-4 w-4" />
          Check Links
        </Button>
      </div>

      {/* Status Tabs - Full Width Beautiful Design */}
      <div className="grid grid-cols-4 gap-4">
        {tabs.map((tab) => {
          const isActive = activeTab === tab.key
          const getColors = () => {
            switch (tab.key) {
              case "ALL":
                return isActive 
                  ? "bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-lg shadow-blue-200" 
                  : "bg-white hover:bg-blue-50 text-gray-600 border border-gray-200"
              case "PUBLISHED":
                return isActive 
                  ? "bg-gradient-to-br from-green-500 to-emerald-600 text-white shadow-lg shadow-green-200" 
                  : "bg-white hover:bg-green-50 text-gray-600 border border-gray-200"
              case "DRAFT":
                return isActive 
                  ? "bg-gradient-to-br from-amber-500 to-orange-500 text-white shadow-lg shadow-amber-200" 
                  : "bg-white hover:bg-amber-50 text-gray-600 border border-gray-200"
              case "TRASH":
                return isActive 
                  ? "bg-gradient-to-br from-red-500 to-rose-600 text-white shadow-lg shadow-red-200" 
                  : "bg-white hover:bg-red-50 text-gray-600 border border-gray-200"
              default:
                return "bg-white text-gray-600 border border-gray-200"
            }
          }

          return (
            <button
              key={tab.key}
              onClick={() => setActiveTab(tab.key)}
              className={`relative overflow-hidden rounded-xl p-6 transition-all duration-300 ${getColors()} ${
                isActive ? "scale-[1.02]" : "hover:scale-[1.01]"
              }`}
            >
              {/* Background Pattern */}
              <div className="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-white/10" />
              <div className="absolute -bottom-4 -left-4 h-16 w-16 rounded-full bg-white/5" />
              
              {/* Content */}
              <div className="relative z-10 flex flex-col items-center justify-center text-center">
                <span className={`text-5xl font-bold tracking-tight ${
                  isActive ? "text-white" : "text-gray-800"
                }`}>
                  {tab.count}
                </span>
                <span className={`mt-2 text-sm font-semibold uppercase tracking-wider ${
                  isActive ? "text-white/90" : "text-gray-500"
                }`}>
                  {tab.label}
                </span>
              </div>

              {/* Active Indicator */}
              {isActive && (
                <div className="absolute bottom-0 left-0 right-0 h-1 bg-white/30" />
              )}
            </button>
          )
        })}
      </div>

      {/* Filters */}
      <Card>
        <CardContent className="p-4">
          <div className="flex flex-wrap items-end gap-3">
            <div className="w-40">
              <Select defaultValue="action">
                <SelectTrigger>
                  <SelectValue placeholder="Select action" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="action">Select action</SelectItem>
                  <SelectItem value="delete">Delete</SelectItem>
                  <SelectItem value="publish">Publish</SelectItem>
                  <SelectItem value="unpublish">Unpublish</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div className="relative flex-1 min-w-[200px]">
              <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
              <Input
                placeholder="Search products..."
                className="pl-10"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                onKeyDown={(e) => e.key === "Enter" && fetchProducts()}
              />
            </div>

            <div className="w-44">
              <Select value={selectedCategory} onValueChange={setSelectedCategory}>
                <SelectTrigger>
                  <SelectValue placeholder="All Categories" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Categories</SelectItem>
                  <SelectItem value="1">Electronics</SelectItem>
                  <SelectItem value="2">Computers</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div className="w-44">
              <Select value={sortOrder} onValueChange={setSortOrder}>
                <SelectTrigger>
                  <SelectValue placeholder="Sort by" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="latest">Latest &lt;- Oldest</SelectItem>
                  <SelectItem value="oldest">Oldest -&gt; Latest</SelectItem>
                  <SelectItem value="price-high">Price: High -&gt; Low</SelectItem>
                  <SelectItem value="price-low">Price: Low -&gt; High</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div className="w-44">
              <Select value={featuredFilter} onValueChange={setFeaturedFilter}>
                <SelectTrigger>
                  <SelectValue placeholder="Show Featured" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">Show Featured</SelectItem>
                  <SelectItem value="featured">Featured Only</SelectItem>
                  <SelectItem value="not-featured">Not Featured</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          <Button
            variant="secondary"
            className="mt-3 bg-gray-600 text-white hover:bg-gray-700"
            onClick={clearFilters}
          >
            Clear filters
          </Button>
        </CardContent>
      </Card>

      {/* Products Table */}
      <Card>
        <div className="overflow-x-auto">
          <table className="w-full">
            <thead className="bg-gray-50 border-b">
              <tr>
                <th className="w-12 px-4 py-3 text-left">
                  <Checkbox
                    checked={
                      products.length > 0 && selectedProducts.length === products.length
                    }
                    onCheckedChange={handleSelectAll}
                  />
                </th>
                <th className="w-12 px-4 py-3 text-left text-sm font-semibold text-gray-600">#</th>
                <th className="w-16 px-4 py-3 text-left text-sm font-semibold text-gray-600">Logo</th>
                <th className="px-4 py-3 text-left text-sm font-semibold text-gray-600">Product</th>
                <th className="px-4 py-3 text-left text-sm font-semibold text-gray-600">Category</th>
                <th className="px-4 py-3 text-left text-sm font-semibold text-gray-600">Tags</th>
                <th className="px-4 py-3 text-left text-sm font-semibold text-gray-600">Ribbon</th>
                <th className="px-4 py-3 text-center text-sm font-semibold text-gray-600">Featured</th>
                <th className="px-4 py-3 text-left text-sm font-semibold text-gray-600">Price</th>
                <th className="px-4 py-3 text-left text-sm font-semibold text-gray-600">Status</th>
              </tr>
            </thead>
            <tbody className="divide-y">
              {loading ? (
                <tr>
                  <td colSpan={10} className="px-4 py-8 text-center">
                    <div className="flex justify-center">
                      <div className="h-8 w-8 animate-spin rounded-full border-4 border-blue-600 border-t-transparent" />
                    </div>
                  </td>
                </tr>
              ) : products.length === 0 ? (
                <tr>
                  <td colSpan={10} className="px-4 py-8 text-center text-gray-500">
                    No products found
                  </td>
                </tr>
              ) : (
                products.map((product, index) => (
                  <tr key={product.id} className="hover:bg-gray-50">
                    <td className="px-4 py-3">
                      <Checkbox
                        checked={selectedProducts.includes(product.id)}
                        onCheckedChange={(checked: boolean) =>
                          handleSelectProduct(product.id, checked)
                        }
                      />
                    </td>
                    <td className="px-4 py-3 text-sm text-gray-600">{index + 1}</td>
                    <td className="px-4 py-3">
                      {product.image ? (
                        <img
                          src={product.image}
                          alt={product.name}
                          className="h-10 w-10 rounded-lg object-cover"
                        />
                      ) : (
                        <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-200 text-xs text-gray-500">
                          IMG
                        </div>
                      )}
                    </td>
                    <td className="px-4 py-3">
                      <div>
                        <p className="font-medium text-gray-900">{product.name}</p>
                        <div className="flex items-center gap-2 text-sm text-gray-500">
                          <span>ID #{product.id}</span>
                          <span>•</span>
                          <Link
                            href={`/admin/products/${product.id}/edit`}
                            className="text-blue-600 hover:underline"
                          >
                            Edit
                          </Link>
                          <span>•</span>
                          <button className="text-red-600 hover:underline">Delete</button>
                        </div>
                      </div>
                    </td>
                    <td className="px-4 py-3">
                      {product.category && (
                        <Badge variant="secondary" className="font-normal">
                          {product.category.name}
                        </Badge>
                      )}
                    </td>
                    <td className="px-4 py-3">
                      <div className="flex flex-wrap gap-1">
                        {product.tags.map((tag) => (
                          <Badge
                            key={tag.id}
                            variant="outline"
                            className="text-xs font-normal"
                          >
                            {tag.name}
                          </Badge>
                        ))}
                      </div>
                    </td>
                    <td className="px-4 py-3">
                      {product.ribbon && (
                        <span
                          className="inline-flex items-center rounded px-2 py-1 text-xs font-medium"
                          style={{
                            backgroundColor: product.ribbon.bgColor,
                            color: product.ribbon.color,
                          }}
                        >
                          {product.ribbon.label}
                        </span>
                      )}
                    </td>
                    <td className="px-4 py-3 text-center">
                      {product.isFeatured && (
                        <Star className="mx-auto h-5 w-5 fill-yellow-400 text-yellow-400" />
                      )}
                    </td>
                    <td className="px-4 py-3">
                      <div className="flex flex-col gap-1">
                        {/* Original Price (strikethrough) */}
                        {product.comparePrice && (
                          <span className="text-xs text-gray-400 line-through">
                            {formatPrice(product.comparePrice)}
                          </span>
                        )}
                        {/* Current Price */}
                        <span className="font-semibold text-gray-900">
                          {formatPrice(product.price)}
                        </span>
                        {/* Discount Badge */}
                        {product.comparePrice && calculateDiscount(product.price, product.comparePrice) && (
                          <Badge className="w-fit bg-red-500 text-white text-xs">
                            {calculateDiscount(product.price, product.comparePrice)}% OFF
                          </Badge>
                        )}
                      </div>
                    </td>
                    <td className="px-4 py-3">
                      <Badge
                        className={`${
                          product.status === "PUBLISHED"
                            ? "bg-green-100 text-green-700 hover:bg-green-100"
                            : product.status === "DRAFT"
                            ? "bg-yellow-100 text-yellow-700 hover:bg-yellow-100"
                            : "bg-gray-100 text-gray-700 hover:bg-gray-100"
                        }`}
                      >
                        {product.status}
                      </Badge>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>

        {/* Pagination */}
        {products.length > 0 && (
          <div className="flex items-center justify-between border-t px-4 py-3">
            <div className="text-sm text-gray-500">
              Showing {products.length} products
            </div>
            <div className="flex items-center gap-2">
              <Button
                variant="outline"
                size="sm"
                onClick={() => setCurrentPage((p) => Math.max(1, p - 1))}
                disabled={currentPage === 1}
              >
                <ChevronLeft className="h-4 w-4" />
              </Button>
              <span className="text-sm text-gray-600">Page {currentPage}</span>
              <Button
                variant="outline"
                size="sm"
                onClick={() => setCurrentPage((p) => p + 1)}
              >
                <ChevronRight className="h-4 w-4" />
              </Button>
            </div>
          </div>
        )}
      </Card>
    </div>
  )
}
