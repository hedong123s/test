<?php
namespace Admin\Controller;

use Think\Controller;


class GlobalController extends Controller {

	//生成验证码$name 为验证码名
	public function verify($name=''){
		$verify=new \Think\Verify();
		$verify->entry($name);
	}
	
	//校验验证码$name 为验证码名
	public function checkVerify($code, $name=''){
		$verify=new \Think\Verify();
		return $verify->check($code,$name);
	}
}
?>