# Phase 2: Backend Core (Product Management)

**Objective:** Develop the core business logic for managing affiliate products, categories, and tags. This phase ensures data integrity and ease of management.

## 1. Database Schema (Prisma)
- [ ] **Product Model:**
    - `id`, `slug` (unique), `title`, `description` (Rich Text).
    - `price`, `currency`, `bogo_offer` (Buy One Get One logic if needed), `discount_code`.
    - `affiliate_link`, `tracking_id` (internal ref).
    - `status` (DRAFT, PUBLISHED, ARCHIVED).
    - `created_at`, `updated_at`.
- [ ] **Media Model:**
    - Handling multiple images per product.
    - `url`, `alt_text`, `type` (THUMBNAIL, GALLERY).
- [ ] **Taxonomy Models:**
    - `Category` (Hierarchical possible).
    - `Tag` (Many-to-Many with Products).

## 2. NestJS Modules
- [ ] **Auth Module:**
    - JWT Strategy for Admin access.
    - Guard protection for all Write operations.
- [ ] **Products Module:**
    - `POST /products`: Create product (Manual Upload).
    - `PATCH /products/:id`: Update details.
    - `GET /products`: Public listing with pagination/filtering.
    - `GET /products/:slug`: Single product view.
- [ ] **Upload Module:**
    - Local file storage (for MV) or S3-compatible integration.
    - Image optimization (sharp) upon upload (resize/compress).

## 3. Manual Upload Workflow
- [ ] **Admin Dashboard (Backend support):**
    - Endpoints specifically designed to support a rich-text editor and drag-and-drop image upload.
    - Validation pipes to ensure data quality (e.g. valid URL format for affiliate links).

## 4. Verification
- [ ] **Unit Tests:** Service methods for creating/updating products.
- [ ] **Integration Tests:** API endpoints return correct HTTP codes and data structures.
- [ ] **Manual Test:** Use Postman/Insomnia to upload a product with image and verify it appears in the DB.
