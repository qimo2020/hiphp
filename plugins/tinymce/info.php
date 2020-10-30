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
/**
 * 插件基本信息
 */
return [
    // 插件名[必填]
    'name'        => 'tinymce',
    // 插件标题[必填]
    'title'       => 'TM富文本',
    // 模块唯一标识[必填]，格式：插件名.plugin.[应用市场分支ID]
    'identifier'  => 'tinymce.plugin',
    // 插件图标[必填]
    'icon'        => '/static/p_tinymce/images/app.png',
    // 插件描述[选填]
    'intro' => 'tinymce富文本编辑器',
    // 插件作者[必填]
    'author'      => 'hiphp',
    // 作者主页[选填]
    'author_url'  => 'www.hiphp.net',
    'version'     => '1.0.0',
    'db_prefix' => 'pre_',
     // 语言包
    'language' => [],
    'config_icon'=>true,
    // 数据表
    'tables' => [],
    'config'    => [
        [
            'title'=>'基础',
            'url'=>url('plugin/setting',['group'=>'tinymce','tab'=>0]),
            'fields'=>[
                [
                    'name'=>'language',
                    'type'=>'input',
                    'title'=>'语言',
                    'tips'=>'英文留空/繁体字:zh_TW',
                    'value'=>'zh_CN',
                ],
                [
                    'name'=>'plugins',
                    'type'=>'input',
                    'title'=>'插件集',
                    'tips'=>'非开发人员不要随意修改此处',
                    'value'=>'print preview searchreplace autolink directionality visualblocks visualchars fullscreen image link media template code codesample table charmap hr pagebreak nonbreaking anchor insertdatetime advlist lists wordcount imagetools textpattern help emoticons autosave bdmap indent2em autoresize lineheight',
                ],
                [
                    'name'=>'toolbar',
                    'type'=>'input',
                    'title'=>'工具栏',
                    'tips'=>'非开发人员不要随意修改此处',
                    'value'=>'code undo redo | forecolor backcolor bold italic underline strikethrough link | image charmap codesample emoticons preview | alignleft aligncenter alignright alignjustify outdent indent | styleselect formatselect fontselect fontsizeselect | bullist numlist | blockquote subscript superscript removeformat | restoredraft media table cut copy paste pastetext hr pagebreak insertdatetime print anchor | fullscreen | bdmap indent2em lineheight',
                ],
                [
                    'name'=>'fontfamily',
                    'type'=>'input',
                    'title'=>'字体',
                    'tips'=>'非开发人员不要随意修改此处',
                    'value'=>'微软雅黑=Microsoft YaHei,Helvetica Neue,PingFang SC,sans-serif;苹果苹方=PingFang SC,Microsoft YaHei,sans-serif;宋体=simsun,serif;仿宋体=FangSong,serif;黑体=SimHei,sans-serif;Arial=arial,helvetica,sans-serif;Arial Black=arial black,avant garde;Book Antiqua=book antiqua,palatino;',
                ],
                [
                    'name'=>'fontsize',
                    'type'=>'input',
                    'title'=>'字体大小',
                    'tips'=>'非开发人员不要随意修改此处',
                    'value'=>'12px 14px 16px 18px 24px 36px 48px 56px 72px',
                ],
            ]
        ]
    ],
];