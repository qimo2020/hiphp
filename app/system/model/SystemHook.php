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

namespace app\system\model;

use think\Model;

/**
 * 插件模型
 * @package app\system\model
 */
class SystemHook extends Model
{
    public $error;
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    /**
     * 钩子入库
     * @param array $data 入库数据
     *@author 祈陌 <3411869134@qq.com>
     * @return bool
     */
    public function storage($data = [])
    {
        if (empty($data)) {
            $data = request()->post();
        }
        // 如果钩子名称存在直接返回true
        if (self::where('name', $data['name'])->find()) {
            return true;
        }
        $validate = new \app\system\validate\SystemHook;
        if($validate->check($data) !== true) {
            $this->error = $validate->getError();
            return false;
        }

        if (isset($data['id']) && !empty($data['id'])) {
            $res = $this->update($data);
        } else {
            $res = $this->create($data);
        }
        if (!$res) {
            $this->error = '保存失败！';
            return false;
        }

        return $res;
    }

    /**
     * 删除钩子
     * @param string $source 来源名称
     *@author 祈陌 <3411869134@qq.com>
     * @return bool
     */
    public static function delHook($source = '')
    {
        if (empty($source)) {
            return false;
        }
        if (self::where('source', $source)->delete() === false) {
            return false;
        }
        return true;
    }

}
