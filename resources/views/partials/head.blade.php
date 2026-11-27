{{-- Shared <head> tags: meta, favicons, font and the Vite bundle. --}}
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#ffffff">
<link rel="icon" href="{{ asset('favicon.ico') }}?v=2" sizes="any">
<link rel="icon" href="{{ asset('favicon.png') }}?v=2" type="image/png">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}?v=2">
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
@vite(['resources/css/app.css', 'resources/js/app.js'])
