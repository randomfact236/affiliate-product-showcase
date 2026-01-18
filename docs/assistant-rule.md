# Assistant Git & Completion Rules

## 🚨 CRITICAL: These Rules Apply to EVERY Task

**This document consolidates the most important rules you must follow:**
1. Git operations (commit, push, branch creation)

**Read this file FIRST whenever you're unsure about Git operations.**

**Note:** For task completion format and chat history rules, see [assistant-instructions.md](assistant-instructions.md) - Default Rules section.

---

## 1. GIT OPERATIONS

### Commit and Push Rules

**RULE:** Never commit and push changes unless explicitly told to do so.

**Requirements:**
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

---

### Branch Creation Rules

**RULE:** The assistant MUST NOT create, checkout, or push any Git branch unless user explicitly instructs it to do so in a direct prompt.

**Requirements:**
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

---

### Backup Branch Naming

**Mandatory Format:** When creating a manual backup branch (e.g., requested by user), branch name MUST use format:
```
backup-YYYY-MM-DD-HHMM
```

**Examples:**
- `backup-2026-01-16-1430` (January 16, 2026 at 2:30 PM)
- `backup-2026-12-25-0915` (December 25, 2026 at 9:15 AM)

---

## 2. QUICK REFERENCE CHECKLIST

### Before Any Git Operation:

- [ ] Did user explicitly say "commit and push"?
- [ ] Did user explicitly say "create branch"?
- [ ] Did I ask for permission if it wasn't explicit?
- [ ] Did I confirm backup branch name format if applicable?

---

## 3. EXAMPLES

### Correct Git Operation

**User:** "Please fix the bug"
**Assistant:** "I'll fix the bug. Do you want me to commit and push these changes?"

**User:** "Yes"
**Assistant:** [Fixes bug, then commits and pushes]

---

### Correct Branch Creation

**User:** "Create a new feature branch"
**Assistant:** "I can create a branch for you. Should I create 'feature/new-pricing' and push to origin?"

**User:** "Yes, create feature/new-pricing"
**Assistant:** [Creates branch and pushes to origin]

---
---

## 4. CONSEQUENCES

**If you violate these rules:**

- ❌ Auto-committing without permission → **VIOLATION**
- ❌ Auto-creating branches without explicit instruction → **VIOLATION**

**Result:**
- User will reject your response
- You must retry with correct format
- Work may need to be redone
- Trust is lost

---
---

## 5. SUMMARY

**Git Rules:**
- Never auto-commit or auto-push
- Never auto-create, auto-checkout, or auto-push branches
- Always ask for explicit permission
- Backup branches must follow `backup-YYYY-MM-DD-HHMM` format

**Note:** For task completion format and chat history rules, see [assistant-instructions.md](assistant-instructions.md) - Default Rules section.

**REMEMBER: These rules are NOT optional. They are MANDATORY.**
