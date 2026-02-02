#!/usr/bin/env python3
"""
PHP Performance Analysis Script

Analyzes PHP codebase for performance bottlenecks and optimization opportunities.

Usage: python scripts/php-performance-audit.py
"""

import os
import re
import json
from pathlib import Path
from typing import Dict, List, Any
from datetime import datetime


def analyze_php_file(file_path: Path) -> Dict[str, Any]:
    """Analyze a single PHP file for performance issues."""
    content = file_path.read_text(encoding='utf-8')
    lines = content.split('\n')
    
    issues = {
        'database_queries': [],
        'loops': [],
        'lazy_loading': [],
        'memory': [],
    }
    
    for line_num, line in enumerate(lines, start=1):
        # Skip empty lines
        line = line.strip()
        if not line:
            continue
        
        # Check for database queries in loops
        if re.search(r'\$wpdb->(get_|query|prepare|update|delete|insert|result)\s*\(', line):
            issues['database_queries'].append({
                'file': str(file_path),
                'line': line_num,
                'type': 'n_plus_one',
                'description': f'Query inside loop at line {line_num}',
                'suggestion': 'Move query outside loop or use caching'
            })
        
        # Check for inefficient loops
        if re.search(r'foreach\s*\(', line):
            issues['loops'].append({
                'file': str(file_path),
                'line': line_num,
                'type': 'inefficient',
                'description': f'Inefficient loop pattern at line {line_num}',
                'suggestion': 'Optimize loop or move operations outside'
            })
        
        # Check for lazy loading opportunities
        if re.search(r'get_posts?\s*\(|get_post_meta?\s*\(', line):
            issues['lazy_loading'].append({
                'file': str(file_path),
                'line': line_num,
                'type': 'eager_loading',
                'description': f'Eager loading detected at line {line_num}',
                'suggestion': 'Consider lazy loading for large datasets'
            })
        
        # Check for memory issues
        if re.search(r'array_merge\(|array_push\(|array_map\(', line):
            issues['memory'].append({
                'file': str(file_path),
                'line': line_num,
                'type': 'large_array',
                'description': f'Large array operation at line {line_num}',
                'suggestion': 'Use generators or process in chunks'
            })
    
    return issues


def analyze_directory(src_dir: str) -> Dict[str, Any]:
    """Analyze all PHP files in a directory."""
    php_files = []
    
    for root, dirs, files in os.walk(src_dir):
        for file in files:
            if file.endswith('.php'):
                php_files.append(os.path.join(root, file))
    
    return php_files


def generate_report(issues: Dict[str, Any], php_files_count: int) -> str:
    """Generate performance analysis report in Markdown format."""
    total_issues = (
        len(issues.get('database_queries', [])) +
        len(issues.get('loops', [])) +
        len(issues.get('lazy_loading', [])) +
        len(issues.get('memory', []))
    )
    
    performance_impact = 'low'
    if total_issues > 0:
        performance_impact = 'medium'
    elif total_issues > 5:
            performance_impact = 'high'
    
    report = f"""# PHP Performance Analysis Report

**Date**: {datetime.now().isoformat()}

## Summary

| Category | Issues | Severity |
|----------|--------|----------|
| Database Queries | {len(issues['database_queries'])} | {len(issues['database_queries'])} |
| Loops | {len(issues['loops'])} | {len(issues['loops'])} |
| Lazy Loading | {len(issues['lazy_loading'])} | {len(issues['lazy_loading'])} |
| Memory | {len(issues['memory'])} | {len(issues['memory'])} |
| **Total Issues** | **{total_issues}** |

"""
    
    if issues['database_queries']:
        report += "### Database Query Issues\n\n"
        for issue in issues['database_queries']:
            report += f"- **{issue['file']}:{issue['line']}**\n"
            report += f"  - Type: {issue['type']}\n"
            report += f"  - Description: {issue['description']}\n"
            report += f"  - Suggestion: {issue['suggestion']}\n\n"
    
    if issues['loops']:
        report += "### Loop Issues\n\n"
        for issue in issues['loops']:
            report += f"- **{issue['file']}:{issue['line']}**\n"
            report += f"  - Type: {issue['type']}\n"
            report += f"  - Description: {issue['description']}\n"
            report += f"  - Suggestion: {issue['suggestion']}\n\n"
    
    if issues['lazy_loading']:
        report += "### Lazy Loading Issues\n\n"
        for issue in issues['lazy_loading']:
            report += f"- **{issue['file']}:{issue['line']}**\n"
            report += f"  - Type: {issue['type']}\n"
            report += f"  - Description: {issue['description']}\n"
            report += f"  - Suggestion: {issue['suggestion']}\n\n"
    
    if issues['memory']:
        report += "### Memory Issues\n\n"
        for issue in issues['memory']:
            report += f"- **{issue['file']}:{issue['line']}**\n"
            report += f"  - Type: {issue['type']}\n"
            report += f"  - Description: {issue['description']}\n"
            report += f"  - Suggestion: {issue['suggestion']}\n\n"
    
    report += f"## Recommendations\n\n"
    report += "1. **Database Queries** (High Priority)\n"
    report += "   - Use WordPress transients API for caching\n"
    report += "   - Implement query result caching\n"
    report += "   - Add indexes for frequently queried columns\n"
    report += "\n"
    report += "2. **Loop Optimization** (Medium Priority)\n"
    report += "   - Move heavy operations outside loops\n"
    report += "   - Use generators for data transformations\n"
    report += "\n"
    report += "3. **Lazy Loading** (Low Priority)\n"
    report += "   - Consider lazy loading for large datasets\n"
    report += "\n"
    report += "4. **Memory Management** (Low Priority)\n"
    report += "   - Process large arrays in chunks\n"
    report += "   - Use generators for data transformations\n"
    
    report += f"## Files Analyzed\n\n"
    report += f"- {php_files_count} PHP files analyzed\n"
    
    report += f"## Analysis Date\n"
    report += f"**Date**: {datetime.now().isoformat()}\n"
    report += f"**Status**: ✅ Complete\n"
    
    return report


def main():
    import sys
    
    # Default to plugin src directory
    if len(sys.argv) > 1:
        src_dir = sys.argv[1]
    else:
        src_dir = 'wp-content/plugins/affiliate-product-showcase/src'
    
    # Get all PHP files
    php_files = analyze_directory(src_dir)
    
    # Analyze all files
    all_issues = {
        'database_queries': [],
        'loops': [],
        'lazy_loading': [],
        'memory': [],
    }
    
    for php_file in php_files:
        file_path = Path(php_file)
        issues = analyze_php_file(file_path)
        all_issues['database_queries'].extend(issues['database_queries'])
        all_issues['loops'].extend(issues['loops'])
        all_issues['lazy_loading'].extend(issues['lazy_loading'])
        all_issues['memory'].extend(issues['memory'])
    
    # Generate report
    report = generate_report(all_issues, len(php_files))
    
    # Save JSON report
    output_json = 'reports/php-performance-analysis.json'
    with open(output_json, 'w', encoding='utf-8') as f:
        json.dump(all_issues, f, indent=2)
    
    print(f"JSON report saved to {output_json}")
    
    # Save Markdown report
    output_md = 'reports/php-performance-analysis-report.md'
    with open(output_md, 'w', encoding='utf-8') as f:
        f.write(report)
    
    print(f"Markdown report saved to {output_md}")


if __name__ == '__main__':
    main()
