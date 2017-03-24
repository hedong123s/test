<?php
namespace Home\Controller;

use Common\Controller\Controller;

/**
 * Home模块下基础控制器基类
 *
 * @author Flc <2016-08-22 10:35:45>
 */
abstract class BaseController extends Controller
{
    /**
     * 初始化
     * 
     * @return [type] [description]
     */
    public function _initialize()
    {
        parent::_initialize();
        $this->is_login();
    }

    //已经登陆不能在进入的页面
	function is_login(){
		if(session("user") != ''){
			$filter = array(
					'/Home/Account/login',
					'/Home/Account/register',
					'/Home/Account/register',
					'/Home/Account/doregister',
			);
			//var_dump($filter);exit();
			if(in_array(__ACTION__,$filter)){
				$this->goUrl(U('home/index/index'));
			}
		}
	}

	//跳转操作(js客户端跳转方式)
	function goUrl($url){
		echo '<script>location.replace("'.$url.'")</script>';
		exit;
	}
}