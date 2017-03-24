<?php
namespace Admin\Controller;

use Think\PageAdmin;
use Common\Support\Validator;
use Think\Model;

/**
 * 赛事管理控制器
 * 
 * @author Flc <2016-08-23 19:29:09>
 */
class MatchController extends BaseController
{
    /**
     * 赛事列表
     * @return [type] [description]
     */
    public function index()
    {
        $prefix = C('DB_PREFIX');

        // 开始查询
        $model = M('matchs');

        // 查询总数
        $tModel = clone $model;
        $count = $tModel->count();

        // 分页
        $Page  = new PageAdmin($count, C('PAGE_NUM'));

        // 列表数据
        $lists = $model->limit($Page->firstRow.','.$Page->listRows)->order(['id' => 'desc'])->select();

        $pages['show']  = $Page->show();  //分页输出
        $pages['total'] = $count;  //总数
        $pages['lists'] = $lists;

        $this->seotitle = '赛程管理';
        $this->pages    = $pages;
        $this->display();
    }

    /**
     * 赛事添加
     * @return [type] [description]
     */
    public function add()
    {
        $defineMatchStage = defineMatchStage();

        $this->defineMatchStage = $defineMatchStage;
        $this->seotitle         = '发布赛程';
        $this->display();
    }

    /**
     * 保存添加
     * @return [type] [description]
     */
    public function store()
    {
        $title            = I('post.title', '', 'trim');
        $content          = I('post.content', '');
        $stage            = I('post.stage', 0, 'intval');
        $status           = I('post.status', 0, 'intval');
        $contact_number   = I('post.contact_number', '', 'trim');
        $sign_start_time  = I('post.sign_start_time', '', 'trim');
        $sign_end_time    = I('post.sign_end_time', '', 'trim');
        $match_start_time = I('post.match_start_time', '', 'trim');
        $match_end_time   = I('post.match_end_time', '', 'trim');
        $pay_end_time     = I('post.pay_end_time', '', 'trim');

        // A. 校验
        // A.1 赛事名称
        if (empty($title)) {
            $this->ajaxReturn(['status' => false, 'msg' => '请输入赛事名称']);
        }

        // A.1.1 阶段
        if (empty($stage) ||
            ! in_array($stage, [1, 2, 3, 4])
        ) {
            $this->ajaxReturn(['status' => false, 'msg' => '请选择阶段']);
        }

        // A.2 报名时间
        if (empty($sign_start_time) ||
            empty($sign_end_time) ||
            false === strtotime($sign_start_time) ||
            false === strtotime($sign_end_time)
        ) {
            $this->ajaxReturn(['status' => false, 'msg' => '请选择报名时间']);
        }

        // A.2.1 报名时间大小
        if (strtotime($sign_end_time) < strtotime($sign_start_time)) {
            $this->ajaxReturn(['status' => false, 'msg' => '报名截止时间必须大于开始时间']);
        }

        // A.3 比赛时间
        if (empty($match_start_time) ||
            empty($match_end_time) ||
            false === strtotime($match_start_time) ||
            false === strtotime($match_end_time)
        ) {
            $this->ajaxReturn(['status' => false, 'msg' => '请选择比赛时间']);
        }

        // A.3.1 比赛时间大小
        if (strtotime($match_end_time) < strtotime($match_start_time)) {
            $this->ajaxReturn(['status' => false, 'msg' => '比赛截止时间必须大于开始时间']);
        }

        // A.4 截止支付时间
        if (empty($pay_end_time) ||
            false === strtotime($pay_end_time)
        ) {
            $this->ajaxReturn(['status' => false, 'msg' => '请选择截止支付时间']);
        }

        // B. 写入数据
        $dbTrans = new Model;
        $dbTrans->startTrans();

        // B.1 创建赛程
        $data = [
            'title'            => $title,
            'content'          => $content,
            'stage'            => $stage,
            'status'           => $status == 1 ? 1 : 0,
            'contact_number'   => $contact_number,
            'sign_start_time'  => strtotime($sign_start_time),
            'sign_end_time'    => strtotime($sign_end_time),
            'match_start_time' => strtotime($match_start_time),
            'match_end_time'   => strtotime($match_end_time),
            'pay_end_time'     => strtotime($pay_end_time),
            'created_at'       => time(),
            'updated_at'       => time(),
        ];
        if (! $match_id = M('matchs')->add($data)) {
            $dbTrans->rollback();
            $this->ajaxReturn(['status' => false, 'msg' => '提交失败，请稍候再试']);
        }

        // B.2 创建板块
        // foreach ([
        //     ['name' => '职业组', 'ident' => 'PROC'],
        //     ['name' => '专业组', 'ident' => 'SPEC'],
        //     ['name' => '业余组', 'ident' => 'AMAT'],
        // ] as $ipate) {
        //     // B.2.1 创建数据
        //     $ipate_data = [
        //         'match_id'   => $match_id,
        //         'name'       => $ipate['name'],
        //         'ident'      => $ipate['ident'],
        //         'is_cross'   => 0,
        //         'created_at' => time(),
        //         'updated_at' => time()
        //     ];
        //     if (! $ipate_id = M('match_ipates')->add($ipate_data)) {
        //         $dbTrans->rollback();
        //         $this->ajaxReturn(['status' => false, 'msg' => '提交失败，请稍候再试']);
        //     }

        //     // B.2.2 创建关联关系
        //     $ipate_r_data = [
        //         'id'  => $ipate_id,
        //         'pid' => 0
        //     ];
        //     if (! M('match_ipate_relation')->add($ipate_r_data)) {
        //         $dbTrans->rollback();
        //         $this->ajaxReturn(['status' => false, 'msg' => '提交失败，请稍候再试']);
        //     }
        // }

        $dbTrans->commit();        

        $this->ajaxReturn(['status' => true, 'msg' => '成功', 'url' => U('index')]);
    }

    /**
     * 赛程编辑
     * @return [type] [description]
     */
    public function edit()
    {   
        $defineMatchStage = defineMatchStage();
        $id = I('get.id', 0, 'intval');

        if (empty($id) ||
            ! $info = M('matchs')->where(['id' => $id])->find())
        {
            $this->error('参数错误');
        }

        $this->defineMatchStage = $defineMatchStage;
        $this->seotitle  = '赛程编辑';
        $this->info      = $info;
        $this->display();
    }

    /**
     * 赛事编辑保存
     * @return [type] [description]
     */
    public function update()
    {
        $id               = I('post.id', 0, 'intval');
        $title            = I('post.title', '', 'trim');
        $content          = I('post.content', '');
        $stage            = I('post.stage', 0, 'intval');
        $status           = I('post.status', 0, 'intval');
        $contact_number   = I('post.contact_number', '', 'trim');
        $sign_start_time  = I('post.sign_start_time', '', 'trim');
        $sign_end_time    = I('post.sign_end_time', '', 'trim');
        $match_start_time = I('post.match_start_time', '', 'trim');
        $match_end_time   = I('post.match_end_time', '', 'trim');
        $pay_end_time     = I('post.pay_end_time', '', 'trim');

        // A. 校验
        // A.0 数据
        if (empty($id) ||
            ! $info = M('matchs')->where(['id' => $id])->find())
        {
            $this->ajaxReturn(['status' => false, 'msg' => '参数错误']);
        }

        // A.1.1 阶段
        if (empty($stage) ||
            ! in_array($stage, [1, 2, 3, 4])
        ) {
            $this->ajaxReturn(['status' => false, 'msg' => '请选择阶段']);
        }

        // A.1 赛事名称
        if (empty($title)) {
            $this->ajaxReturn(['status' => false, 'msg' => '请输入赛事名称']);
        }

        // A.2 报名时间
        if (empty($sign_start_time) ||
            empty($sign_end_time) ||
            false === strtotime($sign_start_time) ||
            false === strtotime($sign_end_time)
        ) {
            $this->ajaxReturn(['status' => false, 'msg' => '请选择报名时间']);
        }

        // A.2.1 报名时间大小
        if (strtotime($sign_end_time) < strtotime($sign_start_time)) {
            $this->ajaxReturn(['status' => false, 'msg' => '报名截止时间必须大于开始时间']);
        }

        // A.3 比赛时间
        if (empty($match_start_time) ||
            empty($match_end_time) ||
            false === strtotime($match_start_time) ||
            false === strtotime($match_end_time)
        ) {
            $this->ajaxReturn(['status' => false, 'msg' => '请选择比赛时间']);
        }

        // A.3.1 比赛时间大小
        if (strtotime($match_end_time) < strtotime($match_start_time)) {
            $this->ajaxReturn(['status' => false, 'msg' => '比赛截止时间必须大于开始时间']);
        }

        // A.4 截止支付时间
        if (empty($pay_end_time) ||
            false === strtotime($pay_end_time)
        ) {
            $this->ajaxReturn(['status' => false, 'msg' => '请选择截止支付时间']);
        }

        // B. 写入数据
        $data = [
            'title'            => $title,
            'content'          => $content,
            'stage'            => $stage,
            'status'           => $status == 1 ? 1 : 0,
            'contact_number'   => $contact_number,
            'sign_start_time'  => strtotime($sign_start_time),
            'sign_end_time'    => strtotime($sign_end_time),
            'match_start_time' => strtotime($match_start_time),
            'match_end_time'   => strtotime($match_end_time),
            'pay_end_time'     => strtotime($pay_end_time),
            'updated_at'       => time(),
        ];
        if (false === M('matchs')->where(['id' => $id])->save($data)) {
            $this->ajaxReturn(['status' => false, 'msg' => '提交失败，请稍候再试']);
        }

        $this->ajaxReturn(['status' => true, 'msg' => '成功', 'url' => U('index')]);
    }
}