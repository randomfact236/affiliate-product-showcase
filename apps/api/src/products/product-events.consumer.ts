import { Injectable, Logger } from "@nestjs/common";
import { RabbitSubscribe } from "@golevelup/nestjs-rabbitmq";
import { PrismaService } from "../prisma/prisma.service";

@Injectable()
export class ProductEventsConsumer {
  private readonly logger = new Logger(ProductEventsConsumer.name);

  constructor(private readonly prisma: PrismaService) {}

  @RabbitSubscribe({
    exchange: "affiliate_events",
    routingKey: "product.viewed",
    queue: "product_views_queue",
  })
  async handleProductView(msg: { productId: string }) {
    this.logger.log(`Processing view for product: ${msg.productId}`);
    try {
      await this.prisma.product.update({
        where: { id: msg.productId },
        data: { viewCount: { increment: 1 } },
      });
    } catch (error) {
      this.logger.error(
        `Failed to increment view count for ${msg.productId}`,
        error.stack,
      );
    }
  }
}
