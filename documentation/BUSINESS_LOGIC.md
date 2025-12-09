# ShortSight - Business Logic Documentation

*Last Updated: December 9, 2025*

---

## Table of Contents

1. [Executive Summary](#executive-summary)
2. [Core Business Model](#core-business-model)
3. [Business Entities](#business-entities)
4. [Business Flows](#business-flows)
5. [Business Rules & Validations](#business-rules--validations)
6. [Security & Anti-Abuse](#security--anti-abuse)
7. [Rate Limiting Strategy](#rate-limiting-strategy)
8. [Monetization Model](#monetization-model)
9. [GDPR Compliance](#gdpr-compliance)
10. [Analytics & Tracking](#analytics--tracking)
11. [API Business Logic](#api-business-logic)
12. [Database Strategy](#database-strategy)
13. [Future Business Features](#future-business-features)

---

## Executive Summary

**ShortSight** is a URL shortening and analytics platform built on Laravel and Vue.js, designed to help individuals and businesses create, manage, and track shortened URLs with comprehensive visitor analytics.

### Core Value Propositions

1. **Instant URL Shortening**: Convert long URLs into short, memorable links
2. **Advanced Analytics**: Track clicks, geographic data, devices, and user behavior
3. **Security First**: Google Safe Browsing integration and malicious URL detection
4. **Developer Friendly**: RESTful API for programmatic access
5. **GDPR Compliant**: Full data portability and user data protection
6. **Enterprise Ready**: Database optimized for millions to hundreds of millions of clicks

### Target Market

- **Individual Users**: Social media marketers, content creators, influencers
- **Small Businesses**: Marketing teams tracking campaign performance
- **Enterprises**: Large-scale link management with team collaboration (planned)
- **Developers**: API integration for automated link management

---

## Core Business Model

### Primary Revenue Streams

1. **Freemium Model**
   - Free Tier: Limited links, basic analytics
   - Pro Tier: Unlimited links, advanced analytics, custom domains (planned)
   - Enterprise Tier: Team features, priority support, white-label (planned)

2. **Advertisement Revenue** (Planned)
   - Non-intrusive interstitial ads for free tier users
   - Google AdSense integration

3. **API Access** (Planned)
   - Free tier: Limited API calls
   - Paid tiers: Higher rate limits and premium endpoints

### Value Exchange

**Free Users**:
- **Get**: Basic URL shortening, QR code generation, basic analytics
- **Give**: View advertisements, limited usage quotas

**Paid Users**:
- **Get**: Unlimited links, ad-free experience, advanced analytics, custom domains
- **Pay**: Monthly/annual subscription fees

---

## Business Entities

### 1. User

**Purpose**: Represents registered platform users who can create and manage links

**Key Attributes**:
- `id` - Unique identifier
- `name` - User's display name
- `email` - Unique email address (authentication)
- `password` - Encrypted with salt and pepper (PasswordEncryptionService)
- `password_salt` - Random salt for password hashing
- `google_id` - Google OAuth identifier (SSO)
- `facebook_id` - Facebook OAuth identifier (SSO)
- `created_at` / `updated_at` - Timestamps

**Business Roles**:
- **Anonymous Users**: Can create links without registration (IP-based rate limiting)
- **Registered Users**: Can manage links, view analytics, export data
- **Premium Users** (Planned): Enhanced features and higher limits

**Relationships**:
- One user can own many links (`links.user` foreign key)
- User can have multiple API tokens (Laravel Sanctum)

---

### 2. Link

**Purpose**: Core entity representing shortened URLs

**Key Attributes**:
- `id` - Unique identifier
- `user` - Owner's user ID (nullable for anonymous links)
- `url` - Original long URL (required, validated)
- `slug` - Short unique identifier (3-20 chars, alphanumeric + dash)
- `title` - Optional descriptive title
- `description` - Optional description
- `is_disabled` - Boolean flag to disable/enable link
- `created_at` / `updated_at` - Timestamps
- `deleted_at` - Soft delete timestamp

**Business Rules**:
- Slug must be unique across the entire platform
- Anonymous links are not associated with any user (`user` is null)
- Disabled links return 404 when accessed
- Soft deletes allow link recovery and analytics preservation

**Relationships**:
- Belongs to one user (optional)
- Has many visitors (click records)

---

### 3. Visitor

**Purpose**: Tracks individual click events and visitor analytics

**Key Attributes**:
- `id` - Unique identifier
- `slug` - Reference to the accessed link
- `ip_address` - Visitor's IP (for geo-location, anonymized for GDPR)
- `user_agent` - Browser user agent string
- `browser` - Detected browser (Chrome, Firefox, Safari, etc.)
- `device` - Device type (Desktop, Mobile, Tablet)
- `platform` - Operating system (Windows, macOS, iOS, Android, Linux)
- `referer` - Source URL (where the click originated)
- `country` - Geo-located country
- `city` - Geo-located city
- `region` - Geo-located region/state
- `postal_code` - Postal/ZIP code
- `latitude` / `longitude` - Geographic coordinates
- `has_vpn` - Boolean indicating VPN detection
- `vpn` - VPN provider information
- `created_at` - Timestamp of the click

**Business Value**:
- Powers analytics dashboards
- Enables geographic targeting insights
- Fraud detection (VPN, repeated clicks)
- Campaign attribution (referer tracking)

**Database Optimization**:
- **Partitioning**: Monthly partitions for scalability
- **Archiving**: Old records summarized and detailed data removed
- **Indexing**: Composite indexes on `slug` + `created_at` for fast queries

**Relationships**:
- Belongs to one link (via `slug`)

---

## Business Flows

### 1. Anonymous Link Creation Flow

**Actors**: Anonymous user (no authentication required)

**Trigger**: User submits a URL to be shortened

**Process**:

1. **Input Validation**
   - Validate URL format (must be valid URL)
   - Validate custom slug (optional, alphanumeric + dash, max 20 chars)
   - Check if custom slug is already taken

2. **Slug Generation**
   - If custom slug provided: validate uniqueness
   - If auto-generate: create random 7-character alphanumeric string
   - Loop until unique slug is found (collision detection)

3. **Security Check**
   - Call Google Safe Browsing API via `UrlSafetyService`
   - Check for MALWARE and SOCIAL_ENGINEERING threats
   - Reject malicious URLs with error message

4. **Rate Limiting**
   - Check IP-based rate limits (configurable):
     - Minute: 10 links (default)
     - Hour: 50 links (default)
     - Day: 200 links (default)
   - Return 429 error if limit exceeded

5. **Link Creation**
   - Save link to database with `user = null`
   - Return short URL: `{domain}/{slug}`

6. **Response**
   - Web: Render page with short URL and QR code
   - API: JSON response with slug, short_url, original_url

**Business Rules**:
- Anonymous links are not editable or deletable
- No analytics dashboard access for anonymous users
- Links persist indefinitely unless platform cleanup policy applies

**Code Reference**: `LinkController::storeWithoutUserAccount()` (line 33)

---

### 2. URL Redirection Flow

**Actors**: Any internet user clicking a short link

**Trigger**: User visits `{domain}/{slug}`

**Process**:

1. **Slug Resolution**
   - Query database: `SELECT * FROM links WHERE slug = ? AND is_disabled = false`
   - If not found: return 404 error
   - If disabled: return 404 error (business rule: disabled = not found)

2. **Visitor Tracking** (Async/Background - Planned)
   - Capture IP address
   - Parse user agent (browser, device, platform)
   - Geo-locate IP (country, city, region, coordinates)
   - Detect VPN usage
   - Record referer URL
   - Save to `visitors` table

3. **Redirection**
   - HTTP 302 redirect to original URL
   - Target: `< 10ms` response time (performance requirement)

**Business Rules**:
- Tracking should not delay redirection (async processing recommended)
- Disabled links are indistinguishable from non-existent links (privacy)
- No tracking for bots/crawlers (optional enhancement)

**Code Reference**: `LinkController::show()` (line 115)

---

### 3. User Registration Flow

**Actors**: New user

**Trigger**: User submits registration form

**Process**:

1. **Input Validation**
   - Name: required, string, max 255 chars
   - Email: required, valid email, unique, max 255 chars
   - Password: required, min 8 chars, confirmed (password_confirmation field)

2. **Rate Limiting**
   - Strict rate limit: 5 attempts per minute, 20 per hour
   - Prevents automated account creation abuse

3. **Password Encryption**
   - Generate random 32-byte salt
   - Combine password with salt and pepper (from environment)
   - Hash using Argon2id (Laravel default, most secure)
   - Store hash and salt separately

4. **User Creation**
   - Save user record to database
   - Password automatically encrypted via `User::setPasswordAttribute()` mutator

5. **Response**
   - Return user ID, name, email (exclude sensitive data)
   - Auto-login optional (planned)

**Security Features**:
- **Salt and Pepper**: Each password has unique salt + application-wide pepper
- **Argon2id**: Resistant to GPU/ASIC attacks
- **Automatic Rehashing**: Password rehashed if algorithm parameters improve

**Code Reference**: `UserController::store()` (line 18)

---

### 4. User Authentication Flow

**Actors**: Registered user

**Trigger**: User submits login credentials

**Process**:

1. **Credential Validation**
   - Find user by email
   - Verify password using `User::verifyPassword()`
   - Salt + pepper verification via `PasswordEncryptionService`

2. **Rate Limiting**
   - Strict throttle: 5 attempts per minute, 20 per hour
   - Prevents brute force attacks

3. **Password Rehashing**
   - Check if password needs rehashing (algorithm upgrade)
   - Automatically rehash and save if needed
   - Maintains security over time

4. **Token Generation**
   - Create Laravel Sanctum personal access token
   - Abilities: `['read', 'write']` (API permissions)
   - Return token to client

5. **Session Management**
   - Token used for API authentication (`auth:sanctum` middleware)
   - Token revoked on logout

**Business Rules**:
- Failed login does not reveal if email exists (security)
- Tokens are long-lived (no expiration unless configured)
- Rehashing happens transparently without user action

**Code Reference**: `AuthController::login()` (line 28)

---

### 5. SSO (Social Authentication) Flow

**Actors**: User choosing Google or Facebook login

**Supported Providers**: Google, Facebook

**Process**:

1. **OAuth Initiation**
   - User clicks "Sign in with Google/Facebook"
   - Redirect to provider's OAuth consent page

2. **Rate Limiting**
   - SSO attempts: 3 per 5 minutes (configurable)
   - Prevents OAuth abuse

3. **Callback Handling**
   - Receive OAuth code from provider
   - Exchange code for access token and user profile
   - Extract: name, email, provider ID

4. **User Lookup/Creation**
   - Search for existing user by `google_id` or `facebook_id`
   - If not found: create new user account
   - If found: log in existing user

5. **Session Establishment**
   - Create authenticated session
   - Redirect to dashboard

**Business Rules**:
- Email from OAuth provider must be verified by provider
- Users can link multiple OAuth providers to one account (planned)
- No password required for SSO-only accounts

**Code Reference**: `SSOController` (routes: line 31-42 in web.php)

---

### 6. GDPR Data Export Flow

**Actors**: Authenticated user

**Trigger**: User requests data export (GDPR Article 20 - Right to Data Portability)

**Process**:

1. **Authentication Check**
   - Require `auth:sanctum` middleware
   - Verify user is authenticated

2. **Data Collection**
   - User profile data (name, email, created_at)
   - All owned links (url, slug, title, description, created_at)
   - All visitor analytics for owned links
   - API tokens (sanitized)

3. **Data Structuring**
   - Format as structured JSON
   - Include metadata (export date, version)
   - Include data summary (counts, date ranges)

4. **Download Response**
   - Generate JSON file
   - Filename: `shortsight_data_export_{user_id}_{timestamp}.json`
   - Content-Type: `application/json`
   - Content-Disposition: `attachment`

**Data Export Structure**:
```json
{
  "export_metadata": {
    "export_date": "2025-12-09T10:30:00Z",
    "data_format_version": "1.0",
    "gdpr_compliant": true
  },
  "user_data": {
    "id": 123,
    "name": "John Doe",
    "email": "john@example.com"
  },
  "links": [...],
  "analytics_summary": {...},
  "data_summary": {
    "total_links": 45,
    "total_clicks": 12500
  }
}
```

**Business Rules**:
- User can export data unlimited times
- Export includes all historical data (no date limits)
- IP addresses in visitor data are anonymized for privacy

**Code Reference**: `UserController::exportData()` (line 63)

---

### 7. Slug Availability Check Flow

**Actors**: User creating custom slug

**Trigger**: Real-time validation during link creation

**Process**:

1. **Input Validation**
   - Slug must be alphanumeric + dash
   - Length: 3-20 characters

2. **Database Lookup**
   - Query: `SELECT EXISTS(SELECT 1 FROM links WHERE slug = ?)`
   - Fast query (indexed on `slug`)

3. **Response**
   - JSON: `{ "available": true/false }`

**Business Value**:
- Improved UX: instant feedback before submission
- Reduces failed submissions
- Prevents slug collision errors

**Code Reference**: `LinkController::checkSlug()` (line 129)

---

## Business Rules & Validations

### URL Validation Rules

| Rule | Requirement | Rationale |
|------|-------------|-----------|
| **Format** | Must be valid URL (RFC 3986) | Ensures redirect destination is reachable |
| **Protocol** | HTTP/HTTPS required | Security and browser compatibility |
| **Malware Check** | Google Safe Browsing API | Protects users from phishing/malware |
| **Domain Blacklist** | (Planned) Block known spam domains | Prevent platform abuse |
| **Self-Reference** | (Planned) Block ShortSight domain | Prevent redirect loops |

**Code Reference**: `LinkController::storeWithoutUserAccount()` (line 27-30, 73-75)

---

### Slug Validation Rules

| Rule | Requirement | Rationale |
|------|-------------|-----------|
| **Length** | 3-20 characters | Balance between brevity and collision probability |
| **Characters** | Alphanumeric + dash only | URL-safe, readable |
| **Uniqueness** | Must be globally unique | Core requirement for link resolution |
| **Reserved Words** | Cannot use: dashboard, login, register, auth, api | Prevents routing conflicts |
| **Case Sensitivity** | Case-sensitive | Increases slug namespace |

**Auto-Generation Logic**:
- Length: 7 characters
- Character set: `a-z`, `A-Z`, `0-9` (62 possible characters)
- Collision probability: ~0.01% at 1M links (62^7 = 3.5 trillion combinations)

**Code Reference**: `LinkController::storeWithoutUserAccount()` (line 62-65)

---

### Password Security Rules

| Rule | Requirement | Implementation |
|------|-------------|----------------|
| **Minimum Length** | 8 characters | Laravel validation |
| **Confirmation** | Must match `password_confirmation` | Prevents typos |
| **Hashing Algorithm** | Argon2id | Most secure, resistant to GPU attacks |
| **Salt** | 32-byte random salt per user | Unique even if passwords match |
| **Pepper** | Application-wide secret | Additional layer from `.env` |
| **Rehashing** | Automatic on login if needed | Future-proof security upgrades |

**Storage**:
- Password hash stored in `users.password`
- Salt stored in `users.password_salt`
- Pepper stored in `.env` file (not in database)

**Code Reference**: `User::setPasswordAttribute()` (line 58), `PasswordEncryptionService`

---

### Link Lifecycle Rules

| State | Access Behavior | Analytics | Editability |
|-------|----------------|-----------|-------------|
| **Active** | Redirects to URL | Tracked | Owner can edit |
| **Disabled** | Returns 404 | Not tracked | Owner can enable |
| **Soft Deleted** | Returns 404 | Preserved | Recoverable (30 days) |
| **Hard Deleted** | Returns 404 | Archived | Permanent deletion |

**Business Rationale**:
- Disabled links: Temporary pause (campaign ended, testing)
- Soft delete: Accidental deletion recovery
- Hard delete: GDPR deletion requests

**Code Reference**: `Link` model uses `SoftDeletes` trait (line 11)

---

## Security & Anti-Abuse

### 1. Malicious URL Prevention

**Technology**: Google Safe Browsing API

**Process**:
1. Send URL to Google Safe Browsing API
2. Check threat types: `MALWARE`, `SOCIAL_ENGINEERING`
3. Reject URL if matches found

**Threat Types Detected**:
- Malware distribution sites
- Phishing pages
- Social engineering attacks
- Unwanted software downloads

**Fallback**: If API fails, allow URL (fail-open strategy to maintain service availability)

**Business Impact**:
- Protects platform reputation
- Prevents ShortSight from being used in attacks
- User trust and safety

**Code Reference**: `UrlSafetyService::isMalicious()` (line 16)

---

### 2. VPN Detection

**Purpose**: Fraud detection and abuse prevention

**Captured Data**:
- `has_vpn` - Boolean flag
- `vpn` - VPN provider name (if detected)

**Use Cases**:
- Identify bot traffic
- Detect click fraud
- Geographic analytics accuracy warnings

**Business Rule**: VPN usage is tracked but not blocked (user privacy respect)

**Code Reference**: `Visitor` model (line 33-34)

---

### 3. CAPTCHA Integration (Planned)

**Recommendation**: Google reCAPTCHA v3

**Implementation Points**:
- Anonymous link creation (high abuse risk)
- User registration
- Password reset requests

**Business Rationale**:
- Prevents automated spam link creation
- Reduces database pollution
- Improves rate limiting effectiveness

---

## Rate Limiting Strategy

ShortSight implements **multi-tier, configurable rate limiting** to prevent abuse while maintaining usability.

### 1. Link Creation Rate Limits

**Scope**: Per IP address (anonymous + authenticated users)

**Default Limits** (configurable via `.env`):

| Window | Default | Environment Variable | Purpose |
|--------|---------|---------------------|----------|
| **Per Minute** | 10 links | `LINK_CREATION_LIMIT_MINUTE` | Prevent burst spam |
| **Per Hour** | 50 links | `LINK_CREATION_LIMIT_HOUR` | Sustained abuse prevention |
| **Per Day** | 200 links | `LINK_CREATION_LIMIT_DAY` | Long-term quota |

**HTTP Responses**:
- Status: `429 Too Many Requests`
- Headers: `X-RateLimit-Limit`, `X-RateLimit-Remaining`, `Retry-After`
- Body: Error message with retry time

**Business Rationale**:
- **Minute limit**: Stops automated scripts
- **Hour limit**: Prevents sustained bot attacks
- **Day limit**: Fair usage for legitimate users

**Code Reference**: `LinkCreationRateLimit` middleware (line 32-56)

---

### 2. API Rate Limits

**Scope**: Per user (authenticated) or IP (anonymous)

**Default Limits**:

| Endpoint Type | Per Minute | Per Hour | Env Variable |
|---------------|------------|----------|--------------|
| **General API** | 100 | 1,000 | `API_LIMIT_MINUTE`, `API_LIMIT_HOUR` |
| **Default** | 60 | - | `API_DEFAULT_LIMIT_MINUTE` |

**Applied To**:
- Slug availability checks
- Link lookups
- URL safety checks
- Analytics queries (planned)

**Business Model**:
- Free tier: Default limits
- Pro tier: 10x limits (planned)
- Enterprise: Custom limits (planned)

**Code Reference**: `api.php` routes with `api.throttle` middleware (line 56)

---

### 3. Authentication Rate Limits

**Scope**: Per IP address

**Strict Limits** (security-critical):

| Action | Per Minute | Per Hour | Env Variable |
|--------|------------|----------|--------------|
| **Login Attempts** | 5 | 20 | `STRICT_LIMIT_MINUTE`, `STRICT_LIMIT_HOUR` |
| **Registration** | 5 | 20 | Same |
| **Password Reset** | 5 | 20 | Same |

**Business Rationale**:
- Prevents brute force password attacks
- Stops automated account creation
- Limits credential stuffing attacks

**Code Reference**: `api.php` routes with `strict.throttle` middleware (line 24)

---

### 4. SSO Rate Limits

**Scope**: Per IP address

**Limits**:

| Action | Attempts | Window | Env Variable |
|--------|----------|--------|--------------|
| **OAuth Attempts** | 3 | 5 minutes | `SSO_LIMIT_ATTEMPTS`, `SSO_LIMIT_MINUTES` |

**Business Rationale**:
- Prevents OAuth token harvesting
- Limits impact of compromised OAuth apps
- Reduces server load from OAuth callbacks

**Code Reference**: `web.php` SSO routes (line 32)

---

### Rate Limit Configuration

All rate limits are configurable via `.env` file, allowing:
- **Development**: Higher limits for testing
- **Production**: Strict limits for security
- **Enterprise Deployments**: Custom limits per client

**Example `.env` Configuration**:
```env
# Link Creation
LINK_CREATION_LIMIT_MINUTE=10
LINK_CREATION_LIMIT_HOUR=50
LINK_CREATION_LIMIT_DAY=200

# API
API_LIMIT_MINUTE=100
API_LIMIT_HOUR=1000

# Authentication
STRICT_LIMIT_MINUTE=5
STRICT_LIMIT_HOUR=20

# SSO
SSO_LIMIT_ATTEMPTS=3
SSO_LIMIT_MINUTES=5
```

---

## Monetization Model

### Current State: Free for All Users

**Implemented Features** (Free):
- Unlimited link creation (rate-limited)
- Visitor tracking and analytics
- QR code generation
- Google Safe Browsing protection
- API access (rate-limited)

---

### Planned Freemium Tiers

#### Free Tier
**Target**: Individual users, hobbyists, small campaigns

**Features**:
- 100 links per month
- Basic analytics (30 days retention)
- Community support
- Interstitial ads on redirects
- API: 1,000 calls/month

**Limits**:
- Standard rate limiting
- No custom domains
- No team features
- No export/reporting

**Revenue**: Ad impressions on free tier links

---

#### Pro Tier ($9.99/month)
**Target**: Professional marketers, small businesses

**Features**:
- **Unlimited links**
- **Advanced analytics** (1 year retention)
  - Click-through rates
  - Conversion tracking
  - Geographic heatmaps
  - Device breakdown
- **No advertisements**
- **Custom slugs** (branded short links)
- **QR code customization** (colors, logos)
- **Email support**
- **API**: 50,000 calls/month
- **Data export** (CSV, JSON)

**Limits**:
- Single user account
- No custom domains
- No team collaboration

**Revenue**: $9.99/month subscription

---

#### Enterprise Tier ($49.99/month)
**Target**: Agencies, large businesses, development teams

**Features**:
- **Everything in Pro**
- **Custom domains** (your.brand/link)
- **Team collaboration** (10 seats included)
  - Role-based permissions
  - Shared link libraries
  - Team analytics dashboards
- **Advanced security**
  - SSO integration (SAML, LDAP)
  - IP whitelisting
  - Two-factor authentication
- **Priority support** (24/7)
- **API**: 500,000 calls/month
- **Webhooks** (real-time notifications)
- **White-label** (remove ShortSight branding)
- **SLA**: 99.9% uptime guarantee

**Revenue**: $49.99/month + $4.99/additional seat

---

### Additional Revenue Streams

#### 1. Pay-As-You-Go API
- $0.0001 per API call beyond plan limits
- Targeted at developers and high-volume users

#### 2. Affiliate Program
- 20% recurring commission for referrals
- Tracked via special referral links
- Paid monthly via PayPal/Stripe

#### 3. Premium Features (Add-ons)
- **Link Retargeting**: $19.99/month (Facebook/Google pixel injection)
- **A/B Testing**: $14.99/month (split test destinations)
- **Advanced Reports**: $9.99/month (custom dashboards, scheduled exports)

#### 4. Sponsored Links (Future)
- Advertisers pay to display content via short links
- Revenue share with link creators

---

### Subscription Business Logic (Planned)

**Payment Processing**: Stripe

**Billing Cycle**:
- Monthly or annual (annual = 20% discount)
- Auto-renewal with 7-day email reminder

**Usage Tracking**:
- Links created this month
- API calls this month
- Storage used (analytics data)

**Enforcement**:
- Soft limits: Warning emails at 80%, 90%, 100%
- Hard limits: Block link creation/API calls when exceeded
- Grace period: 3 days to upgrade before link deactivation

**Cancellation Policy**:
- Immediate cancellation: Downgrade to free tier
- Links remain active (converted to free tier ads)
- Analytics data retained for 90 days

**Code Requirements** (Not Yet Implemented):
- Subscription model in database
- Stripe webhook handling
- Usage tracking middleware
- Plan enforcement logic

---

## GDPR Compliance

ShortSight is designed to comply with **EU General Data Protection Regulation (GDPR)** and **California Consumer Privacy Act (CCPA)**.

### Implemented Compliance Features

#### 1. Right to Data Portability (GDPR Article 20)

**Implementation**: `DataExportService`

**User Rights**:
- Download all personal data in structured JSON format
- Includes: profile, links, analytics, API tokens
- Machine-readable format for transfer to other services

**Data Export Contents**:
```json
{
  "export_metadata": {
    "export_date": "ISO 8601 timestamp",
    "data_format_version": "1.0",
    "gdpr_compliant": true
  },
  "user_data": {
    "id": "user ID",
    "name": "full name",
    "email": "email address",
    "created_at": "registration date"
  },
  "links": [
    {
      "id": "link ID",
      "url": "original URL",
      "slug": "short slug",
      "title": "optional title",
      "created_at": "creation date",
      "click_count": "total clicks"
    }
  ],
  "analytics_summary": {
    "total_clicks": "number",
    "countries": ["array of countries"],
    "date_range": "first to last click"
  },
  "data_summary": {
    "total_links": "count",
    "total_clicks": "count",
    "account_age_days": "number"
  }
}
```

**Access**:
- API endpoint: `GET /api/user/data-export/download`
- Authentication required: `auth:sanctum`

**Code Reference**: `UserController::exportData()` (line 63)

---

#### 2. Right to Deletion (GDPR Article 17) (Planned)

**Scope**:
- Delete user account and all associated data
- Remove personal information from analytics
- Anonymize historical data (preserve statistics)

**Deletion Process** (To Be Implemented):
1. User requests account deletion
2. Confirmation email with 7-day cooling-off period
3. Permanent deletion:
   - User profile deleted
   - Owned links soft-deleted (30-day recovery)
   - IP addresses in visitor data anonymized
   - Email address removed from all records
4. Confirmation email: "Your data has been deleted"

**Data Retention**:
- Anonymous analytics: Aggregated, no personal data
- Legal requirements: Transaction records (7 years)
- Backups: Purged within 90 days

---

### Privacy Measures

#### IP Address Handling
- **Collection**: Required for geo-location and fraud detection
- **Storage**: Full IP stored (analytics requirement)
- **Export**: Anonymized in GDPR exports (last octet removed: `192.168.1.x`)
- **Retention**: 24 months (configurable)

#### Cookie Policy (To Be Implemented)
- **Essential Cookies**: Authentication, CSRF protection
- **Analytics Cookies**: User consent required (GDPR)
- **Advertisement Cookies**: User consent required

#### Privacy Policy (To Be Implemented)
- Clear explanation of data collection
- Purpose of each data point
- User rights and contact information
- Data sharing (Google Safe Browsing, OAuth providers)

---

## Analytics & Tracking

### Visitor Data Collection

**Captured on Each Click**:

| Data Point | Source | Purpose |
|------------|--------|---------|
| **IP Address** | HTTP request | Geo-location, fraud detection |
| **User Agent** | HTTP header | Browser/device detection |
| **Browser** | Parsed from user agent | Analytics segmentation |
| **Device Type** | Parsed from user agent | Mobile vs desktop insights |
| **Platform** | Parsed from user agent | OS analytics |
| **Referer** | HTTP header | Traffic source attribution |
| **Country** | IP geo-location | Geographic analytics |
| **City** | IP geo-location | Local campaign insights |
| **Region** | IP geo-location | Regional targeting |
| **Postal Code** | IP geo-location | Hyper-local analytics |
| **Latitude/Longitude** | IP geo-location | Map visualizations |
| **VPN Detection** | IP analysis | Fraud detection |
| **Timestamp** | Server time | Time-series analytics |

**Code Reference**: `Visitor` model (line 19-35)

---

### Analytics Use Cases

#### 1. Click Performance
- Total clicks per link
- Clicks over time (daily, weekly, monthly)
- Peak traffic times
- Click-through rate (CTR)

#### 2. Geographic Insights
- Top countries, cities, regions
- Geographic heatmaps
- Regional campaign performance
- Time zone adjustments

#### 3. Device & Browser Analytics
- Desktop vs mobile vs tablet distribution
- Browser market share (Chrome, Safari, Firefox, etc.)
- OS distribution (Windows, macOS, iOS, Android, Linux)
- Device-specific optimizations

#### 4. Traffic Sources
- Direct traffic (no referer)
- Social media platforms (Facebook, Twitter, Instagram, LinkedIn)
- Search engines (Google, Bing)
- Email campaigns
- External websites

#### 5. Fraud Detection
- Repeated clicks from same IP
- VPN usage patterns
- Bot traffic identification
- Click farms (abnormal geographic clustering)

---

### Database Performance Strategy

**Challenge**: Millions to hundreds of millions of visitor records

**Solutions Implemented**:

#### 1. Indexing
- Composite index: `(slug, created_at)` - Fast time-range queries
- Index: `ip_address` - Fraud detection lookups
- Index: `country` - Geographic filtering

#### 2. Table Partitioning
- **Strategy**: Monthly partitions
- **Current month**: Hot data, full indexes, fast writes
- **Last 12 months**: Recent data, optimized for reads
- **13-24 months**: Archived partitions
- **25+ months**: Summarized statistics, detailed data deleted

**Code Reference**: `2025_12_08_000001_partition_visitors_table.php` migration

#### 3. Data Archiving
- **Archiving Service**: `DatabaseOptimizationService`
- **Process**:
  1. Aggregate old visitor data into daily summaries
  2. Store: total clicks, top countries, top browsers
  3. Delete individual visitor records
  4. Preserve link-level statistics

**Code Reference**: `2025_12_08_000002_create_data_archiving_strategy.php` migration

#### 4. Query Optimization
- **Materialized Views**: Pre-computed analytics for dashboards
- **Redis Caching**: Link lookups, analytics queries (planned)
- **Read Replicas**: Separate analytics queries from transactional writes (planned)

**Performance Targets**:
- Redirect speed: < 10ms
- Analytics queries: < 100ms (complex aggregations)
- Support: 100M+ clicks per day
- Concurrent users: 100K+

**Code Reference**: README.md Database Optimization section

---

## API Business Logic

### API Authentication

**Method**: Laravel Sanctum (token-based)

**Token Generation**:
1. User logs in via `/api/login`
2. Receive personal access token
3. Include in requests: `Authorization: Bearer {token}`

**Token Abilities**: `['read', 'write']` (permissions)

**Token Management**:
- Create: `POST /api/v1/create-token` (planned)
- Revoke: `GET /api/v1/delete-token` (planned)
- List: User dashboard (planned)

**Code Reference**: `AuthController::login()` (line 42)

---

### API Endpoints

#### 1. Link Creation
**Endpoint**: `POST /api/links`

**Request**:
```json
{
  "url": "https://example.com/very/long/url",
  "customSlugInput": "my-slug" // optional
}
```

**Response** (Success):
```json
{
  "success": true,
  "slug": "my-slug",
  "short_url": "https://shortsight.app/my-slug",
  "original_url": "https://example.com/very/long/url"
}
```

**Response** (Error - Malicious URL):
```json
{
  "message": "The URL is malicious.",
  "errors": {
    "url": ["The URL is malicious."]
  }
}
```

**Rate Limit**: Link creation throttle (10/min, 50/hour, 200/day)

**Code Reference**: `LinkController::storeWithoutUserAccount()` (line 92-98)

---

#### 2. Slug Availability Check
**Endpoint**: `GET /api/check-slug?slug={slug}`

**Response**:
```json
{
  "available": true
}
```

**Use Case**: Real-time validation in UI

**Code Reference**: `LinkController::checkSlug()` (line 129)

---

#### 3. URL Safety Check
**Endpoint**: `GET /api/v1/check-url/{url}`

**Response**:
```json
{
  "safe": true,
  "threats": []
}
```

**Use Case**: Pre-validation before link creation

**Code Reference**: `ApiController::isUrlSafe()` (planned)

---

#### 4. Get Link by URL
**Endpoint**: `GET /api/v1/link/{url}`

**Response**:
```json
{
  "slug": "abc123",
  "short_url": "https://shortsight.app/abc123",
  "created_at": "2025-12-09T10:30:00Z"
}
```

**Use Case**: Check if URL already shortened

**Code Reference**: `ApiController::getStoredLink()` (api.php line 70)

---

#### 5. Health Check
**Endpoint**: `GET /api/v1/ping`

**Response**:
```json
{
  "status": "success",
  "message": "pong"
}
```

**Use Case**: Service monitoring, uptime checks

**Code Reference**: `api.php` (line 57-62)

---

### API Rate Limiting

**Applied to All API Routes**: `api.throttle` middleware

**Default**: 100 requests/minute, 1,000 requests/hour

**Headers** (included in responses):
```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 87
X-RateLimit-Reset: 1702123456
Retry-After: 45
```

**Business Logic**:
- Free tier: Default limits
- Pro tier: 10x limits (1,000/min, 10,000/hour) - planned
- Enterprise: Custom limits + burst allowance - planned

---

## Database Strategy

### Schema Overview

**Core Tables**:
1. `users` - User accounts
2. `links` - Shortened URLs
3. `visitors` - Click analytics (partitioned)
4. `personal_access_tokens` - API authentication
5. `password_reset_tokens` - Password recovery

**Relationships**:
- `links.user` → `users.id` (nullable, for anonymous links)
- `visitors.slug` → `links.slug` (analytics relationship)

---

### Scalability Architecture

#### Links Table
- **Current Scale**: Thousands to millions of links
- **Indexing**:
  - PRIMARY KEY: `id`
  - UNIQUE KEY: `slug` (fast lookup on redirects)
  - INDEX: `user` (user's link queries)
  - INDEX: `created_at` (time-range filtering)
- **Soft Deletes**: `deleted_at` column
- **Estimated Size**:
  - 1M links ≈ 100 MB
  - 100M links ≈ 10 GB

---

#### Visitors Table (High-Volume)
- **Current Scale**: Millions to hundreds of millions of records
- **Growth Rate**: Potentially 100M+ clicks per day (enterprise scale)

**Optimization Strategies**:

##### 1. Partitioning
- **Type**: RANGE partitioning by `created_at` (monthly)
- **Structure**:
  ```
  visitors_2025_01 (January 2025)
  visitors_2025_02 (February 2025)
  visitors_2025_03 (March 2025)
  ...
  ```
- **Query Benefits**:
  - Time-range queries only scan relevant partitions
  - Partition pruning: 12x faster for monthly queries
  - Easy archiving: Drop old partitions

**Code Reference**: `2025_12_08_000001_partition_visitors_table.php`

##### 2. Indexing
- **Composite Index**: `(slug, created_at)` - Link analytics queries
- **Index**: `ip_address` - Fraud detection
- **Index**: `country` - Geographic filtering

##### 3. Archiving Strategy
- **Trigger**: Data older than 24 months
- **Process**:
  1. Aggregate into daily summaries per link
  2. Store: total clicks, top countries, top devices
  3. Delete detailed visitor records
  4. Maintain link-level click counts
- **Space Savings**: 99% reduction (estimate)
  - Before: 100M records = 20 GB
  - After: 730 summary records = 200 KB

**Code Reference**: `2025_12_08_000002_create_data_archiving_strategy.php`

##### 4. Write Optimization
- **Batch Inserts**: Buffer visitor records, insert in bulk (planned)
- **Async Processing**: Queue visitor tracking, non-blocking redirects (planned)
- **Connection Pooling**: Reuse database connections (Laravel default)

##### 5. Read Optimization
- **Materialized Views**: Pre-computed analytics dashboards (planned)
- **Redis Caching**:
  - Link slug → URL mapping (redirect cache)
  - Analytics queries (15-minute TTL)
- **Read Replicas**: Dedicated analytics database (planned)

**Code Reference**: `DatabaseOptimizationService` (planned)

---

### Database Sizing Estimates

**Assumptions**:
- 1M active users
- Average 10 links per user
- Average 100 clicks per link per month

**Storage Requirements**:

| Table | Records | Size per Record | Total Size | Notes |
|-------|---------|-----------------|------------|-------|
| **Users** | 1M | 1 KB | 1 GB | Includes OAuth tokens |
| **Links** | 10M | 500 bytes | 5 GB | Includes soft deletes |
| **Visitors** (current) | 100M/month | 500 bytes | 50 GB/month | Before archiving |
| **Visitors** (archived) | 100M/month | 5 bytes | 500 MB/month | After 24 months |

**Annual Growth** (without archiving): 50 GB/month × 12 = 600 GB/year

**Annual Growth** (with archiving): 500 MB/month × 12 = 6 GB/year

**Cost Impact** (AWS RDS example):
- Database storage: $0.10/GB/month
- Without archiving: $60/month (600 GB)
- With archiving: $0.60/month (6 GB)
- **Savings**: $708/year per year of data

---

### Migration Strategy

**Version Control**: All schema changes tracked in migrations

**Deployment Process**:
1. Run migrations in staging environment
2. Test with production-like data volume
3. Backup production database
4. Apply migrations during low-traffic window
5. Monitor performance post-migration
6. Rollback plan: Database restore from backup

**Critical Migrations**:
- `2025_12_08_000000_optimize_database_for_scale.php` - Indexes
- `2025_12_08_000001_partition_visitors_table.php` - Partitioning
- `2025_12_08_000002_create_data_archiving_strategy.php` - Archiving

---

## Future Business Features

### High-Priority Features (Planned)

#### 1. Subscription Management
- **Stripe Integration**: Payment processing
- **Plan Enforcement**: Usage limits and restrictions
- **Billing Dashboard**: Invoices, payment methods
- **Upgrade/Downgrade**: Seamless plan transitions

#### 2. Advanced Analytics Dashboard
- **Real-Time Analytics**: WebSocket-based live click feed
- **Custom Date Ranges**: Flexible time-range filtering
- **Comparative Analytics**: Side-by-side link performance
- **Conversion Tracking**: Goal completion and attribution
- **Export Reports**: CSV/JSON/PDF analytics exports

#### 3. Custom Domains
- **DNS Configuration**: User brings their own domain
- **SSL Certificates**: Automatic Let's Encrypt provisioning
- **Branded Short Links**: `your.brand/campaign`
- **Domain Verification**: TXT record validation

#### 4. Team Collaboration
- **Workspaces**: Shared link libraries
- **Role-Based Access**: Admin, Editor, Viewer roles
- **Team Analytics**: Aggregated team performance
- **Activity Logs**: Audit trail for compliance

#### 5. API Enhancements
- **OpenAPI Documentation**: Interactive API docs
- **Webhooks**: Real-time event notifications
- **SDKs**: JavaScript, Python, PHP client libraries
- **Rate Limit Dashboard**: Self-service quota management

---

### Medium-Priority Features

#### 6. Link Management
- **Bulk Operations**: Multi-select edit/delete
- **Tagging System**: Organize links with tags
- **Link Folders**: Hierarchical organization
- **Search & Filter**: Advanced link search
- **Link Expiration**: Auto-expire after date/click count

#### 7. QR Code Enhancements
- **Customization**: Colors, logos, shapes
- **Download Formats**: PNG, SVG, PDF
- **Analytics**: Track QR code scans separately
- **Dynamic QR Codes**: Update destination without changing QR

#### 8. Security Enhancements
- **Two-Factor Authentication**: TOTP/SMS 2FA
- **Password-Protected Links**: Require password to access
- **IP Whitelisting**: Restrict link access by IP
- **Link Expiration Enforcement**: Automatic cleanup
- **CAPTCHA on Anonymous Links**: Prevent spam

---

### Advanced Features (Future Vision)

#### 9. A/B Testing
- **Split Testing**: Route % of traffic to different URLs
- **Conversion Tracking**: Compare variant performance
- **Auto-Optimization**: AI-driven traffic routing

#### 10. Link Retargeting
- **Pixel Injection**: Facebook/Google Ads pixels
- **Audience Building**: Retargeting campaigns
- **Attribution Tracking**: Multi-touch attribution

#### 11. AI-Powered Insights
- **Predictive Analytics**: Forecast click trends
- **Anomaly Detection**: Unusual traffic alerts
- **Recommendations**: Optimization suggestions
- **Smart Scheduling**: Best time to share links

#### 12. Integrations
- **Zapier Integration**: Connect to 1,000+ apps
- **Slack Notifications**: Team alerts on milestones
- **Google Analytics**: Pass-through UTM parameters
- **CRM Integration**: Salesforce, HubSpot connectors

#### 13. White-Label Solution
- **Custom Branding**: Remove ShortSight logos
- **Custom Domain**: Fully branded platform
- **Reseller Program**: Agencies sell to clients
- **Multi-Tenant Architecture**: Isolated customer data

---

## Business Metrics & KPIs

### Product Metrics

| Metric | Definition | Target | Current |
|--------|------------|--------|---------|
| **Active Links** | Non-deleted links in database | 1M (Year 1) | - |
| **Daily Clicks** | Total redirects per day | 1M (Year 1) | - |
| **Registered Users** | Total user accounts | 100K (Year 1) | - |
| **Monthly Active Users (MAU)** | Users who created/viewed links in last 30 days | 10K (6 months) | - |
| **Links per User** | Average links created per user | 5 | - |
| **Clicks per Link** | Average clicks per link | 50 | - |

---

### Business Metrics

| Metric | Definition | Target | Strategy |
|--------|------------|--------|----------|
| **Free-to-Paid Conversion** | % of free users who upgrade | 2-5% | In-app upgrade prompts, feature limits |
| **Monthly Recurring Revenue (MRR)** | Predictable monthly revenue | $5K (12 months) | Pro subscriptions, add-ons |
| **Average Revenue Per User (ARPU)** | MRR / total paid users | $10/month | Upsell to higher tiers |
| **Churn Rate** | % of paid users who cancel per month | < 5% | Feature improvements, support |
| **Customer Acquisition Cost (CAC)** | Marketing spend / new users | < $50 | SEO, content marketing, referrals |
| **Lifetime Value (LTV)** | ARPU / churn rate | > $200 | Increase retention, upsell |
| **LTV:CAC Ratio** | LTV / CAC | > 3:1 | Optimize marketing efficiency |

---

### Technical Metrics

| Metric | Definition | Target | Monitoring |
|--------|------------|--------|------------|
| **Redirect Latency** | Time from request to redirect (p99) | < 10ms | Application performance monitoring |
| **API Response Time** | Average API response time (p95) | < 100ms | New Relic, Datadog |
| **Uptime** | % of time service is available | 99.9% (43 min/month downtime) | Pingdom, UptimeRobot |
| **Database Query Time** | Average query execution (p95) | < 50ms | Laravel Telescope, slow query log |
| **Error Rate** | % of requests resulting in 5xx errors | < 0.1% | Sentry, error tracking |

---

## Conclusion

ShortSight's business logic is designed to balance **user value, security, scalability, and monetization**. The platform prioritizes:

1. **User Experience**: Fast redirects, intuitive UI, comprehensive analytics
2. **Security**: Multi-layered protection against abuse and malicious content
3. **Scalability**: Database optimizations for massive scale (100M+ clicks/day)
4. **Compliance**: GDPR-ready with data portability and privacy measures
5. **Monetization**: Clear freemium path with premium features

### Next Steps

**Immediate Priorities** (From Improvement Checklist):
1. Complete backend-frontend integration (Vue.js API connection)
2. Implement real-time visitor tracking on redirects
3. Build analytics dashboard
4. Implement Stripe subscription system
5. Add CAPTCHA to prevent abuse

**Long-Term Vision**:
- Become the go-to URL shortener for SMBs and marketers
- Achieve 1M active users and $500K ARR by Year 3
- Expand into enterprise market with team features and custom domains
- Build developer ecosystem with robust API and integrations

---

*This document should be updated quarterly to reflect new features, business model changes, and market conditions.*

**Document Version**: 1.0
**Last Reviewed**: December 9, 2025
**Next Review**: March 9, 2026
