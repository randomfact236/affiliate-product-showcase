import {
  Controller,
  Get,
  Post,
  Put,
  Delete,
  Body,
  Param,
  Query,
  UseGuards,
  HttpCode,
  HttpStatus,
} from '@nestjs/common';
import { ApiTags, ApiOperation, ApiResponse, ApiBearerAuth, ApiParam, ApiConsumes } from '@nestjs/swagger';
import { MediaService } from './media.service';
import {
  CreateMediaDto,
  BulkConvertDto,
  QueryMediaDto,
  ScanUnconvertedDto,
  MediaResponseDto,
  MediaListResponseDto,
  ConversionStatsDto,
  ConversionJobResponseDto,
} from './dto';
import { JwtAuthGuard } from '../auth/guards/jwt-auth.guard';
import { RolesGuard } from '../auth/guards/roles.guard';
import { Roles } from '../auth/decorators/roles.decorator';
import { CurrentUser } from '../auth/decorators/current-user.decorator';

@ApiTags('Media')
@Controller('media')
export class MediaController {
  constructor(private readonly mediaService: MediaService) {}

  /**
   * Get conversion statistics
   */
  @Get('stats')
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles('ADMIN', 'EDITOR')
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Get image conversion statistics' })
  @ApiResponse({
    status: 200,
    description: 'Conversion statistics',
    type: ConversionStatsDto,
  })
  async getStats(): Promise<ConversionStatsDto> {
    return this.mediaService.getConversionStats();
  }

  /**
   * Get queue status
   */
  @Get('queue-status')
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles('ADMIN', 'EDITOR')
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Get conversion queue status' })
  @ApiResponse({
    status: 200,
    description: 'Queue status (active, waiting, completed, failed)',
  })
  async getQueueStatus() {
    return this.mediaService.getQueueStatus();
  }

  /**
   * Scan for unconverted images
   */
  @Get('unconverted')
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles('ADMIN', 'EDITOR')
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Scan for unconverted images' })
  @ApiResponse({
    status: 200,
    description: 'List of unconverted images',
    type: [MediaResponseDto],
  })
  async scanUnconverted(@Query() query: ScanUnconvertedDto): Promise<MediaResponseDto[]> {
    return this.mediaService.scanUnconverted(query);
  }

  /**
   * Create media record (after upload)
   */
  @Post()
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles('ADMIN', 'EDITOR')
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Create media record after upload' })
  @ApiResponse({
    status: 201,
    description: 'Media created and queued for conversion',
    type: MediaResponseDto,
  })
  async create(
    @Body() createDto: CreateMediaDto,
    @CurrentUser('userId') userId: string,
  ): Promise<MediaResponseDto> {
    return this.mediaService.create(createDto, userId);
  }

  /**
   * Get all media
   */
  @Get()
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles('ADMIN', 'EDITOR')
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Get all media with pagination' })
  @ApiResponse({
    status: 200,
    description: 'Paginated list of media',
    type: MediaListResponseDto,
  })
  async findAll(@Query() query: QueryMediaDto): Promise<MediaListResponseDto> {
    return this.mediaService.findAll(query);
  }

  /**
   * Get media by ID
   */
  @Get(':id')
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles('ADMIN', 'EDITOR')
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Get media by ID' })
  @ApiParam({ name: 'id', description: 'Media ID' })
  @ApiResponse({
    status: 200,
    description: 'Media found',
    type: MediaResponseDto,
  })
  @ApiResponse({ status: 404, description: 'Media not found' })
  async findOne(@Param('id') id: string): Promise<MediaResponseDto> {
    return this.mediaService.findOne(id);
  }

  /**
   * Trigger conversion for a single media
   */
  @Post(':id/convert')
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles('ADMIN', 'EDITOR')
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Trigger image conversion for a media' })
  @ApiParam({ name: 'id', description: 'Media ID' })
  @ApiResponse({
    status: 200,
    description: 'Conversion queued',
    type: ConversionJobResponseDto,
  })
  async convert(@Param('id') id: string): Promise<ConversionJobResponseDto> {
    return this.mediaService.queueForConversion(id);
  }

  /**
   * Bulk convert multiple media
   */
  @Post('bulk-convert')
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles('ADMIN', 'EDITOR')
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Bulk convert multiple images' })
  @ApiResponse({
    status: 200,
    description: 'Bulk conversion queued',
    type: ConversionJobResponseDto,
  })
  async bulkConvert(@Body() bulkDto: BulkConvertDto): Promise<ConversionJobResponseDto> {
    return this.mediaService.bulkConvert(bulkDto);
  }

  /**
   * Update media metadata
   */
  @Put(':id')
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles('ADMIN', 'EDITOR')
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Update media metadata' })
  @ApiParam({ name: 'id', description: 'Media ID' })
  @ApiResponse({
    status: 200,
    description: 'Media updated',
    type: MediaResponseDto,
  })
  async update(
    @Param('id') id: string,
    @Body() updateDto: Partial<CreateMediaDto>,
  ): Promise<MediaResponseDto> {
    return this.mediaService.update(id, updateDto);
  }

  /**
   * Delete media
   */
  @Delete(':id')
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles('ADMIN')
  @ApiBearerAuth()
  @HttpCode(HttpStatus.NO_CONTENT)
  @ApiOperation({ summary: 'Delete media' })
  @ApiParam({ name: 'id', description: 'Media ID' })
  @ApiResponse({ status: 204, description: 'Media deleted' })
  @ApiResponse({ status: 400, description: 'Media is in use' })
  async remove(@Param('id') id: string): Promise<void> {
    return this.mediaService.remove(id);
  }
}
