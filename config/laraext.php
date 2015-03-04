<?php
return [
    'errors' => [
        'display' => 'Off',
        'skip_halt_on' => E_WARNING | E_NOTICE,
        'skip_log_exceptions' => []
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
    ]
];
