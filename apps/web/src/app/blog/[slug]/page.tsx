"use client"

import Image from "next/image"
import Link from "next/link"
import { Clock, MessageCircle, ArrowLeft, Share2, Bookmark } from "lucide-react"
import { Button } from "@/components/ui/button"

interface BlogPostPageProps {
  params: Promise<{ slug: string }>
}

const blogPosts: Record<string, BlogPost> = {
  "best-web-hosting-providers-2024": {
    id: "1",
    title: "Best Web Hosting Providers for 2024",
    excerpt: "Discover the top hosting services with excellent uptime, speed, and customer support for your website needs.",
    image: "https://images.unsplash.com/photo-1544197150-b99a580bb7a8?w=1200&h=600&fit=crop",
    category: "Hosting",
    categoryColor: "bg-blue-600",
    author: "Sarah Johnson",
    authorImage: "https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100&h=100&fit=crop",
    date: "Dec 15, 2024",
    readTime: "8 min read",
    comments: 28,
    content: `
## Introduction

Choosing the right web hosting provider is crucial for your website's success. With so many options available, it can be overwhelming to find the perfect fit. In this comprehensive guide, we'll explore the best web hosting providers for 2024, based on performance, reliability, pricing, and customer support.

## Why Web Hosting Matters

Your web host is the foundation of your online presence. A good hosting provider ensures:

- **Fast loading speeds** - Critical for user experience and SEO
- **High uptime** - Your site stays online 24/7
- **Security features** - Protection against cyber threats
- **Scalability** - Room to grow as your traffic increases
- **Reliable support** - Help when you need it most

## Top 5 Web Hosting Providers for 2024

### 1. Bluehost - Best Overall

Bluehost continues to dominate the hosting market with its perfect balance of features, pricing, and reliability. Officially recommended by WordPress.org, it's ideal for beginners and professionals alike.

**Key Features:**
- Free domain for the first year
- Free SSL certificate
- 1-click WordPress installation
- 24/7 customer support
- 99.9% uptime guarantee

**Pricing:** Starting at $2.95/month

### 2. SiteGround - Best for Speed

SiteGround is renowned for its exceptional performance and cutting-edge technology. Their custom-built platform delivers blazing-fast loading times.

**Key Features:**
- Google Cloud infrastructure
- SuperCacher for enhanced speed
- Free daily backups
- Advanced security features
- Outstanding customer support

**Pricing:** Starting at $2.99/month

### 3. HostGator - Best for Beginners

HostGator offers an intuitive interface and comprehensive features that make it perfect for those just starting their online journey.

**Key Features:**
- Unmetered bandwidth
- Free website migration
- 45-day money-back guarantee
- One-click installs
- 24/7/365 support

**Pricing:** Starting at $2.75/month

### 4. Cloudways - Best Cloud Hosting

For those seeking premium managed cloud hosting, Cloudways offers an excellent platform with top-tier performance.

**Key Features:**
- Multiple cloud providers (AWS, Google Cloud, etc.)
- Managed security and backups
- Staging environments
- Advanced caching
- Pay-as-you-go pricing

**Pricing:** Starting at $11/month

### 5. A2 Hosting - Best for Developers

A2 Hosting stands out with its developer-friendly features and commitment to speed.

**Key Features:**
- Turbo servers (20x faster)
- Developer-friendly tools
- Free site migration
- Anytime money-back guarantee
- Green hosting (carbon neutral)

**Pricing:** Starting at $2.99/month

## How to Choose the Right Host

When selecting a web hosting provider, consider these factors:

### 1. Website Type
- **Blog/Personal site**: Shared hosting is sufficient
- **Business site**: VPS or cloud hosting for reliability
- **E-commerce**: Dedicated resources and enhanced security
- **High-traffic**: Cloud or dedicated hosting

### 2. Technical Requirements
- Storage space needed
- Bandwidth requirements
- Database support
- Programming language support

### 3. Budget Considerations
- Initial promotional pricing vs. renewal rates
- Hidden fees and add-ons
- Money-back guarantee terms

### 4. Support Quality
- 24/7 availability
- Response times
- Support channels (chat, phone, email)

## Performance Comparison

| Provider | Uptime | Load Time | Support Score |
|----------|--------|-----------|---------------|
| Bluehost | 99.98% | 1.2s | 9.2/10 |
| SiteGround | 99.99% | 0.8s | 9.5/10 |
| HostGator | 99.97% | 1.4s | 8.8/10 |
| Cloudways | 99.99% | 0.6s | 9.0/10 |
| A2 Hosting | 99.97% | 1.0s | 8.9/10 |

## Security Features to Look For

Essential security features every host should provide:

- **SSL Certificates** - Encrypt data transmission
- **DDoS Protection** - Prevent attacks
- **Malware Scanning** - Regular security checks
- **Automatic Backups** - Protect your data
- **Firewall Protection** - Block malicious traffic

## Conclusion

The best web hosting provider depends on your specific needs. For most users, **Bluehost** offers the best balance of features and affordability. If speed is your priority, **SiteGround** is unmatched. For cloud hosting enthusiasts, **Cloudways** delivers exceptional performance.

Remember to take advantage of money-back guarantees to test different providers risk-free. Your website's success starts with a solid hosting foundation.
    `,
  },
}

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
  readTime: string
  comments: number
  content: string
}

export default function BlogPostPage({ params }: BlogPostPageProps) {
  // For demo, using slug directly. In production, fetch from API
  const post = blogPosts["best-web-hosting-providers-2024"]

  if (!post) {
    return (
      <div className="min-h-screen bg-white">
        <div className="container mx-auto px-4 py-16 text-center">
          <h1 className="text-2xl font-bold text-gray-900 mb-4">Article Not Found</h1>
          <p className="text-gray-600 mb-6">The article you&apos;re looking for doesn&apos;t exist.</p>
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
      {/* Header Image */}
      <div className="relative h-[400px] md:h-[500px] overflow-hidden">
        <Image
          src={post.image}
          alt={post.title}
          fill
          priority
          className="object-cover"
        />
        <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent" />
        
        {/* Category Badge */}
        <div className="absolute top-6 left-6">
          <span className={`${post.categoryColor} text-white text-sm font-medium px-4 py-1.5 rounded-full`}>
            {post.category}
          </span>
        </div>

        {/* Title Overlay */}
        <div className="absolute bottom-0 left-0 right-0 p-6 md:p-12">
          <div className="container mx-auto max-w-4xl">
            <h1 className="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-4 leading-tight">
              {post.title}
            </h1>
            <p className="text-gray-200 text-lg md:text-xl max-w-2xl">
              {post.excerpt}
            </p>
          </div>
        </div>
      </div>

      {/* Content */}
      <div className="container mx-auto px-4 py-8">
        <div className="max-w-4xl mx-auto">
          {/* Back Link & Actions */}
          <div className="flex items-center justify-between mb-8">
            <Button asChild variant="ghost" className="text-gray-600 hover:text-gray-900">
              <Link href="/blog">
                <ArrowLeft className="mr-2 h-4 w-4" />
                Back to Blog
              </Link>
            </Button>
            <div className="flex items-center gap-2">
              <Button variant="ghost" size="icon" className="text-gray-600">
                <Bookmark className="h-5 w-5" />
              </Button>
              <Button variant="ghost" size="icon" className="text-gray-600">
                <Share2 className="h-5 w-5" />
              </Button>
            </div>
          </div>

          {/* Author & Meta */}
          <div className="flex items-center justify-between py-6 border-y border-gray-100 mb-8">
            <div className="flex items-center gap-3">
              <Image
                src={post.authorImage}
                alt={post.author}
                width={48}
                height={48}
                className="rounded-full"
              />
              <div>
                <p className="font-semibold text-gray-900">{post.author}</p>
                <p className="text-sm text-gray-500">Published on {post.date}</p>
              </div>
            </div>
            <div className="flex items-center gap-4 text-sm text-gray-500">
              <span className="flex items-center gap-1">
                <Clock className="h-4 w-4" />
                {post.readTime}
              </span>
              <span className="flex items-center gap-1">
                <MessageCircle className="h-4 w-4" />
                {post.comments} comments
              </span>
            </div>
          </div>

          {/* Article Content */}
          <div 
            className="prose prose-lg max-w-none prose-headings:font-bold prose-headings:text-gray-900 prose-p:text-gray-600 prose-li:text-gray-600 prose-strong:text-gray-900 prose-a:text-blue-600 prose-a:no-underline hover:prose-a:underline"
            dangerouslySetInnerHTML={{ __html: post.content.replace(/\n/g, '<br/>') }}
          />

          {/* Tags */}
          <div className="mt-12 pt-8 border-t border-gray-100">
            <h3 className="text-sm font-semibold text-gray-900 mb-3">Related Topics</h3>
            <div className="flex flex-wrap gap-2">
              {["Web Hosting", "Cloud Hosting", "WordPress", "Website Speed", "SEO"].map((tag) => (
                <Link
                  key={tag}
                  href={`/blog?tag=${tag.toLowerCase().replace(' ', '-')}`}
                  className="px-4 py-2 bg-gray-100 text-gray-700 rounded-full text-sm font-medium hover:bg-gray-200 transition-colors"
                >
                  {tag}
                </Link>
              ))}
            </div>
          </div>

          {/* Comments Section Placeholder */}
          <div className="mt-12 pt-8 border-t border-gray-100">
            <h3 className="text-xl font-bold text-gray-900 mb-6">Comments ({post.comments})</h3>
            <div className="bg-gray-50 rounded-xl p-6 text-center">
              <p className="text-gray-500">Comments are currently disabled for this article.</p>
            </div>
          </div>
        </div>
      </div>
    </article>
  )
}
