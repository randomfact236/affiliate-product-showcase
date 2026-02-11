"use client"

import { useState } from "react"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Textarea } from "@/components/ui/textarea"
import { Switch } from "@/components/ui/switch"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Separator } from "@/components/ui/separator"
import { Save, Package } from "lucide-react"
import Link from "next/link"
import { ArrowLeft } from "lucide-react"

export default function ProductSettingsPage() {
  const [saving, setSaving] = useState(false)
  const [settings, setSettings] = useState({
    productsPerPage: "12",
    showPrice: true,
    showComparePrice: true,
    showRating: true,
    showBadges: true,
    showStockStatus: true,
    enableReviews: true,
    requireReviewApproval: true,
    defaultCurrency: "USD",
    currencySymbol: "$",
    affiliateDisclosure: "This post contains affiliate links. We may earn a commission if you make a purchase.",
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
            <h1 className="text-3xl font-bold tracking-tight">Product Settings</h1>
            <p className="text-muted-foreground">
              Configure product display and affiliate options
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
          <CardTitle>Product Display</CardTitle>
          <CardDescription>Configure how products are displayed</CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="grid gap-4 md:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor="productsPerPage">Products Per Page</Label>
              <Select
                value={settings.productsPerPage}
                onValueChange={(value) => setSettings({ ...settings, productsPerPage: value })}
              >
                <SelectTrigger>
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="8">8 products</SelectItem>
                  <SelectItem value="12">12 products</SelectItem>
                  <SelectItem value="16">16 products</SelectItem>
                  <SelectItem value="20">20 products</SelectItem>
                  <SelectItem value="24">24 products</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div className="space-y-2">
              <Label htmlFor="defaultCurrency">Default Currency</Label>
              <Select
                value={settings.defaultCurrency}
                onValueChange={(value) => setSettings({ ...settings, defaultCurrency: value })}
              >
                <SelectTrigger>
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="USD">USD - US Dollar</SelectItem>
                  <SelectItem value="EUR">EUR - Euro</SelectItem>
                  <SelectItem value="GBP">GBP - British Pound</SelectItem>
                  <SelectItem value="JPY">JPY - Japanese Yen</SelectItem>
                  <SelectItem value="CAD">CAD - Canadian Dollar</SelectItem>
                  <SelectItem value="AUD">AUD - Australian Dollar</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          <div className="grid gap-4 md:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor="currencySymbol">Currency Symbol</Label>
              <Input
                id="currencySymbol"
                value={settings.currencySymbol}
                onChange={(e) => setSettings({ ...settings, currencySymbol: e.target.value })}
              />
            </div>
          </div>

          <Separator />

          <div className="space-y-4">
            <div className="flex items-center justify-between">
              <div className="space-y-0.5">
                <Label>Show Price</Label>
                <p className="text-sm text-muted-foreground">Display product prices</p>
              </div>
              <Switch
                checked={settings.showPrice}
                onCheckedChange={(checked) => setSettings({ ...settings, showPrice: checked })}
              />
            </div>

            <div className="flex items-center justify-between">
              <div className="space-y-0.5">
                <Label>Show Compare Price</Label>
                <p className="text-sm text-muted-foreground">Show original price with discount</p>
              </div>
              <Switch
                checked={settings.showComparePrice}
                onCheckedChange={(checked) => setSettings({ ...settings, showComparePrice: checked })}
              />
            </div>

            <div className="flex items-center justify-between">
              <div className="space-y-0.5">
                <Label>Show Rating</Label>
                <p className="text-sm text-muted-foreground">Display product ratings and reviews</p>
              </div>
              <Switch
                checked={settings.showRating}
                onCheckedChange={(checked) => setSettings({ ...settings, showRating: checked })}
              />
            </div>

            <div className="flex items-center justify-between">
              <div className="space-y-0.5">
                <Label>Show Badges</Label>
                <p className="text-sm text-muted-foreground">Display product badges (Featured, Sale, etc.)</p>
              </div>
              <Switch
                checked={settings.showBadges}
                onCheckedChange={(checked) => setSettings({ ...settings, showBadges: checked })}
              />
            </div>

            <div className="flex items-center justify-between">
              <div className="space-y-0.5">
                <Label>Show Stock Status</Label>
                <p className="text-sm text-muted-foreground">Display product availability</p>
              </div>
              <Switch
                checked={settings.showStockStatus}
                onCheckedChange={(checked) => setSettings({ ...settings, showStockStatus: checked })}
              />
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Reviews</CardTitle>
          <CardDescription>Manage product review settings</CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="flex items-center justify-between">
            <div className="space-y-0.5">
              <Label>Enable Reviews</Label>
              <p className="text-sm text-muted-foreground">Allow customers to leave reviews</p>
            </div>
            <Switch
              checked={settings.enableReviews}
              onCheckedChange={(checked) => setSettings({ ...settings, enableReviews: checked })}
            />
          </div>

          {settings.enableReviews && (
            <div className="flex items-center justify-between pl-6">
              <div className="space-y-0.5">
                <Label>Require Approval</Label>
                <p className="text-sm text-muted-foreground">Reviews must be approved before publishing</p>
              </div>
              <Switch
                checked={settings.requireReviewApproval}
                onCheckedChange={(checked) => setSettings({ ...settings, requireReviewApproval: checked })}
              />
            </div>
          )}
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Affiliate Settings</CardTitle>
          <CardDescription>Configure affiliate marketing settings</CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="space-y-2">
            <Label htmlFor="affiliateDisclosure">Affiliate Disclosure</Label>
            <Textarea
              id="affiliateDisclosure"
              value={settings.affiliateDisclosure}
              onChange={(e) => setSettings({ ...settings, affiliateDisclosure: e.target.value })}
              rows={3}
            />
            <p className="text-sm text-muted-foreground">
              This text will be displayed on pages with affiliate links
            </p>
          </div>
        </CardContent>
      </Card>
    </div>
  )
}
