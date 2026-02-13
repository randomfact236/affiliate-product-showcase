"use client"

import React, { useEffect, useState, useMemo } from "react"
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
  Sparkles, 
  ArrowLeft, 
  X, 
  Save, 
  Check, 
  Zap,
  Settings2,
  Download,
  Search,
  Filter,
  Eye,
  EyeOff,
  LayoutGrid,
  List,
  ChevronDown,
  ChevronUp
} from "lucide-react"
import { toast } from "sonner"
import { 
  getRibbons, 
  deleteRibbon, 
  createRibbon, 
  updateRibbon,
  toggleRibbonActive,
  bulkDeleteRibbons,
  bulkUpdateRibbons,
  type Ribbon 
} from "@/lib/api/ribbons"
import { ToastProvider } from "@/components/ui/toast-provider"
import { ConfirmDialog } from "@/components/ui/confirm-dialog"
import { DataTableSearch } from "@/components/ui/data-table-search"
import { DataTablePagination } from "@/components/ui/data-table-pagination"
import { SkeletonTable } from "@/components/ui/skeleton-table"
import { BulkActions } from "@/components/ui/bulk-actions"
import { AuditTrail, trackChanges } from "@/lib/audit-trail"
import { exportToCSV } from "@/lib/csv-utils"

// Position options
const POSITION_OPTIONS = [
  { name: "Top Left", value: "TOP_LEFT" },
  { name: "Top Right", value: "TOP_RIGHT" },
  { name: "Bottom Left", value: "BOTTOM_LEFT" },
  { name: "Bottom Right", value: "BOTTOM_RIGHT" },
]

// Icon options
const ICON_OPTIONS = [
  { name: "Star", value: "star" },
  { name: "Sparkles", value: "sparkles" },
  { name: "Tag", value: "tag" },
  { name: "Zap", value: "zap" },
  { name: "Trending Up", value: "trending-up" },
  { name: "Award", value: "award" },
  { name: "Crown", value: "crown" },
  { name: "Heart", value: "heart" },
  { name: "Flag", value: "flag" },
  { name: "Badge", value: "badge" },
]

// CSV Columns
const CSV_COLUMNS = [
  { key: "name" as keyof Ribbon, label: "Name" },
  { key: "label" as keyof Ribbon, label: "Label" },
  { key: "description" as keyof Ribbon, label: "Description" },
  { key: "position" as keyof Ribbon, label: "Position" },
  { key: "priority" as keyof Ribbon, label: "Priority" },
  { key: "isActive" as keyof Ribbon, label: "IsActive" },
]

export default function RibbonsPage() {
  const [ribbons, setRibbons] = useState<Ribbon[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [viewMode, setViewMode] = useState<"grid" | "list">("grid")
  
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
  
  // Quick Edit state - basic fields (label, color, position, active)
  const [quickEditingId, setQuickEditingId] = useState<string | null>(null)
  const [quickEditData, setQuickEditData] = useState({
    label: "",
    color: "#FFFFFF",
    bgColor: "#3B82F6",
    position: "TOP_RIGHT",
    isActive: true,
  })
  const [isQuickUpdating, setIsQuickUpdating] = useState(false)

  // Full Edit state - expanded under ribbon
  const [fullEditingId, setFullEditingId] = useState<string | null>(null)
  const [fullEditFormData, setFullEditFormData] = useState({
    name: "",
    label: "",
    description: "",
    color: "#FFFFFF",
    bgColor: "#3B82F6",
    icon: "",
    position: "TOP_RIGHT",
    priority: 1,
    isActive: true,
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
    label: "",
    description: "",
    color: "#FFFFFF",
    bgColor: "#3B82F6",
    icon: "",
    position: "TOP_RIGHT",
    priority: 1,
    isActive: true,
  })
  const [formErrors, setFormErrors] = useState<Record<string, string>>({})

  // Fetch ribbons
  useEffect(() => {
    fetchRibbons()
  }, [])

  const fetchRibbons = async () => {
    try {
      setLoading(true)
      const data = await getRibbons()
      setRibbons(data)
      setError(null)
    } catch (err) {
      console.error("Failed to fetch ribbons:", err)
      setError("Failed to load ribbons")
      toast.error("Failed to load ribbons")
    } finally {
      setLoading(false)
    }
  }

  // Filter and paginate
  const filteredRibbons = useMemo(() => {
    let filtered = ribbons

    if (searchQuery) {
      const query = searchQuery.toLowerCase()
      filtered = filtered.filter(
        (r) =>
          r.name.toLowerCase().includes(query) ||
          r.label.toLowerCase().includes(query) ||
          r.description?.toLowerCase().includes(query)
      )
    }

    if (statusFilter !== "all") {
      const isActive = statusFilter === "active"
      filtered = filtered.filter((r) => r.isActive === isActive)
    }

    return filtered
  }, [ribbons, searchQuery, statusFilter])

  const totalPages = Math.ceil(filteredRibbons.length / pageSize)
  const paginatedRibbons = useMemo(() => {
    const start = (currentPage - 1) * pageSize
    return filteredRibbons.slice(start, start + pageSize)
  }, [filteredRibbons, currentPage, pageSize])

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
    setSelectAll(newSelected.size === paginatedRibbons.length)
  }

  const toggleSelectAll = () => {
    if (selectAll) {
      setSelectedIds(new Set())
      setSelectAll(false)
    } else {
      setSelectedIds(new Set(paginatedRibbons.map((r) => r.id)))
      setSelectAll(true)
    }
  }

  // Delete with confirmation
  const handleDelete = (id: string, name: string) => {
    setConfirmDialog({
      isOpen: true,
      title: "Delete Ribbon",
      description: `Are you sure you want to delete "${name}"?`,
      onConfirm: async () => {
        try {
          await deleteRibbon(id)
          toast.success(`Ribbon "${name}" deleted`)
          AuditTrail.log({
            action: "DELETE",
            entity: "Ribbon",
            entityId: id,
            changes: { name: { old: name } },
          })
          await fetchRibbons()
          setSelectedIds((prev) => {
            const newSet = new Set(prev)
            newSet.delete(id)
            return newSet
          })
        } catch (err) {
          toast.error(err instanceof Error ? err.message : "Failed to delete ribbon")
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
      title: "Delete Selected Ribbons",
      description: `Delete ${selectedIds.size} selected ribbons?`,
      onConfirm: async () => {
        try {
          const result = await bulkDeleteRibbons(Array.from(selectedIds))
          toast.success(`${result.deleted} ribbons deleted`)
          AuditTrail.log({
            action: "BULK_DELETE",
            entity: "Ribbon",
            metadata: { count: result.deleted },
          })
          await fetchRibbons()
          setSelectedIds(new Set())
          setSelectAll(false)
        } catch (err) {
          toast.error("Failed to delete ribbons")
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
      const result = await bulkUpdateRibbons(Array.from(selectedIds), { isActive: activate })
      toast.success(`${result.updated} ribbons ${activate ? "activated" : "deactivated"}`)
      AuditTrail.log({
        action: "BULK_UPDATE",
        entity: "Ribbon",
        metadata: { count: result.updated, field: "isActive", value: activate },
      })
      await fetchRibbons()
    } catch (err) {
      toast.error("Failed to update ribbons")
    }
  }

  // Export
  const handleExport = () => {
    const dataToExport = selectedIds.size > 0 
      ? ribbons.filter((r) => selectedIds.has(r.id))
      : ribbons

    exportToCSV(dataToExport, CSV_COLUMNS, `ribbons-${new Date().toISOString().split("T")[0]}.csv`)
    toast.success(`${dataToExport.length} ribbons exported`)
    AuditTrail.log({ action: "EXPORT", entity: "Ribbon", metadata: { count: dataToExport.length } })
  }

  // Quick Edit - Basic fields (label, color, position, active)
  const startQuickEdit = (ribbon: Ribbon) => {
    setFullEditingId(null) // Close full edit if open
    setQuickEditingId(ribbon.id)
    setQuickEditData({
      label: ribbon.label,
      color: ribbon.color,
      bgColor: ribbon.bgColor,
      position: ribbon.position,
      isActive: ribbon.isActive,
    })
  }

  const saveQuickEdit = async (id: string) => {
    if (!quickEditData.label.trim()) {
      toast.error("Label is required")
      return
    }

    const ribbon = ribbons.find((r) => r.id === id)
    if (!ribbon) return

    setIsQuickUpdating(true)
    try {
      await updateRibbon(id, {
        label: quickEditData.label.trim(),
        color: quickEditData.color,
        bgColor: quickEditData.bgColor,
        position: quickEditData.position,
        isActive: quickEditData.isActive,
      })
      toast.success("Ribbon updated")
      AuditTrail.log({
        action: "UPDATE",
        entity: "Ribbon",
        entityId: id,
        changes: {
          label: { old: ribbon.label, new: quickEditData.label },
          color: { old: ribbon.color, new: quickEditData.color },
          bgColor: { old: ribbon.bgColor, new: quickEditData.bgColor },
          position: { old: ribbon.position, new: quickEditData.position },
        },
      })
      setQuickEditingId(null)
      await fetchRibbons()
    } catch (err) {
      toast.error("Failed to update ribbon")
    } finally {
      setIsQuickUpdating(false)
    }
  }

  const cancelQuickEdit = () => {
    setQuickEditingId(null)
  }

  // Full Edit - All fields, displayed under the ribbon
  const startFullEdit = (ribbon: Ribbon) => {
    setQuickEditingId(null) // Close quick edit if open
    setFullEditingId(ribbon.id)
    setFullEditFormData({
      name: ribbon.name,
      label: ribbon.label,
      description: ribbon.description || "",
      color: ribbon.color,
      bgColor: ribbon.bgColor,
      icon: ribbon.icon || "",
      position: ribbon.position,
      priority: ribbon.priority,
      isActive: ribbon.isActive,
    })
  }

  const saveFullEdit = async (id: string) => {
    if (!fullEditFormData.name.trim() || !fullEditFormData.label.trim()) {
      toast.error("Name and label are required")
      return
    }

    const ribbon = ribbons.find((r) => r.id === id)
    if (!ribbon) return

    setIsFullUpdating(true)
    try {
      await updateRibbon(id, {
        name: fullEditFormData.name,
        label: fullEditFormData.label,
        description: fullEditFormData.description || undefined,
        color: fullEditFormData.color,
        bgColor: fullEditFormData.bgColor,
        icon: fullEditFormData.icon || undefined,
        position: fullEditFormData.position,
        priority: fullEditFormData.priority,
        isActive: fullEditFormData.isActive,
      })
      toast.success("Ribbon updated successfully")
      const changes = trackChanges(ribbon, { ...ribbon, ...fullEditFormData })
      AuditTrail.log({ action: "UPDATE", entity: "Ribbon", entityId: id, changes })
      setFullEditingId(null)
      await fetchRibbons()
    } catch (err) {
      toast.error("Failed to update ribbon")
    } finally {
      setIsFullUpdating(false)
    }
  }

  const cancelFullEdit = () => {
    setFullEditingId(null)
  }

  // Form handlers
  const validateForm = () => {
    const errors: Record<string, string> = {}
    if (!formData.name.trim()) errors.name = "Name is required"
    if (!formData.label.trim()) errors.label = "Label is required"
    setFormErrors(errors)
    return Object.keys(errors).length === 0
  }

  const handleSubmit = async () => {
    if (!validateForm()) return

    setIsSubmitting(true)
    try {
      await createRibbon({
        name: formData.name,
        label: formData.label,
        description: formData.description || undefined,
        color: formData.color,
        bgColor: formData.bgColor,
        icon: formData.icon || undefined,
        position: formData.position,
        priority: formData.priority,
        isActive: formData.isActive,
      })
      toast.success(`Ribbon "${formData.name}" created`)
      AuditTrail.log({ 
        action: "CREATE", 
        entity: "Ribbon", 
        changes: Object.entries(formData).reduce((acc, [key, value]) => {
          acc[key] = { new: value };
          return acc;
        }, {} as Record<string, { old?: any; new?: any }>)
      })
      setFormData({
        name: "",
        label: "",
        description: "",
        color: "#FFFFFF",
        bgColor: "#3B82F6",
        icon: "",
        position: "TOP_RIGHT",
        priority: 1,
        isActive: true,
      })
      setShowAddForm(false)
      await fetchRibbons()
    } catch (error) {
      toast.error(error instanceof Error ? error.message : "Failed to create ribbon")
    } finally {
      setIsSubmitting(false)
    }
  }

  const handleCancel = () => {
    setShowAddForm(false)
    setFormData({
      name: "",
      label: "",
      description: "",
      color: "#FFFFFF",
      bgColor: "#3B82F6",
      icon: "",
      position: "TOP_RIGHT",
      priority: 1,
      isActive: true,
    })
    setFormErrors({})
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
      <div className="relative overflow-hidden rounded-xl bg-gradient-to-r from-purple-600 via-purple-500 to-pink-500 px-8 py-8 text-white">
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
                <Sparkles className="h-8 w-8" />
                Product Ribbons
              </h1>
              <p className="mt-2 text-purple-100">
                Manage badges and ribbons for products
              </p>
            </div>
            <div className="flex gap-2">
              <Button
                variant="outline"
                className="bg-white/10 border-white/20 text-white hover:bg-white/20"
                onClick={() => setViewMode(viewMode === "grid" ? "list" : "grid")}
              >
                {viewMode === "grid" ? <List className="h-4 w-4" /> : <LayoutGrid className="h-4 w-4" />}
              </Button>
              <Button 
                className="bg-white text-purple-600 hover:bg-purple-50"
                onClick={() => {
                  setShowAddForm(!showAddForm)
                  setFullEditingId(null)
                  setQuickEditingId(null)
                }}
              >
                <Plus className="mr-2 h-4 w-4" />
                {showAddForm ? "Close" : "Add Ribbon"}
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
                placeholder="Search ribbons..."
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

      {/* Add Ribbon Form */}
      {showAddForm && (
        <Card className="border-l-4 border-l-purple-500 animate-in slide-in-from-top-2 duration-200">
          <CardContent className="p-6">
            <div className="flex items-center justify-between mb-6">
              <h2 className="text-lg font-semibold flex items-center gap-2">
                <Sparkles className="h-5 w-5 text-purple-600" />
                Create New Ribbon
              </h2>
              <Button variant="ghost" size="sm" onClick={handleCancel}>
                <X className="h-4 w-4 mr-1" />
                Cancel
              </Button>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div className="space-y-4">
                <div className="space-y-2">
                  <Label htmlFor="name">Name <span className="text-red-500">*</span></Label>
                  <Input
                    id="name"
                    value={formData.name}
                    onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                    className={formErrors.name ? "border-red-500" : ""}
                  />
                  {formErrors.name && <p className="text-sm text-red-500">{formErrors.name}</p>}
                </div>

                <div className="space-y-2">
                  <Label htmlFor="label">Label <span className="text-red-500">*</span></Label>
                  <Input
                    id="label"
                    value={formData.label}
                    onChange={(e) => setFormData({ ...formData, label: e.target.value })}
                    className={formErrors.label ? "border-red-500" : ""}
                  />
                  {formErrors.label && <p className="text-sm text-red-500">{formErrors.label}</p>}
                </div>

                <div className="space-y-2">
                  <Label htmlFor="description">Description</Label>
                  <Textarea
                    id="description"
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
                  <Label htmlFor="isActive" className="font-normal cursor-pointer">Active</Label>
                </div>
              </div>

              <div className="space-y-4">
                <div className="grid grid-cols-2 gap-4">
                  <div className="space-y-2">
                    <Label>Background Color</Label>
                    <div className="flex items-center gap-2">
                      <input
                        type="color"
                        value={formData.bgColor}
                        onChange={(e) => setFormData({ ...formData, bgColor: e.target.value })}
                        className="w-10 h-10 rounded cursor-pointer border-0 p-0"
                      />
                      <Input 
                        value={formData.bgColor} 
                        onChange={(e) => setFormData({ ...formData, bgColor: e.target.value })}
                        className="flex-1"
                      />
                    </div>
                  </div>
                  <div className="space-y-2">
                    <Label>Text Color</Label>
                    <div className="flex items-center gap-2">
                      <input
                        type="color"
                        value={formData.color}
                        onChange={(e) => setFormData({ ...formData, color: e.target.value })}
                        className="w-10 h-10 rounded cursor-pointer border-0 p-0"
                      />
                      <Input 
                        value={formData.color} 
                        onChange={(e) => setFormData({ ...formData, color: e.target.value })}
                        className="flex-1"
                      />
                    </div>
                  </div>
                </div>

                <div className="space-y-2">
                  <Label>Position</Label>
                  <Select
                    value={formData.position}
                    onValueChange={(value: string) => setFormData({ ...formData, position: value })}
                  >
                    <SelectTrigger>
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      {POSITION_OPTIONS.map((pos) => (
                        <SelectItem key={pos.value} value={pos.value}>{pos.name}</SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

                <div className="space-y-2">
                  <Label>Icon</Label>
                  <Select
                    value={formData.icon || "none"}
                    onValueChange={(value: string) =>
                      setFormData({ ...formData, icon: value === "none" ? "" : value })
                    }
                  >
                    <SelectTrigger>
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="none">No Icon</SelectItem>
                      {ICON_OPTIONS.map((icon) => (
                        <SelectItem key={icon.value} value={icon.value}>{icon.name}</SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

                <div className="space-y-2">
                  <Label>Priority</Label>
                  <Input
                    type="number"
                    value={formData.priority}
                    onChange={(e) => setFormData({ ...formData, priority: parseInt(e.target.value) || 0 })}
                  />
                </div>

                <div className="p-3 bg-gray-50 rounded-lg">
                  <span className="text-sm text-gray-600">Preview:</span>
                  <span 
                    className="ml-2 px-3 py-1 rounded text-sm font-medium"
                    style={{ backgroundColor: formData.bgColor, color: formData.color }}
                  >
                    {formData.label || "Ribbon"}
                  </span>
                </div>
              </div>
            </div>

            <div className="flex items-center justify-end gap-3 mt-6 pt-6 border-t">
              <Button variant="outline" onClick={handleCancel}>Cancel</Button>
              <Button className="bg-purple-600 hover:bg-purple-700" onClick={handleSubmit} disabled={isSubmitting}>
                <Save className="h-4 w-4 mr-2" />
                {isSubmitting ? "Creating..." : "Create Ribbon"}
              </Button>
            </div>
          </CardContent>
        </Card>
      )}

      {/* Ribbons Display */}
      {loading ? (
        viewMode === "list" ? (
          <SkeletonTable columns={7} rows={5} />
        ) : (
          <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            {[1, 2, 3].map((i) => (
              <Card key={i} className="h-40 animate-pulse bg-gray-100" />
            ))}
          </div>
        )
      ) : viewMode === "grid" ? (
        // Grid View
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
          {paginatedRibbons.map((ribbon) => (
            <React.Fragment key={`ribbon-${ribbon.id}`}>
              <Card className={`${selectedIds.has(ribbon.id) ? 'ring-2 ring-purple-500' : ''} ${fullEditingId === ribbon.id ? 'border-purple-500' : ''}`}>
                <CardContent className="p-6">
                  {/* Header with Checkbox and Actions */}
                  <div className="flex items-start justify-between">
                    <div className="flex items-center gap-3">
                      <Checkbox
                        checked={selectedIds.has(ribbon.id)}
                        onCheckedChange={() => toggleSelection(ribbon.id)}
                      />
                      {/* Ribbon Preview */}
                      <div
                        className="rounded px-3 py-1 text-sm font-medium"
                        style={{ backgroundColor: ribbon.bgColor, color: ribbon.color }}
                      >
                        {ribbon.label}
                      </div>
                    </div>
                    <div className="flex gap-1">
                      {/* Quick Edit Toggle */}
                      <Button 
                        variant={quickEditingId === ribbon.id ? "default" : "ghost"} 
                        size="icon" 
                        className="h-8 w-8"
                        onClick={() => quickEditingId === ribbon.id ? cancelQuickEdit() : startQuickEdit(ribbon)}
                      >
                        <Zap className={`h-4 w-4 ${quickEditingId === ribbon.id ? 'text-white' : 'text-amber-500'}`} />
                      </Button>
                      {/* Full Edit Toggle */}
                      <Button 
                        variant={fullEditingId === ribbon.id ? "default" : "ghost"} 
                        size="icon" 
                        className="h-8 w-8"
                        onClick={() => fullEditingId === ribbon.id ? cancelFullEdit() : startFullEdit(ribbon)}
                      >
                        <Settings2 className={`h-4 w-4 ${fullEditingId === ribbon.id ? 'text-white' : 'text-purple-500'}`} />
                      </Button>
                      {/* Delete */}
                      <Button 
                        variant="ghost" 
                        size="icon" 
                        className="h-8 w-8 text-red-500"
                        onClick={() => handleDelete(ribbon.id, ribbon.name)}
                      >
                        <Trash2 className="h-4 w-4" />
                      </Button>
                    </div>
                  </div>
                  
                  {/* Basic Info */}
                  <div className="mt-4 space-y-1 text-sm">
                    <p><span className="text-gray-500">Name:</span> {ribbon.name}</p>
                    <p><span className="text-gray-500">Position:</span> {ribbon.position}</p>
                    <p><span className="text-gray-500">Priority:</span> {ribbon.priority}</p>
                  </div>
                  
                  {/* Status Toggle */}
                  <div className="mt-4">
                    <button
                      onClick={async () => {
                        try {
                          await toggleRibbonActive(ribbon.id)
                          toast.success(`Ribbon ${ribbon.isActive ? "deactivated" : "activated"}`)
                          fetchRibbons()
                        } catch {
                          toast.error("Failed to update")
                        }
                      }}
                      className="cursor-pointer"
                    >
                      {ribbon.isActive ? (
                        <span className="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full flex items-center gap-1 inline-flex">
                          <Eye className="h-3 w-3" /> Active
                        </span>
                      ) : (
                        <span className="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded-full flex items-center gap-1 inline-flex">
                          <EyeOff className="h-3 w-3" /> Inactive
                        </span>
                      )}
                    </button>
                  </div>
                </CardContent>
              </Card>

              {/* Quick Edit Panel - Basic Features */}
              {quickEditingId === ribbon.id && (
                <Card className="col-span-full border-amber-200 bg-amber-50/30">
                  <CardContent className="p-4">
                    <div className="flex items-center justify-between mb-3">
                      <h4 className="font-medium text-amber-700 flex items-center gap-2">
                        <Zap className="h-4 w-4" />
                        Quick Edit: {ribbon.name}
                      </h4>
                      <div className="flex gap-2">
                        <Button size="sm" variant="outline" onClick={cancelQuickEdit}>
                          <X className="h-4 w-4 mr-1" /> Cancel
                        </Button>
                        <Button size="sm" className="bg-amber-600 hover:bg-amber-700" onClick={() => saveQuickEdit(ribbon.id)} disabled={isQuickUpdating}>
                          <Check className="h-4 w-4 mr-1" />
                          {isQuickUpdating ? "Saving..." : "Save"}
                        </Button>
                      </div>
                    </div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                      {/* Label */}
                      <div>
                        <Label className="text-xs">Label</Label>
                        <Input
                          value={quickEditData.label}
                          onChange={(e) => setQuickEditData({ ...quickEditData, label: e.target.value })}
                          className="h-9"
                        />
                      </div>
                      
                      {/* Position */}
                      <div>
                        <Label className="text-xs">Position</Label>
                        <Select
                          value={quickEditData.position}
                          onValueChange={(v: string) => setQuickEditData({ ...quickEditData, position: v })}
                        >
                          <SelectTrigger className="h-9">
                            <SelectValue />
                          </SelectTrigger>
                          <SelectContent>
                            {POSITION_OPTIONS.map((p) => (
                              <SelectItem key={p.value} value={p.value}>{p.name}</SelectItem>
                            ))}
                          </SelectContent>
                        </Select>
                      </div>
                      
                      {/* Colors */}
                      <div>
                        <Label className="text-xs">Background</Label>
                        <div className="flex items-center gap-2">
                          <input
                            type="color"
                            value={quickEditData.bgColor}
                            onChange={(e) => setQuickEditData({ ...quickEditData, bgColor: e.target.value })}
                            className="w-9 h-9 rounded cursor-pointer border-0 p-0"
                          />
                          <Input value={quickEditData.bgColor} className="h-9 flex-1" readOnly />
                        </div>
                      </div>
                      
                      <div>
                        <Label className="text-xs">Text Color</Label>
                        <div className="flex items-center gap-2">
                          <input
                            type="color"
                            value={quickEditData.color}
                            onChange={(e) => setQuickEditData({ ...quickEditData, color: e.target.value })}
                            className="w-9 h-9 rounded cursor-pointer border-0 p-0"
                          />
                          <Input value={quickEditData.color} className="h-9 flex-1" readOnly />
                        </div>
                      </div>
                    </div>
                    
                    {/* Quick Preview */}
                    <div className="mt-3 flex items-center gap-3">
                      <span className="text-sm text-gray-500">Preview:</span>
                      <span 
                        className="px-3 py-1 rounded text-sm font-medium"
                        style={{ backgroundColor: quickEditData.bgColor, color: quickEditData.color }}
                      >
                        {quickEditData.label || "Label"}
                      </span>
                      <Checkbox
                        checked={quickEditData.isActive}
                        onCheckedChange={(checked: boolean) => setQuickEditData({ ...quickEditData, isActive: checked })}
                        id={`quick-active-${ribbon.id}`}
                      />
                      <Label htmlFor={`quick-active-${ribbon.id}`} className="text-sm cursor-pointer">Active</Label>
                    </div>
                  </CardContent>
                </Card>
              )}

              {/* Full Edit Panel - All Features */}
              {fullEditingId === ribbon.id && (
                <Card className="col-span-full border-purple-200 bg-purple-50/30">
                  <CardContent className="p-6">
                    <div className="flex items-center justify-between mb-4">
                      <h4 className="font-medium text-purple-700 flex items-center gap-2">
                        <Settings2 className="h-5 w-5" />
                        Full Edit: {ribbon.name}
                      </h4>
                      <div className="flex gap-2">
                        <Button variant="outline" onClick={cancelFullEdit}>
                          <X className="h-4 w-4 mr-1" /> Cancel
                        </Button>
                        <Button className="bg-purple-600 hover:bg-purple-700" onClick={() => saveFullEdit(ribbon.id)} disabled={isFullUpdating}>
                          <Save className="h-4 w-4 mr-1" />
                          {isFullUpdating ? "Saving..." : "Save Changes"}
                        </Button>
                      </div>
                    </div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                      {/* Left Column */}
                      <div className="space-y-4">
                        <div>
                          <Label>Name <span className="text-red-500">*</span></Label>
                          <Input
                            value={fullEditFormData.name}
                            onChange={(e) => setFullEditFormData({ ...fullEditFormData, name: e.target.value })}
                          />
                        </div>
                        <div>
                          <Label>Label <span className="text-red-500">*</span></Label>
                          <Input
                            value={fullEditFormData.label}
                            onChange={(e) => setFullEditFormData({ ...fullEditFormData, label: e.target.value })}
                          />
                        </div>
                        <div>
                          <Label>Description</Label>
                          <Textarea
                            rows={3}
                            value={fullEditFormData.description}
                            onChange={(e) => setFullEditFormData({ ...fullEditFormData, description: e.target.value })}
                          />
                        </div>
                        <div className="flex items-center space-x-2">
                          <Checkbox
                            checked={fullEditFormData.isActive}
                            onCheckedChange={(checked: boolean) => setFullEditFormData({ ...fullEditFormData, isActive: checked })}
                            id={`full-active-${ribbon.id}`}
                          />
                          <Label htmlFor={`full-active-${ribbon.id}`} className="cursor-pointer">Active</Label>
                        </div>
                      </div>
                      
                      {/* Right Column */}
                      <div className="space-y-4">
                        <div className="grid grid-cols-2 gap-4">
                          <div>
                            <Label>Background Color</Label>
                            <div className="flex items-center gap-2">
                              <input
                                type="color"
                                value={fullEditFormData.bgColor}
                                onChange={(e) => setFullEditFormData({ ...fullEditFormData, bgColor: e.target.value })}
                                className="w-10 h-10 rounded cursor-pointer border-0 p-0"
                              />
                              <Input value={fullEditFormData.bgColor} className="flex-1" />
                            </div>
                          </div>
                          <div>
                            <Label>Text Color</Label>
                            <div className="flex items-center gap-2">
                              <input
                                type="color"
                                value={fullEditFormData.color}
                                onChange={(e) => setFullEditFormData({ ...fullEditFormData, color: e.target.value })}
                                className="w-10 h-10 rounded cursor-pointer border-0 p-0"
                              />
                              <Input value={fullEditFormData.color} className="flex-1" />
                            </div>
                          </div>
                        </div>
                        
                        <div className="grid grid-cols-2 gap-4">
                          <div>
                            <Label>Position</Label>
                            <Select
                              value={fullEditFormData.position}
                              onValueChange={(v: string) => setFullEditFormData({ ...fullEditFormData, position: v })}
                            >
                              <SelectTrigger><SelectValue /></SelectTrigger>
                              <SelectContent>
                                {POSITION_OPTIONS.map((p) => (
                                  <SelectItem key={p.value} value={p.value}>{p.name}</SelectItem>
                                ))}
                              </SelectContent>
                            </Select>
                          </div>
                          <div>
                            <Label>Priority</Label>
                            <Input
                              type="number"
                              value={fullEditFormData.priority}
                              onChange={(e) => setFullEditFormData({ ...fullEditFormData, priority: parseInt(e.target.value) || 0 })}
                            />
                          </div>
                        </div>
                        
                        <div>
                          <Label>Icon</Label>
                          <Select
                            value={fullEditFormData.icon || "none"}
                            onValueChange={(v: string) => setFullEditFormData({ ...fullEditFormData, icon: v === "none" ? "" : v })}
                          >
                            <SelectTrigger><SelectValue /></SelectTrigger>
                            <SelectContent>
                              <SelectItem value="none">No Icon</SelectItem>
                              {ICON_OPTIONS.map((i) => (
                                <SelectItem key={i.value} value={i.value}>{i.name}</SelectItem>
                              ))}
                            </SelectContent>
                          </Select>
                        </div>
                        
                        {/* Preview */}
                        <div className="p-3 bg-white rounded-lg border">
                          <span className="text-sm text-gray-500">Preview:</span>
                          <span 
                            className="ml-2 px-4 py-1 rounded text-sm font-medium shadow-sm"
                            style={{ backgroundColor: fullEditFormData.bgColor, color: fullEditFormData.color }}
                          >
                            {fullEditFormData.label || "Label"}
                          </span>
                        </div>
                      </div>
                    </div>
                  </CardContent>
                </Card>
              )}
            </React.Fragment>
          ))}
        </div>
      ) : (
        // List View
        <Card>
          <CardContent className="p-0">
            <Table>
              <TableHeader>
                <TableRow className="bg-gray-50">
                  <TableHead className="w-12"><Checkbox checked={selectAll} onCheckedChange={toggleSelectAll} /></TableHead>
                  <TableHead>Preview</TableHead>
                  <TableHead className="w-[200px]">Actions</TableHead>
                  <TableHead>Name</TableHead>
                  <TableHead>Position</TableHead>
                  <TableHead>Priority</TableHead>
                  <TableHead>Status</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {paginatedRibbons.map((ribbon) => (
                  <React.Fragment key={`ribbon-row-${ribbon.id}`}>
                    <TableRow className={fullEditingId === ribbon.id ? 'bg-purple-50/50' : ''}>
                      <TableCell><Checkbox checked={selectedIds.has(ribbon.id)} onCheckedChange={() => toggleSelection(ribbon.id)} /></TableCell>
                      <TableCell>
                        <div 
                          className="rounded px-3 py-1 text-sm font-medium inline-block"
                          style={{ backgroundColor: ribbon.bgColor, color: ribbon.color }}
                        >
                          {ribbon.label}
                        </div>
                      </TableCell>
                      <TableCell>
                        <div className="flex gap-1">
                          <Button 
                            size="sm" 
                            variant={quickEditingId === ribbon.id ? "default" : "outline"} 
                            className={`h-7 px-2 ${quickEditingId !== ribbon.id && 'text-amber-600 border-amber-200'}`}
                            onClick={() => quickEditingId === ribbon.id ? cancelQuickEdit() : startQuickEdit(ribbon)}
                          >
                            <Zap className="h-3.5 w-3.5 mr-1" /> 
                            {quickEditingId === ribbon.id ? 'Close' : 'Quick'}
                          </Button>
                          <Button 
                            size="sm" 
                            variant={fullEditingId === ribbon.id ? "default" : "outline"} 
                            className={`h-7 px-2 ${fullEditingId !== ribbon.id && 'text-purple-600 border-purple-200'}`}
                            onClick={() => fullEditingId === ribbon.id ? cancelFullEdit() : startFullEdit(ribbon)}
                          >
                            <Settings2 className="h-3.5 w-3.5 mr-1" /> 
                            {fullEditingId === ribbon.id ? 'Close' : 'Full Edit'}
                          </Button>
                          <Button variant="ghost" size="sm" className="h-7 px-2 text-red-500" onClick={() => handleDelete(ribbon.id, ribbon.name)}>
                            <Trash2 className="h-3.5 w-3.5" />
                          </Button>
                        </div>
                      </TableCell>
                      <TableCell>{ribbon.name}</TableCell>
                      <TableCell>{ribbon.position}</TableCell>
                      <TableCell>{ribbon.priority}</TableCell>
                      <TableCell>
                        <button onClick={async () => { await toggleRibbonActive(ribbon.id); fetchRibbons() }} className="cursor-pointer">
                          {ribbon.isActive ? (
                            <span className="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full"><Eye className="h-3 w-3 inline mr-1" /> Active</span>
                          ) : (
                            <span className="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded-full"><EyeOff className="h-3 w-3 inline mr-1" /> Inactive</span>
                          )}
                        </button>
                      </TableCell>
                    </TableRow>
                    
                    {/* Quick Edit Row */}
                    {quickEditingId === ribbon.id && (
                      <TableRow className="bg-amber-50/30">
                        <TableCell colSpan={7} className="p-4">
                          <div className="flex items-center gap-4 flex-wrap">
                            <span className="font-medium text-amber-700">Quick Edit:</span>
                            <div className="w-32">
                              <Label className="text-xs">Label</Label>
                              <Input value={quickEditData.label} onChange={(e) => setQuickEditData({...quickEditData, label: e.target.value})} className="h-8" />
                            </div>
                            <div className="w-32">
                              <Label className="text-xs">Position</Label>
                              <Select value={quickEditData.position} onValueChange={(v: string) => setQuickEditData({...quickEditData, position: v})}>
                                <SelectTrigger className="h-8"><SelectValue /></SelectTrigger>
                                <SelectContent>
                                  {POSITION_OPTIONS.map((p) => <SelectItem key={p.value} value={p.value}>{p.name}</SelectItem>)}
                                </SelectContent>
                              </Select>
                            </div>
                            <div className="flex items-center gap-2">
                              <div>
                                <Label className="text-xs">Bg Color</Label>
                                <input type="color" value={quickEditData.bgColor} onChange={(e) => setQuickEditData({...quickEditData, bgColor: e.target.value})} className="w-8 h-8 rounded cursor-pointer block" />
                              </div>
                              <div>
                                <Label className="text-xs">Text</Label>
                                <input type="color" value={quickEditData.color} onChange={(e) => setQuickEditData({...quickEditData, color: e.target.value})} className="w-8 h-8 rounded cursor-pointer block" />
                              </div>
                            </div>
                            <div className="flex items-center gap-2 mt-5">
                              <Checkbox checked={quickEditData.isActive} onCheckedChange={(c: boolean) => setQuickEditData({...quickEditData, isActive: c})} id={`q-active-${ribbon.id}`} />
                              <Label htmlFor={`q-active-${ribbon.id}`} className="text-sm">Active</Label>
                            </div>
                            <div className="flex gap-2 mt-5">
                              <Button size="sm" variant="outline" onClick={cancelQuickEdit}>Cancel</Button>
                              <Button size="sm" className="bg-amber-600" onClick={() => saveQuickEdit(ribbon.id)} disabled={isQuickUpdating}>
                                {isQuickUpdating ? 'Saving...' : 'Save'}
                              </Button>
                            </div>
                          </div>
                        </TableCell>
                      </TableRow>
                    )}

                    {/* Full Edit Row */}
                    {fullEditingId === ribbon.id && (
                      <TableRow className="bg-purple-50/30">
                        <TableCell colSpan={7} className="p-6">
                          <div className="space-y-4">
                            <div className="flex items-center justify-between">
                              <span className="font-medium text-purple-700">Full Edit: {ribbon.name}</span>
                              <div className="flex gap-2">
                                <Button size="sm" variant="outline" onClick={cancelFullEdit}>Cancel</Button>
                                <Button size="sm" className="bg-purple-600" onClick={() => saveFullEdit(ribbon.id)} disabled={isFullUpdating}>
                                  {isFullUpdating ? 'Saving...' : 'Save Changes'}
                                </Button>
                              </div>
                            </div>
                            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                              <div>
                                <Label className="text-xs">Name</Label>
                                <Input value={fullEditFormData.name} onChange={(e) => setFullEditFormData({...fullEditFormData, name: e.target.value})} className="h-9" />
                              </div>
                              <div>
                                <Label className="text-xs">Label</Label>
                                <Input value={fullEditFormData.label} onChange={(e) => setFullEditFormData({...fullEditFormData, label: e.target.value})} className="h-9" />
                              </div>
                              <div>
                                <Label className="text-xs">Position</Label>
                                <Select value={fullEditFormData.position} onValueChange={(v: string) => setFullEditFormData({...fullEditFormData, position: v})}>
                                  <SelectTrigger className="h-9"><SelectValue /></SelectTrigger>
                                  <SelectContent>
                                    {POSITION_OPTIONS.map((p) => <SelectItem key={p.value} value={p.value}>{p.name}</SelectItem>)}
                                  </SelectContent>
                                </Select>
                              </div>
                              <div>
                                <Label className="text-xs">Priority</Label>
                                <Input type="number" value={fullEditFormData.priority} onChange={(e) => setFullEditFormData({...fullEditFormData, priority: parseInt(e.target.value) || 0})} className="h-9" />
                              </div>
                            </div>
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                              <div>
                                <Label className="text-xs">Description</Label>
                                <Textarea value={fullEditFormData.description} onChange={(e) => setFullEditFormData({...fullEditFormData, description: e.target.value})} rows={2} />
                              </div>
                              <div>
                                <Label className="text-xs">Icon</Label>
                                <Select value={fullEditFormData.icon || "none"} onValueChange={(v: string) => setFullEditFormData({...fullEditFormData, icon: v === "none" ? "" : v})}>
                                  <SelectTrigger className="h-9"><SelectValue /></SelectTrigger>
                                  <SelectContent>
                                    <SelectItem value="none">No Icon</SelectItem>
                                    {ICON_OPTIONS.map((i) => <SelectItem key={i.value} value={i.value}>{i.name}</SelectItem>)}
                                  </SelectContent>
                                </Select>
                              </div>
                              <div className="flex items-center gap-4">
                                <div>
                                  <Label className="text-xs">Bg Color</Label>
                                  <input type="color" value={fullEditFormData.bgColor} onChange={(e) => setFullEditFormData({...fullEditFormData, bgColor: e.target.value})} className="w-10 h-10 rounded cursor-pointer block" />
                                </div>
                                <div>
                                  <Label className="text-xs">Text Color</Label>
                                  <input type="color" value={fullEditFormData.color} onChange={(e) => setFullEditFormData({...fullEditFormData, color: e.target.value})} className="w-10 h-10 rounded cursor-pointer block" />
                                </div>
                                <div className="mt-5">
                                  <span 
                                    className="px-3 py-1 rounded text-sm font-medium"
                                    style={{ backgroundColor: fullEditFormData.bgColor, color: fullEditFormData.color }}
                                  >
                                    {fullEditFormData.label}
                                  </span>
                                </div>
                              </div>
                            </div>
                          </div>
                        </TableCell>
                      </TableRow>
                    )}
                  </React.Fragment>
                ))}
              </TableBody>
            </Table>
            <DataTablePagination
              currentPage={currentPage}
              totalPages={totalPages}
              totalItems={filteredRibbons.length}
              pageSize={pageSize}
              onPageChange={setCurrentPage}
              onPageSizeChange={setPageSize}
            />
          </CardContent>
        </Card>
      )}

      {paginatedRibbons.length === 0 && !loading && (
        <Card className="p-12 text-center">
          <Sparkles className="h-12 w-12 text-gray-300 mx-auto mb-4" />
          <p className="text-muted-foreground">No ribbons found. {searchQuery && "Try adjusting your search."}</p>
        </Card>
      )}
    </div>
  )
}
