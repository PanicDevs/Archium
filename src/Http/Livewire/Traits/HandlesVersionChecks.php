<?php

namespace PanicDev\Archium\Http\Livewire\Traits;

use Illuminate\Support\Facades\File;

/**
 * Trait HandlesVersionChecks
 * 
 * This trait handles all version-related functionality including checking local versions,
 * comparing with remote versions, and managing update decisions for both the main module
 * and its dependencies.
 */
trait HandlesVersionChecks
{
    /**
     * Track installed modules and their versions
     */
    public array $installedModules = [];

    /**
     * Track dependency version overrides and update choices
     */
    public array $dependencyOverrides = [];

    /**
     * Handle version check sub-steps for the main module
     */
    protected function checkVersionSubStep(string $subStep): void
    {
        try {
            $modulePath = config('archium.modules_directory') . '/' . $this->moduleData['directory'];
            
            switch ($subStep) {
                case 'check-local-existence':
                    \Log::info('Checking local existence', [
                        'modulePath' => $modulePath,
                        'exists' => File::exists($modulePath)
                    ]);

                    if (File::exists($modulePath)) {
                        $this->updateSubStep(
                            'version-check',
                            $subStep,
                            'completed',
                            "Module directory exists at {$modulePath}"
                        );
                    } else {
                        \Log::info('Module does not exist, marking for fresh install', [
                            'current_step' => $this->currentStep,
                            'current_sub_step' => $this->currentSubStep,
                            'steps' => $this->steps['version-check']
                        ]);

                        // Mark for fresh installation
                        $this->moduleData['needs_fresh_install'] = true;

                        // Module doesn't exist, skip all version check steps
                        foreach ($this->steps['version-check']['sub_steps'] as $step) {
                            \Log::info('Processing sub-step for fresh install', [
                                'step_key' => $step['key'],
                                'current_status' => $step['status'] ?? 'none'
                            ]);

                            $this->updateSubStep(
                                'version-check',
                                $step['key'],
                                'completed',
                                "Skipped - Module will be installed fresh"
                            );
                        }
                    }
                    break;

                case 'check-local-version':
                    $this->checkLocalVersion($modulePath, $subStep);
                    break;

                case 'fetch-remote-version':
                    $this->fetchRemoteVersion($subStep);
                    break;

                case 'compare-versions':
                    $this->compareVersions($subStep);
                    break;

                case 'update-decision':
                    $this->processUpdateDecision($subStep);
                    break;

                default:
                    throw new \Exception("Unknown version check sub-step: {$subStep}");
            }
        } catch (\Exception $e) {
            $this->updateSubStep('version-check', $subStep, 'failed', null, $e->getMessage());
            // Set error state on the main step
            $this->steps['version-check']['status'] = 'failed';
        }
    }

    /**
     * Check the local version of a module
     */
    private function checkLocalVersion(string $modulePath, string $subStep): void
    {
        $moduleJsonPath = $modulePath . '/module.json';
        if (!File::exists($moduleJsonPath)) {
            throw new \Exception("module.json not found in the module directory");
        }

        $moduleJson = json_decode(File::get($moduleJsonPath), true);
        if (!$moduleJson || !isset($moduleJson['version'])) {
            throw new \Exception("Invalid module.json or missing version information");
        }

        $this->moduleData['local_version'] = $moduleJson['version'];
        $this->updateSubStep(
            'version-check',
            $subStep,
            'completed',
            "Current version: {$moduleJson['version']}"
        );
    }

    /**
     * Fetch and store the remote version
     */
    private function fetchRemoteVersion(string $subStep): void
    {
        // Remote version is already in moduleData from XML
        $remoteVersion = $this->moduleData['version'];
        $this->moduleData['remote_version'] = $remoteVersion;
        $this->updateSubStep(
            'version-check',
            $subStep,
            'completed',
            "Latest version: {$remoteVersion}"
        );
    }

    /**
     * Compare local and remote versions
     */
    private function compareVersions(string $subStep): void
    {
        if (isset($this->moduleData['needs_fresh_install'])) {
            $this->updateSubStep(
                'version-check',
                $subStep,
                'completed',
                "Fresh installation will be performed"
            );
            return;
        }

        $localVersion = $this->moduleData['local_version'];
        $remoteVersion = $this->moduleData['remote_version'];

        // Split versions into parts
        $local = array_map('intval', explode('.', $localVersion));
        $remote = array_map('intval', explode('.', $remoteVersion));

        $needsUpdate = false;
        $updateType = '';

        // Compare major.minor.patch
        if ($local[0] < $remote[0]) {
            $needsUpdate = true;
            $updateType = 'Major update available';
        } elseif ($local[1] < $remote[1]) {
            $needsUpdate = true;
            $updateType = 'Minor update available';
        } elseif ($local[2] < $remote[2]) {
            $needsUpdate = true;
            $updateType = 'Patch update available';
        }

        // Always add the decision step, whether update is needed or not
        $this->steps['version-check']['sub_steps'][] = [
            'key' => 'update-decision',
            'title' => 'Installation Decision',
            'description' => $needsUpdate 
                ? 'Decide whether to update the module or keep current version'
                : 'Module is up to date. Decide whether to reinstall or skip',
            'status' => 'pending',
            'needs_confirmation' => true,
            'options' => $needsUpdate
                ? [
                    'update' => 'Update to latest version',
                    'skip' => 'Keep current version'
                ]
                : [
                    'reinstall' => 'Reinstall module',
                    'skip' => 'Skip installation'
                ]
        ];

        $message = $needsUpdate
            ? "{$updateType} (Current: {$localVersion}, Latest: {$remoteVersion})"
            : "Up to date (Version: {$localVersion})";

        $this->moduleData['update_available'] = $needsUpdate;

        $this->updateSubStep(
            'version-check',
            $subStep,
            'completed',
            $message
        );
    }

    /**
     * Process the update decision
     */
    private function processUpdateDecision(string $subStep): void
    {
        $choice = $this->moduleData['update_choice'] ?? null;
        
        if ($choice === 'update' || $choice === 'reinstall') {
            $this->moduleData['needs_fresh_install'] = true;
            $message = $choice === 'update' 
                ? "Module will be updated to latest version"
                : "Module will be reinstalled";
        } else {
            $this->moduleData['skip_installation'] = true;
            $message = "Installation skipped, keeping current version";
        }

        $this->updateSubStep(
            'version-check',
            $subStep,
            'completed',
            $message
        );
    }

    /**
     * Check if the current step should be skipped
     */
    protected function shouldSkipStep(string $step): bool
    {
        return isset($this->moduleData['skip_installation']) && 
            $this->moduleData['skip_installation'] && 
            $step === 'clone-repository';
    }

    /**
     * Make an update choice for the main module
     */
    public function makeUpdateChoice(string $choice): void
    {
        \Log::info('Making update choice', [
            'choice' => $choice,
            'current_step' => $this->currentStep,
            'current_sub_step' => $this->currentSubStep
        ]);
        
        $this->moduleData['update_choice'] = $choice;
        $this->executeSubStep('version-check', 'update-decision');
    }

    /**
     * Make an update choice for a dependency
     */
    public function makeDependencyUpdateChoice(string $dependency, string $choice): void
    {
        \Log::info('Making dependency update choice', [
            'dependency' => $dependency,
            'choice' => $choice,
            'current_state' => $this->moduleData['dependencies'][$dependency] ?? null
        ]);

        if (!isset($this->moduleData['dependencies'][$dependency])) {
            $this->moduleData['dependencies'][$dependency] = [];
        }

        $this->moduleData['dependencies'][$dependency]['update_choice'] = $choice;
        $this->executeSubStep("check-dependency-{$dependency}", "update-decision-{$dependency}");
    }
} 