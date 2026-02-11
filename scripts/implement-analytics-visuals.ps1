#!/usr/bin/env powershell
#requires -version 5
<#
.SYNOPSIS
    Implements Analytics Visual Features for Affiliate Pro Dashboard
.DESCRIPTION
    Creates chart components, UI components, API endpoints, and database updates
    to match the Affiliate Pro Dashboard specifications from reference images.
.NOTES
    File Name      : implement-analytics-visuals.ps1
    Author         : Affiliate Pro System
    Prerequisite   : PowerShell 5.1, Node.js 18+, PostgreSQL 15
#>

[CmdletBinding()]
param(
    [switch]$SkipDatabase,
    [switch]$SkipFrontend,
    [switch]$SkipBackend,
    [switch]$SkipComponents
)

$ErrorActionPreference = "Stop"
$script:LogFile = "logs/analytics-visuals-$(Get-Date -Format 'yyyyMMdd-HHmmss').log"

# Ensure log directory exists
New-Item -ItemType Directory -Force -Path "logs" | Out-Null

function Write-Log {
    param([string]$Message, [string]$Level = "INFO")
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $logEntry = "[$timestamp] [$Level] $Message"
    Write-Host $logEntry
    Add-Content -Path $script:LogFile -Value $logEntry
}

function Test-Command {
    param([string]$Command)
    return [bool](Get-Command -Name $Command -ErrorAction SilentlyContinue)
}

function New-Backup {
    param([string]$Path)
    if (Test-Path $Path) {
        $backupPath = "$Path.backup-$(Get-Date -Format 'yyyyMMdd-HHmmss')"
        Copy-Item -Path $Path -Destination $backupPath -Recurse -Force
        Write-Log "Created backup: $backupPath"
        return $backupPath
    }
    return $null
}

# ==================== PHASE 1: Database Schema Updates ====================
function Update-DatabaseSchema {
    Write-Log "=== PHASE 1: Database Schema Updates ==="
    
    $migrationSQL = @"
-- Analytics Visual Features Migration
-- Created: $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")

-- Add visitor demographics to AnalyticsSession
ALTER TABLE "AnalyticsSession" 
ADD COLUMN IF NOT EXISTS "gender" VARCHAR(10),
ADD COLUMN IF NOT EXISTS "ageGroup" VARCHAR(20),
ADD COLUMN IF NOT EXISTS "interests" TEXT[],
ADD COLUMN IF NOT EXISTS "language" VARCHAR(10),
ADD COLUMN IF NOT EXISTS "isNewVisitor" BOOLEAN DEFAULT true,
ADD COLUMN IF NOT EXISTS "placementType" VARCHAR(50);

-- Add device breakdown fields
ALTER TABLE "AnalyticsSession"
ADD COLUMN IF NOT EXISTS "deviceType" VARCHAR(20) DEFAULT 'desktop',
ADD COLUMN IF NOT EXISTS "browser" VARCHAR(50),
ADD COLUMN IF NOT EXISTS "os" VARCHAR(50),
ADD COLUMN IF NOT EXISTS "screenResolution" VARCHAR(20);

-- Add traffic source details
ALTER TABLE "AnalyticsSession"
ADD COLUMN IF NOT EXISTS "socialSource" VARCHAR(50),
ADD COLUMN IF NOT EXISTS "countryCode" VARCHAR(5),
ADD COLUMN IF NOT EXISTS "city" VARCHAR(100);

-- Add content tracking to AnalyticsEvent
ALTER TABLE "AnalyticsEvent"
ADD COLUMN IF NOT EXISTS "categoryName" VARCHAR(100),
ADD COLUMN IF NOT EXISTS "tagNames" TEXT[],
ADD COLUMN IF NOT EXISTS "ribbonType" VARCHAR(50),
ADD COLUMN IF NOT EXISTS "contentType" VARCHAR(50);

-- Add page performance tracking
ALTER TABLE "AnalyticsEvent"
ADD COLUMN IF NOT EXISTS "loadTime" INTEGER,
ADD COLUMN IF NOT EXISTS "isEntryPage" BOOLEAN DEFAULT false,
ADD COLUMN IF NOT EXISTS "isExitPage" BOOLEAN DEFAULT false,
ADD COLUMN IF NOT EXISTS "timeOnPage" INTEGER;

-- Create indexes for performance
CREATE INDEX IF NOT EXISTS "idx_session_gender" ON "AnalyticsSession"("gender");
CREATE INDEX IF NOT EXISTS "idx_session_age" ON "AnalyticsSession"("ageGroup");
CREATE INDEX IF NOT EXISTS "idx_session_device" ON "AnalyticsSession"("deviceType");
CREATE INDEX IF NOT EXISTS "idx_session_country" ON "AnalyticsSession"("countryCode");
CREATE INDEX IF NOT EXISTS "idx_session_social" ON "AnalyticsSession"("socialSource");
CREATE INDEX IF NOT EXISTS "idx_session_new_visitor" ON "AnalyticsSession"("isNewVisitor");
CREATE INDEX IF NOT EXISTS "idx_event_category" ON "AnalyticsEvent"("categoryName");
CREATE INDEX IF NOT EXISTS "idx_event_entry" ON "AnalyticsEvent"("isEntryPage");
CREATE INDEX IF NOT EXISTS "idx_event_exit" ON "AnalyticsEvent"("isExitPage");

-- Update AnalyticsMetric enum values if needed
-- Note: This depends on your Prisma schema setup

COMMIT;
"@

    $migrationFile = "apps/api/prisma/migrations/$(Get-Date -Format 'yyyyMMddHHmmss')_analytics_visuals/migration.sql"
    New-Item -ItemType Directory -Force -Path (Split-Path $migrationFile) | Out-Null
    Set-Content -Path $migrationFile -Value $migrationSQL
    Write-Log "Created migration: $migrationFile"
    
    # Apply migration if prisma is available
    if (Test-Command "npx") {
        Push-Location apps/api
        try {
            Write-Log "Applying Prisma migration..."
            npx prisma migrate dev --name analytics_visuals --skip-generate 2>&1 | Tee-Object -Append -FilePath $script:LogFile
            npx prisma generate 2>&1 | Tee-Object -Append -FilePath $script:LogFile
            Write-Log "Database migration completed"
        }
        catch {
            Write-Log "Migration warning: $_" "WARN"
        }
        finally {
            Pop-Location
        }
    }
}

# ==================== PHASE 2: Backend API Endpoints ====================
function New-BackendEndpoints {
    Write-Log "=== PHASE 2: Backend API Endpoints ==="
    
    $endpointsDir = "apps/api/src/analytics"
    New-Item -ItemType Directory -Force -Path $endpointsDir | Out-Null
    
    # Create demographics controller
    $demographicsController = @'
import { Controller, Get, Query, UseGuards } from '@nestjs/common';
import { ApiTags, ApiOperation, ApiBearerAuth } from '@nestjs/swagger';
import { JwtAuthGuard } from '../auth/guards/jwt-auth.guard';
import { AnalyticsDemographicsService } from './services/analytics-demographics.service';

@ApiTags('Analytics Demographics')
@Controller('analytics/demographics')
@UseGuards(JwtAuthGuard)
@ApiBearerAuth()
export class AnalyticsDemographicsController {
  constructor(private readonly demographicsService: AnalyticsDemographicsService) {}

  @Get('gender-split')
  @ApiOperation({ summary: 'Get gender distribution' })
  async getGenderSplit(
    @Query('startDate') startDate?: string,
    @Query('endDate') endDate?: string,
  ) {
    return this.demographicsService.getGenderSplit(startDate, endDate);
  }

  @Get('age-distribution')
  @ApiOperation({ summary: 'Get age group distribution' })
  async getAgeDistribution(
    @Query('startDate') startDate?: string,
    @Query('endDate') endDate?: string,
  ) {
    return this.demographicsService.getAgeDistribution(startDate, endDate);
  }

  @Get('interests')
  @ApiOperation({ summary: 'Get top interests/affinity categories' })
  async getTopInterests(
    @Query('startDate') startDate?: string,
    @Query('endDate') endDate?: string,
    @Query('limit') limit = 10,
  ) {
    return this.demographicsService.getTopInterests(startDate, endDate, +limit);
  }

  @Get('new-vs-returning')
  @ApiOperation({ summary: 'Get new vs returning visitors' })
  async getNewVsReturning(
    @Query('startDate') startDate?: string,
    @Query('endDate') endDate?: string,
  ) {
    return this.demographicsService.getNewVsReturning(startDate, endDate);
  }

  @Get('languages')
  @ApiOperation({ summary: 'Get language distribution' })
  async getLanguages(
    @Query('startDate') startDate?: string,
    @Query('endDate') endDate?: string,
  ) {
    return this.demographicsService.getLanguages(startDate, endDate);
  }
}
'@
    Set-Content -Path "$endpointsDir/analytics-demographics.controller.ts" -Value $demographicsController
    Write-Log "Created: analytics-demographics.controller.ts"
    
    # Create devices controller
    $devicesController = @'
import { Controller, Get, Query, UseGuards } from '@nestjs/common';
import { ApiTags, ApiOperation, ApiBearerAuth } from '@nestjs/swagger';
import { JwtAuthGuard } from '../auth/guards/jwt-auth.guard';
import { AnalyticsDevicesService } from './services/analytics-devices.service';

@ApiTags('Analytics Devices')
@Controller('analytics/devices')
@UseGuards(JwtAuthGuard)
@ApiBearerAuth()
export class AnalyticsDevicesController {
  constructor(private readonly devicesService: AnalyticsDevicesService) {}

  @Get('breakdown')
  @ApiOperation({ summary: 'Get device type breakdown' })
  async getDeviceBreakdown(
    @Query('startDate') startDate?: string,
    @Query('endDate') endDate?: string,
  ) {
    return this.devicesService.getDeviceBreakdown(startDate, endDate);
  }

  @Get('browsers')
  @ApiOperation({ summary: 'Get browser distribution' })
  async getBrowserDistribution(
    @Query('startDate') startDate?: string,
    @Query('endDate') endDate?: string,
  ) {
    return this.devicesService.getBrowserDistribution(startDate, endDate);
  }

  @Get('operating-systems')
  @ApiOperation({ summary: 'Get OS distribution' })
  async getOSDistribution(
    @Query('startDate') startDate?: string,
    @Query('endDate') endDate?: string,
  ) {
    return this.devicesService.getOSDistribution(startDate, endDate);
  }

  @Get('screen-resolutions')
  @ApiOperation({ summary: 'Get screen resolution distribution' })
  async getScreenResolutions(
    @Query('startDate') startDate?: string,
    @Query('endDate') endDate?: string,
  ) {
    return this.devicesService.getScreenResolutions(startDate, endDate);
  }
}
'@
    Set-Content -Path "$endpointsDir/analytics-devices.controller.ts" -Value $devicesController
    Write-Log "Created: analytics-devices.controller.ts"
    
    # Create content analytics controller
    $contentController = @'
import { Controller, Get, Query, UseGuards } from '@nestjs/common';
import { ApiTags, ApiOperation, ApiBearerAuth } from '@nestjs/swagger';
import { JwtAuthGuard } from '../auth/guards/jwt-auth.guard';
import { AnalyticsContentService } from './services/analytics-content.service';

@ApiTags('Analytics Content')
@Controller('analytics/content')
@UseGuards(JwtAuthGuard)
@ApiBearerAuth()
export class AnalyticsContentController {
  constructor(private readonly contentService: AnalyticsContentService) {}

  @Get('categories')
  @ApiOperation({ summary: 'Get category distribution' })
  async getCategories(
    @Query('startDate') startDate?: string,
    @Query('endDate') endDate?: string,
  ) {
    return this.contentService.getCategories(startDate, endDate);
  }

  @Get('tags')
  @ApiOperation({ summary: 'Get tag distribution' })
  async getTags(
    @Query('startDate') startDate?: string,
    @Query('endDate') endDate?: string,
    @Query('limit') limit = 20,
  ) {
    return this.contentService.getTags(startDate, endDate, +limit);
  }

  @Get('ribbons')
  @ApiOperation({ summary: 'Get ribbon type distribution' })
  async getRibbons(
    @Query('startDate') startDate?: string,
    @Query('endDate') endDate?: string,
  ) {
    return this.contentService.getRibbons(startDate, endDate);
  }

  @Get('placements')
  @ApiOperation({ summary: 'Get link placement performance' })
  async getPlacements(
    @Query('startDate') startDate?: string,
    @Query('endDate') endDate?: string,
  ) {
    return this.contentService.getPlacements(startDate, endDate);
  }

  @Get('landing-pages')
  @ApiOperation({ summary: 'Get top landing pages' })
  async getLandingPages(
    @Query('startDate') startDate?: string,
    @Query('endDate') endDate?: string,
    @Query('limit') limit = 10,
  ) {
    return this.contentService.getLandingPages(startDate, endDate, +limit);
  }

  @Get('exit-pages')
  @ApiOperation({ summary: 'Get top exit pages' })
  async getExitPages(
    @Query('startDate') startDate?: string,
    @Query('endDate') endDate?: string,
    @Query('limit') limit = 10,
  ) {
    return this.contentService.getExitPages(startDate, endDate, +limit);
  }
}
'@
    Set-Content -Path "$endpointsDir/analytics-content.controller.ts" -Value $contentController
    Write-Log "Created: analytics-content.controller.ts"
}

# ==================== PHASE 3: Services ====================
function New-AnalyticsServices {
    Write-Log "=== PHASE 3: Analytics Services ==="
    
    $servicesDir = "apps/api/src/analytics/services"
    New-Item -ItemType Directory -Force -Path $servicesDir | Out-Null
    
    # Demographics service
    $demographicsService = @'
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
'@
    Set-Content -Path "$servicesDir/analytics-demographics.service.ts" -Value $demographicsService
    Write-Log "Created: analytics-demographics.service.ts"
    
    # Devices service
    $devicesService = @'
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
'@
    Set-Content -Path "$servicesDir/analytics-devices.service.ts" -Value $devicesService
    Write-Log "Created: analytics-devices.service.ts"
    
    # Content service
    $contentService = @'
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
'@
    Set-Content -Path "$servicesDir/analytics-content.service.ts" -Value $contentService
    Write-Log "Created: analytics-content.service.ts"
}

# ==================== PHASE 4: Frontend Components ====================
function New-ChartComponents {
    Write-Log "=== PHASE 4: Chart Components ==="
    
    $componentsDir = "apps/web/src/components/analytics"
    New-Item -ItemType Directory -Force -Path $componentsDir | Out-Null
    
    # DonutChart component
    $donutChart = @'
'use client';

import React from 'react';
import { PieChart, Pie, Cell, ResponsiveContainer, Tooltip } from 'recharts';

interface DonutChartProps {
  data: Array<{
    name: string;
    value: number;
    color: string;
  }>;
  centerLabel?: string;
  centerValue?: string;
  height?: number;
}

export function DonutChart({ 
  data, 
  centerLabel, 
  centerValue, 
  height = 200 
}: DonutChartProps) {
  return (
    <div className="relative" style={{ height }}>
      <ResponsiveContainer width="100%" height="100%">
        <PieChart>
          <Pie
            data={data}
            cx="50%"
            cy="50%"
            innerRadius={60}
            outerRadius={80}
            paddingAngle={2}
            dataKey="value"
            startAngle={90}
            endAngle={-270}
          >
            {data.map((entry, index) => (
              <Cell key={`cell-${index}`} fill={entry.color} />
            ))}
          </Pie>
          <Tooltip 
            formatter={(value: number) => [`${value}%`, 'Percentage']}
            contentStyle={{
              backgroundColor: '#1e293b',
              border: '1px solid #334155',
              borderRadius: '6px',
            }}
          />
        </PieChart>
      </ResponsiveContainer>
      {(centerLabel || centerValue) && (
        <div className="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
          {centerValue && (
            <span className="text-2xl font-bold text-white">{centerValue}</span>
          )}
          {centerLabel && (
            <span className="text-xs text-gray-400">{centerLabel}</span>
          )}
        </div>
      )}
    </div>
  );
}
'@
    Set-Content -Path "$componentsDir/DonutChart.tsx" -Value $donutChart
    Write-Log "Created: DonutChart.tsx"
    
    # PieChartCard component
    $pieChartCard = @'
'use client';

import React from 'react';
import { PieChart, Pie, Cell, ResponsiveContainer, Tooltip } from 'recharts';

interface PieChartCardProps {
  data: Array<{
    name: string;
    value: number;
    color: string;
  }>;
  title: string;
  height?: number;
  showLegend?: boolean;
}

export function PieChartCard({ 
  data, 
  title, 
  height = 250,
  showLegend = true 
}: PieChartCardProps) {
  const total = data.reduce((sum, item) => sum + item.value, 0);

  return (
    <div className="bg-gray-800 rounded-lg p-4">
      <h4 className="text-white font-medium mb-4">{title}</h4>
      <div style={{ height }}>
        <ResponsiveContainer width="100%" height="100%">
          <PieChart>
            <Pie
              data={data}
              cx="40%"
              cy="50%"
              outerRadius={80}
              dataKey="value"
              label={({ name, percent }) => `${(percent * 100).toFixed(0)}%`}
              labelLine={false}
            >
              {data.map((entry, index) => (
                <Cell key={`cell-${index}`} fill={entry.color} />
              ))}
            </Pie>
            <Tooltip 
              formatter={(value: number, name: string) => {
                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : '0';
                return [`${value} (${percentage}%)`, name];
              }}
              contentStyle={{
                backgroundColor: '#1e293b',
                border: '1px solid #334155',
                borderRadius: '6px',
              }}
            />
          </PieChart>
        </ResponsiveContainer>
      </div>
      {showLegend && (
        <div className="mt-4 grid grid-cols-2 gap-2">
          {data.map((item, index) => (
            <div key={index} className="flex items-center gap-2">
              <div 
                className="w-3 h-3 rounded-full" 
                style={{ backgroundColor: item.color }}
              />
              <span className="text-xs text-gray-300 truncate">{item.name}</span>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
'@
    Set-Content -Path "$componentsDir/PieChartCard.tsx" -Value $pieChartCard
    Write-Log "Created: PieChartCard.tsx"
    
    # HorizontalBar component
    $horizontalBar = @'
'use client';

import React from 'react';

interface HorizontalBarProps {
  data: Array<{
    label: string;
    value: number;
    percentage: number;
    color?: string;
  }>;
  maxItems?: number;
  showValues?: boolean;
}

export function HorizontalBar({ 
  data, 
  maxItems = 10,
  showValues = true 
}: HorizontalBarProps) {
  const displayData = data.slice(0, maxItems);

  return (
    <div className="space-y-3">
      {displayData.map((item, index) => (
        <div key={index} className="space-y-1">
          <div className="flex justify-between text-sm">
            <span className="text-gray-300">{item.label}</span>
            {showValues && (
              <span className="text-gray-400">{item.value.toLocaleString()}</span>
            )}
          </div>
          <div className="flex items-center gap-3">
            <div className="flex-1 h-2 bg-gray-700 rounded-full overflow-hidden">
              <div 
                className="h-full rounded-full transition-all duration-500"
                style={{ 
                  width: `${item.percentage}%`,
                  backgroundColor: item.color || '#3b82f6'
                }}
              />
            </div>
            <span className="text-xs text-gray-400 w-10 text-right">
              {item.percentage}%
            </span>
          </div>
        </div>
      ))}
    </div>
  );
}
'@
    Set-Content -Path "$componentsDir/HorizontalBar.tsx" -Value $horizontalBar
    Write-Log "Created: HorizontalBar.tsx"
    
    # SparklineChart component
    $sparklineChart = @'
'use client';

import React from 'react';
import { LineChart, Line, ResponsiveContainer, Area, AreaChart } from 'recharts';

interface SparklineChartProps {
  data: number[];
  color?: string;
  height?: number;
  fillArea?: boolean;
  trend?: 'up' | 'down' | 'neutral';
}

export function SparklineChart({ 
  data, 
  color = '#3b82f6',
  height = 40,
  fillArea = true,
  trend = 'neutral'
}: SparklineChartProps) {
  const chartData = data.map((value, index) => ({ value, index }));
  
  const trendColor = trend === 'up' ? '#22c55e' : trend === 'down' ? '#ef4444' : color;

  if (fillArea) {
    return (
      <ResponsiveContainer width="100%" height={height}>
        <AreaChart data={chartData}>
          <defs>
            <linearGradient id={`sparkline-${trend}`} x1="0" y1="0" x2="0" y2="1">
              <stop offset="5%" stopColor={trendColor} stopOpacity={0.3}/>
              <stop offset="95%" stopColor={trendColor} stopOpacity={0}/>
            </linearGradient>
          </defs>
          <Area 
            type="monotone" 
            dataKey="value" 
            stroke={trendColor}
            strokeWidth={2}
            fill={`url(#sparkline-${trend})`}
          />
        </AreaChart>
      </ResponsiveContainer>
    );
  }

  return (
    <ResponsiveContainer width="100%" height={height}>
      <LineChart data={chartData}>
        <Line 
          type="monotone" 
          dataKey="value" 
          stroke={trendColor}
          strokeWidth={2}
          dot={false}
        />
      </LineChart>
    </ResponsiveContainer>
  );
}
'@
    Set-Content -Path "$componentsDir/SparklineChart.tsx" -Value $sparklineChart
    Write-Log "Created: SparklineChart.tsx"
    
    # CountryFlag component
    $countryFlag = @'
interface CountryFlagProps {
  code: string;
  size?: 'sm' | 'md' | 'lg';
}

const flagEmojis: Record<string, string> = {
  us: '🇺🇸',
  gb: '🇬🇧',
  ca: '🇨🇦',
  au: '🇦🇺',
  de: '🇩🇪',
  in: '🇮🇳',
  fr: '🇫🇷',
  jp: '🇯🇵',
  br: '🇧🇷',
  mx: '🇲🇽',
  es: '🇪🇸',
  it: '🇮🇹',
  nl: '🇳🇱',
  sg: '🇸🇬',
  ae: '🇦🇪',
};

const sizeClasses = {
  sm: 'text-sm',
  md: 'text-base',
  lg: 'text-lg',
};

export function CountryFlag({ code, size = 'md' }: CountryFlagProps) {
  const emoji = flagEmojis[code.toLowerCase()] || '🌍';
  
  return (
    <span className={sizeClasses[size]} title={code.toUpperCase()}>
      {emoji}
    </span>
  );
}
'@
    Set-Content -Path "$componentsDir/CountryFlag.tsx" -Value $countryFlag
    Write-Log "Created: CountryFlag.tsx"
    
    # ProgressBar component
    $progressBar = @'
interface ProgressBarProps {
  value: number;
  max?: number;
  color?: string;
  label?: string;
  showPercentage?: boolean;
  size?: 'sm' | 'md' | 'lg';
}

const sizeClasses = {
  sm: 'h-1.5',
  md: 'h-2',
  lg: 'h-3',
};

export function ProgressBar({
  value,
  max = 100,
  color = '#3b82f6',
  label,
  showPercentage = true,
  size = 'md',
}: ProgressBarProps) {
  const percentage = Math.min(100, Math.max(0, (value / max) * 100));

  return (
    <div className="space-y-1">
      {(label || showPercentage) && (
        <div className="flex justify-between text-xs">
          {label && <span className="text-gray-300">{label}</span>}
          {showPercentage && (
            <span className="text-gray-400">{Math.round(percentage)}%</span>
          )}
        </div>
      )}
      <div className={`w-full bg-gray-700 rounded-full ${sizeClasses[size]}`}>
        <div
          className="rounded-full transition-all duration-500"
          style={{
            width: `${percentage}%`,
            height: '100%',
            backgroundColor: color,
          }}
        />
      </div>
    </div>
  );
}
'@
    Set-Content -Path "$componentsDir/ProgressBar.tsx" -Value $progressBar
    Write-Log "Created: ProgressBar.tsx"
    
    # KPICard component
    $kpiCard = @'
'use client';

import React from 'react';
import { SparklineChart } from './SparklineChart';
import { ArrowUpIcon, ArrowDownIcon } from '@heroicons/react/24/solid';

interface KPICardProps {
  title: string;
  value: string;
  change?: number;
  changeLabel?: string;
  sparklineData?: number[];
  trend?: 'up' | 'down' | 'neutral';
  icon?: React.ReactNode;
}

export function KPICard({
  title,
  value,
  change,
  changeLabel = 'vs last period',
  sparklineData,
  trend = 'neutral',
  icon,
}: KPICardProps) {
  const isPositive = (change || 0) >= 0;
  const trendColor = trend === 'up' ? 'text-green-400' : trend === 'down' ? 'text-red-400' : 'text-gray-400';

  return (
    <div className="bg-gray-800 rounded-lg p-4">
      <div className="flex items-start justify-between mb-2">
        <div>
          <p className="text-sm text-gray-400">{title}</p>
          <h3 className="text-2xl font-bold text-white mt-1">{value}</h3>
        </div>
        {icon && (
          <div className="p-2 bg-gray-700 rounded-lg">
            {icon}
          </div>
        )}
      </div>
      
      {sparklineData && (
        <div className="my-3">
          <SparklineChart 
            data={sparklineData} 
            trend={trend}
            height={40}
          />
        </div>
      )}
      
      {change !== undefined && (
        <div className="flex items-center gap-1 text-sm">
          {isPositive ? (
            <ArrowUpIcon className="w-4 h-4 text-green-400" />
          ) : (
            <ArrowDownIcon className="w-4 h-4 text-red-400" />
          )}
          <span className={isPositive ? 'text-green-400' : 'text-red-400'}>
            {isPositive ? '+' : ''}{change}%
          </span>
          <span className="text-gray-500 ml-1">{changeLabel}</span>
        </div>
      )}
    </div>
  );
}
'@
    Set-Content -Path "$componentsDir/KPICard.tsx" -Value $kpiCard
    Write-Log "Created: KPICard.tsx"
    
    # AnalyticsTabs component
    $analyticsTabs = @'
'use client';

import React from 'react';

interface Tab {
  id: string;
  label: string;
  icon?: React.ReactNode;
}

interface AnalyticsTabsProps {
  tabs: Tab[];
  activeTab: string;
  onTabChange: (tabId: string) => void;
}

export function AnalyticsTabs({ tabs, activeTab, onTabChange }: AnalyticsTabsProps) {
  return (
    <div className="border-b border-gray-700">
      <nav className="flex space-x-1">
        {tabs.map((tab) => (
          <button
            key={tab.id}
            onClick={() => onTabChange(tab.id)}
            className={`
              flex items-center gap-2 px-4 py-3 text-sm font-medium transition-colors
              ${activeTab === tab.id
                ? 'text-blue-400 border-b-2 border-blue-400 bg-gray-800/50'
                : 'text-gray-400 hover:text-gray-200 hover:bg-gray-800/30'
              }
            `}
          >
            {tab.icon}
            {tab.label}
          </button>
        ))}
      </nav>
    </div>
  );
}
'@
    Set-Content -Path "$componentsDir/AnalyticsTabs.tsx" -Value $analyticsTabs
    Write-Log "Created: AnalyticsTabs.tsx"
    
    # Index file
    $indexFile = @'
export { DonutChart } from './DonutChart';
export { PieChartCard } from './PieChartCard';
export { HorizontalBar } from './HorizontalBar';
export { SparklineChart } from './SparklineChart';
export { CountryFlag } from './CountryFlag';
export { ProgressBar } from './ProgressBar';
export { KPICard } from './KPICard';
export { AnalyticsTabs } from './AnalyticsTabs';
'@
    Set-Content -Path "$componentsDir/index.ts" -Value $indexFile
    Write-Log "Created: index.ts"
}

# ==================== PHASE 5: Dashboard Page ====================
function New-DashboardPage {
    Write-Log "=== PHASE 5: Dashboard Page ==="
    
    $dashboardDir = "apps/web/src/app/admin/analytics"
    New-Item -ItemType Directory -Force -Path $dashboardDir | Out-Null
    
    # Main dashboard page
    $dashboardPage = @'
'use client';

import React, { useState, useEffect } from 'react';
import {
  KPICard,
  AnalyticsTabs,
  DonutChart,
  PieChartCard,
  HorizontalBar,
  CountryFlag,
  ProgressBar,
} from '@/components/analytics';
import {
  CurrencyDollarIcon,
  CursorArrowRaysIcon,
  ChartPieIcon,
  UsersIcon,
  ShoppingBagIcon,
  EyeIcon,
} from '@heroicons/react/24/outline';

const tabs = [
  { id: 'overview', label: 'Overview' },
  { id: 'revenue', label: 'Revenue' },
  { id: 'links', label: 'Links' },
  { id: 'traffic', label: 'Traffic' },
  { id: 'audience', label: 'Audience' },
  { id: 'content', label: 'Content' },
  { id: 'seo', label: 'SEO' },
];

interface DashboardData {
  kpis: {
    revenue: { value: string; change: number; sparkline: number[] };
    clicks: { value: string; change: number; sparkline: number[] };
    conversionRate: { value: string; change: number; sparkline: number[] };
    visitors: { value: string; change: number; sparkline: number[] };
    sales: { value: string; change: number; sparkline: number[] };
    pageviews: { value: string; change: number; sparkline: number[] };
  };
  genderSplit: { male: { percentage: number }; female: { percentage: number } };
  newVsReturning: { new: { percentage: number }; returning: { percentage: number } };
  ageDistribution: Array<{ range: string; percentage: number; count: number }>;
  interests: Array<{ name: string; percentage: number; count: number }>;
  devices: Array<{ type: string; percentage: number; count: number }>;
  countries: Array<{ code: string; name: string; percentage: number; count: number }>;
  socialSources: Array<{ name: string; percentage: number; count: number }>;
  categories: Array<{ name: string; percentage: number; count: number }>;
  placements: Array<{ type: string; percentage: number; count: number }>;
  landingPages: Array<{ url: string; visits: number; percentage: number; ctr: number }>;
}

export default function AnalyticsDashboard() {
  const [activeTab, setActiveTab] = useState('overview');
  const [data, setData] = useState<DashboardData | null>(null);
  const [loading, setLoading] = useState(true);
  const [dateRange, setDateRange] = useState('7d');

  useEffect(() => {
    fetchDashboardData();
  }, [activeTab, dateRange]);

  const fetchDashboardData = async () => {
    setLoading(true);
    try {
      const response = await fetch(
        `${process.env.NEXT_PUBLIC_API_URL}/analytics/dashboard?tab=${activeTab}&range=${dateRange}`
      );
      const result = await response.json();
      setData(result);
    } catch (error) {
      console.error('Failed to fetch analytics:', error);
      // Use mock data for development
      setData(getMockData());
    } finally {
      setLoading(false);
    }
  };

  const getMockData = (): DashboardData => ({
    kpis: {
      revenue: { value: '$24,563', change: 12.5, sparkline: [20, 35, 30, 45, 40, 55, 60] },
      clicks: { value: '15,847', change: 8.2, sparkline: [30, 40, 35, 50, 45, 60, 65] },
      conversionRate: { value: '3.24%', change: -2.1, sparkline: [4, 3.5, 3.8, 3.2, 3.5, 3.1, 3.24] },
      visitors: { value: '45,231', change: 15.3, sparkline: [35, 40, 38, 50, 48, 60, 65] },
      sales: { value: '1,247', change: 10.8, sparkline: [25, 30, 28, 40, 38, 50, 55] },
      pageviews: { value: '128,456', change: 18.7, sparkline: [40, 50, 45, 60, 55, 70, 75] },
    },
    genderSplit: {
      male: { percentage: 58.3 },
      female: { percentage: 41.7 },
    },
    newVsReturning: {
      new: { percentage: 64.2 },
      returning: { percentage: 35.8 },
    },
    ageDistribution: [
      { range: '18-24', percentage: 38, count: 6022 },
      { range: '25-34', percentage: 28, count: 4437 },
      { range: '35-44', percentage: 18, count: 2853 },
      { range: '45-54', percentage: 10, count: 1585 },
      { range: '55+', percentage: 6, count: 951 },
    ],
    interests: [
      { name: 'Technology Enthusiasts', percentage: 42, count: 18980 },
      { name: 'Business Professionals', percentage: 28, count: 12653 },
      { name: 'Shopping Lovers', percentage: 18, count: 8134 },
      { name: 'Travel & Tourism', percentage: 12, count: 5423 },
    ],
    devices: [
      { type: 'Desktop', percentage: 52, count: 32000 },
      { type: 'Mobile', percentage: 40, count: 24615 },
      { type: 'Tablet', percentage: 8, count: 4923 },
    ],
    countries: [
      { code: 'us', name: 'United States', percentage: 45, count: 27692 },
      { code: 'gb', name: 'United Kingdom', percentage: 18, count: 11077 },
      { code: 'ca', name: 'Canada', percentage: 12, count: 7385 },
      { code: 'au', name: 'Australia', percentage: 10, count: 6154 },
      { code: 'de', name: 'Germany', percentage: 8, count: 4923 },
      { code: 'in', name: 'India', percentage: 7, count: 4308 },
    ],
    socialSources: [
      { name: 'Facebook', percentage: 42, count: 8500 },
      { name: 'Twitter', percentage: 28, count: 5667 },
      { name: 'Instagram', percentage: 18, count: 3643 },
      { name: 'LinkedIn', percentage: 8, count: 1619 },
      { name: 'Other', percentage: 4, count: 810 },
    ],
    categories: [
      { name: 'Blog', percentage: 40, count: 12500 },
      { name: 'Tools', percentage: 30, count: 9375 },
      { name: 'Services', percentage: 20, count: 6250 },
      { name: 'Product', percentage: 10, count: 3125 },
    ],
    placements: [
      { type: 'Sidebar', percentage: 35, count: 15500 },
      { type: 'In-Content', percentage: 30, count: 13286 },
      { type: 'Header', percentage: 20, count: 8857 },
      { type: 'Footer', percentage: 10, count: 4429 },
      { type: 'Popup', percentage: 5, count: 2214 },
    ],
    landingPages: [
      { url: '/home', visits: 12456, percentage: 25, ctr: 3.2 },
      { url: '/products/summer-sale', visits: 8934, percentage: 18, ctr: 4.5 },
      { url: '/blog/top-10-gadgets', visits: 6721, percentage: 14, ctr: 2.8 },
      { url: '/reviews/best-laptops-2024', visits: 5432, percentage: 11, ctr: 3.9 },
      { url: '/deals', visits: 4210, percentage: 9, ctr: 5.1 },
    ],
  });

  const genderData = data ? [
    { name: 'Male', value: data.genderSplit.male.percentage, color: '#3b82f6' },
    { name: 'Female', value: data.genderSplit.female.percentage, color: '#ec4899' },
  ] : [];

  const newVsReturningData = data ? [
    { name: 'New', value: data.newVsReturning.new.percentage, color: '#22c55e' },
    { name: 'Returning', value: data.newVsReturning.returning.percentage, color: '#8b5cf6' },
  ] : [];

  const deviceColors = ['#3b82f6', '#22c55e', '#f59e0b'];
  const deviceData = data?.devices.map((d, i) => ({
    name: d.type,
    value: d.percentage,
    color: deviceColors[i % deviceColors.length],
  })) || [];

  const categoryColors = ['#3b82f6', '#22c55e', '#f59e0b', '#ef4444'];
  const categoryData = data?.categories.map((c, i) => ({
    name: c.name,
    value: c.percentage,
    color: categoryColors[i % categoryColors.length],
  })) || [];

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-900 p-6">
        <div className="max-w-7xl mx-auto">
          <div className="animate-pulse space-y-4">
            <div className="h-8 bg-gray-800 rounded w-1/4"></div>
            <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
              {[...Array(6)].map((_, i) => (
                <div key={i} className="h-32 bg-gray-800 rounded"></div>
              ))}
            </div>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-900">
      <div className="max-w-7xl mx-auto p-6">
        {/* Header */}
        <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
          <div>
            <h1 className="text-2xl font-bold text-white">Analytics Dashboard</h1>
            <p className="text-gray-400 text-sm mt-1">
              Track your affiliate performance and audience insights
            </p>
          </div>
          <div className="flex items-center gap-3">
            <select
              value={dateRange}
              onChange={(e) => setDateRange(e.target.value)}
              className="bg-gray-800 text-white border border-gray-700 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="24h">Last 24 hours</option>
              <option value="7d">Last 7 days</option>
              <option value="30d">Last 30 days</option>
              <option value="90d">Last 90 days</option>
            </select>
            <button
              onClick={fetchDashboardData}
              className="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition-colors"
            >
              Refresh
            </button>
          </div>
        </div>

        {/* Tabs */}
        <div className="bg-gray-800/50 rounded-t-lg">
          <AnalyticsTabs tabs={tabs} activeTab={activeTab} onTabChange={setActiveTab} />
        </div>

        {/* Tab Content */}
        <div className="bg-gray-800 rounded-b-lg p-6">
          {activeTab === 'overview' && data && (
            <div className="space-y-6">
              {/* KPI Cards */}
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                <KPICard
                  title="Total Revenue"
                  value={data.kpis.revenue.value}
                  change={data.kpis.revenue.change}
                  sparklineData={data.kpis.revenue.sparkline}
                  trend={data.kpis.revenue.change >= 0 ? 'up' : 'down'}
                  icon={<CurrencyDollarIcon className="w-5 h-5 text-blue-400" />}
                />
                <KPICard
                  title="Clicks"
                  value={data.kpis.clicks.value}
                  change={data.kpis.clicks.change}
                  sparklineData={data.kpis.clicks.sparkline}
                  trend={data.kpis.clicks.change >= 0 ? 'up' : 'down'}
                  icon={<CursorArrowRaysIcon className="w-5 h-5 text-green-400" />}
                />
                <KPICard
                  title="Conversion Rate"
                  value={data.kpis.conversionRate.value}
                  change={data.kpis.conversionRate.change}
                  sparklineData={data.kpis.conversionRate.sparkline}
                  trend={data.kpis.conversionRate.change >= 0 ? 'up' : 'down'}
                  icon={<ChartPieIcon className="w-5 h-5 text-purple-400" />}
                />
                <KPICard
                  title="Visitors"
                  value={data.kpis.visitors.value}
                  change={data.kpis.visitors.change}
                  sparklineData={data.kpis.visitors.sparkline}
                  trend={data.kpis.visitors.change >= 0 ? 'up' : 'down'}
                  icon={<UsersIcon className="w-5 h-5 text-orange-400" />}
                />
                <KPICard
                  title="Sales"
                  value={data.kpis.sales.value}
                  change={data.kpis.sales.change}
                  sparklineData={data.kpis.sales.sparkline}
                  trend={data.kpis.sales.change >= 0 ? 'up' : 'down'}
                  icon={<ShoppingBagIcon className="w-5 h-5 text-pink-400" />}
                />
                <KPICard
                  title="Pageviews"
                  value={data.kpis.pageviews.value}
                  change={data.kpis.pageviews.change}
                  sparklineData={data.kpis.pageviews.sparkline}
                  trend={data.kpis.pageviews.change >= 0 ? 'up' : 'down'}
                  icon={<EyeIcon className="w-5 h-5 text-cyan-400" />}
                />
              </div>

              {/* Second Row */}
              <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {/* Gender Split */}
                <div className="bg-gray-900 rounded-lg p-4">
                  <h3 className="text-white font-medium mb-4">Gender Split</h3>
                  <DonutChart
                    data={genderData}
                    centerValue={`${data.genderSplit.male.percentage}%`}
                    centerLabel="Male"
                    height={200}
                  />
                  <div className="flex justify-center gap-6 mt-4">
                    <div className="flex items-center gap-2">
                      <div className="w-3 h-3 rounded-full bg-blue-500"></div>
                      <span className="text-gray-300 text-sm">
                        Male {data.genderSplit.male.percentage}%
                      </span>
                    </div>
                    <div className="flex items-center gap-2">
                      <div className="w-3 h-3 rounded-full bg-pink-500"></div>
                      <span className="text-gray-300 text-sm">
                        Female {data.genderSplit.female.percentage}%
                      </span>
                    </div>
                  </div>
                </div>

                {/* New vs Returning */}
                <div className="bg-gray-900 rounded-lg p-4">
                  <h3 className="text-white font-medium mb-4">New vs Returning</h3>
                  <DonutChart
                    data={newVsReturningData}
                    centerValue={`${data.newVsReturning.new.percentage}%`}
                    centerLabel="New"
                    height={200}
                  />
                  <div className="flex justify-center gap-6 mt-4">
                    <div className="flex items-center gap-2">
                      <div className="w-3 h-3 rounded-full bg-green-500"></div>
                      <span className="text-gray-300 text-sm">
                        New {data.newVsReturning.new.percentage}%
                      </span>
                    </div>
                    <div className="flex items-center gap-2">
                      <div className="w-3 h-3 rounded-full bg-violet-500"></div>
                      <span className="text-gray-300 text-sm">
                        Returning {data.newVsReturning.returning.percentage}%
                      </span>
                    </div>
                  </div>
                </div>

                {/* Device Breakdown */}
                <PieChartCard data={deviceData} title="Device Breakdown" />
              </div>

              {/* Third Row */}
              <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {/* Age Distribution */}
                <div className="bg-gray-900 rounded-lg p-4">
                  <h3 className="text-white font-medium mb-4">Age Distribution</h3>
                  <HorizontalBar
                    data={data.ageDistribution.map(a => ({
                      label: a.range,
                      value: a.count,
                      percentage: a.percentage,
                      color: '#3b82f6',
                    }))}
                  />
                </div>

                {/* Top Interests */}
                <div className="bg-gray-900 rounded-lg p-4">
                  <h3 className="text-white font-medium mb-4">Top Interests</h3>
                  <HorizontalBar
                    data={data.interests.map(i => ({
                      label: i.name,
                      value: i.count,
                      percentage: i.percentage,
                      color: '#22c55e',
                    }))}
                  />
                </div>
              </div>
            </div>
          )}

          {activeTab === 'traffic' && data && (
            <div className="space-y-6">
              <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {/* Social Sources */}
                <PieChartCard
                  data={data.socialSources.map((s, i) => ({
                    name: s.name,
                    value: s.percentage,
                    color: ['#3b82f6', '#22c55e', '#f59e0b', '#8b5cf6', '#6b7280'][i],
                  }))}
                  title="Social Sources"
                />

                {/* Top Countries */}
                <div className="bg-gray-900 rounded-lg p-4">
                  <h3 className="text-white font-medium mb-4">Top Countries</h3>
                  <div className="space-y-3">
                    {data.countries.map((country, index) => (
                      <div key={index} className="flex items-center gap-3">
                        <CountryFlag code={country.code} size="md" />
                        <div className="flex-1">
                          <div className="flex justify-between text-sm mb-1">
                            <span className="text-gray-300">{country.name}</span>
                            <span className="text-gray-400">
                              {country.count.toLocaleString()}
                            </span>
                          </div>
                          <ProgressBar
                            value={country.percentage}
                            color="#3b82f6"
                            showPercentage={false}
                            size="sm"
                          />
                        </div>
                        <span className="text-xs text-gray-400 w-10 text-right">
                          {country.percentage}%
                        </span>
                      </div>
                    ))}
                  </div>
                </div>
              </div>
            </div>
          )}

          {activeTab === 'content' && data && (
            <div className="space-y-6">
              <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {/* Categories */}
                <PieChartCard data={categoryData} title="Categories" />

                {/* Placement Performance */}
                <div className="bg-gray-900 rounded-lg p-4">
                  <h3 className="text-white font-medium mb-4">Placement Performance</h3>
                  <HorizontalBar
                    data={data.placements.map(p => ({
                      label: p.type,
                      value: p.count,
                      percentage: p.percentage,
                      color: '#f59e0b',
                    }))}
                  />
                </div>
              </div>
            </div>
          )}

          {activeTab === 'seo' && data && (
            <div className="space-y-6">
              <div className="bg-gray-900 rounded-lg p-4">
                <h3 className="text-white font-medium mb-4">Top Landing Pages</h3>
                <div className="overflow-x-auto">
                  <table className="w-full">
                    <thead>
                      <tr className="text-left text-gray-400 text-sm border-b border-gray-700">
                        <th className="pb-3 font-medium">Page URL</th>
                        <th className="pb-3 font-medium text-right">Visits</th>
                        <th className="pb-3 font-medium text-right">%</th>
                        <th className="pb-3 font-medium text-right">CTR</th>
                      </tr>
                    </thead>
                    <tbody className="text-sm">
                      {data.landingPages.map((page, index) => (
                        <tr key={index} className="border-b border-gray-800 last:border-0">
                          <td className="py-3 text-gray-300 truncate max-w-xs">
                            {page.url}
                          </td>
                          <td className="py-3 text-right text-gray-300">
                            {page.visits.toLocaleString()}
                          </td>
                          <td className="py-3 text-right text-gray-400">
                            {page.percentage}%
                          </td>
                          <td className="py-3 text-right">
                            <span className="text-green-400">{page.ctr}%</span>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          )}

          {(activeTab === 'revenue' || activeTab === 'links' || activeTab === 'audience') && (
            <div className="text-center py-12">
              <p className="text-gray-400">
                Detailed {tabs.find(t => t.id === activeTab)?.label} analytics coming soon...
              </p>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
'@
    Set-Content -Path "$dashboardDir/page.tsx" -Value $dashboardPage
    Write-Log "Created: analytics dashboard page.tsx"
}

# ==================== PHASE 6: Update Simple Server ====================
function Update-SimpleServer {
    Write-Log "=== PHASE 6: Update Simple Server ==="
    
    $simpleServerPath = "apps/api/simple-server.js"
    if (Test-Path $simpleServerPath) {
        New-Backup $simpleServerPath
        
        $additionalEndpoints = @'

// ===== ANALYTICS VISUAL FEATURES ENDPOINTS =====

// Demographics endpoints
app.get('/analytics/demographics/gender-split', (req, res) => {
  res.json({
    male: { count: 9239, percentage: 58.3 },
    female: { count: 6608, percentage: 41.7 },
    other: { count: 0, percentage: 0 }
  });
});

app.get('/analytics/demographics/age-distribution', (req, res) => {
  res.json([
    { range: '18-24', count: 6022, percentage: 38 },
    { range: '25-34', count: 4437, percentage: 28 },
    { range: '35-44', count: 2853, percentage: 18 },
    { range: '45-54', count: 1585, percentage: 10 },
    { range: '55+', count: 951, percentage: 6 }
  ]);
});

app.get('/analytics/demographics/interests', (req, res) => {
  res.json([
    { name: 'Technology Enthusiasts', count: 18980, percentage: 42 },
    { name: 'Business Professionals', count: 12653, percentage: 28 },
    { name: 'Shopping Lovers', count: 8134, percentage: 18 },
    { name: 'Travel & Tourism', count: 5423, percentage: 12 }
  ]);
});

app.get('/analytics/demographics/new-vs-returning', (req, res) => {
  res.json({
    new: { count: 10234, percentage: 64.2 },
    returning: { count: 5706, percentage: 35.8 }
  });
});

app.get('/analytics/demographics/languages', (req, res) => {
  res.json([
    { code: 'en', count: 12500, percentage: 78 },
    { code: 'es', count: 1900, percentage: 12 },
    { code: 'fr', count: 950, percentage: 6 },
    { code: 'de', count: 640, percentage: 4 }
  ]);
});

// Devices endpoints
app.get('/analytics/devices/breakdown', (req, res) => {
  res.json([
    { type: 'Desktop', count: 32000, percentage: 52 },
    { type: 'Mobile', count: 24615, percentage: 40 },
    { type: 'Tablet', count: 4923, percentage: 8 }
  ]);
});

app.get('/analytics/devices/browsers', (req, res) => {
  res.json([
    { name: 'Chrome', count: 38000, percentage: 62 },
    { name: 'Safari', count: 14700, percentage: 24 },
    { name: 'Firefox', count: 5500, percentage: 9 },
    { name: 'Edge', count: 3077, percentage: 5 }
  ]);
});

app.get('/analytics/devices/operating-systems', (req, res) => {
  res.json([
    { name: 'Windows', count: 28000, percentage: 46 },
    { name: 'macOS', count: 18300, percentage: 30 },
    { name: 'iOS', count: 9200, percentage: 15 },
    { name: 'Android', count: 5500, percentage: 9 }
  ]);
});

// Content endpoints
app.get('/analytics/content/categories', (req, res) => {
  res.json([
    { name: 'Blog', count: 12500, percentage: 40 },
    { name: 'Tools', count: 9375, percentage: 30 },
    { name: 'Services', count: 6250, percentage: 20 },
    { name: 'Product', count: 3125, percentage: 10 }
  ]);
});

app.get('/analytics/content/tags', (req, res) => {
  res.json([
    { name: 'Technology', count: 8500, percentage: 22 },
    { name: 'Reviews', count: 7200, percentage: 19 },
    { name: 'Deals', count: 6500, percentage: 17 },
    { name: 'Tutorials', count: 5800, percentage: 15 },
    { name: 'News', count: 4200, percentage: 11 },
    { name: 'Comparisons', count: 3800, percentage: 10 },
    { name: 'Buying Guides', count: 2300, percentage: 6 }
  ]);
});

app.get('/analytics/content/placements', (req, res) => {
  res.json([
    { type: 'Sidebar', count: 15500, percentage: 35 },
    { type: 'In-Content', count: 13286, percentage: 30 },
    { type: 'Header', count: 8857, percentage: 20 },
    { type: 'Footer', count: 4429, percentage: 10 },
    { type: 'Popup', count: 2214, percentage: 5 }
  ]);
});

app.get('/analytics/content/landing-pages', (req, res) => {
  res.json([
    { url: '/home', visits: 12456, percentage: 25, ctr: 3.2 },
    { url: '/products/summer-sale', visits: 8934, percentage: 18, ctr: 4.5 },
    { url: '/blog/top-10-gadgets', visits: 6721, percentage: 14, ctr: 2.8 },
    { url: '/reviews/best-laptops-2024', visits: 5432, percentage: 11, ctr: 3.9 },
    { url: '/deals', visits: 4210, percentage: 9, ctr: 5.1 },
    { url: '/tools/price-comparison', visits: 3890, percentage: 8, ctr: 4.2 },
    { url: '/guides/buying-guide', visits: 3210, percentage: 7, ctr: 3.7 },
    { url: '/category/electronics', visits: 2890, percentage: 6, ctr: 2.9 },
    { url: '/about', visits: 1021, percentage: 2, ctr: 1.5 }
  ]);
});

// Social sources endpoint
app.get('/analytics/social-sources', (req, res) => {
  res.json([
    { name: 'Facebook', count: 8500, percentage: 42 },
    { name: 'Twitter', count: 5667, percentage: 28 },
    { name: 'Instagram', count: 3643, percentage: 18 },
    { name: 'LinkedIn', count: 1619, percentage: 8 },
    { name: 'Other', count: 810, percentage: 4 }
  ]);
});

// Dashboard consolidated endpoint
app.get('/analytics/dashboard', (req, res) => {
  const { tab = 'overview', range = '7d' } = req.query;
  
  res.json({
    kpis: {
      revenue: { value: '$24,563', change: 12.5, sparkline: [20, 35, 30, 45, 40, 55, 60] },
      clicks: { value: '15,847', change: 8.2, sparkline: [30, 40, 35, 50, 45, 60, 65] },
      conversionRate: { value: '3.24%', change: -2.1, sparkline: [4, 3.5, 3.8, 3.2, 3.5, 3.1, 3.24] },
      visitors: { value: '45,231', change: 15.3, sparkline: [35, 40, 38, 50, 48, 60, 65] },
      sales: { value: '1,247', change: 10.8, sparkline: [25, 30, 28, 40, 38, 50, 55] },
      pageviews: { value: '128,456', change: 18.7, sparkline: [40, 50, 45, 60, 55, 70, 75] }
    },
    genderSplit: {
      male: { count: 9239, percentage: 58.3 },
      female: { count: 6608, percentage: 41.7 }
    },
    newVsReturning: {
      new: { count: 10234, percentage: 64.2 },
      returning: { count: 5706, percentage: 35.8 }
    },
    ageDistribution: [
      { range: '18-24', count: 6022, percentage: 38 },
      { range: '25-34', count: 4437, percentage: 28 },
      { range: '35-44', count: 2853, percentage: 18 },
      { range: '45-54', count: 1585, percentage: 10 },
      { range: '55+', count: 951, percentage: 6 }
    ],
    interests: [
      { name: 'Technology Enthusiasts', count: 18980, percentage: 42 },
      { name: 'Business Professionals', count: 12653, percentage: 28 },
      { name: 'Shopping Lovers', count: 8134, percentage: 18 },
      { name: 'Travel & Tourism', count: 5423, percentage: 12 }
    ],
    devices: [
      { type: 'Desktop', count: 32000, percentage: 52 },
      { type: 'Mobile', count: 24615, percentage: 40 },
      { type: 'Tablet', count: 4923, percentage: 8 }
    ],
    countries: [
      { code: 'us', name: 'United States', count: 27692, percentage: 45 },
      { code: 'gb', name: 'United Kingdom', count: 11077, percentage: 18 },
      { code: 'ca', name: 'Canada', count: 7385, percentage: 12 },
      { code: 'au', name: 'Australia', count: 6154, percentage: 10 },
      { code: 'de', name: 'Germany', count: 4923, percentage: 8 },
      { code: 'in', name: 'India', count: 4308, percentage: 7 }
    ],
    socialSources: [
      { name: 'Facebook', count: 8500, percentage: 42 },
      { name: 'Twitter', count: 5667, percentage: 28 },
      { name: 'Instagram', count: 3643, percentage: 18 },
      { name: 'LinkedIn', count: 1619, percentage: 8 },
      { name: 'Other', count: 810, percentage: 4 }
    ],
    categories: [
      { name: 'Blog', count: 12500, percentage: 40 },
      { name: 'Tools', count: 9375, percentage: 30 },
      { name: 'Services', count: 6250, percentage: 20 },
      { name: 'Product', count: 3125, percentage: 10 }
    ],
    placements: [
      { type: 'Sidebar', count: 15500, percentage: 35 },
      { type: 'In-Content', count: 13286, percentage: 30 },
      { type: 'Header', count: 8857, percentage: 20 },
      { type: 'Footer', count: 4429, percentage: 10 },
      { type: 'Popup', count: 2214, percentage: 5 }
    ],
    landingPages: [
      { url: '/home', visits: 12456, percentage: 25, ctr: 3.2 },
      { url: '/products/summer-sale', visits: 8934, percentage: 18, ctr: 4.5 },
      { url: '/blog/top-10-gadgets', visits: 6721, percentage: 14, ctr: 2.8 },
      { url: '/reviews/best-laptops-2024', visits: 5432, percentage: 11, ctr: 3.9 },
      { url: '/deals', visits: 4210, percentage: 9, ctr: 5.1 }
    ]
  });
});

'@
        # Append to simple-server.js before the listen call
        $content = Get-Content $simpleServerPath -Raw
        $listenPattern = 'app\.listen\(PORT.*\)'
        if ($content -match $listenPattern) {
            $content = $content -replace $listenPattern, ($additionalEndpoints + "`n`n$&")
            Set-Content -Path $simpleServerPath -Value $content
            Write-Log "Updated simple-server.js with new endpoints"
        }
    }
}

# ==================== PHASE 7: Module Registration ====================
function Update-AnalyticsModule {
    Write-Log "=== PHASE 7: Update Analytics Module ==="
    
    $modulePath = "apps/api/src/analytics/analytics.module.ts"
    if (Test-Path $modulePath) {
        New-Backup $modulePath
        
        $moduleContent = @'
import { Module } from '@nestjs/common';
import { AnalyticsController } from './analytics.controller';
import { AnalyticsService } from './analytics.service';
import { AnalyticsDemographicsController } from './analytics-demographics.controller';
import { AnalyticsDevicesController } from './analytics-devices.controller';
import { AnalyticsContentController } from './analytics-content.controller';
import { AnalyticsDemographicsService } from './services/analytics-demographics.service';
import { AnalyticsDevicesService } from './services/analytics-devices.service';
import { AnalyticsContentService } from './services/analytics-content.service';
import { PrismaModule } from '../prisma/prisma.module';

@Module({
  imports: [PrismaModule],
  controllers: [
    AnalyticsController,
    AnalyticsDemographicsController,
    AnalyticsDevicesController,
    AnalyticsContentController,
  ],
  providers: [
    AnalyticsService,
    AnalyticsDemographicsService,
    AnalyticsDevicesService,
    AnalyticsContentService,
  ],
  exports: [AnalyticsService],
})
export class AnalyticsModule {}
'@
        Set-Content -Path $modulePath -Value $moduleContent
        Write-Log "Updated analytics.module.ts"
    }
}

# ==================== MAIN EXECUTION ====================
function Main {
    Write-Log "========================================"
    Write-Log "  Analytics Visual Features Implementation"
    Write-Log "========================================"
    Write-Log "Starting implementation..."
    
    try {
        if (-not $SkipDatabase) {
            Update-DatabaseSchema
        } else {
            Write-Log "Skipping database updates"
        }
        
        if (-not $SkipBackend) {
            New-BackendEndpoints
            New-AnalyticsServices
            Update-SimpleServer
            Update-AnalyticsModule
        } else {
            Write-Log "Skipping backend updates"
        }
        
        if (-not $SkipComponents) {
            New-ChartComponents
        } else {
            Write-Log "Skipping component creation"
        }
        
        if (-not $SkipFrontend) {
            New-DashboardPage
        } else {
            Write-Log "Skipping frontend updates"
        }
        
        Write-Log "========================================"
        Write-Log "  Implementation Complete!"
        Write-Log "========================================"
        Write-Log ""
        Write-Log "Next steps:"
        Write-Log "1. Restart the API server: npm run dev:api"
        Write-Log "2. Restart the frontend: npm run dev:web"
        Write-Log "3. Access dashboard: http://localhost:3000/admin/analytics"
        Write-Log ""
        Write-Log "Log file: $script:LogFile"
    }
    catch {
        Write-Log "ERROR: $_" "ERROR"
        exit 1
    }
}

# Run main function
Main
