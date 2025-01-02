<!-- Steps Timeline -->
<div class="lg:col-span-2">
    <div class="bg-white dark:bg-gray-900 shadow-sm rounded-lg divide-y divide-gray-200 dark:divide-gray-800">
        @foreach($steps as $key => $step)
            @include('archium::livewire.pages.module-installation.partials.step', [
                'step' => $step,
                'key' => $key,
                'currentStep' => $currentStep,
                'stepResponses' => $stepResponses ?? []
            ])
        @endforeach
    </div>
</div> 