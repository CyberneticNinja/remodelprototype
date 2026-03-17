<?php

// config/auth.php
// Replace the defaults section and add the contractor guard.
// Merge this into your existing config/auth.php

return [

    'defaults' => [
        'guard'     => 'contractor',
        'passwords' => 'contractors',
    ],

    'guards' => [
        'web' => [
            'driver'   => 'session',
            'provider' => 'users',
        ],

        // Contractor guard — the only login in this app
        'contractor' => [
            'driver'   => 'session',
            'provider' => 'contractors',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model'  => App\Models\User::class,
        ],

        'contractors' => [
            'driver' => 'eloquent',
            'model'  => App\Models\Contractor::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table'    => 'password_reset_tokens',
            'expire'   => 60,
            'throttle' => 5,
        ],

        'contractors' => [
            'provider' => 'contractors',
            'table'    => 'password_reset_tokens',
            'expire'   => 60,
            'throttle' => 5,
        ],
    ],

    'password_timeout' => 10800,

];
