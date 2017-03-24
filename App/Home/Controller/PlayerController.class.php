<?php
namespace Home\Controller;
use Think\Page;
use Think\Model;
use Common\Support\IdCard;
/**
 * Created by PhpStorm.
 * User: hedong
 * 选手管理
 */
class PlayerController extends CommonController{
	public function __construct() {
        parent::__construct();
        /*
         *检测用户登陆
        */
        $this->checkLoginInfo();    
    }
    
	/**
	 * 选手列表
	 */
	public function index(){
		$userid = session("user.userid");
		$count = M('user_players')->alias("a")->where(array("a.uid"=>$userid))
		->join("qo_players p on a.player_id = p.id","left" )->order('p.id desc')->count();
		$Page  = new Page($count, 10);		
		$list = M('user_players')->alias("a")->where(array("a.uid"=>$userid,"p.type"=>0))
			->join("qo_players p on a.player_id = p.id","left" )->order('p.id desc')
			->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign("usertype",session("user.type"));
		$this->assign("isrealname",checkRealname($userid));  //是否实名认证
		$pages['show']  = $Page->show();  //分页输出
        $pages['total'] = $count;  //总数
        $pages['lists'] = $lists;
        $this->pages    = $pages;
		$this->assign("list",$list);
		$this->seotitle = '选手管理';
		$this->display();
	}

	/**
	 * 添加选手
	 */
	public function add(){
		$this->display();
	}

	/**
	 * 删除选手
	 */
	public function delete(){
		$id = I('get.id', '', 'trim');
		if(!$id){
			$this->success("参数错误");
		}
		if(M("user_players")->where(array("player_id"=>$id))->delete()){
			$this->success("选手删除成功");
		}else{
			$this->success("选手删除失败");
		}
	}

	/**
	 * 验证姓名
	 */
	public function checkname(){
		$name = I('post.name', '', 'trim');
		if(!isChineseName($name)){
			$this->ajaxReturn(['status' => false, 'msg' => '请输入正确的中文姓名']);
		}else{
			$this->ajaxReturn(['status' => true, 'msg' => '认证成功']);
		}
	}
	
	/**
	 * 验证身份证号码
	 */
	public function checkcertid(){
		$id = session("user.userid");
		$certid = I('post.certid', '', 'trim');
		$name = I('post.name', '', 'trim');
		if(!isChineseName($name)){
			//$this->ajaxReturn(['status' => false, 'msg' => '请输入正确的中文姓名']);
			$this->ajaxReturn(['status' => false, 'msg' => '<img src="/Public/images/error.gif" title="请输入正确的中文姓名" />']);
		}
		if($certid == ''){
			$this->ajaxReturn(['status' => false, 'msg' => '<img src="/Public/images/error.gif" title="请输入正确的身份证号码" />']);
		}
		if(!idcard_check($certid)){
			$this->ajaxReturn(['status' => false, 'msg' => '<img src="/Public/images/error.gif" title="身份证信息校验错误" />']);
		}
		
		//调用实名认证，写入数据
		//$r = getIDCardInfo($certid);
		//if($r['error'] == 2){
		$rr = M("players")->where(array("idcard"=>$certid))->find();
		if($rr){
			if($rr['name'] != $name) {
				$this->ajaxReturn(['status' => false, 'msg' => '<img src="/Public/images/error.gif" title="身份证信息校验错误" />']);
			}
			if(M("user_players")->where(array("uid"=>$id,"player_id"=>$rr['id']))->find()){
				$this->ajaxReturn(['status' => false, 'msg' => '重复添加']);
			}else{
				//添加选手与用户关联关系
				$arr1 = array(
					'uid'=>$id,
					'player_id'=>$rr["id"],
					'is_self'=>0,
					'created_at'=>time()
				);
				if(M('user_players')->add($arr1)){
					$this->ajaxReturn(['status' => true, 'msg' => '认证成功']);
				}
			}
			$this->ajaxReturn(['status' => true, 'msg' => '该选手已经在选手库']);
		}else{
			$r = IdCard::check($name, $certid);
			//if($r['status'] == true ){
			if($r['data']['isok'] == 1){ //查询成功
				if($r['data']['code'] == 1){
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
					if($palyerid){
						$arr1 = array(
							'uid'=>$id,
							'player_id'=>$palyerid,
							'is_self'=>0,
							'created_at'=>time()
						);
						if(M('user_players')->add($arr1)){
							$this->ajaxReturn(['status' => true, 'msg' => '认证成功']);
						}
						
					}
				}
				elseif($r['data']['code'] == 2){
					$this->ajaxReturn(['status' => false, 'msg' => '<img src="/Public/images/error.gif" title="身份证信息校验错误" />']);
				}
				else{ //不一致
					$this->ajaxReturn(['status' => false, 'msg' => '<img src="/Public/images/error.gif" title="无此身份证号码" />']);
				}
			}else{
				$this->ajaxReturn(['status' => false, 'msg' => '<img src="/Public/images/error.gif" title="认证失败" />']);
			}
			//$this->ajaxReturn(['status' => true, 'msg' => '校验成功']);
		}
		
	}
	
}