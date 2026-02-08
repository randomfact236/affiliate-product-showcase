const { PrismaClient } = require('@prisma/client');
const bcrypt = require('bcrypt');

const prisma = new PrismaClient();

async function main() {
  console.log('🌱 Starting database seed...\n');

  // Create admin user
  const adminPassword = await bcrypt.hash('admin123', 10);
  const admin = await prisma.user.upsert({
    where: { email: 'admin@example.com' },
    update: {},
    create: {
      email: 'admin@example.com',
      password: adminPassword,
      firstName: 'Admin',
      lastName: 'User',
      emailVerified: true,
      roles: {
        create: {
          role: {
            connectOrCreate: {
              where: { name: 'ADMIN' },
              create: {
                name: 'ADMIN',
                description: 'Administrator with full access',
              },
            },
          },
        },
      },
    },
  });
  console.log('✅ Admin user created:', admin.email);

  // Create editor user
  const editorPassword = await bcrypt.hash('editor123', 10);
  const editor = await prisma.user.upsert({
    where: { email: 'editor@example.com' },
    update: {},
    create: {
      email: 'editor@example.com',
      password: editorPassword,
      firstName: 'Editor',
      lastName: 'User',
      emailVerified: true,
      roles: {
        create: {
          role: {
            connectOrCreate: {
              where: { name: 'EDITOR' },
              create: {
                name: 'EDITOR',
                description: 'Content Editor',
              },
            },
          },
        },
      },
    },
  });
  console.log('✅ Editor user created:', editor.email);

  // Create USER role
  await prisma.role.upsert({
    where: { name: 'USER' },
    update: {},
    create: {
      name: 'USER',
      description: 'Default user role',
    },
  });

  // Create categories
  const electronics = await prisma.category.create({
    data: {
      slug: 'electronics',
      name: 'Electronics',
      description: 'Electronic devices and gadgets',
      left: 1,
      right: 6,
      depth: 0,
    },
  });
  console.log('✅ Category created:', electronics.name);

  const computers = await prisma.category.create({
    data: {
      slug: 'computers',
      name: 'Computers',
      description: 'Laptops, desktops, and accessories',
      left: 2,
      right: 3,
      depth: 1,
      parentId: electronics.id,
    },
  });
  console.log('✅ Category created:', computers.name);

  const audio = await prisma.category.create({
    data: {
      slug: 'audio',
      name: 'Audio',
      description: 'Headphones, speakers, and audio equipment',
      left: 4,
      right: 5,
      depth: 1,
      parentId: electronics.id,
    },
  });
  console.log('✅ Category created:', audio.name);

  // Create tags
  const tags = await Promise.all([
    prisma.tag.upsert({
      where: { slug: 'wireless' },
      update: {},
      create: { slug: 'wireless', name: 'Wireless' },
    }),
    prisma.tag.upsert({
      where: { slug: 'bluetooth' },
      update: {},
      create: { slug: 'bluetooth', name: 'Bluetooth' },
    }),
    prisma.tag.upsert({
      where: { slug: 'sale' },
      update: {},
      create: { slug: 'sale', name: 'Sale' },
    }),
  ]);
  console.log('✅ Tags created:', tags.map(t => t.name).join(', '));

  // Create attributes
  const colorAttr = await prisma.attribute.create({
    data: {
      name: 'color',
      displayName: 'Color',
      type: 'SELECT',
      isFilterable: true,
      isVisible: true,
      options: {
        create: [
          { value: 'black', displayValue: 'Black', sortOrder: 1 },
          { value: 'white', displayValue: 'White', sortOrder: 2 },
          { value: 'red', displayValue: 'Red', sortOrder: 3 },
          { value: 'blue', displayValue: 'Blue', sortOrder: 4 },
        ],
      },
    },
  });
  console.log('✅ Attribute created:', colorAttr.displayName);

  const warrantyAttr = await prisma.attribute.create({
    data: {
      name: 'warranty',
      displayName: 'Warranty',
      type: 'TEXT',
      isFilterable: false,
      isVisible: true,
    },
  });
  console.log('✅ Attribute created:', warrantyAttr.displayName);

  // Create sample product
  const product1 = await prisma.product.create({
    data: {
      slug: 'premium-wireless-headphones',
      name: 'Premium Wireless Headphones',
      description: 'High-quality wireless headphones with active noise cancellation, 30-hour battery life, and premium sound quality.',
      shortDescription: 'Best wireless headphones with noise cancellation',
      status: 'PUBLISHED',
      publishedAt: new Date(),
      metaTitle: 'Premium Wireless Headphones - Best Noise Cancelling',
      metaDescription: 'Buy the best wireless headphones with active noise cancellation and 30-hour battery life.',
      createdBy: admin.id,
      variants: {
        create: [
          {
            name: 'Black',
            sku: 'PWH-BLK-001',
            price: 19999, // $199.99
            comparePrice: 24999,
            inventory: 50,
            isDefault: true,
            options: JSON.stringify({ color: 'black' }),
          },
          {
            name: 'White',
            sku: 'PWH-WHT-001',
            price: 19999,
            comparePrice: 24999,
            inventory: 30,
            options: JSON.stringify({ color: 'white' }),
          },
        ],
      },
      categories: {
        create: [
          { categoryId: electronics.id },
          { categoryId: audio.id },
        ],
      },
      tags: {
        create: [
          { tagId: tags[0].id }, // wireless
          { tagId: tags[1].id }, // bluetooth
        ],
      },
      images: {
        create: [
          {
            url: 'https://example.com/images/headphones-1.jpg',
            alt: 'Premium Wireless Headphones - Black',
            isPrimary: true,
            sortOrder: 1,
          },
          {
            url: 'https://example.com/images/headphones-2.jpg',
            alt: 'Premium Wireless Headphones - Side View',
            sortOrder: 2,
          },
        ],
      },
      ribbons: {
        create: [
          {
            name: 'Best Seller',
            color: '#FFFFFF',
            bgColor: '#EF4444',
            position: 'TOP_RIGHT',
            priority: 1,
          },
        ],
      },
      attributes: {
        create: [
          { attributeId: warrantyAttr.id, value: '2 years' },
        ],
      },
    },
  });
  console.log('✅ Product created:', product1.name);

  // Create another product
  const product2 = await prisma.product.create({
    data: {
      slug: 'ultra-slim-laptop',
      name: 'Ultra Slim Laptop',
      description: 'Lightweight laptop with powerful performance, perfect for work and travel.',
      shortDescription: 'Lightweight laptop for professionals',
      status: 'PUBLISHED',
      publishedAt: new Date(),
      metaTitle: 'Ultra Slim Laptop - Lightweight & Powerful',
      metaDescription: 'Shop the ultra slim laptop - perfect for professionals on the go.',
      createdBy: admin.id,
      variants: {
        create: [
          {
            name: '256GB',
            sku: 'USL-256-001',
            price: 99900, // $999.00
            comparePrice: 109900,
            inventory: 25,
            isDefault: true,
          },
          {
            name: '512GB',
            sku: 'USL-512-001',
            price: 129900,
            comparePrice: 139900,
            inventory: 15,
          },
        ],
      },
      categories: {
        create: [
          { categoryId: electronics.id },
          { categoryId: computers.id },
        ],
      },
      tags: {
        create: [
          { tagId: tags[2].id }, // sale
        ],
      },
      images: {
        create: [
          {
            url: 'https://example.com/images/laptop-1.jpg',
            alt: 'Ultra Slim Laptop',
            isPrimary: true,
            sortOrder: 1,
          },
        ],
      },
      ribbons: {
        create: [
          {
            name: 'Sale',
            color: '#FFFFFF',
            bgColor: '#10B981',
            position: 'TOP_LEFT',
            priority: 2,
          },
        ],
      },
      affiliateLinks: {
        create: [
          {
            platform: 'amazon',
            url: 'https://amazon.com/dp/example-laptop',
            price: 99900,
            originalPrice: 109900,
          },
          {
            platform: 'bestbuy',
            url: 'https://bestbuy.com/product/example-laptop',
            price: 98900,
            originalPrice: 109900,
          },
        ],
      },
    },
  });
  console.log('✅ Product created:', product2.name);

  console.log('\n✨ Database seed completed!\n');
  console.log('Sample accounts:');
  console.log('  Admin: admin@example.com / admin123');
  console.log('  Editor: editor@example.com / editor123');
}

main()
  .catch((e) => {
    console.error('❌ Seed failed:', e);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });
