# Phase 5: Optimization, Security & Production Deployment

**Objective:** Harden the application for production with enterprise-grade security, performance optimization, monitoring, and deployment automation. Achieve 99.99% uptime and SOC 2 readiness.

**Framework:** Next.js 15 + NestJS 10 + PostgreSQL + Redis  
**Estimated Duration:** 7 days  
**Prerequisites:** Phases 1-4 completed, all tests passing

**Quality Target:** Enterprise Grade (10/10) - 99.99% uptime, SOC 2 ready, monitored

---

## 1. Security Hardening

### 1.1 NestJS Security Configuration
```typescript
// apps/api/src/main.ts (Security Enhanced)
import { NestFactory } from '@nestjs/core';
import { ValidationPipe, VersioningType } from '@nestjs/common';
import { ConfigService } from '@nestjs/config';
import { SwaggerModule, DocumentBuilder } from '@nestjs/swagger';
import helmet from 'helmet';
import compression from 'compression';
import { Logger } from 'nestjs-pino';
import { AppModule } from './app.module';

async function bootstrap() {
  const app = await NestFactory.create(AppModule, { 
    bufferLogs: true,
    rawBody: true,
  });
  
  const configService = app.get(ConfigService);
  const logger = app.get(Logger);
  
  // ==================== SECURITY MIDDLEWARE ====================
  
  // Helmet - comprehensive security headers
  app.use(helmet({
    contentSecurityPolicy: {
      directives: {
        defaultSrc: ["'self'"],
        scriptSrc: ["'self'", "'unsafe-inline'"],
        styleSrc: ["'self'", "'unsafe-inline'"],
        imgSrc: ["'self'", "data:", "https:", "blob:"],
        connectSrc: ["'self'", configService.get('WEB_URL')],
        fontSrc: ["'self'"],
        objectSrc: ["'none'"],
        mediaSrc: ["'self'"],
        frameSrc: ["'none'"],
        upgradeInsecureRequests: [],
      },
    },
    crossOriginEmbedderPolicy: false,
    crossOriginOpenerPolicy: { policy: 'same-origin' },
    crossOriginResourcePolicy: { policy: 'cross-origin' },
    dnsPrefetchControl: { allow: false },
    frameguard: { action: 'deny' },
    hidePoweredBy: true,
    hsts: {
      maxAge: 31536000,
      includeSubDomains: true,
      preload: true,
    },
    ieNoOpen: true,
    noSniff: true,
    originAgentCluster: true,
    permittedCrossDomainPolicies: { permittedPolicies: 'none' },
    referrerPolicy: { policy: 'strict-origin-when-cross-origin' },
    xssFilter: true,
  }));
  
  // Compression
  app.use(compression({
    level: 6,
    threshold: 1024,
    filter: (req, res) => {
      if (req.headers['x-no-compression']) return false;
      return compression.filter(req, res);
    },
  }));
  
  // CORS - strict origin policy
  const allowedOrigins = configService.get('ALLOWED_ORIGINS', '').split(',');
  app.enableCors({
    origin: (origin, callback) => {
      if (!origin) return callback(null, true);
      if (allowedOrigins.includes(origin) || process.env.NODE_ENV === 'development') {
        callback(null, true);
      } else {
        callback(new Error('Not allowed by CORS'));
      }
    },
    credentials: true,
    methods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
    allowedHeaders: ['Content-Type', 'Authorization', 'X-Requested-With', 'X-API-Key'],
    exposedHeaders: ['X-Total-Count', 'X-Page-Count'],
    maxAge: 86400,
  });
  
  // Trust proxy for accurate IP behind load balancer
  app.getHttpAdapter().getInstance().set('trust proxy', 1);
  
  // Global validation pipe
  app.useGlobalPipes(new ValidationPipe({
    whitelist: true,
    forbidNonWhitelisted: true,
    transform: true,
    transformOptions: { enableImplicitConversion: true },
    disableErrorMessages: process.env.NODE_ENV === 'production',
  }));
  
  // Logger
  app.useLogger(logger);
  
  const port = configService.get('API_PORT', 3001);
  const host = configService.get('API_HOST', '0.0.0.0');
  
  await app.listen(port, host);
  
  logger.log(`🚀 API running on port ${port}`);
  logger.log(`📊 Environment: ${process.env.NODE_ENV}`);
}

bootstrap();
```

### 1.2 Rate Limiting Configuration
```typescript
// apps/api/src/config/throttler.config.ts
import { ThrottlerModuleOptions, ThrottlerOptions } from '@nestjs/throttler';
import { ThrottlerStorageRedisService } from 'nestjs-throttler-storage-redis';

export const throttlerConfig: ThrottlerModuleOptions = {
  throttlers: [
    // Default: 100 requests per minute
    {
      name: 'default',
      ttl: 60000,
      limit: 100,
    },
    // Strict for auth endpoints: 5 requests per minute
    {
      name: 'auth',
      ttl: 60000,
      limit: 5,
    },
    // Analytics beacon: higher limit
    {
      name: 'analytics',
      ttl: 60000,
      limit: 200,
    },
  ],
};

// apps/api/src/common/guards/throttler.guard.ts
import { ThrottlerGuard, ThrottlerLimitDetail } from '@nestjs/throttler';
import { Injectable, ExecutionContext, HttpException, HttpStatus } from '@nestjs/common';
import { Request } from 'express';

interface RequestWithUser extends Request {
  user?: { sub: string; roles: string[] };
  ips?: string[];
}

@Injectable()
export class CustomThrottlerGuard extends ThrottlerGuard {
  protected async getTracker(req: RequestWithUser): Promise<string> {
    // Use real IP behind proxy
    const ip = req.ips?.[0] || req.ip || 'unknown';
    const userId = req.user?.sub;
    
    // Combine IP and user ID for more accurate rate limiting
    return userId ? `user:${userId}` : `ip:${ip}`;
  }

  protected async throwThrottlingException(
    request: ExecutionContext,
    throttleParameters: ThrottlerLimitDetail,
  ): Promise<void> {
    const { limit, ttl } = throttleParameters;
    throw new HttpException(
      {
        statusCode: HttpStatus.TOO_MANY_REQUESTS,
        message: 'Too many requests',
        retryAfter: Math.ceil(ttl / 1000),
        limit,
      },
      HttpStatus.TOO_MANY_REQUESTS,
      {
        cause: 'Rate limit exceeded',
      },
    );
  }
}

// apps/api/src/common/decorators/throttle.decorator.ts
import { SetMetadata } from '@nestjs/common';

export const THROTTLE_KEY = 'throttle';
export interface ThrottleOptions {
  limit?: number;
  ttl?: number;
}

export const Throttle = (options: ThrottleOptions) => SetMetadata(THROTTLE_KEY, options);
```

### 1.3 Input Sanitization & SQL Injection Prevention
```typescript
// apps/api/src/common/pipes/sanitize.pipe.ts
import { PipeTransform, Injectable, BadRequestException } from '@nestjs/common';
import DOMPurify from 'isomorphic-dompurify';

@Injectable()
export class SanitizePipe implements PipeTransform {
  private readonly forbiddenPatterns = [
    /<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi,
    /<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/gi,
    /<object\b[^<]*(?:(?!<\/object>)<[^<]*)*<\/object>/gi,
    /<embed\b[^<]*>/gi,
    /javascript:/gi,
    /on\w+\s*=/gi,
    /data:text\/html/gi,
    // SQL Injection patterns
    /(\%27)|(\')|(\-\-)|(\%23)|(#)/gi,
    /((\%3D)|(=))[^\n]*((\%27)|(\')|(\-\-)|(\%3B)|(;))/gi,
    /\w*((\%27)|(\'))((\%6F)|o|(\%4F))((\%72)|r|(\%52))/gi,
    /((\%27)|(\'))union/gi,
    /exec(\s|\+)+(s|x)p\w+/gi,
    /UNION\s+SELECT/gi,
    /INSERT\s+INTO/gi,
    /DELETE\s+FROM/gi,
    /DROP\s+TABLE/gi,
  ];

  transform(value: unknown): unknown {
    if (typeof value === 'string') {
      return this.sanitizeString(value);
    }
    
    if (Array.isArray(value)) {
      return value.map(item => this.transform(item));
    }
    
    if (typeof value === 'object' && value !== null) {
      return this.sanitizeObject(value as Record<string, unknown>);
    }
    
    return value;
  }

  private sanitizeString(str: string): string {
    // Check for SQL injection patterns
    for (const pattern of this.forbiddenPatterns) {
      if (pattern.test(str)) {
        throw new BadRequestException('Invalid input detected');
      }
    }
    
    // HTML sanitization
    return DOMPurify.sanitize(str, {
      ALLOWED_TAGS: ['b', 'i', 'em', 'strong', 'a'],
      ALLOWED_ATTR: ['href', 'target'],
    });
  }

  private sanitizeObject(obj: Record<string, unknown>): Record<string, unknown> {
    const sanitized: Record<string, unknown> = {};
    
    for (const [key, value] of Object.entries(obj)) {
      // Sanitize keys too
      const cleanKey = typeof key === 'string' ? this.sanitizeString(key) : key;
      sanitized[cleanKey] = this.transform(value);
    }
    
    return sanitized;
  }
}
```

### 1.4 API Key Authentication (for service-to-service)
```typescript
// apps/api/src/auth/guards/api-key.guard.ts
import { Injectable, CanActivate, ExecutionContext, UnauthorizedException } from '@nestjs/common';
import { ConfigService } from '@nestjs/config';

@Injectable()
export class ApiKeyGuard implements CanActivate {
  constructor(private configService: ConfigService) {}

  canActivate(context: ExecutionContext): boolean {
    const request = context.switchToHttp().getRequest();
    const apiKey = request.headers['x-api-key'];
    
    if (!apiKey) {
      throw new UnauthorizedException('API key is required');
    }
    
    const validApiKey = this.configService.get('INTERNAL_API_KEY');
    
    if (apiKey !== validApiKey) {
      throw new UnauthorizedException('Invalid API key');
    }
    
    return true;
  }
}
```

---

## 2. Performance Optimization

### 2.1 Database Query Optimization
```typescript
// apps/api/src/common/interceptors/query-optimization.interceptor.ts
import { Injectable, NestInterceptor, ExecutionContext, CallHandler } from '@nestjs/common';
import { Observable } from 'rxjs';
import { tap } from 'rxjs/operators';
import { Logger } from 'nestjs-pino';

interface QueryMetrics {
  method: string;
  url: string;
  duration: number;
  slow: boolean;
}

@Injectable()
export class QueryOptimizationInterceptor implements NestInterceptor {
  private readonly slowQueryThreshold = 500; // ms
  private readonly logger = new Logger(QueryOptimizationInterceptor.name);

  intercept(context: ExecutionContext, next: CallHandler): Observable<unknown> {
    const start = Date.now();
    const request = context.switchToHttp().getRequest();
    
    return next.handle().pipe(
      tap(() => {
        const duration = Date.now() - start;
        
        if (duration > this.slowQueryThreshold) {
          const metrics: QueryMetrics = {
            method: request.method,
            url: request.url,
            duration,
            slow: true,
          };
          
          this.logger.warn(`Slow query detected: ${JSON.stringify(metrics)}`);
        }
      }),
    );
  }
}

// Database index recommendations (add these to your migrations)
/*
-- Product search optimization
CREATE INDEX CONCURRENTLY idx_products_search 
ON products USING gin(to_tsvector('english', name || ' ' || COALESCE(description, '')));

-- Date range queries for analytics
CREATE INDEX CONCURRENTLY idx_analytics_events_date 
ON analytics_events(timestamp DESC) 
WHERE timestamp > NOW() - INTERVAL '90 days';

-- Composite index for common filters
CREATE INDEX CONCURRENTLY idx_products_status_created 
ON products(status, created_at DESC);

-- Foreign key indexes
CREATE INDEX CONCURRENTLY idx_product_categories_product_id ON product_categories(product_id);
CREATE INDEX CONCURRENTLY idx_product_categories_category_id ON product_categories(category_id);

-- For pagination
CREATE INDEX CONCURRENTLY idx_products_cursor ON products(created_at, id);
*/
```

### 2.2 Caching Strategy
```typescript
// apps/api/src/common/cache/cache.service.ts
import { Injectable, Inject } from '@nestjs/common';
import { CACHE_MANAGER } from '@nestjs/cache-manager';
import { Cache } from 'cache-manager';
import { RedisStore } from 'cache-manager-redis-yet';

interface CacheOptions {
  ttl?: number; // seconds
  tags?: string[];
}

@Injectable()
export class CacheService {
  private readonly defaultTTL = 3600; // 1 hour

  constructor(@Inject(CACHE_MANAGER) private cacheManager: Cache) {}

  async get<T>(key: string): Promise<T | undefined> {
    return this.cacheManager.get<T>(key);
  }

  async set(key: string, value: unknown, options: CacheOptions = {}): Promise<void> {
    const ttl = options.ttl || this.defaultTTL;
    await this.cacheManager.set(key, value, ttl);
    
    // Store tag relationships for cache invalidation
    if (options.tags) {
      for (const tag of options.tags) {
        const tagKey = `tag:${tag}`;
        const existing = await this.cacheManager.get<string[]>(tagKey) || [];
        if (!existing.includes(key)) {
          await this.cacheManager.set(tagKey, [...existing, key], ttl * 2);
        }
      }
    }
  }

  async delete(key: string): Promise<void> {
    await this.cacheManager.del(key);
  }

  async reset(): Promise<void> {
    await this.cacheManager.reset();
  }

  async getOrSet<T>(
    key: string,
    factory: () => Promise<T>,
    options: CacheOptions = {},
  ): Promise<T> {
    const cached = await this.get<T>(key);
    if (cached !== undefined) {
      return cached;
    }

    const value = await factory();
    await this.set(key, value, options);
    return value;
  }

  async invalidateByTag(tag: string): Promise<void> {
    const tagKey = `tag:${tag}`;
    const keys = await this.cacheManager.get<string[]>(tagKey) || [];
    
    await Promise.all(keys.map(key => this.delete(key)));
    await this.delete(tagKey);
  }

  async invalidatePattern(pattern: string): Promise<void> {
    const store = this.cacheManager.store as RedisStore;
    const keys = await store.keys(pattern);
    await Promise.all(keys.map(key => this.delete(key)));
  }

  // Cache warming for frequently accessed data
  async warmCache<T>(key: string, factory: () => Promise<T>, options: CacheOptions = {}): Promise<void> {
    const exists = await this.get(key);
    if (!exists) {
      const value = await factory();
      await this.set(key, value, options);
    }
  }
}

// Usage in services
// apps/api/src/products/products.service.ts (caching example)
@Injectable()
export class ProductService {
  constructor(
    private prisma: PrismaService,
    private cache: CacheService,
  ) {}

  async findBySlug(slug: string): Promise<Product | null> {
    return this.cache.getOrSet(
      `product:${slug}`,
      () => this.prisma.product.findUnique({ 
        where: { slug },
        include: {
          categories: true,
          images: true,
          affiliateLinks: true,
        },
      }),
      { ttl: 3600, tags: ['products', `product:${slug}`] },
    );
  }

  async update(id: string, data: UpdateProductDto): Promise<Product> {
    const product = await this.prisma.product.update({ 
      where: { id }, 
      data,
      include: { categories: true },
    });
    
    // Invalidate caches
    await this.cache.delete(`product:${product.slug}`);
    await this.cache.invalidateByTag('products');
    await this.cache.invalidateByTag(`product:${id}`);
    
    return product;
  }

  async findAll(filters: ProductFilterDto): Promise<PaginatedProducts> {
    const cacheKey = `products:list:${JSON.stringify(filters)}`;
    
    return this.cache.getOrSet(
      cacheKey,
      () => this.fetchProductsFromDB(filters),
      { ttl: 300, tags: ['products'] },
    );
  }
}
```

### 2.3 CDN & Edge Caching
```typescript
// apps/web/middleware.ts
import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';

export function middleware(request: NextRequest) {
  const response = NextResponse.next();
  const { pathname } = request.nextUrl;
  
  // Static assets - long cache
  if (pathname.startsWith('/_next/static') || pathname.startsWith('/images')) {
    response.headers.set('Cache-Control', 'public, max-age=31536000, immutable');
    response.headers.set('Vary', 'Accept-Encoding');
  }
  
  // Public pages - short cache with revalidation
  if (pathname === '/' || pathname.startsWith('/products')) {
    response.headers.set('Cache-Control', 'public, s-maxage=60, stale-while-revalidate=300');
  }
  
  // Product detail pages - medium cache
  if (pathname.match(/^\/products\/[^\/]+$/)) {
    response.headers.set('Cache-Control', 'public, s-maxage=300, stale-while-revalidate=600');
  }
  
  // API routes - no cache by default
  if (pathname.startsWith('/api')) {
    response.headers.set('Cache-Control', 'no-store, must-revalidate');
  }
  
  // Security headers
  response.headers.set('X-Frame-Options', 'DENY');
  response.headers.set('X-Content-Type-Options', 'nosniff');
  response.headers.set('Referrer-Policy', 'strict-origin-when-cross-origin');
  response.headers.set('X-DNS-Prefetch-Control', 'on');
  
  return response;
}

export const config = {
  matcher: ['/((?!api/analytics|_next/static|_next/image|favicon.ico).*)'],
};
```

### 2.4 Image Optimization
```typescript
// apps/web/lib/image-loader.ts
import { ImageLoaderProps } from 'next/image';

export function customImageLoader({ src, width, quality }: ImageLoaderProps): string {
  // Skip optimization for local development
  if (process.env.NODE_ENV === 'development' && src.startsWith('/')) {
    return src;
  }
  
  // External CDN with image optimization
  if (src.startsWith('https://cdn.')) {
    const params = new URLSearchParams({
      url: src,
      w: width.toString(),
      q: (quality || 75).toString(),
      fm: 'webp',
    });
    return `${process.env.NEXT_PUBLIC_IMAGE_CDN_URL}/?${params}`;
  }
  
  // MinIO/S3 images
  if (src.includes('minio') || src.includes('s3')) {
    return `${src}?w=${width}&q=${quality || 75}&format=webp`;
  }
  
  return src;
}

// apps/web/next.config.js
module.exports = {
  images: {
    loader: 'custom',
    loaderFile: './lib/image-loader.ts',
    deviceSizes: [640, 750, 828, 1080, 1200, 1920, 2048, 3840],
    imageSizes: [16, 32, 48, 64, 96, 128, 256, 384],
    formats: ['image/webp', 'image/avif'],
    minimumCacheTTL: 60 * 60 * 24 * 30, // 30 days
    dangerouslyAllowSVG: false,
    contentSecurityPolicy: "default-src 'self'; script-src 'none'; sandbox;",
  },
};
```

---

## 3. Docker Production Configuration

### 3.1 Multi-Stage API Dockerfile
```dockerfile
# apps/api/Dockerfile
# ==================== BUILD STAGE ====================
FROM node:20-alpine AS builder

# Install build dependencies
RUN apk add --no-cache python3 make g++

WORKDIR /app

# Copy dependency files
COPY package*.json ./
COPY prisma ./prisma/

# Install dependencies
RUN npm ci

# Copy source code
COPY . .

# Generate Prisma client
RUN npx prisma generate

# Build application
RUN npm run build

# Remove dev dependencies
RUN npm prune --production

# ==================== PRODUCTION STAGE ====================
FROM node:20-alpine AS production

# Security: Install dumb-init for proper signal handling
RUN apk add --no-cache dumb-init

# Security: Create non-root user
RUN addgroup -g 1001 -S nodejs && \
    adduser -S nodejs -u 1001

WORKDIR /app

# Copy only necessary files
COPY --from=builder --chown=nodejs:nodejs /app/dist ./dist
COPY --from=builder --chown=nodejs:nodejs /app/node_modules ./node_modules
COPY --from=builder --chown=nodejs:nodejs /app/package*.json ./
COPY --from=builder --chown=nodejs:nodejs /app/prisma ./prisma

# Security: Set proper permissions
RUN chmod -R 550 /app/dist && \
    chmod -R 550 /app/node_modules && \
    chmod 440 /app/package*.json

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=30s --retries=3 \
    CMD node -e "require('http').get('http://localhost:3001/api/v1/health', (r) => {process.exit(r.statusCode === 200 ? 0 : 1)})"

# Switch to non-root user
USER nodejs

# Expose port
EXPOSE 3001

# Use dumb-init to handle signals properly
ENTRYPOINT ["dumb-init", "--"]

# Start application
CMD ["node", "dist/main.js"]
```

### 3.2 Web Dockerfile
```dockerfile
# apps/web/Dockerfile
# ==================== BUILD STAGE ====================
FROM node:20-alpine AS builder

WORKDIR /app

# Copy dependency files
COPY package*.json ./

# Install dependencies
RUN npm ci

# Copy source code
COPY . .

# Build with production environment
ENV NEXT_TELEMETRY_DISABLED=1
ENV NODE_ENV=production

RUN npm run build

# ==================== PRODUCTION STAGE ====================
FROM node:20-alpine AS production

WORKDIR /app

ENV NODE_ENV=production
ENV NEXT_TELEMETRY_DISABLED=1

# Copy standalone output
COPY --from=builder /app/.next/standalone ./
COPY --from=builder /app/.next/static ./.next/static
COPY --from=builder /app/public ./public

# Security: Create non-root user
RUN addgroup -g 1001 -S nextjs && \
    adduser -S nextjs -u 1001

RUN chown -R nextjs:nextjs /app

USER nextjs

EXPOSE 3000

ENV PORT=3000
ENV HOSTNAME="0.0.0.0"

CMD ["node", "server.js"]
```

### 3.3 Production Docker Compose
```yaml
# docker/docker-compose.prod.yml
version: '3.9'

services:
  # ==================== APPLICATION SERVICES ====================
  
  api:
    build:
      context: ../apps/api
      dockerfile: Dockerfile
    container_name: affiliate-api
    restart: unless-stopped
    deploy:
      replicas: 2
      resources:
        limits:
          cpus: '1.0'
          memory: 1G
        reservations:
          cpus: '0.5'
          memory: 512M
    environment:
      - NODE_ENV=production
      - DATABASE_URL=${DATABASE_URL}
      - REDIS_URL=${REDIS_URL}
      - RABBIT_URL=${RABBIT_URL}
      - JWT_SECRET=${JWT_SECRET}
      - JWT_REFRESH_SECRET=${JWT_REFRESH_SECRET}
      - STORAGE_ACCESS_KEY=${STORAGE_ACCESS_KEY}
      - STORAGE_SECRET_KEY=${STORAGE_SECRET_KEY}
      - INTERNAL_API_KEY=${INTERNAL_API_KEY}
    depends_on:
      postgres:
        condition: service_healthy
      redis:
        condition: service_healthy
      rabbitmq:
        condition: service_healthy
    networks:
      - affiliate-network
    logging:
      driver: "json-file"
      options:
        max-size: "10m"
        max-file: "3"

  web:
    build:
      context: ../apps/web
      dockerfile: Dockerfile
    container_name: affiliate-web
    restart: unless-stopped
    deploy:
      replicas: 2
      resources:
        limits:
          cpus: '0.5'
          memory: 512M
        reservations:
          cpus: '0.25'
          memory: 256M
    environment:
      - NODE_ENV=production
      - API_URL=http://api:3001
      - NEXT_PUBLIC_API_URL=${NEXT_PUBLIC_API_URL}
    depends_on:
      - api
    networks:
      - affiliate-network
    logging:
      driver: "json-file"
      options:
        max-size: "10m"
        max-file: "3"

  # ==================== REVERSE PROXY ====================
  
  nginx:
    image: nginx:alpine
    container_name: affiliate-nginx
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./nginx/nginx.conf:/etc/nginx/nginx.conf:ro
      - ./nginx/conf.d:/etc/nginx/conf.d:ro
      - ./nginx/ssl:/etc/nginx/ssl:ro
      - certbot-data:/etc/letsencrypt
      - ./nginx/logs:/var/log/nginx
    depends_on:
      - api
      - web
    networks:
      - affiliate-network
    logging:
      driver: "json-file"
      options:
        max-size: "10m"
        max-file: "5"

  certbot:
    image: certbot/certbot
    container_name: affiliate-certbot
    restart: unless-stopped
    volumes:
      - certbot-data:/etc/letsencrypt
      - ./certbot/www:/var/www/certbot
    entrypoint: "/bin/sh -c 'trap exit TERM; while :; do certbot renew; sleep 12h & wait $${!}; done;'"

  # ==================== DATABASE SERVICES ====================
  
  postgres:
    image: postgres:16-alpine
    container_name: affiliate-postgres
    restart: unless-stopped
    environment:
      POSTGRES_USER: ${DB_USER}
      POSTGRES_PASSWORD: ${DB_PASSWORD}
      POSTGRES_DB: ${DB_NAME}
    volumes:
      - postgres_data:/var/lib/postgresql/data
      - ./backups:/backups
    command: 
      - "postgres"
      - "-c"
      - "shared_buffers=256MB"
      - "-c"
      - "effective_cache_size=768MB"
      - "-c"
      - "maintenance_work_mem=64MB"
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U ${DB_USER} -d ${DB_NAME}"]
      interval: 10s
      timeout: 5s
      retries: 5
    networks:
      - affiliate-network
    logging:
      driver: "json-file"
      options:
        max-size: "10m"
        max-file: "3"

  redis:
    image: redis:7-alpine
    container_name: affiliate-redis
    restart: unless-stopped
    command: >
      sh -c "redis-server 
      --appendonly yes 
      --appendfsync everysec
      --requirepass ${REDIS_PASSWORD}
      --maxmemory 512mb
      --maxmemory-policy allkeys-lru"
    volumes:
      - redis_data:/data
    healthcheck:
      test: ["CMD", "redis-cli", "-a", "${REDIS_PASSWORD}", "ping"]
      interval: 10s
      timeout: 5s
      retries: 5
    networks:
      - affiliate-network

  rabbitmq:
    image: rabbitmq:3-alpine
    container_name: affiliate-rabbitmq
    restart: unless-stopped
    environment:
      RABBITMQ_DEFAULT_USER: ${RABBIT_USER}
      RABBITMQ_DEFAULT_PASS: ${RABBIT_PASSWORD}
    volumes:
      - rabbitmq_data:/var/lib/rabbitmq
    healthcheck:
      test: rabbitmq-diagnostics -q ping
      interval: 30s
      timeout: 30s
      retries: 3
    networks:
      - affiliate-network

  # ==================== BACKUP SERVICE ====================
  
  backup:
    image: offen/docker-volume-backup:latest
    container_name: affiliate-backup
    restart: unless-stopped
    environment:
      BACKUP_CRON_EXPRESSION: "0 2 * * *"
      BACKUP_RETENTION_DAYS: "30"
      AWS_S3_BUCKET_NAME: ${BACKUP_BUCKET}
      AWS_ACCESS_KEY_ID: ${BACKUP_ACCESS_KEY}
      AWS_SECRET_ACCESS_KEY: ${BACKUP_SECRET_KEY}
      AWS_ENDPOINT: ${BACKUP_ENDPOINT}
    volumes:
      - postgres_data:/backup/postgres:ro
      - redis_data:/backup/redis:ro
    networks:
      - affiliate-network

volumes:
  postgres_data:
    driver: local
  redis_data:
    driver: local
  rabbitmq_data:
    driver: local
  certbot-data:
    driver: local

networks:
  affiliate-network:
    driver: bridge
```

### 3.4 NGINX Configuration
```nginx
# docker/nginx/nginx.conf
user nginx;
worker_processes auto;
error_log /var/log/nginx/error.log warn;
pid /var/run/nginx.pid;

events {
    worker_connections 1024;
    use epoll;
    multi_accept on;
}

http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;

    # Logging format
    log_format main '$remote_addr - $remote_user [$time_local] "$request" '
                    '$status $body_bytes_sent "$http_referer" '
                    '"$http_user_agent" "$http_x_forwarded_for" '
                    'rt=$request_time uct="$upstream_connect_time" '
                    'uht="$upstream_header_time" urt="$upstream_response_time"';

    access_log /var/log/nginx/access.log main;

    # Performance
    sendfile on;
    tcp_nopush on;
    tcp_nodelay on;
    keepalive_timeout 65;
    types_hash_max_size 2048;
    client_max_body_size 50M;

    # Compression
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml application/json application/javascript application/rss+xml application/atom+xml image/svg+xml;

    # Rate limiting zones
    limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;
    limit_req_zone $binary_remote_addr zone=login:10m rate=1r/s;

    # Upstream servers
    upstream api_servers {
        least_conn;
        server api:3001 max_fails=3 fail_timeout=30s;
        keepalive 32;
    }

    upstream web_servers {
        least_conn;
        server web:3000 max_fails=3 fail_timeout=30s;
        keepalive 32;
    }

    # Include server configs
    include /etc/nginx/conf.d/*.conf;
}
```

```nginx
# docker/nginx/conf.d/default.conf
server {
    listen 80;
    server_name _;
    
    # Redirect HTTP to HTTPS
    location / {
        return 301 https://$host$request_uri;
    }
    
    # Certbot challenge
    location /.well-known/acme-challenge/ {
        root /var/www/certbot;
    }
}

server {
    listen 443 ssl http2;
    server_name your-domain.com;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;
    ssl_trusted_certificate /etc/letsencrypt/live/your-domain.com/chain.pem;

    # Modern SSL configuration
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 1d;
    ssl_session_tickets off;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self'; connect-src 'self' https://api.your-domain.com;" always;

    # API proxy
    location /api/ {
        limit_req zone=api burst=20 nodelay;
        
        proxy_pass http://api_servers;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;
        proxy_read_timeout 86400;
    }

    # Next.js frontend
    location / {
        proxy_pass http://web_servers;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;
    }

    # Static assets caching
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        proxy_pass http://web_servers;
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }
}
```

---

## 4. Monitoring & Observability

### 4.1 Health Checks
```typescript
// apps/api/src/health/health.module.ts
import { Module } from '@nestjs/common';
import { TerminusModule } from '@nestjs/terminus';
import { HttpModule } from '@nestjs/axios';
import { HealthController } from './health.controller';
import { PrismaHealthIndicator } from './indicators/prisma.health';
import { RedisHealthIndicator } from './indicators/redis.health';

@Module({
  imports: [
    TerminusModule,
    HttpModule,
  ],
  controllers: [HealthController],
  providers: [
    PrismaHealthIndicator,
    RedisHealthIndicator,
  ],
})
export class HealthModule {}
```

```typescript
// apps/api/src/health/health.controller.ts
import { Controller, Get } from '@nestjs/common';
import {
  HealthCheck,
  HealthCheckService,
  PrismaHealthIndicator,
  MemoryHealthIndicator,
  DiskHealthIndicator,
} from '@nestjs/terminus';
import { RedisHealthIndicator } from './indicators/redis.health';
import { Public } from '../common/decorators/public.decorator';

@Controller('health')
export class HealthController {
  constructor(
    private health: HealthCheckService,
    private prisma: PrismaHealthIndicator,
    private redis: RedisHealthIndicator,
    private memory: MemoryHealthIndicator,
    private disk: DiskHealthIndicator,
  ) {}

  @Get()
  @Public()
  @HealthCheck()
  check() {
    return this.health.check([
      () => this.prisma.pingCheck('database'),
      () => this.redis.isHealthy('redis'),
      () => this.memory.checkHeap('memory_heap', 150 * 1024 * 1024),
      () => this.memory.checkRSS('memory_rss', 150 * 1024 * 1024),
      () => this.disk.checkStorage('disk', { thresholdPercent: 0.9, path: '/' }),
    ]);
  }

  @Get('liveness')
  @Public()
  liveness() {
    return { 
      status: 'ok', 
      timestamp: new Date().toISOString(),
      uptime: process.uptime(),
    };
  }

  @Get('readiness')
  @Public()
  async readiness() {
    const checks = await Promise.allSettled([
      this.prisma.pingCheck('database'),
      this.redis.isHealthy('redis'),
    ]);
    
    const results = {
      database: checks[0].status === 'fulfilled',
      redis: checks[1].status === 'fulfilled',
    };
    
    const allHealthy = Object.values(results).every(Boolean);
    
    return {
      status: allHealthy ? 'ok' : 'error',
      checks: results,
      timestamp: new Date().toISOString(),
    };
  }
}
```

### 4.2 Structured Logging with Pino
```typescript
// apps/api/src/config/logger.config.ts
import { LoggerModule } from 'nestjs-pino';
import { pino } from 'pino';

const productionTransport = {
  target: 'pino/file',
  options: { destination: '/var/log/app/app.log' },
};

const developmentTransport = {
  target: 'pino-pretty',
  options: {
    singleLine: true,
    colorize: true,
    levelFirst: true,
    translateTime: "yyyy-mm-dd HH:MM:ss.l",
  },
};

export const loggerConfig = LoggerModule.forRoot({
  pinoHttp: {
    level: process.env.NODE_ENV === 'production' ? 'info' : 'debug',
    transport: process.env.NODE_ENV === 'production' ? productionTransport : developmentTransport,
    serializers: {
      req: (req) => ({
        id: req.id,
        method: req.method,
        url: req.url,
        userAgent: req.headers?.['user-agent'],
        remoteAddress: req.remoteAddress,
      }),
      res: (res) => ({
        statusCode: res.statusCode,
        responseTime: res.responseTime,
      }),
    },
    customProps: (req) => ({
      userId: req.user?.sub,
      requestId: req.id,
      environment: process.env.NODE_ENV,
      service: 'api',
    }),
    redact: {
      paths: [
        'req.headers.authorization',
        'req.headers.cookie',
        'req.body.password',
        'req.body.token',
        'req.body.apiKey',
      ],
      remove: true,
    },
    genReqId: (req) => req.headers['x-request-id'] || crypto.randomUUID(),
  },
});
```

### 4.3 Prometheus Metrics
```typescript
// apps/api/src/metrics/metrics.module.ts
import { Module } from '@nestjs/common';
import { PrometheusModule } from '@willsoto/nestjs-prometheus';
import { MetricsController } from './metrics.controller';
import { MetricsService } from './metrics.service';

@Module({
  imports: [
    PrometheusModule.register({
      path: '/metrics',
      defaultMetrics: {
        enabled: true,
      },
    }),
  ],
  controllers: [MetricsController],
  providers: [MetricsService],
  exports: [MetricsService],
})
export class MetricsModule {}
```

```typescript
// apps/api/src/metrics/metrics.service.ts
import { Injectable } from '@nestjs/common';
import { Counter, Histogram, Gauge } from 'prom-client';

@Injectable()
export class MetricsService {
  private httpRequestDuration: Histogram<string>;
  private httpRequestsTotal: Counter<string>;
  private affiliateClicksTotal: Counter<string>;
  private activeConnections: Gauge<string>;
  private cacheHitRatio: Gauge<string>;

  constructor() {
    this.httpRequestDuration = new Histogram({
      name: 'http_request_duration_seconds',
      help: 'Duration of HTTP requests in seconds',
      labelNames: ['method', 'route', 'status_code'],
      buckets: [0.1, 0.25, 0.5, 1, 2.5, 5, 10],
    });

    this.httpRequestsTotal = new Counter({
      name: 'http_requests_total',
      help: 'Total number of HTTP requests',
      labelNames: ['method', 'route', 'status_code'],
    });

    this.affiliateClicksTotal = new Counter({
      name: 'affiliate_clicks_total',
      help: 'Total number of affiliate link clicks',
      labelNames: ['platform', 'product_id'],
    });

    this.activeConnections = new Gauge({
      name: 'active_connections',
      help: 'Number of active connections',
      labelNames: ['service'],
    });

    this.cacheHitRatio = new Gauge({
      name: 'cache_hit_ratio',
      help: 'Cache hit ratio percentage',
      labelNames: ['cache_type'],
    });
  }

  recordHttpRequest(method: string, route: string, statusCode: number, duration: number) {
    const labels = { method, route, status_code: statusCode.toString() };
    this.httpRequestDuration.observe(labels, duration);
    this.httpRequestsTotal.inc(labels);
  }

  recordAffiliateClick(platform: string, productId: string) {
    this.affiliateClicksTotal.inc({ platform, product_id: productId });
  }

  setActiveConnections(service: string, count: number) {
    this.activeConnections.set({ service }, count);
  }

  setCacheHitRatio(cacheType: string, ratio: number) {
    this.cacheHitRatio.set({ cache_type: cacheType }, ratio);
  }
}
```

### 4.4 Sentry Error Tracking
```typescript
// apps/api/src/config/sentry.config.ts
import * as Sentry from '@sentry/nestjs';
import { nodeProfilingIntegration } from '@sentry/profiling-node';

export function initSentry(dsn: string) {
  Sentry.init({
    dsn,
    environment: process.env.NODE_ENV,
    release: process.env.APP_VERSION,
    integrations: [
      nodeProfilingIntegration(),
    ],
    tracesSampleRate: process.env.NODE_ENV === 'production' ? 0.1 : 1.0,
    profilesSampleRate: process.env.NODE_ENV === 'production' ? 0.01 : 1.0,
    beforeSend(event) {
      // Filter out sensitive data
      if (event.request) {
        delete event.request.cookies;
        delete event.request.headers?.authorization;
        delete event.request.headers?.cookie;
      }
      return event;
    },
  });
}

// apps/api/src/main.ts
import { initSentry } from './config/sentry.config';

async function bootstrap() {
  // Initialize Sentry before app creation
  if (process.env.SENTRY_DSN) {
    initSentry(process.env.SENTRY_DSN);
  }
  
  const app = await NestFactory.create(AppModule, { 
    bufferLogs: true,
    rawBody: true,
  });
  
  // ... rest of bootstrap
}
```

```typescript
// apps/web/sentry.client.config.ts
import * as Sentry from '@sentry/nextjs';

Sentry.init({
  dsn: process.env.NEXT_PUBLIC_SENTRY_DSN,
  environment: process.env.NODE_ENV,
  release: process.env.NEXT_PUBLIC_APP_VERSION,
  tracesSampleRate: process.env.NODE_ENV === 'production' ? 0.1 : 1.0,
  replaysSessionSampleRate: 0.01,
  replaysOnErrorSampleRate: 1.0,
  integrations: [
    Sentry.replayIntegration({
      maskAllText: true,
      blockAllMedia: true,
    }),
  ],
  beforeSend(event) {
    // Filter out PII
    if (event.user) {
      delete event.user.email;
      delete event.user.ip_address;
    }
    return event;
  },
});
```

---

## 5. Deployment Automation

### 5.1 GitHub Actions Production Deployment
```yaml
# .github/workflows/deploy-production.yml
name: Deploy to Production

on:
  push:
    branches: [main]
  workflow_dispatch:
    inputs:
      environment:
        description: 'Environment to deploy'
        required: true
        default: 'production'
        type: choice
        options:
          - production
          - staging

env:
  REGISTRY: ghcr.io
  IMAGE_PREFIX: ${{ github.repository }}

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with:
          node-version: '20'
          cache: 'npm'
      - run: npm ci
      - run: npm run test
      - run: npm run test:e2e

  build-and-push:
    needs: test
    runs-on: ubuntu-latest
    permissions:
      contents: read
      packages: write
    steps:
      - uses: actions/checkout@v4
      
      - name: Set up Docker Buildx
        uses: docker/setup-buildx-action@v3
      
      - name: Login to Container Registry
        uses: docker/login-action@v3
        with:
          registry: ${{ env.REGISTRY }}
          username: ${{ github.actor }}
          password: ${{ secrets.GITHUB_TOKEN }}
      
      - name: Extract metadata
        id: meta
        uses: docker/metadata-action@v5
        with:
          images: |
            ${{ env.REGISTRY }}/${{ env.IMAGE_PREFIX }}/api
            ${{ env.REGISTRY }}/${{ env.IMAGE_PREFIX }}/web
          tags: |
            type=sha,prefix={{branch}}-
            type=raw,value=latest,enable={{is_default_branch}}
      
      - name: Build and push API
        uses: docker/build-push-action@v5
        with:
          context: ./apps/api
          push: true
          tags: ${{ env.REGISTRY }}/${{ env.IMAGE_PREFIX }}/api:${{ github.sha }}
          cache-from: type=gha
          cache-to: type=gha,mode=max
      
      - name: Build and push Web
        uses: docker/build-push-action@v5
        with:
          context: ./apps/web
          push: true
          tags: ${{ env.REGISTRY }}/${{ env.IMAGE_PREFIX }}/web:${{ github.sha }}
          cache-from: type=gha
          cache-to: type=gha,mode=max

  deploy:
    needs: build-and-push
    runs-on: ubuntu-latest
    environment: production
    steps:
      - uses: actions/checkout@v4
      
      - name: Deploy to production
        uses: appleboy/ssh-action@v1.0.0
        with:
          host: ${{ secrets.PROD_HOST }}
          username: ${{ secrets.PROD_USER }}
          key: ${{ secrets.PROD_SSH_KEY }}
          script: |
            cd /opt/affiliate-platform
            
            # Pull latest images
            docker-compose -f docker/docker-compose.prod.yml pull
            
            # Run migrations
            docker-compose -f docker/docker-compose.prod.yml run --rm api npx prisma migrate deploy
            
            # Deploy with zero downtime
            docker-compose -f docker/docker-compose.prod.yml up -d --remove-orphans
            
            # Cleanup
            docker system prune -f
            
            # Health check
            sleep 10
            curl -f http://localhost:3001/api/v1/health || exit 1
            
            echo "Deployment successful!"

  notify:
    needs: deploy
    runs-on: ubuntu-latest
    if: always()
    steps:
      - name: Notify on success
        if: needs.deploy.result == 'success'
        run: |
          echo "🚀 Deployment successful!"
          # Add Slack/Discord notification here
      
      - name: Notify on failure
        if: needs.deploy.result == 'failure'
        run: |
          echo "❌ Deployment failed!"
          # Add Slack/Discord notification here
```

---

## 6. Disaster Recovery

### 6.1 Backup Strategy
```bash
#!/bin/bash
# scripts/backup.sh

set -e

BACKUP_DIR="/backups/affiliate-platform"
DATE=$(date +%Y%m%d_%H%M%S)
RETENTION_DAYS=30
S3_BUCKET="s3://your-backup-bucket"

# Create backup directory
mkdir -p "$BACKUP_DIR"

# Database backup
echo "Backing up database..."
docker exec affiliate-postgres pg_dump -U affiliate affiliate_db | gzip > "$BACKUP_DIR/db_$DATE.sql.gz"

# Redis backup
echo "Backing up Redis..."
docker exec affiliate-redis redis-cli BGSAVE
sleep 5
sudo cp /var/lib/docker/volumes/affiliate-redis/_data/dump.rdb "$BACKUP_DIR/redis_$DATE.rdb"

# File uploads backup
echo "Backing up uploads..."
tar -czf "$BACKUP_DIR/uploads_$DATE.tar.gz" /var/lib/docker/volumes/affiliate-minio/_data

# Upload to S3
echo "Uploading to S3..."
aws s3 sync "$BACKUP_DIR" "$S3_BUCKET/backups/" --storage-class STANDARD_IA

# Clean local old backups
find "$BACKUP_DIR" -name "*.gz" -mtime +$RETENTION_DAYS -delete
find "$BACKUP_DIR" -name "*.rdb" -mtime +$RETENTION_DAYS -delete

# Clean S3 old backups
aws s3 ls "$S3_BUCKET/backups/" | awk '{print $4}' | sort -r | tail -n +31 | xargs -I {} aws s3 rm "$S3_BUCKET/backups/{}"

echo "Backup completed: $DATE"
```

### 6.2 Recovery Procedures
```markdown
# Disaster Recovery Runbook

## Database Recovery
1. Stop application services:
   ```bash
   docker-compose -f docker/docker-compose.prod.yml stop api web
   ```

2. Restore from backup:
   ```bash
   gunzip < db_YYYYMMDD_HHMMSS.sql.gz | docker exec -i affiliate-postgres psql -U affiliate
   ```

3. Restart services:
   ```bash
   docker-compose -f docker/docker-compose.prod.yml up -d api web
   ```

## Complete System Recovery
1. Provision new server with Docker
2. Clone repository: `git clone ...`
3. Copy `.env.production` file
4. Restore backups to volumes
5. Run: `docker-compose -f docker-compose.prod.yml up -d`
6. Verify: `curl http://localhost:3001/api/v1/health`

## RPO/RTO Targets
- Recovery Point Objective (RPO): 1 hour (hourly backups)
- Recovery Time Objective (RTO): 30 minutes
```

---

## 7. Pre-Launch Checklist

### Security ✅
- [ ] Penetration testing completed
- [ ] Security headers configured
- [ ] Rate limiting enabled
- [ ] Input validation implemented
- [ ] SQL injection prevention tested
- [ ] XSS protection verified
- [ ] Secrets management configured
- [ ] API key authentication for internal services

### Performance ✅
- [ ] Database indexes created
- [ ] Query performance < 100ms (p95)
- [ ] Redis caching configured
- [ ] CDN configured
- [ ] Image optimization enabled
- [ ] Bundle size < 200KB (gzipped)
- [ ] Compression enabled

### Monitoring ✅
- [ ] Health checks implemented
- [ ] Logging configured (structured)
- [ ] Metrics collection enabled (Prometheus)
- [ ] Alerting rules defined
- [ ] Error tracking integrated (Sentry)
- [ ] Uptime monitoring configured

### Compliance ✅
- [ ] Privacy policy published
- [ ] Terms of service published
- [ ] Cookie consent implemented
- [ ] GDPR compliance verified
- [ ] Data retention policies set

---

## Success Criteria

✅ **Phase 5 Complete When:**
1. Security scan shows zero critical vulnerabilities
2. Performance benchmarks meet targets (< 200ms API, < 2.5s LCP)
3. Monitoring dashboards show all services healthy
4. Backup/restore tested successfully
5. CI/CD pipeline deploys with zero downtime
6. SSL certificates auto-renew
7. 99.9% uptime achieved in first week

---

[← Back to Master Plan](./master-plan.md) | [Previous: Phase 4 - Analytics Engine](./phase-04-analytics-engine.md)

**🎉 Project Complete!**
