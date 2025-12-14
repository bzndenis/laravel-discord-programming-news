<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SecurityScan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:scan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan configured sources for security advisories and report to Discord';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $sources = config('security.sources', ['github_advisories']);

        $this->info('Dispatching security scan jobs...');

        foreach ($sources as $source) {
            \App\Jobs\ProcessSecuritySource::dispatch($source);
            $this->info("Dispatched scan for: {$source}");
        }
    }
}
