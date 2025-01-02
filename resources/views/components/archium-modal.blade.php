@props([
    'name',
    'title' => '',
    'maxWidth' => '2xl'
])

@php
$maxWidth = [
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
][$maxWidth];
@endphp

<div
    x-data="{ show: false }"
    x-on:open-modal.window="$event.detail === '{{ $name }}' ? show = true : null"
    x-on:close-modal.window="show = false"
    x-on:keydown.escape.window="show = false"
    x-show="show"
    class="fixed inset-0 z-50 overflow-y-auto"
    style="display: none;"
>
    <!-- Fixed backdrop with instant blur -->
    <div class="fixed inset-0 backdrop-blur-sm" aria-hidden="true"></div>

    <div class="min-h-screen flex items-center justify-center p-4">
        <!-- Animated overlay -->
        <div
            x-show="show"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-950/50 dark:bg-gray-950/75 transition-opacity"
            @click="show = false"
            aria-hidden="true"
        ></div>

        <!-- Modal panel -->
        <div
            x-show="show"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative w-full cursor-default bg-white dark:bg-gray-900 shadow-xl ring-1 ring-gray-950/5 dark:ring-white/10 rounded transform transition-all sm:w-full {{ $maxWidth }} sm:mx-auto"
        >
            @if ($title)
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        {{ $title }}
                    </h3>
                </div>
            @endif

            <div class="px-6 py-4">
                {{ $slot }}
            </div>

            @if (isset($footer))
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div> 