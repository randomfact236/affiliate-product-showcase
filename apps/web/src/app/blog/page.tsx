"use client";

import { useState, useEffect, useMemo } from "react";
import Link from "next/link";
import { ArrowRight, Search, Loader2, RefreshCw } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { cn } from "@/lib/utils";
import { BlogCard } from "@/components/blog/BlogCard";
import { getBlogPosts, type BlogPost } from "@/lib/api/blog";

interface Category {
  id: string;
  name: string;
  color: string;
}

const categories: Category[] = [
  { id: "all", name: "All", color: "bg-gray-800" },
  { id: "hosting", name: "Hosting", color: "bg-blue-600" },
  { id: "ai", name: "AI", color: "bg-purple-600" },
  { id: "seo", name: "SEO", color: "bg-green-600" },
  { id: "marketing", name: "Marketing", color: "bg-orange-600" },
  { id: "writing", name: "Writing", color: "bg-pink-600" },
  { id: "design", name: "Design", color: "bg-red-600" },
  { id: "analytics", name: "Analytics", color: "bg-teal-600" },
];

export default function BlogPage() {
  const [activeCategory, setActiveCategory] = useState("all");
  const [searchQuery, setSearchQuery] = useState("");
  const [posts, setPosts] = useState<BlogPost[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [page, setPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [hasMore, setHasMore] = useState(false);

  const fetchPosts = async (pageNum: number = 1, append: boolean = false) => {
    try {
      setLoading(true);
      setError(null);

      const response = await getBlogPosts({
        page: pageNum,
        limit: 9,
        search: searchQuery || undefined,
        sortBy: "publishedAt",
        sortOrder: "desc",
      });

      if (append) {
        setPosts((prev) => [...prev, ...response.data]);
      } else {
        setPosts(response.data);
      }

      setTotalPages(response.meta.totalPages);
      setHasMore(pageNum < response.meta.totalPages);
    } catch (err) {
      console.error("Failed to fetch blog posts:", err);
      setError(
        err instanceof Error 
          ? `Failed to load blog posts: ${err.message}. Make sure the API server is running on port 3003.` 
          : "Failed to load blog posts. Make sure the API server is running on port 3003."
      );
    } finally {
      setLoading(false);
    }
  };

  // Initial load
  useEffect(() => {
    fetchPosts(1, false);
  }, []);

  // Search debounce
  useEffect(() => {
    const timer = setTimeout(() => {
      setPage(1);
      fetchPosts(1, false);
    }, 300);

    return () => clearTimeout(timer);
  }, [searchQuery]);

  const handleLoadMore = () => {
    const nextPage = page + 1;
    setPage(nextPage);
    fetchPosts(nextPage, true);
  };

  const filteredPosts = useMemo(() => {
    if (activeCategory === "all") return posts;
    return posts.filter((post) =>
      post.categories.some(
        (cat) => cat.slug.toLowerCase() === activeCategory.toLowerCase()
      )
    );
  }, [posts, activeCategory]);

  const featuredPost = filteredPosts[0];
  const regularPosts = filteredPosts.slice(1);

  return (
    <div className="min-h-screen bg-white">
      {/* Header */}
      <div className="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white">
        <div className="container mx-auto px-4 py-16 md:py-20">
          <div className="max-w-3xl mx-auto text-center">
            <h1 className="text-4xl md:text-5xl font-bold mb-4">Our Blog</h1>
            <p className="text-xl text-gray-300 mb-8">
              Discover the latest insights, guides, and expert recommendations
            </p>

            {/* Search Bar */}
            <div className="relative max-w-xl mx-auto">
              <Search className="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
              <Input
                type="text"
                placeholder="Search articles..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="pl-12 pr-4 py-6 bg-white/10 border-white/20 text-white placeholder:text-gray-400 rounded-full focus:bg-white/20"
              />
            </div>
          </div>
        </div>
      </div>

      {/* Category Tabs */}
      <div className="border-b bg-white sticky top-16 z-30 shadow-sm">
        <div className="container mx-auto px-4 py-4">
          <div className="flex items-center gap-2 overflow-x-auto scrollbar-hide pb-1">
            {categories.map((cat) => (
              <button
                key={cat.id}
                onClick={() => setActiveCategory(cat.id)}
                className={cn(
                  "px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-all",
                  activeCategory === cat.id
                    ? `${cat.color} text-white shadow-md`
                    : "bg-gray-100 text-gray-700 hover:bg-gray-200"
                )}
              >
                {cat.name}
              </button>
            ))}
          </div>
        </div>
      </div>

      {/* Blog Content */}
      <div className="container mx-auto px-4 py-12">
        {loading && posts.length === 0 ? (
          <div className="flex items-center justify-center py-20">
            <Loader2 className="h-8 w-8 animate-spin text-blue-600" />
          </div>
        ) : error ? (
          <div className="text-center py-16 max-w-xl mx-auto">
            <div className="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg className="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <p className="text-red-600 text-lg mb-2 font-medium">Unable to Load Blog Posts</p>
            <p className="text-gray-500 mb-6">{error}</p>
            <div className="flex flex-col sm:flex-row gap-3 justify-center">
              <Button onClick={() => fetchPosts(1, false)} variant="outline" className="gap-2">
                <RefreshCw className="h-4 w-4" />
                Try Again
              </Button>
              <Button asChild variant="default">
                <Link href="/">Go Home</Link>
              </Button>
            </div>
          </div>
        ) : filteredPosts.length === 0 ? (
          <div className="text-center py-16">
            <p className="text-gray-500 text-lg">No articles found.</p>
            {searchQuery && (
              <Button
                variant="outline"
                className="mt-4"
                onClick={() => {
                  setSearchQuery("");
                  setActiveCategory("all");
                }}
              >
                Clear Filters
              </Button>
            )}
          </div>
        ) : (
          <>
            {/* Featured Post */}
            {featuredPost && !searchQuery && activeCategory === "all" && page === 1 && (
              <div className="mb-12">
                <h2 className="text-2xl font-bold text-gray-900 mb-6">Featured Article</h2>
                <BlogCard post={featuredPost} variant="featured" />
              </div>
            )}

            {/* Blog Grid */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {(searchQuery || activeCategory !== "all" ? filteredPosts : regularPosts).map(
                (post) => (
                  <BlogCard key={post.id} post={post} variant="default" />
                )
              )}
            </div>

            {/* Load More */}
            {hasMore && (
              <div className="text-center mt-12">
                <Button
                  variant="outline"
                  size="lg"
                  onClick={handleLoadMore}
                  disabled={loading}
                  className="min-w-[200px]"
                >
                  {loading ? (
                    <Loader2 className="h-4 w-4 animate-spin mr-2" />
                  ) : (
                    <ArrowRight className="mr-2 h-4 w-4" />
                  )}
                  Load More Articles
                </Button>
              </div>
            )}
          </>
        )}
      </div>
    </div>
  );
}
