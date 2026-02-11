import { PrismaClient, BlogPostStatus } from '@prisma/client';
import { slugify } from '../src/common/utils/slugify.util';

const prisma = new PrismaClient();

const blogPostsData = [
  {
    title: "Best Web Hosting Providers for 2024",
    excerpt: "Discover the top hosting services with excellent uptime, speed, and customer support for your website needs.",
    content: `
      <h2>Why Web Hosting Matters</h2>
      <p>Your web host is the foundation of your online presence. A quality hosting provider directly impacts your website's success through several key factors:</p>
      <ul>
        <li><strong>Lightning Fast Speed:</strong> Critical for user experience and SEO rankings</li>
        <li><strong>99.9% Uptime:</strong> Your site stays online when customers need it</li>
        <li><strong>Advanced Security:</strong> Protection against cyber threats and data loss</li>
        <li><strong>Easy Scalability:</strong> Grow seamlessly as your traffic increases</li>
      </ul>
      
      <h2>Our Top Picks</h2>
      <h3>1. Bluehost - Best Overall</h3>
      <p>Bluehost is officially recommended by WordPress.org and offers unbeatable value for beginners and experienced users alike. With a starting price of $2.95/month, you get:</p>
      <ul>
        <li>Free domain for the first year</li>
        <li>Free SSL certificate</li>
        <li>1-click WordPress installation</li>
        <li>24/7 expert support</li>
        <li>50GB SSD storage</li>
      </ul>
      
      <h3>2. SiteGround - Best for Speed</h3>
      <p>SiteGround offers cutting-edge technology with Google Cloud infrastructure. Their SuperCacher technology ensures your site loads in under a second.</p>
      
      <h2>Final Recommendations</h2>
      <p>Choosing the right web hosting provider depends on your specific needs. For most users, we recommend Bluehost for its perfect balance of features, performance, and price.</p>
    `,
    categorySlug: "hosting",
    tagSlugs: ["web-hosting", "wordpress", "bluehost"],
    readingTime: 12,
  },
  {
    title: "AI Tools Revolutionizing Content Creation",
    excerpt: "Explore how artificial intelligence is transforming the way we create and optimize content online.",
    content: `
      <h2>The Rise of AI in Content Creation</h2>
      <p>Artificial intelligence has fundamentally changed how content is created, edited, and optimized. From writing assistants to image generators, AI tools are making content creation more accessible and efficient than ever.</p>
      
      <h2>Top AI Writing Tools</h2>
      <h3>ChatGPT</h3>
      <p>OpenAI's ChatGPT has become the go-to tool for content creators. It can help with:</p>
      <ul>
        <li>Drafting blog posts and articles</li>
        <li>Generating creative ideas</li>
        <li>Editing and proofreading</li>
        <li>Creating social media content</li>
      </ul>
      
      <h3>Jasper AI</h3>
      <p>Jasper is specifically designed for marketing content, with templates for ads, emails, and long-form content.</p>
      
      <h2>AI Image Generation</h2>
      <p>Tools like Midjourney, DALL-E, and Stable Diffusion are revolutionizing visual content creation, allowing creators to generate unique images from text descriptions.</p>
      
      <h2>The Future of AI in Content</h2>
      <p>As AI technology continues to evolve, we can expect even more sophisticated tools that understand context, brand voice, and audience preferences.</p>
    `,
    categorySlug: "ai",
    tagSlugs: ["artificial-intelligence", "content-creation", "chatgpt"],
    readingTime: 8,
  },
  {
    title: "SEO Best Practices for 2024",
    excerpt: "Stay ahead of the competition with these proven SEO strategies and ranking factors.",
    content: `
      <h2>Understanding Modern SEO</h2>
      <p>Search engine optimization continues to evolve. What worked in 2020 won't work today. Here are the most important SEO practices for 2024.</p>
      
      <h2>Core Web Vitals</h2>
      <p>Google's Core Web Vitals have become essential ranking factors. Focus on:</p>
      <ul>
        <li><strong>LCP (Largest Contentful Paint):</strong> Should be under 2.5 seconds</li>
        <li><strong>FID (First Input Delay):</strong> Should be under 100 milliseconds</li>
        <li><strong>CLS (Cumulative Layout Shift):</strong> Should be under 0.1</li>
      </ul>
      
      <h2>Quality Content is King</h2>
      <p>Google's helpful content update emphasizes creating content for people, not search engines. Focus on:</p>
      <ul>
        <li>In-depth, comprehensive coverage of topics</li>
        <li>Original research and insights</li>
        <li>Regular content updates</li>
        <li>Expert authorship and E-A-T signals</li>
      </ul>
      
      <h2>Technical SEO Checklist</h2>
      <ul>
        <li>Mobile-first optimization</li>
        <li>Schema markup implementation</li>
        <li>XML sitemap submission</li>
        <li>Internal linking strategy</li>
        <li>HTTPS security</li>
      </ul>
    `,
    categorySlug: "seo",
    tagSlugs: ["seo", "google", "ranking"],
    readingTime: 15,
  },
  {
    title: "Email Marketing Strategies That Convert",
    excerpt: "Learn how to create email campaigns that drive engagement and boost your conversion rates.",
    content: `
      <h2>The Power of Email Marketing</h2>
      <p>Email marketing remains one of the most effective digital marketing channels, with an average ROI of $42 for every $1 spent.</p>
      
      <h2>Building Your Email List</h2>
      <p>Quality over quantity is key. Focus on attracting subscribers who are genuinely interested in your content:</p>
      <ul>
        <li>Offer valuable lead magnets</li>
        <li>Use exit-intent popups strategically</li>
        <li>Create content upgrades</li>
        <li>Implement referral programs</li>
      </ul>
      
      <h2>Crafting High-Converting Emails</h2>
      <h3>Subject Lines</h3>
      <p>Your subject line determines whether your email gets opened. Best practices include:</p>
      <ul>
        <li>Keep it under 50 characters</li>
        <li>Create urgency or curiosity</li>
        <li>Personalize when possible</li>
        <li>Avoid spam trigger words</li>
      </ul>
      
      <h3>Email Content</h3>
      <p>Focus on providing value before asking for anything. The 80/20 rule applies: 80% value, 20% promotion.</p>
      
      <h2>Automation and Segmentation</h2>
      <p>Segmented campaigns drive 760% more revenue than generic blasts. Create automated sequences for:</p>
      <ul>
        <li>Welcome series</li>
        <li>Abandoned cart recovery</li>
        <li>Re-engagement campaigns</li>
        <li>Post-purchase follow-ups</li>
      </ul>
    `,
    categorySlug: "marketing",
    tagSlugs: ["email-marketing", "conversion", "automation"],
    readingTime: 10,
  },
  {
    title: "Top AI Writing Assistants Compared",
    excerpt: "A comprehensive comparison of the best AI writing tools to help you create content faster.",
    content: `
      <h2>The AI Writing Revolution</h2>
      <p>AI writing assistants have transformed content creation. But with so many options, which one should you choose?</p>
      
      <h2>Top Contenders</h2>
      
      <h3>ChatGPT (OpenAI)</h3>
      <p><strong>Best for:</strong> General writing, brainstorming, coding</p>
      <p><strong>Pricing:</strong> Free tier available; Plus at $20/month</p>
      <p><strong>Pros:</strong> Versatile, constantly improving, great value</p>
      <p><strong>Cons:</strong> Can be verbose, requires fact-checking</p>
      
      <h3>Jasper AI</h3>
      <p><strong>Best for:</strong> Marketing copy, long-form content</p>
      <p><strong>Pricing:</strong> Starts at $49/month</p>
      <p><strong>Pros:</strong> Marketing-focused templates, brand voice training</p>
      <p><strong>Cons:</strong> Expensive for casual users</p>
      
      <h3>Copy.ai</h3>
      <p><strong>Best for:</strong> Short-form copy, social media</p>
      <p><strong>Pricing:</strong> Free tier; Pro at $36/month</p>
      <p><strong>Pros:</strong> Great templates, affordable</p>
      <p><strong>Cons:</strong> Less powerful for long-form</p>
      
      <h2>Our Recommendation</h2>
      <p>For most users, ChatGPT Plus offers the best balance of capability and value. For professional marketers, Jasper's specialized features justify the higher price.</p>
    `,
    categorySlug: "writing",
    tagSlugs: ["ai-writing", "content-creation", "productivity"],
    readingTime: 7,
  },
  {
    title: "Design Tools Every Marketer Should Know",
    excerpt: "Create stunning visuals with these beginner-friendly design tools and resources.",
    content: `
      <h2>Visual Content is Essential</h2>
      <p>In today's digital landscape, visual content gets 94% more views than text-only content. Here are the tools that make design accessible to everyone.</p>
      
      <h2>Graphic Design Tools</h2>
      
      <h3>Canva</h3>
      <p>The king of beginner-friendly design. With thousands of templates and an intuitive drag-and-drop interface, Canva makes professional design accessible.</p>
      <p><strong>Best for:</strong> Social media graphics, presentations, simple logos</p>
      
      <h3>Figma</h3>
      <p>The industry standard for UI/UX design. Figma's collaborative features make it perfect for team projects.</p>
      <p><strong>Best for:</strong> Web design, app interfaces, prototyping</p>
      
      <h2>Photo Editing</h2>
      
      <h3>Adobe Photoshop</h3>
      <p>Still the gold standard for professional photo editing and manipulation.</p>
      
      <h3>Remove.bg</h3>
      <p>AI-powered background removal in seconds. Perfect for product photos and headshots.</p>
      
      <h2>Video Creation</h2>
      
      <h3>CapCut</h3>
      <p>Free, powerful video editing that's perfect for TikTok, Instagram Reels, and YouTube Shorts.</p>
      
      <h2>Stock Resources</h2>
      <ul>
        <li><strong>Unsplash:</strong> Free high-quality photos</li>
        <li><strong>Pexels:</strong> Free photos and videos</li>
        <li><strong>Iconoir:</strong> Free icons</li>
        <li><strong>Google Fonts:</strong> Free web fonts</li>
      </ul>
    `,
    categorySlug: "design",
    tagSlugs: ["design-tools", "canva", "visual-content"],
    readingTime: 9,
  },
];

async function main() {
  console.log('🌱 Seeding database...');
  
  // Create admin user
  const admin = await prisma.user.upsert({
    where: { email: 'admin@example.com' },
    update: {},
    create: {
      email: 'admin@example.com',
      password: '$2b$10$YourHashedPasswordHere', // Change in production
      firstName: 'Admin',
      lastName: 'User',
      emailVerified: true,
      status: 'ACTIVE',
    },
  });

  // Create categories if they don't exist
  const categories = [
    { name: "Hosting", slug: "hosting", description: "Web hosting reviews and guides" },
    { name: "AI", slug: "ai", description: "Artificial Intelligence tools and insights" },
    { name: "SEO", slug: "seo", description: "Search Engine Optimization tips" },
    { name: "Marketing", slug: "marketing", description: "Digital marketing strategies" },
    { name: "Writing", slug: "writing", description: "Content writing and copywriting" },
    { name: "Design", slug: "design", description: "Design tools and resources" },
    { name: "Analytics", slug: "analytics", description: "Data analytics and tracking" },
  ];

  for (const cat of categories) {
    await prisma.category.upsert({
      where: { slug: cat.slug },
      update: {},
      create: {
        ...cat,
        left: 1,
        right: 2,
        depth: 0,
        isActive: true,
      },
    });
  }

  // Create tags if they don't exist
  const tags = [
    { name: "Web Hosting", slug: "web-hosting", color: "#3B82F6" },
    { name: "WordPress", slug: "wordpress", color: "#21759B" },
    { name: "Bluehost", slug: "bluehost", color: "#1241A5" },
    { name: "AI", slug: "artificial-intelligence", color: "#8B5CF6" },
    { name: "Content Creation", slug: "content-creation", color: "#EC4899" },
    { name: "ChatGPT", slug: "chatgpt", color: "#10A37F" },
    { name: "SEO", slug: "seo", color: "#22C55E" },
    { name: "Google", slug: "google", color: "#4285F4" },
    { name: "Email Marketing", slug: "email-marketing", color: "#F97316" },
    { name: "Conversion", slug: "conversion", color: "#EF4444" },
    { name: "Design Tools", slug: "design-tools", color: "#EAB308" },
    { name: "Canva", slug: "canva", color: "#00C4CC" },
  ];

  for (const tag of tags) {
    await prisma.tag.upsert({
      where: { slug: tag.slug },
      update: {},
      create: {
        ...tag,
        isActive: true,
      },
    });
  }

  // Create blog posts
  for (const postData of blogPostsData) {
    const slug = slugify(postData.title);
    
    // Check if post already exists
    const existing = await prisma.blogPost.findUnique({
      where: { slug },
    });

    if (existing) {
      console.log(`⏭️  Blog post "${postData.title}" already exists, skipping...`);
      continue;
    }

    // Get category
    const category = await prisma.category.findUnique({
      where: { slug: postData.categorySlug },
    });

    // Get tags
    const postTags = await prisma.tag.findMany({
      where: { slug: { in: postData.tagSlugs } },
    });

    // Create blog post
    await prisma.blogPost.create({
      data: {
        title: postData.title,
        slug,
        excerpt: postData.excerpt,
        content: postData.content,
        contentType: "html",
        status: BlogPostStatus.PUBLISHED,
        publishedAt: new Date(),
        metaTitle: postData.title,
        metaDescription: postData.excerpt,
        readingTime: postData.readingTime,
        createdBy: admin.id,
        authorId: admin.id,
        categories: category
          ? {
              create: {
                categoryId: category.id,
              },
            }
          : undefined,
        tags: {
          create: postTags.map((tag) => ({
            tagId: tag.id,
          })),
        },
      },
    });

    console.log(`✅ Created blog post: "${postData.title}"`);
  }
  
  console.log('✅ Database seeded successfully!');
}

main()
  .catch((e) => {
    console.error(e);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });
