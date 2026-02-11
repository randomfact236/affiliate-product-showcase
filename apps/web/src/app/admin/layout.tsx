"use client"

import Link from "next/link"
import { Button } from "@/components/ui/button"
import { Store, LogOut } from "lucide-react"
import { SidebarNav } from "@/components/admin/sidebar-nav"
import { ThemeToggleSimple } from "@/components/theme-toggle"

export default function AdminLayout({
  children,
}: {
  children: React.ReactNode
}) {
  return (
    <div className="flex min-h-screen bg-muted/50">
      {/* Sidebar */}
      <SidebarNav />

      {/* Mobile Header */}
      <div className="sticky top-0 z-50 w-full border-b bg-background lg:hidden">
        <div className="flex h-16 items-center px-4">
          <Link href="/admin" className="flex items-center space-x-2">
            <Store className="h-6 w-6 text-primary" />
            <span className="text-lg font-bold">Admin</span>
          </Link>
        </div>
      </div>

      {/* Main Content */}
      <main className="flex-1 overflow-y-auto">
        <div className="container mx-auto p-4 lg:p-8">{children}</div>
      </main>
    </div>
  )
}
