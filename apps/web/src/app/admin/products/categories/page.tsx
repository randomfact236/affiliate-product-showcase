"use client"

import React, { useEffect, useState, useMemo, useCallback, useRef } from "react"
import { useRouter } from "next/navigation"
import Link from "next/link"
import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Textarea } from "@/components/ui/textarea"
import { Checkbox } from "@/components/ui/checkbox"
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select"
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table"
import { 
  Plus, 
  Trash2, 
  FolderTree, 
  ArrowLeft, 
  Package, 
  X, 
  Save, 
  Check, 
  Zap,
  Settings2,
  Download,
  Upload,
  History,
  ChevronDown,
  ChevronUp,
  Search,
  Filter,
  Eye,
  EyeOff
} from "lucide-react"
import { toast } from "sonner"
import { 
  getCategories, 
  deleteCategory, 
  createCategory, 
  updateCategory,
  bulkDeleteCategories,
  bulkUpdateCategories,
  type Category 
} from "@/lib/api/categories"
import { ToastProvider } from "@/components/ui/toast-provider"
import { ConfirmDialog } from "@/components/ui/confirm-dialog"
import { DataTableSearch } from "@/components/ui/data-table-search"
import { DataTablePagination } from "@/components/ui/data-table-pagination"
import { SkeletonTable, SkeletonTableRows } from "@/components/ui/skeleton-table"
import { BulkActions } from "@/components/ui/bulk-actions"
import { AuditTrail, trackChanges } from "@/lib/audit-trail"
import { exportToCSV, parseCSV } from "@/lib/csv-utils"

// CSV Column mapping for import/export
const CSV_COLUMNS = [
  { key: "name" as keyof Category, label: "Name" },
  { key: "slug" as keyof Category, label: "Slug" },
  { key: "description" as keyof Category, label: "Description" },
  { key: "isActive" as keyof Category, label: "IsActive" },
  { key: "sortOrder" as keyof Category, label: "SortOrder" },
  { key: "metaTitle" as keyof Category, label: "MetaTitle" },
  { key: "metaDescription" as keyof Category, label: "MetaDescription" },
]

export default function ProductCategoriesPage() {
  const router = useRouter()
  const [categories, setCategories] = useState<Category[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  
  // Search and filter
  const [searchQuery, setSearchQuery] = useState("")
  const [statusFilter, setStatusFilter] = useState<string>("all")
  
  // Pagination
  const [currentPage, setCurrentPage] = useState(1)
  const [pageSize, setPageSize] = useState(10)
  
  // Bulk selection
  const [selectedIds, setSelectedIds] = useState<Set<string>>(new Set())
  const [selectAll, setSelectAll] = useState(false)
  
  // Add form state
  const [showAddForm, setShowAddForm] = useState(false)
  const [isSubmitting, setIsSubmitting] = useState(false)
  
  // Quick Edit state
  const [quickEditingId, setQuickEditingId] = useState<string | null>(null)
  const [quickEditName, setQuickEditName] = useState("")
  const [isQuickUpdating, setIsQuickUpdating] = useState(false)

  // Full Edit state
  const [fullEditingId, setFullEditingId] = useState<string | null>(null)
  const [fullEditFormData, setFullEditFormData] = useState({
    name: "",
    slug: "",
    description: "",
    parentId: "",
    metaTitle: "",
    metaDescription: "",
    image: "",
    isActive: true,
    sortOrder: 0,
  })
  const [isFullUpdating, setIsFullUpdating] = useState(false)

  // Confirmation dialogs
  const [confirmDialog, setConfirmDialog] = useState<{
    isOpen: boolean
    title: string
    description: string
    onConfirm: () => void
    variant?: "default" | "destructive"
  }>({
    isOpen: false,
    title: "",
    description: "",
    onConfirm: () => {},
  })

  // Import/Export
  const [showImportDialog, setShowImportDialog] = useState(false)
  const [importData, setImportData] = useState<Partial<Category>[]>([])
  const [isImporting, setIsImporting] = useState(false)
  const fileInputRef = useRef<HTMLInputElement>(null)

  // Audit trail
  const [showAuditTrail, setShowAuditTrail] = useState(false)

  // Add form state
  const [formData, setFormData] = useState({
    name: "",
    slug: "",
    description: "",
    parentId: "",
    metaTitle: "",
    metaDescription: "",
    image: "",
    isActive: true,
    sortOrder: 0,
  })
  const [formErrors, setFormErrors] = useState<Record<string, string>>({})

  // Fetch categories
  useEffect(() => {
    fetchCategories()
  }, [])

  const fetchCategories = async () => {
    try {
      setLoading(true)
      const data = await getCategories()
      setCategories(data)
      setError(null)
    } catch (err) {
      console.error("Failed to fetch categories:", err)
      setError("Failed to load categories")
      toast.error("Failed to load categories")
    } finally {
      setLoading(false)
    }
  }

  // Filter and paginate categories
  const filteredCategories = useMemo(() => {
    let filtered = categories

    // Search filter
    if (searchQuery) {
      const query = searchQuery.toLowerCase()
      filtered = filtered.filter(
        (c) =>
          c.name.toLowerCase().includes(query) ||
          c.slug.toLowerCase().includes(query) ||
          c.description?.toLowerCase().includes(query)
      )
    }

    // Status filter
    if (statusFilter !== "all") {
      const isActive = statusFilter === "active"
      filtered = filtered.filter((c) => c.isActive === isActive)
    }

    return filtered
  }, [categories, searchQuery, statusFilter])

  const totalPages = Math.ceil(filteredCategories.length / pageSize)
  const paginatedCategories = useMemo(() => {
    const start = (currentPage - 1) * pageSize
    return filteredCategories.slice(start, start + pageSize)
  }, [filteredCategories, currentPage, pageSize])

  // Reset page when filters change
  useEffect(() => {
    setCurrentPage(1)
  }, [searchQuery, statusFilter, pageSize])

  // Selection handlers
  const toggleSelection = (id: string) => {
    const newSelected = new Set(selectedIds)
    if (newSelected.has(id)) {
      newSelected.delete(id)
    } else {
      newSelected.add(id)
    }
    setSelectedIds(newSelected)
    setSelectAll(newSelected.size === paginatedCategories.length)
  }

  const toggleSelectAll = () => {
    if (selectAll) {
      setSelectedIds(new Set())
      setSelectAll(false)
    } else {
      setSelectedIds(new Set(paginatedCategories.map((c) => c.id)))
      setSelectAll(true)
    }
  }

  // Delete with confirmation
  const handleDelete = (id: string, name: string) => {
    setConfirmDialog({
      isOpen: true,
      title: "Delete Category",
      description: `Are you sure you want to delete "${name}"? This action cannot be undone.`,
      onConfirm: async () => {
        try {
          await deleteCategory(id)
          toast.success(`Category "${name}" deleted successfully`)
          
          // Audit log
          AuditTrail.log({
            action: "DELETE",
            entity: "Category",
            entityId: id,
            changes: { name: { old: name } },
          })
          
          await fetchCategories()
          setSelectedIds((prev) => {
            const newSet = new Set(prev)
            newSet.delete(id)
            return newSet
          })
        } catch (err) {
          console.error("Failed to delete category:", err)
          toast.error(err instanceof Error ? err.message : "Failed to delete category")
        }
        setConfirmDialog((prev) => ({ ...prev, isOpen: false }))
      },
      variant: "destructive",
    })
  }

  // Bulk delete
  const handleBulkDelete = () => {
    if (selectedIds.size === 0) return
    
    setConfirmDialog({
      isOpen: true,
      title: "Delete Selected Categories",
      description: `Are you sure you want to delete ${selectedIds.size} selected categories? This action cannot be undone.`,
      onConfirm: async () => {
        try {
          const result = await bulkDeleteCategories(Array.from(selectedIds))
          toast.success(`${result.deleted} categories deleted${result.failed > 0 ? `, ${result.failed} failed` : ""}`)
          
          // Audit log
          AuditTrail.log({
            action: "BULK_DELETE",
            entity: "Category",
            metadata: { count: result.deleted },
          })
          
          await fetchCategories()
          setSelectedIds(new Set())
          setSelectAll(false)
        } catch (err) {
          console.error("Failed to bulk delete:", err)
          toast.error("Failed to delete categories")
        }
        setConfirmDialog((prev) => ({ ...prev, isOpen: false }))
      },
      variant: "destructive",
    })
  }

  // Bulk activate/deactivate
  const handleBulkActivate = async (activate: boolean) => {
    if (selectedIds.size === 0) return
    
    try {
      const result = await bulkUpdateCategories(Array.from(selectedIds), { isActive: activate })
      toast.success(`${result.updated} categories ${activate ? "activated" : "deactivated"}`)
      
      // Audit log
      AuditTrail.log({
        action: "BULK_UPDATE",
        entity: "Category",
        metadata: { count: result.updated, field: "isActive", value: activate },
      })
      
      await fetchCategories()
    } catch (err) {
      console.error("Failed to bulk update:", err)
      toast.error("Failed to update categories")
    }
  }

  // Quick Edit
  const startQuickEdit = (category: Category) => {
    setFullEditingId(null)
    setShowAddForm(false)
    setQuickEditingId(category.id)
    setQuickEditName(category.name)
  }

  const saveQuickEdit = async (id: string) => {
    if (!quickEditName.trim()) {
      toast.error("Category name is required")
      return
    }

    const category = categories.find((c) => c.id === id)
    if (!category) return

    setIsQuickUpdating(true)
    try {
      await updateCategory(id, { name: quickEditName.trim() })
      toast.success("Category updated")
      
      // Audit log
      AuditTrail.log({
        action: "UPDATE",
        entity: "Category",
        entityId: id,
        changes: { name: { old: category.name, new: quickEditName.trim() } },
      })
      
      setQuickEditingId(null)
      await fetchCategories()
    } catch (err) {
      console.error("Failed to update category:", err)
      toast.error("Failed to update category")
    } finally {
      setIsQuickUpdating(false)
    }
  }

  const cancelQuickEdit = () => {
    setQuickEditingId(null)
    setQuickEditName("")
  }

  // Full Edit
  const startFullEdit = (category: Category) => {
    setQuickEditingId(null)
    setShowAddForm(false)
    setFullEditingId(category.id)
    setFullEditFormData({
      name: category.name,
      slug: category.slug,
      description: category.description || "",
      parentId: category.parentId || "",
      metaTitle: category.metaTitle || "",
      metaDescription: category.metaDescription || "",
      image: category.image || "",
      isActive: category.isActive,
      sortOrder: category.sortOrder,
    })
  }

  const saveFullEdit = async (id: string) => {
    if (!fullEditFormData.name.trim()) {
      toast.error("Category name is required")
      return
    }

    const category = categories.find((c) => c.id === id)
    if (!category) return

    setIsFullUpdating(true)
    try {
      const updateData: Partial<typeof fullEditFormData> = {
        name: fullEditFormData.name,
        slug: fullEditFormData.slug,
        description: fullEditFormData.description || undefined,
        isActive: fullEditFormData.isActive,
        sortOrder: fullEditFormData.sortOrder,
        metaTitle: fullEditFormData.metaTitle || undefined,
        metaDescription: fullEditFormData.metaDescription || undefined,
        image: fullEditFormData.image || undefined,
      }

      if (fullEditFormData.parentId) {
        updateData.parentId = fullEditFormData.parentId
      }

      await updateCategory(id, updateData)
      toast.success("Category updated successfully")
      
      // Audit log with change tracking
      const changes = trackChanges(category, { ...category, ...updateData })
      AuditTrail.log({
        action: "UPDATE",
        entity: "Category",
        entityId: id,
        changes,
      })
      
      setFullEditingId(null)
      await fetchCategories()
    } catch (err) {
      console.error("Failed to update category:", err)
      toast.error("Failed to update category")
    } finally {
      setIsFullUpdating(false)
    }
  }

  const cancelFullEdit = () => {
    setFullEditingId(null)
  }

  // Export
  const handleExport = () => {
    const dataToExport = selectedIds.size > 0 
      ? categories.filter((c) => selectedIds.has(c.id))
      : categories

    exportToCSV(
      dataToExport,
      CSV_COLUMNS,
      `categories-${new Date().toISOString().split("T")[0]}.csv`
    )
    
    toast.success(`${dataToExport.length} categories exported`)
    
    // Audit log
    AuditTrail.log({
      action: "EXPORT",
      entity: "Category",
      metadata: { count: dataToExport.length },
    })
  }

  // Import
  const handleImport = async (event: React.ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0]
    if (!file) return

    const text = await file.text()
    try {
      const parsed = parseCSV<Partial<Category>>(text, [
        { csvHeader: "Name", key: "name" },
        { csvHeader: "Slug", key: "slug" },
        { csvHeader: "Description", key: "description" },
        { csvHeader: "IsActive", key: "isActive", transform: (v) => v.toLowerCase() === "true" },
        { csvHeader: "SortOrder", key: "sortOrder", transform: (v) => parseInt(v) || 0 },
        { csvHeader: "MetaTitle", key: "metaTitle" },
        { csvHeader: "MetaDescription", key: "metaDescription" },
      ])

      setImportData(parsed)
      setShowImportDialog(true)
    } catch (err) {
      toast.error("Failed to parse CSV file")
    }
    
    // Reset input
    if (fileInputRef.current) {
      fileInputRef.current.value = ""
    }
  }

  const confirmImport = async () => {
    setIsImporting(true)
    let success = 0
    let failed = 0

    for (const item of importData) {
      try {
        if (item.name && item.slug) {
          await createCategory({
            name: item.name,
            slug: item.slug,
            description: item.description,
            isActive: item.isActive ?? true,
            sortOrder: item.sortOrder ?? 0,
            metaTitle: item.metaTitle,
            metaDescription: item.metaDescription,
          })
          success++
        }
      } catch {
        failed++
      }
    }

    toast.success(`${success} categories imported${failed > 0 ? `, ${failed} failed` : ""}`)
    
    // Audit log
    AuditTrail.log({
      action: "IMPORT",
      entity: "Category",
      metadata: { success, failed },
    })
    
    setShowImportDialog(false)
    setImportData([])
    await fetchCategories()
    setIsImporting(false)
  }

  // Form handlers
  const generateSlug = (name: string) => {
    return name
      .toLowerCase()
      .trim()
      .replace(/[^\w\s-]/g, "")
      .replace(/\s+/g, "-")
      .replace(/-+/g, "-")
  }

  const handleNameChange = (value: string) => {
    const newSlug = formData.slug === generateSlug(formData.name) 
      ? generateSlug(value) 
      : formData.slug
    setFormData({ ...formData, name: value, slug: newSlug })
  }

  const handleFullEditNameChange = (value: string) => {
    const newSlug = fullEditFormData.slug === generateSlug(fullEditFormData.name) 
      ? generateSlug(value) 
      : fullEditFormData.slug
    setFullEditFormData({ ...fullEditFormData, name: value, slug: newSlug })
  }

  const validateForm = () => {
    const errors: Record<string, string> = {}
    if (!formData.name.trim()) errors.name = "Category name is required"
    if (!formData.slug.trim()) errors.slug = "Slug is required"
    else if (!/^[a-z0-9-]+$/.test(formData.slug)) errors.slug = "Invalid slug format"
    setFormErrors(errors)
    return Object.keys(errors).length === 0
  }

  const handleSubmit = async () => {
    if (!validateForm()) return

    setIsSubmitting(true)
    try {
      const data = {
        name: formData.name,
        slug: formData.slug,
        isActive: formData.isActive,
        sortOrder: formData.sortOrder,
        description: formData.description || undefined,
        parentId: formData.parentId || undefined,
        metaTitle: formData.metaTitle || undefined,
        metaDescription: formData.metaDescription || undefined,
        image: formData.image || undefined,
      }

      await createCategory(data)
      toast.success(`Category "${data.name}" created successfully`)
      
      // Audit log
      AuditTrail.log({
        action: "CREATE",
        entity: "Category",
        changes: Object.entries(data).reduce((acc, [key, value]) => {
          acc[key] = { new: value };
          return acc;
        }, {} as Record<string, { old?: any; new?: any }>),
      })
      
      setFormData({
        name: "",
        slug: "",
        description: "",
        parentId: "",
        metaTitle: "",
        metaDescription: "",
        image: "",
        isActive: true,
        sortOrder: 0,
      })
      setShowAddForm(false)
      await fetchCategories()
    } catch (error) {
      console.error("Failed to create category:", error)
      toast.error(error instanceof Error ? error.message : "Failed to create category")
    } finally {
      setIsSubmitting(false)
    }
  }

  const handleCancel = () => {
    setShowAddForm(false)
    setFormData({
      name: "",
      slug: "",
      description: "",
      parentId: "",
      metaTitle: "",
      metaDescription: "",
      image: "",
      isActive: true,
      sortOrder: 0,
    })
    setFormErrors({})
  }

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString("en-US", {
      year: "numeric",
      month: "short",
      day: "numeric",
    })
  }

  return (
    <div className="space-y-6">
      {/* Toast Provider */}
      <ToastProvider />
      
      {/* Confirmation Dialog */}
      <ConfirmDialog
        isOpen={confirmDialog.isOpen}
        onClose={() => setConfirmDialog((prev) => ({ ...prev, isOpen: false }))}
        onConfirm={confirmDialog.onConfirm}
        title={confirmDialog.title}
        description={confirmDialog.description}
        confirmText={confirmDialog.variant === "destructive" ? "Delete" : "Confirm"}
        variant={confirmDialog.variant}
      />

      {/* Header */}
      <div className="relative overflow-hidden rounded-xl bg-gradient-to-r from-blue-600 via-blue-500 to-cyan-500 px-8 py-8 text-white">
        <div className="relative z-10">
          <div className="flex items-center gap-2 mb-2">
            <Button variant="ghost" size="sm" className="text-white/80 hover:text-white hover:bg-white/20" asChild>
              <Link href="/admin/products">
                <ArrowLeft className="h-4 w-4 mr-1" />
                Back to Products
              </Link>
            </Button>
          </div>
          <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
              <h1 className="text-3xl font-bold flex items-center gap-3">
                <Package className="h-8 w-8" />
                Product Categories
              </h1>
              <p className="mt-2 text-blue-100">
                Manage categories for your products
              </p>
            </div>
            <div className="flex gap-2">
              <Button
                variant="outline"
                className="bg-white/10 border-white/20 text-white hover:bg-white/20 hover:text-white"
                onClick={() => setShowAuditTrail(true)}
              >
                <History className="h-4 w-4 mr-2" />
                Audit Log
              </Button>
              <Button 
                className="bg-white text-blue-600 hover:bg-blue-50"
                onClick={() => {
                  setShowAddForm(!showAddForm)
                  setFullEditingId(null)
                  setQuickEditingId(null)
                }}
              >
                <Plus className="mr-2 h-4 w-4" />
                {showAddForm ? "Close Form" : "Add New Category"}
              </Button>
            </div>
          </div>
        </div>
        <div className="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10" />
        <div className="absolute -bottom-10 -right-10 h-32 w-32 rounded-full bg-white/5" />
      </div>

      {/* Search and Filters */}
      <Card>
        <CardContent className="p-4">
          <div className="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
            <div className="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
              <DataTableSearch
                value={searchQuery}
                onChange={setSearchQuery}
                placeholder="Search categories..."
              />
              <Select value={statusFilter} onValueChange={setStatusFilter}>
                <SelectTrigger className="w-[150px]">
                  <Filter className="h-4 w-4 mr-2" />
                  <SelectValue placeholder="Status" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Status</SelectItem>
                  <SelectItem value="active">Active</SelectItem>
                  <SelectItem value="inactive">Inactive</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div className="flex gap-2">
              <input
                type="file"
                accept=".csv"
                ref={fileInputRef}
                onChange={handleImport}
                className="hidden"
              />
              <Button variant="outline" size="sm" onClick={() => fileInputRef.current?.click()}>
                <Upload className="h-4 w-4 mr-2" />
                Import CSV
              </Button>
              <Button variant="outline" size="sm" onClick={handleExport}>
                <Download className="h-4 w-4 mr-2" />
                Export{selectedIds.size > 0 ? ` (${selectedIds.size})` : ""}
              </Button>
            </div>
          </div>
          
          {/* Bulk Actions */}
          <div className="mt-4">
            <BulkActions
              selectedCount={selectedIds.size}
              onDelete={handleBulkDelete}
              onActivate={() => handleBulkActivate(true)}
              onDeactivate={() => handleBulkActivate(false)}
              onExport={handleExport}
            />
          </div>
        </CardContent>
      </Card>

      {/* Add Category Form */}
      {showAddForm && (
        <Card className="border-l-4 border-l-blue-500 animate-in slide-in-from-top-2 duration-200">
          <CardContent className="p-6">
            <div className="flex items-center justify-between mb-6">
              <h2 className="text-lg font-semibold flex items-center gap-2">
                <FolderTree className="h-5 w-5 text-blue-600" />
                Create New Category
              </h2>
              <Button variant="ghost" size="sm" onClick={handleCancel}>
                <X className="h-4 w-4 mr-1" />
                Cancel
              </Button>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div className="space-y-4">
                <div className="space-y-2">
                  <Label htmlFor="name">
                    Category Name <span className="text-red-500">*</span>
                  </Label>
                  <Input
                    id="name"
                    placeholder="Enter category name..."
                    value={formData.name}
                    onChange={(e) => handleNameChange(e.target.value)}
                    className={formErrors.name ? "border-red-500" : ""}
                  />
                  {formErrors.name && <p className="text-sm text-red-500">{formErrors.name}</p>}
                </div>

                <div className="space-y-2">
                  <Label htmlFor="slug">
                    Slug <span className="text-red-500">*</span>
                  </Label>
                  <Input
                    id="slug"
                    placeholder="category-slug"
                    value={formData.slug}
                    onChange={(e) => setFormData({ ...formData, slug: e.target.value })}
                    className={formErrors.slug ? "border-red-500" : ""}
                  />
                  {formErrors.slug && <p className="text-sm text-red-500">{formErrors.slug}</p>}
                </div>

                <div className="space-y-2">
                  <Label htmlFor="description">Description</Label>
                  <Textarea
                    id="description"
                    placeholder="Enter category description..."
                    rows={3}
                    value={formData.description}
                    onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                  />
                </div>

                <div className="flex items-center space-x-2">
                  <Checkbox
                    id="isActive"
                    checked={formData.isActive}
                    onCheckedChange={(checked: boolean) =>
                      setFormData({ ...formData, isActive: checked })
                    }
                  />
                  <Label htmlFor="isActive" className="font-normal cursor-pointer">
                    Active (visible on the website)
                  </Label>
                </div>
              </div>

              <div className="space-y-4">
                <div className="space-y-2">
                  <Label htmlFor="parentId">Parent Category</Label>
                  <Select
                    value={formData.parentId || "none"}
                    onValueChange={(value: string) =>
                      setFormData({ ...formData, parentId: value === "none" ? "" : value })
                    }
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="Select parent category (optional)" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="none">None (Top Level)</SelectItem>
                      {categories.map((category) => (
                        <SelectItem key={category.id} value={category.id}>
                          {"  ".repeat(category.depth)}{category.name}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="metaTitle">Meta Title</Label>
                  <Input
                    id="metaTitle"
                    placeholder="SEO title (optional)"
                    value={formData.metaTitle}
                    onChange={(e) => setFormData({ ...formData, metaTitle: e.target.value })}
                  />
                </div>

                <div className="space-y-2">
                  <Label htmlFor="metaDescription">Meta Description</Label>
                  <Textarea
                    id="metaDescription"
                    placeholder="SEO description (optional)"
                    rows={2}
                    value={formData.metaDescription}
                    onChange={(e) => setFormData({ ...formData, metaDescription: e.target.value })}
                  />
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div className="space-y-2">
                    <Label htmlFor="sortOrder">Sort Order</Label>
                    <Input
                      id="sortOrder"
                      type="number"
                      placeholder="0"
                      value={formData.sortOrder}
                      onChange={(e) =>
                        setFormData({ ...formData, sortOrder: parseInt(e.target.value) || 0 })
                      }
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="image">Image URL</Label>
                    <Input
                      id="image"
                      placeholder="https://..."
                      value={formData.image}
                      onChange={(e) => setFormData({ ...formData, image: e.target.value })}
                    />
                  </div>
                </div>
              </div>
            </div>

            <div className="flex items-center justify-end gap-3 mt-6 pt-6 border-t">
              <Button variant="outline" onClick={handleCancel}>
                Cancel
              </Button>
              <Button className="bg-blue-600 hover:bg-blue-700" onClick={handleSubmit} disabled={isSubmitting}>
                <Save className="h-4 w-4 mr-2" />
                {isSubmitting ? "Creating..." : "Create Category"}
              </Button>
            </div>
          </CardContent>
        </Card>
      )}

      {/* Categories Table */}
      <Card>
        <CardContent className="p-0">
          {loading ? (
            <SkeletonTable columns={7} rows={5} />
          ) : (
            <>
              <Table>
                <TableHeader>
                  <TableRow className="bg-gray-50">
                    <TableHead className="w-12">
                      <Checkbox
                        checked={selectAll}
                        onCheckedChange={toggleSelectAll}
                        aria-label="Select all"
                      />
                    </TableHead>
                    <TableHead className="font-semibold">Name</TableHead>
                    <TableHead className="font-semibold w-[220px]">Actions</TableHead>
                    <TableHead className="font-semibold">Slug</TableHead>
                    <TableHead className="font-semibold">Level</TableHead>
                    <TableHead className="font-semibold">Status</TableHead>
                    <TableHead className="font-semibold">Created</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {paginatedCategories.length === 0 ? (
                    <TableRow>
                      <TableCell colSpan={7} className="h-32 text-center text-muted-foreground">
                        <div className="flex flex-col items-center gap-2">
                          <FolderTree className="h-8 w-8 text-gray-400" />
                          <p>No categories found. {searchQuery && "Try adjusting your search."}</p>
                        </div>
                      </TableCell>
                    </TableRow>
                  ) : (
                    paginatedCategories.map((category) => (
                      <React.Fragment key={`group-${category.id}`}>
                        <TableRow 
                          className={`hover:bg-gray-50 ${fullEditingId === category.id ? 'bg-blue-50/50' : ''}`}
                        >
                          <TableCell>
                            <Checkbox
                              checked={selectedIds.has(category.id)}
                              onCheckedChange={() => toggleSelection(category.id)}
                              aria-label={`Select ${category.name}`}
                            />
                          </TableCell>
                          
                          <TableCell className="min-w-[200px]">
                            {quickEditingId === category.id ? (
                              <div className="flex items-center gap-2">
                                <span className="text-gray-400">{"│  ".repeat(category.depth)}</span>
                                <Input
                                  value={quickEditName}
                                  onChange={(e) => setQuickEditName(e.target.value)}
                                  placeholder="Category name"
                                  className="h-8 flex-1"
                                  autoFocus
                                  onKeyDown={(e) => {
                                    if (e.key === 'Enter') saveQuickEdit(category.id)
                                    if (e.key === 'Escape') cancelQuickEdit()
                                  }}
                                />
                              </div>
                            ) : (
                              <div className="flex items-center gap-2">
                                <span className="text-gray-400">{"│  ".repeat(category.depth)}</span>
                                <div>
                                  <span className="font-medium">{category.name}</span>
                                  {category.description && (
                                    <p className="text-xs text-gray-500 mt-0.5">{category.description}</p>
                                  )}
                                </div>
                              </div>
                            )}
                          </TableCell>

                          <TableCell>
                            {quickEditingId === category.id ? (
                              <div className="flex items-center gap-1">
                                <Button
                                  size="sm"
                                  className="h-7 px-2 bg-green-600 hover:bg-green-700"
                                  onClick={() => saveQuickEdit(category.id)}
                                  disabled={isQuickUpdating}
                                >
                                  <Check className="h-3.5 w-3.5 mr-1" />
                                  Save
                                </Button>
                                <Button
                                  size="sm"
                                  variant="ghost"
                                  className="h-7 px-2"
                                  onClick={cancelQuickEdit}
                                >
                                  <X className="h-3.5 w-3.5" />
                                </Button>
                              </div>
                            ) : (
                              <div className="flex items-center gap-1">
                                <Button
                                  size="sm"
                                  variant="outline"
                                  className="h-7 px-2 text-amber-600 border-amber-200 hover:bg-amber-50"
                                  onClick={() => startQuickEdit(category)}
                                  title="Quick Edit - Edit name only"
                                >
                                  <Zap className="h-3.5 w-3.5 mr-1" />
                                  Quick
                                </Button>
                                <Button
                                  size="sm"
                                  variant="outline"
                                  className="h-7 px-2 text-blue-600 border-blue-200 hover:bg-blue-50"
                                  onClick={() => startFullEdit(category)}
                                  title="Edit - Full editing with all fields"
                                >
                                  <Settings2 className="h-3.5 w-3.5 mr-1" />
                                  Edit
                                </Button>
                                <Button
                                  variant="ghost"
                                  size="sm"
                                  className="h-7 px-2 text-red-500 hover:text-red-700 hover:bg-red-50"
                                  onClick={() => handleDelete(category.id, category.name)}
                                  title="Delete category"
                                >
                                  <Trash2 className="h-3.5 w-3.5" />
                                </Button>
                              </div>
                            )}
                          </TableCell>

                          <TableCell>
                            <code className="text-xs bg-gray-100 px-2 py-1 rounded">
                              {category.slug}
                            </code>
                          </TableCell>

                          <TableCell>
                            {category.depth === 0 ? (
                              <span className="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full">
                                Top Level
                              </span>
                            ) : (
                              <span className="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded-full">
                                Level {category.depth}
                              </span>
                            )}
                          </TableCell>

                          <TableCell>
                            <button
                              onClick={() => {
                                updateCategory(category.id, { isActive: !category.isActive })
                                  .then(() => {
                                    toast.success(`Category ${category.isActive ? "deactivated" : "activated"}`)
                                    fetchCategories()
                                  })
                                  .catch(() => toast.error("Failed to update status"))
                              }}
                              className="cursor-pointer"
                            >
                              {category.isActive ? (
                                <span className="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full flex items-center gap-1">
                                  <Eye className="h-3 w-3" />
                                  Active
                                </span>
                              ) : (
                                <span className="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded-full flex items-center gap-1">
                                  <EyeOff className="h-3 w-3" />
                                  Inactive
                                </span>
                              )}
                            </button>
                          </TableCell>

                          <TableCell className="text-sm text-gray-500">
                            {formatDate(category.createdAt)}
                          </TableCell>
                        </TableRow>

                        {/* Full Edit Expanded Row */}
                        {fullEditingId === category.id && (
                          <TableRow className="bg-blue-50/30 border-l-4 border-l-blue-500">
                            <TableCell colSpan={7} className="p-0">
                              <div className="p-6">
                                <div className="flex items-center justify-between mb-4">
                                  <h3 className="text-lg font-semibold flex items-center gap-2 text-blue-700">
                                    <Settings2 className="h-5 w-5" />
                                    Editing: {category.name}
                                  </h3>
                                  <Button variant="ghost" size="sm" onClick={cancelFullEdit}>
                                    <X className="h-4 w-4 mr-1" />
                                    Cancel
                                  </Button>
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                  <div className="space-y-4">
                                    <div className="space-y-2">
                                      <Label>Category Name <span className="text-red-500">*</span></Label>
                                      <Input
                                        value={fullEditFormData.name}
                                        onChange={(e) => handleFullEditNameChange(e.target.value)}
                                        className="bg-white"
                                      />
                                    </div>
                                    <div className="space-y-2">
                                      <Label>Slug <span className="text-red-500">*</span></Label>
                                      <Input
                                        value={fullEditFormData.slug}
                                        onChange={(e) => setFullEditFormData({ ...fullEditFormData, slug: e.target.value })}
                                        className="bg-white"
                                      />
                                    </div>
                                    <div className="space-y-2">
                                      <Label>Description</Label>
                                      <Textarea
                                        value={fullEditFormData.description}
                                        onChange={(e) => setFullEditFormData({ ...fullEditFormData, description: e.target.value })}
                                        rows={3}
                                        className="bg-white"
                                      />
                                    </div>
                                    <div className="flex items-center space-x-2">
                                      <Checkbox
                                        id={`edit-active-${category.id}`}
                                        checked={fullEditFormData.isActive}
                                        onCheckedChange={(checked: boolean) =>
                                          setFullEditFormData({ ...fullEditFormData, isActive: checked })
                                        }
                                      />
                                      <Label htmlFor={`edit-active-${category.id}`} className="font-normal cursor-pointer">
                                        Active
                                      </Label>
                                    </div>
                                  </div>

                                  <div className="space-y-4">
                                    <div className="space-y-2">
                                      <Label>Parent Category</Label>
                                      <Select
                                        value={fullEditFormData.parentId || "none"}
                                        onValueChange={(value: string) =>
                                          setFullEditFormData({ ...fullEditFormData, parentId: value === "none" ? "" : value })
                                        }
                                      >
                                        <SelectTrigger className="bg-white">
                                          <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                          <SelectItem value="none">None (Top Level)</SelectItem>
                                          {categories
                                            .filter((c) => c.id !== category.id)
                                            .map((cat) => (
                                              <SelectItem key={cat.id} value={cat.id}>
                                                {"  ".repeat(cat.depth)}{cat.name}
                                              </SelectItem>
                                            ))}
                                        </SelectContent>
                                      </Select>
                                    </div>
                                    <div className="space-y-2">
                                      <Label>Meta Title</Label>
                                      <Input
                                        value={fullEditFormData.metaTitle}
                                        onChange={(e) => setFullEditFormData({ ...fullEditFormData, metaTitle: e.target.value })}
                                        className="bg-white"
                                      />
                                    </div>
                                    <div className="space-y-2">
                                      <Label>Meta Description</Label>
                                      <Textarea
                                        value={fullEditFormData.metaDescription}
                                        onChange={(e) => setFullEditFormData({ ...fullEditFormData, metaDescription: e.target.value })}
                                        rows={2}
                                        className="bg-white"
                                      />
                                    </div>
                                    <div className="grid grid-cols-2 gap-4">
                                      <div className="space-y-2">
                                        <Label>Sort Order</Label>
                                        <Input
                                          type="number"
                                          value={fullEditFormData.sortOrder}
                                          onChange={(e) =>
                                            setFullEditFormData({ ...fullEditFormData, sortOrder: parseInt(e.target.value) || 0 })
                                          }
                                          className="bg-white"
                                        />
                                      </div>
                                      <div className="space-y-2">
                                        <Label>Image URL</Label>
                                        <Input
                                          value={fullEditFormData.image}
                                          onChange={(e) => setFullEditFormData({ ...fullEditFormData, image: e.target.value })}
                                          className="bg-white"
                                        />
                                      </div>
                                    </div>
                                  </div>
                                </div>

                                <div className="flex items-center justify-end gap-3 mt-6 pt-6 border-t">
                                  <Button variant="outline" onClick={cancelFullEdit}>
                                    Cancel
                                  </Button>
                                  <Button
                                    className="bg-blue-600 hover:bg-blue-700"
                                    onClick={() => saveFullEdit(category.id)}
                                    disabled={isFullUpdating}
                                  >
                                    <Save className="h-4 w-4 mr-2" />
                                    {isFullUpdating ? "Saving..." : "Save Changes"}
                                  </Button>
                                </div>
                              </div>
                            </TableCell>
                          </TableRow>
                        )}
                      </React.Fragment>
                    ))
                  )}
                </TableBody>
              </Table>
              
              <DataTablePagination
                currentPage={currentPage}
                totalPages={totalPages}
                totalItems={filteredCategories.length}
                pageSize={pageSize}
                onPageChange={setCurrentPage}
                onPageSizeChange={setPageSize}
              />
            </>
          )}
        </CardContent>
      </Card>

      {/* Import Dialog */}
      {showImportDialog && (
        <ConfirmDialog
          isOpen={showImportDialog}
          onClose={() => setShowImportDialog(false)}
          onConfirm={confirmImport}
          title="Import Categories"
          description={`Are you sure you want to import ${importData.length} categories?`}
          confirmText={isImporting ? "Importing..." : "Import"}
        />
      )}

      {/* Audit Trail Modal */}
      {showAuditTrail && (
        <div className="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
          <Card className="w-full max-w-4xl max-h-[80vh] overflow-auto">
            <CardContent className="p-6">
              <div className="flex items-center justify-between mb-4">
                <h3 className="text-lg font-semibold flex items-center gap-2">
                  <History className="h-5 w-5" />
                  Audit Trail
                </h3>
                <div className="flex gap-2">
                  <Button variant="outline" size="sm" onClick={() => AuditTrail.downloadAuditLog()}>
                    <Download className="h-4 w-4 mr-1" />
                    Download
                  </Button>
                  <Button variant="ghost" size="sm" onClick={() => setShowAuditTrail(false)}>
                    <X className="h-4 w-4" />
                  </Button>
                </div>
              </div>
              
              <div className="space-y-2">
                {AuditTrail.getAll().slice(0, 50).map((entry) => (
                  <div key={entry.id} className="p-3 bg-gray-50 rounded-lg text-sm">
                    <div className="flex items-center justify-between">
                      <span className="font-medium">{entry.action}</span>
                      <span className="text-gray-500">{new Date(entry.timestamp).toLocaleString()}</span>
                    </div>
                    <div className="text-gray-600">
                      {entry.entity} {entry.entityId && `- ${entry.entityId}`}
                    </div>
                    {entry.changes && Object.keys(entry.changes).length > 0 && (
                      <div className="mt-1 text-xs text-gray-500">
                        Changes: {Object.keys(entry.changes).join(", ")}
                      </div>
                    )}
                  </div>
                ))}
                {AuditTrail.getAll().length === 0 && (
                  <p className="text-center text-gray-500 py-8">No audit entries yet</p>
                )}
              </div>
            </CardContent>
          </Card>
        </div>
      )}
    </div>
  )
}
