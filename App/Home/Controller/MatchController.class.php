<?php
namespace Home\Controller;

use Think\Page;
use Think\Model;

/**
 * 赛事相关
 */
class MatchController extends CommonController
{
    /**
     * 初始化
     */
    public function __construct() {
        parent::__construct();

        /**
         * 检测用户登陆
         */
        $this->checkLoginInfo();    
    }
    
	/**
	 * 赛事列表
	 */
	public function index()
    {
        $model = M('matchs')->where(['status' => 1]);

        // 查询总数
        $tModel = clone $model;
        $count = $tModel->count();

        // 分页
        $Page  = new Page($count, 10);

        // 列表数据
        $lists = $model->limit($Page->firstRow.','.$Page->listRows)->order(['id' => 'desc'])->select();

        $pages['show']  = $Page->show();  //分页输出
        $pages['total'] = $count;  //总数
        $pages['lists'] = $lists;

        $this->seotitle = '赛事列表';
        $this->pages    = $pages;
        $this->display();
	}

    /**
     * 我报名的赛事
     * @return [type] [description]
     */
    public function attend()
    {
        $prefix = C('DB_PREFIX');
        $user   = $this->_getUser();
        $uid    = $user['id'];

        $model = M('matchs m')
            ->field('m.*, me.team_name, me.team_teacher')
            ->join($prefix . 'match_entrys me on me.match_id = m.id')
            ->where(['me.uid' => $uid]);

        // 查询总数
        $tModel = clone $model;
        $count = $tModel->count();

        // 分页
        $Page  = new Page($count, 10);

        // 列表数据
        $lists = $model->limit($Page->firstRow.','.$Page->listRows)->order(['id' => 'desc'])->select();

        $pages['show']  = $Page->show();  //分页输出
        $pages['total'] = $count;  //总数
        $pages['lists'] = $lists;

        $this->seotitle = '我的赛事';
        $this->pages    = $pages;
        $this->display();
    }

    /**
     * 赛事报名
     * @return [type] [description]
     */
    public function signup()
    {
        $user     = $this->_getUser();
        $match_id = I('get.match_id', 0, 'intval');
        $uid      = $user['id'];

        // A.0 个人校验实名认证
        $this->_ckUserIdCard();

        // A. 赛事
        if (! $match = M('matchs')->where(['id' => $match_id])->find()) {
            $this->error('参数错误');
        }

        // B. 校验报名是否已经报名
        if (M('match_entrys')->where(['match_id' => $match_id, 'uid' => $uid])->count() > 0) {
            $this->redirect('MatchEntry/signup', ['match_id' => $match_id]);
            exit;
            //$this->error('该赛事已报名，无需重复报名');
        }

        // // C. 是否在报名阶段
        // if (! in_array($match['stage'], [1, 3])) {
        //     $this->error('当前不在报名阶段');
        // }

        // // C. 判断报名时间是否合法
        // if (time() < $match['sign_start_time'] ||
        //     time() > $match['sign_end_time']
        // ) {
        //     $this->error('时间不在允许报名时间范围');
        // }

        $this->seotitle = '报名赛事';
        $this->match    = $match;
        $this->display();
    }

    /**
     * 赛事报名表单提交
     * @return [type] [description]
     */
    public function signup_store()
    {
        $user         = $this->_getUser();
        $match_id     = I('post.match_id', 0, 'intval');
        $team_name    = I('post.team_name', '', 'trim');
        $team_teacher = I('post.team_teacher', '', 'trim');
        $team_contact = I('post.team_contact', '', 'trim');
        $team_qq      = I('post.team_qq', '', 'trim');
        $uid          = $user['id'];

        // A. 校验
        // A.0 个人校验实名认证
        $this->_ckUserIdCard();
        
        // A.1 赛事
        if (! $match = M('matchs')->where(['id' => $match_id])->find()) {
            $this->ajaxReturn(['status' => false, 'msg' => '参数错误']);
        }

        // A.1.1. 是否在报名阶段
        if (! in_array($match['stage'], [1, 3])) {
            $this->ajaxReturn(['status' => false, 'msg' => '当前不在报名阶段']);
        }

        // A.2 判断报名时间是否合法
        /*if (time() < $match['sign_start_time'] ||
            time() > $match['sign_end_time']
        ) {
            $this->ajaxReturn(['status' => false, 'msg' => '时间不在允许报名时间范围']);
        }*/

        // A.3 校验报名是否已经报名
        if (M('match_entrys')->where(['match_id' => $match_id, 'uid' => $uid])->count() > 0) {
            $this->ajaxReturn(['status' => false, 'msg' => '该赛事已报名，不可重复报名']);
        }

        // A.4 代表队名称
        if (empty($team_name)) {
            $this->ajaxReturn(['status' => false, 'msg' => '请输入代表队名称']);
        }

        // A.5 带队老师
        if (empty($team_teacher)) {
            $this->ajaxReturn(['status' => false, 'msg' => '请输入带队老师']);
        }

        // A.6 联系方式
        if (empty($team_contact)) {
            $this->ajaxReturn(['status' => false, 'msg' => '请输入联系方式']);
        }

        // B. 写入数据
        $dbTrans = D();
        $dbTrans->startTrans();

        // B.1 写入报名表
        $match_entry_data = [
            'match_id'     => $match['id'],
            'uid'          => $uid,
            'team_name'    => $team_name,
            'team_teacher' => $team_teacher,
            'team_contact' => $team_contact,
            'team_qq'      => $team_qq,
            'created_at'   => time(),
            'updated_at'   => time(),
        ];
        if (! M('match_entrys')->add($match_entry_data)) {
            $dbTrans->rollback();
            $this->ajaxReturn(['status' => false, 'msg' => '服务器异常，请稍后再试']);
        }

        // B.2 更新用户表的带队信息
        $user_data = [
            'team_name'    => $team_name,
            'team_teacher' => $team_teacher,
            'team_contact' => $team_contact,
            'team_qq'      => $team_qq,
            'updated_at'   => time(),
        ];
        if (false === M('users')->where(['id' => $uid])->save($user_data)) {
            $dbTrans->rollback();
            $this->ajaxReturn(['status' => false, 'msg' => '服务器异常，请稍后再试']);
        }

        $dbTrans->commit();

        $this->ajaxReturn(['status' => true, 'msg' => '报名成功', 'url' => U('MatchEntry/index', ['match_id' => $match_id])]);
    }
}