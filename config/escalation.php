<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Error Notification Settings
    |--------------------------------------------------------------------------
    |
    | Control whether error notifications and auto-ticketing are enabled
    |
    */

    'enabled' => env('ERROR_NOTIFICATION_ENABLED', true),
    'auto_report' => env('ERROR_AUTO_REPORT', true), // Automatically capture all exceptions
    'ticket_enabled' => env('ERROR_TICKET_ENABLED', true),
    'sms_enabled' => env('ERROR_SMS_ENABLED', true), // Added strictly optional switch

    /*
    |--------------------------------------------------------------------------
    | Escalation Matrix
    |--------------------------------------------------------------------------
    |
    | Define contacts for each priority level. Notifications will be sent
    | to all contacts in the priority level based on the exception type.
    | Phone number is optional - if provided, SMS will be sent for that priority.
    |
    */

    'escalation_matrix' => [
        'critical' => [
            [
                'name' => 'Chief Technology Officer',
                'email' => 'tishmalo99@gmail.com',
                'phone' => '254700000000',
                'role' => 'CTO',
            ],
            [
                'name' => 'Lead Engineer',
                'email' => 'lead@example.com',
                'phone' => '254700000000',
                'role' => 'Lead Engineer',
            ],
        ],

        'high' => [
            [
                'name' => 'Lead Engineer',
                'email' => 'lead@example.com',
                'phone' => '254700000000',
                'role' => 'Lead Engineer',
            ],
        ],

        'medium' => [
            [
                'name' => 'Support Team',
                'email' => 'support@example.com',
                'phone' => '',
                'role' => 'Support',
            ],
        ],

        'low' => [
            [
                'name' => 'Support Team',
                'email' => 'support@example.com',
                'phone' => '',
                'role' => 'Support',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Channels
    |--------------------------------------------------------------------------
    |
    | Define which notification channels are used for each priority level
    | Available channels: email, sms, ticket
    |
    */

    'notification_channels' => [
        'critical' => ['email', 'sms', 'ticket'], 
        'high' => ['email', 'ticket'],            // Removed SMS default
        'medium' => ['ticket'],                   
        'low' => ['ticket'],                      
    ],

    /*
    |--------------------------------------------------------------------------
    | Exception Priority Mapping
    |--------------------------------------------------------------------------
    */

    'exception_mapping' => [
        'critical' => [
            \PDOException::class,
            \Illuminate\Database\QueryException::class,
        ],
        'high' => [
            \Illuminate\Auth\AuthenticationException::class,
        ],
        'medium' => [
            \RuntimeException::class,
        ],
        'low' => [
            \InvalidArgumentException::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Ignored Exceptions
    |--------------------------------------------------------------------------
    */

    'ignored_exceptions' => [
        \Illuminate\Validation\ValidationException::class,
        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting (Minutes)
    |--------------------------------------------------------------------------
    */

    'rate_limit' => [
        'critical' => 5,
        'high' => 15,
        'medium' => 30,
        'low' => 60,
    ],
];
