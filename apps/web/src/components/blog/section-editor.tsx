"use client"

import React from "react"
import { cn } from "@/lib/utils"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { RichTextEditor } from "@/components/ui/rich-text-editor"
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select"
import { Button } from "@/components/ui/button"
import { GripVertical, Trash2, Type, AlignLeft, Heading } from "lucide-react"

export type SectionType = "heading" | "content" | "heading-content"

export interface BlogSection {
  id: string
  type: SectionType
  heading?: string
  content?: string
  spacing: {
    top: number
    bottom: number
  }
}

interface SectionEditorProps {
  section: BlogSection
  onUpdate: (section: BlogSection) => void
  onDelete: () => void
  onMoveUp?: () => void
  onMoveDown?: () => void
  isFirst?: boolean
  isLast?: boolean
}

const spacingOptions = [
  { value: "0", label: "None" },
  { value: "2", label: "Small (8px)" },
  { value: "4", label: "Medium (16px)" },
  { value: "6", label: "Large (24px)" },
  { value: "8", label: "XLarge (32px)" },
  { value: "12", label: "XXLarge (48px)" },
  { value: "16", label: "Huge (64px)" },
]

export function SectionEditor({
  section,
  onUpdate,
  onDelete,
  onMoveUp,
  onMoveDown,
  isFirst,
  isLast,
}: SectionEditorProps) {
  const updateSpacing = (position: "top" | "bottom", value: number) => {
    onUpdate({
      ...section,
      spacing: {
        ...section.spacing,
        [position]: value,
      },
    })
  }

  const renderSectionContent = () => {
    switch (section.type) {
      case "heading":
        return (
          <div className="space-y-3">
            <div className="flex items-center gap-2 text-sm font-medium text-muted-foreground">
              <Heading className="h-4 w-4" />
              <span>Heading Section</span>
            </div>
            <Input
              value={section.heading || ""}
              onChange={(e) => onUpdate({ ...section, heading: e.target.value })}
              placeholder="Enter section heading..."
              className="text-lg font-semibold"
            />
          </div>
        )

      case "content":
        return (
          <div className="space-y-3">
            <div className="flex items-center gap-2 text-sm font-medium text-muted-foreground">
              <AlignLeft className="h-4 w-4" />
              <span>Content Section</span>
            </div>
            <RichTextEditor
              value={section.content || ""}
              onChange={(value) => onUpdate({ ...section, content: value })}
              placeholder="Write your content here..."
              minHeight="200px"
            />
          </div>
        )

      case "heading-content":
        return (
          <div className="space-y-4">
            <div className="flex items-center gap-2 text-sm font-medium text-muted-foreground">
              <Type className="h-4 w-4" />
              <span>Heading + Content Section</span>
            </div>
            <Input
              value={section.heading || ""}
              onChange={(e) => onUpdate({ ...section, heading: e.target.value })}
              placeholder="Enter section heading..."
              className="text-lg font-semibold"
            />
            <RichTextEditor
              value={section.content || ""}
              onChange={(value) => onUpdate({ ...section, content: value })}
              placeholder="Write your content here..."
              minHeight="200px"
            />
          </div>
        )
    }
  }

  return (
    <div
      className={cn(
        "border rounded-lg bg-card transition-all",
        "hover:border-primary/50"
      )}
      style={{
        marginTop: `${section.spacing.top * 4}px`,
        marginBottom: `${section.spacing.bottom * 4}px`,
      }}
    >
      {/* Section Header with Controls */}
      <div className="flex items-center justify-between p-3 border-b bg-muted/30">
        <div className="flex items-center gap-2">
          <GripVertical className="h-4 w-4 text-muted-foreground cursor-move" />
          <span className="text-sm font-medium capitalize">
            {section.type.replace("-", " + ")} Section
          </span>
        </div>
        <div className="flex items-center gap-1">
          {!isFirst && onMoveUp && (
            <Button
              variant="ghost"
              size="icon"
              className="h-8 w-8"
              onClick={onMoveUp}
            >
              ↑
            </Button>
          )}
          {!isLast && onMoveDown && (
            <Button
              variant="ghost"
              size="icon"
              className="h-8 w-8"
              onClick={onMoveDown}
            >
              ↓
            </Button>
          )}
          <Button
            variant="ghost"
            size="icon"
            className="h-8 w-8 text-destructive hover:text-destructive"
            onClick={onDelete}
          >
            <Trash2 className="h-4 w-4" />
          </Button>
        </div>
      </div>

      {/* Section Content */}
      <div className="p-4 space-y-4">
        {renderSectionContent()}

        {/* Spacing Controls */}
        <div className="pt-4 border-t">
          <div className="grid grid-cols-2 gap-4">
            <div className="space-y-2">
              <Label className="text-xs text-muted-foreground">Top Spacing</Label>
              <Select
                value={section.spacing.top.toString()}
                onValueChange={(value: string) => updateSpacing("top", parseInt(value))}
              >
                <SelectTrigger className="h-8">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  {spacingOptions.map((option) => (
                    <SelectItem key={option.value} value={option.value}>
                      {option.label}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
            <div className="space-y-2">
              <Label className="text-xs text-muted-foreground">Bottom Spacing</Label>
              <Select
                value={section.spacing.bottom.toString()}
                onValueChange={(value: string) => updateSpacing("bottom", parseInt(value))}
              >
                <SelectTrigger className="h-8">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  {spacingOptions.map((option) => (
                    <SelectItem key={option.value} value={option.value}>
                      {option.label}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}

// Helper function to create new sections
export function createSection(type: SectionType): BlogSection {
  return {
    id: `section-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`,
    type,
    heading: type !== "content" ? "" : undefined,
    content: type !== "heading" ? "" : undefined,
    spacing: {
      top: 4,
      bottom: 4,
    },
  }
}

// Helper function to create sections with custom default spacing
export function createSectionWithSpacing(type: SectionType, defaultSpacing: number): BlogSection {
  return {
    id: `section-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`,
    type,
    heading: type !== "content" ? "" : undefined,
    content: type !== "heading" ? "" : undefined,
    spacing: {
      top: defaultSpacing,
      bottom: defaultSpacing,
    },
  }
}
