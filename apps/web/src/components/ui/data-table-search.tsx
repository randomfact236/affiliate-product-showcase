"use client"

import { Search, X } from "lucide-react"
import { Input } from "@/components/ui/input"
import { Button } from "@/components/ui/button"

interface DataTableSearchProps {
  value: string
  onChange: (value: string) => void
  placeholder?: string
}

export function DataTableSearch({ 
  value, 
  onChange, 
  placeholder = "Search..." 
}: DataTableSearchProps) {
  return (
    <div className="relative w-full sm:w-[300px]">
      <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
      <Input
        type="search"
        placeholder={placeholder}
        value={value}
        onChange={(e) => onChange(e.target.value)}
        className="pl-8 pr-8"
      />
      {value && (
        <Button
          variant="ghost"
          size="sm"
          className="absolute right-0 top-0 h-full px-2 hover:bg-transparent"
          onClick={() => onChange("")}
        >
          <X className="h-4 w-4 text-muted-foreground" />
        </Button>
      )}
    </div>
  )
}
