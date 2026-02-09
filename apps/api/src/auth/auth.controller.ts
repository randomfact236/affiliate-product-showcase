import {
  Controller,
  Post,
  Get,
  Body,
  UseGuards,
  Req,
  HttpCode,
  HttpStatus,
} from "@nestjs/common";
import { ApiTags, ApiOperation, ApiBearerAuth } from "@nestjs/swagger";
import { Throttle, SkipThrottle } from "@nestjs/throttler";
import { AuthService } from "./auth.service";
import {
  RegisterDto,
  LoginDto,
  ForgotPasswordDto,
  ResetPasswordDto,
} from "./dto";
import { JwtAuthGuard } from "./guards/jwt-auth.guard";
import { RequestWithUser } from "../common/interfaces/request-with-user.interface";

@ApiTags("Auth")
@Controller({ path: "auth", version: "1" })
export class AuthController {
  constructor(private authService: AuthService) {}

  @Post("register")
  @Throttle({
    default: { limit: 3, ttl: 3600000 }, // 3 registrations per hour per IP
  })
  @ApiOperation({ summary: "Register new user" })
  register(@Body() dto: RegisterDto) {
    return this.authService.register(dto);
  }

  @Post("login")
  @HttpCode(HttpStatus.OK)
  @Throttle({
    default: { limit: 5, ttl: 900000 }, // 5 login attempts per 15 minutes per IP
  })
  @ApiOperation({ summary: "Login user" })
  login(@Body() dto: LoginDto) {
    return this.authService.login(dto);
  }

  @Post("refresh")
  @HttpCode(HttpStatus.OK)
  @Throttle({
    default: { limit: 10, ttl: 60000 }, // 10 refresh attempts per minute
  })
  @ApiOperation({ summary: "Refresh access token" })
  refresh(@Body("refreshToken") refreshToken: string) {
    return this.authService.refreshToken(refreshToken);
  }

  @Post("logout")
  @UseGuards(JwtAuthGuard)
  @ApiBearerAuth("JWT-auth")
  @HttpCode(HttpStatus.OK)
  @SkipThrottle() // Don't rate limit authenticated logout
  @ApiOperation({ summary: "Logout user" })
  logout(@Req() req: RequestWithUser) {
    return this.authService.logout(req.user.userId);
  }

  @Get("profile")
  @UseGuards(JwtAuthGuard)
  @ApiBearerAuth("JWT-auth")
  @SkipThrottle() // Don't rate limit profile fetch
  @ApiOperation({ summary: "Get user profile" })
  getProfile(@Req() req: RequestWithUser) {
    return this.authService.getProfile(req.user.userId);
  }

  @Post("forgot-password")
  @HttpCode(HttpStatus.OK)
  @Throttle({
    default: { limit: 3, ttl: 3600000 }, // 3 forgot password requests per hour per IP
  })
  @ApiOperation({ summary: "Request password reset" })
  forgotPassword(@Body() dto: ForgotPasswordDto) {
    return this.authService.forgotPassword(dto.email);
  }

  @Post("reset-password")
  @HttpCode(HttpStatus.OK)
  @Throttle({
    default: { limit: 5, ttl: 900000 }, // 5 reset attempts per 15 minutes per IP
  })
  @ApiOperation({ summary: "Reset password with token" })
  resetPassword(@Body() dto: ResetPasswordDto) {
    return this.authService.resetPassword(dto.token, dto.password);
  }
}
