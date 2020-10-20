<?php declare(strict_types=1);
namespace app\member\api;
defined('IN_SYSTEM') or die('Access Denied');
use app\common\controller\Common;
class Jwt extends Common
{

    public function __call($method, $args){
       return json(['code'=>0, 'msg'=>'404']);
    }

	/**
 	* @title 默认
 	* @desc 默认
 	* @url member/api/jwt/token
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
        $memberAuth = new \app\member\model\MemberAuth();
        $params = $this->request->post();
        if(!$result = $memberAuth->where('account', $params['account'])->find()){
            return json(['code'=>0, 'msg'=>'账号不存在']);
        }
        if(!password_verify(md5($params['password']), $result['password'])){
            return json(['code'=>0, 'msg'=>'密码错误']);
        }
        $member = \app\member\model\Member::find($result['member_id']);
        $jwt = new \app\api\lib\JwtToken();
        $token = $jwt::create($params['account'], $member['uuid']);
        return json(['code'=>1, 'msg'=>'已生成', 'token'=>$token]);
    }



}