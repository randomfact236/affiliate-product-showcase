import { Metadata } from "next";
import { notFound } from "next/navigation";
import Image from "next/image";
import Link from "next/link";
import { 
  Clock, 
  Eye, 
  ArrowLeft, 
  Share2, 
  Bookmark,
  Facebook,
  Twitter,
  Linkedin,
  Calendar
} from "lucide-react";
import { Button } from "@/components/ui/button";
import { getBlogPostBySlug, getBlogPosts, getLatestPosts } from "@/lib/api/blog";
import { BlogCard } from "@/components/blog/BlogCard";
import { TableOfContents, addIdsToHeadings } from "@/components/blog/table-of-contents";

interface BlogPostPageProps {
  params: Promise<{ slug: string }>;
}

// Generate metadata for SEO
export async function generateMetadata({ params }: BlogPostPageProps): Promise<Metadata> {
  const { slug } = await params;
  
  try {
    const post = await getBlogPostBySlug(slug);
    
    return {
      title: post.metaTitle || post.title,
      description: post.metaDescription || post.excerpt || undefined,
      keywords: post.keywords || undefined,
      openGraph: {
        title: post.title,
        description: post.excerpt || undefined,
        type: "article",
        publishedTime: post.publishedAt || undefined,
        modifiedTime: post.updatedAt,
        authors: post.author ? [`${post.author.firstName} ${post.author.lastName}`] : undefined,
        images: post.featuredImage ? [{
          url: post.featuredImage.originalUrl,
          alt: post.featuredImage.alt || post.title,
        }] : undefined,
      },
    };
  } catch {
    return {
      title: "Blog Post Not Found",
    };
  }
}

// Generate static params for common blog posts
export async function generateStaticParams() {
  try {
    const response = await getBlogPosts({ limit: 20 });
    return response.data.map((post) => ({
      slug: post.slug,
    }));
  } catch {
    return [];
  }
}

export default async function BlogPostPage({ params }: BlogPostPageProps) {
  const { slug } = await params;
  
  let post;
  try {
    post = await getBlogPostBySlug(slug);
  } catch {
    notFound();
  }

  // Fetch related/latest posts for sidebar
  const latestPosts = await getLatestPosts(4);
  const relatedPosts = latestPosts.data.filter((p) => p.id !== post.id).slice(0, 3);

  const authorName = post.author
    ? `${post.author.firstName || ""} ${post.author.lastName || ""}`.trim() || "Anonymous"
    : "Anonymous";

  const publishedDate = post.publishedAt
    ? new Date(post.publishedAt).toLocaleDateString("en-US", {
        month: "long",
        day: "numeric",
        year: "numeric",
      })
    : new Date(post.createdAt).toLocaleDateString("en-US", {
        month: "long",
        day: "numeric",
        year: "numeric",
      });

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
              <Button variant="ghost" size="icon" className="text-gray-600">
                <Share2 className="h-4 w-4" />
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
          src={post.featuredImage?.originalUrl || "/images/placeholder.jpg"}
          alt={post.featuredImage?.alt || post.title}
          fill
          priority
          className="object-cover"
        />
        <div className="absolute inset-0 bg-gradient-to-t from-black via-black/40 to-transparent" />

        <div className="absolute inset-0 flex items-end">
          <div className="container mx-auto px-4 pb-12">
            <div className="max-w-4xl">
              {/* Categories */}
              {post.categories.length > 0 && (
                <div className="flex flex-wrap gap-2 mb-4">
                  {post.categories.map((category) => (
                    <Link
                      key={category.id}
                      href={`/blog?category=${category.slug}`}
                      className="bg-blue-600 text-white text-sm font-semibold px-4 py-1.5 rounded-full hover:bg-blue-700 transition-colors"
                    >
                      {category.name}
                    </Link>
                  ))}
                </div>
              )}

              <h1 className="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-4 leading-tight">
                {post.title}
              </h1>

              {post.excerpt && (
                <p className="text-gray-200 text-lg md:text-xl max-w-2xl">
                  {post.excerpt}
                </p>
              )}
            </div>
          </div>
        </div>
      </div>

      {/* Main Content */}
      <div className="container mx-auto px-4 py-12">
        <div className="max-w-6xl mx-auto">
          <div className="grid lg:grid-cols-[1fr_320px] gap-12">
            {/* Article Content */}
            <div>
              {/* Author Bar */}
              <div className="flex flex-wrap items-center justify-between gap-4 py-6 border-b border-gray-100 mb-8">
                <div className="flex items-center gap-4">
                  {post.author?.avatar ? (
                    <Image
                      src={post.author.avatar}
                      alt={authorName}
                      width={56}
                      height={56}
                      className="rounded-full ring-2 ring-gray-100"
                    />
                  ) : (
                    <div className="w-14 h-14 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white text-xl font-bold">
                      {authorName.charAt(0).toUpperCase()}
                    </div>
                  )}
                  <div>
                    <p className="font-bold text-gray-900">{authorName}</p>
                    <div className="flex items-center gap-3 text-sm text-gray-500">
                      <span className="flex items-center gap-1">
                        <Calendar className="h-4 w-4" />
                        {publishedDate}
                      </span>
                    </div>
                  </div>
                </div>
                <div className="flex items-center gap-4 text-sm text-gray-500">
                  <span className="flex items-center gap-1">
                    <Clock className="h-4 w-4" />
                    {post.readingTime} min read
                  </span>
                  <span className="flex items-center gap-1">
                    <Eye className="h-4 w-4" />
                    {post.viewCount.toLocaleString()} views
                  </span>
                </div>
              </div>

              {/* Article Body */}
              <div
                className="prose prose-lg max-w-none prose-headings:text-gray-900 prose-p:text-gray-600 prose-a:text-blue-600 hover:prose-a:text-blue-700 prose-img:rounded-xl prose-img:shadow-lg"
                dangerouslySetInnerHTML={{ __html: addIdsToHeadings(post.content) }}
              />

              {/* Tags */}
              {post.tags.length > 0 && (
                <div className="mt-12 pt-8 border-t border-gray-100">
                  <h3 className="text-sm font-semibold text-gray-900 mb-4">Tags</h3>
                  <div className="flex flex-wrap gap-2">
                    {post.tags.map((tag) => (
                      <Link
                        key={tag.id}
                        href={`/blog?tag=${tag.slug}`}
                        className="px-3 py-1.5 bg-gray-100 text-gray-700 rounded-full text-sm hover:bg-blue-100 hover:text-blue-700 transition-colors"
                        style={tag.color ? { backgroundColor: `${tag.color}20`, color: tag.color } : undefined}
                      >
                        #{tag.name}
                      </Link>
                    ))}
                  </div>
                </div>
              )}

              {/* Share Section */}
              <div className="mt-12 pt-8 border-t border-gray-100">
                <h3 className="text-sm font-semibold text-gray-900 mb-4">Share this article</h3>
                <div className="flex flex-wrap gap-3">
                  <Button variant="outline" size="sm" className="gap-2">
                    <Twitter className="h-4 w-4" /> Twitter
                  </Button>
                  <Button variant="outline" size="sm" className="gap-2">
                    <Facebook className="h-4 w-4" /> Facebook
                  </Button>
                  <Button variant="outline" size="sm" className="gap-2">
                    <Linkedin className="h-4 w-4" /> LinkedIn
                  </Button>
                </div>
              </div>
            </div>

            {/* Sidebar */}
            <aside className="space-y-8">
              {/* Table of Contents */}
              <TableOfContents content={post.content} />

              {/* Related Posts */}
              {relatedPosts.length > 0 && (
                <div className="bg-gray-50 rounded-2xl p-6">
                  <h3 className="font-bold text-gray-900 mb-4">Related Articles</h3>
                  <div className="space-y-4">
                    {relatedPosts.map((relatedPost) => (
                      <BlogCard
                        key={relatedPost.id}
                        post={relatedPost}
                        variant="compact"
                      />
                    ))}
                  </div>
                </div>
              )}

              {/* Newsletter */}
              <div className="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl p-6 text-white">
                <h3 className="font-bold text-lg mb-2">Stay Updated</h3>
                <p className="text-blue-100 text-sm mb-4">
                  Get the latest articles and insights delivered to your inbox.
                </p>
                <form className="space-y-3">
                  <input
                    type="email"
                    placeholder="Enter your email"
                    className="w-full px-4 py-2.5 rounded-lg text-gray-900 text-sm focus:outline-none focus:ring-2 focus:ring-white/50"
                  />
                  <Button className="w-full bg-white text-blue-600 hover:bg-blue-50 font-semibold">
                    Subscribe
                  </Button>
                </form>
              </div>

              {/* Categories */}
              {post.categories.length > 0 && (
                <div className="bg-white border border-gray-200 rounded-2xl p-6">
                  <h3 className="font-bold text-gray-900 mb-4">Categories</h3>
                  <div className="flex flex-wrap gap-2">
                    {post.categories.map((category) => (
                      <Link
                        key={category.id}
                        href={`/blog?category=${category.slug}`}
                        className="px-3 py-1.5 bg-gray-100 text-gray-700 rounded-full text-sm hover:bg-blue-100 hover:text-blue-700 transition-colors"
                      >
                        {category.name}
                      </Link>
                    ))}
                  </div>
                </div>
              )}
            </aside>
          </div>
        </div>
      </div>
    </article>
  );
}
