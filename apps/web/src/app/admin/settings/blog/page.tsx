"use client"

import { useState } from "react"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Switch } from "@/components/ui/switch"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Separator } from "@/components/ui/separator"
import { Save, BookOpen } from "lucide-react"
import Link from "next/link"
import { ArrowLeft } from "lucide-react"

export default function BlogSettingsPage() {
  const [saving, setSaving] = useState(false)
  const [settings, setSettings] = useState({
    postsPerPage: "10",
    excerptLength: "150",
    enableComments: true,
    requireApproval: true,
    showAuthor: true,
    showReadingTime: true,
    showRelatedPosts: true,
    relatedPostsCount: "3",
    enableSocialShare: true,
    defaultSectionSpacing: "4", // Default spacing between heading and content sections
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
            <h1 className="text-3xl font-bold tracking-tight">Blog Settings</h1>
            <p className="text-muted-foreground">
              Configure blog display and behavior options
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
          <CardTitle>Blog Display</CardTitle>
          <CardDescription>Configure how blog posts are displayed</CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="grid gap-4 md:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor="postsPerPage">Posts Per Page</Label>
              <Select
                value={settings.postsPerPage}
                onValueChange={(value) => setSettings({ ...settings, postsPerPage: value })}
              >
                <SelectTrigger>
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="6">6 posts</SelectItem>
                  <SelectItem value="9">9 posts</SelectItem>
                  <SelectItem value="10">10 posts</SelectItem>
                  <SelectItem value="12">12 posts</SelectItem>
                  <SelectItem value="15">15 posts</SelectItem>
                  <SelectItem value="20">20 posts</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div className="space-y-2">
              <Label htmlFor="excerptLength">Excerpt Length (characters)</Label>
              <Input
                id="excerptLength"
                type="number"
                value={settings.excerptLength}
                onChange={(e) => setSettings({ ...settings, excerptLength: e.target.value })}
              />
            </div>
          </div>

          <Separator />

          <div className="space-y-4">
            <div className="flex items-center justify-between">
              <div className="space-y-0.5">
                <Label>Show Author</Label>
                <p className="text-sm text-muted-foreground">Display author information on blog posts</p>
              </div>
              <Switch
                checked={settings.showAuthor}
                onCheckedChange={(checked) => setSettings({ ...settings, showAuthor: checked })}
              />
            </div>

            <div className="flex items-center justify-between">
              <div className="space-y-0.5">
                <Label>Show Reading Time</Label>
                <p className="text-sm text-muted-foreground">Display estimated reading time</p>
              </div>
              <Switch
                checked={settings.showReadingTime}
                onCheckedChange={(checked) => setSettings({ ...settings, showReadingTime: checked })}
              />
            </div>

            <div className="flex items-center justify-between">
              <div className="space-y-0.5">
                <Label>Show Related Posts</Label>
                <p className="text-sm text-muted-foreground">Display related articles at the end of posts</p>
              </div>
              <Switch
                checked={settings.showRelatedPosts}
                onCheckedChange={(checked) => setSettings({ ...settings, showRelatedPosts: checked })}
              />
            </div>

            {settings.showRelatedPosts && (
              <div className="pl-6 space-y-2">
                <Label>Number of Related Posts</Label>
                <Select
                  value={settings.relatedPostsCount}
                  onValueChange={(value) => setSettings({ ...settings, relatedPostsCount: value })}
                >
                  <SelectTrigger className="w-32">
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="2">2</SelectItem>
                    <SelectItem value="3">3</SelectItem>
                    <SelectItem value="4">4</SelectItem>
                    <SelectItem value="5">5</SelectItem>
                    <SelectItem value="6">6</SelectItem>
                  </SelectContent>
                </Select>
              </div>
            )}

            <div className="flex items-center justify-between">
              <div className="space-y-0.5">
                <Label>Enable Social Share</Label>
                <p className="text-sm text-muted-foreground">Allow readers to share posts on social media</p>
              </div>
              <Switch
                checked={settings.enableSocialShare}
                onCheckedChange={(checked) => setSettings({ ...settings, enableSocialShare: checked })}
              />
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Section Spacing Defaults</CardTitle>
          <CardDescription>Default spacing for blog post sections (can be overridden per post)</CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="space-y-2">
            <Label htmlFor="defaultSectionSpacing">Default Space Between Heading & Content</Label>
            <Select
              value={settings.defaultSectionSpacing}
              onValueChange={(value) => setSettings({ ...settings, defaultSectionSpacing: value })}
            >
              <SelectTrigger className="w-64">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="0">None (0px)</SelectItem>
                <SelectItem value="2">Small (8px)</SelectItem>
                <SelectItem value="4">Medium (16px)</SelectItem>
                <SelectItem value="6">Large (24px)</SelectItem>
                <SelectItem value="8">XLarge (32px)</SelectItem>
                <SelectItem value="12">XXLarge (48px)</SelectItem>
                <SelectItem value="16">Huge (64px)</SelectItem>
              </SelectContent>
            </Select>
            <p className="text-sm text-muted-foreground">
              This spacing will be applied by default when creating new blog posts. 
              You can customize it for individual posts in the post editor.
            </p>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Comments</CardTitle>
          <CardDescription>Manage blog comment settings</CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label>Enable Comments</Label>
              <p className="text-sm text-muted-foreground">Allow visitors to comment on posts</p>
            </div>
            <Switch
              checked={settings.enableComments}
              onCheckedChange={(checked) => setSettings({ ...settings, enableComments: checked })}
            />
          </div>

          {settings.enableComments && (
            <div className="flex items-center justify-between pl-6">
              <div className="space-y-0.5">
                <Label>Require Approval</Label>
                <p className="text-sm text-muted-foreground">Comments must be approved before publishing</p>
              </div>
              <Switch
                checked={settings.requireApproval}
                onCheckedChange={(checked) => setSettings({ ...settings, requireApproval: checked })}
              />
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  )
}
