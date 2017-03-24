<?php
namespace Admin\Controller;

use Common\Controller\Controller;
use Common\Service\AdminAuth as Auth;

/**
 * Admin模块下基础控制器基类
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

        /**
         * 后台登录验证
         */
        $this->_checkLogin();

        /**
         * 验证当前页面是否有权限
         */
        $this->_checkPageRule();
    }

    /**
     * 后台登录验证
     */
    protected function _checkLogin(){
        // 如果未登录，或者登录的账号不为管理员
        if (! Auth::check()) {
            Auth::logout();
            $this->redirect('admin/index/login');
        }
    }

    /**
     * 验证当前页面是否有权限
     */
    protected function _checkPageRule(){
        $pageRule = strtolower(MODULE_NAME . "/" . CONTROLLER_NAME . '/' . ACTION_NAME);

        if (!Auth::verifyRule($pageRule)) {
            $this->error('您暂无权限访问！');
            exit;
        }
    }
}