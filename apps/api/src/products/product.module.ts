import { Module } from "@nestjs/common";
import { ProductService } from "./product.service";
import { ProductController } from "./product.controller";
import { ProductEventsConsumer } from "./product-events.consumer";

@Module({
  controllers: [ProductController],
  providers: [ProductService, ProductEventsConsumer],
  exports: [ProductService],
})
export class ProductModule {}
