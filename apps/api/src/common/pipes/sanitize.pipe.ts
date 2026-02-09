import {
  PipeTransform,
  Injectable,
  ArgumentMetadata,
  BadRequestException,
} from "@nestjs/common";

/**
 * Sanitizes HTML content to prevent XSS attacks.
 * This is a basic implementation - consider using DOMPurify for production.
 */
function sanitizeHtml(input: string): string {
  if (!input) return input;

  return (
    input
      // Remove script tags and their contents
      .replace(/<script[^>]*>[\s\S]*?<\/script>/gi, "")
      // Remove event handlers
      .replace(/\s*on\w+\s*=\s*"[^"]*"/gi, "")
      .replace(/\s*on\w+\s*=\s*'[^']*'/gi, "")
      .replace(/\s*on\w+\s*=\s*[^\s>]+/gi, "")
      // Remove javascript: protocol
      .replace(/javascript:/gi, "")
      // Remove data: protocol (can be used for XSS)
      .replace(/data:[^;]*;base64,/gi, "")
      // Neutralize iframe, object, embed tags
      .replace(/<(iframe|object|embed)[^>]*>[\s\S]*?<\/\1>/gi, "")
      .replace(/<(iframe|object|embed)[^>]*\/?>/gi, "")
      // Limit length to prevent DoS
      .slice(0, 10000)
  );
}

/**
 * Recursively sanitizes an object's string properties
 */
function sanitizeObject(obj: unknown): unknown {
  if (typeof obj === "string") {
    return sanitizeHtml(obj);
  }

  if (Array.isArray(obj)) {
    return obj.map((item) => sanitizeObject(item));
  }

  if (obj !== null && typeof obj === "object") {
    const result: Record<string, unknown> = {};
    for (const [key, value] of Object.entries(obj)) {
      result[key] = sanitizeObject(value);
    }
    return result;
  }

  return obj;
}

@Injectable()
export class SanitizePipe implements PipeTransform {
  transform(value: unknown, metadata: ArgumentMetadata) {
    // Only transform body data
    if (metadata.type !== "body") {
      return value;
    }

    if (value === null || value === undefined) {
      return value;
    }

    try {
      return sanitizeObject(value);
    } catch (error) {
      throw new BadRequestException("Invalid input data");
    }
  }
}
