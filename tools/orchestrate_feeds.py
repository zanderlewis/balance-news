#!/usr/bin/env python3
"""
RSS Feed Management Orchestrator

This script coordinates the RSS feed management workflow by:
1. Reading base URLs from a text file
2. Extracting RSS feeds from each base URL using extract-xml.py
3. Updating JSON source files using update_rss_feeds.py

Usage: python orchestrate_feeds.py <base_urls_file>

Example:
  python orchestrate_feeds.py base_urls.txt
"""

import sys
import subprocess
from pathlib import Path
from urllib.parse import urlparse


def read_base_urls(file_path):
    """Read base URLs from a text file."""
    urls = []
    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            for line in f:
                line = line.strip()
                if line and not line.startswith('#'):  # Skip empty lines and comments
                    urls.append(line)
        return urls
    except FileNotFoundError:
        print(f"Error: File {file_path} not found")
        return []
    except Exception as e:
        print(f"Error reading file {file_path}: {e}")
        return []


def extract_domain_slug(url):
    """Extract a domain slug from URL for naming files."""
    parsed = urlparse(url)
    domain = parsed.netloc.lower()
    
    # Special handling for specific domains
    domain_mappings = {
        'abcnews.go.com': 'abcnews',
        'news.un.org': 'un',
        'feeds.bbci.co.uk': 'bbc',
        'rss.cnn.com': 'cnn',
        'feeds.reuters.com': 'reuters',
        'feeds.foxnews.com': 'foxnews',
        'rss.nytimes.com': 'nytimes',
        'feeds.washingtonpost.com': 'washingtonpost',
        'feeds.theguardian.com': 'guardian',
    }
    
    # Check for exact domain matches first
    if domain in domain_mappings:
        return domain_mappings[domain]
    
    # Remove common prefixes
    domain = domain.replace('www.', '')
    domain = domain.replace('news.', '')
    domain = domain.replace('blog.', '')
    domain = domain.replace('feeds.', '')
    domain = domain.replace('rss.', '')
    
    # Split by dots and take the main domain part
    parts = domain.split('.')
    if len(parts) >= 2:
        # For domains like 'cnn.com', 'bbc.co.uk', 'abcnews.go.com', prefer the main brand name
        if 'abcnews' in parts:
            return 'abcnews'
        elif 'reuters' in parts:
            return 'reuters'
        elif 'theguardian' in parts:
            return 'guardian'
        elif 'washingtonpost' in parts:
            return 'washingtonpost'
        elif 'nytimes' in parts:
            return 'nytimes'
        elif parts[-1] in ['com', 'org', 'net', 'gov', 'edu', 'co'] and len(parts) > 2:
            return parts[-2]
        else:
            return parts[0]
    
    return domain.replace('.', '-')


def run_extract_xml(base_url, output_file):
    """Run extract-xml.py script to discover RSS feeds."""
    script_dir = Path(__file__).parent
    extract_script = script_dir / 'extract-xml.py'
    
    try:
        cmd = [sys.executable, str(extract_script), base_url, '--output', output_file]
        result = subprocess.run(cmd, capture_output=True, text=True)
        
        if result.returncode == 0:
            return True, result.stdout
        else:
            return False, result.stderr
    except Exception as e:
        return False, str(e)


def run_update_rss_feeds(txt_file, source_slug):
    """Run update_rss_feeds.py script to update JSON files."""
    script_dir = Path(__file__).parent
    update_script = script_dir / 'update_rss_feeds.py'
    
    try:
        cmd = [sys.executable, str(update_script), txt_file, source_slug, '--force']
        result = subprocess.run(cmd, capture_output=True, text=True)
        
        if result.returncode == 0:
            return True, result.stdout
        else:
            return False, result.stderr
    except Exception as e:
        return False, str(e)


def process_base_url(base_url, workspace_dir):
    """Process a single base URL through the complete workflow."""
    print(f"\n{'='*60}")
    print(f"Processing: {base_url}")
    print(f"{'='*60}")
    
    # Extract domain slug for file naming
    domain_slug = extract_domain_slug(base_url)
    print(f"Domain slug: {domain_slug}")
    
    # Create temporary file for RSS feeds
    temp_file = workspace_dir / f"{domain_slug}_feeds_temp.txt"
    
    try:
        # Step 1: Extract RSS feeds from base URL
        print(f"\nStep 1: Extracting RSS feeds from {base_url}")
        success, output = run_extract_xml(base_url, str(temp_file))
        
        if not success:
            print(f"❌ Failed to extract RSS feeds: {output}")
            return False
        
        print(f"✅ RSS extraction completed")
        print(output.strip())
        
        # Check if any feeds were found
        if not temp_file.exists() or temp_file.stat().st_size == 0:
            print("⚠️  No RSS feeds found for this URL")
            return True
        
        # Step 2: Update JSON source file
        print(f"\nStep 2: Updating JSON source file for {domain_slug}")
        success, output = run_update_rss_feeds(str(temp_file), domain_slug)
        
        if not success:
            print(f"❌ Failed to update RSS feeds: {output}")
            return False
        
        print(f"✅ JSON source file updated successfully")
        print(output.strip())
        
        return True
        
    except Exception as e:
        print(f"❌ Error processing {base_url}: {e}")
        return False
    
    finally:
        # Clean up temporary file
        if temp_file.exists():
            temp_file.unlink()


def main():
    if len(sys.argv) < 2:
        print("Usage: python orchestrate_feeds.py <base_urls_file>")
        print("\nExample:")
        print("  python orchestrate_feeds.py base_urls.txt")
        print("\nThe base_urls_file should contain one URL per line.")
        print("Lines starting with # are treated as comments and ignored.")
        sys.exit(1)
    
    base_urls_file = sys.argv[1]
    
    # Get workspace directory
    script_dir = Path(__file__).parent
    workspace_dir = script_dir.parent
    
    # Construct full path to base URLs file
    base_urls_path = workspace_dir / base_urls_file
    
    print(f"RSS Feed Management Orchestrator")
    print(f"================================")
    print(f"Base URLs file: {base_urls_path}")
    
    # Read base URLs
    base_urls = read_base_urls(base_urls_path)
    if not base_urls:
        print("No URLs found in the base URLs file")
        sys.exit(1)
    
    print(f"Found {len(base_urls)} base URLs to process")
    
    # Display URLs to be processed
    print("\nBase URLs to process:")
    for i, url in enumerate(base_urls, 1):
        print(f"  {i}. {url}")
    
    # Confirm before proceeding
    response = input(f"\nProcess all {len(base_urls)} base URLs? (y/N): ")
    if response.lower() != 'y':
        print("Cancelled")
        sys.exit(0)
    
    # Process each base URL
    successful = 0
    failed = 0
    
    for i, base_url in enumerate(base_urls, 1):
        print(f"\n🔄 Processing {i}/{len(base_urls)}")
        
        if process_base_url(base_url, workspace_dir):
            successful += 1
        else:
            failed += 1
    
    # Summary
    print(f"\n{'='*60}")
    print(f"PROCESSING COMPLETE")
    print(f"{'='*60}")
    print(f"✅ Successful: {successful}")
    print(f"❌ Failed: {failed}")
    print(f"📊 Total: {len(base_urls)}")
    
    if failed == 0:
        print("\n🎉 All base URLs processed successfully!")
    else:
        print(f"\n⚠️  {failed} base URLs failed to process. Check the output above for details.")
        sys.exit(1)


if __name__ == '__main__':
    main()
