# GitHub Settings and Branch Protection

## Branch protection for `main`
- Require pull requests for merges
- Require at least 1 approval before merge
- Require passing status checks (see below)

## Branch protection for `develop`
- Require pull requests for merges
- Recommended: require at least 1 approval for critical PRs

## Required status checks
- `phpcs` (coding standards)
- `phpstan` (static analysis)
- `phpunit` (unit tests)
- `npm build` (frontend build)

## Signed commits
- Signed commits are recommended but optional. Enforce if your team uses GPG/Signed commits.

## Merge strategy
- Use `Squash and merge` to keep history concise. Use clear PR titles and detailed descriptions.
