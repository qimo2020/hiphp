<?php
namespace plugins\devhelp\api\v1;
use app\BaseController;
defined('IN_SYSTEM') or die('Access Denied');

class Test extends BaseController
{
    public function index()
    {
        echo 'plugins/devhelp/api/v1/test/index';
    }

}