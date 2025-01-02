<?php

namespace PanicDev\Archium\Http\Livewire\Traits;

use Illuminate\Support\Facades\File;
use PanicDev\Archium\Support\ModuleParser;

/**
 * Trait HandlesDependencies
 *
 * This trait handles all dependency-related functionality including checking required
 * dependencies, managing dependency versions, and handling dependency installations.
 */
trait HandlesDependencies
{
    /**
     * List of missing dependencies
     */
    public array $missingDependencies = [];

    /**
     * Check a required dependency's installation status
     */
    protected function checkDependsSubStep(string $subStep): void
    {
        // Extract module name from sub-step key (format: check-{module})
        preg_match('/^check-(.+)$/', $subStep, $matches);
        $moduleKey = $matches[1];

        try {
            // Get module data from XML to find its directory
            $xmlUrl = config('archium.modules_xml_url');
            $xmlContent = @file_get_contents($xmlUrl);
            $modules = ModuleParser::parse($xmlContent);

            if (!isset($modules[$moduleKey])) {
                throw new \Exception("Required module {$moduleKey} not found in modules XML");
            }

            $modulePath = config('archium.modules_directory') . '/' . $modules[$moduleKey]['directory'];

            if (!File::exists($modulePath)) {
                throw new \Exception("Required module {$moduleKey} is not installed. Please install it first.");
            }

            // Check if module is enabled
            if (!File::exists($modulePath . '/module.json')) {
                throw new \Exception("Required module {$moduleKey} is installed but might be corrupted (missing module.json).");
            }

            $moduleJson = json_decode(File::get($modulePath . '/module.json'), true);
            if (!$moduleJson) {
                throw new \Exception("Required module {$moduleKey} has invalid module.json file.");
            }

            if (!($moduleJson['enabled'] ?? false)) {
                throw new \Exception("Required module {$moduleKey} is installed but not enabled.");
            }

            $this->updateSubStep(
                'depends-check',
                $subStep,
                'completed',
                "Required module {$moduleKey} is installed and enabled."
            );
        } catch (\Exception $e) {
            $this->updateSubStep('depends-check', $subStep, 'failed', null, $e->getMessage());
            // Stop the installation process if a required module is missing
            $this->error = "Cannot proceed with installation. {$e->getMessage()}";
        }
    }

    /**
     * Check a dependency's version and status
     */
    protected function checkDependencySubStep(string $dependency, string $subStep): void
    {
        \Log::info('Starting dependency check sub-step', [
            'dependency' => $dependency,
            'subStep' => $subStep,
            'dependency_data' => $this->moduleData['dependencies'][$dependency] ?? null,
            'current_step' => $this->currentStep,
            'current_sub_step' => $this->currentSubStep,
            'update_choice' => $this->moduleData['dependencies'][$dependency]['update_choice'] ?? null
        ]);

        try {
            $modulePath = config('archium.modules_directory') . '/' . $dependency;

            switch ($subStep) {
                case "check-local-{$dependency}":
                    $this->checkDependencyLocalExistence($dependency, $modulePath, $subStep);
                    break;

                case "check-local-version-{$dependency}":
                    $this->checkDependencyLocalVersion($dependency, $modulePath, $subStep);
                    break;

                case "fetch-remote-version-{$dependency}":
                    $this->fetchDependencyRemoteVersion($dependency, $subStep);
                    break;

                case "compare-versions-{$dependency}":
                    $this->compareDependencyVersions($dependency, $subStep);
                    break;

                case "update-decision-{$dependency}":
                    $this->processDependencyUpdateDecision($dependency, $subStep);
                    break;

                default:
                    throw new \Exception("Unknown dependency check sub-step: {$subStep}");
            }
        } catch (\Exception $e) {
            $this->updateSubStep("check-dependency-{$dependency}", $subStep, 'failed', null, $e->getMessage());
            $this->steps["check-dependency-{$dependency}"]['status'] = 'failed';
            throw $e; // Re-throw to stop the process
        }
    }

    /**
     * Check if a dependency exists locally
     */
    private function checkDependencyLocalExistence(string $dependency, string $modulePath, string $subStep): void
    {
        // Get dependency data from cached XML
        $dependencyData = $this->getModuleData($dependency);
        $installPath = config('archium.modules_directory') . '/' . $dependencyData['directory'];

        if (File::exists($installPath)) {
            $this->updateSubStep(
                "check-dependency-{$dependency}",
                $subStep,
                'completed',
                "Dependency exists at {$installPath}"
            );
        } else {
            $this->moduleData['dependencies'][$dependency]['needs_fresh_install'] = true;
            $this->moduleData['dependencies'][$dependency]['module_data'] = $dependencyData;

            $this->updateSubStep(
                "check-dependency-{$dependency}",
                $subStep,
                'completed',
                "Dependency will be installed fresh at {$installPath}"
            );
        }
    }

    /**
     * Check a dependency's local version
     */
    private function checkDependencyLocalVersion(string $dependency, string $modulePath, string $subStep): void
    {
        if (isset($this->moduleData['dependencies'][$dependency]['needs_fresh_install'])) {
            $this->updateSubStep(
                "check-dependency-{$dependency}",
                $subStep,
                'completed',
                "Skipped - Fresh installation planned"
            );
            return;
        }

        $moduleJsonPath = $modulePath . '/module.json';
        if (!File::exists($moduleJsonPath)) {
            throw new \Exception("module.json not found for {$dependency}");
        }

        $moduleJson = json_decode(File::get($moduleJsonPath), true);
        if (!$moduleJson || !isset($moduleJson['version'])) {
            throw new \Exception("Invalid module.json or missing version for {$dependency}");
        }

        $this->moduleData['dependencies'][$dependency]['local_version'] = $moduleJson['version'];
        $this->updateSubStep(
            "check-dependency-{$dependency}",
            $subStep,
            'completed',
            "Current version: {$moduleJson['version']}"
        );
    }

    /**
     * Fetch a dependency's remote version
     */
    private function fetchDependencyRemoteVersion(string $dependency, string $subStep): void
    {
        // Get dependency data from cached XML
        $dependencyData = $this->getModuleData($dependency);
        $remoteVersion = $dependencyData['version'];

        $this->moduleData['dependencies'][$dependency]['remote_version'] = $remoteVersion;
        $this->updateSubStep(
            "check-dependency-{$dependency}",
            $subStep,
            'completed',
            "Latest version: {$remoteVersion}"
        );
    }

    /**
     * Compare a dependency's versions
     */
    private function compareDependencyVersions(string $dependency, string $subStep): void
    {
        if (isset($this->moduleData['dependencies'][$dependency]['needs_fresh_install'])) {
            $this->updateSubStep(
                "check-dependency-{$dependency}",
                $subStep,
                'completed',
                "Fresh installation will be performed"
            );
            return;
        }

        $localVersion = $this->moduleData['dependencies'][$dependency]['local_version'];
        $remoteVersion = $this->moduleData['dependencies'][$dependency]['remote_version'];

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

        // Add update decision sub-step dynamically
        $this->steps["check-dependency-{$dependency}"]['sub_steps'][] = [
            'key' => "update-decision-{$dependency}",
            'title' => "Installation Decision for {$dependency}",
            'description' => $needsUpdate
                ? "Choose whether to update {$dependency} or keep current version"
                : "Module is up to date. Decide whether to reinstall or skip",
            'status' => 'pending',
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

        $this->moduleData['dependencies'][$dependency]['update_available'] = $needsUpdate;

        $this->updateSubStep(
            "check-dependency-{$dependency}",
            $subStep,
            'completed',
            $message
        );
    }

    /**
     * Process a dependency's update decision
     */
    private function processDependencyUpdateDecision(string $dependency, string $subStep): void
    {
        \Log::debug('Processing update decision for dependency', [
            'dependency' => $dependency,
            'update_choice' => $this->moduleData['dependencies'][$dependency]['update_choice'] ?? null,
            'current_step' => $this->currentStep,
            'current_sub_step' => $this->currentSubStep,
            'dependency_data' => $this->moduleData['dependencies'][$dependency] ?? []
        ]);

        if (!isset($this->moduleData['dependencies'][$dependency]['update_choice'])) {
            $this->moduleData['dependencies'][$dependency]['update_choice'] = null;
            return;
        }

        $choice = $this->moduleData['dependencies'][$dependency]['update_choice'];

        if ($choice === 'update' || $choice === 'reinstall') {
            $this->moduleData['dependencies'][$dependency]['needs_fresh_install'] = true;
            $message = $choice === 'update'
                ? "Will update to latest version"
                : "Will reinstall module";
        } else {
            $this->moduleData['dependencies'][$dependency]['skip_installation'] = true;
            $message = "Installation skipped, keeping current version";
        }

        $this->updateSubStep(
            "check-dependency-{$dependency}",
            "update-decision-{$dependency}",
            'completed',
            $message
        );
    }

    /**
     * Check if the current dependency step should be skipped
     */
    protected function shouldSkipDependencyStep(string $dependency, string $step): bool
    {
        return isset($this->moduleData['dependencies'][$dependency]['skip_installation']) &&
            $this->moduleData['dependencies'][$dependency]['skip_installation'] &&
            (str_starts_with($step, "install-dependency-{$dependency}") ||
             str_starts_with($step, "clone-dependency-{$dependency}"));
    }

    /**
     * Handle dependency cloning sub-steps
     */
    protected function cloneDependencySubStep(string $dependency, string $subStep): void
    {
        try {
            // Get dependency data from XML
            $dependencyData = $this->getModuleData($dependency);
            $modulePath = config('archium.modules_directory') . '/' . $dependencyData['directory'];

            switch ($subStep) {
                case 'prepare-directory':
                    // Prepare system before preparing directory
                    $this->prepareSystemForInstallation();
                    
                    $this->prepareDependencyDirectory($dependency, $modulePath);
                    break;

                case 'clone-repo':
                    $this->cloneDependencyRepository($dependency, $modulePath);
                    break;

                case 'verify-clone':
                    $this->verifyDependencyClone($dependency, $modulePath);
                    break;

                default:
                    throw new \Exception("Unknown clone sub-step: {$subStep}");
            }
        } catch (\Exception $e) {
            // Restore backup if exists and something went wrong
            if (isset($this->moduleData['dependencies'][$dependency]['backup_path']) &&
                File::exists($this->moduleData['dependencies'][$dependency]['backup_path'])) {
                if (File::exists($modulePath)) {
                    File::deleteDirectory($modulePath);
                }
                File::move($this->moduleData['dependencies'][$dependency]['backup_path'], $modulePath);
            }

            $this->updateSubStep("clone-dependency-{$dependency}", $subStep, 'failed', null, $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get the backup directory path for a dependency
     */
    private function getDependencyBackupPath(string $dependency): string
    {
        $backupDir = storage_path('app/Archium/Backups/' . date('Y-m-d'));
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }
        $dependencyData = $this->getModuleData($dependency);
        return $backupDir . '/' . $dependencyData['directory'] . '_' . time();
    }

    /**
     * Clean up dependency installation
     */
    private function cleanupDependency(string $dependencyPath, string $dependency): void
    {
        // Remove dependency directory if exists
        if (File::exists($dependencyPath)) {
            File::deleteDirectory($dependencyPath);
        }
    }

    /**
     * Prepare the dependency directory
     */
    private function prepareDependencyDirectory(string $dependency, string $modulePath): void
    {
        // Skip if user chose to keep current version
        if (isset($this->moduleData['dependencies'][$dependency]['update_choice']) && 
            $this->moduleData['dependencies'][$dependency]['update_choice'] === 'skip') {
            $this->updateSubStep(
                "clone-dependency-{$dependency}",
                'prepare-directory',
                'completed',
                'Using existing dependency directory'
            );
            return;
        }

        // Get dependency data from XML
        $dependencyData = $this->getModuleData($dependency);

        if (File::exists($modulePath)) {
            // Create backup
            $backupPath = $this->getDependencyBackupPath($dependency);
            File::move($modulePath, $backupPath);
            if (!isset($this->moduleData['dependencies'][$dependency])) {
                $this->moduleData['dependencies'][$dependency] = [];
            }
            $this->moduleData['dependencies'][$dependency]['backup_path'] = $backupPath;
        }

        // Clean up and create fresh directory
        $this->cleanupDependency($modulePath, $dependency);
        File::makeDirectory($modulePath, 0755, true, true);

        $this->updateSubStep(
            "clone-dependency-{$dependency}",
            'prepare-directory',
            'completed',
            'Directory prepared for installation'
        );
    }

    /**
     * Clone the dependency repository
     */
    private function cloneDependencyRepository(string $dependency, string $modulePath): void
    {
        // Skip if user chose to keep current version
        if (isset($this->moduleData['dependencies'][$dependency]['update_choice']) && 
            $this->moduleData['dependencies'][$dependency]['update_choice'] === 'skip') {
            $this->updateSubStep(
                "clone-dependency-{$dependency}",
                'clone-repo',
                'completed',
                'Using existing dependency files'
            );
            return;
        }

        // Get dependency data from XML
        $dependencyData = $this->getModuleData($dependency);

        $repository = $dependencyData['repository'];
        $branch = $dependencyData['branch'];

        // Execute git clone command
        $command = "git clone --branch {$branch} {$repository} {$modulePath} 2>&1";
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception('Failed to clone dependency repository: ' . implode("\n", $output));
        }

        $this->updateSubStep(
            "clone-dependency-{$dependency}",
            'clone-repo',
            'completed',
            "Repository cloned successfully from branch: {$branch}"
        );
    }

    /**
     * Verify the cloned dependency
     */
    private function verifyDependencyClone(string $dependency, string $modulePath): void
    {
        // Skip verification if user chose to keep current version
        if (isset($this->moduleData['dependencies'][$dependency]['update_choice']) && 
            $this->moduleData['dependencies'][$dependency]['update_choice'] === 'skip') {
            $this->updateSubStep(
                "clone-dependency-{$dependency}",
                'verify-clone',
                'completed',
                'Using existing dependency files'
            );
            return;
        }

        if (!File::exists($modulePath . '/module.json')) {
            throw new \Exception("module.json not found in cloned dependency: {$dependency}");
        }

        // Clean up backup if exists and everything is successful
        if (isset($this->moduleData['dependencies'][$dependency]['backup_path']) && 
            File::exists($this->moduleData['dependencies'][$dependency]['backup_path'])) {
            File::deleteDirectory($this->moduleData['dependencies'][$dependency]['backup_path']);
        }

        $this->updateSubStep(
            "clone-dependency-{$dependency}",
            'verify-clone',
            'completed',
            'Cloned files verified successfully'
        );
    }

    /**
     * Initialize dependency cloning steps
     */
    private function initializeDependencyCloneSteps(): void
    {
        if (empty($this->moduleData['dependencies'])) {
            return;
        }

        foreach ($this->moduleData['dependencies'] as $dependency) {
            $this->steps["clone-dependency-{$dependency}"] = [
                'title' => "Clone Dependency Repository",
                'description' => "Cloning {$dependency} module from repository",
                'status' => 'pending',
                'confirm_message' => "This will clone the {$dependency} module repository.",
                'sub_steps' => [
                    [
                        'key' => 'prepare-directory',
                        'title' => "Prepare {$dependency} module directory",
                        'description' => "Create or clean the module directory for installation.",
                        'status' => 'pending'
                    ],
                    [
                        'key' => 'clone-repo',
                        'title' => "Clone {$dependency} module repository",
                        'description' => "Clone the module from its Git repository.",
                        'status' => 'pending'
                    ],
                    [
                        'key' => 'verify-clone',
                        'title' => "Verify cloned {$dependency} module files",
                        'description' => "Ensure all files were cloned correctly.",
                        'status' => 'pending'
                    ]
                ]
            ];
        }
    }
}
