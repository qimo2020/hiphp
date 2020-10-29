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
namespace app\common\model;
use app\common\model\SystemAnnexGroup as GroupModel;
use think\Model;
use think\facade\Db;
use think\facade\Filesystem;
use think\model\concern\SoftDelete;
/**
 * 附件模型
 * @package app\common\model
 */
class SystemAnnex extends Model
{
    use SoftDelete;
    public static $error = '';
    protected $updateTime = false;
    protected $deleteTime = 'delete_time';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    /**
     * [兼容旧版]附件上传
     * @param string $from 来源
     * @param string $group 附件分组,默认sys[系统]，模块格式：m_模块名，插件：p_插件名
     * @param string $water 水印，参数为空默认调用系统配置，no直接关闭水印，image 图片水印，text文字水印
     * @param string $thumb 缩略图，参数为空默认调用系统配置，no直接关闭缩略图，如需生成 500x500 的缩略图，则 500x500多个规格请用";"隔开
     * @param string $thumb_type 缩略图方式
     * @param string $input 文件表单字段名
     * @return json
     */
    public static function upload($from = 'input', $group = 'system', $water = '', $thumb = '', $thumbType = '', $input = 'file')
    {
        $param = [];
        $param['from'] = $from;
        $param['group'] = $group;
        $param['water'] = $water;
        $param['thumb'] = $thumb;
        $param['thumb_type'] = $thumbType;
        $param['input'] = $input;

        $params = request()->param();

        if (isset($params['full_path'])) {
            $param['full_path'] = true;
        }
        if (isset($params['driver'])) {
            $param['driver'] = $params['driver'];
        }
        if(isset($params['save_dir'])){
            $param['save_dir'] = $params['save_dir'];
        }
        if(isset($params['temp'])){
            $param['temp'] = $params['temp'];
        }
        return self::fileUpload($param);

    }

    /**
     * [新]附件上传
     * @param string $from 来源
     * @param string $group 附件分组,默认system[系统]，模块格式：模块名，插件：插件名
     * @param string $water 水印，参数为空默认调用系统配置，no直接关闭水印，image 图片水印，text文字水印
     * @param string $thumb 缩略图，参数为空默认调用系统配置，no直接关闭缩略图，如需生成 500x500 的缩略图，则 500x500多个规格请用";"隔开
     * @param string $thumb_type 缩略图方式
     * @param string $input 文件表单字段名
     * @param string $full_path 是否返回完整的文件路径(含域名)本地上传有效
     * @param string $driver 指定上传驱动
     * @return json
     */
    public static function fileUpload($param = [])
    {
        if (empty($param)) {
            $param = request()->param();
        }
        $defSavePath = $param['save_dir'] ?? (config('upload.save_dir') ?: './uploads');
        $from = isset($param['from']) ? strtolower($param['from']) : 'input';
        $group = $param['group'] ?? 'system';
        $water = $param['water'] ?? '';
        $thumb = isset($param['thumb']) && !empty($param['thumb']) ? $param['thumb'] : (isset($param['group']) && $param['group'] != 'system' ? config($param['group'].'.'.'_thumb_size') : config('upload.thumb_size')); //如分组无配置，则默认使用系统的上传缩略图尺寸配置
        $thumbType = isset($param['thumb_type']) && !empty($param['thumb_type']) ? $param['thumb_type'] : config('upload.thumb_type');
        $input = $param['input'] ?? 'file';
        $fullPath = $param['full_path'] ?? false;
        $driver = $param['driver'] ?? config('upload.driver');
        if (empty($water)) {
            if (config('upload.image_watermark') == 1) {
                $water = 'image';
            } elseif (config('upload.text_watermark') == 1) {
                $water = 'text';
            }
        }
        switch ($from) {
            case 'umeditor':
                $input = 'upfile';
                break;
            case 'ckeditor':
                $input = 'upload';
                break;
            case 'ueditor':
                $input = 'upfile';
                if (isset($_GET['action']) && $_GET['action'] == 'config') {
                    $content = file_get_contents('./static/js/editor/ueditor/config.json');
                    $json = preg_replace("/\/\*[\s\S]+?\*\//", "", $content);
                    echo json_encode(json_decode($json, 1), 1);
                    exit;
                }
                break;
            default:// 默认使用layui.upload上传控件
                break;
        }
        $file = request()->file($input);
        $data = [];
        if (empty($file)) {
            return self::result('未找到上传的文件(文件大小可能超过php.ini默认2M限制)！', $from);
        }
        if ($file->getMime() == 'text/x-php' || $file->getMime() == 'text/html') {
            return self::result('禁止上传php,html文件！', $from);
        }
        // 格式、大小校验
        if (self::checkExt(config('upload.image_ext'), $input)) {
            $type = 'image';
            if (config('upload.image_size') > 0 && !self::checkSize(config('upload.image_size') * 1024)) {
                return self::result('上传的图片大小超过系统限制[' . config('upload.image_size') . 'KB]！', $from);
            }
        } else if (self::checkExt(config('upload.file_ext'), $input)) {
            $type = 'file';
            if (config('upload.file_size') > 0 && !self::checkSize(config('upload.file_size') * 1024)) {
                return self::result('上传的文件大小超过系统限制[' . config('upload.file_size') . 'KB]！', $from);
            }
        } else if (self::checkExt('avi,mkv,mp3,mp4', $input)) {
            $type = 'media';
        } else {
            return self::result('非系统允许的上传格式！', $from);
        }
        if (!empty($driver) && $driver != 'local') {
            $fileSize = round($_FILES[$input]['size'] / 1024, 2);
            $param['file'] = $file;
            $info = runHook('system_annex_upload', $param, true);
            if(!$info){
                return self::result('文件上传失败！', $from);
            }
            if (!is_array($info)) {
                return self::result($info);
            } elseif (isset($info[0])) {
                $dataId = input('param.data_id', 0);
                $createTime = request()->time();
                // 挂载多个插件的时候，提取有效值
                foreach ($info as $k=>$v) {
                    if (isset($v['file'])) {
                        $data = [
                            'file' => $v['file'],
                            'hash' => $v['hash'],
                            'domain'=>$v['domain'],
                            'driver'=>$v['driver'],
                            'data_id' => $dataId,
                            'type' => $type,
                            'ext' => pathinfo($v['file'])['extension'],
                            'size' => $fileSize,
                            'group' => $group,
                            'create_time' => $createTime,
                        ];
                        $data['protocol'] = isset($v['protocol']) && $v['protocol'] ? 1 : 0;
                        continue;
                    }
                }
            }
        } else {
            // 文件存放路径
            $levelPath = isset($param['classify_dir']) && $param['classify_dir'] ? $param['classify_dir'] . '/' : ''; //分类级储存
            $filePath = isset($param['temp']) && $param['temp'] == 0 ? $group . '/' . $levelPath . $type : $group . '/temp/' . $levelPath . $type;
            // 执行上传
            if(strpos($defSavePath, '..') === false && $defSavePath != './uploads'){ //上传路径解析
                $filesConfig = config('filesystem');
                $uploadsConfig = ['type' => 'local'];
                $uploadsConfig['root'] = root_path() . 'public' . ltrim($defSavePath, '.');
                $filesConfig['disks']['uploads'] = $uploadsConfig;
                config($filesConfig, 'filesystem');
            }
            $upfile = Filesystem::disk('uploads')->putFile($filePath, $file);
            if(strpos($defSavePath, '..') !== false){
                $saveDir = root_path() . ltrim($defSavePath, '.\./');
                $dest = $saveDir . '/'. $upfile;
                if(is_file(root_path() . 'public/uploads/' . $upfile)){
                    $saveDir = pathinfo($dest)['dirname'];
                    if(!is_dir($saveDir)){
                        mkdir($saveDir,0777,true);
                    }
                    copy(root_path() . 'public/uploads/' . $upfile, $dest);
                }
            }
            $isFileExit = root_path() . (strpos($defSavePath, '..') !== false ? ltrim($defSavePath, '.\./') : 'public' . ltrim($defSavePath, '.'));
            if (!is_file($isFileExit . '/' . $upfile)) {
                return self::result($defSavePath . '/' . $upfile, $from);
            }
            $fileCount = 1;
            $fileSize = round($_FILES[$input]['size'] / 1024, 2);
            $upfileDir = strpos($defSavePath, '..') !== false ? $defSavePath : ltrim($defSavePath, '.');
            $data = [
                'file' => $upfileDir . '/' . $upfile,
                'hash' => $file->hash(),
                'data_id' => input('param.data_id', 0),
                'type' => $type,
                'size' => $fileSize,
                'group' => $group,
                'ext' => pathinfo($upfile)['extension'],
                'create_time' => request()->time(),
            ];
            //个别编辑器的回调处理
            $editorParams = request()->param();
            switch ($from) {
                case 'ckeditor':
                    if(isset($editorParams['CKEditorFuncNum']) && $editorParams['CKEditorFuncNum']){
                        $data['CKEditorFuncNum'] = $editorParams['CKEditorFuncNum'];
                    }
                    break;
                default:
                    break;
            }
            //个别编辑器的回调处理 end
            $data['thumb'] = [];
            if ($type == 'image') {
                $image = \think\Image::open('.' . $data['file']);
                // 水印
                if (!empty($water) && $water != 'no') {
                    if ($water == 'text') {
                        if (is_file('.' . config('upload.text_watermark_font'))) {
                            $image->text(config('upload.text_watermark_content'), '.' . config('upload.text_watermark_font'), config('upload.text_watermark_size'), config('upload.text_watermark_color'))->save('.' . $data['file']);
                        }
                    } elseif ($water == 'image') {
                        if (is_file('.' . config('upload.image_watermark_pic'))) {
                            $image->water('.' . config('upload.image_watermark_pic'), config('upload.image_watermark_location'), config('upload.image_watermark_opacity'))->save('.' . $data['file']);
                        }
                    }
                }
                // 缩略图
                if (!empty($thumb) && $thumb != 'no') {
                    $thumbs = explode(';', $thumb);
                    foreach ($thumbs as $k => $v) {
                        $tSize = explode('x', strtolower($v));
                        if (!isset($tSize[1])) {
                            $tSize[1] = $tSize[0];
                        }
                        $newThumb = $data['file'] . '_' . $tSize[0] . 'x' . $tSize[1] . '.' . strtolower(pathinfo($upfile->getInfo('name'), PATHINFO_EXTENSION));
                        $image->thumb($tSize[0], $tSize[1], $thumbType)->save('.' . $newThumb);
                        $thumbSize = round(filesize('.' . $newThumb) / 1024, 2);
                        $data['thumb'][$k]['type'] = 'image';
                        $data['thumb'][$k]['group'] = $group;
                        $data['thumb'][$k]['file'] = $newThumb;
                        $data['thumb'][$k]['size'] = $thumbSize;
                        $data['thumb'][$k]['hash'] = hash_file('md5', '.' . $newThumb);
                        $data['thumb'][$k]['ctime'] = request()->time();
                        $data['thumb'][$k]['data_id'] = input('param.data_id', 0);
                        $fileSize + $thumbSize;
                        $fileCount++;
                    }
                }
            }
            // 返回带域名的路径
            if ($fullPath && $driver == 'local') {
                $data['file'] = request()->domain() . $upfileDir . '/' .$group . '/'. $data['file'];
            }
        }
        return self::result('文件上传成功。', $from, 1, $data);
    }

    /**
     * favicon 图标上传
     * @return json
     */
    public static function favicon($input)
    {
        $file = request()->file($input);
        if (!self::checkExt('ico', $input)) {
            return self::result('只允许上传ico格式');
        }
        $file->move('./', 'favicon.ico');
        $data['file'] = '/favicon.ico';
        return self::result('文件上传成功。', 'input', 1, $data);
    }

    /**
     * 返回结果
     * @return array|string
     */
    private static function result($info = '', $from = 'input', $status = 0, $data = [])
    {
        $arr = [];
        switch ($from) {
            case 'kindeditor':
                if ($status == 0) {
                    $arr['error'] = 1;
                    $arr['message'] = $info;
                } else {
                    $arr['error'] = 0;
                    $arr['url'] = $data['file'];
                }
                break;
            case 'ckeditor':
                if ($status == 1) {
                    echo json_encode(['code'=>1, 'url'=>$data['file']]);
                } else {
                    echo json_encode(['code'=>0]);
                }
                exit;
                break;
            case 'umeditor':
            case 'ueditor':
                if ($status == 0) {

                    $arr['message'] = $info;
                    $arr['state'] = 'ERROR';

                } else {

                    $arr['message'] = $info;
                    $arr['url'] = $data['file'];
                    $arr['state'] = 'SUCCESS';

                }
                echo json_encode($arr, 1);
                exit;
                break;
            default:
                $arr['msg'] = $info;
                $arr['code'] = $status;
                $arr['data'] = $data;

                break;
        }
        return $arr;
    }

    /**
     * 检测上传文件后缀
     * @access public
     * @param  array|string   $ext    允许后缀
     * @return bool
     */
    public static function checkExt($ext, $input)
    {
        $file = request()->file($input);
        if (is_string($ext)) {
            $ext = explode(',', $ext);
        }
        $extension = $file->extension();
        if (!in_array($extension, $ext)) {
            return false;
        }
        return true;
    }

    /**
     * 检测上传文件大小
     * @access public
     * @param  integer   $size    最大大小
     * @return bool
     */
    public static function checkSize($size)
    {
        $file = request()->file('file');
        if ($file->getSize() > (int) $size) {
            return false;
        }
        return true;
    }




}
