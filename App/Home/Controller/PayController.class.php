<?php
namespace Home\Controller;
use Think\Controller;
use Home\Controller\CommonController; 
use Think\Page;
class PayController extends CommonController{
	public function __construct() {
        parent::__construct();
        /*
         *检测用户登陆
        */
        $this->checkLoginInfo();    
    }

    /**
     * 线下支付
     * @return [type] [description]
     */
    public function line(){
    	$match_id =  I('match_id');
    	$uid = session("user.userid"); 
		$res = M("match_entrys")->where(array("id"=>$match_id))->find();
		if($res['pay_status'] != 0){
			$this->error("请勿重复支付", U('paylist'));
		}
		//1、更新状态
		$data["pay_status"] = 1;
		$data["paid_at"] = time();
		$data["updated_at"] = time();
		$r = M("match_entrys")->where("id=".$match_id)->save($data);
		//2、生成一个支付订单
		$arr = array(
					'pay_number'=>generatePayNumber(),
					'match_entry_id'=> $match_id,
					'amount'=> $res['amount'],
					'method_id' =>1,
					'pay_status' =>1,
					'created_at'=>time(),
					'updated_at'=>time(),
					'paid_at'=>time(),
					'remark'=>''
			);
		$e = M("payments")->add($arr);
		//3、生成背号
		$s = MatchPlayerBackNumberCreate::create($res['match_id'], $res['uid']);
		if($r && $e && $s['status'] == true){
			$this->success("支付成功","paylist");
		}else{
			$this->error("支付异常","paylist");
		}

    }

    /**
     * 线上支付
     */
    public function online(){

    }

}
?>