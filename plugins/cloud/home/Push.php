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

namespace plugins\cloud\home;
use think\exception\HttpException;

class Push extends Base
{
    public function connect(){
        if(!$this->request->isPost()){
            throw new HttpException(404, '[404] page not found');
        }
        $params = $this->request->param();
        return json($params);
    }

}