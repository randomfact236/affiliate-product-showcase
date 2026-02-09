"use client"

import { useState } from "react"
import Image from "next/image"
import Link from "next/link"
import { ResponsiveTabs } from "./responsive-tabs"
import { Clock, Eye } from "lucide-react"

interface Post {
  id: string
  title: string
  excerpt: string
  image: string
  category: string
  date: string
  views: number
}

const tabs = [
  { id: "all", label: "All" },
  { id: "tech", label: "Tech" },
  { id: "business", label: "Business" },
  { id: "lifestyle", label: "Lifestyle" },
  { id: "sports", label: "Sports" },
  { id: "entertainment", label: "Entertainment" },
  { id: "health", label: "Health" },
  { id: "travel", label: "Travel" },
]

const postsByCategory: Record<string, Post[]> = {
  all: [
    {
      id: "1",
      title: "The Future of AI: How Machine Learning is Transforming Industries",
      excerpt: "Artificial intelligence is revolutionizing the way businesses operate...",
      image: "https://images.unsplash.com/photo-1677442136019-21780ecad995?w=400&h=300&fit=crop",
      category: "Tech",
      date: "2 hours ago",
      views: 1240,
    },
    {
      id: "2",
      title: "10 Best Productivity Tools for Remote Workers in 2024",
      excerpt: "Working from home requires the right set of tools to stay productive...",
      image: "https://images.unsplash.com/photo-1593642632823-8f7856677741?w=400&h=300&fit=crop",
      category: "Business",
      date: "4 hours ago",
      views: 890,
    },
    {
      id: "3",
      title: "The Ultimate Guide to Minimalist Living",
      excerpt: "Discover how minimalism can improve your mental health and lifestyle...",
      image: "https://images.unsplash.com/photo-1494438639946-1ebd1d20bf85?w=400&h=300&fit=crop",
      category: "Lifestyle",
      date: "6 hours ago",
      views: 2100,
    },
    {
      id: "4",
      title: "Championship Finals: What to Expect This Weekend",
      excerpt: "The most anticipated sports event of the year is just around the corner...",
      image: "https://images.unsplash.com/photo-1461896836934- voices?w=400&h=300&fit=crop",
      category: "Sports",
      date: "8 hours ago",
      views: 3500,
    },
    {
      id: "5",
      title: "New Streaming Service Launches with Exclusive Content",
      excerpt: "Get ready for a new era of entertainment with groundbreaking shows...",
      image: "https://images.unsplash.com/photo-1522869635100-9f4c5e86aa37?w=400&h=300&fit=crop",
      category: "Entertainment",
      date: "10 hours ago",
      views: 1800,
    },
  ],
  tech: [
    {
      id: "t1",
      title: "The Future of AI: How Machine Learning is Transforming Industries",
      excerpt: "Artificial intelligence is revolutionizing the way businesses operate...",
      image: "https://images.unsplash.com/photo-1677442136019-21780ecad995?w=400&h=300&fit=crop",
      category: "Tech",
      date: "2 hours ago",
      views: 1240,
    },
    {
      id: "t2",
      title: "Apple's New M3 Chip: Performance Review",
      excerpt: "We tested the latest processor and the results are impressive...",
      image: "https://images.unsplash.com/photo-1517336714731-489689fd1ca4?w=400&h=300&fit=crop",
      category: "Tech",
      date: "5 hours ago",
      views: 3200,
    },
    {
      id: "t3",
      title: "5G Networks: What You Need to Know",
      excerpt: "The rollout of 5G is changing mobile connectivity forever...",
      image: "https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=400&h=300&fit=crop",
      category: "Tech",
      date: "1 day ago",
      views: 1500,
    },
  ],
  business: [
    {
      id: "b1",
      title: "10 Best Productivity Tools for Remote Workers",
      excerpt: "Working from home requires the right set of tools to stay productive...",
      image: "https://images.unsplash.com/photo-1593642632823-8f7856677741?w=400&h=300&fit=crop",
      category: "Business",
      date: "4 hours ago",
      views: 890,
    },
    {
      id: "b2",
      title: "Stock Market Trends to Watch in 2024",
      excerpt: "Expert analysis of the financial markets and investment opportunities...",
      image: "https://images.unsplash.com/photo-1611974765270-ca1258634369?w=400&h=300&fit=crop",
      category: "Business",
      date: "12 hours ago",
      views: 2200,
    },
  ],
  lifestyle: [
    {
      id: "l1",
      title: "The Ultimate Guide to Minimalist Living",
      excerpt: "Discover how minimalism can improve your mental health and lifestyle...",
      image: "https://images.unsplash.com/photo-1494438639946-1ebd1d20bf85?w=400&h=300&fit=crop",
      category: "Lifestyle",
      date: "6 hours ago",
      views: 2100,
    },
  ],
  sports: [
    {
      id: "s1",
      title: "Championship Finals: What to Expect",
      excerpt: "The most anticipated sports event of the year is just around the corner...",
      image: "https://images.unsplash.com/photo-1461896836934- voices?w=400&h=300&fit=crop",
      category: "Sports",
      date: "8 hours ago",
      views: 3500,
    },
  ],
  entertainment: [
    {
      id: "e1",
      title: "New Streaming Service Launches with Exclusive Content",
      excerpt: "Get ready for a new era of entertainment with groundbreaking shows...",
      image: "https://images.unsplash.com/photo-1522869635100-9f4c5e86aa37?w=400&h=300&fit=crop",
      category: "Entertainment",
      date: "10 hours ago",
      views: 1800,
    },
  ],
  health: [
    {
      id: "h1",
      title: "10 Superfoods for Better Immunity",
      excerpt: "Boost your immune system with these nutrient-rich foods...",
      image: "https://images.unsplash.com/photo-1490645935967-10de6ba17061?w=400&h=300&fit=crop",
      category: "Health",
      date: "3 hours ago",
      views: 2800,
    },
  ],
  travel: [
    {
      id: "tr1",
      title: "Hidden Gems: Best Underrated Travel Destinations",
      excerpt: "Discover beautiful places away from the tourist crowds...",
      image: "https://images.unsplash.com/photo-1488646953014-85cb44e25828?w=400&h=300&fit=crop",
      category: "Travel",
      date: "1 day ago",
      views: 1900,
    },
  ],
}

export function DontMissSection() {
  const [activeTab, setActiveTab] = useState("all")
  const posts = postsByCategory[activeTab] || postsByCategory.all

  return (
    <section className="py-8">
      <div className="container mx-auto px-4">
        {/* Section Header */}
        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
          <h2 className="text-xl font-bold text-gray-900 flex items-center gap-2">
            <span className="w-1 h-6 bg-red-600"></span>
            DON&apos;T MISS
          </h2>
          <ResponsiveTabs
            tabs={tabs}
            activeTab={activeTab}
            onTabChange={setActiveTab}
            className="w-full sm:w-auto"
          />
        </div>

        {/* Posts Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
          {posts.map((post, index) => (
            <article
              key={post.id}
              className={`group ${index === 0 ? "md:col-span-2 md:row-span-2" : ""}`}
            >
              <Link href={`/blog/${post.id}`} className="block">
                <div className={`relative overflow-hidden rounded-lg ${index === 0 ? "aspect-[4/3]" : "aspect-[16/10]"}`}>
                  <Image
                    src={post.image}
                    alt={post.title}
                    fill
                    className="object-cover transition-transform duration-500 group-hover:scale-105"
                  />
                  <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent" />
                  <span className="absolute top-3 left-3 px-3 py-1 bg-red-600 text-white text-xs font-medium rounded">
                    {post.category}
                  </span>
                  <div className="absolute bottom-0 left-0 right-0 p-4">
                    <h3 className={`font-bold text-white leading-tight ${index === 0 ? "text-xl md:text-2xl" : "text-sm"}`}>
                      {post.title}
                    </h3>
                    {index === 0 && (
                      <p className="text-gray-300 text-sm mt-2 line-clamp-2 hidden md:block">
                        {post.excerpt}
                      </p>
                    )}
                    <div className="flex items-center gap-4 mt-2 text-gray-400 text-xs">
                      <span className="flex items-center gap-1">
                        <Clock className="h-3 w-3" />
                        {post.date}
                      </span>
                      <span className="flex items-center gap-1">
                        <Eye className="h-3 w-3" />
                        {post.views.toLocaleString()}
                      </span>
                    </div>
                  </div>
                </div>
              </Link>
            </article>
          ))}
        </div>
      </div>
    </section>
  )
}
