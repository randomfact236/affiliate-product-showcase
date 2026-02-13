"use client";

import React, { useEffect, useState, useMemo, useRef } from "react";
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
  Tag,
  ArrowLeft,
  BookOpen,
  Check,
  X,
  Save,
  Grid3X3,
  List,
  CheckSquare,
  MoreVertical,
  Eye,
  FileSpreadsheet,
  ArrowUpDown,
  Palette,
} from "lucide-react";
import { toast } from "sonner";
import {
  getBlogTags,
  createBlogTag,
  updateBlogTag,
  deleteBlogTag,
  bulkDeleteBlogTags,
  bulkUpdateBlogTags,
  type BlogTag,
} from "@/lib/api/blog-tags";
import { SkeletonTableRows } from "@/components/ui/skeleton-table";
import { DataTableSearch } from "@/components/ui/data-table-search";
import { DataTablePagination } from "@/components/ui/data-table-pagination";
import { ConfirmDialog } from "@/components/ui/confirm-dialog";

interface FilterState {
  status: "all" | "active" | "inactive";
  hasColor: "all" | "yes" | "no";
}

interface SortState {
  column: "name" | "slug" | "postCount" | "createdAt";
  direction: "asc" | "desc";
}

const PRESET_COLORS = [
  { value: "#EF4444", label: "Red" },
  { value: "#F97316", label: "Orange" },
  { value: "#F59E0B", label: "Amber" },
  { value: "#84CC16", label: "Lime" },
  { value: "#22C55E", label: "Green" },
  { value: "#10B981", label: "Emerald" },
  { value: "#14B8A6", label: "Teal" },
  { value: "#06B6D4", label: "Cyan" },
  { value: "#0EA5E9", label: "Sky" },
  { value: "#3B82F6", label: "Blue" },
  { value: "#6366F1", label: "Indigo" },
  { value: "#8B5CF6", label: "Violet" },
  { value: "#A855F7", label: "Purple" },
  { value: "#D946EF", label: "Fuchsia" },
  { value: "#EC4899", label: "Pink" },
  { value: "#F43F5E", label: "Rose" },
  { value: "#6B7280", label: "Gray" },
  { value: "#1F2937", label: "Dark" },
];

export default function BlogTagsPage() {
  const router = useRouter();
  const [tags, setTags] = useState<BlogTag[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // Search and Filter
  const [searchQuery, setSearchQuery] = useState("");
  const [filters, setFilters] = useState<FilterState>({
    status: "all",
    hasColor: "all",
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
  const [editFormData, setEditFormData] = useState<Partial<BlogTag>>({});
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
    color: "",
    isActive: true,
  });
  const isSlugManuallyEdited = useRef(false);
  const [isCreating, setIsCreating] = useState(false);

  useEffect(() => {
    fetchTags();
  }, []);

  const fetchTags = async () => {
    try {
      setLoading(true);
      const data = await getBlogTags();
      setTags(data);
    } catch (err) {
      console.error("Failed to fetch tags:", err);
      setError("Failed to load tags");
      toast.error("Failed to load tags");
    } finally {
      setLoading(false);
    }
  };

  // Filter and sort tags
  const filteredTags = useMemo(() => {
    let result = [...tags];

    // Search filter
    if (searchQuery) {
      const query = searchQuery.toLowerCase();
      result = result.filter(
        (tag) =>
          tag.name.toLowerCase().includes(query) ||
          tag.slug.toLowerCase().includes(query) ||
          (tag.description && tag.description.toLowerCase().includes(query))
      );
    }

    // Status filter
    if (filters.status !== "all") {
      result = result.filter((tag) =>
        filters.status === "active" ? tag.isActive : !tag.isActive
      );
    }

    // Color filter
    if (filters.hasColor !== "all") {
      result = result.filter((tag) =>
        filters.hasColor === "yes" ? tag.color : !tag.color
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
  }, [tags, searchQuery, filters, sort]);

  // Pagination
  const totalPages = Math.ceil(filteredTags.length / itemsPerPage);
  const paginatedTags = useMemo(() => {
    const start = (currentPage - 1) * itemsPerPage;
    return filteredTags.slice(start, start + itemsPerPage);
  }, [filteredTags, currentPage, itemsPerPage]);

  // Selection handlers
  const toggleSelectAll = () => {
    if (selectAll) {
      setSelectedIds(new Set());
    } else {
      setSelectedIds(new Set(paginatedTags.map((t) => t.id)));
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
  const startQuickEdit = (tag: BlogTag) => {
    setQuickEditingId(tag.id);
    setFullEditingId(null);
    setEditFormData({ name: tag.name });
  };

  const saveQuickEdit = async () => {
    if (!quickEditingId || !editFormData.name?.trim()) return;

    try {
      setIsSaving(true);
      await updateBlogTag(quickEditingId, { name: editFormData.name.trim() });
      toast.success("Tag updated successfully");
      await fetchTags();
      setQuickEditingId(null);
    } catch (err) {
      toast.error(err instanceof Error ? err.message : "Failed to update tag");
    } finally {
      setIsSaving(false);
    }
  };

  // Full Edit
  const startFullEdit = (tag: BlogTag) => {
    setFullEditingId(tag.id);
    setQuickEditingId(null);
    setEditFormData({ ...tag });
  };

  const saveFullEdit = async () => {
    if (!fullEditingId || !editFormData.name?.trim()) return;

    try {
      setIsSaving(true);
      await updateBlogTag(fullEditingId, {
        name: editFormData.name.trim(),
        slug: editFormData.slug,
        description: editFormData.description || undefined,
        color: editFormData.color || undefined,
        isActive: editFormData.isActive,
      });
      toast.success("Tag updated successfully");
      await fetchTags();
      setFullEditingId(null);
    } catch (err) {
      toast.error(err instanceof Error ? err.message : "Failed to update tag");
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
      await createBlogTag({
        name: createFormData.name.trim(),
        slug: createFormData.slug.trim(),
        description: createFormData.description || undefined,
        color: createFormData.color || undefined,
        isActive: createFormData.isActive,
      });
      toast.success("Tag created successfully");
      await fetchTags();
      setShowCreateForm(false);
      isSlugManuallyEdited.current = false;
      setCreateFormData({
        name: "",
        slug: "",
        description: "",
        color: "",
        isActive: true,
      });
    } catch (err) {
      toast.error(err instanceof Error ? err.message : "Failed to create tag");
    } finally {
      setIsCreating(false);
    }
  };

  // Delete
  const handleDelete = (tag: BlogTag) => {
    setConfirmDialog({
      isOpen: true,
      title: "Delete Tag",
      description: `Are you sure you want to delete "${tag.name}"? This action cannot be undone.`,
      onConfirm: async () => {
        try {
          await deleteBlogTag(tag.id);
          toast.success("Tag deleted successfully");
          await fetchTags();
        } catch (err) {
          toast.error(err instanceof Error ? err.message : "Failed to delete tag");
        }
      },
    });
  };

  // Bulk Delete
  const handleBulkDelete = () => {
    if (selectedIds.size === 0) return;

    setConfirmDialog({
      isOpen: true,
      title: "Delete Tags",
      description: `Are you sure you want to delete ${selectedIds.size} tag${
        selectedIds.size === 1 ? "" : "s"
      }? This action cannot be undone.`,
      onConfirm: async () => {
        try {
          await bulkDeleteBlogTags(Array.from(selectedIds));
          toast.success(`${selectedIds.size} tags deleted`);
          setSelectedIds(new Set());
          setSelectAll(false);
          await fetchTags();
        } catch (err) {
          toast.error(err instanceof Error ? err.message : "Failed to delete tags");
        }
      },
    });
  };

  // Bulk Update Status
  const handleBulkUpdateStatus = (isActive: boolean) => {
    if (selectedIds.size === 0) return;

    setConfirmDialog({
      isOpen: true,
      title: isActive ? "Activate Tags" : "Deactivate Tags",
      description: `Are you sure you want to ${
        isActive ? "activate" : "deactivate"
      } ${selectedIds.size} tag${selectedIds.size === 1 ? "" : "s"}?`,
      onConfirm: async () => {
        try {
          await bulkUpdateBlogTags(Array.from(selectedIds), { isActive });
          toast.success(`${selectedIds.size} tags ${isActive ? "activated" : "deactivated"}`);
          setSelectedIds(new Set());
          setSelectAll(false);
          await fetchTags();
        } catch (err) {
          toast.error(err instanceof Error ? err.message : "Failed to update tags");
        }
      },
    });
  };

  // Export
  const handleExport = () => {
    const csv = [
      ["ID", "Name", "Slug", "Description", "Color", "Active", "Post Count", "Created At"].join(
        ","
      ),
      ...filteredTags.map((t) =>
        [
          t.id,
          `"${t.name}"`,
          t.slug,
          `"${t.description || ""}"`,
          t.color || "",
          t.isActive,
          t.postCount,
          new Date(t.createdAt).toISOString(),
        ].join(",")
      ),
    ].join("\n");

    const blob = new Blob([csv], { type: "text/csv" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `blog-tags-${new Date().toISOString().split("T")[0]}.csv`;
    a.click();
    URL.revokeObjectURL(url);

    toast.success("Tags exported successfully");
  };

  const stats = {
    total: tags.length,
    active: tags.filter((t) => t.isActive).length,
    inactive: tags.filter((t) => !t.isActive).length,
    withColor: tags.filter((t) => t.color).length,
    totalPosts: tags.reduce((acc, t) => acc + (t.postCount || 0), 0),
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
      <div className="relative overflow-hidden rounded-xl bg-gradient-to-r from-violet-500 via-purple-500 to-fuchsia-500 px-8 py-8 text-white">
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
                <Tag className="h-8 w-8" />
                Blog Tags
              </h1>
              <p className="mt-2 text-purple-100">
                Manage tags for organizing your blog posts
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
                className="bg-white text-purple-600 hover:bg-purple-50"
                onClick={() => {
                  isSlugManuallyEdited.current = false;
                  setShowCreateForm(true);
                }}
              >
                <Plus className="mr-2 h-4 w-4" />
                Add Tag
              </Button>
            </div>
          </div>
        </div>
        {/* Decorative circles */}
        <div className="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10" />
        <div className="absolute -bottom-10 -right-10 h-32 w-32 rounded-full bg-white/5" />
      </div>

      {/* Stats */}
      <div className="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-4">
        <Card className="border-l-4 border-l-violet-500">
          <CardContent className="pt-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-muted-foreground">Total Tags</p>
                <p className="text-2xl font-bold">{stats.total}</p>
              </div>
              <Tag className="h-8 w-8 text-violet-500" />
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
        <Card className="border-l-4 border-l-pink-500">
          <CardContent className="pt-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-muted-foreground">With Color</p>
                <p className="text-2xl font-bold">{stats.withColor}</p>
              </div>
              <Palette className="h-8 w-8 text-pink-500" />
            </div>
          </CardContent>
        </Card>
        <Card className="border-l-4 border-l-cyan-500 col-span-2 sm:col-span-1">
          <CardContent className="pt-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-muted-foreground">Total Posts</p>
                <p className="text-2xl font-bold">{stats.totalPosts}</p>
              </div>
              <BookOpen className="h-8 w-8 text-cyan-500" />
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Create Form */}
      {showCreateForm && (
        <Card className="border-violet-200 bg-violet-50/30">
          <CardHeader>
            <CardTitle className="text-violet-900 flex items-center gap-2">
              <Plus className="h-5 w-5" />
              Create New Tag
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
                  placeholder="Tag name"
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
                  placeholder="tag-slug"
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
                placeholder="Tag description (optional)"
                rows={2}
              />
            </div>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label>Color</Label>
                <div className="flex flex-wrap gap-2">
                  <button
                    className={`w-8 h-8 rounded-full border-2 ${
                      !createFormData.color ? "border-gray-400 ring-2 ring-offset-2 ring-gray-400" : "border-gray-200"
                    }`}
                    style={{ background: "transparent" }}
                    onClick={() => setCreateFormData((prev) => ({ ...prev, color: "" }))}
                    title="No color"
                  >
                    <X className="h-4 w-4 mx-auto text-gray-400" />
                  </button>
                  {PRESET_COLORS.map((color) => (
                    <button
                      key={color.value}
                      className={`w-8 h-8 rounded-full border-2 ${
                        createFormData.color === color.value
                          ? "border-white ring-2 ring-offset-2 ring-violet-500"
                          : "border-transparent"
                      }`}
                      style={{ backgroundColor: color.value }}
                      onClick={() =>
                        setCreateFormData((prev) => ({ ...prev, color: color.value }))
                      }
                      title={color.label}
                    />
                  ))}
                </div>
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
                className="bg-violet-600 hover:bg-violet-700"
              >
                {isCreating ? (
                  <>
                    <div className="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
                    Creating...
                  </>
                ) : (
                  <>
                    <Save className="mr-2 h-4 w-4" />
                    Create Tag
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
                placeholder="Search tags by name, slug, or description..."
              />
            </div>
            <div className="flex gap-2">
              <Button
                variant="outline"
                size="icon"
                onClick={() => setShowFilters(!showFilters)}
                className={showFilters ? "bg-violet-50 text-violet-600" : ""}
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
                  <Tag className="h-4 w-4" />
                )}
              </Button>
            </div>
          </div>

          {showFilters && (
            <div className="mt-4 pt-4 border-t grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div>
                <Label className="text-sm font-medium mb-2 block">Status</Label>
                <div className="flex gap-2 flex-wrap">
                  {["all", "active", "inactive"].map((status) => (
                    <Button
                      key={status}
                      variant={filters.status === status ? "default" : "outline"}
                      size="sm"
                      onClick={() => setFilters((prev) => ({ ...prev, status: status as any }))}
                      className={
                        filters.status === status ? "bg-violet-600 hover:bg-violet-700" : ""
                      }
                    >
                      {status.charAt(0).toUpperCase() + status.slice(1)}
                    </Button>
                  ))}
                </div>
              </div>
              <div>
                <Label className="text-sm font-medium mb-2 block">Color</Label>
                <div className="flex gap-2 flex-wrap">
                  {[
                    { value: "all", label: "All" },
                    { value: "yes", label: "Has Color" },
                    { value: "no", label: "No Color" },
                  ].map((option) => (
                    <Button
                      key={option.value}
                      variant={filters.hasColor === option.value ? "default" : "outline"}
                      size="sm"
                      onClick={() => setFilters((prev) => ({ ...prev, hasColor: option.value as any }))}
                      className={
                        filters.hasColor === option.value ? "bg-violet-600 hover:bg-violet-700" : ""
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
        <div className="bg-violet-50 border border-violet-200 rounded-lg p-4 flex items-center justify-between">
          <div className="flex items-center gap-2">
            <CheckSquare className="h-5 w-5 text-violet-600" />
            <span className="font-medium text-violet-900">
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

      {/* Tags Display */}
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
                      Tag
                      <ArrowUpDown className="h-3 w-3" />
                    </div>
                  </TableHead>
                  <TableHead className="text-left">Actions</TableHead>
                  <TableHead>Slug</TableHead>
                  <TableHead className="text-center">Color</TableHead>
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
                        <Button variant="outline" onClick={fetchTags}>
                          Retry
                        </Button>
                      </div>
                    </TableCell>
                  </TableRow>
                ) : paginatedTags.length === 0 ? (
                  <TableRow>
                    <TableCell colSpan={8} className="h-32 text-center text-muted-foreground">
                      <div className="flex flex-col items-center gap-2">
                        <Tag className="h-8 w-8 text-gray-400" />
                        <p>No tags found</p>
                        {searchQuery && (
                          <Button variant="outline" size="sm" onClick={() => setSearchQuery("")}>
                            Clear Search
                          </Button>
                        )}
                      </div>
                    </TableCell>
                  </TableRow>
                ) : (
                  paginatedTags.map((tag) => (
                    <React.Fragment key={`group-${tag.id}`}>
                      <TableRow
                        className={`${selectedIds.has(tag.id) ? "bg-violet-50/50" : ""} ${
                          fullEditingId === tag.id ? "bg-blue-50/30" : ""
                        }`}
                      >
                        <TableCell>
                          <Checkbox
                            checked={selectedIds.has(tag.id)}
                            onCheckedChange={() => toggleSelect(tag.id)}
                          />
                        </TableCell>
                        <TableCell>
                          {quickEditingId === tag.id ? (
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
                              {tag.color && (
                                <span
                                  className="w-4 h-4 rounded-full border border-gray-200"
                                  style={{ backgroundColor: tag.color }}
                                />
                              )}
                              <span className="font-medium">{tag.name}</span>
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
                                onClick={() => startQuickEdit(tag)}
                                disabled={quickEditingId === tag.id}
                              >
                                <Edit2 className="mr-2 h-4 w-4" />
                                Quick Edit
                              </DropdownMenuItem>
                              <DropdownMenuItem onClick={() => startFullEdit(tag)}>
                                <Edit2 className="mr-2 h-4 w-4" />
                                Full Edit
                              </DropdownMenuItem>
                              <DropdownMenuSeparator />
                              <DropdownMenuItem
                                onClick={() => handleDelete(tag)}
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
                            {tag.slug}
                          </code>
                        </TableCell>
                        <TableCell className="text-center">
                          {tag.color ? (
                            <div className="flex items-center justify-center gap-2">
                              <span
                                className="w-6 h-6 rounded-full border border-gray-200"
                                style={{ backgroundColor: tag.color }}
                              />
                            </div>
                          ) : (
                            <span className="text-gray-400 text-sm">-</span>
                          )}
                        </TableCell>
                        <TableCell className="text-center">
                          <Badge className="bg-cyan-100 text-cyan-700">
                            {tag.postCount || 0}
                          </Badge>
                        </TableCell>
                        <TableCell>
                          <Badge
                            variant={tag.isActive ? "default" : "secondary"}
                            className={
                              tag.isActive
                                ? "bg-green-100 text-green-700 hover:bg-green-100"
                                : "bg-gray-100 text-gray-600 hover:bg-green-100"
                            }
                          >
                            {tag.isActive ? "Active" : "Inactive"}
                          </Badge>
                        </TableCell>
                        <TableCell className="text-sm text-gray-500">
                          {formatDate(tag.createdAt)}
                        </TableCell>
                      </TableRow>

                      {/* Full Edit Panel */}
                      {fullEditingId === tag.id && (
                        <TableRow className="bg-blue-50/30 border-l-4 border-l-blue-400">
                          <TableCell colSpan={8} className="p-6">
                            <div className="space-y-4">
                              <div className="flex items-center justify-between">
                                <h4 className="font-semibold text-blue-900 flex items-center gap-2">
                                  <Edit2 className="h-4 w-4" />
                                  Edit Tag: {tag.name}
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
                                  <Label htmlFor={`edit-name-${tag.id}`}>Name</Label>
                                  <Input
                                    id={`edit-name-${tag.id}`}
                                    value={editFormData.name || ""}
                                    onChange={(e) =>
                                      setEditFormData((prev) => ({ ...prev, name: e.target.value }))
                                    }
                                  />
                                </div>
                                <div className="space-y-2">
                                  <Label htmlFor={`edit-slug-${tag.id}`}>Slug</Label>
                                  <Input
                                    id={`edit-slug-${tag.id}`}
                                    value={editFormData.slug || ""}
                                    onChange={(e) =>
                                      setEditFormData((prev) => ({ ...prev, slug: e.target.value }))
                                    }
                                  />
                                </div>
                              </div>

                              <div className="space-y-2">
                                <Label htmlFor={`edit-desc-${tag.id}`}>Description</Label>
                                <Textarea
                                  id={`edit-desc-${tag.id}`}
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

                              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="space-y-2">
                                  <Label>Color</Label>
                                  <div className="flex flex-wrap gap-2">
                                    <button
                                      className={`w-8 h-8 rounded-full border-2 ${
                                        !editFormData.color ? "border-gray-400 ring-2 ring-offset-2 ring-gray-400" : "border-gray-200"
                                      }`}
                                      style={{ background: "transparent" }}
                                      onClick={() =>
                                        setEditFormData((prev) => ({ ...prev, color: "" }))
                                      }
                                    >
                                      <X className="h-4 w-4 mx-auto text-gray-400" />
                                    </button>
                                    {PRESET_COLORS.map((color) => (
                                      <button
                                        key={color.value}
                                        className={`w-8 h-8 rounded-full border-2 ${
                                          editFormData.color === color.value
                                            ? "border-white ring-2 ring-offset-2 ring-blue-500"
                                            : "border-transparent"
                                        }`}
                                        style={{ backgroundColor: color.value }}
                                        onClick={() =>
                                          setEditFormData((prev) => ({
                                            ...prev,
                                            color: color.value,
                                          }))
                                        }
                                        title={color.label}
                                      />
                                    ))}
                                  </div>
                                </div>
                                <div className="flex items-center gap-2 pt-6">
                                  <Switch
                                    id={`edit-active-${tag.id}`}
                                    checked={editFormData.isActive || false}
                                    onCheckedChange={(checked: boolean) =>
                                      setEditFormData((prev) => ({
                                        ...prev,
                                        isActive: checked,
                                      }))
                                    }
                                  />
                                  <Label htmlFor={`edit-active-${tag.id}`}>Active</Label>
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
                <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                  {[...Array(8)].map((_, i) => (
                    <div
                      key={i}
                      className="h-28 bg-gray-100 animate-pulse rounded-lg"
                    />
                  ))}
                </div>
              ) : paginatedTags.length === 0 ? (
                <div className="text-center py-12">
                  <Tag className="h-12 w-12 text-gray-400 mx-auto mb-4" />
                  <p className="text-muted-foreground">No tags found</p>
                </div>
              ) : (
                <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                  {paginatedTags.map((tag) => (
                    <div
                      key={tag.id}
                      className={`p-4 rounded-lg border transition-all ${
                        selectedIds.has(tag.id)
                          ? "border-violet-500 bg-violet-50/30"
                          : "border-gray-200 hover:border-violet-300 hover:shadow-md"
                      }`}
                    >
                      <div className="flex items-start justify-between">
                        <Checkbox
                          checked={selectedIds.has(tag.id)}
                          onCheckedChange={() => toggleSelect(tag.id)}
                        />
                        <DropdownMenu>
                          <DropdownMenuTrigger asChild>
                            <Button variant="ghost" size="sm" className="h-6 w-6 p-0">
                              <MoreVertical className="h-3 w-3" />
                            </Button>
                          </DropdownMenuTrigger>
                          <DropdownMenuContent align="end">
                            <DropdownMenuItem onClick={() => startFullEdit(tag)}>
                              <Edit2 className="mr-2 h-4 w-4" />
                              Edit
                            </DropdownMenuItem>
                            <DropdownMenuItem
                              onClick={() => handleDelete(tag)}
                              className="text-red-600"
                            >
                              <Trash2 className="mr-2 h-4 w-4" />
                              Delete
                            </DropdownMenuItem>
                          </DropdownMenuContent>
                        </DropdownMenu>
                      </div>
                      <div className="mt-2 flex items-center gap-2">
                        {tag.color && (
                          <span
                            className="w-4 h-4 rounded-full border border-gray-200 flex-shrink-0"
                            style={{ backgroundColor: tag.color }}
                          />
                        )}
                        <span className="font-medium truncate">{tag.name}</span>
                      </div>
                      <code className="text-xs text-muted-foreground block mt-1 truncate">
                        {tag.slug}
                      </code>
                      <div className="mt-2 flex items-center justify-between">
                        <Badge
                          variant={tag.isActive ? "default" : "secondary"}
                          className={
                            tag.isActive
                              ? "bg-green-100 text-green-700 text-xs"
                              : "bg-gray-100 text-gray-600 text-xs"
                          }
                        >
                          {tag.isActive ? "Active" : "Inactive"}
                        </Badge>
                        <Badge variant="outline" className="bg-cyan-50 text-xs">
                          {tag.postCount || 0}
                        </Badge>
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
      {!loading && filteredTags.length > 0 && (
        <DataTablePagination
          currentPage={currentPage}
          totalPages={totalPages}
          pageSize={itemsPerPage}
          totalItems={filteredTags.length}
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
