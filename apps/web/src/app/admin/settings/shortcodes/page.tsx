"use client"

import { useState } from "react"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Copy, Check, ArrowLeft, FileText, Package } from "lucide-react"
import Link from "next/link"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"

interface ShortcodeItem {
  feature: string
  shortcode: string
  description?: string
}

const blogShortcodes: ShortcodeItem[] = [
  { feature: "Recent Posts", shortcode: "[recent_posts]", description: "Display recent blog posts" },
  { feature: "Recent Posts (Custom)", shortcode: "[recent_posts count=\"5\" category=\"tech\"]", description: "Recent posts with filters" },
  { feature: "Featured Posts", shortcode: "[featured_posts]", description: "Display featured blog posts" },
  { feature: "Blog Categories", shortcode: "[blog_categories]", description: "Display blog categories" },
  { feature: "Blog Categories (Cloud)", shortcode: "[blog_categories style=\"cloud\"]", description: "Categories in cloud style" },
  { feature: "Search Posts", shortcode: "[search_posts]", description: "Display search form" },
  { feature: "Tag Cloud", shortcode: "[tag_cloud]", description: "Display popular tags" },
  { feature: "Author Box", shortcode: "[author_box]", description: "Display author information" },
]

const productShortcodes: ShortcodeItem[] = [
  { feature: "Products Grid", shortcode: "[products]", description: "Display products in grid" },
  { feature: "Products (Custom)", shortcode: "[products count=\"8\" category=\"tech\" featured=\"true\"]", description: "Filtered products" },
  { feature: "Featured Products", shortcode: "[featured_products]", description: "Display featured products" },
  { feature: "On Sale Products", shortcode: "[products on_sale=\"true\"]", description: "Display products on sale" },
  { feature: "Product Categories", shortcode: "[product_categories]", description: "Display product categories" },
  { feature: "Single Product", shortcode: "[product id=\"123\"]", description: "Display specific product" },
  { feature: "Product Comparison", shortcode: "[product_comparison ids=\"1,2,3\"]", description: "Compare multiple products" },
  { feature: "Price Filter", shortcode: "[price_filter]", description: "Display price range filter" },
  { feature: "Top Rated Products", shortcode: "[products sort=\"rating\"]", description: "Display top rated products" },
  { feature: "Recently Viewed", shortcode: "[recently_viewed]", description: "Display recently viewed products" },
]

const generalShortcodes: ShortcodeItem[] = [
  { feature: "Site Title", shortcode: "[site_title]", description: "Display site name" },
  { feature: "Site Description", shortcode: "[site_description]", description: "Display site tagline" },
  { feature: "Current Year", shortcode: "[current_year]", description: "Display current year" },
  { feature: "Affiliate Disclosure", shortcode: "[affiliate_disclosure]", description: "Display disclosure text" },
]

function ShortcodeRow({ item }: { item: ShortcodeItem }) {
  const [copied, setCopied] = useState(false)

  const copyToClipboard = () => {
    navigator.clipboard.writeText(item.shortcode)
    setCopied(true)
    setTimeout(() => setCopied(false), 2000)
  }

  return (
    <div className="flex items-center justify-between p-4 border-b last:border-0 hover:bg-muted/50 transition-colors">
      <div className="flex-1 min-w-0 pr-4">
        <p className="font-medium text-sm">{item.feature}</p>
        {item.description && (
          <p className="text-xs text-muted-foreground">{item.description}</p>
        )}
      </div>
      <div className="flex items-center gap-3">
        <code className="text-sm font-mono bg-muted px-3 py-1.5 rounded text-xs md:text-sm whitespace-nowrap">
          {item.shortcode}
        </code>
        <Button
          variant="ghost"
          size="icon"
          className="h-8 w-8 shrink-0"
          onClick={copyToClipboard}
        >
          {copied ? (
            <Check className="h-4 w-4 text-green-500" />
          ) : (
            <Copy className="h-4 w-4" />
          )}
        </Button>
      </div>
    </div>
  )
}

export default function ShortcodesSettingsPage() {
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
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Shortcodes</h1>
          <p className="text-muted-foreground">
            Copy and paste these shortcodes into your content
          </p>
        </div>
      </div>

      <Tabs defaultValue="blog" className="w-full">
        <TabsList className="grid w-full grid-cols-3 max-w-md">
          <TabsTrigger value="blog" className="flex items-center gap-2">
            <FileText className="h-4 w-4" />
            Blog
          </TabsTrigger>
          <TabsTrigger value="products" className="flex items-center gap-2">
            <Package className="h-4 w-4" />
            Products
          </TabsTrigger>
          <TabsTrigger value="general" className="flex items-center gap-2">
            <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            General
          </TabsTrigger>
        </TabsList>

        <TabsContent value="blog" className="mt-6">
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <FileText className="h-5 w-5" />
                Blog Shortcodes
              </CardTitle>
              <CardDescription>
                Use these shortcodes to display blog content anywhere
              </CardDescription>
            </CardHeader>
            <CardContent className="p-0">
              <div className="divide-y">
                <div className="flex items-center justify-between p-4 bg-muted font-medium text-sm">
                  <span className="flex-1">Feature</span>
                  <span className="pr-16">Shortcode</span>
                </div>
                {blogShortcodes.map((item, index) => (
                  <ShortcodeRow key={index} item={item} />
                ))}
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="products" className="mt-6">
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Package className="h-5 w-5" />
                Product Shortcodes
              </CardTitle>
              <CardDescription>
                Use these shortcodes to display products anywhere
              </CardDescription>
            </CardHeader>
            <CardContent className="p-0">
              <div className="divide-y">
                <div className="flex items-center justify-between p-4 bg-muted font-medium text-sm">
                  <span className="flex-1">Feature</span>
                  <span className="pr-16">Shortcode</span>
                </div>
                {productShortcodes.map((item, index) => (
                  <ShortcodeRow key={index} item={item} />
                ))}
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="general" className="mt-6">
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                General Shortcodes
              </CardTitle>
              <CardDescription>
                Use these shortcodes for site-wide elements
              </CardDescription>
            </CardHeader>
            <CardContent className="p-0">
              <div className="divide-y">
                <div className="flex items-center justify-between p-4 bg-muted font-medium text-sm">
                  <span className="flex-1">Feature</span>
                  <span className="pr-16">Shortcode</span>
                </div>
                {generalShortcodes.map((item, index) => (
                  <ShortcodeRow key={index} item={item} />
                ))}
              </div>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>

      {/* Usage Tips */}
      <Card>
        <CardHeader>
          <CardTitle>How to Use Shortcodes</CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="grid md:grid-cols-2 gap-4">
            <div className="space-y-2">
              <h4 className="font-medium">Basic Usage</h4>
              <p className="text-sm text-muted-foreground">
                Simply copy the shortcode and paste it into any blog post or page content.
              </p>
              <code className="block text-sm font-mono bg-muted p-2 rounded">
                [recent_posts]
              </code>
            </div>
            <div className="space-y-2">
              <h4 className="font-medium">With Parameters</h4>
              <p className="text-sm text-muted-foreground">
                Customize output by adding parameters to the shortcode.
              </p>
              <code className="block text-sm font-mono bg-muted p-2 rounded">
                [products count=&quot;6&quot; category=&quot;tech&quot;]
              </code>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  )
}
