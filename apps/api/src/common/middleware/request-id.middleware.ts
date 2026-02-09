import { Injectable, NestMiddleware } from "@nestjs/common";
import { Request, Response, NextFunction } from "express";
import { randomBytes } from "crypto";

// Extend Express Request type
declare global {
  namespace Express {
    interface Request {
      id: string;
      startTime: number;
    }
  }
}

@Injectable()
export class RequestIdMiddleware implements NestMiddleware {
  use(req: Request, res: Response, next: NextFunction) {
    // Generate or preserve request ID
    const requestId =
      (req.headers["x-request-id"] as string) ||
      (req.headers["x-correlation-id"] as string) ||
      this.generateRequestId();

    req.id = requestId;
    req.startTime = Date.now();

    // Set response headers for tracing
    res.setHeader("X-Request-ID", requestId);

    // Log request start
    const logger = (req as any).log || console;
    logger.info?.(
      {
        requestId,
        method: req.method,
        path: req.path,
        query: req.query,
        ip: req.ip,
        userAgent: req.headers["user-agent"],
      },
      "Request started",
    );

    // Log response completion
    res.on("finish", () => {
      const duration = Date.now() - req.startTime;
      logger.info?.(
        {
          requestId,
          method: req.method,
          path: req.path,
          statusCode: res.statusCode,
          duration,
        },
        "Request completed",
      );
    });

    next();
  }

  private generateRequestId(): string {
    // Generate 16 bytes of randomness (32 hex chars)
    return randomBytes(16).toString("hex");
  }
}
