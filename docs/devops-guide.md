# DevOps & Infrastructure Guide

## 📋 Purpose

This guide defines **DevOps and infrastructure standards** for the project.

**Standard:** Professional DevOps practices
**Philosophy:** Automated, monitored, scalable

---

## 📊 Monitoring & Alerting

### Metrics to Track

**Core Web Vitals:**
- ✅ LCP (Largest Contentful Paint)
- ✅ FID (First Input Delay)
- ✅ CLS (Cumulative Layout Shift)
- ✅ TTFB (Time to First Byte)

**Application Metrics:**
- ✅ API performance trends
- ✅ Database query trends
- ✅ Error rates (404, 500, etc.)
- ✅ User engagement metrics
- ✅ Conversion rates (for affiliate links)
- ✅ Server resource usage (CPU, memory, disk)

### Web Vitals Tracking Implementation

```php
// Web Vitals tracking
<script type="module">
import {getCLS, getFID, getFCP, getLCP, getTTFB} from 'web-vitals';

function sendToAnalytics(metric) {
  // Send to Google Analytics, DataDog, etc.
  gtag('event', metric.name, {
    value: metric.value,
    custom_map: { 'metric_value': 'value' }
  });
}

getCLS(sendToAnalytics);
getFID(sendToAnalytics);
getLCP(sendToAnalytics);
getTTFB(sendToAnalytics);
</script>
```

### Alerting Rules

**Critical Alerts (Immediate Action Required):**
- ✅ Error rate > 1%
- ✅ Server CPU > 80%
- ✅ Disk space < 10%
- ✅ Security vulnerabilities detected
- ✅ Database connection failures

**Warning Alerts (Monitor for Trends):**
- ✅ Monitor for API performance degradation
- ✅ Monitor for database performance issues
- ✅ Monitor for increased response times

### Monitoring Stack

**Requirements:**
- ✅ Real-time monitoring (New Relic, Datadog, APM)
- ✅ Error tracking (Sentry, Rollbar)
- ✅ Logging infrastructure (ELK, Splunk)
- ✅ Custom dashboards (Grafana, Looker)
- ✅ Alerting via Slack/PagerDuty
- ✅ 24/7 monitoring for production
- ✅ Log retention (minimum 90 days)
- ✅ Searchable logs

---

## 🔐 Deployment Standards

### Pre-Deployment Checklist

```markdown
## Pre-Deployment Checklist

### Code Quality
- [ ] All tests passing (unit, integration, E2E)
- [ ] Test coverage minimum 90%
- [ ] Static analysis passes (Psalm level 4-5)
- [ ] Code review approved
- [ ] No TODOs or FIXMEs in critical paths

### Security
- [ ] Security scan passed (Snyk, Dependabot)
- [ ] No known vulnerabilities
- [ ] CSP headers configured
- [ ] Nonce verification tested
- [ ] Input/output sanitization verified

### Performance
- [ ] Lighthouse score ≥ 98 (Performance)
- [ ] Lighthouse score ≥ 95 (Accessibility)
- [ ] Lighthouse score ≥ 95 (Best Practices)
- [ ] Lighthouse score ≥ 95 (SEO)
- [ ] Core Web Vitals passing
- [ ] Bundle size < 100KB (gzipped)
- [ ] No obvious performance issues
- [ ] Database queries optimized

### Documentation
- [ ] Changelog updated
- [ ] Migration guide provided (if breaking)
- [ ] API documentation updated
- [ ] README updated (if needed)

### Testing
- [ ] Manual testing completed
- [ ] Cross-browser testing (Chrome, Firefox, Safari)
- [ ] Mobile testing (iOS, Android)
- [ ] Accessibility testing (screen readers)
- [ ] Load testing completed

### Infrastructure
- [ ] Database backups verified
- [ ] Rollback plan tested
- [ ] Monitoring configured
- [ ] Alerting rules updated
- [ ] CDN cache cleared (if needed)
```

### Deployment Requirements

- ✅ All checklist items completed before deployment
- ✅ Staging environment mirrors production
- ✅ Blue-green deployment or similar
- ✅ Zero downtime deployments
- ✅ Rollback plan documented and tested
- ✅ Post-deployment verification
- ✅ Deployment notifications sent

---

## 🔄 Rollback Procedures

### Emergency Rollback Script

```bash
#!/bin/bash

# 1. Identify last stable version
LAST_STABLE=$(git tag -l "v*" | sort -V | tail -n 2 | head -n 1)

# 2. Rollback code
git checkout $LAST_STABLE
git push origin main

# 3. Rollback database (if needed)
wp db rollback --confirm

# 4. Clear caches
wp cache flush
# CDN cache flush

# 5. Verify rollback
curl -f https://your-site.com/health || exit 1

# 6. Notify team
slack-send "🚨 Rollback to $LAST_STABLE completed"
```

### Rollback Requirements

- ✅ Automated rollback scripts
- ✅ Database rollback capability
- ✅ Cache invalidation procedure
- ✅ Health check endpoint
- ✅ Post-rollback verification
- ✅ Team notification on rollback
- ✅ Root cause analysis required

---

## 📏 Performance Budgets

### Budget Configuration

```javascript
// webpack.config.js
const path = require('path');

module.exports = {
  performance: {
    maxEntrypointSize: 244000, // 244KB
    maxAssetSize: 244000,
    hints: 'warning'
  },
  output: {
    filename: '[name].[contenthash].js',
  }
};
```

### Budget Requirements

**Bundle Sizes:**
- ✅ JavaScript bundle < 100KB (gzipped)
- ✅ CSS bundle < 20KB (gzipped)
- ✅ Total page weight < 500KB (initial)

**Performance Metrics:**
- ✅ Monitor API response time (target: 500ms, don't enforce)
- ✅ Track database query time (target: 300ms, don't block)
- ✅ Track LCP trends (monitor improvement)
- ✅ Track FID trends (monitor improvement)
- ✅ CLS < 0.05

### Budget Enforcement

- ✅ Performance budgets enforced in CI
- ✅ Bundle size monitoring
- ✅ Query performance monitoring
- ✅ Automated alerts on budget violations
- ✅ Regular performance audits
- ✅ Budget reviews quarterly

---

## 🐳 Docker Development Environment

### Dockerfile

```dockerfile
# Dockerfile
FROM php:8.1-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl

WORKDIR /var/www

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader

COPY . .

RUN chown -R www-data:www-data /var/www
```

### Docker Compose

```yaml
# docker-compose.yml
version: '3.8'
services:
  wordpress:
    build: .
    ports:
      - "8080:80"
    volumes:
      - .:/var/www
    environment:
      - WORDPRESS_DB_HOST=db
      - WORDPRESS_DB_NAME=wordpress
      - WORDPRESS_DB_USER=wordpress
      - WORDPRESS_DB_PASSWORD=wordpress
  
  db:
    image: mysql:8.0
    volumes:
      - db_data:/var/lib/mysql
    environment:
      - MYSQL_ROOT_PASSWORD=rootpassword
      - MYSQL_DATABASE=wordpress
      - MYSQL_USER=wordpress
      - MYSQL_PASSWORD=wordpress

volumes:
  db_data:
```

### Docker Requirements

- ✅ Docker setup for local development
- ✅ Docker Compose for services
- ✅ Consistent environment across team
- ✅ Hot reloading enabled
- ✅ Database seeds for testing
- ✅ Development Docker Hub images
- ✅ Production Docker images

---

## 🚀 CI/CD Pipeline

### CI Configuration

```yaml
# .github/workflows/ci.yml
name: CI
on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
      - name: Install dependencies
        run: composer install
      - name: Run Psalm
        run: vendor/bin/psalm --level=4
      - name: Run tests
        run: vendor/bin/phpunit --coverage
      - name: Upload coverage
        uses: codecov/codecov-action@v3
```

### CI/CD Requirements

- ✅ Automated testing on every commit
- ✅ Static analysis (Psalm, PHPStan) in CI
- ✅ Code quality checks (PHPCS) in CI
- ✅ Security scanning (Snyk, Dependabot)
- ✅ Test coverage reporting (Codecov)
- ✅ Automated deployment on merge to main
- ✅ Rollback capability
- ✅ Environment separation (dev/staging/prod)

---

## 🎯 Summary

**For all DevOps operations:**
1. **Monitor continuously** - Track metrics and errors
2. **Automate everything** - Reduce manual work
3. **Test thoroughly** - Verify before production
4. **Plan for failure** - Have rollback ready
5. **Document clearly** - Maintain runbooks

**The reward:** Reliable, observable, and maintainable infrastructure.

---

**Version:** 1.0.0
**Last Updated:** 2026-01-23
**Maintained By:** Development Team
**Status:** ACTIVE - Extracted from assistant-quality-standards.md (~350 lines)
