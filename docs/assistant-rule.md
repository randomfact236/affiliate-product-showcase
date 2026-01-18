# Assistant Git & Completion Rules

## 🚨 CRITICAL: These Rules Apply to EVERY Task

**This document consolidates the most important rules you must follow:**
1. Git operations (commit, push, branch creation)
2. Task completion format (attempt_completion)
3. Chat history updates (mandatory)

**Read this file FIRST whenever you're unsure about these operations.**

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

## 2. TASK COMPLETION FORMAT

### Universal Output Format (MANDATORY)

**CRITICAL: ALL attempt_completion messages MUST use this format - NO EXCEPTIONS!**

```markdown
## User Request
"[Exact user message]"

## Assistant Files Used
- ✅ assistant-instructions.md (APPLIED)
- ✅ [other files as applicable]
- ❌ [files not used]

## Summary
[What was done, key findings, actions taken, recent message replies]

---
*Generated on: YYYY-MM-DD HH:MM:SS*
```

**This applies to EVERY attempt_completion:**
- ✅ **EVERY task completion message** - NO EXCEPTIONS
- ✅ **EVERY chat history entry** - NO EXCEPTIONS
- ✅ **EVERY report generation** - NO EXCEPTIONS
- ✅ **INCLUDE recent message replies** - Show conversation context

**This does NOT apply to:**
- ❌ Tool use requests (read_file, execute_command, etc.)
- ❌ Intermediate progress updates
- ❌ Clarification questions (ask_followup_question)

---

### Pre-Completion Checklist (MANDATORY)

**Before using attempt_completion, you MUST verify ALL 6 items:**

- [ ] Did I include "## User Request" with EXACT user message?
- [ ] Did I include "## Assistant Files Used" with ✅/❌ markers?
- [ ] Did I include "## Summary" section?
- [ ] Did I include timestamp at bottom in format "*Generated on: YYYY-MM-DD HH:MM:SS*"?
- [ ] Is format EXACTLY as specified above?
- [ ] **CRITICAL: Did I UPDATE the chat history file with this task summary?**

**If answer is NO to ANY item → STOP. DO NOT use attempt_completion. FIX IT FIRST.**

---

### Assistant Files Used Section

**CRITICAL RULE: ONLY list files that WERE ACTUALLY USED**

- ✅ List assistant-instructions.md if you read it
- ✅ List assistant-quality-standards.md if you read it
- ✅ List assistant-performance-optimization.md if you read it
- ✅ Use ✅ for APPLIED files
- ✅ Use ❌ for NOT USED files
- ❌ NEVER list files you didn't read
- ❌ NEVER say "automatically included"
- ❌ NEVER list files just because they exist

**Quick Reference:**

| Task Type | Files to List |
|------------|---------------|
| Simple file read | assistant-instructions.md |
| Directory listing | assistant-instructions.md |
| Writing code | assistant-instructions.md + assistant-quality-standards.md |
| Code quality scan | assistant-instructions.md + assistant-quality-standards.md |
| Performance scan | assistant-instructions.md + assistant-quality-standards.md + assistant-performance-optimization.md |
| Git operations | assistant-instructions.md |
| Writing tests | assistant-instructions.md + assistant-quality-standards.md |
| Security review | assistant-instructions.md + assistant-quality-standards.md |

---

## 3. CHAT HISTORY UPDATES

### Mandatory Chat History Update

**🚨 CRITICAL: This is NOT optional. You MUST update chat history after EVERY attempt_completion.**

**Location:** `chat-history/` directory

**File Naming:**
- New chat session: Create new file with next sequential number
- Format: `Chat-[Number]-YYYY-MM-DD-HHMM.md`
- Example: `Chat-001-2026-01-17-2245.md`

**Storage Strategy:**
- **Latest message at the top** - Always store the most recent message at the top of the file
- **Old messages below** - Previous task summaries go below the latest message
- **Exact task summary** - Store the exact task completed summary in the history file
- **No matter how many messages** - Even if multiple messages in chat box, only store the latest message summary

---

### File Creation Rules

**New chat session:**
1. Determine next sequential number (e.g., Chat-010, Chat-011)
2. Create new file with timestamp
3. Add task summary to file

**Same chat session:**
1. Read existing chat history file
2. Add NEW entry at TOP (before previous entries)
3. Use write_to_file to save updated content

---

### MANDATORY Process

1. After completing task with attempt_completion, **IMMEDIATELY** update chat history
2. Read existing chat history file (if exists)
3. Add NEW entry at TOP (before previous entries)
4. Use write_to_file to save updated content
5. Verify file was updated successfully

**FAILURE TO UPDATE CHAT HISTORY IS A CRITICAL ERROR**

---

### Chat History File Format

```markdown
## User Request
"[Latest message]"

## Assistant Files Used
- ✅ assistant-instructions.md (APPLIED)
- ✅ assistant-quality-standards.md (APPLIED)
- ✅ assistant-performance-optimization.md (APPLIED)

## Summary
[Summary of latest message]

---
*Generated on: YYYY-MM-DD HH:MM:SS*

## User Request
"[Previous message]"

## Assistant Files Used
- ✅ assistant-instructions.md (APPLIED)
- ✅ assistant-quality-standards.md (APPLIED)
- ❌ assistant-performance-optimization.md (NOT USED)

## Summary
[Summary of previous message]

---
*Generated on: YYYY-MM-DD HH:MM:SS*
```

---

## 4. QUICK REFERENCE CHECKLIST

### Before Any Git Operation:

- [ ] Did user explicitly say "commit and push"?
- [ ] Did user explicitly say "create branch"?
- [ ] Did I ask for permission if it wasn't explicit?
- [ ] Did I confirm backup branch name format if applicable?

### Before Using attempt_completion:

- [ ] User Request section present with exact message?
- [ ] Assistant Files Used section present with ✅/❌?
- [ ] Summary section present?
- [ ] Timestamp present in correct format?
- [ ] Chat history file updated with this summary?
- [ ] Only listed files that were actually used?

### After Every Task:

- [ ] Created/updated chat history file?
- [ ] Added entry to TOP of file?
- [ ] Used correct file name format?
- [ ] Verified file saved successfully?

---

## 5. EXAMPLES

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

### Correct Task Completion

```markdown
## User Request
"Create a user authentication system"

## Assistant Files Used
- ✅ assistant-instructions.md (APPLIED)
- ✅ assistant-quality-standards.md (APPLIED)
- ❌ assistant-performance-optimization.md (NOT USED)

## Summary
Created user authentication system with login, registration, and password reset functionality. All code follows quality standards with proper error handling and validation.

**Recent Conversation:**
User: "Should I add password strength validation?"
Assistant: "Yes, password strength validation is recommended for security..."

---
*Generated on: 2026-01-18 00:51:05*
```

---

## 6. CONSEQUENCES

**If you violate these rules:**

- ❌ Skipping chat history update → **CRITICAL ERROR**
- ❌ Using wrong format for attempt_completion → **INVALID RESPONSE**
- ❌ Auto-committing without permission → **VIOLATION**
- ❌ Auto-creating branches without explicit instruction → **VIOLATION**

**Result:**
- User will reject your response
- You must retry with correct format
- Work may need to be redone
- Trust is lost

---

## 7. SUMMARY

**Git Rules:**
- Never auto-commit or auto-push
- Never auto-create, auto-checkout, or auto-push branches
- Always ask for explicit permission
- Backup branches must follow `backup-YYYY-MM-DD-HHMM` format

**Completion Rules:**
- Always use exact format for attempt_completion
- Always include User Request, Assistant Files Used, Summary
- Always include timestamp
- Always update chat history file

**Chat History Rules:**
- Always update after attempt_completion
- Latest message at the top
- Use correct file naming format
- Failure to update is a critical error

**REMEMBER: These rules are NOT optional. They are MANDATORY.**
