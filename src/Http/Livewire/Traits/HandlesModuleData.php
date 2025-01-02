<?php

namespace PanicDev\Archium\Http\Livewire\Traits;

use PanicDev\Archium\Support\ModuleParser;

/**
 * Trait HandlesModuleData
 * 
 * This trait is responsible for loading and managing module data from XML configuration.
 * It handles the initial loading of module information and maintains the module's state
 * throughout the installation process.
 */
trait HandlesModuleData
{
    /**
     * The unique identifier for the module
     */
    public string $moduleKey;

    /**
     * Module data loaded from XML configuration
     * Contains information like name, version, dependencies, etc.
     */
    public ?array $moduleData = null;

    /**
     * All modules data from XML
     * Cached to avoid multiple XML fetches
     */
    protected array $allModules = [];

    /**
     * Any error that occurred during module loading
     */
    public ?string $error = null;

    /**
     * Load module data from the configured XML source
     * 
     * @throws \Exception When XML URL is not configured or module is not found
     */
    protected function loadModule(): void
    {
        try {
            $xmlUrl = config('archium.modules_xml_url');

            if (empty($xmlUrl)) {
                throw new \Exception('Modules XML URL is not configured. Please set ARCHIUM_MODULES_XML_URL in your .env file.');
            }

            $xmlContent = @file_get_contents($xmlUrl);

            if ($xmlContent === false) {
                throw new \Exception('Unable to fetch modules XML file. Please check your internet connection and try again.');
            }

            // Load and cache all modules data
            $this->allModules = ModuleParser::parse($xmlContent);

            if (!isset($this->allModules[$this->moduleKey])) {
                throw new \Exception("Module not found: {$this->moduleKey}");
            }

            $this->moduleData = $this->allModules[$this->moduleKey];

            // Cache dependency module data
            if (!empty($this->moduleData['dependencies'])) {
                foreach ($this->moduleData['dependencies'] as $dependency) {
                    if (!isset($this->allModules[$dependency])) {
                        throw new \Exception("Dependency module not found: {$dependency}");
                    }
                    $this->moduleData['dependencies_data'][$dependency] = $this->allModules[$dependency];
                }
            }
            
            // Log the module data structure for debugging
            \Log::debug('Module data loaded', [
                'module_key' => $this->moduleKey,
                'dependencies' => $this->moduleData['dependencies'] ?? [],
                'dependency_types' => array_map('gettype', $this->moduleData['dependencies'] ?? [])
            ]);
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            throw $e;
        }
    }

    /**
     * Get module data for a specific module key
     */
    protected function getModuleData(string $moduleKey): array
    {
        if (isset($this->moduleData['dependencies_data'][$moduleKey])) {
            return $this->moduleData['dependencies_data'][$moduleKey];
        }

        throw new \Exception("Module not found: {$moduleKey}");
    }
} 