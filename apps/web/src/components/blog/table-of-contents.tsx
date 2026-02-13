"use client"

import React, { useState, useEffect } from "react"
import { cn } from "@/lib/utils"
import { List, ChevronRight } from "lucide-react"
import { extractHeadings } from "@/lib/blog-utils"

interface TOCItem {
  id: string
  text: string
  level: number
}

interface TableOfContentsProps {
  content: string
  className?: string
}

export function TableOfContents({ content, className }: TableOfContentsProps) {
  const [headings, setHeadings] = useState<TOCItem[]>([])
  const [activeId, setActiveId] = useState<string>("")

  // Extract headings from HTML content
  useEffect(() => {
    if (!content) return
    setHeadings(extractHeadings(content))
  }, [content])

  // Track active heading on scroll
  useEffect(() => {
    if (headings.length === 0) return

    const handleScroll = () => {
      const headingElements = document.querySelectorAll("[data-toc-id]")
      let current = ""

      headingElements.forEach((element) => {
        const rect = element.getBoundingClientRect()
        if (rect.top <= 150) {
          current = element.getAttribute("data-toc-id") || ""
        }
      })

      setActiveId(current)
    }

    window.addEventListener("scroll", handleScroll, { passive: true })
    handleScroll() // Initial check

    return () => window.removeEventListener("scroll", handleScroll)
  }, [headings])

  const scrollToHeading = (id: string) => {
    const element = document.querySelector(`[data-toc-id="${id}"]`)
    if (element) {
      const offset = 120 // Account for sticky header
      const elementPosition = element.getBoundingClientRect().top + window.scrollY
      window.scrollTo({
        top: elementPosition - offset,
        behavior: "smooth",
      })
    }
  }

  if (headings.length === 0) return null

  return (
    <div className={cn("bg-white border border-gray-200 rounded-2xl p-6", className)}>
      <div className="flex items-center gap-2 mb-4">
        <List className="h-5 w-5 text-blue-600" />
        <h3 className="font-bold text-gray-900">Table of Contents</h3>
      </div>
      
      <nav className="space-y-1">
        {headings.map((heading) => (
          <button
            key={heading.id}
            onClick={() => scrollToHeading(heading.id)}
            className={cn(
              "w-full text-left text-sm py-2 px-3 rounded-lg transition-colors flex items-start gap-2",
              "hover:bg-gray-100",
              heading.level === 3 && "pl-6",
              activeId === heading.id
                ? "bg-blue-50 text-blue-700 font-medium"
                : "text-gray-600"
            )}
          >
            <ChevronRight 
              className={cn(
                "h-4 w-4 mt-0.5 shrink-0 transition-colors",
                activeId === heading.id ? "text-blue-600" : "text-gray-400"
              )} 
            />
            <span className="line-clamp-2">{heading.text}</span>
          </button>
        ))}
      </nav>
    </div>
  )
}


