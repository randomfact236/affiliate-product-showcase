"use client"

import { useState } from "react"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Switch } from "@/components/ui/switch"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Separator } from "@/components/ui/separator"
import { Save, Palette, Sun, Moon, Monitor } from "lucide-react"
import Link from "next/link"
import { ArrowLeft } from "lucide-react"
import { useTheme } from "next-themes"

export default function AppearanceSettingsPage() {
  const { theme, setTheme } = useTheme()
  const [saving, setSaving] = useState(false)
  const [settings, setSettings] = useState({
    primaryColor: "#3b82f6",
    fontFamily: "inter",
    borderRadius: "medium",
    enableAnimations: true,
    showScrollToTop: true,
  })

  const handleSave = async () => {
    setSaving(true)
    await new Promise((resolve) => setTimeout(resolve, 1000))
    setSaving(false)
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col gap-4">
        <Button variant="outline" size="sm" asChild className="w-fit">
          <Link href="/admin/settings">
            <ArrowLeft className="mr-2 h-4 w-4" />
            Back to Settings
          </Link>
        </Button>
        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Appearance</h1>
            <p className="text-muted-foreground">
              Customize the look and feel of your website
            </p>
          </div>
          <Button onClick={handleSave} disabled={saving}>
            {saving ? (
              <>
                <div className="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent" />
                Saving...
              </>
            ) : (
              <>
                <Save className="mr-2 h-4 w-4" />
                Save Changes
              </>
            )}
          </Button>
        </div>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Theme Mode</CardTitle>
          <CardDescription>Choose the default theme for your website</CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="flex flex-wrap gap-4">
            {[
              { value: "light", icon: Sun, label: "Light Mode", bg: "bg-white", border: "border-gray-200", iconColor: "text-yellow-500" },
              { value: "dark", icon: Moon, label: "Dark Mode", bg: "bg-gray-900", border: "border-gray-700", iconColor: "text-blue-400" },
              { value: "system", icon: Monitor, label: "System Default", bg: "bg-gradient-to-br from-white to-gray-900", border: "border-gray-300", iconColor: "text-gray-600" },
            ].map((option) => (
              <button
                key={option.value}
                onClick={() => setTheme(option.value)}
                className={`
                  flex flex-col items-center gap-3 p-6 rounded-xl border-2 transition-all
                  ${theme === option.value ? "border-primary bg-primary/5" : "border-border hover:border-muted-foreground"}
                `}
              >
                <div className={`h-12 w-12 rounded-full ${option.bg} border-2 ${option.border} flex items-center justify-center shadow-sm`}>
                  <option.icon className={`h-6 w-6 ${option.iconColor}`} />
                </div>
                <span className="font-medium">{option.label}</span>
              </button>
            ))}
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Customization</CardTitle>
          <CardDescription>Customize colors and styling</CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="space-y-2">
            <Label htmlFor="primaryColor">Primary Color</Label>
            <div className="flex items-center gap-3">
              <input
                type="color"
                id="primaryColor"
                value={settings.primaryColor}
                onChange={(e) => setSettings({ ...settings, primaryColor: e.target.value })}
                className="h-10 w-20 rounded cursor-pointer"
              />
              <Input
                value={settings.primaryColor}
                onChange={(e) => setSettings({ ...settings, primaryColor: e.target.value })}
                className="w-32"
              />
            </div>
          </div>

          <div className="space-y-2">
            <Label htmlFor="fontFamily">Font Family</Label>
            <Select
              value={settings.fontFamily}
              onValueChange={(value) => setSettings({ ...settings, fontFamily: value })}
            >
              <SelectTrigger>
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="inter">Inter (Modern)</SelectItem>
                <SelectItem value="roboto">Roboto (Clean)</SelectItem>
                <SelectItem value="poppins">Poppins (Friendly)</SelectItem>
                <SelectItem value="playfair">Playfair Display (Elegant)</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <Separator />

          <div className="space-y-4">
            <div className="flex items-center justify-between">
              <div className="space-y-0.5">
                <Label>Enable Animations</Label>
                <p className="text-sm text-muted-foreground">Show smooth transitions and animations</p>
              </div>
              <Switch
                checked={settings.enableAnimations}
                onCheckedChange={(checked) => setSettings({ ...settings, enableAnimations: checked })}
              />
            </div>

            <div className="flex items-center justify-between">
              <div className="space-y-0.5">
                <Label>Show Scroll to Top</Label>
                <p className="text-sm text-muted-foreground">Display scroll to top button on long pages</p>
              </div>
              <Switch
                checked={settings.showScrollToTop}
                onCheckedChange={(checked) => setSettings({ ...settings, showScrollToTop: checked })}
              />
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  )
}
