# Phase 2: Backend Core - Auth & Identity

**Duration**: 2 weeks  
**Goal**: Secure authentication and user management API  
**Prerequisites**: Phase 1 complete

---

## Week 1: Database & Auth Foundation

### Day 1-2: Database Schema Design

#### Tasks
- [ ] Design user entity schema
- [ ] Design role/permission schema (RBAC)
- [ ] Design session/token schema
- [ ] Create Prisma schema

#### prisma/schema.prisma
```prisma
generator client {
  provider = "prisma-client-js"
}

datasource db {
  provider = "postgresql"
  url      = env("DATABASE_URL")
}

// User entity
model User {
  id        String   @id @default(cuid())
  email     String   @unique
  password  String   // hashed with bcrypt
  firstName String?
  lastName  String?
  avatar    String?
  
  // Status
  status    UserStatus @default(ACTIVE)
  emailVerified Boolean @default(false)
  
  // Relations
  roles     UserRole[]
  sessions  Session[]
  refreshTokens RefreshToken[]
  passwordResets PasswordReset[]
  
  // Timestamps
  createdAt DateTime @default(now())
  updatedAt DateTime @updatedAt
  lastLoginAt DateTime?
  
  @@index([email])
  @@index([status])
  @@map("users")
}

model Role {
  id          String @id @default(cuid())
  name        String @unique
  description String?
  
  // Relations
  permissions Permission[]
  users       UserRole[]
  
  @@map("roles")
}

model Permission {
  id          String @id @default(cuid())
  resource    String // e.g., "product", "user"
  action      String // e.g., "create", "read", "update", "delete"
  description String?
  
  // Relations
  roles Role[]
  
  @@unique([resource, action])
  @@map("permissions")
}

model UserRole {
  userId String
  roleId String
  
  user User @relation(fields: [userId], references: [id], onDelete: Cascade)
  role Role @relation(fields: [roleId], references: [id], onDelete: Cascade)
  
  @@id([userId, roleId])
  @@map("user_roles")
}

model Session {
  id        String   @id @default(cuid())
  userId    String
  token     String   @unique
  
  // Metadata
  ipAddress String?
  userAgent String?
  
  // Expiration
  expiresAt DateTime
  createdAt DateTime @default(now())
  
  user User @relation(fields: [userId], references: [id], onDelete: Cascade)
  
  @@index([token])
  @@index([userId])
  @@index([expiresAt])
  @@map("sessions")
}

model RefreshToken {
  id        String   @id @default(cuid())
  userId    String
  token     String   @unique
  expiresAt DateTime
  createdAt DateTime @default(now())
  revokedAt DateTime?
  
  user User @relation(fields: [userId], references: [id], onDelete: Cascade)
  
  @@index([token])
  @@map("refresh_tokens")
}

model PasswordReset {
  id        String   @id @default(cuid())
  userId    String
  token     String   @unique
  expiresAt DateTime
  usedAt    DateTime?
  createdAt DateTime @default(now())
  
  user User @relation(fields: [userId], references: [id], onDelete: Cascade)
  
  @@index([token])
  @@map("password_resets")
}

enum UserStatus {
  ACTIVE
  INACTIVE
  SUSPENDED
  PENDING_VERIFICATION
}
```

### Day 3: NestJS Project Structure

#### Tasks
- [ ] Initialize NestJS app in `apps/api`
- [ ] Configure Prisma module
- [ ] Set up configuration module
- [ ] Set up validation (class-validator)

#### apps/api/src/main.ts
```typescript
import { NestFactory } from '@nestjs/core';
import { ValidationPipe } from '@nestjs/common';
import { SwaggerModule, DocumentBuilder } from '@nestjs/swagger';
import { AppModule } from './app.module';

async function bootstrap(): Promise<void> {
  const app = await NestFactory.create(AppModule);
  
  // Global validation
  app.useGlobalPipe(
    new ValidationPipe({
      whitelist: true,
      forbidNonWhitelisted: true,
      transform: true,
    }),
  );
  
  // API prefix
  app.setGlobalPrefix('api/v1');
  
  // Swagger documentation
  const config = new DocumentBuilder()
    .setTitle('Affiliate Platform API')
    .setVersion('1.0')
    .addBearerAuth()
    .build();
  const document = SwaggerModule.createDocument(app, config);
  SwaggerModule.setup('api/docs', app, document);
  
  await app.listen(3001);
}
bootstrap();
```

### Day 4-5: Auth Module Implementation

#### Tasks
- [ ] Create AuthModule structure
- [ ] Implement password hashing service
- [ ] Implement JWT strategy
- [ ] Implement local strategy

#### apps/api/src/auth/auth.service.ts
```typescript
import { Injectable, UnauthorizedException, ConflictException } from '@nestjs/common';
import { JwtService } from '@nestjs/jwt';
import { PrismaService } from '../prisma/prisma.service';
import { PasswordService } from './password.service';
import { RegisterDto, LoginDto } from './dto';

@Injectable()
export class AuthService {
  constructor(
    private prisma: PrismaService,
    private jwtService: JwtService,
    private passwordService: PasswordService,
  ) {}

  async register(dto: RegisterDto): Promise<{ accessToken: string; refreshToken: string }> {
    // Check if email exists
    const existing = await this.prisma.user.findUnique({
      where: { email: dto.email },
    });
    
    if (existing) {
      throw new ConflictException('Email already registered');
    }
    
    // Hash password
    const hashedPassword = await this.passwordService.hash(dto.password);
    
    // Create user with default role
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
    
    // Generate tokens
    return this.generateTokens(user.id, user.email);
  }

  async login(dto: LoginDto): Promise<{ accessToken: string; refreshToken: string }> {
    const user = await this.prisma.user.findUnique({
      where: { email: dto.email },
      include: { roles: { include: { role: { include: { permissions: true } } } } },
    });
    
    if (!user) {
      throw new UnauthorizedException('Invalid credentials');
    }
    
    const isValid = await this.passwordService.verify(dto.password, user.password);
    
    if (!isValid) {
      throw new UnauthorizedException('Invalid credentials');
    }
    
    // Update last login
    await this.prisma.user.update({
      where: { id: user.id },
      data: { lastLoginAt: new Date() },
    });
    
    return this.generateTokens(user.id, user.email);
  }

  private generateTokens(userId: string, email: string): { accessToken: string; refreshToken: string } {
    const payload = { sub: userId, email };
    
    const accessToken = this.jwtService.sign(payload, {
      expiresIn: '15m',
    });
    
    const refreshToken = this.jwtService.sign(payload, {
      expiresIn: '7d',
      secret: process.env.JWT_REFRESH_SECRET,
    });
    
    // Store refresh token hash in DB
    // ... implementation
    
    return { accessToken, refreshToken };
  }
  
  // Additional methods: refreshToken, logout, forgotPassword, resetPassword
}
```

---

## Week 2: RBAC & API Completion

### Day 6-7: RBAC Implementation

#### Tasks
- [ ] Create RolesGuard
- [ ] Create PermissionsGuard
- [ ] Create @Roles() and @Permissions() decorators
- [ ] Create RoleService and PermissionService

#### apps/api/src/auth/guards/roles.guard.ts
```typescript
import { Injectable, CanActivate, ExecutionContext } from '@nestjs/common';
import { Reflector } from '@nestjs/core';
import { PrismaService } from '../../prisma/prisma.service';

@Injectable()
export class RolesGuard implements CanActivate {
  constructor(
    private reflector: Reflector,
    private prisma: PrismaService,
  ) {}

  async canActivate(context: ExecutionContext): Promise<boolean> {
    const requiredRoles = this.reflector.getAllAndOverride<string[]>('roles', [
      context.getHandler(),
      context.getClass(),
    ]);
    
    if (!requiredRoles) {
      return true;
    }
    
    const { user } = context.switchToHttp().getRequest();
    
    const userWithRoles = await this.prisma.user.findUnique({
      where: { id: user.userId },
      include: { roles: { include: { role: true } } },
    });
    
    const userRoles = userWithRoles?.roles.map((ur) => ur.role.name) ?? [];
    
    return requiredRoles.some((role) => userRoles.includes(role));
  }
}
```

#### apps/api/src/auth/decorators/roles.decorator.ts
```typescript
import { SetMetadata } from '@nestjs/common';

export const ROLES_KEY = 'roles';
export const Roles = (...roles: string[]) => SetMetadata(ROLES_KEY, roles);
```

### Day 8-9: Auth Controllers & DTOs

#### Tasks
- [ ] Create AuthController with endpoints
- [ ] Create all DTOs with validation
- [ ] Add Swagger decorators
- [ ] Implement password reset flow

#### apps/api/src/auth/auth.controller.ts
```typescript
import { Controller, Post, Body, Get, UseGuards, HttpCode, HttpStatus } from '@nestjs/common';
import { ApiTags, ApiOperation, ApiResponse, ApiBearerAuth } from '@nestjs/swagger';
import { AuthService } from './auth.service';
import { JwtAuthGuard } from './guards/jwt-auth.guard';
import { RegisterDto, LoginDto, RefreshTokenDto, ForgotPasswordDto, ResetPasswordDto } from './dto';
import { CurrentUser } from './decorators/current-user.decorator';

@ApiTags('Authentication')
@Controller('auth')
export class AuthController {
  constructor(private authService: AuthService) {}

  @Post('register')
  @ApiOperation({ summary: 'Register new user' })
  @ApiResponse({ status: 201, description: 'User registered successfully' })
  @ApiResponse({ status: 409, description: 'Email already exists' })
  async register(@Body() dto: RegisterDto) {
    return this.authService.register(dto);
  }

  @Post('login')
  @HttpCode(HttpStatus.OK)
  @ApiOperation({ summary: 'User login' })
  @ApiResponse({ status: 200, description: 'Login successful' })
  @ApiResponse({ status: 401, description: 'Invalid credentials' })
  async login(@Body() dto: LoginDto) {
    return this.authService.login(dto);
  }

  @Post('refresh')
  @HttpCode(HttpStatus.OK)
  @ApiOperation({ summary: 'Refresh access token' })
  async refreshToken(@Body() dto: RefreshTokenDto) {
    return this.authService.refreshToken(dto.refreshToken);
  }

  @Post('logout')
  @UseGuards(JwtAuthGuard)
  @HttpCode(HttpStatus.OK)
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Logout user' })
  async logout(@CurrentUser('sub') userId: string) {
    return this.authService.logout(userId);
  }

  @Post('forgot-password')
  @HttpCode(HttpStatus.OK)
  @ApiOperation({ summary: 'Request password reset' })
  async forgotPassword(@Body() dto: ForgotPasswordDto) {
    return this.authService.forgotPassword(dto.email);
  }

  @Post('reset-password')
  @HttpCode(HttpStatus.OK)
  @ApiOperation({ summary: 'Reset password with token' })
  async resetPassword(@Body() dto: ResetPasswordDto) {
    return this.authService.resetPassword(dto.token, dto.newPassword);
  }

  @Get('me')
  @UseGuards(JwtAuthGuard)
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Get current user profile' })
  async getProfile(@CurrentUser('sub') userId: string) {
    return this.authService.getProfile(userId);
  }
}
```

### Day 10: Testing & Seeding

#### Tasks
- [ ] Write unit tests for AuthService
- [ ] Write integration tests for AuthController
- [ ] Create database seed script
- [ ] Seed default roles and admin user

#### apps/api/prisma/seed.ts
```typescript
import { PrismaClient } from '@prisma/client';
import * as bcrypt from 'bcrypt';

const prisma = new PrismaClient();

async function main(): Promise<void> {
  // Create roles
  const adminRole = await prisma.role.create({
    data: {
      name: 'ADMIN',
      description: 'Full system access',
      permissions: {
        create: [
          { resource: '*', action: '*' },
        ],
      },
    },
  });

  const userRole = await prisma.role.create({
    data: {
      name: 'USER',
      description: 'Standard user',
      permissions: {
        create: [
          { resource: 'product', action: 'read' },
          { resource: 'profile', action: 'read' },
          { resource: 'profile', action: 'update' },
        ],
      },
    },
  });

  // Create admin user
  const adminPassword = await bcrypt.hash('admin123', 10);
  await prisma.user.create({
    data: {
      email: 'admin@example.com',
      password: adminPassword,
      firstName: 'Admin',
      lastName: 'User',
      emailVerified: true,
      status: 'ACTIVE',
      roles: {
        create: { roleId: adminRole.id },
      },
    },
  });

  console.log('✅ Database seeded');
}

main()
  .catch((e) => {
    console.error(e);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });
```

---

## Deliverables Checklist

- [ ] Database schema with migrations
- [ ] User registration/login/logout
- [ ] JWT token generation and validation
- [ ] Password reset flow
- [ ] RBAC system (roles and permissions)
- [ ] Protected route guards
- [ ] API documentation (Swagger)
- [ ] Unit tests (80%+ coverage)
- [ ] Database seed script

## API Endpoints

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | /api/v1/auth/register | No | Register new user |
| POST | /api/v1/auth/login | No | Login |
| POST | /api/v1/auth/refresh | No | Refresh tokens |
| POST | /api/v1/auth/logout | Yes | Logout |
| POST | /api/v1/auth/forgot-password | No | Request reset |
| POST | /api/v1/auth/reset-password | No | Reset password |
| GET | /api/v1/auth/me | Yes | Current user |

## Success Metrics

| Metric | Target | Measurement |
|--------|--------|-------------|
| Test coverage | > 80% | `pnpm test:cov` |
| API response time | < 100ms | Login endpoint p95 |
| Token generation | < 50ms | JWT sign operation |

## Risks & Mitigation

| Risk | Impact | Mitigation |
|------|--------|------------|
| JWT secret exposure | Critical | Use separate refresh secret, rotate regularly |
| Password hashing slow | Medium | Use bcrypt cost factor 10-12 |
| Race conditions in registration | Medium | Database unique constraints |

## Next Phase Handoff

**Phase 3 Prerequisites:**
- [ ] Auth API fully functional
- [ ] JWT middleware working
- [ ] RBAC guards tested
- [ ] Admin user can authenticate
