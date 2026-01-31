#!/usr/bin/env python3
"""
Script to count @media queries in CSS and SCSS files.
Recursively searches the project directory and reports statistics.

Usage:
    python count-media-queries.py [directory]

    If no directory is specified, scans the parent directory of the scripts folder.
"""

import os
import re
import sys
from pathlib import Path
from collections import defaultdict
from typing import Dict, List, Tuple


def find_css_files(root_dir: str) -> List[Path]:
    """Find all CSS and SCSS files recursively."""
    css_files = []
    root_path = Path(root_dir)
    
    # Common directories to skip
    skip_dirs = {
        'node_modules', '.git', 'vendor', 'dist', 'build',
        '.roo', 'backups', 'chat-history', 'screenshots',
        'docker', '.docker'
    }
    
    for file_path in root_path.rglob('*'):
        if file_path.suffix in {'.css', '.scss', '.sass'}:
            # Skip if in a skipped directory
            if any(skip_dir in file_path.parts for skip_dir in skip_dirs):
                continue
            css_files.append(file_path)
    
    return sorted(css_files)


def count_media_queries(file_path: Path) -> Tuple[int, List[str]]:
    """Count @media queries in a single file and return their types."""
    try:
        with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
            content = f.read()
    except Exception as e:
        print(f"Error reading {file_path}: {e}")
        return 0, []
    
    # Pattern to match @media queries
    # This captures the entire @media declaration including its conditions
    media_pattern = re.compile(r'@media\s+([^{]+)\s*\{', re.IGNORECASE)
    
    matches = media_pattern.findall(content)
    media_types = [m.strip() for m in matches]
    
    return len(matches), media_types


def categorize_media_query(media_query: str) -> str:
    """Categorize a media query by its main feature."""
    media_query_lower = media_query.lower()
    
    categories = {
        'screen': 'screen',
        'min-width': 'min-width',
        'max-width': 'max-width',
        'min-height': 'min-height',
        'max-height': 'max-height',
        'orientation': 'orientation',
        'hover': 'hover',
        'print': 'print',
        'prefers-color-scheme': 'prefers-color-scheme',
        'prefers-reduced-motion': 'prefers-reduced-motion',
    }
    
    for keyword, category in categories.items():
        if keyword in media_query_lower:
            return category
    
    return 'other'


def analyze_media_queries(files: List[Path]) -> Dict:
    """Analyze media queries across all files."""
    results = {
        'total_files': len(files),
        'files_with_media': 0,
        'total_media_queries': 0,
        'by_file': {},
        'by_category': defaultdict(int),
        'all_queries': []
    }
    
    for file_path in files:
        count, queries = count_media_queries(file_path)
        
        if count > 0:
            results['files_with_media'] += 1
            results['total_media_queries'] += count
            results['by_file'][str(file_path)] = {
                'count': count,
                'queries': queries
            }
            
            for query in queries:
                category = categorize_media_query(query)
                results['by_category'][category] += 1
                results['all_queries'].append({
                    'file': str(file_path),
                    'query': query,
                    'category': category
                })
    
    return results


def print_report(results: Dict):
    """Print a formatted report of the analysis."""
    print("=" * 70)
    print("MEDIA QUERY ANALYSIS REPORT")
    print("=" * 70)
    print()
    
    # Summary
    print("SUMMARY")
    print("-" * 70)
    print(f"Total CSS/SCSS files scanned: {results['total_files']}")
    print(f"Files with @media queries: {results['files_with_media']}")
    print(f"Total @media queries found: {results['total_media_queries']}")
    print()
    
    # By category
    print("BY CATEGORY")
    print("-" * 70)
    if results['by_category']:
        sorted_categories = sorted(results['by_category'].items(), key=lambda x: x[1], reverse=True)
        for category, count in sorted_categories:
            print(f"  {category:25s}: {count:4d}")
    else:
        print("  No media queries found.")
    print()
    
    # By file
    print("BY FILE")
    print("-" * 70)
    if results['by_file']:
        sorted_files = sorted(results['by_file'].items(), key=lambda x: x[1]['count'], reverse=True)
        for file_path, data in sorted_files:
            print(f"  {data['count']:3d} | {file_path}")
    else:
        print("  No files with media queries found.")
    print()
    
    # Detailed queries
    if results['all_queries']:
        print("DETAILED MEDIA QUERIES")
        print("-" * 70)
        for item in results['all_queries']:
            print(f"  [{item['category']}] {item['query']}")
            print(f"    -> {item['file']}")
            print()


def save_report(results: Dict, output_path: str = 'reports/media-query-analysis.md'):
    """Save the report as a markdown file."""
    os.makedirs(os.path.dirname(output_path), exist_ok=True)
    
    with open(output_path, 'w', encoding='utf-8') as f:
        f.write("# Media Query Analysis Report\n\n")
        f.write(f"**Generated:** {os.popen('date /t && time /t').read().strip()}\n\n")
        
        # Summary
        f.write("## Summary\n\n")
        f.write(f"- **Total CSS/SCSS files scanned:** {results['total_files']}\n")
        f.write(f"- **Files with @media queries:** {results['files_with_media']}\n")
        f.write(f"- **Total @media queries found:** {results['total_media_queries']}\n\n")
        
        # By category
        f.write("## By Category\n\n")
        f.write("| Category | Count |\n")
        f.write("|----------|-------|\n")
        sorted_categories = sorted(results['by_category'].items(), key=lambda x: x[1], reverse=True)
        for category, count in sorted_categories:
            f.write(f"| {category} | {count} |\n")
        f.write("\n")
        
        # By file
        f.write("## By File\n\n")
        f.write("| Count | File |\n")
        f.write("|-------|------|\n")
        sorted_files = sorted(results['by_file'].items(), key=lambda x: x[1]['count'], reverse=True)
        for file_path, data in sorted_files:
            # Make file path relative for readability
            rel_path = file_path.replace(str(os.getcwd()) + '\\', '').replace('/', '\\')
            f.write(f"| {data['count']} | `{rel_path}` |\n")
        f.write("\n")
        
        # Detailed queries
        f.write("## Detailed Media Queries\n\n")
        for item in results['all_queries']:
            rel_path = item['file'].replace(str(os.getcwd()) + '\\', '').replace('/', '\\')
            f.write(f"### `{item['query']}`\n\n")
            f.write(f"- **Category:** {item['category']}\n")
            f.write(f"- **File:** `{rel_path}`\n\n")
    
    print(f"Report saved to: {output_path}")


def main():
    """Main entry point."""
    # Get the target directory from command line argument or use default
    if len(sys.argv) > 1:
        root_dir = sys.argv[1]
        # Convert to absolute path if relative
        if not os.path.isabs(root_dir):
            root_dir = os.path.abspath(root_dir)
    else:
        # Get the project root directory (parent of scripts folder)
        root_dir = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
    
    print(f"Scanning for CSS/SCSS files in: {root_dir}")
    print()
    
    # Find all CSS files
    files = find_css_files(root_dir)
    print(f"Found {len(files)} CSS/SCSS files")
    print()
    
    # Analyze media queries
    results = analyze_media_queries(files)
    
    # Print report
    print_report(results)
    
    # Save report
    save_report(results)


if __name__ == '__main__':
    main()
