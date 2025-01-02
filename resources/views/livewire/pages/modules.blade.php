<div class="bg-gray-50 dark:bg-gray-950">
    <div class="max-w-[90rem] mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Modules</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Manage your Laravel modules and their settings.</p>
        </div>

        <!-- Module List -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @if($loading)
                <div class="col-span-full flex justify-center items-center py-12">
                    <svg class="animate-spin h-8 w-8 text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            @elseif(count($availableModules) > 0)
                @foreach($availableModules as $module)
                    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <div @class([
                                    'p-2 rounded-lg',
                                    'bg-primary-50 dark:bg-primary-500/10' => $module['required'],
                                    'bg-info-50 dark:bg-info-500/10' => !$module['required']
                                ])>
                                    <svg @class([
                                        'h-5 w-5',
                                        'text-primary-600 dark:text-primary-400' => $module['required'],
                                        'text-info-600 dark:text-info-400' => !$module['required']
                                    ]) xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">{{ $module['name'] }}</h3>
                                    <div class="flex items-center gap-2">
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $module['key'] }}</p>
                                        @if($module['required'])
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-xs font-medium bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                                                Required
                                            </span>
                                        @endif
                                        @if($module['safe'])
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-xs font-medium bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400">
                                                Safe
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-xs font-medium bg-danger-50 text-danger-600 dark:bg-danger-500/10 dark:text-danger-400">
                                                Unsafe
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                @if($module['repository'])
                                    <a href="{{ $module['repository'] }}" 
                                       target="_blank"
                                       rel="noopener noreferrer" 
                                       class="p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 transition-colors duration-200">
                                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                                        </svg>
                                    </a>
                                @endif
                                <div @class([
                                    'text-xs px-2 py-1 rounded-lg',
                                    'bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400' => $module['required'],
                                    'bg-info-50 text-info-600 dark:bg-info-500/10 dark:text-info-400' => !$module['required']
                                ])>
                                    v{{ $module['version'] }}
                                </div>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ $module['description'] }}</p>
                        
                        <div class="border-t border-gray-200 dark:border-gray-800 pt-4 space-y-3">
                            <div>
                                <h4 class="text-xs font-medium text-gray-900 dark:text-white mb-1">Dependencies</h4>
                                <div class="flex flex-wrap gap-2 min-h-[32px]">
                                    @if(count($module['dependencies']) > 0)
                                        @foreach($module['dependencies'] as $dependency)
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">
                                                {{ $dependency }}
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="text-xs text-gray-500 dark:text-gray-400">No dependencies</span>
                                    @endif
                                </div>
                            </div>
                            
                            <div>
                                <h4 class="text-xs font-medium text-gray-900 dark:text-white mb-1">Depends On</h4>
                                <div class="flex flex-wrap gap-2 min-h-[32px]">
                                    @if(count($module['depends']) > 0)
                                        @foreach($module['depends'] as $dependency)
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">
                                                {{ $dependency }}
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="text-xs text-gray-500 dark:text-gray-400">No dependencies</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <a 
                                href="{{ route('archium.modules.install', ['module' => $module['key']]) }}" 
                                class="w-full inline-flex justify-center items-center px-3 py-2 text-sm font-medium text-white rounded-md transition-colors duration-200 bg-primary-600 hover:bg-primary-500 dark:bg-primary-500 dark:hover:bg-primary-400"
                            >
                                Install Module
                            </a>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="col-span-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg p-6">
                    <div class="text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No modules found</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Unable to fetch modules from repository.</p>
                        <div class="mt-6">
                            <button 
                                wire:click="fetchModules"
                                type="button" 
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-500 dark:bg-primary-500 dark:hover:bg-primary-400"
                            >
                                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Retry
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div> 