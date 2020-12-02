<?php declare(strict_types=1);
namespace app\member\model;
defined('IN_SYSTEM') or die('Access Denied');

use think\facade\Db;
use think\Model;
class MemberAuth extends Model
{
    public static $error;

    public function _save($post){
        Db::startTrans();
        try {
            if(isset($post['id']) && is_numeric($post['id'])){
                $model = self::where('member_id', $post['member_id'])->find();
                if(isset($post['tid'])){
                    $model->tid = $post['tid'];
                }
                if(isset($post['account'])){
                    $model->account = $post['account'];
                }
                if(isset($post['password'])){
                    $model->password = password_hash($post['password'], PASSWORD_DEFAULT);
                }
            }else{
                $model = new MemberAuth();
                $model->member_id = $post['member_id'];
                $model->tid = $post['tid'];
                $model->account = $post['account'];
                if(isset($post['password'])){
                    $model->password = password_hash($post['password'], PASSWORD_DEFAULT);
                }
            }
            $model->save();
            Db::commit();
            return ['id'=>$model->id, 'uuid'=>$model->uuid, 'status'=>$model->status];
        }catch (\Exception $e){
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }

    public function getError(){
        return self::$error;
    }
}