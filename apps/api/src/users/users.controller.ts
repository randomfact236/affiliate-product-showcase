import {
  Controller,
  Get,
  Post,
  Delete,
  Body,
  Param,
  Req,
  UseGuards,
} from "@nestjs/common";
import { Request } from "express";
import { JwtAuthGuard } from "../auth/guards/jwt-auth.guard";
import { CurrentUser } from "../auth/decorators/current-user.decorator";
import { PrismaService } from "../prisma/prisma.service";
import { AuditService } from "../common/services/audit.service";

@Controller("users")
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
  @Get("me/export")
  async exportData(@CurrentUser("userId") userId: string) {
    const user = await this.prisma.user.findUnique({
      where: { id: userId },
      include: {
        sessions: true,
        refreshTokens: true,
        createdProducts: true,
        // Include all related data
      },
    });

    if (!user) {
      throw new Error("User not found");
    }

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
  @Delete("me")
  async deleteAccount(
    @CurrentUser("userId") userId: string,
    @Req() req: Request,
  ) {
    // Anonymize user data
    await this.prisma.user.update({
      where: { id: userId },
      data: {
        email: `deleted-${userId}@anonymized.local`,
        password: "DELETED",
        firstName: "Deleted",
        lastName: "User",
        status: "INACTIVE",
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
      action: "USER_DELETE_ACCOUNT",
      userId,
      resourceType: "user",
      resourceId: userId,
      ipAddress: req.ip,
      userAgent: req.headers["user-agent"],
    });

    return { message: "Account deleted successfully" };
  }

  /**
   * GDPR: Consent Management
   */
  @Post("me/consent")
  async updateConsent(
    @CurrentUser("userId") userId: string,
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

    return { message: "Consent preferences updated" };
  }
}
