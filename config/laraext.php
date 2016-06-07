<?php
return [
    'errors' => [
        'display' => 'Off',
        'skip_halt_on' => E_WARNING | E_NOTICE,
        'skip_log_exceptions' => [],
        'exception_callbacks' => []
    ],
    'log' => [
        'rotate_days' => 5,
        'main' => 'logs/laravel_%d.log',
        'exceptions' => [
            'NotFoundHttpException' => 'logs/not_found_%d.log',
            'ErrorException' => 'logs/php_%d.log',
        ],
        'skip_trace' => ['NotFoundHttpException'],
        'mailto' => env('ERROR_MAILTO'),
        'mailto_only' => [],
        'mailto_except' => ['NotFoundHttpException'],
        'mailto_subject' => "Uncaught exception at %u"
    ],
    'logrotate' => [
        'logs/laravel_%d.log' => ['keep' => 10, 'exclude' => '\d{6}01\.log$', 'exclude_size_gt' => 1],
        'logs/not_found_%d.log' => ['keep' => 10],
        'logs/php_%d.log' => ['keep' => 10],
        'logs/path/to/dir/*' => ['keep_dir' => 10]
    ],
    'db' => [
        'backup_keep' => 3,
        'tmp_tables' => [
            ['pattern' => 'table_name_pcre_pattern', 'keep' => 1, 'numeric' => false]
        ]
    ],
    'notify_email' => env('NOTIFY_EMAIL'),
    'console' => [
        'enabled' => env('LOG_CONSOLE', 0),
        'password' => env('LOG_CONSOLE_PASSWORD', ''),
        'ips' => env('LOG_CONSOLE_IPS', '127.0.0.1'),
        'catch_errors' => env('LOG_CONSOLE_CATCH_ERRORS', 0)
    ],
    'tools' => [
        'SomeClass@someMethod',
        'SomeClass',
        ['name' => 'tool_name', 'tool' => "any callable"]
    ]
];



