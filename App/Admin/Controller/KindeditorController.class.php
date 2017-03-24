<?php
namespace Admin\Controller;

use Think\Controller;
use Common\Service\File;

class KindeditorController extends BaseController {

    /**
     * 文件上传
     */
    public function upload(){
        $dirname = I('get.dir', 'file', 'trim');

        // 校验
        if (!isset($_FILES['imgFile'])) {
            $this->ajaxReturn(array('error' => 1, 'message' => '文件不存在'));
        }

        //定义允许上传的文件扩展名
        $exts = array(
            'image' => array('gif', 'jpg', 'jpeg', 'png', 'bmp'),
            'flash' => array('swf', 'flv'),
            'media' => array('swf', 'flv', 'mp3', 'wav', 'wma', 'wmv', 'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb'),
            'file'  => array('doc', 'docx', 'xls', 'xlsx', 'ppt', 'htm', 'html', 'txt', 'zip', 'rar', 'gz', 'bz2'),
        );
        if (!array_key_exists($dirname, $exts)) {
            $this->ajaxReturn(array('error' => 1, 'message' => '文件类型异常'));
        }

        // 调整商城文件的键名
        $file['file'] = $_FILES['imgFile'];

        // 开始上传
        $rs = File::upload($file, $exts[$dirname]);
        if (!$rs || !$rs['status'] || !$rs['data']['filepath']) {
            $this->ajaxReturn(array('error' => 1, 'message' => $rs['msg'] ?: '未知错误'));
        }

        $this->ajaxReturn(array('error' => 0, 'url' => __ROOT__ . '/' . $rs['data']['filepath']));
    }
}
?>