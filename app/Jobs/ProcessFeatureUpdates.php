<?php

namespace App\Jobs;

use App\Services\FeatureMonitorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProcessFeatureUpdates implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(FeatureMonitorService $service): void
    {
        $updates = $service->checkFeatures();
        
        if (empty($updates)) {
            return;
        }

        $webhookUrl = config('features.discord.webhook_url'); // Can use same as security if configured

        if (!$webhookUrl) {
            Log::warning('Discord Webhook URL not configured for features.');
            return;
        }

        foreach ($updates as $update) {
             // Green for features/releases
             $color = 5763719; 

             // Truncate description if too long for Discord Embed (limit is 4096 but practically 2000 is safer)
             $description = Str::limit($update->description, 1000, "...\n\n[Read more]({$update->url})");

             $payload = [
                'username' => 'Tech News Bot',
                'avatar_url' => 'https://cdn-icons-png.flaticon.com/512/3209/3209074.png', // Rocket icon
                'embeds' => [[
                    'title' => "🚀 New {$update->source_name} Release: {$update->version}",
                    'description' => $description,
                    'url' => $update->url,
                    'color' => $color,
                    'fields' => [
                        [
                            'name' => 'Version', 
                            'value' => "`{$update->version}`", 
                            'inline' => true
                        ],
                        [
                            'name' => 'Published', 
                            'value' => $update->published_at->format('Y-m-d H:i'), 
                            'inline' => true
                        ],
                    ],
                    'footer' => [
                        'text' => 'Feature Monitor • ' . now()->format('Y-m-d')
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
    }
}
