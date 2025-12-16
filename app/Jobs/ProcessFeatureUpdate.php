<?php

namespace App\Jobs;

use App\Services\FeatureMonitorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessFeatureUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct() {}

    /**
     * Execute the job.
     */
    public function handle(FeatureMonitorService $service): void
    {
        Log::info("Processing feature updates...");
        
        $updates = $service->checkFeatures();
        
        Log::info("Feature scan complete. Found " . count($updates) . " new updates.");

        if (empty($updates)) {
            Log::info("No new feature updates to process.");
            Cache::put('bot_last_feature_scan_completed', now());
            return;
        }

        $webhookUrl = config('discord.webhooks.features') ?? config('discord.webhooks.security');

        if (!$webhookUrl) {
            Log::warning('Discord Webhook URL not configured for features.');
            Cache::put('bot_last_feature_scan_completed', now());
            return;
        }

        foreach ($updates as $update) {
            $payload = [
                'username' => 'Feature Monitor',
                'avatar_url' => 'https://cdn-icons-png.flaticon.com/512/2165/2165004.png', // Rocket icon
                'content' => config('discord.roles.features_mention') 
                    ? "<@&" . config('discord.roles.features_mention') . ">" 
                    : "",
                'embeds' => [[
                    'title' => "🚀 {$update->source_name}: {$update->version}",
                    'description' => $this->truncateDescription($update->description),
                    'url' => $update->url,
                    'color' => 5763719, // Green
                    'fields' => [
                        [
                            'name' => 'Version', 
                            'value' => "**{$update->version}**", 
                            'inline' => true
                        ],
                        [
                            'name' => 'Source', 
                            'value' => $update->source_name, 
                            'inline' => true
                        ],
                    ],
                    'footer' => [
                        'text' => 'Feature Monitor • ' . $update->published_at?->format('Y-m-d H:i') ?? now()->format('Y-m-d')
                    ]
                ]]
            ];

            try {
                Http::post($webhookUrl, $payload);
                Log::info("Sent Discord notification for {$update->source_name} {$update->version}");
            } catch (\Exception $e) {
                Log::error("Failed to send Discord notification: " . $e->getMessage());
            }
        }
        
        Cache::put('bot_last_feature_scan_completed', now());
    }

    /**
     * Truncate description to fit Discord embed limits.
     */
    protected function truncateDescription(?string $description): string
    {
        if (!$description) {
            return 'No release notes provided.';
        }

        $maxLength = 500;
        if (strlen($description) > $maxLength) {
            return substr($description, 0, $maxLength) . '...';
        }

        return $description;
    }
}
