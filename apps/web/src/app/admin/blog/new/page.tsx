"use client"

import { useState, useEffect } from "react"
import Link from "next/link"
import { useRouter } from "next/navigation"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Textarea } from "@/components/ui/textarea"
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select"
import { Badge } from "@/components/ui/badge"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { ArrowLeft, Loader2, Save, Eye, X, Plus, Heading, AlignLeft, Type } from "lucide-react"
import { slugify } from "@/lib/utils"
import {
  SectionEditor,
  BlogSection,
  createSection,
  createSectionWithSpacing,
  SectionType,
} from "@/components/blog/section-editor"

export default function NewBlogPostPage() {
  const router = useRouter()
  const [loading, setLoading] = useState(false)
  const [preview, setPreview] = useState(false)

  const [formData, setFormData] = useState({
    title: "",
    slug: "",
    excerpt: "",
    status: "DRAFT",
    categoryIds: [] as string[],
    tagIds: [] as string[],
    metaTitle: "",
    metaDescription: "",
    keywords: "",
  })

  const [sections, setSections] = useState<BlogSection[]>([])
  const [defaultSpacing, setDefaultSpacing] = useState(4) // Default 16px (medium)

  // Fetch default spacing from settings
  useEffect(() => {
    const fetchSettings = async () => {
      try {
        const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/settings/blog_default_section_spacing/value`)
        if (response.ok) {
          const data = await response.json()
          const spacing = parseInt(data.value) || 4
          setDefaultSpacing(spacing)
          // Create initial section with default spacing
          setSections([createSectionWithSpacing("heading-content", spacing)])
        } else {
          // Use default if setting not found
          setSections([createSectionWithSpacing("heading-content", 4)])
        }
      } catch (error) {
        console.error("Failed to fetch default spacing:", error)
        setSections([createSectionWithSpacing("heading-content", 4)])
      }
    }
    fetchSettings()
  }, [])

  const generateSlug = (title: string) => {
    return slugify(title)
  }

  const handleTitleChange = (title: string) => {
    setFormData((prev) => ({
      ...prev,
      title,
      slug: prev.slug || generateSlug(title),
      metaTitle: prev.metaTitle || title,
    }))
  }

  const addSection = (type: SectionType) => {
    setSections([...sections, createSectionWithSpacing(type, defaultSpacing)])
  }

  const updateSection = (index: number, updatedSection: BlogSection) => {
    const newSections = [...sections]
    newSections[index] = updatedSection
    setSections(newSections)
  }

  const deleteSection = (index: number) => {
    const newSections = sections.filter((_, i) => i !== index)
    setSections(newSections)
  }

  const moveSection = (index: number, direction: "up" | "down") => {
    if (direction === "up" && index > 0) {
      const newSections = [...sections]
      ;[newSections[index], newSections[index - 1]] = [
        newSections[index - 1],
        newSections[index],
      ]
      setSections(newSections)
    } else if (direction === "down" && index < sections.length - 1) {
      const newSections = [...sections]
      ;[newSections[index], newSections[index + 1]] = [
        newSections[index + 1],
        newSections[index],
      ]
      setSections(newSections)
    }
  }

  // Generate ID for heading text
  const generateHeadingId = (text: string, index: number) => {
    return `heading-${index}-${text.toLowerCase().replace(/[^a-z0-9]+/g, "-").replace(/(^-|-$)/g, "")}`
  }

  // Convert sections to HTML content for saving
  const compileContent = () => {
    let headingIndex = 0
    
    return sections
      .map((section) => {
        const style = `margin-top: ${section.spacing.top * 4}px; margin-bottom: ${section.spacing.bottom * 4}px;`
        
        switch (section.type) {
          case "heading":
            if (!section.heading) return ""
            const headingId = generateHeadingId(section.heading, headingIndex++)
            return `<h2 id="${headingId}" data-toc-id="${headingId}" style="${style}">${section.heading}</h2>`
          case "content":
            return section.content
              ? `<div style="${style}">${section.content}</div>`
              : ""
          case "heading-content":
            let headingHtml = ""
            if (section.heading) {
              const hId = generateHeadingId(section.heading, headingIndex++)
              headingHtml = `<h2 id="${hId}" data-toc-id="${hId}" style="margin-bottom: 16px;">${section.heading}</h2>`
            }
            const content = section.content || ""
            return (headingHtml || content)
              ? `<div style="${style}">${headingHtml}${content}</div>`
              : ""
          default:
            return ""
        }
      })
      .filter(Boolean)
      .join("\n")
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setLoading(true)

    const content = compileContent()

    try {
      const response = await fetch(
        `${process.env.NEXT_PUBLIC_API_URL}/api/v1/blog`,
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({
            ...formData,
            content,
            publishedAt:
              formData.status === "PUBLISHED"
                ? new Date().toISOString()
                : null,
          }),
        }
      )

      if (!response.ok) {
        const error = await response.json()
        throw new Error(error.message || "Failed to create blog post")
      }

      const post = await response.json()
      router.push("/admin/blog")
    } catch (err) {
      alert(err instanceof Error ? err.message : "Failed to create blog post")
    } finally {
      setLoading(false)
    }
  }

  // Preview rendering
  const renderPreview = () => {
    return sections.map((section) => {
      const containerStyle = {
        marginTop: `${section.spacing.top * 4}px`,
        marginBottom: `${section.spacing.bottom * 4}px`,
      }

      switch (section.type) {
        case "heading":
          return (
            <h2 key={section.id} style={containerStyle} className="text-2xl font-bold">
              {section.heading || "Untitled Section"}
            </h2>
          )
        case "content":
          return (
            <div
              key={section.id}
              style={containerStyle}
              dangerouslySetInnerHTML={{ __html: section.content || "" }}
            />
          )
        case "heading-content":
          return (
            <div key={section.id} style={containerStyle}>
              {section.heading && (
                <h2 className="text-2xl font-bold mb-4">{section.heading}</h2>
              )}
              <div dangerouslySetInnerHTML={{ __html: section.content || "" }} />
            </div>
          )
      }
    })
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div className="flex items-center gap-4">
          <Button variant="outline" size="icon" asChild>
            <Link href="/admin/blog">
              <ArrowLeft className="h-4 w-4" />
            </Link>
          </Button>
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Add Blog Post</h1>
            <p className="text-muted-foreground">
              Create a new blog post with sections
            </p>
          </div>
        </div>
        <div className="flex items-center gap-2">
          <Button
            variant="outline"
            onClick={() => setPreview(!preview)}
            disabled={loading}
          >
            <Eye className="mr-2 h-4 w-4" />
            {preview ? "Edit" : "Preview"}
          </Button>
          <Button onClick={handleSubmit} disabled={loading}>
            {loading ? (
              <Loader2 className="mr-2 h-4 w-4 animate-spin" />
            ) : (
              <Save className="mr-2 h-4 w-4" />
            )}
            Save Post
          </Button>
        </div>
      </div>

      {preview ? (
        <Card>
          <CardHeader>
            <CardTitle>Preview</CardTitle>
          </CardHeader>
          <CardContent className="prose max-w-none">
            <h1>{formData.title || "Untitled Post"}</h1>
            {formData.excerpt && <p className="lead">{formData.excerpt}</p>}
            {renderPreview()}
          </CardContent>
        </Card>
      ) : (
        <form
          onSubmit={handleSubmit}
          className="grid gap-6 lg:grid-cols-[1fr_400px]"
        >
          {/* Main Content */}
          <div className="space-y-6">
            <Card>
              <CardHeader>
                <CardTitle>Post Details</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="space-y-2">
                  <Label htmlFor="title">Title *</Label>
                  <Input
                    id="title"
                    value={formData.title}
                    onChange={(e) => handleTitleChange(e.target.value)}
                    placeholder="Enter post title"
                    required
                  />
                </div>

                <div className="space-y-2">
                  <Label htmlFor="slug">Slug *</Label>
                  <Input
                    id="slug"
                    value={formData.slug}
                    onChange={(e) =>
                      setFormData({ ...formData, slug: e.target.value })
                    }
                    placeholder="url-friendly-slug"
                    required
                  />
                  <p className="text-xs text-muted-foreground">
                    This will be used in the URL: /blog/
                    {formData.slug || "your-slug"}
                  </p>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="excerpt">Excerpt</Label>
                  <Textarea
                    id="excerpt"
                    value={formData.excerpt}
                    onChange={(e) =>
                      setFormData({ ...formData, excerpt: e.target.value })
                    }
                    placeholder="Brief summary of the post"
                    rows={3}
                  />
                </div>
              </CardContent>
            </Card>

            {/* Sections */}
            <div className="space-y-4">
              <div className="flex items-center justify-between">
                <h3 className="text-lg font-medium">Content Sections</h3>
                <span className="text-sm text-muted-foreground">
                  {sections.length} section{sections.length !== 1 ? "s" : ""}
                </span>
              </div>

              {sections.map((section, index) => (
                <SectionEditor
                  key={section.id}
                  section={section}
                  onUpdate={(updated) => updateSection(index, updated)}
                  onDelete={() => deleteSection(index)}
                  onMoveUp={() => moveSection(index, "up")}
                  onMoveDown={() => moveSection(index, "down")}
                  isFirst={index === 0}
                  isLast={index === sections.length - 1}
                />
              ))}

              {/* Add Section Buttons */}
              <div className="flex flex-wrap gap-2 pt-4">
                <Button
                  type="button"
                  variant="outline"
                  size="sm"
                  onClick={() => addSection("heading")}
                >
                  <Heading className="mr-2 h-4 w-4" />
                  Add Heading
                </Button>
                <Button
                  type="button"
                  variant="outline"
                  size="sm"
                  onClick={() => addSection("content")}
                >
                  <AlignLeft className="mr-2 h-4 w-4" />
                  Add Content
                </Button>
                <Button
                  type="button"
                  variant="outline"
                  size="sm"
                  onClick={() => addSection("heading-content")}
                >
                  <Type className="mr-2 h-4 w-4" />
                  Add Heading + Content
                </Button>
              </div>
            </div>
          </div>

          {/* Sidebar */}
          <div className="space-y-6">
            {/* Publish Settings */}
            <Card>
              <CardHeader>
                <CardTitle>Publish Settings</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="space-y-2">
                  <Label htmlFor="status">Status</Label>
                  <Select
                    value={formData.status}
                    onValueChange={(value: string) =>
                      setFormData({ ...formData, status: value })
                    }
                  >
                    <SelectTrigger>
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="DRAFT">Draft</SelectItem>
                      <SelectItem value="PENDING_REVIEW">
                        Pending Review
                      </SelectItem>
                      <SelectItem value="PUBLISHED">Published</SelectItem>
                      <SelectItem value="ARCHIVED">Archived</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </CardContent>
            </Card>

            {/* SEO Settings */}
            <Card>
              <CardHeader>
                <CardTitle>SEO</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="space-y-2">
                  <Label htmlFor="metaTitle">Meta Title</Label>
                  <Input
                    id="metaTitle"
                    value={formData.metaTitle}
                    onChange={(e) =>
                      setFormData({ ...formData, metaTitle: e.target.value })
                    }
                    placeholder="SEO title"
                  />
                </div>

                <div className="space-y-2">
                  <Label htmlFor="metaDescription">Meta Description</Label>
                  <Textarea
                    id="metaDescription"
                    value={formData.metaDescription}
                    onChange={(e) =>
                      setFormData({
                        ...formData,
                        metaDescription: e.target.value,
                      })
                    }
                    placeholder="SEO description"
                    rows={3}
                  />
                </div>

                <div className="space-y-2">
                  <Label htmlFor="keywords">Keywords</Label>
                  <Input
                    id="keywords"
                    value={formData.keywords}
                    onChange={(e) =>
                      setFormData({ ...formData, keywords: e.target.value })
                    }
                    placeholder="keyword1, keyword2, keyword3"
                  />
                  <p className="text-xs text-muted-foreground">
                    Comma-separated keywords
                  </p>
                </div>
              </CardContent>
            </Card>
          </div>
        </form>
      )}
    </div>
  )
}
