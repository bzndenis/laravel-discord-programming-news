<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SecurityAdvisory;
use Illuminate\Support\Facades\DB;

class DebugSecurity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:security {--reset : Truncate the advisories table} {--check : Check current count}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug security advisories state';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('reset')) {
            if ($this->confirm('Are you sure you want to delete ALL security advisories? This will allow them to be re-fetched and re-posted.')) {
                SecurityAdvisory::truncate();
                $this->info('Security advisories table truncated.');
            }
        }

        $count = SecurityAdvisory::count();
        $this->info("Current SecurityAdvisory count: {$count}");

        $latest = SecurityAdvisory::latest('created_at')->first();
        if ($latest) {
            $this->info("Latest advisory: {$latest->title} (Created: {$latest->created_at})");
        }
    }
}
