<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('darkMode') || 'system' }" x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))" :class="{'dark': darkMode === 'dark' || (darkMode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }} - Archium</title>

    <style>

    :root {
         --danger-50:254, 242, 242;  --danger-100:254, 226, 226;  --danger-200:254, 202, 202;  --danger-300:252, 165, 165;  --danger-400:248, 113, 113;  --danger-500:239, 68, 68;  --danger-600:220, 38, 38;  --danger-700:185, 28, 28;  --danger-800:153, 27, 27;  --danger-900:127, 29, 29;  --danger-950:69, 10, 10;  --gray-50:250, 250, 250;  --gray-100:244, 244, 245;  --gray-200:228, 228, 231;  --gray-300:212, 212, 216;  --gray-400:161, 161, 170;  --gray-500:113, 113, 122;  --gray-600:82, 82, 91;  --gray-700:63, 63, 70;  --gray-800:39, 39, 42;  --gray-900:24, 24, 27;  --gray-950:9, 9, 11;  --info-50:239, 246, 255;  --info-100:219, 234, 254;  --info-200:191, 219, 254;  --info-300:147, 197, 253;  --info-400:96, 165, 250;  --info-500:59, 130, 246;  --info-600:37, 99, 235;  --info-700:29, 78, 216;  --info-800:30, 64, 175;  --info-900:30, 58, 138;  --info-950:23, 37, 84;  --primary-50:255, 251, 235;  --primary-100:254, 243, 199;  --primary-200:253, 230, 138;  --primary-300:252, 211, 77;  --primary-400:251, 191, 36;  --primary-500:245, 158, 11;  --primary-600:217, 119, 6;  --primary-700:180, 83, 9;  --primary-800:146, 64, 14;  --primary-900:120, 53, 15;  --primary-950:69, 26, 3;  --success-50:240, 253, 244;  --success-100:220, 252, 231;  --success-200:187, 247, 208;  --success-300:134, 239, 172;  --success-400:74, 222, 128;  --success-500:34, 197, 94;  --success-600:22, 163, 74;  --success-700:21, 128, 61;  --success-800:22, 101, 52;  --success-900:20, 83, 45;  --success-950:5, 46, 22;  --warning-50:255, 251, 235;  --warning-100:254, 243, 199;  --warning-200:253, 230, 138;  --warning-300:252, 211, 77;  --warning-400:251, 191, 36;  --warning-500:245, 158, 11;  --warning-600:217, 119, 6;  --warning-700:180, 83, 9;  --warning-800:146, 64, 14;  --warning-900:120, 53, 15;  --warning-950:69, 26, 3;     }
    </style>
    @vite(['resources/css/archium.css', 'resources/js/archium.js'], 'vendor/archium')
</head>
<body class="antialiased bg-gray-50 dark:bg-gray-950">
    <div class="flex flex-col min-h-screen">
        <!-- Header -->
        <header class="sticky top-0 z-40 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800">
            <div class="max-w-7xl mx-auto">
                <div class="flex justify-between items-center py-4 px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center space-x-8">
                        <span class="text-lg font-semibold text-gray-900 dark:text-white">Archium</span>
                        <nav class="flex space-x-6">
                            <a href="{{ route('archium.dashboard') }}"
                               class="px-3 py-2 text-sm font-medium {{ request()->routeIs('archium.dashboard') ? 'text-gray-900 dark:text-white border-b-2 border-primary-500' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
                                Dashboard
                            </a>
                            <a href="{{ route('archium.modules') }}"
                               class="px-3 py-2 text-sm font-medium {{ request()->routeIs('archium.modules') ? 'text-gray-900 dark:text-white border-b-2 border-primary-500' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
                                Modules
                            </a>
                            <a href="#"
                               class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 px-3 py-2 text-sm font-medium">
                                Settings
                            </a>
                        </nav>
                    </div>
                    <div class="flex items-center space-x-4">
                        <!-- Theme Switcher -->
                        <div class="relative" x-data="{ open: false }" x-cloak>
                            <button @click="open = !open" class="flex items-center text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                                <template x-if="darkMode === 'light'">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                </template>
                                <template x-if="darkMode === 'dark'">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                                </template>
                                <template x-if="darkMode === 'system'">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                </template>
                            </button>
                            <div x-show="open" @click.away="open = false" class="origin-top-right absolute right-0 mt-2 w-48 rounded-lg shadow-sm py-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 focus:outline-none" role="menu">
                                <button @click="darkMode = 'light'; open = false" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 flex items-center space-x-2" role="menuitem">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                    <span>Light</span>
                                </button>
                                <button @click="darkMode = 'dark'; open = false" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 flex items-center space-x-2" role="menuitem">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                                    <span>Dark</span>
                                </button>
                                <button @click="darkMode = 'system'; open = false" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 flex items-center space-x-2" role="menuitem">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    <span>System</span>
                                </button>
                            </div>
                        </div>

                        <!-- Global Refresh Button -->
                        <button
                            wire:click="$refresh"
                            class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none"
                        >
                            <svg wire:loading.class="animate-spin" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main content -->
        <main class="flex-1">
            {{ $slot }}
        </main>

        <!-- Notifications -->
        <x-archium::notification />
    </div>
</body>
</html>
