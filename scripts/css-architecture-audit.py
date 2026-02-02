#!/usr/bin/env python3
"""
CSS Architecture Audit Script
Analyzes SCSS file organization, naming conventions, and modularity.

Usage: python scripts/css-architecture-audit.py
Output: reports/css-architecture-report.md
"""

import os
import re
from datetime import datetime
from pathlib import Path


def analyze_file_structure(base_path):
    """Analyze SCSS file structure and organization."""
    scss_files = []
    
    for root, dirs, files in os.walk(base_path):
        dirs[:] = [d for d in dirs if d not in ['node_modules', 'vendor', 'dist', 'build']]
        for file in files:
            if file.endswith('.scss'):
                scss_files.append(os.path.join(root, file))
    
    return scss_files


def check_naming_conventions(content, file_path):
    """Check BEM naming convention compliance."""
    issues = []
    lines = content.split('\n')
    
    # BEM pattern: .block__element--modifier
    bem_pattern = r'^\.[a-z]([a-z0-9-]*(__[a-z0-9-]+)?(--[a-z0-9-]+)?)$'
    
    # Non-BEM patterns that are problematic
    problematic_patterns = [
        (r'\.[a-z]+-[a-z]+-[a-z]+-[a-z]+', 'too-many-hyphens'),
        (r'\.js-', 'js-hook-class'),
        (r'\.[a-z]+[A-Z]', 'camelCase-class'),
    ]
    
    for line_num, line in enumerate(lines, 1):
        # Find class selectors
        selectors = re.findall(r'\.([a-zA-Z][a-zA-Z0-9_-]*)', line)
        
        for selector in selectors:
            # Skip if it's a BEM-compliant class
            if re.match(bem_pattern, '.' + selector):
                continue
            
            # Skip WordPress native classes
            if selector.startswith('wp-') or selector.startswith('admin'):
                continue
            
            # Check for problematic patterns
            for pattern, issue_type in problematic_patterns:
                if re.search(pattern, '.' + selector):
                    issues.append({
                        'file': file_path,
                        'line': line_num,
                        'selector': '.' + selector,
                        'type': issue_type,
                        'severity': 'medium'
                    })
                    break
    
    return issues


def analyze_imports(content, file_path):
    """Analyze SCSS imports and dependencies."""
    imports = []
    lines = content.split('\n')
    
    for line_num, line in enumerate(lines, 1):
        match = re.match(r"@import\s+['\"]([^'\"]+)['\"];", line)
        if match:
            imports.append({
                'file': file_path,
                'line': line_num,
                'import': match.group(1)
            })
    
    return imports


def check_file_organization(scss_files, base_path):
    """Check if files are organized according to 7-1 pattern or similar."""
    issues = []
    
    expected_structure = {
        'abstracts': ['_variables.scss', '_mixins.scss', '_functions.scss'],
        'base': ['_reset.scss', '_typography.scss', '_utilities.scss'],
        'components': [],
        'layouts': [],
        'pages': [],
        'themes': [],
        'vendors': []
    }
    
    # Check directory structure
    dirs = set()
    for file_path in scss_files:
        rel_path = os.path.relpath(file_path, base_path)
        parts = rel_path.split(os.sep)
        if len(parts) > 1:
            dirs.add(parts[0])
    
    # Check for main entry point
    main_files = [f for f in scss_files if os.path.basename(f) == 'main.scss']
    if not main_files:
        issues.append({
            'type': 'missing-main',
            'severity': 'high',
            'message': 'No main.scss entry point found'
        })
    
    return issues, dirs


def analyze_nesting_depth(content, file_path):
    """Check selector nesting depth."""
    issues = []
    lines = content.split('\n')
    
    max_depth = 0
    current_depth = 0
    
    for line_num, line in enumerate(lines, 1):
        # Count opening braces
        open_count = line.count('{')
        close_count = line.count('}')
        
        current_depth += open_count - close_count
        max_depth = max(max_depth, current_depth)
        
        if current_depth > 4:
            issues.append({
                'file': file_path,
                'line': line_num,
                'depth': current_depth,
                'severity': 'medium'
            })
    
    return issues, max_depth


def analyze_variables(content, file_path):
    """Analyze SCSS variable usage."""
    variables_defined = re.findall(r'\$([a-zA-Z][a-zA-Z0-9_-]*)\s*:', content)
    variables_used = re.findall(r'\$([a-zA-Z][a-zA-Z0-9_-]*)', content)
    
    return {
        'defined': set(variables_defined),
        'used': set(variables_used)
    }


def main():
    print("=" * 60)
    print("CSS Architecture Audit")
    print("=" * 60)
    
    base_path = 'wp-content/plugins/affiliate-product-showcase/assets/scss'
    
    if not os.path.exists(base_path):
        print(f"Error: Directory not found: {base_path}")
        return
    
    print(f"\nScanning: {base_path}")
    
    # Get all SCSS files
    scss_files = analyze_file_structure(base_path)
    print(f"Found {len(scss_files)} SCSS files\n")
    
    all_naming_issues = []
    all_imports = []
    all_nesting_issues = []
    all_variables = {}
    max_nesting_depth = 0
    
    for i, file_path in enumerate(scss_files, 1):
        print(f"  [{i}/{len(scss_files)}] Analyzing: {os.path.basename(file_path)}")
        
        try:
            with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
                content = f.read()
        except Exception as e:
            print(f"    Error reading file: {e}")
            continue
        
        # Check naming conventions
        naming_issues = check_naming_conventions(content, file_path)
        all_naming_issues.extend(naming_issues)
        
        # Analyze imports
        imports = analyze_imports(content, file_path)
        all_imports.extend(imports)
        
        # Check nesting depth
        nesting_issues, file_max_depth = analyze_nesting_depth(content, file_path)
        all_nesting_issues.extend(nesting_issues)
        max_nesting_depth = max(max_nesting_depth, file_max_depth)
        
        # Analyze variables
        variables = analyze_variables(content, file_path)
        all_variables[file_path] = variables
    
    # Check file organization
    org_issues, dirs = check_file_organization(scss_files, base_path)
    
    # Generate report
    print("\n" + "=" * 60)
    print("Architecture Audit Summary")
    print("=" * 60)
    print(f"\n  Files Analyzed: {len(scss_files)}")
    print(f"  Directories: {len(dirs)}")
    print(f"  Naming Issues: {len(all_naming_issues)}")
    print(f"  Deep Nesting Issues: {len(all_nesting_issues)}")
    print(f"  Max Nesting Depth: {max_nesting_depth}")
    print(f"  Total Imports: {len(all_imports)}")
    
    # Write markdown report
    report_lines = []
    report_lines.append("# CSS Architecture Audit Report\n")
    report_lines.append(f"**Generated:** {datetime.now().isoformat()}\n")
    report_lines.append("## Summary\n")
    report_lines.append("| Metric | Count |")
    report_lines.append("|--------|-------|")
    report_lines.append(f"| SCSS Files | {len(scss_files)} |")
    report_lines.append(f"| Directories | {len(dirs)} |")
    report_lines.append(f"| Naming Issues | {len(all_naming_issues)} |")
    report_lines.append(f"| Deep Nesting (>4) | {len(all_nesting_issues)} |")
    report_lines.append(f"| Max Nesting Depth | {max_nesting_depth} |")
    report_lines.append(f"| Total Imports | {len(all_imports)} |")
    report_lines.append("")
    
    # File structure
    report_lines.append("## File Structure\n")
    report_lines.append("```")
    for file_path in sorted(scss_files):
        rel_path = os.path.relpath(file_path, base_path)
        report_lines.append(rel_path)
    report_lines.append("```\n")
    
    # Naming convention issues
    if all_naming_issues:
        report_lines.append("## Naming Convention Issues\n")
        report_lines.append("| File | Line | Selector | Issue |")
        report_lines.append("|------|------|----------|-------|")
        for issue in all_naming_issues[:30]:
            file_name = os.path.basename(issue['file'])
            report_lines.append(f"| {file_name} | {issue['line']} | `{issue['selector']}` | {issue['type']} |")
        if len(all_naming_issues) > 30:
            report_lines.append(f"| ... | ... | *{len(all_naming_issues) - 30} more* | ... |")
        report_lines.append("")
    
    # Deep nesting issues
    if all_nesting_issues:
        report_lines.append("## Deep Nesting Issues\n")
        report_lines.append("| File | Line | Depth |")
        report_lines.append("|------|------|-------|")
        for issue in all_nesting_issues[:20]:
            file_name = os.path.basename(issue['file'])
            report_lines.append(f"| {file_name} | {issue['line']} | {issue['depth']} |")
        report_lines.append("")
    
    # Recommendations
    report_lines.append("## Recommendations\n")
    
    if len(dirs) < 3:
        report_lines.append("### 1. Improve Directory Structure\n")
        report_lines.append("Consider organizing files into standard directories:\n")
        report_lines.append("```")
        report_lines.append("scss/")
        report_lines.append("├── main.scss              # Entry point")
        report_lines.append("├── _variables.scss        # Global variables")
        report_lines.append("├── _mixins.scss           # Global mixins")
        report_lines.append("├── base/                  # Reset, typography, utilities")
        report_lines.append("├── components/            # UI components")
        report_lines.append("├── layouts/               # Page layouts")
        report_lines.append("└── pages/                 # Page-specific styles")
        report_lines.append("```\n")
    
    if all_naming_issues:
        report_lines.append("### 2. Naming Conventions\n")
        report_lines.append("- Use BEM methodology: `.block__element--modifier`\n")
        report_lines.append("- Avoid camelCase in class names\n")
        report_lines.append("- Use descriptive, semantic names\n")
        report_lines.append("")
    
    if max_nesting_depth > 4:
        report_lines.append("### 3. Reduce Nesting\n")
        report_lines.append(f"- Current max nesting: {max_nesting_depth} levels\n")
        report_lines.append("- Recommended: Maximum 3-4 levels\n")
        report_lines.append("- Deep nesting increases specificity issues\n")
        report_lines.append("")
    
    report_lines.append("### 4. Variable System\n")
    report_lines.append("- Define color palette in `_variables.scss`\n")
    report_lines.append("- Use semantic variable names\n")
    report_lines.append("- Create spacing scale\n")
    report_lines.append("- Define typography scale\n")
    
    # Write report
    os.makedirs('reports', exist_ok=True)
    report_path = 'reports/css-architecture-report.md'
    with open(report_path, 'w', encoding='utf-8') as f:
        f.write('\n'.join(report_lines))
    
    print(f"\n  Report saved: {report_path}")
    print("=" * 60)


if __name__ == '__main__':
    main()
