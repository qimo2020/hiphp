<?php
// 事件定义文件
return [
    'bind'      => [
    ],

    'listen'    => [
        'AppInit'  => ['app\event\InitEvent'],
        'HttpRun'  => ['app\event\HookEvent'],
        'HttpEnd'  => [],
        'LogLevel' => [],
        'LogWrite' => []
    ],

    'subscribe' => [
    ],
];
