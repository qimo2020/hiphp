<?php declare(strict_types=1);
namespace app\member\admin;
defined('IN_SYSTEM') or die('Access Denied');
use app\system\admin\Base;
use plugins\builder\builder;
class Common extends Base
{
    public $messages = [];
    protected static $error;
    protected function initialize()
    {
        checkPluginDepends(['builder']);
        $this->buiderObj = new builder($this);
        $this->messages = config('message');
    }

    public function getError(){
        return self::$error;
    }

}