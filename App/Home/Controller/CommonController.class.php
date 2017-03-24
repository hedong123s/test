<?php
namespace Home\Controller;
use Think\Controller;
use Home\Controller\BaseController;  //基于HOME基础控制器
class CommonController extends BaseController {
	//初始化
    function _initialize(){
		    parent::_initialize();  //继承父级
    }
    
    /*
     * 检测用户的登陆信息
     */
   public function checkLoginInfo(){
       /*
        * 获取用户登陆信息
        */
       $userInfo=session("user");
       if($userInfo == ''){
           redirect(U('/account/login'));
           exit();
       }
    }

    /**
     * 获取当前登录的用户信息
     * @return [type] [description]
     */
    protected function _getUser()
    {
        static $result = null;

        if ($result != null)
            return $result;

        $uid = session('user.userid');

        return M('users')->where(['id' => $uid])->find(); 
    }

    /**
     * 校验冰获取当前登录的用户的比赛信息（如不合法会跳出或ajax返回）
     * @param  integer $match_id     赛事ID
     * @param  array   &$match       赛事信息
     * @param  [type] &$match_entry [description]
     * @return [type]               [description]
     */
    protected function _ckMatchEntry($match_id, &$match = null, &$match_entry = null)
    {
        // 获取当前登录信息
        $user = $this->_getUser();
        $uid  = $user['id'];

        // 获取赛事信息
        if (! $match = M('matchs')->where(['id' => $match_id])->find()) {
            if (IS_AJAX) {
                $this->ajaxReturn(['status' => false, 'msg' => '赛事信息不合法']);
            } else {
                $this->error('赛事信息不合法');
            }
            exit;
        }

        // 获取报名信息
        if (! $match_entry = M('match_entrys')->where(['match_id' => $match_id, 'uid' => $uid])->find()) {
            if (IS_AJAX) {
                $this->ajaxReturn(['status' => false, 'msg' => '当前比赛暂未报名']);
            } else {
                redirect(U('Match/signup', ['match_id' => $match_id]));
                //$this->error('当前比赛暂未报名', U('Match/signup', ['match_id' => $match_id]));
            }
            exit;
        }

        // 赋值到模板
        $this->assign('_header_menu', 'MATCH');  // 菜单
        $this->assign('_match', $match);  // 当前赛事信息
        $this->assign('_match_entry', $match_entry);  // 当前赛事报名信息
    }

    /**
     * 校验个人用户是否实名认证
     * @return [type] [description]
     */
    protected function _ckUserIdCard()
    {
        // 获取当前登录信息
        $user   = $this->_getUser();
        $uid    = $user['id'];
        $prefix = C('DB_PREFIX');

        // 个人用户
        if ($user['type'] == 0) {
            if (! M('players p')
                    ->field('p.id, p.name, p.idcard, p.sex, p.birthday, up.is_self')
                    ->join($prefix . 'user_players up on up.player_id = p.id')
                    ->where([
                        'up.uid'     => $uid,
                        'up.is_self' => 1,
                        'p.type'     => 0
                    ])
                    ->find()
            ) {
                if (IS_AJAX) {
                    $this->ajaxReturn(['status' => false, 'msg' => '必须先实名认证才可操作']);
                } else {
                    $this->error('必须先实名认证才可操作', U('Member/index'));
                    exit;
                }
            }
        }
    }
}