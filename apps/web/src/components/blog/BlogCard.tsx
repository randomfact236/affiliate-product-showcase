"use client";

import Image from "next/image";
import Link from "next/link";
import { Clock, Eye } from "lucide-react";
import { cn } from "@/lib/utils";
import type { BlogPost } from "@/lib/api/blog";

interface BlogCardProps {
  post: BlogPost;
  variant?: "default" | "featured" | "compact";
  className?: string;
}

export function BlogCard({ post, variant = "default", className }: BlogCardProps) {
  const authorName = post.author
    ? `${post.author.firstName || ""} ${post.author.lastName || ""}`.trim() || "Anonymous"
    : "Anonymous";

  const category = post.categories[0];
  const tag = post.tags[0];
  const badgeColor = tag?.color || "#3b82f6";

  if (variant === "featured") {
    return (
      <article className={cn("group relative", className)}>
        <Link href={`/blog/${post.slug}`} className="block">
          <div className="relative aspect-[16/9] overflow-hidden rounded-2xl">
            <Image
              src={post.featuredImage?.mediumUrl || post.featuredImage?.originalUrl || "/images/placeholder.jpg"}
              alt={post.featuredImage?.alt || post.title}
              fill
              className="object-cover transition-transform duration-500 group-hover:scale-105"
              priority
            />
            <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent" />
            
            {/* Category Badge */}
            {category && (
              <span
                className="absolute top-4 left-4 px-3 py-1 text-white text-xs font-semibold rounded-full"
                style={{ backgroundColor: badgeColor }}
              >
                {category.name}
              </span>
            )}

            {/* Content */}
            <div className="absolute bottom-0 left-0 right-0 p-6">
              <h2 className="text-xl md:text-2xl font-bold text-white mb-2 line-clamp-2 group-hover:text-blue-200 transition-colors">
                {post.title}
              </h2>
              <p className="text-gray-200 text-sm line-clamp-2 mb-4">
                {post.excerpt}
              </p>
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-2">
                  {post.author?.avatar ? (
                    <Image
                      src={post.author.avatar}
                      alt={authorName}
                      width={32}
                      height={32}
                      className="rounded-full"
                    />
                  ) : (
                    <div className="w-8 h-8 bg-gray-600 rounded-full flex items-center justify-center text-white text-xs font-medium">
                      {authorName.charAt(0).toUpperCase()}
                    </div>
                  )}
                  <span className="text-sm text-gray-300">{authorName}</span>
                </div>
                <div className="flex items-center gap-3 text-gray-400 text-xs">
                  <span className="flex items-center gap-1">
                    <Clock className="h-3 w-3" />
                    {post.readingTime} min
                  </span>
                  <span className="flex items-center gap-1">
                    <Eye className="h-3 w-3" />
                    {post.viewCount.toLocaleString()}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </Link>
      </article>
    );
  }

  if (variant === "compact") {
    return (
      <article className={cn("group flex gap-4", className)}>
        <Link href={`/blog/${post.slug}`} className="block flex-shrink-0">
          <div className="relative w-24 h-24 overflow-hidden rounded-lg">
            <Image
              src={post.featuredImage?.thumbnailUrl || post.featuredImage?.originalUrl || "/images/placeholder.jpg"}
              alt={post.featuredImage?.alt || post.title}
              fill
              className="object-cover transition-transform duration-300 group-hover:scale-105"
            />
          </div>
        </Link>
        <div className="flex-1 min-w-0">
          {category && (
            <span
              className="inline-block px-2 py-0.5 text-white text-xs font-medium rounded mb-2"
              style={{ backgroundColor: badgeColor }}
            >
              {category.name}
            </span>
          )}
          <Link href={`/blog/${post.slug}`}>
            <h3 className="font-semibold text-gray-900 group-hover:text-blue-600 transition-colors line-clamp-2 text-sm">
              {post.title}
            </h3>
          </Link>
          <div className="flex items-center gap-2 text-gray-500 text-xs mt-2">
            <Clock className="h-3 w-3" />
            <span>{post.readingTime} min</span>
          </div>
        </div>
      </article>
    );
  }

  // Default variant
  return (
    <article className={cn("group bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow border border-gray-100", className)}>
      <Link href={`/blog/${post.slug}`} className="block">
        <div className="relative aspect-[16/10] overflow-hidden">
          <Image
            src={post.featuredImage?.mediumUrl || post.featuredImage?.originalUrl || "/images/placeholder.jpg"}
            alt={post.featuredImage?.alt || post.title}
            fill
            className="object-cover transition-transform duration-500 group-hover:scale-105"
          />
          {category && (
            <span
              className="absolute top-3 left-3 px-3 py-1 text-white text-xs font-medium rounded"
              style={{ backgroundColor: badgeColor }}
            >
              {category.name}
            </span>
          )}
        </div>
        <div className="p-5">
          <h2 className="text-lg font-bold text-gray-900 group-hover:text-blue-600 transition-colors line-clamp-2 mb-2">
            {post.title}
          </h2>
          <p className="text-gray-600 text-sm line-clamp-2 mb-4">
            {post.excerpt || "No excerpt available"}
          </p>
          <div className="flex items-center justify-between pt-4 border-t border-gray-100">
            <div className="flex items-center gap-2">
              {post.author?.avatar ? (
                <Image
                  src={post.author.avatar}
                  alt={authorName}
                  width={28}
                  height={28}
                  className="rounded-full"
                />
              ) : (
                <div className="w-7 h-7 bg-gray-300 rounded-full flex items-center justify-center text-white text-xs font-medium">
                  {authorName.charAt(0).toUpperCase()}
                </div>
              )}
              <span className="text-sm text-gray-700 truncate max-w-[100px]">{authorName}</span>
            </div>
            <div className="flex items-center gap-3 text-gray-500 text-xs">
              <span className="flex items-center gap-1">
                <Clock className="h-3 w-3" />
                {post.readingTime} min
              </span>
              <span className="flex items-center gap-1">
                <Eye className="h-3 w-3" />
                {post.viewCount.toLocaleString()}
              </span>
            </div>
          </div>
        </div>
      </Link>
    </article>
  );
}
