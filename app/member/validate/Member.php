<?php declare(strict_types=1);
namespace app\member\validate;
use think\Validate;
class Member extends Validate
{
    protected $rule = [
        'nick|昵称' => 'require|unique:member',
        'password|密码' => 'require|length:6,35|confirm',
    ];

    protected $message = [
        'password.require' => '密码不能为空',
        'password.confirm' => '密码不一致',
        'password.length' => '密码长度无效',
    ];

    protected $scene = [
        'client'  =>  ['password'],
    ];

}
