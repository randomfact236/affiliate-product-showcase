"use client"

import { useState, useRef } from "react"
import Link from "next/link"
import { useRouter } from "next/navigation"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Textarea } from "@/components/ui/textarea"
import { Checkbox } from "@/components/ui/checkbox"
import { Badge } from "@/components/ui/badge"
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select"
import {
  Save,
  Upload,
  X,
  Plus,
  Camera,
  Shirt,
  FileText,
  Image,
  Link as LinkIcon,
  List,
  Tag,
  BarChart3,
  DollarSign,
} from "lucide-react"

export default function AddProductPage() {
  const router = useRouter()
  const [isSubmitting, setIsSubmitting] = useState(false)
  
  // Section refs for navigation
  const infoRef = useRef<HTMLDivElement>(null)
  const imagesRef = useRef<HTMLDivElement>(null)
  const affiliateRef = useRef<HTMLDivElement>(null)
  const featuresRef = useRef<HTMLDivElement>(null)
  const pricingRef = useRef<HTMLDivElement>(null)
  const categoriesRef = useRef<HTMLDivElement>(null)
  const statisticsRef = useRef<HTMLDivElement>(null)
  
  // Form state
  const [formData, setFormData] = useState({
    name: "",
    status: "DRAFT",
    isFeatured: false,
    featuredImage: "",
    logo: "",
    affiliateUrl: "",
    buttonName: "",
    shortDescription: "",
    features: [] as string[],
    currentPrice: "",
    originalPrice: "",
    category: "",
    ribbon: "",
    rating: "4.5",
    views: "325",
    userCount: "1.5K",
    reviews: "12",
  })

  const [newFeature, setNewFeature] = useState("")
  const [wordCount, setWordCount] = useState(0)
  const [activeSection, setActiveSection] = useState("info")

  const calculateDiscount = () => {
    const current = parseFloat(formData.currentPrice)
    const original = parseFloat(formData.originalPrice)
    if (!current || !original || original <= current) return 0
    return Math.round(((original - current) / original) * 100)
  }

  const handleShortDescriptionChange = (value: string) => {
    setFormData({ ...formData, shortDescription: value })
    setWordCount(value.trim().split(/\s+/).filter(w => w.length > 0).length)
  }

  const addFeature = () => {
    if (newFeature.trim()) {
      setFormData({ ...formData, features: [...formData.features, newFeature.trim()] })
      setNewFeature("")
    }
  }

  const removeFeature = (index: number) => {
    setFormData({ ...formData, features: formData.features.filter((_, i) => i !== index) })
  }

  const scrollToSection = (ref: React.RefObject<HTMLDivElement | null>, section: string) => {
    ref.current?.scrollIntoView({ behavior: "smooth", block: "start" })
    setActiveSection(section)
  }

  const handleSubmit = async (status: "DRAFT" | "PUBLISHED") => {
    setIsSubmitting(true)
    try {
      const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/products`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ ...formData, status }),
      })
      if (response.ok) {
        router.push("/admin/products")
      }
    } catch (error) {
      console.error("Failed to create product:", error)
    } finally {
      setIsSubmitting(false)
    }
  }

  const navItems = [
    { id: "info", label: "Product Info", icon: FileText, ref: infoRef },
    { id: "images", label: "Images", icon: Image, ref: imagesRef },
    { id: "affiliate", label: "Affiliate", icon: LinkIcon, ref: affiliateRef },
    { id: "features", label: "Features", icon: List, ref: featuresRef },
    { id: "pricing", label: "Pricing", icon: DollarSign, ref: pricingRef },
    { id: "categories", label: "Categories & Tags", icon: Tag, ref: categoriesRef },
    { id: "statistics", label: "Statistics", icon: BarChart3, ref: statisticsRef },
  ]

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Sticky Header with Navigation */}
      <div className="sticky top-0 z-50 bg-white border-b shadow-sm">
        <div className="px-6 py-4">
          <div className="flex items-center justify-between max-w-7xl mx-auto">
            <h1 className="text-xl font-semibold">Add Product</h1>
            <Button variant="ghost" size="icon" asChild>
              <Link href="/admin/products">
                <X className="h-5 w-5" />
              </Link>
            </Button>
          </div>
        </div>
        
        {/* Section Navigation */}
        <div className="border-t bg-gray-50/50">
          <div className="max-w-7xl mx-auto px-6">
            <nav className="flex items-center gap-1 py-2 overflow-x-auto">
              {navItems.map((item) => (
                <button
                  key={item.id}
                  onClick={() => scrollToSection(item.ref, item.id)}
                  className={`flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-all ${
                    activeSection === item.id
                      ? "bg-blue-600 text-white shadow-md"
                      : "text-gray-600 hover:bg-gray-100 hover:text-gray-900"
                  }`}
                >
                  <item.icon className="h-4 w-4" />
                  {item.label}
                </button>
              ))}
            </nav>
          </div>
        </div>
      </div>

      {/* Main Content - All Sections */}
      <div className="max-w-7xl mx-auto px-6 py-8 space-y-8">
        
        {/* Product Info Section */}
        <section ref={infoRef} id="info" className="scroll-mt-32">
          <Card className="border-l-4 border-l-blue-500">
            <CardHeader className="bg-gray-50/50 border-b">
              <div className="flex items-center gap-3">
                <div className="p-2 bg-blue-100 rounded-lg">
                  <FileText className="h-5 w-5 text-blue-600" />
                </div>
                <CardTitle className="text-base font-semibold uppercase tracking-wide text-gray-700">
                  Product Info
                </CardTitle>
              </div>
            </CardHeader>
            <CardContent className="p-6 space-y-6">
              <div className="grid grid-cols-2 gap-6">
                <div className="space-y-2">
                  <Label htmlFor="name">
                    Product Title <span className="text-red-500">*</span>
                  </Label>
                  <Input
                    id="name"
                    placeholder="Enter product title..."
                    value={formData.name}
                    onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="status">Status</Label>
                  <Select
                    value={formData.status}
                    onValueChange={(value: string) => setFormData({ ...formData, status: value })}
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="Select status" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="DRAFT">Draft</SelectItem>
                      <SelectItem value="PUBLISHED">Published</SelectItem>
                      <SelectItem value="ARCHIVED">Archived</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>
              <div className="flex items-center space-x-2">
                <Checkbox
                  id="featured"
                  checked={formData.isFeatured}
                  onCheckedChange={(checked: boolean) =>
                    setFormData({ ...formData, isFeatured: checked })
                  }
                />
                <Label htmlFor="featured" className="font-normal cursor-pointer">
                  Featured Product
                </Label>
              </div>
            </CardContent>
          </Card>
        </section>

        {/* Images Section */}
        <section ref={imagesRef} id="images" className="scroll-mt-32">
          <Card className="border-l-4 border-l-purple-500">
            <CardHeader className="bg-gray-50/50 border-b">
              <div className="flex items-center gap-3">
                <div className="p-2 bg-purple-100 rounded-lg">
                  <Image className="h-5 w-5 text-purple-600" />
                </div>
                <CardTitle className="text-base font-semibold uppercase tracking-wide text-gray-700">
                  Product Images
                </CardTitle>
              </div>
            </CardHeader>
            <CardContent className="p-6">
              <div className="grid grid-cols-2 gap-6">
                {/* Featured Image */}
                <div className="space-y-3">
                  <Label>Product Image (Featured)</Label>
                  <div className="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-purple-400 transition-colors bg-gray-50/50">
                    <Camera className="h-12 w-12 mx-auto text-gray-400 mb-3" />
                    <p className="text-sm text-gray-500 mb-4">Click to upload or enter URL below</p>
                    <Button className="bg-blue-600 hover:bg-blue-700">
                      <Upload className="h-4 w-4 mr-2" />
                      Select from Media Library
                    </Button>
                  </div>
                  <Input
                    placeholder="https://..."
                    value={formData.featuredImage}
                    onChange={(e) => setFormData({ ...formData, featuredImage: e.target.value })}
                  />
                </div>

                {/* Logo */}
                <div className="space-y-3">
                  <Label>Logo</Label>
                  <div className="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-purple-400 transition-colors bg-gray-50/50">
                    <Shirt className="h-12 w-12 mx-auto text-gray-400 mb-3" />
                    <p className="text-sm text-gray-500 mb-4">Click to upload or enter URL below</p>
                    <Button className="bg-blue-600 hover:bg-blue-700">
                      <Upload className="h-4 w-4 mr-2" />
                      Select from Media Library
                    </Button>
                  </div>
                  <Input
                    placeholder="https://..."
                    value={formData.logo}
                    onChange={(e) => setFormData({ ...formData, logo: e.target.value })}
                  />
                </div>
              </div>
            </CardContent>
          </Card>
        </section>

        {/* Affiliate Section */}
        <section ref={affiliateRef} id="affiliate" className="scroll-mt-32">
          <Card className="border-l-4 border-l-green-500">
            <CardHeader className="bg-gray-50/50 border-b">
              <div className="flex items-center gap-3">
                <div className="p-2 bg-green-100 rounded-lg">
                  <LinkIcon className="h-5 w-5 text-green-600" />
                </div>
                <CardTitle className="text-base font-semibold uppercase tracking-wide text-gray-700">
                  Affiliate Details
                </CardTitle>
              </div>
            </CardHeader>
            <CardContent className="p-6">
              <div className="grid grid-cols-2 gap-6">
                <div className="space-y-2">
                  <Label htmlFor="affiliateUrl">Affiliate URL</Label>
                  <Input
                    id="affiliateUrl"
                    placeholder="https://example.com/..."
                    value={formData.affiliateUrl}
                    onChange={(e) => setFormData({ ...formData, affiliateUrl: e.target.value })}
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="buttonName">Button Name</Label>
                  <Input
                    id="buttonName"
                    placeholder="Buy Now"
                    value={formData.buttonName}
                    onChange={(e) => setFormData({ ...formData, buttonName: e.target.value })}
                  />
                </div>
              </div>
            </CardContent>
          </Card>
        </section>

        {/* Features Section */}
        <section ref={featuresRef} id="features" className="scroll-mt-32">
          <Card className="border-l-4 border-l-amber-500">
            <CardHeader className="bg-gray-50/50 border-b">
              <div className="flex items-center gap-3">
                <div className="p-2 bg-amber-100 rounded-lg">
                  <List className="h-5 w-5 text-amber-600" />
                </div>
                <CardTitle className="text-base font-semibold uppercase tracking-wide text-gray-700">
                  Short Description & Features
                </CardTitle>
              </div>
            </CardHeader>
            <CardContent className="p-6 space-y-6">
              {/* Short Description */}
              <div className="space-y-2">
                <Label htmlFor="shortDescription">
                  Short Description <span className="text-red-500">*</span>
                </Label>
                <Textarea
                  id="shortDescription"
                  placeholder="Enter short description (max 40 words)..."
                  rows={4}
                  value={formData.shortDescription}
                  onChange={(e) => handleShortDescriptionChange(e.target.value)}
                  className="resize-none"
                />
                <div className="text-right text-sm text-gray-500">
                  {wordCount}/40 Words
                </div>
              </div>

              {/* Feature List */}
              <div className="space-y-3 pt-4 border-t">
                <Label>Feature List</Label>
                <div className="flex gap-3">
                  <Input
                    placeholder="Add new feature..."
                    value={newFeature}
                    onChange={(e) => setNewFeature(e.target.value)}
                    onKeyDown={(e) => e.key === "Enter" && addFeature()}
                  />
                  <Button onClick={addFeature} className="bg-blue-600 hover:bg-blue-700">
                    <Plus className="h-4 w-4 mr-1" />
                    Add
                  </Button>
                </div>
                <div className="space-y-2">
                  {formData.features.map((feature, index) => (
                    <div
                      key={index}
                      className="flex items-center justify-between bg-gray-50 px-4 py-3 rounded-lg"
                    >
                      <span className="text-sm">{feature}</span>
                      <Button
                        variant="ghost"
                        size="sm"
                        onClick={() => removeFeature(index)}
                        className="text-red-500 hover:text-red-700"
                      >
                        <X className="h-4 w-4" />
                      </Button>
                    </div>
                  ))}
                </div>
              </div>
            </CardContent>
          </Card>
        </section>

        {/* Pricing Section */}
        <section ref={pricingRef} id="pricing" className="scroll-mt-32">
          <Card className="border-l-4 border-l-red-500">
            <CardHeader className="bg-gray-50/50 border-b">
              <div className="flex items-center gap-3">
                <div className="p-2 bg-red-100 rounded-lg">
                  <DollarSign className="h-5 w-5 text-red-600" />
                </div>
                <CardTitle className="text-base font-semibold uppercase tracking-wide text-gray-700">
                  Pricing
                </CardTitle>
              </div>
            </CardHeader>
            <CardContent className="p-6">
              <div className="grid grid-cols-3 gap-6">
                <div className="space-y-2">
                  <Label htmlFor="currentPrice">
                    Current Price <span className="text-red-500">*</span>
                  </Label>
                  <Input
                    id="currentPrice"
                    type="number"
                    step="0.01"
                    placeholder="30.00"
                    value={formData.currentPrice}
                    onChange={(e) => setFormData({ ...formData, currentPrice: e.target.value })}
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="originalPrice">Original Price</Label>
                  <Input
                    id="originalPrice"
                    type="number"
                    step="0.01"
                    placeholder="60.00"
                    value={formData.originalPrice}
                    onChange={(e) => setFormData({ ...formData, originalPrice: e.target.value })}
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="discount">Discount</Label>
                  <Input
                    id="discount"
                    readOnly
                    value={`${calculateDiscount()}% OFF`}
                    className="bg-gray-100 font-semibold text-green-700"
                  />
                </div>
              </div>
            </CardContent>
          </Card>
        </section>

        {/* Categories Section */}
        <section ref={categoriesRef} id="categories" className="scroll-mt-32">
          <Card className="border-l-4 border-l-indigo-500">
            <CardHeader className="bg-gray-50/50 border-b">
              <div className="flex items-center gap-3">
                <div className="p-2 bg-indigo-100 rounded-lg">
                  <Tag className="h-5 w-5 text-indigo-600" />
                </div>
                <CardTitle className="text-base font-semibold uppercase tracking-wide text-gray-700">
                  Categories & Ribbons
                </CardTitle>
              </div>
            </CardHeader>
            <CardContent className="p-6">
              <div className="grid grid-cols-2 gap-6">
                <div className="space-y-2">
                  <Label htmlFor="category">Category</Label>
                  <Select
                    value={formData.category}
                    onValueChange={(value: string) => setFormData({ ...formData, category: value })}
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="Select categories..." />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="electronics">Electronics</SelectItem>
                      <SelectItem value="computers">Computers</SelectItem>
                      <SelectItem value="accessories">Accessories</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
                <div className="space-y-2">
                  <Label htmlFor="ribbon">Ribbon Badge</Label>
                  <Select
                    value={formData.ribbon}
                    onValueChange={(value: string) => setFormData({ ...formData, ribbon: value })}
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="Select ribbons..." />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="featured">Featured</SelectItem>
                      <SelectItem value="new">New Arrival</SelectItem>
                      <SelectItem value="sale">On Sale</SelectItem>
                      <SelectItem value="bestseller">Best Seller</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>
            </CardContent>
          </Card>
        </section>

        {/* Statistics Section */}
        <section ref={statisticsRef} id="statistics" className="scroll-mt-32">
          <Card className="border-l-4 border-l-teal-500">
            <CardHeader className="bg-gray-50/50 border-b">
              <div className="flex items-center gap-3">
                <div className="p-2 bg-teal-100 rounded-lg">
                  <BarChart3 className="h-5 w-5 text-teal-600" />
                </div>
                <CardTitle className="text-base font-semibold uppercase tracking-wide text-gray-700">
                  Product Statistics
                </CardTitle>
              </div>
            </CardHeader>
            <CardContent className="p-6 space-y-6">
              <div className="grid grid-cols-3 gap-6">
                <div className="space-y-2">
                  <Label htmlFor="rating">Rating</Label>
                  <Input
                    id="rating"
                    placeholder="4.5"
                    value={formData.rating}
                    onChange={(e) => setFormData({ ...formData, rating: e.target.value })}
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="views">Views</Label>
                  <Input
                    id="views"
                    placeholder="325"
                    value={formData.views}
                    onChange={(e) => setFormData({ ...formData, views: e.target.value })}
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="userCount">User Count</Label>
                  <Input
                    id="userCount"
                    placeholder="1.5K"
                    value={formData.userCount}
                    onChange={(e) => setFormData({ ...formData, userCount: e.target.value })}
                  />
                </div>
              </div>
              <div className="space-y-2">
                <Label htmlFor="reviews">No. of Reviews</Label>
                <Input
                  id="reviews"
                  placeholder="12"
                  value={formData.reviews}
                  onChange={(e) => setFormData({ ...formData, reviews: e.target.value })}
                />
              </div>
            </CardContent>
          </Card>
        </section>

        {/* Spacer for footer */}
        <div className="h-24" />
      </div>

      {/* Sticky Footer */}
      <div className="fixed bottom-0 left-0 right-0 bg-white border-t shadow-lg">
        <div className="max-w-7xl mx-auto px-6 py-4">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-2 text-sm text-gray-500">
              <span>Quick jump:</span>
              {navItems.map((item) => (
                <button
                  key={item.id}
                  onClick={() => scrollToSection(item.ref, item.id)}
                  className="text-blue-600 hover:underline"
                >
                  {item.label}
                </button>
              )).reduce((prev, curr, i) => (
                i === 0 ? [curr] : [...prev, <span key={`sep-${i}`} className="text-gray-300">|</span>, curr]
              ), [] as React.ReactNode[])}
            </div>
            <div className="flex items-center gap-3">
              <Button
                variant="outline"
                onClick={() => handleSubmit("DRAFT")}
                disabled={isSubmitting}
              >
                <Save className="h-4 w-4 mr-2" />
                Save Draft
              </Button>
              <Button
                className="bg-blue-600 hover:bg-blue-700"
                onClick={() => handleSubmit("PUBLISHED")}
                disabled={isSubmitting}
              >
                <Upload className="h-4 w-4 mr-2" />
                Publish Product
              </Button>
              <Button variant="destructive" asChild>
                <Link href="/admin/products">Cancel</Link>
              </Button>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}
