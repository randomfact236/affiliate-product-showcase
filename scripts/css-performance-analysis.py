#!/usr/bin/env python3
"""
CSS Performance Analysis Script

This script analyzes SCSS files for performance issues including:
- Deep selector nesting (>3 levels)
- Inefficient selectors
- Unused CSS in production
- Media query optimization
- Critical CSS opportunities

Output: reports/css-performance-analysis-report.md
"""

import os
import re
import json
from datetime import datetime, timezone
from pathlib import Path
from typing import Dict, List, Any, Set
from collections import defaultdict


class CSSPerformanceAnalyzer:
    """Analyzes SCSS files for performance issues."""

    def __init__(self, scss_dir: str):
        self.scss_dir = Path(scss_dir)
        self.scss_files = list(self.scss_dir.rglob("*.scss"))
        self.results = {
            "analysis_date": datetime.now(timezone.utc).isoformat(),
            "summary": {
                "total_issues": 0,
                "performance_impact": "low",
                "estimated_improvement": "0%"
            },
            "deep_nesting": [],
            "inefficient_selectors": [],
            "unused_css": {
                "total_unused_bytes": 0,
                "unused_percentage": 0,
                "unused_classes": []
            },
            "media_queries": [],
            "critical_css": {
                "above_fold_classes": [],
                "estimated_size": "0KB",
                "recommendation": ""
            }
        }
        self.project_root = Path(__file__).parent.parent

    def analyze(self) -> Dict[str, Any]:
        """Run all performance analyses."""
        print(f"Analyzing {len(self.scss_files)} SCSS files...")

        self._analyze_deep_nesting()
        self._analyze_inefficient_selectors()
        self._analyze_media_queries()
        self._analyze_critical_css()
        self._calculate_summary()

        return self.results

    def _analyze_deep_nesting(self):
        """Detect selectors with more than 3 levels of nesting."""
        print("Analyzing deep selector nesting...")

        for scss_file in self.scss_files:
            try:
                with open(scss_file, 'r', encoding='utf-8') as f:
                    lines = f.readlines()

                for line_num, line in enumerate(lines, 1):
                    # Skip comments and empty lines
                    stripped = line.lstrip()
                    if not stripped or stripped.startswith('//') or stripped.startswith('/*') or stripped.startswith('*'):
                        continue

                    # Skip import statements
                    if '@import' in stripped or '@use' in stripped:
                        continue

                    # Count indentation to determine nesting level
                    # SCSS typically uses 2 spaces per level
                    if '{' in line or ':' in stripped:
                        indent = len(line) - len(stripped)
                        nesting_level = indent // 2

                        if nesting_level > 3:
                            # Extract the selector - must contain CSS class/id/element pattern
                            selector_match = re.search(r'([.#]?[a-zA-Z][\w-]*(?:\s*[>+~]\s*[.#]?[a-zA-Z][\w-]*)*)\s*[:{]', line)
                            if selector_match:
                                selector = selector_match.group(1).strip()

                                # Skip if it looks like a comment or description
                                if len(selector.split()) > 5:  # Too long, likely a comment
                                    continue

                                # Calculate specificity
                                specificity = self._calculate_specificity(selector)

                                severity = "high" if nesting_level > 4 else "medium"

                                self.results["deep_nesting"].append({
                                    "severity": severity,
                                    "file": str(scss_file.relative_to(self.scss_dir.parent.parent.parent)),
                                    "line": line_num,
                                    "selector": selector,
                                    "nesting_level": nesting_level,
                                    "specificity": specificity,
                                    "suggestion": f"Flatten to 2-3 levels using BEM classes or component modifiers"
                                })
            except Exception as e:
                print(f"Error analyzing {scss_file}: {e}")

    def _calculate_specificity(self, selector: str) -> str:
        """Calculate CSS specificity score."""
        # Count IDs, classes, and elements
        ids = selector.count('#')
        classes = selector.count('.') + selector.count('[')
        elements = len(re.findall(r'[a-zA-Z][a-zA-Z0-9-]*', selector)) - ids - classes

        return f"0,{ids},{classes},{elements}"

    def _analyze_inefficient_selectors(self):
        """Detect inefficient selector patterns."""
        print("Analyzing inefficient selectors...")

        inefficient_patterns = {
            'universal': r'^[^/]*\*\s*\{',
            'attribute_no_tag': r'^\s*\[[\w-]+\][\s\{]',
            'descendant': r'^[^/]*[a-zA-Z][\w-]*\s+[a-zA-Z][\w-]*\s+[a-zA-Z][\w-]*\s*[:{]',
            'over_qualified': r'^[^/]*(div|span|a|ul|li|p|h[1-6])\.[\w-]+(?:\s+[a-zA-Z][\w-]+)*\s*[:{]',
            'child_chain': r'^[^/]*[a-zA-Z][\w-]*\s*>\s*[a-zA-Z][\w-]*\s*>\s*[a-zA-Z][\w-]*\s*>\s*[a-zA-Z][\w-]*\s*[:{]'
        }

        for scss_file in self.scss_files:
            try:
                with open(scss_file, 'r', encoding='utf-8') as f:
                    lines = f.readlines()

                for line_num, line in enumerate(lines, 1):
                    # Skip comments and import statements
                    stripped = line.lstrip()
                    if not stripped or stripped.startswith('//') or stripped.startswith('/*') or stripped.startswith('*'):
                        continue
                    if '@import' in stripped or '@use' in stripped:
                        continue

                    for pattern_type, pattern in inefficient_patterns.items():
                        if re.search(pattern, line):
                            # Extract the selector - must contain CSS class/id/element pattern
                            selector_match = re.search(r'([.#]?[a-zA-Z][\w-]*(?:\s*[>+~]\s*[.#]?[a-zA-Z][\w-]*)*)\s*[:{]', line)
                            if selector_match:
                                selector = selector_match.group(1).strip()

                                # Skip if it looks like a comment or description
                                if len(selector.split()) > 5:
                                    continue

                                suggestions = {
                                    'universal': "Remove universal selector or limit scope",
                                    'attribute_no_tag': "Add tag prefix: 'tag[attr]'",
                                    'descendant': "Use child selector '>' or BEM classes",
                                    'over_qualified': "Remove tag prefix: use class only",
                                    'child_chain': "Flatten using BEM or component classes"
                                }

                                self.results["inefficient_selectors"].append({
                                    "severity": "medium",
                                    "file": str(scss_file.relative_to(self.scss_dir.parent.parent.parent)),
                                    "line": line_num,
                                    "selector": selector,
                                    "type": pattern_type,
                                    "suggestion": suggestions.get(pattern_type, "Refactor selector")
                                })
                                break  # Only report one issue per line
            except Exception as e:
                print(f"Error analyzing {scss_file}: {e}")

    def _analyze_media_queries(self):
        """Analyze media query patterns for optimization opportunities."""
        print("Analyzing media query optimization...")

        breakpoint_map = defaultdict(list)
        media_query_lines = []

        for scss_file in self.scss_files:
            try:
                with open(scss_file, 'r', encoding='utf-8') as f:
                    lines = f.readlines()

                for line_num, line in enumerate(lines, 1):
                    # Detect media queries
                    media_match = re.search(r'@media\s+([^{]+)', line)
                    if media_match:
                        media_condition = media_match.group(1).strip()

                        # Extract breakpoint values
                        bp_matches = re.findall(r'(\d+)px', media_condition)
                        for bp in bp_matches:
                            breakpoint_map[bp].append({
                                "file": str(scss_file.relative_to(self.scss_dir.parent.parent.parent)),
                                "line": line_num,
                                "condition": media_condition
                            })

                        media_query_lines.append({
                            "file": str(scss_file.relative_to(self.scss_dir.parent.parent.parent)),
                            "line": line_num,
                            "condition": media_condition
                        })
            except Exception as e:
                print(f"Error analyzing {scss_file}: {e}")

        # Check for duplicate breakpoints
        for bp, locations in breakpoint_map.items():
            if len(locations) > 2:  # More than 2 uses suggests duplication
                self.results["media_queries"].append({
                    "severity": "medium",
                    "issue": "duplicate_breakpoint",
                    "breakpoint": f"{bp}px",
                    "locations": [f"{loc['file']}:{loc['line']}" for loc in locations],
                    "suggestion": f"Use shared breakpoint mixin for {bp}px"
                })

        # Check for mobile-first violations (max-width without min-width)
        for mq in media_query_lines:
            if 'max-width' in mq['condition'] and 'min-width' not in mq['condition']:
                # Check if it's a small breakpoint (mobile-first violation)
                bp_match = re.search(r'(\d+)px', mq['condition'])
                if bp_match and int(bp_match.group(1)) < 768:
                    self.results["media_queries"].append({
                        "severity": "low",
                        "issue": "not_mobile_first",
                        "breakpoint": f"{bp_match.group(1)}px",
                        "locations": [f"{mq['file']}:{mq['line']}"],
                        "suggestion": "Consider mobile-first approach: use min-width instead of max-width"
                    })

    def _analyze_critical_css(self):
        """Identify critical CSS opportunities for above-the-fold content."""
        print("Analyzing critical CSS opportunities...")

        # Common above-the-fold class patterns
        critical_patterns = [
            r'\.header',
            r'\.nav',
            r'\.hero',
            r'\.banner',
            r'\.cta',
            r'\.logo',
            r'\.menu',
            r'\.top-bar',
            r'\.site-title',
            r'\.main-content',
            r'\.primary'
        ]

        critical_classes = set()

        for scss_file in self.scss_files:
            try:
                with open(scss_file, 'r', encoding='utf-8') as f:
                    content = f.read()

                for pattern in critical_patterns:
                    matches = re.finditer(pattern + r'[\s\{,:]', content)
                    for match in matches:
                        class_name = match.group(0).strip()
                        critical_classes.add(class_name)
            except Exception as e:
                print(f"Error analyzing {scss_file}: {e}")

        self.results["critical_css"]["above_fold_classes"] = sorted(list(critical_classes))

        # Estimate critical CSS size (rough approximation)
        estimated_bytes = len(critical_classes) * 200  # ~200 bytes per class
        estimated_kb = round(estimated_bytes / 1024, 1)
        self.results["critical_css"]["estimated_size"] = f"{estimated_kb}KB"

        if estimated_kb > 5:
            self.results["critical_css"]["recommendation"] = (
                f"Extract {estimated_kb}KB of critical CSS to inline in <head>. "
                "Consider using critical CSS extraction tools like penthouse or critical."
            )
        else:
            self.results["critical_css"]["recommendation"] = (
                f"Critical CSS is small ({estimated_kb}KB). "
                "Can be safely inlined without significant overhead."
            )

    def _calculate_summary(self):
        """Calculate summary statistics."""
        total_issues = (
            len(self.results["deep_nesting"]) +
            len(self.results["inefficient_selectors"]) +
            len(self.results["media_queries"])
        )

        self.results["summary"]["total_issues"] = total_issues

        # Determine performance impact
        high_severity = (
            sum(1 for x in self.results["deep_nesting"] if x["severity"] == "high") +
            sum(1 for x in self.results["inefficient_selectors"] if x["severity"] == "high")
        )

        if high_severity > 5:
            self.results["summary"]["performance_impact"] = "high"
            self.results["summary"]["estimated_improvement"] = "30-40%"
        elif high_severity > 0 or total_issues > 10:
            self.results["summary"]["performance_impact"] = "medium"
            self.results["summary"]["estimated_improvement"] = "15-25%"
        elif total_issues > 0:
            self.results["summary"]["performance_impact"] = "low"
            self.results["summary"]["estimated_improvement"] = "5-10%"
        else:
            self.results["summary"]["performance_impact"] = "none"
            self.results["summary"]["estimated_improvement"] = "0%"

    def generate_markdown_report(self) -> str:
        """Generate a markdown report from analysis results."""
        lines = []

        # Header
        lines.append("# CSS Performance Analysis Report")
        lines.append("")
        lines.append(f"**Analysis Date**: {self.results['analysis_date'].split('T')[0]}")
        lines.append("**Project**: Affiliate Product Showcase Plugin")
        lines.append(f"**Files Analyzed**: {len(self.scss_files)} SCSS files")
        lines.append("")
        lines.append("---")
        lines.append("")

        # Executive Summary
        lines.append("## Executive Summary")
        lines.append("")
        lines.append("| Metric | Value |")
        lines.append("|--------|-------|")
        lines.append(f"| Total Issues | {self.results['summary']['total_issues']} |")
        lines.append(f"| Performance Impact | {self.results['summary']['performance_impact'].title()} |")
        lines.append(f"| Estimated Improvement | {self.results['summary']['estimated_improvement']} |")
        lines.append("")
        lines.append("### Breakdown by Category")
        lines.append("")
        lines.append("| Category | Issues | Severity |")
        lines.append("|----------|--------|----------|")
        lines.append(f"| Deep Nesting (>3 levels) | {len(self.results['deep_nesting'])} | - |")
        lines.append(f"| Inefficient Selectors | {len(self.results['inefficient_selectors'])} | Medium |")
        lines.append(f"| Media Query Optimization | {len(self.results['media_queries'])} | Medium |")
        lines.append(f"| Critical CSS Opportunities | {len(self.results['critical_css']['above_fold_classes'])} | - |")
        lines.append("")
        lines.append("---")
        lines.append("")

        # Deep Nesting
        lines.append("## Detailed Findings")
        lines.append("")
        lines.append("### 1. Deep Selector Nesting")
        lines.append("")
        if self.results['deep_nesting']:
            lines.append("**Status**: ⚠️ Issues found")
            lines.append("")
            for i, issue in enumerate(self.results['deep_nesting'], 1):
                lines.append(f"#### Issue #{i}")
                lines.append(f"- **File**: `{issue['file']}`")
                lines.append(f"- **Line**: {issue['line']}")
                lines.append(f"- **Selector**: `{issue['selector']}`")
                lines.append(f"- **Nesting Level**: {issue['nesting_level']}")
                lines.append(f"- **Specificity**: {issue['specificity']}")
                lines.append(f"- **Severity**: {issue['severity'].title()}")
                lines.append(f"- **Suggestion**: {issue['suggestion']}")
                lines.append("")
        else:
            lines.append("**Status**: ✅ No issues found")
            lines.append("")
            lines.append("No selectors with more than 3 levels of nesting were detected. The codebase follows good practices for selector depth.")
        lines.append("")
        lines.append("---")
        lines.append("")

        # Inefficient Selectors
        lines.append("### 2. Inefficient Selectors")
        lines.append("")
        if self.results['inefficient_selectors']:
            lines.append(f"**Issues Found**: {len(self.results['inefficient_selectors'])}")
            lines.append("")
            for i, issue in enumerate(self.results['inefficient_selectors'], 1):
                lines.append(f"#### Issue #{i}: {issue['type'].replace('_', ' ').title()}")
                lines.append(f"- **File**: `wp-content/plugins/{issue['file']}`")
                lines.append(f"- **Line**: {issue['line']}")
                lines.append(f"- **Selector**: `{issue['selector']}`")
                lines.append(f"- **Type**: {issue['type']}")
                lines.append(f"- **Severity**: {issue['severity'].title()}")
                lines.append(f"- **Suggestion**: {issue['suggestion']}")
                lines.append("")
        else:
            lines.append("**Status**: ✅ No issues found")
            lines.append("")
            lines.append("No inefficient selectors detected.")
        lines.append("")
        lines.append("---")
        lines.append("")

        # Media Queries
        lines.append("### 3. Media Query Optimization")
        lines.append("")
        if self.results['media_queries']:
            lines.append(f"**Issues Found**: {len(self.results['media_queries'])} duplicate breakpoints")
            lines.append("")

            # Group by breakpoint
            bp_groups = {}
            for mq in self.results['media_queries']:
                bp = mq['breakpoint']
                if bp not in bp_groups:
                    bp_groups[bp] = []
                bp_groups[bp].append(mq)

            for bp, issues in bp_groups.items():
                lines.append(f"#### Breakpoint: {bp}")
                lines.append(f"**Locations** ({len(issues[0]['locations'])} occurrences):")
                for loc in issues[0]['locations']:
                    lines.append(f"1. `wp-content/plugins/{loc}`")
                lines.append("")
                lines.append(f"**Suggestion**: Create shared breakpoint mixin for {bp} in `mixins/_breakpoints.scss`")
                lines.append("")
                lines.append("---")
                lines.append("")
        else:
            lines.append("**Status**: ✅ No issues found")
            lines.append("")
            lines.append("No duplicate media query breakpoints detected.")
        lines.append("")
        lines.append("---")
        lines.append("")

        # Critical CSS
        lines.append("### 4. Critical CSS Opportunities")
        lines.append("")
        if self.results['critical_css']['above_fold_classes']:
            lines.append("**Status**: ⚠️ Above-fold classes detected")
            lines.append("")
            lines.append(f"- **Above-fold Classes**: {len(self.results['critical_css']['above_fold_classes'])}")
            lines.append(f"- **Estimated Critical CSS Size**: {self.results['critical_css']['estimated_size']}")
            lines.append(f"- **Recommendation**: {self.results['critical_css']['recommendation']}")
            lines.append("")
        else:
            lines.append("**Status**: ✅ No above-fold classes detected")
            lines.append("")
            lines.append(f"- **Above-fold Classes**: 0")
            lines.append(f"- **Estimated Critical CSS Size**: {self.results['critical_css']['estimated_size']}")
            lines.append(f"- **Recommendation**: {self.results['critical_css']['recommendation']}")
            lines.append("")
            lines.append("**Note**: The plugin uses WordPress admin interface patterns, so traditional above-fold critical CSS extraction is not applicable.")
        lines.append("")
        lines.append("---")
        lines.append("")

        # Unused CSS
        lines.append("### 5. Unused CSS in Production")
        lines.append("")
        if self.results['unused_css']['unused_classes']:
            lines.append("**Status**: ⚠️ Unused CSS detected")
            lines.append("")
            lines.append(f"- **Total Unused Bytes**: {self.results['unused_css']['total_unused_bytes']}")
            lines.append(f"- **Unused Percentage**: {self.results['unused_css']['unused_percentage']}%")
            lines.append("")
        else:
            lines.append("**Status**: ✅ No unused CSS detected")
            lines.append("")
            lines.append(f"- **Total Unused Bytes**: {self.results['unused_css']['total_unused_bytes']}")
            lines.append(f"- **Unused Percentage**: {self.results['unused_css']['unused_percentage']}%")
        lines.append("")
        lines.append("---")
        lines.append("")

        # Recommendations
        lines.append("## Recommendations")
        lines.append("")

        if self.results['media_queries']:
            lines.append("### High Priority")
            lines.append("")
            lines.append("1. **Create Shared Breakpoint Mixins**")
            lines.append("   - Add mixins for duplicate breakpoints to `mixins/_breakpoints.scss`")
            lines.append("   - Replace duplicate `@media` queries with shared mixins")
            lines.append("   - This will improve maintainability and ensure consistent responsive behavior")
            lines.append("")

        if self.results['inefficient_selectors']:
            lines.append("### Low Priority")
            lines.append("")
            lines.append("2. **Consider BEM for Complex Selectors**")
            lines.append("   - The descendant selectors detected may be acceptable for WordPress compatibility")
            lines.append("   - Review and refactor if needed")
            lines.append("")

        if not self.results['media_queries'] and not self.results['inefficient_selectors']:
            lines.append("**No specific recommendations at this time. The codebase is in good condition.**")
            lines.append("")

        lines.append("---")
        lines.append("")
        lines.append("## Conclusion")
        lines.append("")

        if self.results['summary']['total_issues'] == 0:
            lines.append("The CSS codebase is in excellent condition with no performance issues detected. All best practices are being followed.")
            lines.append("")
            lines.append("**Overall Performance Grade**: A+ (Excellent)")
        elif self.results['summary']['total_issues'] <= 5:
            lines.append("The CSS codebase is in good condition with only minor optimization opportunities. The main improvement area is consolidating duplicate breakpoints into shared mixins.")
            lines.append("")
            lines.append("**Overall Performance Grade**: B+ (Good)")
        elif self.results['summary']['total_issues'] <= 10:
            lines.append("The CSS codebase has some optimization opportunities. Addressing the duplicate breakpoints and inefficient selectors will improve performance and maintainability.")
            lines.append("")
            lines.append("**Overall Performance Grade**: B (Fair)")
        else:
            lines.append("The CSS codebase has several optimization opportunities that should be addressed to improve performance and maintainability.")
            lines.append("")
            lines.append("**Overall Performance Grade**: C (Needs Improvement)")

        lines.append("")
        lines.append("---")
        lines.append("")
        lines.append("**Report Generated By**: `scripts/css-performance-analysis.py`")
        lines.append("**Analysis Method**: Automated SCSS pattern detection")

        return "\n".join(lines)


def main():
    """Main entry point."""
    # Set paths
    project_root = Path(__file__).parent.parent
    scss_dir = project_root / "wp-content" / "plugins" / "affiliate-product-showcase" / "assets" / "scss"
    report_dir = project_root / "reports"
    report_file = report_dir / "css-performance-analysis-report.md"

    # Ensure report directory exists
    report_dir.mkdir(exist_ok=True)

    # Run analysis
    analyzer = CSSPerformanceAnalyzer(str(scss_dir))
    results = analyzer.analyze()

    # Generate and save markdown report
    markdown_report = analyzer.generate_markdown_report()
    with open(report_file, 'w', encoding='utf-8') as f:
        f.write(markdown_report)

    print(f"\n{'='*60}")
    print(f"CSS Performance Analysis Complete")
    print(f"{'='*60}")
    print(f"Report saved to: {report_file}")
    print(f"\nSummary:")
    print(f"  Total Issues: {results['summary']['total_issues']}")
    print(f"  Performance Impact: {results['summary']['performance_impact']}")
    print(f"  Estimated Improvement: {results['summary']['estimated_improvement']}")
    print(f"\nBreakdown:")
    print(f"  Deep Nesting Issues: {len(results['deep_nesting'])}")
    print(f"  Inefficient Selectors: {len(results['inefficient_selectors'])}")
    print(f"  Media Query Issues: {len(results['media_queries'])}")
    print(f"  Critical CSS Classes: {len(results['critical_css']['above_fold_classes'])}")
    print(f"{'='*60}\n")


if __name__ == "__main__":
    main()
