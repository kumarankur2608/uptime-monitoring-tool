<?php

return [
    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'website_monitor' => [
        'timeout_seconds' => (int) env('WEBSITE_MONITOR_TIMEOUT_SECONDS', 10),
        'dispatch_chunk_size' => (int) env('WEBSITE_MONITOR_DISPATCH_CHUNK_SIZE', 100),
        'max_websites_per_client' => (int) env('WEBSITE_MONITOR_MAX_WEBSITES_PER_CLIENT', 10),
        'queue' => env('WEBSITE_MONITOR_QUEUE', 'monitoring'),
        'user_agent' => env('WEBSITE_MONITOR_USER_AGENT', env('APP_NAME', 'Laravel').' uptime bot'),
    ],
];
