<?php

namespace App\Services;

use App\Models\SecurityAdvisory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SecurityMonitorService
{
    /**
     * Scan all sources for new advisories.
     *
     * @return \App\Models\SecurityAdvisory[]
     */
    public function scan(): array
    {
        Log::info('Starting security scan...');
        $newAdvisories = [];

        // Fetch real data from GitHub API
        $items = $this->fetchGithubAdvisories();

        foreach ($items as $item) {
            // FILTERING LOGIC: Only processed if Critical or High logic
            if (!$this->matchesCriteria($item)) {
                continue;
            }

            // Create a unique hash to prevent duplicates
            // We use framework, cve, and title to ensure uniqueness
            $hash = md5($item['framework'] . ($item['cve_id'] ?? '') . $item['title']);

            if (SecurityAdvisory::where('hash', $hash)->exists()) {
                continue;
            }

            $advisory = SecurityAdvisory::create([
                'framework_name' => $item['framework'],
                'cve_id' => $item['cve_id'] ?? null,
                'severity' => strtoupper($item['severity']),
                'title' => $item['title'],
                'description' => $item['description'],
                'reference_url' => $item['url'],
                'hash' => $hash,
                'published_at' => isset($item['published_at']) ? \Carbon\Carbon::parse($item['published_at']) : now(),
            ]);

            $newAdvisories[] = $advisory;
            Log::info("New security advisory found: {$advisory->title}");
        }

        return $newAdvisories;
    }

    protected function matchesCriteria(array $item): bool
    {
        $severity = strtoupper($item['severity']);
        // Normalize GitHub severity to our standard
        
        if (in_array($severity, ['CRITICAL', 'HIGH'])) {
            return true;
        }

        return false;
    }

    protected function fetchGithubAdvisories(): array
    {
        $frameworks = config('security.frameworks', []);
        $results = [];

        foreach ($frameworks as $framework) {
            // Attempt to guess package name from repo name (e.g. laravel/framework -> laravel/framework, vercel/next.js -> next)
            // Ideally config should have precise package names. 
            // For this implementation, we will try to use the configured name as the package name filter.
            
            // We only query for High and Critical to save resources if API supports, 
            // but GitHub API 'severity' param is specific. We'll fetch recent ones.
            
            try {
                $response = Http::withHeaders([
                    'Accept' => 'application/vnd.github+json',
                    'X-GitHub-Api-Version' => '2022-11-28',
                    'User-Agent' => 'Laravel-Security-Bot' // GitHub requires UA
                ])->get('https://api.github.com/advisories', [
                    'affects' => $framework,
                    'per_page' => 5, // Keep it light
                    'sort' => 'published',
                    'direction' => 'desc'
                ]);

                if ($response->failed()) {
                    Log::error("GitHub API failed for {$framework}: " . $response->body());
                    continue;
                }

                $advisories = $response->json();

                foreach ($advisories as $advisory) {
                    $results[] = [
                        'framework' => $framework,
                        'cve_id' => $advisory['cve_id'] ?? $advisory['ghsa_id'],
                        'severity' => $advisory['severity'],
                        'title' => $advisory['summary'],
                        'description' => $advisory['description'],
                        'url' => $advisory['html_url'],
                        'published_at' => $advisory['published_at'],
                        'tags' => [] 
                    ];
                }

            } catch (\Exception $e) {
                Log::error("Error processing {$framework}: " . $e->getMessage());
            }
        }

        return $results;
    }
}
