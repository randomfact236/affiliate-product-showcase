"use client"

import Image from "next/image"
import Link from "next/link"
import { useParams } from "next/navigation"
import { Clock, MessageCircle, ArrowLeft, Share2, Bookmark, Facebook, Twitter, Linkedin, Copy, Check } from "lucide-react"
import { Button } from "@/components/ui/button"
import { useState } from "react"

interface BlogPost {
  id: string
  title: string
  excerpt: string
  image: string
  category: string
  categoryColor: string
  author: string
  authorImage: string
  authorBio: string
  date: string
  readTime: string
  comments: number
  content: React.ReactNode
  tableOfContents: { id: string; title: string }[]
}

const blogPosts: Record<string, BlogPost> = {
  "best-web-hosting-providers-2024": {
    id: "1",
    title: "Best Web Hosting Providers for 2024: The Complete Guide",
    excerpt: "Discover the top hosting services with excellent uptime, speed, and customer support for your website needs. Our comprehensive comparison helps you make the right choice.",
    image: "https://images.unsplash.com/photo-1544197150-b99a580bb7a8?w=1200&h=600&fit=crop",
    category: "Hosting",
    categoryColor: "bg-blue-600",
    author: "Sarah Johnson",
    authorImage: "https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100&h=100&fit=crop",
    authorBio: "Sarah is a web hosting expert with over 10 years of experience helping businesses find the perfect hosting solution. She's tested 50+ hosting providers.",
    date: "Dec 15, 2024",
    readTime: "12 min read",
    comments: 47,
    tableOfContents: [
      { id: "why-hosting-matters", title: "Why Web Hosting Matters" },
      { id: "top-picks", title: "Our Top 5 Picks" },
      { id: "comparison", title: "Performance Comparison" },
      { id: "how-to-choose", title: "How to Choose" },
      { id: "security", title: "Security Features" },
      { id: "conclusion", title: "Final Recommendations" },
    ],
    content: null as any, // Will be rendered as JSX
  },
}

function BlogContent() {
  return (
    <>
      <p className="text-xl text-gray-600 leading-relaxed mb-8">
        Choosing the right web hosting provider is one of the most important decisions you&apos;ll make for your website. 
        With countless options available, finding the perfect balance of performance, reliability, and value can be overwhelming. 
        We&apos;ve tested and analyzed the top hosting providers to bring you this comprehensive guide.
      </p>

      <div id="why-hosting-matters" className="scroll-mt-24">
        <h2 className="text-3xl font-bold text-gray-900 mt-12 mb-6">Why Web Hosting Matters</h2>
        <p className="text-gray-600 leading-relaxed mb-4">
          Your web host is the foundation of your online presence. A quality hosting provider directly impacts your website&apos;s success through several key factors:
        </p>
        <div className="grid md:grid-cols-2 gap-4 my-8">
          {[
            { icon: "⚡", title: "Lightning Fast Speed", desc: "Critical for user experience and SEO rankings" },
            { icon: "🔒", title: "99.9% Uptime", desc: "Your site stays online when customers need it" },
            { icon: "🛡️", title: "Advanced Security", desc: "Protection against cyber threats and data loss" },
            { icon: "📈", title: "Easy Scalability", desc: "Grow seamlessly as your traffic increases" },
          ].map((item) => (
            <div key={item.title} className="bg-gradient-to-br from-blue-50 to-indigo-50 p-6 rounded-2xl border border-blue-100">
              <span className="text-3xl mb-3 block">{item.icon}</span>
              <h3 className="font-bold text-gray-900 mb-2">{item.title}</h3>
              <p className="text-gray-600 text-sm">{item.desc}</p>
            </div>
          ))}
        </div>
      </div>

      <div id="top-picks" className="scroll-mt-24">
        <h2 className="text-3xl font-bold text-gray-900 mt-16 mb-8">Our Top 5 Web Hosting Providers</h2>
        
        {/* Bluehost */}
        <div className="bg-white rounded-2xl border border-gray-200 p-8 mb-8 shadow-sm hover:shadow-lg transition-shadow">
          <div className="flex items-start gap-4 mb-4">
            <div className="w-16 h-16 bg-blue-600 rounded-xl flex items-center justify-center text-white text-2xl font-bold flex-shrink-0">1</div>
            <div>
              <div className="flex items-center gap-3 mb-1">
                <h3 className="text-2xl font-bold text-gray-900">Bluehost</h3>
                <span className="bg-green-100 text-green-700 text-xs font-bold px-3 py-1 rounded-full">BEST OVERALL</span>
              </div>
              <p className="text-gray-600">Officially recommended by WordPress.org with unbeatable value</p>
            </div>
          </div>
          <div className="grid md:grid-cols-3 gap-6 mt-6 pt-6 border-t border-gray-100">
            <div>
              <p className="text-sm text-gray-500 mb-1">Starting Price</p>
              <p className="text-3xl font-bold text-gray-900">$2.95<span className="text-base font-normal text-gray-500">/mo</span></p>
            </div>
            <div>
              <p className="text-sm text-gray-500 mb-1">Uptime</p>
              <p className="text-xl font-bold text-gray-900">99.98%</p>
            </div>
            <div>
              <p className="text-sm text-gray-500 mb-1">Load Time</p>
              <p className="text-xl font-bold text-gray-900">1.2 seconds</p>
            </div>
          </div>
          <ul className="grid md:grid-cols-2 gap-3 mt-6">
            {["Free domain first year", "Free SSL certificate", "1-click WordPress install", "24/7 expert support", "50GB SSD storage", "Unmetered bandwidth"].map((feat) => (
              <li key={feat} className="flex items-center gap-2 text-gray-700">
                <CheckIcon /> {feat}
              </li>
            ))}
          </ul>
        </div>

        {/* SiteGround */}
        <div className="bg-white rounded-2xl border border-gray-200 p-8 mb-8 shadow-sm hover:shadow-lg transition-shadow">
          <div className="flex items-start gap-4 mb-4">
            <div className="w-16 h-16 bg-teal-500 rounded-xl flex items-center justify-center text-white text-2xl font-bold flex-shrink-0">2</div>
            <div>
              <div className="flex items-center gap-3 mb-1">
                <h3 className="text-2xl font-bold text-gray-900">SiteGround</h3>
                <span className="bg-purple-100 text-purple-700 text-xs font-bold px-3 py-1 rounded-full">BEST FOR SPEED</span>
              </div>
              <p className="text-gray-600">Cutting-edge technology with Google Cloud infrastructure</p>
            </div>
          </div>
          <div className="grid md:grid-cols-3 gap-6 mt-6 pt-6 border-t border-gray-100">
            <div>
              <p className="text-sm text-gray-500 mb-1">Starting Price</p>
              <p className="text-3xl font-bold text-gray-900">$2.99<span className="text-base font-normal text-gray-500">/mo</span></p>
            </div>
            <div>
              <p className="text-sm text-gray-500 mb-1">Uptime</p>
              <p className="text-xl font-bold text-gray-900">99.99%</p>
            </div>
            <div>
              <p className="text-sm text-gray-500 mb-1">Load Time</p>
              <p className="text-xl font-bold text-gray-900">0.8 seconds</p>
            </div>
          </div>
        </div>

        {/* HostGator */}
        <div className="bg-white rounded-2xl border border-gray-200 p-8 mb-8 shadow-sm hover:shadow-lg transition-shadow">
          <div className="flex items-start gap-4 mb-4">
            <div className="w-16 h-16 bg-orange-500 rounded-xl flex items-center justify-center text-white text-2xl font-bold flex-shrink-0">3</div>
            <div>
              <div className="flex items-center gap-3 mb-1">
                <h3 className="text-2xl font-bold text-gray-900">HostGator</h3>
                <span className="bg-blue-100 text-blue-700 text-xs font-bold px-3 py-1 rounded-full">BEST FOR BEGINNERS</span>
              </div>
              <p className="text-gray-600">Intuitive interface with comprehensive features</p>
            </div>
          </div>
          <div className="grid md:grid-cols-3 gap-6 mt-6 pt-6 border-t border-gray-100">
            <div>
              <p className="text-sm text-gray-500 mb-1">Starting Price</p>
              <p className="text-3xl font-bold text-gray-900">$2.75<span className="text-base font-normal text-gray-500">/mo</span></p>
            </div>
            <div>
              <p className="text-sm text-gray-500 mb-1">Uptime</p>
              <p className="text-xl font-bold text-gray-900">99.97%</p>
            </div>
            <div>
              <p className="text-sm text-gray-500 mb-1">Money Back</p>
              <p className="text-xl font-bold text-gray-900">45 Days</p>
            </div>
          </div>
        </div>
      </div>

      {/* Comparison Table */}
      <div id="comparison" className="scroll-mt-24">
        <h2 className="text-3xl font-bold text-gray-900 mt-16 mb-6">Performance Comparison</h2>
        <div className="overflow-x-auto rounded-2xl border border-gray-200 shadow-sm">
          <table className="w-full">
            <thead className="bg-gray-50">
              <tr>
                <th className="text-left p-4 font-semibold text-gray-900">Provider</th>
                <th className="text-center p-4 font-semibold text-gray-900">Uptime</th>
                <th className="text-center p-4 font-semibold text-gray-900">Load Time</th>
                <th className="text-center p-4 font-semibold text-gray-900">Support Score</th>
                <th className="text-center p-4 font-semibold text-gray-900">Starting Price</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {[
                { name: "Bluehost", uptime: "99.98%", speed: "1.2s", score: "9.2/10", price: "$2.95/mo", highlight: true },
                { name: "SiteGround", uptime: "99.99%", speed: "0.8s", score: "9.5/10", price: "$2.99/mo", highlight: false },
                { name: "HostGator", uptime: "99.97%", speed: "1.4s", score: "8.8/10", price: "$2.75/mo", highlight: false },
                { name: "Cloudways", uptime: "99.99%", speed: "0.6s", score: "9.0/10", price: "$11/mo", highlight: false },
                { name: "A2 Hosting", uptime: "99.97%", speed: "1.0s", score: "8.9/10", price: "$2.99/mo", highlight: false },
              ].map((row) => (
                <tr key={row.name} className={row.highlight ? "bg-blue-50/50" : ""}>
                  <td className="p-4 font-semibold text-gray-900">{row.name}</td>
                  <td className="p-4 text-center text-gray-600">{row.uptime}</td>
                  <td className="p-4 text-center text-gray-600">{row.speed}</td>
                  <td className="p-4 text-center">
                    <span className="bg-green-100 text-green-700 px-2 py-1 rounded-full text-sm font-medium">{row.score}</span>
                  </td>
                  <td className="p-4 text-center font-semibold text-gray-900">{row.price}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {/* How to Choose */}
      <div id="how-to-choose" className="scroll-mt-24">
        <h2 className="text-3xl font-bold text-gray-900 mt-16 mb-6">How to Choose the Right Host</h2>
        <div className="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl p-8 border border-indigo-100">
          <div className="grid md:grid-cols-2 gap-8">
            <div>
              <h3 className="font-bold text-gray-900 mb-3 flex items-center gap-2">
                <span className="w-8 h-8 bg-indigo-600 text-white rounded-full flex items-center justify-center text-sm">1</span>
                Website Type
              </h3>
              <ul className="space-y-2 text-gray-600 ml-10">
                <li>• <strong>Blog/Personal:</strong> Shared hosting works great</li>
                <li>• <strong>Business Site:</strong> VPS or cloud for reliability</li>
                <li>• <strong>E-commerce:</strong> Dedicated resources needed</li>
                <li>• <strong>High Traffic:</strong> Cloud or dedicated hosting</li>
              </ul>
            </div>
            <div>
              <h3 className="font-bold text-gray-900 mb-3 flex items-center gap-2">
                <span className="w-8 h-8 bg-indigo-600 text-white rounded-full flex items-center justify-center text-sm">2</span>
                Key Considerations
              </h3>
              <ul className="space-y-2 text-gray-600 ml-10">
                <li>• Storage space and bandwidth needs</li>
                <li>• Database and language support</li>
                <li>• Promotional vs renewal pricing</li>
                <li>• 24/7 support availability</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      {/* Security */}
      <div id="security" className="scroll-mt-24">
        <h2 className="text-3xl font-bold text-gray-900 mt-16 mb-6">Essential Security Features</h2>
        <div className="flex flex-wrap gap-3">
          {["SSL Certificates", "DDoS Protection", "Malware Scanning", "Automatic Backups", "Firewall Protection", "Two-Factor Authentication"].map((feature) => (
            <span key={feature} className="bg-gray-100 text-gray-700 px-4 py-2 rounded-full font-medium">
              ✓ {feature}
            </span>
          ))}
        </div>
      </div>

      {/* Conclusion */}
      <div id="conclusion" className="scroll-mt-24">
        <h2 className="text-3xl font-bold text-gray-900 mt-16 mb-6">Final Recommendations</h2>
        <div className="bg-gray-900 text-white rounded-2xl p-8">
          <p className="text-lg leading-relaxed mb-6">
            The best web hosting provider depends on your specific needs. Based on our extensive testing:
          </p>
          <div className="grid md:grid-cols-3 gap-4">
            <div className="bg-white/10 rounded-xl p-4">
              <p className="text-blue-400 font-bold mb-1">For Most Users</p>
              <p className="text-xl font-bold">Bluehost</p>
              <p className="text-gray-400 text-sm">Best balance of features and price</p>
            </div>
            <div className="bg-white/10 rounded-xl p-4">
              <p className="text-purple-400 font-bold mb-1">For Speed</p>
              <p className="text-xl font-bold">SiteGround</p>
              <p className="text-gray-400 text-sm">Unmatched performance</p>
            </div>
            <div className="bg-white/10 rounded-xl p-4">
              <p className="text-green-400 font-bold mb-1">For Cloud</p>
              <p className="text-xl font-bold">Cloudways</p>
              <p className="text-gray-400 text-sm">Premium managed hosting</p>
            </div>
          </div>
          <p className="mt-6 text-gray-400 text-sm">
            💡 <strong>Pro Tip:</strong> Take advantage of money-back guarantees to test providers risk-free before committing long-term.
          </p>
        </div>
      </div>
    </>
  )
}

function CheckIcon() {
  return (
    <svg className="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
    </svg>
  )
}

export default function BlogPostPage() {
  const params = useParams()
  const slug = params?.slug as string
  const [copied, setCopied] = useState(false)
  
  const post = blogPosts["best-web-hosting-providers-2024"]

  const handleShare = () => {
    navigator.clipboard.writeText(window.location.href)
    setCopied(true)
    setTimeout(() => setCopied(false), 2000)
  }

  if (!post) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <h1 className="text-2xl font-bold text-gray-900 mb-4">Article Not Found</h1>
          <Button asChild variant="outline">
            <Link href="/blog">
              <ArrowLeft className="mr-2 h-4 w-4" />
              Back to Blog
            </Link>
          </Button>
        </div>
      </div>
    )
  }

  return (
    <article className="min-h-screen bg-white">
      {/* Navigation Bar */}
      <div className="sticky top-16 z-40 bg-white/80 backdrop-blur-md border-b">
        <div className="container mx-auto px-4 py-3">
          <div className="flex items-center justify-between">
            <Button asChild variant="ghost" size="sm" className="text-gray-600">
              <Link href="/blog">
                <ArrowLeft className="mr-2 h-4 w-4" />
                All Articles
              </Link>
            </Button>
            <div className="flex items-center gap-2">
              <Button variant="ghost" size="icon" className="text-gray-600" onClick={handleShare}>
                {copied ? <Check className="h-4 w-4 text-green-500" /> : <Share2 className="h-4 w-4" />}
              </Button>
              <Button variant="ghost" size="icon" className="text-gray-600">
                <Bookmark className="h-4 w-4" />
              </Button>
            </div>
          </div>
        </div>
      </div>

      {/* Hero Section */}
      <div className="relative h-[50vh] min-h-[400px] max-h-[600px] overflow-hidden">
        <Image
          src={post.image}
          alt={post.title}
          fill
          priority
          className="object-cover"
        />
        <div className="absolute inset-0 bg-gradient-to-t from-black via-black/40 to-transparent" />
        
        <div className="absolute inset-0 flex items-end">
          <div className="container mx-auto px-4 pb-12">
            <div className="max-w-4xl">
              <span className={`${post.categoryColor} text-white text-sm font-semibold px-4 py-1.5 rounded-full inline-block mb-4`}>
                {post.category}
              </span>
              <h1 className="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-4 leading-tight">
                {post.title}
              </h1>
              <p className="text-gray-200 text-lg md:text-xl max-w-2xl">
                {post.excerpt}
              </p>
            </div>
          </div>
        </div>
      </div>

      {/* Main Content */}
      <div className="container mx-auto px-4 py-12">
        <div className="max-w-6xl mx-auto">
          <div className="grid lg:grid-cols-[1fr_300px] gap-12">
            {/* Article Content */}
            <div>
              {/* Author Bar */}
              <div className="flex items-center justify-between py-6 border-b border-gray-100 mb-8">
                <div className="flex items-center gap-4">
                  <Image
                    src={post.authorImage}
                    alt={post.author}
                    width={56}
                    height={56}
                    className="rounded-full ring-2 ring-gray-100"
                  />
                  <div>
                    <p className="font-bold text-gray-900">{post.author}</p>
                    <p className="text-sm text-gray-500">{post.authorBio}</p>
                  </div>
                </div>
                <div className="flex items-center gap-4 text-sm text-gray-500">
                  <span className="flex items-center gap-1">
                    <Clock className="h-4 w-4" />
                    {post.readTime}
                  </span>
                  <span className="flex items-center gap-1">
                    <MessageCircle className="h-4 w-4" />
                    {post.comments}
                  </span>
                </div>
              </div>

              {/* Article Body */}
              <div className="prose prose-lg max-w-none">
                <BlogContent />
              </div>

              {/* Share Section */}
              <div className="mt-16 pt-8 border-t border-gray-100">
                <h3 className="text-sm font-semibold text-gray-900 mb-4">Share this article</h3>
                <div className="flex gap-3">
                  <Button variant="outline" size="sm" className="gap-2">
                    <Twitter className="h-4 w-4" /> Twitter
                  </Button>
                  <Button variant="outline" size="sm" className="gap-2">
                    <Facebook className="h-4 w-4" /> Facebook
                  </Button>
                  <Button variant="outline" size="sm" className="gap-2">
                    <Linkedin className="h-4 w-4" /> LinkedIn
                  </Button>
                  <Button variant="outline" size="sm" className="gap-2" onClick={handleShare}>
                    {copied ? <Check className="h-4 w-4" /> : <Copy className="h-4 w-4" />} 
                    {copied ? "Copied!" : "Copy Link"}
                  </Button>
                </div>
              </div>

              {/* Comments Section */}
              <div className="mt-16 pt-8 border-t border-gray-100">
                <h3 className="text-2xl font-bold text-gray-900 mb-6">Comments ({post.comments})</h3>
                <div className="bg-gray-50 rounded-2xl p-8 text-center">
                  <MessageCircle className="h-12 w-12 text-gray-300 mx-auto mb-4" />
                  <p className="text-gray-500">Comments are currently disabled for this article.</p>
                </div>
              </div>
            </div>

            {/* Sidebar */}
            <aside className="hidden lg:block">
              <div className="sticky top-32 space-y-6">
                {/* Table of Contents */}
                <div className="bg-gray-50 rounded-2xl p-6">
                  <h3 className="font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <span className="w-1 h-5 bg-blue-600 rounded-full"></span>
                    Table of Contents
                  </h3>
                  <nav className="space-y-2">
                    {post.tableOfContents.map((item) => (
                      <a
                        key={item.id}
                        href={`#${item.id}`}
                        className="block text-gray-600 hover:text-blue-600 hover:bg-white py-2 px-3 rounded-lg transition-colors text-sm"
                      >
                        {item.title}
                      </a>
                    ))}
                  </nav>
                </div>

                {/* Newsletter */}
                <div className="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl p-6 text-white">
                  <h3 className="font-bold text-lg mb-2">Stay Updated</h3>
                  <p className="text-blue-100 text-sm mb-4">Get the latest hosting reviews and deals delivered to your inbox.</p>
                  <input
                    type="email"
                    placeholder="Enter your email"
                    className="w-full px-4 py-2 rounded-lg text-gray-900 mb-3 text-sm"
                  />
                  <Button className="w-full bg-white text-blue-600 hover:bg-blue-50 font-semibold">
                    Subscribe
                  </Button>
                </div>

                {/* Related Tags */}
                <div className="bg-white border border-gray-200 rounded-2xl p-6">
                  <h3 className="font-bold text-gray-900 mb-4">Related Topics</h3>
                  <div className="flex flex-wrap gap-2">
                    {["Web Hosting", "Cloud Hosting", "WordPress", "Website Speed", "SEO", "Bluehost", "SiteGround"].map((tag) => (
                      <Link
                        key={tag}
                        href={`/blog?tag=${tag.toLowerCase().replace(' ', '-')}`}
                        className="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm hover:bg-blue-100 hover:text-blue-700 transition-colors"
                      >
                        {tag}
                      </Link>
                    ))}
                  </div>
                </div>
              </div>
            </aside>
          </div>
        </div>
      </div>
    </article>
  )
}
