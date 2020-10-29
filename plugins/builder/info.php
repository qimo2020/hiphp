<?php
/*
 * 1.注意确保同一个插件配置中的字段名不能相同;
 * 2.当用户操作清空某个字段值时，该字段会恢复本文件拥有的默认值，所以要注意设置好默认值;
 */
return [
    'name' => 'builder',
    // 模块唯一标识[必填]，格式：插件名.[应用市场ID].plugin.[应用市场分支ID]
    'identifier' => 'builder.plugin',
    'title' => '后台开发构建器',
    'intro' => '表单和表格构建器，ui使用了layui',
    'author' => 'HiPHP',
    'icon' => '',
    'version' => '1.0.1',
    'tables'=>[],
    'author_url' => '',
    'module_depend'=>[],
];