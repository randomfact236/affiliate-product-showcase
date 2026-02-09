import type { Metadata } from "next"
import { Inter } from "next/font/google"
import { Navbar } from "@/components/layout/navbar"
import { Footer } from "@/components/layout/footer"
import { MobileFooterNav } from "@/components/layout/mobile-footer-nav"
import { Toaster } from "@/components/ui/toaster"
import { ConnectionRecovery } from "@/components/connection-recovery"
import Providers from "./providers"
import "./globals.css"

const inter = Inter({
  subsets: ["latin"],
  variable: "--font-inter",
})

export const metadata: Metadata = {
  title: {
    default: "Affiliate Showcase - Discover Amazing Products",
    template: "%s | Affiliate Showcase",
  },
  description:
    "Discover the best deals and products from top affiliate partners. Quality recommendations you can trust.",
  keywords: ["affiliate", "products", "deals", "shopping", "recommendations"],
  metadataBase: new URL(
    process.env.NEXT_PUBLIC_SITE_URL || "http://localhost:3000"
  ),
  openGraph: {
    type: "website",
    locale: "en_US",
    siteName: "Affiliate Showcase",
  },
  twitter: {
    card: "summary_large_image",
  },
  robots: {
    index: true,
    follow: true,
  },
}

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode
}>) {
  return (
    <html lang="en" className={inter.variable} suppressHydrationWarning>
      <body className="min-h-screen bg-background font-sans antialiased">
        <Providers>
          <div className="flex min-h-screen flex-col">
            <Navbar />
            <main className="flex-1 pb-16 lg:pb-0">{children}</main>
            <div className="hidden lg:block">
              <Footer />
            </div>
          </div>

          {/* Mobile Footer Navigation */}
          <MobileFooterNav />

          <Toaster />
          <ConnectionRecovery />
        </Providers>
      </body>
    </html>
  )
}
