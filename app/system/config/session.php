<?php
// +----------------------------------------------------------------------
// | HiPHP框架[基于ThinkPHP6.0开发]
// +----------------------------------------------------------------------
// | Copyright (c) http://www.hiphp.net
// +----------------------------------------------------------------------
// | HiPHP承诺基础框架永久免费开源，您可用于学习和商用，但必须保留软件版权信息。
// +----------------------------------------------------------------------
// | Author: 祈陌 <3411869134@qq.com>，开发者QQ群：829699898
// +----------------------------------------------------------------------
// | 会话设置
// +----------------------------------------------------------------------

$session = [
    // session name
    'name' => 'PHPSESSID',
    // SESSION_ID的提交变量,解决flash上传跨域
    'var_session_id' => '',
    // 驱动方式 支持file cache
    'type' => 'file',
    // 存储连接标识 当type使用cache的时候有效
    'store' => null,
    // 过期时间
    'expire' => 3600,
    // 前缀
    'prefix' => 'system',
];

//插件独立SESSION配置
$pathInfo = explode('/', request()->pathinfo());
if (defined('PLUGIN_ENTRANCE') || 'plugin' == $pathInfo[0]) {
    $plugin = '';
    if (defined('PLUGIN_ENTRANCE')) {
        $plugin = $pathInfo[0];
    } else if ('plugin' == $pathInfo[0]) {
        $plugin = $pathInfo[1];
    }

    if ($plugin && file_exists($fileDir = root_path() . 'plugins/' . $plugin . '/config/session.php')) {
        $session = include_once $fileDir;
    }
}

return $session;