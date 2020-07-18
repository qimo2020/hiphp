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
namespace app\common\controller;
use app\system\model\SystemConfig;
use think\facade\Request;
use think\Exception;
use app\system\model\SystemPlugin;
use think\exception\HttpResponseException;
use think\facade\View;
use think\Response;

/**
 * 插件类
 * @package app\common\controller
 */
abstract class Plugin
{
    /**
     * @var null 视图实例对象
     */
    protected $view = null;

    /**
     * @var string 错误信息
     */
    protected $error = '';

    /**
     * @var string 插件名
     */
    public $pluginsName = '';

    /**
     * @var string 插件路径
     */
    public $pluginsPath = '';

    /**
     * 插件构造方法
     */
    public function __construct()
    {
        $viewReplaceStr = [
            // 站点根目录
            '__ROOT_DIR__' => ROOT_DIR,
            // 静态资源目录
            '__PUBLIC_STATIC__' => ROOT_DIR . 'static',
            // 扩展静态态资源目录
            '__PUBLIC_PACK__' => ROOT_DIR . 'pack',
        ];
        config(['tpl_replace_string' => $viewReplaceStr], 'view');
        // 获取插件名
        $class = get_class($this);
        $this->pluginsName = substr($class, strrpos($class, '\\') + 1);
        $this->pluginsPath = root_path() . 'plugins/' . $this->pluginsName . '/';
    }

    /**
     * 事件监听
     * @author 祈陌 <3411869134@qq.com>
     */
    public function subscribe(\think\Event $event)
    {
        if(empty($this->hooks)) return;
        $hookPlugins = cache('hook_plugins');
        foreach($hookPlugins as $v){
            foreach ($this->hooks as $key=>$value){
                if($key == $v['hook'] && $this->pluginsName == $v['plugins']){
                    $hook = is_numeric($key) ? $value : $key;
                    $event->listen($hook, [$this, $value]);
                }
            }
        }
    }

    /**
     * 获取插件基础信息
     * @param string $key 主键
     * @return mixed
     * @return mixed
     * @author 祈陌 <3411869134@qq.com>
     */
    final protected function getInfo($key = '')
    {
        $info = SystemPlugin::where('name', $this->pluginsName)->find();
        if (!$info) {
            return '';
        }
        if ($key && isset($info[$key])) {
            return $info[$key];
        }
        return $info;
    }

    /**
     * 获取插件配置
     * @param string $key 主键
     * @return mixed
     * @author 祈陌 <3411869134@qq.com>
     */
    final protected function getConfig($key = '')
    {
        $config = SystemConfig::getConfigs($key);
        return $config;
    }

    /**
     * 模板变量赋值
     * @param string $name 模板变量
     * @param string $value 变量的值
     * @return $this
     * @author 祈陌 <3411869134@qq.com>
     */
    final protected function assign($name = '', $value='')
    {
        $this->engine()->assign([$name=>$value]);
        return $this;
    }

    /**
     * 模板渲染[仅限钩子方法调用]
     * @param string $template 模板名
     * @param array $vars 模板输出变量
     * @param array $replace 替换内容
     * @param array $config 模板参数
     * @return mixed
     * @author 祈陌 <3411869134@qq.com>
     */
    final protected function view($template = '', $vars = [])
    {
        if ($template) {
            if (cache('plugins') && array_key_exists('builder', cache('plugins')) && strpos($template, 'build') !== false) {
                $tpl = explode('/', $template);
                $tplPath = root_path() . "plugins/builder/view/block/{$tpl[1]}.";
                $template = strtolower($tplPath . config('view.view_suffix'));
            }else{
                $template = $this->pluginsPath . 'widget/'. $template . '.' . config('view.view_suffix');
            }
        } else {
            throw new Exception('钩子模板不允许为空');
        }
        return $this->engine()->layout(false)->fetch($template, $vars);
    }
    /**
     * 获取模板引擎
     * @access public
     * @param string $type 模板引擎类型
     * @return \think\facade\View
     */
    final protected function engine(string $type = null)
    {
        return View::engine($type);
    }
    /**
     * 获取错误信息
     * @return string
     * @author 祈陌 <3411869134@qq.com>
     */
    final public function getError()
    {
        return $this->error;
    }

    /**
     * 操作成功跳转[兼容html/json]
     * @access protected
     * @param string $type 返回类型
     * @param mixed $msg 提示信息
     * @param string $url 跳转的URL地址
     * @param mixed $data 返回的数据
     * @param integer $wait 跳转等待时间
     * @param array $header 发送的Header信息
     * @return void
     */
    protected function response($type = 1, $msg = '', string $url = null, $data = '', int $wait = 3, array $header = [])
    {
        if (is_null($url) && isset($_SERVER["HTTP_REFERER"])) {
            $url = $_SERVER["HTTP_REFERER"];
        } elseif ($url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : app()->route->buildUrl($url);
        }
        $result = [
            'code' => $type ?: 0,
            'msg' => $msg,
            'data' => $data,
            'url' => $url,
            'wait' => $wait,
        ];
        $type = $this->getResponseType();
        if ('html' == strtolower($type)) {
            $type = 'view';
        }
        switch ($type) {
            case 'view':
                $response = Response::create(app()->config->get('app.dispatch_success_tmpl'), $type)->header($header)->assign($result);
                break;
            case 'json':
                $response = Response::create($result, $type, 200)->header($header);
                break;
        }
        return $response;
    }

    /**
     * 获取当前的response 输出类型
     * @access protected
     * @return string
     */
    protected function getResponseType()
    {
        $isAjax = app()->request->isAjax();
        return $isAjax ? 'json' : 'html';
    }

    /**
     * 安装前
     * @return mixed
     * @author 祈陌 <3411869134@qq.com>
     */
    abstract public function install();

    /**
     * 安装后
     * @return mixed
     * @author 祈陌 <3411869134@qq.com>
     */
    abstract public function installAfter();
    /**
     * 升级前
     * @return mixed
     * @author 祈陌 <3411869134@qq.com>
     */
    abstract public function upgrade();
    /**
     * 升级后
     * @return mixed
     * @author 祈陌 <3411869134@qq.com>
     */
    abstract public function upgradeAfter();
    /**
     * 卸载前
     * @return mixed
     * @author 祈陌 <3411869134@qq.com>
     */
    abstract public function uninstall();

    /**
     * 卸载后
     * @return mixed
     * @author 祈陌 <3411869134@qq.com>
     */
    abstract public function uninstallAfter();


}
