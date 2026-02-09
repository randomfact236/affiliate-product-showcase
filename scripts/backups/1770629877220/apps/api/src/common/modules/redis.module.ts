import { Module, Global, Logger } from '@nestjs/common';
import { ConfigModule, ConfigService } from '@nestjs/config';
import Redis from 'ioredis';
import { REDIS_CLIENT } from '../constants/injection-tokens';

@Global()
@Module({
    imports: [ConfigModule],
    providers: [
        {
            provide: REDIS_CLIENT,
            useFactory: (configService: ConfigService) => {
                const logger = new Logger('RedisModule');
                const nodeEnv = configService.get('app.nodeEnv');
                
                const host = configService.get('REDIS_HOST') || 'localhost';
                const port = parseInt(configService.get('REDIS_PORT') || '6379', 10);
                const password = configService.get('REDIS_PASSWORD');
                const db = parseInt(configService.get('REDIS_DB') || '0', 10);
                
                // In production, password is required
                if (nodeEnv === 'production' && !password) {
                    throw new Error(
                        'FATAL: REDIS_PASSWORD is required in production. ' +
                        'Redis must be secured with authentication.'
                    );
                }

                const redisOptions: Redis.RedisOptions = {
                    host,
                    port,
                    db,
                    password: password || undefined,
                    retryStrategy: (times) => {
                        const delay = Math.min(times * 50, 2000);
                        logger.warn(`Redis connection retry ${times}, waiting ${delay}ms`);
                        return delay;
                    },
                    maxRetriesPerRequest: 3,
                    enableReadyCheck: true,
                    lazyConnect: true, // Don't connect immediately, let NestJS handle lifecycle
                };

                // Enable TLS for production connections
                if (nodeEnv === 'production' && configService.get('REDIS_TLS_ENABLED') === 'true') {
                    redisOptions.tls = {
                        rejectUnauthorized: true,
                    };
                }

                const client = new Redis(redisOptions);

                // Connection event handlers
                client.on('connect', () => {
                    logger.log(`Redis connected to ${host}:${port}`);
                });

                client.on('error', (err) => {
                    logger.error('Redis connection error:', err.message);
                });

                client.on('reconnecting', () => {
                    logger.warn('Redis reconnecting...');
                });

                return client;
            },
            inject: [ConfigService],
        },
    ],
    exports: [REDIS_CLIENT],
})
export class RedisModule { }
