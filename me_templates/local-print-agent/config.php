<?php

return [
    // Do not add a trailing slash.
    'api_base' => 'https://YOUR-DOMAIN.example/api/print-agent',

    // Generate on the VPS with: php artisan print-agent:token BRANCH_ID
    'api_token' => 'PASTE_BRANCH_PRINT_AGENT_TOKEN_HERE',

    'poll_seconds' => 2,
    'request_timeout_seconds' => 15,
    'claim_limit' => 10,
    'log_file' => __DIR__.'/print-agent.log',
];
