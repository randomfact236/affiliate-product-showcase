import { Injectable } from '@nestjs/common';
import { PrismaService } from '../../prisma/prisma.service';

@Injectable()
export class AnalyticsDevicesService {
  constructor(private prisma: PrismaService) {}

  async getDeviceBreakdown(startDate?: string, endDate?: string) {
    const where = this.buildDateFilter(startDate, endDate);
    
    const results = await this.prisma.analyticsSession.groupBy({
      by: ['deviceType'],
      where: { ...where, deviceType: { not: null } },
      _count: { id: true },
    });

    const total = results.reduce((sum, r) => sum + r._count.id, 0);
    
    return results.map(r => ({
      type: r.deviceType,
      count: r._count.id,
      percentage: total > 0 ? Math.round(r._count.id / total * 100) : 0,
    })).sort((a, b) => b.count - a.count);
  }

  async getBrowserDistribution(startDate?: string, endDate?: string) {
    const where = this.buildDateFilter(startDate, endDate);
    
    const results = await this.prisma.analyticsSession.groupBy({
      by: ['browser'],
      where: { ...where, browser: { not: null } },
      _count: { id: true },
    });

    const total = results.reduce((sum, r) => sum + r._count.id, 0);
    
    return results
      .sort((a, b) => b._count.id - a._count.id)
      .map(r => ({
        name: r.browser,
        count: r._count.id,
        percentage: total > 0 ? Math.round(r._count.id / total * 100) : 0,
      }));
  }

  async getOSDistribution(startDate?: string, endDate?: string) {
    const where = this.buildDateFilter(startDate, endDate);
    
    const results = await this.prisma.analyticsSession.groupBy({
      by: ['os'],
      where: { ...where, os: { not: null } },
      _count: { id: true },
    });

    const total = results.reduce((sum, r) => sum + r._count.id, 0);
    
    return results
      .sort((a, b) => b._count.id - a._count.id)
      .map(r => ({
        name: r.os,
        count: r._count.id,
        percentage: total > 0 ? Math.round(r._count.id / total * 100) : 0,
      }));
  }

  async getScreenResolutions(startDate?: string, endDate?: string) {
    const where = this.buildDateFilter(startDate, endDate);
    
    const results = await this.prisma.analyticsSession.groupBy({
      by: ['screenResolution'],
      where: { ...where, screenResolution: { not: null } },
      _count: { id: true },
    });

    const total = results.reduce((sum, r) => sum + r._count.id, 0);
    
    return results
      .sort((a, b) => b._count.id - a._count.id)
      .slice(0, 10)
      .map(r => ({
        resolution: r.screenResolution,
        count: r._count.id,
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
