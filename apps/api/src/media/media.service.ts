import {
  Injectable,
  NotFoundException,
  BadRequestException,
} from '@nestjs/common';
import { InjectQueue } from '@nestjs/bull';
import { Queue } from 'bull';
import { PrismaService } from '../prisma/prisma.service';
import {
  CreateMediaDto,
  MediaConversionDto,
  BulkConvertDto,
  QueryMediaDto,
  ScanUnconvertedDto,
  MediaResponseDto,
  MediaListResponseDto,
  ConversionStatsDto,
  ConversionJobResponseDto,
} from './dto';
import { Prisma, ConversionStatus } from '@prisma/client';

@Injectable()
export class MediaService {
  constructor(
    private readonly prisma: PrismaService,
    @InjectQueue('image-conversion') private readonly conversionQueue: Queue,
  ) {}

  /**
   * Create a new media record and queue for conversion
   */
  async create(createDto: CreateMediaDto, userId?: string): Promise<MediaResponseDto> {
    const media = await this.prisma.media.create({
      data: {
        ...createDto,
        createdBy: userId,
        conversionStatus: ConversionStatus.PENDING,
      },
    });

    // Auto-queue for conversion if it's an image
    if (createDto.mimeType.startsWith('image/')) {
      await this.queueForConversion(media.id);
    } else {
      // Mark non-images as skipped
      await this.prisma.media.update({
        where: { id: media.id },
        data: { conversionStatus: ConversionStatus.SKIPPED },
      });
    }

    return this.mapToResponseDto(media);
  }

  /**
   * Find all media with pagination and filtering
   */
  async findAll(query: QueryMediaDto): Promise<MediaListResponseDto> {
    const { search, mimeType, conversionStatus, isConverted, page = 1, limit = 20 } = query;

    const where: Prisma.MediaWhereInput = {};

    if (search) {
      where.filename = { contains: search, mode: 'insensitive' };
    }

    if (mimeType) {
      where.mimeType = { contains: mimeType };
    }

    if (conversionStatus) {
      where.conversionStatus = conversionStatus;
    }

    if (isConverted !== undefined) {
      where.isConverted = isConverted;
    }

    const skip = (page - 1) * limit;

    const [items, total] = await Promise.all([
      this.prisma.media.findMany({
        where,
        skip,
        take: limit,
        orderBy: { createdAt: 'desc' },
      }),
      this.prisma.media.count({ where }),
    ]);

    return {
      items: items.map(this.mapToResponseDto),
      total,
      page,
      limit,
      totalPages: Math.ceil(total / limit),
    };
  }

  /**
   * Find media by ID
   */
  async findOne(id: string): Promise<MediaResponseDto> {
    const media = await this.prisma.media.findUnique({
      where: { id },
      include: {
        _count: {
          select: { usedBy: true },
        },
      },
    });

    if (!media) {
      throw new NotFoundException(`Media with ID '${id}' not found`);
    }

    return this.mapToResponseDto(media);
  }

  /**
   * Update media metadata
   */
  async update(id: string, updateDto: Partial<CreateMediaDto>): Promise<MediaResponseDto> {
    const existing = await this.prisma.media.findUnique({
      where: { id },
    });

    if (!existing) {
      throw new NotFoundException(`Media with ID '${id}' not found`);
    }

    const media = await this.prisma.media.update({
      where: { id },
      data: updateDto,
    });

    return this.mapToResponseDto(media);
  }

  /**
   * Delete media
   */
  async remove(id: string): Promise<void> {
    const existing = await this.prisma.media.findUnique({
      where: { id },
      include: {
        _count: {
          select: { usedBy: true },
        },
      },
    });

    if (!existing) {
      throw new NotFoundException(`Media with ID '${id}' not found`);
    }

    // Check if media is in use
    const usageCount = (existing as unknown as { _count: { usedBy: number } })._count.usedBy;
    if (usageCount > 0) {
      throw new BadRequestException(
        `Cannot delete media '${existing.filename}' because it is used by ${usageCount} product(s).`,
      );
    }

    await this.prisma.media.delete({
      where: { id },
    });

    // TODO: Also delete files from storage
  }

  /**
   * Queue a single media for conversion
   */
  async queueForConversion(
    mediaId: string,
    options?: MediaConversionDto,
  ): Promise<ConversionJobResponseDto> {
    const media = await this.prisma.media.findUnique({
      where: { id: mediaId },
    });

    if (!media) {
      throw new NotFoundException(`Media with ID '${mediaId}' not found`);
    }

    if (!media.mimeType.startsWith('image/')) {
      throw new BadRequestException(`Media '${media.filename}' is not an image`);
    }

    const job = await this.conversionQueue.add('convert', {
      mediaId,
      ...options,
    });

    return {
      jobId: job.id.toString(),
      status: 'queued',
      queued: 1,
    };
  }

  /**
   * Bulk convert multiple media files
   */
  async bulkConvert(bulkDto: BulkConvertDto): Promise<ConversionJobResponseDto> {
    const { mediaIds, options } = bulkDto;

    // Validate all media exist and are images
    const media = await this.prisma.media.findMany({
      where: {
        id: { in: mediaIds },
        mimeType: { startsWith: 'image/' },
      },
    });

    if (media.length === 0) {
      throw new BadRequestException('No valid images found for conversion');
    }

    // Queue all for conversion
    const jobs = await Promise.all(
      media.map((m) =>
        this.conversionQueue.add('convert', {
          mediaId: m.id,
          ...options,
        }),
      ),
    );

    return {
      jobId: jobs[0].id.toString(), // Return first job ID as reference
      status: 'queued',
      queued: jobs.length,
    };
  }

  /**
   * Scan for unconverted images
   */
  async scanUnconverted(query: ScanUnconvertedDto): Promise<MediaResponseDto[]> {
    const { missingWebpOnly = false, missingAvifOnly = false } = query;

    const where: Prisma.MediaWhereInput = {
      mimeType: { startsWith: 'image/' },
      OR: [
        { isConverted: false },
        { conversionStatus: ConversionStatus.FAILED },
      ],
    };

    const media = await this.prisma.media.findMany({
      where,
      orderBy: { fileSize: 'desc' },
    });

    // Additional filtering based on missing formats
    return media
      .filter((m) => {
        const variants = (m.variants as Record<string, unknown>) || {};
        if (missingWebpOnly && 'webp' in variants) return false;
        if (missingAvifOnly && 'avif' in variants) return false;
        return true;
      })
      .map(this.mapToResponseDto);
  }

  /**
   * Get conversion statistics
   */
  async getConversionStats(): Promise<ConversionStatsDto> {
    const [
      totalImages,
      fullyOptimized,
      needsConversion,
    ] = await Promise.all([
      this.prisma.media.count({
        where: { mimeType: { startsWith: 'image/' } },
      }),
      this.prisma.media.count({
        where: {
          mimeType: { startsWith: 'image/' },
          isConverted: true,
          conversionStatus: ConversionStatus.COMPLETED,
        },
      }),
      this.prisma.media.count({
        where: {
          mimeType: { startsWith: 'image/' },
          OR: [
            { isConverted: false },
            { conversionStatus: ConversionStatus.FAILED },
            { conversionStatus: ConversionStatus.PENDING },
          ],
        },
      }),
    ]);

    // Calculate storage saved (this is a simplified calculation)
    const convertedMedia = await this.prisma.media.findMany({
      where: { isConverted: true },
      select: { fileSize: true, variants: true },
    });

    let storageSaved = 0;
    for (const media of convertedMedia) {
      const variants = (media.variants as Record<string, { fileSize: number }>) || {};
      const variantSizes = Object.values(variants).map((v) => v.fileSize);
      if (variantSizes.length > 0) {
        const avgVariantSize = variantSizes.reduce((a, b) => a + b, 0) / variantSizes.length;
        storageSaved += Math.max(0, media.fileSize - avgVariantSize);
      }
    }

    return {
      totalImages,
      fullyOptimized,
      needsConversion,
      storageSaved,
      storageSavedFormatted: this.formatBytes(storageSaved),
      optimizationPercentage: totalImages > 0 ? Math.round((fullyOptimized / totalImages) * 100) : 0,
    };
  }

  /**
   * Get queue status
   */
  async getQueueStatus(): Promise<{ active: number; waiting: number; completed: number; failed: number }> {
    const [active, waiting, completed, failed] = await Promise.all([
      this.conversionQueue.getActiveCount(),
      this.conversionQueue.getWaitingCount(),
      this.conversionQueue.getCompletedCount(),
      this.conversionQueue.getFailedCount(),
    ]);

    return { active, waiting, completed, failed };
  }

  /**
   * Map database media to response DTO
   */
  private mapToResponseDto(media: unknown): MediaResponseDto {
    const m = media as {
      id: string;
      filename: string;
      originalUrl: string;
      alt: string | null;
      mimeType: string;
      fileSize: number;
      width: number | null;
      height: number | null;
      conversionStatus: ConversionStatus;
      isConverted: boolean;
      variants: Record<string, unknown> | null;
      thumbnailUrl: string | null;
      mediumUrl: string | null;
      largeUrl: string | null;
      createdAt: Date;
      updatedAt: Date;
    };

    return {
      id: m.id,
      filename: m.filename,
      originalUrl: m.originalUrl,
      alt: m.alt,
      mimeType: m.mimeType,
      fileSize: m.fileSize,
      width: m.width,
      height: m.height,
      conversionStatus: m.conversionStatus,
      isConverted: m.isConverted,
      variants: m.variants as unknown as MediaResponseDto['variants'],
      thumbnailUrl: m.thumbnailUrl,
      mediumUrl: m.mediumUrl,
      largeUrl: m.largeUrl,
      createdAt: m.createdAt,
      updatedAt: m.updatedAt,
    };
  }

  /**
   * Format bytes to human readable string
   */
  private formatBytes(bytes: number): string {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  }
}
