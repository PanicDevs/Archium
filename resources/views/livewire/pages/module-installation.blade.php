<!-- Main Module Installation Page -->
<div class="min-h-screen bg-gray-50 dark:bg-gray-950">
    <div class="max-w-[90rem] mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('archium::livewire.pages.module-installation.partials.header')
        @include('archium::livewire.pages.module-installation.partials.warning-banner')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                @include('archium::livewire.pages.module-installation.partials.steps-timeline')

                @if(!$reportReady)
                    <div class="mt-6 bg-white dark:bg-gray-900 shadow-sm rounded-lg p-6">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Installation Report Available</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    View the detailed installation report including module information, dependencies, and timeline.
                                </p>
                            </div>
                            <a href="{{ route('archium.modules.installation.report') }}" 
                               target="_blank"
                               class="inline-flex min-w-max items-center px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-500 dark:bg-primary-500 dark:hover:bg-primary-400 rounded transition-colors duration-200">
                                <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                View Report
                            </a>
                        </div>
                    </div>
                @endif
            </div>
            <div class="lg:h-full">
                @include('archium::livewire.pages.module-installation.partials.module-info')
            </div>
        </div>
    </div>
</div> 