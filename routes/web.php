<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'archium',
    'as' => 'archium.',
    'middleware' => config('archium.middleware', ['web'])
], function () {
    Route::get('/', \PanicDev\Archium\Http\Livewire\Pages\Dashboard::class)->name('dashboard');
    Route::get('/modules', \PanicDev\Archium\Http\Livewire\Pages\Modules::class)->name('modules');
    Route::get('/modules/i/{module}', \PanicDev\Archium\Http\Livewire\Pages\ModuleInstallation::class)->name('modules.install');
}); 