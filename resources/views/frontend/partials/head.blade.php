@php
    $settings = $settings ?? [];
    $church = $church ?? null;

    $siteName = optional($church)->centre_name
        ?: ($settings['site.name'] ?? 'PARISH');

    $tagline = $settings['site.tagline'] ?? 'Contemporary Worship Community';

    $fullTitle = trim($__env->yieldContent('title'))
        ?: $siteName . ' | ' . $tagline;

    $favicon = $settings['site.favicon'] ?? 'uploads/frontend/logo/logo.jpeg';

    $metaDescription = trim($__env->yieldContent('meta_description'))
        ?: ($settings['site.description']
        ?? 'A welcoming spiritual home for worship, fellowship, discipleship, and service.');

    $metaImage = $settings['site.og_image'] ?? $favicon;
@endphp

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">

<!-- TITLE -->
<title>{{ $fullTitle }}</title>

<!-- SEO -->
<meta name="description" content="{{ $metaDescription }}">
<meta name="theme-color" content="#4f46e5">

<!-- FAVICON -->
<link rel="icon" type="image/png" href="{{ asset(ltrim($favicon, '/')) }}">
<link rel="apple-touch-icon" href="{{ asset(ltrim($favicon, '/')) }}">

<!-- OPEN GRAPH (Facebook / WhatsApp) -->
<meta property="og:type" content="website">
<meta property="og:title" content="{{ $fullTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:image" content="{{ asset(ltrim($metaImage, '/')) }}">
<meta property="og:url" content="{{ url()->current() }}">

<!-- TWITTER -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $fullTitle }}">
<meta name="twitter:description" content="{{ $metaDescription }}">
<meta name="twitter:image" content="{{ asset(ltrim($metaImage, '/')) }}">

<!-- FONTS -->
<link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800;900&display=swap" rel="stylesheet">

<!-- ICONS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<!-- SWIPER -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

<!-- APP CSS -->
<link rel="stylesheet" href="{{ asset('frontend-assets/css/app.css') }}">

@stack('styles')