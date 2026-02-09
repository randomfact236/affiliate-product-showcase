"use client"

import Link from "next/link"
import { cn } from "@/lib/utils"

interface FooterProps {
  className?: string
}

export function Footer({ className }: FooterProps) {
  const currentYear = new Date().getFullYear()

  const footerLinks = {
    products: [
      { href: "/products", label: "All Products" },
      { href: "/categories", label: "Categories" },
      { href: "/products?sortBy=popularity", label: "Popular" },
      { href: "/products?sortBy=createdAt", label: "New Arrivals" },
    ],
    company: [
      { href: "/about", label: "About Us" },
      { href: "/contact", label: "Contact" },
      { href: "/privacy", label: "Privacy Policy" },
      { href: "/terms", label: "Terms of Service" },
    ],
  }

  return (
    <footer className={cn("border-t bg-muted", className)}>
      <div className="container mx-auto px-4 py-12">
        <div className="grid grid-cols-1 gap-8 md:grid-cols-4">
          {/* Brand */}
          <div className="space-y-4">
            <h3 className="text-lg font-bold">Affiliate Showcase</h3>
            <p className="text-sm text-muted-foreground">
              Discover the best products from trusted affiliate partners.
              Quality recommendations you can count on.
            </p>
          </div>

          {/* Products */}
          <div>
            <h4 className="mb-4 text-sm font-semibold uppercase tracking-wider">
              Products
            </h4>
            <ul className="space-y-2">
              {footerLinks.products.map((link) => (
                <li key={link.href}>
                  <Link
                    href={link.href}
                    className="text-sm text-muted-foreground transition-colors hover:text-foreground"
                  >
                    {link.label}
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          {/* Company */}
          <div>
            <h4 className="mb-4 text-sm font-semibold uppercase tracking-wider">
              Company
            </h4>
            <ul className="space-y-2">
              {footerLinks.company.map((link) => (
                <li key={link.href}>
                  <Link
                    href={link.href}
                    className="text-sm text-muted-foreground transition-colors hover:text-foreground"
                  >
                    {link.label}
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          {/* Admin */}
          <div>
            <h4 className="mb-4 text-sm font-semibold uppercase tracking-wider">
              Admin
            </h4>
            <ul className="space-y-2">
              <li>
                <Link
                  href="/admin"
                  className="text-sm text-muted-foreground transition-colors hover:text-foreground"
                >
                  Dashboard
                </Link>
              </li>
              <li>
                <Link
                  href="/admin/products"
                  className="text-sm text-muted-foreground transition-colors hover:text-foreground"
                >
                  Manage Products
                </Link>
              </li>
              <li>
                <Link
                  href="/admin/categories"
                  className="text-sm text-muted-foreground transition-colors hover:text-foreground"
                >
                  Manage Categories
                </Link>
              </li>
            </ul>
          </div>
        </div>

        {/* Bottom */}
        <div className="mt-12 border-t pt-8">
          <p className="text-center text-sm text-muted-foreground">
            © {currentYear} Affiliate Showcase. All rights reserved.
          </p>
        </div>
      </div>
    </footer>
  )
}
