import { Controller, Get, Query, UseGuards } from '@nestjs/common';
import { ApiTags, ApiOperation, ApiBearerAuth } from '@nestjs/swagger';
import { JwtAuthGuard } from '../auth/guards/jwt-auth.guard';
import { AnalyticsDevicesService } from './services/analytics-devices.service';

@ApiTags('Analytics Devices')
@Controller('analytics/devices')
@UseGuards(JwtAuthGuard)
@ApiBearerAuth()
export class AnalyticsDevicesController {
  constructor(private readonly devicesService: AnalyticsDevicesService) {}

  @Get('breakdown')
  @ApiOperation({ summary: 'Get device type breakdown' })
  async getDeviceBreakdown(
    @Query('startDate') startDate?: string,
    @Query('endDate') endDate?: string,
  ) {
    return this.devicesService.getDeviceBreakdown(startDate, endDate);
  }

  @Get('browsers')
  @ApiOperation({ summary: 'Get browser distribution' })
  async getBrowserDistribution(
    @Query('startDate') startDate?: string,
    @Query('endDate') endDate?: string,
  ) {
    return this.devicesService.getBrowserDistribution(startDate, endDate);
  }

  @Get('operating-systems')
  @ApiOperation({ summary: 'Get OS distribution' })
  async getOSDistribution(
    @Query('startDate') startDate?: string,
    @Query('endDate') endDate?: string,
  ) {
    return this.devicesService.getOSDistribution(startDate, endDate);
  }

  @Get('screen-resolutions')
  @ApiOperation({ summary: 'Get screen resolution distribution' })
  async getScreenResolutions(
    @Query('startDate') startDate?: string,
    @Query('endDate') endDate?: string,
  ) {
    return this.devicesService.getScreenResolutions(startDate, endDate);
  }
}
