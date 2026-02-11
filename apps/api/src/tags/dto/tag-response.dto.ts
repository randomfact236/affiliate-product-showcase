import { ApiProperty } from '@nestjs/swagger';

export class TagResponseDto {
  @ApiProperty()
  id: string;

  @ApiProperty()
  slug: string;

  @ApiProperty()
  name: string;

  @ApiProperty({ required: false })
  description: string | null;

  @ApiProperty({ required: false })
  color: string | null;

  @ApiProperty({ required: false })
  icon: string | null;

  @ApiProperty()
  sortOrder: number;

  @ApiProperty()
  isActive: boolean;

  @ApiProperty()
  productCount: number;

  @ApiProperty()
  createdAt: Date;

  @ApiProperty()
  updatedAt: Date;
}

export class TagListResponseDto {
  @ApiProperty({ type: [TagResponseDto] })
  items: TagResponseDto[];

  @ApiProperty()
  total: number;

  @ApiProperty()
  page: number;

  @ApiProperty()
  limit: number;

  @ApiProperty()
  totalPages: number;
}

export class MergeTagsDto {
  @ApiProperty({ description: 'Source tag IDs to merge from', type: [String] })
  sourceTagIds: string[];

  @ApiProperty({ description: 'Target tag ID to merge into' })
  targetTagId: string;
}
