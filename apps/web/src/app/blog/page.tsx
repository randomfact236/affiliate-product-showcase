"use client"

import { useState, useMemo } from "react"
import Image from "next/image"
import Link from "next/link"
import { Clock, MessageCircle, ArrowRight } from "lucide-react"
import { Button } from "@/components/ui/button"
import { cn } from "@/lib/utils"

interface BlogPost {
  id: string
  title: string
  excerpt: string
  image: string
  category: string
  categoryColor: string
  author: string
  authorImage: string
  date: string
  comments: number
}

const categories = [
  { id: "all", name: "All", color: "bg-gray-800" },
  { id: "hosting", name: "Hosting", color: "bg-blue-600" },
  { id: "ai", name: "AI", color: "bg-purple-600" },
  { id: "seo", name: "SEO", color: "bg-green-600" },
  { id: "marketing", name: "Marketing", color: "bg-orange-600" },
  { id: "writing", name: "Writing", color: "bg-pink-600" },
  { id: "design", name: "Design", color: "bg-red-600" },
  { id: "analytics", name: "Analytics", color: "bg-teal-600" },
]

const blogPosts: BlogPost[] = [
  {
    id: "1",
    title: "Best Web Hosting Providers for 2024",
    excerpt: "Discover the top hosting services with excellent uptime, speed, and customer support for your website needs.",
    image: "https://images.unsplash.com/photo-1544197150-b99a580bb7a8?w=800&h=500&fit=crop",
    category: "Hosting",
    categoryColor: "bg-blue-600",
    author: "Sarah Johnson",
    authorImage: "https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100&h=100&fit=crop",
    date: "Dec 15, 2024",
    comments: 28,
  },
  {
    id: "2",
    title: "AI Tools Revolutionizing Content Creation",
    excerpt: "Explore how artificial intelligence is transforming the way we create and optimize content online.",
    image: "https://images.unsplash.com/photo-1677442136019-21780ecad995?w=800&h=500&fit=crop",
    category: "AI",
    categoryColor: "bg-purple-600",
    author: "Mike Chen",
    authorImage: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop",
    date: "Dec 14, 2024",
    comments: 45,
  },
  {
    id: "3",
    title: "SEO Best Practices for 2024",
    excerpt: "Stay ahead of the competition with these proven SEO strategies and ranking factors.",
    image: "https://images.unsplash.com/photo-1571721795195-a2ca2d3370a9?w=800&h=500&fit=crop",
    category: "SEO",
    categoryColor: "bg-green-600",
    author: "Emily Davis",
    authorImage: "https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=100&h=100&fit=crop",
    date: "Dec 13, 2024",
    comments: 32,
  },
  {
    id: "4",
    title: "Email Marketing Strategies That Convert",
    excerpt: "Learn how to create email campaigns that drive engagement and boost your conversion rates.",
    image: "https://images.unsplash.com/photo-1563986768609-322da13575f3?w=800&h=500&fit=crop",
    category: "Marketing",
    categoryColor: "bg-orange-600",
    author: "David Wilson",
    authorImage: "https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=100&h=100&fit=crop",
    date: "Dec 12, 2024",
    comments: 19,
  },
  {
    id: "5",
    title: "Top AI Writing Assistants Compared",
    excerpt: "A comprehensive comparison of the best AI writing tools to help you create content faster.",
    image: "https://images.unsplash.com/photo-1455390582262-044cdead277a?w=800&h=500&fit=crop",
    category: "Writing",
    categoryColor: "bg-pink-600",
    author: "Lisa Park",
    authorImage: "https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=100&h=100&fit=crop",
    date: "Dec 11, 2024",
    comments: 56,
  },
  {
    id: "6",
    title: "Design Tools Every Marketer Should Know",
    excerpt: "Create stunning visuals with these beginner-friendly design tools and resources.",
    image: "https://images.unsplash.com/photo-1561070791-2526d30994b5?w=800&h=500&fit=crop",
    category: "Design",
    categoryColor: "bg-red-600",
    author: "Tom Anderson",
    authorImage: "https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=100&h=100&fit=crop",
    date: "Dec 10, 2024",
    comments: 23,
  },
  {
    id: "7",
    title: "Understanding Google Analytics 4",
    excerpt: "Master the new Google Analytics interface and unlock powerful insights about your audience.",
    image: "https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=800&h=500&fit=crop",
    category: "Analytics",
    categoryColor: "bg-teal-600",
    author: "Alex Rivera",
    authorImage: "https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=100&h=100&fit=crop",
    date: "Dec 9, 2024",
    comments: 34,
  },
  {
    id: "8",
    title: "Cloud Hosting vs Shared Hosting",
    excerpt: "Which hosting solution is right for your business? We break down the pros and cons.",
    image: "https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=800&h=500&fit=crop",
    category: "Hosting",
    categoryColor: "bg-blue-600",
    author: "Sarah Johnson",
    authorImage: "https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100&h=100&fit=crop",
    date: "Dec 8, 2024",
    comments: 41,
  },
  {
    id: "9",
    title: "ChatGPT Tips for Better Results",
    excerpt: "Unlock the full potential of AI with these advanced prompting techniques and strategies.",
    image: "https://images.unsplash.com/photo-1676299081847-824916de030a?w=800&h=500&fit=crop",
    category: "AI",
    categoryColor: "bg-purple-600",
    author: "Mike Chen",
    authorImage: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop",
    date: "Dec 7, 2024",
    comments: 67,
  },
]

export default function BlogPage() {
  const [activeCategory, setActiveCategory] = useState("all")

  const filteredPosts = useMemo(() => {
    if (activeCategory === "all") return blogPosts
    return blogPosts.filter(post => 
      post.category.toLowerCase() === activeCategory.toLowerCase()
    )
  }, [activeCategory])

  return (
    <div className="min-h-screen bg-white">
      {/* Header */}
      <div className="bg-gray-50 border-b">
        <div className="container mx-auto px-4 py-8">
          <h1 className="text-3xl md:text-4xl font-bold text-gray-900 mb-2">Blog</h1>
          <p className="text-gray-600">Latest articles, guides, and insights</p>
        </div>
      </div>

      {/* Category Tabs */}
      <div className="border-b bg-white sticky top-16 z-30">
        <div className="container mx-auto px-4 py-4">
          <div className="flex items-center gap-2 overflow-x-auto scrollbar-hide pb-1">
            {categories.map((cat) => (
              <button
                key={cat.id}
                onClick={() => setActiveCategory(cat.id)}
                className={cn(
                  "px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors",
                  activeCategory === cat.id
                    ? `${cat.color} text-white`
                    : "bg-gray-100 text-gray-700 hover:bg-gray-200"
                )}
              >
                {cat.name}
              </button>
            ))}
          </div>
        </div>
      </div>

      {/* Blog Grid */}
      <div className="container mx-auto px-4 py-8">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {filteredPosts.map((post) => (
            <article key={post.id} className="group bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow border border-gray-100">
              <Link href={`/blog/${post.id}`} className="block">
                <div className="relative aspect-[16/10] overflow-hidden">
                  <Image
                    src={post.image}
                    alt={post.title}
                    fill
                    className="object-cover transition-transform duration-500 group-hover:scale-105"
                  />
                  <span className={`absolute top-3 left-3 px-3 py-1 ${post.categoryColor} text-white text-xs font-medium rounded`}>
                    {post.category}
                  </span>
                </div>
                <div className="p-5">
                  <h2 className="text-lg font-bold text-gray-900 group-hover:text-blue-600 transition-colors line-clamp-2 mb-2">
                    {post.title}
                  </h2>
                  <p className="text-gray-600 text-sm line-clamp-2 mb-4">
                    {post.excerpt}
                  </p>
                  <div className="flex items-center justify-between pt-4 border-t border-gray-100">
                    <div className="flex items-center gap-2">
                      <Image
                        src={post.authorImage}
                        alt={post.author}
                        width={28}
                        height={28}
                        className="rounded-full"
                      />
                      <span className="text-sm text-gray-700">{post.author}</span>
                    </div>
                    <div className="flex items-center gap-3 text-gray-500 text-xs">
                      <span className="flex items-center gap-1">
                        <Clock className="h-3 w-3" />
                        {post.date}
                      </span>
                      <span className="flex items-center gap-1">
                        <MessageCircle className="h-3 w-3" />
                        {post.comments}
                      </span>
                    </div>
                  </div>
                </div>
              </Link>
            </article>
          ))}
        </div>

        {/* Empty State */}
        {filteredPosts.length === 0 && (
          <div className="text-center py-16">
            <p className="text-gray-500 text-lg">No articles found in this category.</p>
            <Button 
              variant="outline" 
              className="mt-4"
              onClick={() => setActiveCategory("all")}
            >
              View All Articles
            </Button>
          </div>
        )}

        {/* Load More */}
        {filteredPosts.length > 0 && (
          <div className="text-center mt-10">
            <Button variant="outline" size="lg">
              Load More Articles
              <ArrowRight className="ml-2 h-4 w-4" />
            </Button>
          </div>
        )}
      </div>
    </div>
  )
}
