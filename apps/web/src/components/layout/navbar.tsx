"use client"

import Link from "next/link"
import { useState } from "react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { cn } from "@/lib/utils"
import { Menu, Search, ShoppingBag } from "lucide-react"
import { MobileMenuDrawer } from "./mobile-menu-drawer"
import { ThemeToggleSimple } from "@/components/theme-toggle"

interface NavbarProps {
  className?: string
}

export function Navbar({ className }: NavbarProps) {
  const [isMenuOpen, setIsMenuOpen] = useState(false)
  const [searchQuery, setSearchQuery] = useState("")

  const navLinks = [
    { href: "/", label: "Home" },
    { href: "/blog", label: "Blog" },
    { href: "/products", label: "Products" },
    { href: "/admin", label: "Admin" },
  ]

  return (
    <>
      <header
        className={cn(
          "sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60",
          className
        )}
      >
        <div className="container mx-auto flex h-16 items-center justify-between px-4">
          {/* Left: Menu Button + Logo */}
          <div className="flex items-center">
            {/* Mobile/Tablet Menu Button */}
            <Button
              variant="ghost"
              size="icon"
              className="lg:hidden -ml-2"
              onClick={() => setIsMenuOpen(true)}
              aria-label="Open menu"
              aria-expanded={isMenuOpen}
            >
              <Menu className="h-5 w-5" />
            </Button>

            {/* Logo */}
            <Link href="/" className="flex items-center space-x-2 ml-2 lg:ml-0">
              <ShoppingBag className="h-6 w-6 text-primary" />
              <span className="hidden text-xl font-bold sm:inline-block">
                Affiliate Showcase
              </span>
            </Link>

            {/* Desktop Navigation - shows on lg and above */}
            <nav className="hidden items-center space-x-6 text-sm font-medium lg:flex ml-8">
              {navLinks.map((link) => (
                <Link
                  key={link.href}
                  href={link.href}
                  className="transition-colors hover:text-primary"
                >
                  {link.label}
                </Link>
              ))}
            </nav>
          </div>

          {/* Right: Search + Admin */}
          <div className="flex items-center gap-2">
            {/* Search - Desktop only */}
            <form
              className="relative hidden lg:block"
              onSubmit={(e) => {
                e.preventDefault()
                if (searchQuery.trim()) {
                  window.location.href = `/products?search=${encodeURIComponent(searchQuery)}`
                }
              }}
            >
              <Search className="absolute left-2.5 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <Input
                type="search"
                placeholder="Search products..."
                className="pl-8 w-64"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                aria-label="Search products"
              />
            </form>

            {/* Theme Toggle */}
            <ThemeToggleSimple />

            {/* Admin Link */}
            <Button variant="ghost" size="sm" asChild className="-mr-2 lg:mr-0">
              <Link href="/admin">Admin</Link>
            </Button>
          </div>
        </div>
      </header>

      {/* Mobile Menu Drawer - Same as footer */}
      <MobileMenuDrawer
        isOpen={isMenuOpen}
        onClose={() => setIsMenuOpen(false)}
      />
    </>
  )
}
