<?php

namespace PanicDev\Archium\Http\Livewire\Traits;

use Illuminate\Support\Facades\File;

/**
 * Trait HandlesRepositoryOperations
 * 
 * This trait handles all repository-related operations including cloning repositories,
 * managing directories, and verifying installations for the main module.
 */
trait HandlesRepositoryOperations
{
    /**
     * Handle repository cloning sub-steps
     */
    protected function cloneRepositorySubStep(string $subStep): void
    {
        try {
            $modulePath = config('archium.modules_directory') . '/' . $this->moduleData['directory'];
            
            switch ($subStep) {
                case 'prepare-directory':
                    $this->prepareModuleDirectory($modulePath);
                    break;

                case 'clone-repo':
                    $this->cloneModuleRepository($modulePath);
                    break;

                case 'verify-clone':
                    $this->verifyClonedFiles($modulePath);
                    break;

                default:
                    throw new \Exception("Unknown clone repository sub-step: {$subStep}");
            }
        } catch (\Exception $e) {
            // Restore backup if exists and something went wrong
            if (isset($this->moduleData['backup_path']) && File::exists($this->moduleData['backup_path'])) {
                if (File::exists($modulePath)) {
                    File::deleteDirectory($modulePath);
                }
                File::move($this->moduleData['backup_path'], $modulePath);
            }
            
            $this->updateSubStep('clone-repository', $subStep, 'failed', null, $e->getMessage());
            throw $e;
        }
    }

    /**
     * Prepare the module directory for installation
     */
    private function prepareModuleDirectory(string $modulePath): void
    {
        if (File::exists($modulePath)) {
            // Backup existing directory if it exists
            $backupPath = $modulePath . '_backup_' . time();
            File::move($modulePath, $backupPath);
            $this->moduleData['backup_path'] = $backupPath;
        }
        
        // Create fresh directory
        File::makeDirectory($modulePath, 0755, true, true);
        
        $this->updateSubStep(
            'clone-repository',
            'prepare-directory',
            'completed',
            'Directory prepared for installation'
        );
    }

    /**
     * Clone the module repository
     */
    private function cloneModuleRepository(string $modulePath): void
    {
        $repository = $this->moduleData['repository'];
        $branch = $this->moduleData['branch'];
        
        // Execute git clone command
        $command = "git clone --branch {$branch} {$repository} {$modulePath} 2>&1";
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \Exception('Failed to clone repository: ' . implode("\n", $output));
        }
        
        $this->updateSubStep(
            'clone-repository',
            'clone-repo',
            'completed',
            "Repository cloned successfully from branch: {$branch}"
        );
    }

    /**
     * Verify the cloned files
     */
    private function verifyClonedFiles(string $modulePath): void
    {
        if (!File::exists($modulePath . '/module.json')) {
            throw new \Exception('module.json not found in cloned repository');
        }
        
        // Clean up backup if exists and everything is successful
        if (isset($this->moduleData['backup_path']) && File::exists($this->moduleData['backup_path'])) {
            File::deleteDirectory($this->moduleData['backup_path']);
        }
        
        $this->updateSubStep(
            'clone-repository',
            'verify-clone',
            'completed',
            'Cloned files verified successfully'
        );
    }
} 