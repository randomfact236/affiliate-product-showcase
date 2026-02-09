import { Injectable, Logger } from "@nestjs/common";

@Injectable()
export class EmailService {
  private readonly logger = new Logger(EmailService.name);

  async sendPasswordResetEmail(to: string, token: string) {
    // In production, use SendGrid/AWS SES/Nodemailer
    // For now, log the token so manual testing is possible without leakage in API response
    this.logger.log(`[MOCK EMAIL] Password Reset for ${to}: Token=${token}`);

    // In a real staging environment, this might write to a file or special debug log
    // ensuring it doesn't leak in the HTTP response body.
    return true;
  }
}
