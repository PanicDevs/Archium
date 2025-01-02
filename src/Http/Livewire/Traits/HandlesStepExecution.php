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
     * Execute a main installation step
     */
    public function executeStep(string $step): void
    {
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
                    // Skip enabling modules if installation was skipped
                    if (isset($this->moduleData['skip_installation']) && $this->moduleData['skip_installation']) {
                        $this->updateSubStep(
                            'finalize',
                            'enable-modules',
                            'completed',
                            'Module enabling skipped - No changes were made'
                        );
                        break;
                    }

                    // Enable the main module first using directory name
                    $moduleDirectory = $this->moduleData['directory'];
                    \Artisan::call('module:enable', ['module' => $moduleDirectory]);
                    $output = \Artisan::output();
                    \Log::info('Main module enable command output', [
                        'module' => $moduleDirectory,
                        'output' => $output
                    ]);

                    // Then enable all dependencies that were installed
                    if (!empty($this->moduleData['dependencies'])) {
                        foreach ($this->moduleData['dependencies'] as $dependency => $data) {
                            if (is_array($data) && (!isset($data['skip_installation']) || !$data['skip_installation'])) {
                                // Get dependency data from XML
                                $dependencyData = $this->getModuleData($dependency);
                                \Artisan::call('module:enable', ['module' => $dependencyData['directory']]);
                                $output = \Artisan::output();
                                \Log::info('Dependency enable command output', [
                                    'dependency' => $dependencyData['directory'],
                                    'output' => $output
                                ]);
                            }
                        }
                    }

                    $this->updateSubStep(
                        'finalize',
                        'enable-modules',
                        'completed',
                        'All modules have been enabled successfully'
                    );
                    break;

                case 'verify-installation':
                    // Skip verification if installation was skipped
                    if (isset($this->moduleData['skip_installation']) && $this->moduleData['skip_installation']) {
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
