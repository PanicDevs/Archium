<div class="bg-gray-50 dark:bg-gray-950">
    <div class="max-w-[90rem] mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Module Installation Report</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Review the installation process and its results.</p>
        </div>

        <!-- Module Information -->
        <div class="mb-8">
            <h2 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-4">Module Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg p-4">
                    <h3 class="font-medium text-gray-900 dark:text-white mb-2">Module Details</h3>
                    <dl class="space-y-1">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Key:</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $report['module']['key'] }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Directory:</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $report['module']['directory'] }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Version:</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $report['module']['version'] }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Branch:</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $report['module']['branch'] }}</dd>
                        </div>
                    </dl>
                </div>

                @foreach ($report['dependencies'] as $key => $dependency)
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg p-4">
                    <h3 class="font-medium text-gray-900 dark:text-white mb-2">{{ ucfirst($key) }} Dependency</h3>
                    <dl class="space-y-1">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Directory:</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $dependency['directory'] }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Version:</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $dependency['version'] }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Status:</dt>
                            <dd class="text-sm font-medium">
                                @if($dependency['fresh_install'])
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400">
                                        Fresh Install
                                    </span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Installation Timeline -->
        <div>
            <h2 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-4">Installation Timeline</h2>
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg p-6">
                <div class="flow-root">
                    <ul role="list" class="-mb-8">
                        @foreach ($report['timeline'] as $index => $event)
                            <li>
                                <div class="relative pb-8">
                                    @if (!$loop->last)
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-800" aria-hidden="true"></span>
                                    @endif
                                    <div class="relative flex space-x-3">
                                        <div>
                                            @switch($event['status'])
                                                @case('completed')
                                                    <span class="h-8 w-8 rounded-full bg-success-500 dark:bg-success-600 flex items-center justify-center ring-8 ring-white dark:ring-gray-900">
                                                        <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                        </svg>
                                                    </span>
                                                    @break
                                                @case('processing')
                                                    <span class="h-8 w-8 rounded-full bg-primary-500 dark:bg-primary-600 flex items-center justify-center ring-8 ring-white dark:ring-gray-900">
                                                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                        </svg>
                                                    </span>
                                                    @break
                                                @case('error')
                                                    <span class="h-8 w-8 rounded-full bg-danger-500 dark:bg-danger-600 flex items-center justify-center ring-8 ring-white dark:ring-gray-900">
                                                        <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                                        </svg>
                                                    </span>
                                                    @break
                                                @default
                                                    <span class="h-8 w-8 rounded-full bg-gray-400 dark:bg-gray-600 flex items-center justify-center ring-8 ring-white dark:ring-gray-900">
                                                        <span class="h-2.5 w-2.5 rounded-full bg-gray-600 dark:bg-gray-400"></span>
                                                    </span>
                                            @endswitch
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                    <span class="font-medium text-gray-900 dark:text-white">{{ str_replace('-', ' ', ucfirst($event['step'])) }}</span>
                                                    @if($event['sub_step'])
                                                        <span class="text-gray-400 dark:text-gray-500">→</span>
                                                        <span class="text-gray-600 dark:text-gray-300">{{ str_replace('-', ' ', $event['sub_step']) }}</span>
                                                    @endif
                                                </p>
                                                @if($event['message'])
                                                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ $event['message'] }}</p>
                                                @endif
                                                @if($event['error'])
                                                    <p class="mt-0.5 text-sm text-danger-500 dark:text-danger-400">{{ $event['error'] }}</p>
                                                @endif
                                            </div>
                                            <div class="text-right text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                <time datetime="{{ $event['timestamp'] }}">{{ $event['relative_time'] }}</time>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>