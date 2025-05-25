<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
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
            darkMode: false,
        }
    </script>
    @filamentStyles
    {{-- <style>
        [x-cloak] { display: none !important; }
        .fi-input-label {
            color: #000000 !important; /* gray-700 */
            font-weight: 500 !important;
            margin-bottom: 0.5rem !important;
        }
        .fi-input {
            background-color: white !important;
            color: #111827 !important; /* gray-900 */
            border-color: #d1d5db !important; /* gray-300 */
            border-radius: 0.375rem !important; /* rounded-md */
            padding: 0.5rem 0.75rem !important;
        }
        .fi-input:focus {
            border-color: #3b82f6 !important; /* blue-500 */
            ring-color: #3b82f6 !important;
        }
        .fi-input-error {
            color: #dc2626 !important; /* red-600 */
            font-size: 0.875rem !important;
            margin-top: 0.25rem !important;
        }
          .dark .fi-input, .dark .bg-white {
            background-color: white !important;
            color: #111827 !important;
        }
    </style> --}}
</head>
<body class="font-sans antialiased bg-gray-100">
    {{ $slot }}
    
    <script>
        window.addEventListener('confirmSubmission', event => {
            if (confirm(event.detail.message)) {
                Livewire.emit(event.detail.callback);
            }
        });
    </script>
    @filamentScripts
</body>
</html>