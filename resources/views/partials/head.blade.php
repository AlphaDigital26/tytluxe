<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

{{-- ══════════════════════════════════════════
     PER-PAGE SEO META (overrideable per page)
══════════════════════════════════════════ --}}
<title>@yield('meta_title', 'TYT Luxe — Luxury Hotels & Cruises | Book Your Dream Vacation')</title>
<meta name="description" content="@yield('meta_description', 'Discover handpicked luxury hotels and premium cruises with TYT Luxe. Personalised travel planning for Indian travellers. 2-hour WhatsApp response guarantee. Call: 98750 73788.')">
<meta name="robots" content="@yield('meta_robots', 'index, follow')">
<meta name="author" content="TYT Luxe">
<meta name="referrer" content="no-referrer-when-downgrade">

{{-- ══════════════════════════════════════════
     CANONICAL URL
══════════════════════════════════════════ --}}
<link rel="canonical" href="@yield('canonical', url()->current())">

{{-- ══════════════════════════════════════════
     OPEN GRAPH (Facebook, WhatsApp, LinkedIn)
══════════════════════════════════════════ --}}
<meta property="og:type"        content="@yield('og_type', 'website')">
<meta property="og:site_name"   content="TYT Luxe">
<meta property="og:locale"      content="en_IN">
<meta property="og:url"         content="@yield('canonical', url()->current())">
<meta property="og:title"       content="@yield('meta_title', 'TYT Luxe — Luxury Hotels & Cruises')">
<meta property="og:description" content="@yield('meta_description', 'Discover handpicked luxury hotels and premium cruises with TYT Luxe. Personalised travel planning for Indian travellers.')">
<meta property="og:image"       content="@yield('og_image', asset('assets/images/og-image.jpg'))">
<meta property="og:image:width"  content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt"   content="TYT Luxe — Luxury Travel">

{{-- ══════════════════════════════════════════
     TWITTER CARD
══════════════════════════════════════════ --}}
<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:site"        content="@tytluxe">
<meta name="twitter:title"       content="@yield('meta_title', 'TYT Luxe — Luxury Hotels & Cruises')">
<meta name="twitter:description" content="@yield('meta_description', 'Discover handpicked luxury hotels and premium cruises with TYT Luxe.')">
<meta name="twitter:image"       content="@yield('og_image', asset('assets/images/og-image.jpg'))">
<meta name="twitter:image:alt"   content="TYT Luxe — Luxury Travel">

{{-- ══════════════════════════════════════════
     THEME & PWA
══════════════════════════════════════════ --}}
<meta name="theme-color" content="#b8935a">
<meta name="application-name" content="TYT Luxe">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="TYT Luxe">

{{-- ══════════════════════════════════════════
     FAVICON & ICONS
══════════════════════════════════════════ --}}
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/images/favicon.png') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/images/favicon.png') }}">
<link rel="apple-touch-icon" href="{{ asset('assets/images/favicon.png') }}">
<link rel="manifest" href="{{ asset('manifest.json') }}">

{{-- ══════════════════════════════════════════
     PERFORMANCE: PRECONNECT & DNS-PREFETCH
══════════════════════════════════════════ --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
<link rel="dns-prefetch" href="https://images.unsplash.com">

{{-- Google Fonts: Outfit (body) + Playfair Display (headings) with display=swap — non-blocking --}}
<link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Playfair+Display:ital,wght@0,700;0,900;1,700&display=swap"
    media="print" onload="this.media='all'">
<noscript>
<link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Playfair+Display:ital,wght@0,700;0,900;1,700&display=swap">
</noscript>

{{-- Font Awesome — loaded non-blocking --}}
<link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    media="print" onload="this.media='all'">
<noscript>
<link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</noscript>

{{-- ══════════════════════════════════════════
     THEME STYLESHEETS
══════════════════════════════════════════ --}}
<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/responsive.css') }}">

@vite(['resources/js/app.js'])

@stack('styles')

{{-- ══════════════════════════════════════════
     STRUCTURED DATA (JSON-LD) — yielded per page
══════════════════════════════════════════ --}}
@yield('schema')

</head>