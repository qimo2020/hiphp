<?php
// +----------------------------------------------------------------------
// | HiPHP框架[基于ThinkPHP6.0开发]
// +----------------------------------------------------------------------
// | Copyright (c) http://www.hiphp.net
// +----------------------------------------------------------------------
// | HiPHP承诺基础框架永久免费开源，您可用于学习和商用，但必须保留软件版权信息。
// +----------------------------------------------------------------------
// | Author: 祈陌 <3411869134@qq.com>，开发者QQ群：829699898
// +----------------------------------------------------------------------

namespace app\system\model;

use think\Model;

/**
 * 后台日志模型
 * @package app\system\model
 */
class SystemLog extends Model
{
    protected $autoWriteTimestamp = true;
    
    public function user()
    {
        return $this->hasOne('systemUser', 'id', 'uid');
    }
}
