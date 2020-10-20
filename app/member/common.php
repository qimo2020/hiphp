<?php
if (!function_exists('telMailRep')) {
    /**
     * 匹配手机号并隐藏中间4位数/匹配邮箱并隐藏@前3位
     * @author 祈陌 <3411869134@qq.com>
     */
    function telMailRep($str){
        if(preg_match('/^0?1[3|4|5|6|7|8][0-9]\d{8}$/', $str)){
            return substr_replace($str,'****',3,4);
        }else if(false !== $email = strpos($str,'@')){
            $str = substr_replace($str, "***", $email-3, 3);
        }
        return $str;
    }
}

if (!function_exists('checkCaptcha')) {
    /**
     * 不刷新方式校验验证码
     */
    function checkCaptcha($code)
    {
        if (!session('captcha')) {
            return '验证码不存在';
        }
        $code = mb_strtolower($code, 'UTF-8');
        $res = password_verify($code, session('captcha.key'));
        if(!$res){
            return '验证码错误';
        }
        return true;
    }
}

if (!function_exists('gen_uuid')) {
    /**
     * 获取唯一ID值
     */
    function gen_uuid() {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

            // 16 bits for "time_mid"
            mt_rand( 0, 0xffff ),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand( 0, 0x0fff ) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand( 0, 0x3fff ) | 0x8000,

            // 48 bits for "node"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }
}
