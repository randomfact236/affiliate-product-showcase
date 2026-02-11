import { Injectable, NotFoundException } from '@nestjs/common';
import { PrismaService } from '../prisma/prisma.service';
import { CreateSettingDto, UpdateSettingDto } from './dto/settings.dto';

export interface ShortcodeDefinition {
  tag: string;
  description: string;
  attributes?: Record<string, { type: string; default?: any; description?: string }>;
  render: (attributes: Record<string, any>, content?: string) => string;
}

@Injectable()
export class SettingsService {
  private shortcodes: Map<string, ShortcodeDefinition> = new Map();

  constructor(private prisma: PrismaService) {
    this.registerDefaultShortcodes();
  }

  // Register built-in shortcodes
  private registerDefaultShortcodes() {
    // Blog shortcodes
    this.registerShortcode({
      tag: 'recent_posts',
      description: 'Display recent blog posts',
      attributes: {
        count: { type: 'number', default: 5, description: 'Number of posts to show' },
        category: { type: 'string', default: '', description: 'Filter by category slug' },
        show_excerpt: { type: 'boolean', default: true, description: 'Show post excerpt' },
        show_date: { type: 'boolean', default: true, description: 'Show post date' },
      },
      render: (attrs) => `[recent_posts count="${attrs.count}"]`,
    });

    this.registerShortcode({
      tag: 'featured_posts',
      description: 'Display featured blog posts',
      attributes: {
        count: { type: 'number', default: 3, description: 'Number of posts to show' },
        layout: { type: 'string', default: 'grid', description: 'Layout: grid or list' },
      },
      render: (attrs) => `[featured_posts count="${attrs.count}" layout="${attrs.layout}"]`,
    });

    this.registerShortcode({
      tag: 'blog_categories',
      description: 'Display blog categories',
      attributes: {
        style: { type: 'string', default: 'list', description: 'Style: list, cloud, or dropdown' },
        show_count: { type: 'boolean', default: true, description: 'Show post count' },
      },
      render: (attrs) => `[blog_categories style="${attrs.style}"]`,
    });

    // Product shortcodes
    this.registerShortcode({
      tag: 'products',
      description: 'Display products',
      attributes: {
        count: { type: 'number', default: 8, description: 'Number of products' },
        category: { type: 'string', default: '', description: 'Filter by category' },
        featured: { type: 'boolean', default: false, description: 'Show only featured' },
        on_sale: { type: 'boolean', default: false, description: 'Show only on sale' },
        layout: { type: 'string', default: 'grid', description: 'Layout: grid or list' },
        columns: { type: 'number', default: 4, description: 'Number of columns' },
      },
      render: (attrs) => `[products count="${attrs.count}"]`,
    });

    this.registerShortcode({
      tag: 'featured_products',
      description: 'Display featured products',
      attributes: {
        count: { type: 'number', default: 4, description: 'Number of products' },
        title: { type: 'string', default: 'Featured Products', description: 'Section title' },
      },
      render: (attrs) => `[featured_products count="${attrs.count}" title="${attrs.title}"]`,
    });

    this.registerShortcode({
      tag: 'product_categories',
      description: 'Display product categories',
      attributes: {
        style: { type: 'string', default: 'grid', description: 'Style: grid or list' },
        show_image: { type: 'boolean', default: true, description: 'Show category image' },
      },
      render: (attrs) => `[product_categories style="${attrs.style}"]`,
    });

    // Don't Miss Section shortcode
    this.registerShortcode({
      tag: 'dont_miss',
      description: 'Display "Don\'t Miss" section with curated content',
      attributes: {
        title: { type: 'string', default: "Don't Miss", description: 'Section title' },
        subtitle: { type: 'string', default: '', description: 'Section subtitle' },
        blog_count: { type: 'number', default: 3, description: 'Number of blog posts' },
        product_count: { type: 'number', default: 2, description: 'Number of products' },
        layout: { type: 'string', default: 'mixed', description: 'Layout: mixed, blogs_only, products_only' },
      },
      render: (attrs) => `[dont_miss title="${attrs.title}"]`,
    });

    // General shortcodes
    this.registerShortcode({
      tag: 'site_title',
      description: 'Display site title',
      render: () => '[site_title]',
    });

    this.registerShortcode({
      tag: 'site_description',
      description: 'Display site description',
      render: () => '[site_description]',
    });

    this.registerShortcode({
      tag: 'current_year',
      description: 'Display current year',
      render: () => new Date().getFullYear().toString(),
    });
  }

  // Register a shortcode
  registerShortcode(definition: ShortcodeDefinition) {
    this.shortcodes.set(definition.tag, definition);
  }

  // Get all shortcodes
  getShortcodes(): ShortcodeDefinition[] {
    return Array.from(this.shortcodes.values());
  }

  // Get shortcode by tag
  getShortcode(tag: string): ShortcodeDefinition | undefined {
    return this.shortcodes.get(tag);
  }

  // Parse and render shortcodes in content
  parseShortcodes(content: string): string {
    if (!content) return content;

    // Match shortcodes like [tag attr="value"] or [tag]content[/tag]
    const shortcodeRegex = /\[(\w+)(\s+[^\]]*)?\](?:([^\[]*)\[\/\1\])?/g;

    return content.replace(shortcodeRegex, (match, tag, attrsString, innerContent) => {
      const shortcode = this.shortcodes.get(tag);
      if (!shortcode) return match;

      // Parse attributes
      const attributes: Record<string, any> = {};
      if (attrsString) {
        const attrRegex = /(\w+)=["']([^"']*)["']/g;
        let attrMatch;
        while ((attrMatch = attrRegex.exec(attrsString)) !== null) {
          const [, key, value] = attrMatch;
          // Convert to appropriate type
          if (shortcode.attributes?.[key]) {
            const type = shortcode.attributes[key].type;
            if (type === 'number') {
              attributes[key] = parseInt(value) || shortcode.attributes[key].default;
            } else if (type === 'boolean') {
              attributes[key] = value === 'true' || value === '1';
            } else {
              attributes[key] = value;
            }
          } else {
            attributes[key] = value;
          }
        }
      }

      // Apply defaults
      if (shortcode.attributes) {
        for (const [key, config] of Object.entries(shortcode.attributes)) {
          if (attributes[key] === undefined) {
            attributes[key] = config.default;
          }
        }
      }

      return shortcode.render(attributes, innerContent);
    });
  }

  // CRUD operations for settings
  async findAll() {
    return this.prisma.setting.findMany({
      orderBy: { group: 'asc' },
    });
  }

  async findByGroup(group: string) {
    return this.prisma.setting.findMany({
      where: { group },
    });
  }

  async findOne(key: string) {
    const setting = await this.prisma.setting.findUnique({
      where: { key },
    });
    if (!setting) {
      throw new NotFoundException(`Setting with key "${key}" not found`);
    }
    return setting;
  }

  async findByKey(key: string) {
    const setting = await this.prisma.setting.findUnique({
      where: { key },
    });
    return setting;
  }

  async getValue<T = any>(key: string, defaultValue?: T): Promise<T> {
    const setting = await this.findByKey(key);
    if (!setting) return defaultValue as T;
    
    try {
      return JSON.parse(setting.value);
    } catch {
      return setting.value as unknown as T;
    }
  }

  async create(dto: CreateSettingDto) {
    return this.prisma.setting.create({
      data: {
        key: dto.key,
        value: typeof dto.value === 'string' ? dto.value : JSON.stringify(dto.value),
        type: dto.type || 'string',
        group: dto.group || 'general',
        label: dto.label,
        description: dto.description,
        isPublic: dto.isPublic ?? true,
      },
    });
  }

  async update(key: string, dto: UpdateSettingDto) {
    return this.prisma.setting.update({
      where: { key },
      data: {
        value: dto.value !== undefined 
          ? (typeof dto.value === 'string' ? dto.value : JSON.stringify(dto.value))
          : undefined,
        type: dto.type,
        group: dto.group,
        label: dto.label,
        description: dto.description,
        isPublic: dto.isPublic,
      },
    });
  }

  async remove(key: string) {
    return this.prisma.setting.delete({
      where: { key },
    });
  }

  async upsert(key: string, dto: CreateSettingDto) {
    return this.prisma.setting.upsert({
      where: { key },
      create: {
        key,
        value: typeof dto.value === 'string' ? dto.value : JSON.stringify(dto.value),
        type: dto.type || 'string',
        group: dto.group || 'general',
        label: dto.label,
        description: dto.description,
        isPublic: dto.isPublic ?? true,
      },
      update: {
        value: dto.value !== undefined 
          ? (typeof dto.value === 'string' ? dto.value : JSON.stringify(dto.value))
          : undefined,
        type: dto.type,
        group: dto.group,
        label: dto.label,
        description: dto.description,
        isPublic: dto.isPublic,
      },
    });
  }

  // Get Don't Miss section configuration
  async getDontMissConfig() {
    const config = await this.prisma.setting.findUnique({
      where: { key: 'dont_miss_section' },
    });

    if (config) {
      try {
        return JSON.parse(config.value);
      } catch {
        return this.getDefaultDontMissConfig();
      }
    }

    return this.getDefaultDontMissConfig();
  }

  // Update Don't Miss section configuration
  async updateDontMissConfig(config: any) {
    return this.upsert('dont_miss_section', {
      key: 'dont_miss_section',
      value: JSON.stringify(config),
      type: 'json',
      group: 'home',
      label: "Don't Miss Section",
      description: 'Configuration for the Don\'t Miss section on homepage',
      isPublic: true,
    });
  }

  private getDefaultDontMissConfig() {
    return {
      enabled: true,
      title: "Don't Miss",
      subtitle: 'Latest updates and featured products you should check out',
      layout: 'mixed',
      blogCount: 3,
      productCount: 2,
      showViewAll: true,
      blogCategory: '',
      productCategory: '',
      backgroundColor: '',
      textColor: '',
    };
  }

  // Initialize default settings
  async initializeDefaults() {
    const defaults = [
      {
        key: 'site_name',
        value: 'Affiliate Showcase',
        type: 'string',
        group: 'general',
        label: 'Site Name',
        description: 'The name of your website',
        isPublic: true,
      },
      {
        key: 'site_description',
        value: 'Discover the best affiliate products and deals',
        type: 'string',
        group: 'general',
        label: 'Site Description',
        description: 'Short description of your website',
        isPublic: true,
      },
      {
        key: 'posts_per_page',
        value: '10',
        type: 'number',
        group: 'blog',
        label: 'Posts Per Page',
        description: 'Number of blog posts per page',
        isPublic: true,
      },
      {
        key: 'products_per_page',
        value: '12',
        type: 'number',
        group: 'products',
        label: 'Products Per Page',
        description: 'Number of products per page',
        isPublic: true,
      },
      {
        key: 'enable_blog_comments',
        value: 'true',
        type: 'boolean',
        group: 'blog',
        label: 'Enable Blog Comments',
        description: 'Allow visitors to comment on blog posts',
        isPublic: true,
      },
      {
        key: 'enable_product_reviews',
        value: 'true',
        type: 'boolean',
        group: 'products',
        label: 'Enable Product Reviews',
        description: 'Allow customers to leave product reviews',
        isPublic: true,
      },
      {
        key: 'default_currency',
        value: 'USD',
        type: 'string',
        group: 'products',
        label: 'Default Currency',
        description: 'Default currency for products',
        isPublic: true,
      },
      {
        key: 'currency_symbol',
        value: '$',
        type: 'string',
        group: 'products',
        label: 'Currency Symbol',
        description: 'Symbol for currency display',
        isPublic: true,
      },
      {
        key: 'affiliate_disclosure',
        value: 'This post contains affiliate links. We may earn a commission if you make a purchase.',
        type: 'text',
        group: 'products',
        label: 'Affiliate Disclosure',
        description: 'Text displayed on pages with affiliate links',
        isPublic: true,
      },
      {
        key: 'blog_default_section_spacing',
        value: '4',
        type: 'number',
        group: 'blog',
        label: 'Default Section Spacing',
        description: 'Default space between heading and content sections in blog posts (4 = 16px)',
        isPublic: true,
      },
    ];

    for (const setting of defaults) {
      await this.prisma.setting.upsert({
        where: { key: setting.key },
        create: setting,
        update: {},
      });
    }

    // Initialize Don't Miss section
    await this.updateDontMissConfig(this.getDefaultDontMissConfig());

    return { message: 'Default settings initialized' };
  }
}
