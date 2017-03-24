<?php
namespace Home\Controller;
use Common\Support\Sms;

/**
 * Created by PhpStorm.
 * User: hedong
 * Date: 2016/12/8
 * Time: 13:58
 */
class AccountController extends BaseController{
    /**
     * 登陆
     */
    public function login(){
        $this->prepage_url = $_SERVER["HTTP_REFERER"] ;   //上一页的url地址;
        $condition1 = strpos($this->prepage_url,'/account/login') !== false;  //过滤上一个页面--登陆
        $condition4 = strpos($this->prepage_url,'/account/logout') !== false;  //过滤上一个页面--登陆
        $condition2 =strpos($this->prepage_url,'/account/register') !== false; //过滤上一个页面--注册       
        $condition3 =strpos($this->prepage_url,'/account/do_register') !== false; //过滤上一个页面-完成注册    
        if($condition1 || $condition2 || $condition3 || $condition4){
            $this->prepage_url = U('match/index');
        }   
        if(strpos($this->prepage_url,'/info/match')){
            $this->prepage_url = U('MatchEntry/signup', ['match_id' => I('match_id')]);
        }

        if(strpos($this->prepage_url,'/info/matchentry')){
            $this->prepage_url = U('MatchEntry/signup', ['match_id' => I('match_id')]);
        }   
   

        
        $this->display();
    }

    /**
     * 登录表单
     */
    public function dologin(){
        $username = I('post.username', '', 'trim');
        $password = I('post.password', '', 'trim');
        $url = I('post.url', '', 'trim');
        $map['username'] = $username;
        $map['mobile'] = $username;
        $map['_logic'] = 'OR';
        $r = M("users")->where($map)->find();
        //用户名
        if(!$r){
            $this->ajaxReturn(['status' => false, 'msg' => '用户名/手机号错误']);
        }
        if(md5($password) !== $r['password']){
            $this->ajaxReturn(['status' => false, 'msg' => '密码错误']);
        }
        $userSession=array(  //用户信息
                "userid"=>$r['id'],
                "username"=>$r['username'],
                "timestamp"=>$nowtime,  //时间戳
                "type"=>$r['type']
                //"token"=>authPwd($r['user_name'].$nowtime.'userlogin')
        );
        session("user",$userSession);//写入session

        //$url=isset($_POST['url'])?safe_replace($_POST['url']):''; //登陆后跳转路径
        //$keep==1?cookie("user",$userSession):cookie("user",null);  //写入cookies,记住用户名
        //写入日志
        $this->ajaxReturn(['status' => true, 'msg' => '登录成功','url' => $url]);
    }

    /**
     * 退出登录
     */
    public function logout(){
        session("user",null);
        redirect(U('/account/login'));
        exit();
    }

    /**
     * 注册
     */
    public function register(){
        $this->display();
    }

    /**
     * 注册表单 
     */
    public function doregister(){
        $type = I('get.type', 0, 'intval');  //0=>个人 1=>机构        
        $username = I('post.username', '', 'trim');
        $nickname = I('post.nickname', '', 'trim');
        $password = I('post.password', '', 'trim');
        $repassword = I('post.repassword', '', 'trim');
        $mobile = I('post.mobile', '', 'trim');
        $code = I('post.code', '', 'trim');
        $team_name = I('post.team_name', '', 'trim');
        if (empty($username) || empty($nickname) || empty($password) || empty($repassword)
            || empty($mobile) || empty($code)) {
            $this->ajaxReturn(['status' => false, 'msg' => '填写信息不完整']);
        }
        if($password !== $repassword){
            $this->ajaxReturn(['status' => false, 'msg' => '密码不一致']);
        }
        if(strlen($password) < 6){
            $this->ajaxReturn(['status' => false, 'msg' => '密码长度不足6位']);
        }
        if(M('users')->where(array('username'=>$username))->find()){
            $this->ajaxReturn(['status' => false, 'msg' => '该用户名已经注册']);
        }
        $res=M('users')->where(array('mobile'=>$mobile))->find();
        if($res){
            $this->ajaxReturn(['status' => false, 'msg' => '该手机号码已经注册']);
        }
        //检查验证码
        if($code !== session("codeauth.code")){
            $this->ajaxReturn(['status' => false, 'msg' => '验证码错误']);
        }        
        if($mobile !== session("codeauth.phone")){
            $this->ajaxReturn(['status' => false, 'msg' => '手机号非发送短信手机号码']);
        }
        
        if($type == 1 && $team_name == '' ){
            $this->ajaxReturn(['status' => false, 'msg' => '请填写代表队名称']);
        }
        $data = array(
                    'username'=>$username,
                    'nickname'=>$nickname,
                    'password'=>md5($password),
                    'mobile'  =>$mobile,
                    'type'    => $type,
                    'team_name' =>$team_name,
                    'created_at'=>time()
                );
        if (! M('users')->add($data)) {
            $this->ajaxReturn(['status' => false, 'msg' => '提交失败，请稍候再试']);
        }
        $this->ajaxReturn(['status' => true, 'msg' => '注册成功', 'url' => U('login')]);
    }

    /**
     * 发送短信验证码
     */
    public function sendphone(){
        $phone = I('phone');
        if(!is_mobile($phone)){
            $this->ajaxReturn(['status' => false, 'msg' => '手机号格式错误']);
        }
        if(M('users')->where(array("mobile"=>$phone))->find()){
            $this->ajaxReturn(['status' => false, 'msg' => '手机号已注册']);
        }
        $pool='0123456789';
        $rand_key='';
        for($i = 0;$i < 6;$i++){
            $rand_key.=substr($pool, mt_rand(0,  strlen($pool)-1),1);
        } 
        $rs = Sms::send('2001', $phone, [$rand_key]);
        if($rs['status'] == 1 && $rs['data']['errcode'] == 0){
            //写日志
            session("codeauth.phone",$phone);
            session("codeauth.code",$rand_key);
            $this->ajaxReturn(['status' => true, 'msg' => '发送成功']);
        }else{
            $this->ajaxReturn(['status' => false, 'msg' => $rs['data']['errcode']]);

        }
    }

}