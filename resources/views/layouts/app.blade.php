<!DOCTYPE html>
{{-- Mantén tu lógica Alpine para dark mode aquí --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{ 
          darkMode: {{ Auth::check() && Auth::user()->dark_mode_enabled !== null ? (Auth::user()->dark_mode_enabled ? 'true' : 'false') : 'localStorage.theme === \'dark\' || (!(\'theme\' in localStorage) && window.matchMedia(\'(prefers-color-scheme: dark)\').matches)' }} 
      }"
      x-init="$watch('darkMode', val => localStorage.setItem('theme', val ? 'dark' : 'light')); 
              if (darkMode) document.documentElement.classList.add('dark'); 
              else document.documentElement.classList.remove('dark');"
      :class="{ 'dark': darkMode }"
>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- Título Dinámico usando @yield --}}
        <title>@hasSection('title') @yield('title') - @endif{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        {{-- Choices.js CSS --}}
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css"/>

        {{-- Favicon Links (Asegúrate de tenerlos si los añadiste) --}}
        {{-- <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon"> --}}

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('scripts') 
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            @include('layouts.navigation') {{-- Incluye tu barra de navegación --}}

            @isset($header)
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                         {{ $header }} {{-- Slot para el H2 del encabezado --}}
                    </div>
                </header>
            @endisset

            <main>
                {{ $slot }} {{-- Slot principal para el contenido --}}
            </main>
        </div>

        {{-- Choices.js JS (antes de cerrar body) --}}
        <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    </body>
</html>
