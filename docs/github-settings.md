# GitHub Branch Protection & Settings

This file documents recommended branch protection rules and required status checks for repository settings (GitHub).

Protect `main`
- Disallow direct pushes; require pull requests.
- Require status checks to pass before merging (see list below).
- Require at least 1 approving review.
- Require signed commits (recommended — optional toggle).

Protect `develop`
- Require pull requests for merges.
- Require at least 1 approving review.
- Require status checks to pass before merging.

Required status checks (examples)
- `phpcs` — PHP code style checks
- `phpstan` — Static analysis
- `phpunit` — Unit tests
- `npm: build` — Frontend build step
- `lint` — Combined JS/CSS linter

Additional recommendations
- Enforce branch protection for release branches where applicable.
- Enable `Require linear history` to keep history clean if desired.
- Configure required reviewers and CODEOWNERS for critical paths.

How to apply (GitHub UI)
1. Go to `Settings` → `Branches` → `Add rule`.
2. Enter the branch name pattern (e.g., `main`), then enable the checks above.
