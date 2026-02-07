# Documentation Validation Guide

**Purpose:** Ensure all documentation references are valid and up-to-date.

## Validation Rules

### 1. README.md Links

All markdown links to documentation files must reference existing files:

```bash
# Check for broken links
grep -oP '\(docs/[a-z-]+\.md\)' README.md | while read link; do
  if [ ! -f "wp-content/plugins/affiliate-product-showcase/$link" ]; then
    echo "BROKEN LINK: $link"
  fi
done
```

### 2. plugin-structure.md Accuracy

The `Plugin Structure List Format` section must match actual directory structure.

**Verification Steps:**
1. List actual files in each directory
2. Compare with documented files
3. Update documentation when files are added/removed

```bash
# Verify docs/ section
ls -1 docs/ | sort
# Compare with plugin-structure.md section 4
```

### 3. Pre-Commit Hook

Add to `.git/hooks/pre-commit`:

```bash
#!/bin/bash

# Check README.md for broken documentation links
echo "Checking documentation links..."

broken_links=$(grep -oP '\(docs/[a-z-]+\.md\)' README.md | while read link; do
  if [ ! -f "$link" ]; then
    echo "ERROR: Broken documentation link: $link"
    exit 1
  fi
done

if [ -n "$broken_links" ]; then
  echo "Commit blocked: Found broken documentation links"
  exit 1
fi

echo "Documentation validation passed"
exit 0
```

### 4. CI/CD Integration

Add to GitHub Actions workflow:

```yaml
- name: Validate Documentation
  run: |
    # Check for broken links in README.md
    grep -oP '\(docs/[a-z-]+\.md\)' README.md | while read link; do
      if [ ! -f "$link" ]; then
        echo "ERROR: Broken documentation link: $link"
        exit 1
      fi
    done
```

## Common Issues

### Issue 1: Adding New Documentation File

**Wrong:**
```markdown
# README.md
- [New Guide](docs/new-guide.md)  # File doesn't exist yet
```

**Right:**
```markdown
# 1. Create the file first
touch docs/new-guide.md

# 2. Add content to docs/new-guide.md

# 3. Update README.md
- [New Guide](docs/new-guide.md)

# 4. Update plugin-structure.md
# Add to section 4: docs/ list
```

### Issue 2: Removing Documentation File

**Wrong:**
```bash
# Just delete the file
rm docs/old-guide.md
# README.md still references it - BROKEN LINK!
```

**Right:**
```bash
# 1. Update README.md - remove the link
# 2. Update plugin-structure.md - remove from section 4
# 3. Delete the file
rm docs/old-guide.md
```

### Issue 3: Renaming Documentation File

**Wrong:**
```bash
# Just rename the file
mv docs/old-name.md docs/new-name.md
# README.md still references old-name.md - BROKEN LINK!
```

**Right:**
```bash
# 1. Rename the file
mv docs/old-name.md docs/new-name.md

# 2. Update all references
# - README.md
# - plugin-structure.md
# - Any other files linking to it

# 3. Verify no broken links remain
grep -r "old-name" .
```

## Automated Validation Script

Create `scripts/validate-docs.sh`:

```bash
#!/bin/bash

echo "=== Documentation Validation ==="

# Track errors
errors=0

# Check README.md links
echo "Checking README.md for broken documentation links..."
broken_links=$(grep -oP '\(docs/[a-z-]+\.md\)' README.md | while read link; do
  if [ ! -f "wp-content/plugins/affiliate-product-showcase/$link" ]; then
    echo "  ❌ BROKEN: $link"
    ((errors++))
  fi
done)

if [ $errors -eq 0 ]; then
  echo "  ✅ All README.md links valid"
else
  echo "  ❌ Found $errors broken link(s) in README.md"
fi

# Check plugin-structure.md accuracy
echo ""
echo "Checking plugin-structure.md accuracy..."

# Count files in docs/
actual_count=$(ls -1 docs/ | wc -l)

# Count files documented in plugin-structure.md section 4
doc_count=$(grep -A 20 "^### 4. docs/" plugin-structure.md | grep -E "^\- \`" | wc -l)

if [ $actual_count -eq $doc_count ]; then
  echo "  ✅ plugin-structure.md matches actual files ($actual_count files)"
else
  echo "  ❌ Mismatch: plugin-structure.md lists $doc_count files, actual count is $actual_count"
  ((errors++))
fi

# Final result
echo ""
if [ $errors -eq 0 ]; then
  echo "✅ Documentation validation PASSED"
  exit 0
else
  echo "❌ Documentation validation FAILED: $errors error(s) found"
  exit 1
fi
```

## Usage

### Before Committing

```bash
# Run validation script
bash scripts/validate-docs.sh

# If passes, commit
git add .
git commit -m "docs: update documentation"
```

### In CI/CD

```yaml
name: Documentation Validation
on: [push, pull_request]

jobs:
  validate:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Validate Documentation
        run: bash scripts/validate-docs.sh
```

## Best Practices

1. **Always create documentation files before linking them**
2. **Update all references when renaming/moving files**
3. **Remove references before deleting files**
4. **Keep plugin-structure.md in sync with actual structure**
5. **Run validation script before committing**
6. **Use pre-commit hooks to catch issues early**

## Monitoring

### Broken Link Detection

Use automated tools:

```bash
# Install markdown-link-check
npm install -g markdown-link-check

# Check all markdown files
markdown-link-check **/*.md
```

### Continuous Monitoring

Set up scheduled checks:

```yaml
# .github/workflows/docs-check.yml
name: Documentation Check
on:
  schedule:
    - cron: '0 0 * * 0'  # Weekly

jobs:
  check:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - run: bash scripts/validate-docs.sh
```

## Summary

Documentation validation ensures:
- ✅ No broken links in README.md
- ✅ plugin-structure.md matches actual files
- ✅ All references are up-to-date
- ✅ Users can access all linked documentation
- ✅ Developer experience is not degraded by broken links

**Status:** Active validation workflow
**Last Updated:** 2026-01-16
