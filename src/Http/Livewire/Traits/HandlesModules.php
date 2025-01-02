<?php

namespace PanicDev\Archium\Http\Livewire\Traits;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Artisan;

trait HandlesModules
{
    /**
     * Whether the Laravel Modules package is installed.
     */
    public bool $modulePackageInstalled = false;

    /**
     * Whether the config file has been published.
     */
    public bool $configPublished = false;

    /**
     * The total number of modules installed.
     */
    public int $totalModules = 0;

    /**
     * The number of active modules.
     */
    public int $activeModules = 0;

    /**
     * Whether a config update is available.
     */
    public bool $updateAvailable = false;

    /**
     * The current version from the local config.
     */
    public ?string $currentVersion = null;

    /**
     * The latest version from the repository.
     */
    public ?string $latestVersion = null;

    /**
     * The URL to the config file in the GitHub repository.
     */
    protected function getConfigUrl(): string
    {
        return config('archium.repository.config_url', 'https://raw.githubusercontent.com/PanicDevs/ArchiumSettings/main/nwidart-laravel-modules-config-file.php');
    }

    /**
     * Check the status of Laravel Modules installation and configuration.
     */
    public function checkStatus(): void
    {
        // Check if Laravel Modules is installed
        $composerJson = json_decode(file_get_contents(base_path('composer.json')), true);
        $this->modulePackageInstalled = isset($composerJson['require']['nwidart/laravel-modules']);

        // Check if config is published
        $this->configPublished = file_exists(config_path('modules.php'));

        // Count modules if package is installed
        if ($this->modulePackageInstalled) {
            $modulesPath = base_path('Modules');
            if (is_dir($modulesPath)) {
                $this->totalModules = count(array_filter(scandir($modulesPath), function ($item) use ($modulesPath) {
                    return is_dir($modulesPath . '/' . $item) && !in_array($item, ['.', '..']);
                }));

                // Count active modules
                $this->activeModules = $this->totalModules; // For now, assume all modules are active
            }
        }

        // Check config version if published
        if ($this->configPublished) {
            $this->checkConfigVersion();
        }
    }

    /**
     * Check if a config update is available.
     */
    public function checkConfigVersion(): void
    {
        try {
            Log::info('Starting checkConfigVersion...');
            
            // Get current version
            $this->currentVersion = config('modules.archium_version');
            Log::info('Current version check:', [
                'raw_value' => config('modules.archium_version'),
                'current_version' => $this->currentVersion,
                'config_exists' => file_exists(config_path('modules.php')),
                'config_content' => file_exists(config_path('modules.php')) ? file_get_contents(config_path('modules.php')) : null,
                'updateAvailable' => $this->updateAvailable
            ]);

            // Reset update status if current version is not set
            if (empty($this->currentVersion)) {
                Log::warning('Current version is empty, disabling update check', [
                    'updateAvailable' => $this->updateAvailable
                ]);
                $this->updateAvailable = false;
                $this->latestVersion = null;
                return;
            }

            // Get latest version from repository
            Log::info('Fetching latest version from repository...');
            $response = Http::get($this->getConfigUrl());
            if ($response->successful()) {
                $content = $response->body();
                Log::info('Fetched config content successfully');

                // Extract version using a more precise pattern
                if (preg_match("/'archium_version'\s*=>\s*'([\d\.]+)'/", $content, $matches) ||
                    preg_match('/"archium_version"\s*=>\s*"([\d\.]+)"/', $content, $matches)) {
                    $this->latestVersion = $matches[1];
                    
                    Log::info('Version comparison:', [
                        'current' => $this->currentVersion,
                        'latest' => $this->latestVersion,
                        'comparison_result' => version_compare($this->latestVersion, $this->currentVersion, '>')
                    ]);
                    
                    // Only set updateAvailable if both versions are valid and there's a newer version
                    $this->updateAvailable = !empty($this->currentVersion) && 
                                           !empty($this->latestVersion) && 
                                           version_compare($this->latestVersion, $this->currentVersion, '>');
                    
                    Log::info('Final version check result:', [
                        'current' => $this->currentVersion,
                        'latest' => $this->latestVersion,
                        'update_available' => $this->updateAvailable
                    ]);
                } else {
                    Log::warning('Failed to extract version from config content', [
                        'content' => $content
                    ]);
                    $this->updateAvailable = false;
                    $this->latestVersion = null;
                }
            } else {
                Log::error('Failed to fetch config file', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                $this->updateAvailable = false;
                $this->latestVersion = null;
            }
        } catch (\Exception $e) {
            Log::error('Failed to check config version', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->updateAvailable = false;
            $this->latestVersion = null;
        }
    }

    /**
     * Force update the config file from GitHub.
     */
    public function forceUpdateConfig(): void
    {
        try {
            Log::info('Starting config file force update...');

            // Backup existing config
            $configPath = config_path('modules.php');
            $backupPath = config_path('modules.php.backup');

            if (File::exists($configPath)) {
                Log::info('Backing up existing config file', [
                    'source' => $configPath,
                    'backup' => $backupPath
                ]);
                File::copy($configPath, $backupPath);
            }

            // Get latest config from GitHub
            $response = Http::get($this->getConfigUrl());

            if (!$response->successful()) {
                Log::error('Failed to get config file from GitHub', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                // Restore backup if it exists
                if (File::exists($backupPath)) {
                    Log::info('Restoring config backup after failed download');
                    File::move($backupPath, $configPath);
                }

                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Failed to update config: HTTP ' . $response->status()
                ]);

                $this->dispatch('close-modal');
                return;
            }

            // Write the config file
            Log::info('Writing new config file...');
            File::put($configPath, $response->body());

            // Clean up backup
            if (File::exists($backupPath)) {
                Log::info('Cleaning up backup file');
                File::delete($backupPath);
            }

            // Clear config cache and reload
            Log::info('Refreshing configuration...');
            Artisan::call('config:clear');
            
            // Reset version variables before reloading config
            $this->updateAvailable = false;
            $this->currentVersion = null;
            $this->latestVersion = null;

            // Force a fresh config load
            $freshConfig = require config_path('modules.php');
            app('config')->set('modules', $freshConfig);
            
            // Set current version from fresh config
            $this->currentVersion = $freshConfig['archium_version'] ?? null;
            
            Log::info('Config refreshed with new values:', [
                'new_version' => $this->currentVersion,
                'updateAvailable' => $this->updateAvailable
            ]);

            // Show success message
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Config has been updated successfully!'
            ]);

            $this->dispatch('close-modal');

            Log::info('Config file update completed successfully');
        } catch (\Exception $e) {
            Log::error('Exception during config update', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Restore backup if it exists
            if (File::exists($backupPath)) {
                Log::info('Restoring config backup after exception');
                File::move($backupPath, $configPath);
            }

            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);

            $this->dispatch('close-modal');
        }
    }

    /**
     * Install the Laravel Modules package.
     */
    public function installLaravelModules(): void
    {
        try {
            Log::info('Starting Laravel Modules installation...');

            // Backup composer.json
            $composerJsonPath = base_path('composer.json');
            $backupPath = base_path('composer.json.backup');

            Log::info('Backing up composer.json', [
                'source' => $composerJsonPath,
                'backup' => $backupPath
            ]);

            File::copy($composerJsonPath, $backupPath);

            // Run composer require
            $command = PHP_BINARY . ' ' . base_path('vendor/bin/composer') . ' require nwidart/laravel-modules';
            Log::info('Running composer command', ['command' => $command]);

            $result = Process::env([
                    'COMPOSER_HOME' => base_path('vendor/bin'),
                    'PATH' => getenv('PATH')
                ])
                ->path(base_path())
                ->timeout(300) // 5 minutes timeout
                ->run($command);

            if (!$result->successful()) {
                Log::error('Composer command failed', [
                    'output' => $result->output(),
                    'error' => $result->errorOutput(),
                    'exit_code' => $result->exitCode()
                ]);

                // Restore backup if installation failed
                if (File::exists($backupPath)) {
                    Log::info('Restoring composer.json backup after failed installation');
                    File::move($backupPath, $composerJsonPath);
                }

                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Failed to install Laravel Modules: ' . $result->errorOutput()
                ]);

                // Close modal even on error
                $this->dispatch('close-modal');
                return;
            }

            // Clean up backup
            if (File::exists($backupPath)) {
                Log::info('Cleaning up backup file');
                File::delete($backupPath);
            }

            // Update status
            $this->checkStatus();

            // Show success message
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Laravel Modules has been installed successfully!'
            ]);

            // Close modal after success
            $this->dispatch('close-modal');

            Log::info('Laravel Modules installation completed successfully');
        } catch (\Exception $e) {
            Log::error('Exception during installation', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Restore backup if it exists
            if (File::exists($backupPath)) {
                Log::info('Restoring composer.json backup after exception');
                File::move($backupPath, $composerJsonPath);
            }

            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);

            // Close modal even on exception
            $this->dispatch('close-modal');
        }
    }

    /**
     * Get the update type and its associated styles.
     */
    public function getUpdateTypeProperty(): array
    {
        if (!$this->currentVersion || !$this->latestVersion) {
            return [
                'type' => 'Patch',
                'class' => 'bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400',
                'icon_class' => 'text-success-600 dark:text-success-400',
                'icon_bg' => 'bg-success-100 dark:bg-success-500/10',
                'button_class' => 'bg-success-600 hover:bg-success-500 dark:bg-success-500 dark:hover:bg-success-400'
            ];
        }

        // Split versions into parts
        $current = array_map('intval', explode('.', $this->currentVersion));
        $latest = array_map('intval', explode('.', $this->latestVersion));
        
        // Determine update type
        return match(true) {
            $latest[0] > $current[0] => [
                'type' => 'Major',
                'class' => 'bg-danger-50 text-danger-600 dark:bg-danger-500/10 dark:text-danger-400',
                'icon_class' => 'text-danger-600 dark:text-danger-400',
                'icon_bg' => 'bg-danger-100 dark:bg-danger-500/10',
                'button_class' => 'bg-danger-600 hover:bg-danger-500 dark:bg-danger-500 dark:hover:bg-danger-400'
            ],
            $latest[1] > $current[1] => [
                'type' => 'Minor',
                'class' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-400',
                'icon_class' => 'text-warning-600 dark:text-warning-400',
                'icon_bg' => 'bg-warning-100 dark:bg-warning-500/10',
                'button_class' => 'bg-warning-600 hover:bg-warning-500 dark:bg-warning-500 dark:hover:bg-warning-400'
            ],
            default => [
                'type' => 'Patch',
                'class' => 'bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400',
                'icon_class' => 'text-success-600 dark:text-success-400',
                'icon_bg' => 'bg-success-100 dark:bg-success-500/10',
                'button_class' => 'bg-success-600 hover:bg-success-500 dark:bg-success-500 dark:hover:bg-success-400'
            ],
        };
    }

    /**
     * Publish the config file from GitHub.
     */
    public function publishConfig(): void
    {
        try {
            Log::info('Starting config file publication...');

            // Get config file content from GitHub
            $response = Http::get($this->getConfigUrl());

            if (!$response->successful()) {
                Log::error('Failed to get config file from GitHub', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Failed to get config file: HTTP ' . $response->status()
                ]);
                
                $this->dispatch('close-modal');
                return;
            }

            // Write the config file
            $configPath = config_path('modules.php');
            File::put($configPath, $response->body());

            // Clear config cache
            Artisan::call('config:clear');

            // Update status
            $this->checkStatus();

            // Show success message
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Config file has been published successfully!'
            ]);

            $this->dispatch('close-modal');

            Log::info('Config file publication completed successfully');
        } catch (\Exception $e) {
            Log::error('Exception during config publication', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);

            $this->dispatch('close-modal');
        }
    }
}
