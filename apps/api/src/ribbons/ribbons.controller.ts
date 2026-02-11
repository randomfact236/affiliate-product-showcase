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
  ParseIntPipe,
  DefaultValuePipe,
} from '@nestjs/common';
import { ApiTags, ApiOperation, ApiResponse, ApiBearerAuth, ApiParam } from '@nestjs/swagger';
import { RibbonsService } from './ribbons.service';
import {
  CreateRibbonDto,
  UpdateRibbonDto,
  QueryRibbonsDto,
  RibbonResponseDto,
  RibbonListResponseDto,
} from './dto';
import { JwtAuthGuard } from '../auth/guards/jwt-auth.guard';
import { RolesGuard } from '../auth/guards/roles.guard';
import { Roles } from '../auth/decorators/roles.decorator';
import { CurrentUser } from '../auth/decorators/current-user.decorator';

@ApiTags('Ribbons')
@Controller('ribbons')
export class RibbonsController {
  constructor(private readonly ribbonsService: RibbonsService) {}

  /**
   * Public endpoint to get all active ribbons
   */
  @Get('active')
  @ApiOperation({ summary: 'Get all active ribbons (public)' })
  @ApiResponse({
    status: 200,
    description: 'List of active ribbons',
    type: [RibbonResponseDto],
  })
  async findActive(): Promise<RibbonResponseDto[]> {
    return this.ribbonsService.findActive();
  }

  /**
   * Admin: Create a new ribbon
   */
  @Post()
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles('ADMIN', 'EDITOR')
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Create a new ribbon' })
  @ApiResponse({
    status: 201,
    description: 'Ribbon created successfully',
    type: RibbonResponseDto,
  })
  @ApiResponse({ status: 409, description: 'Ribbon with this name already exists' })
  async create(
    @Body() createDto: CreateRibbonDto,
    @CurrentUser('userId') userId: string,
  ): Promise<RibbonResponseDto> {
    return this.ribbonsService.create(createDto, userId);
  }

  /**
   * Admin: Get all ribbons with filtering
   */
  @Get()
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles('ADMIN', 'EDITOR')
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Get all ribbons with pagination and filtering' })
  @ApiResponse({
    status: 200,
    description: 'Paginated list of ribbons',
    type: RibbonListResponseDto,
  })
  async findAll(
    @Query() query: QueryRibbonsDto,
  ): Promise<RibbonListResponseDto> {
    return this.ribbonsService.findAll(query);
  }

  /**
   * Admin: Get ribbon by ID
   */
  @Get(':id')
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles('ADMIN', 'EDITOR')
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Get a ribbon by ID' })
  @ApiParam({ name: 'id', description: 'Ribbon ID' })
  @ApiResponse({
    status: 200,
    description: 'Ribbon found',
    type: RibbonResponseDto,
  })
  @ApiResponse({ status: 404, description: 'Ribbon not found' })
  async findOne(@Param('id') id: string): Promise<RibbonResponseDto> {
    return this.ribbonsService.findOne(id);
  }

  /**
   * Admin: Update ribbon
   */
  @Put(':id')
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles('ADMIN', 'EDITOR')
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Update a ribbon' })
  @ApiParam({ name: 'id', description: 'Ribbon ID' })
  @ApiResponse({
    status: 200,
    description: 'Ribbon updated successfully',
    type: RibbonResponseDto,
  })
  @ApiResponse({ status: 404, description: 'Ribbon not found' })
  @ApiResponse({ status: 409, description: 'Ribbon with this name already exists' })
  async update(
    @Param('id') id: string,
    @Body() updateDto: UpdateRibbonDto,
    @CurrentUser('userId') userId: string,
  ): Promise<RibbonResponseDto> {
    return this.ribbonsService.update(id, updateDto, userId);
  }

  /**
   * Admin: Toggle ribbon active status
   */
  @Patch(':id/toggle-active')
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles('ADMIN', 'EDITOR')
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Toggle ribbon active status' })
  @ApiParam({ name: 'id', description: 'Ribbon ID' })
  @ApiResponse({
    status: 200,
    description: 'Ribbon status toggled successfully',
    type: RibbonResponseDto,
  })
  @ApiResponse({ status: 404, description: 'Ribbon not found' })
  async toggleActive(
    @Param('id') id: string,
    @CurrentUser('userId') userId: string,
  ): Promise<RibbonResponseDto> {
    return this.ribbonsService.toggleActive(id, userId);
  }

  /**
   * Admin: Delete ribbon
   */
  @Delete(':id')
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles('ADMIN')
  @ApiBearerAuth()
  @HttpCode(HttpStatus.NO_CONTENT)
  @ApiOperation({ summary: 'Delete a ribbon' })
  @ApiParam({ name: 'id', description: 'Ribbon ID' })
  @ApiResponse({ status: 204, description: 'Ribbon deleted successfully' })
  @ApiResponse({ status: 404, description: 'Ribbon not found' })
  @ApiResponse({
    status: 400,
    description: 'Cannot delete ribbon that is assigned to products',
  })
  async remove(@Param('id') id: string): Promise<void> {
    return this.ribbonsService.remove(id);
  }
}
