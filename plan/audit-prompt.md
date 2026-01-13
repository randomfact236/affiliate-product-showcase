# Repository Audit & Gap-Filling Prompt — Affiliate Product Showcase (Jan 2026)

## Role & Quality Bar
You are an expert WordPress plugin developer with deep knowledge of:
- Modern plugin architecture (PSR-4, strict typing, dependency boundaries)
- Docker dev environments (production parity, secure defaults, healthchecks)
- Vite + React + Tailwind stacks (TypeScript strict mode, manifest-based asset loading)
- Security hardening, privacy/offline-first, accessibility (WCAG), marketplace readiness (WordPress.org + CodeCanyon)

**Quality expectation:** enterprise-grade 10/10 — no shortcuts.

## Target Platform & Constraints
- PHP: **8.3**
- WordPress: **6.7+**
- Frontend build: **Vite 5+**, **React 18**, **Tailwind 3.4+**
- Runtime policy: **Zero external runtime dependencies** and **zero external HTTP requests**.
  - Build-time dependencies (npm/composer tooling) are allowed.
  - Runtime must not use CDNs/fonts/icons or call external APIs.

## Coding Standards & Static Analysis
All code must be:
- Fully typed (PHP strict types + full type hints)
- Fully documented (PHPDoc where needed; public APIs documented)
- Secure-by-default (capability checks, nonces for state changes, sanitization/validation on input, escaping on output)

Quality tools targets:
- WPCS + VIP-Go rules where applicable
- PHPCompatibility (PHP 8.3)
- PHPStan **level 8**
- Psalm **level 1**

**Baselines:** Do not add baselines unless explicitly allowed by the user. Prefer fixing root causes.

## Repository Location
Audit the current repository content visible in the workspace.

The detailed reference plan must be taken from: `plan/plan_sync.md`.
- If `plan/plan_sync.md` is missing or incomplete, stop and ask for it.

---

# PHASE A — AUDIT ONLY (NO CHANGES)

## Hard Rules (Audit Phase)
- **Do not modify any files.**
- **Do not install dependencies.**
- You may run **read-only** commands (e.g., listing files, reading configs, `git status`) only if needed for evidence.

## Required Audit Workflow
1. Thoroughly scan and understand the current physical file/folder structure of the repository.
2. Compare **every checklist item** from `plan/plan_sync.md` against actual files, code, and configuration.
3. Classify each plan item (including all sub-items) into exactly one of:
   - ✅ Fully implemented & meets quality bar
   - ⚠️ Partially implemented / low quality / outdated / insecure / incomplete
   - ❌ Not implemented
   - 🔍 Need more information / ambiguous / file not found but conceptually present

## Counting Rule (So totals are consistent)
- Count **each numbered plan item and numbered sub-item** as one “item checked”.
- If the plan uses bullet sub-items without numbers, group them under the nearest numbered item and treat them as sub-requirements in the evidence.

## Required Output Format (Strict)

# Repository Audit & Gap-Filling Report — Affiliate Product Showcase
## Executive Summary
- Total items checked: ...
- Fully implemented: ... (..%)
- Partially: ... (..%)
- Missing: ... (..%)
- Needs more info: ... (..%)
- Overall quality grade: .../100

## PART 1: PROJECT FOLDER & INFRASTRUCTURE DETAILS
### 1.1 Docker Environment
#### 1.1.1 WordPress container
Status: ✅/⚠️/❌/🔍
Evidence: (file paths + key configuration facts)
Plan reference: (exact plan section number)
Implementation performed (if any): no

(continue for all Docker subitems...)

### 1.2 Folder structure & root files (1.2.1–1.2.28)
(continue item-by-item...)

### 1.3 Git & branch strategy

### 1.4 Composer configuration

### 1.5 NPM configuration

### 1.6 Configuration files (.gitignore, phpcs, phpunit, editorconfig, dockerignore, etc.)

## PART 2: PLUGIN FOLDER & CODE DETAILS
- Main plugin file & root files
- `src/` directory: every class/trait/interface/abstract reviewed
- `frontend/` directory: TS/TSX architecture, build, linting, Tailwind
- `blocks/` directory: each block reviewed
- `assets/dist/`: manifest presence/correctness, production assets
- `languages/`, `tests/`, `docs/`

## CRITICAL FINDINGS & QUALITY GRADE
- High priority blockers
- Medium priority issues
- Security / privacy / offline compliance concerns
- Overall completion percentage (rough estimate)
- Enterprise-grade quality score (0–100)

**STOP HERE after the audit and wait for approval to implement.**

---

# PHASE B — IMPLEMENTATION (ONLY AFTER APPROVAL)

## Implementation Rules
- Implement all ❌ and high/medium ⚠️ items.
- Keep diffs minimal and focused; group tightly-coupled config changes when necessary.
- Preserve naming/prefixing conventions:
  - PHP function prefix: `aps_`
  - Constants: `AFFILIATE_PRODUCT_SHOWCASE_...`
  - Text domain: `affiliate-product-showcase`
- Enforce security best practices everywhere (capability checks, nonces, sanitize/escape, REST permission callbacks).
- Ensure Vite → manifest → PHP enqueue pipeline is production-ready and offline.
- Docker must be dev-friendly and production-parity with secure defaults and healthchecks.

## After Implementation
- Summarize all actions taken (files created/updated + what changed).
- Run the most relevant linters/tests available in the repo (only those that exist) and report results.

------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

# PROMPT-2: Audit Prompt Completeness Analysis

## Analysis Summary

This section provides an independent analysis of the audit prompt's completeness and readiness for execution.

## Assessment: ⚠️ PARTIALLY COMPLETE (Missing Critical Dependency)

### What IS Complete in the Audit Prompt

The audit prompt itself is **well-structured and comprehensive** in the following areas:

✅ **Role Definition & Quality Standards**
- Clear expectations for enterprise-grade development (10/10 quality bar)
- Expert role definition with specific technology stack knowledge

✅ **Technical Constraints & Platform Requirements**
- PHP 8.3, WordPress 6.7+, Vite 5+, React 18, Tailwind 3.4+
- Zero external runtime dependencies policy
- Offline-first requirement clearly stated

✅ **Coding Standards & Quality Tools**
- PHPStan level 8, Psalm level 1
- WPCS, VIP-Go, PHPCompatibility rules
- Strict typing and full documentation requirements
- No baselines preference (fix root causes)

✅ **Audit Workflow & Methodology**
- Clear 3-step audit process
- 4-category classification system (✅/⚠️/❌/🔍)
- Specific counting rules for consistency
- Detailed output format template

✅ **Implementation Guidelines**
- Naming conventions (aps_ prefix, constants, text domain)
- Security best practices (capability checks, nonces, sanitization, escaping)
- Docker requirements (dev-friendly, production-parity, healthchecks)
- Vite manifest pipeline requirements

✅ **Project-Specific Knowledge**
- WordPress.org + CodeCanyon marketplace readiness
- Accessibility (WCAG) requirements
- Privacy/offline-first architecture

### What IS Missing (Critical Dependency)

❌ **Reference Plan File Required**
- The audit prompt explicitly states:
  > "The detailed reference plan must be taken from: `plan/plan_sync.md`"
  > "If `plan/plan_sync.md` is missing or incomplete, stop and ask for it."

**Impact:** The audit prompt is a **framework/template** that cannot execute without the actual checklist items from `plan/plan_sync.md`. Without this reference, the auditor has no items to audit against.

### Clarification: Audit Scope

The audit prompt is designed to audit the **COMPLETE plan file** (`plan/plan_sync.md`), which includes:

- **Step 1 — Setup** (topics 1.1 through 1.12 covering Docker, folder structure, Git, Composer, NPM, config files, etc.)
- **Step 2 — Content Types & Taxonomies**
- **Step 3 — Admin UI & Meta**
- **Step 4 — Submission Flow & Security**
- **Step 5 — Frontend Components**
- **Step 6 — Shortcodes, Filters & Sorting**
- **Step 7 — Link Tracking & Redirects**
- **Step 8 — Settings & Styling Controls**
- **Step 9 — Testing & Standards**
- **Step 10 — Docs, Accessibility & QA**
- **Step 11 — CI/CD & Packaging**
- **Step 12 — Marketing & Launch**

The prompt explicitly states:
> "Compare **every checklist item** from `plan/plan_sync.md` against actual files, code, and configuration."

This means the auditor must check **ALL steps and all sub-items** (1.1.1 through 12.10.20), not just the first 12 topics (1.1-1.12) of Step 1.

### Structural Completeness Assessment

| Component | Status | Notes |
|-----------|--------|-------|
| Role & Quality Bar | ✅ Complete | Well-defined expectations |
| Platform Constraints | ✅ Complete | All versions and policies specified |
| Coding Standards | ✅ Complete | Tool levels and baselines policy clear |
| Audit Workflow | ✅ Complete | Steps, classification, counting rules detailed |
| Output Format | ✅ Complete | Comprehensive template provided |
| Implementation Rules | ✅ Complete | Naming, security, Docker, Vite all covered |
| Reference Plan | ❌ Missing | Depends on `plan/plan_sync.md` |`

### Conclusion

The audit prompt is **90% complete as a framework** but **100% incomplete for execution** without the reference plan file.

**Recommendation:** Before using this audit prompt:
1. Verify `plan/plan_sync.md` exists and contains complete checklist items
2. Review plan_sync.md to ensure it aligns with the audit prompt's quality standards
3. Consider adding a validation step in the audit workflow to check plan_sync.md existence

### Additional Findings

**Strengths:**
- Clear separation between Phase A (audit) and Phase B (implementation)
- Explicit prohibition of file modifications during audit phase
- Comprehensive quality expectations covering security, privacy, accessibility
- Well-defined classification system with emoji indicators

**Potential Improvements (Optional):**
- Could add pre-audit checklist to verify reference plan exists
- Could include examples of what constitutes "partial" vs "full" implementation
- Could specify timeout limits for audit phase
- Could add section on handling ambiguous items

**Overall Assessment:**
The audit prompt demonstrates excellent planning and thoroughness. The missing `plan/plan_sync.md` is an intentional external dependency, not an oversight. The prompt is ready to use **once the reference plan is available**.

------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

# PROMPT-3: Audit Plan Scope Analysis for Topics 1.1 to 1.12

## Analysis Summary

This section provides a detailed analysis of the audit plan's scope specifically for **Step 1 — Setup** covering topics **1.1 through 1.12** and their subtopics.

## Current Audit Scope Assessment

### **CRITICAL FINDING: Audit Plan is NOT Limited to Topics 1.1-1.12**

**Important Clarification:** The audit prompt is designed to audit the **ENTIRE plan file** (`plan/plan_sync.md`), which includes:

- **Step 1 — Setup** (topics 1.1 through 1.12) ✅ **Your focus area**
- **Step 2 — Content Types & Taxonomies** ❌ **Not in 1.1-1.12**
- **Step 3 — Admin UI & Meta** ❌ **Not in 1.1-1.12**
- **Step 4 — Submission Flow & Security** ❌ **Not in 1.1-1.12**
- **Step 5 — Frontend Components** ❌ **Not in 1.1-1.12**
- **Step 6 — Shortcodes, Filters & Sorting** ❌ **Not in 1.1-1.12**
- **Step 7 — Link Tracking & Redirects** ❌ **Not in 1.1-1.12**
- **Step 8 — Settings & Styling Controls** ❌ **Not in 1.1-1.12**
- **Step 9 — Testing & Standards** ❌ **Not in 1.1-1.12**
- **Step 10 — Docs, Accessibility & QA** ❌ **Not in 1.1-1.12**
- **Step 11 — CI/CD & Packaging** ❌ **Not in 1.1-1.12**
- **Step 12 — Marketing & Launch** ❌ **Not in 1.1-1.12**

## Detailed Breakdown: Topics 1.1 to 1.12 (Step 1 — Setup)

Based on the audit prompt's structure, here's what **WILL BE AUDITED** when focusing on topics 1.1-1.12:

### **1.1 Docker Environment** (Multiple subtopics expected)
- 1.1.1 WordPress container
- 1.1.2 Database container
- 1.1.3 Web server (nginx/Apache)
- 1.1.4 Healthchecks
- 1.1.5 Volume mappings
- 1.1.6 Environment variables
- 1.1.7 Network configuration
- 1.1.8 Security defaults

### **1.2 Folder Structure & Root Files** (28 items: 1.2.1–1.2.28)
- Expected items: index.php, wp-config.php, README.md, CHANGELOG.md, LICENSE, etc.
- Configuration files: .gitignore, .editorconfig, .eslintrc.json, .prettierrc, etc.
- Build files: package.json, vite.config.js, composer.json, etc.
- Documentation files
- Script files

### **1.3 Git & Branch Strategy**
- 1.3.1 Branch naming conventions
- 1.3.2 Git workflow
- 1.3.3 Commit message standards
- 1.3.4 .gitattributes configuration

### **1.4 Composer Configuration**
- 1.4.1 composer.json structure
- 1.4.2 PHP version requirements
- 1.4.3 Autoload configuration (PSR-4)
- 1.4.4 Required packages
- 1.4.5 Scripts configuration

### **1.5 NPM Configuration**
- 1.5.1 package.json structure
- 1.5.2 Scripts (dev, build, lint, etc.)
- 1.5.3 Dependencies (React, Vite, Tailwind)
- 1.5.4 Dev dependencies (TypeScript, ESLint, Prettier)

### **1.6 Configuration Files**
- 1.6.1 .gitignore
- 1.6.2 phpcs.xml.dist
- 1.6.3 phpstan.neon
- 1.6.4 psalm.xml
- 1.6.5 phpunit.xml.dist
- 1.6.6 .editorconfig
- 1.6.7 .eslintrc.json
- 1.6.8 .prettierrc
- 1.6.9 stylelint.config.js
- 1.6.10 .dockerignore
- 1.6.11 Other config files

### **1.7 Plugin Main File**
- 1.7.1 Main plugin file structure
- 1.7.2 Plugin header
- 1.7.3 Constants definition
- 1.7.4 Basic initialization

### **1.8 src/ Directory Structure**
- 1.8.1 Namespace configuration
- 1.8.2 Class structure
- 1.8.3 Type declarations
- 1.8.4 Documentation standards

### **1.9 frontend/ Directory Structure**
- 1.9.1 TypeScript configuration
- 1.9.2 React setup
- 1.9.3 Tailwind configuration
- 1.9.4 Build configuration (Vite)
- 1.9.5 Linting setup

### **1.10 blocks/ Directory Structure**
- 1.10.1 Block registration
- 1.10.2 Block structure
- 1.10.3 Build configuration

### **1.11 assets/dist/ Directory**
- 1.11.1 Manifest presence
- 1.11.2 Production assets
- 1.11.3 Asset loading strategy

### **1.12 Additional Setup Items**
- 1.12.1 languages/ directory
- 1.12.2 tests/ directory
- 1.12.3 docs/ directory
- 1.12.4 Other supporting directories

## Key Observations

### **Scope Mismatch Risk**
⚠️ **WARNING:** If you only want to audit topics 1.1-1.12, you **MUST** modify the audit prompt to:
1. Change the reference from `plan/plan_sync.md` to a **subset file** containing only Step 1
2. Update the output format to only show PART 1 (Project Folder & Infrastructure)
3. Remove references to Steps 2-12

### **Current Prompt Design**
The existing audit prompt is designed for **comprehensive repository auditing** across all 12 steps. It will:
- Scan the entire `plan/plan_sync.md` file
- Check ALL items from 1.1.1 through 12.10.20
- Generate a complete audit report covering all phases

### **Alternative: Focused Audit**
If you specifically want to audit **only topics 1.1-1.12**, you would need:

**Modified Prompt Requirements:**
```
Reference Plan: `plan/step-1-setup.md` (subset of plan_sync.md)
Audit Scope: Only Step 1 items (1.1 through 1.12)
Output Format: PART 1 only (Project Folder & Infrastructure)
Skip: PART 2 (Plugin Code Details) and all Steps 2-12
```

## Recommendations

### **Option A: Full Repository Audit (Current Prompt)**
✅ **Use existing prompt as-is**
- Audits complete plan file
- Comprehensive coverage
- Best for complete quality assessment

### **Option B: Focused Step 1 Audit (Modified Prompt)**
⚠️ **Requires prompt modification**
- Create subset file: `plan/step-1-setup.md`
- Modify audit prompt to reference subset
- Focus output on infrastructure only
- Faster, more targeted audit

### **Option C: Phased Audit Approach**
🔄 **Multiple audit cycles**
- Cycle 1: Topics 1.1-1.12 (Step 1)
- Cycle 2: Topics 2.1-2.x (Step 2)
- Continue through all 12 steps
- Build comprehensive report incrementally

## Conclusion

**The audit prompt is NOT designed for 1.1-1.12 only** — it's designed for complete repository auditing. To achieve your specific goal:

1. **Confirm your intent**: Do you want a complete audit or just Step 1?
2. **If Step 1 only**: I can help modify the prompt or create a subset plan file
3. **If complete audit**: Use the existing prompt with `plan/plan_sync.md`

**Next Steps:**
- Please clarify if you want the full audit or a focused Step 1 audit
- If focused, I can create a modified prompt or help extract Step 1 items
- If full audit, the existing prompt is ready to use (once `plan/plan_sync.md` is available)


------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

# PROMPT-4: Audit Plan Scope Analysis for Topics 1.1 to 1.12


You are an elite WordPress plugin & modern development environment auditor.
Your mission is to perform a very strict, enterprise-grade audit focused exclusively on the initial setup & infrastructure phase (plan items 1.1 through 1.12).

Current date: January 2026
Target stack: PHP 8.3+, WordPress 6.7+, Vite 5+, modern best practices

────────────────────────────────────────────────────────────
                    AUDIT SCOPE – ONLY THESE ITEMS
────────────────────────────────────────────────────────────

1.1  Docker Environment & Dev Containers
1.2  Project Folder Structure (1.2.1 through 1.2.28)
1.3  Git & Branching Strategy
1.4  Composer Configuration & Dependencies
1.5  NPM / package.json / Vite Configuration
1.6  Important Configuration Files (.env*, wp-config*, .gitignore, etc.)
1.7  Plugin Main File Header & Structure
1.8  `src/` directory structure & organization (PHP)
1.9  `frontend/` directory structure & conventions (TS/React/Tailwind)
1.10 `blocks/` directory (block.json, block PHP/JS, build)
1.11 `assets/dist/` – build output correctness & .gitignore
1.12 Additional Setup Files & Scripts (lint, test, build scripts, CI helpers)

You MUST NOT audit anything beyond 1.12 (no feature logic, no 2.x–12.x items).

Grouping note:
- Project root & environment: items **1.1 – 1.6** (Docker, repo layout, Git, Composer, NPM, global config files).
- Plugin files & code: items **1.7 – 1.12** (main plugin file, `src/`, `frontend/`, `blocks/`, `assets/dist/`, scripts).

────────────────────────────────────────────────────────────
                     AUDIT RULES – STRICT MODE
────────────────────────────────────────────────────────────

Classification (use exactly these emojis & meanings):

✅ PERFECT / industry best practice / no improvement needed
⚠️  Acceptable but meaningful improvement possible/recommended
❌  Wrong / risky / outdated / non-standard / security concern
🔍  Cannot determine / missing file / need more information

Count at the end of each major section (1.1, 1.2, etc.):
- Total items audited
- ✅ / ⚠️ / ❌ / 🔍 breakdown

Quality bar: WordPress VIP / enterprise plugin / future-proof 2026 standards

Technical constraints to enforce:
• PHP ≥ 8.3
• WordPress ≥ 6.7
• Vite 5.x (or latest stable in Jan 2026)
• Composer 2.7+
• Node.js 20+ / npm 10+
• Strongly typed PHP where reasonable
• No abandoned/vulnerable dependencies

Reference plan file: `plan/plan_sync.md` (preferred) — auditor MUST match numbered sub-items to lines in this file.
If you prefer a focused subset, create `plan/step-1-setup.md` and reference it instead.

Phase A (Audit Only):
- Do NOT modify files.
- Do NOT install dependencies.
- Stop and wait for explicit approval before making changes (Phase B).

Evidence format (required for each checked item):
- File path (relative to repo) + line range (if applicable)
- 1–2 line quoted snippet or key config entry
- Short verdict and recommendation (1–2 sentences)

Output format – very clean & scannable:

# Initial Setup Audit (1.1 – 1.12) – [Repository Name]

## Summary Dashboard
✅ Perfect: XX
⚠️ Needs improvement: XX
❌ Problems: XX
🔍 Cannot evaluate: XX
Coverage: XX / total checked items

## Detailed Findings

### 1.1 Docker Environment
Status: ✅ / ⚠️ / ❌ / 🔍
Evidence: [file path](plan/plan_sync.md#Lxxx) — brief snippet / key facts
Recommendation: ...

(... continue for each major point 1.1 through 1.12 ...)

## Final Statistics (1.1–1.12 only)
Total checked items: ___
✅ ____   ⚠️ ____   ❌ ____   🔍 ____

Overall Setup Quality Grade: [A+/A/B/C/D/F]
(one sentence harsh but fair summary)

Ready for next phase? YES / CONDITIONAL / NO
(if CONDITIONAL or NO → list the must-fix ❌ items first)

## Findings / Improvements (concise)
- Add explicit reference to `plan/plan_sync.md` so each numbered item is traceable.
- Enforce Phase A gate: audit-only, no changes, explicit approval required for Phase B.
- Require a standard evidence format: path + lines + snippet + verdict.
- This prompt (PROMPT-4) is the best-fit runner for a focused 1.1–1.12 audit; use it as the audit driver.

--------------------------------------------------------------------