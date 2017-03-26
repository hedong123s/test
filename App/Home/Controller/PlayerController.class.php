<?php
namespace Home\Controller;
use Think\Page;
use Think\PagePlayer;
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

	/**
	 * 通过ajax根据选手的ID数组    删除
	 * 			选手信息			palyers
	 * 			用户选手表信息		user_players
	 * 			赛事选手背号信息	match_player_backs
	 */
	public function ajaxDelAll(){
		$data = I('post.ids', '');
		if($data == ''){
			$this->ajaxReturn('未选择');
		}
		//开启事务
		$db = D();
		$db->startTrans();
		//先删除user_players表中的信息
		if(false === M('user_players')->where(array(
					'player_id'=>array('in', $data),
			))->delete()){
			$db->rollback();
			$this->ajaxReturn('删除失败');
		};
		//再删除match_player_backs中的信息
		if(false === M('match_player_backs')->where(array(
				'player_id' => array('in', $data)
			))->delete()){
			$db->rollback();
			$this->ajaxReturn('删除失败');
		};
		//在删除选手信息
		if(false === M('players')->where(array(
				'id' => array('in', $data)
			))->delete()){
			$db->rollback();
			$this->ajaxReturn('删除失败');
		};

		//提交事务
		$db->commit();
		$this->ajaxReturn('ok');
	}

	/**
	 * ajax根据条件搜索选手
	 */
	public function ajaxSearchPlayer(){

		$userid = session('user.userid');
		//过滤参数，并添加where条件
		$where['a.uid'] = $userid;
		//id
		$id = I('get.id', 0, 'intval');
		if($id !== 0){
			$where['b.id'] = array('like', '%'.$id.'%');
		}
		//姓名
		$name = I('get.name', '', 'trim');
		if($name !== ''){
			$where['b.name'] = array('like', '%'.$name.'%');
		}
		//年龄
		$minAge = I('get.minAge', 0, 'intval');
		$minAgeTime = ageToTime($minAge-1);
		$maxAge = I('get.maxAge', 0, 'intval');
		$maxAgeTime = ageToTime($maxAge);
		if($minAge !==0 && $maxAge !==0){
			$where['unix_timestamp(b.birthday)'] = array('between', array($maxAgeTime, $minAgeTime));
		}elseif($minAge !== 0 && $maxAge===0){
			$where['unix_timestamp(b.birthday)'] = array('elt', $minAgeTime);
		}elseif($maxAge !== 0 && $minAge===0){
			$where['unix_timestamp(b.birthday)'] = array('egt', $maxAgeTime);
		}
		//性别
		$sex = I('get.sex', '', 'trim');
		if($sex !== ''){
			$where['b.sex'] = $sex;
		}
		$upModel = M('user_players');
		//计算总数并分页
		$count = $upModel->alias('a')
				->where($where)
				->join('left join __PLAYERS__ b on a.player_id=b.id')
				->count();
		$page = new PagePlayer($count, 10);
		$pages['show'] = $page->show();
		$pages['total'] = $count;
		$pages['lists'] = $upModel->alias('a')
				->field('a.is_self, b.id, b.name, b.idcard, b.sex, b.birthday')
				->where($where)
				->join('left join __PLAYERS__ b on a.player_id=b.id')
				->limit($page->firstRow.','.$page->listRows)
				->order('b.id desc')
				->select();
		//计算年龄
		foreach($pages['lists'] as $k=>&$v){
			$v['age'] = calcAge(strtotime($v['birthday']));
		}
		$this->ajaxReturn($pages, 'json');
	}

}