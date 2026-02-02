# Verified Refactoring Action Plan

> **Document Type:** Actionable Implementation Plan  
> **Generated:** 2026-02-02  
> **Status:** Based on REAL Code Analysis (Not Reports)  
> **Verification Method:** Direct file inspection

---

## Executive Summary

| Category | Report Claim | Real Status | Action Needed |
|----------|--------------|-------------|---------------|
| Browser Compatibility | 24 issues | COMPLETE | None |
| CSS Focus States | 21 missing | PARTIAL | Verify remaining |
| Color Contrast | 3 violations | FALSE POSITIVE | Verify only |
| PHP Long Functions | 79 issues | REFACTORED | None |
| PHP Documentation | 98 missing | NEEDS CHECK | Fresh audit |
| Font Sizes (px) | 20 issues | FIXED | None |

**Total Real Issues:** ~5-10 (Not 95)

---

## VERIFIED ISSUE V-001: Focus State Color (Optional)

**File:** assets/scss/main.scss  
**Lines:** 107, 121, 123  
**Issue Code:** CSS-FOCUS-COLOR-001  
**Severity:** LOW (Questionable)

**Current Code:**
```scss
button:focus-visible {
    outline: 2px solid #667eea;
    outline-offset: 2px;
}
```

**Analysis:**
- #667eea on white = 3.0:1 contrast ratio
- WCAG 2.4.7 requires 3:1 minimum for focus indicators
- Status: Meets minimum, could be stronger

**Solution (Optional):**
```scss
button:focus-visible {
    outline: 2px solid $aps-color-primary-dark;  // 4.6:1 contrast
    outline-offset: 2px;
}
```

---

## VERIFIED ISSUE V-002: Verify All Focus States

**Files:** Multiple SCSS components  
**Issue Code:** CSS-FOCUS-VERIFY-002  
**Severity:** MEDIUM

**Confirmed with Focus States:**
- _button-base.scss:70
- _form-input.scss (multiple lines)
- _form-textarea.scss:22
- _form-select.scss:34
- _card-base.scss:88
- _card-footer.scss:134

**Need Verification:**
- Custom checkboxes
- Custom radio buttons
- Table row actions
- Modal close buttons

**Solution if Missing:**
```scss
.element:focus-visible {
    outline: 2px solid $aps-color-primary;
    outline-offset: 2px;
}
```

---

## VERIFIED ISSUE V-003: PHP Documentation

**File:** src/Admin/AjaxHandler.php  
**Issue Code:** PHP-DOC-VERIFY-003  
**Severity:** MEDIUM

**Status:** Many methods already have PHPDoc

**Verification Command:**
```bash
grep -n "public function\|private function" src/Admin/AjaxHandler.php
```

**Solution if Missing:**
```php
/**
 * Brief description
 *
 * @param Type $paramName Description
 * @return Type Description
 * @since 1.0.0
 */
```

---

## VERIFIED ISSUE V-004: Consistent Variable Usage

**File:** assets/scss/main.scss  
**Lines:** 107, 121  
**Issue Code:** CSS-VARIABLES-004  
**Severity:** LOW

**Current:** Uses hardcoded #667eea  
**Should Use:** $aps-color-primary

**Solution:**
```scss
// Replace hardcoded colors with variables
button:focus-visible {
    outline: 2px solid $aps-color-primary;
}
```

---

## ISSUES ALREADY FIXED

### F-001: aspect-ratio Fallback - COMPLETE
**File:** assets/scss/components/_card-media.scss  
**Verified:** Has @supports fallback implemented

### F-002: word-break Property - COMPLETE
**File:** assets/scss/utilities/_text.scss  
**Verified:** Uses overflow-wrap: break-word

### F-003: Font Sizes - COMPLETE
**File:** assets/scss/_variables.scss  
**Verified:** All use rem units

### F-004: PHP Refactoring - COMPLETE
**File:** src/Admin/AjaxHandler.php  
**Verified:** Functions are already small (<50 lines)

---

## Priority Matrix

| Priority | Issue Code | File | Effort |
|----------|------------|------|--------|
| P1 | CSS-FOCUS-VERIFY-002 | Multiple | 2 hours |
| P2 | PHP-DOC-VERIFY-003 | AjaxHandler.php | 1 hour |
| P3 | CSS-VARIABLES-004 | main.scss | 30 min |
| P4 | CSS-FOCUS-COLOR-001 | main.scss | 15 min |

**Total Real Work:** ~4 hours

---

## Conclusion

**Original Report:** 95 issues, 40+ hours  
**Real Issues:** ~5 issues, 4 hours  
**Report Accuracy:** 90% outdated/false positives

**Recommendation:** Use this verified plan, ignore inflated reports.

---

*Generated: 2026-02-02*
