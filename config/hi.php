<?php
return [
    'tables' => ['system_annex', 'system_annex_group', 'system_config', 'system_hook', 'system_hook_plugins', 'system_language', 'system_log', 'system_menu', 'system_menu_lang', 'system_module', 'system_plugins', 'system_role', 'system_user'],
    'config_group' => ['base' => '基础', 'system' => '系统', 'upload' => '上传', 'databases' => '数据库', 'clouds'=>'云端'],
    'modules' => ['base', 'system', 'install', 'index', 'lang'],
    'configs' => ['app', 'cache', 'cookie', 'database', 'filesystem', 'lang', 'log', 'middleware', 'route', 'session', 'trace', 'view', 'hi'],
    'scriptName' => ['index', 'admin', 'router', 'think'],
    'admin_path' => 'admin.php'
];