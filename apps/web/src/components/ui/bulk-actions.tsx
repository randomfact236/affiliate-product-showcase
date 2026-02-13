"use client"

import { Trash2, Power, PowerOff, Download } from "lucide-react"
import { Button } from "@/components/ui/button"

interface BulkActionsProps {
  selectedCount: number
  onDelete: () => void
  onActivate: () => void
  onDeactivate: () => void
  onExport: () => void
}

export function BulkActions({
  selectedCount,
  onDelete,
  onActivate,
  onDeactivate,
  onExport,
}: BulkActionsProps) {
  if (selectedCount === 0) return null

  return (
    <div className="flex items-center gap-2 px-4 py-2 bg-blue-50 border border-blue-200 rounded-lg">
      <span className="text-sm font-medium text-blue-700">
        {selectedCount} item{selectedCount !== 1 ? "s" : ""} selected
      </span>
      <div className="h-4 w-px bg-blue-300 mx-2" />
      <Button
        size="sm"
        variant="outline"
        className="h-7 text-green-600 border-green-200 hover:bg-green-50"
        onClick={onActivate}
      >
        <Power className="h-3.5 w-3.5 mr-1" />
        Activate
      </Button>
      <Button
        size="sm"
        variant="outline"
        className="h-7 text-amber-600 border-amber-200 hover:bg-amber-50"
        onClick={onDeactivate}
      >
        <PowerOff className="h-3.5 w-3.5 mr-1" />
        Deactivate
      </Button>
      <Button
        size="sm"
        variant="outline"
        className="h-7 text-blue-600 border-blue-200 hover:bg-blue-50"
        onClick={onExport}
      >
        <Download className="h-3.5 w-3.5 mr-1" />
        Export
      </Button>
      <Button
        size="sm"
        variant="outline"
        className="h-7 text-red-600 border-red-200 hover:bg-red-50"
        onClick={onDelete}
      >
        <Trash2 className="h-3.5 w-3.5 mr-1" />
        Delete
      </Button>
    </div>
  )
}
