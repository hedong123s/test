<?php
namespace Admin\Controller;

use Think\PageAdmin;
use Common\Support\Validator;

/**
 * 赛事 - 赛组管理控制器@该功能无效
 * 
 * @author Flc <2016-08-23 19:29:09>
 */
class MatchGroupsController extends BaseController
{
    /**
     * 赛组列表
     * @return [type] [description]
     */
    public function index()
    {
        $prefix   = C('DB_PREFIX');
        $match_id = I('get.match_id', 0, 'intval');

        // 赛程信息
        if (empty($match_id) ||
            ! $match = M('matchs')->where(['id' => $match_id])->find()
        ) {
            $this->error('参数错误');
        }        

        // 开始查询
        $model = M('match_groups')
            ->where(['match_id' => $match_id]);

        // 查询总数
        $tModel = clone $model;
        $count = $tModel->count();

        // 分页
        $Page  = new PageAdmin($count, C('PAGE_NUM'));

        // 列表数据
        $lists = $model->limit($Page->firstRow.','.$Page->listRows)->order(['id' => 'asc'])->select();

        $pages['show']  = $Page->show();  //分页输出
        $pages['total'] = $count;  //总数
        $pages['lists'] = $lists;

        $this->seotitle = '赛组管理';
        $this->match    = $match;
        $this->pages    = $pages;
        $this->display();
    }

    /**
     * 赛事添加
     * @return [type] [description]
     */
    public function add()
    {
        $match_id = I('get.match_id', 0, 'intval');

        // 赛程信息
        if (empty($match_id) ||
            ! $match = M('matchs')->where(['id' => $match_id])->find()
        ) {
            $this->error('参数错误');
        }

        $this->seotitle = '添加赛组';
        $this->match    = $match;
        $this->display();
    }

    /**
     * 保存添加
     * @return [type] [description]
     */
    public function store()
    {
        $match_id = I('post.match_id', 0, 'intval');
        $title    = I('post.title', '', 'trim');
        $content  = I('post.content', '');

        // A. 校验
        // A.0 赛程信息
        if (empty($match_id) ||
            ! $match = M('matchs')->where(['id' => $match_id])->find()
        ) {
            $this->ajaxReturn(['status' => false, 'msg' => '参数错误']);
        }

        // A.1 赛组名称
        if (empty($title)) {
            $this->ajaxReturn(['status' => false, 'msg' => '请输入赛组名称']);
        }

        // B. 写入数据
        $data = [
            'match_id'      => $match_id,
            'group_title'   => $title,
            'group_content' => $content,
            'created_at'    => time(),
            'updated_at'    => time(),
        ];
        if (! M('match_groups')->add($data)) {
            $this->ajaxReturn(['status' => false, 'msg' => '提交失败，请稍候再试']);
        }

        $this->ajaxReturn(['status' => true, 'msg' => '成功', 'url' => U('index', ['match_id' => $match_id])]);
    }

    /**
     * 赛组编辑
     * @return [type] [description]
     */
    public function edit()
    {
        $id = I('get.id', 0, 'intval');

        if (empty($id) ||
            ! $info = M('match_groups')->where(['id' => $id])->find())
        {
            $this->error('参数错误');
        }

        // 赛程信息
        if (! $match = M('matchs')->where(['id' => $info['match_id']])->find()) {
            $this->error('参数错误');
        }

        $this->seotitle = '赛组编辑';
        $this->info     = $info;
        $this->match    = $match;
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

        // A. 校验
        // A.0 数据
        if (empty($id) ||
            ! $info = M('match_groups')->where(['id' => $id])->find())
        {
            $this->ajaxReturn(['status' => false, 'msg' => '参数错误']);
        }

        // A.0.1 赛程信息
        if (! $match = M('matchs')->where(['id' => $info['match_id']])->find()) {
            $this->ajaxReturn(['status' => false, 'msg' => '参数错误']);
        }

        // A.1 赛组名称
        if (empty($title)) {
            $this->ajaxReturn(['status' => false, 'msg' => '请输入赛组名称']);
        }

        // B. 写入数据
        $data = [
            'group_title'   => $title,
            'group_content' => $content,
            'updated_at'    => time(),
        ];
        if (false === M('match_groups')->where(['id' => $id])->save($data)) {
            $this->ajaxReturn(['status' => false, 'msg' => '提交失败，请稍候再试']);
        }

        $this->ajaxReturn(['status' => true, 'msg' => '成功', 'url' => U('index', ['match_id' => $match['id']])]);
    }
}