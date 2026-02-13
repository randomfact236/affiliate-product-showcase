"use client"

import React, { useEffect, useState, useMemo, useRef } from "react"
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
  Edit2, 
  Trash2, 
  Tags, 
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
  Search,
  Filter,
  Eye,
  EyeOff,
  Palette
} from "lucide-react"
import { toast } from "sonner"
import { 
  getTags, 
  deleteTag, 
  createTag, 
  updateTag,
  toggleTagActive,
  bulkDeleteTags,
  bulkUpdateTags,
  type Tag 
} from "@/lib/api/tags"
import { ToastProvider } from "@/components/ui/toast-provider"
import { ConfirmDialog } from "@/components/ui/confirm-dialog"
import { DataTableSearch } from "@/components/ui/data-table-search"
import { DataTablePagination } from "@/components/ui/data-table-pagination"
import { SkeletonTableRows } from "@/components/ui/skeleton-table"
import { BulkActions } from "@/components/ui/bulk-actions"
import { AuditTrail, trackChanges } from "@/lib/audit-trail"
import { exportToCSV } from "@/lib/csv-utils"

// Predefined color options
const COLOR_OPTIONS = [
  { name: "Red", value: "#EF4444" },
  { name: "Orange", value: "#F97316" },
  { name: "Amber", value: "#F59E0B" },
  { name: "Yellow", value: "#EAB308" },
  { name: "Lime", value: "#84CC16" },
  { name: "Green", value: "#22C55E" },
  { name: "Emerald", value: "#10B981" },
  { name: "Teal", value: "#14B8A6" },
  { name: "Cyan", value: "#06B6D4" },
  { name: "Sky", value: "#0EA5E9" },
  { name: "Blue", value: "#3B82F6" },
  { name: "Indigo", value: "#6366F1" },
  { name: "Violet", value: "#8B5CF6" },
  { name: "Purple", value: "#A855F7" },
  { name: "Fuchsia", value: "#D946EF" },
  { name: "Pink", value: "#EC4899" },
  { name: "Rose", value: "#F43F5E" },
  { name: "Gray", value: "#6B7280" },
]

// Icon options
const ICON_OPTIONS = [
  { name: "Tag", value: "tag" },
  { name: "Star", value: "star" },
  { name: "Heart", value: "heart" },
  { name: "Zap", value: "zap" },
  { name: "Trending", value: "trending-up" },
  { name: "Award", value: "award" },
  { name: "Bookmark", value: "bookmark" },
  { name: "Crown", value: "crown" },
  { name: "Diamond", value: "diamond" },
  { name: "Flag", value: "flag" },
]

// CSV Columns
const CSV_COLUMNS = [
  { key: "name" as keyof Tag, label: "Name" },
  { key: "slug" as keyof Tag, label: "Slug" },
  { key: "description" as keyof Tag, label: "Description" },
  { key: "color" as keyof Tag, label: "Color" },
  { key: "icon" as keyof Tag, label: "Icon" },
  { key: "isActive" as keyof Tag, label: "IsActive" },
  { key: "sortOrder" as keyof Tag, label: "SortOrder" },
]

export default function ProductTagsPage() {
  const [tags, setTags] = useState<Tag[]>([])
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
    color: "",
    icon: "",
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

  // Add form state
  const [formData, setFormData] = useState({
    name: "",
    slug: "",
    description: "",
    color: "",
    icon: "",
    isActive: true,
    sortOrder: 0,
  })
  const [formErrors, setFormErrors] = useState<Record<string, string>>({})

  // Fetch tags
  useEffect(() => {
    fetchTags()
  }, [])

  const fetchTags = async () => {
    try {
      setLoading(true)
      const data = await getTags()
      setTags(data)
      setError(null)
    } catch (err) {
      console.error("Failed to fetch tags:", err)
      setError("Failed to load tags")
      toast.error("Failed to load tags")
    } finally {
      setLoading(false)
    }
  }

  // Filter and paginate
  const filteredTags = useMemo(() => {
    let filtered = tags

    if (searchQuery) {
      const query = searchQuery.toLowerCase()
      filtered = filtered.filter(
        (t) =>
          t.name.toLowerCase().includes(query) ||
          t.slug.toLowerCase().includes(query) ||
          t.description?.toLowerCase().includes(query)
      )
    }

    if (statusFilter !== "all") {
      const isActive = statusFilter === "active"
      filtered = filtered.filter((t) => t.isActive === isActive)
    }

    return filtered
  }, [tags, searchQuery, statusFilter])

  const totalPages = Math.ceil(filteredTags.length / pageSize)
  const paginatedTags = useMemo(() => {
    const start = (currentPage - 1) * pageSize
    return filteredTags.slice(start, start + pageSize)
  }, [filteredTags, currentPage, pageSize])

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
    setSelectAll(newSelected.size === paginatedTags.length)
  }

  const toggleSelectAll = () => {
    if (selectAll) {
      setSelectedIds(new Set())
      setSelectAll(false)
    } else {
      setSelectedIds(new Set(paginatedTags.map((t) => t.id)))
      setSelectAll(true)
    }
  }

  // Delete with confirmation
  const handleDelete = (id: string, name: string) => {
    setConfirmDialog({
      isOpen: true,
      title: "Delete Tag",
      description: `Are you sure you want to delete "${name}"? This action cannot be undone.`,
      onConfirm: async () => {
        try {
          await deleteTag(id)
          toast.success(`Tag "${name}" deleted successfully`)
          AuditTrail.log({
            action: "DELETE",
            entity: "Tag",
            entityId: id,
            changes: { name: { old: name } },
          })
          await fetchTags()
          setSelectedIds((prev) => {
            const newSet = new Set(prev)
            newSet.delete(id)
            return newSet
          })
        } catch (err) {
          console.error("Failed to delete tag:", err)
          toast.error(err instanceof Error ? err.message : "Failed to delete tag")
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
      title: "Delete Selected Tags",
      description: `Are you sure you want to delete ${selectedIds.size} selected tags?`,
      onConfirm: async () => {
        try {
          const result = await bulkDeleteTags(Array.from(selectedIds))
          toast.success(`${result.deleted} tags deleted`)
          AuditTrail.log({
            action: "BULK_DELETE",
            entity: "Tag",
            metadata: { count: result.deleted },
          })
          await fetchTags()
          setSelectedIds(new Set())
          setSelectAll(false)
        } catch (err) {
          console.error("Failed to bulk delete:", err)
          toast.error("Failed to delete tags")
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
      const result = await bulkUpdateTags(Array.from(selectedIds), { isActive: activate })
      toast.success(`${result.updated} tags ${activate ? "activated" : "deactivated"}`)
      AuditTrail.log({
        action: "BULK_UPDATE",
        entity: "Tag",
        metadata: { count: result.updated, field: "isActive", value: activate },
      })
      await fetchTags()
    } catch (err) {
      console.error("Failed to bulk update:", err)
      toast.error("Failed to update tags")
    }
  }

  // Export
  const handleExport = () => {
    const dataToExport = selectedIds.size > 0 
      ? tags.filter((t) => selectedIds.has(t.id))
      : tags

    exportToCSV(
      dataToExport,
      CSV_COLUMNS,
      `tags-${new Date().toISOString().split("T")[0]}.csv`
    )
    
    toast.success(`${dataToExport.length} tags exported`)
    AuditTrail.log({
      action: "EXPORT",
      entity: "Tag",
      metadata: { count: dataToExport.length },
    })
  }

  // Quick Edit
  const startQuickEdit = (tag: Tag) => {
    setFullEditingId(null)
    setShowAddForm(false)
    setQuickEditingId(tag.id)
    setQuickEditName(tag.name)
  }

  const saveQuickEdit = async (id: string) => {
    if (!quickEditName.trim()) {
      toast.error("Tag name is required")
      return
    }

    const tag = tags.find((t) => t.id === id)
    if (!tag) return

    setIsQuickUpdating(true)
    try {
      await updateTag(id, { name: quickEditName.trim() })
      toast.success("Tag updated")
      AuditTrail.log({
        action: "UPDATE",
        entity: "Tag",
        entityId: id,
        changes: { name: { old: tag.name, new: quickEditName.trim() } },
      })
      setQuickEditingId(null)
      await fetchTags()
    } catch (err) {
      console.error("Failed to update tag:", err)
      toast.error("Failed to update tag")
    } finally {
      setIsQuickUpdating(false)
    }
  }

  const cancelQuickEdit = () => {
    setQuickEditingId(null)
    setQuickEditName("")
  }

  // Full Edit
  const startFullEdit = (tag: Tag) => {
    setQuickEditingId(null)
    setShowAddForm(false)
    setFullEditingId(tag.id)
    setFullEditFormData({
      name: tag.name,
      slug: tag.slug,
      description: tag.description || "",
      color: tag.color || "",
      icon: tag.icon || "",
      isActive: tag.isActive,
      sortOrder: tag.sortOrder,
    })
  }

  const saveFullEdit = async (id: string) => {
    if (!fullEditFormData.name.trim()) {
      toast.error("Tag name is required")
      return
    }

    const tag = tags.find((t) => t.id === id)
    if (!tag) return

    setIsFullUpdating(true)
    try {
      await updateTag(id, {
        name: fullEditFormData.name,
        slug: fullEditFormData.slug,
        description: fullEditFormData.description || undefined,
        color: fullEditFormData.color || undefined,
        icon: fullEditFormData.icon || undefined,
        isActive: fullEditFormData.isActive,
        sortOrder: fullEditFormData.sortOrder,
      })
      toast.success("Tag updated successfully")
      const changes = trackChanges(tag, { ...tag, ...fullEditFormData })
      AuditTrail.log({
        action: "UPDATE",
        entity: "Tag",
        entityId: id,
        changes,
      })
      setFullEditingId(null)
      await fetchTags()
    } catch (err) {
      console.error("Failed to update tag:", err)
      toast.error("Failed to update tag")
    } finally {
      setIsFullUpdating(false)
    }
  }

  const cancelFullEdit = () => {
    setFullEditingId(null)
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
    if (!formData.name.trim()) errors.name = "Tag name is required"
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
        color: formData.color || undefined,
        icon: formData.icon || undefined,
      }

      await createTag(data)
      toast.success(`Tag "${data.name}" created successfully`)
      AuditTrail.log({
        action: "CREATE",
        entity: "Tag",
        changes: Object.entries(data).reduce((acc, [key, value]) => {
          acc[key] = { new: value };
          return acc;
        }, {} as Record<string, { old?: any; new?: any }>),
      })
      setFormData({
        name: "",
        slug: "",
        description: "",
        color: "",
        icon: "",
        isActive: true,
        sortOrder: 0,
      })
      setShowAddForm(false)
      await fetchTags()
    } catch (error) {
      console.error("Failed to create tag:", error)
      toast.error(error instanceof Error ? error.message : "Failed to create tag")
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
      color: "",
      icon: "",
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
      <ToastProvider />
      
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
                <Tags className="h-8 w-8" />
                Product Tags
              </h1>
              <p className="mt-2 text-blue-100">
                Manage tags for your products
              </p>
            </div>
            <Button 
              className="bg-white text-blue-600 hover:bg-blue-50"
              onClick={() => {
                setShowAddForm(!showAddForm)
                setFullEditingId(null)
                setQuickEditingId(null)
              }}
            >
              <Plus className="mr-2 h-4 w-4" />
              {showAddForm ? "Close Form" : "Add New Tag"}
            </Button>
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
                placeholder="Search tags..."
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
              <Button variant="outline" size="sm" onClick={handleExport}>
                <Download className="h-4 w-4 mr-2" />
                Export{selectedIds.size > 0 ? ` (${selectedIds.size})` : ""}
              </Button>
            </div>
          </div>
          
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

      {/* Add Tag Form */}
      {showAddForm && (
        <Card className="border-l-4 border-l-blue-500 animate-in slide-in-from-top-2 duration-200">
          <CardContent className="p-6">
            <div className="flex items-center justify-between mb-6">
              <h2 className="text-lg font-semibold flex items-center gap-2">
                <Tags className="h-5 w-5 text-blue-600" />
                Create New Tag
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
                    Tag Name <span className="text-red-500">*</span>
                  </Label>
                  <Input
                    id="name"
                    placeholder="Enter tag name..."
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
                    placeholder="tag-slug"
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
                    placeholder="Enter tag description..."
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
                    Active
                  </Label>
                </div>
              </div>

              <div className="space-y-4">
                <div className="space-y-2">
                  <Label htmlFor="color">Color</Label>
                  <Select
                    value={formData.color || "none"}
                    onValueChange={(value: string) =>
                      setFormData({ ...formData, color: value === "none" ? "" : value })
                    }
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="Select a color" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="none">No Color</SelectItem>
                      {COLOR_OPTIONS.map((color) => (
                        <SelectItem key={color.value} value={color.value}>
                          <span className="flex items-center gap-2">
                            <span className="w-4 h-4 rounded-full" style={{ backgroundColor: color.value }} />
                            {color.name}
                          </span>
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="icon">Icon</Label>
                  <Select
                    value={formData.icon || "none"}
                    onValueChange={(value: string) =>
                      setFormData({ ...formData, icon: value === "none" ? "" : value })
                    }
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="Select an icon" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="none">No Icon</SelectItem>
                      {ICON_OPTIONS.map((icon) => (
                        <SelectItem key={icon.value} value={icon.value}>
                          {icon.name}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

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

                {formData.color && (
                  <div className="flex items-center gap-2 p-3 bg-gray-50 rounded-lg">
                    <span className="text-sm text-gray-600">Preview:</span>
                    <span 
                      className="px-3 py-1 rounded-full text-white text-sm font-medium"
                      style={{ backgroundColor: formData.color }}
                    >
                      {formData.name || "Tag Preview"}
                    </span>
                  </div>
                )}
              </div>
            </div>

            <div className="flex items-center justify-end gap-3 mt-6 pt-6 border-t">
              <Button variant="outline" onClick={handleCancel}>
                Cancel
              </Button>
              <Button className="bg-blue-600 hover:bg-blue-700" onClick={handleSubmit} disabled={isSubmitting}>
                <Save className="h-4 w-4 mr-2" />
                {isSubmitting ? "Creating..." : "Create Tag"}
              </Button>
            </div>
          </CardContent>
        </Card>
      )}

      {/* Tags Table */}
      <Card>
        <CardContent className="p-0">
          {loading ? (
            <SkeletonTableRows columns={7} rows={5} />
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
                    <TableHead className="font-semibold">Color</TableHead>
                    <TableHead className="font-semibold">Products</TableHead>
                    <TableHead className="font-semibold">Status</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {paginatedTags.length === 0 ? (
                    <TableRow>
                      <TableCell colSpan={7} className="h-32 text-center text-muted-foreground">
                        <div className="flex flex-col items-center gap-2">
                          <Tags className="h-8 w-8 text-gray-400" />
                          <p>No tags found. {searchQuery && "Try adjusting your search."}</p>
                        </div>
                      </TableCell>
                    </TableRow>
                  ) : (
                    paginatedTags.map((tag) => (
                      <React.Fragment key={`group-${tag.id}`}>
                        <TableRow 
                          className={`hover:bg-gray-50 ${fullEditingId === tag.id ? 'bg-blue-50/50' : ''}`}
                        >
                          <TableCell>
                            <Checkbox
                              checked={selectedIds.has(tag.id)}
                              onCheckedChange={() => toggleSelection(tag.id)}
                              aria-label={`Select ${tag.name}`}
                            />
                          </TableCell>
                          
                          <TableCell>
                            {quickEditingId === tag.id ? (
                              <Input
                                value={quickEditName}
                                onChange={(e) => setQuickEditName(e.target.value)}
                                placeholder="Tag name"
                                className="h-8"
                                autoFocus
                                onKeyDown={(e) => {
                                  if (e.key === 'Enter') saveQuickEdit(tag.id)
                                  if (e.key === 'Escape') cancelQuickEdit()
                                }}
                              />
                            ) : (
                              <div className="flex items-center gap-2">
                                {tag.color && (
                                  <span 
                                    className="w-3 h-3 rounded-full"
                                    style={{ backgroundColor: tag.color }}
                                  />
                                )}
                                <span className="font-medium">{tag.name}</span>
                              </div>
                            )}
                          </TableCell>

                          <TableCell>
                            {quickEditingId === tag.id ? (
                              <div className="flex items-center gap-1">
                                <Button
                                  size="sm"
                                  className="h-7 px-2 bg-green-600 hover:bg-green-700"
                                  onClick={() => saveQuickEdit(tag.id)}
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
                                  onClick={() => startQuickEdit(tag)}
                                >
                                  <Zap className="h-3.5 w-3.5 mr-1" />
                                  Quick
                                </Button>
                                <Button
                                  size="sm"
                                  variant="outline"
                                  className="h-7 px-2 text-blue-600 border-blue-200 hover:bg-blue-50"
                                  onClick={() => startFullEdit(tag)}
                                >
                                  <Settings2 className="h-3.5 w-3.5 mr-1" />
                                  Edit
                                </Button>
                                <Button
                                  variant="ghost"
                                  size="sm"
                                  className="h-7 px-2 text-red-500 hover:text-red-700 hover:bg-red-50"
                                  onClick={() => handleDelete(tag.id, tag.name)}
                                >
                                  <Trash2 className="h-3.5 w-3.5" />
                                </Button>
                              </div>
                            )}
                          </TableCell>

                          <TableCell>
                            <code className="text-xs bg-gray-100 px-2 py-1 rounded">
                              {tag.slug}
                            </code>
                          </TableCell>

                          <TableCell>
                            {tag.color ? (
                              <div className="flex items-center gap-2">
                                <span 
                                  className="w-6 h-6 rounded border"
                                  style={{ backgroundColor: tag.color }}
                                />
                                <span className="text-xs text-gray-500">{tag.color}</span>
                              </div>
                            ) : (
                              <span className="text-gray-400">-</span>
                            )}
                          </TableCell>

                          <TableCell>
                            <span className="text-sm">
                              {tag.productCount} products
                            </span>
                          </TableCell>

                          <TableCell>
                            <button
                              onClick={async () => {
                                try {
                                  await toggleTagActive(tag.id)
                                  toast.success(`Tag ${tag.isActive ? "deactivated" : "activated"}`)
                                  fetchTags()
                                } catch {
                                  toast.error("Failed to update status")
                                }
                              }}
                              className="cursor-pointer"
                            >
                              {tag.isActive ? (
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
                        </TableRow>

                        {/* Full Edit Expanded Row */}
                        {fullEditingId === tag.id && (
                          <TableRow className="bg-blue-50/30 border-l-4 border-l-blue-500">
                            <TableCell colSpan={7} className="p-0">
                              <div className="p-6">
                                <div className="flex items-center justify-between mb-4">
                                  <h3 className="text-lg font-semibold flex items-center gap-2 text-blue-700">
                                    <Settings2 className="h-5 w-5" />
                                    Editing: {tag.name}
                                  </h3>
                                  <Button variant="ghost" size="sm" onClick={cancelFullEdit}>
                                    <X className="h-4 w-4 mr-1" />
                                    Cancel
                                  </Button>
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                  <div className="space-y-4">
                                    <div className="space-y-2">
                                      <Label>Tag Name <span className="text-red-500">*</span></Label>
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
                                        id={`edit-active-${tag.id}`}
                                        checked={fullEditFormData.isActive}
                                        onCheckedChange={(checked: boolean) =>
                                          setFullEditFormData({ ...fullEditFormData, isActive: checked })
                                        }
                                      />
                                      <Label htmlFor={`edit-active-${tag.id}`} className="font-normal cursor-pointer">
                                        Active
                                      </Label>
                                    </div>
                                  </div>

                                  <div className="space-y-4">
                                    <div className="space-y-2">
                                      <Label>Color</Label>
                                      <Select
                                        value={fullEditFormData.color || "none"}
                                        onValueChange={(value: string) =>
                                          setFullEditFormData({ ...fullEditFormData, color: value === "none" ? "" : value })
                                        }
                                      >
                                        <SelectTrigger className="bg-white">
                                          <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                          <SelectItem value="none">No Color</SelectItem>
                                          {COLOR_OPTIONS.map((color) => (
                                            <SelectItem key={color.value} value={color.value}>
                                              <span className="flex items-center gap-2">
                                                <span className="w-4 h-4 rounded-full" style={{ backgroundColor: color.value }} />
                                                {color.name}
                                              </span>
                                            </SelectItem>
                                          ))}
                                        </SelectContent>
                                      </Select>
                                    </div>
                                    <div className="space-y-2">
                                      <Label>Icon</Label>
                                      <Select
                                        value={fullEditFormData.icon || "none"}
                                        onValueChange={(value: string) =>
                                          setFullEditFormData({ ...fullEditFormData, icon: value === "none" ? "" : value })
                                        }
                                      >
                                        <SelectTrigger className="bg-white">
                                          <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                          <SelectItem value="none">No Icon</SelectItem>
                                          {ICON_OPTIONS.map((icon) => (
                                            <SelectItem key={icon.value} value={icon.value}>
                                              {icon.name}
                                            </SelectItem>
                                          ))}
                                        </SelectContent>
                                      </Select>
                                    </div>
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
                                  </div>
                                </div>

                                <div className="flex items-center justify-end gap-3 mt-6 pt-6 border-t">
                                  <Button variant="outline" onClick={cancelFullEdit}>
                                    Cancel
                                  </Button>
                                  <Button
                                    className="bg-blue-600 hover:bg-blue-700"
                                    onClick={() => saveFullEdit(tag.id)}
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
                totalItems={filteredTags.length}
                pageSize={pageSize}
                onPageChange={setCurrentPage}
                onPageSizeChange={setPageSize}
              />
            </>
          )}
        </CardContent>
      </Card>
    </div>
  )
}
