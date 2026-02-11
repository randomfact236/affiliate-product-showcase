"use client"

import { useState } from "react"
import Link from "next/link"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Textarea } from "@/components/ui/textarea"
import { Switch } from "@/components/ui/switch"
import { Separator } from "@/components/ui/separator"
import { 
  Save, 
  Globe, 
  Bell, 
  Shield, 
  FileText, 
  ShoppingBag, 
  Palette,
  Code,
  Sparkles,
  ChevronRight,
} from "lucide-react"

export default function SettingsPage() {
  const [saving, setSaving] = useState(false)
  const [generalSettings, setGeneralSettings] = useState({
    siteName: "Affiliate Showcase",
    siteDescription: "Discover the best affiliate products and deals",
    siteUrl: "https://example.com",
    contactEmail: "contact@example.com",
    phoneNumber: "",
    address: "",
  })

  const handleSave = async () => {
    setSaving(true)
    await new Promise((resolve) => setTimeout(resolve, 1000))
    setSaving(false)
  }

  const settingCategories = [
    {
      title: "Blog Settings",
      description: "Configure blog posts, comments, and display options",
      href: "/admin/settings/blog",
      icon: FileText,
    },
    {
      title: "Product Settings",
      description: "Configure products, reviews, and affiliate options",
      href: "/admin/settings/products",
      icon: ShoppingBag,
    },
    {
      title: "Appearance",
      description: "Customize theme, colors, and styling",
      href: "/admin/settings/appearance",
      icon: Palette,
    },
    {
      title: "Don't Miss Sections",
      description: "Manage featured content sections with auto-generated shortcodes",
      href: "/admin/settings/dont-miss",
      icon: Sparkles,
    },
    {
      title: "Shortcodes",
      description: "View and copy available shortcodes",
      href: "/admin/settings/shortcodes",
      icon: Code,
    },
  ]

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Settings</h1>
          <p className="text-muted-foreground">
            Manage your website configuration and preferences
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

      {/* Setting Categories Grid */}
      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        {settingCategories.map((category) => {
          const Icon = category.icon
          return (
            <Link key={category.href} href={category.href}>
              <Card className="h-full hover:border-primary transition-colors cursor-pointer">
                <CardContent className="p-6">
                  <div className="flex items-start justify-between">
                    <div className="flex items-center gap-3">
                      <div className="p-2 rounded-lg bg-primary/10">
                        <Icon className="h-5 w-5 text-primary" />
                      </div>
                      <div>
                        <h3 className="font-semibold">{category.title}</h3>
                        <p className="text-sm text-muted-foreground">{category.description}</p>
                      </div>
                    </div>
                    <ChevronRight className="h-5 w-5 text-muted-foreground" />
                  </div>
                </CardContent>
              </Card>
            </Link>
          )
        })}
      </div>

      {/* General Settings */}
      <Card>
        <CardHeader>
          <CardTitle>Site Information</CardTitle>
          <CardDescription>Basic information about your website</CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="grid gap-4 md:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor="siteName">Site Name</Label>
              <Input
                id="siteName"
                value={generalSettings.siteName}
                onChange={(e) => setGeneralSettings({ ...generalSettings, siteName: e.target.value })}
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="siteUrl">Site URL</Label>
              <Input
                id="siteUrl"
                value={generalSettings.siteUrl}
                onChange={(e) => setGeneralSettings({ ...generalSettings, siteUrl: e.target.value })}
              />
            </div>
          </div>
          <div className="space-y-2">
            <Label htmlFor="siteDescription">Site Description</Label>
            <Textarea
              id="siteDescription"
              value={generalSettings.siteDescription}
              onChange={(e) => setGeneralSettings({ ...generalSettings, siteDescription: e.target.value })}
              rows={3}
            />
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Contact Information</CardTitle>
          <CardDescription>How visitors can reach you</CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="grid gap-4 md:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor="contactEmail">Contact Email</Label>
              <Input
                id="contactEmail"
                type="email"
                value={generalSettings.contactEmail}
                onChange={(e) => setGeneralSettings({ ...generalSettings, contactEmail: e.target.value })}
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="phoneNumber">Phone Number</Label>
              <Input
                id="phoneNumber"
                value={generalSettings.phoneNumber}
                onChange={(e) => setGeneralSettings({ ...generalSettings, phoneNumber: e.target.value })}
              />
            </div>
          </div>
          <div className="space-y-2">
            <Label htmlFor="address">Address</Label>
            <Textarea
              id="address"
              value={generalSettings.address}
              onChange={(e) => setGeneralSettings({ ...generalSettings, address: e.target.value })}
              rows={2}
            />
          </div>
        </CardContent>
      </Card>

      {/* Notifications & Security */}
      <div className="grid gap-6 lg:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Bell className="h-5 w-5" />
              Notifications
            </CardTitle>
            <CardDescription>Configure email notifications</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            {[
              { label: "New blog post published", desc: "When a new post goes live" },
              { label: "New comment received", desc: "When someone comments on a post" },
              { label: "Product review submitted", desc: "When a product review is added" },
            ].map((item, index) => (
              <div key={index} className="flex items-center justify-between">
                <div className="space-y-0.5">
                  <Label>{item.label}</Label>
                  <p className="text-sm text-muted-foreground">{item.desc}</p>
                </div>
                <Switch defaultChecked={index < 2} />
              </div>
            ))}
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Shield className="h-5 w-5" />
              Security
            </CardTitle>
            <CardDescription>Manage security settings</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            {[
              { label: "Two-factor authentication", desc: "Require 2FA for admin access" },
              { label: "Login notifications", desc: "Get notified of new logins" },
              { label: "Auto-logout", desc: "Logout after 30 minutes of inactivity" },
            ].map((item, index) => (
              <div key={index} className="flex items-center justify-between">
                <div className="space-y-0.5">
                  <Label>{item.label}</Label>
                  <p className="text-sm text-muted-foreground">{item.desc}</p>
                </div>
                <Switch defaultChecked={index === 2} />
              </div>
            ))}
          </CardContent>
        </Card>
      </div>
    </div>
  )
}
