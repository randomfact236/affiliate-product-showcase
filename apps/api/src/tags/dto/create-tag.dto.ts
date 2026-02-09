import { IsString, IsOptional } from "class-validator";
import { ApiProperty } from "@nestjs/swagger";

export class CreateTagDto {
  @ApiProperty({ example: "wireless" })
  @IsString()
  slug: string;

  @ApiProperty({ example: "Wireless" })
  @IsString()
  name: string;
}

export class UpdateTagDto {
  @ApiProperty({ example: "Wireless", required: false })
  @IsString()
  @IsOptional()
  name?: string;
}
