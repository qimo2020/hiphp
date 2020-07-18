<?php declare(strict_types=1);

namespace plugins\cloud\lib;
use hi\Http;

class Cloud
{
    // 错误信息
    private $error = '应用中心出现未知错误';
    // 接口
    private $api = '';
    // 站点标识
    private $token = '';
    // 请求的数据
    private $data = [];
    // 下载锁
    public $lock = '';
    // 请求类型
    public $type = 'post';
    //服务器地址
    public static $apiUrl = 'https://open.hiphp.net/';

    /**
     * 架构函数
     * @param string $path 目录路径
     */
    public function __construct($apiUrl = '')
    {
        $this->token = config('hi_cloud.token');
        $this->sid = config('hi_cloud.sid');
        if($apiUrl){
            self::$apiUrl = $apiUrl;
        }
    }

    /**
     * 获取服务器地址
     * @return string
     */
    public function apiUrl()
    {
        return self::$apiUrl;
    }

    /**
     * 获取错误信息
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * 需要发送的数据
     * @param  array $data 数据
     * @return obj
     */
    public function data($data = [])
    {
        $this->data = $data;
        return $this;
    }

    /**
     * api 请求接口
     * @param  string $api 接口
     * @return array
     */
    public function api($api = '')
    {
        $this->api = $this->apiUrl() . $api;
        return $this->run($this->data);
    }

    /**
     * type 请求类型
     * @param  string $type 请求类型(get,post)
     * @return obj
     */
    public function type($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * 文件下载
     * @param  string $api 接口
     * @return array
     */
    public function down($api, $saveFile)
    {
        $this->api = $this->apiUrl() . $api;
        $request = $this->run(true);
        $result = Http::down($request['url'], $saveFile, $request['params']);
        return $result;
    }

    /**
     * 执行接口
     * @return array
     */
    private function run($down = false)
    {
        $params['format'] = 'json';
        $params['timestamp'] = $this->data['timestamp'] ?? time();
        $params['domain'] = $this->data['domain'] ?? request()->domain();
        $params['ip'] = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : getClientIp();
        $params['sid'] = $this->sid ?: 0;
        $params['version'] = $this->data['version'] ?? config('hiphp.version');
        $params = array_merge($params, $this->data);
        $params = array_filter($params);
        if(isset($this->data['sign'])){
            $params['sign'] = $this->data['sign'];
        }else{
            $signArr = $params;
            $signArr['token'] = $this->token ?: 0;
            $params['sign'] = \hi\Sign::getSign($signArr, $params['sid'], true);
        }
        if ($down === true) {
            $result = [];
            $result['url'] = $this->api;
            $result['params'] = http_build_query($params);
            return $result;
        }
        $type = $this->type;
        $result = Http::$type($this->api, $params);
        return self::_response($result);
    }

    /**
     * 以数组格式返回
     * @return array
     */
    private function _response($result = [])
    {
        if (is_file($this->lock)) {
            @unlink($this->lock);
        }

        if (!$result || isset($result['errno'])) {
            if (isset($result['msg'])) {
                return ['code' => 0, 'msg' => $result['msg']];
            }

            return ['code' => 0, 'msg' => '请求的接口网络异常，请稍后在试'];
        } else {

            return json_decode($result, true);
        }
    }
}
