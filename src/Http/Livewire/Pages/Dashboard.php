<?php

namespace PanicDev\Archium\Http\Livewire\Pages;

use Livewire\Component;
use PanicDev\Archium\Http\Livewire\Traits\HandlesModules;

/**
 * The main dashboard component for Archium.
 *
 * This component provides an overview of the module system status and allows
 * users to manage Laravel Modules installation and configuration.
 */
class Dashboard extends Component
{
    use HandlesModules;

    /**
     * Initialize the component state.
     */
    public function mount(): void
    {
        $this->checkStatus();
    }

    /**
     * Render the dashboard view.
     */
    public function render()
    {
        return view('archium::livewire.pages.dashboard')
            ->layout('archium::layouts.app');
    }
}
