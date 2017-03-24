<?php
namespace Home\Controller;
use Think\Controller;
use Home\Controller\CommonController; 
use Think\Page;
/**
 * 首页控制器
 * 
 * @author Flc <2016-08-22 10:44:29>
 */
class InfoController extends CommonController
{
	public function __construct() {
        parent::__construct();
    }

    public function index(){
    	$this->display();
    }

    /**
     * 赛事公布
     * @return [type] [description]
     */
    public function lists(){
    	$model = M('matchs')->where(['status' => 1]);

        // 查询总数
        $tModel = clone $model;
        $count = $tModel->count();

        // 分页
        $Page  = new Page($count, 10);

        // 列表数据
        $lists = $model->limit($Page->firstRow.','.$Page->listRows)->order(['id' => 'desc'])->select();

        $pages['show']  = $Page->show();  //分页输出
        $pages['total'] = $count;  //总数
        $pages['lists'] = $lists;

        $this->seotitle = '赛事列表';
        $this->pages    = $pages;
    	$this->display();
    }

    /**
     * 赛事详情
     * @return [type] [description]
     */
    public function matchentry(){
    	$match_id = I('get.match_id', 0, 'intval');

        // 获取赛事信息
        if (! $match = M('matchs')->where(['id' => $match_id])->find()) {
            $this->error('赛事信息不合法');
            exit;
        }

        $is_login = 0;
        if(session("user") != ''){
            $is_login = 1;
        }
        $this->assign("is_login",$is_login);
        $this->assign('_header_menu', 'MATCH');  // 菜单
        $this->assign('_match', $match);  // 当前赛事信息
        $this->seotitle = '赛事简介';
        $this->display();
    }

    /**
     * 报名系统
     */
    public function match(){
    	$model = M('matchs')->where(['status' => 1]);
        $is_login = 0;
        if(session("user") != ''){
            $is_login = 1;
        }
        // 查询总数
        $tModel = clone $model;
        $count = $tModel->count();

        // 分页
        $Page  = new Page($count, 10);

        // 列表数据
        $lists = $model->limit($Page->firstRow.','.$Page->listRows)->order(['id' => 'desc'])->select();

        $pages['show']  = $Page->show();  //分页输出
        $pages['total'] = $count;  //总数
        $pages['lists'] = $lists;
        $this->assign("is_login",$is_login);
        $this->seotitle = '赛事列表';
        $this->pages    = $pages;
    	$this->display();
    }
}