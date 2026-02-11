import { Module } from '@nestjs/common';
import { BullModule } from '@nestjs/bull';
import { MediaController } from './media.controller';
import { MediaService } from './media.service';
import { ImageConversionProcessor } from './processors/image-conversion.processor';

@Module({
  imports: [
    BullModule.registerQueue({
      name: 'image-conversion',
      defaultJobOptions: {
        attempts: 3,
        backoff: {
          type: 'exponential',
          delay: 5000,
        },
        removeOnComplete: 100,
        removeOnFail: 50,
      },
    }),
  ],
  controllers: [MediaController],
  providers: [MediaService, ImageConversionProcessor],
  exports: [MediaService],
})
export class MediaModule {}
