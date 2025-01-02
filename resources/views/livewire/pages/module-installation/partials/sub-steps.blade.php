<div class="mt-4 space-y-4">
    @foreach($step['sub_steps'] as $subStep)
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0 mt-1">
                @switch($subStep['status'])
                    @case('completed')
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-success-50 dark:bg-success-500/10">
                            <svg class="h-3 w-3 text-success-500 dark:text-success-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </span>
                        @break
                    @case('processing')
                        <span class="flex h-5 w-5 items-center justify-center">
                            <svg class="animate-spin h-3 w-3 text-primary-500 dark:text-primary-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                        @break
                    @case('failed')
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-danger-50 dark:bg-danger-500/10">
                            <svg class="h-3 w-3 text-danger-500 dark:text-danger-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </span>
                        @break
                    @default
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                            <span class="h-1.5 w-1.5 rounded-full bg-gray-400 dark:bg-gray-600"></span>
                        </span>
                @endswitch
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ $subStep['title'] }}
                    </p>
                </div>

                @if($currentStep === $key && $subStep['status'] === 'pending')
                    <div class="mt-2">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $subStep['description'] ?? 'This step requires confirmation before proceeding.' }}
                        </p>
                        @if($currentSubStep === $subStep['key'] || ($currentSubStep === '' && $loop->first && collect($step['sub_steps'])->every(fn($s) => $s['status'] === 'pending')))
                            <div class="mt-2">
                                @if(isset($subStep['options']))
                                    <div class="flex gap-2">
                                        @foreach($subStep['options'] as $value => $label)
                                            <button
                                                wire:click="@if(preg_match('/^update-decision-(.+)$/', $subStep['key'], $matches))makeDependencyUpdateChoice('{{ $matches[1] }}', '{{ $value }}')@else makeUpdateChoice('{{ $value }}')@endif"
                                                wire:loading.attr="disabled"
                                                type="button"
                                                @class([
                                                    'inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed',
                                                    'text-white bg-primary-600 hover:bg-primary-500 dark:bg-primary-500 dark:hover:bg-primary-400' => $value != 'skip',
                                                    'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 dark:text-gray-300 dark:bg-gray-800 dark:border-gray-600 dark:hover:bg-gray-700' => $value === 'skip'
                                                ])
                                            >
                                                <svg wire:loading class="animate-spin -ml-1 mr-1 h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                <span wire:loading.remove>{{ $label }}</span>
                                                <span wire:loading>Processing...</span>
                                            </button>
                                        @endforeach
                                    </div>
                                @else
                                    <button
                                        wire:click="executeSubStep('{{ $key }}', '{{ $subStep['key'] }}')"
                                        wire:loading.attr="disabled"
                                        type="button"
                                        class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium text-white bg-primary-600 hover:bg-primary-500 dark:bg-primary-500 dark:hover:bg-primary-400 rounded transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <svg wire:loading class="animate-spin -ml-1 mr-1 h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span wire:loading.remove>Confirm & Continue</span>
                                        <span wire:loading>Processing...</span>
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif

                @if(isset($stepResponses["{$key}.{$subStep['key']}"]))
                    <div class="mt-1">
                        @if(isset($stepResponses["{$key}.{$subStep['key']}"]['error']))
                            <p class="text-sm text-danger-600 dark:text-danger-400">
                                {{ $stepResponses["{$key}.{$subStep['key']}"]['error'] }}
                            </p>
                            @if($subStep['status'] === 'failed' && $currentStep === $key)
                                <div class="mt-2">
                                    <button
                                        wire:click="executeSubStep('{{ $key }}', '{{ $subStep['key'] }}')"
                                        type="button"
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300"
                                    >
                                        <svg class="w-3 h-3 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        Retry
                                    </button>
                                </div>
                            @endif
                        @elseif(isset($stepResponses["{$key}.{$subStep['key']}"]['message']))
                            <p class="text-sm text-success-600 dark:text-success-400">
                                {{ $stepResponses["{$key}.{$subStep['key']}"]['message'] }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endforeach
</div>
