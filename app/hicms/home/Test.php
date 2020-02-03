<?php

namespace app\hicms\home;
use app\common\controller\Common;
use think\facade\Request;
class Test extends Common
{
    public function __call($method, $args)
    {
        print_r($this->request->param());
        echo '欢迎使用 cms-home-Test-empty';
    }

    public function index(){
        print_r($this->request->param());
        echo '欢迎使用 cms-home-Test-index';
    }

    public function hello(){

        return $this->view();
    }
}
