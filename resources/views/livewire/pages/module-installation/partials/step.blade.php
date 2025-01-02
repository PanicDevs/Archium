<div @class([
    'p-4',
    'opacity-50' => $currentStep !== $key && $step['status'] === 'pending'
])>
    <div class="flex items-start">
        <div class="flex-shrink-0">
            @switch($step['status'])
                @case('completed')
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-success-50 dark:bg-success-500/10">
                        <svg class="h-5 w-5 text-success-500 dark:text-success-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </span>
                    @break
                @case('processing')
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-50 dark:bg-primary-500/10">
                        <svg class="animate-spin h-5 w-5 text-primary-500 dark:text-primary-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                    @break
                @case('failed')
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-danger-50 dark:bg-danger-500/10">
                        <svg class="h-5 w-5 text-danger-500 dark:text-danger-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </span>
                    @break
                @default
                    <span class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-gray-200 dark:border-gray-700">
                        <span class="h-2.5 w-2.5 rounded-full bg-gray-300 dark:bg-gray-600"></span>
                    </span>
            @endswitch
        </div>
        <div class="ml-4 min-w-0 flex-1">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $step['title'] }}</p>
            </div>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $step['description'] }}</p>

            @if(empty($step['sub_steps']) && $currentStep === $key && $step['status'] === 'pending')
                <div class="mt-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $step['confirm_message'] ?? 'This step requires confirmation before proceeding.' }}
                    </p>
                    <div class="mt-2">
                        <button
                            wire:click="executeStep('{{ $key }}')"
                            type="button"
                            class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium text-white bg-primary-600 hover:bg-primary-500 dark:bg-primary-500 dark:hover:bg-primary-400 rounded transition-colors duration-200"
                        >
                            Confirm & Continue
                        </button>
                    </div>
                </div>
            @endif

            @if(!empty($step['sub_steps']))
                @include('archium::livewire.pages.module-installation.partials.sub-steps', [
                    'step' => $step,
                    'key' => $key,
                    'currentStep' => $currentStep,
                    'stepResponses' => $stepResponses
                ])
            @endif

            @if($step['status'] === 'failed' && $currentStep === $key)
                <div class="mt-4">
                    <button
                        wire:click="retryStep('{{ $key }}')"
                        type="button"
                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300"
                    >
                        <svg class="w-4 h-4 mr-1.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Retry Step
                    </button>
                </div>
            @endif

            @if($step['status'] === 'completed')
                <div class="mt-2 flex items-center gap-2">
                    <svg class="w-4 h-4 text-success-500 dark:text-success-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="text-sm text-success-600 dark:text-success-400">Checkpoint reached</span>
                </div>
            @endif

            @if(isset($stepResponses[$key]))
                <div class="mt-2">
                    @if(isset($stepResponses[$key]['error']))
                        <p class="text-sm text-danger-600 dark:text-danger-400">{{ $stepResponses[$key]['error'] }}</p>
                    @elseif(isset($stepResponses[$key]['message']))
                        <p class="text-sm text-success-600 dark:text-success-400">{{ $stepResponses[$key]['message'] }}</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div> 