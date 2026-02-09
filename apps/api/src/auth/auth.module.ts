import { Module } from "@nestjs/common";
import { JwtModule } from "@nestjs/jwt";
import { PassportModule } from "@nestjs/passport";
import { ConfigService } from "@nestjs/config";
import { AuthService } from "./auth.service";
import { AuthController } from "./auth.controller";
import { PasswordService } from "./password.service";
import { EmailService } from "../common/services/email.service";
import { JwtStrategy } from "./strategies";
import { PrismaModule } from "../prisma/prisma.module";

@Module({
  imports: [
    PrismaModule,
    PassportModule.register({ defaultStrategy: "jwt" }),
    JwtModule.registerAsync({
      useFactory: (configService: ConfigService) => {
        const secret = configService.get<string>("jwt.secret");
        const expiresIn = configService.get<string>(
          "jwt.expiresIn",
        ) as `${number}${"s" | "m" | "h" | "d"}`;
        const issuer = configService.get<string>("jwt.issuer");
        const audience = configService.get<string>("jwt.audience");

        if (!secret) {
          throw new Error("JWT secret is not configured");
        }

        return {
          secret,
          signOptions: {
            expiresIn,
            issuer,
            audience,
          },
        };
      },
      inject: [ConfigService],
    }),
  ],
  controllers: [AuthController],
  providers: [AuthService, PasswordService, JwtStrategy, EmailService],
  exports: [AuthService, PasswordService],
})
export class AuthModule {}
