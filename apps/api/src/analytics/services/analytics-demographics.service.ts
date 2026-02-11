import { Injectable } from '@nestjs/common';
import { PrismaService } from '../../prisma/prisma.service';

@Injectable()
export class AnalyticsDemographicsService {
  constructor(private prisma: PrismaService) {}

  async getGenderSplit(startDate?: string, endDate?: string) {
    const where = this.buildDateFilter(startDate, endDate);
    
    const results = await this.prisma.analyticsSession.groupBy({
      by: ['gender'],
      where: { ...where, gender: { not: null } },
      _count: { id: true },
    });

    const total = results.reduce((sum, r) => sum + r._count.id, 0);
    
    return {
      male: this.formatResult(results.find(r => r.gender?.toLowerCase() === 'male'), total),
      female: this.formatResult(results.find(r => r.gender?.toLowerCase() === 'female'), total),
      other: this.formatResult(results.find(r => r.gender?.toLowerCase() === 'other'), total),
    };
  }

  async getAgeDistribution(startDate?: string, endDate?: string) {
    const where = this.buildDateFilter(startDate, endDate);
    
    const results = await this.prisma.analyticsSession.groupBy({
      by: ['ageGroup'],
      where: { ...where, ageGroup: { not: null } },
      _count: { id: true },
    });

    const total = results.reduce((sum, r) => sum + r._count.id, 0);
    const ageOrder = ['18-24', '25-34', '35-44', '45-54', '55+'];
    
    return ageOrder.map(range => {
      const item = results.find(r => r.ageGroup === range);
      return {
        range,
        count: item?._count.id || 0,
        percentage: total > 0 ? Math.round((item?._count.id || 0) / total * 100) : 0,
      };
    });
  }

  async getTopInterests(startDate?: string, endDate?: string, limit = 10) {
    const where = this.buildDateFilter(startDate, endDate);
    
    const sessions = await this.prisma.analyticsSession.findMany({
      where: { ...where, interests: { isEmpty: false } },
      select: { interests: true },
    });

    const interestCounts = new Map<string, number>();
    sessions.forEach(session => {
      session.interests?.forEach(interest => {
        interestCounts.set(interest, (interestCounts.get(interest) || 0) + 1);
      });
    });

    const total = sessions.length;
    const sorted = Array.from(interestCounts.entries())
      .sort((a, b) => b[1] - a[1])
      .slice(0, limit);

    return sorted.map(([name, count]) => ({
      name,
      count,
      percentage: total > 0 ? Math.round(count / total * 100) : 0,
    }));
  }

  async getNewVsReturning(startDate?: string, endDate?: string) {
    const where = this.buildDateFilter(startDate, endDate);
    
    const results = await this.prisma.analyticsSession.groupBy({
      by: ['isNewVisitor'],
      where,
      _count: { id: true },
    });

    const total = results.reduce((sum, r) => sum + r._count.id, 0);
    const newVisitors = results.find(r => r.isNewVisitor === true)?._count.id || 0;
    const returning = results.find(r => r.isNewVisitor === false)?._count.id || 0;

    return {
      new: { count: newVisitors, percentage: total > 0 ? Math.round(newVisitors / total * 100) : 0 },
      returning: { count: returning, percentage: total > 0 ? Math.round(returning / total * 100) : 0 },
    };
  }

  async getLanguages(startDate?: string, endDate?: string) {
    const where = this.buildDateFilter(startDate, endDate);
    
    const results = await this.prisma.analyticsSession.groupBy({
      by: ['language'],
      where: { ...where, language: { not: null } },
      _count: { id: true },
    });

    const total = results.reduce((sum, r) => sum + r._count.id, 0);
    
    return results
      .sort((a, b) => b._count.id - a._count.id)
      .map(r => ({
        code: r.language,
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

  private formatResult(item: any, total: number) {
    const count = item?._count?.id || 0;
    return {
      count,
      percentage: total > 0 ? Math.round(count / total * 100) : 0,
    };
  }
}
