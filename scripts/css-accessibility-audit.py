#!/usr/bin/env python3
"""
CSS Accessibility Audit Script

Analyzes SCSS files for WCAG accessibility violations including:
1. Missing Focus States
2. Color Contrast Violations
3. Text Resize Issues
4. Hidden Content Issues

Output: reports/css-accessibility-audit.json
"""

import json
import re
import os
from datetime import datetime, timezone
from pathlib import Path
from typing import Dict, List, Tuple, Optional
from dataclasses import dataclass, asdict


@dataclass
class FocusStateIssue:
    severity: str
    file: str
    line: int
    element: str
    issue: str
    wcag_criterion: str
    suggestion: str


@dataclass
class ColorContrastIssue:
    severity: str
    file: str
    line: int
    foreground: str
    background: str
    ratio: float
    wcag_aa_required: float
    wcag_aaa_required: float
    wcag_criterion: str
    suggestion: str


@dataclass
class TextResizeIssue:
    severity: str
    file: str
    line: int
    property: str
    value: str
    issue: str
    wcag_criterion: str
    suggestion: str


@dataclass
class HiddenContentIssue:
    severity: str
    file: str
    line: int
    property: str
    value: str
    issue: str
    wcag_criterion: str
    suggestion: str


class ColorContrastCalculator:
    """Calculate WCAG color contrast ratios"""

    @staticmethod
    def hex_to_rgb(hex_color: str) -> Tuple[float, float, float]:
        """Convert hex color to RGB values (0-255)"""
        hex_color = hex_color.lstrip('#')
        if len(hex_color) == 3:
            hex_color = ''.join([c * 2 for c in hex_color])
        if len(hex_color) != 6:
            return (0, 0, 0)
        r = int(hex_color[0:2], 16)
        g = int(hex_color[2:4], 16)
        b = int(hex_color[4:6], 16)
        return (r, g, b)

    @staticmethod
    def get_luminance(rgb: Tuple[float, float, float]) -> float:
        """Calculate relative luminance from RGB values"""
        r, g, b = [x / 255.0 for x in rgb]
        r = ColorContrastCalculator._linearize(r)
        g = ColorContrastCalculator._linearize(g)
        b = ColorContrastCalculator._linearize(b)
        return 0.2126 * r + 0.7152 * g + 0.0722 * b

    @staticmethod
    def _linearize(c: float) -> float:
        """Linearize a color component"""
        if c <= 0.03928:
            return c / 12.92
        return ((c + 0.055) / 1.055) ** 2.4

    @staticmethod
    def contrast_ratio(fg_hex: str, bg_hex: str) -> float:
        """Calculate contrast ratio between two hex colors"""
        fg_rgb = ColorContrastCalculator.hex_to_rgb(fg_hex)
        bg_rgb = ColorContrastCalculator.hex_to_rgb(bg_hex)
        fg_lum = ColorContrastCalculator.get_luminance(fg_rgb)
        bg_lum = ColorContrastCalculator.get_luminance(bg_rgb)
        lighter = max(fg_lum, bg_lum)
        darker = min(fg_lum, bg_lum)
        if darker == 0:
            return 21.0  # Maximum contrast
        return (lighter + 0.05) / (darker + 0.05)


class SCSSAccessibilityAuditor:
    """Audits SCSS files for accessibility issues"""

    # Interactive elements that should have focus states
    INTERACTIVE_ELEMENTS = [
        'button', 'a', 'input', 'select', 'textarea',
        '[role="button"]', '[role="link"]', '[role="tab"]',
        '[tabindex]', '.aps-button', '.aps-link'
    ]

    # Properties that define text color
    COLOR_PROPERTIES = ['color', 'background-color', 'background', 'border-color']

    # Properties that define text size
    FONT_SIZE_PROPERTIES = ['font-size', 'line-height']

    # Properties that hide content
    HIDING_PROPERTIES = ['display', 'visibility', 'text-indent', 'opacity']

    def __init__(self, scss_dir: str):
        self.scss_dir = Path(scss_dir)
        self.focus_issues: List[FocusStateIssue] = []
        self.contrast_issues: List[ColorContrastIssue] = []
        self.resize_issues: List[TextResizeIssue] = []
        self.hidden_issues: List[HiddenContentIssue] = []

    def audit(self) -> Dict:
        """Run full accessibility audit"""
        print("Starting CSS Accessibility Audit...")
        print(f"Scanning directory: {self.scss_dir}")

        scss_files = list(self.scss_dir.rglob('*.scss'))
        print(f"Found {len(scss_files)} SCSS files")

        for scss_file in scss_files:
            self._audit_file(scss_file)

        result = self._generate_report()
        self._save_report(result)

        return result

    def _audit_file(self, file_path: Path):
        """Audit a single SCSS file"""
        try:
            with open(file_path, 'r', encoding='utf-8') as f:
                content = f.read()
                lines = content.split('\n')

            self._check_focus_states(file_path, content, lines)
            self._check_color_contrast(file_path, content, lines)
            self._check_text_resize(file_path, content, lines)
            self._check_hidden_content(file_path, content, lines)

        except Exception as e:
            print(f"Error processing {file_path}: {e}")

    def _check_focus_states(self, file_path: Path, content: str, lines: List[str]):
        """Check for missing focus states on interactive elements"""
        # Process line by line to maintain accurate line numbers
        i = 0
        while i < len(lines):
            line = lines[i].strip()

            # Skip empty lines and comments
            if not line or line.startswith('//') or line.startswith('/*'):
                i += 1
                continue

            # Check if this line starts a selector block (ends with {)
            if line.endswith('{'):
                selector = line[:-1].strip()

                # Skip SCSS directives
                if self._is_scss_directive(selector):
                    i += 1
                    continue

                # Skip pseudo-elements and non-interactive pseudo-classes
                if any(x in selector for x in ['::before', '::after', '::first-letter', '::first-line']):
                    i += 1
                    continue

                # Skip if it's only a hover/active state (not base element)
                if any(x in selector for x in [':hover', ':active', ':visited', ':focus-within']):
                    i += 1
                    continue

                # Check if selector is interactive
                is_interactive = self._is_interactive_selector(selector)

                if is_interactive:
                    # Collect the block content
                    block_content = ''
                    brace_count = 1
                    j = i + 1

                    while j < len(lines) and brace_count > 0:
                        block_line = lines[j]
                        brace_count += block_line.count('{')
                        brace_count -= block_line.count('}')
                        block_content += block_line
                        j += 1

                    # Check for focus states
                    has_focus = ':focus' in block_content
                    has_focus_visible = ':focus-visible' in block_content

                    if not has_focus and not has_focus_visible:
                        # Extract element name from selector
                        element = selector.split(',')[0].strip()
                        element_parts = element.split()
                        element = element_parts[0] if element_parts else element

                        issue = FocusStateIssue(
                            severity="critical",
                            file=str(file_path.relative_to(self.scss_dir.parent.parent.parent)),
                            line=i + 1,  # 1-indexed line number
                            element=element,
                            issue="No focus state defined",
                            wcag_criterion="2.4.7 Focus Visible",
                            suggestion="Add :focus and :focus-visible styles for keyboard navigation"
                        )
                        self.focus_issues.append(issue)

            i += 1

    def _is_scss_directive(self, selector: str) -> bool:
        """Check if a line is an SCSS directive, not a CSS selector"""
        selector_stripped = selector.strip()

        # Skip SCSS directives
        scss_directives = [
            '@use', '@import', '@include', '@extend', '@mixin', '@function',
            '@return', '@if', '@else', '@for', '@each', '@while', '@warn',
            '@error', '@debug', '@at-root', '@content', '@charset', '@namespace',
            '@supports', '@keyframes', '@media', '@font-face', '@page'
        ]

        for directive in scss_directives:
            if selector_stripped.startswith(directive):
                return True

        # Skip if it doesn't start with a valid CSS selector character
        # Valid selectors start with: . # [ letter : (for pseudo-classes) *
        if selector_stripped and not re.match(r'^[\.\#\[\w:\-\*\+>~\s]', selector_stripped):
            return True

        return False

    def _remove_comments(self, content: str) -> str:
        """Remove SCSS comments from content"""
        # Remove single-line comments
        content = re.sub(r'//.*$', '', content, flags=re.MULTILINE)
        # Remove multi-line comments
        content = re.sub(r'/\*.*?\*/', '', content, flags=re.DOTALL)
        return content

    def _is_interactive_selector(self, selector: str) -> bool:
        """Check if a selector represents an interactive element"""
        selector_lower = selector.lower()

        # Check for HTML interactive elements (must be at start or after a combinator)
        interactive_elements = ['button', 'a', 'input', 'select', 'textarea']
        for elem in interactive_elements:
            # Match element at start or after combinator (>, +, ~, or space)
            pattern = r'(^|[\s>+~,])' + re.escape(elem) + r'([\s>+~,:]|$)'
            if re.search(pattern, selector_lower):
                return True

        # Check for ARIA roles that indicate interactivity
        aria_roles = ['role="button"', "role='button'", 'role="link"', "role='link'",
                      'role="tab"', "role='tab'", 'role="menuitem"', "role='menuitem'",
                      'role="checkbox"', "role='checkbox'", 'role="radio"', "role='radio'"]
        for role in aria_roles:
            if role in selector_lower:
                return True

        # Check for tabindex attribute
        if 'tabindex' in selector_lower:
            return True

        # Check for known interactive classes (APS plugin specific)
        interactive_classes = ['.aps-button', '.aps-link', '.aps-tab', '.aps-menu-item',
                               '.aps-checkbox', '.aps-radio', '.aps-toggle']
        for cls in interactive_classes:
            if cls in selector_lower:
                return True

        return False

    def _check_color_contrast(self, file_path: Path, content: str, lines: List[str]):
        """Check for color contrast violations"""
        # Find color declarations
        color_pattern = re.compile(
            r'(color|background-color|background):\s*([^;]+);',
            re.IGNORECASE
        )

        # Track colors in each block
        blocks = self._extract_color_blocks(content)

        for block in blocks:
            if 'color' in block and 'background' in block:
                fg_color = block['color']
                bg_color = block['background']

                # Only check hex colors
                if fg_color.startswith('#') and bg_color.startswith('#'):
                    ratio = ColorContrastCalculator.contrast_ratio(fg_color, bg_color)

                    # Check WCAG AA (4.5:1 for normal text, 3:1 for large text)
                    is_large_text = block.get('font-size', '').replace(' ', '').startswith('18') or \
                                   block.get('font-weight', '') in ['700', 'bold', '800', '900']

                    required_ratio = 3.0 if is_large_text else 4.5

                    if ratio < required_ratio:
                        line_num = block['line']
                        issue = ColorContrastIssue(
                            severity="serious",
                            file=str(file_path.relative_to(self.scss_dir.parent.parent.parent)),
                            line=line_num,
                            foreground=fg_color,
                            background=bg_color,
                            ratio=round(ratio, 2),
                            wcag_aa_required=required_ratio,
                            wcag_aaa_required=7.0,
                            wcag_criterion="1.4.3 Contrast (Minimum)",
                            suggestion=f"Increase contrast - current ratio {ratio:.2f}:1, minimum required {required_ratio}:1"
                        )
                        self.contrast_issues.append(issue)

    def _extract_color_blocks(self, content: str) -> List[Dict]:
        """Extract color information from CSS blocks"""
        blocks = []
        selector_pattern = re.compile(r'^([^\{]+)\s*\{', re.MULTILINE)

        for match in selector_pattern.finditer(content):
            start_pos = match.end()
            end_pos = content.find('}', start_pos)
            if end_pos == -1:
                continue

            block_content = content[start_pos:end_pos]
            line_num = content[:match.start()].count('\n') + 1

            block = {'line': line_num}

            # Extract color properties
            color_match = re.search(r'color:\s*([^;]+);', block_content, re.IGNORECASE)
            if color_match:
                block['color'] = color_match.group(1).strip()

            bg_match = re.search(r'background(?:-color)?:\s*([^;]+);', block_content, re.IGNORECASE)
            if bg_match:
                bg_value = bg_match.group(1).strip()
                # Skip gradients and images
                if not any(x in bg_value for x in ['linear-gradient', 'url(', 'radial-gradient']):
                    block['background'] = bg_value

            # Extract font-size for large text detection
            font_size_match = re.search(r'font-size:\s*([^;]+);', block_content, re.IGNORECASE)
            if font_size_match:
                block['font-size'] = font_size_match.group(1).strip()

            # Extract font-weight for large text detection
            font_weight_match = re.search(r'font-weight:\s*([^;]+);', block_content, re.IGNORECASE)
            if font_weight_match:
                block['font-weight'] = font_weight_match.group(1).strip()

            if 'color' in block and 'background' in block:
                blocks.append(block)

        return blocks

    def _check_text_resize(self, file_path: Path, content: str, lines: List[str]):
        """Check for fixed font sizes that don't scale"""
        # Find font-size declarations with pixel values
        font_size_pattern = re.compile(
            r'font-size:\s*(\d+\.?\d*)px\s*(?:!important)?;',
            re.IGNORECASE
        )

        for i, line in enumerate(lines, start=1):
            matches = font_size_pattern.finditer(line)
            for match in matches:
                value = match.group(0)
                px_value = match.group(1)

                issue = TextResizeIssue(
                    severity="moderate",
                    file=str(file_path.relative_to(self.scss_dir.parent.parent.parent)),
                    line=i,
                    property="font-size",
                    value=f"{px_value}px",
                    issue="Fixed pixel value doesn't scale with user preferences",
                    wcag_criterion="1.4.4 Resize Text",
                    suggestion="Use rem or em units instead of px for scalable text"
                )
                self.resize_issues.append(issue)

    def _check_hidden_content(self, file_path: Path, content: str, lines: List[str]):
        """Check for hidden content without screen reader alternatives"""
        # Patterns for hiding content without accessibility
        hiding_patterns = [
            (r'display:\s*none\s*(?:!important)?;', 'display: none'),
            (r'visibility:\s*hidden\s*(?:!important)?;', 'visibility: hidden'),
            (r'text-indent:\s*-\d+px\s*(?:!important)?;', 'text-indent with negative value'),
            (r'opacity:\s*0\s*(?:!important)?;', 'opacity: 0'),
        ]

        for i, line in enumerate(lines, start=1):
            # Skip comments
            line_stripped = line.strip()
            if line_stripped.startswith('//') or line_stripped.startswith('/*'):
                continue

            for pattern, description in hiding_patterns:
                if re.search(pattern, line, re.IGNORECASE):
                    # Check if there's a screen-reader-only class or sr-only pattern
                    # This is a simplified check - in reality, we'd need more context
                    has_sr_alternative = any(x in line.lower() for x in ['sr-only', 'screen-reader', 'visually-hidden'])

                    if not has_sr_alternative:
                        issue = HiddenContentIssue(
                            severity="moderate",
                            file=str(file_path.relative_to(self.scss_dir.parent.parent.parent)),
                            line=i,
                            property=description.split(':')[0].strip(),
                            value=description,
                            issue="Content hidden without screen reader alternative",
                            wcag_criterion="1.3.1 Info and Relationships",
                            suggestion="Consider using a screen-reader-only class or aria-hidden attribute appropriately"
                        )
                        self.hidden_issues.append(issue)
                        break  # Only report once per line

    def _generate_report(self) -> Dict:
        """Generate the final accessibility report"""
        total_violations = (
            len(self.focus_issues) +
            len(self.contrast_issues) +
            len(self.resize_issues) +
            len(self.hidden_issues)
        )

        critical = sum(1 for i in self.focus_issues if i.severity == 'critical')
        serious = sum(1 for i in self.contrast_issues if i.severity == 'serious')
        moderate = sum(1 for i in self.resize_issues + self.hidden_issues if i.severity == 'moderate')
        minor = 0

        return {
            "audit_date": datetime.now(timezone.utc).isoformat().replace('+00:00', 'Z'),
            "wcag_level": "AA",
            "summary": {
                "total_violations": total_violations,
                "critical": critical,
                "serious": serious,
                "moderate": moderate,
                "minor": minor
            },
            "focus_states": [asdict(issue) for issue in self.focus_issues],
            "color_contrast": [asdict(issue) for issue in self.contrast_issues],
            "text_resize": [asdict(issue) for issue in self.resize_issues],
            "hidden_content": [asdict(issue) for issue in self.hidden_issues]
        }

    def _save_report(self, report: Dict):
        """Save the report to a JSON file"""
        output_dir = Path('reports')
        output_dir.mkdir(exist_ok=True)

        output_file = output_dir / 'css-accessibility-audit.json'

        with open(output_file, 'w', encoding='utf-8') as f:
            json.dump(report, f, indent=2)

        print(f"\nReport saved to: {output_file}")


def main():
    """Main entry point"""
    # Get SCSS directory
    scss_dir = Path('wp-content/plugins/affiliate-product-showcase/assets/scss')

    if not scss_dir.exists():
        print(f"Error: SCSS directory not found: {scss_dir}")
        return 1

    # Run audit
    auditor = SCSSAccessibilityAuditor(scss_dir)
    report = auditor.audit()

    # Print summary
    print("\n" + "=" * 60)
    print("CSS ACCESSIBILITY AUDIT SUMMARY")
    print("=" * 60)
    print(f"WCAG Level: {report['wcag_level']}")
    print(f"Total Violations: {report['summary']['total_violations']}")
    print(f"  - Critical: {report['summary']['critical']}")
    print(f"  - Serious: {report['summary']['serious']}")
    print(f"  - Moderate: {report['summary']['moderate']}")
    print(f"  - Minor: {report['summary']['minor']}")
    print("\nBreakdown by Category:")
    print(f"  - Missing Focus States: {len(report['focus_states'])}")
    print(f"  - Color Contrast Issues: {len(report['color_contrast'])}")
    print(f"  - Text Resize Issues: {len(report['text_resize'])}")
    print(f"  - Hidden Content Issues: {len(report['hidden_content'])}")
    print("=" * 60)

    return 0


if __name__ == '__main__':
    exit(main())
