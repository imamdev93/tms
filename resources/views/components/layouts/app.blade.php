<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Registrasi</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
     <script>
        tailwind.config = {
            darkMode: 'class', // Ubah ke 'class' untuk kontrol manual
        }
    </script>
    <style>
        :root {
            --primary-50: 236 253 245;
            --primary-100: 209 250 229;
            /* tambahkan warna lain sesuai kebutuhan */
        }
        .fi-form {
            color-scheme: light only !important;
        }
        html, body {
            color-scheme: light !important;
        }
    </style>
    @filamentStyles
</head>
<body class="font-sans antialiased bg-gray-100">
    {{ $slot }}
    <script>
        document.documentElement.classList.remove('dark');
        document.documentElement.classList.add('light');
    </script>
    @filamentScripts
</body>
</html>