"use client"

import { useEffect, useState } from "react"
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
import { Plus, Edit2, Trash2, FolderTree, X, Save, ChevronDown, ChevronUp } from "lucide-react"
import { getCategories, deleteCategory, createCategory, type Category } from "@/lib/api/categories"

export default function AdminCategoriesPage() {
  const [categories, setCategories] = useState<Category[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [showAddForm, setShowAddForm] = useState(false)
  const [isSubmitting, setIsSubmitting] = useState(false)

  // Form state
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

  useEffect(() => {
    fetchCategories()
  }, [])

  const fetchCategories = async () => {
    try {
      setLoading(true)
      const data = await getCategories()
      setCategories(data)
    } catch (err) {
      console.error("Failed to fetch categories:", err)
      setError("Failed to load categories")
    } finally {
      setLoading(false)
    }
  }

  const handleDelete = async (id: string) => {
    if (!confirm("Are you sure you want to delete this category?")) {
      return
    }
    try {
      await deleteCategory(id)
      await fetchCategories()
    } catch (err) {
      console.error("Failed to delete category:", err)
      alert(err instanceof Error ? err.message : "Failed to delete category")
    }
  }

  // Auto-generate slug from name
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

  const validateForm = () => {
    const errors: Record<string, string> = {}

    if (!formData.name.trim()) {
      errors.name = "Category name is required"
    }

    if (!formData.slug.trim()) {
      errors.slug = "Slug is required"
    } else if (!/^[a-z0-9-]+$/.test(formData.slug)) {
      errors.slug = "Slug can only contain lowercase letters, numbers, and hyphens"
    }

    setFormErrors(errors)
    return Object.keys(errors).length === 0
  }

  const handleSubmit = async () => {
    if (!validateForm()) return

    setIsSubmitting(true)
    try {
      const data: {
        name: string
        slug: string
        description?: string
        parentId?: string
        metaTitle?: string
        metaDescription?: string
        image?: string
        isActive: boolean
        sortOrder: number
      } = {
        name: formData.name,
        slug: formData.slug,
        isActive: formData.isActive,
        sortOrder: formData.sortOrder,
      }

      if (formData.description) {
        data.description = formData.description
      }

      if (formData.parentId && formData.parentId !== "none") {
        data.parentId = formData.parentId
      }

      if (formData.metaTitle) {
        data.metaTitle = formData.metaTitle
      }

      if (formData.metaDescription) {
        data.metaDescription = formData.metaDescription
      }

      if (formData.image) {
        data.image = formData.image
      }

      await createCategory(data)
      
      // Reset form and refresh
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
      if (error instanceof Error) {
        setFormErrors({ submit: error.message })
      }
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
      {/* Header */}
      <div className="relative overflow-hidden rounded-xl bg-gradient-to-r from-violet-600 via-blue-600 to-blue-500 px-8 py-8 text-white">
        <div className="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 className="text-3xl font-bold">Categories</h1>
            <p className="mt-2 text-blue-100">
              Organize your products into categories
            </p>
          </div>
          <Button 
            className="bg-white text-blue-600 hover:bg-blue-50"
            onClick={() => setShowAddForm(!showAddForm)}
          >
            {showAddForm ? (
              <>
                <ChevronUp className="mr-2 h-4 w-4" />
                Hide Form
              </>
            ) : (
              <>
                <Plus className="mr-2 h-4 w-4" />
                Add Category
              </>
            )}
          </Button>
        </div>
        {/* Decorative circles */}
        <div className="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10" />
        <div className="absolute -bottom-10 -right-10 h-32 w-32 rounded-full bg-white/5" />
      </div>

      {/* Add Category Form - Inline */}
      {showAddForm && (
        <Card className="border-l-4 border-l-blue-500 animate-in slide-in-from-top-2 duration-200">
          <CardContent className="p-6">
            <div className="flex items-center justify-between mb-6">
              <h2 className="text-lg font-semibold flex items-center gap-2">
                <FolderTree className="h-5 w-5 text-blue-600" />
                Add New Category
              </h2>
              <Button variant="ghost" size="sm" onClick={handleCancel}>
                <X className="h-4 w-4 mr-1" />
                Cancel
              </Button>
            </div>

            {formErrors.submit && (
              <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
                {formErrors.submit}
              </div>
            )}

            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              {/* Basic Info */}
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
                  {formErrors.name && (
                    <p className="text-sm text-red-500">{formErrors.name}</p>
                  )}
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
                  {formErrors.slug && (
                    <p className="text-sm text-red-500">{formErrors.slug}</p>
                  )}
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

              {/* Parent & SEO */}
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
                        setFormData({
                          ...formData,
                          sortOrder: parseInt(e.target.value) || 0,
                        })
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

            {/* Form Actions */}
            <div className="flex items-center justify-end gap-3 mt-6 pt-6 border-t">
              <Button variant="outline" onClick={handleCancel}>
                Cancel
              </Button>
              <Button
                className="bg-blue-600 hover:bg-blue-700"
                onClick={handleSubmit}
                disabled={isSubmitting}
              >
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
          <Table>
            <TableHeader>
              <TableRow className="bg-gray-50">
                <TableHead className="font-semibold">Name</TableHead>
                <TableHead className="font-semibold">Slug</TableHead>
                <TableHead className="font-semibold">Level</TableHead>
                <TableHead className="font-semibold">Status</TableHead>
                <TableHead className="font-semibold">Created</TableHead>
                <TableHead className="text-right font-semibold">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {loading ? (
                <TableRow>
                  <TableCell colSpan={6} className="h-32 text-center">
                    <div className="flex justify-center">
                      <div className="h-8 w-8 animate-spin rounded-full border-4 border-blue-600 border-t-transparent" />
                    </div>
                  </TableCell>
                </TableRow>
              ) : error ? (
                <TableRow>
                  <TableCell
                    colSpan={6}
                    className="h-32 text-center text-muted-foreground"
                  >
                    <div className="space-y-2">
                      <p className="text-red-500">{error}</p>
                      <Button variant="outline" onClick={fetchCategories}>
                        Retry
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>
              ) : categories.length === 0 ? (
                <TableRow>
                  <TableCell
                    colSpan={6}
                    className="h-32 text-center text-muted-foreground"
                  >
                    <div className="flex flex-col items-center gap-2">
                      <FolderTree className="h-8 w-8 text-gray-400" />
                      <p>No categories yet. Click &quot;Add Category&quot; to get started.</p>
                    </div>
                  </TableCell>
                </TableRow>
              ) : (
                categories.map((category) => (
                  <TableRow key={category.id} className="hover:bg-gray-50">
                    <TableCell>
                      <div className="flex items-center gap-2">
                        <span className="text-gray-400">{"│  ".repeat(category.depth)}</span>
                        <span className="font-medium">{category.name}</span>
                      </div>
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
                      {category.isActive ? (
                        <span className="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full">
                          Active
                        </span>
                      ) : (
                        <span className="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded-full">
                          Inactive
                        </span>
                      )}
                    </TableCell>
                    <TableCell className="text-sm text-gray-500">
                      {formatDate(category.createdAt)}
                    </TableCell>
                    <TableCell className="text-right">
                      <div className="flex items-center justify-end gap-2">
                        <Button variant="ghost" size="sm" asChild>
                          <Link href={`/admin/categories/${category.id}/edit`}>
                            <Edit2 className="h-4 w-4" />
                          </Link>
                        </Button>
                        <Button
                          variant="ghost"
                          size="sm"
                          className="text-red-500 hover:text-red-700"
                          onClick={() => handleDelete(category.id)}
                        >
                          <Trash2 className="h-4 w-4" />
                        </Button>
                      </div>
                    </TableCell>
                  </TableRow>
                ))
              )}
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  )
}
