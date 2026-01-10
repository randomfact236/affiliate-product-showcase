# Git Workflow & Branch Naming

This document describes the branch structure used for the project and branch naming conventions.

Branch roles
- `main` — production-ready, protected branch. Only mergeable via pull requests.
- `develop` — integration branch for ongoing development; feature branches merge here.
- `feature/*` — short-lived branches for new features (merge into `develop`).
- `hotfix/*` — emergency fixes branched from `main` (and merged back to `main` and `develop`).
- `release/*` — release preparation branches created from `develop` for versioning and final QA.

Examples
- `feature/product-grid`
- `hotfix/security-patch`
- `release/1.0.0`

Branch lifecycle
- Create feature branches from `develop` for new work: `git checkout -b feature/your-feature develop`.
- Open PRs from `feature/*` → `develop` for review and CI.
- When ready, create a `release/*` from `develop`, run final QA, merge `release/*` → `main` and tag the release.
- Hotfixes: `git checkout -b hotfix/desc main`; after fix, merge `hotfix/*` → `main`, tag, then merge `main` → `develop`.

Pull request flow
- Use PR titles and bodies describing the change, link relevant issues, and include test instructions.
- Require at least one reviewer approval (see repository protection rules).
