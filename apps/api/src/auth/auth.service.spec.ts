import { Test, TestingModule } from "@nestjs/testing";
import { AuthService } from "./auth.service";
import { PrismaService } from "../prisma/prisma.service";
import { JwtService } from "@nestjs/jwt";
import { PasswordService } from "./password.service";
import { EmailService } from "../common/services/email.service";
import { UnauthorizedException, ConflictException } from "@nestjs/common";

describe("AuthService", () => {
  let service: AuthService;
  let prisma: PrismaService;
  let passwordService: PasswordService;
  let jwtService: JwtService;

  const mockPrismaService = {
    user: {
      findUnique: jest.fn(),
      create: jest.fn(),
      update: jest.fn(),
    },
    session: {
      create: jest.fn(),
    },
    refreshToken: {
      create: jest.fn(),
      findUnique: jest.fn(),
      update: jest.fn(),
    },
  };

  const mockJwtService = {
    sign: jest.fn(),
    verify: jest.fn(),
  };

  const mockPasswordService = {
    hash: jest.fn(),
    verify: jest.fn(),
  };

  const mockEmailService = {
    sendWelcomeEmail: jest.fn(),
  };

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      providers: [
        AuthService,
        { provide: PrismaService, useValue: mockPrismaService },
        { provide: JwtService, useValue: mockJwtService },
        { provide: PasswordService, useValue: mockPasswordService },
        { provide: EmailService, useValue: mockEmailService },
        {
          provide: "REDIS_CLIENT",
          useValue: {
            setex: jest.fn(),
            exists: jest.fn(),
            del: jest.fn(),
            keys: jest.fn(),
          },
        },
      ],
    }).compile();

    service = module.get<AuthService>(AuthService);
    prisma = module.get<PrismaService>(PrismaService);
    passwordService = module.get<PasswordService>(PasswordService);
    jwtService = module.get<JwtService>(JwtService);
  });

  afterEach(() => {
    jest.clearAllMocks();
  });

  it("should be defined", () => {
    expect(service).toBeDefined();
  });

  describe("register", () => {
    const registerDto = {
      email: "test@example.com",
      password: "password123",
      firstName: "John",
      lastName: "Doe",
    };

    it("should successfully register a new user", async () => {
      // Mock no existing user
      mockPrismaService.user.findUnique.mockResolvedValue(null);
      // Mock password hashing
      mockPasswordService.hash.mockResolvedValue("hashed_password");
      // Mock user creation
      const resultUser = {
        id: "user-id",
        ...registerDto,
        password: "hashed_password",
      };
      mockPrismaService.user.create.mockResolvedValue(resultUser);
      // Mock JWT generation
      mockJwtService.sign.mockReturnValue("mock_token");
      process.env.JWT_SECRET = "secret";
      process.env.JWT_REFRESH_SECRET = "refresh_secret";

      const result = await service.register(registerDto);

      expect(prisma.user.findUnique).toHaveBeenCalledWith({
        where: { email: registerDto.email },
      });
      expect(passwordService.hash).toHaveBeenCalledWith(registerDto.password);
      expect(prisma.user.create).toHaveBeenCalled();
      expect(result).toHaveProperty("accessToken");
      expect(result).toHaveProperty("refreshToken");
    });

    it("should throw ConflictException if email exists", async () => {
      mockPrismaService.user.findUnique.mockResolvedValue({
        id: "existing-id",
      });

      await expect(service.register(registerDto)).rejects.toThrow(
        ConflictException,
      );
    });
  });

  describe("login", () => {
    const loginDto = {
      email: "test@example.com",
      password: "password123",
    };

    it("should successfully login", async () => {
      const user = {
        id: "user-id",
        email: loginDto.email,
        password: "hashed_password",
      };

      mockPrismaService.user.findUnique.mockResolvedValue(user);
      mockPasswordService.verify.mockResolvedValue(true);
      mockJwtService.sign.mockReturnValue("mock_token");

      process.env.JWT_SECRET = "secret";
      process.env.JWT_REFRESH_SECRET = "refresh_secret";

      const result = await service.login(loginDto);

      expect(prisma.user.update).toHaveBeenCalledWith({
        where: { id: user.id },
        data: { lastLoginAt: expect.any(Date) },
      });
      expect(result).toHaveProperty("accessToken");
    });

    it("should throw UnauthorizedException if user not found", async () => {
      mockPrismaService.user.findUnique.mockResolvedValue(null);

      await expect(service.login(loginDto)).rejects.toThrow(
        UnauthorizedException,
      );
    });

    it("should throw UnauthorizedException if password incorrect", async () => {
      const user = {
        id: "user-id",
        email: loginDto.email,
        password: "hashed_password",
      };
      mockPrismaService.user.findUnique.mockResolvedValue(user);
      mockPasswordService.verify.mockResolvedValue(false);

      await expect(service.login(loginDto)).rejects.toThrow(
        UnauthorizedException,
      );
    });
  });
});
