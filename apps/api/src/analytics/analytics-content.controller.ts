import { Controller, Get, Query, UseGuards } from '@nestjs/common';
import { ApiTags, ApiOperation, ApiBearerAuth } from '@nestjs/swagger';
import { JwtAuthGuard } from '../auth/guards/jwt-auth.guard';
import { AnalyticsContentService } from './services/analytics-content.service';

@ApiTags('Analytics Content')
@Controller('analytics/content')
@UseGuards(JwtAuthGuard)
@ApiBearerAuth()
export class AnalyticsContentController {
  constructor(private readonly contentService: AnalyticsContentService) {}

  @Get('categories')
  @ApiOperation({ summary: 'Get category distribution' })
  async getCategories(
    @Query('startDate') startDate?: string,
    @Query('endDate') endDate?: string,
  ) {
    return this.contentService.getCategories(startDate, endDate);
  }

  @Get('tags')
  @ApiOperation({ summary: 'Get tag distribution' })
  async getTags(
    @Query('startDate') startDate?: string,
    @Query('endDate') endDate?: string,
    @Query('limit') limit = 20,
  ) {
    return this.contentService.getTags(startDate, endDate, +limit);
  }

  @Get('ribbons')
  @ApiOperation({ summary: 'Get ribbon type distribution' })
  async getRibbons(
    @Query('startDate') startDate?: string,
    @Query('endDate') endDate?: string,
  ) {
    return this.contentService.getRibbons(startDate, endDate);
  }

  @Get('placements')
  @ApiOperation({ summary: 'Get link placement performance' })
  async getPlacements(
    @Query('startDate') startDate?: string,
    @Query('endDate') endDate?: string,
  ) {
    return this.contentService.getPlacements(startDate, endDate);
  }

  @Get('landing-pages')
  @ApiOperation({ summary: 'Get top landing pages' })
  async getLandingPages(
    @Query('startDate') startDate?: string,
    @Query('endDate') endDate?: string,
    @Query('limit') limit = 10,
  ) {
    return this.contentService.getLandingPages(startDate, endDate, +limit);
  }

  @Get('exit-pages')
  @ApiOperation({ summary: 'Get top exit pages' })
  async getExitPages(
    @Query('startDate') startDate?: string,
    @Query('endDate') endDate?: string,
    @Query('limit') limit = 10,
  ) {
    return this.contentService.getExitPages(startDate, endDate, +limit);
  }
}
