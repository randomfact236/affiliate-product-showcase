#!/usr/bin/env python3
"""
PHP Security Audit Script
Detects security vulnerabilities in PHP code:
- Input sanitization issues
- Output escaping issues  
- Missing nonce verification
- Missing capability checks
- SQL injection risks

Usage: python scripts/php-security-audit.py
Output: reports/php-security-audit.json
"""

import json
import re
import os
from datetime import datetime
from pathlib import Path


def find_php_files(base_path):
    """Find all PHP files recursively."""
    php_files = []
    for root, dirs, files in os.walk(base_path):
        # Skip vendor and node_modules
        dirs[:] = [d for d in dirs if d not in ['vendor', 'node_modules', 'tests']]
        for file in files:
            if file.endswith('.php'):
                php_files.append(os.path.join(root, file))
    return php_files


def check_input_sanitization(content, file_path):
    """Check for unsanitized input usage."""
    issues = []
    lines = content.split('\n')
    
    # Patterns for unsanitized input
    patterns = [
        (r'\$_(POST|GET|REQUEST)\[[^\]]+\]\s*;', 'direct_superglobal'),
        (r'\$_(POST|GET|REQUEST)\[[^\]]+\]\s*\)', 'unescaped_input'),
    ]
    
    # Sanitization functions that are acceptable
    sanitization_funcs = [
        'sanitize_text_field', 'sanitize_email', 'sanitize_url',
        'esc_url_raw', 'intval', 'absint', 'wp_kses_post',
        'sanitize_title', 'sanitize_file_name', 'sanitize_key',
        'sanitize_meta', 'sanitize_option', 'sanitize_sql_orderby',
        'wp_kses', 'wp_kses_allowed_html', 'wp_kses_data'
    ]
    
    for line_num, line in enumerate(lines, 1):
        # Skip lines that already have sanitization
        if any(func in line for func in sanitization_funcs):
            continue
        
        # Check for direct superglobal usage
        for pattern, issue_type in patterns:
            if re.search(pattern, line, re.IGNORECASE):
                # Check if it's inside a sanitization function
                if not any(func in line for func in sanitization_funcs):
                    issues.append({
                        'file': file_path,
                        'line': line_num,
                        'type': 'input_sanitization',
                        'severity': 'critical',
                        'code': line.strip(),
                        'issue': f'Unsanitized {issue_type}',
                        'suggestion': 'Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function'
                    })
                    break
    
    return issues


def check_output_escaping(content, file_path):
    """Check for unescaped output."""
    issues = []
    lines = content.split('\n')
    
    # Escaping functions
    escaping_funcs = [
        'esc_html', 'esc_attr', 'esc_url', 'esc_js',
        'esc_textarea', 'esc_sql', 'wp_kses', 'wp_kses_post',
        'wp_kses_data', 'esc_html__', 'esc_attr__', 'esc_url__',
        'esc_html_e', 'esc_attr_e', 'esc_url_e'
    ]
    
    # Patterns for echo/print with variables
    patterns = [
        r'echo\s+\$(\w+)',
        r'echo\s+\$_(POST|GET|REQUEST)',
        r'print\s+\$(\w+)',
    ]
    
    for line_num, line in enumerate(lines, 1):
        # Skip if already escaped
        if any(func in line for func in escaping_funcs):
            continue
        
        # Check for echo with variables
        for pattern in patterns:
            if re.search(pattern, line, re.IGNORECASE):
                issues.append({
                    'file': file_path,
                    'line': line_num,
                    'type': 'output_escaping',
                    'severity': 'high',
                    'code': line.strip(),
                    'issue': 'Unescaped output',
                    'suggestion': 'Use esc_html(), esc_attr(), or appropriate WordPress escaping function'
                })
                break
    
    return issues


def check_nonce_verification(content, file_path):
    """Check for missing nonce verification in forms/AJAX."""
    issues = []
    lines = content.split('\n')
    
    # AJAX handler patterns
    ajax_patterns = [
        r'add_action\s*\(\s*[\'"]wp_ajax_',
        r'add_action\s*\(\s*[\'"]wp_ajax_nopriv_',
    ]
    
    # Form handler patterns
    form_patterns = [
        r'if\s*\(\s*\$_(POST|GET|REQUEST)',
        r'isset\s*\(\s*\$_(POST|GET|REQUEST)',
    ]
    
    # Nonce verification functions
    nonce_funcs = [
        'check_ajax_referer',
        'wp_verify_nonce',
        'check_admin_referer'
    ]
    
    # Find AJAX handlers
    in_ajax_handler = False
    handler_start = 0
    
    for line_num, line in enumerate(lines, 1):
        # Check if this is an AJAX handler
        for pattern in ajax_patterns:
            if re.search(pattern, line):
                in_ajax_handler = True
                handler_start = line_num
                break
        
        # Check for nonce verification
        if in_ajax_handler and any(func in line for func in nonce_funcs):
            in_ajax_handler = False
        
        # If we found an AJAX handler without nonce verification
        if in_ajax_handler and line_num > handler_start + 10:
            issues.append({
                'file': file_path,
                'line': handler_start,
                'type': 'nonce_verification',
                'severity': 'critical',
                'code': lines[handler_start-1].strip(),
                'issue': 'AJAX handler without nonce verification',
                'suggestion': 'Add check_ajax_referer("action_name", "nonce_field") at the start of the handler'
            })
            in_ajax_handler = False
    
    return issues


def check_capability_checks(content, file_path):
    """Check for missing capability checks."""
    issues = []
    lines = content.split('\n')
    
    # Admin operation patterns
    admin_patterns = [
        r'wp_delete_post',
        r'wp_update_post',
        r'wp_insert_post',
        r'delete_post_meta',
        r'update_post_meta',
        r'add_post_meta',
        r'wp_delete_attachment',
        r'wp_delete_comment',
        r'wp_update_comment',
    ]
    
    # Capability check functions
    cap_funcs = [
        'current_user_can',
        'user_can'
    ]
    
    for line_num, line in enumerate(lines, 1):
        for pattern in admin_patterns:
            if re.search(pattern, line, re.IGNORECASE):
                # Check if there's a capability check nearby (within 10 lines before)
                has_check = False
                for i in range(max(0, line_num-10), line_num):
                    if any(func in lines[i] for func in cap_funcs):
                        has_check = True
                        break
                
                if not has_check:
                    issues.append({
                        'file': file_path,
                        'line': line_num,
                        'type': 'capability_checks',
                        'severity': 'high',
                        'code': line.strip(),
                        'issue': 'Admin operation without capability check',
                        'suggestion': 'Add if (!current_user_can("capability")) { wp_die("Unauthorized"); }'
                    })
                break
    
    return issues


def check_sql_injection(content, file_path):
    """Check for SQL injection risks."""
    issues = []
    lines = content.split('\n')
    
    # Direct SQL patterns
    sql_patterns = [
        (r'\$wpdb->query\s*\(\s*["\']', 'direct_query'),
        (r'\$wpdb->get_results\s*\(\s*["\']', 'direct_get_results'),
        (r'\$wpdb->get_var\s*\(\s*["\']', 'direct_get_var'),
        (r'\$wpdb->get_col\s*\(\s*["\']', 'direct_get_col'),
    ]
    
    # Safe patterns
    safe_patterns = [
        r'\$wpdb->prepare',
    ]
    
    for line_num, line in enumerate(lines, 1):
        for pattern, issue_type in sql_patterns:
            if re.search(pattern, line, re.IGNORECASE):
                # Check if it's prepared
                if not any(re.search(safe, line, re.IGNORECASE) for safe in safe_patterns):
                    # Check for variable interpolation
                    if re.search(r'\$\w+', line) or re.search(r'\{.*\}', line):
                        issues.append({
                            'file': file_path,
                            'line': line_num,
                            'type': 'sql_injection',
                            'severity': 'critical',
                            'code': line.strip(),
                            'issue': 'Potential SQL injection risk',
                            'suggestion': 'Use $wpdb->prepare("SELECT * FROM table WHERE id = %d", $id)'
                        })
                break
    
    return issues


def analyze_file(file_path):
    """Analyze a single PHP file."""
    try:
        with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
            content = f.read()
    except Exception as e:
        print(f"Error reading {file_path}: {e}")
        return []
    
    issues = []
    issues.extend(check_input_sanitization(content, file_path))
    issues.extend(check_output_escaping(content, file_path))
    issues.extend(check_nonce_verification(content, file_path))
    issues.extend(check_capability_checks(content, file_path))
    issues.extend(check_sql_injection(content, file_path))
    
    return issues


def main():
    print("=" * 60)
    print("PHP Security Audit")
    print("=" * 60)
    
    # Target directory
    target_dir = 'wp-content/plugins/affiliate-product-showcase/src'
    
    if not os.path.exists(target_dir):
        print(f"❌ Directory not found: {target_dir}")
        return
    
    print(f"\nScanning: {target_dir}")
    
    # Find PHP files
    php_files = find_php_files(target_dir)
    print(f"Found {len(php_files)} PHP files\n")
    
    # Analyze each file
    all_issues = []
    for i, file_path in enumerate(php_files, 1):
        print(f"  [{i}/{len(php_files)}] {os.path.basename(file_path)}")
        issues = analyze_file(file_path)
        all_issues.extend(issues)
    
    # Categorize issues
    input_sanitization = [i for i in all_issues if i['type'] == 'input_sanitization']
    output_escaping = [i for i in all_issues if i['type'] == 'output_escaping']
    nonce_verification = [i for i in all_issues if i['type'] == 'nonce_verification']
    capability_checks = [i for i in all_issues if i['type'] == 'capability_checks']
    sql_injection = [i for i in all_issues if i['type'] == 'sql_injection']
    
    # Count by severity
    critical = len([i for i in all_issues if i['severity'] == 'critical'])
    high = len([i for i in all_issues if i['severity'] == 'high'])
    medium = len([i for i in all_issues if i['severity'] == 'medium'])
    low = len([i for i in all_issues if i['severity'] == 'low'])
    
    # Create report
    report = {
        'audit_date': datetime.now().isoformat(),
        'summary': {
            'total_files': len(php_files),
            'total_issues': len(all_issues),
            'critical': critical,
            'high': high,
            'medium': medium,
            'low': low
        },
        'input_sanitization': input_sanitization,
        'output_escaping': output_escaping,
        'nonce_verification': nonce_verification,
        'capability_checks': capability_checks,
        'sql_injection': sql_injection
    }
    
    # Ensure reports directory exists
    os.makedirs('reports', exist_ok=True)
    
    # Save report
    output_file = 'reports/php-security-audit.json'
    with open(output_file, 'w') as f:
        json.dump(report, f, indent=2)
    
    # Print summary
    print("\n" + "=" * 60)
    print("Security Audit Summary")
    print("=" * 60)
    print(f"\n  Files Analyzed: {len(php_files)}")
    print(f"  Total Issues: {len(all_issues)}")
    print(f"\n  By Severity:")
    print(f"    Critical: {critical}")
    print(f"    High: {high}")
    print(f"    Medium: {medium}")
    print(f"    Low: {low}")
    print(f"\n  By Category:")
    print(f"    Input Sanitization: {len(input_sanitization)}")
    print(f"    Output Escaping: {len(output_escaping)}")
    print(f"    Nonce Verification: {len(nonce_verification)}")
    print(f"    Capability Checks: {len(capability_checks)}")
    print(f"    SQL Injection: {len(sql_injection)}")
    print(f"\nReport saved: {output_file}")
    print("=" * 60)


if __name__ == '__main__':
    main()
