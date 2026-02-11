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
import { DontMissService } from './dont-miss.service';
import { CreateDontMissSectionDto, UpdateDontMissSectionDto } from './dto/dont-miss.dto';

@Controller('dont-miss')
export class DontMissController {
  constructor(private readonly dontMissService: DontMissService) {}

  @Get()
  findAll(@Query('includeInactive') includeInactive?: string) {
    return this.dontMissService.findAll(includeInactive === 'true');
  }

  @Get('shortcodes')
  getAllShortcodes() {
    return this.dontMissService.getAllShortcodes();
  }

  @Get(':id')
  findById(@Param('id') id: string) {
    return this.dontMissService.findById(id);
  }

  @Get('by-shortcode/:shortcode')
  findByShortcode(@Param('shortcode') shortcode: string) {
    return this.dontMissService.findByShortcode(shortcode);
  }

  @Get(':shortcode/content')
  getSectionContent(@Param('shortcode') shortcode: string) {
    return this.dontMissService.getSectionContent(shortcode);
  }

  @Post()
  create(@Body() dto: CreateDontMissSectionDto) {
    return this.dontMissService.create(dto);
  }

  @Put(':id')
  update(@Param('id') id: string, @Body() dto: UpdateDontMissSectionDto) {
    return this.dontMissService.update(id, dto);
  }

  @Delete(':id')
  @HttpCode(HttpStatus.NO_CONTENT)
  remove(@Param('id') id: string) {
    return this.dontMissService.remove(id);
  }

  @Put(':id/duplicate')
  duplicate(@Param('id') id: string) {
    return this.dontMissService.duplicate(id);
  }

  @Post('reorder')
  @HttpCode(HttpStatus.OK)
  reorder(@Body() body: { ids: string[] }) {
    return this.dontMissService.reorder(body.ids);
  }
}
