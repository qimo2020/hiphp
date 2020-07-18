<?php declare(strict_types=1);
// +----------------------------------------------------------------------
// | HiPHP框架[基于ThinkPHP6.0开发]
// +----------------------------------------------------------------------
// | Copyright (c) http://www.hiphp.net
// +----------------------------------------------------------------------
// | HiPHP承诺基础框架永久免费开源，您可用于学习和商用，但必须保留软件版权信息。
// +----------------------------------------------------------------------
// | Author: 祈陌 <3411869134@qq.com>，开发者QQ群：829699898
// +----------------------------------------------------------------------
namespace app\common\lib;
use think\facade\Db;

/**
 * 数据表结构操作类
 * @package app\common\model
 */
class DbStruct
{
    protected static $tableName = '';
    public static $errorMsg = '';
    public static function add(string $tableName, array $fields)
    {
        if(!isset($tableName)){
            self::$errorMsg = 'tableName not found';
            return false;
        }
        self::$tableName = strtolower($tableName);
        if(false === self::check($fields)){
            return false;
        }
        if(count($fields) == count($fields, 1)){
            if(!isset($fields['name']) || !isset($fields['type'])){
                self::$errorMsg = 'name or type params not found';
                return false;
            }
            if(in_array(strtolower($fields['name']), array_keys(Db::name(self::$tableName)->getFields()))){
                self::$errorMsg = 'the field('.$fields['name'].') is exist';
                return false;
            }
            $sql = self::executeSql($fields);
        }else{
            foreach ($fields as $k=>$v){
                if(isset($v['name']) && isset($v['type']) && !in_array(strtolower($v['name']), array_keys(Db::name(self::$tableName)->getFields()))){
                    $sql = self::executeSql($v);
                }
            }
        }
        return $sql;
    }

    protected static function executeSql(array $fields){
        $sql = 'ALTER TABLE `' . hiConfig('database.connections.mysql.prefix') . self::$tableName . '` ADD `' . strtolower($fields['name']) . '` ';
        $sql .= isset($fields['length']) && $fields['length'] ? $fields['type'].'('.$fields['length'].')' : $fields['type'];
        if(!in_array($fields['type'], ['text'])){
            $sql .= " NOT NULL";
        }
        if(isset($fields['value']) && $fields['value']){
            $sql .= " DEFAULT '".$fields['value']."'";
        }
        if((isset($fields['index']) && $fields['index']) || (isset($fields['unique']) && $fields['unique'])){
            $sql .= ",ADD";
            if($fields['unique']){
                $sql .= " unique";
            }
            if($fields['index']){
                $sql .= " INDEX index_".$fields['name']."(`".$fields['name']."`)";
            }
        }
        if(isset($fields['comment']) && $fields['comment']){
            $sql .= " COMMENT '".$fields['COMMENT']."'";
        }
        Db::execute($sql);
        return $sql;
    }

    protected static function check(array $fields){
        if($fields['type'] != 'text' && (!isset($fields['length']) || !$fields['length'])){
            self::$errorMsg = 'needed field length';
            return false;
        }
        if(in_array($fields['type'], ['text']) && (isset($fields['value']) && $fields['value'])){
            self::$errorMsg = 'the field no default value';
            return false;
        }
        if((isset($fields['unique']) && 1 == $fields['unique']) && (!isset($fields['index']) || 1 != $fields['index'])){
            self::$errorMsg = 'the field no index required';
            return false;
        }
        return true;
    }

    public static function delete(string $tableName, array $fields)
    {
        foreach ($fields as $v) {
            if(in_array(strtolower($v), array_keys(Db::name($tableName)->getFields()))){
                Db::execute('ALTER TABLE ' . hiConfig('database.connections.mysql.prefix') . $tableName . ' DROP ' . strtolower($v));
            }
        }
        return true;
    }
}
