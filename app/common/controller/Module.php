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
use think\exception\HttpResponseException;
use think\Response;

/**
 * 模块类
 * @package app\common\controller
 */
abstract class Module
{
    /**
     * @var string 错误信息
     */
    protected $error = '';

    /**
     * @var string 模块名
     */
    public $moduleName = '';

    /**
     * @var string 插件路径
     */
    public $modulePath = '';

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
    protected function response(int $type = 1, $msg = '', string $url = '', $data = '', int $wait = 3, array $header = [])
    {
        if (is_null($url) && isset($_SERVER["HTTP_REFERER"])) {
            $url = $_SERVER["HTTP_REFERER"];
        } else if ($url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : $this->app->route->buildUrl($url);
        }
        $result = [
            'code' => $type ?: 0,
            'msg' => $msg,
            'data' => $data,
            'url' => $url,
            'wait' => $wait,
        ];
        $type = $this->getResponseType();
        switch (strtolower($type)) {
            case 'html':
                $response = Response::create(app()->config->get('app.dispatch_success_tmpl'), 'view', 200)->header($header)->assign($result);
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
