#!/usr/bin/env python3
"""
Design Image Analysis Tool
Analyzes the design screenshot to extract UI elements, colors, and layout.
"""

from PIL import Image
import numpy as np
import sys

def analyze_design(image_path):
    """Analyze the design image and extract key elements."""
    
    print("=" * 70)
    print("DESIGN IMAGE ANALYSIS")
    print("=" * 70)
    
    # Open image
    img = Image.open(image_path)
    arr = np.array(img)
    
    print(f"\nImage Dimensions: {img.size[0]} x {img.size[1]} pixels")
    print(f"Mode: {img.mode}")
    
    # Analyze sections
    height = img.size[1]
    width = img.size[0]
    
    # Header section (top ~20%)
    header_section = arr[:int(height * 0.2), :]
    print(f"\n--- HEADER SECTION (top 20%) ---")
    analyze_color_section(header_section, "Header")
    
    # Sidebar section (left ~20%)
    sidebar_section = arr[:, :int(width * 0.2)]
    print(f"\n--- SIDEBAR SECTION (left 20%) ---")
    analyze_color_section(sidebar_section, "Sidebar")
    
    # Main content section
    main_content = arr[int(height * 0.2):, int(width * 0.2):]
    print(f"\n--- MAIN CONTENT SECTION ---")
    analyze_color_section(main_content, "Main Content")
    
    # Detect potential UI elements (horizontal lines for sections, cards)
    print(f"\n--- POTENTIAL UI ELEMENTS ---")
    detect_ui_elements(arr)
    
    # Color palette summary
    print(f"\n--- COLOR PALETTE SUMMARY ---")
    get_color_palette(arr, top_n=10)
    
    print("\n" + "=" * 70)

def analyze_color_section(section, name):
    """Analyze colors in a section."""
    # Flatten and get unique colors
    flat = section.reshape(-1, section.shape[-1])
    unique_colors, counts = np.unique(flat, axis=0, return_counts=True)
    
    # Sort by frequency - convert to list of tuples first
    color_count_pairs = [(int(c), tuple(uc)) for c, uc in zip(counts, unique_colors)]
    sorted_colors = sorted(color_count_pairs, reverse=True)
    
    print(f"{name} dominant colors:")
    for i, (count, color) in enumerate(sorted_colors[:5]):
        if len(color) == 4:
            rgb = tuple(color[:3])
            alpha = color[3]
        else:
            rgb = tuple(color)
            alpha = 255
        percentage = (count / flat.shape[0]) * 100
        print(f"  {i+1}. RGB{rgb} (alpha={alpha}): {percentage:.1f}%")

def get_color_palette(arr, top_n=10):
    """Get the dominant colors in the image."""
    flat = arr.reshape(-1, arr.shape[-1])
    unique_colors, counts = np.unique(flat, axis=0, return_counts=True)
    color_count_pairs = [(int(c), tuple(uc)) for c, uc in zip(counts, unique_colors)]
    sorted_colors = sorted(color_count_pairs, reverse=True)
    
    print("Top colors by frequency:")
    for i, (count, color) in enumerate(sorted_colors[:top_n]):
        if len(color) == 4:
            rgb = tuple(color[:3])
        else:
            rgb = tuple(color)
        percentage = (count / flat.shape[0]) * 100
        print(f"  {i+1}. RGB{rgb}: {percentage:.1f}%")

def detect_ui_elements(arr):
    """Detect potential UI elements like cards, buttons, sections."""
    height, width = arr.shape[:2]
    
    # Detect horizontal separators (lines)
    print("Horizontal separators detected at:")
    for y in range(50, height - 50, 10):
        row = arr[y, :]
        # Check if row is mostly uniform color (potential separator)
        unique_colors = np.unique(row.reshape(-1, row.shape[-1]), axis=0)
        if len(unique_colors) < 5:
            avg_color = np.mean(row, axis=0)[:3].astype(int)
            print(f"  - y={y}: RGB{tuple(avg_color)}")
    
    # Detect vertical separators
    print("\nVertical separators detected at:")
    for x in range(50, width - 50, 10):
        col = arr[:, x]
        unique_colors = np.unique(col.reshape(-1, col.shape[-1]), axis=0)
        if len(unique_colors) < 5:
            avg_color = np.mean(col, axis=0)[:3].astype(int)
            print(f"  - x={x}: RGB{tuple(avg_color)}")

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("Usage: python analyze_design.py <image_path>")
        sys.exit(1)
    
    image_path = sys.argv[1]
    analyze_design(image_path)
