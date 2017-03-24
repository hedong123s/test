<?php
namespace Admin\Controller;

use Common\Service\AdminAuth as Auth;  // 用户信息
use Common\Support\Sms;
class IndexController extends BaseController {

	/**
	 * 重置初始化
	 * @return [type] [description]
	 */
	function _initialize(){
		// ...
	}
	
	/**
	 * 首页页面
	 * @return [type] [description]
	 */
    function index(){
		parent::_initialize();
		require_once MODULE_PATH."Conf/menu.php";  //载入菜单文件

		// 获取用户信息
		$admin = Auth::info();

		$this->navList = $MENU;
//		var_dump($MENU);die;
		$this->admin   = $admin;
		$this->display();
    }

    //菜单ajax
	function ajaxmenu(){
		parent::_initialize();
		$checkStatus=0;  //是否需要验证  1为需要，0为不需要

		$id=isset($_GET['id'])?intval($_GET['id']):0;
		require_once MODULE_PATH . "Conf/menu.php";  //载入菜单文件
		
		$resp='';		
		if(isset($MENU[$id]['submenu'])){
			
			$navList=$MENU[$id]['submenu'];  //获取二级（及三级）菜单
			
			foreach($navList as $navValue){  //二级循环
				$navIco=isset($navValue['ico'])?$navValue['ico']:""; //图标
				$navTitle=isset($navValue['title'])?$navValue['title']:"";  //标题
				$navTarget=isset($navValue['target'])?$navValue['target']:"main_iframe";  //target类型
				$navUrl=isset($navValue['url'])?$navValue['url']:"";  //连接
				
				if(isset($navValue['submenu'])){
					$resp.= '<li class="cur"><a href="javascript:;" class="ico '.$navIco.'">'.$navTitle.'</a><span class="arrow"></span>';
					
					//三级
					$resp.= '<ul>';
					
					foreach($navValue['submenu'] as $subList){
						$subTitle=isset($subList['title'])?$subList['title']:"";  //标题
						$subTarget=isset($subList['target'])?$subList['target']:"main_iframe";  //target类型
						$subUrl=isset($subList['url'])?$subList['url']:"";  //连接
						if($checkStatus==0 || !!$this->checkAjaxUrl($subUrl)){
							$resp.= '<li><a href="'.$subUrl.'" target="'.$subTarget.'">'.$subTitle.'</a></li>';
						}
					}
					
					$resp.= '</ul>';
					$resp.= '</li>';
				}else{
					if($checkStatus==0 || !!$this->checkAjaxUrl($navUrl)){
						$resp.= '<li><a href="'.$navUrl.'" target="'.$navTarget.'" class="ico '.$navIco.'">'.$navTitle.'</a></li>';
					}
				}
			}			
		}else{
			$resp.= "参数错误";
		}
		$this->show($resp);
	}
	
	/**
	 * 验证AjaxMenu Url是否显示
	 */
	function checkAjaxUrl($url){
		$url=strtolower($url);
		$urlArray=parse_url($url);
		if(!$urlArray['path']) return true;
		$urlPath=strtolower($urlArray['path']);
		foreach($this->getRuleAll() as $val){
			if(strpos($urlPath,strtolower($val))!==false){
				if(!in_array(strtolower($val),$this->getRuleUser())) return false;
			}
		}
		return true;
	}

	/**
	 * 欢迎页
	 * @return [type] [description]
	 */
	function main(){
		parent::_initialize();
		
		$this->display();
	}
	
	/**
	 * 登录页面
	 * @return [type] [description]
	 */
	public function login(){
		$this->display();
	}

	/**
	 * AJAX验证登录
	 * @return [type] [description]
	 */
	public function check(){
		$username = I('post.username', '', 'trim');
		$password = I('post.password', '', 'trim');
		$code     = I('post.code', '', 'trim');
		$keep     = I('post.keep', 0, 'intval');

		// A. 校验
		// A.1 输入校验
		if ($username == '') {
			$this->ajaxReturn(array('status' => false, 'msg' => '请输入用户名'));
		}
		if ($password == '') {
			$this->ajaxReturn(array('status' => false, 'msg' => '请输入密码'));
		}
		if ($code == '') {
			$this->ajaxReturn(array('status' => false, 'msg' => '请输入验证码'));
		}

		// A.2 验证码验证
		if (!R("Global/checkVerify", array($code, "adminlogin"))) {
			$this->ajaxReturn(array('status' => false, 'msg' => '验证码错误'));
		}

		// A.3 验证用户名
		if (!$admin = M('admins')->where(array('status' => 1, 'username' => $username))->find()) {
			$this->ajaxReturn(array('status' => false, 'msg' => '用户名不存在或已被禁用'));
		}

		// A.4 校验密码（PHP>=5.5）
		// if (! hash_check($password, $admin['password'])) {
		// 	$this->ajaxReturn(array('status' => false, 'msg' => '您输入的密码错误'));
		// }

		// B. 更新登录等信息
		$admin_data = array(
			'last_logined_ip' => get_client_ip(),
			'times'         => $admin['times'] + 1,
			'last_logined_at'     => time(),
		);
		if (false === M('admins')->where(array('id' => $admin['id']))->save($admin_data)) {
			$this->ajaxReturn(array('status' => false, 'msg' => '数据操作异常'));
		}

		// C. 生成session
		Auth::login($admin['id']);

		$this->ajaxReturn(array('status' => true, 'msg' => '登录成功'));
	}
	
	/**
	 * 退出登录
	 * @return [type] [description]
	 */
	public function logout(){
		Auth::logout();
		$this->redirect('admin/index/login');
	}

	/**
	 * 上传demo
	 */
	public function upload(){
		$this->seotitle = '上传demo';
		$this->display();
	}

	/**
	 * 编辑器
	 */
	public function editor(){
		$this->seotitle = '编辑器demo';
		$this->html = S('editor');
		$this->display();
	}

	/**
	 * 保存编辑器内容
	 */
	public function save_editor(){
		$content = I('content', '');
		S('editor', $content);
		$this->success('保存成功', U('editor'));
	}
    
}
?>