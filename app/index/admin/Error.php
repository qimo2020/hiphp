<?php
namespace app\index\admin;
use app\common\controller\Common;
use think\exception\HttpException;
class Error extends Common
{
    public function __call($method, $args)
    {
        throw new HttpException(404, '页面不存在');
    }
}
