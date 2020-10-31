<?php
namespace app\system;
use app\common\controller\Module;
use think\facade\Db;
defined('IN_SYSTEM') or die('Access Denied');
class system extends Module
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
        return true;
    }
    /**
     * 升级前
     * @return mixed
     */
    public function upgrade(){
        return true;
    }
    /**
     * 升级后
     * @return mixed
     */
    public function upgradeAfter(){
		$prefix = config('database.connections.mysql.prefix');
        $res = Db::query("SELECT COLUMN_NAME FROM information_schema.columns WHERE TABLE_NAME='{$prefix}system_user'");
        $fields = array_column($res,"COLUMN_NAME");
		if(!in_array('test', $fields)){
			Db::execute("alter table {$prefix}system_user add test varchar(10) not Null");
		}
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
    public function uninstallAfter(){
		
        return true;
    }
}