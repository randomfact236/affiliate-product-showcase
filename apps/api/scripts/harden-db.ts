import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

async function main() {
    console.log('🛡️  Starting Database Hardening...');

    // 1. Price Constraints
    try {
        await prisma.$executeRawUnsafe(`
      ALTER TABLE "product_variants" 
      ADD CONSTRAINT "price_positive" CHECK ("price" >= 0);
    `);
        console.log('✅ Added CHECK constraint: price >= 0');
    } catch (e) {
        console.log('⚠️  Constraint price_positive might already exist or failed:', e.message.split('\n')[0]);
    }

    // 2. Inventory Constraints
    try {
        await prisma.$executeRawUnsafe(`
      ALTER TABLE "product_variants" 
      ADD CONSTRAINT "inventory_positive" CHECK ("inventory" >= 0);
    `);
        console.log('✅ Added CHECK constraint: inventory >= 0');
    } catch (e) {
        console.log('⚠️  Constraint inventory_positive might already exist or failed:', e.message.split('\n')[0]);
    }

    console.log('🛡️  Database Hardening Complete.');
}

main()
    .catch((e) => {
        console.error(e);
        process.exit(1);
    })
    .finally(async () => {
        await prisma.$disconnect();
    });
