<?php

namespace app\hicms\home;
use app\common\controller\Common;

class Index extends Common
{
    public function index(){

        return $this->view();
    }
}
