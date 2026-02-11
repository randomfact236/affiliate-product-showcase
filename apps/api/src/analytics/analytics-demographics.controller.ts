import { Controller, Get, Query, UseGuards } from '@nestjs/common';
import { ApiTags, ApiOperation, ApiBearerAuth } from '@nestjs/swagger';
import { JwtAuthGuard } from '../auth/guards/jwt-auth.guard';
import { AnalyticsDemographicsService } from './services/analytics-demographics.service';

@ApiTags('Analytics Demographics')
@Controller('analytics/demographics')
@UseGuards(JwtAuthGuard)
@ApiBearerAuth()
export class AnalyticsDemographicsController {
  constructor(private readonly demographicsService: AnalyticsDemographicsService) {}

  @Get('gender-split')
  @ApiOperation({ summary: 'Get gender distribution' })
  async getGenderSplit(
    @Query('startDate') startDate?: string,
    @Query('endDate') endDate?: string,
  ) {
    return this.demographicsService.getGenderSplit(startDate, endDate);
  }

  @Get('age-distribution')
  @ApiOperation({ summary: 'Get age group distribution' })
  async getAgeDistribution(
    @Query('startDate') startDate?: string,
    @Query('endDate') endDate?: string,
  ) {
    return this.demographicsService.getAgeDistribution(startDate, endDate);
  }

  @Get('interests')
  @ApiOperation({ summary: 'Get top interests/affinity categories' })
  async getTopInterests(
    @Query('startDate') startDate?: string,
    @Query('endDate') endDate?: string,
    @Query('limit') limit = 10,
  ) {
    return this.demographicsService.getTopInterests(startDate, endDate, +limit);
  }

  @Get('new-vs-returning')
  @ApiOperation({ summary: 'Get new vs returning visitors' })
  async getNewVsReturning(
    @Query('startDate') startDate?: string,
    @Query('endDate') endDate?: string,
  ) {
    return this.demographicsService.getNewVsReturning(startDate, endDate);
  }

  @Get('languages')
  @ApiOperation({ summary: 'Get language distribution' })
  async getLanguages(
    @Query('startDate') startDate?: string,
    @Query('endDate') endDate?: string,
  ) {
    return this.demographicsService.getLanguages(startDate, endDate);
  }
}
