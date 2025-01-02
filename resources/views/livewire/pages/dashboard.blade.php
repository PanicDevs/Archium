<div class="bg-gray-50 dark:bg-gray-950">
    <div class="max-w-[90rem] mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Quick Actions -->
        @if(!$modulePackageInstalled || !$configPublished)
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-medium text-gray-600 dark:text-gray-400">Quick Actions</h2>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @if (!$modulePackageInstalled)
                        <button
                            wire:click="$dispatch('open-modal', 'confirm-install')"
                            class="group relative flex items-center px-4 py-3 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg text-sm hover:border-primary-500 dark:hover:border-primary-500 transition-colors"
                        >
                            <div class="flex items-center space-x-3">
                                <div class="p-2 bg-danger-50 dark:bg-danger-500/10 rounded-lg">
                                    <svg class="h-5 w-5 text-danger-600 dark:text-danger-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                </div>
                                <div class="flex flex-col items-start">
                                    <span class="font-medium text-gray-900 dark:text-white">Install Laravel Modules</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Required for module management</span>
                                </div>
                            </div>
                            <svg class="ml-auto h-5 w-5 text-gray-400 dark:text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    @endif
                    @if (!$configPublished)
                        <button
                            wire:click="$dispatch('open-modal', 'confirm-publish')"
                            class="group relative flex items-center px-4 py-3 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg text-sm hover:border-primary-500 dark:hover:border-primary-500 transition-colors"
                        >
                            <div class="flex items-center space-x-3">
                                <div class="p-2 bg-warning-50 dark:bg-warning-500/10 rounded-lg">
                                    <svg wire:loading.remove class="h-5 w-5 text-warning-600 dark:text-warning-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <div class="flex flex-col items-start">
                                    <span class="font-medium text-gray-900 dark:text-white">Publish Config</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Configure module settings</span>
                                </div>
                            </div>
                            <svg class="ml-auto h-5 w-5 text-gray-400 dark:text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    @endif
                </div>
            </div>
        @endif

        <!-- Available Updates -->
        @if($updateAvailable)
            <div class="mb-6">
                <h2 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-4">Available Updates</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Config Update -->
                    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg">
                        <div class="p-4">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center space-x-3">
                                    <div class="p-2 rounded-lg {{ $this->updateType['icon_bg'] }}">
                                        <svg class="h-5 w-5 {{ $this->updateType['icon_class'] }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">Config Update</span>
                                </div>
                                <div class="text-xs px-2 py-1 rounded-lg {{ $this->updateType['class'] }}">
                                    {{ $this->updateType['type'] }}
                                </div>
                            </div>
                            <div class="flex items-baseline space-x-2">
                                <div class="text-2xl font-semibold text-gray-900 dark:text-white">v{{ $latestVersion }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">from v{{ $currentVersion }}</div>
                            </div>
                            <div class="mt-3">
                                <button 
                                    wire:click="$dispatch('open-modal', 'confirm-update-configuration-file')" 
                                    class="w-full inline-flex justify-center items-center px-3 py-2 text-sm font-medium text-white rounded-md transition-colors duration-200 {{ $this->updateType['button_class'] }}"
                                >
                                    Update Configuration
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- System Status -->
        <div class="mb-12">
            <h2 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-4">System Status</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Package Status -->
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg">
                    <div class="p-4">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center space-x-3">
                                <div class="p-2 bg-info-50 dark:bg-info-500/10 rounded-lg">
                                    <svg class="h-5 w-5 text-info-600 dark:text-info-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">Laravel Modules</span>
                            </div>
                            <div @class([
                                'text-xs px-2 py-1 rounded-lg',
                                'bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400' => $modulePackageInstalled,
                                'bg-danger-50 text-danger-600 dark:bg-danger-500/10 dark:text-danger-400' => !$modulePackageInstalled,
                            ])>
                                {{ $modulePackageInstalled ? 'Installed' : 'Not Installed' }}
                            </div>
                        </div>
                        <div class="flex items-baseline space-x-2">
                            <div class="text-2xl font-semibold text-gray-900 dark:text-white">
                                {{ $modulePackageInstalled ? 'Ready' : 'Required' }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $modulePackageInstalled ? 'Package is installed' : 'Action needed' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Config Status -->
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg">
                    <div class="p-4">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center space-x-3">
                                <div class="p-2 bg-info-50 dark:bg-info-500/10 rounded-lg">
                                    <svg class="h-5 w-5 text-info-600 dark:text-info-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">Configuration</span>
                            </div>
                            <div @class([
                                'text-xs px-2 py-1 rounded-lg',
                                'bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400' => $configPublished,
                                'bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-400' => !$configPublished,
                            ])>
                                {{ $configPublished ? 'Published' : 'Not Published' }}
                            </div>
                        </div>
                        <div class="flex items-baseline space-x-2">
                            <div class="text-2xl font-semibold text-gray-900 dark:text-white">
                                {{ $configPublished ? 'Ready' : 'Setup' }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $configPublished ? 'Config is published' : 'Needs publishing' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Modules -->
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg">
                    <div class="p-4">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center space-x-3">
                                <div class="p-2 bg-info-50 dark:bg-info-500/10 rounded-lg">
                                    <svg class="h-5 w-5 text-info-600 dark:text-info-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">Total Modules</span>
                            </div>
                        </div>
                        <div class="flex items-baseline space-x-2">
                            <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $totalModules }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">modules</div>
                        </div>
                    </div>
                </div>

                <!-- Active Modules -->
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg">
                    <div class="p-4">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center space-x-3">
                                <div class="p-2 bg-info-50 dark:bg-info-500/10 rounded-lg">
                                    <svg class="h-5 w-5 text-info-600 dark:text-info-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">Active Modules</span>
                            </div>
                        </div>
                        <div class="flex items-baseline space-x-2">
                            <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $activeModules }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">/ {{ $totalModules }} active</div>
                        </div>
                        <div class="mt-3">
                            <div class="relative h-1.5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                                <div class="absolute top-0 left-0 h-full bg-info-500 dark:bg-info-400 transition-all duration-500"
                                    style="width: {{ $totalModules > 0 ? ($activeModules / $totalModules) * 100 : 0 }}%">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Module List -->
        <div>
            <h2 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-4">Installed Modules</h2>
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg">
                <div class="p-4 text-sm text-gray-500 dark:text-gray-400">
                    Module list will be displayed here
                </div>
            </div>
        </div>
    </div>

    <!-- Install Confirmation Modal -->
    <x-archium::archium-modal name="confirm-install" :title="__('Install Laravel Modules')">
        <div class="space-y-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                This will install the <code class="px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800 font-mono text-xs">nwidart/laravel-modules</code> package using Composer. Are you sure you want to proceed?
            </p>

            <div class="bg-warning-50 dark:bg-warning-500/10 rounded p-4">
                <div class="flex">
                    <div class="shrink-0">
                        <svg class="h-5 w-5 text-warning-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-warning-800 dark:text-warning-200">
                            Before proceeding
                        </h3>
                        <div class="mt-2 text-sm text-warning-700 dark:text-warning-300">
                            <p>Make sure you have:</p>
                            <ul class="list-disc list-inside mt-1 space-y-1">
                                <li>Committed your current changes</li>
                                <li>Backed up your composer.json</li>
                                <li>A stable internet connection</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <x-slot name="footer">
            <div class="flex justify-end space-x-3">
                <button
                    type="button"
                    x-on:click="$dispatch('close-modal')"
                    class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:text-gray-800 dark:hover:text-white"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    wire:click="installLaravelModules"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-500 dark:bg-primary-500 dark:hover:bg-primary-400 rounded disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <svg wire:loading class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span wire:loading.remove>Install Package</span>
                    <span wire:loading>Installing...</span>
                </button>
            </div>
        </x-slot>
    </x-archium::archium-modal>

    <!-- Publish Config Confirmation Modal -->
    <x-archium::archium-modal name="confirm-publish" :title="__('Publish Configuration')">
        <div class="space-y-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                This will download and publish the latest configuration file from our repository. Are you sure you want to proceed?
            </p>

            <div class="bg-warning-50 dark:bg-warning-500/10 rounded p-4">
                <div class="flex">
                    <div class="shrink-0">
                        <svg class="h-5 w-5 text-warning-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-warning-800 dark:text-warning-200">
                            Before proceeding
                        </h3>
                        <div class="mt-2 text-sm text-warning-700 dark:text-warning-300">
                            <p>Make sure you have:</p>
                            <ul class="list-disc list-inside mt-1 space-y-1">
                                <li>Backed up any existing config file</li>
                                <li>A stable internet connection</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <x-slot name="footer">
            <div class="flex justify-end space-x-3">
                <button
                    type="button"
                    x-on:click="$dispatch('close-modal')"
                    class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:text-gray-800 dark:hover:text-white"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    wire:click="publishConfig"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-500 dark:bg-primary-500 dark:hover:bg-primary-400 rounded disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <svg wire:loading class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span wire:loading.remove>Publish Config</span>
                    <span wire:loading>Publishing...</span>
                </button>
            </div>
        </x-slot>
    </x-archium::archium-modal>

    <!-- Update Config Confirmation Modal -->
    <x-archium::archium-modal name="confirm-update-configuration-file" :title="__('Update Configuration')">
        <div class="space-y-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Are you sure you want to update the configuration from version {{ $currentVersion }} to {{ $latestVersion }}? This action will overwrite your current configuration file.
            </p>

            <div class="bg-warning-50 dark:bg-warning-500/10 rounded p-4">
                <div class="flex">
                    <div class="shrink-0">
                        <svg class="h-5 w-5 text-warning-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-warning-800 dark:text-warning-200">
                            Before proceeding
                        </h3>
                        <div class="mt-2 text-sm text-warning-700 dark:text-warning-300">
                            <p>Make sure you have:</p>
                            <ul class="list-disc list-inside mt-1 space-y-1">
                                <li>Committed your current changes</li>
                                <li>Backed up your current config file</li>
                                <li>A stable internet connection</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <x-slot name="footer">
            <div class="flex justify-end space-x-3">
                <button
                    type="button"
                    x-on:click="$dispatch('close-modal')"
                    class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:text-gray-800 dark:hover:text-white"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    wire:click="forceUpdateConfig"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-white rounded disabled:opacity-50 disabled:cursor-not-allowed {{ $this->updateType['button_class'] }}"
                >
                    <svg wire:loading class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span wire:loading.remove>Update Config</span>
                    <span wire:loading>Updating...</span>
                </button>
            </div>
        </x-slot>
    </x-archium::archium-modal>
</div>
