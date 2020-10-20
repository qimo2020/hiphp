<?php
namespace app\member\home;
use app\common\controller\Common;
use think\exception\HttpException;

class Captcha extends Common
{
    public function __call($method, $args)
    {
        throw new HttpException(404, '[404] page not found');
    }

    public function index(){
        return Captcha::create();
    }

    //不刷新的方式进行验证码校验
    public function check(){
        if($this->request->isPost()){
            $res = $this->checkCode($this->request->post('captcha'));
            if(!$res){
                return $this->response(0, 'captcha error');
            };
            return $this->response(1, 'validate success');
        }else{
            return $this->response(0, 'illegal request');
        }
    }

    protected function checkCode($code){
        if(!session('captcha')){
            return $this->response(0, 'captcha session not found');
        }
        $code = mb_strtolower($code, 'UTF-8');
        $res = password_verify($code, session('captcha.key'));
        return $res;
    }


}
