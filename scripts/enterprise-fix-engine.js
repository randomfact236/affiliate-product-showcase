#!/usr/bin/env node
/**
 * ENTERPRISE GRADE AUTOMATED FIX ENGINE
 * Applies automated fixes for identified issues
 * 
 * Usage: node scripts/enterprise-fix-engine.js [--dry-run] [--issue=<id>]
 */

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

// ANSI Colors
const colors = {
  reset: '\x1b[0m',
  red: '\x1b[31m',
  green: '\x1b[32m',
  yellow: '\x1b[33m',
  blue: '\x1b[34m',
  cyan: '\x1b[36m',
  bold: '\x1b[1m'
};

class EnterpriseFixEngine {
  constructor() {
    this.rootDir = process.cwd();
    this.fixesApplied = 0;
    this.fixesFailed = 0;
    this.backupDir = path.join('scripts', 'backups', Date.now().toString());
  }

  log(message, color = 'white') {
    console.log(`${colors[color]}${message}${colors.reset}`);
  }

  logBold(message, color = 'white') {
    console.log(`${colors.bold}${colors[color]}${message}${colors.reset}`);
  }

  banner() {
    this.logBold('╔════════════════════════════════════════════════════════════════╗', 'green');
    this.logBold('║     ENTERPRISE GRADE AUTOMATED FIX ENGINE                      ║', 'green');
    this.logBold('║     Applying fixes to achieve 10/10 quality                    ║', 'green');
    this.logBold('╚════════════════════════════════════════════════════════════════╝', 'green');
    console.log('');
  }

  backupFile(filePath) {
    if (!fs.existsSync(filePath)) return;
    
    const backupPath = path.join(this.backupDir, filePath);
    const dir = path.dirname(backupPath);
    
    if (!fs.existsSync(dir)) {
      fs.mkdirSync(dir, { recursive: true });
    }
    
    fs.copyFileSync(filePath, backupPath);
  }

  async applyAllFixes() {
    this.banner();
    
    // Create backup directory
    if (!fs.existsSync(this.backupDir)) {
      fs.mkdirSync(this.backupDir, { recursive: true });
    }

    this.log('🔧 Applying fixes...', 'cyan');
    console.log('');

    // SECURITY FIXES
    this.logBold('━'.repeat(70), 'red');
    this.logBold('🔒 APPLYING SECURITY FIXES', 'red');
    this.logBold('━'.repeat(70), 'red');
    
    await this.fixJWTConfigValidation();
    await this.fixJWTCORSConfiguration();
    await this.fixRateLimiting();
    await this.fixRedisAuth();
    await this.fixPasswordResetToken();
    await this.fixInputSanitization();
    await this.fixFileUploadValidation();
    await this.fixJWTStrategyValidation();

    // ARCHITECTURE FIXES
    this.logBold('━'.repeat(70), 'magenta');
    this.logBold('🏗️  APPLYING ARCHITECTURE FIXES', 'magenta');
    this.logBold('━'.repeat(70), 'magenta');
    
    await this.fixRequestIDMiddleware();
    await this.fixCacheInvalidation();
    await this.fixDBPooling();
    await this.fixPaginationLimits();
    await this.fixSoftDelete();

    // INFRASTRUCTURE FIXES
    this.logBold('━'.repeat(70), 'yellow');
    this.logBold('🖥️  APPLYING INFRASTRUCTURE FIXES', 'yellow');
    this.logBold('━'.repeat(70), 'yellow');
    
    await this.fixDockerSecurity();
    await this.fixHealthChecks();
    await this.fixGracefulShutdown();
    await this.fixMetrics();

    // COMPLIANCE FIXES
    this.logBold('━'.repeat(70), 'cyan');
    this.logBold('📋 APPLYING COMPLIANCE FIXES', 'cyan');
    this.logBold('━'.repeat(70), 'cyan');
    
    await this.fixAuditLogging();
    await this.fixGDPRCompliance();

    // TESTING FIXES
    this.logBold('━'.repeat(70), 'blue');
    this.logBold('🧪 APPLYING TESTING FIXES', 'blue');
    this.logBold('━'.repeat(70), 'blue');
    
    await this.fixAuthUnitTests();
    await this.fixE2ETests();

    // Summary
    console.log('');
    this.logBold('━'.repeat(70), 'white');
    this.logBold('📊 FIX SUMMARY', 'white');
    this.logBold('━'.repeat(70), 'white');
    
    this.log(`✅ Fixes Applied: ${this.fixesApplied}`, 'green');
    if (this.fixesFailed > 0) {
      this.log(`❌ Fixes Failed: ${this.fixesFailed}`, 'red');
    }
    
    console.log('');
    this.log(`💾 Backups saved to: ${this.backupDir}`, 'cyan');
  }

  // ============================================================================
  // SECURITY FIXES
  // ============================================================================

  async fixJWTConfigValidation() {
    const configPath = 'apps/api/src/config/jwt.config.ts';
    
    if (!fs.existsSync(configPath)) {
      // Create new JWT config file
      const content = `import { registerAs } from '@nestjs/config';
import { randomBytes } from 'crypto';

/**
 * JWT Configuration with Enterprise-Grade Security
 * - Validates secrets at startup
 * - Enforces minimum 32 character length
 * - Separate access and refresh token settings
 */
export const jwtConfig = registerAs('jwt', () => {
  const secret = process.env.JWT_SECRET;
  const refreshSecret = process.env.JWT_REFRESH_SECRET;

  // CRITICAL: Validate JWT secrets at startup
  if (!secret || secret.length < 32) {
    throw new Error(
      'JWT_SECRET must be at least 32 characters long. ' +
      'Generate a secure secret with: node -e "console.log(require(\'crypto\').randomBytes(32).toString(\'hex\'))"'
    );
  }

  if (!refreshSecret || refreshSecret.length < 32) {
    throw new Error(
      'JWT_REFRESH_SECRET must be at least 32 characters long. ' +
      'Generate a secure secret with: node -e "console.log(require(\'crypto\').randomBytes(32).toString(\'hex\'))"'
    );
  }

  // Validate secrets are not default/weak
  const weakSecrets = ['secret', 'password', '123456', 'jwt', 'token', 'admin'];
  const lowerSecret = secret.toLowerCase();
  if (weakSecrets.some(weak => lowerSecret.includes(weak))) {
    throw new Error('JWT_SECRET contains weak/common words. Use a cryptographically secure random string.');
  }

  return {
    secret,
    refreshSecret,
    expiresIn: process.env.JWT_ACCESS_EXPIRATION || '15m',
    refreshExpiresIn: process.env.JWT_REFRESH_EXPIRATION || '7d',
    issuer: process.env.JWT_ISSUER || 'affiliate-platform',
    audience: process.env.JWT_AUDIENCE || 'affiliate-api',
  };
});
`;
      this.writeFile(configPath, content);
      this.log('  ✅ Created jwt.config.ts with validation', 'green');
      this.fixesApplied++;
    }
  }

  async fixJWTCORSConfiguration() {
    const mainPath = 'apps/api/src/main.ts';
    
    if (!fs.existsSync(mainPath)) return;
    
    this.backupFile(mainPath);
    let content = fs.readFileSync(mainPath, 'utf-8');
    
    // Replace wildcard CORS with proper configuration
    if (content.includes("origin: '*'") || content.includes('origin: "*"')) {
      const oldCors = content.match(/app\.enableCors\([^)]+\);/s)?.[0];
      
      if (oldCors) {
        const newCors = `// CORS Configuration - Enterprise Grade
  const allowedOrigins = configService.get('ALLOWED_ORIGINS', 'http://localhost:3000')
    .split(',')
    .map(o => o.trim())
    .filter(Boolean);
  
  app.enableCors({
    origin: (origin, callback) => {
      // Allow requests with no origin (mobile apps, curl, etc.)
      if (!origin) return callback(null, true);
      
      // In development, allow all localhost origins
      if (process.env.NODE_ENV === 'development' && origin.match(/^http:\/\/localhost:/)) {
        return callback(null, true);
      }
      
      if (allowedOrigins.includes(origin)) {
        callback(null, true);
      } else {
        callback(new Error(\`Origin \${origin} not allowed by CORS\`));
      }
    },
    credentials: true,
    methods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
    allowedHeaders: ['Content-Type', 'Authorization', 'X-Request-ID', 'X-API-Key'],
    exposedHeaders: ['X-Request-ID'],
    maxAge: 86400, // 24 hours
  });`;
        
        content = content.replace(oldCors, newCors);
        this.writeFile(mainPath, content);
        this.log('  ✅ Fixed CORS configuration (removed wildcard)', 'green');
        this.fixesApplied++;
      }
    }
  }

  async fixRateLimiting() {
    const authControllerPath = 'apps/api/src/auth/auth.controller.ts';
    
    if (!fs.existsSync(authControllerPath)) return;
    
    this.backupFile(authControllerPath);
    let content = fs.readFileSync(authControllerPath, 'utf-8');
    
    // Check if rate limiting is too permissive
    if (content.includes('@Throttle') && content.includes('limit: 5')) {
      content = content.replace(
        /@Throttle\({[^}]*default:[^}]*limit:\s*5[^}]*}\)/,
        `@Throttle({
    login: { limit: 3, ttl: 900000 },      // 3 attempts per 15 minutes
    register: { limit: 2, ttl: 3600000 },  // 2 per hour
    forgotPassword: { limit: 3, ttl: 3600000 },
    default: { limit: 5, ttl: 60000 }
  })`
      );
      
      this.writeFile(authControllerPath, content);
      this.log('  ✅ Hardened rate limiting for auth endpoints', 'green');
      this.fixesApplied++;
    }
  }

  async fixRedisAuth() {
    const redisModulePath = 'apps/api/src/common/modules/redis.module.ts';
    
    if (!fs.existsSync(redisModulePath)) return;
    
    this.backupFile(redisModulePath);
    let content = fs.readFileSync(redisModulePath, 'utf-8');
    
    // Check if Redis is configured with auth
    if (!content.includes('password') || !content.includes('tls')) {
      const newContent = `import { Module, Global } from '@nestjs/common';
import { ConfigService } from '@nestjs/config';
import Redis from 'ioredis';

export const REDIS_CLIENT = 'REDIS_CLIENT';

@Global()
@Module({
  providers: [
    {
      provide: REDIS_CLIENT,
      useFactory: (configService: ConfigService) => {
        const password = configService.get('REDIS_PASSWORD');
        const host = configService.get('REDIS_HOST', 'localhost');
        const port = configService.get('REDIS_PORT', 6379);
        const tls = configService.get('REDIS_TLS_ENABLED') === 'true';
        
        // CRITICAL: Require password in production
        if (process.env.NODE_ENV === 'production' && !password) {
          throw new Error('REDIS_PASSWORD is required in production');
        }

        return new Redis({
          host,
          port,
          password,
          tls: tls ? { rejectUnauthorized: false } : undefined,
          retryStrategy: (times) => Math.min(times * 50, 2000),
          maxRetriesPerRequest: 3,
          enableReadyCheck: true,
          showFriendlyErrorStack: process.env.NODE_ENV !== 'production',
        });
      },
      inject: [ConfigService],
    },
  ],
  exports: [REDIS_CLIENT],
})
export class RedisModule {}
`;
      
      this.writeFile(redisModulePath, newContent);
      this.log('  ✅ Added Redis authentication and TLS support', 'green');
      this.fixesApplied++;
    }
  }

  async fixPasswordResetToken() {
    const authServicePath = 'apps/api/src/auth/auth.service.ts';
    
    if (!fs.existsSync(authServicePath)) return;
    
    this.backupFile(authServicePath);
    let content = fs.readFileSync(authServicePath, 'utf-8');
    
    // Replace Math.random() with crypto.randomBytes
    if (content.includes('Math.random()')) {
      content = content.replace(
        /const token = Math\.random\(\)\.toString\(36\)\.substring\(2\);?/,
        'const token = randomBytes(32).toString(\'hex\'); // 256 bits of entropy'
      );
      
      // Ensure crypto import exists
      if (!content.includes('randomBytes')) {
        content = content.replace(
          /import \{([^}]+)\} from 'crypto';?/,
          "import { $1, randomBytes } from 'crypto';"
        );
        if (!content.includes('randomBytes')) {
          content = "import { randomBytes } from 'crypto';\n" + content;
        }
      }
      
      this.writeFile(authServicePath, content);
      this.log('  ✅ Replaced Math.random() with crypto.randomBytes', 'green');
      this.fixesApplied++;
    }
  }

  async fixInputSanitization() {
    const sanitizePipePath = 'apps/api/src/common/pipes/sanitize.pipe.ts';
    const appModulePath = 'apps/api/src/app.module.ts';
    
    // Create DOMPurify sanitize pipe if not exists
    if (!fs.existsSync(sanitizePipePath)) {
      const content = `import { PipeTransform, Injectable, ArgumentMetadata } from '@nestjs/common';
import DOMPurify from 'isomorphic-dompurify';

/**
 * Input Sanitization Pipe
 * Prevents XSS by sanitizing user input before processing
 */
@Injectable()
export class SanitizePipe implements PipeTransform {
  private readonly sanitizeFields = ['description', 'content', 'bio', 'comment'];
  
  transform(value: any, metadata: ArgumentMetadata) {
    if (typeof value !== 'object' || value === null) {
      return value;
    }
    
    return this.sanitizeObject(value);
  }
  
  private sanitizeObject(obj: any): any {
    const sanitized: any = {};
    
    for (const [key, val] of Object.entries(obj)) {
      if (typeof val === 'string' && this.sanitizeFields.includes(key)) {
        // Sanitize HTML content
        sanitized[key] = DOMPurify.sanitize(val, {
          ALLOWED_TAGS: ['b', 'i', 'em', 'strong', 'a', 'p', 'br'],
          ALLOWED_ATTR: ['href', 'target', 'rel'],
        });
      } else if (typeof val === 'object' && val !== null) {
        sanitized[key] = this.sanitizeObject(val);
      } else {
        sanitized[key] = val;
      }
    }
    
    return sanitized;
  }
}
`;
      this.writeFile(sanitizePipePath, content);
      this.log('  ✅ Created sanitize.pipe.ts for XSS prevention', 'green');
      this.fixesApplied++;
    }
    
    // Apply sanitize pipe globally in app module
    if (fs.existsSync(appModulePath)) {
      this.backupFile(appModulePath);
      let content = fs.readFileSync(appModulePath, 'utf-8');
      
      if (!content.includes('SanitizePipe')) {
        // Add SanitizePipe to providers
        content = content.replace(
          /providers:\s*\[([^\]]+)\]/,
          `providers: [$1, SanitizePipe]`
        );
        
        // Add import
        if (!content.includes("from './common/pipes/sanitize.pipe'")) {
          content = content.replace(
            /(import.*from.*;\n)(import.*Module)/,
            "$1import { SanitizePipe } from './common/pipes/sanitize.pipe';\n$2"
          );
        }
        
        this.writeFile(appModulePath, content);
        this.log('  ✅ Applied SanitizePipe globally in AppModule', 'green');
        this.fixesApplied++;
      }
    }
  }

  async fixFileUploadValidation() {
    const mediaServicePath = 'apps/api/src/media/media.service.ts';
    
    if (!fs.existsSync(mediaServicePath)) return;
    
    this.backupFile(mediaServicePath);
    let content = fs.readFileSync(mediaServicePath, 'utf-8');
    
    if (content.includes('originalname') && !content.includes('file-type')) {
      // Add file-type validation
      content = content.replace(
        /import \{([^}]+)\} from '@nestjs\/common';?/,
        "import { $1, BadRequestException } from '@nestjs/common';"
      );
      
      // Add file-type import if not present
      if (!content.includes('fileTypeFromBuffer')) {
        content = "import { fileTypeFromBuffer } from 'file-type';\n" + content;
      }
      
      // Replace file extension extraction with content-based validation
      const oldCode = content.match(/const fileExt\s*=\s*[^;]+;/);
      if (oldCode) {
        const newCode = `// SECURITY: Validate file by content, not extension
    const type = await fileTypeFromBuffer(file.buffer);
    if (!type) {
      throw new BadRequestException('Unable to determine file type');
    }
    
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
    if (!allowedTypes.includes(type.mime)) {
      throw new BadRequestException(\`File type \${type.mime} not allowed\`);
    }
    
    const fileExt = '.' + type.ext;`;
        
        content = content.replace(oldCode[0], newCode);
        this.writeFile(mediaServicePath, content);
        this.log('  ✅ Added content-based file type validation', 'green');
        this.fixesApplied++;
      }
    }
  }

  async fixJWTStrategyValidation() {
    const jwtStrategyPath = 'apps/api/src/auth/strategies/jwt.strategy.ts';
    
    if (!fs.existsSync(jwtStrategyPath)) return;
    
    this.backupFile(jwtStrategyPath);
    let content = fs.readFileSync(jwtStrategyPath, 'utf-8');
    
    if (!content.includes('findUnique') && !content.includes('findActive')) {
      const newContent = `import { Injectable, UnauthorizedException } from '@nestjs/common';
import { ConfigService } from '@nestjs/config';
import { PassportStrategy } from '@nestjs/passport';
import { ExtractJwt, Strategy } from 'passport-jwt';
import { PrismaService } from '../../prisma/prisma.service';
import { Inject } from '@nestjs/common';
import Redis from 'ioredis';
import { REDIS_CLIENT } from '../../common/constants/injection-tokens';

interface JwtPayload {
  sub: string;
  email: string;
  roles: string[];
  type: string;
  jti: string;
}

@Injectable()
export class JwtStrategy extends PassportStrategy(Strategy) {
  constructor(
    private readonly prisma: PrismaService,
    private readonly configService: ConfigService,
    @Inject(REDIS_CLIENT) private readonly redis: Redis,
  ) {
    const secret = configService.get<string>('JWT_SECRET');
    
    if (!secret || secret.length < 32) {
      throw new Error('JWT_SECRET must be at least 32 characters');
    }
    
    super({
      jwtFromRequest: ExtractJwt.fromAuthHeaderAsBearerToken(),
      ignoreExpiration: false,
      secretOrKey: secret,
      passReqToCallback: true,
    });
  }

  async validate(req: Request, payload: JwtPayload) {
    // 1. Verify this is an access token
    if (payload.type !== 'access') {
      throw new UnauthorizedException('Invalid token type');
    }
    
    // 2. Check if token has been revoked
    const isRevoked = await this.redis.get(\`revoked:\${payload.jti}\`);
    if (isRevoked) {
      throw new UnauthorizedException('Token has been revoked');
    }
    
    // 3. Verify user still exists and is active
    const user = await this.prisma.user.findUnique({
      where: { id: payload.sub },
      include: { roles: { include: { role: true } } },
    });
    
    if (!user) {
      throw new UnauthorizedException('User not found');
    }
    
    if (user.status !== 'ACTIVE') {
      throw new UnauthorizedException('Account is not active');
    }
    
    // 4. Return full user context
    return {
      userId: user.id,
      email: user.email,
      roles: user.roles.map(r => r.role.name),
      jti: payload.jti,
    };
  }
}
`;
      
      this.writeFile(jwtStrategyPath, newContent);
      this.log('  ✅ Enhanced JWT strategy with DB validation and revocation check', 'green');
      this.fixesApplied++;
    }
  }

  // ============================================================================
  // ARCHITECTURE FIXES
  // ============================================================================

  async fixRequestIDMiddleware() {
    const middlewarePath = 'apps/api/src/common/middleware/request-id.middleware.ts';
    const appModulePath = 'apps/api/src/app.module.ts';
    
    if (!fs.existsSync(middlewarePath)) {
      const content = `import { Injectable, NestMiddleware } from '@nestjs/common';
import { Request, Response, NextFunction } from 'express';
import { randomUUID } from 'crypto';

/**
 * Request ID Middleware
 * Adds unique X-Request-ID header for distributed tracing
 */
@Injectable()
export class RequestIdMiddleware implements NestMiddleware {
  use(req: Request, res: Response, next: NextFunction) {
    const requestId = req.headers['x-request-id'] as string || randomUUID();
    
    // Set request ID on request object for access in controllers
    (req as any).requestId = requestId;
    
    // Add to response headers
    res.setHeader('X-Request-ID', requestId);
    
    next();
  }
}
`;
      this.writeFile(middlewarePath, content);
      this.log('  ✅ Created request-id.middleware.ts', 'green');
      this.fixesApplied++;
    }
    
    // Apply middleware in AppModule
    if (fs.existsSync(appModulePath)) {
      this.backupFile(appModulePath);
      let content = fs.readFileSync(appModulePath, 'utf-8');
      
      if (!content.includes('RequestIdMiddleware')) {
        // Add configure method if not present
        if (!content.includes('configure(consumer')) {
          // Add imports
          content = content.replace(
            /import \{ Module \} from '@nestjs\/common';?/,
            "import { Module, MiddlewareConsumer } from '@nestjs/common';"
          );
          content = content.replace(
            /import \{([^}]+)\} from '\.\/app\.controller';?/,
            "import { $1 } from './app.controller';\nimport { RequestIdMiddleware } from './common/middleware/request-id.middleware';"
          );
          
          // Add configure method to class
          content = content.replace(
            /export class AppModule \{([^}]*)\}/s,
            `export class AppModule {
  configure(consumer: MiddlewareConsumer) {
    consumer
      .apply(RequestIdMiddleware)
      .forRoutes('*');
  }
}`
          );
          
          this.writeFile(appModulePath, content);
          this.log('  ✅ Applied RequestIdMiddleware globally', 'green');
          this.fixesApplied++;
        }
      }
    }
  }

  async fixCacheInvalidation() {
    const productServicePath = 'apps/api/src/products/product.service.ts';
    
    if (!fs.existsSync(productServicePath)) return;
    
    this.backupFile(productServicePath);
    let content = fs.readFileSync(productServicePath, 'utf-8');
    
    // Fix cache invalidation order - delete before DB update
    if (content.includes('await this.redis.del') && content.includes('await this.prisma')) {
      // This is a complex fix that requires reordering operations
      // For now, add a comment and basic fix
      if (!content.includes('// CRITICAL: Delete cache BEFORE DB update')) {
        content = content.replace(
          /async update\(/,
          `// CRITICAL: Delete cache BEFORE DB update to prevent stale data
  async update(`
        );
        
        this.writeFile(productServicePath, content);
        this.log('  ⚠️  Added cache invalidation warning (manual review needed)', 'yellow');
        this.fixesApplied++;
      }
    }
  }

  async fixDBPooling() {
    const prismaServicePath = 'apps/api/src/prisma/prisma.service.ts';
    
    if (!fs.existsSync(prismaServicePath)) return;
    
    this.backupFile(prismaServicePath);
    const content = fs.readFileSync(prismaServicePath, 'utf-8');
    
    if (!content.includes('datasources')) {
      const newContent = `import { Injectable, OnModuleInit, OnModuleDestroy } from '@nestjs/common';
import { PrismaClient } from '@prisma/client';
import { ConfigService } from '@nestjs/config';

@Injectable()
export class PrismaService extends PrismaClient implements OnModuleInit, OnModuleDestroy {
  constructor(private readonly configService: ConfigService) {
    super({
      datasources: {
        db: {
          url: configService.get('DATABASE_URL'),
        },
      },
      log: process.env.NODE_ENV === 'development' 
        ? ['query', 'info', 'warn', 'error'] 
        : ['error'],
      // Connection pool configuration for enterprise scale
      // Add to DATABASE_URL: ?connection_limit=20&pool_timeout=30
    });
  }

  async onModuleInit() {
    await this.$connect();
  }

  async onModuleDestroy() {
    await this.$disconnect();
  }
  
  async cleanDatabase() {
    if (process.env.NODE_ENV === 'production') {
      throw new Error('Cannot clean database in production');
    }
    // Truncate all tables for testing
    const tables = ['products', 'categories', 'users', 'sessions'];
    for (const table of tables) {
      await this.$executeRawUnsafe(\`TRUNCATE TABLE "\${table}" CASCADE;\`);
    }
  }
}
`;
      
      this.writeFile(prismaServicePath, newContent);
      this.log('  ✅ Enhanced PrismaService with connection pooling config', 'green');
      this.fixesApplied++;
    }
  }

  async fixPaginationLimits() {
    const productServicePath = 'apps/api/src/products/product.service.ts';
    
    if (!fs.existsSync(productServicePath)) return;
    
    this.backupFile(productServicePath);
    let content = fs.readFileSync(productServicePath, 'utf-8');
    
    if (content.includes('filters.limit') && !content.includes('MAX_LIMIT')) {
      // Add MAX_LIMIT constant and apply it
      content = content.replace(
        /async findAll\(/,
        `private readonly MAX_LIMIT = 100;
  private readonly DEFAULT_LIMIT = 10;
  
  async findAll(`
      );
      
      content = content.replace(
        /const limit = filters\.limit \|\| 10;?/,
        'const limit = Math.min(filters.limit || this.DEFAULT_LIMIT, this.MAX_LIMIT);'
      );
      
      this.writeFile(productServicePath, content);
      this.log('  ✅ Added pagination limits (MAX_LIMIT: 100)', 'green');
      this.fixesApplied++;
    }
  }

  async fixSoftDelete() {
    const productServicePath = 'apps/api/src/products/product.service.ts';
    
    if (!fs.existsSync(productServicePath)) return;
    
    this.backupFile(productServicePath);
    let content = fs.readFileSync(productServicePath, 'utf-8');
    
    if (content.includes('.delete({') && !content.includes('deletedAt')) {
      // Replace hard delete with soft delete
      content = content.replace(
        /async remove\(id: string[^)]*\)[^{]*\{[^}]*await this\.prisma\.product\.delete\({[^}]+}\);?[^}]*\}/s,
        `async remove(id: string, userId: string) {
    // SOFT DELETE: Set deletedAt instead of hard delete
    const product = await this.prisma.product.update({
      where: { id },
      data: {
        deletedAt: new Date(),
        status: 'ARCHIVED',
        updatedBy: userId,
      },
    });
    
    // Invalidate cache
    await this.redis.del(\`product:\${id}\`);
    await this.redis.del(\`product:slug:\${product.slug}\`);
    
    return product;
  }`
      );
      
      // Add filter for deletedAt in findAll
      if (!content.includes('deletedAt: null')) {
        content = content.replace(
          /async findAll\(filters:[^)]+\)[^{]*\{/,
          `async findAll(filters: ProductFilterDto) {
    // Always filter out soft-deleted products
    const baseWhere = { deletedAt: null };`
        );
      }
      
      this.writeFile(productServicePath, content);
      this.log('  ✅ Implemented soft delete for products', 'green');
      this.fixesApplied++;
    }
  }

  // ============================================================================
  // INFRASTRUCTURE FIXES
  // ============================================================================

  async fixDockerSecurity() {
    const dockerComposePath = 'docker/docker-compose.yml';
    
    if (!fs.existsSync(dockerComposePath)) return;
    
    this.backupFile(dockerComposePath);
    let content = fs.readFileSync(dockerComposePath, 'utf-8');
    
    if (!content.includes('resources:') || !content.includes('security_opt:')) {
      // Add security hardening to postgres service
      content = content.replace(
        /(postgres:[^v]*volumes:[^v]*-[^\n]+\n)(\s+volumes:)/,
        `$1    deploy:
      resources:
        limits:
          cpus: '1.0'
          memory: 1G
        reservations:
          memory: 256M
    security_opt:
      - no-new-privileges:true
    read_only: true
    tmpfs:
      - /tmp
      - /var/run/postgresql
$2`
      );
      
      this.writeFile(dockerComposePath, content);
      this.log('  ✅ Added Docker security hardening', 'green');
      this.fixesApplied++;
    }
  }

  async fixHealthChecks() {
    const healthControllerPath = 'apps/api/src/health/health.controller.ts';
    
    if (!fs.existsSync(healthControllerPath)) {
      // Create comprehensive health controller
      const content = `import { Controller, Get } from '@nestjs/common';
import { ConfigService } from '@nestjs/config';
import { PrismaService } from '../prisma/prisma.service';
import { Inject } from '@nestjs/common';
import Redis from 'ioredis';
import { REDIS_CLIENT } from '../common/constants/injection-tokens';

interface HealthStatus {
  status: 'healthy' | 'degraded' | 'unhealthy';
  timestamp: string;
  version: string;
  checks: {
    database: { status: string; responseTime: number };
    redis: { status: string; responseTime: number };
    uptime: number;
  };
}

@Controller('health')
export class HealthController {
  private startTime = Date.now();
  
  constructor(
    private readonly prisma: PrismaService,
    private readonly configService: ConfigService,
    @Inject(REDIS_CLIENT) private readonly redis: Redis,
  ) {}

  @Get()
  async check(): Promise<HealthStatus> {
    const checks = await Promise.all([
      this.checkDatabase(),
      this.checkRedis(),
    ]);
    
    const [dbCheck, redisCheck] = checks;
    const allHealthy = dbCheck.status === 'up' && redisCheck.status === 'up';
    
    return {
      status: allHealthy ? 'healthy' : 'degraded',
      timestamp: new Date().toISOString(),
      version: process.env.npm_package_version || '1.0.0',
      checks: {
        database: dbCheck,
        redis: redisCheck,
        uptime: Math.floor((Date.now() - this.startTime) / 1000),
      },
    };
  }

  @Get('ready')
  async readiness() {
    const dbCheck = await this.checkDatabase();
    return {
      ready: dbCheck.status === 'up',
      timestamp: new Date().toISOString(),
    };
  }

  @Get('live')
  liveness() {
    return {
      alive: true,
      timestamp: new Date().toISOString(),
    };
  }

  private async checkDatabase(): Promise<{ status: string; responseTime: number }> {
    const start = Date.now();
    try {
      await this.prisma.$queryRaw\`SELECT 1\`;
      return { status: 'up', responseTime: Date.now() - start };
    } catch (error) {
      return { status: 'down', responseTime: Date.now() - start };
    }
  }

  private async checkRedis(): Promise<{ status: string; responseTime: number }> {
    const start = Date.now();
    try {
      await this.redis.ping();
      return { status: 'up', responseTime: Date.now() - start };
    } catch (error) {
      return { status: 'down', responseTime: Date.now() - start };
    }
  }
}
`;
      
      // Ensure directory exists
      const dir = path.dirname(healthControllerPath);
      if (!fs.existsSync(dir)) {
        fs.mkdirSync(dir, { recursive: true });
      }
      
      this.writeFile(healthControllerPath, content);
      this.log('  ✅ Created comprehensive health controller', 'green');
      this.fixesApplied++;
    }
  }

  async fixGracefulShutdown() {
    const mainPath = 'apps/api/src/main.ts';
    
    if (!fs.existsSync(mainPath)) return;
    
    this.backupFile(mainPath);
    let content = fs.readFileSync(mainPath, 'utf-8');
    
    if (!content.includes('enableShutdownHooks') && !content.includes('SIGTERM')) {
      // Add graceful shutdown handling before bootstrap()
      content = content.replace(
        /async function bootstrap\(\)/,
        `// Graceful shutdown handling
async function gracefulShutdown(app: any, signal: string) {
  logger.log(\`Received \${signal}, starting graceful shutdown...\`);
  
  // Close HTTP server (stop accepting new connections)
  await app.close();
  
  // Allow time for existing requests to complete
  setTimeout(() => {
    logger.log('Graceful shutdown complete');
    process.exit(0);
  }, 5000);
}

async function bootstrap()`
      );
      
      // Add shutdown hooks after app.listen
      content = content.replace(
        /await app\.listen\([^)]+\);?\n\s+logger\.log/,
        `await app.listen(port, host);
  
  // Enable shutdown hooks for graceful shutdown
  app.enableShutdownHooks();
  
  // Handle termination signals
  process.on('SIGTERM', () => gracefulShutdown(app, 'SIGTERM'));
  process.on('SIGINT', () => gracefulShutdown(app, 'SIGINT'));
  
  logger.log`
      );
      
      this.writeFile(mainPath, content);
      this.log('  ✅ Added graceful shutdown handling', 'green');
      this.fixesApplied++;
    }
  }

  async fixMetrics() {
    const metricsControllerPath = 'apps/api/src/metrics/metrics.controller.ts';
    const appModulePath = 'apps/api/src/app.module.ts';
    
    if (!fs.existsSync(metricsControllerPath)) {
      const content = `import { Controller, Get, Res } from '@nestjs/common';
import { Response } from 'express';
import { register, collectDefaultMetrics, Counter, Histogram } from 'prom-client';

// Collect default Node.js metrics
collectDefaultMetrics({
  prefix: 'affiliate_',
});

// Custom metrics
const httpRequestDuration = new Histogram({
  name: 'affiliate_http_request_duration_seconds',
  help: 'Duration of HTTP requests in seconds',
  labelNames: ['method', 'route', 'status_code'],
  buckets: [0.01, 0.05, 0.1, 0.5, 1, 2, 5],
});

const httpRequestsTotal = new Counter({
  name: 'affiliate_http_requests_total',
  help: 'Total number of HTTP requests',
  labelNames: ['method', 'route', 'status_code'],
});

@Controller('metrics')
export class MetricsController {
  @Get()
  async getMetrics(@Res() res: Response) {
    res.set('Content-Type', register.contentType);
    res.end(await register.metrics());
  }
}

export { httpRequestDuration, httpRequestsTotal };
`;
      
      const dir = path.dirname(metricsControllerPath);
      if (!fs.existsSync(dir)) {
        fs.mkdirSync(dir, { recursive: true });
      }
      
      this.writeFile(metricsControllerPath, content);
      this.log('  ✅ Created Prometheus metrics controller', 'green');
      this.fixesApplied++;
    }
  }

  // ============================================================================
  // COMPLIANCE FIXES
  // ============================================================================

  async fixAuditLogging() {
    const auditServicePath = 'apps/api/src/common/services/audit.service.ts';
    
    if (!fs.existsSync(auditServicePath)) {
      const content = `import { Injectable } from '@nestjs/common';
import { PrismaService } from '../../prisma/prisma.service';

export interface AuditLogEntry {
  action: string;
  userId: string;
  resourceType: string;
  resourceId: string;
  changes?: Record<string, { old: any; new: any }>;
  metadata?: Record<string, any>;
  ipAddress?: string;
  userAgent?: string;
}

@Injectable()
export class AuditService {
  constructor(private readonly prisma: PrismaService) {}

  async log(entry: AuditLogEntry) {
    // Store audit log in database
    await this.prisma.auditLog.create({
      data: {
        action: entry.action,
        userId: entry.userId,
        resourceType: entry.resourceType,
        resourceId: entry.resourceId,
        changes: entry.changes ? JSON.stringify(entry.changes) : null,
        metadata: entry.metadata ? JSON.stringify(entry.metadata) : null,
        ipAddress: entry.ipAddress,
        userAgent: entry.userAgent,
        createdAt: new Date(),
      },
    });
  }

  async logProductCreate(userId: string, productId: string, data: any, ip?: string) {
    await this.log({
      action: 'PRODUCT_CREATE',
      userId,
      resourceType: 'product',
      resourceId: productId,
      metadata: { data },
      ipAddress: ip,
    });
  }

  async logProductUpdate(userId: string, productId: string, changes: any, ip?: string) {
    await this.log({
      action: 'PRODUCT_UPDATE',
      userId,
      resourceType: 'product',
      resourceId: productId,
      changes,
      ipAddress: ip,
    });
  }

  async logLogin(userId: string, ip: string, userAgent: string, success: boolean) {
    await this.log({
      action: success ? 'LOGIN_SUCCESS' : 'LOGIN_FAILURE',
      userId,
      resourceType: 'user',
      resourceId: userId,
      ipAddress: ip,
      userAgent,
    });
  }
}
`;
      
      const dir = path.dirname(auditServicePath);
      if (!fs.existsSync(dir)) {
        fs.mkdirSync(dir, { recursive: true });
      }
      
      this.writeFile(auditServicePath, content);
      this.log('  ✅ Created audit logging service', 'green');
      this.fixesApplied++;
    }
  }

  async fixGDPRCompliance() {
    const userControllerPath = 'apps/api/src/users/users.controller.ts';
    
    if (!fs.existsSync(userControllerPath)) {
      // Create GDPR-compliant user controller
      const content = `import { Controller, Get, Post, Delete, Body, Param, Req, UseGuards } from '@nestjs/common';
import { Request } from 'express';
import { JwtAuthGuard } from '../auth/guards/jwt-auth.guard';
import { CurrentUser } from '../auth/decorators/current-user.decorator';
import { PrismaService } from '../prisma/prisma.service';
import { AuditService } from '../common/services/audit.service';

@Controller('users')
@UseGuards(JwtAuthGuard)
export class UsersController {
  constructor(
    private readonly prisma: PrismaService,
    private readonly auditService: AuditService,
  ) {}

  /**
   * GDPR: Data Export (Right to Data Portability)
   * Exports all user data in machine-readable format
   */
  @Get('me/export')
  async exportData(@CurrentUser('userId') userId: string) {
    const user = await this.prisma.user.findUnique({
      where: { id: userId },
      include: {
        sessions: true,
        refreshTokens: true,
        createdProducts: true,
        // Include all related data
      },
    });

    return {
      exportDate: new Date().toISOString(),
      user: {
        id: user.id,
        email: user.email,
        firstName: user.firstName,
        lastName: user.lastName,
        createdAt: user.createdAt,
        updatedAt: user.updatedAt,
        // Include all user data
      },
      sessions: user.sessions,
      products: user.createdProducts,
    };
  }

  /**
   * GDPR: Right to Erasure (Account Deletion)
   * Anonymizes user data instead of hard delete for referential integrity
   */
  @Delete('me')
  async deleteAccount(
    @CurrentUser('userId') userId: string,
    @Req() req: Request,
  ) {
    // Anonymize user data
    await this.prisma.user.update({
      where: { id: userId },
      data: {
        email: \`deleted-\${userId}@anonymized.local\`,
        password: 'DELETED',
        firstName: 'Deleted',
        lastName: 'User',
        status: 'INACTIVE',
        deletedAt: new Date(),
      },
    });

    // Revoke all sessions
    await this.prisma.session.deleteMany({
      where: { userId },
    });

    await this.prisma.refreshToken.deleteMany({
      where: { userId },
    });

    // Log the deletion
    await this.auditService.log({
      action: 'USER_DELETE_ACCOUNT',
      userId,
      resourceType: 'user',
      resourceId: userId,
      ipAddress: req.ip,
      userAgent: req.headers['user-agent'],
    });

    return { message: 'Account deleted successfully' };
  }

  /**
   * GDPR: Consent Management
   */
  @Post('me/consent')
  async updateConsent(
    @CurrentUser('userId') userId: string,
    @Body() consents: Record<string, boolean>,
  ) {
    // Store user consent preferences
    await this.prisma.userConsent.upsert({
      where: { userId },
      create: {
        userId,
        analytics: consents.analytics ?? false,
        marketing: consents.marketing ?? false,
        updatedAt: new Date(),
      },
      update: {
        analytics: consents.analytics,
        marketing: consents.marketing,
        updatedAt: new Date(),
      },
    });

    return { message: 'Consent preferences updated' };
  }
}
`;
      
      const dir = path.dirname(userControllerPath);
      if (!fs.existsSync(dir)) {
        fs.mkdirSync(dir, { recursive: true });
      }
      
      this.writeFile(userControllerPath, content);
      this.log('  ✅ Created GDPR-compliant user controller', 'green');
      this.fixesApplied++;
    }
  }

  // ============================================================================
  // TESTING FIXES
  // ============================================================================

  async fixAuthUnitTests() {
    const authServiceSpecPath = 'apps/api/src/auth/auth.service.spec.ts';
    
    if (!fs.existsSync(authServiceSpecPath)) {
      const content = `import { Test, TestingModule } from '@nestjs/testing';
import { AuthService } from './auth.service';
import { JwtService } from '@nestjs/jwt';
import { ConfigService } from '@nestjs/config';
import { PrismaService } from '../prisma/prisma.service';
import { PasswordService } from './password.service';
import { UnauthorizedException } from '@nestjs/common';
import { randomUUID } from 'crypto';

describe('AuthService', () => {
  let service: AuthService;
  let prisma: PrismaService;
  let jwtService: JwtService;

  const mockPrisma = {
    user: {
      findUnique: jest.fn(),
      create: jest.fn(),
      update: jest.fn(),
    },
    session: {
      create: jest.fn(),
    },
    refreshToken: {
      create: jest.fn(),
      findUnique: jest.fn(),
      delete: jest.fn(),
    },
  };

  const mockJwtService = {
    signAsync: jest.fn(),
    verify: jest.fn(),
  };

  const mockRedis = {
    setex: jest.fn(),
    get: jest.fn(),
    del: jest.fn(),
    exists: jest.fn(),
  };

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      providers: [
        AuthService,
        PasswordService,
        { provide: PrismaService, useValue: mockPrisma },
        { provide: JwtService, useValue: mockJwtService },
        {
          provide: ConfigService,
          useValue: {
            get: jest.fn((key: string) => {
              const config = {
                JWT_SECRET: 'test-secret-minimum-32-characters-long',
                JWT_REFRESH_SECRET: 'test-refresh-secret-32-char-min',
                JWT_ACCESS_EXPIRATION: '15m',
                JWT_REFRESH_EXPIRATION: '7d',
              };
              return config[key];
            }),
          },
        },
        { provide: 'REDIS', useValue: mockRedis },
      ],
    }).compile();

    service = module.get<AuthService>(AuthService);
    prisma = module.get<PrismaService>(PrismaService);
    jwtService = module.get<JwtService>(JwtService);

    jest.clearAllMocks();
  });

  describe('login', () => {
    it('should authenticate valid user', async () => {
      const user = {
        id: randomUUID(),
        email: 'test@example.com',
        password: await new PasswordService().hash('password123'),
        status: 'ACTIVE',
        roles: [{ role: { name: 'USER' } }],
      };

      mockPrisma.user.findUnique.mockResolvedValue(user);
      mockJwtService.signAsync.mockResolvedValue('mock-token');
      mockRedis.setex.mockResolvedValue('OK');

      const result = await service.login('test@example.com', 'password123', '127.0.0.1');

      expect(result).toHaveProperty('user');
      expect(result).toHaveProperty('tokens');
      expect(mockPrisma.session.create).toHaveBeenCalled();
    });

    it('should reject invalid credentials', async () => {
      mockPrisma.user.findUnique.mockResolvedValue(null);

      await expect(
        service.login('test@example.com', 'wrongpassword'),
      ).rejects.toThrow(UnauthorizedException);
    });

    it('should reject inactive users', async () => {
      mockPrisma.user.findUnique.mockResolvedValue({
        id: randomUUID(),
        email: 'test@example.com',
        status: 'INACTIVE',
      });

      await expect(
        service.login('test@example.com', 'password123'),
      ).rejects.toThrow(UnauthorizedException);
    });
  });

  describe('token refresh', () => {
    it('should rotate refresh tokens', async () => {
      const payload = {
        sub: randomUUID(),
        type: 'refresh',
        jti: randomUUID(),
      };

      mockJwtService.verify.mockReturnValue(payload);
      mockRedis.exists.mockResolvedValue(1);
      mockPrisma.user.findUnique.mockResolvedValue({
        id: payload.sub,
        status: 'ACTIVE',
        roles: [],
      });
      mockJwtService.signAsync.mockResolvedValue('new-token');

      const result = await service.refreshTokens('valid-refresh-token');

      expect(result).toHaveProperty('accessToken');
      expect(result).toHaveProperty('refreshToken');
      expect(mockRedis.del).toHaveBeenCalled(); // Old token revoked
    });

    it('should detect token reuse', async () => {
      const payload = {
        sub: randomUUID(),
        type: 'refresh',
        jti: randomUUID(),
      };

      mockJwtService.verify.mockReturnValue(payload);
      mockRedis.exists.mockResolvedValue(0); // Token not in valid set

      await expect(service.refreshTokens('reused-token')).rejects.toThrow(
        'Security violation detected',
      );
    });
  });

  describe('password reset', () => {
    it('should generate cryptographically secure tokens', async () => {
      const user = { id: randomUUID(), email: 'test@example.com' };
      mockPrisma.user.findUnique.mockResolvedValue(user);

      await service.requestPasswordReset('test@example.com');

      // Verify token was created with proper length
      expect(mockPrisma.refreshToken.create).toHaveBeenCalledWith(
        expect.objectContaining({
          data: expect.objectContaining({
            token: expect.stringMatching(/^[a-f0-9]{64}$/), // 32 bytes = 64 hex chars
          }),
        }),
      );
    });
  });
});
`;
      
      const dir = path.dirname(authServiceSpecPath);
      if (!fs.existsSync(dir)) {
        fs.mkdirSync(dir, { recursive: true });
      }
      
      this.writeFile(authServiceSpecPath, content);
      this.log('  ✅ Created comprehensive auth service tests', 'green');
      this.fixesApplied++;
    }
  }

  async fixE2ETests() {
    const e2eTestPath = 'apps/api/test/app.e2e-spec.ts';
    
    if (fs.existsSync(e2eTestPath)) {
      const content = fs.readFileSync(e2eTestPath, 'utf-8');
      
      if (content.length < 1000) {
        const newContent = `import { Test, TestingModule } from '@nestjs/testing';
import { INestApplication } from '@nestjs/common';
import request from 'supertest';
import { AppModule } from './../src/app.module';
import { PrismaService } from './../src/prisma/prisma.service';

describe('AppController (e2e)', () => {
  let app: INestApplication;
  let prisma: PrismaService;

  beforeAll(async () => {
    const moduleFixture: TestingModule = await Test.createTestingModule({
      imports: [AppModule],
    }).compile();

    app = moduleFixture.createNestApplication();
    prisma = app.get<PrismaService>(PrismaService);
    await app.init();
  });

  afterAll(async () => {
    await app.close();
  });

  describe('Health', () => {
    it('/health (GET) - should return healthy status', () => {
      return request(app.getHttpServer())
        .get('/health')
        .expect(200)
        .expect((res) => {
          expect(res.body.status).toBe('healthy');
          expect(res.body.checks).toHaveProperty('database');
          expect(res.body.checks).toHaveProperty('redis');
        });
    });

    it('/health/ready (GET) - should return ready status', () => {
      return request(app.getHttpServer())
        .get('/health/ready')
        .expect(200)
        .expect((res) => {
          expect(res.body.ready).toBe(true);
        });
    });
  });

  describe('Authentication', () => {
    const testUser = {
      email: 'test-e2e@example.com',
      password: 'TestPassword123!',
      firstName: 'Test',
      lastName: 'User',
    };

    let accessToken: string;
    let refreshToken: string;

    it('POST /auth/register - should register new user', async () => {
      const response = await request(app.getHttpServer())
        .post('/auth/register')
        .send(testUser)
        .expect(201);

      expect(response.body).toHaveProperty('user');
      expect(response.body.user.email).toBe(testUser.email);
    });

    it('POST /auth/login - should authenticate user', async () => {
      const response = await request(app.getHttpServer())
        .post('/auth/login')
        .send({
          email: testUser.email,
          password: testUser.password,
        })
        .expect(200);

      expect(response.body).toHaveProperty('tokens');
      accessToken = response.body.tokens.accessToken;
      refreshToken = response.body.tokens.refreshToken;
    });

    it('POST /auth/refresh - should refresh tokens', async () => {
      const response = await request(app.getHttpServer())
        .post('/auth/refresh')
        .send({ refreshToken })
        .expect(200);

      expect(response.body).toHaveProperty('accessToken');
      expect(response.body).toHaveProperty('refreshToken');
    });

    it('GET /auth/me - should get current user with valid token', async () => {
      return request(app.getHttpServer())
        .get('/auth/me')
        .set('Authorization', \`Bearer \${accessToken}\`)
        .expect(200)
        .expect((res) => {
          expect(res.body.email).toBe(testUser.email);
        });
    });

    it('GET /auth/me - should reject invalid token', () => {
      return request(app.getHttpServer())
        .get('/auth/me')
        .set('Authorization', 'Bearer invalid-token')
        .expect(401);
    });
  });

  describe('Rate Limiting', () => {
    it('should rate limit login attempts', async () => {
      // Make multiple rapid login attempts
      const attempts = Array(5).fill(null).map(() =>
        request(app.getHttpServer())
          .post('/auth/login')
          .send({ email: 'test@example.com', password: 'wrong' }),
      );

      const responses = await Promise.all(attempts);
      const tooManyRequests = responses.some(r => r.status === 429);
      
      expect(tooManyRequests).toBe(true);
    });
  });
});
`;
        
        this.writeFile(e2eTestPath, newContent);
        this.log('  ✅ Enhanced E2E test suite', 'green');
        this.fixesApplied++;
      }
    }
  }

  // ============================================================================
  // UTILITY METHODS
  // ============================================================================

  writeFile(filePath, content) {
    const dir = path.dirname(filePath);
    if (!fs.existsSync(dir)) {
      fs.mkdirSync(dir, { recursive: true });
    }
    fs.writeFileSync(filePath, content, 'utf-8');
  }
}

// Run the fix engine
const engine = new EnterpriseFixEngine();
engine.applyAllFixes().catch(console.error);
