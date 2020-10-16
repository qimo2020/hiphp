<?php declare(strict_types=1);
namespace hi;
class Sign {
    public static function getSign($data, $key='', $urlencode=false)
    {
        foreach ($data as $k => $v) {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = self::formatParaMap($Parameters, $urlencode);
        //签名步骤二：可在string后加入KEY
        if($key){
            $String = $String . "&key=" . $key;
        }
        //签名步骤三：MD5加密
        $String = md5($String);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($String);
        return $result;
    }
    protected static function formatParaMap($paraMap, $urlencode=false)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if ($urlencode == true) {
                $v = urlencode((string)$v);
            }
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar = '';
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }
}