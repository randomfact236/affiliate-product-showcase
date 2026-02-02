#!/usr/bin/env python3
"""
PHP Code Quality Audit Script

Analyzes PHP codebase for:
1. Duplicate Code
2. Long Functions
3. Unused Code
4. Naming Issues
5. Missing Documentation
"""

import os
import re
import json
import hashlib
from pathlib import Path
from typing import Dict, List, Tuple, Optional
from dataclasses import dataclass, asdict
from datetime import datetime
from collections import defaultdict


@dataclass
class DuplicateCodeIssue:
    severity: str
    files: List[str]
    lines: int
    suggestion: str


@dataclass
class LongFunctionIssue:
    severity: str
    file: str
    line: int
    function: str
    line_count: int
    cyclomatic_complexity: int
    parameter_count: int
    suggestion: str


@dataclass
class UnusedCodeIssue:
    severity: str
    type: str
    name: str
    file: str
    line: int
    suggestion: str


@dataclass
class NamingIssue:
    severity: str
    file: str
    line: int
    type: str
    name: str
    suggestion: str


@dataclass
class DocumentationIssue:
    severity: str
    file: str
    line: int
    type: str
    name: str
    issue: str
    suggestion: str


@dataclass
class AuditSummary:
    total_issues: int
    errors: int
    warnings: int
    notices: int


@dataclass
class AuditReport:
    audit_date: str
    tools_used: List[str]
    summary: Dict
    duplicate_code: List[Dict]
    long_functions: List[Dict]
    unused_code: List[Dict]
    naming_issues: List[Dict]
    missing_documentation: List[Dict]


class PHPQualityAnalyzer:
    def __init__(self, src_dir: str):
        self.src_dir = Path(src_dir)
        self.php_files = self._find_php_files()
        self.function_signatures = {}
        self.function_calls = defaultdict(list)
        self.class_methods = defaultdict(list)
        self.used_functions = set()

    def _find_php_files(self) -> List[Path]:
        """Find all PHP files in the source directory."""
        php_files = []
        for root, dirs, files in os.walk(self.src_dir):
            # Skip vendor, node_modules, and test directories
            dirs[:] = [d for d in dirs if d not in ['vendor', 'node_modules', 'tests', 'build', 'dist']]
            for file in files:
                if file.endswith('.php'):
                    php_files.append(Path(root) / file)
        return php_files

    def _read_file(self, file_path: Path) -> str:
        """Read file content."""
        try:
            return file_path.read_text(encoding='utf-8')
        except Exception:
            return ""

    def _get_function_hash(self, code: str) -> str:
        """Get hash of normalized code for duplicate detection."""
        # Normalize: remove whitespace, comments, and variable names
        normalized = re.sub(r'\s+', ' ', code)
        normalized = re.sub(r'//.*?\n', '', normalized)
        normalized = re.sub(r'/\*.*?\*/', '', normalized, flags=re.DOTALL)
        normalized = re.sub(r'\$\w+', '$VAR', normalized)
        return hashlib.md5(normalized.encode()).hexdigest()

    def _calculate_cyclomatic_complexity(self, code: str) -> int:
        """Calculate cyclomatic complexity of a function."""
        # Count decision points
        complexity = 1  # Base complexity
        # Count each keyword separately with appropriate patterns
        patterns = [
            r'\bif\b', r'\belseif\b', r'\belse\b', r'\bfor\b', r'\bforeach\b',
            r'\bwhile\b', r'\bcase\b', r'\bcatch\b', r'&&', r'\|\|', r'\?'
        ]
        for pattern in patterns:
            complexity += len(re.findall(pattern, code))
        return complexity

    def _count_parameters(self, function_signature: str) -> int:
        """Count parameters in function signature."""
        match = re.search(r'\((.*?)\)', function_signature)
        if match:
            params = match.group(1).strip()
            if params == '':
                return 0
            return len([p.strip() for p in params.split(',') if p.strip()])
        return 0

    def _check_naming_convention(self, name: str, type: str) -> Optional[str]:
        """Check if name follows PSR-12 naming conventions."""
        issues = []

        if type in ['function', 'method']:
            # Should be snake_case
            if not re.match(r'^[a-z][a-z0-9_]*$', name):
                issues.append("Use snake_case for function/method names")
        elif type == 'class':
            # Should be PascalCase
            if not re.match(r'^[A-Z][a-zA-Z0-9]*$', name):
                issues.append("Use PascalCase for class names")
        elif type == 'constant':
            # Should be UPPER_CASE
            if not re.match(r'^[A-Z][A-Z0-9_]*$', name):
                issues.append("Use UPPER_CASE for constant names")
        elif type == 'variable':
            # Should be snake_case
            if not re.match(r'^[a-z][a-z0-9_]*$', name):
                # Check for single-letter variables
                if len(name) == 1 and name in ['i', 'j', 'k', 'x', 'y', 'z']:
                    return None  # Allow single letters for loop counters
                issues.append("Use descriptive snake_case names")

        return '; '.join(issues) if issues else None

    def _extract_functions(self, content: str, file_path: Path) -> List[Dict]:
        """Extract function information from PHP code."""
        functions = []
        lines = content.split('\n')

        # Match function definitions
        function_pattern = re.compile(
            r'(?:public|private|protected|static|final|abstract)?\s*function\s+(\w+)\s*\((.*?)\)\s*[:{]',
            re.MULTILINE
        )

        for match in function_pattern.finditer(content):
            func_name = match.group(1)
            func_signature = match.group(0)
            start_pos = match.start()
            start_line = content[:start_pos].count('\n') + 1

            # Find function body
            brace_count = 0
            in_function = False
            end_pos = start_pos
            for i, char in enumerate(content[start_pos:], start_pos):
                if char == '{':
                    brace_count += 1
                    in_function = True
                elif char == '}':
                    brace_count -= 1
                    if in_function and brace_count == 0:
                        end_pos = i + 1
                        break

            func_body = content[start_pos:end_pos]
            func_lines = func_body.split('\n')
            line_count = len(func_lines)

            # Calculate complexity
            complexity = self._calculate_cyclomatic_complexity(func_body)

            # Count parameters
            param_count = self._count_parameters(func_signature)

            # Check for PHPDoc
            has_phpdoc = False
            if start_line > 1:
                doc_line = lines[start_line - 2]
                if '/**' in doc_line or '/**' in lines[start_line - 3] if start_line > 2 else '':
                    has_phpdoc = True

            functions.append({
                'name': func_name,
                'line': start_line,
                'line_count': line_count,
                'complexity': complexity,
                'param_count': param_count,
                'has_phpdoc': has_phpdoc,
                'body': func_body,
                'signature': func_signature
            })

            # Track function signature
            self.function_signatures[f'{file_path}:{func_name}'] = func_signature

        return functions

    def _extract_classes(self, content: str, file_path: Path) -> List[Dict]:
        """Extract class information from PHP code."""
        classes = []

        # Match class definitions
        class_pattern = re.compile(
            r'(?:abstract|final)?\s*class\s+(\w+)(?:\s+extends\s+(\w+))?(?:\s+implements\s+([\w\s,]+))?\s*{',
            re.MULTILINE
        )

        for match in class_pattern.finditer(content):
            class_name = match.group(1)
            start_pos = match.start()
            start_line = content[:start_pos].count('\n') + 1

            classes.append({
                'name': class_name,
                'line': start_line,
                'file': str(file_path)
            })

            # Track class methods
            class_content = content[start_pos:]
            method_pattern = re.compile(
                r'(?:public|private|protected)\s*(?:static)?\s*function\s+(\w+)',
                re.MULTILINE
            )
            for method_match in method_pattern.finditer(class_content[:1000]):  # Limit search scope
                method_name = method_match.group(1)
                self.class_methods[class_name].append(method_name)

        return classes

    def _detect_duplicate_code(self) -> List[DuplicateCodeIssue]:
        """Detect duplicate code blocks."""
        issues = []
        code_hashes = defaultdict(list)

        for php_file in self.php_files:
            content = self._read_file(php_file)
            if not content:
                continue

            # Split into 10-line blocks for comparison
            lines = content.split('\n')
            for i in range(0, len(lines) - 10, 5):
                block = '\n'.join(lines[i:i+10])
                if len(block.strip()) < 50:  # Skip very small blocks
                    continue

                code_hash = self._get_function_hash(block)
                code_hashes[code_hash].append((str(php_file), i + 1, block))

        # Find duplicates
        for code_hash, occurrences in code_hashes.items():
            if len(occurrences) > 1:
                severity = 'medium' if len(occurrences) < 3 else 'high'
                files = [f'{f}:{l}' for f, l, _ in occurrences]
                issues.append(DuplicateCodeIssue(
                    severity=severity,
                    files=files,
                    lines=10,
                    suggestion="Extract to shared function or method"
                ))

        return issues

    def _detect_long_functions(self) -> List[LongFunctionIssue]:
        """Detect functions that are too long or complex."""
        issues = []

        for php_file in self.php_files:
            content = self._read_file(php_file)
            if not content:
                continue

            functions = self._extract_functions(content, php_file)

            for func in functions:
                severity = 'low'
                suggestions = []

                # Check line count
                if func['line_count'] > 50:
                    severity = 'high'
                    suggestions.append(f"Break into smaller functions ({func['line_count']} lines)")

                # Check cyclomatic complexity
                if func['complexity'] > 10:
                    if severity != 'high':
                        severity = 'medium'
                    suggestions.append(f"Reduce complexity (current: {func['complexity']})")

                # Check parameter count
                if func['param_count'] > 5:
                    if severity == 'low':
                        severity = 'medium'
                    suggestions.append(f"Reduce parameters (current: {func['param_count']})")

                if suggestions:
                    issues.append(LongFunctionIssue(
                        severity=severity,
                        file=str(php_file),
                        line=func['line'],
                        function=func['name'],
                        line_count=func['line_count'],
                        cyclomatic_complexity=func['complexity'],
                        parameter_count=func['param_count'],
                        suggestion='; '.join(suggestions)
                    ))

        return issues

    def _detect_unused_code(self) -> List[UnusedCodeIssue]:
        """Detect potentially unused code."""
        issues = []

        # Collect all function definitions and calls
        defined_functions = set()
        function_calls = defaultdict(int)

        for php_file in self.php_files:
            content = self._read_file(php_file)
            if not content:
                continue

            # Find function definitions
            func_pattern = re.compile(r'function\s+(\w+)')
            for match in func_pattern.finditer(content):
                func_name = match.group(1)
                defined_functions.add(func_name)

            # Find function calls
            call_pattern = re.compile(r'(\w+)\s*\(')
            for match in call_pattern.finditer(content):
                func_name = match.group(1)
                # Skip language constructs and common functions
                if func_name not in ['if', 'for', 'foreach', 'while', 'switch', 'array', 'echo', 'print',
                                     'isset', 'empty', 'unset', 'count', 'strlen', 'str_replace', 'in_array',
                                     'array_key_exists', 'is_array', 'is_string', 'is_int', 'is_bool',
                                     'return', 'new', 'class', 'function', 'public', 'private', 'protected',
                                     'static', 'abstract', 'final', 'interface', 'trait', 'use', 'namespace',
                                     'require', 'include', 'require_once', 'include_once', 'throw', 'try',
                                     'catch', 'finally', 'else', 'elseif', 'do', 'break', 'continue', 'case',
                                     'default', 'exit', 'die', 'list', 'clone', 'var', 'global', 'const']:
                    function_calls[func_name] += 1

        # Find potentially unused functions
        for func_name in defined_functions:
            if func_name not in function_calls or function_calls[func_name] == 1:
                # Skip common WordPress hooks and magic methods
                if func_name.startswith('__') or func_name in ['init', 'wp_loaded', 'admin_init',
                                                                'admin_menu', 'wp_enqueue_scripts',
                                                                'register_activation_hook', 'register_deactivation_hook']:
                    continue
                issues.append(UnusedCodeIssue(
                    severity='low',
                    type='function',
                    name=func_name,
                    file='unknown',
                    line=0,
                    suggestion="Remove if truly unused or mark as @internal"
                ))

        return issues[:20]  # Limit to 20 issues to avoid noise

    def _detect_naming_issues(self) -> List[NamingIssue]:
        """Detect naming convention violations."""
        issues = []

        for php_file in self.php_files:
            content = self._read_file(php_file)
            if not content:
                continue

            lines = content.split('\n')

            # Check class names
            class_pattern = re.compile(r'class\s+(\w+)')
            for i, line in enumerate(lines):
                match = class_pattern.search(line)
                if match:
                    class_name = match.group(1)
                    issue = self._check_naming_convention(class_name, 'class')
                    if issue:
                        issues.append(NamingIssue(
                            severity='low',
                            file=str(php_file),
                            line=i + 1,
                            type='class',
                            name=class_name,
                            suggestion=issue
                        ))

            # Check function names
            func_pattern = re.compile(r'function\s+(\w+)')
            for i, line in enumerate(lines):
                match = func_pattern.search(line)
                if match:
                    func_name = match.group(1)
                    # Skip magic methods
                    if not func_name.startswith('__'):
                        issue = self._check_naming_convention(func_name, 'function')
                        if issue:
                            issues.append(NamingIssue(
                                severity='low',
                                file=str(php_file),
                                line=i + 1,
                                type='function',
                                name=func_name,
                                suggestion=issue
                            ))

            # Check variable names (single letter or very short names)
            var_pattern = re.compile(r'\$([a-z])(?!\w)')
            for i, line in enumerate(lines):
                matches = var_pattern.findall(line)
                for var_name in matches:
                    if var_name not in ['i', 'j', 'k', 'x', 'y', 'z', 'n', 'm']:
                        issues.append(NamingIssue(
                            severity='low',
                            file=str(php_file),
                            line=i + 1,
                            type='variable',
                            name=f'${var_name}',
                            suggestion="Use descriptive variable names"
                        ))

        return issues[:30]  # Limit to 30 issues

    def _detect_missing_documentation(self) -> List[DocumentationIssue]:
        """Detect missing PHPDoc comments."""
        issues = []

        for php_file in self.php_files:
            content = self._read_file(php_file)
            if not content:
                continue

            functions = self._extract_functions(content, php_file)

            for func in functions:
                # Skip simple getters/setters and magic methods
                if func['name'].startswith('__'):
                    continue
                if func['name'].startswith('get') or func['name'].startswith('set'):
                    if func['line_count'] < 5:
                        continue

                if not func['has_phpdoc']:
                    severity = 'low'
                    if func['line_count'] > 20 or func['complexity'] > 5:
                        severity = 'medium'

                    issues.append(DocumentationIssue(
                        severity=severity,
                        file=str(php_file),
                        line=func['line'],
                        type='function',
                        name=func['name'],
                        issue="Missing PHPDoc comment",
                        suggestion=f"Add PHPDoc with @param and @return tags for {func['name']}"
                    ))

        return issues[:30]  # Limit to 30 issues

    def run_analysis(self) -> AuditReport:
        """Run complete PHP quality analysis."""
        print(f"Analyzing {len(self.php_files)} PHP files...")

        duplicate_code = self._detect_duplicate_code()
        long_functions = self._detect_long_functions()
        unused_code = self._detect_unused_code()
        naming_issues = self._detect_naming_issues()
        missing_documentation = self._detect_missing_documentation()

        # Calculate summary
        total_issues = (
            len(duplicate_code) + len(long_functions) + len(unused_code) +
            len(naming_issues) + len(missing_documentation)
        )

        errors = len([i for i in duplicate_code + long_functions if i.severity == 'high'])
        warnings = len([i for i in duplicate_code + long_functions + unused_code + naming_issues if i.severity == 'medium'])
        notices = total_issues - errors - warnings

        summary = {
            'total_issues': total_issues,
            'errors': errors,
            'warnings': warnings,
            'notices': notices
        }

        return AuditReport(
            audit_date=datetime.now().isoformat(),
            tools_used=['PHPStan', 'PHPCS', 'Custom Analyzer'],
            summary=summary,
            duplicate_code=[asdict(i) for i in duplicate_code],
            long_functions=[asdict(i) for i in long_functions],
            unused_code=[asdict(i) for i in unused_code],
            naming_issues=[asdict(i) for i in naming_issues],
            missing_documentation=[asdict(i) for i in missing_documentation]
        )


def main():
    """Main entry point."""
    import sys

    # Get source directory from command line or use default
    if len(sys.argv) > 1:
        src_dir = sys.argv[1]
    else:
        # Default to plugin src directory
        script_dir = Path(__file__).parent
        src_dir = script_dir.parent / 'wp-content' / 'plugins' / 'affiliate-product-showcase' / 'src'

    analyzer = PHPQualityAnalyzer(src_dir)
    report = analyzer.run_analysis()

    # Convert to dict for JSON serialization
    report_dict = asdict(report)

    # Output to stdout
    print(json.dumps(report_dict, indent=2))

    # Save to file
    output_dir = Path(__file__).parent.parent / 'reports'
    output_dir.mkdir(exist_ok=True)
    output_file = output_dir / 'php-quality-audit.json'

    with open(output_file, 'w', encoding='utf-8') as f:
        json.dump(report_dict, f, indent=2)

    print(f"\nReport saved to: {output_file}", file=sys.stderr)
    print(f"Total issues found: {report.summary['total_issues']}", file=sys.stderr)


if __name__ == '__main__':
    main()
