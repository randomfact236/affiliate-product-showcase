#!/usr/bin/env python3
"""
CSS Quality Audit Script
========================

This script performs a comprehensive analysis of the SCSS codebase and generates
a detailed report covering multiple quality issues.

Detection Scope:
1. Duplicate CSS Rules
2. Long CSS Blocks
3. Repeated Values Requiring Variables
4. Unused CSS Classes
5. Coding Standard Violations

Output: reports/css-quality-audit.json
"""

import json
import os
import re
from collections import defaultdict
from datetime import datetime
from pathlib import Path
from typing import Dict, List, Set, Tuple


class CSSQualityAuditor:
    """Main auditor class for CSS quality analysis."""

    def __init__(self, scss_dir: str, php_dirs: List[str], js_dirs: List[str]):
        self.scss_dir = Path(scss_dir)
        self.php_dirs = [Path(d) for d in php_dirs]
        self.js_dirs = [Path(d) for d in js_dirs]
        self.scss_files = []
        self.php_files = []
        self.js_files = []
        self.used_classes = set()
        self.defined_classes = set()
        self.defined_class_locations = {}
        self.repeated_values = defaultdict(lambda: {"count": 0, "locations": []})

    def scan_files(self):
        """Scan all relevant files in the project."""
        print("Scanning SCSS files...")
        self.scss_files = list(self.scss_dir.rglob("*.scss"))
        print(f"Found {len(self.scss_files)} SCSS files")

        print("Scanning PHP files...")
        for php_dir in self.php_dirs:
            if php_dir.exists():
                self.php_files.extend(php_dir.rglob("*.php"))
        print(f"Found {len(self.php_files)} PHP files")

        print("Scanning JavaScript files...")
        for js_dir in self.js_dirs:
            if js_dir.exists():
                self.js_files.extend(js_dir.rglob("*.js"))
                self.js_files.extend(js_dir.rglob("*.jsx"))
                self.js_files.extend(js_dir.rglob("*.ts"))
                self.js_files.extend(js_dir.rglob("*.tsx"))
        print(f"Found {len(self.js_files)} JavaScript/TypeScript files")

    def extract_classes_from_file(self, file_path: Path) -> Set[str]:
        """Extract CSS class names from a file."""
        classes = set()
        try:
            with open(file_path, "r", encoding="utf-8", errors="ignore") as f:
                content = f.read()

                # Match class="..." or class='...' patterns
                class_pattern = r'class\s*=\s*["\']([^"\']*)["\']'
                matches = re.findall(class_pattern, content)

                for match in matches:
                    # Split by whitespace and filter empty strings
                    for cls in match.split():
                        if cls:
                            classes.add(cls)

                # Also match JavaScript classList.add/remove patterns
                js_class_pattern = r'(?:classList\.(?:add|remove|toggle|contains))\s*\(\s*["\']([^"\']+)["\']'
                js_matches = re.findall(js_class_pattern, content)
                for match in js_matches:
                    if match:
                        classes.add(match)

        except Exception as e:
            print(f"Error reading {file_path}: {e}")

        return classes

    def find_used_classes(self):
        """Find all CSS classes used in PHP and JavaScript files."""
        print("Finding used CSS classes...")
        for php_file in self.php_files:
            classes = self.extract_classes_from_file(php_file)
            self.used_classes.update(classes)

        for js_file in self.js_files:
            classes = self.extract_classes_from_file(js_file)
            self.used_classes.update(classes)

        print(f"Found {len(self.used_classes)} used classes")

    def parse_scss_rules(self, file_path: Path) -> List[Dict]:
        """Parse SCSS file to extract CSS rules and their properties."""
        rules = []
        try:
            with open(file_path, "r", encoding="utf-8", errors="ignore") as f:
                lines = f.readlines()

            current_selector = None
            current_properties = []
            current_line_start = 0
            brace_depth = 0
            in_rule = False

            for i, line in enumerate(lines, 1):
                stripped = line.strip()

                # Skip comments and empty lines
                if stripped.startswith("//") or stripped.startswith("/*") or stripped == "":
                    continue

                # Track brace depth
                if "{" in line:
                    brace_depth += line.count("{")
                if "}" in line:
                    brace_depth -= line.count("}")

                # Start of a rule
                if "{" in line and not in_rule:
                    # Extract selector
                    selector_part = line.split("{")[0].strip()
                    # Remove @ rules like @media, @keyframes
                    if not selector_part.startswith("@"):
                        current_selector = selector_part
                        current_line_start = i
                        in_rule = True
                        current_properties = []

                # Properties within a rule
                elif in_rule and brace_depth > 0:
                    # Extract property: value pairs
                    if ":" in line and not line.strip().startswith("@"):
                        prop_match = re.match(r'(\s*)([\w-]+)\s*:\s*([^;]+);?', line)
                        if prop_match:
                            prop_name = prop_match.group(2)
                            prop_value = prop_match.group(3).strip().rstrip(";")
                            current_properties.append({
                                "line": i,
                                "property": prop_name,
                                "value": prop_value
                            })

                # End of a rule
                elif "}" in line and in_rule and brace_depth == 0:
                    if current_selector and current_properties:
                        rules.append({
                            "file": str(file_path),
                            "line": current_line_start,
                            "selector": current_selector,
                            "properties": current_properties,
                            "property_count": len(current_properties)
                        })

                        # Extract class names from selector
                        class_names = re.findall(r'\.([\w-]+)', current_selector)
                        for cls in class_names:
                            if cls not in self.defined_class_locations:
                                self.defined_class_locations[cls] = []
                            self.defined_class_locations[cls].append({
                                "file": str(file_path),
                                "line": current_line_start,
                                "selector": current_selector
                            })
                            self.defined_classes.add(cls)

                    in_rule = False
                    current_selector = None
                    current_properties = []

        except Exception as e:
            print(f"Error parsing {file_path}: {e}")

        return rules

    def detect_duplicate_rules(self, all_rules: List[Dict]) -> List[Dict]:
        """Detect duplicate CSS rules across files."""
        print("Detecting duplicate CSS rules...")
        duplicates = []

        # Group rules by property signature
        rule_signatures = defaultdict(list)

        for rule in all_rules:
            # Create a signature from sorted properties
            props = rule["properties"]
            signature = tuple(sorted((p["property"], p["value"]) for p in props))
            rule_signatures[signature].append(rule)

        # Find duplicates
        for signature, rules in rule_signatures.items():
            if len(rules) > 1:
                # Check if properties are identical
                severity = "high" if len(rules) > 2 else "medium"
                locations = [f"{r['file']}:{r['line']}" for r in rules]

                duplicates.append({
                    "severity": severity,
                    "file": rules[0]["file"],
                    "line": rules[0]["line"],
                    "selector": rules[0]["selector"],
                    "duplicates_found_at": locations[1:],
                    "suggestion": "Extract to shared mixin or extend placeholder"
                })

        print(f"Found {len(duplicates)} duplicate rule groups")
        return duplicates

    def detect_long_blocks(self, all_rules: List[Dict]) -> List[Dict]:
        """Detect CSS blocks that are too long."""
        print("Detecting long CSS blocks...")
        long_blocks = []

        for rule in all_rules:
            property_count = rule["property_count"]

            # Calculate actual lines (estimate: properties + selectors + braces)
            # More accurate: track end_line in parse_scss_rules
            estimated_lines = property_count + 2  # +2 for selector and closing brace

            # Identify component from selector
            selector = rule["selector"]
            component = selector.split(".")[1].split(" ")[0] if "." in selector else "unknown"

            if estimated_lines > 50 or property_count > 20:
                long_blocks.append({
                    "severity": "high" if estimated_lines > 100 or property_count > 30 else "medium",
                    "file": rule["file"],
                    "line": rule["line"],
                    "line_count": estimated_lines,
                    "property_count": property_count,
                    "component": component,
                    "suggestion": f"Break into smaller components: _{component}.scss, _{component}-header.scss, etc."
                })

        print(f"Found {len(long_blocks)} long blocks")
        return long_blocks

    def detect_repeated_values(self, all_rules: List[Dict]) -> List[Dict]:
        """Detect repeated values that should be variables."""
        print("Detecting repeated values...")
        repeated_values = []

        # Collect all values
        value_counts = defaultdict(lambda: {"count": 0, "locations": [], "type": None})

        for rule in all_rules:
            for prop in rule["properties"]:
                value = prop["value"]
                prop_name = prop["property"]

                # Determine value type
                value_type = None
                if re.match(r'^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$', value):
                    value_type = "color"
                elif re.match(r'^rgba?\([^)]+\)$', value):
                    value_type = "color"
                elif re.match(r'^\d+(px|em|rem|vh|vw|%)$', value):
                    value_type = "spacing"
                elif prop_name in ["font-size", "line-height"]:
                    value_type = "typography"
                elif prop_name in ["margin", "padding", "gap"]:
                    value_type = "spacing"

                if value_type:
                    key = (value_type, value)
                    value_counts[key]["count"] += 1
                    value_counts[key]["type"] = value_type
                    value_counts[key]["locations"].append(f"{rule['file']}:{prop['line']}")

        # Find values that appear 3+ times
        for (value_type, value), info in value_counts.items():
            if info["count"] >= 3:
                suggestion = ""
                if value_type == "color":
                    suggestion = f"Create $color-{self._get_color_name(value)} variable"
                elif value_type == "spacing":
                    suggestion = "Create spacing scale variable"
                elif value_type == "typography":
                    suggestion = "Create typography scale variable"

                repeated_values.append({
                    "severity": "medium",
                    "type": value_type,
                    "value": value,
                    "occurrences": info["count"],
                    "locations": info["locations"][:10],  # Limit to 10 locations
                    "suggestion": suggestion
                })

        print(f"Found {len(repeated_values)} repeated values")
        return repeated_values

    def _get_color_name(self, color: str) -> str:
        """Generate a semantic name for a color value."""
        # Simple heuristic for color naming
        color = color.lower()
        if color in ["#000000", "#000", "black"]:
            return "black"
        elif color in ["#ffffff", "#fff", "white"]:
            return "white"
        elif color in ["#ff0000", "#f00", "red"]:
            return "red"
        elif color in ["#00ff00", "#0f0", "green"]:
            return "green"
        elif color in ["#0000ff", "#00f", "blue"]:
            return "blue"
        elif color in ["#ffff00", "#ff0", "yellow"]:
            return "yellow"
        elif color in ["#ff00ff", "#f0f", "magenta"]:
            return "magenta"
        elif color in ["#00ffff", "#0ff", "cyan"]:
            return "cyan"
        else:
            return "custom"

    def detect_unused_classes(self) -> List[Dict]:
        """Detect unused CSS classes."""
        print("Detecting unused CSS classes...")
        unused_classes = []

        for cls, locations in self.defined_class_locations.items():
            if cls not in self.used_classes:
                is_bem = "--" in cls or "__" in cls

                # Determine confidence
                confidence = "high"
                if is_bem:
                    confidence = "medium"  # BEM might be used dynamically
                if cls.startswith("js-"):
                    confidence = "low"  # JS- prefix indicates dynamic usage

                if confidence == "high" or not is_bem:
                    for loc in locations:
                        unused_classes.append({
                            "severity": "low",
                            "class": f".{cls}",
                            "file": loc["file"],
                            "line": loc["line"],
                            "confidence": confidence,
                            "suggestion": "Remove if truly unused" if confidence == "high" else "Verify usage before removing"
                        })

        print(f"Found {len(unused_classes)} potentially unused classes")
        return unused_classes

    def detect_standard_violations(self) -> List[Dict]:
        """Detect coding standard violations."""
        print("Detecting coding standard violations...")
        violations = []

        for scss_file in self.scss_files:
            try:
                with open(scss_file, "r", encoding="utf-8", errors="ignore") as f:
                    lines = f.readlines()

                for i, line in enumerate(lines, 1):
                    stripped = line.rstrip()

                    # Check for inconsistent indentation (should be 2 spaces)
                    if stripped and not stripped.startswith("//") and not stripped.startswith("/*"):
                        # Count leading spaces
                        leading_spaces = len(line) - len(line.lstrip())
                        if leading_spaces > 0 and leading_spaces % 2 != 0:
                            violations.append({
                                "severity": "low",
                                "type": "indentation",
                                "file": str(scss_file),
                                "line": i,
                                "issue": f"Inconsistent indentation ({leading_spaces} spaces, expected even number)",
                                "suggestion": "Use 2-space indentation"
                            })

                    # Check for missing semicolons
                    if ":" in line and not line.strip().startswith("@"):
                        # Check if property line ends with semicolon
                        if not line.strip().endswith(";") and not line.strip().endswith("{") and not line.strip().endswith("}"):
                            violations.append({
                                "severity": "low",
                                "type": "missing-semicolon",
                                "file": str(scss_file),
                                "line": i,
                                "issue": "Missing semicolon",
                                "suggestion": "Add semicolon at end of property declaration"
                            })

                    # Check for trailing whitespace
                    if line != line.rstrip():
                        violations.append({
                            "severity": "low",
                            "type": "trailing-whitespace",
                            "file": str(scss_file),
                            "line": i,
                            "issue": "Trailing whitespace",
                            "suggestion": "Remove trailing whitespace"
                        })

            except Exception as e:
                print(f"Error checking {scss_file}: {e}")

        print(f"Found {len(violations)} standard violations")
        return violations

    def run_audit(self) -> Dict:
        """Run the complete audit and return results."""
        print("\n" + "="*60)
        print("CSS QUALITY AUDIT")
        print("="*60 + "\n")

        # Step 1: Scan all files
        self.scan_files()

        # Step 2: Find used classes
        self.find_used_classes()

        # Step 3: Parse all SCSS rules
        print("\nParsing SCSS rules...")
        all_rules = []
        for scss_file in self.scss_files:
            rules = self.parse_scss_rules(scss_file)
            all_rules.extend(rules)

        print(f"Found {len(all_rules)} CSS rules")

        # Step 4: Run all detections
        duplicate_rules = self.detect_duplicate_rules(all_rules)
        long_blocks = self.detect_long_blocks(all_rules)
        repeated_values = self.detect_repeated_values(all_rules)
        unused_classes = self.detect_unused_classes()
        standard_violations = self.detect_standard_violations()

        # Step 5: Generate summary
        total_issues = (
            len(duplicate_rules) +
            len(long_blocks) +
            len(repeated_values) +
            len(unused_classes) +
            len(standard_violations)
        )

        high_severity = (
            sum(1 for d in duplicate_rules if d["severity"] == "high") +
            sum(1 for l in long_blocks if l["severity"] == "high")
        )

        medium_severity = (
            sum(1 for d in duplicate_rules if d["severity"] == "medium") +
            sum(1 for l in long_blocks if l["severity"] == "medium") +
            len(repeated_values)
        )

        low_severity = len(unused_classes) + len(standard_violations)

        summary = {
            "total_issues": total_issues,
            "high_severity": high_severity,
            "medium_severity": medium_severity,
            "low_severity": low_severity
        }

        # Step 6: Build final report
        report = {
            "audit_date": datetime.utcnow().isoformat() + "Z",
            "summary": summary,
            "duplicate_rules": duplicate_rules,
            "long_blocks": long_blocks,
            "repeated_values": repeated_values,
            "unused_classes": unused_classes,
            "standard_violations": standard_violations
        }

        return report


def main():
    """Main entry point."""
    # Define directories to scan
    scss_dir = "wp-content/plugins/affiliate-product-showcase/assets/scss"
    php_dirs = [
        "wp-content/plugins/affiliate-product-showcase/src",
        "wp-content/plugins/affiliate-product-showcase/includes",
        "wp-content/plugins/affiliate-product-showcase/templates"
    ]
    js_dirs = [
        "wp-content/plugins/affiliate-product-showcase/assets/js",
        "wp-content/plugins/affiliate-product-showcase/src",
        "wp-content/plugins/affiliate-product-showcase/blocks"
    ]

    # Create auditor and run audit
    auditor = CSSQualityAuditor(scss_dir, php_dirs, js_dirs)
    report = auditor.run_audit()

    # Ensure reports directory exists
    reports_dir = Path("reports")
    reports_dir.mkdir(exist_ok=True)

    # Write report to JSON file
    output_file = reports_dir / "css-quality-audit.json"
    with open(output_file, "w", encoding="utf-8") as f:
        json.dump(report, f, indent=2)

    print("\n" + "="*60)
    print("AUDIT COMPLETE")
    print("="*60)
    print(f"\nReport saved to: {output_file}")
    print(f"\nSummary:")
    print(f"  Total Issues: {report['summary']['total_issues']}")
    print(f"  High Severity: {report['summary']['high_severity']}")
    print(f"  Medium Severity: {report['summary']['medium_severity']}")
    print(f"  Low Severity: {report['summary']['low_severity']}")
    print()


if __name__ == "__main__":
    main()
