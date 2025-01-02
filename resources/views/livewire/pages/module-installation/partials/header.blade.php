<!-- Header -->
<div class="mb-8">
    <div class="flex items-center gap-2">
        <a href="{{ route('archium.modules') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Install Module: {{ $moduleData['name'] ?? $moduleKey }}</h1>
    </div>
    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Follow the installation process step by step.</p>
</div> 