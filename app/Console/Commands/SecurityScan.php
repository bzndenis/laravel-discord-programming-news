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

        $this->info('Starting security scan...');

        foreach ($sources as $source) {
            $this->info("Scanning source: {$source}");
            
            try {
                // Run directly instead of queueing to ensure it executes
                $service = app(\App\Services\SecurityMonitorService::class);
                $advisories = $service->scan();
                
                // Send to Discord if webhook is configured
                $webhookUrl = config('security.discord.webhook_url');
                if ($webhookUrl && !empty($advisories)) {
                    $this->info("Sending " . count($advisories) . " advisories to Discord...");
                    $job = new \App\Jobs\ProcessSecuritySource($source);
                    // Only send notifications, scan already done
                    foreach ($advisories as $advisory) {
                        $this->sendDiscordNotification($advisory, $webhookUrl);
                    }
                }
                
                // Update last scan cache
                \Illuminate\Support\Facades\Cache::put('bot_last_scan_completed', now());
                
                $this->info("✓ Completed scan for: {$source} - Found " . count($advisories) . " new advisories");
            } catch (\Exception $e) {
                $this->error("✗ Failed to scan {$source}: " . $e->getMessage());
                \Illuminate\Support\Facades\Log::error("Security scan failed for {$source}: " . $e->getMessage());
            }
        }
        
        $this->info('Security scan completed.');
    }
    
    protected function sendDiscordNotification($advisory, $webhookUrl): void
    {
        $severityEmoji = $advisory->severity === 'CRITICAL' ? '🔴' : '🟠';
        $color = $advisory->severity === 'CRITICAL' ? 15548997 : 15105570;

        $payload = [
            'username' => 'Security Monitor',
            'avatar_url' => 'https://cdn-icons-png.flaticon.com/512/2881/2881142.png',
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
            \Illuminate\Support\Facades\Http::post($webhookUrl, $payload);
            \Illuminate\Support\Facades\Log::info("Sent Discord notification for {$advisory->cve_id}");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to send Discord notification: " . $e->getMessage());
        }
    }
}
