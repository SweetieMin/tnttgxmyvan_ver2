<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>Đoàn TNTT Gx Mỹ Vân</title>

<link rel="icon" type="image/png" sizes="16x16"
    href="/storage/{{ $site_favicon ?? '/images/sites/FAVICON_default.png' }}" />

<!-- Android Chrome -->
<meta name="mobile-web-app-capable" content="yes">
<meta name="theme-color" content="#0d6efd"> <!-- Màu thanh trạng thái -->

<!-- Apple Safari -->
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="Đoàn TNTT Giáo Xứ Mỹ Vân">

<!-- Biểu tượng cho cả iOS và Android -->
<link rel="apple-touch-icon" href="/storage/{{ $site_favicon ?? '/images/sites/FAVICON_default.png' }}">
<link rel="icon" type="image/png" sizes="192x192" href="/storage/{{ $site_favicon ?? '/images/sites/FAVICON_default.png' }}">
<link rel="apple-touch-icon"
href="/storage/{{ $site_favicon ?? '/images/sites/FAVICON_default.png' }}">



<link
    href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&family=Inter:wght@400;500;600&family=Manrope:wght@400;500;600&display=swap"
    rel="stylesheet">


@vite(['resources/css/app.css', 'resources/js/app.js'])

@fluxAppearance
