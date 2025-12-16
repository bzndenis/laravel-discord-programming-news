<?php

use App\Services\DiscordInteractionService;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing Discord Configuration...\n\n";

// Test config
$appId = config('discord.app_id');
$publicKey = config('discord.public_key');
$botToken = config('discord.bot_token');

echo "✓ App ID: " . ($appId ? substr($appId, 0, 10) . "..." : "NOT SET") . "\n";
echo "✓ Public Key: " . ($publicKey ? substr($publicKey, 0, 10) . "..." : "NOT SET") . "\n";
echo "✓ Bot Token: " . ($botToken ? substr($botToken, 0, 10) . "..." : "NOT SET") . "\n\n";

if (!$appId || !$publicKey || !$botToken) {
    echo "❌ Missing Discord credentials!\n";
    exit(1);
}

echo "✓ All credentials configured!\n\n";

// Test Discord API connection
echo "Testing Discord API connection...\n";

try {
    $response = Illuminate\Support\Facades\Http::withHeaders([
        'Authorization' => "Bot {$botToken}",
    ])->get("https://discord.com/api/v10/applications/{$appId}/commands");

    if ($response->successful()) {
        $commands = $response->json();
        echo "✓ Successfully connected to Discord API!\n";
        echo "✓ Found " . count($commands) . " registered commands\n\n";
        
        if (count($commands) > 0) {
            echo "Registered commands:\n";
            foreach ($commands as $cmd) {
                echo "  - /" . $cmd['name'] . ": " . $cmd['description'] . "\n";
            }
        }
    } else {
        echo "❌ Failed to connect: " . $response->status() . "\n";
        echo $response->body() . "\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n✓ Discord bot is ready!\n";
echo "\nNext steps:\n";
echo "1. Set Interactions Endpoint in Discord Developer Portal\n";
echo "2. Test commands in your Discord server\n";
