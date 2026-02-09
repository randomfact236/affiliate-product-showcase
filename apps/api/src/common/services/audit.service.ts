import { Injectable, Logger } from "@nestjs/common";
import { PrismaService } from "../../prisma/prisma.service";

export interface AuditLogEntry {
  action: string;
  userId?: string;
  resourceType: string;
  resourceId?: string;
  changes?: Record<string, { old: unknown; new: unknown }>;
  metadata?: Record<string, unknown>;
  ipAddress?: string;
  userAgent?: string;
}

@Injectable()
export class AuditService {
  private readonly logger = new Logger("Audit");

  constructor(private readonly prisma: PrismaService) {}

  async log(entry: AuditLogEntry): Promise<void> {
    const timestamp = new Date().toISOString();

    // Log to application logs (structured)
    this.logger.log({
      ...entry,
      timestamp,
      type: "AUDIT",
    });

    // In production, we would stream these to:
    // - Dedicated audit table
    // - Immutable WORM storage
    // - SIEM
    // For now, structured logging to stdout is sufficient as our centralized logging
    // infrastructure (e.g., Datadog/Splunk) will ingest this.
    this.logger.log(`AUDIT_EVENT: ${JSON.stringify(entry)}`);
  }

  async logAuth(
    action:
      | "LOGIN"
      | "LOGOUT"
      | "REGISTER"
      | "PASSWORD_RESET"
      | "TOKEN_REFRESH",
    userId: string,
    metadata?: Record<string, unknown>,
    req?: { ip?: string; headers?: { "user-agent"?: string } },
  ): Promise<void> {
    await this.log({
      action: `AUTH_${action}`,
      userId,
      resourceType: "auth",
      metadata,
      ipAddress: req?.ip,
      userAgent: req?.headers?.["user-agent"],
    });
  }

  async logDataChange(
    action: "CREATE" | "UPDATE" | "DELETE",
    resourceType: string,
    resourceId: string,
    userId: string,
    changes: Record<string, { old: unknown; new: unknown }>,
    req?: { ip?: string; headers?: { "user-agent"?: string } },
  ): Promise<void> {
    await this.log({
      action: `DATA_${action}`,
      userId,
      resourceType,
      resourceId,
      changes,
      ipAddress: req?.ip,
      userAgent: req?.headers?.["user-agent"],
    });
  }
}
