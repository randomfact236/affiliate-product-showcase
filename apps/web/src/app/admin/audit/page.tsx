"use client";

import React, { useEffect, useState, useCallback } from "react";
import { Card, CardContent, CardHeader } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import {
  Search,
  Filter,
  Download,
  Clock,
  User,
  FileText,
  Trash2,
  Edit3,
  Plus,
  RefreshCw,
  History,
  AlertCircle,
  CheckCircle,
} from "lucide-react";
import { toast } from "sonner";
import { SkeletonTableRows } from "@/components/ui/skeleton-table";
import { DataTablePagination } from "@/components/ui/data-table-pagination";

interface AuditEntry {
  id: string;
  action: "CREATE" | "UPDATE" | "DELETE" | "BULK_DELETE" | "BULK_UPDATE" | "LOGIN" | "LOGOUT" | "EXPORT" | "IMPORT";
  entity: string;
  entityId?: string;
  entityName?: string;
  userId?: string;
  userEmail?: string;
  userName?: string;
  changes?: Record<string, { old?: any; new?: any }>;
  metadata?: Record<string, any>;
  timestamp: string;
}

interface AuditStats {
  total: number;
  createCount: number;
  updateCount: number;
  deleteCount: number;
}

type FilterAction = "all" | "CREATE" | "UPDATE" | "DELETE" | "BULK_DELETE" | "BULK_UPDATE";
type FilterEntity = "all" | "Product" | "Category" | "Tag" | "Ribbon" | "BlogPost" | "Media" | "User";

const API_URL = process.env.NEXT_PUBLIC_API_URL || "http://localhost:3003";

export default function AuditTrailPage() {
  const [logs, setLogs] = useState<AuditEntry[]>([]);
  const [stats, setStats] = useState<AuditStats | null>(null);
  const [loading, setLoading] = useState(true);

  // Filters
  const [searchQuery, setSearchQuery] = useState("");
  const [filterAction, setFilterAction] = useState<FilterAction>("all");
  const [filterEntity, setFilterEntity] = useState<FilterEntity>("all");
  const [showFilters, setShowFilters] = useState(false);

  // Pagination
  const [currentPage, setCurrentPage] = useState(1);
  const [itemsPerPage, setItemsPerPage] = useState(20);
  const [totalItems, setTotalItems] = useState(0);
  const [totalPages, setTotalPages] = useState(1);

  const fetchLogs = useCallback(async () => {
    try {
      setLoading(true);
      const params = new URLSearchParams({
        page: currentPage.toString(),
        limit: itemsPerPage.toString(),
      });

      if (searchQuery) params.append("search", searchQuery);
      if (filterAction !== "all") params.append("action", filterAction);
      if (filterEntity !== "all") params.append("entity", filterEntity);

      const response = await fetch(`${API_URL}/audit?${params}`);
      if (response.ok) {
        const data = await response.json();
        setLogs(data.items || []);
        setTotalItems(data.total || 0);
        setTotalPages(data.totalPages || 1);
      } else {
        loadLocalLogs();
      }
    } catch (error) {
      loadLocalLogs();
    } finally {
      setLoading(false);
    }
  }, [currentPage, itemsPerPage, searchQuery, filterAction, filterEntity]);

  const loadLocalLogs = () => {
    if (typeof window !== "undefined") {
      const stored = localStorage.getItem("audit_trail");
      if (stored) {
        const allLogs = JSON.parse(stored);
        setLogs(allLogs.slice(0, itemsPerPage));
        setTotalItems(allLogs.length);
        setTotalPages(Math.ceil(allLogs.length / itemsPerPage));
      }
    }
  };

  useEffect(() => {
    fetchLogs();
  }, [fetchLogs]);

  useEffect(() => {
    setCurrentPage(1);
  }, [searchQuery, filterAction, filterEntity]);

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleString("en-US", {
      year: "numeric",
      month: "short",
      day: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    });
  };

  const getActionIcon = (action: string) => {
    switch (action) {
      case "CREATE":
        return <Plus className="h-4 w-4 text-green-500" />;
      case "UPDATE":
        return <Edit3 className="h-4 w-4 text-blue-500" />;
      case "DELETE":
      case "BULK_DELETE":
        return <Trash2 className="h-4 w-4 text-red-500" />;
      case "BULK_UPDATE":
        return <RefreshCw className="h-4 w-4 text-orange-500" />;
      case "LOGIN":
        return <CheckCircle className="h-4 w-4 text-green-500" />;
      case "LOGOUT":
        return <AlertCircle className="h-4 w-4 text-gray-500" />;
      case "EXPORT":
      case "IMPORT":
        return <FileText className="h-4 w-4 text-purple-500" />;
      default:
        return <History className="h-4 w-4 text-gray-500" />;
    }
  };

  const getActionBadge = (action: string) => {
    const styles: Record<string, string> = {
      CREATE: "bg-green-100 text-green-700",
      UPDATE: "bg-blue-100 text-blue-700",
      DELETE: "bg-red-100 text-red-700",
      BULK_DELETE: "bg-red-100 text-red-700",
      BULK_UPDATE: "bg-orange-100 text-orange-700",
      LOGIN: "bg-green-100 text-green-700",
      LOGOUT: "bg-gray-100 text-gray-700",
      EXPORT: "bg-purple-100 text-purple-700",
      IMPORT: "bg-purple-100 text-purple-700",
    };
    return <Badge className={styles[action] || "bg-gray-100 text-gray-700"}>{action}</Badge>;
  };

  const handleExport = () => {
    const csv = [
      ["Timestamp", "Action", "Entity", "Entity Name", "User", "Changes"].join(","),
      ...logs.map((log) =>
        [
          new Date(log.timestamp).toISOString(),
          log.action,
          log.entity,
          log.entityName || "",
          log.userEmail || "System",
          log.changes ? JSON.stringify(log.changes).replace(/,/g, ";") : "",
        ].join(",")
      ),
    ].join("\n");

    const blob = new Blob([csv], { type: "text/csv" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `audit-log-${new Date().toISOString().split("T")[0]}.csv`;
    a.click();
    URL.revokeObjectURL(url);

    toast.success("Audit log exported successfully");
  };

  const clearFilters = () => {
    setSearchQuery("");
    setFilterAction("all");
    setFilterEntity("all");
    setCurrentPage(1);
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="relative overflow-hidden rounded-xl bg-gradient-to-r from-slate-600 via-slate-700 to-slate-800 px-8 py-8 text-white">
        <div className="relative z-10">
          <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
              <h1 className="text-3xl font-bold flex items-center gap-3">
                <History className="h-8 w-8" />
                Audit Trail
              </h1>
              <p className="mt-2 text-slate-300">
                Track all activities and changes across the system
              </p>
            </div>
            <Button
              variant="outline"
              className="bg-white/10 text-white border-white/20 hover:bg-white/20"
              onClick={handleExport}
            >
              <Download className="mr-2 h-4 w-4" />
              Export Log
            </Button>
          </div>
        </div>
        <div className="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10" />
        <div className="absolute -bottom-10 -right-10 h-32 w-32 rounded-full bg-white/5" />
      </div>

      {/* Filters */}
      <Card>
        <CardHeader>
          <div className="flex flex-col sm:flex-row gap-4">
            <div className="relative flex-1">
              <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
              <Input
                placeholder="Search by user, entity, or action..."
                className="pl-10"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
              />
            </div>
            <Button
              variant="outline"
              onClick={() => setShowFilters(!showFilters)}
              className={showFilters ? "bg-slate-50 text-slate-600" : ""}
            >
              <Filter className="mr-2 h-4 w-4" />
              Filters
            </Button>
          </div>

          {showFilters && (
            <div className="mt-4 pt-4 border-t grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label className="text-sm font-medium mb-2 block">Action</label>
                <select
                  className="w-full h-10 px-3 rounded-md border border-input bg-background text-sm"
                  value={filterAction}
                  onChange={(e) => setFilterAction(e.target.value as FilterAction)}
                >
                  <option value="all">All Actions</option>
                  <option value="CREATE">Create</option>
                  <option value="UPDATE">Update</option>
                  <option value="DELETE">Delete</option>
                  <option value="BULK_DELETE">Bulk Delete</option>
                  <option value="BULK_UPDATE">Bulk Update</option>
                </select>
              </div>

              <div>
                <label className="text-sm font-medium mb-2 block">Entity</label>
                <select
                  className="w-full h-10 px-3 rounded-md border border-input bg-background text-sm"
                  value={filterEntity}
                  onChange={(e) => setFilterEntity(e.target.value as FilterEntity)}
                >
                  <option value="all">All Entities</option>
                  <option value="Product">Product</option>
                  <option value="Category">Category</option>
                  <option value="Tag">Tag</option>
                  <option value="Ribbon">Ribbon</option>
                  <option value="BlogPost">Blog Post</option>
                  <option value="Media">Media</option>
                  <option value="User">User</option>
                </select>
              </div>
            </div>
          )}

          <Button variant="outline" className="mt-3" onClick={clearFilters}>
            Clear Filters
          </Button>
        </CardHeader>
      </Card>

      {/* Audit Log Table */}
      <Card>
        <CardContent className="p-0">
          <Table>
            <TableHeader>
              <TableRow className="bg-gray-50 hover:bg-gray-50">
                <TableHead>Timestamp</TableHead>
                <TableHead>Action</TableHead>
                <TableHead>Entity</TableHead>
                <TableHead>User</TableHead>
                <TableHead className="text-right">Details</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {loading ? (
                <SkeletonTableRows rows={5} columns={5} />
              ) : logs.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={5} className="h-32 text-center text-muted-foreground">
                    <div className="flex flex-col items-center gap-2">
                      <History className="h-8 w-8 text-gray-400" />
                      <p>No audit logs found</p>
                    </div>
                  </TableCell>
                </TableRow>
              ) : (
                logs.map((log) => (
                  <TableRow key={log.id} className="hover:bg-gray-50">
                    <TableCell className="text-sm">
                      <div className="flex items-center gap-1">
                        <Clock className="h-3 w-3 text-muted-foreground" />
                        {formatDate(log.timestamp)}
                      </div>
                    </TableCell>
                    <TableCell>
                      <div className="flex items-center gap-2">
                        {getActionIcon(log.action)}
                        {getActionBadge(log.action)}
                      </div>
                    </TableCell>
                    <TableCell>
                      <div className="flex flex-col">
                        <span className="font-medium">{log.entity}</span>
                        {log.entityName && (
                          <span className="text-xs text-muted-foreground">{log.entityName}</span>
                        )}
                      </div>
                    </TableCell>
                    <TableCell>
                      <div className="flex items-center gap-1">
                        <User className="h-3 w-3 text-muted-foreground" />
                        <span className="text-sm">
                          {log.userName || log.userEmail || "System"}
                        </span>
                      </div>
                    </TableCell>
                    <TableCell className="text-right">
                      {log.changes ? (
                        <Button 
                          variant="ghost" 
                          size="sm" 
                          onClick={() => {
                            const changesHtml = Object.entries(log.changes || {})
                              .map(([key, value]: [string, any]) => {
                                const oldVal = value.old !== undefined ? String(value.old) : "—";
                                const newVal = value.new !== undefined ? String(value.new) : "—";
                                return `<div><strong>${key}:</strong> <span style="text-decoration:line-through;color:red">${oldVal}</span> → <span style="color:green">${newVal}</span></div>`;
                              })
                              .join("");
                            
                            const toastDiv = document.createElement("div");
                            toastDiv.innerHTML = changesHtml;
                            toast.info(toastDiv, { duration: 5000 });
                          }}
                        >
                          View Changes
                        </Button>
                      ) : (
                        <span className="text-muted-foreground text-sm">—</span>
                      )}
                    </TableCell>
                  </TableRow>
                ))
              )}
            </TableBody>
          </Table>
        </CardContent>
      </Card>

      {/* Pagination */}
      {totalPages > 1 && (
        <DataTablePagination
          currentPage={currentPage}
          totalPages={totalPages}
          pageSize={itemsPerPage}
          totalItems={totalItems}
          onPageChange={setCurrentPage}
          onPageSizeChange={(size) => {
            setItemsPerPage(size);
            setCurrentPage(1);
          }}
        />
      )}
    </div>
  );
}
