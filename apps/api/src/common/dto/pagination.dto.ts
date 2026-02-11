import { IsOptional, IsInt, Min } from "class-validator";
import { ApiProperty } from "@nestjs/swagger";
import { Transform } from "class-transformer";

export class PaginationDto {
  @ApiProperty({
    required: false,
    default: 0,
    description: "Number of items to skip",
  })
  @IsOptional()
  @IsInt()
  @Min(0)
  @Transform(({ value }) => parseInt(value, 10) || 0)
  skip?: number = 0;

  @ApiProperty({
    required: false,
    default: 50,
    description: "Number of items to return",
  })
  @IsOptional()
  @IsInt()
  @Min(1)
  @Transform(({ value }) => parseInt(value, 10) || 50)
  limit?: number = 50;
}
