<?php
<<<<<<< HEAD
return [
    'tables' => ['system_annex', 'system_annex_group', 'system_config', 'system_hook', 'system_hook_plugins', 'system_language', 'system_log', 'system_menu', 'system_menu_lang', 'system_module', 'system_plugins', 'system_role', 'system_user'],
    'config_group' => ['base' => '基础', 'system' => '系统', 'upload' => '上传', 'databases' => '数据库', 'clouds'=>'云端'],
    'modules' => ['base', 'system', 'install', 'index', 'lang'],
    'configs' => ['app', 'cache', 'cookie', 'database', 'filesystem', 'lang', 'log', 'middleware', 'route', 'session', 'trace', 'view', 'hi'],
    'scriptName' => ['index', 'admin', 'router', 'think'],
    'admin_path' => 'admin.php'
];
=======
// +----------------------------------------------------------------------
// | HiPHP框架[基于ThinkPHP6.0开发]
// +----------------------------------------------------------------------
// | Copyright (c) http://www.hiphp.net
// +----------------------------------------------------------------------
// | HiPHP承诺基础框架永久免费开源，您可用于学习和商用，但必须保留软件版权信息。
// +----------------------------------------------------------------------
// | Author: 祈陌 <3411869134@qq.com>，开发者QQ群：829699898
// +----------------------------------------------------------------------
/*
 * HiPHP框架配置
 */
return [
    // HiPHP初始数据表
    'tables' => [
        'system_annex',
        'system_annex_group',
        'system_config',
        'system_hook',
        'system_hook_plugins',
        'system_language',
        'system_log',
        'system_menu',
        'system_menu_lang',
        'system_module',
        'system_plugins',
        'system_role',
        'system_user',
    ],
    // 系统配置分组初始信息
    'config_group' => [
        'base' => '基础',
        'system' => '系统',
        'upload' => '上传',
        'databases' => '数据库',
    ],
    // HiPHP内置模块
    'modules' => ['base', 'system', 'install', 'index', 'lang'],
    // HiPHP合法配置文件
    'configs' => ['app', 'cache', 'cookie', 'database', 'filesystem', 'lang', 'log', 'middleware', 'route', 'session', 'trace', 'view', 'hi'],
    'admin_path'=>'admin.php'
];

>>>>>>> d52ad045fd7ed93be13a3b7bc1a6f4fbc770c8c7
