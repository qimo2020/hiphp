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
namespace app\system\validate;
use think\Validate;
/**
 * 钩子验证器
 * @package app\system\validate
 */
class SystemHook extends Validate
{
    //定义验证规则
    protected $rule = [
		'name|钩子名称'	=> 'require|unique:system_hook',
		'intro|钩子描述' => 'require',
    ];
}
