import {
  Controller,
  Get,
  Post,
  Put,
  Delete,
  Patch,
  Body,
  Param,
  Query,
  Req,
  UseGuards,
  HttpCode,
  HttpStatus,
} from '@nestjs/common';
import { Request } from 'express';
import {
  ApiTags,
  ApiOperation,
  ApiResponse,
  ApiBearerAuth,
  ApiParam,
} from '@nestjs/swagger';
import { UsersService } from './users.service';
import {
  CreateUserDto,
  UpdateUserDto,
  QueryUsersDto,
  UserResponseDto,
  UserListResponseDto,
  UpdateUserRolesDto,
} from './dto';
import { JwtAuthGuard } from '../auth/guards/jwt-auth.guard';
import { RolesGuard } from '../auth/guards/roles.guard';
import { Roles } from '../auth/decorators/roles.decorator';
import { CurrentUser } from '../auth/decorators/current-user.decorator';
import { AuditService } from '../common/services/audit.service';
import { PrismaService } from '../prisma/prisma.service';

@ApiTags('Users')
@Controller('users')
@UseGuards(JwtAuthGuard)
export class UsersController {
  constructor(
    private readonly usersService: UsersService,
    private readonly prisma: PrismaService,
    private readonly auditService: AuditService,
  ) {}

  // ==================== ADMIN ENDPOINTS ====================

  /**
   * Admin: Get all users
   */
  @Get()
  @UseGuards(RolesGuard)
  @Roles('ADMIN')
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Get all users (Admin only)' })
  @ApiResponse({
    status: 200,
    description: 'List of users',
    type: UserListResponseDto,
  })
  async findAll(@Query() query: QueryUsersDto): Promise<UserListResponseDto> {
    return this.usersService.findAll(query);
  }

  /**
   * Admin: Get user statistics
   */
  @Get('stats')
  @UseGuards(RolesGuard)
  @Roles('ADMIN')
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Get user statistics (Admin only)' })
  @ApiResponse({ status: 200, description: 'User statistics' })
  async getStats() {
    return this.usersService.getStats();
  }

  /**
   * Admin: Create user
   */
  @Post()
  @UseGuards(RolesGuard)
  @Roles('ADMIN')
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Create new user (Admin only)' })
  @ApiResponse({
    status: 201,
    description: 'User created successfully',
    type: UserResponseDto,
  })
  async create(
    @Body() createDto: CreateUserDto,
    @CurrentUser('userId') adminId: string,
    @Req() req: Request,
  ): Promise<UserResponseDto> {
    const user = await this.usersService.create(createDto, adminId);

    await this.auditService.log({
      action: 'USER_CREATE',
      userId: adminId,
      resourceType: 'user',
      resourceId: user.id,
      ipAddress: req.ip,
      userAgent: req.headers['user-agent'],
    });

    return user;
  }

  /**
   * Admin: Get user by ID
   */
  @Get(':id')
  @UseGuards(RolesGuard)
  @Roles('ADMIN')
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Get user by ID (Admin only)' })
  @ApiParam({ name: 'id', description: 'User ID' })
  @ApiResponse({
    status: 200,
    description: 'User found',
    type: UserResponseDto,
  })
  @ApiResponse({ status: 404, description: 'User not found' })
  async findOne(@Param('id') id: string): Promise<UserResponseDto> {
    return this.usersService.findOne(id);
  }

  /**
   * Admin: Update user
   */
  @Put(':id')
  @UseGuards(RolesGuard)
  @Roles('ADMIN')
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Update user (Admin only)' })
  @ApiParam({ name: 'id', description: 'User ID' })
  @ApiResponse({
    status: 200,
    description: 'User updated successfully',
    type: UserResponseDto,
  })
  async update(
    @Param('id') id: string,
    @Body() updateDto: UpdateUserDto,
    @CurrentUser('userId') adminId: string,
    @Req() req: Request,
  ): Promise<UserResponseDto> {
    const user = await this.usersService.update(id, updateDto);

    await this.auditService.log({
      action: 'USER_UPDATE',
      userId: adminId,
      resourceType: 'user',
      resourceId: id,
      ipAddress: req.ip,
      userAgent: req.headers['user-agent'],
    });

    return user;
  }

  /**
   * Admin: Delete user (soft delete)
   */
  @Delete(':id')
  @UseGuards(RolesGuard)
  @Roles('ADMIN')
  @ApiBearerAuth()
  @HttpCode(HttpStatus.NO_CONTENT)
  @ApiOperation({ summary: 'Delete user (Admin only)' })
  @ApiParam({ name: 'id', description: 'User ID' })
  @ApiResponse({ status: 204, description: 'User deleted successfully' })
  async remove(
    @Param('id') id: string,
    @CurrentUser('userId') adminId: string,
    @Req() req: Request,
  ): Promise<void> {
    await this.usersService.remove(id);

    await this.auditService.log({
      action: 'USER_DELETE',
      userId: adminId,
      resourceType: 'user',
      resourceId: id,
      ipAddress: req.ip,
      userAgent: req.headers['user-agent'],
    });
  }

  /**
   * Admin: Update user roles
   */
  @Patch(':id/roles')
  @UseGuards(RolesGuard)
  @Roles('ADMIN')
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Update user roles (Admin only)' })
  @ApiParam({ name: 'id', description: 'User ID' })
  @ApiResponse({
    status: 200,
    description: 'User roles updated',
    type: UserResponseDto,
  })
  async updateRoles(
    @Param('id') id: string,
    @Body() rolesDto: UpdateUserRolesDto,
    @CurrentUser('userId') adminId: string,
    @Req() req: Request,
  ): Promise<UserResponseDto> {
    const user = await this.usersService.updateRoles(id, rolesDto.roleIds);

    await this.auditService.log({
      action: 'USER_UPDATE_ROLES',
      userId: adminId,
      resourceType: 'user',
      resourceId: id,
      ipAddress: req.ip,
      userAgent: req.headers['user-agent'],
    });

    return user;
  }

  /**
   * Admin: Toggle user status
   */
  @Patch(':id/toggle-status')
  @UseGuards(RolesGuard)
  @Roles('ADMIN')
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Toggle user active status (Admin only)' })
  @ApiParam({ name: 'id', description: 'User ID' })
  @ApiResponse({
    status: 200,
    description: 'User status toggled',
    type: UserResponseDto,
  })
  async toggleStatus(
    @Param('id') id: string,
    @CurrentUser('userId') adminId: string,
    @Req() req: Request,
  ): Promise<UserResponseDto> {
    const user = await this.usersService.toggleStatus(id);

    await this.auditService.log({
      action: 'USER_TOGGLE_STATUS',
      userId: adminId,
      resourceType: 'user',
      resourceId: id,
      ipAddress: req.ip,
      userAgent: req.headers['user-agent'],
    });

    return user;
  }

  // ==================== USER SELF-MANAGEMENT ENDPOINTS ====================

  /**
   * Get current user profile
   */
  @Get('me/profile')
  @ApiOperation({ summary: 'Get current user profile' })
  @ApiResponse({
    status: 200,
    description: 'User profile',
    type: UserResponseDto,
  })
  async getProfile(@CurrentUser('userId') userId: string): Promise<UserResponseDto> {
    return this.usersService.findOne(userId);
  }

  /**
   * Update current user profile
   */
  @Put('me/profile')
  @ApiOperation({ summary: 'Update current user profile' })
  @ApiResponse({
    status: 200,
    description: 'Profile updated',
    type: UserResponseDto,
  })
  async updateProfile(
    @CurrentUser('userId') userId: string,
    @Body() updateDto: UpdateUserDto,
  ): Promise<UserResponseDto> {
    // Prevent updating sensitive fields
    delete (updateDto as unknown as { password?: string }).password;
    delete (updateDto as unknown as { email?: string }).email;
    delete (updateDto as unknown as { roleIds?: string[] }).roleIds;

    return this.usersService.update(userId, updateDto);
  }

  /**
   * GDPR: Data Export (Right to Data Portability)
   */
  @Get('me/export')
  @ApiOperation({ summary: 'Export user data (GDPR)' })
  async exportData(@CurrentUser('userId') userId: string) {
    const user = await this.prisma.user.findUnique({
      where: { id: userId },
      include: {
        sessions: true,
        refreshTokens: true,
        createdProducts: true,
        roles: {
          include: {
            role: true,
          },
        },
      },
    });

    if (!user) {
      throw new Error('User not found');
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
      },
      sessions: user.sessions,
      products: user.createdProducts,
      roles: user.roles.map((r) => r.role.name),
    };
  }

  /**
   * GDPR: Right to Erasure (Account Deletion)
   */
  @Delete('me')
  @HttpCode(HttpStatus.NO_CONTENT)
  @ApiOperation({ summary: 'Delete own account (GDPR)' })
  async deleteAccount(
    @CurrentUser('userId') userId: string,
    @Req() req: Request,
  ): Promise<void> {
    await this.usersService.remove(userId);

    await this.auditService.log({
      action: 'USER_DELETE_ACCOUNT',
      userId,
      resourceType: 'user',
      resourceId: userId,
      ipAddress: req.ip,
      userAgent: req.headers['user-agent'],
    });
  }

  /**
   * GDPR: Consent Management
   */
  @Post('me/consent')
  @ApiOperation({ summary: 'Update consent preferences' })
  async updateConsent(
    @CurrentUser('userId') userId: string,
    @Body() consents: Record<string, boolean>,
  ) {
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
