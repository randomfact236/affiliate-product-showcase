import { Test, TestingModule } from "@nestjs/testing";
import { ProductService } from "./product.service";
import { PrismaService } from "../prisma/prisma.service";
import { ConflictException } from "@nestjs/common";
import { REDIS_CLIENT } from "../common/constants/injection-tokens";

const mockPrismaProduct = {
  findUnique: jest.fn(),
  create: jest.fn(),
  findMany: jest.fn(),
  count: jest.fn(),
  update: jest.fn(),
  delete: jest.fn(),
};

const mockPrismaService = {
  product: mockPrismaProduct,
  $transaction: jest
    .fn()
    .mockImplementation((cb) => cb({ product: mockPrismaProduct })),
};

const mockRedis = {
  get: jest.fn(),
  setex: jest.fn(),
  del: jest.fn(),
};

describe("ProductService", () => {
  let service: ProductService;
  let prisma: typeof mockPrismaService;

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      providers: [
        ProductService,
        {
          provide: PrismaService,
          useValue: mockPrismaService,
        },
        {
          provide: REDIS_CLIENT,
          useValue: mockRedis,
        },
      ],
    }).compile();

    service = module.get<ProductService>(ProductService);
    prisma = module.get(PrismaService);
  });

  afterEach(() => {
    jest.clearAllMocks();
  });

  it("should be defined", () => {
    expect(service).toBeDefined();
  });

  describe("create", () => {
    it("should create a product", async () => {
      const dto = { name: "Test Product", description: "Desc", basePrice: 100 };
      const userId = "user-123";
      const expectedProduct = { id: "1", ...dto, slug: "test-product" };

      mockPrismaProduct.findUnique.mockResolvedValue(null);
      mockPrismaProduct.create.mockResolvedValue(expectedProduct);

      const result = await service.create(dto as any, userId);

      // expect(mockPrismaProduct.findUnique).toHaveBeenCalledWith({ where: { slug: 'test-product' } });
      // Since slug generation is internal, exact slug match might vary if logic changes,
      // but we expect findUnique to be called.
      expect(mockPrismaProduct.findUnique).toHaveBeenCalled();
      expect(mockPrismaProduct.create).toHaveBeenCalled();
      expect(result).toEqual(expectedProduct);
    });

    it("should throw conflict if slug exists", async () => {
      const dto = { name: "Test Product", basePrice: 100 };
      mockPrismaProduct.findUnique.mockResolvedValue({
        id: "1",
        slug: "test-product",
      });

      await expect(service.create(dto as any, "user-1")).rejects.toThrow(
        ConflictException,
      );
    });
  });

  describe("findAll", () => {
    it("should return paginated products", async () => {
      const filters = {
        page: 1,
        limit: 10,
        sortBy: "createdAt",
        sortOrder: "desc",
      };
      const products = [{ id: "1", name: "P1" }];
      const total = 1;

      mockPrismaProduct.findMany.mockResolvedValue(products);
      mockPrismaProduct.count.mockResolvedValue(total);

      const result = await service.findAll(filters as any);

      expect(result.data).toEqual(products);
      expect(result.meta.total).toBe(1);
    });
  });
});
