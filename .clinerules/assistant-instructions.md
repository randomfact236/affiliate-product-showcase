# Assistant Instructions

### Project Context

**Project:** Affiliate Product Showcase WordPress Plugin
**Framework:** Modern WordPress Plugin Boilerplate (Tailwind + Vite + PSR-4 + Security & Cache Ready)
**Quality Standard:** Hybrid Quality Matrix - Enterprise-grade where it matters, practical everywhere
**Architecture:** DI container, event-driven architecture, REST API, Gutenberg blocks
**Tech Stack:** PHP 8.1+, JavaScript/React, Vite, Tailwind CSS
**Code Quality:** PHPUnit, PHPCS (WPCS), PHPStan, Psalm

**Quality Standard:** 10/10 enterprise-grade, fully/highly optimized, no compromises. 


### 🚨 CRITICAL: Code Change Policy (MANDATORY)

**RULE: NEVER make code changes unless explicitly instructed to do so.**

**Requirements:**
- ❌ **NEVER** automatically modify, edit, or delete code files
- ❌ **NEVER** make code changes even when user asks questions or requests help
- ❌ **NEVER** apply fixes, patches, or modifications without explicit approval
- ✅ **ALWAYS** provide explanations, analysis, and solutions WITHOUT making changes
- ✅ **ALWAYS** ask: "Do you want me to make these changes?" before applying any code modifications
- ✅ **ONLY** make code changes when you receive:
  - Explicit "yes" response to making changes
  - Direct instruction to "fix this" or "apply this change"
  - Command to "update" or "modify" specific files

**What You CAN Do Without Permission:**
- ✅ Read files to analyze code
- ✅ Search for code patterns
- ✅ Explain issues and provide solutions
- ✅ Show code examples (without saving to files)
- ✅ Run diagnostic commands (git status, php -l, etc.)
- ✅ Stage files for changes (git add) - but NOT commit

**What You MUST Get Permission For:**
- ❌ Writing or editing files (write_to_file, replace_in_file)
- ❌ Making code changes or fixes
- ❌ Deleting or moving files
- ❌ Applying patches or solutions to code

**Example of CORRECT Behavior:**

**User:** "Why is there two main menu items?"
**Assistant:** Explains the issue, identifies the cause, suggests solution, then asks "Do you want me to fix this?"
**User:** "Yes"
**Assistant:** [Applies the fix]

**Example of INCORRECT Behavior:**

**User:** "Why is there two main menu items?"
**Assistant:** [Immediately fixes the issue without asking]

**This Policy Applies To:**
- ✅ **ALL** code modifications
- ✅ **ALL** file edits (write_to_file, replace_in_file)
- ✅ **ALL** bug fixes
- ✅ **ALL** feature implementations
- ✅ **ALL** refactoring work
- ✅ **ALL** configuration changes

**Exceptions:**
The ONLY times you can make changes without asking:
- When user explicitly says "fix this" or "make this change"
- When user says "yes" to your question about making changes
- When user provides direct command to modify files

**NO EXCEPTIONS** to this rule. User must explicitly approve all code changes.

---

### 🚨 CRITICAL: Git Operations Rules (MANDATORY)

**RULE:** Never commit, push, or create branches unless explicitly instructed.

**Commit and Push Rules:**
- ❌ **NEVER** auto-commit changes
- ❌ **NEVER** auto-push to repository
- ✅ **ALWAYS** ask: "Do you want me to commit and push these changes?"
- ✅ **ONLY** execute git commit and push when you receive:
  - Explicit "yes" response to question
  - Direct command to commit and push

**You CAN do without permission:**
- ✅ git status checks
- ✅ git diff to show changes
- ✅ git add to stage files
- ✅ Prepare commits (but NOT commit)

**Branch Creation Rules:**
- ❌ **NEVER** auto-create branches
- ❌ **NEVER** auto-checkout branches
- ❌ **NEVER** auto-push branches to origin
- ✅ **ALWAYS** propose exact git command and wait for user confirmation
- ✅ **ALWAYS** ask before any branch-related changes
- ✅ **NO EXCEPTIONS** to this rule

**User must provide EXPLICIT instruction like:**
```
"Create branch feature/new-pricing and push to origin"
"Create backup-2026-01-16-1430 and push to origin"
"Checkout to branch develop and pull latest"
```

**What will NOT trigger branch creation:**
- ❌ "Fix this issue" (requires explicit branch creation request)
- ❌ "Make these changes" (requires explicit branch creation request)
- ❌ "Start working on feature X" (requires explicit branch creation request)

**Backup Branch Naming:**

When creating a manual backup branch (e.g., requested by user), branch name MUST use format:
```
backup-YYYY-MM-DD-HHMM
```

**Examples:**
- `backup-2026-01-16-1430` (January 16, 2026 at 2:30 PM)
- `backup-2026-12-25-0915` (December 25, 2026 at 9:15 AM)

**Quick Reference Checklist:**

Before Any Git Operation:
- [ ] Did user explicitly say "commit and push"?
- [ ] Did user explicitly say "create branch"?
- [ ] Did I ask for permission if it wasn't explicit?
- [ ] Did I confirm backup branch name format if applicable?

**Consequences:**

**If you violate these rules:**
- ❌ Auto-committing without permission → **VIOLATION**
- ❌ Auto-creating branches without explicit instruction → **VIOLATION**

**Result:**
- User will reject your response
- You must retry with correct format
- Work may need to be redone
- Trust is lost

**Summary:**
- Never auto-commit or auto-push
- Never auto-create, auto-checkout, or auto-push branches
- Always ask for explicit permission
- Backup branches must follow `backup-YYYY-MM-DD-HHMM` format

**REMEMBER: These git rules are NOT optional. They are MANDATORY.**

---



---

---
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

### Default Chat History Rules

**⚠️ OPTIONAL: Update chat history ONLY when explicitly requested.**

**Location:** `chat-history/` directory

**File Naming:**
- New chat session: Create new file with next sequential number
- Format: `Chat-[Number]-YYYY-MM-DD-HHMM.md`
- Example: `Chat-001-2026-01-17-2245.md`

**Storage Strategy:**
- **Copy ALL output result of each message** - Copy the complete output from each message
- **Top(latest) to bottom(previous message) order** - Arrange messages with latest at top, oldest at bottom
- **Exact output** - Store the complete, exact output result (all sections) in the history file
- **No matter how many messages** - Even if multiple messages in chat box, copy the latest message output when requested

**When to Create/Update Chat History:**
- User explicitly requests: "update chat history"
- User explicitly requests: "create chat history"
- User explicitly requests: "save to chat history"
- **DO NOT** automatically create/update unless explicitly requested

---

### File Creation Rules

**New chat session (when requested):**
1. Determine next sequential number (e.g., Chat-010, Chat-011)
2. Create new file with timestamp
3. Add task summary to file

**Same chat session (when requested):**
1. Read existing chat history file
2. Add NEW entry at TOP (before previous entries)
3. Use write_to_file to save updated content

---

### OPTIONAL Process

**ONLY perform these steps when user explicitly requests chat history update:**

1. User requests: "update chat history" or similar
2. Read existing chat history file (if exists)
3. Add NEW entry at TOP (before previous entries)
4. Use write_to_file to save updated content
5. Verify file was updated successfully

**NOTE:** Chat history creation/update is OPTIONAL and MANUAL. Do NOT create automatically.

**Benefits of Manual Chat History:**
- Faster workflow (only update when requested)
- Reduced token usage (fewer automatic operations)
- User control over what gets documented
- Flexibility to update when needed
- Latest information immediately visible

---

### Chat History File Format

**Reverse Chronological Order (Latest → Oldest):**
**Must match Standard Output Format exactly**

```markdown
## User Request
"[Latest Message]"

## Assistant Files Used
- ✅ docs/assistant-instructions.md (APPLIED)
- ❌ docs/assistant-quality-standards.md (NOT USED)
- ❌ docs/assistant-performance-optimization.md (NOT USED)

## [Task summary content]

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

---
*Generated on: YYYY-MM-DD HH:MM:SS*

## User Request
"[Next Message]"

## Assistant Files Used
- ✅ docs/assistant-instructions.md (APPLIED)
- ❌ docs/assistant-quality-standards.md (NOT USED)

## [Task summary content]

---

## 💡 Recommendations

**Code Quality:**
- [Specific, actionable suggestion]

**Next Steps:**
- [Immediate next action]

**Consider This:**
- [Related feature]

---
*Generated on: YYYY-MM-DD HH:MM:SS*

## User Request
"[Oldest Message]"

## Assistant Files Used
- ✅ docs/assistant-instructions.md (APPLIED)
- ❌ docs/assistant-performance-optimization.md (NOT USED)

## [Task summary content]

---

## 💡 Recommendations

**Code Quality:**
- [Specific, actionable suggestion]

**Next Steps:**
- [Immediate next action]

---
*Generated on: YYYY-MM-DD HH:MM:SS*
```

**IMPORTANT:** Every chat history entry MUST follow the complete Standard Output Format with:
1. User Request
2. Assistant Files Used
3. Main Content
4. Separator (---)
5. 💡 Recommendations (with Code Quality, Next Steps, Consider This)
6. Separator (---)
7. Generated timestamp

---
### Standard Output Format

**This format applies to ALL messages sent in the chat - NO EXCEPTIONS.**

---

### Mandatory Header Sections

These sections appear at the TOP of EVERY message:

**1. User Request (Mandatory - First Section)**
- The exact message or task provided by user
- Appears at the TOP of EVERY message
- Format: `## User Request "[Latest Message]"`

**2. Assistant Files Used (Mandatory - Second Section)**
- List ALL assistant instruction/reference files (docs/assistant-*.md)
- DO NOT list project files, source code, or files created during task
- Status indicators are MANDATORY:
  - ✅ (APPLIED) - File was used/applied during the task
  - ❌ (NOT USED) - File was referenced/available but not directly applied
- Even files marked as NOT USED must be listed to maintain complete context
- Format: `## Assistant Files Used` with list of files and status

---

### Recommendation Sections (Mandatory - Appear at Bottom)

These sections appear under "## 💡 Recommendations" at the BOTTOM of EVERY message:

**3. Code Quality Suggestions**
- Refactoring opportunities
- Performance optimizations
- Security enhancements
- Best practice improvements

**4. Next Steps**
- Immediate follow-up actions
- Related features to consider
- Testing recommendations

**5. Related Features**
- Features that complement current implementation
- Edge cases to handle
- Integrations to consider

---

### Guidelines for Mandatory Header Sections

**User Request Section:**
- Must appear FIRST in EVERY message
- Must contain the exact user message/task
- Format: `## User Request "[Latest Message]"`

**Assistant Files Used Section:**
- Must appear SECOND (immediately after User Request) in EVERY message
- List **ALL** assistant instruction/reference files (docs/assistant-*.md)
- Required files to **ALWAYS** include:
  ✅ docs/assistant-instructions.md (or .clinerules/assistant-instructions.md)
  ✅ docs/assistant-quality-standards.md
  ✅ docs/assistant-performance-optimization.md
- DO NOT list project files, source code, or files created during task
- Status indicators are MANDATORY:
  - ✅ (APPLIED) - File was used/applied during the task
  - ❌ (NOT USED) - File was referenced/available but not directly applied
- Even files marked as NOT USED must be listed to maintain complete context
- Format: `## Assistant Files Used` with list of files and status

⚠️ **CRITICAL: Do NOT skip listing any assistant file.** Even if a file was not used, it MUST be listed with status ❌ (NOT USED).

---

### Guidelines for Recommendations Sections

- **Keep recommendations concise**: Maximum 2-3 key points per section
- **Make them actionable**: Specific, implementable suggestions
- **Be context-aware**: Tailor suggestions to specific message context
- **Avoid redundancy**: Don't repeat same suggestions multiple times
- **Prioritize value**: Only include genuinely useful recommendations
- **Apply to**: Code Quality, Next Steps, and Related Features sections
- **Mandatory in ALL messages**: Recommendations section must appear at BOTTOM of EVERY message

---

### Complete Output Structure Example

```
## User Request
"[Latest Message]"

## Assistant Files Used
- ✅ docs/assistant-instructions.md (APPLIED)
- ❌ docs/assistant-quality-standards.md (NOT USED)
- ❌ docs/assistant-performance-optimization.md (NOT USED)

## [Main Task Summary/Results Content]

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

---
*Generated on: YYYY-MM-DD HH:MM:SS*
```

---

## 🔍 Transparent Work Mode (Real-Time Visibility)

**Purpose:** Provide maximum visibility into file operations since real-time GUI preview is not available.

### File Operation Announcements (MANDATORY)

**Before Reading a File:**
```
📂 Reading: {filepath}
```

**After Reading a File:**
```
✅ Read: {filepath} ({line_count} lines, {size}KB)
```

**Before Writing a File:**
```
📝 Writing: {filepath} ({change_type: modify/create/append})
```

**After Writing a File:**
```
✅ Written: {filepath} ({bytes_written} bytes)
```

### Progress Tracking (For Multi-File Operations)

**Show progress indicator:**
```
📊 Progress: [3/12] files processed
⏳ Current: src/Admin/ProductsTable.php
⏭️ Next: src/Admin/AjaxHandler.php
```

### Activity Status

**Include "🔨 Currently:" line in main content:**
```
🔨 Currently: Analyzing CSS focus states in _buttons.scss
```

### File Change Summary (End of Session)

**List all files touched:**
```
📋 Files Modified This Session:
✅ Read: src/Admin/ProductsTable.php (164 lines)
✅ Written: plan/action-plan.md (400 lines, +200)
✅ Modified: src/Admin/AjaxHandler.php (150 lines, -5/+15)
```

### Visual Indicators

| Symbol | Meaning |
|--------|---------|
| 📂 | Opening/Reading file |
| 📝 | Writing/Modifying file |
| ✅ | Completed successfully |
| ⏳ | In progress |
| ⏭️ | Next item |
| 📊 | Progress stats |
| 🔨 | Current activity |
| 📋 | Summary/List |

---

### When to Skip Recommendations

**IMPORTANT:** Recommendations are MANDATORY for ALL messages - NO EXCEPTIONS.

**The ONLY time to skip recommendations is when:**
- User explicitly says "no recommendations needed"
- User provides clear instruction to omit recommendations

**Recommendations Location Rule:**
- ALWAYS provide recommendations in EVERY message
- MUST appear at BOTTOM of message after main content
- Use standard format: Code Quality, Next Steps, Consider This
- MUST be included in chat summary entries
- MUST be included in generated report files (when applicable)

**No exceptions** - recommendations are required for all messages including:
- Task completions
- Casual conversations
- Questions and answers
- Clarifications
- Informational responses
- Tool use results

**Assistant Files Used Section Rule (CRITICAL):**
- **ALWAYS** include the "Assistant Files Used" section in EVERY task completion output
- **List ALL assistant instruction files** that are relevant to the task context
- **Status indicators are MANDATORY:**
  - ✅ (APPLIED) - File was used/applied during the task
  - ❌ (NOT USED) - File was referenced/available but not directly applied
- **Even files marked as NOT USED must be listed** to maintain complete context
- **This section appears in both:** chat history entries AND task completion outputs
- **Purpose:** Maintain complete audit trail of which assistant guidelines were available, even if not all were directly used

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
```
## Quality Standard: Enterprise Grade (10/10)
Target: All optimizations implemented to meet enterprise standards
```

✅ **Correct Report on Actual Code:**
```
## Quality Assessment: 3/10 (Poor)
Status: NOT meeting enterprise standards
Issues: Multiple critical errors, missing implementations
```

❌ **Incorrect Report (Sugarcoated):**
```
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

   - 1. PHPStan (Level 6+)
```bash
composer --working-dir=wp-content/plugins/affiliate-product-showcase phpstan
```
**Minimum Level:** 6  
**Purpose:** Static analysis, type errors, dead code  
**Config:** phpstan.neon

   - 2. Psalm (Level 3+)
```bash
composer --working-dir=wp-content/plugins/affiliate-product-showcase psalm
```
**Minimum Level:** 3  
**Purpose:** Type checking, security vulnerabilities, logic bugs  
**Config:** psalm.xml

   - 3. PHPCS (PSR-12 + WPCS)
```bash
composer --working-dir=wp-content/plugins/affiliate-product-showcase phpcs
```
**Standard:** PSR-12 + WordPress Coding Standards  
**Purpose:** Code style, coding standards, duplicate code  
**Config:** phpcs.xml

### Frontend Analysis (Both Required)

   - 1. ESLint
```bash
npm --prefix wp-content/plugins/affiliate-product-showcase run lint:js
```
**Maximum Errors:** 0  
**Maximum Warnings:** <10  
**Purpose:** Syntax errors, unused code, code quality  
**Config:** .eslintrc.js

   - 2. Stylelint
```bash
npm --prefix wp-content/plugins/affiliate-product-showcase run lint:css
```
**Maximum Errors:** 0  
**Maximum Warnings:** <5  
**Purpose:** CSS syntax errors, invalid selectors, duplicate styles  
**Config:** stylelint.config.js

### Testing (All Required)

   - 1. PHPUnit
```bash
composer --working-dir=wp-content/plugins/affiliate-product-showcase test
```
**Status:** All tests passing required

   - 2. Code Coverage
```bash
composer --working-dir=wp-content/plugins/affiliate-product-showcase test-coverage
```
**Minimum Coverage:** 80% overall  
**Required Coverage:**
- Critical paths: 90%+
- Main business logic: 85%+
- Utility functions: 80%+
- Configuration: 70%+

   - 3. Frontend Tests
```bash
npm --prefix wp-content/plugins/affiliate-product-showcase run test
```
**Status:** All tests passing required

### Security Scanning (Required)

   - 1. PHP Dependencies
```bash
composer audit
composer outdated
```

   - 2. JavaScript Dependencies
```bash
npm audit
npm outdated
```

   - 3. Sensitive Data Detection
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

   - PHP Style Issues
```bash
# Auto-fix PHPCS issues
composer --working-dir=wp-content/plugins/affiliate-product-showcase phpcs -- --fix
```
**Fixable:** ~60% of PHPCS issues  
**Manual Review Required:** Yes

   - JavaScript Issues
```bash
# Auto-fix ESLint issues
npm --prefix wp-content/plugins/affiliate-product-showcase run lint:js -- --fix
```
**Fixable:** ~70% of ESLint issues  
**Manual Review Required:** Yes

   - CSS Issues
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

---

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
