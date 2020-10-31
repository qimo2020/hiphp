<?php
namespace app\member;
use app\common\controller\Module;
defined('IN_SYSTEM') or die('Access Denied');
class member extends Module
{
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
        //默认授权方式初始化
        $model = new \app\member\model\MemberAuthType();
        $data = [
            ['title'=>'账号', 'identifier'=>'username', 'rule'=>'require|alphaDash|min:4','message'=>'请输入用户名|账号只能是字母和数字，下划线_及破折号-|账号至少4位'],
            ['title'=>'手机号', 'identifier'=>'phone', 'rule_hook'=>'verify_sms', 'rule_hook_tp'=>'verify_sms_tp|type:regist', 'rule'=>'require|regex:^1\d{10}','message'=>'请输入手机号|手机号格式不正确','check_after'=>1, 'status'=>0],
            ['title'=>'邮箱', 'identifier'=>'email', 'rule_hook'=>'', 'rule_hook_after'=>'email_regist_send', 'rule_hook_tp'=>'email_regist_tp', 'rule'=>'require|email','message'=>'请输入邮箱地址|邮箱格式不正确','check_after'=>1, 'status'=>0],
        ];
        $model->saveAll($data);
        return true;
    }
    /**
     * 升级前
     * @return mixed
     * @author 祈陌 <3411869134@qq.com>
     */
    public function upgrade(){
        return true;
    }

    /**
     * 升级后
     * @return mixed
     * @author 祈陌 <3411869134@qq.com>
     */
    public function upgradeAfter(){
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