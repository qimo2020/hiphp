<?php
namespace app\index\home;

use app\common\controller\Common;

class Demo extends Common
{
    public function index()
    {
        return $this->view();
    }

}
