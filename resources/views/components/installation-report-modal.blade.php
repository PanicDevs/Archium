@props(['report'])

<div x-data="{ show: false }" 
     x-show="show" 
     x-on:show-installation-report.window="show = true; $event.detail && ($refs.reportContent.innerHTML = generateReport($event.detail))"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">
    
    <!-- Modal backdrop -->
    <div class="fixed inset-0 bg-black opacity-50"></div>

    <!-- Modal content -->
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <!-- Modal header -->
            <div class="sticky top-0 bg-white border-b px-6 py-4 flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-900">Installation Report</h2>
                <button @click="show = false" class="text-gray-400 hover:text-gray-500">
                    <span class="sr-only">Close</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal body -->
            <div class="p-6" x-ref="reportContent"></div>

            <!-- Modal footer -->
            <div class="sticky bottom-0 bg-white border-t px-6 py-4 flex justify-end">
                <button @click="show = false" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function generateReport(report) {
    return `
        <div class="space-y-8">
            <!-- Module Information -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="text-lg font-semibold mb-4">Module Information</h3>
                <dl class="grid grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Key</dt>
                        <dd class="text-sm text-gray-900">${report.module.key}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Directory</dt>
                        <dd class="text-sm text-gray-900">${report.module.directory}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Version</dt>
                        <dd class="text-sm text-gray-900">${report.module.version}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Branch</dt>
                        <dd class="text-sm text-gray-900">${report.module.branch}</dd>
                    </div>
                    <div class="col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Repository</dt>
                        <dd class="text-sm text-gray-900 break-all">${report.module.repository}</dd>
                    </div>
                </dl>
            </div>

            <!-- Dependencies Information -->
            ${Object.keys(report.dependencies).length > 0 ? `
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="text-lg font-semibold mb-4">Dependencies</h3>
                    <div class="space-y-4">
                        ${Object.entries(report.dependencies).map(([key, dep]) => `
                            <div class="border rounded-lg p-4">
                                <h4 class="font-medium text-gray-900 mb-2">${key}</h4>
                                <dl class="grid grid-cols-2 gap-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Directory</dt>
                                        <dd class="text-sm text-gray-900">${dep.directory}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Local Version</dt>
                                        <dd class="text-sm text-gray-900">${dep.local_version}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Remote Version</dt>
                                        <dd class="text-sm text-gray-900">${dep.remote_version}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                                        <dd class="text-sm">
                                            ${dep.skipped ? 
                                                '<span class="text-gray-500">Skipped</span>' : 
                                                dep.fresh_install ? 
                                                    '<span class="text-green-600">Fresh Install</span>' : 
                                                    '<span class="text-blue-600">Updated</span>'
                                            }
                                        </dd>
                                    </div>
                                    <div class="col-span-2">
                                        <dt class="text-sm font-medium text-gray-500">Repository</dt>
                                        <dd class="text-sm text-gray-900 break-all">${dep.repository}</dd>
                                    </div>
                                </dl>
                            </div>
                        `).join('')}
                    </div>
                </div>
            ` : ''}

            <!-- Installation Timeline -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="text-lg font-semibold mb-4">Installation Timeline</h3>
                <div class="space-y-4">
                    ${report.timeline.map(entry => `
                        <div class="relative pl-8 pb-4 border-l-2 ${
                            entry.status === 'completed' ? 'border-green-500' :
                            entry.status === 'failed' ? 'border-red-500' :
                            entry.status === 'skipped' ? 'border-gray-500' :
                            'border-blue-500'
                        }">
                            <div class="absolute -left-2 top-0">
                                <div class="rounded-full h-4 w-4 ${
                                    entry.status === 'completed' ? 'bg-green-500' :
                                    entry.status === 'failed' ? 'bg-red-500' :
                                    entry.status === 'skipped' ? 'bg-gray-500' :
                                    'bg-blue-500'
                                }"></div>
                            </div>
                            <div class="text-sm text-gray-500">${entry.relative_time}</div>
                            <div class="font-medium text-gray-900">${entry.title}</div>
                            ${entry.message ? `<div class="text-sm text-gray-600 mt-1">${entry.message}</div>` : ''}
                            ${entry.error ? `<div class="text-sm text-red-600 mt-1">${entry.error}</div>` : ''}
                            <div class="text-xs text-gray-400 mt-1">${entry.timestamp}</div>
                        </div>
                    `).join('')}
                </div>
            </div>
        </div>
    `;
}
</script> 