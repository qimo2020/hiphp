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
 * 插件验证器
 * @package app\system\validate
 */
class SystemPlugin extends Validate
{
    protected $rule = [
        'name|插件名' => 'require|alphaDash|unique:system_plugin',
        'title|插件标题' => 'require',
        'identifier|插件标识' => 'require|unique:system_plugin',
    ];
}
