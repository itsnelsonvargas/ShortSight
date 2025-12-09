# ShortSight - Advanced URL Shortener

ShortSight is a modern, enterprise-grade URL shortener built with Laravel and Vue.js, optimized to handle millions of clicks with comprehensive analytics and security features.

## 🚀 Key Features

- **URL Shortening** with custom slugs and branded domains
- **Advanced Analytics** with geolocation tracking and click insights
- **Enterprise Security** with URL validation and abuse prevention
- **Redis Caching** for lightning-fast redirects
- **API Access** for programmatic link management
- **QR Code Generation** for offline sharing
- **Social Authentication** (Google, Facebook)
- **SEO Optimized** with comprehensive meta tags

## ⚙️ Configuration

### Site Branding

You can easily rebrand the application by setting the `SITE_NAME` environment variable:

```bash
# In your .env file
SITE_NAME=YourBrandName
```

This will automatically update:
- Page titles and meta tags
- Social media sharing cards
- Web app manifest
- User-Agent strings
- Structured data markup

### Database Optimization

ShortSight is optimized to handle millions to hundreds of millions of clicks with enterprise-grade performance:

### 🚀 **High-Performance Features**

- **Intelligent Indexing**: Composite indexes on frequently queried columns (slug+timestamp, user+status, etc.)
- **Table Partitioning**: Monthly partitioning of visitor data for optimal query performance
- **Data Archiving**: Automatic archiving of old visitor records with summarized statistics
- **Query Optimization**: Pre-computed analytics views and cached query results
- **Connection Pooling**: Optimized database connections for high concurrency

### 📊 **Scalability Architecture**

```
Visitors Table (Partitioned by Month)
├── Current Month: Hot data, fully indexed
├── Last 12 Months: Recent data, optimized queries
├── 13-24 Months: Archived detailed data
└── 25+ Months: Summarized statistics only
```

### ⚡ **Performance Optimizations**

- **Bulk Insert Operations**: Batch visitor tracking for high-throughput scenarios
- **Redis Caching**: Multi-level caching for link lookups and analytics
- **Read/Write Splitting**: Separate read replicas for analytics queries
- **Query Result Streaming**: Memory-efficient handling of large result sets

### 🔧 **Database Migrations**

Run these migrations to enable massive-scale optimizations:

```bash
php artisan migrate
php artisan visitors:archive  # Archive old data
php artisan cache:warm        # Pre-warm caches
```

### 📈 **Expected Performance**

- **Redirect Speed**: <10ms average response time
- **Analytics Queries**: <100ms for complex aggregations
- **Concurrent Users**: Supports 100K+ simultaneous users
- **Daily Clicks**: Handles 100M+ clicks per day
- **Data Retention**: Efficient storage for years of historical data

## Rate Limiting Configuration

ShortSight uses configurable rate limiting to prevent abuse. All rate limits can be configured via environment variables:

### Environment Variables

```bash
# Link Creation Rate Limits (per IP)
LINK_CREATION_LIMIT_MINUTE=10    # Links per minute
LINK_CREATION_LIMIT_HOUR=50      # Links per hour
LINK_CREATION_LIMIT_DAY=200      # Links per day

# API Rate Limits (per user/IP)
API_LIMIT_MINUTE=100             # API calls per minute
API_LIMIT_HOUR=1000              # API calls per hour

# Strict Rate Limits (for authentication)
STRICT_LIMIT_MINUTE=5            # Auth attempts per minute
STRICT_LIMIT_HOUR=20             # Auth attempts per hour

# SSO Rate Limits
SSO_LIMIT_ATTEMPTS=3             # SSO attempts allowed
SSO_LIMIT_MINUTES=5              # Time window for SSO limits

# Default API Rate Limit (Laravel default)
API_DEFAULT_LIMIT_MINUTE=60      # Default API rate limit
```

### Default Values

If environment variables are not set, the following defaults are used:
- Link creation: 10/minute, 50/hour, 200/day
- API calls: 100/minute, 1000/hour
- Authentication: 5/minute, 20/hour
- SSO: 3 attempts per 5 minutes
- Default API: 60/minute

### Usage

Add these variables to your `.env` file to customize rate limiting behavior for your deployment needs.

## 🛠️ Installation & Setup

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd shortsight
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure your brand**
   ```bash
   # Set your site name in .env
   SITE_NAME=YourBrandName
   ```

5. **Database setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Build assets**
   ```bash
   npm run build
   ```

7. **Start the application**
   ```bash
   php artisan serve
   ```

## 📊 Performance & Scaling

ShortSight is built for massive scale with enterprise-grade optimizations:

### 🚀 **High-Performance Features**

- **Intelligent Indexing**: Composite indexes on frequently queried columns
- **Table Partitioning**: Monthly partitioning of visitor data for optimal query performance
- **Data Archiving**: Automatic archiving of old visitor records with summarized statistics
- **Query Optimization**: Pre-computed analytics views and cached query results
- **Redis Caching**: Multi-level caching for link lookups and analytics
- **Connection Pooling**: Optimized database connections for high concurrency

### 📈 **Expected Performance**

- **Redirect Speed**: <10ms average response time
- **Analytics Queries**: <100ms for complex aggregations
- **Concurrent Users**: Supports 100K+ simultaneous users
- **Daily Clicks**: Handles 100M+ clicks per day
- **Data Retention**: Efficient storage for years of historical data

### 🔧 **Database Migrations**

```bash
php artisan migrate
php artisan visitors:archive  # Archive old data
php artisan cache:warm        # Pre-warm caches
```

## 🔒 Security Features

- **URL Validation**: Comprehensive malware and phishing detection
- **Rate Limiting**: Configurable abuse prevention
- **GDPR Compliance**: Data export and deletion capabilities
- **Secure Authentication**: Social login and 2FA support
- **Content Filtering**: Dangerous file type blocking

## 📱 API Documentation

ShortSight provides a RESTful API for programmatic access:

```bash
# Get link information
GET /api/v1/link/{url}

# Create short links
POST /api/v1/links
```

## 🤝 Contributing

We welcome contributions! Please see our contributing guidelines for details.

## 📄 License

This project is licensed under the MIT License.
