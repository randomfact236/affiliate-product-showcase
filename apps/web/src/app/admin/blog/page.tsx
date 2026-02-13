"use client";

import React, { useEffect, useState, useMemo, useCallback } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Badge } from "@/components/ui/badge";
import { Checkbox } from "@/components/ui/checkbox";
import { Switch } from "@/components/ui/switch";
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
  Search,
  MoreHorizontal,
  Edit,
  Trash2,
  Eye,
  Calendar,
  User,
  Loader2,
  Check,
  X,
  Save,
  Filter,
  Grid3X3,
  List,
  CheckSquare,
  FileSpreadsheet,
  Download,
  ArrowUpDown,
  BookOpen,
  Clock,
  EyeIcon,
  Heart,
  Share2,
  MoreVertical,
} from "lucide-react";
import { toast } from "sonner";
import {
  getBlogPosts,
  updateBlogPost,
  deleteBlogPost,
  bulkDeleteBlogPosts,
  bulkUpdateBlogPosts,
  type BlogPost,
} from "@/lib/api/blog";
import { SkeletonTableRows } from "@/components/ui/skeleton-table";
import { DataTablePagination } from "@/components/ui/data-table-pagination";
import { ConfirmDialog } from "@/components/ui/confirm-dialog";

interface FilterState {
  status: "all" | "PUBLISHED" | "DRAFT" | "PENDING_REVIEW" | "ARCHIVED";
}

interface SortState {
  column: "title" | "createdAt" | "updatedAt" | "viewCount" | "publishedAt";
  direction: "asc" | "desc";
}

export default function AdminBlogPage() {
  const router = useRouter();
  const [posts, setPosts] = useState<BlogPost[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // Search and Filter
  const [searchQuery, setSearchQuery] = useState("");
  const [filters, setFilters] = useState<FilterState>({
    status: "all",
  });
  const [sort, setSort] = useState<SortState>({
    column: "createdAt",
    direction: "desc",
  });

  // Selection
  const [selectedIds, setSelectedIds] = useState<Set<string>>(new Set());
  const [selectAll, setSelectAll] = useState(false);

  // Editing States
  const [quickEditingId, setQuickEditingId] = useState<string | null>(null);
  const [fullEditingId, setFullEditingId] = useState<string | null>(null);
  const [editFormData, setEditFormData] = useState<Partial<BlogPost>>({});
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
  const [totalItems, setTotalItems] = useState(0);
  const [totalPages, setTotalPages] = useState(1);

  const fetchPosts = useCallback(async () => {
    try {
      setLoading(true);
      setError(null);
      const response = await getBlogPosts({
        page: currentPage,
        limit: itemsPerPage,
        search: searchQuery || undefined,
        status: filters.status !== "all" ? filters.status : undefined,
        sortBy: sort.column,
        sortOrder: sort.direction,
      });
      setPosts(response.data);
      setTotalItems(response.meta.total);
      setTotalPages(response.meta.totalPages);
    } catch (err) {
      setError(err instanceof Error ? err.message : "Failed to load blog posts");
      toast.error("Failed to load blog posts");
    } finally {
      setLoading(false);
    }
  }, [currentPage, itemsPerPage, searchQuery, filters.status, sort.column, sort.direction]);

  useEffect(() => {
    fetchPosts();
  }, [fetchPosts]);

  // Reset page when filters change
  useEffect(() => {
    setCurrentPage(1);
    setSelectedIds(new Set());
    setSelectAll(false);
  }, [searchQuery, filters.status, sort.column, sort.direction]);

  // Selection handlers
  const toggleSelectAll = () => {
    if (selectAll) {
      setSelectedIds(new Set());
    } else {
      setSelectedIds(new Set(posts.map((p) => p.id)));
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
  const startQuickEdit = (post: BlogPost) => {
    setQuickEditingId(post.id);
    setFullEditingId(null);
    setEditFormData({ title: post.title });
  };

  const saveQuickEdit = async () => {
    if (!quickEditingId || !editFormData.title?.trim()) return;

    try {
      setIsSaving(true);
      await updateBlogPost(quickEditingId, { title: editFormData.title.trim() });
      toast.success("Post updated successfully");
      await fetchPosts();
      setQuickEditingId(null);
    } catch (err) {
      toast.error(err instanceof Error ? err.message : "Failed to update post");
    } finally {
      setIsSaving(false);
    }
  };

  // Full Edit
  const startFullEdit = (post: BlogPost) => {
    setFullEditingId(post.id);
    setQuickEditingId(null);
    setEditFormData({
      title: post.title,
      slug: post.slug,
      excerpt: post.excerpt,
      status: post.status,
      metaTitle: post.metaTitle,
      metaDescription: post.metaDescription,
      keywords: post.keywords,
    });
  };

  const saveFullEdit = async () => {
    if (!fullEditingId || !editFormData.title?.trim()) return;

    try {
      setIsSaving(true);
      await updateBlogPost(fullEditingId, {
        title: editFormData.title.trim(),
        slug: editFormData.slug,
        excerpt: editFormData.excerpt,
        status: editFormData.status,
        metaTitle: editFormData.metaTitle,
        metaDescription: editFormData.metaDescription,
        keywords: editFormData.keywords,
      });
      toast.success("Post updated successfully");
      await fetchPosts();
      setFullEditingId(null);
    } catch (err) {
      toast.error(err instanceof Error ? err.message : "Failed to update post");
    } finally {
      setIsSaving(false);
    }
  };

  // Delete
  const handleDelete = (post: BlogPost) => {
    setConfirmDialog({
      isOpen: true,
      title: "Delete Post",
      description: `Are you sure you want to delete "${post.title}"? This action cannot be undone.`,
      onConfirm: async () => {
        try {
          await deleteBlogPost(post.id);
          toast.success("Post deleted successfully");
          await fetchPosts();
        } catch (err) {
          toast.error(err instanceof Error ? err.message : "Failed to delete post");
        }
      },
    });
  };

  // Bulk Delete
  const handleBulkDelete = () => {
    if (selectedIds.size === 0) return;

    setConfirmDialog({
      isOpen: true,
      title: "Delete Posts",
      description: `Are you sure you want to delete ${selectedIds.size} post${selectedIds.size === 1 ? "" : "s"}? This action cannot be undone.`,
      onConfirm: async () => {
        try {
          await bulkDeleteBlogPosts(Array.from(selectedIds));
          toast.success(`${selectedIds.size} posts deleted`);
          setSelectedIds(new Set());
          setSelectAll(false);
          await fetchPosts();
        } catch (err) {
          toast.error(err instanceof Error ? err.message : "Failed to delete posts");
        }
      },
    });
  };

  // Bulk Update Status
  const handleBulkUpdateStatus = (status: string) => {
    if (selectedIds.size === 0) return;

    setConfirmDialog({
      isOpen: true,
      title: status === "PUBLISHED" ? "Publish Posts" : "Update Status",
      description: `Are you sure you want to ${status === "PUBLISHED" ? "publish" : "update status for"} ${selectedIds.size} post${selectedIds.size === 1 ? "" : "s"}?`,
      onConfirm: async () => {
        try {
          const updateData: any = { status };
          if (status === "PUBLISHED") {
            updateData.publishedAt = new Date().toISOString();
          }
          await bulkUpdateBlogPosts(Array.from(selectedIds), updateData);
          toast.success(`${selectedIds.size} posts updated`);
          setSelectedIds(new Set());
          setSelectAll(false);
          await fetchPosts();
        } catch (err) {
          toast.error(err instanceof Error ? err.message : "Failed to update posts");
        }
      },
    });
  };

  // Export
  const handleExport = () => {
    const csv = [
      ["ID", "Title", "Slug", "Excerpt", "Status", "Author", "Views", "Likes", "Shares", "Published At", "Created At"].join(",")
,
      ...posts.map((p) =>
        [
          p.id,
          `"${p.title}"`,
          p.slug,
          `"${p.excerpt || ""}"`,
          p.status,
          p.author ? `${p.author.firstName || ""} ${p.author.lastName || ""}`.trim() || "Anonymous" : "Anonymous",
          p.viewCount,
          p.likeCount,
          p.shareCount,
          p.publishedAt || "",
          new Date(p.createdAt).toISOString(),
        ].join(",")
      ),
    ].join("\n");

    const blob = new Blob([csv], { type: "text/csv" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `blog-posts-${new Date().toISOString().split("T")[0]}.csv`;
    a.click();
    URL.revokeObjectURL(url);

    toast.success("Posts exported successfully");
  };

  const getStatusBadge = (status: string) => {
    const styles: Record<string, string> = {
      PUBLISHED: "bg-green-100 text-green-700 hover:bg-green-100",
      DRAFT: "bg-yellow-100 text-yellow-700 hover:bg-yellow-100",
      PENDING_REVIEW: "bg-blue-100 text-blue-700 hover:bg-blue-100",
      ARCHIVED: "bg-gray-100 text-gray-600 hover:bg-gray-100",
    };
    return (
      <Badge className={styles[status] || styles.DRAFT}>
        {status.replace("_", " ")}
      </Badge>
    );
  };

  const formatDate = (dateString: string | null) => {
    if (!dateString) return "Not published";
    return new Date(dateString).toLocaleDateString("en-US", {
      year: "numeric",
      month: "short",
      day: "numeric",
    });
  };

  const stats = {
    total: totalItems,
    published: posts.filter((p) => p.status === "PUBLISHED").length,
    draft: posts.filter((p) => p.status === "DRAFT").length,
    pending: posts.filter((p) => p.status === "PENDING_REVIEW").length,
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="relative overflow-hidden rounded-xl bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500 px-8 py-8 text-white">
        <div className="relative z-10">
          <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
              <h1 className="text-3xl font-bold flex items-center gap-3">
                <BookOpen className="h-8 w-8" />
                Blog Posts
              </h1>
              <p className="mt-2 text-indigo-100">
                Manage your blog posts and articles
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
              <Button className="bg-white text-indigo-600 hover:bg-indigo-50" asChild>
                <Link href="/admin/blog/new">
                  <Plus className="mr-2 h-4 w-4" />
                  Add Post
                </Link>
              </Button>
            </div>
          </div>
        </div>
        <div className="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10" />
        <div className="absolute -bottom-10 -right-10 h-32 w-32 rounded-full bg-white/5" />
      </div>

      {/* Stats */}
      <div className="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <Card className="border-l-4 border-l-blue-500">
          <CardContent className="pt-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-muted-foreground">Total Posts</p>
                <p className="text-2xl font-bold">{stats.total}</p>
              </div>
              <BookOpen className="h-8 w-8 text-blue-500" />
            </div>
          </CardContent>
        </Card>
        <Card className="border-l-4 border-l-green-500">
          <CardContent className="pt-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-muted-foreground">Published</p>
                <p className="text-2xl font-bold">{stats.published}</p>
              </div>
              <Check className="h-8 w-8 text-green-500" />
            </div>
          </CardContent>
        </Card>
        <Card className="border-l-4 border-l-yellow-500">
          <CardContent className="pt-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-muted-foreground">Drafts</p>
                <p className="text-2xl font-bold">{stats.draft}</p>
              </div>
              <Clock className="h-8 w-8 text-yellow-500" />
            </div>
          </CardContent>
        </Card>
        <Card className="border-l-4 border-l-purple-500">
          <CardContent className="pt-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-muted-foreground">Pending Review</p>
                <p className="text-2xl font-bold">{stats.pending}</p>
              </div>
              <EyeIcon className="h-8 w-8 text-purple-500" />
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Search and Filters */}
      <Card>
        <CardHeader>
          <div className="flex flex-col sm:flex-row gap-4">
            <div className="relative flex-1">
              <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
              <Input
                placeholder="Search posts by title, excerpt..."
                className="pl-10"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
              />
            </div>
            <div className="flex gap-2">
              <Button
                variant="outline"
                size="icon"
                onClick={() => setShowFilters(!showFilters)}
                className={showFilters ? "bg-indigo-50 text-indigo-600" : ""}
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
            <div className="mt-4 pt-4 border-t grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <Label className="text-sm font-medium mb-2 block">Status</Label>
                <div className="flex gap-2 flex-wrap">
                  {[
                    { value: "all", label: "All" },
                    { value: "PUBLISHED", label: "Published" },
                    { value: "DRAFT", label: "Draft" },
                    { value: "PENDING_REVIEW", label: "Pending Review" },
                    { value: "ARCHIVED", label: "Archived" },
                  ].map((option) => (
                    <Button
                      key={option.value}
                      variant={filters.status === option.value ? "default" : "outline"}
                      size="sm"
                      onClick={() => setFilters({ status: option.value as any })}
                      className={
                        filters.status === option.value
                          ? "bg-indigo-600 hover:bg-indigo-700"
                          : ""
                      }
                    >
                      {option.label}
                    </Button>
                  ))}
                </div>
              </div>

              <div>
                <Label className="text-sm font-medium mb-2 block">Sort By</Label>
                <div className="flex gap-2">
                  <select
                    className="h-9 px-3 rounded-md border border-input bg-background text-sm"
                    value={sort.column}
                    onChange={(e) => setSort({ ...sort, column: e.target.value as any })}
                  >
                    <option value="createdAt">Date Created</option>
                    <option value="publishedAt">Date Published</option>
                    <option value="title">Title</option>
                    <option value="viewCount">Views</option>
                    <option value="updatedAt">Last Updated</option>
                  </select>
                  <Button
                    variant="outline"
                    size="icon"
                    className="h-9 w-9"
                    onClick={() =>
                      setSort({ ...sort, direction: sort.direction === "asc" ? "desc" : "asc" })
                    }
                  >
                    <ArrowUpDown className="h-4 w-4" />
                  </Button>
                </div>
              </div>
            </div>
          )}
        </CardHeader>
      </Card>

      {/* Bulk Actions */}
      {selectedIds.size > 0 && (
        <div className="bg-indigo-50 border border-indigo-200 rounded-lg p-4 flex items-center justify-between">
          <div className="flex items-center gap-2">
            <CheckSquare className="h-5 w-5 text-indigo-600" />
            <span className="font-medium text-indigo-900">
              {selectedIds.size} selected
            </span>
          </div>
          <div className="flex gap-2">
            <Button
              variant="outline"
              size="sm"
              onClick={() => handleBulkUpdateStatus("PUBLISHED")}
              className="border-green-200 text-green-600 hover:bg-green-50"
            >
              <Check className="mr-1 h-4 w-4" />
              Publish
            </Button>
            <Button
              variant="outline"
              size="sm"
              onClick={() => handleBulkUpdateStatus("DRAFT")}
              className="border-gray-200 hover:bg-gray-50"
            >
              <Clock className="mr-1 h-4 w-4" />
              Draft
            </Button>
            <Button
              variant="outline"
              size="sm"
              onClick={() => handleBulkUpdateStatus("ARCHIVED")}
              className="border-gray-200 hover:bg-gray-50"
            >
              Archive
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

      {/* Posts Display */}
      <Card>
        <CardContent className="p-0">
          {viewMode === "table" ? (
            <Table>
              <TableHeader>
                <TableRow className="bg-gray-50 hover:bg-gray-50">
                  <TableHead className="w-12">
                    <Checkbox checked={selectAll} onCheckedChange={toggleSelectAll} />
                  </TableHead>
                  <TableHead
                    className="cursor-pointer"
                    onClick={() =>
                      setSort({
                        column: "title",
                        direction: sort.column === "title" && sort.direction === "asc" ? "desc" : "asc",
                      })
                    }
                  >
                    <div className="flex items-center gap-1">
                      Post
                      <ArrowUpDown className="h-3 w-3" />
                    </div>
                  </TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead className="text-center">Author</TableHead>
                  <TableHead className="text-center">Views</TableHead>
                  <TableHead>Published</TableHead>
                  <TableHead className="text-right">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {loading ? (
                  <SkeletonTableRows rows={5} columns={7} />
                ) : error ? (
                  <TableRow>
                    <TableCell colSpan={7} className="h-32 text-center">
                      <div className="space-y-2">
                        <p className="text-red-500">{error}</p>
                        <Button variant="outline" onClick={fetchPosts}>
                          Retry
                        </Button>
                      </div>
                    </TableCell>
                  </TableRow>
                ) : posts.length === 0 ? (
                  <TableRow>
                    <TableCell colSpan={7} className="h-32 text-center text-muted-foreground">
                      <div className="flex flex-col items-center gap-2">
                        <BookOpen className="h-8 w-8 text-gray-400" />
                        <p>No posts found</p>
                        {searchQuery && (
                          <Button variant="outline" size="sm" onClick={() => setSearchQuery("")}>
                            Clear Search
                          </Button>
                        )}
                      </div>
                    </TableCell>
                  </TableRow>
                ) : (
                  posts.map((post) => (
                    <React.Fragment key={`group-${post.id}`}>
                      <TableRow
                        className={`${selectedIds.has(post.id) ? "bg-indigo-50/50" : ""} ${
                          fullEditingId === post.id ? "bg-blue-50/30" : ""
                        }`}
                      >
                        <TableCell>
                          <Checkbox
                            checked={selectedIds.has(post.id)}
                            onCheckedChange={() => toggleSelect(post.id)}
                          />
                        </TableCell>
                        <TableCell>
                          {quickEditingId === post.id ? (
                            <div className="flex items-center gap-2">
                              <Input
                                value={editFormData.title || ""}
                                onChange={(e) =>
                                  setEditFormData((prev) => ({ ...prev, title: e.target.value }))
                                }
                                className="h-8 w-64"
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
                            <div className="flex items-start gap-3">
                              {post.featuredImage && (
                                <img
                                  src={post.featuredImage.thumbnailUrl || post.featuredImage.originalUrl}
                                  alt=""
                                  className="w-10 h-10 rounded object-cover"
                                />
                              )}
                              <div>
                                <p className="font-medium">{post.title}</p>
                                <p className="text-xs text-muted-foreground line-clamp-1">
                                  {post.excerpt || "No excerpt"}
                                </p>
                              </div>
                            </div>
                          )}
                        </TableCell>
                        <TableCell>{getStatusBadge(post.status)}</TableCell>
                        <TableCell className="text-center">
                          <div className="flex items-center justify-center gap-1">
                            <User className="h-3 w-3 text-muted-foreground" />
                            <span className="text-sm">
                              {post.author
                                ? `${post.author.firstName || ""} ${post.author.lastName || ""}`.trim() || "Anonymous"
                                : "Anonymous"}
                            </span>
                          </div>
                        </TableCell>
                        <TableCell className="text-center">
                          <div className="flex items-center justify-center gap-1">
                            <EyeIcon className="h-3 w-3 text-muted-foreground" />
                            <span className="text-sm">{post.viewCount.toLocaleString()}</span>
                          </div>
                        </TableCell>
                        <TableCell className="text-sm text-muted-foreground">
                          <div className="flex items-center gap-1">
                            <Calendar className="h-3 w-3" />
                            {formatDate(post.publishedAt)}
                          </div>
                        </TableCell>
                        <TableCell className="text-right">
                          <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                              <Button variant="ghost" size="sm">
                                <MoreHorizontal className="h-4 w-4" />
                              </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                              <DropdownMenuItem
                                onClick={() => startQuickEdit(post)}
                                disabled={quickEditingId === post.id}
                              >
                                <Edit className="mr-2 h-4 w-4" />
                                Quick Edit
                              </DropdownMenuItem>
                              <DropdownMenuItem onClick={() => startFullEdit(post)}>
                                <Edit className="mr-2 h-4 w-4" />
                                Full Edit
                              </DropdownMenuItem>
                              <DropdownMenuItem asChild>
                                <Link href={`/blog/${post.slug}`} target="_blank">
                                  <Eye className="mr-2 h-4 w-4" />
                                  View
                                </Link>
                              </DropdownMenuItem>
                              <DropdownMenuItem asChild>
                                <Link href={`/admin/blog/${post.id}`}>
                                  <Edit className="mr-2 h-4 w-4" />
                                  Edit Full Post
                                </Link>
                              </DropdownMenuItem>
                              <DropdownMenuSeparator />
                              <DropdownMenuItem
                                onClick={() => handleDelete(post)}
                                className="text-red-600"
                              >
                                <Trash2 className="mr-2 h-4 w-4" />
                                Delete
                              </DropdownMenuItem>
                            </DropdownMenuContent>
                          </DropdownMenu>
                        </TableCell>
                      </TableRow>

                      {/* Full Edit Panel */}
                      {fullEditingId === post.id && (
                        <TableRow className="bg-blue-50/30 border-l-4 border-l-blue-400">
                          <TableCell colSpan={7} className="p-6">
                            <div className="space-y-4">
                              <div className="flex items-center justify-between">
                                <h4 className="font-semibold text-blue-900 flex items-center gap-2">
                                  <Edit className="h-4 w-4" />
                                  Edit Post: {post.title}
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
                                  <Label>Title</Label>
                                  <Input
                                    value={editFormData.title || ""}
                                    onChange={(e) =>
                                      setEditFormData((prev) => ({ ...prev, title: e.target.value }))
                                    }
                                  />
                                </div>
                                <div className="space-y-2">
                                  <Label>Slug</Label>
                                  <Input
                                    value={editFormData.slug || ""}
                                    onChange={(e) =>
                                      setEditFormData((prev) => ({ ...prev, slug: e.target.value }))
                                    }
                                  />
                                </div>
                              </div>

                              <div className="space-y-2">
                                <Label>Excerpt</Label>
                                <Input
                                  value={editFormData.excerpt || ""}
                                  onChange={(e) =>
                                    setEditFormData((prev) => ({ ...prev, excerpt: e.target.value }))
                                  }
                                  placeholder="Brief summary of the post"
                                />
                              </div>

                              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="space-y-2">
                                  <Label>Status</Label>
                                  <select
                                    className="w-full h-10 px-3 rounded-md border border-input bg-background text-sm"
                                    value={editFormData.status || ""}
                                    onChange={(e) =>
                                      setEditFormData((prev) => ({ ...prev, status: e.target.value }))
                                    }
                                  >
                                    <option value="DRAFT">Draft</option>
                                    <option value="PENDING_REVIEW">Pending Review</option>
                                    <option value="PUBLISHED">Published</option>
                                    <option value="ARCHIVED">Archived</option>
                                  </select>
                                </div>
                                <div className="space-y-2">
                                  <Label>Meta Title</Label>
                                  <Input
                                    value={editFormData.metaTitle || ""}
                                    onChange={(e) =>
                                      setEditFormData((prev) => ({
                                        ...prev,
                                        metaTitle: e.target.value,
                                      }))
                                    }
                                    placeholder="SEO title"
                                  />
                                </div>
                              </div>

                              <div className="space-y-2">
                                <Label>Meta Description</Label>
                                <Input
                                  value={editFormData.metaDescription || ""}
                                  onChange={(e) =>
                                    setEditFormData((prev) => ({
                                      ...prev,
                                      metaDescription: e.target.value,
                                    }))
                                  }
                                  placeholder="SEO description"
                                />
                              </div>

                              <div className="space-y-2">
                                <Label>Keywords</Label>
                                <Input
                                  value={editFormData.keywords || ""}
                                  onChange={(e) =>
                                    setEditFormData((prev) => ({ ...prev, keywords: e.target.value }))
                                  }
                                  placeholder="comma, separated, keywords"
                                />
                              </div>

                              <div className="flex justify-end gap-2 pt-4">
                                <Button variant="outline" onClick={() => setFullEditingId(null)}>
                                  Cancel
                                </Button>
                                <Button
                                  onClick={saveFullEdit}
                                  disabled={isSaving}
                                  className="bg-blue-600 hover:bg-blue-700"
                                >
                                  {isSaving ? (
                                    <>
                                      <Loader2 className="mr-2 h-4 w-4 animate-spin" />
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
                    <div key={i} className="h-64 bg-gray-100 animate-pulse rounded-lg" />
                  ))}
                </div>
              ) : posts.length === 0 ? (
                <div className="text-center py-12">
                  <BookOpen className="h-12 w-12 text-gray-400 mx-auto mb-4" />
                  <p className="text-muted-foreground">No posts found</p>
                </div>
              ) : (
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                  {posts.map((post) => (
                    <Card
                      key={post.id}
                      className={`overflow-hidden ${
                        selectedIds.has(post.id) ? "ring-2 ring-indigo-500" : ""
                      }`}
                    >
                      <div className="aspect-video bg-muted relative">
                        {post.featuredImage ? (
                          <img
                            src={post.featuredImage.thumbnailUrl || post.featuredImage.originalUrl}
                            alt={post.title}
                            className="w-full h-full object-cover"
                          />
                        ) : (
                          <div className="flex h-full items-center justify-center">
                            <BookOpen className="h-12 w-12 text-muted-foreground" />
                          </div>
                        )}
                        <div className="absolute top-2 left-2">
                          <Checkbox
                            checked={selectedIds.has(post.id)}
                            onCheckedChange={() => toggleSelect(post.id)}
                          />
                        </div>
                        <div className="absolute top-2 right-2">
                          {getStatusBadge(post.status)}
                        </div>
                      </div>
                      <CardContent className="p-4">
                        <h4 className="font-medium line-clamp-2 mb-2">{post.title}</h4>
                        <p className="text-sm text-muted-foreground line-clamp-2 mb-3">
                          {post.excerpt || "No excerpt"}
                        </p>
                        <div className="flex items-center justify-between text-sm text-muted-foreground">
                          <div className="flex items-center gap-3">
                            <span className="flex items-center gap-1">
                              <EyeIcon className="h-3 w-3" />
                              {post.viewCount}
                            </span>
                            <span className="flex items-center gap-1">
                              <Heart className="h-3 w-3" />
                              {post.likeCount}
                            </span>
                          </div>
                          <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                              <Button variant="ghost" size="sm" className="h-8 w-8 p-0">
                                <MoreVertical className="h-4 w-4" />
                              </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                              <DropdownMenuItem onClick={() => startFullEdit(post)}>
                                <Edit className="mr-2 h-4 w-4" />
                                Edit
                              </DropdownMenuItem>
                              <DropdownMenuItem asChild>
                                <Link href={`/blog/${post.slug}`} target="_blank">
                                  <Eye className="mr-2 h-4 w-4" />
                                  View
                                </Link>
                              </DropdownMenuItem>
                              <DropdownMenuSeparator />
                              <DropdownMenuItem
                                onClick={() => handleDelete(post)}
                                className="text-red-600"
                              >
                                <Trash2 className="mr-2 h-4 w-4" />
                                Delete
                              </DropdownMenuItem>
                            </DropdownMenuContent>
                          </DropdownMenu>
                        </div>
                      </CardContent>
                    </Card>
                  ))}
                </div>
              )}
            </div>
          )}
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
