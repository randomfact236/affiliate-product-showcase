# package-lock.json Policy

## Audit Finding (Issue #6): package-lock.json File Presence

**Current Status:** The repository includes `package-lock.json` file.

**Decision:** This is **ACCEPTABLE** and follows modern JavaScript/Node.js best practices.

## Rationale

### 1. Deterministic Builds (Recommended)

**Security Benefit:**
- `package-lock.json` ensures exact versions of dependencies are installed across all environments
- Prevents supply chain attacks by locking dependency tree to known-good versions
- Provides a complete dependency audit trail for security reviews

**Reproducibility:**
- All developers and CI/CD pipelines install identical dependency trees
- Eliminates "works on my machine" issues caused by version drift
- Production deployments match development environment exactly

### 2. Modern Best Practices

Since npm v5.x (2017), `package-lock.json` is the standard:
- Automatically generated and maintained by npm
- Required for `npm ci` (clean install) in CI/CD pipelines
- Officially recommended by npm and Node.js documentation
- Standard practice in professional JavaScript projects

### 3. WordPress VIP/Enterprise Standards

For WordPress VIP and Enterprise deployments:
- **VIP requires:** Deterministic builds are required for security and stability
- **Enterprise requires:** Reproducible builds for compliance and auditing
- **CI/CD requirements:** `npm ci` is faster and more reliable than `npm install`

### 4. Version Control Benefits

**Advantages of committing package-lock.json:**
- Track dependency changes in git history
- Easy rollback if new dependency versions cause issues
- Security auditing of dependency tree over time
- Automated tools can scan for vulnerabilities in committed lockfile

**Addressing Common Concerns:**
- **File size:** Modern git handles large files efficiently; size is negligible compared to benefits
- **Merge conflicts:** Rare in practice; resolved like any other dependency conflict
- **Team coordination:** Actually improves coordination by ensuring consistent versions

## Implementation Guidelines

### For CI/CD Pipelines

Always use `npm ci` instead of `npm install`:
```yaml
- name: Install dependencies
  run: npm ci  # Faster, uses package-lock.json
```

### For Development

```bash
# Initial install or after pulling package-lock.json changes
npm install

# When adding new dependencies
npm install <package>  # Automatically updates package-lock.json

# When updating dependencies
npm update  # Updates packages and package-lock.json

# For clean install (matches CI/CD)
rm -rf node_modules
npm ci
```

### For Security Audits

```bash
# Check for vulnerabilities
npm audit

# Fix vulnerabilities automatically
npm audit fix

# Check dependencies against lockfile
npm ci --dry-run
```

## Git Configuration

Ensure `package-lock.json` is tracked (not in `.gitignore`):

```bash
# Should NOT be in .gitignore
# package-lock.json

# Should be in .gitattributes
package-lock.json text eol=lf
```

## References

- [npm Documentation: package-lock.json](https://docs.npmjs.com/cli/v7/configuring-npm/package-lock-json)
- [npm ci documentation](https://docs.npmjs.com/cli/v7/commands/npm-ci)
- [WordPress VIP JavaScript Best Practices](https://wpvip.com/documentation/vip-go/code-review-block/js-vip/)
- [Node.js Security Best Practices](https://nodejs.org/en/docs/guides/security/)
