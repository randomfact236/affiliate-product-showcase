#!/usr/bin/env python3
"""
Browser Compatibility Audit Script

This script analyzes SCSS/CSS and JavaScript files for browser compatibility issues including:
- Vendor prefix issues in CSS
- Deprecated CSS properties
- CSS features with limited browser support
- JavaScript ES6+ features that may need transpilation
- Missing polyfills

Output: reports/browser-compatibility-audit-report.md
"""

import os
import re
import json
from datetime import datetime, timezone
from pathlib import Path
from typing import Dict, List, Any, Set
from collections import defaultdict


# Browser support targets from vite.config.js
BROWSER_TARGETS = {
    'chrome': '>=90',
    'firefox': '>=88',
    'safari': '>=14',
    'edge': '>=90',
    'coverage': '>0.2%',
    'excluded': ['IE 11', 'op_mini all']
}

# Deprecated CSS properties (as of 2024)
DEPRECATED_CSS_PROPERTIES = {
    'filter': {'since': 'deprecated in favor of modern filters', 'alternative': 'use modern filter functions'},
    '-webkit-box-shadow': {'since': 'deprecated', 'alternative': 'use box-shadow'},
    '-moz-box-shadow': {'since': 'deprecated', 'alternative': 'use box-shadow'},
    '-webkit-border-radius': {'since': 'deprecated', 'alternative': 'use border-radius'},
    '-moz-border-radius': {'since': 'deprecated', 'alternative': 'use border-radius'},
    '-webkit-gradient': {'since': 'deprecated', 'alternative': 'use linear-gradient() or radial-gradient()'},
    '-moz-linear-gradient': {'since': 'deprecated', 'alternative': 'use linear-gradient()'},
    '-webkit-linear-gradient': {'since': 'deprecated', 'alternative': 'use linear-gradient()'},
    '-ms-linear-gradient': {'since': 'deprecated', 'alternative': 'use linear-gradient()'},
    '-o-linear-gradient': {'since': 'deprecated', 'alternative': 'use linear-gradient()'},
    'zoom': {'since': 'non-standard', 'alternative': 'use transform: scale()'},
    'user-select': {'since': 'non-standard prefixes', 'alternative': 'use unprefixed user-select'},
    '-webkit-box-sizing': {'since': 'deprecated', 'alternative': 'use box-sizing'},
    '-moz-box-sizing': {'since': 'deprecated', 'alternative': 'use box-sizing'},
    'word-break': {'since': 'break-word is non-standard', 'alternative': 'use overflow-wrap: break-word'},
}

# CSS features requiring vendor prefixes (for browsers < targets)
PREFIX_REQUIRED_FEATURES = {
    'backdrop-filter': {'webkit': 'safari < 9', 'unprefixed': 'safari >= 9, chrome >= 76, firefox >= 103'},
    'clip-path': {'webkit': 'safari < 9', 'unprefixed': 'safari >= 9, chrome >= 55, firefox >= 54'},
    'mask-image': {'webkit': 'safari < 15.4', 'unprefixed': 'safari >= 15.4, chrome >= 120'},
    'text-decoration-style': {'webkit': 'safari < 12.1', 'unprefixed': 'safari >= 12.1, chrome >= 57'},
    'text-decoration-color': {'webkit': 'safari < 12.1', 'unprefixed': 'safari >= 12.1, chrome >= 57'},
    'text-decoration-line': {'webkit': 'safari < 12.1', 'unprefixed': 'safari >= 12.1, chrome >= 57'},
    'object-fit': {'webkit': 'safari < 10.1', 'unprefixed': 'safari >= 10.1, chrome >= 31, firefox >= 36'},
    'object-position': {'webkit': 'safari < 10.1', 'unprefixed': 'safari >= 10.1, chrome >= 31, firefox >= 36'},
    'transition': {'webkit': 'safari < 9', 'unprefixed': 'safari >= 9, chrome >= 26, firefox >= 16'},
    'transform': {'webkit': 'safari < 9', 'unprefixed': 'safari >= 9, chrome >= 36, firefox >= 16'},
    'animation': {'webkit': 'safari < 9', 'unprefixed': 'safari >= 9, chrome >= 43, firefox >= 16'},
    'flexbox': {'webkit': 'safari < 9', 'unprefixed': 'safari >= 9, chrome >= 29, firefox >= 28'},
    'grid': {'webkit': 'safari < 10.1', 'unprefixed': 'safari >= 10.1, chrome >= 57, firefox >= 52'},
}

# CSS features with limited support in target browsers
LIMITED_SUPPORT_FEATURES = {
    'aspect-ratio': {'min_version': {'chrome': 88, 'firefox': 89, 'safari': 15}, 'fallback': 'use padding-bottom hack'},
    'gap': {'min_version': {'chrome': 84, 'firefox': 63, 'safari': 14.1}, 'fallback': 'use margin on children'},
    'gap (flex)': {'min_version': {'chrome': 84, 'firefox': 63, 'safari': 14.1}, 'fallback': 'use margin on children'},
    'gap (grid)': {'min_version': {'chrome': 84, 'firefox': 63, 'safari': 14.1}, 'fallback': 'use margin on children'},
    'place-items': {'min_version': {'chrome': 59, 'firefox': 45, 'safari': 11}, 'fallback': 'use align-items and justify-items'},
    'place-content': {'min_version': {'chrome': 59, 'firefox': 45, 'safari': 11}, 'fallback': 'use align-content and justify-content'},
    'place-self': {'min_version': {'chrome': 59, 'firefox': 45, 'safari': 11}, 'fallback': 'use align-self and justify-self'},
    'min-height: min-content': {'min_version': {'chrome': 57, 'firefox': 66, 'safari': 12.1}, 'fallback': 'use min-height with fixed value'},
    'min-height: max-content': {'min_version': {'chrome': 57, 'firefox': 66, 'safari': 12.1}, 'fallback': 'use min-height with fixed value'},
    'scroll-behavior': {'min_version': {'chrome': 61, 'firefox': 36, 'safari': '15.4'}, 'fallback': 'use JavaScript scroll'},
    'overscroll-behavior': {'min_version': {'chrome': 63, 'firefox': 59, 'safari': 16}, 'fallback': 'use preventDefault on scroll events'},
    'color-mix': {'min_version': {'chrome': 111, 'firefox': 113, 'safari': 16.2}, 'fallback': 'use CSS variables with opacity'},
}

# ES6+ features and their browser support
ES6_FEATURES = {
    'const': {'min_version': {'chrome': 49, 'firefox': 36, 'safari': 10}, 'transpiled': True},
    'let': {'min_version': {'chrome': 49, 'firefox': 36, 'safari': 10}, 'transpiled': True},
    'arrow functions': {'min_version': {'chrome': 45, 'firefox': 22, 'safari': 10}, 'transpiled': True},
    'template literals': {'min_version': {'chrome': 41, 'firefox': 34, 'safari': 9.1}, 'transpiled': True},
    'destructuring': {'min_version': {'chrome': 49, 'firefox': 41, 'safari': 10}, 'transpiled': True},
    'spread operator': {'min_version': {'chrome': 46, 'firefox': 36, 'safari': 10}, 'transpiled': True},
    'rest parameters': {'min_version': {'chrome': 47, 'firefox': 36, 'safari': 10}, 'transpiled': True},
    'default parameters': {'min_version': {'chrome': 49, 'firefox': 26, 'safari': 10}, 'transpiled': True},
    'classes': {'min_version': {'chrome': 49, 'firefox': 45, 'safari': 10}, 'transpiled': True},
    'modules (import/export)': {'min_version': {'chrome': 61, 'firefox': 60, 'safari': 11}, 'transpiled': True},
    'async/await': {'min_version': {'chrome': 55, 'firefox': 52, 'safari': 11}, 'transpiled': True},
    'object spread': {'min_version': {'chrome': 60, 'firefox': 55, 'safari': 11.1}, 'transpiled': True},
    'optional chaining': {'min_version': {'chrome': 80, 'firefox': 74, 'safari': 13.1}, 'transpiled': True},
    'nullish coalescing': {'min_version': {'chrome': 80, 'firefox': 72, 'safari': 13.1}, 'transpiled': True},
    'private class fields': {'min_version': {'chrome': 84, 'firefox': 90, 'safari': 15}, 'transpiled': True},
}


class BrowserCompatibilityAnalyzer:
    """Analyzes SCSS/CSS and JavaScript files for browser compatibility issues."""

    def __init__(self, scss_dir: str, js_dir: str):
        self.scss_dir = Path(scss_dir)
        self.js_dir = Path(js_dir)
        self.scss_files = list(self.scss_dir.rglob("*.scss"))
        self.css_files = list(Path(scss_dir).parent.rglob("*.css"))
        self.js_files = list(Path(js_dir).rglob("*.js"))
        self.results = {
            "analysis_date": datetime.now(timezone.utc).isoformat(),
            "browser_targets": BROWSER_TARGETS,
            "summary": {
                "total_issues": 0,
                "critical_issues": 0,
                "medium_issues": 0,
                "low_issues": 0,
                "overall_compatibility": "unknown"
            },
            "css": {
                "vendor_prefix_issues": [],
                "deprecated_properties": [],
                "limited_support_features": [],
                "missing_prefixes": []
            },
            "javascript": {
                "es6_features": [],
                "transpilation_needed": [],
                "polyfill_recommendations": []
            },
            "configuration": {
                "autoprefixer_configured": False,
                "babel_configured": False,
                "target_es_version": "unknown"
            }
        }
        self.project_root = Path(__file__).parent.parent

    def analyze(self) -> Dict[str, Any]:
        """Run all compatibility analyses."""
        print(f"Analyzing {len(self.scss_files)} SCSS files...")
        print(f"Analyzing {len(self.css_files)} CSS files...")
        print(f"Analyzing {len(self.js_files)} JavaScript files...")

        self._check_configuration()
        self._analyze_css_vendor_prefixes()
        self._analyze_css_deprecated_properties()
        self._analyze_css_limited_support()
        self._analyze_javascript_features()
        self._calculate_summary()

        return self.results

    def _check_configuration(self):
        """Check if Autoprefixer and Babel are properly configured."""
        print("Checking build configuration...")

        # Check postcss.config.js for Autoprefixer
        postcss_config = self.project_root / "wp-content" / "plugins" / "affiliate-product-showcase" / "postcss.config.js"
        if postcss_config.exists():
            try:
                with open(postcss_config, 'r', encoding='utf-8') as f:
                    content = f.read()
                    if 'autoprefixer' in content.lower():
                        self.results["configuration"]["autoprefixer_configured"] = True
            except Exception as e:
                print(f"Error reading postcss.config.js: {e}")

        # Check vite.config.js for ES target
        vite_config = self.project_root / "wp-content" / "plugins" / "affiliate-product-showcase" / "vite.config.js"
        if vite_config.exists():
            try:
                with open(vite_config, 'r', encoding='utf-8') as f:
                    content = f.read()
                    # Extract target version
                    target_match = re.search(r"target:\s*['\"]([^'\"]+)['\"]", content)
                    if target_match:
                        self.results["configuration"]["target_es_version"] = target_match.group(1)
                    # Check for Babel
                    if '@vitejs/plugin-react' in content or 'babel' in content.lower():
                        self.results["configuration"]["babel_configured"] = True
            except Exception as e:
                print(f"Error reading vite.config.js: {e}")

    def _analyze_css_vendor_prefixes(self):
        """Detect vendor prefix issues in CSS."""
        print("Analyzing CSS vendor prefixes...")

        # Patterns for vendor prefixes
        prefix_patterns = {
            'webkit': r'-webkit-([a-z-]+)\s*:',
            'moz': r'-moz-([a-z-]+)\s*:',
            'ms': r'-ms-([a-z-]+)\s*:',
            'o': r'-o-([a-z-]+)\s*:',
        }

        for css_file in self.css_files:
            try:
                with open(css_file, 'r', encoding='utf-8') as f:
                    lines = f.readlines()

                for line_num, line in enumerate(lines, 1):
                    # Skip comments
                    if '/*' in line or '*/' in line:
                        continue

                    for prefix_type, pattern in prefix_patterns.items():
                        match = re.search(pattern, line)
                        if match:
                            property_name = match.group(1)

                            # Check if unprefixed version exists
                            unprefixed_pattern = rf'{property_name}\s*:'
                            has_unprefixed = False

                            # Look ahead for unprefixed version
                            for i in range(min(5, len(lines) - line_num)):
                                if re.search(unprefixed_pattern, lines[line_num + i]):
                                    has_unprefixed = True
                                    break

                            if not has_unprefixed:
                                self.results["css"]["vendor_prefix_issues"].append({
                                    "severity": "medium",
                                    "file": str(css_file.relative_to(self.scss_dir.parent.parent.parent)),
                                    "line": line_num,
                                    "property": f"-{prefix_type}-{property_name}",
                                    "issue": "missing_unprefixed",
                                    "suggestion": f"Add unprefixed version: {property_name}"
                                })
            except Exception as e:
                print(f"Error analyzing {css_file}: {e}")

    def _analyze_css_deprecated_properties(self):
        """Detect deprecated CSS properties."""
        print("Analyzing deprecated CSS properties...")

        for scss_file in self.scss_files:
            try:
                with open(scss_file, 'r', encoding='utf-8') as f:
                    lines = f.readlines()

                for line_num, line in enumerate(lines, 1):
                    # Skip comments
                    if line.strip().startswith('//') or '/*' in line or '*/' in line:
                        continue

                    for deprecated_prop, info in DEPRECATED_CSS_PROPERTIES.items():
                        # Match property with colon
                        pattern = rf'{re.escape(deprecated_prop)}\s*:'
                        if re.search(pattern, line):
                            self.results["css"]["deprecated_properties"].append({
                                "severity": "medium",
                                "file": str(scss_file.relative_to(self.scss_dir.parent.parent.parent)),
                                "line": line_num,
                                "property": deprecated_prop,
                                "since": info['since'],
                                "alternative": info['alternative'],
                                "suggestion": f"Replace with: {info['alternative']}"
                            })
            except Exception as e:
                print(f"Error analyzing {scss_file}: {e}")

    def _analyze_css_limited_support(self):
        """Detect CSS features with limited browser support."""
        print("Analyzing CSS features with limited browser support...")

        for scss_file in self.scss_files:
            try:
                with open(scss_file, 'r', encoding='utf-8') as f:
                    lines = f.readlines()

                for line_num, line in enumerate(lines, 1):
                    # Skip comments
                    if line.strip().startswith('//') or '/*' in line or '*/' in line:
                        continue

                    for feature, info in LIMITED_SUPPORT_FEATURES.items():
                        # Check if feature is used
                        pattern = rf'{re.escape(feature)}\s*:'
                        if re.search(pattern, line, re.IGNORECASE):
                            # Check if minimum version is above target
                            min_chrome = info['min_version'].get('chrome', 0)
                            min_firefox = info['min_version'].get('firefox', 0)
                            min_safari = info['min_version'].get('safari', 0)

                            # Compare with targets
                            target_chrome = int(BROWSER_TARGETS['chrome'].replace('>=', ''))
                            target_firefox = int(BROWSER_TARGETS['firefox'].replace('>=', ''))
                            target_safari = int(BROWSER_TARGETS['safari'].replace('>=', ''))

                            needs_fallback = False
                            if isinstance(min_chrome, int) and min_chrome > target_chrome:
                                needs_fallback = True
                            if isinstance(min_firefox, int) and min_firefox > target_firefox:
                                needs_fallback = True
                            if isinstance(min_safari, int) and min_safari > target_safari:
                                needs_fallback = True

                            if needs_fallback:
                                self.results["css"]["limited_support_features"].append({
                                    "severity": "medium",
                                    "file": str(scss_file.relative_to(self.scss_dir.parent.parent.parent)),
                                    "line": line_num,
                                    "feature": feature,
                                    "min_versions": info['min_version'],
                                    "targets": BROWSER_TARGETS,
                                    "fallback": info['fallback'],
                                    "suggestion": f"Consider fallback: {info['fallback']}"
                                })
            except Exception as e:
                print(f"Error analyzing {scss_file}: {e}")

    def _analyze_javascript_features(self):
        """Analyze JavaScript for ES6+ features."""
        print("Analyzing JavaScript ES6+ features...")

        es6_patterns = {
            'const': r'\bconst\s+',
            'let': r'\blet\s+',
            'arrow functions': r'=\s*\([^)]*\)\s*=>|=>\s*\{',
            'template literals': r'`[^`]*`',
            'destructuring (array)': r'\[[^]]*\]\s*=\s*\[',
            'destructuring (object)': r'\{[^}]*\}\s*=\s*\{',
            'spread operator': r'\.\.\.',
            'rest parameters': r'\([^)]*\.\.\.',
            'default parameters': r'function\s*\([^)]*=\s*[^)]*\)',
            'class': r'\bclass\s+\w+',
            'import': r'\bimport\s+',
            'export': r'\bexport\s+',
            'async/await': r'\basync\s+|await\s+',
            'optional chaining': r'\?\.',
            'nullish coalescing': r'\?\?',
            'private class fields': r'#\w+',
        }

        for js_file in self.js_files:
            try:
                with open(js_file, 'r', encoding='utf-8') as f:
                    content = f.read()

                for feature, pattern in es6_patterns.items():
                    if re.search(pattern, content):
                        feature_info = ES6_FEATURES.get(feature, {})
                        min_versions = feature_info.get('min_version', {})
                        transpiled = feature_info.get('transpiled', False)

                        self.results["javascript"]["es6_features"].append({
                            "severity": "low",
                            "file": str(js_file.relative_to(self.scss_dir.parent.parent.parent)),
                            "feature": feature,
                            "min_versions": min_versions,
                            "transpiled": transpiled,
                            "note": "Vite with target: es2019 should handle this"
                        })
                        break  # Only report each file once
            except Exception as e:
                print(f"Error analyzing {js_file}: {e}")

    def _calculate_summary(self):
        """Calculate summary statistics."""
        css_issues = (
            len(self.results["css"]["vendor_prefix_issues"]) +
            len(self.results["css"]["deprecated_properties"]) +
            len(self.results["css"]["limited_support_features"])
        )

        js_issues = len(self.results["javascript"]["es6_features"])

        self.results["summary"]["total_issues"] = css_issues + js_issues
        self.results["summary"]["critical_issues"] = 0
        self.results["summary"]["medium_issues"] = css_issues
        self.results["summary"]["low_issues"] = js_issues

        # Determine overall compatibility
        if self.results["configuration"]["autoprefixer_configured"] and self.results["configuration"]["babel_configured"]:
            if css_issues == 0 and js_issues == 0:
                self.results["summary"]["overall_compatibility"] = "excellent"
            elif css_issues <= 5:
                self.results["summary"]["overall_compatibility"] = "good"
            else:
                self.results["summary"]["overall_compatibility"] = "fair"
        else:
            self.results["summary"]["overall_compatibility"] = "needs_improvement"

    def generate_markdown_report(self) -> str:
        """Generate a markdown report from analysis results."""
        lines = []

        # Header
        lines.append("# Browser Compatibility Audit Report")
        lines.append("")
        lines.append(f"**Analysis Date**: {self.results['analysis_date'].split('T')[0]}")
        lines.append("**Project**: Affiliate Product Showcase Plugin")
        lines.append("")
        lines.append("---")
        lines.append("")

        # Executive Summary
        lines.append("## Executive Summary")
        lines.append("")
        lines.append("| Metric | Value |")
        lines.append("|--------|-------|")
        lines.append(f"| Total Issues | {self.results['summary']['total_issues']} |")
        lines.append(f"| Critical Issues | {self.results['summary']['critical_issues']} |")
        lines.append(f"| Medium Issues | {self.results['summary']['medium_issues']} |")
        lines.append(f"| Low Issues | {self.results['summary']['low_issues']} |")
        lines.append(f"| Overall Compatibility | {self.results['summary']['overall_compatibility'].replace('_', ' ').title()} |")
        lines.append("")

        # Browser Targets
        lines.append("### Browser Targets")
        lines.append("")
        lines.append("| Browser | Minimum Version |")
        lines.append("|---------|-----------------|")
        for browser, version in BROWSER_TARGETS.items():
            if browser not in ['coverage', 'excluded']:
                lines.append(f"| {browser.title()} | {version} |")
        lines.append("")
        lines.append(f"| Coverage | {BROWSER_TARGETS['coverage']} |")
        lines.append(f"| Excluded | {', '.join(BROWSER_TARGETS['excluded'])} |")
        lines.append("")

        # Configuration Status
        lines.append("### Build Configuration")
        lines.append("")
        lines.append("| Tool | Status |")
        lines.append("|------|--------|")
        lines.append(f"| Autoprefixer | {'✅ Configured' if self.results['configuration']['autoprefixer_configured'] else '❌ Not Configured'} |")
        lines.append(f"| Babel/Transpilation | {'✅ Configured' if self.results['configuration']['babel_configured'] else '❌ Not Configured'} |")
        lines.append(f"| ES Target | {self.results['configuration']['target_es_version']} |")
        lines.append("")
        lines.append("---")
        lines.append("")

        # CSS Findings
        lines.append("## CSS Compatibility Analysis")
        lines.append("")

        # Vendor Prefix Issues
        lines.append("### 1. Vendor Prefix Issues")
        lines.append("")
        if self.results['css']['vendor_prefix_issues']:
            lines.append(f"**Issues Found**: {len(self.results['css']['vendor_prefix_issues'])}")
            lines.append("")
            for i, issue in enumerate(self.results['css']['vendor_prefix_issues'][:10], 1):
                lines.append(f"#### Issue #{i}")
                lines.append(f"- **File**: `wp-content/plugins/{issue['file']}`")
                lines.append(f"- **Line**: {issue['line']}")
                lines.append(f"- **Property**: `{issue['property']}`")
                lines.append(f"- **Issue**: Missing unprefixed version")
                lines.append(f"- **Suggestion**: {issue['suggestion']}")
                lines.append("")
            if len(self.results['css']['vendor_prefix_issues']) > 10:
                lines.append(f"... and {len(self.results['css']['vendor_prefix_issues']) - 10} more issues")
                lines.append("")
        else:
            lines.append("**Status**: ✅ No issues found")
            lines.append("")
            lines.append("No vendor prefix issues detected. Autoprefixer should handle vendor prefixes automatically.")
        lines.append("")
        lines.append("---")
        lines.append("")

        # Deprecated Properties
        lines.append("### 2. Deprecated CSS Properties")
        lines.append("")
        if self.results['css']['deprecated_properties']:
            lines.append(f"**Issues Found**: {len(self.results['css']['deprecated_properties'])}")
            lines.append("")
            for i, issue in enumerate(self.results['css']['deprecated_properties'][:10], 1):
                lines.append(f"#### Issue #{i}")
                lines.append(f"- **File**: `wp-content/plugins/{issue['file']}`")
                lines.append(f"- **Line**: {issue['line']}")
                lines.append(f"- **Property**: `{issue['property']}`")
                lines.append(f"- **Since**: {issue['since']}")
                lines.append(f"- **Alternative**: {issue['alternative']}")
                lines.append(f"- **Suggestion**: {issue['suggestion']}")
                lines.append("")
            if len(self.results['css']['deprecated_properties']) > 10:
                lines.append(f"... and {len(self.results['css']['deprecated_properties']) - 10} more issues")
                lines.append("")
        else:
            lines.append("**Status**: ✅ No deprecated properties found")
            lines.append("")
            lines.append("No deprecated CSS properties detected.")
        lines.append("")
        lines.append("---")
        lines.append("")

        # Limited Support Features
        lines.append("### 3. CSS Features with Limited Browser Support")
        lines.append("")
        if self.results['css']['limited_support_features']:
            lines.append(f"**Issues Found**: {len(self.results['css']['limited_support_features'])}")
            lines.append("")
            for i, issue in enumerate(self.results['css']['limited_support_features'][:10], 1):
                lines.append(f"#### Issue #{i}")
                lines.append(f"- **File**: `wp-content/plugins/{issue['file']}`")
                lines.append(f"- **Line**: {issue['line']}")
                lines.append(f"- **Feature**: `{issue['feature']}`")
                lines.append(f"- **Minimum Versions**: {issue['min_versions']}")
                lines.append(f"- **Fallback**: {issue['fallback']}")
                lines.append(f"- **Suggestion**: {issue['suggestion']}")
                lines.append("")
            if len(self.results['css']['limited_support_features']) > 10:
                lines.append(f"... and {len(self.results['css']['limited_support_features']) - 10} more issues")
                lines.append("")
        else:
            lines.append("**Status**: ✅ No limited support features found")
            lines.append("")
            lines.append("All CSS features used are fully supported by target browsers.")
        lines.append("")
        lines.append("---")
        lines.append("")

        # JavaScript Findings
        lines.append("## JavaScript Compatibility Analysis")
        lines.append("")

        lines.append("### ES6+ Features Detected")
        lines.append("")
        if self.results['javascript']['es6_features']:
            lines.append(f"**Files with ES6+ features**: {len(self.results['javascript']['es6_features'])}")
            lines.append("")
            lines.append("The following ES6+ features were detected:")
            lines.append("")
            features_found = set()
            for issue in self.results['javascript']['es6_features']:
                features_found.add(issue['feature'])
            for feature in sorted(features_found):
                feature_info = ES6_FEATURES.get(feature, {})
                lines.append(f"- **{feature}**: {feature_info.get('min_versions', {})}")
            lines.append("")
            lines.append("**Note**: Vite is configured with `target: 'es2019'`, which means:")
            lines.append("- All ES2019 and earlier features are natively supported")
            lines.append("- Features requiring ES2020+ will be transpiled")
            lines.append("- Babel (via @vitejs/plugin-react) handles transpilation")
            lines.append("")
        else:
            lines.append("**Status**: ✅ No ES6+ features detected")
            lines.append("")
            lines.append("All JavaScript code appears to use ES5-compatible syntax.")
        lines.append("")
        lines.append("---")
        lines.append("")

        # Recommendations
        lines.append("## Recommendations")
        lines.append("")

        if not self.results['configuration']['autoprefixer_configured']:
            lines.append("### Critical")
            lines.append("")
            lines.append("1. **Configure Autoprefixer**")
            lines.append("   - Add autoprefixer to postcss.config.js")
            lines.append("   - This will automatically add vendor prefixes based on browser targets")
            lines.append("   - Example configuration:")
            lines.append("   ```javascript")
            lines.append("   export default {")
            lines.append("     plugins: {")
            lines.append("       tailwindcss: {},")
            lines.append("       autoprefixer: {}")
            lines.append("     }")
            lines.append("   }")
            lines.append("   ```")
            lines.append("")

        if self.results['css']['deprecated_properties']:
            lines.append("### High Priority")
            lines.append("")
            lines.append("2. **Replace Deprecated CSS Properties**")
            lines.append("   - Review and replace all deprecated CSS properties")
            lines.append("   - Use modern alternatives as suggested in the findings")
            lines.append("   - Test in all target browsers after replacement")
            lines.append("")

        if self.results['css']['limited_support_features']:
            lines.append("### Medium Priority")
            lines.append("")
            lines.append("3. **Add Fallbacks for Limited Support Features**")
            lines.append("   - Consider adding CSS feature detection")
            lines.append("   - Provide fallbacks for older browsers")
            lines.append("   - Use @supports queries to detect feature support")
            lines.append("")

        if not self.results['configuration']['babel_configured']:
            lines.append("### Medium Priority")
            lines.append("")
            lines.append("4. **Configure JavaScript Transpilation**")
            lines.append("   - Ensure Babel is properly configured for ES2019 target")
            lines.append("   - Verify @vitejs/plugin-react is installed")
            lines.append("   - Test transpiled output in older browsers")
            lines.append("")

        if self.results['summary']['total_issues'] == 0:
            lines.append("**No specific recommendations at this time. The codebase is well-configured for browser compatibility.**")
            lines.append("")

        lines.append("---")
        lines.append("")

        # Conclusion
        lines.append("## Conclusion")
        lines.append("")

        if self.results['summary']['total_issues'] == 0:
            lines.append("The codebase has excellent browser compatibility. All CSS and JavaScript features are properly supported by the target browsers.")
            lines.append("")
            lines.append("**Overall Compatibility Grade**: A+ (Excellent)")
        elif self.results['summary']['total_issues'] <= 5:
            lines.append("The codebase has good browser compatibility with minor issues that should be addressed.")
            lines.append("")
            lines.append("**Overall Compatibility Grade**: B+ (Good)")
        elif self.results['summary']['total_issues'] <= 15:
            lines.append("The codebase has fair browser compatibility. Several issues should be addressed to ensure consistent behavior across target browsers.")
            lines.append("")
            lines.append("**Overall Compatibility Grade**: B (Fair)")
        else:
            lines.append("The codebase needs improvement in browser compatibility. Multiple issues should be addressed to ensure proper functionality across all target browsers.")
            lines.append("")
            lines.append("**Overall Compatibility Grade**: C (Needs Improvement)")

        lines.append("")
        lines.append("---")
        lines.append("")
        lines.append("**Report Generated By**: `scripts/browser-compatibility-audit.py`")
        lines.append("**Analysis Method**: Automated CSS/JS pattern detection with browser support data")

        return "\n".join(lines)


def main():
    """Main entry point."""
    # Set paths
    project_root = Path(__file__).parent.parent
    scss_dir = project_root / "wp-content" / "plugins" / "affiliate-product-showcase" / "assets" / "scss"
    js_dir = project_root / "wp-content" / "plugins" / "affiliate-product-showcase" / "assets" / "js"
    report_dir = project_root / "reports"
    report_file = report_dir / "browser-compatibility-audit-report.md"

    # Ensure report directory exists
    report_dir.mkdir(exist_ok=True)

    # Run analysis
    analyzer = BrowserCompatibilityAnalyzer(str(scss_dir), str(js_dir))
    results = analyzer.analyze()

    # Generate and save markdown report
    markdown_report = analyzer.generate_markdown_report()
    with open(report_file, 'w', encoding='utf-8') as f:
        f.write(markdown_report)

    print(f"\n{'='*60}")
    print(f"Browser Compatibility Audit Complete")
    print(f"{'='*60}")
    print(f"Report saved to: {report_file}")
    print(f"\nSummary:")
    print(f"  Total Issues: {results['summary']['total_issues']}")
    print(f"  Critical Issues: {results['summary']['critical_issues']}")
    print(f"  Medium Issues: {results['summary']['medium_issues']}")
    print(f"  Low Issues: {results['summary']['low_issues']}")
    print(f"  Overall Compatibility: {results['summary']['overall_compatibility']}")
    print(f"\nConfiguration:")
    print(f"  Autoprefixer: {'✅' if results['configuration']['autoprefixer_configured'] else '❌'}")
    print(f"  Babel/Transpilation: {'✅' if results['configuration']['babel_configured'] else '❌'}")
    print(f"  ES Target: {results['configuration']['target_es_version']}")
    print(f"\nBreakdown:")
    print(f"  CSS Vendor Prefix Issues: {len(results['css']['vendor_prefix_issues'])}")
    print(f"  Deprecated CSS Properties: {len(results['css']['deprecated_properties'])}")
    print(f"  Limited Support Features: {len(results['css']['limited_support_features'])}")
    print(f"  ES6+ Features Detected: {len(results['javascript']['es6_features'])}")
    print(f"{'='*60}\n")


if __name__ == "__main__":
    main()
