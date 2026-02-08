import { IsString, IsOptional, IsBoolean, IsEnum } from 'class-validator';
import { ApiProperty } from '@nestjs/swagger';
import { AttributeType } from '@prisma/client';

export class CreateAttributeOptionDto {
  @ApiProperty({ example: 'red' })
  @IsString()
  value: string;

  @ApiProperty({ example: 'Red' })
  @IsString()
  displayValue: string;

  @ApiProperty({ default: 0, required: false })
  @IsOptional()
  sortOrder?: number = 0;
}

export class CreateAttributeDto {
  @ApiProperty({ example: 'color' })
  @IsString()
  name: string;

  @ApiProperty({ example: 'Color' })
  @IsString()
  displayName: string;

  @ApiProperty({ enum: AttributeType, example: AttributeType.SELECT })
  @IsEnum(AttributeType)
  type: AttributeType;

  @ApiProperty({ type: [CreateAttributeOptionDto], required: false })
  @IsOptional()
  options?: CreateAttributeOptionDto[];

  @ApiProperty({ default: false, required: false })
  @IsBoolean()
  @IsOptional()
  isFilterable?: boolean = false;

  @ApiProperty({ default: true, required: false })
  @IsBoolean()
  @IsOptional()
  isVisible?: boolean = true;
}

export class UpdateAttributeDto {
  @ApiProperty({ required: false })
  @IsString()
  @IsOptional()
  displayName?: string;

  @ApiProperty({ required: false })
  @IsBoolean()
  @IsOptional()
  isFilterable?: boolean;

  @ApiProperty({ required: false })
  @IsBoolean()
  @IsOptional()
  isVisible?: boolean;
}
