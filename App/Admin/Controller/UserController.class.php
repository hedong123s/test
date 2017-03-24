<?php
namespace Admin\Controller;

use Think\PageAdmin;
use Common\Support\Validator;
use Common\Service\AdminAuth as Auth;
use Common\Service\Invest\Invest;
use Common\Service\Invest\Helper;
use Common\Service\Invest\Strategy\QoFx;
use Common\Service\Invest\Strategy\Qoop;

/**
 * 用户管理控制器
 * 
 * @author Flc <2016-08-23 19:29:09>
 */
class UserController extends BaseController
{
    /**
     * 初始化
     * 
     * @return [type] [description]
     */
    public function _initialize()
    {
        parent::_initialize();

        $this->investQofx = new Invest(new QoFx); // 外汇
        $this->investQoop = new Invest(new Qoop);; // 二元
    }

    /**
     * 用户列表
     * @return [type] [description]
     */
    public function index()
    {
        $map       = array();       
        ($mt4id    = I('mt4id','','trim'))&&$map['mt4id']=array('like',"%".$mt4id."%");
        ($phone    = I('phone','','trim'))&&$map['phone']=array('like',"%".$phone."%");
        ($truename = I('truename','','trim'))&&$map['truename']=array('like',"%".$truename."%");
        ($email    = I('email','','trim'))&&$map['email']=array('like',"%".$email."%");        
 
       
        $prefix = C('DB_PREFIX');

        $count = M('users')->where($map)->count();
        $page  = new PageAdmin($count, C('PAGE_NUM'));
      
        $pages['show']  = $page->show();  //分页输出
        $pages['total'] = $count;  //总数
        $list           = M('users')
            ->where($map)
            ->order("userid desc")
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        foreach ($list as &$item) {
            // 外汇
            $item['qofx_invest_effe']   = $this->investQofx->getPersonEffeRecPeopleTotal($item['userid']);
            $item['qofx_invest_noeffe'] = $this->investQofx->getPersonNoEffeRecPeopleTotal($item['userid']);
            $item['qofx_invest_total']  = $this->investQofx->getPersonRecPeopleTotal($item['userid']);

            // 二元
            $item['qoop_invest_effe']   = $this->investQoop->getPersonEffeRecPeopleTotal($item['userid']);
            $item['qoop_invest_noeffe'] = $this->investQoop->getPersonNoEffeRecPeopleTotal($item['userid']);
            $item['qoop_invest_total']  = $this->investQoop->getPersonRecPeopleTotal($item['userid']);
        }
        $pages['lists'] = $list;
        $this->search   = array(
            'mt4id'=>$mt4id,
            'phone'=>$phone,
            'truename'=>$truename,
            'email'=>$email
        ); 

        $this->seotitle = '用户列表';
        $this->pages    = $pages;
        $this->display();
    }

    /**
     * 外汇、二元推荐数据
     * @return [type] [description]
     */
    public function user_reclist()
    {
        $userid       = I('get.userid', 0, 'intval');
        $paltform     = I('get.paltform', 'qofx', 'trim');
        $p            = I('get.p', 1, 'intval');
        $username     = I('get.username', '', 'trim');
        $phone        = I('get.phone', '', 'trim');
        $effeMinMoney = Helper::getEffeMinMoney();

        // 搜索相关
        $search = [];
        $map    = [];

        if (! empty($username)) {
            $map['username']    = $username;
            $search['username'] = $username;
        }

        if (! empty($phone)) {
            $map['phone']    = $phone;
            $search['phone'] = $phone;
        }

        // 搜索相关附加字段
        $search['userid'] = $userid;

        // 获取数据
        if ($paltform == 'qoop') {
            $search['paltform'] = 'qoop';
            $invest = new Invest(new Qoop);
        } else {
            $search['paltform'] = 'qofx';
            $invest = new Invest(new QoFx);
        }

        $rs = $invest->getPersonRecPeopleList($userid, $map, $p, C('PAGE_NUM'));
        if (! $rs || ! $rs['status']) {
            $this->ajaxReturn(['status' => false, 'msg' => '数据不存在']);
        }

        // 查询数据
        $count = $rs['data']['result_total'];

        $Page  = new PageAdmin($count, C('PAGE_NUM'));
        $page  = $Page->show();
        $list = $rs['data']['list'];

        $pages = [
            'show'  => $page,
            'total' => $count,
            'lists' => $list,
        ];

        $this->seotitle     = '用户推荐数据 - ' . ($paltform == 'qoop' ? '二元' : '外汇');
        $this->pages        = $pages;
        $this->search       = $search;
        $this->effeMinMoney = $effeMinMoney;
        $this->display();
    }

    /**
     * 实名认证
     * @return [type] [description]
     */
    public function idcard()
    {
        $map       = array();       
        ($mt4id   = I('mt4id','','trim'))&&$map['u.mt4id']=array('like',"%".$mt4id."%");
        ($phone    = I('phone','','trim'))&&$map['u.phone']=array('like',"%".$phone."%");
        ($truename = I('truename','','trim'))&&$map['c.truename']=array('like',"%".$truename."%");
        
        $prefix = C('DB_PREFIX');

        $count = M('idcard_audit c')->join($prefix . 'users u on u.userid = c.userid')->where($map)->count();
        $page  = new PageAdmin($count, C('PAGE_NUM'));        
        $pages['show']  = $page->show();  //分页输出
        $pages['total'] = $count;  //总数
        $pages['lists'] = M('idcard_audit c')
            ->field('c.*, u.phone, u.mt4id, fone.filepath idcard_imgurl1, ftwo.filepath idcard_imgurl2')
            ->join($prefix . 'users u on u.userid = c.userid')
            ->join($prefix . 'files fone on fone.file_id = c.idcard_img1', 'left')
            ->join($prefix . 'files ftwo on ftwo.file_id = c.idcard_img2', 'left')
            ->where($map)
            ->order("c.id desc")
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        
        $this->search   = array(
            'mt4id'=>$mt4id,
            'phone'=>$phone,
            'truename'=>$truename,           
        ); 

        $this->seotitle = '实名认证';
        $this->pages    = $pages;
        $this->display();
    }

    /**
     * 实名认证审核
     * @return [type] [description]
     */
    public function idcard_audit()
    {
        $id     = I('get.id', 0, 'intval');
        $prefix = C('DB_PREFIX');

        if (empty($id) ||
            ! $info = M('idcard_audit ia')
                ->field('ia.*, fone.filepath idcard_imgurl1, ftwo.filepath idcard_imgurl2')
                ->join($prefix . 'files fone on fone.file_id = ia.idcard_img1', 'left')
                ->join($prefix . 'files ftwo on ftwo.file_id = ia.idcard_img2', 'left')
                ->where(['ia.id' => $id])->find()
            ) 
        {
            $this->error('参数错误');
        }

        if ($info['status'] != 0) {
            $this->error('当前状态不可操作');
        }

        // 获取用户信息
        if (! $user = M('users')->where(['userid' => $info['userid'], 'idcard_cert' => 0])->find()) {
            $this->error('当前状态不可操作');
        }

        $this->seotitle = '实名认证审核';
        $this->info     = $info;
        $this->user     = $user;
        $this->display();
    }

    /**
     * 实名认证信息查看
     * @return [type] [description]
     */
    public function idcard_show()
    {
        $id     = I('get.id', 0, 'intval');
        $prefix = C('DB_PREFIX');

        if (empty($id) ||
            ! $info = M('idcard_audit ia')
                ->field('ia.*, fone.filepath idcard_imgurl1, ftwo.filepath idcard_imgurl2')
                ->join($prefix . 'files fone on fone.file_id = ia.idcard_img1', 'left')
                ->join($prefix . 'files ftwo on ftwo.file_id = ia.idcard_img2', 'left')
                ->where(['ia.id' => $id])->find()
            ) 
        {
            $this->error('参数错误');
        }

        // 获取用户信息
        if (! $user = M('users')->where(['userid' => $info['userid']])->find()) {
            $this->error('用户数据异常');
        }

        $this->seotitle = '实名认证信息查看';
        $this->info     = $info;
        $this->user     = $user;
        $this->display();
    }

    /**
     * 实名认证审核保存
     * @return [type] [description]
     */
    public function idcard_audit_store()
    {
        $id     = I('post.id', 0, 'intval');
        $params = I('post.');

        $dbTrans = M();
        $dbTrans->startTrans();

        // A. 校验
        // A.1 
        if (empty($id) ||
            ! $info = M('idcard_audit')->where(['id' => $id])->lock(true)->find()) 
        {
            $dbTrans->rollback();
            $this->ajaxReturn(['status' => false, 'msg' => '参数错误']);
        }

        if ($info['status'] != 0) {
            $dbTrans->rollback();
            $this->ajaxReturn(['status' => false, 'msg' => '当前状态不可操作']);
        }

        // A.2 获取用户信息并校验是否可操作
        if (! $user = M('users')->where(['userid' => $info['userid'], 'idcard_cert' => 0])->lock(true)->find()) {
            $dbTrans->rollback();
            $this->ajaxReturn(['status' => false, 'msg' => '当前状态不可操作']);
        }

        // A.3 数据校验
        $rules = [
            'status'  => 'required|in:1,2',
            'context' => 'required'
        ];
        $messages = [
            'status.required'  => '请选择审核状态',
            'status.in'        => '审核状态参数错误',
            'context.required' => '请输入审核备注'
        ];
        $v = Validator::make($params, $rules, $messages);

        if ($v->fails()) {
            $dbTrans->rollback();
            $this->ajaxReturn(['status' => false, 'msg' => $v->errors()->first()]);
        }

        // B. 操作数据
        // B.1 审核成功
        if ($params['status'] == 1) {
            // B.1.1 更新用户表
            $user_data = [
                'idcard'             => $info['idcard'],
                'idcard_img1'        => $info['idcard_img1'],
                'idcard_img2'        => $info['idcard_img2'],
                'idcard_cert'        => 1,
                'idcard_cert_source' => 0,
                'updated_at'         => time()
            ];

            if (false === M('users')->where(['userid' => $info['userid'], 'idcard_cert' => 0])->save($user_data))
            {
                $dbTrans->rollback();
                $this->ajaxReturn(['status' => false, 'msg' => '服务器异常，请稍后再试']);
            }

            // B.1.2 更新审核表
            $info_data = [
                'status'     => 1,
                'updated_at' => time(),
                'audited_at' => time(),
                'context'    => $params['context'],
                'remark'     => serialize([
                    'user_before' => $user,
                    'user_after'  => array_merge($user, $user_data),
                    'info_before' => $info,
                    'operator'    => Auth::info()
                ])
            ];

            if (false === M('idcard_audit')->where(['id' => $id, 'status' => 0])->save($info_data)) 
            {
                $dbTrans->rollback();
                $this->ajaxReturn(['status' => false, 'msg' => '服务器异常，请稍后再试']);
            }
        } 

        // B.2 审核失败
        elseif ($params['status'] == 2) {
            $info_data = [
                'status'     => 2,
                'updated_at' => time(),
                'audited_at' => time(),
                'context'    => $params['context'],
                'remark'     => serialize([
                    'user_before' => $user,
                    'info_before' => $info,
                    'operator'    => Auth::info()
                ])
            ];

            if (false === M('idcard_audit')->where(['id' => $id, 'status' => 0])->save($info_data)) 
            {
                $dbTrans->rollback();
                $this->ajaxReturn(['status' => false, 'msg' => '服务器异常，请稍后再试']);
            }
        }

        else {
            $dbTrans->rollback();
            $this->ajaxReturn(['status' => false, 'msg' => '操作错误']);
        }

        // C. 提交
        $dbTrans->commit();

        $this->ajaxReturn(['status' => true, 'msg' => '成功', 'url' => U('idcard')]);
    }

    /**
     * 银行卡审核列表
     * @return [type] [description]
     */
    public function bank()
    {
        $prefix = C('DB_PREFIX');
        $map       = array();       
        ($mt4id    = I('mt4id','','trim'))&&$map['u.mt4id']=array('like',"%".$mt4id."%");
        ($phone    = I('phone','','trim'))&&$map['u.phone']=array('like',"%".$phone."%");
        ($truename = I('truename','','trim'))&&$map['u.truename']=array('like',"%".$truename."%");

        $count = M('bank_audit ba')->join($prefix . 'users u on u.userid = ba.userid')->where($map)->count();
        $page  = new PageAdmin($count, C('PAGE_NUM'));
        
        $pages['show']  = $page->show();  //分页输出
        $pages['total'] = $count;  //总数
        $pages['lists'] = M('bank_audit ba')
            ->field('ba.*, u.phone, u.truename, u.mt4id, f.filepath bank_imgurl, prov.province bank_addr_provname, city.city bank_addr_cityname')
            ->join($prefix . 'users u on u.userid = ba.userid')
            ->join($prefix . 'files f on f.file_id = ba.bank_img', 'left')
            ->join($prefix . 'province prov on prov.provinceid = ba.bank_addr_prov')
            ->join($prefix . 'city city on city.cityid = ba.bank_addr_city')
            ->where($map)
            ->order("ba.id desc")
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        $this->search   = array(
            'mt4id'=>$mt4id,
            'phone'=>$phone,
            'truename'=>$truename,
            'email'=>$email
        ); 
        $this->seotitle = '银行卡认证';
        $this->pages    = $pages;
        $this->display();
    }

    /**
     * 实名认证审核
     * @return [type] [description]
     */
    public function bank_audit()
    {
        $id     = I('get.id', 0, 'intval');
        $prefix = C('DB_PREFIX');

        if (empty($id) ||
            ! $info = M('bank_audit ba')
                ->field('ba.*, f.filepath bank_imgurl, prov.province bank_addr_provname, city.city bank_addr_cityname')
                ->join($prefix . 'files f on f.file_id = ba.bank_img', 'left')
                ->join($prefix . 'province prov on prov.provinceid = ba.bank_addr_prov')
                ->join($prefix . 'city city on city.cityid = ba.bank_addr_city')
                ->where(['ba.id' => $id])->find()
            ) 
        {
            $this->error('参数错误');
        }

        if ($info['status'] != 0) {
            $this->error('当前状态不可操作');
        }

        // 获取用户信息
        if (! $user = M('users')->where(['userid' => $info['userid'], 'bank_cert' => 0])->find()) {
            $this->error('当前状态不可操作');
        }

        $this->seotitle = '银行卡认证审核';
        $this->info     = $info;
        $this->user     = $user;
        $this->display();
    }

    /**
     * 银行认证审核保存
     * @return [type] [description]
     */
    public function bank_audit_store()
    {
        $id     = I('post.id', 0, 'intval');
        $params = I('post.');

        $dbTrans = M();
        $dbTrans->startTrans();

        // A. 校验
        // A.1 
        if (empty($id) ||
            ! $info = M('bank_audit')->where(['id' => $id])->lock(true)->find()) 
        {
            $dbTrans->rollback();
            $this->ajaxReturn(['status' => false, 'msg' => '参数错误']);
        }

        if ($info['status'] != 0) {
            $dbTrans->rollback();
            $this->ajaxReturn(['status' => false, 'msg' => '当前状态不可操作']);
        }

        // A.2 获取用户信息并校验是否可操作
        if (! $user = M('users')->where(['userid' => $info['userid'], 'bank_cert' => 0])->lock(true)->find()) {
            $dbTrans->rollback();
            $this->ajaxReturn(['status' => false, 'msg' => '当前状态不可操作']);
        }

        // A.3 数据校验
        $rules = [
            'status'  => 'required|in:1,2',
            'context' => 'required'
        ];
        $messages = [
            'status.required'  => '请选择审核状态',
            'status.in'        => '审核状态参数错误',
            'context.required' => '请输入审核备注'
        ];
        $v = Validator::make($params, $rules, $messages);

        if ($v->fails()) {
            $dbTrans->rollback();
            $this->ajaxReturn(['status' => false, 'msg' => $v->errors()->first()]);
        }

        // B. 操作数据
        // B.1 审核成功
        if ($params['status'] == 1) {
            // B.1.1 更新用户表
            $user_data = [
                'bank_name'        => $info['bank_name'],
                'bank_number'      => $info['bank_number'],
                'bank_addr_prov'   => $info['bank_addr_prov'],
                'bank_addr_city'   => $info['bank_addr_city'],
                'bank_addr_name'   => $info['bank_addr_name'],
                'bank_img'         => $info['bank_img'],
                'bank_cert'        => 1,
                'bank_cert_source' => 0,
                'updated_at'       => time()
            ];

            if (false === M('users')->where(['userid' => $info['userid'], 'bank_cert' => 0])->save($user_data))
            {
                $dbTrans->rollback();
                $this->ajaxReturn(['status' => false, 'msg' => '服务器异常，请稍后再试']);
            }

            // B.1.2 更新审核表
            $info_data = [
                'status'     => 1,
                'updated_at' => time(),
                'audited_at' => time(),
                'context'    => $params['context'],
                'remark'     => serialize([
                    'user_before' => $user,
                    'user_after'  => array_merge($user, $user_data),
                    'info_before' => $info,
                    'operator'    => Auth::info()
                ])
            ];

            if (false === M('bank_audit')->where(['id' => $id, 'status' => 0])->save($info_data)) 
            {
                $dbTrans->rollback();
                $this->ajaxReturn(['status' => false, 'msg' => '服务器异常，请稍后再试']);
            }
        } 

        // B.2 审核失败
        elseif ($params['status'] == 2) {
            $info_data = [
                'status'     => 2,
                'updated_at' => time(),
                'audited_at' => time(),
                'context'    => $params['context'],
                'remark'     => serialize([
                    'user_before' => $user,
                    'info_before' => $info,
                    'operator'    => Auth::info()
                ])
            ];

            if (false === M('bank_audit')->where(['id' => $id, 'status' => 0])->save($info_data)) 
            {
                $dbTrans->rollback();
                $this->ajaxReturn(['status' => false, 'msg' => '服务器异常，请稍后再试']);
            }
        }

        else {
            $dbTrans->rollback();
            $this->ajaxReturn(['status' => false, 'msg' => '操作错误']);
        }

        // C. 提交
        $dbTrans->commit();

        $this->ajaxReturn(['status' => true, 'msg' => '成功', 'url' => U('bank')]);
    }

    /**
     * 银行卡认证信息查看
     * @return [type] [description]
     */
    public function bank_show()
    {
        $id     = I('get.id', 0, 'intval');
        $prefix = C('DB_PREFIX');

        if (empty($id) ||
            ! $info = M('bank_audit ba')
                ->field('ba.*, f.filepath bank_imgurl, prov.province bank_addr_provname, city.city bank_addr_cityname')
                ->join($prefix . 'files f on f.file_id = ba.bank_img', 'left')
                ->join($prefix . 'province prov on prov.provinceid = ba.bank_addr_prov')
                ->join($prefix . 'city city on city.cityid = ba.bank_addr_city')
                ->where(['ba.id' => $id])->find()
            ) 
        {
            $this->error('参数错误');
        }

        // 获取用户信息
        if (! $user = M('users')->where(['userid' => $info['userid']])->find()) {
            $this->error('用户数据异常');
        }

        $this->seotitle = '银行卡认证信息查看';
        $this->info     = $info;
        $this->user     = $user;
        $this->display();
    }
}
?>