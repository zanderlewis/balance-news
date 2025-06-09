#!/usr/bin/env python3
"""
Script to update RSS feeds in source JSON files from text files.
Usage: python update_rss_feeds.py <txt_file> [source_slug]

Examples:
  python update_rss_feeds.py nyt.txt
  python update_rss_feeds.py nyt.txt nyt
"""

import json
import sys
import re
from urllib.parse import urlparse
from pathlib import Path

def extract_feed_name_and_category(url):
    """Extract a human-readable name and category from RSS URL."""
    
    # Parse the URL to get components
    parsed = urlparse(url)
    path = parsed.path.lower()
    domain = parsed.netloc.lower()
    
    # Extract feed identifiers from the URL path
    feed_identifiers = extract_feed_identifiers(url)
    
    # Determine category based on URL patterns
    category = determine_category_from_identifiers(feed_identifiers)
    
    # Generate human-readable name
    name = generate_feed_name(url, feed_identifiers)
    
    return name, category

def extract_feed_identifiers(url):
    """Extract meaningful identifiers from RSS URL."""
    parsed = urlparse(url)
    path = parsed.path.lower()
    
    # Common patterns to extract feed identifiers
    identifiers = []
    
    # Split path into segments and clean them
    segments = [seg for seg in path.split('/') if seg and seg not in ['rss', 'xml', 'feed', 'feeds', 'services']]
    
    # Look for meaningful segments
    for segment in segments:
        # Remove common prefixes/suffixes
        clean_segment = re.sub(r'\.(xml|rss)$', '', segment)
        clean_segment = re.sub(r'^(rss|xml)', '', clean_segment)
        
        if clean_segment and len(clean_segment) > 1:
            identifiers.append(clean_segment)
    
    # Also check query parameters for feed types
    if parsed.query:
        query_parts = parsed.query.lower().split('&')
        for part in query_parts:
            if '=' in part:
                key, value = part.split('=', 1)
                if key in ['category', 'section', 'type', 'topic']:
                    identifiers.append(value)
    
    return identifiers

def determine_category_from_identifiers(identifiers):
    """Determine category based on extracted identifiers."""
    
    # Comprehensive category mapping with priority ordering
    # More specific categories first, then more general ones
    category_mapping = {
        # Health/Medical
        'health': 'health', 'medical': 'health', 'medicine': 'health',
        'healthcare': 'health', 'wellness': 'health', 'fitness': 'health',
        'well': 'health', 'mental-health': 'health', 'psychology': 'health',
        'healthheadlines': 'health',
        
        # Science/Environment
        'science': 'science', 'scientific': 'science', 'research': 'science',
        'space': 'science', 'astronomy': 'science', 'nasa': 'science',
        'climate': 'science', 'climate-change': 'science', 'environment': 'science',
        'environmental': 'science', 'nature': 'science', 'conservation': 'science',
        
        # Education
        'education': 'education', 'educational': 'education', 'school': 'education',
        'schools': 'education', 'university': 'education', 'college': 'education',
        'learning': 'education', 'students': 'education', 'academic': 'education',
        'culture-and-education': 'education',
        
        # Business/Economy/Finance
        'business': 'business', 'economy': 'business', 'economic': 'business',
        'finance': 'business', 'financial': 'business', 'markets': 'business',
        'market': 'business', 'stocks': 'business', 'trading': 'business',
        'dealbook': 'business', 'deals': 'business', 'mergers': 'business',
        'smallbusiness': 'business', 'small-business': 'business', 'startup': 'business',
        'yourmoney': 'business', 'your-money': 'business', 'personal-finance': 'business',
        'investing': 'business', 'investment': 'business', 'banking': 'business',
        'crypto': 'business', 'cryptocurrency': 'business', 'bitcoin': 'business',
        'energyenvironment': 'business', 'energy-environment': 'business', 'energy': 'business',
        'mediaandadvertising': 'business', 'media-and-advertising': 'business',
        'advertising': 'business', 'marketing': 'business',
        'jobs': 'business', 'employment': 'business', 'careers': 'business',
        'realestate': 'business', 'real-estate': 'business', 'housing': 'business',
        'money': 'business', 'moneyheadlines': 'business', 'businessheadlines': 'business',
        'economic-development': 'business', 'development': 'business',
        
        # World/International
        'world': 'world', 'international': 'world', 'global': 'world',
        'africa': 'world', 'americas': 'world', 'asiapacific': 'world', 'asia-pacific': 'world',
        'asia': 'world', 'europe': 'world', 'middleeast': 'world', 'middle-east': 'world',
        'foreign': 'world', 'overseas': 'world', 'regions': 'world', 'region': 'world',
        'migrants': 'world', 'refugees': 'world', 'migrants-and-refugees': 'world',
        'humanitarian': 'world', 'humanitarian-aid': 'world',

        'women': 'social', 'gender': 'social', 'equality': 'social', 'social-justice': 'social',
        'human-rights': 'social', 'rights': 'social', 'civil-rights': 'social',
        'discrimination': 'social', 'diversity': 'social', 'inclusion': 'social',
        'lgbtq': 'social', 'minorities': 'social', 'racism': 'social', 'social-issues': 'social',

        # Politics/Government
        'politics': 'politics', 'political': 'politics', 'government': 'politics',
        'upshot': 'politics', 'election': 'politics', 'elections': 'politics',
        'campaign': 'politics', 'policy': 'politics', 'congress': 'politics',
        'senate': 'politics', 'house': 'politics', 'whitehouse': 'politics',
        'supreme-court': 'politics', 'justice': 'politics', 'legal': 'politics',
        'law': 'politics', 'law-and-crime-prevention': 'politics',
        'un-affairs': 'politics', 'peace-and-security': 'politics', 'security': 'politics',
        'sdgs': 'politics', 'sustainable-development': 'politics',
        
        # Technology
        'technology': 'technology', 'tech': 'technology', 'digital': 'technology',
        'personaltech': 'technology', 'personal-tech': 'technology', 'gadgets': 'technology',
        'software': 'technology', 'hardware': 'technology', 'internet': 'technology',
        'ai': 'technology', 'artificial-intelligence': 'technology', 'machine-learning': 'technology',
        'cybersecurity': 'technology', 'security': 'technology', 'privacy': 'technology',
        'mobile': 'technology', 'apps': 'technology', 'social-media': 'technology',
        
        # Sports
        'sports': 'sports', 'sport': 'sports', 'athletics': 'sports',
        'baseball': 'sports', 'basketball': 'sports', 'football': 'sports',
        'soccer': 'sports', 'tennis': 'sports', 'golf': 'sports', 'hockey': 'sports',
        'collegebasketball': 'sports', 'college-basketball': 'sports',
        'collegefootball': 'sports', 'college-football': 'sports',
        'probasketball': 'sports', 'pro-basketball': 'sports', 'nba': 'sports',
        'profootball': 'sports', 'pro-football': 'sports', 'nfl': 'sports',
        'olympics': 'sports', 'olympic': 'sports', 'worldcup': 'sports',
        'motorsports': 'sports', 'racing': 'sports',
        
        # Science/Health
        'science': 'science', 'scientific': 'science', 'research': 'science',
        'health': 'health', 'medical': 'health', 'medicine': 'health',
        'healthcare': 'health', 'wellness': 'health', 'fitness': 'health',
        'climate': 'science', 'climate-change': 'science', 'environment': 'science',
        'environmental': 'science', 'nature': 'science', 'conservation': 'science',
        'space': 'science', 'astronomy': 'science', 'nasa': 'science',
        'well': 'health', 'mental-health': 'health', 'psychology': 'health',
        
        # Arts/Entertainment/Culture
        'arts': 'arts', 'art': 'arts', 'culture': 'arts', 'cultural': 'arts',
        'artanddesign': 'arts', 'art-and-design': 'arts', 'design': 'arts',
        'books': 'arts', 'literature': 'arts', 'reading': 'arts', 'review': 'arts',
        'dance': 'arts', 'dancing': 'arts', 'ballet': 'arts',
        'movies': 'arts', 'film': 'arts', 'cinema': 'arts', 'hollywood': 'arts',
        'music': 'arts', 'concerts': 'arts', 'albums': 'arts',
        'television': 'arts', 'tv': 'arts', 'streaming': 'arts',
        'theater': 'arts', 'theatre': 'arts', 'broadway': 'arts',
        'entertainment': 'arts', 'celebrity': 'arts', 'celebrities': 'arts',
        'gaming': 'arts', 'games': 'arts', 'video-games': 'arts',
        'lens': 'arts', 'photography': 'arts', 'photos': 'arts',
        
        # Lifestyle
        'lifestyle': 'lifestyle', 'living': 'lifestyle', 'life': 'lifestyle',
        'fashionandstyle': 'lifestyle', 'fashion-and-style': 'lifestyle', 'fashion': 'lifestyle',
        'style': 'lifestyle', 'beauty': 'lifestyle', 'luxury': 'lifestyle',
        'diningandwine': 'lifestyle', 'dining-and-wine': 'lifestyle', 'food': 'lifestyle',
        'dining': 'lifestyle', 'wine': 'lifestyle', 'restaurants': 'lifestyle',
        'cooking': 'lifestyle', 'recipes': 'lifestyle',
        'weddings': 'lifestyle', 'wedding': 'lifestyle', 'marriage': 'lifestyle',
        'tmagazine': 'lifestyle', 'magazine': 'lifestyle',
        'travel': 'lifestyle', 'tourism': 'lifestyle', 'vacation': 'lifestyle',
        'automobiles': 'lifestyle', 'cars': 'lifestyle', 'automotive': 'lifestyle',
        'home': 'lifestyle', 'garden': 'lifestyle', 'gardening': 'lifestyle',
        'parenting': 'lifestyle', 'family': 'lifestyle', 'relationships': 'lifestyle',
        
        # Education
        'education': 'education', 'educational': 'education', 'school': 'education',
        'schools': 'education', 'university': 'education', 'college': 'education',
        'learning': 'education', 'students': 'education', 'academic': 'education',
        'culture-and-education': 'education',
        
        # Opinion/Editorial
        'opinion': 'opinion', 'opinions': 'opinion', 'editorial': 'opinion',
        'editorials': 'opinion', 'commentary': 'opinion', 'analysis': 'opinion',
        'sunday-review': 'opinion', 'op-ed': 'opinion', 'column': 'opinion',
        'columnist': 'opinion', 'blog': 'opinion', 'blogs': 'opinion',
        
        # National/Regional
        'us': 'national', 'usa': 'national', 'national': 'national',
        'domestic': 'national', 'america': 'national', 'american': 'national',
        'nyregion': 'regional', 'ny-region': 'regional', 'regional': 'regional',
        'local': 'regional', 'metro': 'regional', 'city': 'regional',
        
        # General/News
        'homepage': 'general', 'home': 'general', 'top': 'general', 'main': 'general',
        'news': 'general', 'latest': 'general', 'breaking': 'general',
        'recent': 'general', 'all': 'general', 'headlines': 'general',
        'mostemailed': 'general', 'most-emailed': 'general',
        'mostshared': 'general', 'most-shared': 'general',
        'mostviewed': 'general', 'most-viewed': 'general',
        'popular': 'general', 'trending': 'general',
        'obituaries': 'general', 'obits': 'general',
        'weather': 'general', 'traffic': 'general',
    }
    
    # Find the best matching category by checking each identifier
    best_match = 'general'
    best_score = 0
    
    for identifier in identifiers:
        # Clean identifier
        clean_id = identifier.lower().strip()
        clean_id = re.sub(r'[^a-z0-9\-]', '', clean_id)
        
        # Skip common non-content identifiers
        if clean_id in ['subscribe', 'en', 'news', 'topic', 'region', 'www', 'com', 'org']:
            continue
        
        # Check for exact matches first (highest priority)
        if clean_id in category_mapping:
            return category_mapping[clean_id]
        
        # Check for partial matches and score them
        for key, category in category_mapping.items():
            if key == clean_id:  # Exact match - highest priority
                return category
            elif key in clean_id:  # Key is contained in identifier
                score = len(key)  # Longer matches are more specific
                if score > best_score:
                    best_match = category
                    best_score = score
            elif clean_id in key:  # Identifier is contained in key (less preferred)
                score = len(clean_id) * 0.5  # Lower score for reverse matches
                if score > best_score:
                    best_match = category
                    best_score = score
    
    return best_match

def generate_feed_name(url, identifiers):
    """Generate a human-readable name for the RSS feed."""
    
    # Name mappings for common identifiers
    name_mappings = {
        # Common feed patterns with proper spacing
        'homepage': 'Home Page', 'home': 'Home', 'main': 'Main',
        'topstories': 'Top Stories', 'top-stories': 'Top Stories',
        'usheadlines': 'US Headlines', 'us-headlines': 'US Headlines',
        'internationalheadlines': 'International Headlines', 'international-headlines': 'International Headlines',
        'politicsheadlines': 'Politics Headlines', 'politics-headlines': 'Politics Headlines',
        'moneyheadlines': 'Money Headlines', 'money-headlines': 'Money Headlines',
        'businessheadlines': 'Business Headlines', 'business-headlines': 'Business Headlines',
        'technologyheadlines': 'Technology Headlines', 'technology-headlines': 'Technology Headlines',
        'healthheadlines': 'Health Headlines', 'health-headlines': 'Health Headlines',
        'entertainmentheadlines': 'Entertainment Headlines', 'entertainment-headlines': 'Entertainment Headlines',
        'sportsheadlines': 'Sports Headlines', 'sports-headlines': 'Sports Headlines',
        'travelheadlines': 'Travel Headlines', 'travel-headlines': 'Travel Headlines',
        'worldnewsheadlines': 'World News Headlines', 'world-news-headlines': 'World News Headlines',
        'gmaheadlines': 'GMA Headlines', 'gma-headlines': 'GMA Headlines',
        '2020headlines': '20/20 Headlines', '20-20-headlines': '20/20 Headlines',
        'primetimeheadlines': 'Primetime Headlines', 'primetime-headlines': 'Primetime Headlines',
        'nightlineheadlines': 'Nightline Headlines', 'nightline-headlines': 'Nightline Headlines',
        'thisweekheadlines': 'This Week Headlines', 'this-week-headlines': 'This Week Headlines',
        'all': 'All News', 'latest': 'Latest News', 'breaking': 'Breaking News',
        'asiapacific': 'Asia Pacific', 'asia-pacific': 'Asia Pacific',
        'middleeast': 'Middle East', 'middle-east': 'Middle East',
        'nyregion': 'NY Region', 'ny-region': 'NY Region',
        'energyenvironment': 'Energy & Environment', 'energy-environment': 'Energy & Environment',
        'smallbusiness': 'Small Business', 'small-business': 'Small Business',
        'yourmoney': 'Your Money', 'your-money': 'Your Money',
        'personaltech': 'Personal Tech', 'personal-tech': 'Personal Tech',
        'collegebasketball': 'College Basketball', 'college-basketball': 'College Basketball',
        'collegefootball': 'College Football', 'college-football': 'College Football',
        'probasketball': 'Pro Basketball', 'pro-basketball': 'Pro Basketball',
        'profootball': 'Pro Football', 'pro-football': 'Pro Football',
        'artanddesign': 'Art & Design', 'art-and-design': 'Art & Design',
        'fashionandstyle': 'Fashion & Style', 'fashion-and-style': 'Fashion & Style',
        'diningandwine': 'Dining & Wine', 'dining-and-wine': 'Dining & Wine',
        'realestate': 'Real Estate', 'real-estate': 'Real Estate',
        'mostemailed': 'Most Emailed', 'most-emailed': 'Most Emailed',
        'mostshared': 'Most Shared', 'most-shared': 'Most Shared',
        'mostviewed': 'Most Viewed', 'most-viewed': 'Most Viewed',
        'mediaandadvertising': 'Media & Advertising', 'media-and-advertising': 'Media & Advertising',
        'sunday-review': 'Sunday Review', 'sundayreview': 'Sunday Review',
        'climate-change': 'Climate Change', 'climatechange': 'Climate Change',
        'human-rights': 'Human Rights', 'humanrights': 'Human Rights',
        'un-affairs': 'UN Affairs', 'unaffairs': 'UN Affairs',
        'law-and-crime-prevention': 'Law and Crime Prevention',
        'humanitarian-aid': 'Humanitarian Aid', 'humanitarianaid': 'Humanitarian Aid',
        'culture-and-education': 'Culture and Education',
        'economic-development': 'Economic Development',
        'peace-and-security': 'Peace and Security',
        'migrants-and-refugees': 'Migrants and Refugees',
        'sdgs': 'Sustainable Development Goals',
    }
    
    # Find the most meaningful identifier
    best_identifier = None
    for identifier in reversed(identifiers):  # Check from most specific to least
        clean_id = identifier.lower().strip()
        if len(clean_id) > 2 and clean_id not in ['rss', 'xml', 'feed', 'feeds']:
            best_identifier = clean_id
            break
    
    if not best_identifier and identifiers:
        best_identifier = identifiers[-1]
    
    if not best_identifier:
        # Fallback to domain-based name
        parsed = urlparse(url)
        domain_parts = parsed.netloc.split('.')
        best_identifier = domain_parts[0] if domain_parts else 'feed'
    
    # Generate name
    if best_identifier in name_mappings:
        return name_mappings[best_identifier]
    else:
        # Convert to title case and clean up
        name = best_identifier.replace('-', ' ').replace('_', ' ')
        name = re.sub(r'([a-z])([A-Z])', r'\1 \2', name)  # Handle camelCase
        # Handle common patterns like "headlines" suffix
        if name.lower().endswith('headlines'):
            name = name[:-9].strip() + ' Headlines'
        # Ensure proper title case
        words = name.split()
        capitalized_words = []
        for word in words:
            if word.lower() in ['and', 'of', 'the', 'in', 'on', 'at', 'to', 'for', 'with']:
                capitalized_words.append(word.lower())
            else:
                capitalized_words.append(word.capitalize())
        return ' '.join(capitalized_words)

def create_default_source_structure(source_slug):
    """Create a default source structure for new JSON files."""
    # Convert source slug to a human-readable name
    name_mappings = {
        'un': 'United Nations News',
        'nyt': 'The New York Times', 
        'nytimes': 'The New York Times',
        'cnn': 'CNN',
        'bbc': 'BBC News',
        'reuters': 'Reuters',
        'ap': 'Associated Press',
        'go': 'ABC News',
        'abcgo': 'ABC News',
        'abcnews': 'ABC News',
        'foxnews': 'Fox News',
        'wsj': 'The Wall Street Journal',
        'guardian': 'The Guardian',
        'washingtonpost': 'The Washington Post',
        'usatoday': 'USA Today',
        'npr': 'NPR',
        'msnbc': 'MSNBC',
        'cbsnews': 'CBS News',
        'nbcnews': 'NBC News',
    }
    
    # Convert slug to title case as fallback
    display_name = name_mappings.get(source_slug, source_slug.replace('-', ' ').replace('_', ' ').title())
    
    # Determine bias label based on known sources
    bias_mappings = {
        'un': 'center',
        'reuters': 'center',
        'ap': 'center',
        'bbc': 'center',
        'nyt': 'lean-left',
        'nytimes': 'lean-left',
        'guardian': 'lean-left',
        'cnn': 'left',
        'msnbc': 'left',
        'foxnews': 'right',
        'wsj': 'lean-right',
        'go': 'center',
        'abcgo': 'center',
        'abcnews': 'center',
    }
    
    bias_label = bias_mappings.get(source_slug, 'center')
    
    # Determine URL based on source slug
    url_mappings = {
        'un': 'https://news.un.org',
        'nyt': 'https://www.nytimes.com',
        'nytimes': 'https://www.nytimes.com',
        'cnn': 'https://www.cnn.com',
        'bbc': 'https://www.bbc.com/news',
        'reuters': 'https://www.reuters.com',
        'ap': 'https://apnews.com',
        'go': 'https://abcnews.go.com',
        'abcgo': 'https://abcnews.go.com',
        'abcnews': 'https://abcnews.go.com',
        'foxnews': 'https://www.foxnews.com',
        'wsj': 'https://www.wsj.com',
        'guardian': 'https://www.theguardian.com',
    }
    
    base_url = url_mappings.get(source_slug, f'https://{source_slug}.com')
    
    return {
        "name": display_name,
        "slug": source_slug,
        "url": base_url,
        "bias_label": bias_label,
        "country_code": "US",
        "categories": [],
        "is_active": True,
        "rss_feeds": []
    }

def load_rss_urls_from_file(filepath):
    """Load RSS URLs from a text file."""
    urls = []
    try:
        with open(filepath, 'r') as f:
            for line in f:
                line = line.strip()
                if line and line.startswith('http'):
                    urls.append(line)
    except FileNotFoundError:
        print(f"Error: File {filepath} not found")
        return []
    
    return urls

def update_json_file(json_filepath, rss_feeds, source_slug):
    """Update the JSON file with new RSS feeds."""
    try:
        # Try to load existing JSON
        try:
            with open(json_filepath, 'r') as f:
                data = json.load(f)
        except FileNotFoundError:
            # Create a new JSON structure if file doesn't exist
            print(f"JSON file not found. Creating new file: {json_filepath}")
            
            # Create directory if it doesn't exist
            json_filepath.parent.mkdir(parents=True, exist_ok=True)
            
            # Generate basic metadata based on source slug
            data = create_default_source_structure(source_slug)
        
        # Append new RSS feeds to existing ones (avoid duplicates)
        existing_feeds = data.get('rss_feeds', [])
        existing_urls = {feed['url'] for feed in existing_feeds}
        
        # Add only new feeds that don't already exist
        new_feeds_added = 0
        for feed in rss_feeds:
            if feed['url'] not in existing_urls:
                existing_feeds.append(feed)
                new_feeds_added += 1
                print(f"  Added: {feed['name']} - {feed['url']}")
            else:
                print(f"  Skipped (duplicate): {feed['name']} - {feed['url']}")
        
        data['rss_feeds'] = existing_feeds
        
        # Dynamically update categories based on all RSS feeds
        categories = set()
        for feed in data['rss_feeds']:
            categories.add(feed['category'])
        data['categories'] = sorted(list(categories))
        
        # Write back to file
        with open(json_filepath, 'w') as f:
            json.dump(data, f, indent=4)
        
        print(f"Updated {json_filepath} with {new_feeds_added} new RSS feeds (total: {len(data['rss_feeds'])})")
        return True
        
    except json.JSONDecodeError:
        print(f"Error: Invalid JSON in {json_filepath}")
        return False

def main():
    import argparse
    
    parser = argparse.ArgumentParser(description="Update RSS feeds in source JSON files from text files")
    parser.add_argument("txt_file", help="Text file containing RSS URLs")
    parser.add_argument("source_slug", nargs="?", help="Source slug for JSON filename (default: derived from txt_file)")
    parser.add_argument("--force", "-f", action="store_true", help="Skip confirmation prompt")
    
    args = parser.parse_args()
    
    txt_file = args.txt_file
    
    # Determine source slug
    if args.source_slug:
        source_slug = args.source_slug
    else:
        # Extract from filename (e.g., nyt.txt -> nyt)
        source_slug = Path(txt_file).stem
    
    # Get the script directory and construct paths
    script_dir = Path(__file__).parent
    workspace_dir = script_dir.parent
    
    # Construct full paths
    txt_filepath = workspace_dir / txt_file
    json_filepath = workspace_dir / 'database' / 'sources' / f'{source_slug}.json'
    
    print(f"Processing {txt_filepath}")
    print(f"Target JSON: {json_filepath}")
    
    # Load URLs from text file
    urls = load_rss_urls_from_file(txt_filepath)
    if not urls:
        print("No URLs found in text file")
        sys.exit(1)
    
    print(f"Found {len(urls)} RSS URLs")
    
    # Convert URLs to RSS feed objects
    rss_feeds = []
    for url in urls:
        name, category = extract_feed_name_and_category(url)
        rss_feeds.append({
            "name": name,
            "url": url,
            "category": category
        })
    
    # Display what will be added
    print("\nRSS feeds to be added:")
    for feed in rss_feeds:
        print(f"  {feed['name']} ({feed['category']}) - {feed['url']}")
    
    # Confirm before proceeding (unless --force is used)
    if not args.force:
        response = input(f"\nUpdate {json_filepath} with these feeds? (y/N): ")
        if response.lower() != 'y':
            print("Cancelled")
            sys.exit(0)
    
    # Update JSON file
    success = update_json_file(json_filepath, rss_feeds, source_slug)
    if success:
        print("Update completed successfully!")
    else:
        print("Update failed!")
        sys.exit(1)

if __name__ == '__main__':
    main()
