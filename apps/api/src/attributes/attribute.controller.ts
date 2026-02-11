import {
  Controller,
  Get,
  Post,
  Put,
  Delete,
  Body,
  Param,
  Query,
  UseGuards,
  ParseIntPipe,
  DefaultValuePipe,
} from "@nestjs/common";
import { ApiTags, ApiOperation, ApiBearerAuth, ApiQuery } from "@nestjs/swagger";
import { AttributeService } from "./attribute.service";
import {
  CreateAttributeDto,
  UpdateAttributeDto,
} from "./dto/create-attribute.dto";
import { QueryAttributesDto } from "./dto/query-attributes.dto";
import { JwtAuthGuard, RolesGuard } from "../auth/guards";
import { Roles } from "../auth/decorators";

@ApiTags("Attributes")
@Controller("attributes")
export class AttributeController {
  constructor(private attributeService: AttributeService) {}

  @Get()
  @ApiOperation({ summary: "Get all attributes with pagination" })
  @ApiQuery({ name: "search", required: false, description: "Search by name" })
  @ApiQuery({ name: "type", required: false, enum: ["TEXT", "NUMBER", "SELECT", "MULTISELECT", "BOOLEAN", "COLOR"], description: "Filter by type" })
  @ApiQuery({ name: "isFilterable", required: false, type: Boolean, description: "Filter by filterable status" })
  @ApiQuery({ name: "skip", required: false, type: Number, description: "Number of items to skip" })
  @ApiQuery({ name: "limit", required: false, type: Number, description: "Number of items to return" })
  findAll(@Query() query: QueryAttributesDto) {
    return this.attributeService.findAll(query);
  }

  @Get("stats")
  @ApiOperation({ summary: "Get attribute statistics" })
  getStats() {
    return this.attributeService.getStats();
  }

  @Get(":id")
  @ApiOperation({ summary: "Get attribute by ID" })
  findOne(@Param("id") id: string) {
    return this.attributeService.findOne(id);
  }

  @Post()
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles("ADMIN", "EDITOR")
  @ApiBearerAuth()
  @ApiOperation({ summary: "Create attribute (Admin/Editor only)" })
  create(@Body() dto: CreateAttributeDto) {
    return this.attributeService.create(dto);
  }

  @Put(":id")
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles("ADMIN", "EDITOR")
  @ApiBearerAuth()
  @ApiOperation({ summary: "Update attribute (Admin/Editor only)" })
  update(@Param("id") id: string, @Body() dto: UpdateAttributeDto) {
    return this.attributeService.update(id, dto);
  }

  @Delete(":id")
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles("ADMIN")
  @ApiBearerAuth()
  @ApiOperation({ summary: "Delete attribute (Admin only)" })
  remove(@Param("id") id: string) {
    return this.attributeService.remove(id);
  }

  // Product Attribute Values
  @Post("product/:productId/:attributeId")
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles("ADMIN", "EDITOR")
  @ApiBearerAuth()
  @ApiOperation({ summary: "Set product attribute value" })
  setProductAttribute(
    @Param("productId") productId: string,
    @Param("attributeId") attributeId: string,
    @Body("value") value: string,
  ) {
    return this.attributeService.setProductAttribute(
      productId,
      attributeId,
      value,
    );
  }

  @Delete("product/:productId/:attributeId")
  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles("ADMIN", "EDITOR")
  @ApiBearerAuth()
  @ApiOperation({ summary: "Remove product attribute value" })
  removeProductAttribute(
    @Param("productId") productId: string,
    @Param("attributeId") attributeId: string,
  ) {
    return this.attributeService.removeProductAttribute(productId, attributeId);
  }

  @Get("product/:productId")
  @ApiOperation({ summary: "Get product attribute values" })
  getProductAttributes(@Param("productId") productId: string) {
    return this.attributeService.getProductAttributes(productId);
  }
}
