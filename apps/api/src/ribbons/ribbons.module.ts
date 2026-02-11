import { Module } from '@nestjs/common';
import { RibbonsController } from './ribbons.controller';
import { RibbonsService } from './ribbons.service';

@Module({
  controllers: [RibbonsController],
  providers: [RibbonsService],
  exports: [RibbonsService],
})
export class RibbonsModule {}
