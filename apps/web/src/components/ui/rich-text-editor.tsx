"use client"

import React, { useRef, useEffect } from "react"
import { cn } from "@/lib/utils"
import styles from "./rich-text-editor.module.css"
import {
  Bold,
  Italic,
  Underline,
  Strikethrough,
  AlignLeft,
  AlignCenter,
  AlignRight,
  List,
  ListOrdered,
  Link,
  Image,
  Heading1,
  Heading2,
  Heading3,
  Quote,
  Code,
  Undo,
  Redo,
} from "lucide-react"

interface RichTextEditorProps {
  value: string
  onChange: (value: string) => void
  placeholder?: string
  className?: string
  minHeight?: string
}

const ToolbarButton = ({
  onClick,
  active,
  children,
  title,
}: {
  onClick: () => void
  active?: boolean
  children: React.ReactNode
  title?: string
}) => (
  <button
    type="button"
    onClick={onClick}
    title={title}
    className={cn(
      "p-2 rounded-md transition-colors hover:bg-muted",
      active && "bg-primary text-primary-foreground hover:bg-primary/90"
    )}
  >
    {children}
  </button>
)

export function RichTextEditor({
  value,
  onChange,
  placeholder = "Write your content here...",
  className,
  minHeight = "300px",
}: RichTextEditorProps) {
  const editorRef = useRef<HTMLDivElement>(null)
  const isUpdatingRef = useRef(false)

  // Sync external value to editor
  useEffect(() => {
    if (editorRef.current && !isUpdatingRef.current && editorRef.current.innerHTML !== value) {
      editorRef.current.innerHTML = value
    }
  }, [value])

  const handleInput = () => {
    if (editorRef.current) {
      isUpdatingRef.current = true
      onChange(editorRef.current.innerHTML)
      setTimeout(() => {
        isUpdatingRef.current = false
      }, 0)
    }
  }

  const execCommand = (command: string, value: string = "") => {
    document.execCommand(command, false, value)
    handleInput()
    editorRef.current?.focus()
  }

  const formatBlock = (block: string) => {
    execCommand("formatBlock", block)
  }

  const insertLink = () => {
    const url = prompt("Enter URL:")
    if (url) {
      execCommand("createLink", url)
    }
  }

  const insertImage = () => {
    const url = prompt("Enter image URL:")
    if (url) {
      execCommand("insertImage", url)
    }
  }

  const isActive = (command: string) => {
    if (typeof document !== "undefined") {
      return document.queryCommandState(command)
    }
    return false
  }

  return (
    <div className={cn("border rounded-md overflow-hidden bg-background", className)}>
      {/* Toolbar */}
      <div className="flex flex-wrap items-center gap-1 p-2 border-b bg-muted/50">
        {/* History */}
        <div className="flex items-center gap-1 pr-2 border-r border-border">
          <ToolbarButton onClick={() => execCommand("undo")} title="Undo">
            <Undo className="h-4 w-4" />
          </ToolbarButton>
          <ToolbarButton onClick={() => execCommand("redo")} title="Redo">
            <Redo className="h-4 w-4" />
          </ToolbarButton>
        </div>

        {/* Text Style */}
        <div className="flex items-center gap-1 px-2 border-r border-border">
          <ToolbarButton onClick={() => execCommand("bold")} active={isActive("bold")} title="Bold">
            <Bold className="h-4 w-4" />
          </ToolbarButton>
          <ToolbarButton onClick={() => execCommand("italic")} active={isActive("italic")} title="Italic">
            <Italic className="h-4 w-4" />
          </ToolbarButton>
          <ToolbarButton onClick={() => execCommand("underline")} active={isActive("underline")} title="Underline">
            <Underline className="h-4 w-4" />
          </ToolbarButton>
          <ToolbarButton onClick={() => execCommand("strikeThrough")} active={isActive("strikeThrough")} title="Strikethrough">
            <Strikethrough className="h-4 w-4" />
          </ToolbarButton>
        </div>

        {/* Headings */}
        <div className="flex items-center gap-1 px-2 border-r border-border">
          <ToolbarButton onClick={() => formatBlock("H1")} title="Heading 1">
            <Heading1 className="h-4 w-4" />
          </ToolbarButton>
          <ToolbarButton onClick={() => formatBlock("H2")} title="Heading 2">
            <Heading2 className="h-4 w-4" />
          </ToolbarButton>
          <ToolbarButton onClick={() => formatBlock("H3")} title="Heading 3">
            <Heading3 className="h-4 w-4" />
          </ToolbarButton>
        </div>

        {/* Alignment */}
        <div className="flex items-center gap-1 px-2 border-r border-border">
          <ToolbarButton onClick={() => execCommand("justifyLeft")} active={isActive("justifyLeft")} title="Align Left">
            <AlignLeft className="h-4 w-4" />
          </ToolbarButton>
          <ToolbarButton onClick={() => execCommand("justifyCenter")} active={isActive("justifyCenter")} title="Align Center">
            <AlignCenter className="h-4 w-4" />
          </ToolbarButton>
          <ToolbarButton onClick={() => execCommand("justifyRight")} active={isActive("justifyRight")} title="Align Right">
            <AlignRight className="h-4 w-4" />
          </ToolbarButton>
        </div>

        {/* Lists */}
        <div className="flex items-center gap-1 px-2 border-r border-border">
          <ToolbarButton onClick={() => execCommand("insertUnorderedList")} active={isActive("insertUnorderedList")} title="Bullet List">
            <List className="h-4 w-4" />
          </ToolbarButton>
          <ToolbarButton onClick={() => execCommand("insertOrderedList")} active={isActive("insertOrderedList")} title="Numbered List">
            <ListOrdered className="h-4 w-4" />
          </ToolbarButton>
        </div>

        {/* Special */}
        <div className="flex items-center gap-1 px-2 border-r border-border">
          <ToolbarButton onClick={() => execCommand("formatBlock", "BLOCKQUOTE")} title="Quote">
            <Quote className="h-4 w-4" />
          </ToolbarButton>
          <ToolbarButton onClick={() => execCommand("formatBlock", "PRE")} title="Code Block">
            <Code className="h-4 w-4" />
          </ToolbarButton>
        </div>

        {/* Insert */}
        <div className="flex items-center gap-1 pl-2">
          <ToolbarButton onClick={insertLink} title="Insert Link">
            <Link className="h-4 w-4" />
          </ToolbarButton>
          <ToolbarButton onClick={insertImage} title="Insert Image">
            <Image className="h-4 w-4" />
          </ToolbarButton>
        </div>
      </div>

      {/* Editor */}
      <div
        ref={editorRef}
        contentEditable
        onInput={handleInput}
        onBlur={handleInput}
        className={cn("w-full p-4 outline-none", styles.editor)}
        style={{ "--min-height": minHeight } as React.CSSProperties}
        data-placeholder={placeholder}
        dangerouslySetInnerHTML={{ __html: value }}
      />
    </div>
  )
}
