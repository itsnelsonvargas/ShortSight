<?php

return [
    /*
    |--------------------------------------------------------------------------
    | URL Safety Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for URL validation, blacklists, and security checks
    |
    */

    'domain_blacklist' => [
        // Known malicious domains and patterns
        'malware.com',
        'virus.net',
        'phishing.org',
        'spamlink.info',
        'suspicious-site.biz',
        'test-malware-site.com',
        'fake-bank-login.net',
        // Common phishing domains
        'secure-login.net',
        'account-verification.com',
        'paypal-secure-login.com',
        'bankofamerica-login.net',
        'chase-online-login.com',
        'wellsfargo-secure.net',
        // Malware distribution sites
        'download-free-software.net',
        'crack-software.com',
        'pirated-software.org',
        'keygen-site.com',
        'warez-download.net',
        // Suspicious shorteners (prevent double-shortening)
        'bit.ly',
        'tinyurl.com',
        'goo.gl',
        't.co',
        'ow.ly',
        'buff.ly',
        'adf.ly',
        'is.gd',
        'v.gd',
        'shorte.st',
        // Gambling and adult content (optional blocking)
        'casino-online.net',
        'adult-content-site.com',
        // Add more domains as needed
    ],

    'content_type_blacklist' => [
        // Executable files
        'application/x-msdownload',          // .exe files
        'application/x-executable',
        'application/x-dosexec',
        'application/x-msdos-program',
        'application/octet-stream',          // Generic binary (suspicious)
        // Archive files (could contain malware)
        'application/zip',
        'application/x-rar-compressed',
        'application/x-7z-compressed',
        'application/x-tar',
        'application/gzip',
        'application/x-bzip2',
        'application/x-lzip',
        'application/x-lzma',
        'application/x-xz',
        // Installer packages
        'application/x-msi',                 // Windows installer
        'application/x-deb',                 // Debian package
        'application/x-rpm',                 // RPM package
        'application/vnd.android.package-archive', // APK files
        // Script files
        'application/x-javascript',
        'application/javascript',
        'text/javascript',
        'application/x-perl',
        'application/x-python-code',
        'application/x-ruby',
        'application/x-shellscript',
        // Outdated/web vulnerable formats
        'application/x-shockwave-flash',     // Flash files
        'application/vnd.ms-powerpoint',     // PowerPoint (old versions vulnerable)
        'application/vnd.ms-excel',          // Excel (old versions vulnerable)
        'application/msword',                // Word (old versions vulnerable)
        // Other potentially dangerous types
        'application/x-java-archive',        // JAR files
        'application/java-archive',
        'application/x-sharedlib',           // Shared libraries
        'application/x-mach-binary',         // Mach-O binaries (macOS)
    ],

    'malicious_patterns' => [
        // Common phishing patterns
        '/\b(?:login|signin|secure|account|verify|confirm|update|auth|portal)\b.*(?:paypal|ebay|amazon|bank|google|facebook|twitter|instagram|apple|microsoft|netflix|spotify)\b/i',
        // Suspicious query parameters that could lead to XSS or code injection
        '/(?:redirect|url|return|next|continue|callback|referer)=.*(?:javascript|data|vbscript|onload|onerror|eval|alert):/i',
        // IP addresses in URLs (often malicious or suspicious)
        '/https?:\/\/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}(?::\d+)?(?:\/|$)/',
        // Common malware and suspicious domain patterns
        '/(?:malware|virus|trojan|ransomware|spyware|keylogger|backdoor|rootkit|exploit)\./i',
        // High-risk TLDs often associated with spam/malware
        '/\.(?:xyz|tk|ml|ga|cf|gq|work|click|link|top|win|bid|download|review|party|pro|club|online|site|space|website|tech|store|live|fun|host|icu|monster|buzz|digital|network|systems|email|solutions|services|agency|company|center|world|zone)\b/i',
        // URL shortening services (to prevent nested shortening and abuse)
        '/(?:bit\.ly|tinyurl\.com|goo\.gl|t\.co|ow\.ly|buff\.ly|adf\.ly|is\.gd|v\.gd|shorte\.st|tiny\.cc|cli\.gs|qr\.ae|1url\.com|tiny\.pl|prettylinkpro\.com|shrink\.me|short\.ie|short\.to|url\.ie|to\.ly|lnkd\.in|db\.tt|wp\.me|ift\.tt|tiny\.ly|tr\.im|su\.pr|ht\.ly|fb\.me|twitthis\.com|u\.to|j\.mp|buzurl\.com|cutt\.us|u\.bb|yourls\.org|polr\.me|urlshortener\.site)\//i',
        // Suspicious keywords in domain names
        '/(?:free|crack|hack|porn|sex|casino|gambling|drugs|pharmacy|viagra|cialis|lottery|winner|prize|millionaire|inheritance|scam|fraud)\./i',
        // Unicode/domain homograph attacks (visually similar characters)
        '/[а-яё]/i', // Cyrillic characters that look like Latin
        '/[α-ω]/i',  // Greek characters
        // Base64 encoded suspicious strings (common in malware)
        '/(?:data:text\/html;base64,|javascript:.*base64,)/i',
        // SQL injection attempts in URLs
        '/(?:union.*select|script.*alert|onload.*alert|onerror.*alert)/i',
        // Command injection patterns
        '/(?:\$\{.*\}|%7B.*%7D|eval\(|exec\(|system\()/i',
        // Social engineering patterns
        '/(?:urgent|immediate|action.required|account.suspended|security.alert|verify.your.account)/i',
    ],

    'validation_cache_ttl' => env('URL_VALIDATION_CACHE_TTL', 86400), // 24 hours

    'content_check_timeout' => env('URL_CONTENT_CHECK_TIMEOUT', 5), // 5 seconds timeout for content checks

    // Feature toggles
    'enable_content_type_check' => env('ENABLE_CONTENT_TYPE_CHECK', true),
    'enable_pattern_detection' => env('ENABLE_PATTERN_DETECTION', true),
    'enable_domain_blacklist' => env('ENABLE_DOMAIN_BLACKLIST', true),
    'enable_google_safe_browsing' => env('ENABLE_GOOGLE_SAFE_BROWSING', true),
    'enable_url_length_check' => env('ENABLE_URL_LENGTH_CHECK', true),
    'enable_unicode_check' => env('ENABLE_UNICODE_CHECK', true),

    // URL length limits
    'max_url_length' => env('MAX_URL_LENGTH', 2048), // Maximum URL length to prevent buffer overflow attacks
    'max_domain_length' => env('MAX_DOMAIN_LENGTH', 253), // Maximum domain length per RFC

    // Content type check settings
    'allow_http_content_check' => env('ALLOW_HTTP_CONTENT_CHECK', false), // Only check HTTPS by default for security

    // Additional security settings
    'block_private_ips' => env('BLOCK_PRIVATE_IPS', true), // Block links to private IP ranges
    'block_localhost' => env('BLOCK_LOCALHOST', true), // Block localhost and local network access

    'private_ip_ranges' => [
        '127.0.0.0/8',      // localhost
        '10.0.0.0/8',       // private network
        '172.16.0.0/12',    // private network
        '192.168.0.0/16',   // private network
        '169.254.0.0/16',   // link-local
        'fc00::/7',         // unique local address (IPv6)
        'fe80::/10',        // link-local (IPv6)
    ],

    // Warning thresholds (non-blocking but logged)
    'warn_suspicious_tld' => env('WARN_SUSPICIOUS_TLD', true),
    'warn_long_url' => env('WARN_LONG_URL', true),
    'warn_unicode_domain' => env('WARN_UNICODE_DOMAIN', true),
];
