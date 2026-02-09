-- Initial database setup script for PostgreSQL
-- This script runs when the PostgreSQL container is first initialized

-- Create extensions if they don't exist
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";
CREATE EXTENSION IF NOT EXISTS "pg_trgm";  -- For full-text search

-- Note: Application users and grants are managed by Prisma migrations
-- This file ensures required extensions are available

-- Set timezone
SET timezone = 'UTC';
