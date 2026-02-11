import { Module } from '@nestjs/common';
import { AnalyticsController } from './analytics.controller';
import { AnalyticsService } from './analytics.service';
import { AnalyticsDemographicsController } from './analytics-demographics.controller';
import { AnalyticsDevicesController } from './analytics-devices.controller';
import { AnalyticsContentController } from './analytics-content.controller';
import { AdvancedTrackingController } from './advanced-tracking.controller';
import { AnalyticsDemographicsService } from './services/analytics-demographics.service';
import { AnalyticsDevicesService } from './services/analytics-devices.service';
import { AnalyticsContentService } from './services/analytics-content.service';
import { AdvancedTrackingService } from './services/advanced-tracking.service';
import { PrismaModule } from '../prisma/prisma.module';
import { RedisModule } from '../common/modules/redis.module';

@Module({
  imports: [PrismaModule, RedisModule],
  controllers: [
    AnalyticsController,
    AnalyticsDemographicsController,
    AnalyticsDevicesController,
    AnalyticsContentController,
    AdvancedTrackingController,
  ],
  providers: [
    AnalyticsService,
    AnalyticsDemographicsService,
    AnalyticsDevicesService,
    AnalyticsContentService,
    AdvancedTrackingService,
  ],
  exports: [AnalyticsService],
})
export class AnalyticsModule {}
