<?php
namespace Admin\Controller;

use Think\PageAdmin;
use Common\Support\Validator;
use Think\Model;

/**
 * 赛事 - 版块管理控制器（当前类不可用）
 * 
 * @author Flc <2016-08-23 19:29:09>
 */
class MatchIpatesController extends BaseController
{
    /**
     * 版块列表
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
        $lists = M('match_ipates')
            ->where(['match_id' => $match_id])
            ->order(['id' => 'asc'])
            ->select();

        // 所属一级板块查询
        foreach ($lists as &$value) {
            $first_ipates = M('match_ipate_relation mir')
                ->field('mi.name')
                ->join($prefix . 'match_ipates mi on mir.pid = mi.id')
                ->where(['mir.id' => $value['id']])
                ->select();
            $first_ipates_arr = [];
            if (!! $first_ipates) {
                foreach ($first_ipates as $first_ipate) {
                    $first_ipates_arr[] = $first_ipate['name'];
                }
            }

            $value['first_ipate_array'] = $first_ipates_arr;
        }

        $this->seotitle = '版块管理';
        $this->match    = $match;
        $this->lists    = $lists;
        $this->display();
    }

    /**
     * 二级版块添加
     * @return [type] [description]
     */
    public function add()
    {
        $prefix   = C('DB_PREFIX');
        $match_id = I('get.match_id', 0, 'intval');

        // 赛程信息
        if (empty($match_id) ||
            ! $match = M('matchs')->where(['id' => $match_id])->find()
        ) {
            $this->error('参数错误');
        }

        // 一级版块
        $first_ipates = M('match_ipates i')
            ->field('i.*')
            ->join($prefix . 'match_ipate_relation ir on ir.id = i.id')
            ->where(['i.match_id' => $match_id, 'ir.pid' => 0])
            ->order(['i.id' => 'asc'])
            ->select();

        $this->seotitle     = '添加二级版块';
        $this->match        = $match;
        $this->first_ipates = $first_ipates;
        $this->display();
    }

    /**
     * 保存添加
     * @return [type] [description]
     */
    public function store()
    {
        $match_id = I('post.match_id', 0, 'intval');
        $name     = I('post.name', '', 'trim');
        $pids     = I('post.pid', []);

        // A. 校验
        // A.0 赛程信息
        if (empty($match_id) ||
            ! $match = M('matchs')->where(['id' => $match_id])->find()
        ) {
            $this->ajaxReturn(['status' => false, 'msg' => '参数错误']);
        }

        // A.1 二级版块名称
        if (empty($name)) {
            $this->ajaxReturn(['status' => false, 'msg' => '请输入二级版块名称']);
        }

        // A.2 一级版块
        if (empty($pids) || ! is_array($pids) || count($pids) == 0) {
            $this->ajaxReturn(['status' => false, 'msg' => '请选择所属一级版块']);
        }

        // B. 写入数据
        $dbTrans = new Model;
        $dbTrans->startTrans();

        // B.1 写入二级版块数据
        $data = [
            'match_id'   => $match_id,
            'name'       => $name,
            'created_at' => time(),
            'updated_at' => time(),
        ];
        if (! $ipate_id = M('match_ipates')->add($data)) {
            $dbTrans->rollback();
            $this->ajaxReturn(['status' => false, 'msg' => '提交失败，请稍候再试']);
        }

        // B.2 写入关联表
        $ipate_r_data = [];
        foreach ($pids as $pid) {
            $ipate_r_data[] = [
                'pid' => $pid,
                'id'  => $ipate_id
            ];
        }

        if (is_array($ipate_r_data) && count($ipate_r_data) > 0) {
            if (! M('match_ipate_relation')->addAll($ipate_r_data)) {
                $dbTrans->rollback();
                $this->ajaxReturn(['status' => false, 'msg' => '提交失败，请稍候再试']);
            }
        }

        $dbTrans->commit();

        $this->ajaxReturn(['status' => true, 'msg' => '成功', 'url' => U('index', ['match_id' => $match_id])]);
    }

    /**
     * 更新一级分类下级是否可跨类报名
     * @return [type] [description]
     */
    public function cross()
    {
        $id     = I('get.id', 0, 'intval');
        $status = I('get.status', 0, 'intval');

        if (empty($id) ||
            ! $info = M('match_ipates')->where(['id' => $id])->find())
        {
            $this->error('参数错误');
        }

        // 获取pid
        if (M('match_ipate_relation')->where(['id' => $id])->getField('pid') != 0) {
            $this->error('参数错误');
        }

        // 更新
        M('match_ipates')->where(['id' => $id])->save(['is_cross' => $status ? 1 : 0, 'updated_at' => time()]);

        redirect($_SERVER['HTTP_REFERER']);
    }

    /**
     * 删除
     * @return [type] [description]
     */
    public function delete()
    {
        $id = I('get.id', 0, 'intval');

        if (empty($id) ||
            ! $info = M('match_ipates')->where(['id' => $id])->find())
        {
            $this->error('参数错误');
        }

        if (in_array($info['ident'], ['PROC', 'SPEC', 'AMAT'])) {
            $this->error('参数错误');
        }

        M('match_ipates')->where(['id' => $id])->delete();
        M('match_ipate_relation')->where(['id' => $id])->delete();

        redirect($_SERVER['HTTP_REFERER']);
    }

    /**
     * 赛组编辑
     * @return [type] [description]
     */
    public function edit()
    {
        $prefix = C('DB_PREFIX');
        $id     = I('get.id', 0, 'intval');

        if (empty($id) ||
            ! $info = M('match_ipates')->where(['id' => $id])->find())
        {
            $this->error('参数错误');
        }

        if (in_array($info['ident'], ['PROC', 'SPEC', 'AMAT'])) {
            $this->error('参数错误');
        }

        // 赛程信息
        if (! $match = M('matchs')->where(['id' => $info['match_id']])->find()) {
            $this->error('参数错误');
        }

        // 一级版块
        $first_ipates = M('match_ipates i')
            ->field('i.*')
            ->join($prefix . 'match_ipate_relation ir on ir.id = i.id')
            ->where(['i.match_id' => $info['match_id'], 'ir.pid' => 0])
            ->order(['i.id' => 'asc'])
            ->select();

        // 上级选中的一级板块id
        $pids = M('match_ipate_relation')->where(['id' => $id])->getField('pid', true);

        $this->seotitle     = '编辑二级版块';
        $this->info         = $info;
        $this->match        = $match;
        $this->pids         = $pids;
        $this->first_ipates = $first_ipates;
        $this->display();
    }

    /**
     * 赛事编辑保存
     * @return [type] [description]
     */
    public function update()
    {
        $prefix = C('DB_PREFIX');
        $id     = I('post.id', 0, 'intval');
        $name   = I('post.name', '', 'trim');
        $pids   = I('post.pid', []);

        // A. 校验
        // A.1 二级板块信息
        if (empty($id) ||
            ! $info = M('match_ipates')->where(['id' => $id])->find())
        {
            $this->ajaxReturn(['status' => false, 'msg' => '参数错误']);
        }

        // A.2 必须是二级板块
        if (in_array($info['ident'], ['PROC', 'SPEC', 'AMAT'])) {
            $this->ajaxReturn(['status' => false, 'msg' => '参数错误']);
        }

        // A.3 赛程信息
        if (! $match = M('matchs')->where(['id' => $info['match_id']])->find()) {
            $this->ajaxReturn(['status' => false, 'msg' => '参数错误']);
        }

        // A.4 二级版块名称
        if (empty($name)) {
            $this->ajaxReturn(['status' => false, 'msg' => '请输入二级版块名称']);
        }

        // A.5 一级版块
        if (empty($pids) || ! is_array($pids) || count($pids) == 0) {
            $this->ajaxReturn(['status' => false, 'msg' => '请选择所属一级版块']);
        }

        // B. 写入数据
        $dbTrans = new Model;
        $dbTrans->startTrans();

        // B.1 写入二级版块数据
        $data = [
            'name'       => $name,
            'updated_at' => time(),
        ];
        if (false === M('match_ipates')->where(['id' => $id])->save($data)) {
            $dbTrans->rollback();
            $this->ajaxReturn(['status' => false, 'msg' => '提交失败，请稍候再试']);
        }

        // B.2 删除历史分类
        if (false === M('match_ipate_relation')->where(['id' => $id])->delete()) {
            $dbTrans->rollback();
            $this->ajaxReturn(['status' => false, 'msg' => '删除失败，请稍候再试']);
        }

        // B.3 写入关联表
        $ipate_r_data = [];
        foreach ($pids as $pid) {
            $ipate_r_data[] = [
                'pid' => $pid,
                'id'  => $id
            ];
        }

        if (is_array($ipate_r_data) && count($ipate_r_data) > 0) {
            if (! M('match_ipate_relation')->addAll($ipate_r_data)) {
                $dbTrans->rollback();
                $this->ajaxReturn(['status' => false, 'msg' => '提交失败，请稍候再试']);
            }
        }

        $dbTrans->commit();

        $this->ajaxReturn(['status' => true, 'msg' => '成功', 'url' => U('index', ['match_id' => $info['match_id']])]);
    }
}