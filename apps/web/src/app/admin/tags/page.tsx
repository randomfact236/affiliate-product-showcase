"use client"

import { useEffect, useState } from "react"
import Link from "next/link"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Badge } from "@/components/ui/badge"
import { Plus, Search, Edit2, Trash2 } from "lucide-react"

interface Tag {
  id: string
  slug: string
  name: string
  description: string | null
  color: string | null
  icon: string | null
  productCount: number
  isActive: boolean
}

export default function TagsPage() {
  const [tags, setTags] = useState<Tag[]>([])
  const [loading, setLoading] = useState(true)
  const [searchQuery, setSearchQuery] = useState("")

  useEffect(() => {
    fetchTags()
  }, [])

  const fetchTags = async () => {
    try {
      const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/tags`)
      if (response.ok) {
        const data = await response.json()
        setTags(data.items || [])
      }
    } catch (error) {
      console.error("Failed to fetch tags:", error)
    } finally {
      setLoading(false)
    }
  }

  const filteredTags = tags.filter(
    (tag) =>
      tag.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
      tag.slug.toLowerCase().includes(searchQuery.toLowerCase())
  )

  if (loading) {
    return (
      <div className="flex h-64 items-center justify-center">
        <div className="h-8 w-8 animate-spin rounded-full border-4 border-primary border-t-transparent" />
      </div>
    )
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Tags</h1>
          <p className="text-muted-foreground">
            Manage product tags and labels
          </p>
        </div>
        <Button asChild>
          <Link href="/admin/tags/new">
            <Plus className="mr-2 h-4 w-4" />
            Add Tag
          </Link>
        </Button>
      </div>

      {/* Search */}
      <Card>
        <CardHeader>
          <div className="flex items-center gap-4">
            <div className="relative flex-1">
              <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
              <Input
                placeholder="Search tags..."
                className="pl-10"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
              />
            </div>
          </div>
        </CardHeader>
      </Card>

      {/* Tags Grid */}
      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        {filteredTags.map((tag) => (
          <Card key={tag.id}>
            <CardContent className="p-6">
              <div className="flex items-start justify-between">
                <div className="flex items-center gap-3">
                  {tag.color && (
                    <div
                      className="h-8 w-8 rounded-full"
                      style={{ backgroundColor: tag.color }}
                    />
                  )}
                  <div>
                    <h3 className="font-semibold">{tag.name}</h3>
                    <p className="text-sm text-muted-foreground">/{tag.slug}</p>
                  </div>
                </div>
                <div className="flex gap-2">
                  <Button variant="ghost" size="icon" asChild>
                    <Link href={`/admin/tags/${tag.id}/edit`}>
                      <Edit2 className="h-4 w-4" />
                    </Link>
                  </Button>
                  <Button variant="ghost" size="icon" className="text-destructive">
                    <Trash2 className="h-4 w-4" />
                  </Button>
                </div>
              </div>
              {tag.description && (
                <p className="mt-3 text-sm text-muted-foreground">
                  {tag.description}
                </p>
              )}
              <div className="mt-4 flex items-center gap-2">
                <Badge variant="secondary">{tag.productCount} products</Badge>
                {!tag.isActive && (
                  <Badge variant="outline">Inactive</Badge>
                )}
              </div>
            </CardContent>
          </Card>
        ))}
      </div>

      {filteredTags.length === 0 && (
        <Card className="p-12 text-center">
          <p className="text-muted-foreground">No tags found</p>
        </Card>
      )}
    </div>
  )
}
