import { Injectable } from '@nestjs/common';
import { PrismaService } from '../../prisma/prisma.service';

@Injectable()
export class AnalyticsContentService {
  constructor(private prisma: PrismaService) {}

  async getCategories(startDate?: string, endDate?: string) {
    const where = this.buildDateFilter(startDate, endDate);
    
    const results = await this.prisma.analyticsEvent.groupBy({
      by: ['categoryName'],
      where: { ...where, categoryName: { not: null } },
      _count: { id: true },
    });

    const total = results.reduce((sum, r) => sum + r._count.id, 0);
    
    return results.map(r => ({
      name: r.categoryName,
      count: r._count.id,
      percentage: total > 0 ? Math.round(r._count.id / total * 100) : 0,
    })).sort((a, b) => b.count - a.count);
  }

  async getTags(startDate?: string, endDate?: string, limit = 20) {
    const where = this.buildDateFilter(startDate, endDate);
    
    const events = await this.prisma.analyticsEvent.findMany({
      where: { ...where, tagNames: { isEmpty: false } },
      select: { tagNames: true },
    });

    const tagCounts = new Map<string, number>();
    events.forEach(event => {
      event.tagNames?.forEach(tag => {
        tagCounts.set(tag, (tagCounts.get(tag) || 0) + 1);
      });
    });

    const total = events.length;
    const sorted = Array.from(tagCounts.entries())
      .sort((a, b) => b[1] - a[1])
      .slice(0, limit);

    return sorted.map(([name, count]) => ({
      name,
      count,
      percentage: total > 0 ? Math.round(count / total * 100) : 0,
    }));
  }

  async getRibbons(startDate?: string, endDate?: string) {
    const where = this.buildDateFilter(startDate, endDate);
    
    const results = await this.prisma.analyticsEvent.groupBy({
      by: ['ribbonType'],
      where: { ...where, ribbonType: { not: null } },
      _count: { id: true },
    });

    const total = results.reduce((sum, r) => sum + r._count.id, 0);
    
    return results.map(r => ({
      type: r.ribbonType,
      count: r._count.id,
      percentage: total > 0 ? Math.round(r._count.id / total * 100) : 0,
    })).sort((a, b) => b.count - a.count);
  }

  async getPlacements(startDate?: string, endDate?: string) {
    const where = this.buildDateFilter(startDate, endDate);
    
    const results = await this.prisma.analyticsSession.groupBy({
      by: ['placementType'],
      where: { ...where, placementType: { not: null } },
      _count: { id: true },
    });

    const total = results.reduce((sum, r) => sum + r._count.id, 0);
    
    return results.map(r => ({
      type: r.placementType,
      count: r._count.id,
      percentage: total > 0 ? Math.round(r._count.id / total * 100) : 0,
    })).sort((a, b) => b.count - a.count);
  }

  async getLandingPages(startDate?: string, endDate?: string, limit = 10) {
    const where = this.buildDateFilter(startDate, endDate);
    
    const results = await this.prisma.analyticsEvent.groupBy({
      by: ['path'],
      where: { ...where, isEntryPage: true },
      _count: { id: true },
    });

    const total = results.reduce((sum, r) => sum + r._count.id, 0);
    
    return results
      .sort((a, b) => b._count.id - a._count.id)
      .slice(0, limit)
      .map(r => ({
        url: r.path,
        visits: r._count.id,
        percentage: total > 0 ? Math.round(r._count.id / total * 100) : 0,
      }));
  }

  async getExitPages(startDate?: string, endDate?: string, limit = 10) {
    const where = this.buildDateFilter(startDate, endDate);
    
    const results = await this.prisma.analyticsEvent.groupBy({
      by: ['path'],
      where: { ...where, isExitPage: true },
      _count: { id: true },
    });

    const total = results.reduce((sum, r) => sum + r._count.id, 0);
    
    return results
      .sort((a, b) => b._count.id - a._count.id)
      .slice(0, limit)
      .map(r => ({
        url: r.path,
        exits: r._count.id,
        percentage: total > 0 ? Math.round(r._count.id / total * 100) : 0,
      }));
  }

  private buildDateFilter(startDate?: string, endDate?: string) {
    const filter: any = {};
    if (startDate || endDate) {
      filter.createdAt = {};
      if (startDate) filter.createdAt.gte = new Date(startDate);
      if (endDate) filter.createdAt.lte = new Date(endDate);
    }
    return filter;
  }
}
