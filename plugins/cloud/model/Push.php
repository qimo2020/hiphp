<?php declare(strict_types=1);
namespace plugins\cloud\model;
use plugins\cloud\lib\Cloud;
use think\Model;

class Push extends Model
{
    public $error = '';
    public $apiBind = 'bind';
    public $apiUrl;
    public $cloud;
    public function __construct()
    {
        $this->updatePath  = root_path().'backup/uppack/';
        $filePath = root_path() . 'plugins/cloud/config/cloud.php';
        $cloudConfigs = file_exists($filePath) ? include_once ($filePath) : ['identifier'=>''];
        $cloudHost = config('clouds.cloud_push_domain') ?: '';
        $this->cloud = new Cloud($cloudConfigs['identifier'], $this->updatePath, $cloudHost);
        $this->apiUrl = $this->cloud->apiUrl();
    }

}