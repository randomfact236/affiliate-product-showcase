"use client"

import React, { useEffect, useState, useCallback } from "react"
import Link from "next/link"
import { useRouter } from "next/navigation"
import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
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
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import {
  Plus,
  Trash2,
  Upload,
  Link2,
  Search,
  Star,
  Edit,
  Edit2,
  ChevronLeft,
  ChevronRight,
  Check,
  X,
  Save,
  MoreHorizontal,
  Filter,
  Grid3X3,
  List,
  CheckSquare,
  FileSpreadsheet,
  Eye,
  Package,
  Loader2,
  MoreVertical,
} from "lucide-react"
import { toast } from "sonner"
import {
  getProducts,
  updateProduct,
  deleteProduct,
  bulkDeleteProducts,
  bulkUpdateProducts,
  type Product,
} from "@/lib/api/products"
import { SkeletonTableRows } from "@/components/ui/skeleton-table"
import { DataTablePagination } from "@/components/ui/data-table-pagination"
import { ConfirmDialog } from "@/components/ui/confirm-dialog"

interface ProductStats {
  all: number
  published: number
  draft: number
  trash: number
}

interface FilterState {
  status: "ALL" | "PUBLISHED" | "DRAFT" | "ARCHIVED"
  category: string
  featured: "all" | "featured" | "not-featured"
}

interface SortState {
  column: "name" | "price" | "createdAt" | "updatedAt"
  direction: "asc" | "desc"
}

export default function ProductsPage() {
  const router = useRouter()
  const [products, setProducts] = useState<Product[]>([])
  const [stats, setStats] = useState<ProductStats>({ all: 0, published: 0, draft: 0, trash: 0 })
  const [loading, setLoading] = useState(true)
  
  // Filters
  const [searchQuery, setSearchQuery] = useState("")
  const [filters, setFilters] = useState<FilterState>({
    status: "ALL",
    category: "all",
    featured: "all",
  })
  const [sort, setSort] = useState<SortState>({
    column: "createdAt",
    direction: "desc",
  })
  const [showFilters, setShowFilters] = useState(false)
  const [viewMode, setViewMode] = useState<"table" | "grid">("table")

  // Selection
  const [selectedIds, setSelectedIds] = useState<Set<string>>(new Set())
  const [selectAll, setSelectAll] = useState(false)

  // Editing
  const [quickEditingId, setQuickEditingId] = useState<string | null>(null)
  const [fullEditingId, setFullEditingId] = useState<string | null>(null)
  const [editFormData, setEditFormData] = useState<Partial<Product>>({})
  const [isSaving, setIsSaving] = useState(false)

  // Pagination
  const [currentPage, setCurrentPage] = useState(1)
  const [itemsPerPage, setItemsPerPage] = useState(10)
  const [totalItems, setTotalItems] = useState(0)
  const [totalPages, setTotalPages] = useState(1)

  // Confirm Dialog
  const [confirmDialog, setConfirmDialog] = useState<{
    isOpen: boolean
    title: string
    description: string
    onConfirm: () => void
  }>({
    isOpen: false,
    title: "",
    description: "",
    onConfirm: () => {},
  })

  const fetchProducts = useCallback(async () => {
    setLoading(true)
    try {
      const params: any = {
        page: currentPage,
        limit: itemsPerPage,
        search: searchQuery || undefined,
        sortBy: sort.column,
        sortOrder: sort.direction,
      }
      
      if (filters.status !== "ALL") params.status = filters.status
      if (filters.category !== "all") params.category = filters.category
      if (filters.featured === "featured") params.featured = true
      if (filters.featured === "not-featured") params.featured = false

      const response = await getProducts(params)
      setProducts(response.items)
      setTotalItems(response.total)
      setTotalPages(response.totalPages)
    } catch (error) {
      console.error("Failed to fetch products:", error)
      toast.error("Failed to load products")
    } finally {
      setLoading(false)
    }
  }, [currentPage, itemsPerPage, searchQuery, filters, sort])

  const fetchStats = useCallback(async () => {
    try {
      const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/products/stats`)
      if (response.ok) {
        const data = await response.json()
        setStats(data)
      }
    } catch (error) {
      console.error("Failed to fetch stats:", error)
    }
  }, [])

  useEffect(() => {
    fetchProducts()
    fetchStats()
  }, [fetchProducts])

  useEffect(() => {
    setCurrentPage(1)
    setSelectedIds(new Set())
    setSelectAll(false)
  }, [searchQuery, filters, sort])

  const formatPrice = (cents: number) => {
    return `$${(cents / 100).toFixed(0)}`
  }

  const calculateDiscount = (price: number, comparePrice: number | null) => {
    if (!comparePrice || comparePrice <= price) return null
    const discount = Math.round(((comparePrice - price) / comparePrice) * 100)
    return discount
  }

  // Selection
  const toggleSelectAll = () => {
    if (selectAll) {
      setSelectedIds(new Set())
    } else {
      setSelectedIds(new Set(products.map((p) => p.id)))
    }
    setSelectAll(!selectAll)
  }

  const toggleSelect = (id: string) => {
    const newSelected = new Set(selectedIds)
    if (newSelected.has(id)) {
      newSelected.delete(id)
    } else {
      newSelected.add(id)
    }
    setSelectedIds(newSelected)
  }

  // Quick Edit
  const startQuickEdit = (product: Product) => {
    setQuickEditingId(product.id)
    setFullEditingId(null)
    setEditFormData({ name: product.name, price: product.price })
  }

  const saveQuickEdit = async () => {
    if (!quickEditingId || !editFormData.name?.trim()) return

    try {
      setIsSaving(true)
      await updateProduct(quickEditingId, { 
        name: editFormData.name.trim(),
        price: editFormData.price 
      })
      toast.success("Product updated successfully")
      await fetchProducts()
      setQuickEditingId(null)
    } catch (err) {
      toast.error(err instanceof Error ? err.message : "Failed to update product")
    } finally {
      setIsSaving(false)
    }
  }

  // Full Edit
  const startFullEdit = (product: Product) => {
    setFullEditingId(product.id)
    setQuickEditingId(null)
    setEditFormData({
      name: product.name,
      slug: product.slug,
      shortDescription: product.shortDescription,
      price: product.price,
      comparePrice: product.comparePrice,
      status: product.status,
      isFeatured: product.isFeatured,
    })
  }

  const saveFullEdit = async () => {
    if (!fullEditingId || !editFormData.name?.trim()) return

    try {
      setIsSaving(true)
      await updateProduct(fullEditingId, {
        name: editFormData.name.trim(),
        slug: editFormData.slug,
        shortDescription: editFormData.shortDescription,
        price: editFormData.price,
        comparePrice: editFormData.comparePrice,
        status: editFormData.status,
        isFeatured: editFormData.isFeatured,
      })
      toast.success("Product updated successfully")
      await fetchProducts()
      setFullEditingId(null)
    } catch (err) {
      toast.error(err instanceof Error ? err.message : "Failed to update product")
    } finally {
      setIsSaving(false)
    }
  }

  // Delete
  const handleDelete = (product: Product) => {
    setConfirmDialog({
      isOpen: true,
      title: "Delete Product",
      description: `Are you sure you want to delete "${product.name}"? This action cannot be undone.`,
      onConfirm: async () => {
        try {
          await deleteProduct(product.id)
          toast.success("Product deleted successfully")
          await fetchProducts()
          await fetchStats()
        } catch (err) {
          toast.error(err instanceof Error ? err.message : "Failed to delete product")
        }
      },
    })
  }

  // Bulk Delete
  const handleBulkDelete = () => {
    if (selectedIds.size === 0) return

    setConfirmDialog({
      isOpen: true,
      title: "Delete Products",
      description: `Are you sure you want to delete ${selectedIds.size} product${selectedIds.size === 1 ? "" : "s"}? This action cannot be undone.`,
      onConfirm: async () => {
        try {
          await bulkDeleteProducts(Array.from(selectedIds))
          toast.success(`${selectedIds.size} products deleted`)
          setSelectedIds(new Set())
          setSelectAll(false)
          await fetchProducts()
          await fetchStats()
        } catch (err) {
          toast.error(err instanceof Error ? err.message : "Failed to delete products")
        }
      },
    })
  }

  // Bulk Update Status
  const handleBulkUpdateStatus = (status: string) => {
    if (selectedIds.size === 0) return

    setConfirmDialog({
      isOpen: true,
      title: status === "PUBLISHED" ? "Publish Products" : "Update Status",
      description: `Are you sure you want to ${status === "PUBLISHED" ? "publish" : "update status for"} ${selectedIds.size} product${selectedIds.size === 1 ? "" : "s"}?`,
      onConfirm: async () => {
        try {
          await bulkUpdateProducts(Array.from(selectedIds), { status: status as any })
          toast.success(`${selectedIds.size} products updated`)
          setSelectedIds(new Set())
          setSelectAll(false)
          await fetchProducts()
          await fetchStats()
        } catch (err) {
          toast.error(err instanceof Error ? err.message : "Failed to update products")
        }
      },
    })
  }

  // Bulk Feature
  const handleBulkFeature = (isFeatured: boolean) => {
    if (selectedIds.size === 0) return

    setConfirmDialog({
      isOpen: true,
      title: isFeatured ? "Feature Products" : "Unfeature Products",
      description: `Are you sure you want to ${isFeatured ? "feature" : "unfeature"} ${selectedIds.size} product${selectedIds.size === 1 ? "" : "s"}?`,
      onConfirm: async () => {
        try {
          await bulkUpdateProducts(Array.from(selectedIds), { isFeatured })
          toast.success(`${selectedIds.size} products updated`)
          setSelectedIds(new Set())
          setSelectAll(false)
          await fetchProducts()
          await fetchStats()
        } catch (err) {
          toast.error(err instanceof Error ? err.message : "Failed to update products")
        }
      },
    })
  }

  // Export
  const handleExport = () => {
    const csv = [
      ["ID", "Name", "Slug", "Status", "Price", "Compare Price", "Category", "Featured", "Created At"].join(","),
      ...products.map((p) =>
        [
          p.id,
          `"${p.name}"`,
          p.slug,
          p.status,
          p.price,
          p.comparePrice || "",
          p.category?.name || "",
          p.isFeatured ? "Yes" : "No",
          new Date(p.createdAt).toISOString(),
        ].join(",")
      ),
    ].join("\n")

    const blob = new Blob([csv], { type: "text/csv" })
    const url = URL.createObjectURL(blob)
    const a = document.createElement("a")
    a.href = url
    a.download = `products-${new Date().toISOString().split("T")[0]}.csv`
    a.click()
    URL.revokeObjectURL(url)

    toast.success("Products exported successfully")
  }

  const clearFilters = () => {
    setSearchQuery("")
    setFilters({
      status: "ALL",
      category: "all",
      featured: "all",
    })
    setSort({
      column: "createdAt",
      direction: "desc",
    })
    setCurrentPage(1)
  }

  const tabs = [
    { key: "ALL", label: "ALL", count: stats.all },
    { key: "PUBLISHED", label: "PUBLISHED", count: stats.published },
    { key: "DRAFT", label: "DRAFT", count: stats.draft },
    { key: "ARCHIVED", label: "ARCHIVED", count: stats.trash },
  ] as const

  return (
    <div className="space-y-6">
      {/* Gradient Header */}
      <div className="relative overflow-hidden rounded-xl bg-gradient-to-r from-violet-600 via-blue-600 to-blue-500 px-8 py-8 text-white">
        <div className="relative z-10">
          <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
              <h1 className="text-3xl font-bold flex items-center gap-3">
                <Package className="h-8 w-8" />
                Manage Products
              </h1>
              <p className="mt-2 text-blue-100">
                Quick overview of your catalog with actions, filters, and bulk selection.
              </p>
            </div>
            <div className="flex gap-2">
              <Button
                variant="outline"
                className="bg-white/10 text-white border-white/20 hover:bg-white/20"
                onClick={handleExport}
              >
                <FileSpreadsheet className="mr-2 h-4 w-4" />
                Export
              </Button>
              <Button className="bg-white text-blue-600 hover:bg-blue-50" asChild>
                <Link href="/admin/products/new">
                  <Plus className="mr-2 h-4 w-4" />
                  Add Product
                </Link>
              </Button>
            </div>
          </div>
        </div>
        <div className="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10" />
        <div className="absolute -bottom-10 -right-10 h-32 w-32 rounded-full bg-white/5" />
      </div>

      {/* Status Tabs */}
      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {tabs.map((tab) => {
          const isActive = filters.status === tab.key
          const getColors = () => {
            switch (tab.key) {
              case "ALL":
                return isActive 
                  ? "bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-lg" 
                  : "bg-white hover:bg-blue-50 text-gray-600 border border-gray-200"
              case "PUBLISHED":
                return isActive 
                  ? "bg-gradient-to-br from-green-500 to-emerald-600 text-white shadow-lg" 
                  : "bg-white hover:bg-green-50 text-gray-600 border border-gray-200"
              case "DRAFT":
                return isActive 
                  ? "bg-gradient-to-br from-amber-500 to-orange-500 text-white shadow-lg" 
                  : "bg-white hover:bg-amber-50 text-gray-600 border border-gray-200"
              case "ARCHIVED":
                return isActive 
                  ? "bg-gradient-to-br from-gray-500 to-gray-600 text-white shadow-lg" 
                  : "bg-white hover:bg-gray-50 text-gray-600 border border-gray-200"
              default:
                return "bg-white text-gray-600 border border-gray-200"
            }
          }

          return (
            <button
              key={tab.key}
              onClick={() => setFilters({ ...filters, status: tab.key })}
              className={`relative overflow-hidden rounded-xl p-6 transition-all duration-300 ${getColors()} ${
                isActive ? "scale-[1.02]" : "hover:scale-[1.01]"
              }`}
            >
              <div className="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-white/10" />
              <div className="absolute -bottom-4 -left-4 h-16 w-16 rounded-full bg-white/5" />
              
              <div className="relative z-10 flex flex-col items-center justify-center text-center">
                <span className={`text-4xl font-bold tracking-tight ${
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
            </button>
          )
        })}
      </div>

      {/* Filters */}
      <Card>
        <CardContent className="p-4">
          <div className="flex flex-col sm:flex-row gap-4">
            <div className="relative flex-1">
              <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
              <Input
                placeholder="Search products..."
                className="pl-10"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
              />
            </div>
            <div className="flex gap-2">
              <Button
                variant="outline"
                size="icon"
                onClick={() => setShowFilters(!showFilters)}
                className={showFilters ? "bg-blue-50 text-blue-600" : ""}
              >
                <Filter className="h-4 w-4" />
              </Button>
              <Button
                variant="outline"
                size="icon"
                onClick={() => setViewMode(viewMode === "table" ? "grid" : "table")}
              >
                {viewMode === "table" ? (
                  <Grid3X3 className="h-4 w-4" />
                ) : (
                  <List className="h-4 w-4" />
                )}
              </Button>
            </div>
          </div>

          {showFilters && (
            <div className="mt-4 pt-4 border-t grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div>
                <Label className="text-sm font-medium mb-2 block">Category</Label>
                <Select value={filters.category} onValueChange={(v: string) => setFilters({ ...filters, category: v })}>
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

              <div>
                <Label className="text-sm font-medium mb-2 block">Featured</Label>
                <Select 
                  value={filters.featured} 
                  onValueChange={(v: any) => setFilters({ ...filters, featured: v })}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="All Products" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">All Products</SelectItem>
                    <SelectItem value="featured">Featured Only</SelectItem>
                    <SelectItem value="not-featured">Not Featured</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div>
                <Label className="text-sm font-medium mb-2 block">Sort By</Label>
                <div className="flex gap-2">
                  <Select 
                    value={sort.column} 
                    onValueChange={(v: any) => setSort({ ...sort, column: v })}
                  >
                    <SelectTrigger className="flex-1">
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="createdAt">Date Created</SelectItem>
                      <SelectItem value="name">Name</SelectItem>
                      <SelectItem value="price">Price</SelectItem>
                    </SelectContent>
                  </Select>
                  <Button
                    variant="outline"
                    size="icon"
                    onClick={() => setSort({ ...sort, direction: sort.direction === "asc" ? "desc" : "asc" })}
                  >
                    <Filter className="h-4 w-4" />
                  </Button>
                </div>
              </div>
            </div>
          )}

          <Button
            variant="secondary"
            className="mt-3 bg-gray-600 text-white hover:bg-gray-700"
            onClick={clearFilters}
          >
            Clear filters
          </Button>
        </CardContent>
      </Card>

      {/* Bulk Actions */}
      {selectedIds.size > 0 && (
        <div className="bg-blue-50 border border-blue-200 rounded-lg p-4 flex items-center justify-between">
          <div className="flex items-center gap-2">
            <CheckSquare className="h-5 w-5 text-blue-600" />
            <span className="font-medium text-blue-900">
              {selectedIds.size} selected
            </span>
          </div>
          <div className="flex gap-2 flex-wrap">
            <Button
              variant="outline"
              size="sm"
              onClick={() => handleBulkUpdateStatus("PUBLISHED")}
              className="border-green-200 text-green-600 hover:bg-green-50"
            >
              <Check className="mr-1 h-4 w-4" />
              Publish
            </Button>
            <Button
              variant="outline"
              size="sm"
              onClick={() => handleBulkUpdateStatus("DRAFT")}
              className="border-gray-200 hover:bg-gray-50"
            >
              Draft
            </Button>
            <Button
              variant="outline"
              size="sm"
              onClick={() => handleBulkFeature(true)}
              className="border-yellow-200 text-yellow-600 hover:bg-yellow-50"
            >
              <Star className="mr-1 h-4 w-4" />
              Feature
            </Button>
            <Button
              variant="outline"
              size="sm"
              onClick={handleBulkDelete}
              className="border-red-200 text-red-600 hover:bg-red-50"
            >
              <Trash2 className="mr-1 h-4 w-4" />
              Delete
            </Button>
          </div>
        </div>
      )}

      {/* Products Display */}
      <Card>
        {viewMode === "table" ? (
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead className="bg-gray-50 border-b">
                <tr>
                  <th className="w-12 px-4 py-3 text-left">
                    <Checkbox checked={selectAll} onCheckedChange={toggleSelectAll} />
                  </th>
                  <th className="w-12 px-4 py-3 text-left text-sm font-semibold text-gray-600">#</th>
                  <th className="w-16 px-4 py-3 text-left text-sm font-semibold text-gray-600">Image</th>
                  <th className="px-4 py-3 text-left text-sm font-semibold text-gray-600">Product</th>
                  <th className="px-4 py-3 text-left text-sm font-semibold text-gray-600">Category</th>
                  <th className="px-4 py-3 text-center text-sm font-semibold text-gray-600">Featured</th>
                  <th className="px-4 py-3 text-left text-sm font-semibold text-gray-600">Price</th>
                  <th className="px-4 py-3 text-left text-sm font-semibold text-gray-600">Status</th>
                  <th className="px-4 py-3 text-right text-sm font-semibold text-gray-600">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y">
                {loading ? (
                  <SkeletonTableRows rows={5} columns={9} />
                ) : products.length === 0 ? (
                  <tr>
                    <td colSpan={9} className="px-4 py-8 text-center text-gray-500">
                      No products found
                    </td>
                  </tr>
                ) : (
                  products.map((product, index) => (
                    <React.Fragment key={`group-${product.id}`}>
                      <tr className={`hover:bg-gray-50 ${selectedIds.has(product.id) ? "bg-blue-50/50" : ""} ${fullEditingId === product.id ? "bg-blue-50/30" : ""}`}>
                        <td className="px-4 py-3">
                          <Checkbox
                            checked={selectedIds.has(product.id)}
                            onCheckedChange={() => toggleSelect(product.id)}
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
                          {quickEditingId === product.id ? (
                            <div className="flex items-center gap-2">
                              <Input
                                value={editFormData.name || ""}
                                onChange={(e) => setEditFormData({ ...editFormData, name: e.target.value })}
                                className="h-8 w-48"
                                autoFocus
                                onKeyDown={(e) => {
                                  if (e.key === "Enter") saveQuickEdit()
                                  if (e.key === "Escape") setQuickEditingId(null)
                                }}
                              />
                              <Button size="sm" className="h-8 w-8 p-0" onClick={saveQuickEdit} disabled={isSaving}>
                                <Check className="h-4 w-4" />
                              </Button>
                              <Button size="sm" variant="ghost" className="h-8 w-8 p-0" onClick={() => setQuickEditingId(null)}>
                                <X className="h-4 w-4" />
                              </Button>
                            </div>
                          ) : (
                            <div>
                              <p className="font-medium text-gray-900">{product.name}</p>
                              <p className="text-xs text-gray-500">ID #{product.id}</p>
                            </div>
                          )}
                        </td>
                        <td className="px-4 py-3">
                          {product.category && (
                            <Badge variant="secondary" className="font-normal">
                              {product.category.name}
                            </Badge>
                          )}
                        </td>
                        <td className="px-4 py-3 text-center">
                          {product.isFeatured && (
                            <Star className="mx-auto h-5 w-5 fill-yellow-400 text-yellow-400" />
                          )}
                        </td>
                        <td className="px-4 py-3">
                          <div className="flex flex-col gap-1">
                            {product.comparePrice && (
                              <span className="text-xs text-gray-400 line-through">
                                {formatPrice(product.comparePrice)}
                              </span>
                            )}
                            <span className="font-semibold text-gray-900">
                              {formatPrice(product.price)}
                            </span>
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
                                ? "bg-green-100 text-green-700"
                                : product.status === "DRAFT"
                                ? "bg-yellow-100 text-yellow-700"
                                : "bg-gray-100 text-gray-700"
                            }`}
                          >
                            {product.status}
                          </Badge>
                        </td>
                        <td className="px-4 py-3 text-right">
                          <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                              <Button variant="ghost" size="sm">
                                <MoreHorizontal className="h-4 w-4" />
                              </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                              <DropdownMenuItem onClick={() => startQuickEdit(product)} disabled={quickEditingId === product.id}>
                                <Edit2 className="mr-2 h-4 w-4" />
                                Quick Edit
                              </DropdownMenuItem>
                              <DropdownMenuItem onClick={() => startFullEdit(product)}>
                                <Edit className="mr-2 h-4 w-4" />
                                Full Edit
                              </DropdownMenuItem>
                              <DropdownMenuItem asChild>
                                <Link href={`/products/${product.slug}`} target="_blank">
                                  <Eye className="mr-2 h-4 w-4" />
                                  View
                                </Link>
                              </DropdownMenuItem>
                              <DropdownMenuItem asChild>
                                <Link href={`/admin/products/${product.id}/edit`}>
                                  <Edit className="mr-2 h-4 w-4" />
                                  Edit Full Product
                                </Link>
                              </DropdownMenuItem>
                              <DropdownMenuSeparator />
                              <DropdownMenuItem onClick={() => handleDelete(product)} className="text-red-600">
                                <Trash2 className="mr-2 h-4 w-4" />
                                Delete
                              </DropdownMenuItem>
                            </DropdownMenuContent>
                          </DropdownMenu>
                        </td>
                      </tr>

                      {/* Full Edit Panel */}
                      {fullEditingId === product.id && (
                        <tr className="bg-blue-50/30 border-l-4 border-l-blue-400">
                          <td colSpan={9} className="p-6">
                            <div className="space-y-4">
                              <div className="flex items-center justify-between">
                                <h4 className="font-semibold text-blue-900 flex items-center gap-2">
                                  <Edit className="h-4 w-4" />
                                  Edit Product: {product.name}
                                </h4>
                                <Button variant="ghost" size="sm" onClick={() => setFullEditingId(null)}>
                                  <X className="h-4 w-4" />
                                </Button>
                              </div>

                              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="space-y-2">
                                  <Label>Name</Label>
                                  <Input
                                    value={editFormData.name || ""}
                                    onChange={(e) => setEditFormData({ ...editFormData, name: e.target.value })}
                                  />
                                </div>
                                <div className="space-y-2">
                                  <Label>Slug</Label>
                                  <Input
                                    value={editFormData.slug || ""}
                                    onChange={(e) => setEditFormData({ ...editFormData, slug: e.target.value })}
                                  />
                                </div>
                              </div>

                              <div className="space-y-2">
                                <Label>Short Description</Label>
                                <Input
                                  value={editFormData.shortDescription || ""}
                                  onChange={(e) => setEditFormData({ ...editFormData, shortDescription: e.target.value })}
                                />
                              </div>

                              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div className="space-y-2">
                                  <Label>Price (cents)</Label>
                                  <Input
                                    type="number"
                                    value={editFormData.price || 0}
                                    onChange={(e) => setEditFormData({ ...editFormData, price: parseInt(e.target.value) })}
                                  />
                                </div>
                                <div className="space-y-2">
                                  <Label>Compare Price (cents)</Label>
                                  <Input
                                    type="number"
                                    value={editFormData.comparePrice || ""}
                                    onChange={(e) => setEditFormData({ ...editFormData, comparePrice: parseInt(e.target.value) || undefined })}
                                  />
                                </div>
                                <div className="space-y-2">
                                  <Label>Status</Label>
                                  <select
                                    className="w-full h-10 px-3 rounded-md border border-input bg-background text-sm"
                                    value={editFormData.status || ""}
                                    onChange={(e) => setEditFormData({ ...editFormData, status: e.target.value as any })}
                                  >
                                    <option value="DRAFT">Draft</option>
                                    <option value="PUBLISHED">Published</option>
                                    <option value="ARCHIVED">Archived</option>
                                  </select>
                                </div>
                              </div>

                              <div className="flex items-center gap-2">
                                <input
                                  type="checkbox"
                                  id={`featured-${product.id}`}
                                  checked={editFormData.isFeatured || false}
                                  onChange={(e) => setEditFormData({ ...editFormData, isFeatured: e.target.checked })}
                                  className="h-4 w-4"
                                />
                                <Label htmlFor={`featured-${product.id}`}>Featured Product</Label>
                              </div>

                              <div className="flex justify-end gap-2 pt-4">
                                <Button variant="outline" onClick={() => setFullEditingId(null)}>
                                  Cancel
                                </Button>
                                <Button onClick={saveFullEdit} disabled={isSaving} className="bg-blue-600 hover:bg-blue-700">
                                  {isSaving ? (
                                    <>
                                      <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                      Saving...
                                    </>
                                  ) : (
                                    <>
                                      <Save className="mr-2 h-4 w-4" />
                                      Save Changes
                                    </>
                                  )}
                                </Button>
                              </div>
                            </div>
                          </td>
                        </tr>
                      )}
                    </React.Fragment>
                  ))
                )}
              </tbody>
            </table>
          </div>
        ) : (
          // Grid View
          <div className="p-4">
            {loading ? (
              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                {[...Array(6)].map((_, i) => (
                  <div key={i} className="h-64 bg-gray-100 animate-pulse rounded-lg" />
                ))}
              </div>
            ) : products.length === 0 ? (
              <div className="text-center py-12">
                <Package className="h-12 w-12 text-gray-400 mx-auto mb-4" />
                <p className="text-muted-foreground">No products found</p>
              </div>
            ) : (
              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                {products.map((product) => (
                  <Card key={product.id} className={`overflow-hidden ${selectedIds.has(product.id) ? "ring-2 ring-blue-500" : ""}`}>
                    <div className="aspect-square bg-muted relative">
                      {product.image ? (
                        <img src={product.image} alt={product.name} className="w-full h-full object-cover" />
                      ) : (
                        <div className="flex h-full items-center justify-center">
                          <Package className="h-12 w-12 text-muted-foreground" />
                        </div>
                      )}
                      <div className="absolute top-2 left-2">
                        <Checkbox checked={selectedIds.has(product.id)} onCheckedChange={() => toggleSelect(product.id)} />
                      </div>
                      {product.isFeatured && (
                        <div className="absolute top-2 right-2">
                          <Star className="h-5 w-5 fill-yellow-400 text-yellow-400" />
                        </div>
                      )}
                    </div>
                    <CardContent className="p-4">
                      <h4 className="font-medium line-clamp-1">{product.name}</h4>
                      <div className="flex items-center justify-between mt-2">
                        <span className="font-bold">{formatPrice(product.price)}</span>
                        <Badge className={product.status === "PUBLISHED" ? "bg-green-100 text-green-700" : "bg-yellow-100 text-yellow-700"}>
                          {product.status}
                        </Badge>
                      </div>
                      <div className="flex justify-end mt-3">
                        <DropdownMenu>
                          <DropdownMenuTrigger asChild>
                            <Button variant="ghost" size="sm" className="h-8 w-8 p-0">
                              <MoreVertical className="h-4 w-4" />
                            </Button>
                          </DropdownMenuTrigger>
                          <DropdownMenuContent align="end">
                            <DropdownMenuItem onClick={() => startFullEdit(product)}>
                              <Edit className="mr-2 h-4 w-4" />
                              Edit
                            </DropdownMenuItem>
                            <DropdownMenuItem asChild>
                              <Link href={`/products/${product.slug}`} target="_blank">
                                <Eye className="mr-2 h-4 w-4" />
                                View
                              </Link>
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem onClick={() => handleDelete(product)} className="text-red-600">
                              <Trash2 className="mr-2 h-4 w-4" />
                              Delete
                            </DropdownMenuItem>
                          </DropdownMenuContent>
                        </DropdownMenu>
                      </div>
                    </CardContent>
                  </Card>
                ))}
              </div>
            )}
          </div>
        )}

        {/* Pagination */}
        {totalPages > 1 && (
          <DataTablePagination
            currentPage={currentPage}
            totalPages={totalPages}
            pageSize={itemsPerPage}
            totalItems={totalItems}
            onPageChange={setCurrentPage}
            onPageSizeChange={(size) => {
              setItemsPerPage(size)
              setCurrentPage(1)
            }}
          />
        )}
      </Card>

      {/* Confirm Dialog */}
      <ConfirmDialog
        isOpen={confirmDialog.isOpen}
        title={confirmDialog.title}
        description={confirmDialog.description}
        onConfirm={() => {
          confirmDialog.onConfirm()
          setConfirmDialog((prev) => ({ ...prev, isOpen: false }))
        }}
        onClose={() => setConfirmDialog((prev) => ({ ...prev, isOpen: false }))}
      />
    </div>
  )
}
