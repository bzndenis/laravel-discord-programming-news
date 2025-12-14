<?php

return [
    'frameworks' => [
        'laravel/framework', // Composer
        'next',              // NPM
        'react',             // NPM
        'vue',               // NPM
        'axios',             // NPM Example
    ],

    'discord' => [
        'webhook_url' => env('DISCORD_SECURITY_WEBHOOK_URL'),
        'channel_id' => env('DISCORD_SECURITY_CHANNEL_ID'),
        'mention_role_id' => env('DISCORD_SECURITY_ROLE_ID'),
    ],

    'sources' => [
        'github_advisories',
        // 'nvd_feed', 
    ],
];
