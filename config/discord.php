<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Discord Bot Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Discord bot interactions and webhooks.
    |
    */

    'app_id' => env('DISCORD_APP_ID'),
    'public_key' => env('DISCORD_PUBLIC_KEY'),
    'bot_token' => env('DISCORD_BOT_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Discord Webhooks
    |--------------------------------------------------------------------------
    |
    | Webhook URLs for different notification types.
    |
    */

    'webhooks' => [
        'security' => env('DISCORD_SECURITY_WEBHOOK_URL'),
        'features' => env('DISCORD_FEATURES_WEBHOOK_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Discord Channel & Role IDs
    |--------------------------------------------------------------------------
    |
    | Channel and role IDs for mentions and targeting specific channels.
    |
    */

    'channels' => [
        'security' => env('DISCORD_SECURITY_CHANNEL_ID'),
        'features' => env('DISCORD_FEATURES_CHANNEL_ID'),
    ],

    'roles' => [
        'security_mention' => env('DISCORD_SECURITY_ROLE_ID'),
        'features_mention' => env('DISCORD_FEATURES_ROLE_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Slash Commands Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Discord slash commands.
    |
    */

    'commands' => [
        [
            'name' => 'security-update',
            'description' => 'Manually trigger security advisories scan',
            'type' => 1, // CHAT_INPUT
        ],
        [
            'name' => 'feature-update',
            'description' => 'Manually trigger feature updates scan',
            'type' => 1, // CHAT_INPUT
        ],
        [
            'name' => 'status',
            'description' => 'Check bot status and last update information',
            'type' => 1, // CHAT_INPUT
        ],
    ],
];
