<?php
namespace Home\Controller;
use Common\Support\IdCard;
/**
 * Created by PhpStorm.
 * User: hedong
 * 会员管理
 */
class MemberController extends CommonController{
	public function __construct() {
        parent::__construct();
        /*
         *检测用户登陆
        */
        $this->checkLoginInfo();    
    }
    
	/**
	 * 会员首页
	 */
	public function index(){
		$id = session("user.userid"); 
		$map['id'] = $id;
        $res = M("users")->where($map)->find();
        //是否已经实名认证
        if($res['type'] == 0){
        	$maps['is_self'] = 1;
        	$maps['uid'] = $id;
        	$r = M("user_players")->where($maps)->find();
        	if(!$r){
        		$res['realname'] = '';
        	}else{
        		$rname = M("players")->where(array("id"=>$r['player_id']))->find();
        		$res['realname'] = $rname['name'];
        	}
        }
        $this->seotitle = '用户基本信息';
        $this->assign("res",$res);
        $this->display();
	}

	/**
	 * 个人资料修改
	 */
	public function edit(){
		$id = I('get.id', '', 'trim');
		$res = M("users")->where(array("id"=>$id))->find();
		//var_dump($res);
		$this->assign("res",$res);
		$this->display();

	}

	/**
	 * 个人资料修改表单提交
	 */
	public function doedit(){
        $team_name      = I('post.team_name', '', 'trim');
        $team_teacher   = I('post.team_teacher', '', 'trim');
        $team_contact   = I('post.team_contact', '', 'trim');
        $team_qq        = I('post.team_qq', '', 'trim');
        $id             = I('post.id', '', 'trim');

        // A.2 代表队名称
        if (empty($team_name)) {
            $this->ajaxReturn(['status' => false, 'msg' => '请输入代表队名称']);
        }

        // A.3 带队老师
        if (empty($team_teacher)) {
            $this->ajaxReturn(['status' => false, 'msg' => '请输入带队老师']);
        }

        // A.4 联系方式
        if (empty($team_contact)) {
            $this->ajaxReturn(['status' => false, 'msg' => '请输入联系方式']);
        }

        $arr = array(
        		'team_name'     => $team_name,
        		'team_teacher'  => $team_teacher,
        		'team_contact'  => $team_contact,
        		'team_qq'       => $team_qq,
        		'updated_at'   => time(),
        	);

        $r = M("users")->where(array("id"=>$id))->save($arr);
		if($r){
			$this->ajaxReturn(['status' => true, 'msg' => '修改成功']);
		}else{
			$this->ajaxReturn(['status' => false, 'msg' => '未知错误']);
		}
	}

	/**
	 * 重设密码
	 */
	public function resetpass(){
		$id = I('get.id', '', 'trim');
		$this->display();
	}

	/**
	 * 重设密码表单提交
	 */
	public function doreset(){
		$id = I('post.id', '', 'trim');
		$pass = I('post.pass', '', 'trim');
		$newpass = I('post.newpass', '', 'trim');
		$repass = I('post.repass', '', 'trim');
		if($pass == '' || $newpass == '' || $repass == ''){
			$this->ajaxReturn(['status' => false, 'msg' => '密码不能为空']);
		}
		$res = M("users")->where(array("id"=>$id))->find();
		if($res['password'] != md5($pass)){
			$this->ajaxReturn(['status' => false, 'msg' => '原始密码错误']);
		}
		if($pass == $newpass){
			$this->ajaxReturn(['status' => false, 'msg' => '新密码与旧密码一致']);
		}
		if($newpass !== $repass){
			$this->ajaxReturn(['status' => false, 'msg' => '新密码输入不一致']);
		}
		$r = M("users")->where(array("id"=>$id))->save(array("password"=>md5($newpass),"update_at"=>time()));
		if($r){
			$this->ajaxReturn(['status' => true, 'msg' => '密码修改成功']);
		}else{
			$this->ajaxReturn(['status' => false, 'msg' => '未知错误']);
		}
	}

	/**
	 * 实名认证
	 */
	public function nameauth(){
		$this->display();
	}

	/**
	 * 实名认证表单提交
	 */
	public function doauth(){
		$id = session("user.userid");
		$name = I('post.name', '', 'trim');
		$certid = I('post.certid', '', 'trim');
		$act = I('get.act', '', 'trim');  //act self||player 
		if($name == ''){
			$this->ajaxReturn(['status' => false, 'msg' => '请输入姓名']);
		}
		if($certid == ''){
			$this->ajaxReturn(['status' => false, 'msg' => '请输入正确的身份证号码']);
		}
		if(!idcard_check($certid)){
			$this->ajaxReturn(['status' => false, 'msg' => '身份证信息校验错误']);
		}
		$map['idcard'] = $certid;
		if(M("players")->where($map)->find()){
			$this->ajaxReturn(['status' => false, 'msg' => '该选手已经被添加']);
		}
		//调用实名认证成功
		//$r = getIDCardInfo($certid);
		$r = IdCard::check($name, $certid);
		if($r['status'] == true && $r['data']['isok'] == 0){
			$this->ajaxReturn(['status' => false, 'msg' => '查询失败']);
		}
		if($r['status'] == true && $r['data']['isok'] == 1 && $r['data']['code'] == 1){
		//if($r['error'] == 2){
			$sex = $r['data']['data']['sex'] == 'M' ? 1 : 2;
			//$sex = $r['sex'] == '男' ? 1 : 2;
			$arr = array(
				'type'=>0,
				'name'=>$name,
				'idcard'=>$certid,
				'sex'=>$sex,
				'birthday'=>$r['data']['data']['birthday'],  //$r['birthday'],
				'address'=>$r['data']['data']['address'],  //'',
				'created_at'=>time()
			);
			$palyerid = M("players")->add($arr);
			if($act == 'player'){
				$is_self = 0;
				$url = 'player/index';
			}else{
				$is_self = 1;
				$url = 'member/index';
			}
			if($palyerid){
				$arr1 = array(
					'uid'=>$id,
					'player_id'=>$palyerid,
					'is_self'=>$is_self,
					'created_at'=>time()
				);
				if(M('user_players')->add($arr1)){
					$this->ajaxReturn(['status' => true, 'msg' => '认证成功','url' => U($url)]);
				}
				
			}
		}else{
			$this->ajaxReturn(['status' => false, 'msg' => '认证失败']);
		}
		
	}


}