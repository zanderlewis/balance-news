import argparse
import re
import sys
import urllib.request
import urllib.parse
import urllib.error
from html.parser import HTMLParser


class FeedLinkParser(HTMLParser):
    def __init__(self, base_url):
        super().__init__()
        self.base_url = base_url
        self.links = []
        self.xml_links = []

    def handle_starttag(self, tag, attrs):
        attrs_dict = dict(attrs)

        # Extract links from <link> tags
        if tag == "link" and "href" in attrs_dict:
            link_type = attrs_dict.get("type", "") or ""
            if any(
                xml_type in link_type
                for xml_type in [
                    "application/rss+xml",
                    "application/atom+xml",
                    "text/xml",
                    "application/xml",
                ]
            ):
                href = attrs_dict["href"]
                absolute_url = urllib.parse.urljoin(self.base_url, href)
                # Skip audio XML feeds
                if not is_audio_feed(absolute_url, attrs_dict.get("title", "")):
                    self.xml_links.append(
                        (attrs_dict.get("title", "Unnamed Feed"), absolute_url)
                    )

        # Extract all <a> tags for later filtering
        if tag == "a" and "href" in attrs_dict:
            href = attrs_dict["href"]
            self.links.append(href)


def is_audio_feed(url, title: str | None = ""):
    """Check if the URL indicates an audio feed."""
    # Filter URLs that contain a word in a list
    audio_keywords = [
        "audio",
        "podcast",
        "podcasts",
        "mp3",
        "m4a",
        "wav",
        "ogg",
        "soundcloud",
        "spotify",
    ]
    if any(keyword in url.lower() for keyword in audio_keywords):
        return True  # Is an audio feed
    return False  # Not an audio feed


def is_valid_feed(url):
    """Check if the URL points to a valid RSS/Atom feed."""
    try:
        headers = {
            "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3"
        }
        req = urllib.request.Request(url, headers=headers)
        with urllib.request.urlopen(req, timeout=5) as response:
            content = response.read(2000).decode("utf-8", errors="ignore")
            # Check for XML tags that typically appear in feeds
            if (
                ("<rss" in content and "<channel>" in content)
                or (
                    "<feed" in content
                    and 'xmlns="http://www.w3.org/2005/Atom"' in content
                )
                or ("<?xml" in content and ("<rss" in content or "<feed" in content))
            ):
                return True
            return False
    except Exception:
        return False


def check_if_xml_feed(url):
    """Check if the URL itself is an XML feed."""
    try:
        headers = {
            "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3"
        }
        req = urllib.request.Request(url, headers=headers)
        with urllib.request.urlopen(req, timeout=5) as response:
            content_type = response.headers.get('content-type', '').lower()
            content = response.read(2000).decode("utf-8", errors="ignore")
            
            # Check content type
            if any(xml_type in content_type for xml_type in ['xml', 'rss', 'atom']):
                # Verify it's actually a feed
                if (
                    ("<rss" in content and "<channel>" in content)
                    or (
                        "<feed" in content
                        and 'xmlns="http://www.w3.org/2005/Atom"' in content
                    )
                    or ("<?xml" in content and ("<rss" in content or "<feed" in content))
                ):
                    return True
            return False
    except Exception:
        return False


def extract_xml_links(url):
    """Extract all XML links from a given URL."""
    # First check if the URL itself is an XML feed
    if check_if_xml_feed(url) and not is_audio_feed(url):
        return [("Direct Feed", url)]
    
    try:
        # Make a request to the URL
        headers = {
            "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3"
        }
        req = urllib.request.Request(url, headers=headers)
        with urllib.request.urlopen(req) as response:
            html = response.read().decode("utf-8", errors="ignore")

        # Parse the HTML content
        parser = FeedLinkParser(url)
        parser.feed(html)

        # Process regular links to find potential feeds
        xml_pattern = re.compile(r"\.xml$|\.rss$|feed|rss|atom", re.IGNORECASE)
        xml_links = parser.xml_links.copy()

        potential_feeds = []
        for link in parser.links:
            if link and xml_pattern.search(link):
                absolute_url = urllib.parse.urljoin(url, link)
                if absolute_url not in [x[1] for x in xml_links] and not is_audio_feed(absolute_url):  # Avoid duplicates and audio feeds
                    potential_feeds.append(("Potential Feed", absolute_url))

        # Verify which potential feeds are actually RSS/Atom feeds
        verified_feeds = xml_links.copy()
        print(f"Checking {len(potential_feeds)} potential feeds...", file=sys.stderr)

        for title, feed_url in potential_feeds:
            if is_valid_feed(feed_url):
                verified_feeds.append((title, feed_url))

        return verified_feeds

    except urllib.error.URLError as e:
        print(f"Error fetching URL: {e}", file=sys.stderr)
        return []


def main():
    # Set up command line arguments
    parser = argparse.ArgumentParser(description="Extract XML/RSS links from a website")
    parser.add_argument("url", help="URL of the website to extract XML links from")
    parser.add_argument(
        "--output",
        "-o",
        help="Output file to save the results",
        default=None,
    )
    args = parser.parse_args()

    # Extract XML links
    xml_links = extract_xml_links(args.url)

    # Print results
    if xml_links:
        print(f"Found {len(xml_links)} verified RSS/Atom feeds.")
        if args.output:
            with open(args.output, "w") as f:
                for _, url in xml_links:
                    f.write(f"{url}\n")
        else:
            print("Results:")
            for title, url in xml_links:
                print(f"- {title}: {url}")
    else:
        print("No RSS/Atom feeds found")


if __name__ == "__main__":
    main()
