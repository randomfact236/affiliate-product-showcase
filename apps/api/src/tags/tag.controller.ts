import { Controller, Get, Post, Put, Delete, Body, Param, UseGuards } from '@nestjs/common';
import { ApiTags, ApiOperation, ApiBearerAuth } from '@nestjs/swagger';
import { TagService } from './tag.service';
import { CreateTagDto, UpdateTagDto } from './dto/create-tag.dto';
import { JwtAuthGuard, RolesGuard } from '../auth/guards';
import { Roles } from '../auth/decorators';

@ApiTags('Tags')
@Controller('tags')
export class TagController {
  constructor(private tagService: TagService) {}

  @Get()
  @ApiOperation({ summary: 'Get all tags' })
  findAll() {
    return this.tagService.findAll();
  }

  @Get(':id')
  @ApiOperation({ summary: 'Get tag by ID' })
  findOne(@Param('id') id: string) {
    return this.tagService.findOne(id);
  }

  @Get('slug/:slug')
  @ApiOperation({ summary: 'Get tag by slug' })
  findBySlug(@Param('slug') slug: string) {
    return this.tagService.findBySlug(slug);
  }

  @Post()
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles('ADMIN', 'EDITOR')
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Create tag (Admin/Editor only)' })
  create(@Body() dto: CreateTagDto) {
    return this.tagService.create(dto);
  }

  @Put(':id')
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles('ADMIN', 'EDITOR')
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Update tag (Admin/Editor only)' })
  update(@Param('id') id: string, @Body() dto: UpdateTagDto) {
    return this.tagService.update(id, dto);
  }

  @Delete(':id')
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles('ADMIN')
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Delete tag (Admin only)' })
  remove(@Param('id') id: string) {
    return this.tagService.remove(id);
  }
}
