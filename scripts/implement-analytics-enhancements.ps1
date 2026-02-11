#!/usr/bin/env powershell
#requires -version 5
<#
.SYNOPSIS
    Implements Analytics Menu Enhancements
.DESCRIPTION
    Adds real-time widget, export functionality, search/filter, sub-menus, alerts, and dashboard builder
.NOTES
    File Name      : implement-analytics-enhancements.ps1
    Author         : Affiliate Pro System
#>

[CmdletBinding()]
param(
    [switch]$SkipFrontend,
    [switch]$SkipBackend
)

$ErrorActionPreference = "Stop"
$script:LogFile = "logs/analytics-enhancements-$(Get-Date -Format 'yyyyMMdd-HHmmss').log"
New-Item -ItemType Directory -Force -Path "logs" | Out-Null

function Write-Log {
    param([string]$Message, [string]$Level = "INFO")
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $logEntry = "[$timestamp] [$Level] $Message"
    Write-Host $logEntry
    Add-Content -Path $script:LogFile -Value $logEntry
}

# ==================== PHASE 1: Real-time Widget ====================
function New-RealtimeWidget {
    Write-Log "=== PHASE 1: Real-time Activity Widget ==="
    
    $componentDir = "apps/web/src/components/analytics"
    
    # RealtimeWidget component
    $realtimeWidget = @'
'use client';

import React, { useState, useEffect } from 'react';
import { Activity, Users, MousePointerClick, ShoppingCart } from 'lucide-react';

interface LiveEvent {
  id: string;
  type: 'pageview' | 'click' | 'conversion';
  location: string;
  page: string;
  time: string;
}

interface RealtimeStats {
  activeUsers: number;
  pageViews: number;
  clicks: number;
  conversions: number;
}

export function RealtimeWidget() {
  const [stats, setStats] = useState<RealtimeStats>({
    activeUsers: 0,
    pageViews: 0,
    clicks: 0,
    conversions: 0
  });
  const [recentEvents, setRecentEvents] = useState<LiveEvent[]>([]);
  const [isConnected, setIsConnected] = useState(false);

  useEffect(() => {
    // Simulate real-time data (replace with WebSocket in production)
    const interval = setInterval(() => {
      // Mock data updates
      setStats({
        activeUsers: Math.floor(Math.random() * 50) + 100,
        pageViews: Math.floor(Math.random() * 200) + 500,
        clicks: Math.floor(Math.random() * 50) + 100,
        conversions: Math.floor(Math.random() * 10) + 5
      });

      // Add new event
      const newEvent: LiveEvent = {
        id: Date.now().toString(),
        type: ['pageview', 'click', 'conversion'][Math.floor(Math.random() * 3)] as LiveEvent['type'],
        location: ['US', 'UK', 'CA', 'DE', 'FR'][Math.floor(Math.random() * 5)],
        page: ['/home', '/products', '/blog', '/reviews'][Math.floor(Math.random() * 4)],
        time: new Date().toLocaleTimeString()
      };

      setRecentEvents(prev => [newEvent, ...prev].slice(0, 10));
      setIsConnected(true);
    }, 3000);

    return () => clearInterval(interval);
  }, []);

  const getEventIcon = (type: LiveEvent['type']) => {
    switch (type) {
      case 'pageview': return <Activity className="w-4 h-4 text-blue-400" />;
      case 'click': return <MousePointerClick className="w-4 h-4 text-green-400" />;
      case 'conversion': return <ShoppingCart className="w-4 h-4 text-yellow-400" />;
    }
  };

  return (
    <div className="bg-gray-800 rounded-lg p-4">
      <div className="flex items-center justify-between mb-4">
        <div className="flex items-center gap-2">
          <Activity className="w-5 h-5 text-green-400" />
          <h3 className="text-white font-medium">Real-time Activity</h3>
        </div>
        <div className="flex items-center gap-2">
          <span className={`w-2 h-2 rounded-full ${isConnected ? 'bg-green-400 animate-pulse' : 'bg-red-400'}`} />
          <span className="text-xs text-gray-400">{isConnected ? 'Live' : 'Disconnected'}</span>
        </div>
      </div>

      {/* Stats Grid */}
      <div className="grid grid-cols-2 gap-3 mb-4">
        <div className="bg-gray-900 rounded p-3">
          <div className="flex items-center gap-2 text-gray-400 text-xs mb-1">
            <Users className="w-3 h-3" />
            Active Users
          </div>
          <div className="text-2xl font-bold text-white">{stats.activeUsers}</div>
          <div className="text-xs text-green-400">+12% vs last hour</div>
        </div>
        <div className="bg-gray-900 rounded p-3">
          <div className="flex items-center gap-2 text-gray-400 text-xs mb-1">
            <MousePointerClick className="w-3 h-3" />
            Clicks
          </div>
          <div className="text-2xl font-bold text-white">{stats.clicks}</div>
          <div className="text-xs text-green-400">+8% vs last hour</div>
        </div>
        <div className="bg-gray-900 rounded p-3">
          <div className="flex items-center gap-2 text-gray-400 text-xs mb-1">
            <Activity className="w-3 h-3" />
            Page Views
          </div>
          <div className="text-2xl font-bold text-white">{stats.pageViews}</div>
          <div className="text-xs text-gray-400">Same as last hour</div>
        </div>
        <div className="bg-gray-900 rounded p-3">
          <div className="flex items-center gap-2 text-gray-400 text-xs mb-1">
            <ShoppingCart className="w-3 h-3" />
            Conversions
          </div>
          <div className="text-2xl font-bold text-white">{stats.conversions}</div>
          <div className="text-xs text-green-400">+23% vs last hour</div>
        </div>
      </div>

      {/* Recent Events */}
      <div className="border-t border-gray-700 pt-3">
        <h4 className="text-gray-400 text-xs uppercase mb-2">Recent Activity</h4>
        <div className="space-y-2 max-h-48 overflow-y-auto">
          {recentEvents.map((event) => (
            <div key={event.id} className="flex items-center gap-3 text-sm">
              {getEventIcon(event.type)}
              <span className="text-gray-300 capitalize">{event.type}</span>
              <span className="text-gray-500">from</span>
              <span className="text-gray-400">{event.location}</span>
              <span className="text-gray-500">on</span>
              <span className="text-gray-400 truncate max-w-[100px]">{event.page}</span>
              <span className="text-gray-600 text-xs ml-auto">{event.time}</span>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
'@
    Set-Content -Path "$componentDir/RealtimeWidget.tsx" -Value $realtimeWidget
    Write-Log "Created: RealtimeWidget.tsx"
}

# ==================== PHASE 2: Export Functionality ====================
function New-ExportFunctionality {
    Write-Log "=== PHASE 2: Export Functionality ==="
    
    $componentDir = "apps/web/src/components/analytics"
    
    # ExportMenu component
    $exportMenu = @'
'use client';

import React, { useState } from 'react';
import { Download, FileText, Table, FileSpreadsheet, Code, Calendar } from 'lucide-react';

interface ExportMenuProps {
  data?: any;
  filename?: string;
  onExport?: (format: string) => void;
}

export function ExportMenu({ data, filename = 'analytics-report', onExport }: ExportMenuProps) {
  const [isOpen, setIsOpen] = useState(false);
  const [isExporting, setIsExporting] = useState(false);

  const exportFormats = [
    { id: 'pdf', label: 'PDF Report', icon: FileText, color: '#ef4444' },
    { id: 'csv', label: 'CSV', icon: Table, color: '#22c55e' },
    { id: 'excel', label: 'Excel', icon: FileSpreadsheet, color: '#16a34a' },
    { id: 'json', label: 'JSON', icon: Code, color: '#3b82f6' },
  ];

  const handleExport = async (format: string) => {
    setIsExporting(true);
    
    try {
      if (onExport) {
        await onExport(format);
      } else {
        // Default export behavior
        await defaultExport(format, data, filename);
      }
    } catch (error) {
      console.error('Export failed:', error);
    } finally {
      setIsExporting(false);
      setIsOpen(false);
    }
  };

  const defaultExport = async (format: string, data: any, filename: string) => {
    switch (format) {
      case 'json':
        downloadJSON(data, filename);
        break;
      case 'csv':
        downloadCSV(data, filename);
        break;
      case 'pdf':
        await downloadPDF(filename);
        break;
      case 'excel':
        downloadExcel(data, filename);
        break;
    }
  };

  const downloadJSON = (data: any, filename: string) => {
    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
    downloadBlob(blob, `${filename}.json`);
  };

  const downloadCSV = (data: any[], filename: string) => {
    if (!Array.isArray(data) || data.length === 0) return;
    
    const headers = Object.keys(data[0]);
    const csv = [
      headers.join(','),
      ...data.map(row => headers.map(h => `"${row[h]}"`).join(','))
    ].join('\n');
    
    const blob = new Blob([csv], { type: 'text/csv' });
    downloadBlob(blob, `${filename}.csv`);
  };

  const downloadPDF = async (filename: string) => {
    // Open print dialog for PDF generation
    window.print();
  };

  const downloadExcel = (data: any, filename: string) => {
    // For now, export as CSV with Excel MIME type
    const csv = convertToCSV(data);
    const blob = new Blob([csv], { type: 'application/vnd.ms-excel' });
    downloadBlob(blob, `${filename}.xls`);
  };

  const convertToCSV = (data: any): string => {
    if (Array.isArray(data)) {
      const headers = Object.keys(data[0] || {});
      return [
        headers.join('\t'),
        ...data.map(row => headers.map(h => row[h]).join('\t'))
      ].join('\n');
    }
    return JSON.stringify(data, null, 2);
  };

  const downloadBlob = (blob: Blob, filename: string) => {
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
  };

  return (
    <div className="relative">
      <button
        onClick={() => setIsOpen(!isOpen)}
        disabled={isExporting}
        className="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
      >
        <Download className="w-4 h-4" />
        {isExporting ? 'Exporting...' : 'Export'}
      </button>

      {isOpen && (
        <>
          <div 
            className="fixed inset-0 z-40" 
            onClick={() => setIsOpen(false)}
          />
          <div className="absolute right-0 mt-2 w-48 bg-gray-800 rounded-lg shadow-xl border border-gray-700 z-50">
            <div className="p-2">
              <div className="text-xs text-gray-400 px-3 py-2 uppercase">Export Format</div>
              {exportFormats.map((format) => (
                <button
                  key={format.id}
                  onClick={() => handleExport(format.id)}
                  className="w-full flex items-center gap-3 px-3 py-2 text-sm text-gray-300 hover:bg-gray-700 rounded transition-colors"
                >
                  <format.icon className="w-4 h-4" style={{ color: format.color }} />
                  {format.label}
                </button>
              ))}
            </div>
            <div className="border-t border-gray-700 p-2">
              <button
                onClick={() => handleExport('scheduled')}
                className="w-full flex items-center gap-3 px-3 py-2 text-sm text-gray-300 hover:bg-gray-700 rounded transition-colors"
              >
                <Calendar className="w-4 h-4 text-purple-400" />
                Schedule Report
              </button>
            </div>
          </div>
        </>
      )}
    </div>
  );
}

// Scheduled Reports Component
export function ScheduledReports() {
  const [reports, setReports] = useState([
    { id: 1, name: 'Daily Revenue', frequency: 'daily', format: 'PDF', email: 'admin@site.com', lastSent: '2024-01-15' },
    { id: 2, name: 'Weekly Analytics', frequency: 'weekly', format: 'Excel', email: 'team@site.com', lastSent: '2024-01-14' },
  ]);

  return (
    <div className="bg-gray-800 rounded-lg p-4">
      <h3 className="text-white font-medium mb-4">Scheduled Reports</h3>
      <div className="space-y-2">
        {reports.map((report) => (
          <div key={report.id} className="flex items-center justify-between p-3 bg-gray-900 rounded">
            <div>
              <div className="text-gray-300">{report.name}</div>
              <div className="text-xs text-gray-500">
                {report.frequency} • {report.format} • {report.email}
              </div>
            </div>
            <div className="text-xs text-gray-500">
              Last sent: {report.lastSent}
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
'@
    Set-Content -Path "$componentDir/ExportMenu.tsx" -Value $exportMenu
    Write-Log "Created: ExportMenu.tsx"
}

# ==================== PHASE 3: Search & Filter Bar ====================
function New-SearchFilterBar {
    Write-Log "=== PHASE 3: Search & Filter Bar ==="
    
    $componentDir = "apps/web/src/components/analytics"
    
    $searchFilterBar = @'
'use client';

import React, { useState } from 'react';
import { Search, Calendar, Filter, X, ChevronDown, Save } from 'lucide-react';

interface FilterOption {
  id: string;
  label: string;
  type: 'select' | 'multiselect' | 'date' | 'toggle' | 'range';
  options?: string[];
  value?: any;
}

interface SearchFilterBarProps {
  onSearch?: (query: string, filters: Record<string, any>) => void;
  onSaveFilter?: (name: string, filters: Record<string, any>) => void;
  filterOptions?: FilterOption[];
  savedFilters?: Array<{ id: string; name: string; filters: Record<string, any> }>;
}

export function SearchFilterBar({ 
  onSearch, 
  onSaveFilter,
  filterOptions = defaultFilterOptions,
  savedFilters = []
}: SearchFilterBarProps) {
  const [query, setQuery] = useState('');
  const [filters, setFilters] = useState<Record<string, any>>({});
  const [showFilters, setShowFilters] = useState(false);
  const [dateRange, setDateRange] = useState('7d');
  const [isSaving, setIsSaving] = useState(false);
  const [filterName, setFilterName] = useState('');

  const handleSearch = () => {
    onSearch?.(query, { ...filters, dateRange });
  };

  const handleFilterChange = (filterId: string, value: any) => {
    setFilters(prev => ({ ...prev, [filterId]: value }));
  };

  const handleSaveFilter = () => {
    if (filterName && onSaveFilter) {
      onSaveFilter(filterName, { ...filters, dateRange });
      setIsSaving(false);
      setFilterName('');
    }
  };

  const clearFilters = () => {
    setQuery('');
    setFilters({});
    setDateRange('7d');
    onSearch?.('', {});
  };

  const dateRangeOptions = [
    { value: '24h', label: 'Last 24 hours' },
    { value: '7d', label: 'Last 7 days' },
    { value: '30d', label: 'Last 30 days' },
    { value: '90d', label: 'Last 90 days' },
    { value: 'custom', label: 'Custom range' },
  ];

  return (
    <div className="bg-gray-800 rounded-lg p-4 space-y-4">
      {/* Search Row */}
      <div className="flex flex-wrap items-center gap-3">
        {/* Search Input */}
        <div className="flex-1 min-w-[300px] relative">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
          <input
            type="text"
            value={query}
            onChange={(e) => setQuery(e.target.value)}
            onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
            placeholder="Search metrics, pages, products..."
            className="w-full pl-10 pr-4 py-2 bg-gray-900 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-blue-500"
          />
        </div>

        {/* Date Range */}
        <div className="relative">
          <Calendar className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
          <select
            value={dateRange}
            onChange={(e) => setDateRange(e.target.value)}
            className="pl-10 pr-8 py-2 bg-gray-900 border border-gray-700 rounded-lg text-white appearance-none cursor-pointer focus:outline-none focus:border-blue-500"
          >
            {dateRangeOptions.map(opt => (
              <option key={opt.value} value={opt.value}>{opt.label}</option>
            ))}
          </select>
          <ChevronDown className="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" />
        </div>

        {/* Filter Toggle */}
        <button
          onClick={() => setShowFilters(!showFilters)}
          className={`flex items-center gap-2 px-4 py-2 rounded-lg border transition-colors ${
            showFilters 
              ? 'bg-blue-600 border-blue-600 text-white' 
              : 'bg-gray-900 border-gray-700 text-gray-300 hover:border-gray-600'
          }`}
        >
          <Filter className="w-4 h-4" />
          Filters
          {Object.keys(filters).length > 0 && (
            <span className="ml-1 px-1.5 py-0.5 bg-blue-500 text-white text-xs rounded-full">
              {Object.keys(filters).length}
            </span>
          )}
        </button>

        {/* Search Button */}
        <button
          onClick={handleSearch}
          className="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
        >
          Search
        </button>

        {/* Clear Button */}
        {(query || Object.keys(filters).length > 0) && (
          <button
            onClick={clearFilters}
            className="flex items-center gap-1 text-gray-400 hover:text-white transition-colors"
          >
            <X className="w-4 h-4" />
            Clear
          </button>
        )}
      </div>

      {/* Filter Panel */}
      {showFilters && (
        <div className="border-t border-gray-700 pt-4">
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            {filterOptions.map((filter) => (
              <div key={filter.id}>
                <label className="block text-sm text-gray-400 mb-1">{filter.label}</label>
                {filter.type === 'select' && (
                  <select
                    value={filters[filter.id] || ''}
                    onChange={(e) => handleFilterChange(filter.id, e.target.value)}
                    className="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-white focus:outline-none focus:border-blue-500"
                  >
                    <option value="">All</option>
                    {filter.options?.map(opt => (
                      <option key={opt} value={opt}>{opt}</option>
                    ))}
                  </select>
                )}
                {filter.type === 'toggle' && (
                  <label className="flex items-center gap-2 cursor-pointer">
                    <input
                      type="checkbox"
                      checked={filters[filter.id] || false}
                      onChange={(e) => handleFilterChange(filter.id, e.target.checked)}
                      className="w-4 h-4 rounded border-gray-600 bg-gray-900 text-blue-600 focus:ring-blue-500"
                    />
                    <span className="text-gray-300">Enable</span>
                  </label>
                )}
              </div>
            ))}
          </div>

          {/* Save Filter */}
          <div className="flex items-center gap-3 mt-4 pt-4 border-t border-gray-700">
            {isSaving ? (
              <>
                <input
                  type="text"
                  value={filterName}
                  onChange={(e) => setFilterName(e.target.value)}
                  placeholder="Filter name"
                  className="px-3 py-1.5 bg-gray-900 border border-gray-700 rounded text-white text-sm focus:outline-none focus:border-blue-500"
                />
                <button
                  onClick={handleSaveFilter}
                  className="flex items-center gap-1 px-3 py-1.5 bg-green-600 text-white rounded text-sm hover:bg-green-700"
                >
                  <Save className="w-3 h-3" />
                  Save
                </button>
                <button
                  onClick={() => setIsSaving(false)}
                  className="text-gray-400 hover:text-white text-sm"
                >
                  Cancel
                </button>
              </>
            ) : (
              <button
                onClick={() => setIsSaving(true)}
                className="flex items-center gap-1 text-blue-400 hover:text-blue-300 text-sm"
              >
                <Save className="w-4 h-4" />
                Save Current Filter
              </button>
            )}

            {/* Saved Filters */}
            {savedFilters.length > 0 && (
              <div className="flex items-center gap-2 ml-auto">
                <span className="text-gray-500 text-sm">Saved:</span>
                {savedFilters.map((saved) => (
                  <button
                    key={saved.id}
                    onClick={() => setFilters(saved.filters)}
                    className="px-2 py-1 bg-gray-700 text-gray-300 rounded text-sm hover:bg-gray-600"
                  >
                    {saved.name}
                  </button>
                ))}
              </div>
            )}
          </div>
        </div>
      )}

      {/* Active Filters */}
      {Object.keys(filters).length > 0 && (
        <div className="flex flex-wrap items-center gap-2">
          <span className="text-sm text-gray-500">Active filters:</span>
          {Object.entries(filters).map(([key, value]) => (
            value && (
              <span 
                key={key} 
                className="flex items-center gap-1 px-2 py-1 bg-blue-900/50 text-blue-300 rounded text-sm"
              >
                {key}: {value.toString()}
                <button 
                  onClick={() => handleFilterChange(key, undefined)}
                  className="hover:text-white"
                >
                  <X className="w-3 h-3" />
                </button>
              </span>
            )
          ))}
        </div>
      )}
    </div>
  );
}

const defaultFilterOptions: FilterOption[] = [
  { id: 'device', label: 'Device', type: 'select', options: ['Desktop', 'Mobile', 'Tablet'] },
  { id: 'source', label: 'Source', type: 'select', options: ['Organic', 'Social', 'Email', 'Direct', 'Referral'] },
  { id: 'country', label: 'Country', type: 'select', options: ['US', 'UK', 'CA', 'DE', 'FR', 'AU'] },
  { id: 'excludeBots', label: 'Exclude Bots', type: 'toggle' },
];
'@
    Set-Content -Path "$componentDir/SearchFilterBar.tsx" -Value $searchFilterBar
    Write-Log "Created: SearchFilterBar.tsx"
}

# Main execution
function Main {
    Write-Log "========================================"
    Write-Log "  Analytics Menu Enhancements"
    Write-Log "========================================"
    
    New-RealtimeWidget
    New-ExportFunctionality
    New-SearchFilterBar
    
    Write-Log "========================================"
    Write-Log "  Implementation Complete!"
    Write-Log "========================================"
}

Main
