"use client"

import { useEffect, useState } from "react"
import { useParams, useRouter } from "next/navigation"
import Link from "next/link"
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
import { ArrowLeft, Save, Sparkles, Loader2 } from "lucide-react"
import { toast } from "sonner"
import { getRibbon, updateRibbon, type Ribbon } from "@/lib/api/ribbons"
import { AuditTrail } from "@/lib/audit-trail"

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

export default function EditRibbonPage() {
  const params = useParams()
  const router = useRouter()
  const id = params.id as string

  const [ribbon, setRibbon] = useState<Ribbon | null>(null)
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
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
  const [errors, setErrors] = useState<Record<string, string>>({})

  useEffect(() => {
    fetchRibbon()
  }, [id])

  const fetchRibbon = async () => {
    try {
      setLoading(true)
      const data = await getRibbon(id)
      setRibbon(data)
      setFormData({
        name: data.name,
        label: data.label,
        description: data.description || "",
        color: data.color,
        bgColor: data.bgColor,
        icon: data.icon || "",
        position: data.position,
        priority: data.priority,
        isActive: data.isActive,
      })
    } catch (err) {
      console.error("Failed to fetch ribbon:", err)
      toast.error("Failed to load ribbon")
    } finally {
      setLoading(false)
    }
  }

  const validateForm = () => {
    const newErrors: Record<string, string> = {}
    if (!formData.name.trim()) newErrors.name = "Name is required"
    if (!formData.label.trim()) newErrors.label = "Label is required"
    setErrors(newErrors)
    return Object.keys(newErrors).length === 0
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    if (!validateForm()) return

    setSaving(true)
    try {
      await updateRibbon(id, {
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

      toast.success("Ribbon updated successfully")
      
      AuditTrail.log({
        action: "UPDATE",
        entity: "Ribbon",
        entityId: id,
        changes: { name: { old: ribbon?.name, new: formData.name } },
      })

      router.push("/admin/ribbons")
    } catch (err) {
      console.error("Failed to update ribbon:", err)
      toast.error(err instanceof Error ? err.message : "Failed to update ribbon")
    } finally {
      setSaving(false)
    }
  }

  if (loading) {
    return (
      <div className="flex h-96 items-center justify-center">
        <Loader2 className="h-8 w-8 animate-spin text-purple-600" />
      </div>
    )
  }

  if (!ribbon) {
    return (
      <div className="flex h-96 items-center justify-center">
        <div className="text-center">
          <p className="text-gray-500">Ribbon not found</p>
          <Button asChild className="mt-4">
            <Link href="/admin/ribbons">Back to Ribbons</Link>
          </Button>
        </div>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="relative overflow-hidden rounded-xl bg-gradient-to-r from-purple-600 via-purple-500 to-pink-500 px-8 py-8 text-white">
        <div className="relative z-10">
          <div className="flex items-center gap-2 mb-2">
            <Button variant="ghost" size="sm" className="text-white/80 hover:text-white hover:bg-white/20" asChild>
              <Link href="/admin/ribbons">
                <ArrowLeft className="h-4 w-4 mr-1" />
                Back to Ribbons
              </Link>
            </Button>
          </div>
          <div>
            <h1 className="text-3xl font-bold flex items-center gap-3">
              <Sparkles className="h-8 w-8" />
              Edit Ribbon
            </h1>
            <p className="mt-2 text-purple-100">
              Editing: {ribbon.name}
            </p>
          </div>
        </div>
        <div className="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10" />
        <div className="absolute -bottom-10 -right-10 h-32 w-32 rounded-full bg-white/5" />
      </div>

      {/* Edit Form */}
      <form onSubmit={handleSubmit}>
        <Card className="border-l-4 border-l-purple-500">
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Sparkles className="h-5 w-5 text-purple-600" />
              Ribbon Details
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-6">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              {/* Left Column */}
              <div className="space-y-4">
                <div className="space-y-2">
                  <Label htmlFor="name">
                    Name <span className="text-red-500">*</span>
                  </Label>
                  <Input
                    id="name"
                    value={formData.name}
                    onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                    className={errors.name ? "border-red-500" : ""}
                  />
                  {errors.name && <p className="text-sm text-red-500">{errors.name}</p>}
                </div>

                <div className="space-y-2">
                  <Label htmlFor="label">
                    Label <span className="text-red-500">*</span>
                  </Label>
                  <Input
                    id="label"
                    value={formData.label}
                    onChange={(e) => setFormData({ ...formData, label: e.target.value })}
                    className={errors.label ? "border-red-500" : ""}
                  />
                  {errors.label && <p className="text-sm text-red-500">{errors.label}</p>}
                </div>

                <div className="space-y-2">
                  <Label htmlFor="description">Description</Label>
                  <Textarea
                    id="description"
                    rows={3}
                    value={formData.description}
                    onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                    placeholder="Enter ribbon description..."
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
                    Active (visible on products)
                  </Label>
                </div>
              </div>

              {/* Right Column */}
              <div className="space-y-4">
                {/* Colors */}
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

                {/* Position */}
                <div className="space-y-2">
                  <Label>Position</Label>
                  <Select
                    value={formData.position}
                    onValueChange={(value: string) =>
                      setFormData({ ...formData, position: value })
                    }
                  >
                    <SelectTrigger>
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      {POSITION_OPTIONS.map((pos) => (
                        <SelectItem key={pos.value} value={pos.value}>
                          {pos.name}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

                {/* Icon */}
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
                        <SelectItem key={icon.value} value={icon.value}>
                          {icon.name}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

                {/* Priority */}
                <div className="space-y-2">
                  <Label>Priority (higher = more important)</Label>
                  <Input
                    type="number"
                    min={1}
                    max={100}
                    value={formData.priority}
                    onChange={(e) =>
                      setFormData({ ...formData, priority: parseInt(e.target.value) || 0 })
                    }
                  />
                </div>

                {/* Preview */}
                <div className="p-4 bg-gray-50 rounded-lg">
                  <Label className="text-sm text-gray-500 mb-2 block">Preview</Label>
                  <div className="flex items-center gap-4">
                    <span 
                      className="px-4 py-2 rounded text-sm font-medium shadow-sm"
                      style={{ backgroundColor: formData.bgColor, color: formData.color }}
                    >
                      {formData.label || "Ribbon Label"}
                    </span>
                    <span className="text-sm text-gray-500">
                      Position: {POSITION_OPTIONS.find(p => p.value === formData.position)?.name}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            {/* Actions */}
            <div className="flex items-center justify-end gap-3 pt-6 border-t">
              <Button type="button" variant="outline" asChild>
                <Link href="/admin/ribbons">Cancel</Link>
              </Button>
              <Button 
                type="submit" 
                className="bg-purple-600 hover:bg-purple-700"
                disabled={saving}
              >
                {saving ? (
                  <>
                    <Loader2 className="h-4 w-4 mr-2 animate-spin" />
                    Saving...
                  </>
                ) : (
                  <>
                    <Save className="h-4 w-4 mr-2" />
                    Save Changes
                  </>
                )}
              </Button>
            </div>
          </CardContent>
        </Card>
      </form>
    </div>
  )
}
