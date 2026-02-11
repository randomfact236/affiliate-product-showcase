import { IsString, IsOptional, IsBoolean, IsIn } from 'class-validator';

export class CreateSettingDto {
  @IsString()
  key: string;

  value: any;

  @IsOptional()
  @IsString()
  @IsIn(['string', 'number', 'boolean', 'json', 'text'])
  type?: string = 'string';

  @IsOptional()
  @IsString()
  group?: string = 'general';

  @IsOptional()
  @IsString()
  label?: string;

  @IsOptional()
  @IsString()
  description?: string;

  @IsOptional()
  @IsBoolean()
  isPublic?: boolean = true;
}

export class UpdateSettingDto {
  @IsOptional()
  value?: any;

  @IsOptional()
  @IsString()
  @IsIn(['string', 'number', 'boolean', 'json', 'text'])
  type?: string;

  @IsOptional()
  @IsString()
  group?: string;

  @IsOptional()
  @IsString()
  label?: string;

  @IsOptional()
  @IsString()
  description?: string;

  @IsOptional()
  @IsBoolean()
  isPublic?: boolean;
}

export class BulkUpdateSettingsDto {
  settings: Record<string, any>;
}
