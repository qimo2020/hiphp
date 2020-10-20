<?php
// +----------------------------------------------------------------------
// | HiPHP框架[基于ThinkPHP5.1开发]
// +----------------------------------------------------------------------
// | Copyright (c) http://www.hiphp.net
// +----------------------------------------------------------------------
// | HiPHP承诺基础框架永久免费开源，您可用于学习和商用，但必须保留软件版权信息。
// +----------------------------------------------------------------------
// | Author: 祈陌 <3411869134@qq.com>，开发者QQ群：50304283
// +----------------------------------------------------------------------
/**
 * 模块菜单
 * 字段说明
 * url 【链接地址】格式：member/控制器/方法，可填写完整外链[必须以http开头]
 * param 【扩展参数】格式：a=123&b=234555
 */
return [
    [
        'pid'           => 0,
        'title'         => '会员',
        'icon'          => 'icon iconfont iconmember',
        'module'        => 'member',
        'url'           => 'member/index/index',
        'param'         => '',
        'create_time' => time(),
        'childs' => [
            [
                'module' => 'member',
                'title' => '会员列表',
                'icon' => 'icon iconfont iconmemberist',
                'param' => '',
                'url' => 'member/member/index',
                'sort' => 0,
                'create_time' => time(),
            ],
            [
                'module' => 'member',
                'title' => '授权管理',
                'icon' => 'icon iconfont iconmembertype',
                'param' => '',
                'url' => 'member/memberauth/index',
                'sort' => 1,
                'create_time' => time(),
            ]
        ]
    ],
];