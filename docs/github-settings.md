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

## Signed commits (Recommended)

### Why Use Signed Commits
- Verify commit author identity
- Prevent commit impersonation
- Meet enterprise security requirements
- Required for some organizations

### Enable Signed Commits (Optional)
GitHub Settings → Repositories → [repo] → Settings → Branches:
- ☑ Require signed commits (optional, not enforced by default)

### How to Sign Commits
Developers should:
1. Generate a GPG key: https://docs.github.com/en/authentication/managing-commit-signature-verification
2. Add the key to their GitHub account
3. Configure git:
```
git config --global user.signingkey YOUR_KEY_ID
git config --global commit.gpgsign true
```

### Verification
Signed commits show a "Verified" badge on GitHub.

## Merge strategy
- Use `Squash and merge` to keep history concise. Use clear PR titles and detailed descriptions.
