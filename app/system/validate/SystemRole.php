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
 * 角色验证器
 * @package app\system\validate
 */
class SystemRole extends Validate
{
    //定义验证规则
    protected $rule = [
        'name|角色名称'     => 'require|unique:system_role',
        'auth|设置权限'     => 'require',
        'status|状态设置'   => 'require|in:0,1',
    ];

    //定义验证提示
    protected $message = [
        'name.require'      => '请输入角色名称',
        'name.unique'       => '角色名称已存在',
        'auth.require'      => '请设置权限',
        'status.require'    => '请设置角色状态',
    ];
}
