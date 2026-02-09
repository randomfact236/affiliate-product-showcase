import { Injectable, UnauthorizedException, ConflictException, NotFoundException } from '@nestjs/common';
import { JwtService } from '@nestjs/jwt';
import { ConfigService } from '@nestjs/config';
import { randomBytes } from 'crypto';
import { PrismaService } from '../prisma/prisma.service';
import { PasswordService } from './password.service';
import { EmailService } from '../common/services/email.service';
import { RegisterDto, LoginDto } from './dto';

@Injectable()
export class AuthService {
  constructor(
    private prisma: PrismaService,
    private jwtService: JwtService,
    private passwordService: PasswordService,
    private emailService: EmailService,
    private configService: ConfigService,
  ) { }

  async register(dto: RegisterDto) {
    const existing = await this.prisma.user.findUnique({
      where: { email: dto.email },
    });

    if (existing) {
      throw new ConflictException('Email already registered');
    }

    const hashedPassword = await this.passwordService.hash(dto.password);

    const user = await this.prisma.user.create({
      data: {
        email: dto.email,
        password: hashedPassword,
        firstName: dto.firstName,
        lastName: dto.lastName,
        roles: {
          create: {
            role: {
              connectOrCreate: {
                where: { name: 'USER' },
                create: { name: 'USER', description: 'Default user role' },
              },
            },
          },
        },
      },
    });

    return this.generateTokens(user.id, user.email);
  }

  async login(dto: LoginDto) {
    // Optimization: Don't fetch the entire permission tree on login. 
    // Just get the user and password hash.
    const user = await this.prisma.user.findUnique({
      where: { email: dto.email },
    });

    if (!user) {
      throw new UnauthorizedException('Invalid credentials');
    }

    const isValid = await this.passwordService.verify(dto.password, user.password);

    if (!isValid) {
      throw new UnauthorizedException('Invalid credentials');
    }

    await this.prisma.user.update({
      where: { id: user.id },
      data: { lastLoginAt: new Date() },
    });

    return this.generateTokens(user.id, user.email);
  }

  async refreshToken(refreshToken: string) {
    if (!process.env.JWT_REFRESH_SECRET) {
      throw new Error('JWT_REFRESH_SECRET is not defined');
    }

    try {
      const payload = this.jwtService.verify(refreshToken, {
        secret: process.env.JWT_REFRESH_SECRET,
      });

      // DATABASE CHECK: Ensure token exists and is not revoked
      const storedToken = await this.prisma.refreshToken.findUnique({
        where: { token: refreshToken },
      });

      if (!storedToken) {
        // Potential Token Reuse Attack - In a real enterprise system, we might want to panic and revoke all user tokens here.
        throw new UnauthorizedException('Invalid refresh token (Reuse detected)');
      }

      if (storedToken.revokedAt || storedToken.expiresAt < new Date()) {
        throw new UnauthorizedException('Invalid or expired refresh token');
      }

      // Token Rotation: Revoke the used token
      await this.prisma.refreshToken.update({
        where: { id: storedToken.id },
        data: { revokedAt: new Date() }
      });

      return this.generateTokens(payload.sub, payload.email);
    } catch (e) {
      throw new UnauthorizedException('Invalid refresh token');
    }
  }

  async logout(userId: string) {
    // Invalidate all refresh tokens for the user
    await this.prisma.refreshToken.updateMany({
      where: { userId, revokedAt: null },
      data: { revokedAt: new Date() }
    });
    return { message: 'Logged out successfully' };
  }

  async forgotPassword(email: string) {
    const user = await this.prisma.user.findUnique({ where: { email } });
    
    // Security: Don't reveal if email exists
    if (!user) {
      return { 
        message: 'If your email is registered, you will receive a password reset link.' 
      };
    }

    // Generate cryptographically secure reset token
    const token = randomBytes(32).toString('hex');

    await this.prisma.passwordReset.create({
      data: {
        userId: user.id,
        token,
        expiresAt: new Date(Date.now() + 24 * 60 * 60 * 1000), // 24 hours
      },
    });

    await this.emailService.sendPasswordResetEmail(user.email, token);

    return { 
      message: 'If your email is registered, you will receive a password reset link.' 
    };
  }

  async resetPassword(token: string, newPassword: string) {
    const reset = await this.prisma.passwordReset.findUnique({
      where: { token },
      include: { user: true },
    });

    if (!reset || reset.expiresAt < new Date() || reset.usedAt) {
      throw new UnauthorizedException('Invalid or expired token');
    }

    const hashedPassword = await this.passwordService.hash(newPassword);

    await this.prisma.$transaction([
      this.prisma.user.update({
        where: { id: reset.userId },
        data: { password: hashedPassword },
      }),
      this.prisma.passwordReset.update({
        where: { id: reset.id },
        data: { usedAt: new Date() },
      }),
    ]);

    return { message: 'Password reset successful' };
  }

  async getProfile(userId: string) {
    const user = await this.prisma.user.findUnique({
      where: { id: userId },
      select: {
        id: true,
        email: true,
        firstName: true,
        lastName: true,
        status: true,
        emailVerified: true,
        createdAt: true,
        roles: {
          include: {
            role: {
              include: {
                permissions: true,
              },
            },
          },
        },
      },
    });

    if (!user) {
      throw new NotFoundException('User not found');
    }

    return user;
  }

  private async generateTokens(userId: string, email: string) {
    const jwtSecret = this.configService.get<string>('jwt.secret')!;
    const jwtRefreshSecret = this.configService.get<string>('jwt.refreshSecret')!;
    const expiresIn = this.configService.get<string>('jwt.expiresIn')! as `${number}${'s'|'m'|'h'|'d'}`;
    const refreshExpiresIn = this.configService.get<string>('jwt.refreshExpiresIn')! as `${number}${'s'|'m'|'h'|'d'}`;
    const issuer = this.configService.get<string>('jwt.issuer');
    const audience = this.configService.get<string>('jwt.audience');

    const payload = { sub: userId, email };

    const accessToken = this.jwtService.sign(payload, {
      secret: jwtSecret,
      expiresIn,
      issuer,
      audience,
    });

    const refreshToken = this.jwtService.sign(payload, {
      secret: jwtRefreshSecret,
      expiresIn: refreshExpiresIn,
      issuer,
      audience,
    });

    // Calculate expiration date from refreshExpiresIn
    const expiresInMs = this.parseExpirationToMs(refreshExpiresIn);

    // Save refresh token to DB
    await this.prisma.refreshToken.create({
      data: {
        userId,
        token: refreshToken,
        expiresAt: new Date(Date.now() + expiresInMs),
      }
    });

    return { accessToken, refreshToken };
  }

  private parseExpirationToMs(expiration: string): number {
    const match = expiration.match(/^(\d+)([smhd])$/);
    if (!match) {
      // Default to 7 days if parsing fails
      return 7 * 24 * 60 * 60 * 1000;
    }
    
    const value = parseInt(match[1], 10);
    const unit = match[2];
    
    const multipliers: Record<string, number> = {
      s: 1000,
      m: 60 * 1000,
      h: 60 * 60 * 1000,
      d: 24 * 60 * 60 * 1000,
    };
    
    return value * multipliers[unit];
  }
}
