"use client"

import { useState, useEffect } from "react"
import Link from "next/link"
import { useRouter } from "next/navigation"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
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
  Save,
  X,
  FolderTree,
  FileText,
  Image,
  Tags,
  ArrowLeft,
} from "lucide-react"
import { createCategory, getCategories, type Category } from "@/lib/api/categories"

export default function AddCategoryPage() {
  const router = useRouter()
  const [isSubmitting, setIsSubmitting] = useState(false)
  const [existingCategories, setExistingCategories] = useState<Category[]>([])
  const [isLoading, setIsLoading] = useState(true)

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

  const [errors, setErrors] = useState<Record<string, string>>({})

  useEffect(() => {
    fetchCategories()
  }, [])

  const fetchCategories = async () => {
    try {
      const categories = await getCategories()
      setExistingCategories(categories)
    } catch (error) {
      console.error("Failed to fetch categories:", error)
    } finally {
      setIsLoading(false)
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
    const newErrors: Record<string, string> = {}

    if (!formData.name.trim()) {
      newErrors.name = "Category name is required"
    }

    if (!formData.slug.trim()) {
      newErrors.slug = "Slug is required"
    } else if (!/^[a-z0-9-]+$/.test(formData.slug)) {
      newErrors.slug = "Slug can only contain lowercase letters, numbers, and hyphens"
    }

    setErrors(newErrors)
    return Object.keys(newErrors).length === 0
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
      router.push("/admin/categories")
    } catch (error) {
      console.error("Failed to create category:", error)
      if (error instanceof Error) {
        setErrors({ submit: error.message })
      }
    } finally {
      setIsSubmitting(false)
    }
  }

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <div className="sticky top-0 z-50 bg-white border-b shadow-sm">
        <div className="px-6 py-4">
          <div className="flex items-center justify-between max-w-5xl mx-auto">
            <div className="flex items-center gap-4">
              <Button variant="ghost" size="icon" asChild>
                <Link href="/admin/categories">
                  <ArrowLeft className="h-5 w-5" />
                </Link>
              </Button>
              <div>
                <h1 className="text-xl font-semibold">Add Category</h1>
                <p className="text-sm text-gray-500">
                  Create a new category to organize your products
                </p>
              </div>
            </div>
            <Button variant="ghost" size="icon" asChild>
              <Link href="/admin/categories">
                <X className="h-5 w-5" />
              </Link>
            </Button>
          </div>
        </div>
      </div>

      {/* Main Content */}
      <div className="max-w-5xl mx-auto px-6 py-8 space-y-6">
        {errors.submit && (
          <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            {errors.submit}
          </div>
        )}

        {/* Basic Info Section */}
        <Card className="border-l-4 border-l-blue-500">
          <CardHeader className="bg-gray-50/50 border-b">
            <div className="flex items-center gap-3">
              <div className="p-2 bg-blue-100 rounded-lg">
                <FileText className="h-5 w-5 text-blue-600" />
              </div>
              <CardTitle className="text-base font-semibold uppercase tracking-wide text-gray-700">
                Basic Information
              </CardTitle>
            </div>
          </CardHeader>
          <CardContent className="p-6 space-y-6">
            <div className="grid grid-cols-2 gap-6">
              <div className="space-y-2">
                <Label htmlFor="name">
                  Category Name <span className="text-red-500">*</span>
                </Label>
                <Input
                  id="name"
                  placeholder="Enter category name..."
                  value={formData.name}
                  onChange={(e) => handleNameChange(e.target.value)}
                  className={errors.name ? "border-red-500" : ""}
                />
                {errors.name && (
                  <p className="text-sm text-red-500">{errors.name}</p>
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
                  onChange={(e) =>
                    setFormData({ ...formData, slug: e.target.value })
                  }
                  className={errors.slug ? "border-red-500" : ""}
                />
                {errors.slug && (
                  <p className="text-sm text-red-500">{errors.slug}</p>
                )}
                <p className="text-xs text-gray-500">
                  Used in URLs. Only lowercase letters, numbers, and hyphens.
                </p>
              </div>
            </div>

            <div className="space-y-2">
              <Label htmlFor="description">Description</Label>
              <Textarea
                id="description"
                placeholder="Enter category description..."
                rows={4}
                value={formData.description}
                onChange={(e) =>
                  setFormData({ ...formData, description: e.target.value })
                }
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
          </CardContent>
        </Card>

        {/* Parent Category Section */}
        <Card className="border-l-4 border-l-amber-500">
          <CardHeader className="bg-gray-50/50 border-b">
            <div className="flex items-center gap-3">
              <div className="p-2 bg-amber-100 rounded-lg">
                <FolderTree className="h-5 w-5 text-amber-600" />
              </div>
              <CardTitle className="text-base font-semibold uppercase tracking-wide text-gray-700">
                Hierarchy
              </CardTitle>
            </div>
          </CardHeader>
          <CardContent className="p-6">
            <div className="space-y-2">
              <Label htmlFor="parentId">Parent Category</Label>
              <Select
                value={formData.parentId || "none"}
                onValueChange={(value: string) =>
                  setFormData({ ...formData, parentId: value === "none" ? "" : value })
                }
                disabled={isLoading}
              >
                <SelectTrigger>
                  <SelectValue placeholder="Select parent category (optional)" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="none">None (Top Level)</SelectItem>
                  {existingCategories.map((category) => (
                    <SelectItem key={category.id} value={category.id}>
                      {"  ".repeat(category.depth)}{category.name}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
              <p className="text-xs text-gray-500">
                Select a parent to create a subcategory, or leave as None for a top-level category.
              </p>
            </div>
          </CardContent>
        </Card>

        {/* SEO Section */}
        <Card className="border-l-4 border-l-green-500">
          <CardHeader className="bg-gray-50/50 border-b">
            <div className="flex items-center gap-3">
              <div className="p-2 bg-green-100 rounded-lg">
                <Tags className="h-5 w-5 text-green-600" />
              </div>
              <CardTitle className="text-base font-semibold uppercase tracking-wide text-gray-700">
                SEO Settings
              </CardTitle>
            </div>
          </CardHeader>
          <CardContent className="p-6 space-y-6">
            <div className="space-y-2">
              <Label htmlFor="metaTitle">Meta Title</Label>
              <Input
                id="metaTitle"
                placeholder="SEO title (optional)"
                value={formData.metaTitle}
                onChange={(e) =>
                  setFormData({ ...formData, metaTitle: e.target.value })
                }
              />
              <p className="text-xs text-gray-500">
                Title shown in search engine results. Defaults to category name if empty.
              </p>
            </div>
            <div className="space-y-2">
              <Label htmlFor="metaDescription">Meta Description</Label>
              <Textarea
                id="metaDescription"
                placeholder="SEO description (optional)"
                rows={3}
                value={formData.metaDescription}
                onChange={(e) =>
                  setFormData({ ...formData, metaDescription: e.target.value })
                }
              />
              <p className="text-xs text-gray-500">
                Brief description for search engines.
              </p>
            </div>
          </CardContent>
        </Card>

        {/* Image Section */}
        <Card className="border-l-4 border-l-purple-500">
          <CardHeader className="bg-gray-50/50 border-b">
            <div className="flex items-center gap-3">
              <div className="p-2 bg-purple-100 rounded-lg">
                <Image className="h-5 w-5 text-purple-600" />
              </div>
              <CardTitle className="text-base font-semibold uppercase tracking-wide text-gray-700">
                Category Image
              </CardTitle>
            </div>
          </CardHeader>
          <CardContent className="p-6">
            <div className="space-y-2">
              <Label htmlFor="image">Image URL</Label>
              <Input
                id="image"
                placeholder="https://..."
                value={formData.image}
                onChange={(e) =>
                  setFormData({ ...formData, image: e.target.value })
                }
              />
              <p className="text-xs text-gray-500">
                URL to the category image (optional).
              </p>
            </div>
          </CardContent>
        </Card>

        {/* Sort Order Section */}
        <Card className="border-l-4 border-l-teal-500">
          <CardHeader className="bg-gray-50/50 border-b">
            <div className="flex items-center gap-3">
              <div className="p-2 bg-teal-100 rounded-lg">
                <FolderTree className="h-5 w-5 text-teal-600" />
              </div>
              <CardTitle className="text-base font-semibold uppercase tracking-wide text-gray-700">
                Display Settings
              </CardTitle>
            </div>
          </CardHeader>
          <CardContent className="p-6">
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
              <p className="text-xs text-gray-500">
                Lower numbers appear first. Categories are sorted by this value within their parent.
              </p>
            </div>
          </CardContent>
        </Card>

        {/* Spacer for footer */}
        <div className="h-24" />
      </div>

      {/* Sticky Footer */}
      <div className="fixed bottom-0 left-0 right-0 bg-white border-t shadow-lg">
        <div className="max-w-5xl mx-auto px-6 py-4">
          <div className="flex items-center justify-end gap-3">
            <Button variant="outline" asChild>
              <Link href="/admin/categories">Cancel</Link>
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
        </div>
      </div>
    </div>
  )
}
