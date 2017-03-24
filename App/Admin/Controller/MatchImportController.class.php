<?php
namespace Admin\Controller;
use Think\PageAdmin;
use Common\Support\Validator;
use Think\Upload;
use Think\Log;


class MatchimportController extends BaseController{
	public function index(){
        $match_id = I('match_id');
        $count = M("broadcast_match")->where(array("match_id"=>$match_id))->count();
        $Page  = new PageAdmin($count, 15);
        $lists = M("broadcast_match")->where(array("match_id"=>$match_id))
            ->limit($Page->firstRow.','.$Page->listRows)->select();
        $match = M("matchs")->where(array("id"=>$match_id))->find();
        $this->assign("match",$match);
        $pages['show']  = $Page->show();  //分页输出
        $pages['total'] = $count;  //总数
        $pages['lists'] = $lists;
        $this->pages    = $pages;
		$this->display();
	}

    /**
     * 播报系统编辑
     */
    public function edit(){
        $id = I('id');
        $res = M("broadcast_match")->where(array("id"=>$id))->find();
        $match = M("matchs")->where(array("id"=>$res['match_id']))->find();
        $res['aDances']    = explode(',', $res['dances']);  // 舞转为数组
        $this->assign("match",$match);
        $this->assign("info",$res);
        $defineDances = defineDances();
        $this->defineDances = $defineDances;
        //var_dump($res);
        //var_dump($match);
        $this->display();
    }

    /**
     * 删除
     *
     * @return [type] [description]
     */
    public function destroy()
    {
        $id           = I('get.id', 0, 'intval');
        // C. 删除
        M('broadcast_match')->where(['id' => $id])->delete();

        redirect($_SERVER['HTTP_REFERER']);
    }

	/**
     * 文件导入
     * @return [type] [description]
     */
    public function import()
    {
        //$this->ajaxReturn(['status' => false, 'msg' => '参数错误']);
        $match_id = I('get.match_id');

        if (empty($match_id) ||
            ! $match = M('matchs')->where(['id' => $match_id])->find()
        ) {
            $this->ajaxReturn(['status' => false, 'msg' => '参数错误']);
        } 


        // A. 上传文件
        $rootPath = ROOT_PATH . 'Uploads/';
        $savePath = 'Temp/';

        $upload           = new Upload();// 实例化上传类
        $upload->maxSize  = 20971520 ;// 设置附件上传大小
        $upload->exts     = array('xls','xlsx');// 设置附件上传类型
        $upload->rootPath = $rootPath; // 设置附件上传根目录
        $upload->savePath = $savePath; // 设置附件上传（子）目录

        if (! $info = $upload->upload()) {
            $this->ajaxReturn(['status' => false, 'msg' => $upload->getError()]);
        }

        // B. 获取文件数据
        $file = $info['file'];

        // B.1 文件路径
        $filepath = $rootPath . $file['savepath'] . $file['savename'];

        
        // C. 读取excel文件数据
        try {
            $res = $this->readExcel($filepath);
        } catch (\Exception $e) {
            $this->ajaxReturn(['status' => false, 'msg' => '文件异常']);
        }

        //$this->ajaxReturn(['status' => false, 'msg' => $res]);

        
        if (! $res ||
            ! $res['status'] ||
            ! is_array($res['data'])
        ) {
            $this->ajaxReturn(['status' => false, 'msg' => $res['msg'] ?: '文件异常']);
        }

        // C.1 excel数据
        $data = $res['data'];

        array_shift($data);

        // C.2 数据量校验
        if (count($data) == 0) {
            $this->ajaxReturn(['status' => false, 'msg' => '无相关数据需处理']);
        }

        // D. 写入到数据库
        $success = $error = 0;  // 成功或失败的数量

        foreach ($data as $row) {
            try {
                $importEventRs = $this->importToEvent($row, $match_id);
                if (! $importEventRs || ! $importEventRs['status']) {
                    $error ++;
                    continue;
                }
            } catch (\Exception $e) {
                $error ++;
                continue;
            }

            $success++;
        }

        $this->ajaxReturn(['status' => true, 'msg' => '处理成功', 'data' => ['success' => $success, 'error' => $error]]);
    }

    /**
     * 导入到表
     * @return [type] [description]
     */
    protected function importToEvent($data = [], $match_id)
    {
        list(
            $merge_num,
            $type,
            $match_time,
            $group_code,
            $group_name,
            $group,
            $back_num,
            $match_num,
            $pass_num,
            $race_time,
            $dances
        ) = $data;



        // B. 写入数据
        $dbTrans = D();
        $dbTrans->startTrans();



        // B.2 写入数据
        $data = [
            'merge_num' =>$merge_num,
            'type'=>$type,
            'match_time'=>$match_time,
            'group_code'=>$group_code,
            'group_name'=>$group_name,
            'group'=>$group,
            'back_num'=>$back_num,
            'match_num'=>$match_num,
            'pass_num'=>$pass_num,
            'race_time'=>$race_time,
            'dances'=>$dances,
            'add_time'             => time(),
            'updated_time'             => time(),
            'match_id' =>$match_id
        ];
        //Log::write($match_time,'match_time');
        if (! $event_id = M('broadcast_match')->add($data)) {
            return ['status' => false, 'msg' => '提交失败，请稍候再试'];
        }

        $dbTrans->commit();

        return ['status' => true, 'msg' => '成功'];
    }

    /**
     * 读取Excel文件数据
     * @param  string $filepath 文件路径
     * @return array           
     */
    protected function readExcel($filepath)
    {
        if (!is_file($filepath)) {
            return ['status' => false, 'msg' => '文件异常'];
        }

        // 读取excel文件
        Vendor('PHPExcel.PHPExcel');
        $objReader = \PHPExcel_IOFactory::createReader('Excel2007'); 

        $objReader->setReadDataOnly(true);

        $objPHPExcel        = $objReader->load($filepath); 
        $objWorksheet       = $objPHPExcel->getActiveSheet(); 
        $highestRow         = $objWorksheet->getHighestRow(); 
        $highestColumn      = $objWorksheet->getHighestColumn(); 
        $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn); 

        $data = []; 
        for ($row = 1; $row <= $highestRow; $row++) {
            for ($col = 0; $col < $highestColumnIndex; $col++) { 
                $data[$row][] =(string) $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
            } 
        } 
        return ['status' => true, 'msg' => '成功', 'data' => array_values($data)];
    }

    /**
     * 赛事编辑保存
     * @return [type] [description]
     */
    public function update()
    {
        $id          = I('post.id','' , 'intval');
        $match_id    = I('post.match_id', '', 'intval');
        $merge_num   = I('post.merge_num', '', 'trim');
        $type        = I('post.type', '', 'trim');
        $dances      = I('post.dances', []);
        $match_time  = I('post.match_time', '', 'trim');
        $group_code  = I('post.group_code', '', 'intval');
        $group_name  = I('post.group_name', '', 'trim');
        $group       = I('post.group', '', 'trim');
        $back_num    = I('post.back_num', '', 'trim');
        $match_num   = I('post.match_num', '', 'trim');
        $pass_num    = I('post.pass_num', '', 'intval');
        $race_time   = I('post.race_time', '', 'trim');
        $publish_status    = I('post.publish_status', '', 'intval');
        $collect_status    = I('post.collect_status', '', 'intval');



        // B. 写入数据
        $dbTrans = M();
        $dbTrans->startTrans();


        // B.2 更新数据
        $data = [
            'merge_num' =>$merge_num,
            'type'=>$type,
            'match_time'=>$match_time,
            'group_code'=>$group_code,
            'group_name'=>$group_name,
            'group'=>$group,
            'back_num'=>$back_num,
            'match_num'=>$match_num,
            'pass_num'=>$pass_num,
            'race_time'=>$race_time,
            'dances'=>implode(',', $dances),
            'update_time'=> time(),
            'collect_status'=>$collect_status,
            'publish_status'=>$publish_status
        ];

        if (false === M('broadcast_match')->where(['id' => $id])->save($data)) {
            $dbTrans->rollback();
            $this->ajaxReturn(['status' => false, 'msg' => '提交失败，请稍候再试']);
        }

       

        $dbTrans->commit();

        $this->ajaxReturn(['status' => true, 'msg' => '成功', 'url' => U('index', ['match_id' => $match_id])]);
    }

}