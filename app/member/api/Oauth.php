<?php declare(strict_types=1);
namespace app\member\api;
defined('IN_SYSTEM') or die('Access Denied');
use app\common\controller\Common;
class Oauth extends Common
{

    public function __call($method, $args){
       return json(['code'=>0, 'msg'=>'404']);
    }

	/**
 	* @title 默认
 	* @desc 默认
 	* @url member/api/oauth/token
 	* @method POST
 	* @public string $timestamp 1 - 当前时间戳
 	* @public string $nonce 1 - 8位长度随机数
 	* @public string $format 0 json 响应数据格式，json或xml, 默认json
 	* @public string $sign 1 - 签名
 	* @param string $account 1 - -
 	* @param string $password 1 - -
 	* @test 1
 	*/
    public function token(){
        $headers = request()->header();
        $params = $this->request->post();
        if(!isset($headers['timestamp']) || !isset($headers['nonce']) || !isset($headers['client'])){
            return json(['code'=>0, 'msg'=>'请求头参数错误[timestamp,nonce,client]']);
        }
        $memberAuth = new \app\member\model\MemberAuth();
        if(!$result = $memberAuth->where('account', $params['account'])->find()){
            return json(['code'=>0, 'msg'=>'账号不存在']);
        }
        if(!password_verify(md5($params['password']), $result['password'])){
            return json(['code'=>0, 'msg'=>'密码错误']);
        }
        $member = \app\member\model\Member::find($result['member_id']);
        $tag = 'hi_token_' . $headers['client'] . $member['uuid'];
        $token = randomStr(rand(80, 100), 8);
        $expire = configs('api')['user_token_expire'];
        cache($tag, $token, ['expire' => $expire * 60 * 60], 'api_module');
        $token = md5($token . $headers['timestamp'] . $headers['nonce']);
        return json(['code' => 1, 'msg'=>'登陆成功','token' => $token, 'uuid'=>$member['uuid']]);
    }



}