<?php
/**
 * 文件上传
 * @author Flc <2016-01-05 14:02:56>
 * @example
 *     use Libs\File;
 *     
 *     File::upload(); // 通用文件上传
 *     File::generateMultipleThumbs();     // 生成多个系统定义尺寸缩略图(尺寸配置，参考core.php:THUMB_SIZE_SPECS)
 *     File::generateThumb(); // 生成单个缩略图
 *      
 */
namespace Common\Service;

use Think\Upload;
use Think\Image;

class File{
    /**
     * 通用文件上传
     * @param  array   $file     文件信息数组，键名，必须为
     * @param  array   $exts    支持的上传格式
     * @param  integer $size     文件大小
     * @param  boolean $is_thunb 是否生成缩略图
     * @return [type]        [description]
     */
    public static function upload($file, $exts = array(), $size = 0, $is_thumb = false){
        if (!$size) $size = C('FILE_UPLOAD_MAX_SIZE');  //文件大小

        // A. 开始上传文件
        $rootPath = 'Uploads/';  //设置附件上传根目录        
        $upload           = new Upload();  // 实例化上传类
        $upload->maxSize  = $size ;// 设置附件上传大小
        $upload->exts     = count($exts) == 0 ? '' : $exts; //array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath = ROOT_PATH . $rootPath; // 设置附件上传根目录
        $info   =   $upload->upload($file);
        if(!$info) {
            return array('status' => false, 'msg' => $upload->getError());
        }

        // B. 拼装数据
        $data     = $info['file'];
        $filepath = $data['filepath'] = $rootPath . $data['savepath'] . $data['savename']; //文件存储目录

        // C. 写入数据库
        $insert_data = array(
            'name'       => $data['name'],
            'size'       => $data['size'],
            'filepath'   => $filepath,
            'md5'        => $data['md5'],
            'sha1'       => $data['sha1'],
            'created_at' => time()
        );
        if (!$file_id = M('files')->add($insert_data)) {
            return array('status' => false, 'msg' => '文件写入失败');
        }
        $data['fileid'] = $file_id;

        // D. 生成缩略图
        /*if ($is_thumb && $typename == 'image') {
            $rs = self::generateMultipleThumbs($filepath);
            if ($rs && is_array($rs)) {
                $data['thumbs'] = $rs;
            }
        }*/

        return array('status' => true, 'msg' => '上传成功', 'data' => $data);
    }

    /**
     * 生成多个系统定义尺寸缩略图(尺寸配置，参考core.php:THUMB_SIZE_SPECS)
     * @param  string $img_url 文件图片
     * @return array           
     */
    public static function generateMultipleThumbs($img_url){
        $result = array();

        $sizes = C('THUMB_SIZE_SPECS');
        foreach ($sizes as $k => $v) {
            if ($v[0] > 0 && $v[1] > 0) {
                $result[$k] = self::generateThumb($img_url, $v[0], $v[1]);
            }
        }

        return $result;
    }

    /**
     * 生成单个缩略图
     * @param  string $img_url 文件路径
     * @param  number $width   宽度
     * @param  number $height  高度
     * @return array           文件相关信息
     */
    public static function generateThumb($img_url, $width, $height){
        static $imgs = array();
        $key = md5($img_url);

        // 获取文件相关数据
        if (array_key_exists($key, $imgs)) {
            $info = $imgs[$key];
        } else {
            $imgInfo = pathinfo($img_url);  // 获取文件信息
            $info    = $imgs[$key] = array(
                'ext'  => $imgInfo['extension'],  // 文件后缀
                'path' => $imgInfo['dirname'],  // 文件保存路径
                'name' => $imgInfo['filename'],  // 原文件名
            );
        }

        // 开始生成缩略图
        $image = new Image(); 
        $image->open($img_url);

        // 新文件名
        $fileTemp = C('THUMB_SAVE_NAME_TEMP');
        $filename = str_replace(
            array('{filename}', '{width}', '{height}', '{ext}'), 
            array($info['name'], $width, $height, $info['ext']), 
            $fileTemp
        );

        // 文件保存路径
        $filepath = $info['path'] . '/' . $filename;

        // 生成缩略图
        $image->thumb($width, $height)->save($filepath);

        // 读取文件信息
        $image->open($filepath);
        
        return array(
            'fileshow' => U('/file/read') . '?file=' . $filepath,
            'filename' => $filename,
            'filepath' => $filepath,
            'ext'      => $info['ext'],
            'width'    => $image->width(),
            'height'   => $image->height(),
            'path'     => $info['path']
        );
    }
}