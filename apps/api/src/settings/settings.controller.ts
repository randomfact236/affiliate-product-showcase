import {
  Controller,
  Get,
  Post,
  Put,
  Delete,
  Body,
  Param,
  Query,
  HttpCode,
  HttpStatus,
} from '@nestjs/common';
import { SettingsService } from './settings.service';
import { CreateSettingDto, UpdateSettingDto, BulkUpdateSettingsDto } from './dto/settings.dto';

@Controller('settings')
export class SettingsController {
  constructor(private readonly settingsService: SettingsService) {}

  @Get()
  findAll(@Query('group') group?: string) {
    if (group) {
      return this.settingsService.findByGroup(group);
    }
    return this.settingsService.findAll();
  }

  @Get('public')
  async findPublic() {
    const settings = await this.settingsService.findAll();
    return settings.filter((s) => s.isPublic);
  }

  @Get('shortcodes')
  getShortcodes() {
    return this.settingsService.getShortcodes();
  }

  @Get('dont-miss')
  getDontMissConfig() {
    return this.settingsService.getDontMissConfig();
  }

  @Get(':key')
  findOne(@Param('key') key: string) {
    return this.settingsService.findOne(key);
  }

  @Get(':key/value')
  async getValue(@Param('key') key: string, @Query('default') defaultValue?: string) {
    const value = await this.settingsService.getValue(key, defaultValue);
    return { key, value };
  }

  @Post()
  create(@Body() dto: CreateSettingDto) {
    return this.settingsService.create(dto);
  }

  @Put(':key')
  update(@Param('key') key: string, @Body() dto: UpdateSettingDto) {
    return this.settingsService.update(key, dto);
  }

  @Put()
  @HttpCode(HttpStatus.OK)
  async bulkUpdate(@Body() dto: BulkUpdateSettingsDto) {
    const results = [];
    for (const [key, value] of Object.entries(dto.settings)) {
      const result = await this.settingsService.upsert(key, {
        key,
        value,
        type: typeof value === 'boolean' ? 'boolean' : typeof value === 'number' ? 'number' : 'string',
      });
      results.push(result);
    }
    return { message: 'Settings updated', count: results.length };
  }

  @Put('dont-miss/config')
  updateDontMissConfig(@Body() config: any) {
    return this.settingsService.updateDontMissConfig(config);
  }

  @Delete(':key')
  @HttpCode(HttpStatus.NO_CONTENT)
  remove(@Param('key') key: string) {
    return this.settingsService.remove(key);
  }

  @Post('initialize')
  @HttpCode(HttpStatus.OK)
  initializeDefaults() {
    return this.settingsService.initializeDefaults();
  }
}
