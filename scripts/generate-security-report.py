#!/usr/bin/env python3
"""
Generate Markdown Security Report from JSON audit results.
Output: reports/php-security-audit-report.md
"""

import json
import os
from datetime import datetime


def escape_code(code):
    """Escape code for markdown display."""
    return code.replace('|', '\\|').replace('\n', ' ').strip()


def generate_report():
    # Load JSON report
    json_file = 'reports/php-security-audit.json'
    if not os.path.exists(json_file):
        print(f"Error: {json_file} not found. Run php-security-audit.py first.")
        return

    with open(json_file, 'r', encoding='utf-8') as f:
        data = json.load(f)

    # Generate markdown
    md_lines = []
    md_lines.append("# PHP Security Audit Report")
    md_lines.append("")
    md_lines.append(f"**Generated:** {data['audit_date']}")
    md_lines.append("")
    md_lines.append("## Summary")
    md_lines.append("")
    md_lines.append("| Metric | Count |")
    md_lines.append("|--------|-------|")
    md_lines.append(f"| Files Analyzed | {data['summary']['total_files']} |")
    md_lines.append(f"| Total Issues | {data['summary']['total_issues']} |")
    md_lines.append(f"| Critical | {data['summary']['critical']} |")
    md_lines.append(f"| High | {data['summary']['high']} |")
    md_lines.append(f"| Medium | {data['summary']['medium']} |")
    md_lines.append(f"| Low | {data['summary']['low']} |")
    md_lines.append("")

    # Input Sanitization Issues
    if data['input_sanitization']:
        md_lines.append("---")
        md_lines.append("")
        md_lines.append(f"## Input Sanitization Issues ({len(data['input_sanitization'])})")
        md_lines.append("")
        md_lines.append("| File | Line | Code | Issue | Solution |")
        md_lines.append("|------|------|------|-------|----------|")
        
        for issue in data['input_sanitization'][:50]:  # Show first 50
            file_path = issue['file'].replace('\\', '/')
            line = issue['line']
            code = escape_code(issue['code'])
            if len(code) > 80:
                code = code[:77] + "..."
            issue_desc = issue['issue']
            solution = issue['suggestion']
            md_lines.append(f"| `{file_path}` | {line} | `{code}` | {issue_desc} | {solution} |")
        
        if len(data['input_sanitization']) > 50:
            md_lines.append(f"| ... | ... | *{len(data['input_sanitization']) - 50} more issues* | ... | ... |")
        md_lines.append("")

    # Output Escaping Issues
    if data['output_escaping']:
        md_lines.append("---")
        md_lines.append("")
        md_lines.append(f"## Output Escaping Issues ({len(data['output_escaping'])})")
        md_lines.append("")
        md_lines.append("| File | Line | Code | Issue | Solution |")
        md_lines.append("|------|------|------|-------|----------|")
        
        for issue in data['output_escaping'][:50]:
            file_path = issue['file'].replace('\\', '/')
            line = issue['line']
            code = escape_code(issue['code'])
            if len(code) > 80:
                code = code[:77] + "..."
            issue_desc = issue['issue']
            solution = issue['suggestion']
            md_lines.append(f"| `{file_path}` | {line} | `{code}` | {issue_desc} | {solution} |")
        
        if len(data['output_escaping']) > 50:
            md_lines.append(f"| ... | ... | *{len(data['output_escaping']) - 50} more issues* | ... | ... |")
        md_lines.append("")

    # Capability Checks Issues
    if data['capability_checks']:
        md_lines.append("---")
        md_lines.append("")
        md_lines.append(f"## Capability Check Issues ({len(data['capability_checks'])})")
        md_lines.append("")
        md_lines.append("| File | Line | Code | Issue | Solution |")
        md_lines.append("|------|------|------|-------|----------|")
        
        for issue in data['capability_checks'][:50]:
            file_path = issue['file'].replace('\\', '/')
            line = issue['line']
            code = escape_code(issue['code'])
            if len(code) > 80:
                code = code[:77] + "..."
            issue_desc = issue['issue']
            solution = issue['suggestion']
            md_lines.append(f"| `{file_path}` | {line} | `{code}` | {issue_desc} | {solution} |")
        
        if len(data['capability_checks']) > 50:
            md_lines.append(f"| ... | ... | *{len(data['capability_checks']) - 50} more issues* | ... | ... |")
        md_lines.append("")

    # Nonce Verification Issues
    if data['nonce_verification']:
        md_lines.append("---")
        md_lines.append("")
        md_lines.append(f"## Nonce Verification Issues ({len(data['nonce_verification'])})")
        md_lines.append("")
        md_lines.append("| File | Line | Code | Issue | Solution |")
        md_lines.append("|------|------|------|-------|----------|")
        
        for issue in data['nonce_verification']:
            file_path = issue['file'].replace('\\', '/')
            line = issue['line']
            code = escape_code(issue['code'])
            if len(code) > 80:
                code = code[:77] + "..."
            issue_desc = issue['issue']
            solution = issue['suggestion']
            md_lines.append(f"| `{file_path}` | {line} | `{code}` | {issue_desc} | {solution} |")
        md_lines.append("")

    # SQL Injection Issues
    if data['sql_injection']:
        md_lines.append("---")
        md_lines.append("")
        md_lines.append(f"## SQL Injection Issues ({len(data['sql_injection'])})")
        md_lines.append("")
        md_lines.append("| File | Line | Code | Issue | Solution |")
        md_lines.append("|------|------|------|-------|----------|")
        
        for issue in data['sql_injection']:
            file_path = issue['file'].replace('\\', '/')
            line = issue['line']
            code = escape_code(issue['code'])
            if len(code) > 80:
                code = code[:77] + "..."
            issue_desc = issue['issue']
            solution = issue['suggestion']
            md_lines.append(f"| `{file_path}` | {line} | `{code}` | {issue_desc} | {solution} |")
        md_lines.append("")

    # Recommendations
    md_lines.append("---")
    md_lines.append("")
    md_lines.append("## Recommendations")
    md_lines.append("")
    md_lines.append("### Immediate Action Required")
    md_lines.append("")
    md_lines.append("1. **Review SQL Injection Issues** - These pose the highest security risk")
    md_lines.append("2. **Fix Input Sanitization** - Use WordPress sanitization functions:")
    md_lines.append("   - `sanitize_text_field()` for text inputs")
    md_lines.append("   - `sanitize_email()` for emails")
    md_lines.append("   - `esc_url_raw()` for URLs")
    md_lines.append("   - `intval()` / `absint()` for integers")
    md_lines.append("3. **Add Output Escaping** - Use WordPress escaping functions:")
    md_lines.append("   - `esc_html()` for HTML content")
    md_lines.append("   - `esc_attr()` for HTML attributes")
    md_lines.append("   - `esc_url()` for URLs")
    md_lines.append("   - `esc_js()` for JavaScript")
    md_lines.append("4. **Add Capability Checks** - Use `current_user_can()` before admin operations")
    md_lines.append("")
    md_lines.append("### Quick Fix Examples")
    md_lines.append("")
    md_lines.append("**Before (Unsanitized):**")
    md_lines.append("```php")
    md_lines.append('$name = $_POST["name"];')
    md_lines.append("```")
    md_lines.append("")
    md_lines.append("**After (Sanitized):**")
    md_lines.append("```php")
    md_lines.append('$name = sanitize_text_field($_POST["name"]);')
    md_lines.append("```")
    md_lines.append("")
    md_lines.append("**Before (Unescaped):**")
    md_lines.append("```php")
    md_lines.append('echo $name;')
    md_lines.append("```")
    md_lines.append("")
    md_lines.append("**After (Escaped):**")
    md_lines.append("```php")
    md_lines.append('echo esc_html($name);')
    md_lines.append("```")
    md_lines.append("")

    # Write report
    output_file = 'reports/php-security-audit-report.md'
    with open(output_file, 'w', encoding='utf-8') as f:
        f.write('\n'.join(md_lines))

    print(f"Report generated: {output_file}")


if __name__ == '__main__':
    generate_report()
