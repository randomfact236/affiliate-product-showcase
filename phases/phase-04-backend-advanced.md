# Phase 4: Backend Advanced

**Duration**: 2 weeks  
**Goal**: Affiliate, analytics, search, media, notifications  
**Prerequisites**: Phase 3 complete (Products)

---

## Week 1: Media & Search

### Day 1-2: Media Service

#### Tasks
- [ ] Set up AWS S3 integration
- [ ] Create upload API with presigned URLs
- [ ] Integrate Sharp for image processing
- [ ] Create media management endpoints

#### apps/api/src/media/media.service.ts
```typescript
import { Injectable } from '@nestjs/common';
import { S3Client, PutObjectCommand, GetObjectCommand } from '@aws-sdk/client-s3';
import { getSignedUrl } from '@aws-sdk/s3-request-presigner';
import * as sharp from 'sharp';
import { PrismaService } from '../prisma/prisma.service';

@Injectable()
export class MediaService {
  private s3: S3Client;
  private bucket: string;

  constructor(private prisma: PrismaService) {
    this.s3 = new S3Client({
      region: process.env.AWS_REGION,
      credentials: {
        accessKeyId: process.env.AWS_ACCESS_KEY_ID!,
        secretAccessKey: process.env.AWS_SECRET_ACCESS_KEY!,
      },
    });
    this.bucket = process.env.S3_BUCKET!;
  }

  async getPresignedUploadUrl(
    filename: string,
    contentType: string,
    entityType: string,
    entityId: string,
  ): Promise<{ uploadUrl: string; publicUrl: string; key: string }> {
    const key = `${entityType}/${entityId}/${Date.now()}-${filename}`;
    
    const command = new PutObjectCommand({
      Bucket: this.bucket,
      Key: key,
      ContentType: contentType,
    });
    
    const uploadUrl = await getSignedUrl(this.s3, command, { expiresIn: 300 });
    const publicUrl = `https://${process.env.CDN_DOMAIN}/${key}`;
    
    // Save reference in DB
    await this.prisma.media.create({
      data: {
        key,
        filename,
        contentType,
        entityType,
        entityId,
        url: publicUrl,
        status: 'PENDING',
      },
    });
    
    return { uploadUrl, publicUrl, key };
  }

  async processImage(key: string, variants: ImageVariant[]): Promise<ProcessedImage> {
    // Download from S3
    const getCommand = new GetObjectCommand({
      Bucket: this.bucket,
      Key: key,
    });
    
    const response = await this.s3.send(getCommand);
    const buffer = await this.streamToBuffer(response.Body as ReadableStream);
    
    const results: ProcessedImage = { original: key, variants: {} };
    
    // Process each variant
    for (const variant of variants) {
      const processed = await sharp(buffer)
        .resize(variant.width, variant.height, { fit: variant.fit || 'cover' })
        .toFormat(variant.format || 'webp', { quality: variant.quality || 80 })
        .toBuffer();
      
      const variantKey = `${key}.${variant.name}.${variant.format || 'webp'}`;
      
      await this.s3.send(
        new PutObjectCommand({
          Bucket: this.bucket,
          Key: variantKey,
          Body: processed,
          ContentType: `image/${variant.format || 'webp'}`,
        }),
      );
      
      results.variants[variant.name] = `https://${process.env.CDN_DOMAIN}/${variantKey}`;
    }
    
    // Update DB record
    await this.prisma.media.update({
      where: { key },
      data: {
        status: 'PROCESSED',
        variants: results.variants,
        processedAt: new Date(),
      },
    });
    
    return results;
  }

  private async streamToBuffer(stream: ReadableStream): Promise<Buffer> {
    const chunks: Buffer[] = [];
    for await (const chunk of stream) {
      chunks.push(Buffer.from(chunk));
    }
    return Buffer.concat(chunks);
  }
}

// Image variant configuration
interface ImageVariant {
  name: string;
  width: number;
  height: number;
  fit?: 'cover' | 'contain' | 'fill';
  format?: 'webp' | 'jpeg' | 'png';
  quality?: number;
}
```

### Day 3-4: Elasticsearch Integration

#### Tasks
- [ ] Set up Elasticsearch client
- [ ] Create product index mapping
- [ ] Implement indexing service
- [ ] Build search API

#### apps/api/src/search/search.service.ts
```typescript
import { Injectable, OnModuleInit } from '@nestjs/common';
import { ElasticsearchService } from '@nestjs/elasticsearch';
import { PrismaService } from '../prisma/prisma.service';

@Injectable()
export class SearchService implements OnModuleInit {
  private readonly INDEX = 'products';

  constructor(
    private elasticsearch: ElasticsearchService,
    private prisma: PrismaService,
  ) {}

  async onModuleInit(): Promise<void> {
    await this.createIndex();
  }

  async createIndex(): Promise<void> {
    const exists = await this.elasticsearch.indices.exists({ index: this.INDEX });
    
    if (!exists) {
      await this.elasticsearch.indices.create({
        index: this.INDEX,
        body: {
          settings: {
            analysis: {
              analyzer: {
                custom_analyzer: {
                  type: 'custom',
                  tokenizer: 'standard',
                  filter: ['lowercase', 'asciifolding', 'word_delimiter'],
                },
              },
            },
          },
          mappings: {
            properties: {
              id: { type: 'keyword' },
              name: {
                type: 'text',
                analyzer: 'custom_analyzer',
                fields: {
                  keyword: { type: 'keyword' },
                },
              },
              description: { type: 'text', analyzer: 'custom_analyzer' },
              slug: { type: 'keyword' },
              price: { type: 'integer' },
              categoryNames: { type: 'keyword' },
              tagNames: { type: 'keyword' },
              status: { type: 'keyword' },
              createdAt: { type: 'date' },
            },
          },
        },
      });
    }
  }

  async indexProduct(productId: string): Promise<void> {
    const product = await this.prisma.product.findUnique({
      where: { id: productId },
      include: {
        variants: true,
        categories: { include: { category: true } },
        tags: { include: { tag: true } },
      },
    });

    if (!product) return;

    await this.elasticsearch.index({
      index: this.INDEX,
      id: product.id,
      body: {
        id: product.id,
        name: product.name,
        description: product.description,
        slug: product.slug,
        price: product.variants.find((v) => v.isDefault)?.price || 0,
        categoryNames: product.categories.map((c) => c.category.name),
        tagNames: product.tags.map((t) => t.tag.name),
        status: product.status,
        createdAt: product.createdAt,
      },
    });
  }

  async search(query: string, filters: SearchFilters): Promise<SearchResult> {
    const must: unknown[] = [
      {
        multi_match: {
          query,
          fields: ['name^3', 'description', 'categoryNames^2', 'tagNames'],
          type: 'best_fields',
          fuzziness: 'AUTO',
        },
      },
    ];

    if (filters.category) {
      must.push({ term: { categoryNames: filters.category } });
    }

    if (filters.minPrice !== undefined || filters.maxPrice !== undefined) {
      must.push({
        range: {
          price: {
            gte: filters.minPrice,
            lte: filters.maxPrice,
          },
        },
      });
    }

    const response = await this.elasticsearch.search({
      index: this.INDEX,
      body: {
        from: (filters.page - 1) * filters.limit,
        size: filters.limit,
        query: { bool: { must } },
        sort: this.buildSort(filters.sortBy, filters.sortOrder),
        aggs: {
          categories: {
            terms: { field: 'categoryNames', size: 20 },
          },
          price_range: {
            histogram: {
              field: 'price',
              interval: 1000,
            },
          },
        },
      },
    });

    return {
      hits: response.hits.hits.map((hit) => ({
        id: hit._id,
        score: hit._score,
        ...(hit._source as Record<string, unknown>),
      })),
      total: (response.hits.total as { value: number }).value,
      facets: response.aggregations as Record<string, unknown>,
    };
  }

  async suggest(query: string): Promise<string[]> {
    const response = await this.elasticsearch.search({
      index: this.INDEX,
      body: {
        suggest: {
          product_suggest: {
            prefix: query,
            completion: {
              field: 'name.keyword',
              fuzzy: true,
              size: 10,
            },
          },
        },
      },
    });

    return response.suggest?.product_suggest?.[0]?.options?.map(
      (opt: { text: string }) => opt.text,
    ) || [];
  }

  private buildSort(sortBy: string, sortOrder: 'asc' | 'desc'): unknown[] {
    const sortMap: Record<string, string> = {
      name: 'name.keyword',
      price: 'price',
      date: 'createdAt',
      relevance: '_score',
    };

    return [{ [sortMap[sortBy] || '_score']: sortOrder }];
  }
}
```

### Day 5: Queue System Setup

#### Tasks
- [ ] Configure BullMQ with Redis
- [ ] Create queue module
- [ ] Set up job processors
- [ ] Add queue monitoring

---

## Week 2: Affiliate & Analytics

### Day 6-7: Affiliate Service

#### Tasks
- [ ] Create link generation algorithm
- [ ] Implement click tracking
- [ ] Build commission calculation
- [ ] Create partner management

#### apps/api/src/affiliate/affiliate.service.ts
```typescript
import { Injectable } from '@nestjs/common';
import { createHash } from 'crypto';
import { PrismaService } from '../prisma/prisma.service';
import { RedisService } from '../redis/redis.service';

@Injectable()
export class AffiliateService {
  constructor(
    private prisma: PrismaService,
    private redis: RedisService,
  ) {}

  async generateLink(data: CreateAffiliateLinkDto): Promise<AffiliateLink> {
    // Generate unique tracking code
    const trackingCode = this.generateTrackingCode(data.partnerId, data.productId);
    
    // Short code for URLs (6 chars)
    const shortCode = trackingCode.substring(0, 6);
    
    return this.prisma.affiliateLink.create({
      data: {
        trackingCode,
        shortCode,
        partnerId: data.partnerId,
        productId: data.productId,
        url: data.destinationUrl,
        commissionRate: data.commissionRate,
        commissionType: data.commissionType, // 'percentage' | 'fixed'
        expiresAt: data.expiresAt,
      },
    });
  }

  async trackClick(shortCode: string, metadata: ClickMetadata): Promise<ClickResult> {
    const link = await this.prisma.affiliateLink.findUnique({
      where: { shortCode },
      include: { product: true },
    });

    if (!link || (link.expiresAt && link.expiresAt < new Date())) {
      return { valid: false, redirectUrl: '/404' };
    }

    // Deduplication: 24h window per IP
    const dedupeKey = `click:${link.id}:${metadata.ip}:${new Date().toISOString().split('T')[0]}`;
    const isDuplicate = await this.redis.get(dedupeKey);
    
    if (!isDuplicate) {
      // Store click event
      await Promise.all([
        this.prisma.affiliateClick.create({
          data: {
            linkId: link.id,
            ipAddress: metadata.ip,
            userAgent: metadata.userAgent,
            referrer: metadata.referrer,
            country: metadata.country,
            deviceType: metadata.deviceType,
          },
        }),
        // Set dedupe key with 24h expiry
        this.redis.setex(dedupeKey, 86400, '1'),
        // Increment real-time counter
        this.redis.hincrby(`stats:clicks:${link.id}`, 'today', 1),
      ]);
    }

    // Set tracking cookie (30 days)
    const trackingCookie = this.generateTrackingCookie(link.id);

    return {
      valid: true,
      redirectUrl: link.url,
      trackingCookie,
      cookieExpiry: 30 * 24 * 60 * 60, // 30 days in seconds
    };
  }

  async recordConversion(
    trackingCookie: string,
    orderData: OrderData,
  ): Promise<ConversionResult> {
    // Decode tracking cookie
    const linkId = this.decodeTrackingCookie(trackingCookie);
    if (!linkId) {
      return { recorded: false, reason: 'Invalid tracking' };
    }

    const link = await this.prisma.affiliateLink.findUnique({
      where: { id: linkId },
      include: { partner: true },
    });

    if (!link) {
      return { recorded: false, reason: 'Link not found' };
    }

    // Calculate commission
    const commission = this.calculateCommission(
      orderData.amount,
      link.commissionRate,
      link.commissionType,
    );

    // Record conversion
    const conversion = await this.prisma.affiliateConversion.create({
      data: {
        linkId: link.id,
        partnerId: link.partnerId,
        orderId: orderData.orderId,
        orderAmount: orderData.amount,
        commissionAmount: commission,
        currency: orderData.currency,
        status: 'PENDING', // Will be confirmed after return period
        metadata: orderData.metadata,
      },
    });

    // Update partner balance
    await this.prisma.partner.update({
      where: { id: link.partnerId },
      data: {
        pendingBalance: { increment: commission },
        totalConversions: { increment: 1 },
      },
    });

    return {
      recorded: true,
      conversionId: conversion.id,
      commission,
    };
  }

  private generateTrackingCode(partnerId: string, productId?: string): string {
    const data = `${partnerId}:${productId}:${Date.now()}:${Math.random()}`;
    return createHash('sha256').update(data).digest('hex');
  }

  private generateTrackingCookie(linkId: string): string {
    const data = `${linkId}:${Date.now()}`;
    return createHash('sha256').update(data).digest('base64url');
  }

  private decodeTrackingCookie(cookie: string): string | null {
    // In real implementation, use encrypted JWT or similar
    // This is simplified
    return cookie.split(':')[0];
  }

  private calculateCommission(
    amount: number,
    rate: number,
    type: string,
  ): number {
    if (type === 'fixed') {
      return rate;
    }
    return Math.round(amount * (rate / 100));
  }
}
```

### Day 8-9: Analytics Service

#### Tasks
- [ ] Create event ingestion API
- [ ] Implement aggregation queries
- [ ] Build analytics dashboard data API
- [ ] Set up scheduled reports

#### apps/api/src/analytics/analytics.service.ts
```typescript
import { Injectable } from '@nestjs/common';
import { PrismaService } from '../prisma/prisma.service';
import { RedisService } from '../redis/redis.service';

type EventType = 'page_view' | 'product_view' | 'click' | 'add_to_cart' | 'purchase';

interface AnalyticsEvent {
  type: EventType;
  userId?: string;
  sessionId: string;
  productId?: string;
  metadata: Record<string, unknown>;
  timestamp: Date;
}

@Injectable()
export class AnalyticsService {
  constructor(
    private prisma: PrismaService,
    private redis: RedisService,
  ) {}

  async trackEvent(event: AnalyticsEvent): Promise<void> {
    // Write to time-series database or queue for processing
    // For Phase 4, using Prisma with batching
    
    // Real-time counters in Redis
    const date = event.timestamp.toISOString().split('T')[0];
    const hour = event.timestamp.getHours();
    
    const pipeline = this.redis.pipeline();
    
    // Daily counters
    pipeline.hincrby(`analytics:${event.type}:daily`, date, 1);
    
    // Hourly counters
    pipeline.hincrby(`analytics:${event.type}:hourly:${date}`, hour.toString(), 1);
    
    // Product-specific counters
    if (event.productId) {
      pipeline.hincrby(`analytics:product:${event.productId}:${date}`, event.type, 1);
    }
    
    await pipeline.exec();
    
    // Store raw event for later analysis
    await this.prisma.analyticsEvent.create({
      data: {
        type: event.type,
        userId: event.userId,
        sessionId: event.sessionId,
        productId: event.productId,
        metadata: event.metadata,
        timestamp: event.timestamp,
      },
    });
  }

  async getDashboardData(dateRange: DateRange): Promise<DashboardData> {
    const [overview, topProducts, trafficSources] = await Promise.all([
      this.getOverviewMetrics(dateRange),
      this.getTopProducts(dateRange, 10),
      this.getTrafficSources(dateRange),
    ]);

    return {
      overview,
      topProducts,
      trafficSources,
    };
  }

  private async getOverviewMetrics(dateRange: DateRange): Promise<OverviewMetrics> {
    const [pageViews, uniqueVisitors, clicks, conversions] = await Promise.all([
      this.prisma.analyticsEvent.count({
        where: {
          type: 'page_view',
          timestamp: { gte: dateRange.start, lte: dateRange.end },
        },
      }),
      this.prisma.analyticsEvent.groupBy({
        by: ['sessionId'],
        where: {
          timestamp: { gte: dateRange.start, lte: dateRange.end },
        },
        _count: { sessionId: true },
      }),
      this.prisma.affiliateClick.count({
        where: {
          createdAt: { gte: dateRange.start, lte: dateRange.end },
        },
      }),
      this.prisma.affiliateConversion.count({
        where: {
          createdAt: { gte: dateRange.start, lte: dateRange.end },
          status: 'CONFIRMED',
        },
      }),
    ]);

    return {
      pageViews,
      uniqueVisitors: uniqueVisitors.length,
      clicks,
      conversions,
      conversionRate: clicks > 0 ? (conversions / clicks) * 100 : 0,
    };
  }

  async getProductAnalytics(productId: string, dateRange: DateRange): Promise<ProductAnalytics> {
    const [views, clicks, conversions] = await Promise.all([
      this.prisma.analyticsEvent.count({
        where: {
          productId,
          type: 'product_view',
          timestamp: { gte: dateRange.start, lte: dateRange.end },
        },
      }),
      this.prisma.affiliateClick.count({
        where: {
          link: { productId },
          createdAt: { gte: dateRange.start, lte: dateRange.end },
        },
      }),
      this.prisma.affiliateConversion.count({
        where: {
          link: { productId },
          createdAt: { gte: dateRange.start, lte: dateRange.end },
          status: 'CONFIRMED',
        },
      }),
    ]);

    return {
      productId,
      views,
      clicks,
      conversions,
      ctr: views > 0 ? (clicks / views) * 100 : 0,
    };
  }
}
```

### Day 10: Notification Service

#### Tasks
- [ ] Set up SendGrid integration
- [ ] Create email template system
- [ ] Implement queue-based sending
- [ ] Add notification preferences

---

## Deliverables Checklist

- [ ] Media upload with presigned URLs
- [ ] Image processing (Sharp) with variants
- [ ] Elasticsearch product indexing
- [ ] Search API with filters and facets
- [ ] Autocomplete suggestions
- [ ] Affiliate link generation
- [ ] Click tracking with deduplication
- [ ] Conversion tracking
- [ ] Commission calculation
- [ ] Analytics event ingestion
- [ ] Dashboard data API
- [ ] Email notification service
- [ ] Queue system (BullMQ)

## API Endpoints

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | /api/v1/media/upload-url | Admin | Get presigned URL |
| POST | /api/v1/media/process | Admin | Process image variants |
| GET | /api/v1/search | No | Search products |
| GET | /api/v1/search/suggest | No | Autocomplete |
| POST | /api/v1/search/reindex | Admin | Rebuild index |
| POST | /api/v1/affiliate/links | Partner | Create link |
| GET | /l/:shortCode | No | Affiliate redirect |
| POST | /api/v1/analytics/events | No | Track event |
| GET | /api/v1/analytics/dashboard | Admin | Dashboard data |
| POST | /api/v1/notifications/email | Admin | Send email |

## Success Metrics

| Metric | Target | Measurement |
|--------|--------|-------------|
| Search response | < 100ms | Elasticsearch query |
| Image processing | < 2s | Variant generation |
| Click tracking | < 50ms | Redirect latency |
| Event ingestion | < 10ms | API response |

## Next Phase Handoff

**Phase 5 Prerequisites:**
- [ ] Search API returning results
- [ ] Media upload working
- [ ] Affiliate links generating and tracking
- [ ] Analytics events storing
- [ ] Backend APIs documented
