import { Module, NestModule, MiddlewareConsumer } from "@nestjs/common";
import { ConfigModule } from "@nestjs/config";
import { LoggerModule } from "nestjs-pino";
import { ThrottlerModule, ThrottlerGuard } from "@nestjs/throttler";
import { APP_GUARD } from "@nestjs/core";
import { AppController } from "./app.controller";
import { AppService } from "./app.service";
import { jwtConfig, appConfig } from "./config";
import { PrismaModule } from "./prisma/prisma.module";
import { AuthModule } from "./auth/auth.module";
import { ProductModule } from "./products/product.module";
import { CategoryModule } from "./categories/category.module";
import { TagModule } from "./tags/tag.module";
import { AttributeModule } from "./attributes/attribute.module";
import { MediaModule } from "./media/media.module";
import { RedisModule } from "./common/modules/redis.module";
import { QueueModule } from "./common/modules/queue.module";
import { HealthModule } from "./health/health.module";
import { RequestIdMiddleware } from "./common/middleware";

@Module({
  imports: [
    // Configuration with validation
    ConfigModule.forRoot({
      isGlobal: true,
      load: [jwtConfig, appConfig],
      cache: true,
    }),

    // Logging
    LoggerModule.forRoot({
      pinoHttp: {
        transport:
          process.env.NODE_ENV !== "production"
            ? { target: "pino-pretty" }
            : undefined,
        autoLogging: true,
        genReqId: () => {
          // Generate unique request ID for tracing using cryptographically secure method
          const { randomBytes } = require('crypto');
          return `${Date.now()}-${randomBytes(8).toString('hex')}`;
        },
      },
    }),

    // Rate limiting with tiered approach
    ThrottlerModule.forRoot({
      throttlers: [
        {
          name: "default",
          ttl: 60000, // 1 minute
          limit: 100, // 100 requests per minute for general endpoints
        },
        {
          name: "strict",
          ttl: 60000, // 1 minute
          limit: 10, // 10 requests per minute for sensitive operations
        },
      ],
    }),

    // Feature modules
    PrismaModule,
    AuthModule,
    ProductModule,
    CategoryModule,
    TagModule,
    AttributeModule,
    MediaModule,
    RedisModule,
    QueueModule,
    HealthModule,
  ],
  controllers: [AppController],
  providers: [
    AppService,
    {
      provide: APP_GUARD,
      useClass: ThrottlerGuard,
    },
  ],
})
export class AppModule implements NestModule {
  configure(consumer: MiddlewareConsumer) {
    // Apply request ID middleware to all routes
    consumer.apply(RequestIdMiddleware).forRoutes("*");
  }
}
