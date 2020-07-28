<?php
use think\facade\Request;
$session = include_once root_path().'config/session.php';
if(!defined('ADMIN_ENTRANCE')){
    $pathInfo = Request::instance()->pathinfo();
    if(defined('PLUGIN_ENTRANCE') || 'plugin' == explode('/', $pathInfo)[0]) {
        $pluginName = defined('PLUGIN_ENTRANCE') ? explode('/', $pathInfo)[0] : explode('/', $pathInfo)[1];
        if(file_exists($path = root_path() . 'plugins/' . strtolower((string)$pluginName) . '/config/session.php')){
            $session = include_once $path;
        }
    }
}
return $session;