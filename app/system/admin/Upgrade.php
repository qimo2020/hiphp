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

namespace app\system\admin;

use hi\Cloud;

class Upgrade extends Base
{
    public function index()
    {
        $data = [];
        $result = runHook('cloud_push', ['type' => 'system', 'method' => 'upgrade'], true);
        if ($result[0]) {
            $result = $this->mpSort($result[0], 'version');
            $keys = [];
            foreach ($result as $k => $v) {
                if (version_compare($v['version'], config('hiphp.version'), '<=')) {
                    unset($result[$k]);
                } else {
                    $keys[$v['version']] = $k;
                }
            }
            if ($keys) {
                $version = array_search(min($keys), $keys);
                $data = $result[$keys[$version]];
            }
        }
        $this->assign('data', $data);
        return $this->view();
    }

    protected function mpSort($array, $key)
    {
        for ($i = 0; $i < count($array); $i++) {
            for ($j = $i; $j < count($array); $j++) {
                if (version_compare($array[$i][$key], $array[$j][$key], '>')) {
                    $temp = $array[$i];
                    $array[$i] = $array[$j];
                    $array[$j] = $temp;
                }
            }
        }
        return $array;
    }

}