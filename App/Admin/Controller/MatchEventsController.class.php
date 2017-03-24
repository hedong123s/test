<?php
namespace Admin\Controller;

use Think\PageAdmin;
use Common\Support\Validator;
use Think\Upload;

/**
 * 赛事 - 赛组 - 组别管理控制器
 * 
 * @author Flc <2016-08-23 19:29:09>
 */
class MatchEventsController extends BaseController
{
    /**
     * 组别列表
     * @return [type] [description]
     */
    public function index()
    {
        $prefix   = C('DB_PREFIX');
        $match_id = I('get.match_id', 0, 'intval');

        // 赛组信息
        if (empty($match_id) ||
            ! $match = M('matchs')->where(['id' => $match_id])->find()
        ) {
            $this->error('参数错误');
        }

        // 搜索
        $search = $map = [];
        $event_number = I('get.event_number', '', 'trim');
        $event_title  = I('get.event_title', '', 'trim');

        // 搜索.组别代码
        if (! empty($event_number)) {
            $search['event_number'] = $event_number;
            $map['event_number']    = ['like', '%' . $event_number . '%'];
        }

        // 搜索.组别名称
        if (! empty($event_title)) {
            $search['event_title'] = $event_title;
            $map['event_title']    = ['like', '%' . $event_title . '%'];
        }

        // 开始查询
        $model = M('match_events')
            ->where(['match_id' => $match_id])
            ->where($map);

        // 查询总数
        $tModel = clone $model;
        $count = $tModel->count();

        // 分页
        $Page  = new PageAdmin($count, C('PAGE_NUM'));

        // 列表数据
        $lists = $model
            ->limit($Page->firstRow.','.$Page->listRows)->order(['id' => 'asc'])
            ->order(['id' => 'desc'])
            ->select();

        $pages['show']  = $Page->show();  //分页输出
        $pages['total'] = $count;  //总数
        $pages['lists'] = $lists;

        $this->seotitle = '组别管理';
        $this->match    = $match;
        $this->pages    = $pages;
        $this->search   = $search;
        $this->display();
    }

    /**
     * 赛事添加
     * @return [type] [description]
     */
    public function add()
    {
        $defineDances = defineDances();
        $match_id     = I('get.match_id', 0, 'intval');

        // 赛程信息
        if (empty($match_id) ||
            ! $match = M('matchs')->where(['id' => $match_id])->find()
        ) {
            $this->error('参数错误');
        }

        $this->seotitle     = '添加组别';
        $this->match        = $match;
        $this->defineDances = $defineDances;
        $this->display();
    }

    /**
     * 保存添加
     * @return [type] [description]
     */
    public function store()
    {
        $match_id               = I('post.match_id', 0, 'intval');
        $title                  = I('post.title', '', 'trim');
        $event_number           = I('post.event_number', '', 'trim');
        $dances                 = I('post.dances', []);
        $fee                    = I('post.fee', '', 'trim');
        $age_type               = I('post.age_type', 0, 'intval');
        $age_date               = I('post.age_date', 0, 'intval');
        $age_date2              = I('post.age_date2', 0, 'intval');
        $ipate                  = I('post.ipate', '', 'trim');
        $ipate_amat             = I('post.ipate_amat', '', 'trim');
        $disabled_event_numbers = I('post.disabled_event_numbers', '', 'trim');
        $person_type            = I('post.person_type', 0, 'intval');
        $person_sex             = I('post.person_sex', []);
        

        // A. 校验
        // A.0.1 赛程信息
        if (! $match = M('matchs')->where(['id' => $match_id])->find()) {
            $this->ajaxReturn(['status' => false, 'msg' => '参数错误']);
        }

        // A.1 组别名称
        if (empty($title)) {
            $this->ajaxReturn(['status' => false, 'msg' => '请输入组别名称']);
        }

        // A.2 组别代码
        if (empty($event_number)) {
            $this->ajaxReturn(['status' => false, 'msg' => '请输入组别代码']);
        }
        if (M('match_events')->where(['match_id' => $match['id'], 'event_number' => $event_number])->count() > 0) {
            $this->ajaxReturn(['status' => false, 'msg' => '该赛事组别代码已存在']);
        }

        // A.3 服务费
        if (empty($fee)) {
            $this->ajaxReturn(['status' => false, 'msg' => '请输入服务费']);
        }
        if (! is_money($fee)) {
            $this->ajaxReturn(['status' => false, 'msg' => '服务费不合法']);
        }

        // A.4 年龄限制
        if (! in_array($age_type, [0, 1, 2, 3])) {
            $this->ajaxReturn(['status' => false, 'msg' => '年龄限制不合法']);
        }
        if (in_array($age_type, [1, 2, 3])) {
            // A.4.1 为空校验
            if (empty($age_date)) {
                $this->ajaxReturn(['status' => false, 'msg' => '请选择年龄限制 - 出生年']);
            }

            // A.4.2 年合法性
            if (! checkdate(1, 1, $age_date)) {
                $this->ajaxReturn(['status' => false, 'msg' => '年龄限制 - 出生年不合法']);
            }

            // A.4.3 如果为区间
            if ($age_type == 3) {
                if (empty($age_date2)) {
                    $this->ajaxReturn(['status' => false, 'msg' => '请选择年龄限制 - 出生年']);
                }

                if (! checkdate(1, 1, $age_date2)) {
                    $this->ajaxReturn(['status' => false, 'msg' => '年龄限制 - 出生年不合法']);
                }

                // 第二个大于第一个年
                if ($age_date2 < $age_date) {
                    $this->ajaxReturn(['status' => false, 'msg' => '区间年龄必须右大于等于左']);
                }
            }
        }
        

        // A.5 允许参赛板块
        if (empty($ipate) ||
            ! in_array($ipate, ['PROC', 'SPEC', 'AMAT', 'TAS'])
        ) {
            $this->ajaxReturn(['status' => false, 'msg' => '请选择归属板块']);
        }
        // A.5.1 业余板块细分
        if ($ipate == 'AMAT' && (
            empty($ipate_amat) ||
            ! in_array($ipate_amat, ['ELITE', 'ORDIN'])
        )) {
            $this->ajaxReturn(['status' => false, 'msg' => '请选择业余组子项']);
        }

        // A.5.Add.1 禁报组别代码
        if (! empty($disabled_event_numbers)) {
            $disabled_event_numbers_arr = explode(',', $disabled_event_numbers);
            // 去除空格
            $disabled_event_numbers_arr = array_map(function($value){
                return trim($value);
            }, $disabled_event_numbers_arr);
            // 去重
            $disabled_event_numbers_unique_arr = array_unique($disabled_event_numbers_arr);
            // 合并为逗号
            $disabled_event_numbers = implode(',', $disabled_event_numbers_unique_arr);
        }

        // A.6 参赛人员类型
        if (! in_array($person_type, [1, 2, 3, 4])) {
            $this->ajaxReturn(['status' => false, 'msg' => '参赛人员类型不合法']);
        }
        if (in_array($person_type, [1, 2, 3])) {
            // A.6.1 不同类型需求的人员配置
            $person_config = [
                1 => 1,
                2 => 2,
                3 => 6
            ];
            $person_num = $person_config[$person_type];

            // A.6.2 校验性别数量合法性
            if (! is_array($person_sex) || count($person_sex) != $person_num) {
                $this->ajaxReturn(['status' => false, 'msg' => '参赛人员性别不合法']);
            }
        }

        // B. 写入数据
        $dbTrans = M();
        $dbTrans->startTrans();

        // B.1 相关数据设置
        // B.1.1 年龄限制出生年
        $age_date_data  = in_array($age_type, [1, 2, 3]) ? ($age_date . '-01-01') : 0;
        $age_date_data2 = in_array($age_type, [3]) ? ($age_date2 . '-01-01') : 0;
        // B.1.2 性别限制
        $person_sex_str_data = in_array($person_type, [1, 2, 3]) ? implode(',', $person_sex) : '';

        // B.2 写入数据
        $data = [
            'match_id'               => $match['id'],
            'event_number'           => $event_number,
            'event_title'            => $title,
            'dances'                 => implode(',', $dances),
            'fee'                    => $fee,
            'ipate'                  => $ipate == 'AMAT' ? $ipate . ':' . $ipate_amat : $ipate,
            'age_type'               => $age_type,
            'age_date'               => $age_date_data,
            'age_date2'              => $age_date_data2,
            'person_type'            => $person_type,
            'person_sex_str'         => $person_sex_str_data,
            'disabled_event_numbers' => $disabled_event_numbers,
            'created_at'             => time(),
            'updated_at'             => time(),
        ];
        if (! $event_id = M('match_events')->add($data)) {
            $dbTrans->rollback();
            $this->ajaxReturn(['status' => false, 'msg' => '提交失败，请稍候再试']);
        }

        // B.3 写入组别与板块关联表
        // $event_ipate_datas = [];
        // foreach ($ipates as $ipate_id) {
        //     $event_ipate_datas[] = [
        //         'event_id' => $event_id,
        //         'ipate_id' => $ipate_id
        //     ];
        // }
        // if (count($event_ipate_datas) > 0 &&
        //     ! M('r_match_event_ipate')->addAll($event_ipate_datas)
        // ) {
        //     $dbTrans->rollback();
        //     $this->ajaxReturn(['status' => false, 'msg' => '提交失败，请稍候再试']);
        // }

        $dbTrans->commit();

        $this->ajaxReturn(['status' => true, 'msg' => '成功', 'url' => U('index', ['match_id' => $match_id])]);
    }

    /**
     * 组别编辑
     * @return [type] [description]
     */
    public function edit()
    {
        $id           = I('get.id', 0, 'intval');
        $defineDances = defineDances();

        // A. 查询组别信息
        if (empty($id) ||
            ! $info = M('match_events')->where(['id' => $id])->find())
        {
            $this->error('参数错误');
        }

        // B. 赛程信息
        if (! $match = M('matchs')->where(['id' => $info['match_id']])->find()) {
            $this->error('参数错误');
        }

        // D. 组别信息相关转义
        $info['aDances']    = explode(',', $info['dances']);  // 舞转为数组
        $info['aPersonSex'] = explode(',', $info['person_sex_str']);  // 性别为数组

        $this->seotitle     = '组别编辑';
        $this->info         = $info;
        $this->match        = $match;
        $this->defineDances = $defineDances;
        $this->display();
    }

    /**
     * 赛事编辑保存
     * @return [type] [description]
     */
    public function update()
    {
        $id                     = I('post.id', 0, 'intval');
        $title                  = I('post.title', '', 'trim');
        $event_number           = I('post.event_number', '', 'trim');
        $dances                 = I('post.dances', []);
        $fee                    = I('post.fee', '', 'trim');
        $age_type               = I('post.age_type', 0, 'intval');
        $age_date               = I('post.age_date', 0, 'intval');
        $age_date2              = I('post.age_date2', 0, 'intval');
        $ipate                  = I('post.ipate', '', 'trim');
        $ipate_amat             = I('post.ipate_amat', '', 'trim');
        $disabled_event_numbers = I('post.disabled_event_numbers', '', 'trim');
        $person_type            = I('post.person_type', 0, 'intval');
        $person_sex             = I('post.person_sex', []);

        // A. 校验
        // A.0.1 查询组别信息
        if (empty($id) ||
            ! $info = M('match_events')->where(['id' => $id])->find())
        {
            $this->ajaxReturn(['status' => false, 'msg' => '参数错误']);
        }

        // A.0.2 赛程信息
        if (! $match = M('matchs')->where(['id' => $info['match_id']])->find()) {
            $this->ajaxReturn(['status' => false, 'msg' => '参数错误']);
        }

        // A.1 组别名称
        if (empty($title)) {
            $this->ajaxReturn(['status' => false, 'msg' => '请输入组别名称']);
        }

        // A.2 组别代码
        if (empty($event_number)) {
            $this->ajaxReturn(['status' => false, 'msg' => '请输入组别代码']);
        }
        if (M('match_events')->where(['match_id' => $match['id'], 'event_number' => $event_number, 'id' => ['neq', $id]])->count() > 0) {
            $this->ajaxReturn(['status' => false, 'msg' => '该赛事组别代码已存在']);
        }

        // A.3 服务费
        if (empty($fee)) {
            $this->ajaxReturn(['status' => false, 'msg' => '请输入服务费']);
        }
        if (! is_money($fee)) {
            $this->ajaxReturn(['status' => false, 'msg' => '服务费不合法']);
        }

        // A.4 年龄限制
        if (! in_array($age_type, [0, 1, 2, 3])) {
            $this->ajaxReturn(['status' => false, 'msg' => '年龄限制不合法']);
        }
        if (in_array($age_type, [1, 2, 3])) {
            // A.4.1 为空校验
            if (empty($age_date)) {
                $this->ajaxReturn(['status' => false, 'msg' => '请选择年龄限制 - 出生年']);
            }

            // A.4.2 年合法性
            if (! checkdate(1, 1, $age_date)) {
                $this->ajaxReturn(['status' => false, 'msg' => '年龄限制 - 出生年不合法']);
            }

            // A.4.3 如果为区间
            if ($age_type == 3) {
                if (empty($age_date2)) {
                    $this->ajaxReturn(['status' => false, 'msg' => '请选择年龄限制 - 出生年']);
                }

                if (! checkdate(1, 1, $age_date2)) {
                    $this->ajaxReturn(['status' => false, 'msg' => '年龄限制 - 出生年不合法']);
                }

                // 第二个大于第一个年
                if ($age_date2 < $age_date) {
                    $this->ajaxReturn(['status' => false, 'msg' => '区间年龄必须右大于等于左']);
                }
            }
        }

        // A.5 允许参赛板块
        if (empty($ipate) ||
            ! in_array($ipate, ['PROC', 'SPEC', 'AMAT', 'TAS'])
        ) {
            $this->ajaxReturn(['status' => false, 'msg' => '请选择归属板块']);
        }
        // A.5.1 业余板块细分
        if ($ipate == 'AMAT' && (
            empty($ipate_amat) ||
            ! in_array($ipate_amat, ['ELITE', 'ORDIN'])
        )) {
            $this->ajaxReturn(['status' => false, 'msg' => '请选择业余组子项']);
        }

        // A.5.Add.1 禁报组别代码
        if (! empty($disabled_event_numbers)) {
            $disabled_event_numbers_arr = explode(',', $disabled_event_numbers);
            // 去除空格
            $disabled_event_numbers_arr = array_map(function($value){
                return trim($value);
            }, $disabled_event_numbers_arr);
            // 去重
            $disabled_event_numbers_unique_arr = array_unique($disabled_event_numbers_arr);
            // 合并为逗号
            $disabled_event_numbers = implode(',', $disabled_event_numbers_unique_arr);
        }

        // A.6 参赛人员类型
        if (! in_array($person_type, [1, 2, 3, 4])) {
            $this->ajaxReturn(['status' => false, 'msg' => '参赛人员类型不合法']);
        }
        if (in_array($person_type, [1, 2, 3])) {
            // A.6.1 不同类型需求的人员配置
            $person_config = [
                1 => 1,
                2 => 2,
                3 => 6
            ];
            $person_num = $person_config[$person_type];

            // A.6.2 校验性别数量合法性
            if (! is_array($person_sex) || count($person_sex) != $person_num) {
                $this->ajaxReturn(['status' => false, 'msg' => '参赛人员性别不合法']);
            }
        }

        // B. 写入数据
        $dbTrans = M();
        $dbTrans->startTrans();

        // B.1 相关数据设置
        // B.1.1 年龄限制出生年
        $age_date_data  = in_array($age_type, [1, 2, 3]) ? ($age_date . '-01-01') : 0;
        $age_date_data2 = in_array($age_type, [3]) ? ($age_date2 . '-01-01') : 0;
        // B.1.2 性别限制
        $person_sex_str_data = in_array($person_type, [1, 2, 3]) ? implode(',', $person_sex) : '';

        // B.2 更新数据
        $data = [
            'event_number'           => $event_number,
            'event_title'            => $title,
            'dances'                 => implode(',', $dances),
            'fee'                    => $fee,
            'ipate'                  => $ipate == 'AMAT' ? $ipate . ':' . $ipate_amat : $ipate,
            'disabled_event_numbers' => $disabled_event_numbers,
            'age_type'               => $age_type,
            'age_date'               => $age_date_data,
            'age_date2'              => $age_date_data2,
            'person_type'            => $person_type,
            'person_sex_str'         => $person_sex_str_data,
            'updated_at'             => time(),
        ];
        if (false === M('match_events')->where(['id' => $id])->save($data)) {
            $dbTrans->rollback();
            $this->ajaxReturn(['status' => false, 'msg' => '提交失败，请稍候再试']);
        }

        // // B.3 清除已绑定的关系
        // if (false === M('r_match_event_ipate')->where(['event_id' => $id])->delete()) {
        //     $dbTrans->rollback();
        //     $this->ajaxReturn(['status' => false, 'msg' => '提交失败，请稍候再试']);
        // }

        // // B.4 写入组别与板块关联表
        // $event_ipate_datas = [];
        // foreach ($ipates as $ipate_id) {
        //     $event_ipate_datas[] = [
        //         'event_id' => $id,
        //         'ipate_id' => $ipate_id
        //     ];
        // }
        // if (count($event_ipate_datas) > 0 &&
        //     ! M('r_match_event_ipate')->addAll($event_ipate_datas)
        // ) {
        //     $dbTrans->rollback();
        //     $this->ajaxReturn(['status' => false, 'msg' => '提交失败，请稍候再试']);
        // }

        $dbTrans->commit();

        $this->ajaxReturn(['status' => true, 'msg' => '成功', 'url' => U('index', ['match_id' => $match['id']])]);
    }

    /**
     * 删除
     *
     * @return [type] [description]
     */
    public function destroy()
    {
        $id           = I('get.id', 0, 'intval');
        $defineDances = defineDances();

        // A. 查询组别信息
        if (empty($id) ||
            ! $info = M('match_events')->where(['id' => $id])->find())
        {
            $this->error('参数错误');
        }

        // B. 查看是否有报名信息
        if (M('match_event_entrys')->where(['event_id' => $id])->count() > 0) {
            $this->error('当前组别含报名信息，无法删除');
        }

        // C. 删除
        M('match_events')->where(['id' => $id])->delete();

        redirect($_SERVER['HTTP_REFERER']);
    }

    /**
     * 文件导入
     * @return [type] [description]
     */
    public function import()
    {
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
        $upload->exts     = array('xlsx');// 设置附件上传类型
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
            $event_number,
            $event_title,
            $dances,
            $fee,
            $age_type_str,
            $age_year,
            $age_year2,
            $ipate_str,
            $ipate_amat_str,
            $disabled_event_numbers,
            $person_type_str,
            $person_sexs_str
        ) = $data;

        // A. 校验
        // A.1 代码校验
        if (empty($event_number) ||
            M('match_events')->where(['match_id' => $match_id, 'event_number' => $event_number])->count() > 0
        ) {
            return ['status' => false, 'msg' => '组别代码不能为空或组别代码重复'];
        }

        // A.2 组别名称校验
        if (empty($event_title)) {
            return ['status' => false, 'msg' => '组别名称不能为空'];
        }

        // A.3 服务费
        if (empty($fee)) {
            return ['status' => false, 'msg' => '服务不能为空'];
        }
        if (! is_money($fee)) {
            return ['status' => false, 'msg' => '服务费不合法'];
        }

        // A.4 年龄限制
        if (! in_array($age_type_str, ['不限', '之前', '之后', '区间'])) {
            return ['status' => false, 'msg' => '年龄限制不合法'];
        }
        if (in_array($age_type_str, ['之前', '之后', '区间'])) {
            // A.4.1 为空校验
            if (empty($age_year)) {
                return ['status' => false, 'msg' => '年龄限制 - 出生年不能为空'];
            }

            // A.4.2 年合法性
            if (! checkdate(1, 1, $age_year)) {
                return ['status' => false, 'msg' => '年龄限制 - 出生年不合法'];
            }
        }
        // A.4.3 如果为区间
        if ($age_type_str == '区间') {
            if (empty($age_year2)) {
                return ['status' => false, 'msg' => '年龄限制 - 出生年不合法'];
            }

            if (! checkdate(1, 1, $age_year2)) {
                return ['status' => false, 'msg' => '年龄限制 - 出生年不合法'];
            }

            // 第二个大于第一个年
            if ($age_year2 < $age_year) {
                return ['status' => false, 'msg' => '区间年龄必须右大于等于左'];
            }
        }

        $age_type_config = [
            '不限' => 0,
            '之前' => 1,
            '之后' => 2,
            '区间' => 3,
        ];
        $age_type = $age_type_config[$age_type_str];

        // A.5 允许参赛板块 'PROC', 'SPEC', 'AMAT', 'TAS'  'ELITE', 'ORDIN'
        if (empty($ipate_str) ||
            ! in_array($ipate_str, ['职业组', '专业组', '业余组', '师生组'])
        ) {
            return ['status' => false, 'msg' => '归属板块不合法'];
        }
        $ipate_config = [
            '职业组' => 'PROC',
            '专业组' => 'SPEC',
            '业余组' => 'AMAT',
            '师生组' => 'TAS',
        ];
        $ipate = $ipate_config[$ipate_str];

        // A.5.1 业余板块细分
        if ($ipate_str == '业余组' && (
            empty($ipate_amat_str) ||
            ! in_array($ipate_amat_str, ['重要组', '普通组'])
        )) {
            return ['status' => false, 'msg' => '业余组子项不合法'];
        }
        if ($ipate_str == '业余组') {
            $ipate_amat_config = [
                '重要组' => 'ELITE',
                '普通组' => 'ORDIN',
            ];
            $ipate_amat = $ipate_amat_config[$ipate_amat_str];
        }

        // A.5.Add.1 禁报组别代码
        if (! empty($disabled_event_numbers)) {
            $disabled_event_numbers_arr = explode(',', $disabled_event_numbers);
            // 去除空格
            $disabled_event_numbers_arr = array_map(function($value){
                return trim($value);
            }, $disabled_event_numbers_arr);
            // 去重
            $disabled_event_numbers_unique_arr = array_unique($disabled_event_numbers_arr);
            // 合并为逗号
            $disabled_event_numbers = implode(',', $disabled_event_numbers_unique_arr);
        }

        // A.6 参赛人员类型
        if (! in_array($person_type_str, ['单人', '双人', '6人群舞', '集体舞'])) {
            return ['status' => false, 'msg' => '参赛人员类型不合法'];
        }
        $person_type_config = [
            '单人'    => 1,
            '双人'    => 2,
            '6人群舞' => 3,
            '集体舞'  => 4,
        ];
        $person_type = $person_type_config[$person_type_str];

        if (in_array($person_type_str, ['单人', '双人', '6人群舞'])) {
            // A.6.1 不同类型需求的人员配置
            $person_config = [
                '单人'    => 1,
                '双人'    => 2,
                '6人群舞' => 6
            ];
            $person_num = $person_config[$person_type_str];

            // A.6.2 校验性别数量合法性
            $person_sexs = explode(',', $person_sexs_str);
            if (! is_array($person_sexs) || count($person_sexs) != $person_num) {
                return ['status' => false, 'msg' => '参赛人员性别数量不合法'];
            }

            // A.6.2 校验性别
            $person_true_sexs = [];
            $person_true_sexs_config = [
                '不限' => 0,
                '男'  => 1,
                '女'  => 2,
            ];
            foreach ($person_sexs as $person_sex) {
                if (! in_array($person_sex, ['不限', '男', '女'])) {
                    return ['status' => false, 'msg' => '参赛人员性别不合法'];
                }

                $person_true_sexs[] = $person_true_sexs_config[$person_sex];
            }
        }

        // B. 写入数据
        $dbTrans = D();
        $dbTrans->startTrans();

        // B.1 相关数据设置
        // B.1.1 年龄限制出生年
        $age_date_data = in_array($age_type, [1, 2, 3]) ? ($age_year . '-01-01') : 0;
        $age_date_data2 = in_array($age_type, [3]) ? ($age_year2 . '-01-01') : 0;
        // B.1.2 性别限制
        $person_sex_str_data = in_array($person_type, [1, 2, 3]) ? implode(',', $person_true_sexs) : '';

        // B.2 写入数据
        $data = [
            'match_id'               => $match_id,
            'event_number'           => $event_number,
            'event_title'            => $event_title,
            'dances'                 => $dances,
            'fee'                    => $fee,
            'ipate'                  => $ipate == 'AMAT' ? $ipate . ':' . $ipate_amat : $ipate,
            'age_type'               => $age_type,
            'age_date'               => $age_date_data,
            'age_date2'              => $age_date_data2,
            'person_type'            => $person_type,
            'person_sex_str'         => $person_sex_str_data,
            'disabled_event_numbers' => $disabled_event_numbers,
            'created_at'             => time(),
            'updated_at'             => time(),
        ];
        if (! $event_id = M('match_events')->add($data)) {
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
}