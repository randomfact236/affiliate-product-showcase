import { Module } from '@nestjs/common';
import { ConfigModule } from '@nestjs/config';
import { AppController } from './app.controller';
import { AppService } from './app.service';
import { PrismaModule } from './prisma/prisma.module';
import { AuthModule } from './auth/auth.module';
import { ProductModule } from './products/product.module';
import { CategoryModule } from './categories/category.module';
import { TagModule } from './tags/tag.module';
import { AttributeModule } from './attributes/attribute.module';

@Module({
  imports: [
    ConfigModule.forRoot({ isGlobal: true }),
    PrismaModule,
    AuthModule,
    ProductModule,
    CategoryModule,
    TagModule,
    AttributeModule,
  ],
  controllers: [AppController],
  providers: [AppService],
})
export class AppModule {}
