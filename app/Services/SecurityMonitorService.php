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
            try {
                // GitHub Security Advisories API - fetch all recent advisories
                // Then filter by package name in the vulnerabilities array
                $response = Http::timeout(30)->withHeaders([
                    'Accept' => 'application/vnd.github+json',
                    'X-GitHub-Api-Version' => '2022-11-28',
                    'User-Agent' => 'Laravel-Security-Bot'
                ])->get('https://api.github.com/advisories', [
                    'per_page' => 30, // Get more to filter by package
                    'sort' => 'published',
                    'direction' => 'desc'
                ]);

                if ($response->failed()) {
                    Log::error("GitHub API failed for {$framework}: Status " . $response->status() . " - " . $response->body());
                    continue;
                }

                $advisories = $response->json();
                
                if (!is_array($advisories)) {
                    Log::warning("GitHub API returned non-array response for {$framework}");
                    continue;
                }

                foreach ($advisories as $advisory) {
                    // Check if this advisory affects our framework
                    $affectsFramework = false;
                    
                    if (isset($advisory['vulnerabilities']) && is_array($advisory['vulnerabilities'])) {
                        foreach ($advisory['vulnerabilities'] as $vuln) {
                            $package = $vuln['package']['name'] ?? '';
                            $ecosystem = $vuln['package']['ecosystem'] ?? '';
                            
                            // Match framework name (case-insensitive)
                            if (stripos($package, $framework) !== false || 
                                stripos($framework, $package) !== false ||
                                ($ecosystem === 'npm' && in_array($framework, ['next', 'react', 'vue', 'axios']) && 
                                 stripos($package, $framework) !== false)) {
                                $affectsFramework = true;
                                break;
                            }
                        }
                    }
                    
                    // Also check if framework name appears in summary or description
                    if (!$affectsFramework) {
                        $summary = strtolower($advisory['summary'] ?? '');
                        $description = strtolower($advisory['description'] ?? '');
                        $frameworkLower = strtolower($framework);
                        
                        if (stripos($summary, $frameworkLower) !== false || 
                            stripos($description, $frameworkLower) !== false) {
                            $affectsFramework = true;
                        }
                    }

                    if (!$affectsFramework) {
                        continue;
                    }

                    $results[] = [
                        'framework' => $framework,
                        'cve_id' => $advisory['cve_id'] ?? $advisory['ghsa_id'] ?? null,
                        'severity' => $advisory['severity'] ?? 'MODERATE',
                        'title' => $advisory['summary'] ?? 'No title',
                        'description' => $advisory['description'] ?? '',
                        'url' => $advisory['html_url'] ?? '',
                        'published_at' => $advisory['published_at'] ?? now()->toIso8601String(),
                        'tags' => []
                    ];
                }

                Log::info("Found " . count($results) . " advisories for {$framework}");

            } catch (\Exception $e) {
                Log::error("Error processing {$framework}: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString());
            }
        }

        return $results;
    }
}
