<?php

namespace PanicDev\Archium\Http\Livewire\Pages;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PanicDev\Archium\Support\ModuleParser;
use PanicDev\Archium\Http\Livewire\Traits\HandlesModules;

class Modules extends Component
{
    use HandlesModules;

    /**
     * Available modules from the repository.
     */
    public array $availableModules = [];

    /**
     * Whether modules are being fetched.
     */
    public bool $loading = true;

    public function mount(): void
    {
        $this->checkStatus();
        $this->fetchModules();
    }

    /**
     * Fetch available modules from the repository.
     */
    public function fetchModules(): void
    {
        try {
            $this->loading = true;

            $response = Http::get('https://raw.githubusercontent.com/PanicDevs/ArchiumSettings/refs/heads/main/modules.archium');

            if (!$response->successful()) {
                Log::error('Failed to fetch modules list', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Failed to fetch modules list'
                ]);

                return;
            }

            $this->availableModules = ModuleParser::parse($response->body());
            
            Log::info('Modules fetched successfully', [
                'count' => count($this->availableModules)
            ]);
        } catch (\Exception $e) {
            Log::error('Exception while fetching modules', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to fetch modules: ' . $e->getMessage()
            ]);
        } finally {
            $this->loading = false;
        }
    }

    public function render()
    {
        return view('archium::livewire.pages.modules')
            ->layout('archium::layouts.app');
    }
} 