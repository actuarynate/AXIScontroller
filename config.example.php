<?php
return [
    'api_base_url' => getenv('ELINK_API_BASE_URL') ?: 'https://elinkapi-ext-nprd-eus2-01-moodysdemo.insdev.moodysanalytics.com',
    'verify_tls' => filter_var(getenv('ELINK_VERIFY_TLS') ?: 'true', FILTER_VALIDATE_BOOLEAN),
    'timeout_seconds' => 60,
    'poll_interval_ms' => 3000,
];
