import {
  Controller,
  Get,
  Post,
  Put,
  Delete,
  Patch,
  Body,
  Param,
  Query,
  UseGuards,
  HttpCode,
  HttpStatus,
} from '@nestjs/common';
import { ApiTags, ApiOperation, ApiResponse, ApiBearerAuth, ApiParam } from '@nestjs/swagger';
import { TagsService } from './tags.service';
import {
  CreateTagDto,
  UpdateTagDto,
  QueryTagsDto,
  TagResponseDto,
  TagListResponseDto,
  MergeTagsDto,
} from './dto';
import { JwtAuthGuard } from '../auth/guards/jwt-auth.guard';
import { RolesGuard } from '../auth/guards/roles.guard';
import { Roles } from '../auth/decorators/roles.decorator';
import { CurrentUser } from '../auth/decorators/current-user.decorator';

@ApiTags('Tags')
@Controller('tags')
export class TagsController {
  constructor(private readonly tagsService: TagsService) {}

  /**
   * Public endpoint to get all active tags
   */
  @Get('active')
  @ApiOperation({ summary: 'Get all active tags (public)' })
  @ApiResponse({
    status: 200,
    description: 'List of active tags',
    type: [TagResponseDto],
  })
  async findActive(): Promise<TagResponseDto[]> {
    return this.tagsService.findActive();
  }

  /**
   * Admin: Create a new tag
   */
  @Post()
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles('ADMIN', 'EDITOR')
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Create a new tag' })
  @ApiResponse({
    status: 201,
    description: 'Tag created successfully',
    type: TagResponseDto,
  })
  @ApiResponse({ status: 409, description: 'Tag with this slug already exists' })
  async create(
    @Body() createDto: CreateTagDto,
    @CurrentUser('userId') userId: string,
  ): Promise<TagResponseDto> {
    return this.tagsService.create(createDto, userId);
  }

  /**
   * Admin: Get all tags with filtering
   */
  @Get()
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles('ADMIN', 'EDITOR')
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Get all tags with pagination and filtering' })
  @ApiResponse({
    status: 200,
    description: 'Paginated list of tags',
    type: TagListResponseDto,
  })
  async findAll(@Query() query: QueryTagsDto): Promise<TagListResponseDto> {
    return this.tagsService.findAll(query);
  }

  /**
   * Admin: Get tag by ID
   */
  @Get(':id')
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles('ADMIN', 'EDITOR')
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Get a tag by ID' })
  @ApiParam({ name: 'id', description: 'Tag ID' })
  @ApiResponse({
    status: 200,
    description: 'Tag found',
    type: TagResponseDto,
  })
  @ApiResponse({ status: 404, description: 'Tag not found' })
  async findOne(@Param('id') id: string): Promise<TagResponseDto> {
    return this.tagsService.findOne(id);
  }

  /**
   * Admin: Update tag
   */
  @Put(':id')
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles('ADMIN', 'EDITOR')
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Update a tag' })
  @ApiParam({ name: 'id', description: 'Tag ID' })
  @ApiResponse({
    status: 200,
    description: 'Tag updated successfully',
    type: TagResponseDto,
  })
  @ApiResponse({ status: 404, description: 'Tag not found' })
  @ApiResponse({ status: 409, description: 'Tag with this slug already exists' })
  async update(
    @Param('id') id: string,
    @Body() updateDto: UpdateTagDto,
    @CurrentUser('userId') userId: string,
  ): Promise<TagResponseDto> {
    return this.tagsService.update(id, updateDto, userId);
  }

  /**
   * Admin: Toggle tag active status
   */
  @Patch(':id/toggle-active')
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles('ADMIN', 'EDITOR')
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Toggle tag active status' })
  @ApiParam({ name: 'id', description: 'Tag ID' })
  @ApiResponse({
    status: 200,
    description: 'Tag status toggled successfully',
    type: TagResponseDto,
  })
  @ApiResponse({ status: 404, description: 'Tag not found' })
  async toggleActive(
    @Param('id') id: string,
    @CurrentUser('userId') userId: string,
  ): Promise<TagResponseDto> {
    return this.tagsService.toggleActive(id, userId);
  }

  /**
   * Admin: Merge tags
   */
  @Post('merge')
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles('ADMIN')
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Merge multiple tags into one' })
  @ApiResponse({
    status: 200,
    description: 'Tags merged successfully',
  })
  @ApiResponse({ status: 404, description: 'One or more tags not found' })
  @ApiResponse({ status: 400, description: 'Invalid merge request' })
  async merge(
    @Body() mergeDto: MergeTagsDto,
    @CurrentUser('userId') userId: string,
  ): Promise<{ message: string; merged: number; target: TagResponseDto }> {
    const result = await this.tagsService.merge(mergeDto, userId);
    return {
      message: `Successfully merged ${result.merged} tags into '${result.target.name}'`,
      ...result,
    };
  }

  /**
   * Admin: Delete tag
   */
  @Delete(':id')
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles('ADMIN')
  @ApiBearerAuth()
  @HttpCode(HttpStatus.NO_CONTENT)
  @ApiOperation({ summary: 'Delete a tag' })
  @ApiParam({ name: 'id', description: 'Tag ID' })
  @ApiResponse({ status: 204, description: 'Tag deleted successfully' })
  @ApiResponse({ status: 404, description: 'Tag not found' })
  @ApiResponse({
    status: 400,
    description: 'Cannot delete tag that is assigned to products',
  })
  async remove(@Param('id') id: string): Promise<void> {
    return this.tagsService.remove(id);
  }
}
