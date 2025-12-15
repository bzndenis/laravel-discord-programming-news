<?php

namespace App\Services;

use App\Models\FeatureUpdate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FeatureMonitorService
{
    public function checkFeatures(): array
    {
        Log::info('Starting feature update check...');
        $newUpdates = [];
        $repos = config('features.repos', []);

        foreach ($repos as $name => $repo) {
            $updates = $this->fetchUpdatesForRepo($name, $repo);
            foreach ($updates as $update) {
                // Deduplicate
                $hash = md5($name . $update['tag_name']);
                
                if (FeatureUpdate::where('hash', $hash)->exists()) {
                    continue;
                }

                $feature = FeatureUpdate::create([
                    'source_name' => $name,
                    'version' => $update['tag_name'],
                    'title' => $update['name'] ?? $update['tag_name'],
                    'description' => $update['body'] ?? 'No release notes provided.',
                    'url' => $update['html_url'],
                    'hash' => $hash,
                    'published_at' => Carbon::parse($update['published_at']),
                ]);

                $newUpdates[] = $feature;
                Log::info("New feature update found: {$feature->source_name} {$feature->version}");
            }
        }

        return $newUpdates;
    }

    protected function fetchUpdatesForRepo(string $name, string $repo): array
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/vnd.github+json',
                'X-GitHub-Api-Version' => '2022-11-28',
                'User-Agent' => 'Laravel-Feature-Bot'
            ])->get("https://api.github.com/repos/{$repo}/releases", [
                'per_page' => 3 
            ]);

            if ($response->failed()) {
                Log::warning("Failed to fetch releases for {$name}: " . $response->body());
                return [];
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error("Error checking features for {$name}: " . $e->getMessage());
            return [];
        }
    }
}
