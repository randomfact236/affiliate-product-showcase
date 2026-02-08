# Phase 5: Optimization, Security & Deployment

**Objective:** Prepare the application for the real world. Secure the infrastructure and ensure it runs reliably 24/7.

## 1. Security Hardening
- [ ] **Rate Limiting:** Implement `ThrottlerModule` in NestJS to prevent API abuse.
- [ ] **Helmet:** Secure HTTP headers.
- [ ] **CORS:** Strict origin policy for the API (only allow Frontend domain).
- [ ] **Input Validation:** Global Verification of all DTOs using `class-validator` to prevent SQL Injection/XSS.

## 2. Performance Tuning
- [ ] **Caching Strategy:**
    - **API Response Cache:** Cache public product data in Redis (TTL 1 hour).
    - **Next.js Cache:** Utilize `unstable_cache` or standard fetch caching for ISR.
- [ ] **Database Indexing:** Ensure all foreign keys and frequently queried fields (slug, category_id) are indexed.

## 3. Deployment
- [ ] **Containerization:**
    - optimized `Dockerfile` for Production (Multi-stage build).
    - `.dockerignore` to keep image small.
- [ ] **Environment Variables:** Secure production secrets management.
- [ ] **Monitoring:** Setup basic uptime monitoring (Health Check endpoint).

## 4. Final Review
- [ ] **Code Audit:** Run full lint and type check.
- [ ] **User Acceptance Testing (UAT):** Full walkthrough of the "Manual Upload -> Frontend Display -> Analytics Tracking" flow.
