<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RegisterDiscordCommands extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discord:register-commands
                            {--guild= : Register commands to a specific guild (faster for testing)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register Discord slash commands to Discord API';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $appId = config('discord.app_id');
        $botToken = config('discord.bot_token');
        $commands = config('discord.commands', []);

        if (!$appId || !$botToken) {
            $this->error('Discord App ID or Bot Token not configured.');
            $this->info('Please set DISCORD_APP_ID and DISCORD_BOT_TOKEN in your .env file.');
            return self::FAILURE;
        }

        if (empty($commands)) {
            $this->error('No commands configured in config/discord.php');
            return self::FAILURE;
        }

        $this->info('Registering ' . count($commands) . ' Discord slash commands...');

        // Determine URL based on guild or global registration
        $guildId = $this->option('guild');
        if ($guildId) {
            $url = "https://discord.com/api/v10/applications/{$appId}/guilds/{$guildId}/commands";
            $this->info("Registering to guild: {$guildId}");
        } else {
            $url = "https://discord.com/api/v10/applications/{$appId}/commands";
            $this->info("Registering globally (may take up to 1 hour to propagate)");
        }

        try {
            // Register each command
            foreach ($commands as $command) {
                $this->info("Registering command: /{$command['name']}");

                $response = Http::withHeaders([
                    'Authorization' => "Bot {$botToken}",
                    'Content-Type' => 'application/json',
                ])->post($url, $command);

                if ($response->successful()) {
                    $this->info("✓ Successfully registered: /{$command['name']}");
                } else {
                    $this->error("✗ Failed to register: /{$command['name']}");
                    $this->error("Response: " . $response->body());
                    Log::error('Discord command registration failed', [
                        'command' => $command['name'],
                        'response' => $response->body(),
                    ]);
                }
            }

            $this->newLine();
            $this->info('✓ Discord commands registration complete!');
            $this->newLine();
            $this->info('Next steps:');
            $this->info('1. Set up Interactions Endpoint URL in Discord Developer Portal');
            $this->info('   URL: https://your-domain.com/api/discord/interactions');
            $this->info('2. Save the endpoint URL in Discord settings');
            $this->info('3. Test the commands in your Discord server');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to register commands: ' . $e->getMessage());
            Log::error('Discord command registration exception', [
                'error' => $e->getMessage(),
            ]);
            return self::FAILURE;
        }
    }
}
