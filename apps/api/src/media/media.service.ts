import { Injectable, Logger, BadRequestException } from "@nestjs/common";
import { ConfigService } from "@nestjs/config";
import { S3Client, PutObjectCommand } from "@aws-sdk/client-s3";
import { v4 as uuidv4 } from "uuid";

// Magic numbers for file type validation
const FILE_SIGNATURES: Record<string, string[]> = {
  "image/jpeg": ["FFD8FF"], // JPEG
  "image/png": ["89504E47"], // PNG
  "image/webp": ["52494646"], // WEBP (RIFF header)
  "image/gif": ["47494638"], // GIF
};

const ALLOWED_MIME_TYPES = Object.keys(FILE_SIGNATURES);
const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB

// Custom file interface to avoid Express.Multer dependency issues
export interface MulterFile {
  fieldname: string;
  originalname: string;
  encoding: string;
  mimetype: string;
  size: number;
  buffer: Buffer;
}

export interface FileUploadResult {
  url: string;
  key: string;
  filename: string;
  mimetype: string;
  size: number;
}

@Injectable()
export class MediaService {
  private readonly logger = new Logger(MediaService.name);
  private readonly s3Client: S3Client;
  private readonly bucketName: string;
  private readonly publicUrlBase: string;

  constructor(private readonly configService: ConfigService) {
    this.bucketName =
      this.configService.get("STORAGE_BUCKET") || "affiliate-media";

    const endpoint = this.configService.get("STORAGE_ENDPOINT") || "localhost";
    const port = this.configService.get("STORAGE_PORT") || "9000";
    const accessKey = this.configService.get("STORAGE_ACCESS_KEY");
    const secretKey = this.configService.get("STORAGE_SECRET_KEY");
    const region = this.configService.get("STORAGE_REGION") || "us-east-1";

    // Fail fast if required credentials are missing in production
    const nodeEnv = this.configService.get("NODE_ENV") || "development";
    if (nodeEnv === "production") {
      if (!accessKey || !secretKey) {
        throw new Error(
          "FATAL: STORAGE_ACCESS_KEY and STORAGE_SECRET_KEY are required in production",
        );
      }
    }

    this.publicUrlBase =
      this.configService.get("STORAGE_PUBLIC_URL") ||
      `http://${endpoint}:${port}/${this.bucketName}`;

    this.s3Client = new S3Client({
      region,
      endpoint: `http://${endpoint}:${port}`,
      forcePathStyle: true,
      credentials: {
        accessKeyId: accessKey,
        secretAccessKey: secretKey,
      },
    });
  }

  async uploadFile(file: MulterFile): Promise<FileUploadResult> {
    // Validate file size
    if (file.size > MAX_FILE_SIZE) {
      throw new BadRequestException(
        `File size exceeds ${MAX_FILE_SIZE / 1024 / 1024}MB limit`,
      );
    }

    // Validate file content using magic numbers (not just mimetype)
    const detectedType = await this.detectFileType(file.buffer);

    if (!detectedType) {
      throw new BadRequestException(
        "Invalid file content. File type could not be verified.",
      );
    }

    if (!ALLOWED_MIME_TYPES.includes(detectedType)) {
      throw new BadRequestException(
        `File type not allowed. Allowed types: ${ALLOWED_MIME_TYPES.join(", ")}`,
      );
    }

    // Validate mimetype matches content (prevents mime spoofing)
    if (
      file.mimetype !== detectedType &&
      !this.isMimeTypeCompatible(file.mimetype, detectedType)
    ) {
      throw new BadRequestException(
        "File mimetype does not match file content. Possible file spoofing detected.",
      );
    }

    // Generate secure filename with validated extension
    const extension = this.getExtensionFromMimeType(detectedType);
    const fileName = `${uuidv4()}.${extension}`;
    const key = `uploads/${fileName}`;

    try {
      await this.s3Client.send(
        new PutObjectCommand({
          Bucket: this.bucketName,
          Key: key,
          Body: file.buffer,
          ContentType: detectedType,
          // Don't set ACL - use bucket policy instead for better security
        }),
      );

      const publicUrl = `${this.publicUrlBase}/${key}`;
      this.logger.log(`File uploaded successfully: ${publicUrl}`);

      return {
        url: publicUrl,
        key,
        filename: fileName,
        mimetype: detectedType,
        size: file.size,
      };
    } catch (error) {
      this.logger.error(`Failed to upload file: ${(error as Error).message}`);
      throw new BadRequestException("Failed to upload file. Please try again.");
    }
  }

  /**
   * Detect file type by examining magic numbers in the file buffer
   */
  private async detectFileType(buffer: Buffer): Promise<string | null> {
    // Get hex signature from first bytes
    const hexSignature = buffer.slice(0, 8).toString("hex").toUpperCase();

    for (const [mimeType, signatures] of Object.entries(FILE_SIGNATURES)) {
      for (const signature of signatures) {
        if (hexSignature.startsWith(signature)) {
          return mimeType;
        }
      }
    }

    return null;
  }

  /**
   * Check if claimed mimetype is compatible with detected type
   */
  private isMimeTypeCompatible(claimed: string, detected: string): boolean {
    // Allow some common aliases
    const compatibleMap: Record<string, string[]> = {
      "image/jpeg": ["image/jpg"],
      "image/jpg": ["image/jpeg"],
    };

    return compatibleMap[detected]?.includes(claimed) || false;
  }

  /**
   * Get file extension from mimetype
   */
  private getExtensionFromMimeType(mimeType: string): string {
    const extMap: Record<string, string> = {
      "image/jpeg": "jpg",
      "image/png": "png",
      "image/webp": "webp",
      "image/gif": "gif",
    };
    return extMap[mimeType] || "bin";
  }
}
