"use client"

import Image from "next/image"
import Link from "next/link"
import { Clock, MessageCircle, TrendingUp } from "lucide-react"

interface FeaturedPost {
  id: string
  title: string
  excerpt: string
  image: string
  category: string
  categoryColor: string
  date: string
  comments: number
  trending?: boolean
}

const featuredPosts: FeaturedPost[] = [
  {
    id: "1",
    title: "Breaking: Major Technology Breakthrough Announced by Leading Tech Giants",
    excerpt: "In a surprising move, major technology companies have announced a collaborative breakthrough that could change the industry forever...",
    image: "https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=800&h=600&fit=crop",
    category: "Tech",
    categoryColor: "bg-blue-600",
    date: "1 hour ago",
    comments: 45,
    trending: true,
  },
  {
    id: "2",
    title: "Global Markets React to New Economic Policies",
    excerpt: "Stock markets worldwide are experiencing significant shifts...",
    image: "https://images.unsplash.com/photo-1611974765270-ca1258634369?w=400&h=300&fit=crop",
    category: "Business",
    categoryColor: "bg-green-600",
    date: "2 hours ago",
    comments: 23,
  },
  {
    id: "3",
    title: "Revolutionary Health Discovery Changes Everything",
    excerpt: "Scientists have made a groundbreaking discovery...",
    image: "https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=400&h=300&fit=crop",
    category: "Health",
    categoryColor: "bg-red-500",
    date: "3 hours ago",
    comments: 67,
    trending: true,
  },
  {
    id: "4",
    title: "Celebrity Couple Announces Surprise Wedding",
    excerpt: "Fans are shocked by the unexpected announcement...",
    image: "https://images.unsplash.com/photo-1519741497674-611481863552?w=400&h=300&fit=crop",
    category: "Entertainment",
    categoryColor: "bg-purple-600",
    date: "4 hours ago",
    comments: 156,
  },
  {
    id: "5",
    title: "Championship Results: Underdog Team Takes the Title",
    excerpt: "In an unexpected turn of events, the underdog team...",
    image: "https://images.unsplash.com/photo-1461896836934- voices?w=400&h=300&fit=crop",
    category: "Sports",
    categoryColor: "bg-orange-600",
    date: "5 hours ago",
    comments: 89,
  },
]

export function HeroSection() {
  const mainPost = featuredPosts[0]
  const sidePosts = featuredPosts.slice(1)

  return (
    <section className="py-6">
      <div className="container mx-auto px-4">
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">
          {/* Main Featured Post */}
          <article className="lg:col-span-2 group">
            <Link href={`/blog/${mainPost.id}`} className="block">
              <div className="relative aspect-[16/9] lg:aspect-[16/10] overflow-hidden rounded-lg">
                <Image
                  src={mainPost.image}
                  alt={mainPost.title}
                  fill
                  priority
                  className="object-cover transition-transform duration-700 group-hover:scale-105"
                />
                <div className="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent" />
                
                {/* Category Badge */}
                <span className={`absolute top-4 left-4 px-4 py-1.5 ${mainPost.categoryColor} text-white text-sm font-semibold rounded`}>
                  {mainPost.category}
                </span>
                
                {/* Trending Badge */}
                {mainPost.trending && (
                  <span className="absolute top-4 right-4 px-3 py-1.5 bg-red-600 text-white text-xs font-semibold rounded flex items-center gap-1">
                    <TrendingUp className="h-3 w-3" />
                    TRENDING
                  </span>
                )}
                
                {/* Content */}
                <div className="absolute bottom-0 left-0 right-0 p-6">
                  <h1 className="text-2xl md:text-3xl lg:text-4xl font-bold text-white leading-tight mb-3">
                    {mainPost.title}
                  </h1>
                  <p className="text-gray-300 text-base mb-4 line-clamp-2 hidden md:block">
                    {mainPost.excerpt}
                  </p>
                  <div className="flex items-center gap-6 text-gray-400 text-sm">
                    <span className="flex items-center gap-1">
                      <Clock className="h-4 w-4" />
                      {mainPost.date}
                    </span>
                    <span className="flex items-center gap-1">
                      <MessageCircle className="h-4 w-4" />
                      {mainPost.comments} comments
                    </span>
                  </div>
                </div>
              </div>
            </Link>
          </article>

          {/* Side Posts */}
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-4">
            {sidePosts.map((post) => (
              <article key={post.id} className="group">
                <Link href={`/blog/${post.id}`} className="block">
                  <div className="relative aspect-[16/10] overflow-hidden rounded-lg">
                    <Image
                      src={post.image}
                      alt={post.title}
                      fill
                      className="object-cover transition-transform duration-500 group-hover:scale-105"
                    />
                    <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent" />
                    
                    {/* Category Badge */}
                    <span className={`absolute top-3 left-3 px-2 py-1 ${post.categoryColor} text-white text-xs font-medium rounded`}>
                      {post.category}
                    </span>
                    
                    {/* Content */}
                    <div className="absolute bottom-0 left-0 right-0 p-4">
                      <h3 className="text-white font-semibold text-sm leading-tight line-clamp-2 mb-2">
                        {post.title}
                      </h3>
                      <div className="flex items-center gap-4 text-gray-400 text-xs">
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
        </div>
      </div>
    </section>
  )
}
