<?php

namespace app\hicms\admin;
use app\system\admin\Base;

class Error extends Base
{
    public function __call($method, $args)
    {
        echo '欢迎使用 hicms 后台';
    }
}
