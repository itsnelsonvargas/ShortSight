<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Primary Meta Tags -->
    <title>{{ config('app.site_name') }} - Free URL Shortener with Advanced Analytics | Shorten Links Fast</title>
    <meta name="title" content="{{ config('app.site_name') }} - Free URL Shortener with Advanced Analytics | Shorten Links Fast">
    <meta name="description" content="Create short, branded links with comprehensive click tracking, geolocation analytics, and custom domains. Free URL shortener with enterprise features - QR codes, password protection, and detailed analytics.">
    <meta name="keywords" content="URL shortener, link shortener, bitly alternative, custom domains, link analytics, QR codes, click tracking, geolocation, free URL shortener, branded links">
    <meta name="author" content="{{ config('app.site_name') }}">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <meta name="language" content="English">
    <meta name="revisit-after" content="7 days">
    <meta name="theme-color" content="#2563eb">

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url('/') }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:title" content="{{ config('app.site_name') }} - Free URL Shortener with Advanced Analytics">
    <meta property="og:description" content="Create short, branded links with comprehensive click tracking, geolocation analytics, and custom domains. Free URL shortener with enterprise features.">
    <meta property="og:site_name" content="{{ config('app.site_name') }}">
    <meta property="og:locale" content="en_US">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary">
    <meta property="twitter:url" content="{{ url('/') }}">
    <meta property="twitter:title" content="{{ config('app.site_name') }} - Free URL Shortener with Advanced Analytics">
    <meta property="twitter:description" content="Create short, branded links with comprehensive click tracking, geolocation analytics, and custom domains. Free URL shortener with enterprise features.">
    <meta property="twitter:creator" content="@{{ strtolower(config('app.site_name')) }}">
    <meta property="twitter:site" content="@{{ strtolower(config('app.site_name')) }}">

    <!-- Favicon and Icons -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="manifest" href="{{ url('site.webmanifest') }}">

    <!-- DNS Prefetch for Performance -->
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Structured Data (JSON-LD) -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebApplication",
        "name": "{{ config('app.site_name') }}",
        "description": "Free URL shortener with advanced analytics, custom domains, and QR code generation",
        "url": "{{ url('/') }}",
        "applicationCategory": "WebApplication",
        "operatingSystem": "Web Browser",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD"
        },
        "featureList": [
            "URL shortening",
            "Click tracking analytics",
            "Geolocation tracking",
            "Custom domains",
            "QR code generation",
            "Password protection",
            "Link expiration",
            "API access"
        ],
        "publisher": {
            "@type": "Organization",
            "name": "{{ config('app.site_name') }}",
            "url": "{{ url('/') }}"
        },
        "potentialAction": {
            "@type": "UseAction",
            "target": "{{ url('/') }}",
            "description": "Shorten your URLs with advanced analytics"
        }
    }
    </script>

    <!-- Security Headers -->
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    <meta http-equiv="Referrer-Policy" content="strict-origin-when-cross-origin">

    <!-- Performance - Add when fonts are available -->
    <!-- <link rel="preload" href="{{ asset('fonts/inter-var.woff2') }}" as="font" type="font/woff2" crossorigin> -->

    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
    <div id="app"></div>
</body>
</html>
