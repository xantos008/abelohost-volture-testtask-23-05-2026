<?php

declare(strict_types=1);

use app\Core\Env;

return [
    'name'  => Env::get('APP_NAME', 'PHP Blog'),
    'env'   => Env::get('APP_ENV', 'production'),
    'debug' => Env::bool('APP_DEBUG', false),
    'url'   => Env::get('APP_URL', 'http://localhost'),

    'session' => [
        'lifetime' => (int) Env::get('SESSION_LIFETIME', 120),
        'name'     => 'blog_session',
    ],

    'security' => [
        'x_frame_options'        => 'SAMEORIGIN',
        'x_content_type_options' => 'nosniff',
        'referrer_policy'        => 'strict-origin-when-cross-origin',
        'csp'                    => "default-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; script-src 'self'",
    ],
];