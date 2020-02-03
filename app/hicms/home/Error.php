<?php

namespace app\hicms\home;
use app\common\controller\Common;

class Error extends Common
{
    public function __call($method, $args)
    {
        return 'welcome to home-error-empty';
    }

}
