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
 * 模块基本信息
 */

$oauths = runHook('oauth_info', [], true);
$oauthStr = '';
if($oauths){
    foreach ($oauths as $v){
        $oauthStr .= $v['name'].':'.$v['title'].';';
    }
    $oauthStr = rtrim($oauthStr, ';');
}
return [
    // 模块名[必填]
    'name'        => 'member',
    // 模块标题[必填]
    'title'       => '会员',
    // 模块唯一标识[必填]，格式：模块名.module.[应用市场分支ID]
    'identifier'  => 'member.module',
    // 主题模板[必填]，默认default
    'theme'        => 'default',
    // 模块图标[选填]
    'icon'        => '/static/m_member/images/app.png',
    // 模块简介[选填]
    'intro' => '会员系统',
    // 开发者[必填]
    'author'      => 'hiphp',
    // 开发者网址[选填]
    'author_url'  => 'www.hiphp.net',
    // 版本[必填],格式采用三段式：主版本号.次版本号.修订版本号
    // 主版本号【位数变化：1-99】：当模块出现大更新或者很大的改动，比如整体架构发生变化。此版本号会变化。
    // 次版本号【位数变化：0-999】：当模块功能有新增或删除，此版本号会变化，如果仅仅是补充原有功能时，此版本号不变化。
    // 修订版本号【位数变化：0-999】：一般是 Bug 修复或是一些小的变动，功能上没有大的变化，修复一个严重的bug即发布一个修订版。
    'version'     => '1.0.1',
    // 模块依赖[可选]，格式[[模块名, 模块唯一标识, 依赖版本, 对比方式]]
    'module_depend' => [],
    // 插件依赖[可选]，格式[[插件名, 插件唯一标识, 依赖版本, 对比方式]]
    'plugin_depend' => [['builder','builder.plugin','1.0.0']],
    // 模块数据表[有数据库表时必填,不包含表前缀]
    'tables' => [
        'member',
        'member_auth_type',
        'member_auth',
    ],
    // 原始数据库表前缀,模块带sql文件时必须配置
    'db_prefix' => 'pre_',
    // 模块预埋钩子[非系统钩子，必须填写]
    'hooks' => [
        // '钩子名称' => '钩子描述'
    ],
    'language'=>['china'], //安装时第1个语言包名为默认语言
    'config_icon'=>true,
    'config' => [
        [
            'title'=>'基本',
            'url'=>url('module/setting', ['group'=>'member', 'tab'=>0]),
            'fields'=>[
                [
                    'name'=>'regist_status',
                    'type'=>'select',
                    'title'=>'注册状态',
                    'value'=>'2',
                    'tips'=>'会员在注册后的默认状态',
                    'options'=>'-1:未激活;0:禁用;1:待审;2:正常'
                ],
                [
                    'name'=>'regist_onoff',
                    'type'=>'switch',
                    'title'=>'注册开关',
                    'value'=>'1',
                    'tips'=>'',
                    'options'=>'0:关闭;1:开启'
                ],
                [
                    'name'=>'login_onoff',
                    'type'=>'switch',
                    'title'=>'登录开关',
                    'value'=>'1',
                    'tips'=>'',
                    'options'=>'0:关闭;1:开启'
                ],
                [
                    'name'=>'login_oauth',
                    'type'=>'checkbox',
                    'title'=>'第三方登陆',
                    'value'=>'',
                    'tips'=>'',
                    'options'=>$oauthStr
                ]
            ],
        ],
    ],
];