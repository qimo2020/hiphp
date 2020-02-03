<?php

namespace app\hicms\api;
use app\common\controller\Common;

class Error extends Common
{
    public function __call($method, $args)
    {
        echo request()->controller();
        echo '<br>';
        return 'welcome to  hicms api home-error-empty';
    }

}
