/**
 * Server-safe utility functions for blog content processing
 * These functions work without browser APIs (DOMParser)
 */

/**
 * Add IDs to heading tags in HTML content using regex
 * This is server-safe and works without DOMParser
 */
export function addIdsToHeadings(content: string): string {
  if (!content) return content

  let index = 0
  // Match h2 and h3 tags, capturing the tag name and inner content
  return content.replace(/<(h[23])([^>]*)>([^<]*)<\/\1>/gi, (match, tag, attrs, text) => {
    const cleanText = text.trim()
    const id = `heading-${index}-${cleanText.toLowerCase().replace(/[^a-z0-9]+/g, "-").replace(/^-+|-+$/g, "")}`
    index++
    
    // Check if there's already an id attribute
    if (attrs.includes('id=')) {
      return match.replace(/id="[^"]*"/, `id="${id}" data-toc-id="${id}"`)
    }
    
    return `<${tag}${attrs} id="${id}" data-toc-id="${id}" style="scroll-margin-top: 120px;">${text}</${tag}>`
  })
}

/**
 * Extract headings from HTML content using regex
 * Server-safe alternative to DOMParser
 */
export function extractHeadings(content: string): Array<{
  id: string
  text: string
  level: number
}> {
  if (!content) return []

  const headings: Array<{ id: string; text: string; level: number }> = []
  let index = 0
  
  // Match h2 and h3 tags
  const regex = /<(h[23])([^>]*)>([^<]*)<\/\1>/gi
  let match
  
  while ((match = regex.exec(content)) !== null) {
    const tag = match[1].toLowerCase()
    const text = match[3].trim()
    const level = tag === 'h2' ? 2 : 3
    const id = `heading-${index}-${text.toLowerCase().replace(/[^a-z0-9]+/g, "-").replace(/^-+|-+$/g, "")}`
    
    headings.push({ id, text, level })
    index++
  }

  return headings
}
