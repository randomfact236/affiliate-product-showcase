import { Metadata } from "next"
import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"
import Link from "next/link"
import { ArrowLeft, Plus } from "lucide-react"

interface ProductPageProps {
  params: Promise<{ slug: string }>
}

export async function generateMetadata({
  params,
}: ProductPageProps): Promise<Metadata> {
  const { slug } = await params

  return {
    title: `Product: ${slug}`,
    description: "Product details page",
  }
}

export default async function ProductPage({ params }: ProductPageProps) {
  const { slug } = await params

  // This is a placeholder - actual product data would be fetched here
  const product = null

  if (!product) {
    return (
      <div className="container mx-auto px-4 py-16">
        <Card className="py-16">
          <CardContent className="flex flex-col items-center justify-center text-center">
            <h1 className="text-2xl font-bold">Product Not Found</h1>
            <p className="mt-2 text-muted-foreground">
              The product &quot;{slug}&quot; doesn&apos;t exist or has been
              removed.
            </p>
            <div className="mt-6 flex gap-4">
              <Button variant="outline" asChild>
                <Link href="/products">
                  <ArrowLeft className="mr-2 h-4 w-4" />
                  Back to Products
                </Link>
              </Button>
              <Button asChild>
                <Link href="/admin/products">
                  <Plus className="mr-2 h-4 w-4" />
                  Add Products
                </Link>
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>
    )
  }

  return null
}
