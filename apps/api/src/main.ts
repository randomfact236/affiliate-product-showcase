import { NestFactory } from '@nestjs/core';
import { ValidationPipe } from '@nestjs/common';
import { SwaggerModule, DocumentBuilder } from '@nestjs/swagger';
import { AppModule } from './app.module';

async function bootstrap() {
  const app = await NestFactory.create(AppModule);
  app.setGlobalPrefix('api/v1');
  
  // Validation pipe
  app.useGlobalPipes(new ValidationPipe({
    whitelist: true,
    forbidNonWhitelisted: true,
    transform: true,
  }));
  
  // Swagger documentation
  const config = new DocumentBuilder()
    .setTitle('Affiliate Platform API')
    .setDescription('Enterprise Affiliate Marketing Platform API')
    .setVersion('1.0')
    .addBearerAuth()
    .build();
  const document = SwaggerModule.createDocument(app as any, config);
  SwaggerModule.setup('api/docs', app as any, document);
  
  const port = process.env.API_PORT || 3003;
  await app.listen(port);
  
  console.log(`🚀 API Server running on http://localhost:${port}`);
  console.log(`📚 API Docs available at http://localhost:${port}/api/docs`);
}
bootstrap();
