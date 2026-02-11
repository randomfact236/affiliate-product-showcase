import {
  Injectable,
  NotFoundException,
  ConflictException,
  BadRequestException,
} from '@nestjs/common';
import { PrismaService } from '../prisma/prisma.service';
import {
  CreateUserDto,
  UpdateUserDto,
  QueryUsersDto,
  UserListResponseDto,
  UserResponseDto,
} from './dto';
import { Prisma, UserStatus } from '@prisma/client';
import * as bcrypt from 'bcrypt';

@Injectable()
export class UsersService {
  constructor(private readonly prisma: PrismaService) {}

  /**
   * Create a new user
   */
  async create(createDto: CreateUserDto, createdBy?: string): Promise<UserResponseDto> {
    // Check for duplicate email
    const existing = await this.prisma.user.findUnique({
      where: { email: createDto.email },
    });

    if (existing) {
      throw new ConflictException(`User with email '${createDto.email}' already exists`);
    }

    // Hash password
    const hashedPassword = await bcrypt.hash(createDto.password, 10);

    const user = await this.prisma.user.create({
      data: {
        email: createDto.email,
        password: hashedPassword,
        firstName: createDto.firstName,
        lastName: createDto.lastName,
        avatar: createDto.avatar,
        status: createDto.status || UserStatus.ACTIVE,
        roles: createDto.roleIds
          ? {
              create: createDto.roleIds.map((roleId) => ({
                role: { connect: { id: roleId } },
              })),
            }
          : undefined,
      },
      include: {
        roles: {
          include: {
            role: true,
          },
        },
      },
    });

    return this.mapToResponseDto(user);
  }

  /**
   * Find all users with pagination and filtering
   */
  async findAll(query: QueryUsersDto): Promise<UserListResponseDto> {
    const { search, status, role, page = 1, limit = 20 } = query;

    const where: Prisma.UserWhereInput = {
      deletedAt: null, // Exclude soft-deleted users
    };

    if (search) {
      where.OR = [
        { email: { contains: search, mode: 'insensitive' } },
        { firstName: { contains: search, mode: 'insensitive' } },
        { lastName: { contains: search, mode: 'insensitive' } },
      ];
    }

    if (status) {
      where.status = status;
    }

    if (role) {
      where.roles = {
        some: {
          role: {
            name: role,
          },
        },
      };
    }

    const skip = (page - 1) * limit;

    const [items, total] = await Promise.all([
      this.prisma.user.findMany({
        where,
        skip,
        take: limit,
        orderBy: { createdAt: 'desc' },
        include: {
          roles: {
            include: {
              role: true,
            },
          },
        },
      }),
      this.prisma.user.count({ where }),
    ]);

    return {
      items: items.map(this.mapToResponseDto),
      total,
      page,
      limit,
      totalPages: Math.ceil(total / limit),
    };
  }

  /**
   * Find a user by ID
   */
  async findOne(id: string): Promise<UserResponseDto> {
    const user = await this.prisma.user.findUnique({
      where: { id, deletedAt: null },
      include: {
        roles: {
          include: {
            role: true,
          },
        },
      },
    });

    if (!user) {
      throw new NotFoundException(`User with ID '${id}' not found`);
    }

    return this.mapToResponseDto(user);
  }

  /**
   * Find a user by email
   */
  async findByEmail(email: string): Promise<UserResponseDto | null> {
    const user = await this.prisma.user.findUnique({
      where: { email, deletedAt: null },
      include: {
        roles: {
          include: {
            role: true,
          },
        },
      },
    });

    return user ? this.mapToResponseDto(user) : null;
  }

  /**
   * Update a user
   */
  async update(id: string, updateDto: UpdateUserDto): Promise<UserResponseDto> {
    const existing = await this.prisma.user.findUnique({
      where: { id, deletedAt: null },
    });

    if (!existing) {
      throw new NotFoundException(`User with ID '${id}' not found`);
    }

    // Check email uniqueness if email is being updated
    const updateEmail = (updateDto as unknown as { email?: string }).email;
    if (updateEmail && updateEmail !== existing.email) {
      const duplicate = await this.prisma.user.findUnique({
        where: { email: updateEmail },
      });

      if (duplicate) {
        throw new ConflictException(`User with email '${updateEmail}' already exists`);
      }
    }

    // Prepare update data
    const updateData: Prisma.UserUpdateInput = {
      firstName: updateDto.firstName,
      lastName: updateDto.lastName,
      avatar: updateDto.avatar,
      status: updateDto.status,
    };

    // Handle password update
    if (updateDto.password) {
      updateData.password = await bcrypt.hash(updateDto.password, 10);
    }

    // Handle email update
    if (updateEmail) {
      updateData.email = updateEmail;
    }

    // Handle roles update
    if (updateDto.roleIds) {
      // Delete existing roles and create new ones
      await this.prisma.userRole.deleteMany({
        where: { userId: id },
      });

      if (updateDto.roleIds.length > 0) {
        await this.prisma.userRole.createMany({
          data: updateDto.roleIds.map((roleId) => ({
            userId: id,
            roleId,
          })),
        });
      }
    }

    const user = await this.prisma.user.update({
      where: { id },
      data: updateData,
      include: {
        roles: {
          include: {
            role: true,
          },
        },
      },
    });

    return this.mapToResponseDto(user);
  }

  /**
   * Soft delete a user
   */
  async remove(id: string): Promise<void> {
    const existing = await this.prisma.user.findUnique({
      where: { id, deletedAt: null },
    });

    if (!existing) {
      throw new NotFoundException(`User with ID '${id}' not found`);
    }

    // Soft delete - anonymize data
    await this.prisma.user.update({
      where: { id },
      data: {
        email: `deleted-${id}@anonymized.local`,
        password: 'DELETED',
        firstName: 'Deleted',
        lastName: 'User',
        status: UserStatus.INACTIVE,
        deletedAt: new Date(),
      },
    });

    // Revoke all sessions
    await this.prisma.session.deleteMany({
      where: { userId: id },
    });

    await this.prisma.refreshToken.deleteMany({
      where: { userId: id },
    });
  }

  /**
   * Update user roles
   */
  async updateRoles(id: string, roleIds: string[]): Promise<UserResponseDto> {
    const existing = await this.prisma.user.findUnique({
      where: { id, deletedAt: null },
    });

    if (!existing) {
      throw new NotFoundException(`User with ID '${id}' not found`);
    }

    // Delete existing roles
    await this.prisma.userRole.deleteMany({
      where: { userId: id },
    });

    // Create new roles
    if (roleIds.length > 0) {
      await this.prisma.userRole.createMany({
        data: roleIds.map((roleId) => ({
          userId: id,
          roleId,
        })),
      });
    }

    const user = await this.prisma.user.findUnique({
      where: { id },
      include: {
        roles: {
          include: {
            role: true,
          },
        },
      },
    });

    return this.mapToResponseDto(user!);
  }

  /**
   * Toggle user active status
   */
  async toggleStatus(id: string): Promise<UserResponseDto> {
    const existing = await this.prisma.user.findUnique({
      where: { id, deletedAt: null },
    });

    if (!existing) {
      throw new NotFoundException(`User with ID '${id}' not found`);
    }

    const newStatus =
      existing.status === UserStatus.ACTIVE
        ? UserStatus.INACTIVE
        : UserStatus.ACTIVE;

    const user = await this.prisma.user.update({
      where: { id },
      data: { status: newStatus },
      include: {
        roles: {
          include: {
            role: true,
          },
        },
      },
    });

    return this.mapToResponseDto(user);
  }

  /**
   * Get user statistics
   */
  async getStats(): Promise<{
    total: number;
    active: number;
    inactive: number;
    pending: number;
  }> {
    const [total, active, inactive, pending] = await Promise.all([
      this.prisma.user.count({ where: { deletedAt: null } }),
      this.prisma.user.count({
        where: { status: UserStatus.ACTIVE, deletedAt: null },
      }),
      this.prisma.user.count({
        where: { status: UserStatus.INACTIVE, deletedAt: null },
      }),
      this.prisma.user.count({
        where: { status: UserStatus.PENDING_VERIFICATION, deletedAt: null },
      }),
    ]);

    return { total, active, inactive, pending };
  }

  /**
   * Map database user to response DTO
   */
  private mapToResponseDto(user: unknown): UserResponseDto {
    const u = user as {
      id: string;
      email: string;
      firstName: string | null;
      lastName: string | null;
      avatar: string | null;
      status: UserStatus;
      emailVerified: boolean;
      createdAt: Date;
      updatedAt: Date;
      lastLoginAt: Date | null;
      roles: { role: { name: string } }[];
    };

    return {
      id: u.id,
      email: u.email,
      firstName: u.firstName,
      lastName: u.lastName,
      avatar: u.avatar,
      status: u.status,
      emailVerified: u.emailVerified,
      createdAt: u.createdAt,
      updatedAt: u.updatedAt,
      lastLoginAt: u.lastLoginAt,
      roles: u.roles?.map((r) => r.role.name) || [],
    };
  }
}
