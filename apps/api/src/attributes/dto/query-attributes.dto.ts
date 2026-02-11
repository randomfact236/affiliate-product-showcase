import { IsString, IsOptional, IsBoolean, IsEnum } from "class-validator";
import { ApiProperty } from "@nestjs/swagger";
import { Transform } from "class-transformer";
import { AttributeType } from "@prisma/client";
import { PaginationDto } from "../../common/dto/pagination.dto";

export class QueryAttributesDto extends PaginationDto {
  @ApiProperty({ required: false, description: "Search by name or displayName" })
  @IsString()
  @IsOptional()
  search?: string;

  @ApiProperty({ enum: AttributeType, required: false })
  @IsEnum(AttributeType)
  @IsOptional()
  type?: AttributeType;

  @ApiProperty({ required: false, description: "Filter by filterable status" })
  @IsBoolean()
  @IsOptional()
  @Transform(({ value }) => value === "true" || value === true)
  isFilterable?: boolean;

  @ApiProperty({ required: false, description: "Include options in response" })
  @IsBoolean()
  @IsOptional()
  @Transform(({ value }) => value !== "false")
  includeOptions?: boolean = true;
}
