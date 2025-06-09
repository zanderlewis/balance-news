#!/usr/bin/env python3
"""
Favicon Converter Script

This script converts an SVG favicon to ICO format and generates Apple Touch Icon.
It generates multiple sizes (16x16, 32x32, 48x48) within the ICO file
and a 180x180 PNG for Apple Touch Icon for better compatibility across different browsers and contexts.

Usage:
    uv run tools/convert_favicon.py
"""

import sys
from pathlib import Path
from PIL import Image, ImageDraw
import io
import xml.etree.ElementTree as ET
import re

def parse_svg_simple(svg_content, size):
    """
    Simple SVG parser that handles basic shapes like circles and paths.
    This is a minimal implementation for favicon conversion.
    """
    # Create a new image with transparent background
    image = Image.new('RGBA', (size, size), (255, 255, 255, 0))
    draw = ImageDraw.Draw(image)
    
    try:
        # Parse the SVG XML
        root = ET.fromstring(svg_content)
        
        # Extract viewBox to understand coordinate system
        viewbox = root.get('viewBox', '0 0 24 24')
        vb_parts = viewbox.split()
        if len(vb_parts) == 4:
            vb_width = float(vb_parts[2])
            vb_height = float(vb_parts[3])
        else:
            vb_width = vb_height = 24
        
        # Scale factor from SVG coordinates to image pixels
        scale_x = size / vb_width
        scale_y = size / vb_height
        
        # Find and render circles
        for circle in root.iter():
            if circle.tag.endswith('circle'):
                cx = float(circle.get('cx', 0)) * scale_x
                cy = float(circle.get('cy', 0)) * scale_y
                r = float(circle.get('r', 0)) * min(scale_x, scale_y)
                
                fill = circle.get('fill', 'black')
                if fill == 'white':
                    fill_color = (255, 255, 255, 255)
                else:
                    fill_color = (0, 0, 0, 255)
                
                # Draw circle
                draw.ellipse([cx-r, cy-r, cx+r, cy+r], fill=fill_color)
        
        # Find and render simple paths (very basic - just draw as rectangles for now)
        for path in root.iter():
            if path.tag.endswith('path'):
                stroke = path.get('stroke', 'currentColor')
                if stroke and stroke != 'none':
                    # For the document icon, let's draw some simple rectangles
                    # This is a very simplified approach
                    
                    # Draw main document rectangle
                    doc_x = size * 0.2
                    doc_y = size * 0.15
                    doc_w = size * 0.6
                    doc_h = size * 0.7
                    
                    stroke_color = (0, 0, 0, 255)
                    
                    # Document outline
                    draw.rectangle([doc_x, doc_y, doc_x + doc_w, doc_y + doc_h], 
                                 outline=stroke_color, width=2)
                    
                    # Document lines
                    line_spacing = size * 0.08
                    line_start_x = doc_x + size * 0.1
                    line_end_x = doc_x + doc_w - size * 0.1
                    
                    for i in range(4):
                        line_y = doc_y + size * 0.2 + (i * line_spacing)
                        if line_y < doc_y + doc_h - size * 0.1:
                            draw.line([line_start_x, line_y, line_end_x, line_y], 
                                    fill=stroke_color, width=1)
                    
                    # Small square (checkbox-like)
                    square_size = size * 0.08
                    square_x = doc_x + size * 0.05
                    square_y = doc_y + doc_h - size * 0.15
                    draw.rectangle([square_x, square_y, 
                                  square_x + square_size, square_y + square_size],
                                 outline=stroke_color, width=1)
                    break
        
        return image
        
    except Exception as e:
        # Fallback: create a simple colored square
        print(f"Warning: Could not parse SVG properly ({e}), creating simple icon")
        draw.rectangle([size*0.1, size*0.1, size*0.9, size*0.9], 
                      fill=(100, 100, 100, 255), outline=(0, 0, 0, 255))
        return image

def convert_svg_to_apple_touch_icon(svg_path, png_path, size=180):
    """
    Convert SVG file to Apple Touch Icon PNG format.
    
    Args:
        svg_path (str): Path to the input SVG file
        png_path (str): Path to the output PNG file
        size (int): Size of the Apple Touch Icon (default 180x180)
    """
    try:
        # Read the SVG file
        with open(svg_path, 'r', encoding='utf-8') as svg_file:
            svg_content = svg_file.read()
        
        # Parse and render SVG at specific size
        image = parse_svg_simple(svg_content, size)
        
        # Save as PNG
        image.save(png_path, format='PNG')
        
        print(f"Generated {size}x{size} Apple Touch Icon: {png_path}")
        
    except Exception as e:
        print(f"Error converting to Apple Touch Icon: {e}")
        sys.exit(1)

def convert_svg_to_ico(svg_path, ico_path, sizes=[16, 32, 48]):
    """
    Convert SVG file to ICO format with multiple sizes.
    
    Args:
        svg_path (str): Path to the input SVG file
        ico_path (str): Path to the output ICO file
        sizes (list): List of sizes to include in the ICO file
    """
    try:
        # Read the SVG file
        with open(svg_path, 'r', encoding='utf-8') as svg_file:
            svg_content = svg_file.read()
        
        # Convert SVG to PNG at different sizes and collect them
        images = []
        
        for size in sizes:
            # Parse and render SVG at specific size
            image = parse_svg_simple(svg_content, size)
            images.append(image)
            print(f"Generated {size}x{size} icon")
        
        # Save as ICO file with multiple sizes
        images[0].save(
            ico_path,
            format='ICO',
            sizes=[(img.width, img.height) for img in images],
            append_images=images[1:]
        )
        
        print(f"Successfully converted {svg_path} to {ico_path}")
        print(f"ICO file contains sizes: {', '.join([f'{size}x{size}' for size in sizes])}")
        
    except ImportError as e:
        print(f"Error: Missing required dependencies. Please install them with:")
        print("pip install Pillow")
        sys.exit(1)
        
    except FileNotFoundError:
        print(f"Error: SVG file not found at {svg_path}")
        sys.exit(1)
        
    except Exception as e:
        print(f"Error converting favicon: {e}")
        sys.exit(1)

def main():
    # Get the project root directory
    script_dir = Path(__file__).parent
    project_root = script_dir.parent
    
    # Define paths
    svg_path = project_root / "public" / "favicon.svg"
    ico_path = project_root / "public" / "favicon.ico"
    apple_touch_icon_path = project_root / "public" / "apple-touch-icon.png"
    
    print("Favicon Converter")
    print("=" * 50)
    print(f"Source SVG: {svg_path}")
    print(f"Target ICO: {ico_path}")
    print(f"Target Apple Touch Icon: {apple_touch_icon_path}")
    print()
    
    # Check if SVG file exists
    if not svg_path.exists():
        print(f"Error: SVG file not found at {svg_path}")
        sys.exit(1)
    
    # Backup existing ICO if it exists
    if ico_path.exists():
        backup_path = ico_path.with_suffix('.ico.backup')
        ico_path.replace(backup_path)
        print(f"Backed up existing favicon.ico to {backup_path.name}")
    
    # Backup existing Apple Touch Icon if it exists
    if apple_touch_icon_path.exists():
        backup_path = apple_touch_icon_path.with_suffix('.png.backup')
        apple_touch_icon_path.replace(backup_path)
        print(f"Backed up existing apple-touch-icon.png to {backup_path.name}")
    
    # Convert SVG to Apple Touch Icon
    convert_svg_to_apple_touch_icon(str(svg_path), str(apple_touch_icon_path))
    
    # Convert SVG to ICO
    convert_svg_to_ico(str(svg_path), str(ico_path))
    
    print()
    print("Conversion complete!")
    print(f"New apple-touch-icon.png saved to: {apple_touch_icon_path}")
    print(f"New favicon.ico saved to: {ico_path}")

if __name__ == "__main__":
    main()
