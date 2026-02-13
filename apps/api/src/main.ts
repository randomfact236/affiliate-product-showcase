import { NestFactory, HttpAdapterHost } from "@nestjs/core";
import { ValidationPipe, VersioningType } from "@nestjs/common";
import { ConfigService } from "@nestjs/config";
import { SwaggerModule, DocumentBuilder } from "@nestjs/swagger";
import { Logger } from "nestjs-pino";
import { AppModule } from "./app.module";
import { AllExceptionsFilter } from "./common/filters/all-exceptions.filter";
import { TransformInterceptor } from "./common/interceptors/transform.interceptor";
import { SanitizePipe } from "./common/pipes/sanitize.pipe";
import helmet from "helmet";
import { NestExpressApplication } from "@nestjs/platform-express";

async function bootstrap() {
  const app = await NestFactory.create<NestExpressApplication>(AppModule, {
    bufferLogs: true,
  });

  const configService = app.get(ConfigService);
  const logger = app.get(Logger);

  // Security Headers with strict configuration
  app.use(
    helmet({
      contentSecurityPolicy: {
        directives: {
          defaultSrc: ["'self'"],
          scriptSrc: ["'self'", "'unsafe-inline'"],
          styleSrc: ["'self'", "'unsafe-inline'"],
          imgSrc: ["'self'", "data:", "https:", "blob:"],
          connectSrc: ["'self'"],
          fontSrc: ["'self'"],
          objectSrc: ["'none'"],
          mediaSrc: ["'self'"],
          frameSrc: ["'none'"],
        },
      },
      crossOriginEmbedderPolicy: false,
      hsts: {
        maxAge: 31536000, // 1 year
        includeSubDomains: true,
        preload: true,
      },
      referrerPolicy: { policy: "strict-origin-when-cross-origin" },
      noSniff: true,
      xssFilter: true,
      hidePoweredBy: true,
    }),
  );

  // Use nestjs-pino logger
  app.useLogger(logger);

  app.setGlobalPrefix("api");
  app.enableVersioning({
    type: VersioningType.URI,
    defaultVersion: "1",
  });

  // Secure CORS Configuration - NO WILDCARD ALLOWED
  const allowedOrigins = configService.get<string[]>("app.allowedOrigins");
  const nodeEnv = configService.get("app.nodeEnv");

  app.enableCors({
    origin: (
      origin: string | undefined,
      callback: (err: Error | null, allow?: boolean) => void,
    ) => {
      // Allow requests with no origin (mobile apps, curl, etc.)
      if (!origin) {
        return callback(null, true);
      }

      // In development, be more lenient but still validate
      if (nodeEnv === "development" && origin.startsWith("http://localhost:")) {
        return callback(null, true);
      }

      // Check against allowed origins
      if (allowedOrigins && allowedOrigins.includes(origin)) {
        return callback(null, true);
      }

      logger.warn(`CORS blocked request from origin: ${origin}`);
      callback(new Error("Not allowed by CORS"));
    },
    credentials: true,
    methods: ["GET", "POST", "PUT", "PATCH", "DELETE", "OPTIONS"],
    allowedHeaders: [
      "Content-Type",
      "Authorization",
      "X-Requested-With",
      "X-Request-ID",
    ],
    maxAge: 86400, // 24 hours
  });

  // Validation pipe with strict DTOs
  app.useGlobalPipes(
    new ValidationPipe({
      whitelist: true, // Strip properties not in DTO
      forbidNonWhitelisted: true, // Throw error on unknown properties
      transform: true, // Auto-transform types
      transformOptions: {
        enableImplicitConversion: true,
      },
      disableErrorMessages: nodeEnv === "production", // Hide details in prod
    }),
  );

  // Global Pipes, Filters & Interceptors
  const httpAdapter = app.get(HttpAdapterHost);
  app.useGlobalFilters(new AllExceptionsFilter(httpAdapter));
  app.useGlobalInterceptors(new TransformInterceptor());
  app.useGlobalPipes(new SanitizePipe()); // XSS protection for input sanitization

  // Swagger documentation (development only)
  if (nodeEnv !== "production") {
    const swaggerConfig = new DocumentBuilder()
      .setTitle("Affiliate Platform API")
      .setDescription("Enterprise Affiliate Marketing Platform API")
      .setVersion("1.0.0")
      .addBearerAuth(
        {
          type: "http",
          scheme: "bearer",
          bearerFormat: "JWT",
          description: "Enter JWT token",
        },
        "JWT-auth",
      )
      .addTag("Auth", "Authentication endpoints")
      .addTag("Products", "Product management")
      .addTag("Categories", "Category taxonomy")
      .addTag("Analytics", "Analytics collection")
      .build();

    const document = SwaggerModule.createDocument(app as any, swaggerConfig);
    SwaggerModule.setup("api/docs", app as any, document, {
      swaggerOptions: {
        persistAuthorization: true,
        tagsSorter: "alpha",
        operationsSorter: "alpha",
      },
    });
  }

  // Graceful shutdown handling
  app.enableShutdownHooks();

  const port = configService.get<number>("app.port") || 3003;
  const host = configService.get("app.host") || "0.0.0.0";

  await app.listen(port, host);

  logger.log(`🚀 API Server running on http://${host}:${port}`);
  logger.log(`📚 API Docs available at http://${host}:${port}/api/docs`);
  logger.log(`🔒 Environment: ${nodeEnv}`);
  logger.log(`🌐 Allowed Origins: ${allowedOrigins?.join(", ")}`);

  // Handle graceful shutdown
  const signals = ["SIGTERM", "SIGINT"];
  signals.forEach((signal) => {
    process.on(signal, async () => {
      logger.log(`Received ${signal}, starting graceful shutdown...`);
      await app.close();
      logger.log("Graceful shutdown complete");
      process.exit(0);
    });
  });
}

bootstrap();
