"""
Image Reader Tool
Reads and extracts information from image files
"""
import sys
from PIL import Image
import pytesseract

def read_image_info(image_path):
    """Read image and extract basic information"""
    try:
        img = Image.open(image_path)
        
        print("=" * 60)
        print(f"IMAGE INFORMATION: {image_path}")
        print("=" * 60)
        print(f"Format: {img.format}")
        print(f"Mode: {img.mode}")
        print(f"Size: {img.size[0]} x {img.size[1]} pixels")
        print(f"DPI: {img.info.get('dpi', 'N/A')}")
        print("=" * 60)
        
        # Try OCR if available
        try:
            text = pytesseract.image_to_string(img)
            if text.strip():
                print("\nEXTRACTED TEXT:")
                print("-" * 60)
                print(text)
                print("-" * 60)
            else:
                print("\nNo text detected in image")
        except Exception as ocr_error:
            print(f"\nOCR not available: {ocr_error}")
            print("Note: Tesseract OCR needs to be installed separately")
        
        # Save a grayscale version for better analysis
        gray_img = img.convert('L')
        gray_path = image_path.replace('.png', '_gray.png').replace('.jpg', '_gray.jpg')
        gray_img.save(gray_path)
        print(f"\nGrayscale version saved to: {gray_path}")
        
        return True
        
    except FileNotFoundError:
        print(f"Error: Image file not found: {image_path}")
        return False
    except Exception as e:
        print(f"Error reading image: {e}")
        return False

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("Usage: python read_image.py <image_path>")
        sys.exit(1)
    
    image_path = sys.argv[1]
    read_image_info(image_path)
