<div class="min-h-screen bg-gray-50 dark:bg-gray-950">
    <div class="max-w-[90rem] mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center gap-2">
                <a href="{{ route('archium.modules') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Installation Report: {{ $report['module']['name'] ?? $report['module']['key'] }}</h1>
            </div>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Installation report details and timeline.</p>
        </div>

        <div class="space-y-8">
            <!-- Module Information -->
            <div class="bg-white dark:bg-gray-900 shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Module Information</h3>
                <dl class="grid grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Key</dt>
                        <dd class="text-sm text-gray-900">{{ $report['module']['key'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Directory</dt>
                        <dd class="text-sm text-gray-900">{{ $report['module']['directory'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Version</dt>
                        <dd class="text-sm text-gray-900">{{ $report['module']['version'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Branch</dt>
                        <dd class="text-sm text-gray-900">{{ $report['module']['branch'] }}</dd>
                    </div>
                    <div class="col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Repository</dt>
                        <dd class="text-sm text-gray-900 break-all">{{ $report['module']['repository'] }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Dependencies Information -->
            @if(count($report['dependencies'] ?? []) > 0)
                <div class="bg-white dark:bg-gray-900 shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">Dependencies</h3>
                    <div class="space-y-4">
                        @foreach($report['dependencies'] as $key => $dep)
                            <div class="border rounded-lg p-4">
                                <h4 class="font-medium text-gray-900 mb-2">{{ $key }}</h4>
                                <dl class="grid grid-cols-2 gap-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Directory</dt>
                                        <dd class="text-sm text-gray-900">{{ $dep['directory'] }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Local Version</dt>
                                        <dd class="text-sm text-gray-900">{{ $dep['local_version'] }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Remote Version</dt>
                                        <dd class="text-sm text-gray-900">{{ $dep['remote_version'] }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                                        <dd class="text-sm">
                                            @if($dep['skipped'])
                                                <span class="text-gray-500">Skipped</span>
                                            @elseif($dep['fresh_install'])
                                                <span class="text-green-600">Fresh Install</span>
                                            @else
                                                <span class="text-blue-600">Updated</span>
                                            @endif
                                        </dd>
                                    </div>
                                    <div class="col-span-2">
                                        <dt class="text-sm font-medium text-gray-500">Repository</dt>
                                        <dd class="text-sm text-gray-900 break-all">{{ $dep['repository'] }}</dd>
                                    </div>
                                </dl>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Installation Timeline -->
            <div class="bg-white dark:bg-gray-900 shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Installation Timeline</h3>
                <div class="space-y-4">
                    @foreach($report['timeline'] as $entry)
                        <div class="relative pl-8 pb-4 border-l-2 {{ 
                            $entry['status'] === 'completed' ? 'border-green-500' :
                            ($entry['status'] === 'failed' ? 'border-red-500' :
                            ($entry['status'] === 'skipped' ? 'border-gray-500' : 'border-blue-500'))
                        }}">
                            <div class="absolute -left-2 top-0">
                                <div class="rounded-full h-4 w-4 {{ 
                                    $entry['status'] === 'completed' ? 'bg-green-500' :
                                    ($entry['status'] === 'failed' ? 'bg-red-500' :
                                    ($entry['status'] === 'skipped' ? 'bg-gray-500' : 'bg-blue-500'))
                                }}"></div>
                            </div>
                            <div class="text-sm text-gray-500">{{ $entry['relative_time'] }}</div>
                            <div class="font-medium text-gray-900">{{ $entry['title'] }}</div>
                            @if($entry['message'])
                                <div class="text-sm text-gray-600 mt-1">{{ $entry['message'] }}</div>
                            @endif
                            @if($entry['error'])
                                <div class="text-sm text-red-600 mt-1">{{ $entry['error'] }}</div>
                            @endif
                            <div class="text-xs text-gray-400 mt-1">{{ $entry['timestamp'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div> 