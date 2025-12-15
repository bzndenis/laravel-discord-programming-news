<?php

return [
    'repos' => [
        'Laravel' => 'laravel/framework',
        'Node.js' => 'nodejs/node',
        'Next.js' => 'vercel/next.js',
        'Go' => 'golang/go',
        'MySQL' => 'mysql/mysql-server', // Often uses tags
        'PostgreSQL' => 'postgres/postgres', // Often uses tags
        'PHP' => 'php/php-src',
    ],

    // Discord configuration (uses same env vars as security, but can be separate if needed)
    'discord' => [
        'webhook_url' => env('DISCORD_SECURITY_WEBHOOK_URL'), // Reusing for now
        'role_id' => env('DISCORD_SECURITY_ROLE_ID'),
    ],
];
