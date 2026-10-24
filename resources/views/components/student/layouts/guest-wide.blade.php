<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('university.name_ar') }}</title>
        <link rel="icon" href="{{ asset(config('university.icon')) }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/student.css', 'resources/js/student.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased" style="font-family: 'Cairo', sans-serif;">
        <div class="min-h-screen flex flex-col items-center py-8" style="background: linear-gradient(160deg, #eef2ff 0%, #f3f4f6 55%, #e8f5ee 100%);">
            <div dir="rtl" style="width: calc(100% - 2rem); max-width: 62rem;">
                <x-student.university-header />
            </div>

            <div class="mt-2" style="width: calc(100% - 2rem); max-width: 62rem;">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
