<?php
namespace Admin\Controller;

use Think\PageAdmin;
use Common\Support\Validator;
use Common\Service\MatchPlayerBackNumberCreate;

/**
 * 报名审核
 */
class EnteredController extends BaseController
{
	/**
	 * 报名列表
	 * @return [type] [description]
	 */
	public function index(){
		$prefix = C('DB_PREFIX');
		$count = M("match_event_entrys")->count();
        $Page  = new PageAdmin($count, 10);
		$lists = M("match_event_entrys m")->limit($Page->firstRow.','.$Page->listRows)
		->field("m.*,u.username,me.event_title,me.event_number,me.dances,me.fee,ms.title")
		->join($prefix . 'users u on m.uid = u.id')
		->join($prefix . 'match_events me on me.id = m.event_id')
		->join($prefix . 'matchs ms on ms.id = m.match_id')
		->select();		
		$pages['show']  = $Page->show();  //分页输出
        $pages['total'] = $count;  //总数
        $pages['lists'] = $lists;
        $this->pages    = $pages;
		$this->display();
	}

	/**
	 * 审核通过
	 */
	public function pass(){
		$id = I('id');
		$match_entry_id = I('match_entry_id');
		$money = I('money');
		$status = M("match_event_entrys")->where(array("id"=>$id))->getField("status");
		if($status != 0){
			$this->error("请勿重新审核", U('index'));
		}
		$dbTrans = M();
        $dbTrans->startTrans();             
		$r = M("match_event_entrys")->where("id=".$id)->save(array("status"=>1,"audit_time"=>time()));
		if(!$r){
			$dbTrans->rollback();
			$this->error("审核失败", U('index'));
		}
		$e = M('match_entrys')->where("id=".$match_entry_id)->setInc("amount",$money);
		//全部审核完成
		$m = M('match_event_entrys')->where(array("match_entry_id"=>$match_entry_id,"status"=>0))->find();
		if(!$m){
			//更新赛事报名表状态
			$s = M('match_entrys')->where("id=".$match_entry_id)->save(array("status"=>1,"confirm_time"=>time()));
		}
		
		if(!$e){
			$dbTrans->rollback();
			$this->error("审核失败", U('index'));
		}
		if($r && $e){
			$dbTrans->commit();
			$this->success("已审核通过", U('index'));
		}
	}

	/**
	 * 审核不通过
	 */
	public function nopass(){
		$data['id'] =  I('id');
		$status = M("match_event_entrys")->where(array("id"=>$data['id']))->getField("status");
		if($status != 0){
			$this->error("请勿重新审核", U('index'));
		}
		$data['status'] = 2;
		$data['audit_time'] = time();
		if(M("match_event_entrys")->save($data)){
			$this->success("已审核不通过", U('index'));
		}else{
			$this->error("审核失败", U('index'));
		}
	}

	/**
	 * 待支付列表
	 */
	public function paylist(){
		$prefix = C('DB_PREFIX');
		$map = array("m.status"=>1);
		$count = M("match_entrys m")->where($map)->count();
        $Page  = new PageAdmin($count, 2);
		$lists = M("match_entrys m")->where($map)
				->field("m.*,u.username,ms.title")
				->join($prefix . 'users u on m.uid = u.id')
				//->join($prefix . 'match_events me on me.id = m.event_id')
				->join($prefix . 'matchs ms on ms.id = m.match_id')
				->select();
		//var_dump($list);
		$pages['show']  = $Page->show();  //分页输出
        $pages['total'] = $count;  //总数
        $pages['lists'] = $lists;
        $this->pages    = $pages;
        //var_dump($pages);
		$this->display();
	}

	/**
	 * 确认线下支付
	 */
	public function paycommit(){
		$id =  I('id');
		$res = M("match_entrys")->where(array("id"=>$id))->find();
		if($res['pay_status'] != 0){
			$this->error("请勿重复支付", U('paylist'));
		}
		//1、更新状态
		$data["pay_status"] = 1;
		$data["paid_at"] = time();
		$data["updated_at"] = time();
		$r = M("match_entrys")->where("id=".$id)->save($data);
		//2、生成一个支付订单
		$arr = array(
					'pay_number'=>generatePayNumber(),
					'match_entry_id'=> $id,
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

	public function matchlist(){
		$type = I('type');
		$count = M("matchs")->count();
		$Page  = new PageAdmin($count, 10);
		$lists = M("matchs")->order("id desc")->field("id,title")->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign("list",$list);
		$this->assign("type",$type);
		$pages['show']  = $Page->show();  //分页输出
        $pages['total'] = $count;  //总数
        $pages['lists'] = $lists;
        $this->pages    = $pages;
		$this->display();
	}

	/**
	 * 报名审核
	 */
	public function audit(){
		$id = I('id');
		$prefix = C('DB_PREFIX');
		$map['match_id'] = $id;

		// 搜索
        $search = $maps = [];
        $event_number = I('get.event_number', '', 'trim');
        $event_title  = I('get.event_title', '', 'trim');

        // 搜索.组别代码
        if (! empty($event_number)) {
            $search['event_number'] = $event_number;
            $maps['event_number']    = ['like', '%' . $event_number . '%'];
        }

        // 搜索.组别名称
        if (! empty($event_title)) {
            $search['event_title'] = $event_title;
            $maps['event_title']    = ['like', '%' . $event_title . '%'];
        }

		$match = M("matchs")->field("id,title")->where(array('id'=>$id))->find();
		$count = M("match_events")->where($map)->where($maps)->count();
        $Page  = new PageAdmin($count, 15);
		/*$lists = M("match_event_entrys m")->limit($Page->firstRow.','.$Page->listRows)
		->field("m.*,u.username,me.event_title,me.event_number,me.dances,me.fee,ms.title")
		->join($prefix . 'users u on m.uid = u.id')
		->join($prefix . 'match_events me on me.id = m.event_id')
		->join($prefix . 'matchs ms on ms.id = m.match_id')
		->select();*/		
		$lists = M("match_events m")->where($map)->where($maps)
				->limit($Page->firstRow.','.$Page->listRows)
				->field("m.*,ms.title")
				//->join($prefix . 'users u on m.uid = u.id')
				//->join($prefix . 'match_events me on me.id = m.event_id')
				->join($prefix . 'matchs ms on ms.id = m.match_id')
				->select();
		$pages['show']  = $Page->show();  //分页输出
        $pages['total'] = $count;  //总数
        $pages['lists'] = $lists;
        $this->matchname = $match['title'];
        $this->pages    = $pages;
		$this->display();
	}

	/**
	 * 支付审核
	 */
	public function pay_audit(){
		$id = I('id');
		$prefix = C('DB_PREFIX');
		$map['match_id'] = $id;
		$match = M("matchs")->field("id,title")->where(array('id'=>$id))->find();
		$count = M("match_entrys")->where($map)->count();
        $Page  = new PageAdmin($count, 10);
        $lists = M("match_entrys m")->where($map)
        		->order("id desc")
				->limit($Page->firstRow.','.$Page->listRows)
				->field("m.*,ms.title,u.username")
				->join($prefix . 'users u on m.uid = u.id')
				//->join($prefix . 'match_events me on me.id = m.event_id')
				->join($prefix . 'matchs ms on ms.id = m.match_id')
				->select();
		$pages['show']  = $Page->show();  //分页输出
        $pages['total'] = $count;  //总数
        $pages['lists'] = $lists;
        $this->matchname = $match['title'];
        $this->match_id = $match['id'];
        $this->pages    = $pages;
        //var_dump($lists);
		$this->display();
	}

	/**
	 * 报名审核
	 * @return [type] [description]
	 */
	public function audit_pass(){
		$id = I('id');
		$r = M("match_events")->where(array("id"=>$id))->find();

		/*if($r['status'] != 0){
			$this->error("请勿重新审核");
		}*/
		$map["match_id"] = $r['match_id'];
		$map['event_id'] = $r['id'];
		$map['status'] = 0;
		$arr['status'] = 1;
		$arr['audit_time'] = time();
		$rr = M("match_event_entrys")->where($map)->save($arr);
		$_arr = array('status'=>1,'audit_time'=>time());
		$r = M("match_events")->where(array("id"=>$id))->save($_arr);
		if($r){
			$this->success("已确认");
		}

	}

	/**
	 * 支付审核
	 */
	public function pay_audit_pass(){
		$id = I('id');
		$r = M("match_entrys")->where(array("id"=>$id))->find();
		if($r['transfer_status'] == 0){
			$this->error("未支付不能审核");
		}

		if($r['transfer_status'] == 1){
			$this->error("请勿重新审核");
		}
		//$map["match_id"] = $r['match_id'];
		//$map['event_id'] = $r['id'];
		//$map['status'] = 0;
		//$arr['status'] = 1;
		//$arr['audit_time'] = time();
		//$rr = M("match_event_entrys")->where($map)->save($arr);
		//1、更新状态
		$_arr = array('pay_status'=>1,'transfer_status'=>1,'paid_at'=>time(),'updated_at'=>time(),'transfer_end_time'=>time());
		$rr = M("match_entrys")->where(array("id"=>$id))->save($_arr);
		//2、生成一个支付订单
		$arr = array(
					'pay_number'=>generatePayNumber(),
					'match_entry_id'=> $id,
					'amount'=> $r['amount'],
					'method_id' =>1,
					'pay_status' =>1,
					'created_at'=>time(),
					'updated_at'=>time(),
					'paid_at'=>time(),
					'remark'=>''
			);
		$e = M("payments")->add($arr);
		$r['match_id'].'----'. $r['uid'];
		//3、生成背号
		$s = MatchPlayerBackNumberCreate::create($r['match_id'], $r['uid']);
		if($rr && $e && $s['status'] == true){
			$this->success("已确认成功");
		}else{
			$_arr = array('transfer_status'=>3,'transfer_end_time'=>'');
			$rr = M("match_entrys")->where(array("id"=>$id))->save($_arr);
			$this->error("确认异常");
		}
		
	}

	/**
	 * 批量审核
	 */
	public function batch_pass(){
		$id_str = I('id_str');
		$id_arr = explode(',', $id_str);
		foreach ($id_arr as $k => $v) {
			$id = $v;
			$r = M("match_events")->where(array("id"=>$id))->find();
			if($r['status'] != 0){
				continue;
			}
			$map["match_id"] = $r['match_id'];
			$map['event_id'] = $r['id'];
			$map['status'] = 0;
			$arr['status'] = 1;
			$arr['audit_time'] = time();
			$rr = M("match_event_entrys")->where($map)->save($arr);
			$_arr = array('status'=>1,'audit_time'=>time());
			$r[] = M("match_events")->where(array("id"=>$id))->save($_arr);
		}
		$this->ajaxReturn(['status' => true, 'msg' => '审核完成']);
		
	}

	public function audit_nopass(){
		$id = I('id');
		$r = M("match_events")->where(array("id"=>$id))->find();

		/*if($r['status'] != 0){
			$this->error("请勿重新审核");
		}*/
		$map["match_id"] = $r['match_id'];
		$map['event_id'] = $r['id'];
		$map['status'] = 0;
		$arr['status'] = 2;
		$arr['audit_time'] = time();
		$arr['end_time'] = time();
		$rr = M("match_event_entrys")->where($map)->save($arr);
		$_arr = array('status'=>2,'audit_time'=>time());
		$r = M("match_events")->where(array("id"=>$id))->save($_arr);
		if($r){
			$this->success("已取消");
		}
	}

	/**
	 * 导出
	 */
	public function export(){
		$prefix   = C('DB_PREFIX');
        $match_id = I('get.match_id', 0, 'intval');


        

        // C. 导出数据
        $excel = new Excel;

        // C.1 标题
        $title = ['序号','选手', '背号','组别代码', '比赛组别', '竞赛舞种', '服务费(元)', '代表队','报名费','审核'];

        // C.2 导出的数据
        $export_data = [];
        foreach ($match_event_entrys as $k=>$match_event_entry) {
            // C.2.1 选手信息
            $players = '';
            foreach ($match_event_entry['players'] as $player) {
                if (empty($players)) {
                    $players = $player['name'];
                } else {
                    $players .= '、' . $player['name'];
                }
            }

            $match_event_entry['back_numbers'] = M('match_event_entrys mee')
                    ->field('mpb.back_number')
                    ->join($prefix . 'match_event_entry_player meep on mee.id = meep.event_entry_id')
                    ->join($prefix . 'players p on meep.player_id = p.id')
                    ->join($prefix . 'match_player_backs mpb on mpb.player_id = meep.player_id and mpb.match_id = mee.match_id')
                    ->where([
                        'mee.id' => $match_event_entry['event_entry_id'],
                        'status' => 1,
                    ])
                    ->order('FIELD(p.sex, 1 , 2 , 0), p.birthday asc, p.id asc')
                    ->select();

            // C.2.2 拼合数据
            $export_data[] = [
                $k,
                $players,
                $match_event_entry['back_numbers'][0]['back_number'],
                $match_event_entry['event_number'],
                $match_event_entry['event_title'],
                $match_event_entry['event_dances'],
                decimal_number($match_event_entry['event_fee']),
                $match_entry['team_name'],
                $match_event_entry['event_fee'],
                '确认'
            ];
        }

        // 导出文件
        $excel->setTitle($title)->setData($export_data)->render('已缴费报名表.xlsx');
	}
}
?>