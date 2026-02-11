import { UserStatus } from '@prisma/client';
import { ApiProperty } from '@nestjs/swagger';

export class UserResponseDto {
  @ApiProperty()
  id: string;

  @ApiProperty()
  email: string;

  @ApiProperty({ required: false })
  firstName: string | null;

  @ApiProperty({ required: false })
  lastName: string | null;

  @ApiProperty({ required: false })
  avatar: string | null;

  @ApiProperty({ enum: UserStatus })
  status: UserStatus;

  @ApiProperty()
  emailVerified: boolean;

  @ApiProperty()
  createdAt: Date;

  @ApiProperty()
  updatedAt: Date;

  @ApiProperty({ type: [String] })
  roles: string[];

  @ApiProperty({ required: false })
  lastLoginAt: Date | null;
}

export class UserListResponseDto {
  @ApiProperty({ type: [UserResponseDto] })
  items: UserResponseDto[];

  @ApiProperty()
  total: number;

  @ApiProperty()
  page: number;

  @ApiProperty()
  limit: number;

  @ApiProperty()
  totalPages: number;
}

export class UpdateUserRolesDto {
  @ApiProperty({ description: 'Role IDs to assign', type: [String] })
  roleIds: string[];
}
