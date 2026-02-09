"use client"

import { useState, useRef, useEffect } from "react"
import { ChevronDown } from "lucide-react"
import { cn } from "@/lib/utils"

interface Tab {
  id: string
  label: string
}

interface ResponsiveTabsProps {
  tabs: Tab[]
  activeTab: string
  onTabChange: (tabId: string) => void
  className?: string
}

export function ResponsiveTabs({ tabs, activeTab, onTabChange, className }: ResponsiveTabsProps) {
  const [visibleTabs, setVisibleTabs] = useState<Tab[]>(tabs)
  const [overflowTabs, setOverflowTabs] = useState<Tab[]>([])
  const [showMoreDropdown, setShowMoreDropdown] = useState(false)
  const containerRef = useRef<HTMLDivElement>(null)
  const moreButtonRef = useRef<HTMLButtonElement>(null)

  useEffect(() => {
    const calculateVisibleTabs = () => {
      if (!containerRef.current) return

      const containerWidth = containerRef.current.offsetWidth
      const moreButtonWidth = 80 // Approximate width of "More" button
      const tabMinWidth = 80 // Minimum width per tab
      
      // Calculate how many tabs can fit
      const availableWidth = containerWidth - moreButtonWidth
      const maxVisibleTabs = Math.floor(availableWidth / tabMinWidth)
      
      if (tabs.length <= maxVisibleTabs) {
        setVisibleTabs(tabs)
        setOverflowTabs([])
      } else {
        setVisibleTabs(tabs.slice(0, maxVisibleTabs))
        setOverflowTabs(tabs.slice(maxVisibleTabs))
      }
    }

    calculateVisibleTabs()
    window.addEventListener("resize", calculateVisibleTabs)
    return () => window.removeEventListener("resize", calculateVisibleTabs)
  }, [tabs])

  // Close dropdown when clicking outside
  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (
        moreButtonRef.current &&
        !moreButtonRef.current.contains(event.target as Node)
      ) {
        setShowMoreDropdown(false)
      }
    }

    document.addEventListener("mousedown", handleClickOutside)
    return () => document.removeEventListener("mousedown", handleClickOutside)
  }, [])

  const isActiveInOverflow = overflowTabs.some(tab => tab.id === activeTab)

  return (
    <div ref={containerRef} className={cn("flex items-center gap-1", className)}>
      {visibleTabs.map((tab) => (
        <button
          key={tab.id}
          onClick={() => onTabChange(tab.id)}
          className={cn(
            "px-4 py-2 text-sm font-medium whitespace-nowrap transition-colors",
            activeTab === tab.id
              ? "bg-red-600 text-white"
              : "bg-gray-100 text-gray-700 hover:bg-gray-200"
          )}
        >
          {tab.label}
        </button>
      ))}
      
      {overflowTabs.length > 0 && (
        <div className="relative">
          <button
            ref={moreButtonRef}
            onClick={() => setShowMoreDropdown(!showMoreDropdown)}
            className={cn(
              "px-4 py-2 text-sm font-medium flex items-center gap-1 transition-colors",
              isActiveInOverflow
                ? "bg-red-600 text-white"
                : "bg-gray-100 text-gray-700 hover:bg-gray-200"
            )}
          >
            More
            <ChevronDown className={cn("h-4 w-4 transition-transform", showMoreDropdown && "rotate-180")} />
          </button>
          
          {showMoreDropdown && (
            <div className="absolute top-full right-0 mt-1 bg-white shadow-lg border border-gray-200 rounded-md py-1 min-w-[150px] z-50">
              {overflowTabs.map((tab) => (
                <button
                  key={tab.id}
                  onClick={() => {
                    onTabChange(tab.id)
                    setShowMoreDropdown(false)
                  }}
                  className={cn(
                    "w-full px-4 py-2 text-left text-sm transition-colors hover:bg-gray-50",
                    activeTab === tab.id ? "text-red-600 font-medium" : "text-gray-700"
                  )}
                >
                  {tab.label}
                </button>
              ))}
            </div>
          )}
        </div>
      )}
    </div>
  )
}
