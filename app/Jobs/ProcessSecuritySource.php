<?php

namespace App\Jobs;

use App\Services\SecurityMonitorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessSecuritySource implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $source
    ) {}

    /**
     * Execute the job.
     */
    public function handle(SecurityMonitorService $service): void
    {
        Log::info("Processing security source: {$this->source}");
        
        // In a real scenario, you'd pass $this->source to the scan method to only scan that source.
        // $advisories = $service->scan($this->source);
        $advisories = $service->scan(); 

        $webhookUrl = config('security.discord.webhook_url');

        if (!$webhookUrl) {
            Log::warning('Discord Webhook URL not configured.');
            return;
        }

        foreach ($advisories as $advisory) {
             $severityEmoji = $advisory->severity === 'CRITICAL' ? '🔴' : '🟠';
             $color = $advisory->severity === 'CRITICAL' ? 15548997 : 15105570; // Red : Orange

             $payload = [
                'username' => 'Security Monitor', // Custom bot name
                'avatar_url' => 'https://cdn-icons-png.flaticon.com/512/2881/2881142.png', // Optional: Security shield icon
                'content' => config('security.discord.mention_role_id') 
                    ? "<@&" . config('security.discord.mention_role_id') . ">" 
                    : "",
                'embeds' => [[
                    'title' => "{$severityEmoji} {$advisory->framework_name}: {$advisory->title}",
                    'description' => $advisory->description,
                    'url' => $advisory->reference_url,
                    'color' => $color,
                    'fields' => [
                        [
                            'name' => 'Severity', 
                            'value' => "**{$advisory->severity}**", 
                            'inline' => true
                        ],
                        [
                            'name' => 'CVE ID', 
                            'value' => $advisory->cve_id ?? 'N/A', 
                            'inline' => true
                        ],
                    ],
                    'footer' => [
                        'text' => 'Security Monitor • ' . $advisory->published_at?->format('Y-m-d H:i') ?? now()->format('Y-m-d')
                    ]
                ]]
             ];

             try {
                 Http::post($webhookUrl, $payload);
                 Log::info("Sent Discord notification for {$advisory->cve_id}");
             } catch (\Exception $e) {
                 Log::error("Failed to send Discord notification: " . $e->getMessage());
             }
        }
        
        Cache::put('bot_last_scan_completed', now());
    }
}
