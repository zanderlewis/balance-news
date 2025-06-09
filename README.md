# Balance News

**See the full picture of every story. Compare news coverage across the entire political spectrum.**

Balance News is a comprehensive news aggregation platform that brings you balanced news coverage by providing perspectives from left, center, and right-leaning sources. Break out of information silos and get the complete story on current events.

## ✨ Features

- **🔄 Multi-Perspective Analysis** - Compare how the same story is covered across left, center, and right-leaning news sources
- **🔍 Smart Search & Filtering** - Find relevant stories quickly with powerful search and filter by bias, source, or time range
- **⚡ Daily Updates** - Stay informed with the latest news from trusted sources, updated every day
- **🎯 Bias Transparency** - Each source is clearly labeled with its bias rating so you can make informed reading choices
- **📱 Mobile Optimized** - Read balanced news anywhere with our responsive design that works on all devices
- **🔐 User Authentication** - Personal dashboard with bookmarking and reading preferences

## 🛠️ Tech Stack

### Backend
- **Laravel 12** - PHP web application framework
- **Livewire/Volt** - Full-stack reactive components
- **Flux UI** - Modern UI components for Livewire
- **SQLite** - Lightweight database for development

### Frontend
- **Tailwind CSS 4.1** - Utility-first CSS framework
- **Vite** - Fast build tool and development server
- **Responsive Design** - Mobile-first approach with dark mode support

### Tools & Utilities
- **Python Scripts** - RSS feed processing and data extraction
- **Composer** - PHP dependency management
- **NPM** - JavaScript package management

## 🚀 Getting Started

### Prerequisites

- PHP 8.4
- Composer
- Node.js & NPM
- UV (for Python tools)

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd balance.news
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install JavaScript dependencies**
   ```bash
   npm install
   ```

4. **Install Python dependencies**
   ```bash
   uv sync
   ```

5. **Set up environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

6. **Database setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

7. **Build assets**
   ```bash
   npm run dev
   ```

8. **Start the development server**
   ```bash
   php artisan serve
   ```

Visit `http://localhost:8000` to access the application.

## 📊 Database Structure

### Core Models

- **NewsSource** - News outlets with bias ratings and RSS feeds
- **Article** - Individual news articles with metadata
- **RssFeed** - RSS feed configurations for automated content ingestion
- **User** - User accounts with authentication
- **UserBookmarks** - Saved articles for users

### Key Features

- **Bias Classification** - Sources are categorized as left, center, or right-leaning
- **RSS Automation** - Automated content ingestion from multiple news sources
- **User Preferences** - Personalized reading experience with bookmarks

## 🔧 Development

### Running in Development Mode

Start the development servers:

```bash
php artisan serve
npm run dev
```

### RSS Feed Management

Update RSS feeds using the Python tools:

```bash
python tools/orchestrate_feeds.py
```

## 🌟 Key Components

### News Aggregation
- Automated RSS feed processing
- Article deduplication and categorization
- Bias rating integration

### User Interface
- Clean, modern design with dark mode
- Responsive layout for all devices
- Interactive comparison views

### Data Management
- SQLite for development simplicity
- Efficient article storage and retrieval
- User preference tracking

## 🔄 RSS Feed Processing

The platform includes Python tools for automated news aggregation:

- **`update_rss_feeds.py`** - Fetches latest articles from RSS feeds
- **`orchestrate_feeds.py`** - Coordinates feed processing workflows
- **`extract-xml.py`** - XML parsing utilities

## 🎨 UI/UX Features

- **Multi-perspective Grid** - Side-by-side comparison of news coverage
- **Bias Indicators** - Color-coded source labels (blue/gray/red)
- **Modern Design** - Gradient backgrounds and smooth animations
- **Accessibility** - ARIA labels and keyboard navigation support

## 📱 Responsive Design

The platform is optimized for:
- **Desktop** - Full-featured dashboard experience
- **Tablet** - Optimized grid layouts
- **Mobile** - Touch-friendly navigation and reading experience

## 🔐 Authentication

- User registration and login
- Session management
- Protected routes for user-specific features
- Profile settings and preferences
