<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ isset($title) && filled($title) ? trim($title . ' - ' . $siteTitle) : $siteTitle }}</title>

<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('storage/' . ltrim($siteFavicon ?: 'images/sites/FAVICON_default.png', '/')) }}" />
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('storage/' . ltrim($siteFavicon ?: 'images/sites/FAVICON_default.png', '/')) }}" />
<link rel="apple-touch-icon" href="{{ asset('storage/' . ltrim($siteFavicon ?: 'images/sites/FAVICON_default.png', '/')) }}" />

<meta name="mobile-web-app-capable" content="yes" />
<meta name="theme-color" content="#0ea5e9" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="default" />
<meta name="apple-mobile-web-app-title" content="{{ $siteTitle }}" />

<link
    href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&family=Inter:wght@400;500;600&family=Manrope:wght@400;500;600&display=swap"
    rel="stylesheet"
>

@filamentStyles
@vite(['resources/css/app.css', 'resources/css/filament.css', 'resources/js/app.js'])

<meta name="description" content="{{ $siteMetaDescription ?: $siteTagline ?: $siteTitle }}" />
<meta name="keywords" content="{{ $siteMetaKeywords }}" />
<meta name="author" content="{{ $siteTitle }}" />
<meta name="robots" content="index, nofollow" />
<link rel="canonical" href="{{ url()->current() }}" />

<meta property="og:title" content="{{ isset($title) && filled($title) ? trim($title . ' - ' . $siteTitle) : $siteTitle }}" />
<meta property="og:description" content="{{ $siteMetaDescription ?: $siteTagline ?: $siteTitle }}" />
<meta property="og:type" content="website" />
<meta property="og:url" content="{{ url()->current() }}" />
<meta property="og:site_name" content="{{ $siteTitle }}" />
<meta property="og:locale" content="vi_VN" />
<meta property="og:image" content="{{ asset('storage/' . ltrim($siteLogo ?: 'images/sites/LOGO_default.png', '/')) }}" />
<meta property="og:image:alt" content="{{ $siteTitle }}" />

<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="{{ isset($title) && filled($title) ? trim($title . ' - ' . $siteTitle) : $siteTitle }}" />
<meta name="twitter:description" content="{{ $siteMetaDescription ?: $siteTagline ?: $siteTitle }}" />
<meta name="twitter:image" content="{{ asset('storage/' . ltrim($siteLogo ?: 'images/sites/LOGO_default.png', '/')) }}" />
<meta name="twitter:image:alt" content="{{ $siteTitle }}" />

<script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => $siteTitle,
        'description' => $siteMetaDescription ?: $siteTagline ?: $siteTitle,
        'url' => url('/'),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>

<script type="application/ld+json">
    {!! json_encode(array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => $siteTitle,
        'url' => url('/'),
        'logo' => asset('storage/' . ltrim($siteLogo ?: 'images/sites/LOGO_default.png', '/')),
        'sameAs' => array_values(array_filter([
            $siteFacebookUrl,
            $siteInstagramUrl,
            $siteYoutubeUrl,
            $siteTikTokUrl,
        ])),
    ], fn (mixed $value): bool => filled($value)), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>

@fluxAppearance
