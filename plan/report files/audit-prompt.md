
# PROMPT: Audit Plan Scope Analysis for Topics 1.1 to 1.12


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

Note create a audited file inside the plan folder with the audit-file
--------------------------------------------------------------------