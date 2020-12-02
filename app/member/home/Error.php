<?php declare(strict_types=1);
namespace app\member\home;
defined('IN_SYSTEM') or die('Access Denied');
use app\common\controller\Common;
use app\system\model\SystemLang as LangModel;
use think\exception\HttpException;
use think\exception\ValidateException;
use app\member\model\Member as MemberModel;
use app\member\model\MemberAuthType as memberAuthTypeModel;
use app\member\validate\Member as memberValidate;
use think\facade\Session;
class Error extends Common
{
    public function __call($method, $args)
    {
        configs('member');
        $this->langvars = LangModel::langVals(app('http')->getName());
        $controller = strtolower($this->request->controller());
        if(!$controller || !in_array($controller, ['login', 'logout', 'regist'])){
            throw new HttpException(404, '[404] page not found');
        }
        $model = new MemberModel();
        if($this->request->isGet()){ //支持跳转回来源地址
            $param = $this->request->param();
            if(isset($param['from']) && $param['from']){ //TODO:待过滤白名单域名
                cookie('login_redirect', urldecode($param['from']));
            }
            if(isset($param['oauth'])){ //第三方登陆
                $oauth = cache(Session::getId().'_'.$param['oauth']);
                if($oauth){
                    if(is_array($oauth) && isset($oauth[0])){
                        if($oauth[0]['uid'] > 0){
                            $redirectUri = cookie('login_redirect') ? cookie('login_redirect') : '/';
                            return redirect($redirectUri);
                        }else{
                            $oauth = $oauth[0]['id'];
                        }
                    }
                    $class = '\\plugins\\oauth_'.$param['oauth'].'\\model\\Oauth';
                    if(!class_exists($class)){
                        return $this->response(0, $this->langvars['oauth_error']);
                    }
                    if(null !== $oauthInfo = $class::find($oauth)){
                        $oauths = runHook('oauth_info', [], true);
                        if($oauths){
                            foreach ($oauths as $v){
                                $oauthTitles[$v['name']] = $v['title'];
                            }
                        }
                        $this->assign('oauthTitles', $oauthTitles);
                        $this->assign('oauths', ['nick'=>$oauthInfo->nick, 'type'=>$param['oauth']]);
                    }
                }
            }
        }
        if($this->request->isPost()){
            $this->messages = config('message');
            $post = $this->request->post();
            if(true !== $msg = $this->check()){
                return $this->response(0, (string)$msg);
            }
            if($oauthList = config('member.login_oauth')){
                $oauthListArr = is_array($oauthList) ? $oauthList : explode(',', $oauthList);
            }
            if($controller == 'regist'){
                if(!isset($post['account']) || !isset($post['password'])){
                    return $this->response(0, $this->langvars['regist_fail']);
                }
                $type = memberAuthTypeModel::find($post['account_type']);
                if(null !== $type && $type->rule){ //各账号类型验证
                    $post[$type['identifier']] = $post['account'];
                    $rules[$type['identifier'].'|'.$type->title] = $type->rule;
                    if(count($rulesArr = explode('|', $type->rule)) != count($messageArr = explode('|', $type->message))){
                        return $this->response(0, $this->messages['validate_rule_error']);
                    }
                    foreach($messageArr as $k=>$v){
                        $messages[$type->identifier.'.'.(strpos($rulesArr[$k], ':') !== false ? explode(':', $rulesArr[$k])[0] : $rulesArr[$k])] = $v;
                    }
                    try {
                        validate()->rule(['account'=>['unique:member_auth']])->rule($rules)->message(['account.unique'=>$this->messages['account_exist']])->message($messages)->check($post);
                        validate(memberValidate::class)->scene('client')->check($post);
                    } catch (ValidateException $e) {
                        return $this->response(0, $e->getError());
                    }
                }
                if($type['rule_hook_after']){
                    if(true !== $check = checkCaptcha($post['captcha'])){
                        return $this->response(0, $check);
                    }
                }else{
                    if(!captcha_check($post['captcha'])){
                        return $this->response(0, $this->langvars['captcha_error']);
                    }
                }
                if($type['rule_hook']){
                    $hook = runHook($type['rule_hook'], $post, true);
                    if(!$hook){
                        return $this->response(0, '钩子错误');
                    }
                    foreach ($hook as $v){
                        if(!isset($v['code']) || !isset($v['msg'])){
                            return $this->response(0, $this->messages['validate_error']);
                        }
                        if($v['code'] == 0){
                            return $this->response(0, json_encode($v['msg']));
                        }
                    }
                }
                $post['tid'] = $post['account_type'];
                $model = new MemberModel();
                if (false === $res = $model->_save($post)) {
                    return $this->response(0, $this->langvars['regist_fail']);
                }
                $login = [];
                $login['uid'] = $res['id'];
                $login['uuid'] = $res['uuid'];
                $login['account'] = telMailRep($post['account']);
                $login['status'] = $res['status'];
                $login['avatar'] = $res['avatar']??'';
                session('member', $login);
                session('member_sign', $model->dataSign($login));
                $redirectUri = cookie('login_redirect') ?: '';
                if($type['rule_hook_after']){
                    if($redirectUri) $post['redirect_url'] = $redirectUri;
                    $post['member_id'] = $res['id'];
                    $hook = runHook($type['rule_hook_after'], $post, true);
                    if(isset($hook[0]['code']) && isset($hook[0]['msg'])){
                        if($hook[0]['code'] == 0){
                            return $this->response(0, (string)$hook[0]['msg']);
                        }
                        if($hook[0]['code'] == 1 && isset($hook[0]['redirect'])){
                            return $this->response(1, $hook[0]['msg'], $hook[0]['redirect']);
                        }
                    }
                }
                $msg = $this->langvars['regist_success'];
                if(isset($post['oauth_type']) && in_array($post['oauth_type'], $oauthListArr)){
                    $class = '\\plugins\\oauth_'.$post['oauth_type'].'\\model\\Oauth';
                    $id = cache(Session::getId().'_'.$post['oauth_type']);
                    if(!$id) $this->response(0, $this->langvars['oauth_error'], $redirectUri);
                    if(is_array($id) && !empty($id[0])){
                        $data = $id[0];
                    }else{
                        if(null !== $data = $class::field('account,uuid,nick,avatar')->find($id)){
                            $data = $data->toArray();
                        }else{
                            return $this->response(0, $this->langvars['oauth_error'], $redirectUri);
                        }
                    }
                    $data['uid'] = $login['uid'];
                    $class = '\\plugins\\oauth_'.$post['oauth_type'].'\\lib\\'.ucfirst($post['oauth_type']);
                    $bind = $class::uidBind($data);
                    if(false === $bind){
                        return $this->response(0, $class::$error, $redirectUri);
                    }
                    $login = session('member');
                    $login['nick'] = $data['nick'];
                    $login['avatar'] = $data['avatar'];
                    session('member', $login);
                    session('member_sign', $model->dataSign($login));
                    $msg = $this->messages['bind_success'];
                }
                cookie('login_redirect', null);
                return $this->response(1, $msg, $redirectUri);
            }else if($controller == 'login'){
                if(!captcha_check($post['captcha'])){
                    return $this->response(0, $this->langvars['captcha_error']);
                }
                if (!$uid = $model->login($post['account'], $post['password'])) {
                    return $this->response(0, (string)$model::$error);
                }
                $msg = $this->langvars['login_success'];
                $redirectUri = cookie('login_redirect') ? cookie('login_redirect') : '/';
                if(isset($post['oauth_type']) && in_array($post['oauth_type'], $oauthListArr)){
                    $class = '\\plugins\\oauth_'.$post['oauth_type'].'\\model\\Oauth';
                    $id = cache(Session::getId().'_'.$post['oauth_type']);
                    if(!$id) $this->response(0, $this->langvars['oauth_error'], $redirectUri);
                    if(is_array($id) && !empty($id[0])){
                        $data = $id[0];
                    }else{
                        if(null !== $data = $class::field('account,uuid,nick,avatar')->find($id)){
                            $data = $data->toArray();
                        }else{
                            return $this->response(0, $this->langvars['oauth_error'], $redirectUri);
                        }
                    }
                    $data['uid'] = $uid;
                    $class = '\\plugins\\oauth_'.$post['oauth_type'].'\\lib\\'.ucfirst($post['oauth_type']);
                    $bind = $class::uidBind($data);
                    if(false === $bind){
                        return $this->response(0, $class::$error, $redirectUri);
                    }
                    $login = session('member');
                    $login['nick'] = $data['nick'];
                    $login['avatar'] = $data['avatar'];
                    session('member', $login);
                    session('member_sign', $model->dataSign($login));
                    $msg = $this->messages['bind_success'];
                }
                cookie('login_redirect', null);
                return $this->response(1, $msg, $redirectUri);
            }
        }
        if($controller == 'logout'){
            if (!$model->logout()) {
                return $this->response(0, (string)$model::$error);
            }
            return $this->response(1, (string)$this->langvars['login_out'], (string)urldecode($param['from']));
        }
        if ($model->isLogin()) {
            return redirect((string)url('/'));
        }
        $authTypes = memberAuthTypeModel::where('status', 1)->select()->toArray();
        $this->assign('authTypes', $authTypes);
        $this->assign('from', $controller);
        return $this->view('index/index');
    }

    public function check()
    {
        $controller = strtolower($this->request->controller());
        if($controller == 'login' && !config('member.login_onoff')){
            return $this->langvars['login_stop'];
        }
        if($controller == 'regist' && !config('member.regist_onoff')){
            return $this->langvars['regist_stop'];
        }
        return true;
    }

}
