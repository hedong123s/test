<?php
namespace Home\Controller;
use Think\Controller;
use Home\Controller\CommonController; 
/**
 * 首页控制器
 * 
 * @author Flc <2016-08-22 10:44:29>
 */
class IndexController extends CommonController
{
    public function __construct() {
        parent::__construct();
        /*
         *检测用户登陆
        */
        $this->checkLoginInfo();    
    }

    /**
     * 首页
     * @return [type] [description]
     */
    public function index()
    {
        $user = $this->_getUser();

        $this->seotitle = '欢迎页';
        $this->user     = $user;
        $this->display();
    }


    /**
     * 列表页
     * @return [type] [description]
     */
    public function lists()
    {

        $this->display();
    }

    /**
     * 编辑页
     * @return [type] [description]
     */
    public function edit()
    {

        $this->display();
    }
}