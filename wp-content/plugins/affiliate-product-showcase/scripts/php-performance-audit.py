#!/usr/bin/env python3
"""
PHP Performance Analysis Script

Detects performance issues in PHP code:
- N+1 query problems
- Queries inside loops
- Missing caching
- Inefficient loops
- Memory issues

Output: reports/php-performance-analysis.json
"""

import os
import re
import json
from pathlib import Path
from datetime import datetime
from typing import Dict, List, Any, Optional


class PHPPerformanceAnalyzer:
    """Analyzes PHP code for performance issues."""
    
    def __init__(self, plugin_path: str):
        self.plugin_path = Path(plugin_path)
        self.src_path = self.plugin_path / 'src'
        self.issues = {
            'database_queries': [],
            'loops': [],
            'lazy_loading': [],
            'memory_usage': [],
            'caching': []
        }
        self.total_files = 0
        self.files_analyzed = 0
        
    def analyze(self) -> Dict[str, Any]:
        """Run complete analysis."""
        print("🔍 PHP Performance Analysis")
        print("=" * 50)
        
        php_files = list(self.src_path.rglob('*.php'))
        self.total_files = len(php_files)
        
        for php_file in php_files:
            self.files_analyzed += 1
            print(f"📂 Analyzing: {php_file.relative_to(self.plugin_path)}")
            self._analyze_file(php_file)
        
        return self._generate_report()
    
    def _analyze_file(self, file_path: Path) -> None:
        """Analyze a single PHP file."""
        try:
            content = file_path.read_text(encoding='utf-8')
            lines = content.split('\n')
        except Exception as e:
            print(f"  ⚠️ Error reading {file_path}: {e}")
            return
        
        relative_path = str(file_path.relative_to(self.plugin_path))
        
        # Analyze for different issues
        self._detect_n_plus_one_queries(content, lines, relative_path)
        self._detect_queries_in_loops(content, lines, relative_path)
        self._detect_missing_caching(content, lines, relative_path)
        self._detect_inefficient_loops(content, lines, relative_path)
        self._detect_memory_issues(content, lines, relative_path)
        self._detect_lazy_loading_opportunities(content, lines, relative_path)
    
    def _detect_n_plus_one_queries(self, content: str, lines: List[str], file_path: str) -> None:
        """Detect N+1 query patterns (queries inside loops)."""
        # Pattern: foreach/for/while loop containing database queries
        loop_patterns = [
            r'foreach\s*\([^)]+\)\s*\{[^}]*(?:WP_Query|get_post_meta|wp_get_post_terms|get_posts)',
            r'for\s*\([^)]+\)\s*\{[^}]*(?:WP_Query|get_post_meta|wp_get_post_terms|get_posts)',
            r'while\s*\([^)]+\)\s*\{[^}]*(?:WP_Query|get_post_meta|wp_get_post_terms|get_posts)'
        ]
        
        for i, line in enumerate(lines, 1):
            # Check if we're inside a loop
            if any(keyword in line for keyword in ['foreach', 'for ', 'while ']):
                # Look ahead for database queries in the next 20 lines
                for j in range(i, min(i + 20, len(lines))):
                    query_line = lines[j]
                    if any(func in query_line for func in [
                        'new WP_Query', 'get_post_meta(', 
                        'wp_get_post_terms(', 'get_posts(',
                        'get_term_meta(', 'update_post_meta(',
                        'wp_insert_post(', 'wp_update_post('
                    ]):
                        # Check if not using cache
                        if 'wp_cache_get' not in query_line and 'wp_cache_' not in content[max(0,i-5):i]:
                            self.issues['database_queries'].append({
                                'severity': 'high',
                                'file': file_path,
                                'line': j + 1,
                                'type': 'n_plus_one',
                                'description': f'Query inside loop at line {j + 1} (loop started at {i})',
                                'code_snippet': query_line.strip()[:100],
                                'suggestion': 'Cache query results before loop or use wp_cache_get()'
                            })
                            break
    
    def _detect_queries_in_loops(self, content: str, lines: List[str], file_path: str) -> None:
        """Detect database queries inside loop blocks."""
        in_loop = False
        loop_start = 0
        brace_count = 0
        
        for i, line in enumerate(lines, 1):
            # Track loop entry
            if not in_loop and any(keyword in line for keyword in ['foreach (', 'for (', 'while (']):
                if '{' in line or any(lines[j].strip() == '{' for j in range(i, min(i+3, len(lines)))):
                    in_loop = True
                    loop_start = i
                    brace_count = 1
            
            if in_loop:
                brace_count += line.count('{') - line.count('}')
                
                # Check for queries
                if any(func in line for func in [
                    'get_post_meta(', 'wp_get_post_terms(',
                    'get_term_meta(', 'get_user_meta(',
                    'wp_insert_post(', 'wp_update_post(',
                    '$wpdb->get_results(', '$wpdb->get_row('
                ]):
                    self.issues['loops'].append({
                        'severity': 'high',
                        'file': file_path,
                        'line': i,
                        'type': 'query_in_loop',
                        'description': f'Database query inside loop (started at line {loop_start})',
                        'code_snippet': line.strip()[:100],
                        'suggestion': 'Move query outside loop or use batch operations'
                    })
                
                # Exit loop tracking
                if brace_count <= 0:
                    in_loop = False
    
    def _detect_missing_caching(self, content: str, lines: List[str], file_path: str) -> None:
        """Detect database queries without caching."""
        for i, line in enumerate(lines, 1):
            # Check for expensive operations without caching
            if any(func in line for func in [
                'new WP_Query', 'get_posts(',
                'wp_get_post_terms(', 'get_categories(',
                'get_tags(', 'wp_count_posts('
            ]):
                # Check if result is cached
                prev_lines = '\n'.join(lines[max(0, i-10):i])
                next_lines = '\n'.join(lines[i:min(i+10, len(lines))])
                
                if 'wp_cache_get' not in prev_lines and 'wp_cache_add' not in next_lines:
                    if 'transient' not in prev_lines.lower() and 'set_transient' not in next_lines.lower():
                        self.issues['caching'].append({
                            'severity': 'medium',
                            'file': file_path,
                            'line': i,
                            'type': 'missing_cache',
                            'description': 'Database query without caching mechanism',
                            'code_snippet': line.strip()[:100],
                            'suggestion': 'Use wp_cache_get() / wp_cache_set() or transients'
                        })
    
    def _detect_inefficient_loops(self, content: str, lines: List[str], file_path: str) -> None:
        """Detect inefficient loop patterns."""
        for i, line in enumerate(lines, 1):
            # Count() in loop condition
            if re.search(r'for\s*\(\s*\$.*=\s*0;\s*\$.*<\s*(?:count|sizeof)\s*\(', line):
                self.issues['loops'].append({
                    'severity': 'medium',
                    'file': file_path,
                    'line': i,
                    'type': 'inefficient_loop',
                    'description': 'Function call in loop condition (executed every iteration)',
                    'code_snippet': line.strip()[:100],
                    'suggestion': 'Store count() result in variable before loop'
                })
            
            # Nested loops
            if 'foreach' in line and any('foreach' in lines[j] for j in range(max(0, i-10), i)):
                self.issues['loops'].append({
                    'severity': 'low',
                    'file': file_path,
                    'line': i,
                    'type': 'nested_loop',
                    'description': 'Nested loop detected - O(n²) complexity risk',
                    'code_snippet': line.strip()[:100],
                    'suggestion': 'Consider optimizing or using lookup arrays'
                })
    
    def _detect_memory_issues(self, content: str, lines: List[str], file_path: str) -> None:
        """Detect potential memory issues."""
        for i, line in enumerate(lines, 1):
            # Large array accumulation in loops
            if '[]' in line and any(op in line for op in ['+=', '.=']) and any(keyword in line for keyword in ['foreach', 'for ', 'while ']):
                self.issues['memory_usage'].append({
                    'severity': 'medium',
                    'file': file_path,
                    'line': i,
                    'type': 'array_accumulation',
                    'description': 'Array/string accumulation in loop - memory growth risk',
                    'code_snippet': line.strip()[:100],
                    'suggestion': 'Consider batch processing or generators'
                })
            
            # Large result sets without pagination
            if 'posts_per_page' in line and '-1' in line:
                self.issues['memory_usage'].append({
                    'severity': 'high',
                    'file': file_path,
                    'line': i,
                    'type': 'unlimited_query',
                    'description': 'Query with unlimited posts_per_page (-1)',
                    'code_snippet': line.strip()[:100],
                    'suggestion': 'Use pagination with reasonable limits (20-100)'
                })
            
            # Loading all meta/terms at once
            if any(func in line for func in ['get_post_custom(', 'wp_get_object_terms(']) and 'fields' not in line:
                self.issues['memory_usage'].append({
                    'severity': 'low',
                    'file': file_path,
                    'line': i,
                    'type': 'full_data_load',
                    'description': 'Loading all fields without specifying what is needed',
                    'code_snippet': line.strip()[:100],
                    'suggestion': "Use 'fields' parameter to limit returned data"
                })
    
    def _detect_lazy_loading_opportunities(self, content: str, lines: List[str], file_path: str) -> None:
        """Detect opportunities for lazy loading."""
        for i, line in enumerate(lines, 1):
            # Eager loading in constructor
            if '__construct' in line or 'public function __construct' in line:
                # Check next 20 lines for heavy operations
                for j in range(i, min(i + 20, len(lines))):
                    if any(func in lines[j] for func in [
                        'get_posts(', 'new WP_Query',
                        'wp_get_post_terms(', 'get_categories('
                    ]):
                        self.issues['lazy_loading'].append({
                            'severity': 'low',
                            'file': file_path,
                            'line': j + 1,
                            'type': 'eager_loading',
                            'description': 'Data loading in constructor - consider lazy loading',
                            'code_snippet': lines[j].strip()[:100],
                            'suggestion': 'Move to getter method with memoization'
                        })
                        break
    
    def _generate_report(self) -> Dict[str, Any]:
        """Generate the analysis report."""
        total_issues = sum(len(v) for v in self.issues.values())
        
        # Determine performance impact
        high_count = sum(1 for cat in self.issues.values() for i in cat if i['severity'] == 'high')
        medium_count = sum(1 for cat in self.issues.values() for i in cat if i['severity'] == 'medium')
        
        if high_count > 5:
            impact = 'high'
        elif high_count > 0 or medium_count > 10:
            impact = 'medium'
        else:
            impact = 'low'
        
        report = {
            'analysis_date': datetime.now().isoformat(),
            'summary': {
                'total_files_analyzed': self.files_analyzed,
                'total_issues': total_issues,
                'performance_impact': impact,
                'critical_issues': high_count,
                'medium_issues': medium_count,
                'low_issues': total_issues - high_count - medium_count
            },
            'database_queries': self.issues['database_queries'],
            'loops': self.issues['loops'],
            'lazy_loading': self.issues['lazy_loading'],
            'memory_usage': self.issues['memory_usage'],
            'caching': self.issues['caching']
        }
        
        return report


def main():
    """Main entry point."""
    # Get plugin root directory
    script_dir = Path(__file__).parent
    plugin_dir = script_dir.parent
    
    print(f"📁 Plugin Directory: {plugin_dir}")
    print(f"📁 Source Directory: {plugin_dir / 'src'}")
    print()
    
    # Run analysis
    analyzer = PHPPerformanceAnalyzer(str(plugin_dir))
    report = analyzer.analyze()
    
    # Ensure reports directory exists
    reports_dir = plugin_dir / 'reports'
    reports_dir.mkdir(exist_ok=True)
    
    # Save report
    report_file = reports_dir / 'php-performance-analysis.json'
    with open(report_file, 'w', encoding='utf-8') as f:
        json.dump(report, f, indent=2)
    
    # Print summary
    print()
    print("=" * 50)
    print("📊 Analysis Complete")
    print("=" * 50)
    print(f"Files Analyzed: {report['summary']['total_files_analyzed']}")
    print(f"Total Issues: {report['summary']['total_issues']}")
    print(f"Performance Impact: {report['summary']['performance_impact'].upper()}")
    print(f"  - Critical: {report['summary']['critical_issues']}")
    print(f"  - Medium: {report['summary']['medium_issues']}")
    print(f"  - Low: {report['summary']['low_issues']}")
    print()
    print(f"📄 Report saved: {report_file}")
    
    return report


if __name__ == '__main__':
    main()
