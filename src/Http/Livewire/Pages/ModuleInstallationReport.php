<?php

namespace PanicDev\Archium\Http\Livewire\Pages;

use Livewire\Component;

class ModuleInstallationReport extends Component
{
    public $report;

    public function mount()
    {
        if (!session()->has('installation_report')) {
            return redirect()->route('archium.modules');
        }

        $this->report = session('installation_report');
    }

    public function render()
    {
        return view('archium::livewire.pages.module-installation-report')->layout('archium::layouts.app');
    }
}
