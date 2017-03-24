<?php
namespace Admin\Controller;

use Common\Service\File;

class FileController extends BaseController {

    /**
     * 后台上传通用页面
     */
    public function index(){
        $type   = I('get.type', 'other', 'trim');
        $number = I('get.number', '1', 'intval');
        $size   = I('get.size', C('FILE_UPLOAD_MAX_SIZE'), 'intval');

        // 上传类型
        $preTypes = C('FILE_UPLOAD_TYPES');
        if (!array_key_exists($type, $preTypes)) {
            $type = 'other';
        }
        $extStr = $preTypes[$type];
        $exts   = explode('|', $extStr);

        // 页面输出的文件类型
        $extArrs = array_map(function($v){
            return '*.' . $v;
        }, $exts);

        // 文件数量
        $number = max($number, 1);

        // 文件大小
        if ($size <= 0 ) {
            $size = C('FILE_UPLOAD_MAX_SIZE');
        }

        $this->type    = $type;
        $this->exts    = $exts;
        $this->extArrs = $extArrs;
        $this->number  = $number;
        $this->size    = $size;
        $this->formatSize = $this->getRealSize($size);
        $this->display();
    }

    /**
     * 文件上传
     */
    public function upload(){
        $type   = I('get.type', 'other', 'trim');
        $size   = I('get.size', C('FILE_UPLOAD_MAX_SIZE'), 'intval');

        if (!isset($_FILES['file'])) {
            $this->ajaxReturn(array('status' => false, 'msg' => '上传文件不存在'));
        }
        $file = $_FILES;

        // 上传类型
        $preTypes = C('FILE_UPLOAD_TYPES');
        if (!array_key_exists($type, $preTypes)) {
            $type = 'other';
        }
        $extStr = $preTypes[$type];
        $exts   = explode('|', $extStr);

        // 开始上传
        $rs = File::upload($file, $exts, $size);
        if (!$rs || !$rs['status'] || !$rs['data']) {
            $this->ajaxReturn(array('status' => false, 'msg' => $rs['msg'] ?: '未知错误'));
        }

        $this->ajaxReturn(array('status' => true, 'msg' => '上传成功', 'data' => $rs['data']));
    }

    /**
     * 返回文件大小
     * @param int $size 文件大小
     */
    protected function getRealSize($size){
        $kb = 1024;          // Kilobyte
        $mb = 1024 * $kb;    // Megabyte
        $gb = 1024 * $mb;    // Gigabyte
        $tb = 1024 * $gb;    // Terabyte

        if($size < $kb)
            return $size.'B';

        else if($size < $mb)
            return round($size/$kb,2).'KB';

        else if($size < $gb)
            return round($size/$mb,2).'MB';

        else if($size < $tb)
            return round($size/$gb,2).'GB';

        else
            return round($size/$tb,2).'TB';
    }
}
?>