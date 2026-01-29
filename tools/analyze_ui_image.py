"""
UI Image Analyzer
Analyzes UI design images to identify form fields, buttons, and layout elements
"""
import sys
from PIL import Image
import numpy as np
from collections import defaultdict

def analyze_ui_elements(image_path):
    """Analyze UI design image for form fields, buttons, and layout"""
    try:
        img = Image.open(image_path)
        img_array = np.array(img)
        
        print("=" * 70)
        print(f"UI DESIGN ANALYSIS: {image_path}")
        print("=" * 70)
        print(f"Image Size: {img.size[0]} x {img.size[1]} pixels")
        print("=" * 70)
        
        # Analyze color distribution
        print("\nCOLOR ANALYSIS:")
        print("-" * 70)
        
        # Get unique colors and their frequency
        pixels = img_array.reshape(-1, 4) if img_array.shape[2] == 4 else img_array.reshape(-1, 3)
        unique_colors, counts = np.unique(pixels, axis=0, return_counts=True)
        
        # Sort by frequency
        color_freq = sorted(zip(unique_colors, counts), key=lambda x: x[1], reverse=True)
        
        print("Top Colors by Frequency:")
        for i, (color, count) in enumerate(color_freq[:10]):
            percentage = (count / pixels.shape[0]) * 100
            if len(color) == 4:
                r, g, b, a = color
                alpha_status = f" (alpha={a})"
            else:
                r, g, b = color
                alpha_status = ""
            print(f"  {i+1}. RGB({r:3d}, {g:3d}, {b:3d}){alpha_status} - {percentage:.1f}% ({count} pixels)")
        
        # Detect potential form fields (white/light gray rectangles)
        print("\nPOTENTIAL UI ELEMENTS:")
        print("-" * 70)
        
        # Convert to grayscale for edge detection
        gray = img.convert('L')
        gray_array = np.array(gray)
        
        # Simple horizontal line detection (for form fields)
        h_lines = []
        for y in range(gray_array.shape[0]):
            row = gray_array[y]
            # Find continuous light segments (potential form fields)
            in_field = False
            field_start = 0
            for x in range(row.shape[0]):
                if row[x] > 200:  # Light color
                    if not in_field:
                        in_field = True
                        field_start = x
                else:
                    if in_field:
                        field_width = x - field_start
                        if field_width > 100:  # Minimum width for form field
                            h_lines.append((field_start, y, field_width))
                        in_field = False
            if in_field:
                field_width = row.shape[0] - field_start
                if field_width > 100:
                    h_lines.append((field_start, y, field_width))
        
        # Group lines into form fields
        if h_lines:
            print(f"Detected {len(h_lines)} potential form field lines")
            
            # Group by y-position (fields are multiple lines)
            field_groups = defaultdict(list)
            for x, y, w in h_lines:
                group_key = y // 10  # Group nearby lines
                field_groups[group_key].append((x, y, w))
            
            print(f"Grouped into {len(field_groups)} potential form fields/areas:")
            for i, (group, lines) in enumerate(sorted(field_groups.items())[:10], 1):
                avg_width = sum(w for _, _, w in lines) // len(lines)
                print(f"  Field {i}: ~{len(lines)} lines, avg width ~{avg_width}px")
        
        # Detect buttons (colored rectangles)
        print("\nBUTTON DETECTION:")
        print("-" * 70)
        
        # Look for colored regions (not white/gray)
        colored_regions = []
        for y in range(0, gray_array.shape[0], 20):
            for x in range(0, gray_array.shape[1], 20):
                pixel = img_array[y, x]
                r, g, b = pixel[:3]
                
                # Check if it's a colored pixel (not white/gray)
                if not (200 <= r <= 255 and 200 <= g <= 255 and 200 <= b <= 255):
                    # Check surrounding pixels to find region
                    region_width = 0
                    region_height = 0
                    
                    # Find width
                    for wx in range(x, min(x + 300, gray_array.shape[1])):
                        wpixel = img_array[y, wx]
                        wr, wg, wb = wpixel[:3]
                        if not (200 <= wr <= 255 and 200 <= wg <= 255 and 200 <= wb <= 255):
                            region_width += 1
                        else:
                            break
                    
                    # Find height
                    for hy in range(y, min(y + 100, gray_array.shape[0])):
                        hpixel = img_array[hy, x]
                        hr, hg, hb = hpixel[:3]
                        if not (200 <= hr <= 255 and 200 <= hg <= 255 and 200 <= hb <= 255):
                            region_height += 1
                        else:
                            break
                    
                    if region_width > 50 and region_height > 20:
                        colored_regions.append((x, y, region_width, region_height, (r, g, b)))
        
        if colored_regions:
            print(f"Detected {len(colored_regions)} potential buttons:")
            for i, (x, y, w, h, color) in enumerate(colored_regions[:5], 1):
                print(f"  Button {i}: Position ({x}, {y}), Size {w}x{h}, Color RGB{color}")
        
        # Layout analysis
        print("\nLAYOUT ANALYSIS:")
        print("-" * 70)
        
        # Check for header area (top portion)
        header_height = img.size[1] // 5
        header_colors = img_array[:header_height, :, :3].reshape(-1, 3)
        avg_header_color = np.mean(header_colors, axis=0).astype(int)
        print(f"Header area (top {header_height}px): Average color RGB{tuple(avg_header_color)}")
        
        # Check for sidebar (left portion)
        sidebar_width = img.size[0] // 5
        if sidebar_width > 100:
            sidebar_colors = img_array[:, :sidebar_width, :3].reshape(-1, 3)
            avg_sidebar_color = np.mean(sidebar_colors, axis=0).astype(int)
            print(f"Sidebar area (left {sidebar_width}px): Average color RGB{tuple(avg_sidebar_color)}")
        
        # Check for main content area
        content_area = img_array[header_height:, sidebar_width:, :3] if sidebar_width > 100 else img_array[header_height:, :, :3]
        avg_content_color = np.mean(content_area.reshape(-1, 3), axis=0).astype(int)
        print(f"Main content area: Average color RGB{tuple(avg_content_color)}")
        
        print("\n" + "=" * 70)
        print("Analysis complete!")
        print("=" * 70)
        
        return True
        
    except FileNotFoundError:
        print(f"Error: Image file not found: {image_path}")
        return False
    except Exception as e:
        print(f"Error analyzing image: {e}")
        import traceback
        traceback.print_exc()
        return False

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("Usage: python analyze_ui_image.py <image_path>")
        sys.exit(1)
    
    image_path = sys.argv[1]
    analyze_ui_elements(image_path)
