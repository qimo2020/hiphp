<?php
// +----------------------------------------------------------------------
// | HiPHP框架[基于ThinkPHP5.1开发]
// +----------------------------------------------------------------------
// | Copyright (c) http://www.hiphp.net
// +----------------------------------------------------------------------
// | HiPHP承诺基础框架永久免费开源，您可用于学习和商用，但必须保留软件版权信息。
// +----------------------------------------------------------------------
// | Author: 祈陌 <3411869134@qq.com>，开发者QQ群：829699898
// +----------------------------------------------------------------------

namespace plugins\devhelp\model;

use think\Model;
use think\facade\Env;
use hi\Dir;
/**
 * 模块模型
 * @package app\system\model
 */
class SystemModule extends Model
{
    public $error='';

    /**
     * 设计并生成标准模块结构
     * @author 祈陌 <3411869134@qq.com>
     * @return bool
     */
    public function design($data = [])
    {
        $app_path = base_path();
        if (empty($data)) {
            $data = input('post.');
        }

        $icon = 'static/m_'.$data['name'].'/images/app.png';
        $data['icon'] = ROOT_DIR.$icon;
        $mod_path = $app_path.$data['name'] . '/';
        if (is_dir($mod_path) || self::where('name', $data['name'])->find() || in_array($data['name'], config('hi.modules')) !== false) {
            $this->error = '模块已存在！';
            return false;
        }

        if (!is_writable(root_path().'app')) {
            $this->error = '[application]目录不可写！';
            return false;
        }

        if (!is_writable(root_path().'public/static')) {
            $this->error = '[static]目录不可写！';
            return false;
        }

        // 生成模块目录结构
        $build = [];
        if ($data['file']) {
            $build[$data['name']]['__file__'] = explode(',', $data['file']);
        }
        $build[$data['name']]['__dir__'] = parseAttr($data['dir']);
        $build[$data['name']]['model'] = ['example'];
        $build[$data['name']]['view'] = ['index/index'];
        $BuildObj = new \hi\Build();
        $BuildObj->run($build);
        if (!is_dir($mod_path)) {
            $this->error = '模块目录生成失败[app/'.$data['name'].']！';
            return false;
        }

        // 删除默认的应用配置目录
        Dir::delDir(root_path().'config/'.$data['name']);

        // 生成对应的前台主题模板目录、静态资源目录, 后台静态资源目录
        $dir_list = [
            'app/'.$data['name'].'/theme/default/index',
            'app/'.$data['name'].'/theme/mobile/default/index',
            'public/static/m_'.$data['name'].'/default/css',
            'public/static/m_'.$data['name'].'/default/js',
            'public/static/m_'.$data['name'].'/default/images',
            'public/static/m_'.$data['name'].'/css',
            'public/static/m_'.$data['name'].'/js',
            'public/static/m_'.$data['name'].'/images',
        ];
        self::mkDir($dir_list);
        self::mkThemeConfig($app_path . $data['name'].'/theme/default/', $data);
        self::mkThemeConfig($app_path . $data['name'].'/theme/mobile/default/', $data);
        self::mkSql($mod_path, $data);
        self::mkMenu($mod_path, $data);
        self::mkInfo($mod_path, $data);
        self::mkControl($mod_path, $data);

        // 将生成的模块信息添加到模块管理表
        $sql = [];
        $sql['name'] = $data['name'];
        $sql['identifier'] = $data['identifier'];
        $sql['title'] = $data['title'];
        $sql['intro'] = $data['intro'];
        $sql['author'] = $data['author'];
        $sql['icon'] = $data['icon'];
        $sql['version'] = $data['version'];
        $sql['url'] = $data['url'];
        $sql['status'] = 0;
        self::create($sql);

        return true;
    }

    /**
     * 生成目录
     * @param array $list 目录列表
     * @author 祈陌 <3411869134@qq.com>
     */
    public static function mkDir($list)
    {
        $root_path = root_path();
        foreach ($list as $dir) {
            if (!is_dir($root_path . $dir)) {
                // 创建目录
                mkdir($root_path . $dir, 0755, true);
            }
        }
    }

    /**
     * 生成模块控制器
     * @author 祈陌 <3411869134@qq.com>
     */
    public static function mkControl($path = '', $data = [])
    {
        // 生成后台默认控制器
        if (is_dir($path.'admin')) {
            $admin_contro = "<?php\nnamespace app\\".$data["name"]."\\admin;\nuse app\system\admin\Base;\n\nclass Index extends Base\n{\n    protected ".'$hiModel'." = '';//模型名称[通用添加、修改专用]\n    protected ".'$hiTable'." = '';//表名称[通用添加、修改专用]\n    protected ".'$hiAddScene'." = '';//添加数据验证场景名\n    protected ".'$hiEditScene'." = '';//更新数据验证场景名\n\n    public function index()\n    {\n        return ".'$this->view()'.";\n    }\n    public function welcome()\n    {\n        return ".'$this->view()'.";\n    }\n}";
            // 删除框架生成的html文件
            @unlink($path . 'view/index/index.html');
            file_put_contents($path . 'admin/Index.php', $admin_contro);
            file_put_contents($path . 'view/index/index.html', "我是后台模板[".$path."view/index/index.html]");
        }

        // 生成前台默认控制器
        if (is_dir($path.'home')) {
            $home_contro = "<?php\nnamespace app\\".$data["name"]."\\home;\nuse app\common\controller\Common;\n\nclass Index extends Common\n{\n    public function index()\n    {\n        return ".'$this->view()'.";\n    }\n}";
            file_put_contents($path . 'home/Index.php', $home_contro);
            file_put_contents(base_path().$data['name'].'/theme/default/index/index.html', '我是前台模板[/app/'.$data['name'].'/theme/default/index/index.html]');
        }
    }

    /**
     * 生成SQL文件
     * @author 祈陌 <3411869134@qq.com>
     */
    public static function mkSql($path = '')
    {
        if (!is_dir($path . 'sql')) {
            mkdir($path . 'sql', 0755, true);
        }
        file_put_contents($path . 'sql/install.sql', "/*\n sql安装文件\n*/");
        file_put_contents($path . 'sql/uninstall.sql', "/*\n sql卸载文件\n*/");
    }

    /**
     * 生成模块菜单文件
     */
    public static function mkMenu($path = '', $data = [])
    {
        // 菜单示例代码
        $menus = <<<INFO
<?php
// +----------------------------------------------------------------------
// | HiPHP框架[基于ThinkPHP5.1开发]
// +----------------------------------------------------------------------
// | Copyright (c) http://www.HiPHP.net
// +----------------------------------------------------------------------
// | HiPHP承诺基础框架永久免费开源，您可用于学习和商用，但必须保留软件版权信息。
// +----------------------------------------------------------------------
// | Author: 祈陌 <3411869134@qq.com>，开发者QQ群：50304283
// +----------------------------------------------------------------------
/**
 * 模块菜单
 * 字段说明
 * url 【链接地址】格式：{$data['name']}/控制器/方法，可填写完整外链[必须以http开头]
 * param 【扩展参数】格式：a=123&b=234555
 */
return [
    [
        'pid'           => 0,
        'title'         => '{$data['title']}',
        'icon'          => '',
        'module'        => '{$data['name']}',
        'url'           => '{$data['name']}/index/index',
        'param'         => '',
    ],
];
INFO;
        file_put_contents($path . 'menu.php', $menus);
    }

    /**
     * 生成模块信息文件
     * @author 祈陌 <3411869134@qq.com>
     */
    public static function mkInfo($path = '', $data = [])
    {
        // 配置内容
        $config = <<<INFO
<?php
// +----------------------------------------------------------------------
// | HiPHP框架[基于ThinkPHP5.1开发]
// +----------------------------------------------------------------------
// | Copyright (c) http://www.HiPHP.net
// +----------------------------------------------------------------------
// | HiPHP承诺基础框架永久免费开源，您可用于学习和商用，但必须保留软件版权信息。
// +----------------------------------------------------------------------
// | Author: 祈陌 <3411869134@qq.com>，开发者QQ群：50304283
// +----------------------------------------------------------------------
/**
 * 模块基本信息
 */
return [
    // 模块名[必填]
    'name'        => '{$data['name']}',
    // 模块标题[必填]
    'title'       => '{$data['title']}',
    // 模块唯一标识[必填]，格式：模块名.[应用市场ID].module.[应用市场分支ID]
    'identifier'  => '{$data['identifier']}',
    // 主题模板[必填]，默认default
    'theme'        => 'default',
    // 模块图标[选填]
    'icon'        => '{$data['icon']}',
    // 模块简介[选填]
    'intro' => '{$data['intro']}',
    // 开发者[必填]
    'author'      => '{$data['author']}',
    // 开发者网址[选填]
    'author_url'  => '{$data['url']}',
    // 版本[必填],格式采用三段式：主版本号.次版本号.修订版本号
    // 主版本号【位数变化：1-99】：当模块出现大更新或者很大的改动，比如整体架构发生变化。此版本号会变化。
    // 次版本号【位数变化：0-999】：当模块功能有新增或删除，此版本号会变化，如果仅仅是补充原有功能时，此版本号不变化。
    // 修订版本号【位数变化：0-999】：一般是 Bug 修复或是一些小的变动，功能上没有大的变化，修复一个严重的bug即发布一个修订版。
    'version'     => '{$data['version']}',
    // 模块依赖[可选]，格式[[模块名, 模块唯一标识, 依赖版本, 对比方式]]
    'module_depend' => [],
    // 插件依赖[可选]，格式[[插件名, 插件唯一标识, 依赖版本, 对比方式]]
    'plugin_depend' => [],
    // 模块数据表[有数据库表时必填,不包含表前缀]
    'tables' => [
        // 'table_name',
    ],
    // 原始数据库表前缀,模块带sql文件时必须配置
    'db_prefix' => 'pre_',
    // 模块预埋钩子[非系统钩子，必须填写]
    'hooks' => [
        // '钩子名称' => '钩子描述'
    ],
    // 模块配置，格式['sort' => '100','title' => '配置标题','name' => '配置名称','type' => '配置类型','options' => '配置选项','value' => '配置默认值', 'tips' => '配置提示'],各参数设置可参考管理后台->系统->系统功能->配置管理->添加
    'config' => [],
];
INFO;
        file_put_contents($path . 'info.php', $config);
    }

    public static function mkThemeConfig($path, $data = [])
    {
        $str = '<?xml version="1.0" encoding="ISO-8859-1"?>
<root>
    <item id="title"><![CDATA[默认模板]]></item>
    <item id="version"><![CDATA[v1.0.0]]></item>
    <item id="time"><![CDATA['.date('Y-m-d H:i').']]></item>
    <item id="author"><![CDATA[HiPHP]]></item>
    <item id="copyright"><![CDATA[HiPHP]]></item>
    <item id="db_prefix"><![CDATA[pre_]]></item>
    <item id="identifier" title="默认模板必须留空，非默认模板必须填写对应的应用标识"><![CDATA[]]></item>
    <item id="depend" title="请填写当前对应的模块标识"><![CDATA['.$data['identifier'].']]></item>
</root>';

        file_put_contents($path.'config.xml', $str);
    }
}
