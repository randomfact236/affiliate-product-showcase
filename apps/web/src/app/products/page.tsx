"use client"

import { useState } from "react"
import Link from "next/link"
import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select"
import { MobileFilterDrawer } from "@/components/layout/mobile-filter-drawer"
import { Star, Check, Users, ExternalLink } from "lucide-react"
import { cn } from "@/lib/utils"

// Mock categories with icons
const categories = [
  { id: "all", name: "All", icon: "🛠️" },
  { id: "hosting", name: "Hosting", icon: "🖥️" },
  { id: "ai", name: "AI", icon: "🤖" },
  { id: "seo", name: "SEO", icon: "🔍" },
  { id: "marketing", name: "Marketing", icon: "📢" },
  { id: "writing", name: "Writing", icon: "✍️" },
  { id: "design", name: "Design", icon: "🎨" },
  { id: "productivity", name: "Productivity", icon: "⚡" },
]

// Mock tags
const tags = [
  { id: "featured", name: "Featured", icon: "⭐" },
  { id: "free-trial", name: "Free Trial", icon: "🆓" },
  { id: "verified", name: "Verified", icon: "✅" },
  { id: "popular", name: "Popular", icon: "🔥" },
  { id: "new", name: "New", icon: "🆕" },
  { id: "discount", name: "Discount", icon: "🏷️" },
]

// Mock product data
const mockProducts = [
  {
    id: "1",
    name: "SEMrush Pro",
    description: "The most accurate difficulty score in the industry. Find low-competition keywords and spy on competitors' traffic sources easily.",
    originalPrice: 229.95,
    currentPrice: 119,
    discount: 48,
    rating: 5,
    reviewCount: 3421,
    userCount: "10M+",
    features: ["Keyword Research", "Competitor Analysis", "Site Audit", "Traffic Analytics"],
    badges: [
      { name: "Featured", icon: "⭐", color: "bg-yellow-100 text-yellow-700" },
      { name: "SEO", icon: "🔍", color: "bg-blue-100 text-blue-700" },
      { name: "Writing", icon: "✍️", color: "bg-orange-100 text-orange-700" },
      { name: "Analytics", icon: "📊", color: "bg-purple-100 text-purple-700" },
      { name: "Verified", icon: "✅", color: "bg-green-100 text-green-700" },
    ],
    ctaText: "Claim Discount",
    trialText: "14-day free trial available",
    featured: true,
  },
  {
    id: "2",
    name: "Ahrefs Standard",
    description: "Comprehensive SEO toolset with backlink analysis, keyword research, and rank tracking capabilities.",
    originalPrice: 199,
    currentPrice: 99,
    discount: 50,
    rating: 4.8,
    reviewCount: 2156,
    userCount: "5M+",
    features: ["Backlink Analysis", "Keyword Explorer", "Site Explorer", "Content Gap"],
    badges: [
      { name: "SEO", icon: "🔍", color: "bg-blue-100 text-blue-700" },
      { name: "Analytics", icon: "📊", color: "bg-purple-100 text-purple-700" },
      { name: "Verified", icon: "✅", color: "bg-green-100 text-green-700" },
    ],
    ctaText: "Claim Discount",
    trialText: "7-day free trial available",
    featured: false,
  },
  {
    id: "3",
    name: "Surfer SEO",
    description: "Content optimization platform that helps you create SEO-optimized content with data-driven guidelines.",
    originalPrice: 129,
    currentPrice: 69,
    discount: 46,
    rating: 4.7,
    reviewCount: 1834,
    userCount: "2M+",
    features: ["Content Editor", "SERP Analysis", "Keyword Research", "Content Audit"],
    badges: [
      { name: "Featured", icon: "⭐", color: "bg-yellow-100 text-yellow-700" },
      { name: "Writing", icon: "✍️", color: "bg-orange-100 text-orange-700" },
      { name: "SEO", icon: "🔍", color: "bg-blue-100 text-blue-700" },
    ],
    ctaText: "Claim Discount",
    trialText: "7-day free trial available",
    featured: true,
  },
  {
    id: "4",
    name: "Grammarly Premium",
    description: "AI-powered writing assistant that helps you eliminate errors and find the perfect words.",
    originalPrice: 29.95,
    currentPrice: 12,
    discount: 60,
    rating: 4.6,
    reviewCount: 5234,
    userCount: "30M+",
    features: ["Grammar Check", "Plagiarism Detection", "Tone Detector", "Word Choice"],
    badges: [
      { name: "Writing", icon: "✍️", color: "bg-orange-100 text-orange-700" },
      { name: "AI", icon: "🤖", color: "bg-indigo-100 text-indigo-700" },
      { name: "Verified", icon: "✅", color: "bg-green-100 text-green-700" },
    ],
    ctaText: "Claim Discount",
    trialText: "Free plan available",
    featured: false,
  },
  {
    id: "5",
    name: "Canva Pro",
    description: "Design tool for creating social media graphics, presentations, posters and other visual content.",
    originalPrice: 12.99,
    currentPrice: 6.49,
    discount: 50,
    rating: 4.8,
    reviewCount: 8912,
    userCount: "100M+",
    features: ["Templates", "Brand Kit", "Background Remover", "Animation"],
    badges: [
      { name: "Design", icon: "🎨", color: "bg-pink-100 text-pink-700" },
      { name: "Featured", icon: "⭐", color: "bg-yellow-100 text-yellow-700" },
    ],
    ctaText: "Claim Discount",
    trialText: "30-day free trial",
    featured: true,
  },
  {
    id: "6",
    name: "Notion AI",
    description: "All-in-one workspace with AI writing assistance, project management, and team collaboration.",
    originalPrice: 20,
    currentPrice: 8,
    discount: 60,
    rating: 4.7,
    reviewCount: 3241,
    userCount: "20M+",
    features: ["AI Writing", "Database", "Wiki", "Project Management"],
    badges: [
      { name: "AI", icon: "🤖", color: "bg-indigo-100 text-indigo-700" },
      { name: "Productivity", icon: "📈", color: "bg-teal-100 text-teal-700" },
    ],
    ctaText: "Claim Discount",
    trialText: "Free personal use",
    featured: false,
  },
]

export default function ProductsPage() {
  const [selectedCategory, setSelectedCategory] = useState("all")
  const [selectedTags, setSelectedTags] = useState<string[]>([])
  const [searchQuery, setSearchQuery] = useState("")
  const [sortBy, setSortBy] = useState("featured")
  const [isFilterOpen, setIsFilterOpen] = useState(false)

  const toggleTag = (tagId: string) => {
    setSelectedTags(prev =>
      prev.includes(tagId)
        ? prev.filter(t => t !== tagId)
        : [...prev, tagId]
    )
  }

  const clearFilters = () => {
    setSelectedCategory("all")
    setSelectedTags([])
    setSearchQuery("")
  }

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Sticky Header - All Tools, Clear All & Sort */}
      <div className="sticky top-16 z-30 bg-gray-50 border-b border-gray-200">
        <div className="container mx-auto px-4 py-4">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-4">
              <h1 className="text-2xl font-bold text-gray-900">All Tools</h1>
              {/* Clear All - shown when filters are active */}
              {(selectedCategory !== "all" || selectedTags.length > 0) && (
                <button
                  onClick={clearFilters}
                  className="text-sm text-blue-600 hover:text-blue-700 font-medium"
                >
                  Clear All
                </button>
              )}
            </div>
            <div className="flex items-center gap-2">
              <span className="text-sm text-gray-500">Sort by</span>
              <Select value={sortBy} onValueChange={setSortBy}>
                <SelectTrigger className="w-40 bg-white">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="featured">Featured</SelectItem>
                  <SelectItem value="all">All</SelectItem>
                  <SelectItem value="latest">Latest</SelectItem>
                  <SelectItem value="oldest">Oldest</SelectItem>
                  <SelectItem value="random">Random</SelectItem>
                  <SelectItem value="popularity">Popularity</SelectItem>
                  <SelectItem value="rating">Rating</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>
        </div>
      </div>

      <div className="container mx-auto px-4 py-6">
        <div className="flex gap-6">
          {/* Left Sidebar - Hidden on mobile/tablet, visible on lg+ */}
          <aside className="hidden lg:block w-64 flex-shrink-0">
            <div className="sticky top-36 w-64">
              {/* Desktop Sidebar Content */}
              <div className="space-y-6">
                {/* Filter Header with Clear All */}
                <div className="flex items-center justify-between">
                  <h4 className="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    Filter
                  </h4>
                  {(selectedCategory !== "all" || selectedTags.length > 0) && (
                    <button
                      onClick={clearFilters}
                      className="text-xs text-blue-600 hover:text-blue-700 font-medium"
                    >
                      Clear All
                    </button>
                  )}
                </div>

                {/* Categories */}
                <div>
                  <h4 className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                    Category
                  </h4>
                  <div className="flex flex-wrap gap-2">
                    {categories.map((category) => (
                      <button
                        key={category.id}
                        onClick={() => setSelectedCategory(category.id)}
                        className={cn(
                          "inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-medium transition-colors",
                          selectedCategory === category.id
                            ? "bg-blue-100 text-blue-700 border border-blue-200"
                            : "bg-white text-gray-700 border border-gray-200 hover:bg-gray-50"
                        )}
                      >
                        <span>{category.icon}</span>
                        <span>{category.name}</span>
                      </button>
                    ))}
                  </div>
                </div>

                {/* Tags */}
                <div>
                  <h4 className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                    Tags
                  </h4>
                  <div className="flex flex-wrap gap-2">
                    {tags.map((tag) => (
                      <button
                        key={tag.id}
                        onClick={() => toggleTag(tag.id)}
                        className={cn(
                          "inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-medium transition-colors",
                          selectedTags.includes(tag.id)
                            ? "bg-blue-100 text-blue-700 border border-blue-200"
                            : "bg-white text-gray-700 border border-gray-200 hover:bg-gray-50"
                        )}
                      >
                        <span>{tag.icon}</span>
                        <span>{tag.name}</span>
                      </button>
                    ))}
                  </div>
                </div>
              </div>
            </div>
          </aside>

          {/* Main Content - Product Grid */}
          <main className="flex-1 min-w-0">
            {/* Product Cards - 3 Column Grid */}
            <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
              {mockProducts.map((product) => (
                <div key={product.id} className="relative">
                  {/* Featured Badge - Above the card, aligned left */}
                  {product.featured && (
                    <div className="absolute -top-3 left-4 z-10">
                      <div className="inline-flex items-center gap-1 px-3 py-1 bg-blue-600 text-white text-xs font-bold rounded-full shadow-md">
                        <Star className="h-3 w-3 fill-current" />
                        FEATURED
                      </div>
                    </div>
                  )}

                  <Card className="overflow-hidden border border-gray-200 shadow-sm hover:shadow-lg transition-shadow flex flex-col">
                  {/* Image Container */}
                  <div className="relative aspect-[16/10] overflow-hidden bg-gradient-to-br from-blue-400 via-cyan-400 to-teal-400">
                    {/* Product Preview Text */}
                    <div className="absolute inset-0 flex items-center justify-center">
                      <span className="text-white/80 text-sm font-medium">Preview</span>
                    </div>
                  </div>

                  <CardContent className="p-5 flex flex-col flex-1">
                    {/* Header: Grid layout for proper alignment */}
                    <div className="grid grid-cols-[1fr_auto] gap-4 mb-3 items-center">
                      {/* Left Column: Logo + Name */}
                      <div className="flex items-center gap-2 min-w-0">
                        <div className="w-8 h-8 bg-orange-500 rounded flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                          {product.name.charAt(0)}
                        </div>
                        <h2 className="text-base font-bold text-gray-900 truncate leading-tight">
                          {product.name}
                        </h2>
                      </div>
                      
                      {/* Right Column: Original Price + Current Price + Discount (Vertical) */}
                      <div className="text-right flex flex-col items-end">
                        {/* Original Price - Top */}
                        <span className="text-xs text-gray-400 line-through">
                          ${product.originalPrice}/mo
                        </span>
                        {/* Current Price - Middle */}
                        <div className="flex items-baseline gap-1">
                          <span className="text-xl font-bold text-gray-900">
                            ${product.currentPrice}
                          </span>
                          <span className="text-xs text-gray-500">/mo</span>
                        </div>
                        {/* Discount - Bottom */}
                        <span className="text-xs font-medium text-green-600 bg-green-50 px-2 py-0.5 rounded mt-0.5">
                          {product.discount}% OFF
                        </span>
                      </div>
                    </div>

                    {/* Description */}
                    <p className="text-sm text-gray-600 line-clamp-2 mb-3">
                      {product.description}
                    </p>

                    {/* Featured Tag */}
                    {product.featured && (
                      <div className="flex items-center gap-1 text-amber-500 text-sm mb-3">
                        <Star className="h-4 w-4 fill-current" />
                        <span className="font-medium">Featured</span>
                      </div>
                    )}

                    {/* Feature List - 2 Column Grid with checkmarks */}
                    <div className="grid grid-cols-2 gap-x-4 gap-y-2 mb-4">
                      {product.features.slice(0, 4).map((feature, idx) => (
                        <div key={idx} className="flex items-center gap-2">
                          <Check className="h-4 w-4 text-green-500 flex-shrink-0" />
                          <span className="text-sm text-gray-700 truncate">{feature}</span>
                        </div>
                      ))}
                    </div>

                    {/* Reviews Section */}
                    <div className="flex items-center gap-2 mb-4 pt-3 border-t border-gray-100">
                      {/* Star Rating + Number (close together) */}
                      <div className="flex items-center gap-1">
                        <div className="flex">
                          {[...Array(5)].map((_, i) => (
                            <Star
                              key={i}
                              className={cn(
                                "h-3.5 w-3.5",
                                i < Math.floor(product.rating)
                                  ? "text-amber-400 fill-amber-400"
                                  : i === Math.floor(product.rating) && product.rating % 1 >= 0.5
                                  ? "text-amber-400 fill-amber-400/50"
                                  : "text-gray-300"
                              )}
                            />
                          ))}
                        </div>
                        {/* Rating Number only (no /5) */}
                        <span className="text-sm font-bold text-gray-900 ml-0.5">{product.rating}</span>
                      </div>
                      
                      {/* Review Count */}
                      <span className="text-sm text-gray-500">
                        {product.reviewCount.toLocaleString()} reviews
                      </span>

                      {/* Users Badge */}
                      <div className="flex items-center gap-1 ml-auto text-red-500">
                        <Users className="h-4 w-4" />
                        <span className="text-xs font-medium">{product.userCount} users</span>
                      </div>
                    </div>

                    {/* CTA Button */}
                    <div className="mt-auto space-y-2">
                      <Button
                        className="w-full bg-gray-900 hover:bg-gray-800 text-white font-semibold py-2.5 h-auto text-sm flex items-center justify-center gap-2"
                        asChild
                      >
                        <Link href={`/products/${product.id}`}>
                          {product.ctaText}
                          <ExternalLink className="h-4 w-4" />
                        </Link>
                      </Button>
                      {product.trialText && (
                        <p className="text-center text-xs text-gray-500">
                          {product.trialText}
                        </p>
                      )}
                    </div>
                  </CardContent>
                </Card>
              </div>
              ))}
            </div>
          </main>
        </div>
      </div>

      {/* Mobile Filter Drawer */}
      <MobileFilterDrawer
        isOpen={isFilterOpen}
        onClose={() => setIsFilterOpen(false)}
        categories={categories}
        tags={tags}
        selectedCategory={selectedCategory}
        selectedTags={selectedTags}
        searchQuery={searchQuery}
        onCategorySelect={setSelectedCategory}
        onTagToggle={toggleTag}
        onSearchChange={setSearchQuery}
        onClearFilters={clearFilters}
      />
    </div>
  )
}
