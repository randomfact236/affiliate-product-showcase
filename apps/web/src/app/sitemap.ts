import { MetadataRoute } from "next"

export default async function sitemap(): Promise<MetadataRoute.Sitemap> {
  const baseUrl = process.env.NEXT_PUBLIC_SITE_URL || "http://localhost:3000"

  // Static pages
  const staticPages: MetadataRoute.Sitemap = [
    {
      url: baseUrl,
      lastModified: new Date(),
      changeFrequency: "daily",
      priority: 1,
    },
    {
      url: `${baseUrl}/products`,
      lastModified: new Date(),
      changeFrequency: "daily",
      priority: 0.9,
    },
    {
      url: `${baseUrl}/categories`,
      lastModified: new Date(),
      changeFrequency: "weekly",
      priority: 0.8,
    },
    {
      url: `${baseUrl}/admin`,
      lastModified: new Date(),
      changeFrequency: "monthly",
      priority: 0.3,
    },
  ]

  // In production, fetch dynamic products and categories from API
  // const productsRes = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/products?status=PUBLISHED&limit=1000`)
  // const products = await productsRes.json()
  // const productPages = products.data.map((product) => ({
  //   url: `${baseUrl}/products/${product.slug}`,
  //   lastModified: new Date(product.updatedAt),
  //   changeFrequency: "weekly" as const,
  //   priority: 0.8,
  // }))

  // const categoriesRes = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/categories`)
  // const categories = await categoriesRes.json()
  // const categoryPages = categories.map((category) => ({
  //   url: `${baseUrl}/categories/${category.slug}`,
  //   lastModified: new Date(category.updatedAt),
  //   changeFrequency: "weekly" as const,
  //   priority: 0.7,
  // }))

  return [...staticPages]
}
