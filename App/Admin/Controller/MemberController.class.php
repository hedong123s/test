<?php
namespace Admin\Controller;
use Think\Controller;
//会员类
class MemberController extends BaseController {
    /*================会员  开始=================*/
    
    //会员 列表
    function index(){
        $db = D('users');
        //搜索条件
        $count          = $db->count();
        $page           = new \Think\PageAdmin($count, C('PAGE_NUM'));
        $pages['show']  = $page->show();  //分页输出
        $pages['total'] = $count;  //总数     
        $pages['lists'] = $db->order("id desc")->limit($page->firstRow.','.$page->listRows)->select();
        
        $this->pages    = $pages;
        $this->seotitle = '会员列表';
        $this->display();
    }
    
    //会员 添加
    function add(){
        $this->seotitle = '会员添加';
        $this->display();
    }
    
    //会员 修改
    function edit(){
        $id = I('get.id', 0, 'intval');

        if (!$data = D('users')->find($id)) {
            $this->error("参数错误");
        }

        $this->seotitle = '会员编辑';
        $this->data     = $data;
        $this->display();
    }
    
    //会员 保存
    function save(){
        $action = I('get.action', '');
        $model = M('users');
        if ($action == 'add') {   //添加数据

            $d = I('post.', array(), 'trim');

            // 校验
            if ($d['username'] == '') {
                $this->error('请输入用户名');
            }
            if ($info=$model->where(array('username' => $d['username']))->find()) {
                $this->error('用户名已存在');
            }
            if ($d['password'] == '' || $d['repassword'] == '') {
                $this->error('请输入密码或确认密码');
            }
            if ($d['password'] != $d['repassword']) {
                $this->error('密码和确认密码不一致');
            }
            if($d['mobile'] == ''){
                $this->error('手机号不能为空');
            }
            if($d['email'] && preg_match('/^[a-zA-Z1-9][a-zA-Z0-9]{0,10}@[a-zA-Z0-9]+\.[a-zA-Z0-9]+$/i', $d['email']) === 0){
                $this->error('邮箱格式不正确');
            }
            // 开始写入
            $model->startTrans();

            $d['password'] = md5($d['password']);
            $d['created_ip'] = get_client_ip();
            $d['updated_at'] = $d['created_at'] = time();
            if(!$model->create($d)){
                $this->error($model->getError());
            }
            $insertid = $model->add();
            if (!$insertid) {
                $model->rollback();
                $this->error('写入失败');
            }

            $model->commit();
            $this->success('提交成功', U('index'));

        }elseif($action=='edit'){   //修改数据
            $id = I('get.id', 0, 'intval');
            $d  = I('post.', array(), 'trim');

            // 校验
            if (!M('users')->where(array('id' => $id))->find()) {
                $this->error("参数错误");
            }
            if ($d['username'] == '') {
                $this->error('请输入用户名');
            }
            if ($model->where(array('username' => $d['username']))->find() && $d['username']!=$model->where(array('id'=>$id))->getField('username')){
                $this->error('用户名已存在');
            }
            if ($d['password'] != $d['repassword']) {
                $this->error('密码和确认密码不一致');
            }
            if($d['mobile'] == ''){
                $this->error('手机号不能为空');
            }
            if($d['email'] && preg_match('/^[a-zA-Z1-9][a-zA-Z0-9]{0,10}@[a-zA-Z0-9]+\.[a-zA-Z0-9]+$/i', $d['email']) === 0){
                $this->error('邮箱格式不正确');
            }
            //如果用户没有填写密码和确认密码，则原密码不改变
            if ($d['password'] == '' && $d['repassword'] == '') {
                unset($d['password']);
            }
            // 开始写入
            $model->startTrans();

            $d['password'] = md5($d['password']);
            $d['updated_at'] = $d['created_at'] = time();
            if(!$model->create($d)){
                $this->error($model->getError());
            }
            $res = $model->where(array('id'=>$id))->save();
            if (!$res) {
                $model->rollback();
                $this->error('修改失败');
            }

            $model->commit();
            $this->success('提交成功', U('index'));
        }else{
            $this->error("参数错误");
        }
    }
 
}
?>