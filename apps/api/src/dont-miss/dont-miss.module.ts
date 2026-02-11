import { Module } from '@nestjs/common';
import { DontMissService } from './dont-miss.service';
import { DontMissController } from './dont-miss.controller';
import { PrismaModule } from '../prisma/prisma.module';

@Module({
  imports: [PrismaModule],
  controllers: [DontMissController],
  providers: [DontMissService],
  exports: [DontMissService],
})
export class DontMissModule {}
