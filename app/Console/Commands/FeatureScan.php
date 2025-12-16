<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FeatureScan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'features:scan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan for new feature releases (Laravel, Node, etc)';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Dispatching feature scan job...');
        \App\Jobs\ProcessFeatureUpdate::dispatch();
        $this->info('Job dispatched.');
    }
}
