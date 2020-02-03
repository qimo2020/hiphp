<?php
namespace plugins\devhelp\home;
use app\common\controller\Common;
defined('IN_SYSTEM') or die('Access Denied');
use think\facade\Request;
/**
 * [开发助手插件]前台Index控制器
 * @package plugins\devhelp\home
 */
class Test extends Common
{
    public function index()
    {
        print_r(request()->param());
        echo '<br>';
        echo 'plugins/devhelp/home/test/index';
    }

}