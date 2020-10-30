<?php declare(strict_types=1);
// +----------------------------------------------------------------------
// | HiPHP框架[基于ThinkPHP6.0开发]
// +----------------------------------------------------------------------
// | Copyright (c) http://www.HiPHP.net
// +----------------------------------------------------------------------
// | HiPHP承诺基础框架永久免费开源，您可用于学习和商用，但必须保留软件版权信息。
// +----------------------------------------------------------------------
// | Author: 祈陌 <3411869134@qq.com>，开发者QQ群：829699898
// +----------------------------------------------------------------------

namespace plugins\tinymce;
use app\common\controller\Plugin;
defined('IN_SYSTEM') or die('Access Denied');
/**
 * 富文本编辑器插件
 * @package plugins\sms
 */
class tinymce extends Plugin
{
    /**
     * @var array 插件钩子清单
     */
    public $hooks = [
        'editor' => 'identifier',
        'tinymce' => 'run'
    ];

    public function identifier()
    {
        return 'tinymce';
    }

    public function run($params){
        configs('tinymce', true);
        $this->assign('data', $params);
        return $this->view('index');
    }

    /**
     * 安装前的业务处理，可在此方法实现，默认返回true
     * @return bool
     */
    public function install()
    {
        return true;
    }

    /**
     * 安装后的业务处理，可在此方法实现，默认返回true
     * @return bool
     */
    public function installAfter()
    {
        return true;
    }
    
    /**
     * 升级前的业务处理，可在此方法实现，默认返回true
     * @return bool
     */
    public function upgrade()
    {
        return true;
    }

    /**
     * 升级后的业务处理，可在此方法实现，默认返回true
     * @return bool
     */
    public function upgradeAfter()
    {
        return true;
    }

    /**
     * 卸载前的业务处理，可在此方法实现，默认返回true
     * @return bool
     */
    public function uninstall()
    {

        return true;
    }

    /**
     * 卸载后的业务处理，可在此方法实现，默认返回true
     * @return bool
     */
    public function uninstallAfter()
    {
        return true;
    }

}