<?php

namespace app\middleware;

use think\facade\Request;

class InitMiddleware
{
    public function handle($request, \Closure $next)
    {
        if (defined('INSTALL_ENTRANCE')){
            return $next($request);
        };

        // 获取站点根目录
        $entry = Request::baseFile();
        $rootDir = preg_replace(['/index.php$/', '/plugin.php$/', '/' . config('hi.admin_path') . '$/'], ['', '', ''], $entry);
        define('ROOT_DIR', $rootDir);

        return $next($request);
    }
}