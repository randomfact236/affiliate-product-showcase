"use client"

import Link from "next/link"
import { usePathname } from "next/navigation"
import { cn } from "@/lib/utils"
import {
  LayoutDashboard,
  Package,
  Tags,
  BarChart3,
  Settings,
  LogOut,
  Store,
  Bookmark,
  Image,
  Ribbon,
  PlusCircle,
  BookOpen,
  PenLine,
  FileText,
  FolderOpen,
  ChevronDown,
  Layers,
  Tag,
  Type,
  Palette,
  Sparkles,
} from "lucide-react"
import { useState } from "react"
import { ThemeToggleSimple } from "@/components/theme-toggle"

interface NavItem {
  href: string
  label: string
  icon: React.ElementType
  children?: NavItem[]
}

const adminNavItems: NavItem[] = [
  { href: "/admin", label: "Dashboard", icon: LayoutDashboard },
  {
    href: "/admin/blog",
    label: "Blog",
    icon: BookOpen,
    children: [
      { href: "/admin/blog", label: "All Posts", icon: FileText },
      { href: "/admin/blog/new", label: "Add New", icon: PlusCircle },
      { href: "/admin/categories", label: "Categories", icon: FolderOpen },
      { href: "/admin/tags", label: "Tags", icon: Tag },
    ],
  },
  {
    href: "/admin/products",
    label: "Products",
    icon: Package,
    children: [
      { href: "/admin/products", label: "All Products", icon: Layers },
      { href: "/admin/products/new", label: "Add New", icon: PlusCircle },
      { href: "/admin/ribbons", label: "Ribbons", icon: Sparkles },
      { href: "/admin/categories", label: "Categories", icon: FolderOpen },
      { href: "/admin/tags", label: "Tags", icon: Bookmark },
    ],
  },
  { href: "/admin/media", label: "Media Library", icon: Image },
  { href: "/admin/analytics", label: "Analytics", icon: BarChart3 },
  {
    href: "/admin/settings",
    label: "Settings",
    icon: Settings,
    children: [
      { href: "/admin/settings", label: "General", icon: LayoutDashboard },
      { href: "/admin/settings/blog", label: "Blog Settings", icon: BookOpen },
      { href: "/admin/settings/products", label: "Product Settings", icon: Package },
      { href: "/admin/settings/appearance", label: "Appearance", icon: Palette },
      { href: "/admin/settings/dont-miss", label: "Don't Miss", icon: Sparkles },
      { href: "/admin/settings/shortcodes", label: "Shortcodes", icon: Type },
    ],
  },
]

function NavItemComponent({
  item,
  depth = 0,
}: {
  item: NavItem
  depth?: number
}) {
  const pathname = usePathname()
  // All menus expanded by default
  const [isExpanded, setIsExpanded] = useState(true)

  const Icon = item.icon
  const isActive = pathname === item.href
  const hasChildren = item.children && item.children.length > 0
  const isChildActive = hasChildren
    ? item.children!.some((child) => pathname.startsWith(child.href.split('?')[0]))
    : false

  if (hasChildren) {
    return (
      <div className="space-y-1">
        <button
          onClick={() => setIsExpanded(!isExpanded)}
          className={cn(
            "flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm font-medium transition-colors",
            isChildActive
              ? "bg-primary/10 text-primary"
              : "text-muted-foreground hover:bg-muted hover:text-foreground"
          )}
        >
          <div className="flex items-center">
            <Icon className="mr-3 h-4 w-4" />
            {item.label}
          </div>
          <ChevronDown
            className={cn(
              "h-4 w-4 transition-transform duration-200",
              isExpanded && "rotate-180"
            )}
          />
        </button>
        {isExpanded && (
          <div className="ml-4 border-l pl-3 space-y-1">
            {item.children!.map((child) => (
              <NavItemComponent key={child.href} item={child} depth={depth + 1} />
            ))}
          </div>
        )}
      </div>
    )
  }

  return (
    <Link
      href={item.href}
      className={cn(
        "flex items-center rounded-lg px-3 py-2 text-sm font-medium transition-colors",
        isActive
          ? "bg-primary text-primary-foreground"
          : "text-muted-foreground hover:bg-muted hover:text-foreground",
        depth > 0 && "ml-2"
      )}
      aria-current={isActive ? "page" : undefined}
    >
      <Icon className="mr-3 h-4 w-4" />
      {item.label}
    </Link>
  )
}

export function SidebarNav() {
  return (
    <aside className="sticky top-0 hidden h-screen w-64 flex-col border-r bg-background lg:flex">
      {/* Logo */}
      <div className="flex h-16 items-center border-b px-6">
        <Link href="/admin" className="flex items-center space-x-2">
          <Store className="h-6 w-6 text-primary" />
          <span className="text-lg font-bold">Admin Panel</span>
        </Link>
      </div>

      {/* Navigation */}
      <nav className="flex-1 space-y-1 overflow-y-auto p-4">
        {adminNavItems.map((item) => (
          <NavItemComponent key={item.href} item={item} />
        ))}
      </nav>

      {/* Footer */}
      <div className="border-t p-4 space-y-3">
        <div className="flex items-center justify-between px-3">
          <span className="text-sm text-muted-foreground">Theme</span>
          <ThemeToggleSimple />
        </div>
        <div className="flex items-center gap-3 px-3">
          <div className="h-8 w-8 rounded-full bg-primary/10" />
          <div className="flex-1 overflow-hidden">
            <p className="truncate text-sm font-medium">Admin User</p>
            <p className="truncate text-xs text-muted-foreground">
              admin@example.com
            </p>
          </div>
        </div>
        <Link
          href="/"
          className="flex w-full items-center justify-center gap-2 rounded-lg border px-4 py-2 text-sm font-medium transition-colors hover:bg-muted"
        >
          <LogOut className="h-4 w-4" />
          Back to Site
        </Link>
      </div>
    </aside>
  )
}
