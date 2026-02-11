"use client"

import { useEffect, useState } from "react"
import Link from "next/link"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardHeader } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Badge } from "@/components/ui/badge"
import { Progress } from "@/components/ui/progress"
import {
  Upload,
  Search,
  ImageIcon,
  FileImage,
  RefreshCw,
  CheckCircle,
  AlertCircle,
} from "lucide-react"

interface Media {
  id: string
  filename: string
  originalUrl: string
  mimeType: string
  fileSize: number
  width: number | null
  height: number | null
  conversionStatus: string
  isConverted: boolean
  thumbnailUrl: string | null
  createdAt: string
}

interface ConversionStats {
  totalImages: number
  fullyOptimized: number
  needsConversion: number
  storageSaved: number
  storageSavedFormatted: string
  optimizationPercentage: number
}

export default function MediaPage() {
  const [media, setMedia] = useState<Media[]>([])
  const [stats, setStats] = useState<ConversionStats | null>(null)
  const [loading, setLoading] = useState(true)
  const [searchQuery, setSearchQuery] = useState("")

  useEffect(() => {
    fetchMedia()
    fetchStats()
  }, [])

  const fetchMedia = async () => {
    try {
      const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/media`)
      if (response.ok) {
        const data = await response.json()
        setMedia(data.items || [])
      }
    } catch (error) {
      console.error("Failed to fetch media:", error)
    } finally {
      setLoading(false)
    }
  }

  const fetchStats = async () => {
    try {
      const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/media/stats`)
      if (response.ok) {
        const data = await response.json()
        setStats(data)
      }
    } catch (error) {
      console.error("Failed to fetch stats:", error)
    }
  }

  const formatFileSize = (bytes: number) => {
    if (bytes === 0) return "0 Bytes"
    const k = 1024
    const sizes = ["Bytes", "KB", "MB", "GB"]
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i]
  }

  const getStatusIcon = (status: string) => {
    switch (status) {
      case "COMPLETED":
        return <CheckCircle className="h-4 w-4 text-green-500" />
      case "FAILED":
        return <AlertCircle className="h-4 w-4 text-red-500" />
      case "PROCESSING":
        return <RefreshCw className="h-4 w-4 animate-spin text-blue-500" />
      default:
        return <FileImage className="h-4 w-4 text-gray-400" />
    }
  }

  const filteredMedia = media.filter((m) =>
    m.filename.toLowerCase().includes(searchQuery.toLowerCase())
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
          <h1 className="text-3xl font-bold tracking-tight">Media Library</h1>
          <p className="text-muted-foreground">
            Manage images and auto-conversion
          </p>
        </div>
        <Button asChild>
          <Link href="/admin/media/upload">
            <Upload className="mr-2 h-4 w-4" />
            Upload
          </Link>
        </Button>
      </div>

      {/* Stats Cards */}
      {stats && (
        <div className="grid gap-4 md:grid-cols-4">
          <Card>
            <CardContent className="p-6">
              <div className="flex items-center gap-2">
                <ImageIcon className="h-4 w-4 text-muted-foreground" />
                <span className="text-sm text-muted-foreground">Total Images</span>
              </div>
              <p className="mt-2 text-3xl font-bold">{stats.totalImages}</p>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-6">
              <div className="flex items-center gap-2">
                <CheckCircle className="h-4 w-4 text-green-500" />
                <span className="text-sm text-muted-foreground">Optimized</span>
              </div>
              <p className="mt-2 text-3xl font-bold">{stats.fullyOptimized}</p>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-6">
              <div className="flex items-center gap-2">
                <RefreshCw className="h-4 w-4 text-yellow-500" />
                <span className="text-sm text-muted-foreground">Needs Conversion</span>
              </div>
              <p className="mt-2 text-3xl font-bold">{stats.needsConversion}</p>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-6">
              <div className="flex items-center gap-2">
                <FileImage className="h-4 w-4 text-blue-500" />
                <span className="text-sm text-muted-foreground">Storage Saved</span>
              </div>
              <p className="mt-2 text-3xl font-bold">{stats.storageSavedFormatted}</p>
            </CardContent>
          </Card>
        </div>
      )}

      {/* Optimization Progress */}
      {stats && (
        <Card>
          <CardContent className="p-6">
            <div className="flex items-center justify-between mb-2">
              <span className="text-sm font-medium">Optimization Progress</span>
              <span className="text-sm text-muted-foreground">
                {stats.optimizationPercentage}%
              </span>
            </div>
            <Progress value={stats.optimizationPercentage} className="h-2" />
          </CardContent>
        </Card>
      )}

      {/* Search */}
      <Card>
        <CardHeader>
          <div className="flex items-center gap-4">
            <div className="relative flex-1">
              <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
              <Input
                placeholder="Search media..."
                className="pl-10"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
              />
            </div>
          </div>
        </CardHeader>
      </Card>

      {/* Media Grid */}
      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        {filteredMedia.map((item) => (
          <Card key={item.id} className="overflow-hidden">
            {/* Image Preview */}
            <div className="aspect-square bg-muted">
              {item.thumbnailUrl || item.originalUrl ? (
                <img
                  src={item.thumbnailUrl || item.originalUrl}
                  alt={item.filename}
                  className="h-full w-full object-cover"
                />
              ) : (
                <div className="flex h-full items-center justify-center">
                  <ImageIcon className="h-12 w-12 text-muted-foreground" />
                </div>
              )}
            </div>
            <CardContent className="p-4">
              <div className="flex items-center justify-between">
                <p className="truncate text-sm font-medium">{item.filename}</p>
                {getStatusIcon(item.conversionStatus)}
              </div>
              <div className="mt-2 flex items-center gap-2">
                <Badge variant="secondary">{formatFileSize(item.fileSize)}</Badge>
                {item.width && item.height && (
                  <Badge variant="outline">
                    {item.width}x{item.height}
                  </Badge>
                )}
              </div>
            </CardContent>
          </Card>
        ))}
      </div>

      {filteredMedia.length === 0 && (
        <Card className="p-12 text-center">
          <p className="text-muted-foreground">No media found</p>
          <Button className="mt-4" asChild>
            <Link href="/admin/media/upload">
              <Upload className="mr-2 h-4 w-4" />
              Upload Media
            </Link>
          </Button>
        </Card>
      )}
    </div>
  )
}
