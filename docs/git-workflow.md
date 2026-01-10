# Git Workflow

## Git Flow overview
We use a simple Git flow based on `main` (production) and `develop` (active development). Feature work is developed on `feature/*` branches, merged into `develop`, and then promoted to `main` via release branches and PRs.

## Branch naming conventions
- `feature/*` - New features (e.g., `feature/product-grid`)
- `hotfix/*` - Critical fixes (e.g., `hotfix/security-patch`)
- `release/*` - Version releases (e.g., `release/1.0.0`)

## Workflow
1. Create branch from `develop`: `feature/your-feature`
2. Work, commit, open PR into `develop`
3. After review and CI, merge into `develop`
4. When ready for release, create `release/x.y.z` from `develop`, test, then merge into `main` and tag
5. Hotfixes branch from `main` (`hotfix/x.y.z`), merge into `main` and `develop`

## Commit message format
We follow Conventional Commits for clarity and automated changelog generation. Format:

```
<type>(scope?): <description>

[optional body]

[optional footer]
```

Common types: `feat`, `fix`, `docs`, `chore`, `refactor`, `perf`, `test`.

## Examples of good commits
- `feat(blocks): add product-grid block with responsive layout`
- `fix(api): validate affiliate links before saving`
- `docs(readme): document new settings page`

Include clear descriptions and reference issue numbers where applicable.
