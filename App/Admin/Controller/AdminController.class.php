<?php
namespace Admin\Controller;
use Think\Controller;
//管理及管理组类
class AdminController extends BaseController {
    /*================管理组 开始=================*/
    //管理组列表
    function role_lists(){
        $db    = M('admin_roles');
        $count = $db->count();
        $page  = new \Think\PageAdmin($count, C('PAGE_NUM'));
        
        $pages['show']  = $page->show();  //分页输出
        $pages['total'] = $count;  //总数
        $pages['lists'] = $db->order("id asc")->limit($page->firstRow.','.$page->listRows)->select();
        
        $this->seotitle = '管理组列表';
        $this->pages    = $pages;
        $this->display();
    }
    
    //管理组添加
    function role_add(){
        $this->seotitle = '管理组添加';
        $this->display();
    }
    
    //管理组修改
    function role_edit(){
        $id = I('get.id', 0, 'intval');

        $db = M('admin_roles');
        if ($r = $db->where(array('id' => $id))->find()) {
            $data = $r;
        }else{
            $this->error("参数错误");
        }
                
        $this->assign("seotitle", "管理组修改");
        $this->assign("data", $data);                                   
        $this->display();
    }
    
    //管理组操作保存
    function role_save(){
        $action = I('get.action', 'action', 'trim');

        if ($action == 'add') {   //添加数据

            $data = I('post.');
            if ($data['name'] == '') {
                $this->error('请输入管理组名');
            }
            $data['created_at'] = time();
            $data['updated_at'] = time();
            $db                = M('admin_roles');

            if($db->create($data)){
                $db->add();
                $this->success('提交成功',U('role_lists'));
            }else{
                $this->error("参数错误");
            }

        } elseif ($action == 'edit') {   //修改数据
            $id   = I('get.id', '0', 'intval');
            $data = I('post.');
            if ($data['name'] == '') {
                $this->error('请输入管理组名');
            }
            $data['updated_at'] = time();

            $db = M('admin_roles');
            if ($db->create($data)) {
                $db->where(array('id' => $id))->save();
                $this->success('提交成功',U('role_lists'));
            }else{
                $this->error("参数错误");
            }
        }else{
            $this->error("参数错误");
        }
    }
    /*================管理组 结束=================*/
    
    
    /*================管理员 开始=================*/
    
    //管理员列表
    function index(){
        $db = D('admins');
        //搜索条件
        $where = [];
        
        $count          = $db->relation('Roles')->where($where)->count();
        $page           = new \Think\PageAdmin($count, C('PAGE_NUM'));
        $pages['show']  = $page->show();  //分页输出
        $pages['total'] = $count;  //总数     
        $pages['lists'] = $db->where($where)->relation('Roles')->order("id desc")->limit($page->firstRow.','.$page->listRows)->select();
        
        $this->pages    = $pages;
        $this->seotitle = '管理员列表';
        $this->display();
    }
    
    //管理员添加
    function add(){
        $roles = M('admin_roles')->field('id, name')->order('id asc')->select();

        $this->seotitle = '管理员添加';
        $this->roles    = $roles;
        $this->display();
    }
    
    //管理员修改
    function edit(){
        $id = I('get.id', 0, 'intval');

        if (!$data = D('admins')->relation('Roles')->where(array('id' => $id))->find()) {
            $this->error("参数错误");
        }

        /**
         * 已选择管理组
         */
        $alrRoles = array();
        foreach ($data['Roles'] as $item) {
            $alrRoles[] = $item['id'];
        }

        $roles = M('admin_roles')->field('id, name')->order('id asc')->select();
        
        $this->seotitle = '管理员编辑';
        $this->roles    = $roles;
        $this->data     = $data;    
        $this->alrRoles = $alrRoles;
        $this->display();
    }
    
    //管理员保存
    function save(){
        $action = I('get.action', '');
        if ($action == 'add') {   //添加数据

            $d = I('post.', array(), 'trim');

            // 校验
            if ($d['username'] == '') {
                $this->error('请输入用户名');
            }
            if (M('admins')->where(array('username' => $d['username']))->find()) {
                $this->error('用户名已存在');
            }
            if ($d['password'] == '' || $d['repassword'] == '') {
                $this->error('请输入密码或确认密码');
            }
            if ($d['password'] != $d['repassword']) {
                $this->error('密码和确认密码不一致');
            }
            if (!isset($d['role_ids']) || !is_array($d['role_ids']) || count($d['role_ids']) <= 0) {
                $this->error('请选择管理组');
            }

            // 开始写入
            $dbTrans = M();
            $dbTrans->startTrans();

            $user_data = array(
                'uuid'       => uuid(),
                'username'   => $d['username'],
                'nickname'   => $d['nickname'],
                'password'   => hash_make($d['password']),
                'created_at' => time(),
                'updated_at' => time(),
                'status'     => 1
            );
            $insertid = M('admins')->add($user_data);
            if (!$insertid) {
                $dbTrans->rollback();
                $this->error('写入失败');
            }

            $admins_roles_data = array();
            foreach ($d['role_ids'] as $item) {
                $admins_roles_data[] = array(
                    'admin_id' => $insertid,
                    'role_id'  => $item
                );
            }
            $flag = M('admins_roles')->addAll($admins_roles_data);
            if (!$flag) {
                $dbTrans->rollback();
                $this->error('写入失败');
            }

            $dbTrans->commit();
            $this->success('提交成功', U('index'));

        }elseif($action=='edit'){   //修改数据
            $id = I('get.id', 0, 'intval');
            $d  = I('post.', array(), 'trim');

            // 校验
            if (!M('admins')->where(array('type_id' => 0, 'id' => $id))->find()) {
                $this->error("参数错误");
            }
            if ($d['password'] != '') {
                if ($d['password'] != $d['repassword']) {
                    $this->error('密码和确认密码不一致');
                }
            }
            if (!isset($d['role_ids']) || !is_array($d['role_ids']) || count($d['role_ids']) <= 0) {
                $this->error('请选择管理组');
            }

            // 开始写入
            $dbTrans = D();
            $dbTrans->startTrans();

            $admin_data = array(
                'nickname'       => $d['nickname'],
                'updated_at' => time(),
            );
            if ($d['password'] != '') {
                $admin_data['password'] = hash_make($d['password']);
            }
            $flag = M('admins')->where(array('id' => $id))->save($admin_data);
            if ($flag === false) {
                $dbTrans->rollback();
                $this->error('更新失败');
            }

            // 删除未选择的管理组
            $flag = M('admins_roles')->where(array('admin_id' => $id, 'role_id' => array('notin', $d['role_ids'])))->delete();
            if ($flag === false) {
                $dbTrans->rollback();
                $this->error('更新失败');
            }

            $admins_roles_data = array();
            foreach ($d['role_ids'] as $item) {
                if (!M('admins_roles')->where(array('admin_id' => $id, 'role_id' => $item))->find()) {
                    $admins_roles_data[] = array(
                        'admin_id' => $id,
                        'role_id' => $item
                    );
                }
            }

            if (count($admins_roles_data) > 0) {
                $flag = M('admins_roles')->addAll($admins_roles_data);
                if (!$flag) {
                    $dbTrans->rollback();
                    $this->error('更新失败');
                }
            }

            $dbTrans->commit();
            $this->success('提交成功', U('index'));
        }else{
            $this->error("参数错误");
        }
    }
    /*================管理员 结束=================*/

    
    /*================权限规则 开始=================*/
    //规则列表
    function rule_index(){
        $db = D('AdminRules');

        $count=$db->relation('Roles')->where($where)->count();
        $page=new \Think\PageAdmin($count,C('PAGE_NUM'));
        $pages['show']=$page->show();  //分页输出
        $pages['total']=$count;  //总数       
        $pages['lists']=$db->where($where)->relation('Roles')->order("id desc")->limit($page->firstRow.','.$page->listRows)->select();
        
        $this->seotitle  = '权限规则列表';
        $this->pages = $pages;
        $this->ruleTypes = C('ADMIN_RULE_TYPE');
        $this->display();
    }
    
    //规则添加
    function rule_add(){
        $this->seotitle  = '权限规则添加';
        $this->ruleTypes = C('ADMIN_RULE_TYPE');
        $this->display();
    }
    
    //规则修改
    function rule_edit(){
        $id = I('get.id', 0, 'intval');

        if (!$data = M('admin_rules')->where(array('id' => $id))->find()) {
            $this->error("参数错误");
        }
        
        $this->seotitle  = '权限规则修改';
        $this->data      = $data;  
        $this->ruleTypes = C('ADMIN_RULE_TYPE');                            
        $this->display();
    }
    
    //规则保存
    function rule_save(){
        $action = I('get.action', '', 'trim');

        if($action=='add'){   //添加数据
            $d = I('post.', array(), 'trim');

            // 校验
            if ($d['name'] == '') {
                $this->error('请输入规则名称');
            }
            if ($d['value'] == '') {
                $this->error('请输入规则标识');
            }
            if (M('admin_rules')->where(array('value' => $d['value']))->find()) {
                $this->error('该标识已存在');
            }
            if ($d['typeid'] == '') {
                $this->error('请选择所属模块');
            }

            $insert_data = array(
                'name'   => $d['name'],
                'value'  => $d['value'],
                'typeid' => $d['typeid'],
                'status' => $d['status'] == 1 ? 1 : 0,
            );
            if (!M('admin_rules')->add($insert_data)) {
                $this->error('提交失败');
            }

            $this->success('提交成功',U('rule_index'));

        }elseif($action=='edit'){   //修改数据
            $id = I('get.id', 0, 'intval');
            $d  = I('post.', array(), 'trim');

            // 校验
            if (!M('admin_rules')->where(array('id' => $id))->find()) {
                $this->error("参数错误");
            }
            if ($d['name'] == '') {
                $this->error('请输入规则名称');
            }
            if ($d['value'] == '') {
                $this->error('请输入规则标识');
            }
            if (M('admin_rules')->where(array('value' => $d['value'], 'id' => array('neq', $id)))->find()) {
                $this->error('该标识已存在');
            }
            if ($d['typeid'] == '') {
                $this->error('请选择所属模块');
            }

            $rule_data = array(
                'name'   => $d['name'],
                'value'  => $d['value'],
                'typeid' => $d['typeid'],
                'status' => $d['status'] == 1 ? 1 : 0,
            );

            if (false === M('admin_rules')->where(array('id' => $id))->save($rule_data)) {
                $this->error('提交失败');
            }

            $this->success('提交成功',U('rule_index'));
        }else{
            $this->error("参数错误");
        }
    }

    /**
     * 设置权限
     */
    public function setRules(){
        $id = I('get.id', 0, 'intval');

        // 获取管理组信息
        if (!$role = D('AdminRoles')->relation('Rules')->where(array('id' => $id))->find()) {
            $this->error('参数错误');
        }

        // 获取已选的规则
        $alrRuleids = array();
        if (is_array($role['Rules'])) {
            foreach ($role['Rules'] as $item) {
                $alrRuleids[] = $item['id'];
            }
        }

        // 获取规则
        $rules = M('admin_rules')->where(array('status' => 1))->order('id asc')->select();

        // 重组规格
        $ruleArrs = array();
        foreach ($rules as $item) {
            $ruleArrs[$item['typeid']][] = $item;
        }

        $this->seotitle   = '设置权限';
        $this->role       = $role;
        $this->ruleTypes  = C('ADMIN_RULE_TYPE');
        $this->ruleArrs   = $ruleArrs;
        $this->alrRuleids = $alrRuleids;
        $this->display();
    }

    /**
     * 保存权限设置
     */
    public function setRulesSave(){
        $id       = I('get.id', 0, 'intval');
        $rule_ids = I('post.rule_ids');

        // 获取管理组信息
        if (!$role = M('admin_roles')->where(array('id' => $id))->find()) {
            $this->error('参数错误');
        }

        $dbTrans = D();
        $dbTrans->startTrans();

        // 删除数据库已有，但未选择的权限
        $flag = M('admin_roles_rules')->where(array('role_id' => $id, 'rule_id' => array('notin', $rule_ids)))->delete();
        if ($flag === false) {
            $dbTrans->rollback();
            $this->error('操作异常1');
        }

        // 获取已经存在的role_id
        $rulesRs      = M('admin_roles_rules')->field('rule_id')->where(array('role_id' => $id, 'rule_id' => array('in', $rule_ids)))->select();
        $alr_rule_ids = array();
        foreach ($rulesRs as $item) {
            $alr_rule_ids[] = $item['rule_id'];
        } 
        foreach ($rule_ids as $key => $item) {
            if (in_array($item, $alr_rule_ids)) {
                unset($rule_ids[$key]);
            }
        }

        // 插入数据库
        $datas = array();
        foreach ($rule_ids as $item) {
            $datas[] = array(
                'role_id' => $id,
                'rule_id' => $item
            );
        }
        if (count($datas) > 0) {
            $flag = M('admin_roles_rules')->addAll($datas);
            if (!$flag) {
                $dbTrans->rollback();
                $this->error('操作异常');
            }
        }

        $dbTrans->commit();
        $this->success('提交成功', U('role_lists'));
    }


}
?>