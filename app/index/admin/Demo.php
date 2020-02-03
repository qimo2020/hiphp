<?php
namespace app\index\admin;

use app\common\controller\Common;

class Demo extends Common
{
    public function index()
    {
        echo request()->param('id');
        echo '<br>';
        return $this->view();
    }

}
