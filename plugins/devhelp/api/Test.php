<?php
namespace plugins\devhelp\api;
use app\BaseController;
defined('IN_SYSTEM') or die('Access Denied');
use think\facade\Request;
class Test extends BaseController
{
    public function index()
    {
        print_r(request()->param());
        echo '<br>';
        echo 'plugins/devhelp/api/test/index';
    }

}