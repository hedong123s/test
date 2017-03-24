<?php
/**
 * 全站基础控制器
 *
 * @author Flc <2016-08-22 10:26:17>
 */
namespace Common\Controller;

use Think\Controller as ThinkController;
use Common\Support\Validator;

abstract class Controller extends ThinkController
{
    /**
     * 初始化
     * 
     * @return mixed 
     */
    public function _initialize()
    {
        header("Content-Type: text/html; charset=utf-8"); // 输出编码

        $this->_init(); // 初始化
    }

    /**
     * 初始化
     * @return [type] [description]
     */
    protected function _init(){
        // 设置Token
        $this->setToken();

        // 初始化数据库
        M()->db(1, 'DB_QOFX');
        M()->db(2, 'DB_QOOP');
    }

    /**
     * 设置Token
     * @example
     *
     *  使用方法：
     *      1.前台增加input：<input type="hidden" value="{$_token}" /> 
     *      2.提交接受校验：$this->checkToken($token);
     */
    protected function setToken(){
        $this->_token = md5(session_id() . C('AUTH_GLOBAL_KEY'));

        $this->assign('_token', $this->_token);
    }

    /**
     * 校验Token
     * @param  string $token 接受的token值
     * @return boolean
     */
    protected static function checkToken($token){
        $nToken = md5(session_id() . C('AUTH_GLOBAL_KEY'));
        if ($nToken != $token) {
            return false;
        }
        return true;
    }

    /**
     * 404错误
     */
    public function _404(){
        send_http_status('404');
        
        $this->display('Home@Public/Errors/404');
        exit;
    }

    /**
     * 空白方法
     */
    public function _empty(){
        $this->_404();
    }
}