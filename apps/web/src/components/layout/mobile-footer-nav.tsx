"use client"

import { useState } from "react"
import Link from "next/link"
import { usePathname } from "next/navigation"
import { cn } from "@/lib/utils"
import { Home, BookOpen, Search, SlidersHorizontal, Menu } from "lucide-react"
import { MobileMenuDrawer } from "./mobile-menu-drawer"
import { MobileSearchModal } from "./mobile-search-modal"
import { MobileFilterDrawer } from "./mobile-filter-drawer"

// Mock data for filter drawer
const categories = [
  { id: "all", name: "All", icon: "🛠️" },
  { id: "hosting", name: "Hosting", icon: "🖥️" },
  { id: "ai", name: "AI", icon: "🤖" },
  { id: "seo", name: "SEO", icon: "🔍" },
  { id: "marketing", name: "Marketing", icon: "📢" },
]

const tags = [
  { id: "featured", name: "Featured", icon: "⭐" },
  { id: "free-trial", name: "Free Trial", icon: "🆓" },
  { id: "verified", name: "Verified", icon: "✅" },
]

export function MobileFooterNav() {
  const pathname = usePathname()
  const [isMenuOpen, setIsMenuOpen] = useState(false)
  const [isSearchOpen, setIsSearchOpen] = useState(false)
  const [isFilterOpen, setIsFilterOpen] = useState(false)
  const [selectedCategory, setSelectedCategory] = useState("all")
  const [selectedTags, setSelectedTags] = useState<string[]>([])
  const [searchQuery, setSearchQuery] = useState("")

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

  const navItems = [
    { href: "/", label: "Home", icon: Home },
    { href: "/blog", label: "Blog", icon: BookOpen },
    { href: "#", label: "Search", icon: Search, onClick: () => setIsSearchOpen(true) },
    { href: "#", label: "Filter", icon: SlidersHorizontal, onClick: () => setIsFilterOpen(true) },
    { href: "#", label: "Menu", icon: Menu, onClick: () => setIsMenuOpen(true) },
  ]

  return (
    <>
      <nav className="fixed bottom-0 left-0 right-0 z-50 bg-white border-t border-gray-200 lg:hidden">
        <div className="flex items-center justify-around h-16">
          {navItems.map((item) => {
            const Icon = item.icon
            const isActive = pathname === item.href && item.href !== "#"

            const handleClick = (e: React.MouseEvent) => {
              if (item.onClick) {
                e.preventDefault()
                item.onClick()
              }
            }

            return (
              <Link
                key={item.label}
                href={item.href}
                onClick={handleClick}
                className={cn(
                  "flex flex-col items-center justify-center flex-1 h-full gap-1",
                  isActive ? "text-blue-600" : "text-gray-500 hover:text-gray-700"
                )}
              >
                <Icon className="h-5 w-5" />
                <span className="text-xs font-medium">{item.label}</span>
              </Link>
            )
          })}
        </div>
      </nav>

      {/* Mobile Drawers/Modals */}
      <MobileMenuDrawer
        isOpen={isMenuOpen}
        onClose={() => setIsMenuOpen(false)}
      />
      <MobileSearchModal
        isOpen={isSearchOpen}
        onClose={() => setIsSearchOpen(false)}
      />
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
    </>
  )
}
