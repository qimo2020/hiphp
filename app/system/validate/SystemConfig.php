<?php declare(strict_types=1);
// +----------------------------------------------------------------------
// | HiPHP框架[基于ThinkPHP6.0开发]
// +----------------------------------------------------------------------
// | Copyright (c) http://www.hiphp.net
// +----------------------------------------------------------------------
// | HiPHP承诺基础框架永久免费开源，您可用于学习和商用，但必须保留软件版权信息。
// +----------------------------------------------------------------------
// | Author: 祈陌 <3411869134@qq.com>，开发者QQ群：829699898
// +----------------------------------------------------------------------

namespace app\system\validate;

use think\Validate;

/**
 * 配置验证器
 * @package app\system\validate
 */
class SystemConfig extends Validate
{
    //定义验证规则
    protected $rule = [
        'name|配置标识'	=> 'require|unique:system_config',
        'title|配置标题' => 'require',
        'type|配置类型'	=> 'require',
    ];
}
