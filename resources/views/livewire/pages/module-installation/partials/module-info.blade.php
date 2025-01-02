<!-- Module Info -->
<div class="lg:sticky lg:top-24 space-y-4 max-h-[calc(100vh-8rem)] overflow-y-auto">
    @if($moduleData)
        <div class="bg-white dark:bg-gray-900 shadow-sm rounded-lg p-4">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Module Information</h2>
            <dl class="space-y-3">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $moduleData['name'] }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Key</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $moduleData['key'] }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Version</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $moduleData['version'] }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                    <dd class="mt-1">
                        <div class="flex items-center gap-2">
                            @if($moduleData['required'])
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-xs font-medium bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                                    Required
                                </span>
                            @endif
                            @if($moduleData['safe'])
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-xs font-medium bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400">
                                    Safe
                                </span>
                            @else
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-xs font-medium bg-danger-50 text-danger-600 dark:bg-danger-500/10 dark:text-danger-400">
                                    Unsafe
                                </span>
                            @endif
                        </div>
                    </dd>
                </div>
                @if($moduleData['repository'])
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Repository</dt>
                        <dd class="mt-1">
                            <a href="{{ $moduleData['repository'] }}" 
                               target="_blank"
                               rel="noopener noreferrer" 
                               class="inline-flex items-center text-sm text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300">
                                <svg class="w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                                </svg>
                                View Repository
                            </a>
                        </dd>
                    </div>
                @endif
            </dl>
        </div>

        @if(is_array($moduleData['dependencies']) && count(array_filter($moduleData['dependencies'], 'is_string')) > 0)
            <div class="bg-white dark:bg-gray-900 shadow-sm rounded-lg p-4">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Dependencies</h2>
                <ul class="space-y-2">
                    @foreach($moduleData['dependencies'] as $key => $dependency)
                        @if(is_string($dependency))
                        <li class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ $dependency }}</span>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">
                                @if(isset($moduleData['dependencies'][$dependency]['update_available']))
                                    Update Available
                                @elseif(isset($moduleData['dependencies'][$dependency]['needs_fresh_install']))
                                    Fresh Install
                                @elseif(isset($moduleData['dependencies'][$dependency]['skip_installation']))
                                    Skip Update
                                @else
                                    Pending
                                @endif
                            </span>
                        </li>
                        @endif
                    @endforeach
                </ul>
            </div>
        @endif

        @if(count($moduleData['depends']) > 0)
            <div class="bg-white dark:bg-gray-900 shadow-sm rounded-lg p-4">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Depends On</h2>
                <ul class="space-y-2">
                    @foreach($moduleData['depends'] as $dependency)
                        <li class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ $dependency }}</span>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">
                                Required
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    @endif
</div> 