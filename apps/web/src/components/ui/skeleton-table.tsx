"use client"

import { Skeleton } from "@/components/ui/skeleton"
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table"

interface SkeletonTableProps {
  columns: number
  rows?: number
}

// Full table skeleton with header and body - use when you need a complete table
export function SkeletonTable({ columns, rows = 5 }: SkeletonTableProps) {
  return (
    <Table>
      <TableHeader>
        <TableRow className="bg-gray-50">
          {Array.from({ length: columns }).map((_, i) => (
            <TableHead key={i}>
              <Skeleton className="h-4 w-20" />
            </TableHead>
          ))}
        </TableRow>
      </TableHeader>
      <TableBody>
        {Array.from({ length: rows }).map((_, rowIndex) => (
          <TableRow key={rowIndex}>
            {Array.from({ length: columns }).map((_, colIndex) => (
              <TableCell key={colIndex}>
                <Skeleton className="h-4 w-full" />
              </TableCell>
            ))}
          </TableRow>
        ))}
      </TableBody>
    </Table>
  )
}

// Row-only skeleton - use inside an existing TableBody
interface SkeletonTableRowsProps {
  columns: number
  rows?: number
}

export function SkeletonTableRows({ columns, rows = 5 }: SkeletonTableRowsProps) {
  return (
    <>
      {Array.from({ length: rows }).map((_, rowIndex) => (
        <TableRow key={rowIndex}>
          {Array.from({ length: columns }).map((_, colIndex) => (
            <TableCell key={colIndex}>
              <Skeleton className="h-4 w-full" />
            </TableCell>
          ))}
        </TableRow>
      ))}
    </>
  )
}

// Header-only skeleton - use inside an existing TableHeader
interface SkeletonTableHeaderProps {
  columns: number
}

export function SkeletonTableHeader({ columns }: SkeletonTableHeaderProps) {
  return (
    <TableRow className="bg-gray-50">
      {Array.from({ length: columns }).map((_, i) => (
        <TableHead key={i}>
          <Skeleton className="h-4 w-20" />
        </TableHead>
      ))}
    </TableRow>
  )
}
