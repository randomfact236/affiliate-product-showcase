import { Module, Global } from "@nestjs/common";
import { RabbitMQModule } from "@golevelup/nestjs-rabbitmq";
import { ConfigModule, ConfigService } from "@nestjs/config";

@Global()
@Module({
  imports: [
    RabbitMQModule.forRootAsync(RabbitMQModule, {
      imports: [ConfigModule],
      useFactory: (configService: ConfigService) => ({
        exchanges: [
          {
            name: "affiliate_events",
            type: "topic",
          },
        ],
        uri: `amqp://${configService.get("RABBIT_USER", "guest")}:${configService.get("RABBIT_PASSWORD", "guest")}@${configService.get("RABBIT_HOST", "localhost")}:${configService.get("RABBIT_PORT", 5672)}`,
        connectionInitOptions: { wait: false },
      }),
      inject: [ConfigService],
    }),
  ],
  exports: [RabbitMQModule],
})
export class QueueModule {}
