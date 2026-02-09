"use client"

import Link from "next/link"
import { cn } from "@/lib/utils"
import { X, Home, BookOpen, Package, Phone, Shield, Settings } from "lucide-react"

interface MobileMenuDrawerProps {
  isOpen: boolean
  onClose: () => void
}

const menuItems = [
  { href: "/", label: "Home", icon: Home, description: "Discover amazing products" },
  { href: "/blog", label: "Blog", icon: BookOpen, description: "Latest articles & guides" },
  { href: "/products", label: "Products", icon: Package, description: "Browse all tools" },
]

const secondaryItems = [
  { href: "/contact", label: "Contact Us", icon: Phone },
  { href: "/privacy", label: "Privacy Policy", icon: Shield },
  { href: "/admin", label: "Admin Dashboard", icon: Settings },
]

export function MobileMenuDrawer({ isOpen, onClose }: MobileMenuDrawerProps) {
  return (
    <>
      {/* Backdrop */}
      {isOpen && (
        <div
          className="fixed inset-0 bg-black/50 z-50 lg:hidden"
          onClick={onClose}
        />
      )}

      {/* Drawer */}
      <div
        className={cn(
          "fixed top-0 right-0 bottom-0 w-80 max-w-[85vw] bg-white z-50 transform transition-transform duration-300 ease-in-out lg:hidden shadow-2xl",
          isOpen ? "translate-x-0" : "translate-x-full"
        )}
      >
        <div className="flex flex-col h-full">
          {/* Header */}
          <div className="flex items-center justify-between p-4 border-b border-gray-200">
            <div className="flex items-center gap-2">
              <div className="h-8 w-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center text-white font-bold">
                A
              </div>
              <span className="font-bold text-lg">Affiliate Showcase</span>
            </div>
            <button
              onClick={onClose}
              className="p-2 hover:bg-gray-100 rounded-full transition-colors"
              aria-label="Close menu"
            >
              <X className="h-5 w-5" />
            </button>
          </div>

          {/* Main Menu Items */}
          <div className="flex-1 overflow-y-auto py-4">
            <nav className="px-4 space-y-2">
              {menuItems.map((item) => {
                const Icon = item.icon
                return (
                  <Link
                    key={item.href}
                    href={item.href}
                    onClick={onClose}
                    className="flex items-start gap-4 p-4 rounded-xl hover:bg-gray-50 transition-colors group"
                  >
                    <div className="h-10 w-10 bg-blue-50 rounded-lg flex items-center justify-center group-hover:bg-blue-100 transition-colors">
                      <Icon className="h-5 w-5 text-blue-600" />
                    </div>
                    <div>
                      <p className="font-semibold text-gray-900">{item.label}</p>
                      <p className="text-sm text-gray-500">{item.description}</p>
                    </div>
                  </Link>
                )
              })}
            </nav>

            {/* Divider */}
            <div className="my-4 border-t border-gray-200" />

            {/* Secondary Items */}
            <nav className="px-4 space-y-1">
              {secondaryItems.map((item) => {
                const Icon = item.icon
                return (
                  <Link
                    key={item.href}
                    href={item.href}
                    onClick={onClose}
                    className="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors"
                  >
                    <Icon className="h-5 w-5 text-gray-400" />
                    <span className="text-gray-700">{item.label}</span>
                  </Link>
                )
              })}
            </nav>
          </div>

          {/* Footer */}
          <div className="p-4 border-t border-gray-200 bg-gray-50">
            <p className="text-xs text-gray-500 text-center">
              © 2024 Affiliate Showcase. All rights reserved.
            </p>
          </div>
        </div>
      </div>
    </>
  )
}
