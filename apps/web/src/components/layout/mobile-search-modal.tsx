"use client"

import { useState, useEffect, useRef, useMemo } from "react"
import { Input } from "@/components/ui/input"
import { Search, X, Clock, TrendingUp } from "lucide-react"
import Link from "next/link"

interface MobileSearchModalProps {
  isOpen: boolean
  onClose: () => void
}

// Mock search data with categories
const mockProducts = [
  { id: 1, name: "SEMrush", category: "SEO Tools" },
  { id: 2, name: "Ahrefs", category: "SEO Tools" },
  { id: 3, name: "Grammarly", category: "Writing" },
  { id: 4, name: "ChatGPT", category: "AI Tools" },
  { id: 5, name: "Jasper AI", category: "AI Tools" },
  { id: 6, name: "Mailchimp", category: "Marketing" },
  { id: 7, name: "HubSpot", category: "Marketing" },
  { id: 8, name: "Canva", category: "Design" },
  { id: 9, name: "Figma", category: "Design" },
  { id: 10, name: "SurferSEO", category: "SEO Tools" },
]

const popularSearches = [
  "SEO tools",
  "AI writing",
  "Email marketing",
  "Social media",
  "Analytics",
  "Keyword research",
]

const recentSearches = [
  "SEMrush",
  "Ahrefs",
  "Grammarly",
]

const categories = [
  { name: "SEO Tools", icon: "🔍", count: 28 },
  { name: "AI Tools", icon: "🤖", count: 32 },
  { name: "Marketing", icon: "📢", count: 49 },
  { name: "Design", icon: "🎨", count: 24 },
  { name: "Writing", icon: "✍️", count: 18 },
  { name: "Analytics", icon: "📊", count: 15 },
]

export function MobileSearchModal({ isOpen, onClose }: MobileSearchModalProps) {
  const [searchQuery, setSearchQuery] = useState("")
  const inputRef = useRef<HTMLInputElement>(null)

  // Filter products using useMemo
  const filteredProducts = useMemo(() => {
    if (!searchQuery.trim()) return []
    return mockProducts.filter(p =>
      p.name.toLowerCase().includes(searchQuery.toLowerCase())
    )
  }, [searchQuery])

  // Close on escape key
  useEffect(() => {
    const handleEscape = (e: KeyboardEvent) => {
      if (e.key === "Escape") onClose()
    }

    if (isOpen) {
      document.addEventListener("keydown", handleEscape)
      setTimeout(() => inputRef.current?.focus(), 100)
    }

    return () => document.removeEventListener("keydown", handleEscape)
  }, [isOpen, onClose])

  if (!isOpen) return null

  return (
    <div className="fixed inset-0 z-50 lg:hidden">
      {/* Backdrop */}
      <div
        className="absolute inset-0 bg-black/50"
        onClick={onClose}
      />

      {/* Search Container - positioned at bottom */}
      <div className="absolute bottom-16 left-0 right-0 bg-white shadow-2xl flex flex-col max-h-[80vh]">
        
        {/* Scrollable Content Area */}
        <div className="flex-1 overflow-y-auto">
          
          {/* Recent Searches - at TOP */}
          {!searchQuery.trim() && recentSearches.length > 0 && (
            <div className="p-4 border-b border-gray-100">
              <div className="flex items-center justify-between mb-3">
                <h3 className="text-xs font-semibold text-gray-500 uppercase tracking-wider flex items-center gap-2">
                  <Clock className="h-3.5 w-3.5" />
                  Recent Searches
                </h3>
                <button className="text-xs text-blue-600 hover:text-blue-700">
                  Clear
                </button>
              </div>
              <div className="space-y-2">
                {recentSearches.map((term) => (
                  <Link
                    key={term}
                    href={`/products?search=${encodeURIComponent(term)}`}
                    onClick={onClose}
                    className="flex items-center gap-3 p-3 hover:bg-gray-50 rounded-lg transition-colors"
                  >
                    <Search className="h-4 w-4 text-gray-400" />
                    <span className="text-gray-700">{term}</span>
                  </Link>
                ))}
              </div>
            </div>
          )}

          {/* Popular Searches - below recent */}
          {!searchQuery.trim() && (
            <div className="p-4 border-b border-gray-100">
              <h3 className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                <TrendingUp className="h-3.5 w-3.5" />
                Popular Searches
              </h3>
              <div className="flex flex-wrap gap-2">
                {popularSearches.map((term) => (
                  <Link
                    key={term}
                    href={`/products?search=${encodeURIComponent(term)}`}
                    onClick={onClose}
                    className="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-full text-sm font-medium transition-colors"
                  >
                    {term}
                  </Link>
                ))}
              </div>
            </div>
          )}

          {/* Categories - displayed as pills like popular searches */}
          {!searchQuery.trim() && (
            <div className="p-4 border-b border-gray-100">
              <h3 className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                Browse Categories
              </h3>
              <div className="flex flex-wrap gap-2">
                {categories.map((cat) => (
                  <Link
                    key={cat.name}
                    href={`/categories/${cat.name.toLowerCase().replace(" ", "-")}`}
                    onClick={onClose}
                    className="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-full text-sm font-medium transition-colors flex items-center gap-2"
                  >
                    <span>{cat.icon}</span>
                    <span>{cat.name}</span>
                  </Link>
                ))}
              </div>
            </div>
          )}

          {/* Matching Results - shown when typing */}
          {searchQuery.trim() && (
            <div className="p-4 border-b border-gray-100">
              <h3 className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                Matching Results
              </h3>
              <div className="space-y-2">
                {filteredProducts.length > 0 ? (
                  filteredProducts.map((product) => (
                    <Link
                      key={product.id}
                      href={`/products?search=${encodeURIComponent(product.name)}`}
                      onClick={onClose}
                      className="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition-colors"
                    >
                      <div className="flex items-center gap-3">
                        <Search className="h-4 w-4 text-gray-400" />
                        <span className="text-gray-700 font-medium">{product.name}</span>
                      </div>
                      <span className="text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded-full">
                        {product.category}
                      </span>
                    </Link>
                  ))
                ) : (
                  <div className="text-center py-4 text-gray-500">
                    No results found for &quot;{searchQuery}&quot;
                  </div>
                )}
              </div>
            </div>
          )}
        </div>

        {/* Search Input - FIXED at bottom */}
        <div className="p-4 border-t border-gray-200 bg-white sticky bottom-0">
          <div className="relative">
            <Search className="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
            <Input
              ref={inputRef}
              type="search"
              placeholder="Search tools, categories..."
              className="pl-10 pr-10 h-12 text-base"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
            />
            {searchQuery && (
              <button
                onClick={() => setSearchQuery("")}
                className="absolute right-3 top-1/2 -translate-y-1/2 p-1 hover:bg-gray-100 rounded-full"
              >
                <X className="h-4 w-4 text-gray-400" />
              </button>
            )}
          </div>
        </div>
      </div>
    </div>
  )
}
