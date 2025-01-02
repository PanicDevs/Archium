<?php

namespace PanicDev\Archium\Http\Livewire\Pages;

use Livewire\Component;
use PanicDev\Archium\Http\Livewire\Traits\HandlesModuleData;
use PanicDev\Archium\Http\Livewire\Traits\HandlesInstallationSteps;
use PanicDev\Archium\Http\Livewire\Traits\HandlesStepExecution;
use PanicDev\Archium\Http\Livewire\Traits\HandlesVersionChecks;
use PanicDev\Archium\Http\Livewire\Traits\HandlesDependencies;
use PanicDev\Archium\Http\Livewire\Traits\HandlesRepositoryOperations;

/**
 * ModuleInstallation Component
 * 
 * This component manages the installation process of Archium modules.
 * It handles the entire workflow from checking dependencies to installing
 * the module and its requirements.
 */
class ModuleInstallation extends Component
{
    use HandlesModuleData;
    use HandlesInstallationSteps;
    use HandlesStepExecution;
    use HandlesVersionChecks;
    use HandlesDependencies;
    use HandlesRepositoryOperations;

    public $reportReady = false;

    /**
     * Initialize the component with the module key
     */
    public function mount(string $module)
    {
        $this->moduleKey = $module;
        $this->loadModule();
        $this->initializeSteps();
    }

    /**
     * Render the component
     */
    public function render()
    {
        return view('archium::livewire.pages.module-installation')->layout('archium::layouts.app');
    }
}
