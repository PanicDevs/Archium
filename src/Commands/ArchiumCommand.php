<?php

namespace PanicDev\Archium\Commands;

use Illuminate\Console\Command;

class ArchiumCommand extends Command
{
    public $signature = 'archium:setup';

    public $description = 'Setup Archium package';

    public function handle(): int
    {
        $this->comment('Archium setup completed.');

        return self::SUCCESS;
    }
} 