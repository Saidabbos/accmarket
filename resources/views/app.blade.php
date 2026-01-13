<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- SEO Meta Tags -->
        <meta name="description" content="Accssio - безопасная торговая площадка для покупки и продажи цифровых товаров. Мгновенная доставка, защищённые платежи.">
        <meta name="keywords" content="digital marketplace, цифровые товары, аккаунты, купить аккаунт, accssio">
        <meta name="author" content="Accssio">
        <meta name="robots" content="index, follow">
        <link rel="canonical" href="{{ url()->current() }}">

        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:title" content="{{ config('app.name', 'Accssio') }} - Digital Marketplace">
        <meta property="og:description" content="Безопасная торговая площадка для покупки и продажи цифровых товаров. Мгновенная доставка, защищённые платежи.">
        <meta property="og:image" content="{{ asset('images/og-image.png') }}">
        <meta property="og:site_name" content="Accssio">
        <meta property="og:locale" content="ru_RU">

        <!-- Twitter -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:url" content="{{ url()->current() }}">
        <meta name="twitter:title" content="{{ config('app.name', 'Accssio') }} - Digital Marketplace">
        <meta name="twitter:description" content="Безопасная торговая площадка для покупки и продажи цифровых товаров.">
        <meta name="twitter:image" content="{{ asset('images/og-image.png') }}">

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
