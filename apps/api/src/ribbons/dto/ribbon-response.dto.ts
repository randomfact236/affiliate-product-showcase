import { RibbonPosition } from '@prisma/client';
import { ApiProperty } from '@nestjs/swagger';

export class RibbonResponseDto {
  @ApiProperty()
  id: string;

  @ApiProperty()
  name: string;

  @ApiProperty()
  label: string;

  @ApiProperty({ required: false })
  description: string | null;

  @ApiProperty()
  bgColor: string;

  @ApiProperty()
  textColor: string;

  @ApiProperty({ required: false })
  icon: string | null;

  @ApiProperty({ enum: RibbonPosition })
  position: RibbonPosition;

  @ApiProperty()
  sortOrder: number;

  @ApiProperty()
  isActive: boolean;

  @ApiProperty()
  createdAt: Date;

  @ApiProperty()
  updatedAt: Date;

  @ApiProperty({ required: false })
  createdBy: string | null;

  @ApiProperty({ required: false })
  updatedBy: string | null;
}

export class RibbonListResponseDto {
  @ApiProperty({ type: [RibbonResponseDto] })
  items: RibbonResponseDto[];

  @ApiProperty()
  total: number;

  @ApiProperty()
  page: number;

  @ApiProperty()
  limit: number;

  @ApiProperty()
  totalPages: number;
}
