<?php declare(strict_types=1);
namespace app\member\model;
defined('IN_SYSTEM') or die('Access Denied');
use think\facade\Db;
use think\Model;
use think\model\concern\SoftDelete;
class Member extends Model
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    public static $error;

    public function auth()
    {
        return $this->hasOne(MemberAuth::class,'member_id');
    }

    public function _save($post){
        Db::startTrans();
        try {
            if(isset($post['id']) && is_numeric($post['id'])){
                $model = self::find($post['id']);
                $model->auth->account = $post['account'];
                $model->auth->password = password_hash($post['password'], PASSWORD_DEFAULT);
            }else{
                $model = new Member();
                $model->uuid = gen_uuid();
                $auth = new MemberAuth();
                $auth->account = $post['account'];
                $auth->tid = $post['tid'];
                $auth->password = password_hash($post['password'], PASSWORD_DEFAULT);
                $model->auth = $auth;
            }
            $model->together(['auth'])->save($post);
            Db::commit();
            return ['id'=>$model->id, 'uuid'=>$model->uuid, 'status'=>$model->status];
        }catch (\Exception $e){
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }

    }
    /**
     * 用户登录
     * @param string $username 用户名
     * @param string $password 密码
     * @param bool $remember 记住登录 TODO
     * @return bool|mixed
     */
    public function login($account = '', $password = '', $remember = false)
    {
        $account = trim($account);
        $password = trim($password);
        $map = [];
        $map['account'] = $account;
        $memberAuth = MemberAuth::where($map)->find();
        if(!$memberAuth){
            self::$error = '用户未注册！';
            return false;
        }
        // 密码校验
        if (!password_verify($password, $memberAuth->password)) {
            self::$error = '登录密码错误！';
            return false;
        }
        // 更新登录信息
        $member = self::where(['id'=>$memberAuth->member_id])->find();
        $member->last_login_time = time();
        $member->last_login_ip = getClientIp();
        if ($member->save()) {
            // 执行登陆
            $login = [];
            $login['uid'] = $member->id;
            $login['uuid'] = $member->uuid;
            $login['account'] = telMailRep($memberAuth->account);
            $login['nick'] = $member->nick;
            $login['status'] = $member->status;
            $login['avatar'] = $member->avatar??'';
            // 缓存登录信息
            session('member', $login);
            session('member_sign', $this->dataSign($login));
            return $member->id;
        }
        return false;
    }

    public function loginOauth($id, $data){
        if(null === $member = self::find($id)){
            self::$error = '授权用户不存在！';
            return false;
        }
        if($member->status < 1){
            self::$error = '授权用户[未激活或已禁用]';
            return false;
        }
        $member->last_login_time = time();
        $member->last_login_ip = getClientIp();
        if ($member->save()) {
            $login = [];
            $login['uid'] = $member->id;
            $login['uuid'] = $member->uuid;
            $login['account'] = '';
            $login['nick'] = $data['nick'];
            $login['status'] = $member->status;
            $login['avatar'] = $data['avatar'];
            session('member', $login);
            session('member_sign', $this->dataSign($login));
            return $member->id;
        }
        return false;
    }

    /**
     * 判断是否登录
     * @return bool|array
     */
    public function isLogin()
    {
        $member = session('member');
        if (isset($member['uid'])) {
            if (!self::where('id', $member['uid'])->find()) {
                return false;
            }
            return session('member_sign') == $this->dataSign($member) ? $member : false;
        }
        return false;
    }

    /**
     * 退出登陆
     * @return bool
     */
    public function logout()
    {
        session('member', null);
        session('member_sign', null);
        return true;
    }

    /**
     * 数据签名认证
     * @param array $data 被认证的数据
     * @return string 签名
     */
    public function dataSign($data = [])
    {
        if (!is_array($data)) {
            $data = (array)$data;
        }
        ksort($data);
        $code = http_build_query($data);
        $sign = sha1($code);
        return $sign;
    }

}