#!/usr/bin/env python3
"""
Script to find and count !important declarations in CSS files.

This script recursively searches through the project directory,
finds all CSS files, and counts occurrences of !important declarations.

Usage:
    python scripts/find-important.py [directory]

Arguments:
    directory: Optional directory to search (default: current directory)

Output:
    - Total count of !important declarations
    - Count per file
    - Line numbers where !important is found
"""

import os
import re
import sys
from pathlib import Path
from collections import defaultdict
from typing import Dict, List, Tuple

# Set UTF-8 encoding for Windows console
if sys.platform == 'win32':
    import codecs
    sys.stdout = codecs.getwriter('utf-8')(sys.stdout.buffer, 'strict')


def find_css_files(directory: Path) -> List[Path]:
    """Find all CSS files recursively in the given directory."""
    css_files = []
    for root, dirs, files in os.walk(directory):
        # Skip common directories to ignore
        dirs[:] = [d for d in dirs if d not in {
            'node_modules', '.git', 'vendor', 'build', 'dist', '.cache'
        }]
        
        for file in files:
            if file.endswith(('.css', '.scss', '.sass', '.less')):
                css_files.append(Path(root) / file)
    
    return sorted(css_files)


def find_important_in_file(file_path: Path) -> Tuple[int, List[int]]:
    """
    Find !important declarations in a CSS file.
    
    Returns:
        Tuple of (count, list of line numbers where !important was found)
    """
    count = 0
    line_numbers = []
    
    try:
        with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
            for line_num, line in enumerate(f, 1):
                # Match !important (case-insensitive, with optional whitespace)
                if re.search(r'!\s*important', line, re.IGNORECASE):
                    count += 1
                    line_numbers.append(line_num)
    except Exception as e:
        print(f"Error reading {file_path}: {e}", file=sys.stderr)
    
    return count, line_numbers


def print_report(file_counts: Dict[Path, Tuple[int, List[int]]]) -> None:
    """Print a formatted report of !important counts."""
    total_count = sum(count for count, _ in file_counts.values())
    files_with_important = [(path, count, lines) for path, (count, lines) in file_counts.items() if count > 0]
    files_with_important.sort(key=lambda x: x[1], reverse=True)
    
    print("=" * 80)
    print("!IMPORTANT DECLARATION ANALYSIS REPORT")
    print("=" * 80)
    print()
    
    if not files_with_important:
        print("OK: No !important declarations found!")
        return
    
    print(f"Total files with !important: {len(files_with_important)}")
    print(f"Total !important declarations: {total_count}")
    print()
    
    print("-" * 80)
    print("DETAILS BY FILE:")
    print("-" * 80)
    print()
    
    for path, count, lines in files_with_important:
        # Get relative path from current directory
        rel_path = path.relative_to(Path.cwd()) if path.is_absolute() else path
        print(f"[FILE] {rel_path}")
        print(f"   Count: {count}")
        if lines:
            lines_str = ", ".join(map(str, lines[:10]))  # Show first 10
            if len(lines) > 10:
                lines_str += f" ... ({len(lines) - 10} more)"
            print(f"   Lines: {lines_str}")
        print()
    
    print("-" * 80)
    print("SUMMARY:")
    print("-" * 80)
    print(f"Files analyzed: {len(file_counts)}")
    print(f"Files with !important: {len(files_with_important)}")
    print(f"Total !important declarations: {total_count}")
    print()
    
    # Severity assessment
    if total_count == 0:
        print("EXCELLENT: No !important declarations found!")
    elif total_count < 10:
        print("LOW: Minimal use of !important declarations.")
    elif total_count < 50:
        print("MODERATE: Consider reducing !important usage.")
    else:
        print("HIGH: Excessive use of !important! This can lead to CSS specificity issues.")
    print()


def main():
    """Main entry point."""
    # Determine directory to search
    if len(sys.argv) > 1:
        search_dir = Path(sys.argv[1])
    else:
        search_dir = Path.cwd()
    
    if not search_dir.exists():
        print(f"Error: Directory '{search_dir}' does not exist.", file=sys.stderr)
        sys.exit(1)
    
    print(f"Searching for CSS files in: {search_dir}")
    print()
    
    # Find all CSS files
    css_files = find_css_files(search_dir)
    print(f"Found {len(css_files)} CSS files to analyze.")
    print()
    
    if not css_files:
        print("No CSS files found.")
        return
    
    # Analyze each file
    file_counts = {}
    for css_file in css_files:
        count, lines = find_important_in_file(css_file)
        file_counts[css_file] = (count, lines)
    
    # Print report
    print_report(file_counts)


if __name__ == "__main__":
    main()
