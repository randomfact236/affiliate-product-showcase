import { Process, Processor } from '@nestjs/bull';
import { Logger } from '@nestjs/common';
import { Job } from 'bull';
import sharp from 'sharp';
import { PrismaService } from '../../prisma/prisma.service';
import { ConversionStatus, Prisma } from '@prisma/client';

interface ConversionJobData {
  mediaId: string;
  generateWebp?: boolean;
  generateAvif?: boolean;
  generateVariants?: boolean;
  webpQuality?: number;
  avifQuality?: number;
}

interface VariantInfo {
  url: string;
  fileSize: number;
}

@Processor('image-conversion')
export class ImageConversionProcessor {
  private readonly logger = new Logger(ImageConversionProcessor.name);

  constructor(private readonly prisma: PrismaService) {}

  @Process('convert')
  async handleConversion(job: Job<ConversionJobData>) {
    const { mediaId, generateWebp = true, generateAvif = true, generateVariants = true, webpQuality = 80, avifQuality = 70 } = job.data;

    this.logger.log(`Starting conversion for media ${mediaId}`);

    try {
      // Update status to PROCESSING
      await this.prisma.media.update({
        where: { id: mediaId },
        data: { conversionStatus: ConversionStatus.PROCESSING },
      });

      // Get media record
      const media = await this.prisma.media.findUnique({
        where: { id: mediaId },
      });

      if (!media) {
        throw new Error(`Media ${mediaId} not found`);
      }

      // Skip non-image files
      if (!media.mimeType.startsWith('image/')) {
        await this.prisma.media.update({
          where: { id: mediaId },
          data: { conversionStatus: ConversionStatus.SKIPPED },
        });
        return { status: 'skipped', reason: 'not-an-image' };
      }

      // TODO: Download original image from storage
      // For now, we'll assume the file is accessible at originalUrl
      const imageBuffer = await this.downloadImage(media.originalUrl);

      const variants: Record<string, VariantInfo> = {};
      let thumbnailUrl: string | null = null;
      let mediumUrl: string | null = null;
      let largeUrl: string | null = null;

      // Generate WebP
      if (generateWebp) {
        this.logger.debug(`Generating WebP for ${mediaId}`);
        const webpBuffer = await sharp(imageBuffer)
          .webp({ quality: webpQuality })
          .toBuffer();

        const webpUrl = await this.uploadVariant(media.id, webpBuffer, 'webp');
        variants['webp'] = {
          url: webpUrl,
          fileSize: webpBuffer.length,
        };

        // Generate size variants from WebP
        if (generateVariants) {
          thumbnailUrl = await this.generateSizeVariant(imageBuffer, media.id, 300, webpQuality, 'webp');
          mediumUrl = await this.generateSizeVariant(imageBuffer, media.id, 600, webpQuality, 'webp');
          largeUrl = await this.generateSizeVariant(imageBuffer, media.id, 1200, webpQuality, 'webp');
        }
      }

      // Generate AVIF
      if (generateAvif) {
        this.logger.debug(`Generating AVIF for ${mediaId}`);
        const avifBuffer = await sharp(imageBuffer)
          .avif({ quality: avifQuality })
          .toBuffer();

        const avifUrl = await this.uploadVariant(media.id, avifBuffer, 'avif');
        variants['avif'] = {
          url: avifUrl,
          fileSize: avifBuffer.length,
        };
      }

      // Update media record
      await this.prisma.media.update({
        where: { id: mediaId },
        data: {
          isConverted: true,
          conversionStatus: ConversionStatus.COMPLETED,
          variants: JSON.parse(JSON.stringify(variants)) as Prisma.InputJsonValue,
          thumbnailUrl,
          mediumUrl,
          largeUrl,
        },
      });

      this.logger.log(`Conversion completed for media ${mediaId}`);

      return {
        status: 'completed',
        variants: Object.keys(variants),
        savings: this.calculateSavings(media.fileSize, variants),
      };
    } catch (error) {
      this.logger.error(`Conversion failed for media ${mediaId}: ${error.message}`, error.stack);

      await this.prisma.media.update({
        where: { id: mediaId },
        data: { conversionStatus: ConversionStatus.FAILED },
      });

      throw error;
    }
  }

  private async downloadImage(url: string): Promise<Buffer> {
    // TODO: Implement actual download from MinIO/S3
    // For now, this is a placeholder
    // In production, use the storage service to download the file
    throw new Error('Download not implemented - use StorageService');
  }

  private async uploadVariant(mediaId: string, buffer: Buffer, format: string): Promise<string> {
    // TODO: Implement actual upload to MinIO/S3
    // For now, this is a placeholder returning a mock URL
    // In production, use the storage service to upload the file
    return `/uploads/${mediaId}.${format}`;
  }

  private async generateSizeVariant(
    imageBuffer: Buffer,
    mediaId: string,
    width: number,
    quality: number,
    format: 'webp' | 'avif' = 'webp',
  ): Promise<string> {
    const resized = await sharp(imageBuffer)
      .resize(width, null, { withoutEnlargement: true })
      .toFormat(format, { quality })
      .toBuffer();

    return this.uploadVariant(mediaId, resized, `${width}.${format}`);
  }

  private calculateSavings(originalSize: number, variants: Record<string, VariantInfo>): number {
    const totalVariantSize = Object.values(variants).reduce((sum, v) => sum + v.fileSize, 0);
    // Average size of variants compared to original
    const avgVariantSize = totalVariantSize / Object.keys(variants).length;
    return Math.max(0, originalSize - avgVariantSize);
  }
}
