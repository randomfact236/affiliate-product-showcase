import { Module } from "@nestjs/common";
import { AttributeService } from "./attribute.service";
import { AttributeController } from "./attribute.controller";
import { PrismaModule } from "../prisma/prisma.module";
import { RedisModule } from "../common/modules/redis.module";

@Module({
  imports: [PrismaModule, RedisModule],
  controllers: [AttributeController],
  providers: [AttributeService],
  exports: [AttributeService],
})
export class AttributeModule {}
