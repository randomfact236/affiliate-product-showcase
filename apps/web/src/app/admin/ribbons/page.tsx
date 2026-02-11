"use client"

import { useEffect, useState } from "react"
import Link from "next/link"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Badge } from "@/components/ui/badge"
import { Plus, Search, Edit2, Trash2, Eye, EyeOff } from "lucide-react"

interface Ribbon {
  id: string
  name: string
  label: string
  description: string | null
  color: string
  bgColor: string
  icon: string | null
  position: string
  priority: number
  isActive: boolean
}

export default function RibbonsPage() {
  const [ribbons, setRibbons] = useState<Ribbon[]>([])
  const [loading, setLoading] = useState(true)
  const [searchQuery, setSearchQuery] = useState("")

  useEffect(() => {
    fetchRibbons()
  }, [])

  const fetchRibbons = async () => {
    try {
      const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/ribbons`)
      if (response.ok) {
        const data = await response.json()
        setRibbons(data.items || [])
      }
    } catch (error) {
      console.error("Failed to fetch ribbons:", error)
    } finally {
      setLoading(false)
    }
  }

  const toggleActive = async (id: string) => {
    try {
      const response = await fetch(
        `${process.env.NEXT_PUBLIC_API_URL}/ribbons/${id}/toggle-active`,
        { method: "PATCH" }
      )
      if (response.ok) {
        fetchRibbons()
      }
    } catch (error) {
      console.error("Failed to toggle ribbon:", error)
    }
  }

  const filteredRibbons = ribbons.filter(
    (ribbon) =>
      ribbon.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
      ribbon.label.toLowerCase().includes(searchQuery.toLowerCase())
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
          <h1 className="text-3xl font-bold tracking-tight">Ribbons</h1>
          <p className="text-muted-foreground">
            Manage product ribbons and badges
          </p>
        </div>
        <Button asChild>
          <Link href="/admin/ribbons/new">
            <Plus className="mr-2 h-4 w-4" />
            Add Ribbon
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
                placeholder="Search ribbons..."
                className="pl-10"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
              />
            </div>
          </div>
        </CardHeader>
      </Card>

      {/* Ribbons Grid */}
      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        {filteredRibbons.map((ribbon) => (
          <Card key={ribbon.id}>
            <CardContent className="p-6">
              <div className="flex items-start justify-between">
                <div className="flex items-center gap-3">
                  {/* Ribbon Preview */}
                  <div
                    className="rounded px-3 py-1 text-sm font-medium"
                    style={{
                      backgroundColor: ribbon.bgColor,
                      color: ribbon.color,
                    }}
                  >
                    {ribbon.label}
                  </div>
                </div>
                <div className="flex gap-2">
                  <Button
                    variant="ghost"
                    size="icon"
                    onClick={() => toggleActive(ribbon.id)}
                  >
                    {ribbon.isActive ? (
                      <Eye className="h-4 w-4" />
                    ) : (
                      <EyeOff className="h-4 w-4 text-muted-foreground" />
                    )}
                  </Button>
                  <Button variant="ghost" size="icon" asChild>
                    <Link href={`/admin/ribbons/${ribbon.id}/edit`}>
                      <Edit2 className="h-4 w-4" />
                    </Link>
                  </Button>
                  <Button variant="ghost" size="icon" className="text-destructive">
                    <Trash2 className="h-4 w-4" />
                  </Button>
                </div>
              </div>
              <div className="mt-4 space-y-2">
                <p className="text-sm">
                  <span className="text-muted-foreground">Name:</span> {ribbon.name}
                </p>
                <p className="text-sm">
                  <span className="text-muted-foreground">Position:</span>{" "}
                  {ribbon.position}
                </p>
                <p className="text-sm">
                  <span className="text-muted-foreground">Priority:</span>{" "}
                  {ribbon.priority}
                </p>
              </div>
              <div className="mt-4">
                {!ribbon.isActive && (
                  <Badge variant="outline">Inactive</Badge>
                )}
              </div>
            </CardContent>
          </Card>
        ))}
      </div>

      {filteredRibbons.length === 0 && (
        <Card className="p-12 text-center">
          <p className="text-muted-foreground">No ribbons found</p>
        </Card>
      )}
    </div>
  )
}
