<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark bg-background">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#0f0f19">
    <meta name="color-scheme" content="dark">
    
    <!-- Favicon -->
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    
    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/favicon.svg">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SOCLY">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    
    <!-- Default Open Graph / Twitter meta -->
    <meta property="og:site_name" content="SOCLY">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ config('app.url') }}">
    <meta property="og:title" content="SOCLY — Sociální síť bez hranic">
    <meta property="og:description" content="Sleduj, sdílej, vydělávej. Originální obsah od tvůrců bez cenzury.">
    <meta property="og:image" content="{{ config('app.url') }}/og-default.jpg">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="SOCLY">
    <meta name="twitter:description" content="Sleduj, sdílej, vydělávej.">
    <meta name="twitter:image" content="{{ config('app.url') }}/og-default.jpg">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
</head>
<body class="font-sans antialiased min-h-dvh">
    @inertia
    
</body>
</html>
