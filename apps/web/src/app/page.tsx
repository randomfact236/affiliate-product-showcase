import { HeroBanner } from "@/components/home/hero-banner"
import { DontMissSection } from "@/components/home/dont-miss-section"
import { BlogSection } from "@/components/home/blog-section"

export default function HomePage() {
  return (
    <main className="min-h-screen bg-white">
      {/* Hero Banner - Full width carousel */}
      <HeroBanner />
      
      {/* Don't Miss Section - Tabs with More dropdown */}
      <DontMissSection />
      
      {/* Blog Section - Latest Articles */}
      <BlogSection />
    </main>
  )
}
