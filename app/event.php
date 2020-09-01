<?php
// 事件定义文件
return [
    'bind'      => [
    ],

    'listen'    => [
        'AppInit'  => ['app\listener\InitEvent'],
        'HttpRun'  => ['app\listener\HookEvent'],
        'HttpEnd'  => [],
        'LogLevel' => [],
        'LogWrite' => []
    ],

    'subscribe' => [
    ],
];
