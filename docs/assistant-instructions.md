# Assistant Instructions

### Project Context

**Project:** Affiliate Product Showcase WordPress Plugin
**Framework:** Modern WordPress Plugin Boilerplate (Tailwind + Vite + PSR-4 + Security & Cache Ready)
**Quality Standard:** Hybrid Quality Matrix - Enterprise-grade where it matters, practical everywhere
**Architecture:** DI container, event-driven architecture, REST API, Gutenberg blocks
**Tech Stack:** PHP 8.0+, JavaScript/React, Vite, Tailwind CSS
**Code Quality:** PHPUnit, CodeSniffer, Psalm level 4-5

## Behavior Preferences

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

### Default Recommendations

**Always provide proactive recommendations after code changes, file modifications, or feature implementations.**

Include following sections in outputs:

####1. Code Quality Suggestions
- Refactoring opportunities
- Performance optimizations
- Security enhancements
- Best practice improvements

####2. Next Steps
- Immediate follow-up actions
- Related features to consider
- Testing recommendations

####3. Related Features
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
- Task is purely informational (no code changes)
- User provides clear instruction to omit them

---

## Specialized Reference Guides

When working on specific domains, refer to these comprehensive guides:

### Performance Optimization
**Guide:** [Performance Optimization Guide](docs/performance-optimization-guide.md)

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

**Usage:** Copy standard assessment format from guide and fill in your specific analysis. Use scorecard to track progress and prioritize improvements.

### Quality Standards
**Guide:** [Assistant Quality Standards](docs/assistant-quality-standards.md)

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
