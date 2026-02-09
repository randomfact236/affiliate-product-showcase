import { Metadata } from "next"
import Link from "next/link"
import { Card, CardContent } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { FolderOpen, Plus, ArrowRight } from "lucide-react"

export const metadata: Metadata = {
  title: "Categories",
  description: "Browse products by category",
}

export default function CategoriesPage() {
  return (
    <div className="container mx-auto px-4 py-8">
      {/* Header */}
      <div className="mb-8">
        <h1 className="text-4xl font-bold tracking-tight">Categories</h1>
        <p className="mt-2 text-muted-foreground">
          Browse our products by category
        </p>
      </div>

      {/* Empty State */}
      <Card className="py-16">
        <CardContent className="flex flex-col items-center justify-center text-center">
          <div className="mb-4 rounded-full bg-muted p-4">
            <FolderOpen className="h-8 w-8 text-muted-foreground" />
          </div>
          <h2 className="text-xl font-semibold">No Categories Yet</h2>
          <p className="mt-2 max-w-md text-muted-foreground">
            Categories will appear here once they are added to the system
            through the admin dashboard.
          </p>
          <div className="mt-6 flex gap-4">
            <Button variant="outline" asChild>
              <Link href="/products">
                Browse All Products
                <ArrowRight className="ml-2 h-4 w-4" />
              </Link>
            </Button>
            <Button asChild>
              <Link href="/admin/categories">
                <Plus className="mr-2 h-4 w-4" />
                Add Categories
              </Link>
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  )
}
