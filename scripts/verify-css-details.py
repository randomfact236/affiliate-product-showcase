#!/usr/bin/env python3
"""
Script to verify CSS details: @media queries, CSS variables, and !important counts.
"""

import re
import os
from pathlib import Path
from typing import Dict

def analyze_css_file(file_path: Path) -> Dict[str, int]:
    """Analyze a CSS file for @media queries and CSS variables."""
    try:
        with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
            content = f.read()
    except Exception as e:
        print(f"Error reading {file_path}: {e}", file=sys.stderr)
        return {'@media': 0, 'variables': 0, '!important': 0}
    
    # Count @media queries
    media_count = len(re.findall(r'@media', content, re.IGNORECASE))
    
    # Count CSS variables (--variable-name:)
    var_count = len(re.findall(r'--[\w-]+:', content))
    
    # Count !important
    important_count = len(re.findall(r'!\s*important', content, re.IGNORECASE))
    
    return {
        '@media': media_count,
        'variables': var_count,
        '!important': important_count
    }

def main():
    css_dir = Path('wp-content/plugins/affiliate-product-showcase/assets/css')
    
    if not css_dir.exists():
        print(f"Directory not found: {css_dir}")
        return
    
    results = {}
    
    for css_file in sorted(css_dir.glob('*.css')):
        stats = analyze_css_file(css_file)
        if stats['@media'] > 0 or stats['variables'] > 0 or stats['!important'] > 0:
            results[str(css_file)] = stats
    
    # Print @media counts
    print("=" * 80)
    print("@MEDIA QUERY COUNTS")
    print("=" * 80)
    for file_path, stats in sorted(results.items()):
        if stats['@media'] > 0:
            print(f"{file_path}: {stats['@media']}")
    print()
    
    # Print CSS variable counts
    print("=" * 80)
    print("CSS VARIABLE COUNTS")
    print("=" * 80)
    for file_path, stats in sorted(results.items()):
        if stats['variables'] > 0:
            print(f"{file_path}: {stats['variables']}")
    print()
    
    # Print !important counts
    print("=" * 80)
    print("!IMPORTANT COUNTS")
    print("=" * 80)
    for file_path, stats in sorted(results.items()):
        if stats['!important'] > 0:
            print(f"{file_path}: {stats['!important']}")
    print()
    
    # Summary
    total_media = sum(s['@media'] for s in results.values())
    total_vars = sum(s['variables'] for s in results.values())
    total_important = sum(s['!important'] for s in results.values())
    
    print("=" * 80)
    print("SUMMARY")
    print("=" * 80)
    print(f"Total @media queries: {total_media}")
    print(f"Total CSS variables: {total_vars}")
    print(f"Total !important: {total_important}")

if __name__ == "__main__":
    import sys
    main()
