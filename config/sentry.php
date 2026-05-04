<?php

return [
    'dsn' => env('SENTRY_LARAVEL_DSN', ''),
    'traces_sample_rate' => env('APP_ENV') === 'production' ? 0.1 : 0.0,
    'profiles_sample_rate' => 0.0,
    'send_default_pii' => false,
    'environment' => env('APP_ENV', 'production'),
];
