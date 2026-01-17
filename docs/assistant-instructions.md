# Assistant Instructions

### Project Context

**Project:** Affiliate Product Showcase WordPress Plugin
**Framework:** Modern WordPress Plugin Boilerplate (Tailwind + Vite + PSR-4 + Security & Cache Ready)
**Quality Standard:** Hybrid Quality Matrix - Enterprise-grade where it matters, practical everywhere
**Architecture:** DI container, event-driven architecture, REST API, Gutenberg blocks
**Tech Stack:** PHP 8.1+, JavaScript/React, Vite, Tailwind CSS
**Code Quality:** PHPUnit, PHPCS (WPCS), PHPStan, Psalm

## Behavior Preferences

### Assistant Files Usage Documentation (MANDATORY)

**IMPORTANT: ALWAYS list ALL FOUR assistant files to prevent missing required standards.**

- **ALWAYS list all four files to ensure no required standards are missed**
- **CLEARLY MARK which files were ACTUALLY APPLIED vs. which were listed for completeness**
- **NEVER skip listing a file - always include all four**
- **This prevents the risk of missing required standards**

---

## When to Use Each File

### assistant-instructions.md
**Use for:**
- ✅ ANY task (always applies)
- ✅ Scanning and analysis tasks
- ✅ Code writing
- ✅ Report generation
- ✅ File operations (read, write, list)

**Contains:** Behavior preferences, scanning tasks, reporting standards, assistant files usage

---

### assistant-quality-standards.md
**Use for:**
- ✅ Writing or reviewing code
- ✅ Code quality assessments
- ✅ Testing and coverage
- ✅ Security reviews
- ✅ Best practices implementation

**DO NOT use for:**
- ❌ Simple file reading (just reporting contents)
- ❌ File listing (ls/dir operations)
- ❌ Directory scanning without quality assessment
- ❌ Informational reports only

**Contains:** Code quality standards, testing requirements, security standards, best practices

---


---

### assistant-performance-optimization.md
**Use for:**
- ✅ Performance analysis
- ✅ Optimization tasks
- ✅ Performance audits
- ✅ Code optimization reviews
- ✅ Asset optimization (bundles, images, CSS)

**DO NOT use for:**
- ❌ Regular code writing (unless performance-related)
- ❌ Structure scanning
- ❌ Documentation tasks

**Contains:** Performance guidelines, optimization standards, checklist

---

## File Selection Examples

### Example 1: Writing Code
**Task:** "Write a compression tool function"
```
**Standards Applied:**
- ✅ assistant-instructions.md (APPLIED - General behavior and reporting)
- ✅ assistant-quality-standards.md (APPLIED - Code quality, testing requirements)
- ✅ assistant-performance-optimization.md (LISTED - Not used: not performance-focused)
```

### Example 2: Just Reading File
**Task:** "Read and show me the contents of package.json"
```
**Standards Applied:**
- ✅ assistant-instructions.md (APPLIED - General behavior and reporting)
- ✅ assistant-quality-standards.md (LISTED - Not used: informational task only)
- ✅ assistant-performance-optimization.md (LISTED - Not used: not performance-focused)
```

### Example 3: Git Operations
**Task:** "Commit and push these changes"
```
**Standards Applied:**
- ✅ assistant-instructions.md (APPLIED - General behavior and reporting, includes git operation rules)
- ✅ assistant-quality-standards.md (LISTED - Not used: git operations only)
- ✅ assistant-performance-optimization.md (LISTED - Not used: not performance-focused)
```

### Example 4: Performance Scan
**Task:** "Analyze performance of the codebase"
```
**Standards Applied:**
- ✅ assistant-instructions.md (APPLIED - General behavior and reporting)
- ✅ assistant-quality-standards.md (APPLIED - Code quality assessment)
- ✅ assistant-performance-optimization.md (APPLIED - Performance standards)
```

### Example 5: Quality Scan
**Task:** "Scan section 12 and report code quality"
```
**Standards Applied:**
- ✅ assistant-instructions.md (APPLIED - General behavior and reporting)
- ✅ assistant-quality-standards.md (APPLIED - Quality assessment scale)
- ✅ assistant-performance-optimization.md (LISTED - Not used: quality scan only)
```

### Example 6: Directory Listing
**Task:** "List all files in the tools directory"
```
**Standards Applied:**
- ✅ assistant-instructions.md (APPLIED - General behavior and reporting)
- ✅ assistant-quality-standards.md (LISTED - Not used: informational task only)
- ✅ assistant-performance-optimization.md (LISTED - Not used: not performance-focused)
```

---

## Required Format

### In Chat Summary (attempt_completion result):
```
**Standards Applied:**
- ✅ assistant-instructions.md (Brief context of what was used)
- ✅ assistant-quality-standards.md (If code quality was assessed)
- ✅ assistant-performance-optimization.md (If performance analysis was done)
```

### In Generated Report Files:
```markdown
## Standards Applied

**Files Used for This Analysis:**
- ✅ assistant-instructions.md (Quality reporting, brutal truth rule, assistant files usage)
- ✅ assistant-quality-standards.md (Quality assessment scale, code quality standards)
- ✅ [Only include files that were actually used]
```

---

## Why This Matters

**Accurate Documentation:**
- Shows which standards actually guided the work
- Creates honest audit trail
- Prevents false claims about standards usage
- Maintains trust in reporting

**Token Efficiency:**
- Saves context window space
- Reduces token usage on irrelevant documentation
- Focuses on actual work performed

**User Clarity:**
- Makes it clear what standards were applied
- Shows relevance of each file to the task
- Avoids confusion about what was done

**NO "Automatically Included":**
- Never say a file was used if it wasn't read
- Never list files just because they exist
- Only list files that ACTUALLY guided the work

---

## Handling Ambiguous Commands

### What If You Just Say "Scan"?

When you say "scan" without specifying the type, I will:

**Step 1: Analyze Context**
- Look at what directory/section is being scanned
- Check if it's related to code quality, performance, or structure
- Review previous conversation context

**Step 2: Make Reasonable Assumption**
- Code directories (src/, blocks/, tools/) → Assume quality scan
- Configuration files (package.json, tsconfig.json) → Assume structure scan
- Performance-related contexts → Assume performance scan
- Simple directory listing requested → Assume structure scan

**Step 3: Clarify Assumption**
I will explicitly state: "Assuming this is a [type] scan. If not, please clarify."

**Step 4: Apply Relevant Files Based on Assumption**

---

### Examples of Ambiguous "Scan" Commands

#### Example 1: "Scan section 12"
**Context:** Section 12 contains tools/ directory with code files

**My Assumption:** Quality scan (checking code quality)

**Files Used:**
```
✅ assistant-instructions.md (Scanning standards, reporting)
✅ assistant-quality-standards.md (Quality assessment, error classification)
❌ assistant-performance-optimization.md (Not explicitly performance-focused)
```

**I will state:** "Assuming this is a code quality scan. If you want a structure scan or performance scan, let me know."

---

#### Example 2: "Scan the tools directory"
**Context:** Directory with tool code files

**My Assumption:** Quality scan (checking code issues, quality)

**Files Used:**
```
✅ assistant-instructions.md (Scanning standards, reporting)
✅ assistant-quality-standards.md (Quality assessment)
❌ assistant-performance-optimization.md (Not explicitly requested)
```

**I will state:** "Performing a code quality scan. If you want structure scan or performance scan, please clarify."

---

#### Example 3: "Scan for performance issues"
**Context:** Explicitly mentions performance

**My Assumption:** Performance scan (checking optimization opportunities)

**Files Used:**
```
✅ assistant-instructions.md (Scanning standards, reporting)
✅ assistant-quality-standards.md (Code quality assessment)
✅ assistant-performance-optimization.md (Performance checklist, optimization standards)
```

**I will state:** "Performing a performance scan as requested."

---

#### Example 4: "Scan the root files"
**Context:** Root configuration files (package.json, composer.json, etc.)

**My Assumption:** Structure scan (checking file presence, naming, organization)

**Files Used:**
```
✅ assistant-instructions.md (Scanning standards, reporting)
❌ assistant-quality-standards.md (Not assessing code quality)
❌ assistant-performance-optimization.md (Not performance-focused)
```

**I will state:** "Performing a structure scan of root files."

---

## Quick Reference Guide

| Task Type | Files to List |
|------------|---------------|
| Simple file read | assistant-instructions.md |
| Directory listing | assistant-instructions.md |
| Writing code | assistant-instructions.md + assistant-quality-standards.md |
| Code quality scan | assistant-instructions.md + assistant-quality-standards.md |
| Performance scan | assistant-instructions.md + assistant-quality-standards.md + assistant-performance-optimization.md |
| Git operations | assistant-instructions.md (includes git rules) |
| Writing tests | assistant-instructions.md + assistant-quality-standards.md |
| Security review | assistant-instructions.md + assistant-quality-standards.md |
| Optimization task | assistant-instructions.md + assistant-quality-standards.md + assistant-performance-optimization.md |
| **"Scan" (ambiguous)** | **assistant-instructions.md + assistant-quality-standards.md (assumes quality scan)** |

---

**CRITICAL RULE:**
- **ONLY list assistant files that WERE ACTUALLY USED**
- **NEVER list files that weren't read or applied**
- **If a standard wasn't applied, don't list the file**
- **Be honest about what standards guided the work**
- **No exceptions - only document files that were actually used**

- **Mandatory Documentation Format:**

**In Generated Report Files:**
```markdown
**Standards Applied:**
- ✅ assistant-instructions.md (Quality reporting, brutal truth rule)
- ✅ assistant-quality-standards.md (Enterprise-grade 10/10 requirements)
- ✅ assistant-performance-optimization.md (Performance standards)
```

**In Chat Summary (attempt_completion result):**
```
After reading and applying standards from:
- ✅ assistant-instructions.md
- ✅ assistant-quality-standards.md  
- ✅ assistant-performance-optimization.md
```

**Why This Matters:**
- Ensures transparency in analysis standards used
- Creates audit trail of which standards were applied
- Allows reviewers to verify correct standards were followed
- Prevents incomplete or partial standard application
- Makes it clear what "enterprise-grade" or other standards mean

**CRITICAL RULE:**
- NEVER just say "followed assistant instructions"
- ALWAYS list EVERY assistant file read and applied
- If assistant-quality-standards.md is referenced, it MUST be read and applied
- "Automatically included" means READ and APPLY, not "referenced but not read"

### Time Estimation Policy (MANDATORY)

**IMPORTANT: NEVER provide time estimates for any tasks.**

- **Do NOT provide estimates for:**
  - Fixing issues or bugs
  - Developing features
  - Implementing functionality
  - Refactoring code
  - Writing tests
  - Any development work

- **What to provide instead of time estimates:**
  - Task complexity assessment (Low/Medium/High/Critical)
  - Dependencies and prerequisites
  - Required steps to complete the task
  - Potential risks or challenges
  - Implementation phases or stages
  - Testing requirements
  - Expected outcomes

- **Why time estimates are not provided:**
  - Development time varies significantly based on:
    - Developer experience and familiarity with codebase
    - Unforeseen technical challenges
    - Dependencies and external factors
    - Testing and debugging time
    - Integration complexity
  - Time estimates are often inaccurate and create unrealistic expectations
  - Focus should be on understanding the work, not predicting duration

- **Correct Example:**
  ```
  **Task Complexity:** High
  
  **Prerequisites:**
  - Update PHP version to 8.1+
  - Install TypeScript compiler
  
  **Implementation Steps:**
  1. Convert all .js files to .ts
  2. Add TypeScript types to functions
  3. Create interfaces for data structures
  4. Run TypeScript compiler and fix errors
  
  **Risks:**
  - May need to update related type definitions
  - Build process may need adjustment
  ```

- **Incorrect Example:**
  ```
  This will take 4-6 hours to complete.
  ```

### Code Writing Permission

**IMPORTANT: Never start writing code unless explicitly told to do so.**

- Always ask: "Do you want me to start writing code?"
- Only begin writing code when you receive:
  - **Explicit "yes"** response to question
  - **Direct "start"** command
- This ensures you maintain control and review requirements before implementation

### Git Operations Permission

**IMPORTANT: Never commit and push changes unless explicitly told to do so.**

- Always ask: "Do you want me to commit and push these changes?"
- Only execute git commit and push when you receive:
  - **Explicit "yes"** response to question
  - **Direct command** to commit and push
- This allows you to review changes before they're committed to repository
- You can make git status checks, staging, and preparation without committing

---

## Git Operation Rules

### Branch Creation Policy

**CRITICAL: The assistant MUST NOT create, checkout, or push any Git branch unless user explicitly instructs it to do so in a direct prompt.**

- **Confirmation Required:** For any action that would create or modify remote branches, assistant will propose exact command and wait for user confirmation.
- **Exceptions:** None. The assistant will always ask before making branch-related changes.
- **Visibility:** The assistant will record this policy in project and in its internal task list for adherence.

### Git Backup Branch Naming

**Mandatory Format:** When creating a manual backup branch (e.g., requested by user), branch name MUST use format:
```
backup-YYYY-MM-DD-HHMM
```

**Examples:**
- `backup-2026-01-16-1430` (January 16, 2026 at 2:30 PM)
- `backup-2026-12-25-0915` (December 25, 2026 at 9:15 AM)

### Branch Creation Request Format

If you want assistant to create a branch in future, please reply with an explicit instruction like:
```
"Create branch X and push to origin"
```

**Examples of explicit instructions:**
- "Create branch feature/new-pricing and push to origin"
- "Create backup-2026-01-16-1430 and push to origin"
- "Checkout to branch develop and pull latest"

**What will NOT trigger branch creation:**
- "Fix this issue" (requires explicit branch creation request)
- "Make these changes" (requires explicit branch creation request)
- "Start working on feature X" (requires explicit branch creation request)

**Summary:**
- Assistant will NEVER automatically create, checkout, or push branches
- User must EXPLICITLY instruct to create a branch
- Assistant will always propose exact git command and wait for confirmation
- Backup branches must follow `backup-YYYY-MM-DD-HHMM` format

### User Request Documentation (MANDATORY FOR ALL TASKS)

**IMPORTANT: ALWAYS include the user's original request at the top of ALL attempt_completion results.**

- **Universal Rule:** This applies to ALL tasks, regardless of type:
  - ✅ Verification and re-verification reports
  - ✅ Gap analysis reports
  - ✅ Resolution summary reports
  - ✅ Code writing tasks
  - ✅ Feature implementation
  - ✅ Bug fixes
  - ✅ File operations
  - ✅ Documentation tasks
  - ✅ ANY task completion

- **Location:** Always include in attempt_completion result (chat summary)

- **Format for User Request Documentation:**
  ```
  **User Request:** "scan section 12 and also compare with related root files"
  ```

- **Purpose:** 
  - Provides clear context about what was requested
  - Documents the user's intent and requirements
  - Creates an audit trail of all user requests
  - Ensures verification addresses the actual request
  - Helps reviewers understand the task scope

- **When to Include:**
  - ALWAYS include in every attempt_completion result
  - No exceptions - this is a universal requirement

- **Where to Include:**
  - Chat Summary (attempt_completion result) - ALWAYS
  - Generated Report Files - For verification/gap/resolution reports

- **Why This Matters:**
  - Provides clear context for the verification work
  - Documents the user's intent and requirements
  - Ensures verification addresses the actual request
  - Creates an audit trail of user requests
  - Helps reviewers understand the verification scope

- **Example Executive Summary:**
  ```
  ## Executive Summary

  **User Request:** "re scan the section 7 and section 8, confirm whether the those secttion are completely, error free or not"

  **Overall Status:** ✅ **BOTH SECTIONS COMPLETE AND ERROR-FREE**

  Both Section 7 and Section 8 have been re-scanned and verified. All critical issues have been addressed, and both sections are now production-ready with no errors or gaps.
  ```

### Default Recommendations

**Always provide proactive recommendations after code changes, file modifications, or feature implementations.**

Include following sections in outputs:

#### 1. Code Quality Suggestions
- Refactoring opportunities
- Performance optimizations
- Security enhancements
- Best practice improvements

#### 2. Next Steps
- Immediate follow-up actions
- Related features to consider
- Testing recommendations

#### 3. Related Features
- Features that complement current implementation
- Edge cases to handle
- Integrations to consider

### Guidelines

- **Keep recommendations concise**: Maximum 2-3 key points per section
- **Make them actionable**: Specific, implementable suggestions
- **Be context-aware**: Tailor suggestions to specific changes made
- **Avoid redundancy**: Don't repeat same suggestions multiple times
- **Prioritize value**: Only include genuinely useful recommendations

### Example Output Structure

```
---

## 💡 Recommendations

**Code Quality:**
- [Specific, actionable suggestion 1]
- [Specific, actionable suggestion 2]

**Next Steps:**
- [Immediate next action 1]
- [Related task 2]

**Consider This:**
- [Related feature or enhancement idea]
```

### When to Skip Recommendations

Skip recommendations only when:
- User explicitly says "no recommendations needed"
- User provides clear instruction to omit recommendations

**IMPORTANT:** For informational tasks (scans, reports, audits, etc.), ALWAYS provide recommendations in chat summary. Do not skip recommendations for informational tasks unless explicitly told to do so.

**Recommendations Location Rule:**
- ALWAYS provide recommendations in chat summary (attempt_completion result)
- Recommendations should also be included in generated report files (when applicable)
- Use standard format: Code Quality, Next Steps, Consider This

**Brutal Truth Rule:**
- NEVER sugarcoat or make reports "nice" to satisfy user
- ALWAYS provide honest, accurate assessments based on facts
- Rate code objectively using the defined quality scale
- Report issues even if they're inconvenient or uncomfortable
- Score should reflect ACTUAL state, not desired state
- No "perfect compliance" if issues are found
- Call out missing code, broken references, and deviations clearly

---

## Scanning Tasks

### Quality Reporting Principle (CRITICAL)

**IMPORTANT: Distinction Between Guides and Reports**

**Guides & Instructions:**
- Target: Enterprise Grade (10/10)
- Purpose: Set the standard to aim for
- Content: Show what perfect looks like

**Reports on Actual Code:**
- Report: EXACT reality truthfully
- Purpose: Assess actual state without sugarcoating
- Content: Show what the code ACTUALLY is (could be 3/10, 5/10, 7/10, etc.)

**Brutal Truth Rule:**
- NEVER sugarcoat reports to make them "nice"
- ALWAYS report the ACTUAL state, even if it's poor
- Be honest about quality: if it's 3/10, say "3/10"
- Rate code objectively using accurate scores
- Call out issues even if they're inconvenient or uncomfortable
- Score should reflect ACTUAL state, not desired state

**Examples:**

✅ **Correct Guide/Instructions:**
```markdown
## Quality Standard: Enterprise Grade (10/10)
Target: All optimizations implemented to meet enterprise standards
```

✅ **Correct Report on Actual Code:**
```markdown
## Quality Assessment: 3/10 (Poor)
Status: NOT meeting enterprise standards
Issues: Multiple critical errors, missing implementations
```

❌ **Incorrect Report (Sugarcoated):**
```markdown
## Quality Assessment: 7/10 (Acceptable)
Status: "Making progress toward standards"
```

**Summary:**
- Guides aim for perfection (10/10)
- Reports reveal truth (actual score)

**IMPORTANT: Professional tools are REQUIRED for comprehensive error detection.** When performing deep scans, code analysis, or verification tasks:

---

## Pre-Scan Verification Checklist

**BEFORE running any analysis, verify:**

### Tool Installation Verification
```bash
# PHP Tools
composer show --installed | grep phpstan
composer show --installed | grep psalm
composer show --installed | grep phpcs
composer show --installed | grep phpunit

# Frontend Tools
npm list eslint
npm list stylelint
npm list @testing-library/react
```

**Required:**
- ✅ PHPStan (v1.10+)
- ✅ Psalm (v5.15+)
- ✅ PHPCS (v3.7+)
- ✅ PHPUnit (v9.6+)
- ✅ ESLint (v8.56+)
- ✅ Stylelint (v16.2+)

### Configuration Files Verification
```bash
# PHP config files must exist:
phpstan.neon (or phpstan.neon.dist)
psalm.xml (or psalm.xml.dist)
phpcs.xml (or phpcs.xml.dist)

# Frontend config files must exist:
.eslintrc.js (or .eslintrc.json)
stylelint.config.js (or .stylelintrc.js)
```

**Required Config Files:**
- ✅ `phpstan.neon` or `phpstan.neon.dist` - Analysis rules
- ✅ `psalm.xml` or `psalm.xml.dist` - Type checking rules
- ✅ `phpcs.xml` or `phpcs.xml.dist` - Coding standards
- ✅ `.eslintrc.js` or `.eslintrc.json` - JS linting rules
- ✅ `stylelint.config.js` or `.stylelintrc.js` - CSS linting rules

**If Config Files Missing:**
- ❌ DO NOT proceed with analysis
- ⚠️ Report missing configurations
- 📋 Create configuration files first

---

## MANDATORY Tool Execution Standards

### PHP Analysis (All 3 Required)

#### 1. PHPStan (Level 6+)
```bash
composer --working-dir=wp-content/plugins/affiliate-product-showcase phpstan
```
**Minimum Level:** 6  
**Purpose:** Static analysis, type errors, dead code  
**Config:** phpstan.neon

#### 2. Psalm (Level 3+)
```bash
composer --working-dir=wp-content/plugins/affiliate-product-showcase psalm
```
**Minimum Level:** 3  
**Purpose:** Type checking, security vulnerabilities, logic bugs  
**Config:** psalm.xml

#### 3. PHPCS (PSR-12 + WPCS)
```bash
composer --working-dir=wp-content/plugins/affiliate-product-showcase phpcs
```
**Standard:** PSR-12 + WordPress Coding Standards  
**Purpose:** Code style, coding standards, duplicate code  
**Config:** phpcs.xml

### Frontend Analysis (Both Required)

#### 1. ESLint
```bash
npm --prefix wp-content/plugins/affiliate-product-showcase run lint:js
```
**Maximum Errors:** 0  
**Maximum Warnings:** <10  
**Purpose:** Syntax errors, unused code, code quality  
**Config:** .eslintrc.js

#### 2. Stylelint
```bash
npm --prefix wp-content/plugins/affiliate-product-showcase run lint:css
```
**Maximum Errors:** 0  
**Maximum Warnings:** <5  
**Purpose:** CSS syntax errors, invalid selectors, duplicate styles  
**Config:** stylelint.config.js

### Testing (All Required)

#### 1. PHPUnit
```bash
composer --working-dir=wp-content/plugins/affiliate-product-showcase test
```
**Status:** All tests passing required

#### 2. Code Coverage
```bash
composer --working-dir=wp-content/plugins/affiliate-product-showcase test-coverage
```
**Minimum Coverage:** 80% overall  
**Required Coverage:**
- Critical paths: 90%+
- Main business logic: 85%+
- Utility functions: 80%+
- Configuration: 70%+

#### 3. Frontend Tests
```bash
npm --prefix wp-content/plugins/affiliate-product-showcase run test
```
**Status:** All tests passing required

### Security Scanning (Required)

#### 1. PHP Dependencies
```bash
composer audit
composer outdated
```

#### 2. JavaScript Dependencies
```bash
npm audit
npm outdated
```

#### 3. Sensitive Data Detection
```bash
# Scan for hardcoded secrets, API keys, passwords
npm run check-debug
```

---

## Error Severity Classification

### CRITICAL (Blocks Production) 🚫

**Definition:** Issues that prevent code from running correctly or pose security risks

**Examples:**
- Syntax errors (PHP, JavaScript, CSS)
- Fatal errors (uncaught exceptions)
- Security vulnerabilities (SQL injection, XSS, CSRF)
- Missing required dependencies
- Broken imports/requires
- Type mismatches causing runtime errors
- Failing critical tests

**Action Required:** STOP - Must fix before proceeding

---

### MAJOR (Impacts Functionality) ⚠️

**Definition:** Issues that affect functionality or user experience

**Examples:**
- Type errors (not causing fatal errors)
- Logic bugs (incorrect behavior)
- Failing tests (non-critical)
- Performance issues (N+1 queries, missing cache)
- Memory leaks
- Blocking render resources
- Missing lazy loading
- Excessive API calls

**Action Required:** Should fix soon (within sprint)

---

### MINOR (Code Quality) 📝

**Definition:** Issues that don't affect functionality but impact maintainability

**Examples:**
- Style violations (PSR-12, ESLint, Stylelint)
- Missing documentation (PHPDoc, comments)
- Code duplication (3-10 lines)
- Unused variables/functions
- Inconsistent naming conventions
- Minor optimization opportunities

**Action Required:** Track and fix during technical debt time

---

### INFO (Suggestions) 💡

**Definition:** Best practice recommendations and optimization opportunities

**Examples:**
- Refactoring opportunities
- Performance optimizations (<5% impact)
- Code organization improvements
- Documentation enhancements
- Best practice adoption

**Action Required:** Consider for future improvements

---

## Quality Score Calculation

### Formula

```
Quality Score = 10 - (Critical * 2) - (Major * 0.5) - (Minor * 0.1)
```

**Score Interpretation:**
- **10/10 (Excellent):** 0 critical, 0-5 major, 0-20 minor
- **9/10 (Very Good):** 0 critical, 6-10 major, 21-40 minor
- **8/10 (Good):** 0 critical, 11-30 major, 41-80 minor
- **7/10 (Acceptable):** 0 critical, 31-50 major, 81-120 minor
- **6/10 (Fair):** 0 critical, 51-80 major, 121-200 minor
- **5/10 or below (Poor):** 1+ critical OR 81+ major OR 201+ minor

**Production Ready Criteria:**
- ✅ 0 critical errors
- ✅ ≤30 major errors
- ✅ ≤120 minor errors
- ✅ Quality score ≥7/10

---

## Complete Analysis Workflow

### 1. Pre-Analysis Phase

```bash
# Step 1: Verify tools installed
composer show --installed | grep -E "(phpstan|psalm|phpcodesniffer|phpunit)"
npm list eslint stylelint @testing-library/react

# Step 2: Verify config files exist
ls -la phpstan.neon psalm.xml phpcs.xml
ls -la .eslintrc.js stylelint.config.js

# Step 3: If missing, stop and report
```

**Output:** Report verification status, proceed only if all checks pass

---

### 2. Static Analysis Phase

```bash
# Step 1: Run PHPStan
composer --working-dir=wp-content/plugins/affiliate-product-showcase phpstan

# Step 2: Run Psalm
composer --working-dir=wp-content/plugins/affiliate-product-showcase psalm

# Step 3: Run PHPCS
composer --working-dir=wp-content/plugins/affiliate-product-showcase phpcs

# Step 4: Run ESLint
npm --prefix wp-content/plugins/affiliate-product-showcase run lint:js

# Step 5: Run Stylelint
npm --prefix wp-content/plugins/affiliate-product-showcase run lint:css
```

**Output:** Capture all tool outputs, categorize errors by severity

---

### 3. Testing Phase

```bash
# Step 1: Run PHPUnit
composer --working-dir=wp-content/plugins/affiliate-product-showcase test

# Step 2: Generate coverage report
composer --working-dir=wp-content/plugins/affiliate-product-showcase test-coverage

# Step 3: Run frontend tests
npm --prefix wp-content/plugins/affiliate-product-showcase run test
```

**Output:** Test results, coverage percentage, failing tests

---

### 4. Security Phase

```bash
# Step 1: Audit PHP dependencies
composer audit

# Step 2: Audit JavaScript dependencies
npm audit

# Step 3: Check for sensitive data
npm run check-debug
```

**Output:** Security vulnerabilities, outdated packages, hardcoded secrets

---

### 5. Result Aggregation Phase

**Combine and Categorize:**
- Count errors by severity (Critical/Major/Minor/Info)
- Identify common patterns across tools
- Cross-tool correlation (issues found by multiple tools)
- Compare with baseline (if available)
- Calculate quality score

**Output:** Structured error summary

---

### 6. Report Generation Phase

**Report Sections:**
1. Executive Summary (status, quality score, production ready?)
2. Professional Tool Results (each tool's findings)
3. Error Analysis by Severity
4. Common Patterns and Trends
5. Baseline Comparison (if available)
6. Coverage and Testing Results
7. Security Scan Results
8. Recommendations (prioritized action items)

---

## Tool Output Interpretation Guidelines

### PHPStan Output Parsing

```
Format:
Level X - [Error Type]: [File]:[Line]: [Message]

Interpretation:
- Level 0-2: Syntax errors (CRITICAL)
- Level 3-5: Type errors (MAJOR)
- Level 6-8: Possible bugs (MAJOR)
- Level 9: Deprecated/unused (MINOR)
```

### Psalm Output Parsing

```
Format:
[Error Type]: [File]:[Line]: [Message]

Interpretation:
- InvalidReturnType: Type mismatch (MAJOR)
- UndefinedVariable: Undefined (CRITICAL)
- PossiblyInvalidArgument: Type issue (MAJOR)
- MissingReturnType: Missing docblock (MINOR)
```

### PHPCS Output Parsing

```
Format:
[Standard] - [Error Type]: [File]:[Line]: [Message]

Interpretation:
- ERROR: Coding standard violation (MINOR)
- WARNING: Best practice suggestion (INFO)
```

### ESLint Output Parsing

```
Format:
[Error Type]: [File]:[Line]: [Message] [Rule]

Interpretation:
- error: Code quality issue (MAJOR)
- warning: Best practice (MINOR)
```

### Stylelint Output Parsing

```
Format:
[Error Type] - [Rule] - [File]:[Line]: [Message]

Interpretation:
- error: CSS issue (MAJOR)
- warning: Optimization (MINOR)
```

---

## Cross-Tool Correlation

### Priority Enhancement

**If multiple tools report same issue:**
- 2 tools report → Priority: HIGH (confirmed issue)
- 3 tools report → Priority: CRITICAL (must fix)

**Example:**
```
PHPStan: "Undefined variable $productId"
Psalm: "UndefinedVariable $productId"
PHPCS: "Undefined variable $productId"
→ Priority: CRITICAL (confirmed by 3 tools)
```

### Conflict Resolution

**If tools disagree:**
- Investigate context manually
- Check tool configurations
- Prioritize more strict tool
- Document discrepancy

**Example:**
```
ESLint: "unused variable"
Code analysis: Variable is used in conditional
→ Priority: MINOR (document investigation result)
```

---

## Baseline and Regression Detection

### Baseline Creation

```bash
# First scan - create baseline
composer --working-dir=wp-content/plugins/affiliate-product-showcase phpstan --generate-baseline
composer --working-dir=wp-content/plugins/affiliate-product-showcase psalm --set-baseline=psalm.xml
```

### Regression Detection

**Compare current scan with baseline:**
```
New errors introduced: X
Existing errors fixed: Y
Regressions: Z
```

**Analysis:**
- New errors → Investigate recent changes
- Fixed errors → Verify no regressions
- Regressions → Immediate attention required

---

## Automated Fix Capabilities

### Auto-Fix Options

#### PHP Style Issues
```bash
# Auto-fix PHPCS issues
composer --working-dir=wp-content/plugins/affiliate-product-showcase phpcs -- --fix
```
**Fixable:** ~60% of PHPCS issues  
**Manual Review Required:** Yes

#### JavaScript Issues
```bash
# Auto-fix ESLint issues
npm --prefix wp-content/plugins/affiliate-product-showcase run lint:js -- --fix
```
**Fixable:** ~70% of ESLint issues  
**Manual Review Required:** Yes

#### CSS Issues
```bash
# Auto-fix Stylelint issues
npm --prefix wp-content/plugins/affiliate-product-showcase run lint:css -- --fix
```
**Fixable:** ~40% of Stylelint issues  
**Manual Review Required:** Yes

### What CANNOT Be Auto-Fixed

- ❌ Syntax errors (CRITICAL)
- ❌ Type errors (MAJOR)
- ❌ Logic bugs (MAJOR)
- ❌ Security vulnerabilities (CRITICAL)
- ❌ Performance issues (MAJOR)
- ❌ Test failures (CRITICAL)

---

## When to Use Professional Tools

**ALWAYS use professional tools for:**

1. **Deep Code Scanning** - When analyzing code quality, syntax, or structure
2. **Verification Tasks** - When confirming sections are error-free
3. **Re-verification** - When re-checking previously fixed issues
4. **Quality Assessment** - When providing quality scores/ratings
5. **Error Detection** - When looking for syntax, duplicate, or other errors
6. **Comprehensive Reports** - When creating detailed verification reports
7. **Security Audits** - When checking for vulnerabilities
8. **Performance Analysis** - When identifying optimization opportunities

---

## Reporting Professional Tool Results

**ALWAYS include in verification reports:**

```markdown
### Professional Tool Analysis

**Tool Verification:**
- ✅ All tools verified and installed
- ✅ All config files present
- ✅ Minimum versions met

**PHP Analysis:**
- PHPStan: [Status] - [Total errors] - By severity: Critical [X], Major [X], Minor [X]
- Psalm: [Status] - [Total errors] - By severity: Critical [X], Major [X], Minor [X]
- PHPCS: [Status] - [Total errors] - By severity: Critical [X], Major [X], Minor [X]

**Frontend Analysis:**
- ESLint: [Status] - [Total errors] - [Total warnings] - By severity: Critical [X], Major [X], Minor [X]
- Stylelint: [Status] - [Total errors] - [Total warnings] - By severity: Critical [X], Major [X], Minor [X]

**Testing:**
- PHPUnit: [Status] - [Passed]/[Total] tests
- Coverage: [X]% overall - [Breakdown by area]
- Frontend Tests: [Status] - [Passed]/[Total] tests

**Security Scan:**
- Composer Audit: [Vulnerabilities found]
- NPM Audit: [Vulnerabilities found]
- Sensitive Data: [Issues found]

**Cross-Tool Correlation:**
- Issues confirmed by 2+ tools: [List]
- Conflicting findings: [List with resolution]

**Quality Score:**
- Calculated Score: [X]/10
- Production Ready: ✅ Yes / ❌ No
- Blocking Issues: [List critical issues]

**Tool Execution:**
- Tools Run: ✅ All / ⚠️ Partial / ❌ None
- Execution Method: [Direct / User-Provided / Manual Only]
```

---

## Integration with Manual Analysis

Professional tools provide **automated, comprehensive error detection**. Use them **ALONGSIDE** manual analysis:

**Professional Tools:**
- ✅ Syntax errors (automatic detection)
- ✅ Type errors (static analysis)
- ✅ Code style issues (automated linting)
- ✅ Duplicate code (pattern detection)
- ✅ Security vulnerabilities (security scanners)
- ✅ Functional errors (test failures)

**Manual Analysis:**
- ✅ Code organization (architectural review)
- ✅ Best practices compliance (standards review)
- ✅ Logic correctness (code review)
- ✅ Pattern consistency (structure review)
- ✅ Documentation quality (documentation review)
- ✅ Integration completeness (dependency review)

**Combined Approach:**
1. Run professional tools first (automated detection)
2. Analyze tool results (identify issues)
3. Perform manual analysis (contextual review)
4. Combine findings (comprehensive report)
5. Provide recommendations (actionable next steps)

---

## Minimum Requirements for Production

**To mark a section as "Production Ready":**

- ✅ 0 critical errors
- ✅ ≤30 major errors
- ✅ ≤120 minor errors
- ✅ Quality score ≥7/10
- ✅ 80%+ test coverage
- ✅ All tests passing
- ✅ No security vulnerabilities
- ✅ All tools executed (not manual only)

**Any deviation from these requirements must be clearly documented.**

---

### Structure and File Scanning

When scanning plugin structure, directories, or files as per the Plugin Structure List Format (e.g., section 3 of plugin-structure.md):

**ALWAYS identify and report:**
- **Code Quality Issues:** Check for:
  - Missing files or directories
  - Incorrect file naming conventions
  - Structural deviations from documented format
  - Broken or missing dependencies in related configuration files
  - Inconsistent patterns across similar files
  - Missing required code in related files

- **Code Quality Rating:** Provide a quality assessment using the following scale:
  - **10/10 (Excellent):** Perfect compliance with documented format, no issues found
  - **9/10 (Very Good):** Minor cosmetic issues that don't affect functionality
  - **8/10 (Good):** Some deviations but core functionality intact
  - **7/10 (Acceptable):** Notable issues that should be addressed soon
  - **6/10 (Fair):** Multiple issues requiring attention
  - **5/10 or below (Poor):** Significant problems requiring immediate fixes

  **Rating Criteria:**
  - Structure completeness (files present, correct naming)
  - Configuration integrity (dependencies, build settings)
  - Code organization (separation of concerns, patterns)
  - Documentation quality (comments, descriptions)
  - Best practices compliance (WordPress standards, coding conventions)

- **Related Files Verification:** Verify that related root files contain:
  - Required dependencies
  - Proper configuration
  - Necessary build/compile settings
  - Appropriate registration or import code

### Code Optimization Scanning

When scanning code (PHP, JavaScript, CSS), assess optimization level to ensure efficient performance and resource usage:

**ALWAYS identify and report:**

- **Critical-Impact Issues:** (Must Fix - Block on these)
  - N+1 query problems in database operations
  - Missing object caching for expensive operations
  - Large asset bundles (>500KB) without code splitting
  - Unoptimized database queries (no indexes, excessive joins, SELECT *)
  - Blocking render resources (critical CSS/JS inline in head)
  - Missing lazy loading for images and media
  - Excessive API calls (not batched/cached, >50 calls per page)
  - Memory leaks (unclosed connections, circular references)
  - Synchronous blocking operations in hot paths

- **High-Impact Issues:** (Important - Should Fix Soon)
  - Unused dependencies or packages in package.json/composer.json
  - Duplicate code blocks that could be refactored (10+ lines)
  - Missing minification for production CSS/JS assets
  - Unnecessary re-renders in React components (no memo/useMemo where needed)
  - Large image files not optimized (>500KB, not compressed)
  - Missing database query caching for expensive operations
  - CSS selector specificity issues (overspecified selectors)
  - Missing code splitting for large modules (>200KB)
  - Inefficient loops or algorithms (O(n²) when O(n) possible)
  - Unoptimized regex patterns (catastrophic backtracking risk)

**Tailwind CSS Optimization Issues:**
  - **Critical:** Tailwind JIT mode not enabled (legacy full build)
  - **Critical:** Missing content/purge configuration (includes unused CSS)
  - **High:** Bloated CSS bundle (>200KB) due to unused utilities
  - **High:** Arbitrary Tailwind values used instead of core utilities (increases bundle size)
  - **High:** Duplicate or redundant Tailwind class usage in components
  - **High:** Missing Critical CSS extraction (above-fold styles)
  - **High:** CSS specificity conflicts from custom styles overriding utilities
  - **Medium:** Unused Tailwind utilities detected in scan (not removed in build)
  - **Medium:** Missing Tailwind production optimizations (purgeOptions, content paths)
  - **Low:** Excessive class names on elements (>10 classes, reduces readability)

**Tailwind Optimization Checklist:**
  - ✅ JIT mode enabled in tailwind.config.js (mode: 'jit')
  - ✅ Content paths configured (content: ['./**/*.{html,js,php}'])
  - ✅ Safelist minimal (only dynamic classes needed)
  - ✅ No arbitrary values (use core utilities: p-4, not p-[17px])
  - ✅ CSS bundle size <200KB (after minification)
  - ✅ Critical CSS extracted for above-fold content
  - ✅ No custom CSS that conflicts with utilities
  - ✅ Duplicate class usage minimized
  - ✅ Production build verified (npm run build)

- **Medium-Impact Issues:** (Track and Plan)
  - Minor code duplication (3-10 lines)
  - Small bundle size improvements possible (<10KB)
  - Missing image lazy loading for below-fold images
  - Unused CSS rules or JavaScript functions
  - Suboptimal database queries (could use indexes better)
  - Missing virtualization for large lists (>1000 items)
  - Not using CDN for static assets
  - Missing compression for text-based assets (gzip/brotli)
  - Inefficient DOM manipulations (excessive reflows)
  - Missing debouncing/throttling for event handlers

- **Low-Impact Issues:** (Nice to Have - Track Trends)
  - Micro-optimizations in hot paths (<5% improvement)
  - Minor code style optimizations
  - Small performance improvements (<10KB savings)
  - Optimization opportunities with <1% measurable impact
  - Premature optimizations (before measuring)

- **What NOT to Report:**
  - ❌ Premature optimizations (before measuring actual impact)
  - ❌ Micro-optimizations with <1% impact that hurt readability
  - ❌ Opinionated style preferences (tabs vs spaces, naming conventions)
  - ❌ Over-engineering suggestions that add complexity
  - ❌ Optimizations that significantly harm code maintainability
  - ❌ Theoretical optimizations without practical benefit

**Optimization Rating Criteria:**

- **Critical Issues Count:** 0-5+ (lower is better)
- **Performance Impact:** Measured improvement potential (Critical >50%, High 20-50%, Medium 5-20%, Low <5%)
- **Code Maintainability:** Does optimization keep code readable and maintainable?
- **Resource Usage:** CPU, memory, bandwidth impact
- **User Experience Impact:** Visible performance degradation or improvement

**Optimization Quality Scale:**
- **10/10 (Excellent):** No critical/high issues, well-optimized code
- **9/10 (Very Good):** No critical issues, minor optimization opportunities
- **8/10 (Good):** No critical issues, some medium optimizations needed
- **7/10 (Acceptable):** 1-2 high-impact issues, needs attention soon
- **6/10 (Fair):** 3-5 high-impact issues, multiple optimizations needed
- **5/10 or below (Poor):** Multiple critical issues, performance severely impacted

**Output Format for Optimization Assessment:**

```
### Optimization Assessment: [X]/10

**Critical Issues:** [COUNT] (Must Fix)
1. [Specific issue with impact assessment]
2. [Specific issue with impact assessment]

**High-Impact Issues:** [COUNT] (Important)
1. [Specific issue with impact assessment]
2. [Specific issue with impact assessment]

**Medium-Impact Issues:** [COUNT] (Track)
1. [Specific issue with impact assessment]

**Low-Impact Issues:** [COUNT] (Nice to Have)
1. [Specific issue with impact assessment]

**Overall Impact:** [High/Medium/Low] based on critical and high-impact issues
```

**Impact Assessment Examples:**
- "N+1 query in ProductRepository::get_products() - Potential 80% performance degradation on 100+ products"
- "Missing object cache for expensive operation - Could reduce response time by 60%"
- "Large asset bundle (850KB) without code splitting - Increases page load time by 2-3 seconds on slow connections"
- "Missing lazy loading for 20 images - Adds 500KB initial load, delays visible content by 1.5 seconds"

**When to Use This Guidance:**
- Scanning plugin structure sections (e.g., section 3: blocks/, section 6: src/)
- Verifying directory structure compliance
- Checking configuration files (package.json, tsconfig.json, etc.)
- Validating code organization and architecture
- Auditing file presence and completeness

**Important: When "use assistant instruction file" is specified, automatically apply:**
1. All instructions in this file (behavior preferences, scanning tasks, etc.)
2. **All standards from Assistant Quality Standards file** - This is your primary reference for code quality, testing, security, accessibility, and best practices
3. All relevant specialized guides (Performance Optimization, etc.) as needed

**Clarification:** You do NOT need to specify "assistant quality standards" separately - it is automatically included when using assistant instructions.

**Output Format:**
- Use the exact Plugin Structure List Format from the relevant section
- Mark verified items with ✅
- Mark missing or non-compliant items with ❌
- Include specific code quality findings in the scan summary
- List all related files and their verification status


## Specialized Reference Guides

When working on specific domains, refer to these comprehensive guides:

### Performance Optimization
**Guide:** [Performance Optimization Guide](assistant-performance-optimization.md)

**When to use:**
- Analyzing web performance
- Optimizing page load times
- Implementing performance improvements
- Conducting performance audits
- Creating optimization recommendations

**What it includes:**
- Standard assessment format (0-10 quality scale)
- Comprehensive optimization checklist
- Priority-based recommendations (Critical/High/Medium/Low)
- Implementation timelines
- Code examples and best practices
- Expected performance improvements
- Tools and commands reference

**Usage:** Copy the standard assessment format from the guide and fill in your specific analysis. Use scorecard to track progress and prioritize improvements.

### Quality Standards
**Guide:** [Assistant Quality Standards](assistant-quality-standards.md)

**When to use:**
- Writing new code (ALWAYS)
- Reviewing existing code
- Conducting code reviews
- Setting coding standards
- Training team members
- Establishing best practices

**What it includes:**
- Hybrid Quality Matrix approach (Essential standards at 10/10, performance goals as targets)
- Quality standards for all code
- Detailed requirements by category:
  - Code Quality (PHP, JavaScript/React)
  - Performance (Frontend, Backend - Track trends, don't block)
  - Security (Input validation, XSS, SQL injection, CSRF)
  - Accessibility (WCAG 2.1 AA/AAA)
  - Testing (Unit, Integration, E2E - 90%+ coverage)
  - Documentation (PHPDoc, API docs - Basic documentation)
  - Git standards (Commits, Pull requests)
  - DevOps & Infrastructure (Monitoring, Deployment, Rollback)
- Pre-commit checklist
- Code examples for every requirement
- Wrong vs. Correct comparisons

**Standard:** Hybrid Quality Matrix - Maintain quality excellence while supporting sustainable development

**Usage:** **THIS IS YOUR PRIMARY REFERENCE.** Before writing any code, consult this guide. Use pre-commit checklist before every commit. All new code must meet hybrid quality matrix standards.

---

## Chat Summary Storage (STRICT MANDATORY RULE)

**🚨 CRITICAL: This is a STRICT rule that MUST be followed for EVERY task completion.**

**NO EXCEPTIONS - This rule applies to ALL tasks regardless of type or complexity.**

### Chat Summary Format (MANDATORY)

**Every task completion must include the following format at the top of the summary:**

```
## User Request
"[Your exact message]"

## Assistant Files Used
- ✅ assistant-instructions.md (APPLIED)
- ✅ assistant-quality-standards.md (APPLIED)
- ✅ assistant-performance-optimization.md (APPLIED)
```

**OR if no assistant files were used:**
```
## User Request
"[Your exact message]"

## Assistant Files Used
No assistant file is used
```

**IMPORTANT:** The "User Request" section should contain the **recent message** sent in the chat, not the original message from when the chat started. Each summary should focus on the current task and recent conversation.

### Storage Requirements

**Location:** Root directory `chat-history/`

**File Naming Convention:**
```
chat-history/Chat-[Number]-YYYY-MM-DD-HHMM.md
```

**Examples:**
- `chat-history/Chat-001-2026-01-17-1550.md`
- `chat-history/Chat-002-2026-01-17-1600.md`
- `chat-history/Chat-003-2026-01-18-0930.md`

### File Content Format

Each chat summary file should contain:

```markdown
# Chat Summary - [Date] - [Time]

**Chat Number:** [Number]
**Date:** YYYY-MM-DD
**Time:** HH:MM (Timezone)

## User Request
"[Your exact message]"

## Assistant Files Used
- ✅ assistant-instructions.md (APPLIED)
- ✅ assistant-quality-standards.md (APPLIED)
- ✅ assistant-performance-optimization.md (APPLIED)

## Summary
[Brief summary of the recent message]

## Key Points
- [Point 1]
- [Point 2]
- [Point 3]

## Actions Taken
- [Action 1]
- [Action 2]

## Files Modified
- [File path 1]
- [File path 2]

## Git Commit
- Commit Hash: [Hash]
- Commit Message: [Message]

## Status
✅ Complete / ⚠️ Partial / ❌ Pending

---
*Generated on: YYYY-MM-DD HH:MM:SS*
```

**IMPORTANT:** Only summarize the recent message, not the entire chat history. Each summary should be concise and focused on the current task.

### When to Create/Update Chat Files

**🚨 CRITICAL: This is a NON-NEGOTIABLE requirement. Failure to create/update chat summaries will result in incomplete task completion.**

**ALWAYS create or update a chat summary file when:**

1. **Task Completion:** After using attempt_completion - **MANDATORY**
2. **Multi-step Tasks:** After completing each major step - **MANDATORY**
3. **Code Changes:** After modifying files - **MANDATORY**
4. **Git Operations:** After commit/push operations - **MANDATORY**
5. **Verification Tasks:** After completing verification reports - **MANDATORY**
6. **After Each Message Summary:** After creating each summary report - **MANDATORY**

**NO EXCEPTIONS - This applies to:**
- ✅ All task types (code writing, scanning, verification, documentation, etc.)
- ✅ All task complexities (simple reads, complex implementations, etc.)
- ✅ All task durations (quick tasks, multi-hour sessions, etc.)
- ✅ All task outcomes (success, partial success, failure, etc.)

**FAILURE TO COMPLY = INCOMPLETE TASK**

### File Creation Strategy: Per Chat Session

**IMPORTANT: Files are created per chat session, NOT per day.**

**How it works:**
- **First message of a new chat session:** Create a new file with next sequential number
- **Subsequent messages in same session:** Update the existing file (append new summaries)
- **New chat session:** Create a new file with incremented number

**Example:**
```
Chat Session 1 (Today, 6:19 PM):
- Message 1: Create file 001
- Message 2: Update file 001
- Message 3: Update file 001

Chat Session 2 (Today, 6:30 PM):
- Message 1: Create file 002
- Message 2: Update file 002

Chat Session 3 (Tomorrow, 9:00 AM):
- Message 1: Create file 003
```

**Why per session, not per day:**
- ✅ Tracks individual conversations
- ✅ Easier to reference specific discussions
- ✅ Maintains chronological order
- ✅ Prevents file from becoming too large
- ✅ Clear separation between different topics/sessions

### File Naming Logic

**For New Chats:**
- Check existing files in chat-history/
- Find the highest numbered file
- Increment the number by 1
- Use current date and time

**For Continuing Chats:**
- If same chat continues, update the existing file
- Add new sections with timestamps
- Maintain version history

### Example Implementation

**Step 1: Determine file name**
```bash
# Get current date/time
DATE=$(date +%Y-%m-%d)
TIME=$(date +%H%M)

# Find next chat number
NEXT_NUM=$(ls chat-history/*.md 2>/dev/null | wc -l)
NEXT_NUM=$((NEXT_NUM + 1))

# Format number with leading zeros
FILE_NAME="chat-history/Chat-$(printf "%03d" ${NEXT_NUM})-${DATE}-${TIME}.md"
```

**Step 2: Create file with template**
```bash
cat > "$FILE_NAME" << EOF
# Chat Summary - ${DATE} - ${TIME}

**Chat Number:** ${NEXT_NUM}
**Date:** ${DATE}
**Time:** ${TIME}

## User Request
[User request here]

## Summary
[Summary here]

## Key Points
- [Point 1]

## Actions Taken
- [Action 1]

## Files Modified
- [File path 1]

## Git Commit
- Commit Hash: [Hash]
- Commit Message: [Message]

## Status
✅ Complete

---
*Generated on: $(date)*
EOF
```

**Step 3: Update file after task completion**
```bash
# Append to existing file
cat >> "$FILE_NAME" << EOF

## Additional Notes
[Additional information]

---
*Updated on: $(date)*
EOF
```

### Why This Matters

**Documentation:**
- Creates audit trail of all conversations
- Helps track progress over time
- Provides reference for future work

**Organization:**
- Keeps all chat history in one place
- Easy to search and reference
- Maintains chronological order

**Accountability:**
- Documents what was done and when
- Shows git commit history
- Tracks file modifications

### Integration with Git

**IMPORTANT: Include chat-history folder in git commits**`

```bash
# Add chat history to git
git add chat-history/

# Commit with appropriate message
git commit -m "docs: Add chat history for [task description]"

# Push to remote
git push
```

**Git Ignore Consideration:**
- Chat history SHOULD be tracked in git
- They provide valuable documentation
- Do NOT add to .gitignore

---

## Local Verification (Required)

Run these from the repo root:

```bash
# PHP: static analysis + style
composer --working-dir=wp-content/plugins/affiliate-product-showcase analyze

# PHP: tests (with coverage when needed)
composer --working-dir=wp-content/plugins/affiliate-product-showcase test
composer --working-dir=wp-content/plugins/affiliate-product-showcase test-coverage

# Frontend: lint + tests
npm --prefix wp-content/plugins/affiliate-product-showcase run lint
npm --prefix wp-content/plugins/affiliate-product-showcase run test
