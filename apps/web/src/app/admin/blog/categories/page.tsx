"use client";

import React, { useEffect, useState, useMemo, useCallback, useRef } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Checkbox } from "@/components/ui/checkbox";
import { Switch } from "@/components/ui/switch";
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
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import {
  Plus,
  Edit2,
  Trash2,
  MoreHorizontal,
  Search,
  Filter,
  FolderTree,
  ArrowLeft,
  BookOpen,
  Check,
  X,
  Save,
  ChevronDown,
  ChevronUp,
  Grid3X3,
  List,
  ChevronLeft,
  ChevronRight,
  CheckSquare,
  Square,
  FolderOpen,
  ExternalLink,
  FileText,
  MoreVertical,
  Eye,
  FileSpreadsheet,
  ArrowUpDown,
} from "lucide-react";
import { toast } from "sonner";
import {
  getBlogCategories,
  createBlogCategory,
  updateBlogCategory,
  deleteBlogCategory,
  bulkDeleteBlogCategories,
  bulkUpdateBlogCategories,
  type BlogCategory,
} from "@/lib/api/blog-categories";
import { SkeletonTableRows } from "@/components/ui/skeleton-table";
import { DataTableSearch } from "@/components/ui/data-table-search";
import { DataTablePagination } from "@/components/ui/data-table-pagination";
import { ConfirmDialog } from "@/components/ui/confirm-dialog";

interface FilterState {
  status: "all" | "active" | "inactive";
  depth: "all" | "0" | "1+";
}

interface SortState {
  column: "name" | "slug" | "postCount" | "createdAt";
  direction: "asc" | "desc";
}

export default function BlogCategoriesPage() {
  const router = useRouter();
  const [categories, setCategories] = useState<BlogCategory[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // Search and Filter
  const [searchQuery, setSearchQuery] = useState("");
  const [filters, setFilters] = useState<FilterState>({
    status: "all",
    depth: "all",
  });
  const [sort, setSort] = useState<SortState>({
    column: "name",
    direction: "asc",
  });

  // Selection
  const [selectedIds, setSelectedIds] = useState<Set<string>>(new Set());
  const [selectAll, setSelectAll] = useState(false);

  // Editing States
  const [quickEditingId, setQuickEditingId] = useState<string | null>(null);
  const [fullEditingId, setFullEditingId] = useState<string | null>(null);
  const [editFormData, setEditFormData] = useState<Partial<BlogCategory>>({});
  const [isSaving, setIsSaving] = useState(false);

  // UI States
  const [viewMode, setViewMode] = useState<"table" | "grid">("table");
  const [showFilters, setShowFilters] = useState(false);

  // Confirm Dialog
  const [confirmDialog, setConfirmDialog] = useState<{
    isOpen: boolean;
    title: string;
    description: string;
    onConfirm: () => void;
  }>({
    isOpen: false,
    title: "",
    description: "",
    onConfirm: () => {},
  });

  // Pagination
  const [currentPage, setCurrentPage] = useState(1);
  const [itemsPerPage, setItemsPerPage] = useState(10);

  // Create Form
  const [showCreateForm, setShowCreateForm] = useState(false);
  const [createFormData, setCreateFormData] = useState({
    name: "",
    slug: "",
    description: "",
    parentId: "",
    isActive: true,
    sortOrder: 0,
  });
  const isSlugManuallyEdited = useRef(false);
  const [isCreating, setIsCreating] = useState(false);

  useEffect(() => {
    fetchCategories();
  }, []);

  const fetchCategories = async () => {
    try {
      setLoading(true);
      const data = await getBlogCategories();
      setCategories(data);
    } catch (err) {
      console.error("Failed to fetch categories:", err);
      setError("Failed to load categories");
      toast.error("Failed to load categories");
    } finally {
      setLoading(false);
    }
  };

  // Filter and sort categories
  const filteredCategories = useMemo(() => {
    let result = [...categories];

    // Search filter
    if (searchQuery) {
      const query = searchQuery.toLowerCase();
      result = result.filter(
        (cat) =>
          cat.name.toLowerCase().includes(query) ||
          cat.slug.toLowerCase().includes(query) ||
          (cat.description && cat.description.toLowerCase().includes(query))
      );
    }

    // Status filter
    if (filters.status !== "all") {
      result = result.filter((cat) =>
        filters.status === "active" ? cat.isActive : !cat.isActive
      );
    }

    // Depth filter
    if (filters.depth !== "all") {
      result = result.filter((cat) =>
        filters.depth === "0" ? cat.depth === 0 : cat.depth > 0
      );
    }

    // Sort
    result.sort((a, b) => {
      let comparison = 0;
      switch (sort.column) {
        case "name":
          comparison = a.name.localeCompare(b.name);
          break;
        case "slug":
          comparison = a.slug.localeCompare(b.slug);
          break;
        case "postCount":
          comparison = (a.postCount || 0) - (b.postCount || 0);
          break;
        case "createdAt":
          comparison =
            new Date(a.createdAt).getTime() - new Date(b.createdAt).getTime();
          break;
      }
      return sort.direction === "asc" ? comparison : -comparison;
    });

    return result;
  }, [categories, searchQuery, filters, sort]);

  // Pagination
  const totalPages = Math.ceil(filteredCategories.length / itemsPerPage);
  const paginatedCategories = useMemo(() => {
    const start = (currentPage - 1) * itemsPerPage;
    return filteredCategories.slice(start, start + itemsPerPage);
  }, [filteredCategories, currentPage, itemsPerPage]);

  // Selection handlers
  const toggleSelectAll = () => {
    if (selectAll) {
      setSelectedIds(new Set());
    } else {
      setSelectedIds(new Set(paginatedCategories.map((c) => c.id)));
    }
    setSelectAll(!selectAll);
  };

  const toggleSelect = (id: string) => {
    const newSelected = new Set(selectedIds);
    if (newSelected.has(id)) {
      newSelected.delete(id);
    } else {
      newSelected.add(id);
    }
    setSelectedIds(newSelected);
  };

  // Quick Edit
  const startQuickEdit = (category: BlogCategory) => {
    setQuickEditingId(category.id);
    setFullEditingId(null);
    setEditFormData({ name: category.name });
  };

  const saveQuickEdit = async () => {
    if (!quickEditingId || !editFormData.name?.trim()) return;

    try {
      setIsSaving(true);
      await updateBlogCategory(quickEditingId, { name: editFormData.name.trim() });
      toast.success("Category updated successfully");
      await fetchCategories();
      setQuickEditingId(null);
    } catch (err) {
      toast.error(err instanceof Error ? err.message : "Failed to update category");
    } finally {
      setIsSaving(false);
    }
  };

  // Full Edit
  const startFullEdit = (category: BlogCategory) => {
    setFullEditingId(category.id);
    setQuickEditingId(null);
    setEditFormData({ ...category });
  };

  const saveFullEdit = async () => {
    if (!fullEditingId || !editFormData.name?.trim()) return;

    try {
      setIsSaving(true);
      await updateBlogCategory(fullEditingId, {
        name: editFormData.name.trim(),
        slug: editFormData.slug,
        description: editFormData.description || undefined,
        parentId: editFormData.parentId || undefined,
        isActive: editFormData.isActive,
        sortOrder: editFormData.sortOrder,
      });
      toast.success("Category updated successfully");
      await fetchCategories();
      setFullEditingId(null);
    } catch (err) {
      toast.error(err instanceof Error ? err.message : "Failed to update category");
    } finally {
      setIsSaving(false);
    }
  };

  // Create
  const handleCreate = async () => {
    if (!createFormData.name.trim() || !createFormData.slug.trim()) {
      toast.error("Name and slug are required");
      return;
    }

    try {
      setIsCreating(true);
      await createBlogCategory({
        name: createFormData.name.trim(),
        slug: createFormData.slug.trim(),
        description: createFormData.description || undefined,
        parentId: createFormData.parentId || null,
        isActive: createFormData.isActive,
        sortOrder: createFormData.sortOrder,
      });
      toast.success("Category created successfully");
      await fetchCategories();
      setShowCreateForm(false);
      isSlugManuallyEdited.current = false;
      setCreateFormData({
        name: "",
        slug: "",
        description: "",
        parentId: "",
        isActive: true,
        sortOrder: 0,
      });
    } catch (err) {
      toast.error(err instanceof Error ? err.message : "Failed to create category");
    } finally {
      setIsCreating(false);
    }
  };

  // Delete
  const handleDelete = (category: BlogCategory) => {
    setConfirmDialog({
      isOpen: true,
      title: "Delete Category",
      description: `Are you sure you want to delete "${category.name}"? This action cannot be undone.`,
      onConfirm: async () => {
        try {
          await deleteBlogCategory(category.id);
          toast.success("Category deleted successfully");
          await fetchCategories();
        } catch (err) {
          toast.error(err instanceof Error ? err.message : "Failed to delete category");
        }
      },
    });
  };

  // Bulk Delete
  const handleBulkDelete = () => {
    if (selectedIds.size === 0) return;

    setConfirmDialog({
      isOpen: true,
      title: "Delete Categories",
      description: `Are you sure you want to delete ${selectedIds.size} categor${
        selectedIds.size === 1 ? "y" : "ies"
      }? This action cannot be undone.`,
      onConfirm: async () => {
        try {
          await bulkDeleteBlogCategories(Array.from(selectedIds));
          toast.success(`${selectedIds.size} categories deleted`);
          setSelectedIds(new Set());
          setSelectAll(false);
          await fetchCategories();
        } catch (err) {
          toast.error(err instanceof Error ? err.message : "Failed to delete categories");
        }
      },
    });
  };

  // Bulk Update Status
  const handleBulkUpdateStatus = (isActive: boolean) => {
    if (selectedIds.size === 0) return;

    setConfirmDialog({
      isOpen: true,
      title: isActive ? "Activate Categories" : "Deactivate Categories",
      description: `Are you sure you want to ${
        isActive ? "activate" : "deactivate"
      } ${selectedIds.size} categor${selectedIds.size === 1 ? "y" : "ies"}?`,
      onConfirm: async () => {
        try {
          await bulkUpdateBlogCategories(Array.from(selectedIds), { isActive });
          toast.success(`${selectedIds.size} categories ${isActive ? "activated" : "deactivated"}`);
          setSelectedIds(new Set());
          setSelectAll(false);
          await fetchCategories();
        } catch (err) {
          toast.error(err instanceof Error ? err.message : "Failed to update categories");
        }
      },
    });
  };

  // Export
  const handleExport = () => {
    const csv = [
      ["ID", "Name", "Slug", "Description", "Parent ID", "Depth", "Active", "Post Count", "Created At"].join(
        ","
      ),
      ...filteredCategories.map((c) =>
        [
          c.id,
          `"${c.name}"`,
          c.slug,
          `"${c.description || ""}"`,
          c.parentId || "",
          c.depth,
          c.isActive,
          c.postCount,
          new Date(c.createdAt).toISOString(),
        ].join(",")
      ),
    ].join("\n");

    const blob = new Blob([csv], { type: "text/csv" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `blog-categories-${new Date().toISOString().split("T")[0]}.csv`;
    a.click();
    URL.revokeObjectURL(url);

    toast.success("Categories exported successfully");
  };

  const stats = {
    total: categories.length,
    active: categories.filter((c) => c.isActive).length,
    inactive: categories.filter((c) => !c.isActive).length,
    totalPosts: categories.reduce((acc, c) => acc + (c.postCount || 0), 0),
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString("en-US", {
      year: "numeric",
      month: "short",
      day: "numeric",
    });
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="relative overflow-hidden rounded-xl bg-gradient-to-r from-emerald-500 via-teal-500 to-cyan-500 px-8 py-8 text-white">
        <div className="relative z-10">
          <div className="flex items-center gap-2 mb-2">
            <Button
              variant="ghost"
              size="sm"
              className="text-white/80 hover:text-white hover:bg-white/20"
              asChild
            >
              <Link href="/admin/blog">
                <ArrowLeft className="h-4 w-4 mr-1" />
                Back to Blog
              </Link>
            </Button>
          </div>
          <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
              <h1 className="text-3xl font-bold flex items-center gap-3">
                <BookOpen className="h-8 w-8" />
                Blog Categories
              </h1>
              <p className="mt-2 text-teal-100">
                Manage categories for your blog posts
              </p>
            </div>
            <div className="flex gap-2">
              <Button
                variant="outline"
                className="bg-white/10 text-white border-white/20 hover:bg-white/20"
                onClick={handleExport}
              >
                <FileSpreadsheet className="mr-2 h-4 w-4" />
                Export
              </Button>
              <Button
                className="bg-white text-emerald-600 hover:bg-emerald-50"
                onClick={() => {
                  isSlugManuallyEdited.current = false;
                  setShowCreateForm(true);
                }}
              >
                <Plus className="mr-2 h-4 w-4" />
                Add Category
              </Button>
            </div>
          </div>
        </div>
        {/* Decorative circles */}
        <div className="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10" />
        <div className="absolute -bottom-10 -right-10 h-32 w-32 rounded-full bg-white/5" />
      </div>

      {/* Stats */}
      <div className="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <Card className="border-l-4 border-l-emerald-500">
          <CardContent className="pt-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-muted-foreground">Total Categories</p>
                <p className="text-2xl font-bold">{stats.total}</p>
              </div>
              <FolderTree className="h-8 w-8 text-emerald-500" />
            </div>
          </CardContent>
        </Card>
        <Card className="border-l-4 border-l-green-500">
          <CardContent className="pt-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-muted-foreground">Active</p>
                <p className="text-2xl font-bold">{stats.active}</p>
              </div>
              <Check className="h-8 w-8 text-green-500" />
            </div>
          </CardContent>
        </Card>
        <Card className="border-l-4 border-l-gray-400">
          <CardContent className="pt-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-muted-foreground">Inactive</p>
                <p className="text-2xl font-bold">{stats.inactive}</p>
              </div>
              <X className="h-8 w-8 text-gray-400" />
            </div>
          </CardContent>
        </Card>
        <Card className="border-l-4 border-l-cyan-500">
          <CardContent className="pt-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-muted-foreground">Total Posts</p>
                <p className="text-2xl font-bold">{stats.totalPosts}</p>
              </div>
              <FileText className="h-8 w-8 text-cyan-500" />
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Create Form */}
      {showCreateForm && (
        <Card className="border-emerald-200 bg-emerald-50/30">
          <CardHeader>
            <CardTitle className="text-emerald-900 flex items-center gap-2">
              <Plus className="h-5 w-5" />
              Create New Category
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label htmlFor="new-name">
                  Name <span className="text-red-500">*</span>
                </Label>
                <Input
                  id="new-name"
                  value={createFormData.name}
                  onChange={(e) => {
                    const name = e.target.value;
                    setCreateFormData((prev) => {
                      // Only auto-generate slug if it hasn't been manually edited
                      const newSlug = isSlugManuallyEdited.current 
                        ? prev.slug 
                        : name.toLowerCase().trim().replace(/\s+/g, "-").replace(/[^a-z0-9-]/g, "");
                      return {
                        ...prev,
                        name,
                        slug: newSlug,
                      };
                    });
                  }}
                  placeholder="Category name"
                />
              </div>
              <div className="space-y-2">
                <Label htmlFor="new-slug">
                  Slug <span className="text-red-500">*</span>
                </Label>
                <Input
                  id="new-slug"
                  value={createFormData.slug}
                  onChange={(e) => {
                    isSlugManuallyEdited.current = true;
                    setCreateFormData((prev) => ({ ...prev, slug: e.target.value }));
                  }}
                  placeholder="category-slug"
                />
              </div>
            </div>
            <div className="space-y-2">
              <Label htmlFor="new-description">Description</Label>
              <Textarea
                id="new-description"
                value={createFormData.description}
                onChange={(e) =>
                  setCreateFormData((prev) => ({ ...prev, description: e.target.value }))
                }
                placeholder="Category description (optional)"
                rows={2}
              />
            </div>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div className="space-y-2">
                <Label htmlFor="new-parent">Parent Category</Label>
                <select
                  id="new-parent"
                  className="w-full h-10 px-3 rounded-md border border-input bg-background text-sm"
                  value={createFormData.parentId}
                  onChange={(e) =>
                    setCreateFormData((prev) => ({ ...prev, parentId: e.target.value }))
                  }
                >
                  <option value="">None (Top Level)</option>
                  {categories
                    .filter((c) => c.depth === 0)
                    .map((c) => (
                      <option key={c.id} value={c.id}>
                        {c.name}
                      </option>
                    ))}
                </select>
              </div>
              <div className="space-y-2">
                <Label htmlFor="new-sort">Sort Order</Label>
                <Input
                  id="new-sort"
                  type="number"
                  value={createFormData.sortOrder}
                  onChange={(e) =>
                    setCreateFormData((prev) => ({
                      ...prev,
                      sortOrder: parseInt(e.target.value) || 0,
                    }))
                  }
                />
              </div>
              <div className="flex items-center gap-2 pt-6">
                <Switch
                  id="new-active"
                  checked={createFormData.isActive}
                  onCheckedChange={(checked: boolean) =>
                    setCreateFormData((prev) => ({ ...prev, isActive: checked }))
                  }
                />
                <Label htmlFor="new-active">Active</Label>
              </div>
            </div>
            <div className="flex justify-end gap-2 pt-4">
              <Button variant="outline" onClick={() => {
                    isSlugManuallyEdited.current = false;
                    setShowCreateForm(false);
                  }}>
                Cancel
              </Button>
              <Button
                onClick={handleCreate}
                disabled={isCreating}
                className="bg-emerald-600 hover:bg-emerald-700"
              >
                {isCreating ? (
                  <>
                    <div className="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
                    Creating...
                  </>
                ) : (
                  <>
                    <Save className="mr-2 h-4 w-4" />
                    Create Category
                  </>
                )}
              </Button>
            </div>
          </CardContent>
        </Card>
      )}

      {/* Search and Filters */}
      <Card>
        <CardContent className="p-4">
          <div className="flex flex-col sm:flex-row gap-4">
            <div className="flex-1">
              <DataTableSearch
                value={searchQuery}
                onChange={setSearchQuery}
                placeholder="Search categories by name, slug, or description..."
              />
            </div>
            <div className="flex gap-2">
              <Button
                variant="outline"
                size="icon"
                onClick={() => setShowFilters(!showFilters)}
                className={showFilters ? "bg-emerald-50 text-emerald-600" : ""}
              >
                <Filter className="h-4 w-4" />
              </Button>
              <Button
                variant="outline"
                size="icon"
                onClick={() => setViewMode(viewMode === "table" ? "grid" : "table")}
              >
                {viewMode === "table" ? (
                  <Grid3X3 className="h-4 w-4" />
                ) : (
                  <List className="h-4 w-4" />
                )}
              </Button>
            </div>
          </div>

          {showFilters && (
            <div className="mt-4 pt-4 border-t grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div>
                <Label className="text-sm font-medium mb-2 block">Status</Label>
                <div className="flex gap-2">
                  {["all", "active", "inactive"].map((status) => (
                    <Button
                      key={status}
                      variant={filters.status === status ? "default" : "outline"}
                      size="sm"
                      onClick={() => setFilters((prev) => ({ ...prev, status: status as any }))}
                      className={
                        filters.status === status ? "bg-emerald-600 hover:bg-emerald-700" : ""
                      }
                    >
                      {status.charAt(0).toUpperCase() + status.slice(1)}
                    </Button>
                  ))}
                </div>
              </div>
              <div>
                <Label className="text-sm font-medium mb-2 block">Level</Label>
                <div className="flex gap-2">
                  {[
                    { value: "all", label: "All" },
                    { value: "0", label: "Top Level" },
                    { value: "1+", label: "Subcategories" },
                  ].map((option) => (
                    <Button
                      key={option.value}
                      variant={filters.depth === option.value ? "default" : "outline"}
                      size="sm"
                      onClick={() => setFilters((prev) => ({ ...prev, depth: option.value as any }))}
                      className={
                        filters.depth === option.value ? "bg-emerald-600 hover:bg-emerald-700" : ""
                      }
                    >
                      {option.label}
                    </Button>
                  ))}
                </div>
              </div>
            </div>
          )}
        </CardContent>
      </Card>

      {/* Bulk Actions */}
      {selectedIds.size > 0 && (
        <div className="bg-emerald-50 border border-emerald-200 rounded-lg p-4 flex items-center justify-between">
          <div className="flex items-center gap-2">
            <CheckSquare className="h-5 w-5 text-emerald-600" />
            <span className="font-medium text-emerald-900">
              {selectedIds.size} selected
            </span>
          </div>
          <div className="flex gap-2">
            <Button
              variant="outline"
              size="sm"
              onClick={() => handleBulkUpdateStatus(true)}
              className="border-green-200 hover:bg-green-50"
            >
              <Check className="mr-1 h-4 w-4" />
              Activate
            </Button>
            <Button
              variant="outline"
              size="sm"
              onClick={() => handleBulkUpdateStatus(false)}
              className="border-gray-200 hover:bg-gray-50"
            >
              <X className="mr-1 h-4 w-4" />
              Deactivate
            </Button>
            <Button
              variant="outline"
              size="sm"
              onClick={handleBulkDelete}
              className="border-red-200 text-red-600 hover:bg-red-50"
            >
              <Trash2 className="mr-1 h-4 w-4" />
              Delete
            </Button>
          </div>
        </div>
      )}

      {/* Categories Display */}
      <Card>
        <CardContent className="p-0">
          {viewMode === "table" ? (
            <Table>
              <TableHeader>
                <TableRow className="bg-gray-50 hover:bg-gray-50">
                  <TableHead className="w-12">
                    <Checkbox
                      checked={selectAll}
                      onCheckedChange={toggleSelectAll}
                    />
                  </TableHead>
                  <TableHead
                    className="cursor-pointer"
                    onClick={() =>
                      setSort({
                        column: "name",
                        direction: sort.column === "name" && sort.direction === "asc" ? "desc" : "asc",
                      })
                    }
                  >
                    <div className="flex items-center gap-1">
                      Name
                      <ArrowUpDown className="h-3 w-3" />
                    </div>
                  </TableHead>
                  <TableHead className="text-left">Actions</TableHead>
                  <TableHead>Slug</TableHead>
                  <TableHead className="text-center">Level</TableHead>
                  <TableHead className="text-center">Posts</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead
                    className="cursor-pointer"
                    onClick={() =>
                      setSort({
                        column: "createdAt",
                        direction:
                          sort.column === "createdAt" && sort.direction === "asc" ? "desc" : "asc",
                      })
                    }
                  >
                    <div className="flex items-center gap-1">
                      Created
                      <ArrowUpDown className="h-3 w-3" />
                    </div>
                  </TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {loading ? (
                  <SkeletonTableRows rows={5} columns={8} />
                ) : error ? (
                  <TableRow>
                    <TableCell colSpan={8} className="h-32 text-center">
                      <div className="space-y-2">
                        <p className="text-red-500">{error}</p>
                        <Button variant="outline" onClick={fetchCategories}>
                          Retry
                        </Button>
                      </div>
                    </TableCell>
                  </TableRow>
                ) : paginatedCategories.length === 0 ? (
                  <TableRow>
                    <TableCell colSpan={8} className="h-32 text-center text-muted-foreground">
                      <div className="flex flex-col items-center gap-2">
                        <FolderTree className="h-8 w-8 text-gray-400" />
                        <p>No categories found</p>
                        {searchQuery && (
                          <Button variant="outline" size="sm" onClick={() => setSearchQuery("")}>
                            Clear Search
                          </Button>
                        )}
                      </div>
                    </TableCell>
                  </TableRow>
                ) : (
                  paginatedCategories.map((category) => (
                    <React.Fragment key={`group-${category.id}`}>
                      <TableRow
                        className={`${selectedIds.has(category.id) ? "bg-emerald-50/50" : ""} ${
                          fullEditingId === category.id ? "bg-blue-50/30" : ""
                        }`}
                      >
                        <TableCell>
                          <Checkbox
                            checked={selectedIds.has(category.id)}
                            onCheckedChange={() => toggleSelect(category.id)}
                          />
                        </TableCell>
                        <TableCell>
                          {quickEditingId === category.id ? (
                            <div className="flex items-center gap-2">
                              <Input
                                value={editFormData.name || ""}
                                onChange={(e) =>
                                  setEditFormData((prev) => ({ ...prev, name: e.target.value }))
                                }
                                className="h-8 w-40"
                                autoFocus
                                onKeyDown={(e) => {
                                  if (e.key === "Enter") saveQuickEdit();
                                  if (e.key === "Escape") setQuickEditingId(null);
                                }}
                              />
                              <Button
                                size="sm"
                                className="h-8 w-8 p-0"
                                onClick={saveQuickEdit}
                                disabled={isSaving}
                              >
                                <Check className="h-4 w-4" />
                              </Button>
                              <Button
                                size="sm"
                                variant="ghost"
                                className="h-8 w-8 p-0"
                                onClick={() => setQuickEditingId(null)}
                              >
                                <X className="h-4 w-4" />
                              </Button>
                            </div>
                          ) : (
                            <div className="flex items-center gap-2">
                              <span className="text-gray-400">
                                {"│  ".repeat(category.depth)}
                              </span>
                              <span className="font-medium">{category.name}</span>
                              {category.depth === 0 && (
                                <Badge variant="secondary" className="text-xs bg-emerald-100 text-emerald-700">
                                  Top
                                </Badge>
                              )}
                            </div>
                          )}
                        </TableCell>
                        <TableCell className="text-left">
                          <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                              <Button variant="ghost" size="sm">
                                <MoreHorizontal className="h-4 w-4" />
                              </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                              <DropdownMenuItem
                                onClick={() => startQuickEdit(category)}
                                disabled={quickEditingId === category.id}
                              >
                                <Edit2 className="mr-2 h-4 w-4" />
                                Quick Edit
                              </DropdownMenuItem>
                              <DropdownMenuItem onClick={() => startFullEdit(category)}>
                                <Edit2 className="mr-2 h-4 w-4" />
                                Full Edit
                              </DropdownMenuItem>
                              <DropdownMenuItem
                                onClick={() =>
                                  router.push(`/admin/blog/categories/${category.id}`)
                                }
                              >
                                <Eye className="mr-2 h-4 w-4" />
                                View Details
                              </DropdownMenuItem>
                              <DropdownMenuSeparator />
                              <DropdownMenuItem
                                onClick={() => handleDelete(category)}
                                className="text-red-600"
                              >
                                <Trash2 className="mr-2 h-4 w-4" />
                                Delete
                              </DropdownMenuItem>
                            </DropdownMenuContent>
                          </DropdownMenu>
                        </TableCell>
                        <TableCell>
                          <code className="text-xs bg-gray-100 px-2 py-1 rounded">
                            {category.slug}
                          </code>
                        </TableCell>
                        <TableCell className="text-center">
                          <Badge variant="outline" className="text-xs">
                            {category.depth === 0 ? "Top" : `Level ${category.depth}`}
                          </Badge>
                        </TableCell>
                        <TableCell className="text-center">
                          <Badge className="bg-cyan-100 text-cyan-700">
                            {category.postCount || 0}
                          </Badge>
                        </TableCell>
                        <TableCell>
                          <Badge
                            variant={category.isActive ? "default" : "secondary"}
                            className={
                              category.isActive
                                ? "bg-green-100 text-green-700 hover:bg-green-100"
                                : "bg-gray-100 text-gray-600 hover:bg-gray-100"
                            }
                          >
                            {category.isActive ? "Active" : "Inactive"}
                          </Badge>
                        </TableCell>
                        <TableCell className="text-sm text-gray-500">
                          {formatDate(category.createdAt)}
                        </TableCell>
                      </TableRow>

                      {/* Full Edit Panel */}
                      {fullEditingId === category.id && (
                        <TableRow className="bg-blue-50/30 border-l-4 border-l-blue-400">
                          <TableCell colSpan={8} className="p-6">
                            <div className="space-y-4">
                              <div className="flex items-center justify-between">
                                <h4 className="font-semibold text-blue-900 flex items-center gap-2">
                                  <Edit2 className="h-4 w-4" />
                                  Edit Category: {category.name}
                                </h4>
                                <Button
                                  variant="ghost"
                                  size="sm"
                                  onClick={() => setFullEditingId(null)}
                                >
                                  <X className="h-4 w-4" />
                                </Button>
                              </div>

                              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="space-y-2">
                                  <Label htmlFor={`edit-name-${category.id}`}>Name</Label>
                                  <Input
                                    id={`edit-name-${category.id}`}
                                    value={editFormData.name || ""}
                                    onChange={(e) =>
                                      setEditFormData((prev) => ({ ...prev, name: e.target.value }))
                                    }
                                  />
                                </div>
                                <div className="space-y-2">
                                  <Label htmlFor={`edit-slug-${category.id}`}>Slug</Label>
                                  <Input
                                    id={`edit-slug-${category.id}`}
                                    value={editFormData.slug || ""}
                                    onChange={(e) =>
                                      setEditFormData((prev) => ({ ...prev, slug: e.target.value }))
                                    }
                                  />
                                </div>
                              </div>

                              <div className="space-y-2">
                                <Label htmlFor={`edit-desc-${category.id}`}>Description</Label>
                                <Textarea
                                  id={`edit-desc-${category.id}`}
                                  value={editFormData.description || ""}
                                  onChange={(e) =>
                                    setEditFormData((prev) => ({
                                      ...prev,
                                      description: e.target.value,
                                    }))
                                  }
                                  rows={2}
                                />
                              </div>

                              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div className="space-y-2">
                                  <Label htmlFor={`edit-parent-${category.id}`}>
                                    Parent Category
                                  </Label>
                                  <select
                                    id={`edit-parent-${category.id}`}
                                    className="w-full h-10 px-3 rounded-md border border-input bg-background text-sm"
                                    value={editFormData.parentId || ""}
                                    onChange={(e) =>
                                      setEditFormData((prev) => ({
                                        ...prev,
                                        parentId: e.target.value || null,
                                      }))
                                    }
                                  >
                                    <option value="">None (Top Level)</option>
                                    {categories
                                      .filter((c) => c.id !== category.id && c.depth === 0)
                                      .map((c) => (
                                        <option key={c.id} value={c.id}>
                                          {c.name}
                                        </option>
                                      ))}
                                  </select>
                                </div>
                                <div className="space-y-2">
                                  <Label htmlFor={`edit-sort-${category.id}`}>Sort Order</Label>
                                  <Input
                                    id={`edit-sort-${category.id}`}
                                    type="number"
                                    value={editFormData.sortOrder || 0}
                                    onChange={(e) =>
                                      setEditFormData((prev) => ({
                                        ...prev,
                                        sortOrder: parseInt(e.target.value) || 0,
                                      }))
                                    }
                                  />
                                </div>
                                <div className="flex items-center gap-2 pt-6">
                                  <Switch
                                    id={`edit-active-${category.id}`}
                                    checked={editFormData.isActive || false}
                                    onCheckedChange={(checked: boolean) =>
                                      setEditFormData((prev) => ({
                                        ...prev,
                                        isActive: checked,
                                      }))
                                    }
                                  />
                                  <Label htmlFor={`edit-active-${category.id}`}>Active</Label>
                                </div>
                              </div>

                              <div className="flex justify-end gap-2 pt-4">
                                <Button
                                  variant="outline"
                                  onClick={() => setFullEditingId(null)}
                                >
                                  Cancel
                                </Button>
                                <Button
                                  onClick={saveFullEdit}
                                  disabled={isSaving}
                                  className="bg-blue-600 hover:bg-blue-700"
                                >
                                  {isSaving ? (
                                    <>
                                      <div className="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
                                      Saving...
                                    </>
                                  ) : (
                                    <>
                                      <Save className="mr-2 h-4 w-4" />
                                      Save Changes
                                    </>
                                  )}
                                </Button>
                              </div>
                            </div>
                          </TableCell>
                        </TableRow>
                      )}
                    </React.Fragment>
                  ))
                )}
              </TableBody>
            </Table>
          ) : (
            // Grid View
            <div className="p-4">
              {loading ? (
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                  {[...Array(6)].map((_, i) => (
                    <div
                      key={i}
                      className="h-32 bg-gray-100 animate-pulse rounded-lg"
                    />
                  ))}
                </div>
              ) : paginatedCategories.length === 0 ? (
                <div className="text-center py-12">
                  <FolderTree className="h-12 w-12 text-gray-400 mx-auto mb-4" />
                  <p className="text-muted-foreground">No categories found</p>
                </div>
              ) : (
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                  {paginatedCategories.map((category) => (
                    <div
                      key={category.id}
                      className={`p-4 rounded-lg border transition-all ${
                        selectedIds.has(category.id)
                          ? "border-emerald-500 bg-emerald-50/30"
                          : "border-gray-200 hover:border-emerald-300 hover:shadow-md"
                      }`}
                    >
                      <div className="flex items-start justify-between">
                        <div className="flex items-center gap-2">
                          <Checkbox
                            checked={selectedIds.has(category.id)}
                            onCheckedChange={() => toggleSelect(category.id)}
                          />
                          <div>
                            <h4 className="font-medium">{category.name}</h4>
                            <code className="text-xs text-muted-foreground">
                              {category.slug}
                            </code>
                          </div>
                        </div>
                        <DropdownMenu>
                          <DropdownMenuTrigger asChild>
                            <Button variant="ghost" size="sm" className="h-8 w-8 p-0">
                              <MoreVertical className="h-4 w-4" />
                            </Button>
                          </DropdownMenuTrigger>
                          <DropdownMenuContent align="end">
                            <DropdownMenuItem onClick={() => startFullEdit(category)}>
                              <Edit2 className="mr-2 h-4 w-4" />
                              Edit
                            </DropdownMenuItem>
                            <DropdownMenuItem
                              onClick={() => handleDelete(category)}
                              className="text-red-600"
                            >
                              <Trash2 className="mr-2 h-4 w-4" />
                              Delete
                            </DropdownMenuItem>
                          </DropdownMenuContent>
                        </DropdownMenu>
                      </div>
                      <div className="mt-3 flex items-center gap-2">
                        <Badge
                          variant={category.isActive ? "default" : "secondary"}
                          className={
                            category.isActive
                              ? "bg-green-100 text-green-700"
                              : "bg-gray-100 text-gray-600"
                          }
                        >
                          {category.isActive ? "Active" : "Inactive"}
                        </Badge>
                        <Badge variant="outline" className="bg-cyan-50">
                          {category.postCount || 0} posts
                        </Badge>
                        {category.depth > 0 && (
                          <Badge variant="outline">Level {category.depth}</Badge>
                        )}
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>
          )}
        </CardContent>
      </Card>

      {/* Pagination */}
      {!loading && filteredCategories.length > 0 && (
        <DataTablePagination
          currentPage={currentPage}
          totalPages={totalPages}
          pageSize={itemsPerPage}
          totalItems={filteredCategories.length}
          onPageChange={setCurrentPage}
          onPageSizeChange={(value) => {
            setItemsPerPage(value);
            setCurrentPage(1);
          }}
        />
      )}

      {/* Confirm Dialog */}
      <ConfirmDialog
        isOpen={confirmDialog.isOpen}
        title={confirmDialog.title}
        description={confirmDialog.description}
        onConfirm={() => {
          confirmDialog.onConfirm();
          setConfirmDialog((prev) => ({ ...prev, isOpen: false }));
        }}
        onClose={() => setConfirmDialog((prev) => ({ ...prev, isOpen: false }))}
      />
    </div>
  );
}
