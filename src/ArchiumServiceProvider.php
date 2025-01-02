<?php

namespace PanicDev\Archium;

use Livewire\Livewire;
use Illuminate\Support\Facades\Blade;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use PanicDev\Archium\Commands\ArchiumCommand;
use PanicDev\Archium\Http\Livewire\Pages\Dashboard;
use PanicDev\Archium\Http\Livewire\Pages\Modules;
use PanicDev\Archium\Http\Livewire\Pages\ModuleInstallation;

class ArchiumServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('archium')
            ->hasConfigFile()
            ->hasViews()
            ->hasAssets();

        if (file_exists($this->package->basePath('/../routes/web.php'))) {
            $package->hasRoute('web');
        }
    }

    public function packageBooted()
    {
        // Publish compiled assets from dist directory
        if ($this->app->runningInConsole()) {
            $this->publishes([
                $this->package->basePath('/../dist') => public_path('vendor/archium'),
            ], "{$this->package->shortName()}-assets");
        }

        // Register Livewire Components
        Livewire::component('archium::dashboard', Dashboard::class);
        Livewire::component('archium::pages.modules', Modules::class);
        Livewire::component('archium::pages.module-installation', ModuleInstallation::class);

        // Register anonymous Blade components
        Blade::anonymousComponentPath($this->package->basePath('/../resources/views/components'), 'archium');
    }

    public function packageRegistered()
    {
        // Register views with archium namespace
        $this->loadViewsFrom($this->package->basePath('/../resources/views'), 'archium');
    }
}
