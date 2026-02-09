import { Controller, Get, Logger } from "@nestjs/common";
import { ApiTags, ApiOperation } from "@nestjs/swagger";
import { PrismaService } from "../prisma/prisma.service";
import { REDIS_CLIENT } from "../common/constants/injection-tokens";
import { Inject } from "@nestjs/common";
import type { Redis } from "ioredis";

interface HealthStatus {
  status: "healthy" | "unhealthy" | "degraded";
  timestamp: string;
  version: string;
  uptime: number;
  checks: {
    database: { status: "up" | "down"; responseTime: number };
    redis: { status: "up" | "down"; responseTime: number };
  };
}

@ApiTags("Health")
@Controller("health")
export class HealthController {
  private readonly logger = new Logger(HealthController.name);
  private readonly startTime = Date.now();

  constructor(
    private readonly prisma: PrismaService,
    @Inject(REDIS_CLIENT) private readonly redis: Redis,
  ) {}

  @Get()
  @ApiOperation({ summary: "Health check endpoint" })
  async check(): Promise<HealthStatus> {
    const checks = await Promise.all([this.checkDatabase(), this.checkRedis()]);

    const [database, redis] = checks;

    const allUp = database.status === "up" && redis.status === "up";
    const anyDown = database.status === "down" || redis.status === "down";

    const status: HealthStatus["status"] = anyDown
      ? "unhealthy"
      : allUp
        ? "healthy"
        : "degraded";

    const healthStatus: HealthStatus = {
      status,
      timestamp: new Date().toISOString(),
      version: process.env.npm_package_version || "1.0.0",
      uptime: Date.now() - this.startTime,
      checks: {
        database,
        redis,
      },
    };

    if (status !== "healthy") {
      this.logger.warn(
        `Health check degraded: ${JSON.stringify(healthStatus)}`,
      );
    }

    return healthStatus;
  }

  @Get("ready")
  @ApiOperation({ summary: "Readiness probe" })
  async readiness() {
    const db = await this.checkDatabase();

    if (db.status !== "up") {
      return {
        status: "not ready",
        reason: "Database unavailable",
      };
    }

    return {
      status: "ready",
      timestamp: new Date().toISOString(),
    };
  }

  @Get("live")
  @ApiOperation({ summary: "Liveness probe" })
  liveness() {
    return {
      status: "alive",
      timestamp: new Date().toISOString(),
      uptime: Date.now() - this.startTime,
    };
  }

  private async checkDatabase(): Promise<{
    status: "up" | "down";
    responseTime: number;
  }> {
    const start = Date.now();
    try {
      await this.prisma.$queryRaw`SELECT 1`;
      return {
        status: "up",
        responseTime: Date.now() - start,
      };
    } catch (error) {
      this.logger.error(
        "Database health check failed",
        (error as Error).message,
      );
      return {
        status: "down",
        responseTime: Date.now() - start,
      };
    }
  }

  private async checkRedis(): Promise<{
    status: "up" | "down";
    responseTime: number;
  }> {
    const start = Date.now();
    try {
      await this.redis.ping();
      return {
        status: "up",
        responseTime: Date.now() - start,
      };
    } catch (error) {
      this.logger.error("Redis health check failed", (error as Error).message);
      return {
        status: "down",
        responseTime: Date.now() - start,
      };
    }
  }
}
