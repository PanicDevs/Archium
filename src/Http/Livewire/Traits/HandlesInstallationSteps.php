<?php

namespace PanicDev\Archium\Http\Livewire\Traits;

/**
 * Trait HandlesInstallationSteps
 * 
 * This trait manages the installation process steps, including their initialization,
 * state management, and progression through the installation workflow.
 */
trait HandlesInstallationSteps
{
    /**
     * Current active installation step
     */
    public string $currentStep = 'depends-check';

    /**
     * Array of all installation steps and their configurations
     */
    public array $steps = [];

    /**
     * Responses/messages for each step
     */
    public array $stepResponses = [];

    /**
     * Current active sub-step within a step
     */
    public ?string $currentSubStep = '';

    /**
     * Array of sub-steps for the current main step
     */
    public array $subSteps = [];

    /**
     * Initialize all installation steps and their configurations
     */
    protected function initializeSteps(): void
    {
        if (!$this->moduleData) {
            return;
        }

        \Log::info('Initializing steps with module data', [
            'module_key' => $this->moduleKey,
            'dependencies' => $this->moduleData['depends'] ?? []
        ]);

        $this->initializeDependencyCheckStep();
        $this->initializeVersionCheckStep();
        $this->initializeDependencySteps();
        $this->initializeRepositoryCloneStep();
        if (method_exists($this, 'initializeDependencyCloneSteps')) {
            $this->initializeDependencyCloneSteps();
        }
        $this->initializeFinalizationStep();

        // Set initial sub-steps
        $this->subSteps = $this->steps[$this->currentStep]['sub_steps'] ?? [];
        if (!empty($this->subSteps)) {
            $this->currentSubStep = $this->subSteps[0]['key'];
        }
    }

    /**
     * Initialize the dependency check step
     */
    private function initializeDependencyCheckStep(): void
    {
        $this->steps['depends-check'] = [
            'title' => 'Required Modules Check',
            'description' => 'Checking required modules',
            'status' => 'pending',
            'confirm_message' => 'This will check for required modules.',
            'sub_steps' => array_map(function($dependency) {
                \Log::debug('Creating step for dependency', [
                    'dependency' => $dependency,
                    'type' => gettype($dependency)
                ]);
                return [
                    'key' => "check-{$dependency}",
                    'title' => "Check {$dependency}",
                    'description' => "Verify if {$dependency} module is installed and enabled.",
                    'status' => 'pending'
                ];
            }, $this->moduleData['depends'] ?? [])
        ];
    }

    /**
     * Initialize the version check step
     */
    private function initializeVersionCheckStep(): void
    {
        $this->steps['version-check'] = [
            'title' => 'Version Check',
            'description' => 'Checking current and available versions',
            'status' => 'pending',
            'confirm_message' => 'This will check if the module is already installed and verify its version.',
            'sub_steps' => [
                [
                    'key' => 'check-local-existence',
                    'title' => 'Check if module exists locally',
                    'description' => 'Verify if the module is already installed in your project.',
                    'status' => 'pending'
                ],
                [
                    'key' => 'check-local-version',
                    'title' => 'Check local version',
                    'description' => 'Get the version of the currently installed module.',
                    'status' => 'pending'
                ],
                [
                    'key' => 'fetch-remote-version',
                    'title' => 'Fetch latest version from repository',
                    'description' => 'Get the latest available version from the repository.',
                    'status' => 'pending'
                ],
                [
                    'key' => 'compare-versions',
                    'title' => 'Compare versions',
                    'description' => 'Compare local and remote versions to determine if an update is needed.',
                    'status' => 'pending'
                ]
            ]
        ];
    }

    /**
     * Initialize steps for checking dependencies
     */
    private function initializeDependencySteps(): void
    {
        if (empty($this->moduleData['dependencies'])) {
            return;
        }

        foreach ($this->moduleData['dependencies'] as $dependency) {
            $this->steps["check-dependency-{$dependency}"] = [
                'title' => "Check Dependency: {$dependency}",
                'description' => "Verifying dependency {$dependency}",
                'status' => 'pending',
                'confirm_message' => "This will check dependency {$dependency}.",
                'sub_steps' => [
                    [
                        'key' => "check-local-{$dependency}",
                        'title' => "Check if {$dependency} exists locally",
                        'description' => "Verify if {$dependency} is already installed.",
                        'status' => 'pending'
                    ],
                    [
                        'key' => "check-local-version-{$dependency}",
                        'title' => "Check {$dependency} local version",
                        'description' => "Get the version of the currently installed dependency.",
                        'status' => 'pending'
                    ],
                    [
                        'key' => "fetch-remote-version-{$dependency}",
                        'title' => "Fetch {$dependency} latest version",
                        'description' => "Get the latest available version from repository.",
                        'status' => 'pending'
                    ],
                    [
                        'key' => "compare-versions-{$dependency}",
                        'title' => "Compare {$dependency} versions",
                        'description' => "Compare local and remote versions.",
                        'status' => 'pending'
                    ]
                ]
            ];
        }
    }

    /**
     * Initialize the repository clone step
     */
    private function initializeRepositoryCloneStep(): void
    {
        $this->steps['clone-repository'] = [
            'title' => 'Clone Repository',
            'description' => 'Cloning module from repository',
            'status' => 'pending',
            'confirm_message' => 'This will clone the module repository. Any existing files will be overwritten.',
            'sub_steps' => [
                [
                    'key' => 'prepare-directory',
                    'title' => 'Prepare module directory',
                    'description' => 'Create or clean the module directory for installation.',
                    'status' => 'pending'
                ],
                [
                    'key' => 'clone-repo',
                    'title' => 'Clone repository',
                    'description' => 'Clone the module from its Git repository.',
                    'status' => 'pending'
                ],
                [
                    'key' => 'verify-clone',
                    'title' => 'Verify cloned files',
                    'description' => 'Ensure all files were cloned correctly.',
                    'status' => 'pending'
                ]
            ]
        ];
    }

    /**
     * Initialize steps for installing dependencies
     */
    private function initializeDependencyInstallationSteps(): void
    {
        if (empty($this->moduleData['dependencies'])) {
            return;
        }

        foreach ($this->moduleData['dependencies'] as $dependency) {
            $this->steps["install-dependency-{$dependency}"] = [
                'title' => "Install Dependency: {$dependency}",
                'description' => "Installing dependency {$dependency}",
                'status' => 'pending',
                'confirm_message' => "This will install the dependency {$dependency}.",
                'sub_steps' => [
                    [
                        'key' => 'check-existence',
                        'title' => 'Check if dependency exists',
                        'description' => "Verify if {$dependency} is already installed.",
                        'status' => 'pending'
                    ],
                    [
                        'key' => 'check-version',
                        'title' => 'Check dependency version',
                        'description' => "Check the version of {$dependency}.",
                        'status' => 'pending'
                    ],
                    [
                        'key' => 'install',
                        'title' => 'Install dependency',
                        'description' => "Install or update {$dependency} to the required version.",
                        'status' => 'pending'
                    ]
                ]
            ];
        }
    }

    /**
     * Initialize the finalization step
     */
    private function initializeFinalizationStep(): void
    {
        $this->steps['finalize'] = [
            'title' => 'Finalize Installation',
            'description' => 'Completing module installation',
            'status' => 'pending',
            'confirm_message' => 'This will finalize the module installation.',
            'sub_steps' => [
                [
                    'key' => 'enable-modules',
                    'title' => 'Enable modules',
                    'description' => 'Enable the main module and its dependencies.',
                    'status' => 'pending'
                ],
                [
                    'key' => 'verify-installation',
                    'title' => 'Verify installation',
                    'description' => 'Ensure all components are installed correctly.',
                    'status' => 'pending'
                ],
                [
                    'key' => 'generate-report',
                    'title' => 'Generate installation report',
                    'description' => 'Create a summary of all installation steps and their outcomes.',
                    'status' => 'pending'
                ]
            ]
        ];
    }

    /**
     * Get the next step in the installation process
     */
    protected function getNextStep(string $currentStep): ?string
    {
        $steps = array_keys($this->steps);
        $currentIndex = array_search($currentStep, $steps);

        return isset($steps[$currentIndex + 1]) ? $steps[$currentIndex + 1] : null;
    }

    /**
     * Get the next sub-step within the current step
     */
    protected function getNextSubStep(string $step, string $currentSubStep): ?string
    {
        $subSteps = array_column($this->steps[$step]['sub_steps'], 'key');
        $currentIndex = array_search($currentSubStep, $subSteps);

        return isset($subSteps[$currentIndex + 1]) ? $subSteps[$currentIndex + 1] : null;
    }
} 