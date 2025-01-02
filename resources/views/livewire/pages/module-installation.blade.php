<!-- Main Module Installation Page -->
<div class="min-h-screen bg-gray-50 dark:bg-gray-950">
    <div class="max-w-[90rem] mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('archium::livewire.pages.module-installation.partials.header')
        @include('archium::livewire.pages.module-installation.partials.warning-banner')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                @include('archium::livewire.pages.module-installation.partials.steps-timeline')
            </div>
            <div class="lg:h-full">
                @include('archium::livewire.pages.module-installation.partials.module-info')
            </div>
        </div>
    </div>
</div> 