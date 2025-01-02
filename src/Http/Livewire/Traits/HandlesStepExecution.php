<?php

namespace PanicDev\Archium\Http\Livewire\Traits;

use Illuminate\Support\Facades\File;

/**
 * Trait HandlesStepExecution
 *
 * This trait manages the execution of installation steps and their state transitions.
 * It handles both main steps and sub-steps, including state updates, error handling,
 * and progress tracking.
 */
trait HandlesStepExecution
{
    /**
     * Installation report tracking all steps and their outcomes
     */
    public array $installationReport = [];

    /**
     * Store original module states before installation
     */
    protected array $originalModuleStates = [];

    /**
     * Prepare system for installation by storing module states and cleaning cache
     */
    protected function prepareSystemForInstallation(): void
    {
        \Log::info("Starting system preparation for installation");
        
        try {
            // Store current module states from modules_statuses.json
            $modulesStatusFile = base_path('modules_statuses.json');
            if (File::exists($modulesStatusFile)) {
                $this->originalModuleStates = json_decode(File::get($modulesStatusFile), true);
                \Log::info("Read original states from modules_statuses.json", [
                    'states' => $this->originalModuleStates
                ]);
            }

            // Get current modules list from modules directory
            $modulesPath = config('archium.modules_directory');
            $modulesList = [];
            if (File::exists($modulesPath)) {
                foreach (File::directories($modulesPath) as $moduleDir) {
                    $moduleName = basename($moduleDir);
                    if (File::exists($moduleDir . '/module.json')) {
                        $modulesList[$moduleName] = true;
                    }
                }
            }
            
            \Log::info("Current modules list before installation", [
                'modules' => $modulesList
            ]);

            // Force disable all modules found in either source
            $allModules = array_unique(
                array_merge(
                    array_keys($modulesList), 
                    array_keys($this->originalModuleStates)
                )
            );

            foreach ($allModules as $name) {
                \Log::info("Attempting to disable module", ['module' => $name]);
                
                try {
                    \Artisan::call('module:disable', ['module' => $name]);
                    \Log::info("Module disable command output", [
                        'module' => $name,
                        'output' => \Artisan::output()
                    ]);
                } catch (\Exception $e) {
                    \Log::warning("Failed to disable module but continuing", [
                        'module' => $name,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Verify modules are disabled by checking modules_statuses.json
            if (File::exists($modulesStatusFile)) {
                $currentStates = json_decode(File::get($modulesStatusFile), true);
                \Log::info("Current module states after disabling", [
                    'states' => $currentStates
                ]);

                // If any module is still enabled, try to force disable it again
                foreach ($currentStates as $name => $enabled) {
                    if ($enabled === true) {
                        \Log::warning("Module still enabled, trying to force disable", ['module' => $name]);
                        try {
                            \Artisan::call('module:disable', ['module' => $name]);
                        } catch (\Exception $e) {
                            \Log::error("Failed to force disable module", [
                                'module' => $name,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                }
            }

            // Aggressively clear all caches
            $commands = [
                'optimize:clear',
                'cache:clear',
                'config:clear',
                'route:clear',
                'view:clear'
            ];

            foreach ($commands as $command) {
                try {
                    \Artisan::call($command);
                    \Log::info("Cache clear command output", [
                        'command' => $command,
                        'output' => \Artisan::output()
                    ]);
                } catch (\Exception $e) {
                    \Log::warning("Cache clear command failed but continuing", [
                        'command' => $command,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Final verification by reading modules_statuses.json
            if (File::exists($modulesStatusFile)) {
                $finalStates = json_decode(File::get($modulesStatusFile), true);
                \Log::info("Final module states after preparation", [
                    'states' => $finalStates
                ]);
            }

        } catch (\Exception $e) {
            \Log::error("Error during system preparation", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Execute a main installation step
     */
    public function executeStep(string $step): void
    {
        // Initialize system before clone step or dependency clone steps
        if ($step === 'clone-repository' || preg_match('/^clone-dependency-(.+)$/', $step)) {
            $this->prepareSystemForInstallation();
        }

        // Check if this step should be skipped
        if (method_exists($this, 'shouldSkipStep') && $this->shouldSkipStep($step)) {
            $this->markStepAsSkipped($step);
            return;
        }

        // Check if this is a dependency step that should be skipped
        if (preg_match('/^install-dependency-(.+)$/', $step, $matches) &&
            method_exists($this, 'shouldSkipDependencyStep') &&
            $this->shouldSkipDependencyStep($matches[1], $step)) {
            $this->markStepAsSkipped($step);
            return;
        }

        $this->steps[$step]['status'] = 'processing';

        try {
            // For steps without sub-steps, mark them as completed immediately
            $this->updateStep($step, 'completed', 'Step completed successfully.');
        } catch (\Exception $e) {
            $this->updateStep($step, 'failed', null, $e->getMessage());
        }
    }

    /**
     * Mark a step and its sub-steps as skipped
     */
    private function markStepAsSkipped(string $step): void
    {
        \Log::info('Marking step as skipped', [
            'step' => $step,
            'current_step' => $this->currentStep,
            'current_sub_step' => $this->currentSubStep
        ]);

        // Mark all sub-steps as completed with skipped message
        if (isset($this->steps[$step]['sub_steps'])) {
            foreach ($this->steps[$step]['sub_steps'] as &$subStep) {
                $this->updateSubStep(
                    $step,
                    $subStep['key'],
                    'completed',
                    'Skipped - Installation not required'
                );
            }
        }

        // Mark main step as completed
        $this->updateStep($step, 'completed', 'Step skipped - Installation not required');

        \Log::info('After marking step as skipped', [
            'step' => $step,
            'current_step' => $this->currentStep,
            'current_sub_step' => $this->currentSubStep,
            'next_step' => $this->getNextStep($step)
        ]);
    }

    /**
     * Execute a sub-step within a main step
     */
    public function executeSubStep(string $step, string $subStep): void
    {
        \Log::info('Executing sub-step', [
            'step' => $step,
            'sub_step' => $subStep,
            'current_step' => $this->currentStep,
            'current_sub_step' => $this->currentSubStep
        ]);

        // Set the main step to processing state if it's not already completed
        if ($this->steps[$step]['status'] !== 'completed') {
            $this->steps[$step]['status'] = 'processing';
        }

        // Set the sub-step to processing
        $this->updateSubStep($step, $subStep, 'processing');

        try {
            // Check if this is a step that should be skipped
            if (method_exists($this, 'shouldSkipStep') && $this->shouldSkipStep($step)) {
                $this->markStepAsSkipped($step);
                return;
            }

            // Check if this is a dependency step that should be skipped
            if ((preg_match('/^install-dependency-(.+)$/', $step, $matches) ||
                 preg_match('/^clone-dependency-(.+)$/', $step, $matches)) &&
                method_exists($this, 'shouldSkipDependencyStep') &&
                $this->shouldSkipDependencyStep($matches[1], $step)) {
                $this->markStepAsSkipped($step);
                return;
            }

            switch ($step) {
                case 'depends-check':
                    $this->checkDependsSubStep($subStep);
                    break;
                case 'version-check':
                    $this->checkVersionSubStep($subStep);
                    break;
                case (preg_match('/^check-dependency-(.+)$/', $step, $matches) ? true : false):
                    $this->checkDependencySubStep($matches[1], $subStep);
                    break;
                case 'clone-repository':
                    $this->cloneRepositorySubStep($subStep);
                    break;
                case (preg_match('/^clone-dependency-(.+)$/', $step, $matches) ? true : false):
                    $this->cloneDependencySubStep($matches[1], $subStep);
                    break;
                case 'finalize':
                    $this->finalizeSubStep($subStep);
                    break;
            }

            // After successful execution, get the next sub-step
            $nextSubStep = $this->getNextSubStep($step, $subStep);

            \Log::info('After executing sub-step', [
                'step' => $step,
                'sub_step' => $subStep,
                'next_sub_step' => $nextSubStep,
                'current_step' => $this->currentStep,
                'current_sub_step' => $this->currentSubStep,
                'step_status' => $this->steps[$step]['status']
            ]);

            // If there's no next sub-step, mark the main step as completed
            if (!$nextSubStep) {
                $this->updateStep($step, 'completed');
            } else if ($step === $this->currentStep) {
                // Only update currentSubStep if we're still on the same step
                $this->currentSubStep = $nextSubStep;
            }
        } catch (\Exception $e) {
            $this->updateSubStep($step, $subStep, 'failed', null, $e->getMessage());
            // Set error state on the main step
            $this->steps[$step]['status'] = 'failed';
            throw $e; // Re-throw to stop the process
        }
    }

    /**
     * Update the state of a main step
     */
    protected function updateStep(string $step, string $status, ?string $message = null, ?string $error = null): void
    {
        \Log::info('Updating step', [
            'step' => $step,
            'status' => $status,
            'message' => $message,
            'current_step' => $this->currentStep,
            'current_sub_step' => $this->currentSubStep
        ]);

        $this->steps[$step]['status'] = $status;

        if ($message || $error) {
            $this->stepResponses[$step] = array_filter([
                'message' => $message,
                'error' => $error
            ]);
        }

        // Add to installation report
        $this->installationReport[] = [
            'step' => $step,
            'status' => $status,
            'message' => $message,
            'error' => $error,
            'timestamp' => now()
        ];

        if ($status === 'completed') {
            $nextStep = $this->getNextStep($step);
            \Log::info('Step completed, checking next step', [
                'current_step' => $step,
                'next_step' => $nextStep,
                'has_sub_steps' => isset($this->steps[$nextStep]['sub_steps'])
            ]);

            if ($nextStep) {
                $this->currentStep = $nextStep;

                // Initialize sub-steps for the new step if they exist
                if (isset($this->steps[$nextStep]['sub_steps']) && !empty($this->steps[$nextStep]['sub_steps'])) {
                    $this->subSteps = $this->steps[$nextStep]['sub_steps'];
                    // Set the first sub-step as current
                    $this->currentSubStep = $this->steps[$nextStep]['sub_steps'][0]['key'];
                } else {
                    // Reset currentSubStep when there are no sub-steps
                    $this->currentSubStep = '';
                }

                \Log::info('After moving to next step', [
                    'current_step' => $this->currentStep,
                    'current_sub_step' => $this->currentSubStep,
                    'sub_steps' => $this->subSteps ?? []
                ]);
            }
        }
    }

    /**
     * Update the state of a sub-step
     */
    protected function updateSubStep(string $step, string $subStep, string $status, ?string $message = null, ?string $error = null): void
    {
        foreach ($this->steps[$step]['sub_steps'] as &$sub) {
            if ($sub['key'] === $subStep) {
                $sub['status'] = $status;
                break;
            }
        }

        if ($message || $error) {
            $this->stepResponses["{$step}.{$subStep}"] = array_filter([
                'message' => $message,
                'error' => $error
            ]);
        }

        // Add to installation report
        $this->installationReport[] = [
            'step' => $step,
            'sub_step' => $subStep,
            'status' => $status,
            'message' => $message,
            'error' => $error,
            'timestamp' => now()
        ];

        // Move to next sub-step if completed
        if ($status === 'completed') {
            $nextSubStep = $this->getNextSubStep($step, $subStep);
            if ($nextSubStep) {
                $this->currentSubStep = $nextSubStep;
            } else {
                // All sub-steps completed, move to next main step
                $this->updateStep($step, 'completed');
            }
        }
    }

    /**
     * Retry a failed step
     */
    public function retryStep(string $step): void
    {
        // Reset step status
        $this->steps[$step]['status'] = 'pending';

        // Reset all sub-steps
        if (!empty($this->steps[$step]['sub_steps'])) {
            foreach ($this->steps[$step]['sub_steps'] as &$subStep) {
                $subStep['status'] = 'pending';
            }
        }

        // Clear any error messages
        $this->error = null;

        // Clear step responses
        foreach ($this->stepResponses as $key => $response) {
            if (str_starts_with($key, $step)) {
                unset($this->stepResponses[$key]);
            }
        }
    }

    /**
     * Handle finalization sub-steps
     */
    private function finalizeSubStep(string $subStep): void
    {
        try {
            switch ($subStep) {
                case 'enable-modules':
                    \Log::info("Starting enable-modules finalization step", [
                        'skip_installation' => $this->moduleData['skip_installation'] ?? false,
                        'original_states' => $this->originalModuleStates
                    ]);

                    // Check if any changes were made (main module or dependencies)
                    $hasChanges = false;
                    
                    // Check main module
                    if (!isset($this->moduleData['skip_installation']) || !$this->moduleData['skip_installation']) {
                        $hasChanges = true;
                    }
                    
                    // Check dependencies
                    if (!empty($this->moduleData['dependencies'])) {
                        foreach ($this->moduleData['dependencies'] as $dependency => $data) {
                            if (is_array($data) && (!isset($data['skip_installation']) || !$data['skip_installation'])) {
                                $hasChanges = true;
                                break;
                            }
                        }
                    }

                    \Log::info("Installation changes check", [
                        'has_changes' => $hasChanges,
                        'main_module_skipped' => $this->moduleData['skip_installation'] ?? false,
                        'dependencies' => $this->moduleData['dependencies'] ?? []
                    ]);

                    // Skip enabling modules if no changes were made
                    if (!$hasChanges) {
                        \Log::info("Skipping module enabling - No changes were made");
                        $this->updateSubStep(
                            'finalize',
                            'enable-modules',
                            'completed',
                            'Module enabling skipped - No changes were made'
                        );
                        break;
                    }

                    // First restore original module states
                    foreach ($this->originalModuleStates as $moduleName => $wasEnabled) {
                        \Log::info("Processing original module state restoration", [
                            'module' => $moduleName,
                            'was_enabled' => $wasEnabled
                        ]);
                        
                        if ($wasEnabled) {
                            \Artisan::call('module:enable', ['module' => $moduleName]);
                            \Log::info('Module enable command output for restoration', [
                                'module' => $moduleName,
                                'output' => \Artisan::output()
                            ]);
                        }
                    }

                    // Verify current state after restoration
                    $modulesStatusFile = base_path('modules_statuses.json');
                    if (File::exists($modulesStatusFile)) {
                        $currentStates = json_decode(File::get($modulesStatusFile), true);
                        \Log::info("Module states after restoring original states", [
                            'states' => $currentStates
                        ]);
                    }

                    // Then enable the main module if it's new
                    $moduleDirectory = $this->moduleData['directory'];
                    \Log::info("Checking main module installation", [
                        'module_directory' => $moduleDirectory,
                        'exists_in_original' => isset($this->originalModuleStates[$moduleDirectory])
                    ]);
                    
                    if (!isset($this->originalModuleStates[$moduleDirectory])) {
                        \Log::info("Enabling new main module", ['module' => $moduleDirectory]);
                        \Artisan::call('module:enable', ['module' => $moduleDirectory]);
                        \Log::info('Main module enable command output', [
                            'module' => $moduleDirectory,
                            'output' => \Artisan::output()
                        ]);
                    }

                    // Then enable any new dependencies that were installed
                    if (!empty($this->moduleData['dependencies'])) {
                        \Log::info("Processing dependencies for enabling", [
                            'dependencies' => $this->moduleData['dependencies']
                        ]);
                        
                        foreach ($this->moduleData['dependencies'] as $dependency => $data) {
                            \Log::info("Processing dependency", [
                                'dependency' => $dependency,
                                'data' => $data
                            ]);
                            
                            if (is_array($data) && (!isset($data['skip_installation']) || !$data['skip_installation'])) {
                                $dependencyData = $this->getModuleData($dependency);
                                $depDirectory = $dependencyData['directory'];
                                
                                \Log::info("Checking dependency installation", [
                                    'dependency' => $dependency,
                                    'directory' => $depDirectory,
                                    'exists_in_original' => isset($this->originalModuleStates[$depDirectory])
                                ]);
                                
                                // Only enable if it's a new module
                                if (!isset($this->originalModuleStates[$depDirectory])) {
                                    \Log::info("Enabling new dependency", ['dependency' => $depDirectory]);
                                    \Artisan::call('module:enable', ['module' => $depDirectory]);
                                    \Log::info('Dependency enable command output', [
                                        'dependency' => $depDirectory,
                                        'output' => \Artisan::output()
                                    ]);
                                }
                            }
                        }
                    }

                    // Final state verification
                    if (File::exists($modulesStatusFile)) {
                        $finalStates = json_decode(File::get($modulesStatusFile), true);
                        \Log::info("Final module states after all operations", [
                            'states' => $finalStates
                        ]);
                    }

                    // Clear bootstrap cache again after enabling modules
                    \Artisan::call('optimize:clear');
                    \Log::info("Cleared bootstrap cache after enabling modules", [
                        'output' => \Artisan::output()
                    ]);

                    $this->updateSubStep(
                        'finalize',
                        'enable-modules',
                        'completed',
                        'All modules have been restored and new modules enabled successfully'
                    );
                    break;

                case 'verify-installation':
                    // Check if any changes were made (main module or dependencies)
                    $hasChanges = false;
                    
                    // Check main module
                    if (!isset($this->moduleData['skip_installation']) || !$this->moduleData['skip_installation']) {
                        $hasChanges = true;
                    }
                    
                    // Check dependencies
                    if (!empty($this->moduleData['dependencies'])) {
                        foreach ($this->moduleData['dependencies'] as $dependency => $data) {
                            if (is_array($data) && (!isset($data['skip_installation']) || !$data['skip_installation'])) {
                                $hasChanges = true;
                                break;
                            }
                        }
                    }

                    // Skip verification if no changes were made
                    if (!$hasChanges) {
                        $this->updateSubStep(
                            'finalize',
                            'verify-installation',
                            'completed',
                            'Verification skipped - No changes were made'
                        );
                        break;
                    }

                    // Get module data from XML
                    $moduleData = $this->getModuleData($this->moduleData['key']);
                    $modulePath = config('archium.modules_directory') . '/' . $moduleData['directory'];
                    $gitPath = $modulePath . '/.git';
                    if (File::exists($gitPath)) {
                        File::deleteDirectory($gitPath);
                        \Log::info("Removed .git directory from module", [
                            'module' => $this->moduleData['key'],
                            'path' => $gitPath
                        ]);
                    }

                    // For dependencies
                    if (!empty($this->moduleData['dependencies'])) {
                        foreach ($this->moduleData['dependencies'] as $dependency => $data) {
                            if (is_array($data) && (!isset($data['skip_installation']) || !$data['skip_installation'])) {
                                // Get dependency data from XML
                                $dependencyData = $this->getModuleData($dependency);
                                $dependencyPath = config('archium.modules_directory') . '/' . $dependencyData['directory'];
                                $gitPath = $dependencyPath . '/.git';
                                if (File::exists($gitPath)) {
                                    File::deleteDirectory($gitPath);
                                    \Log::info("Removed .git directory from dependency", [
                                        'dependency' => $dependency,
                                        'path' => $gitPath
                                    ]);
                                }
                            }
                        }
                    }

                    $this->updateSubStep(
                        'finalize',
                        'verify-installation',
                        'completed',
                        'All modules verified and .git directories cleaned up'
                    );
                    break;

                case 'generate-report':
                    // Generate the final installation report
                    $report = [
                        'module' => [
                            'key' => $this->moduleData['key'],
                            'directory' => $this->moduleData['directory'],
                            'version' => $this->moduleData['version'] ?? 'unknown',
                            'repository' => $this->moduleData['repository'] ?? 'unknown',
                            'branch' => $this->moduleData['branch'] ?? 'unknown',
                            'skipped' => isset($this->moduleData['skip_installation']) && $this->moduleData['skip_installation']
                        ],
                        'dependencies' => [],
                        'timeline' => []
                    ];

                    // Add dependency information
                    if (!empty($this->moduleData['dependencies_data'])) {
                        foreach ($this->moduleData['dependencies_data'] as $dependencyKey => $dependencyData) {
                            $report['dependencies'][$dependencyKey] = [
                                'directory' => $dependencyData['directory'],
                                'version' => $this->moduleData['dependencies'][$dependencyKey]['version'] ?? $dependencyData['version'] ?? 'unknown',
                                'repository' => $dependencyData['repository'] ?? 'unknown',
                                'branch' => $dependencyData['branch'] ?? 'unknown',
                                'skipped' => isset($this->moduleData['dependencies'][$dependencyKey]['skip_installation']) && 
                                           $this->moduleData['dependencies'][$dependencyKey]['skip_installation']
                            ];
                        }
                    }

                    // Add timeline from installation report
                    $report['timeline'] = array_map(function($entry) {
                        return [
                            'step' => $entry['step'],
                            'sub_step' => $entry['sub_step'] ?? null,
                            'status' => $entry['status'],
                            'message' => $entry['message'] ?? null,
                            'error' => $entry['error'] ?? null,
                            'timestamp' => $entry['timestamp']
                        ];
                    }, $this->installationReport);

                    // Store the report in session for viewing
                    session(['installation_report' => $report]);
                    
                    $this->reportReady = true;

                    $this->updateSubStep(
                        'finalize',
                        'generate-report',
                        'completed',
                        'Installation report generated successfully'
                    );
                    break;

                default:
                    throw new \Exception("Unknown finalization sub-step: {$subStep}");
            }
        } catch (\Exception $e) {
            $this->updateSubStep('finalize', $subStep, 'failed', null, $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get the human-readable title for a sub-step
     */
    private function getSubStepTitle(string $step, string $subStep): string
    {
        foreach ($this->steps[$step]['sub_steps'] as $sub) {
            if ($sub['key'] === $subStep) {
                return $sub['title'];
            }
        }
        return $subStep; // Fallback to key if title not found
    }
}
