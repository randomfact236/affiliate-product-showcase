"use client"

import Image from "next/image"
import Link from "next/link"
import { Clock, MessageCircle, ArrowRight } from "lucide-react"
import { Button } from "@/components/ui/button"

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
  featured?: boolean
}

const blogPosts: BlogPost[] = [
  {
    id: "b1",
    title: "The Complete Guide to Digital Marketing in 2024",
    excerpt: "Learn the latest strategies and techniques to boost your online presence and drive more traffic to your business...",
    image: "https://images.unsplash.com/photo-1432888498266-38ffec3eaf0a?w=600&h=400&fit=crop",
    category: "Marketing",
    categoryColor: "bg-blue-600",
    author: "Sarah Johnson",
    authorImage: "https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100&h=100&fit=crop",
    date: "Dec 15, 2024",
    comments: 28,
    featured: true,
  },
  {
    id: "b2",
    title: "10 Essential Tools Every Developer Should Know",
    excerpt: "Discover the must-have tools that will make your development workflow more efficient...",
    image: "https://images.unsplash.com/photo-1461749280684-dccba630e2f6?w=400&h=250&fit=crop",
    category: "Tech",
    categoryColor: "bg-purple-600",
    author: "Mike Chen",
    authorImage: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop",
    date: "Dec 14, 2024",
    comments: 45,
  },
  {
    id: "b3",
    title: "How to Start a Successful Online Business",
    excerpt: "A comprehensive guide to launching your own e-commerce venture from scratch...",
    image: "https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=400&h=250&fit=crop",
    category: "Business",
    categoryColor: "bg-green-600",
    author: "Emily Davis",
    authorImage: "https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=100&h=100&fit=crop",
    date: "Dec 13, 2024",
    comments: 32,
  },
  {
    id: "b4",
    title: "The Future of Remote Work: Trends to Watch",
    excerpt: "Explore how the workplace is evolving and what it means for your career...",
    image: "https://images.unsplash.com/photo-1593642632823-8f7856677741?w=400&h=250&fit=crop",
    category: "Lifestyle",
    categoryColor: "bg-orange-600",
    author: "David Wilson",
    authorImage: "https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=100&h=100&fit=crop",
    date: "Dec 12, 2024",
    comments: 19,
  },
  {
    id: "b5",
    title: "Healthy Eating: Simple Meal Prep Ideas",
    excerpt: "Save time and eat better with these easy meal preparation strategies...",
    image: "https://images.unsplash.com/photo-1490645935967-10de6ba17061?w=400&h=250&fit=crop",
    category: "Health",
    categoryColor: "bg-red-500",
    author: "Lisa Park",
    authorImage: "https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=100&h=100&fit=crop",
    date: "Dec 11, 2024",
    comments: 56,
  },
  {
    id: "b6",
    title: "Travel on a Budget: Tips for 2024",
    excerpt: "See the world without breaking the bank with these money-saving travel tips...",
    image: "https://images.unsplash.com/photo-1488646953014-85cb44e25828?w=400&h=250&fit=crop",
    category: "Travel",
    categoryColor: "bg-teal-600",
    author: "Tom Anderson",
    authorImage: "https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=100&h=100&fit=crop",
    date: "Dec 10, 2024",
    comments: 23,
  },
]

export function BlogSection() {
  const featuredPost = blogPosts.find(post => post.featured)
  const regularPosts = blogPosts.filter(post => !post.featured)

  return (
    <section className="py-12 bg-gray-50">
      <div className="container mx-auto px-4">
        {/* Section Header */}
        <div className="flex items-center justify-between mb-8">
          <h2 className="text-2xl font-bold text-gray-900 flex items-center gap-2">
            <span className="w-1 h-6 bg-blue-600"></span>
            LATEST ARTICLES
          </h2>
          <Button variant="outline" size="sm" asChild className="hidden sm:flex">
            <Link href="/blog">
              View All
              <ArrowRight className="ml-2 h-4 w-4" />
            </Link>
          </Button>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          {/* Featured Post */}
          {featuredPost && (
            <article className="lg:col-span-2 group">
              <Link href={`/blog/${featuredPost.id}`} className="block">
                <div className="relative aspect-[16/9] overflow-hidden rounded-xl">
                  <Image
                    src={featuredPost.image}
                    alt={featuredPost.title}
                    fill
                    className="object-cover transition-transform duration-700 group-hover:scale-105"
                  />
                  <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent" />
                  <span className={`absolute top-4 left-4 px-4 py-1.5 ${featuredPost.categoryColor} text-white text-sm font-semibold rounded`}>
                    {featuredPost.category}
                  </span>
                </div>
                <div className="mt-4">
                  <h3 className="text-2xl font-bold text-gray-900 group-hover:text-blue-600 transition-colors">
                    {featuredPost.title}
                  </h3>
                  <p className="text-gray-600 mt-2 line-clamp-2">
                    {featuredPost.excerpt}
                  </p>
                  <div className="flex items-center gap-4 mt-4">
                    <div className="flex items-center gap-2">
                      <Image
                        src={featuredPost.authorImage}
                        alt={featuredPost.author}
                        width={32}
                        height={32}
                        className="rounded-full"
                      />
                      <span className="text-sm font-medium text-gray-900">
                        {featuredPost.author}
                      </span>
                    </div>
                    <span className="text-gray-400">|</span>
                    <span className="flex items-center gap-1 text-gray-500 text-sm">
                      <Clock className="h-4 w-4" />
                      {featuredPost.date}
                    </span>
                    <span className="flex items-center gap-1 text-gray-500 text-sm">
                      <MessageCircle className="h-4 w-4" />
                      {featuredPost.comments}
                    </span>
                  </div>
                </div>
              </Link>
            </article>
          )}

          {/* Regular Posts List */}
          <div className="space-y-4">
            {regularPosts.slice(0, 4).map((post) => (
              <article key={post.id} className="group">
                <Link href={`/blog/${post.id}`} className="flex gap-4">
                  <div className="relative w-24 h-20 flex-shrink-0 overflow-hidden rounded-lg">
                    <Image
                      src={post.image}
                      alt={post.title}
                      fill
                      className="object-cover transition-transform duration-500 group-hover:scale-110"
                    />
                  </div>
                  <div className="flex-1 min-w-0">
                    <span className={`inline-block px-2 py-0.5 ${post.categoryColor} text-white text-xs font-medium rounded mb-1`}>
                      {post.category}
                    </span>
                    <h4 className="text-sm font-semibold text-gray-900 group-hover:text-blue-600 transition-colors line-clamp-2">
                      {post.title}
                    </h4>
                    <div className="flex items-center gap-3 mt-1 text-gray-500 text-xs">
                      <span className="flex items-center gap-1">
                        <Clock className="h-3 w-3" />
                        {post.date}
                      </span>
                    </div>
                  </div>
                </Link>
              </article>
            ))}
          </div>
        </div>

        {/* Mobile View All Button */}
        <div className="mt-8 text-center sm:hidden">
          <Button variant="outline" asChild>
            <Link href="/blog">
              View All Articles
              <ArrowRight className="ml-2 h-4 w-4" />
            </Link>
          </Button>
        </div>
      </div>
    </section>
  )
}
