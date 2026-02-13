"use client"

import { useState, useEffect } from "react"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Switch } from "@/components/ui/switch"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Badge } from "@/components/ui/badge"
import { Separator } from "@/components/ui/separator"
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog"

import {
  Plus,
  Copy,
  Check,
  Trash2,
  Edit,
  Sparkles,
  Code,
  Eye,
  EyeOff,
} from "lucide-react"

interface DontMissSection {
  id: string
  name: string
  shortcode: string
  title: string
  subtitle: string | null
  layout: "mixed" | "blogs_only" | "products_only"
  blogCount: number
  productCount: number
  blogCategoryId: string | null
  productCategoryId: string | null
  showViewAll: boolean
  sortBy: "latest" | "popular" | "featured"
  backgroundColor: string | null
  textColor: string | null
  isActive: boolean
  sortOrder: number
}

type FormData = {
  name: string
  title: string
  subtitle: string
  layout: "mixed" | "blogs_only" | "products_only"
  blogCount: number
  productCount: number
  blogCategoryId: string
  productCategoryId: string
  showViewAll: boolean
  sortBy: "latest" | "popular" | "featured"
  backgroundColor: string
  textColor: string
  isActive: boolean
}

const defaultFormData: FormData = {
  name: "",
  title: "Don't Miss",
  subtitle: "",
  layout: "mixed",
  blogCount: 3,
  productCount: 2,
  blogCategoryId: "",
  productCategoryId: "",
  showViewAll: true,
  sortBy: "latest",
  backgroundColor: "",
  textColor: "",
  isActive: true,
}

export default function DontMissSettingsPage() {
  const [sections, setSections] = useState<DontMissSection[]>([])
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const [copiedCode, setCopiedCode] = useState<string | null>(null)
  const [editingSection, setEditingSection] = useState<DontMissSection | null>(null)
  const [formData, setFormData] = useState(defaultFormData)
  const [isDialogOpen, setIsDialogOpen] = useState(false)
  const [categories] = useState([
    { id: "all", name: "All Categories" },
    { id: "tech", name: "Technology" },
    { id: "lifestyle", name: "Lifestyle" },
    { id: "reviews", name: "Reviews" },
  ])

  useEffect(() => {
    fetchSections()
  }, [])

  const fetchSections = async () => {
    try {
      const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/dont-miss?includeInactive=true`)
      if (response.ok) {
        const data = await response.json()
        setSections(data)
      }
    } catch (error) {
      console.error("Failed to fetch sections:", error)
    } finally {
      setLoading(false)
    }
  }

  const handleSave = async () => {
    setSaving(true)
    try {
      const url = editingSection
        ? `${process.env.NEXT_PUBLIC_API_URL}/dont-miss/${editingSection.id}`
        : `${process.env.NEXT_PUBLIC_API_URL}/dont-miss`
      
      const method = editingSection ? "PUT" : "POST"
      
      // Convert "all" back to empty string for API
      const dataToSend = {
        ...formData,
        blogCategoryId: formData.blogCategoryId === "all" ? "" : formData.blogCategoryId,
        productCategoryId: formData.productCategoryId === "all" ? "" : formData.productCategoryId,
      }

      const response = await fetch(url, {
        method,
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(dataToSend),
      })

      if (response.ok) {
        await fetchSections()
        setIsDialogOpen(false)
        setEditingSection(null)
        setFormData(defaultFormData)
      }
    } catch (error) {
      console.error("Failed to save section:", error)
    } finally {
      setSaving(false)
    }
  }

  const handleDelete = async (id: string) => {
    try {
      const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/dont-miss/${id}`, {
        method: "DELETE",
      })
      if (response.ok) {
        await fetchSections()
      }
    } catch (error) {
      console.error("Failed to delete section:", error)
    }
  }

  const handleDuplicate = async (id: string) => {
    try {
      const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/dont-miss/${id}/duplicate`, {
        method: "PUT",
      })
      if (response.ok) {
        await fetchSections()
      }
    } catch (error) {
      console.error("Failed to duplicate section:", error)
    }
  }

  const handleToggleActive = async (section: DontMissSection) => {
    try {
      const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/dont-miss/${section.id}`, {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ isActive: !section.isActive }),
      })
      if (response.ok) {
        await fetchSections()
      }
    } catch (error) {
      console.error("Failed to toggle section:", error)
    }
  }

  const copyShortcode = (shortcode: string) => {
    navigator.clipboard.writeText(`[${shortcode}]`)
    setCopiedCode(shortcode)
    setTimeout(() => setCopiedCode(null), 2000)
  }

  const openEditDialog = (section: DontMissSection) => {
    setEditingSection(section)
    setFormData({
      name: section.name,
      title: section.title,
      subtitle: section.subtitle || "",
      layout: section.layout,
      blogCount: section.blogCount,
      productCount: section.productCount,
      blogCategoryId: section.blogCategoryId || "all",
      productCategoryId: section.productCategoryId || "all",
      showViewAll: section.showViewAll,
      sortBy: section.sortBy,
      backgroundColor: section.backgroundColor || "",
      textColor: section.textColor || "",
      isActive: section.isActive,
    })
    setIsDialogOpen(true)
  }

  const openCreateDialog = () => {
    setEditingSection(null)
    setFormData(defaultFormData)
    setIsDialogOpen(true)
  }

  if (loading) {
    return (
      <div className="space-y-6">
        <div className="h-8 w-48 bg-muted rounded animate-pulse" />
        <div className="grid gap-4">
          {[1, 2, 3].map((i) => (
            <div key={i} className="h-32 bg-muted rounded-lg animate-pulse" />
          ))}
        </div>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Don't Miss Sections</h1>
          <p className="text-muted-foreground">
            Create and manage featured content sections with auto-generated shortcodes
          </p>
        </div>
        <Button onClick={openCreateDialog}>
          <Plus className="mr-2 h-4 w-4" />
          Add New Section
        </Button>
      </div>

      {/* Sections List */}
      <div className="grid gap-4">
        {sections.length === 0 ? (
          <Card>
            <CardContent className="py-12 text-center">
              <Sparkles className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
              <h3 className="text-lg font-medium mb-2">No sections yet</h3>
              <p className="text-muted-foreground mb-4">
                Create your first &quot;Don&apos;t Miss&quot; section to feature content on your homepage
              </p>
              <Button onClick={openCreateDialog}>
                <Plus className="mr-2 h-4 w-4" />
                Create Section
              </Button>
            </CardContent>
          </Card>
        ) : (
          sections.map((section) => (
            <Card key={section.id} className={!section.isActive ? "opacity-60" : undefined}>
              <CardContent className="p-6">
                <div className="flex items-start justify-between gap-4">
                  <div className="flex-1 min-w-0">
                    <div className="flex items-center gap-3 mb-2">
                      <h3 className="text-lg font-semibold">{section.name}</h3>
                      <Badge variant={section.isActive ? "default" : "secondary"}>
                        {section.isActive ? "Active" : "Inactive"}
                      </Badge>
                      {section.layout === "mixed" && <Badge variant="outline">Mixed</Badge>}
                      {section.layout === "blogs_only" && <Badge variant="outline">Blogs Only</Badge>}
                      {section.layout === "products_only" && <Badge variant="outline">Products Only</Badge>}
                    </div>
                    
                    <p className="text-muted-foreground text-sm mb-3">
                      {section.title} — {section.subtitle || "No subtitle"}
                    </p>

                    <div className="flex flex-wrap items-center gap-4 text-sm text-muted-foreground">
                      <div className="flex items-center gap-2">
                        <Code className="h-4 w-4" />
                        <code className="bg-muted px-2 py-0.5 rounded text-xs">
                          [{section.shortcode}]
                        </code>
                        <Button
                          variant="ghost"
                          size="icon"
                          className="h-6 w-6"
                          onClick={() => copyShortcode(section.shortcode)}
                        >
                          {copiedCode === section.shortcode ? (
                            <Check className="h-3 w-3 text-green-500" />
                          ) : (
                            <Copy className="h-3 w-3" />
                          )}
                        </Button>
                      </div>
                      
                      {section.layout !== "products_only" && (
                        <span>{section.blogCount} blogs</span>
                      )}
                      {section.layout !== "blogs_only" && (
                        <span>{section.productCount} products</span>
                      )}
                      {section.blogCategoryId && (
                        <Badge variant="outline" className="text-xs">Blog Category</Badge>
                      )}
                      {section.productCategoryId && (
                        <Badge variant="outline" className="text-xs">Product Category</Badge>
                      )}
                    </div>
                  </div>

                  <div className="flex items-center gap-1">
                    <Button
                      variant="ghost"
                      size="icon"
                      onClick={() => handleToggleActive(section)}
                      title={section.isActive ? "Deactivate" : "Activate"}
                    >
                      {section.isActive ? (
                        <Eye className="h-4 w-4" />
                      ) : (
                        <EyeOff className="h-4 w-4" />
                      )}
                    </Button>
                    <Button
                      variant="ghost"
                      size="icon"
                      onClick={() => openEditDialog(section)}
                      title="Edit"
                    >
                      <Edit className="h-4 w-4" />
                    </Button>
                    <Button
                      variant="ghost"
                      size="icon"
                      onClick={() => handleDuplicate(section.id)}
                      title="Duplicate"
                    >
                      <Copy className="h-4 w-4" />
                    </Button>
                    <Button 
                      variant="ghost" 
                      size="icon" 
                      className="text-destructive" 
                      title="Delete"
                      onClick={() => {
                        if (confirm(`Delete "${section.name}"? This action cannot be undone.`)) {
                          handleDelete(section.id)
                        }
                      }}
                    >
                      <Trash2 className="h-4 w-4" />
                    </Button>
                  </div>
                </div>
              </CardContent>
            </Card>
          ))
        )}
      </div>

      {/* Create/Edit Dialog */}
      <Dialog open={isDialogOpen} onOpenChange={setIsDialogOpen}>
        <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
          <DialogHeader>
            <DialogTitle>
              {editingSection ? "Edit Section" : "Create New Section"}
            </DialogTitle>
            <DialogDescription>
              Configure your &quot;Don&apos;t Miss&quot; section. A unique shortcode will be auto-generated.
            </DialogDescription>
          </DialogHeader>

          <div className="space-y-4 py-4">
            <div className="grid gap-4 md:grid-cols-2">
              <div className="space-y-2">
                <Label htmlFor="name">Internal Name *</Label>
                <Input
                  id="name"
                  value={formData.name}
                  onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                  placeholder="e.g., Homepage Featured"
                />
                <p className="text-xs text-muted-foreground">For admin reference only</p>
              </div>
              <div className="space-y-2">
                <Label htmlFor="title">Display Title *</Label>
                <Input
                  id="title"
                  value={formData.title}
                  onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                  placeholder="e.g., Don't Miss"
                />
              </div>
            </div>

            <div className="space-y-2">
              <Label htmlFor="subtitle">Subtitle</Label>
              <Input
                id="subtitle"
                value={formData.subtitle}
                onChange={(e) => setFormData({ ...formData, subtitle: e.target.value })}
                placeholder="Brief description of this section"
              />
            </div>

            <Separator />

            <div className="space-y-2">
              <Label>Layout</Label>
              <Select
                value={formData.layout}
                onValueChange={(value: any) => setFormData({ ...formData, layout: value })}
              >
                <SelectTrigger>
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="mixed">Mixed (Blogs + Products)</SelectItem>
                  <SelectItem value="blogs_only">Blogs Only</SelectItem>
                  <SelectItem value="products_only">Products Only</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div className="grid gap-4 md:grid-cols-2">
              {formData.layout !== "products_only" && (
                <div className="space-y-2">
                  <Label>Blog Posts Count</Label>
                  <Select
                    value={formData.blogCount.toString()}
                    onValueChange={(value: string) => setFormData({ ...formData, blogCount: parseInt(value) })}
                  >
                    <SelectTrigger>
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      {[1, 2, 3, 4, 5, 6, 8, 10, 12].map((n) => (
                        <SelectItem key={n} value={n.toString()}>{n} posts</SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>
              )}
              {formData.layout !== "blogs_only" && (
                <div className="space-y-2">
                  <Label>Products Count</Label>
                  <Select
                    value={formData.productCount.toString()}
                    onValueChange={(value: string) => setFormData({ ...formData, productCount: parseInt(value) })}
                  >
                    <SelectTrigger>
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      {[1, 2, 3, 4, 5, 6, 8, 10, 12].map((n) => (
                        <SelectItem key={n} value={n.toString()}>{n} products</SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>
              )}
            </div>

            <div className="grid gap-4 md:grid-cols-2">
              <div className="space-y-2">
                <Label>Blog Category (Optional)</Label>
                <Select
                  value={formData.blogCategoryId}
                  onValueChange={(value: string) => setFormData({ ...formData, blogCategoryId: value })}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="All Categories" />
                  </SelectTrigger>
                  <SelectContent>
                    {categories.map((cat) => (
                      <SelectItem key={cat.id} value={cat.id}>{cat.name}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
              <div className="space-y-2">
                <Label>Product Category (Optional)</Label>
                <Select
                  value={formData.productCategoryId}
                  onValueChange={(value: string) => setFormData({ ...formData, productCategoryId: value })}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="All Categories" />
                  </SelectTrigger>
                  <SelectContent>
                    {categories.map((cat) => (
                      <SelectItem key={cat.id} value={cat.id}>{cat.name}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
            </div>

            <div className="space-y-2">
              <Label>Sort By</Label>
              <Select
                value={formData.sortBy}
                onValueChange={(value: any) => setFormData({ ...formData, sortBy: value })}
              >
                <SelectTrigger>
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="latest">Latest First</SelectItem>
                  <SelectItem value="popular">Most Popular</SelectItem>
                  <SelectItem value="featured">Featured First</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <Separator />

            <div className="grid gap-4 md:grid-cols-2">
              <div className="space-y-2">
                <Label>Background Color</Label>
                <div className="flex gap-2">
                  <input
                    type="color"
                    value={formData.backgroundColor || "#ffffff"}
                    onChange={(e) => setFormData({ ...formData, backgroundColor: e.target.value })}
                    className="h-10 w-20 rounded"
                  />
                  <Input
                    value={formData.backgroundColor}
                    onChange={(e) => setFormData({ ...formData, backgroundColor: e.target.value })}
                    placeholder="#ffffff or empty"
                  />
                </div>
              </div>
              <div className="space-y-2">
                <Label>Text Color</Label>
                <div className="flex gap-2">
                  <input
                    type="color"
                    value={formData.textColor || "#000000"}
                    onChange={(e) => setFormData({ ...formData, textColor: e.target.value })}
                    className="h-10 w-20 rounded"
                  />
                  <Input
                    value={formData.textColor}
                    onChange={(e) => setFormData({ ...formData, textColor: e.target.value })}
                    placeholder="#000000 or empty"
                  />
                </div>
              </div>
            </div>

            <div className="flex items-center justify-between">
              <div className="space-y-0.5">
                <Label>Show &quot;View All&quot; Link</Label>
                <p className="text-sm text-muted-foreground">Display a link to view all content</p>
              </div>
              <Switch
                checked={formData.showViewAll}
                onCheckedChange={(checked: boolean) => setFormData({ ...formData, showViewAll: checked })}
              />
            </div>

            <div className="flex items-center justify-between">
              <div className="space-y-0.5">
                <Label>Active</Label>
                <p className="text-sm text-muted-foreground">Show this section on the website</p>
              </div>
              <Switch
                checked={formData.isActive}
                onCheckedChange={(checked: boolean) => setFormData({ ...formData, isActive: checked })}
              />
            </div>
          </div>

          <DialogFooter>
            <Button variant="outline" onClick={() => setIsDialogOpen(false)}>
              Cancel
            </Button>
            <Button onClick={handleSave} disabled={!formData.name || !formData.title || saving}>
              {saving ? "Saving..." : editingSection ? "Save Changes" : "Create Section"}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  )
}
