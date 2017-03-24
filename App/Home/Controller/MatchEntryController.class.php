<?php
namespace Home\Controller;

use Think\Page;
use Think\Model;
use Common\Support\Sign;

/**
 * 报名的赛事控制器
 */
class MatchEntryController extends CommonController
{
    /**
     * 初始化
     */
    public function __construct() {
        parent::__construct();

        /**
         * 检测用户登陆
         */
        $this->checkLoginInfo();

        /**
         * 个人校验实名认证
         */
        $this->_ckUserIdCard();
    }
    
	/**
	 * 赛事简介
	 */
	public function index()
    {
        $match_id = I('get.match_id', 0, 'intval');

        // 获取赛事信息
        if (! $match = M('matchs')->where(['id' => $match_id])->find()) {
            $this->error('赛事信息不合法');
            exit;
        }
        
        $this->assign('_header_menu', 'MATCH');  // 菜单
        $this->assign('_match', $match);  // 当前赛事信息
        $this->seotitle = '赛事简介';
        $this->display();
	}

    /**
     * 我要报名 - 步骤1 - 选择参赛人员
     */
    public function signup()
    {
        $match_id = I('get.match_id', 0, 'intval');

        // 校验赛事信息
        $this->_ckMatchEntry($match_id, $match, $match_entry);

        // 获取是否允许报名
        $is_signup = true;
        if (! in_array($match['stage'], [1, 3]) /*||
            time() < $match['sign_start_time'] ||
            time() > $match['sign_end_time']*/
        ) {
            $is_signup = false;
        }

        $this->is_signup = $is_signup;
        $this->seotitle  = '我要报名';
        $this->display();
    }

    /**
     * 我要报名 - 步骤1校验
     * @return [type] [description]
     */
    public function signup_check()
    {
        $match_id = I('post.match_id', 0, 'intval');
        $ipate    = I('post.ipate', '', 'trim');
        $person   = I('post.person', 0, 'intval');

        // 校验赛事信息
        $this->_ckMatchEntry($match_id, $match, $match_entry);

        // A. 校验
        // A.0 是否可报名校验
        // A.0.1 报名时间校验
        if (! in_array($match['stage'], [1, 3]) /*||
            time() < $match['sign_start_time'] ||
            time() > $match['sign_end_time']*/
        ) {
            $this->ajaxReturn(['status' => false, 'msg' => '不在可报名阶段或时间内']);
        }
        // A.0.2 确认后不可报名
        if ($match_entry['status'] == 1) {
            $this->ajaxReturn(['status' => false, 'msg' => '当前状态不可报名']);
        }
        // A.1 校验板块
        if (empty($ipate) ||
            ! in_array($ipate, ['PROC', 'SPEC', 'AMAT', 'O_SIX', 'O_GROUP'])
        ) {
            $this->ajaxReturn(['status' => false, 'msg' => '请选择板块']);
        }

        // A.2 校验人数
        if (in_array($ipate, ['PROC', 'SPEC', 'AMAT']) &&
            (empty($person) || ! in_array($person, [1, 2]))
        ) {
            $this->ajaxReturn(['status' => false, 'msg' => '请选择选手人数']);
        }

        // B. 组合URL
        $url = '';
        // B.1 单、双人
        if (in_array($ipate, ['PROC', 'SPEC', 'AMAT'])) {
            $url = U('signup_step2', ['match_id' => $match_id, 'person_type' => $person, 'ipate' => $ipate]);
        }
        // B.2 6人组
        elseif ($ipate == 'O_SIX') {
            $url = U('signup_step2', ['match_id' => $match_id, 'person_type' => 3, 'ipate' => $ipate]);
        }
        // B.3 集体舞蹈
        elseif ($ipate == 'O_GROUP') {
            $url = U('signup_step2', ['match_id' => $match_id, 'person_type' => 4, 'ipate' => $ipate]);
        }

        $this->ajaxReturn(['status' => true, 'msg' => '成功', 'url' => $url]);
    }

    /**
     * 我要报名 - 步骤2 - 选择选手
     */
    public function signup_step2()
    {
        $prefix      = C('DB_PREFIX');
        $user        = $this->_getUser();
        $uid         = $user['id'];
        $match_id    = I('get.match_id', 0, 'intval');
        $person_type = I('get.person_type', 0, 'intval');
        $ipate       = I('get.ipate', '', 'trim');

        // A. 校验赛事信息
        $this->_ckMatchEntry($match_id, $match, $match_entry);

        // A.2 校验选手类型
        if (! in_array($person_type, [1, 2, 3, 4])) {
            $this->error('参数错误');
        }

        // B. 获取当前用户所有选手
        $players = M('players p')
            ->field('p.id, p.name, p.idcard, p.sex, p.birthday, up.is_self')
            ->join($prefix . 'user_players up on up.player_id = p.id')
            ->where([
                'up.uid'  => $uid,
                'p.type' => 0
            ])
            ->select();

        // B.1 获取年龄
        foreach ($players as &$player) {
            $player['age'] = calcAge(strtotime($player['birthday']));
        }

        // C. 如果是个人。则为选手自身
        $user_player = null;
        if ($user['type'] == 0) {
            $user_player = M('players p')
                ->field('p.id, p.name, p.idcard, p.sex, p.birthday, up.is_self')
                ->join($prefix . 'user_players up on up.player_id = p.id')
                ->where([
                    'up.uid'     => $uid,
                    'up.is_self' => 1,
                    'p.type'     => 0
                ])
                ->find();
            $user_player['age'] = calcAge(strtotime($user_player['birthday']));
        }

        $this->user        = $user;
        $this->person_type = $person_type;
        $this->ipate       = $ipate;
        $this->players     = $players;
        $this->user_player = $user_player;
        $this->seotitle    = '我要报名';
        $this->display();
    }

    /**
     * 我要报名 - 第二步提交校验
     * @return [type] [description]
     */
    public function signup_step2_check()
    {
        $prefix            = C('DB_PREFIX');
        $user              = $this->_getUser();
        $uid               = $user['id'];
        $match_id          = I('post.match_id', 0, 'intval');
        $person_type       = I('post.person_type', 0, 'intval');
        $ipate             = I('post.ipate', '', 'trim');
        $player            = I('post.player', []);
        $player_group_name = I('post.player_group_name', '', 'trim');

        // A. 校验
        // A.1 校验赛事信息
        $this->_ckMatchEntry($match_id, $match, $match_entry);

        // A.2 校验选手类型
        if (! in_array($person_type, [1, 2, 3, 4])) {
            $this->ajaxReturn(['status' => false, 'msg' => '参数错误']);
        }

        // A.3 所选板块校验
        // A.3.1 初步校验
        if (empty($ipate) ||
            ! in_array($ipate, ['PROC', 'SPEC', 'AMAT', 'O_SIX', 'O_GROUP'])
        ) {
            $this->ajaxReturn(['status' => false, 'msg' => '板块数据异常']);
        }
        // A.3.2 细节校验
        if ((in_array($ipate, ['PROC', 'SPEC']) && $person_type != 2) ||
            ($ipate == 'AMAT' && ! in_array($person_type, [1, 2])) ||
            ($ipate == 'O_SIX' && $person_type != 3) ||
            ($ipate == 'O_GROUP' && $person_type != 4)
        ) {
            $this->ajaxReturn(['status' => false, 'msg' => '板块数据异常']);
        }

        // A.4 选手相关校验
        // A.4.1 单人、双人、6人
        if (in_array($person_type, [1, 2, 3])) {
            // A.4.1.1 人数校验
            if (($person_type == 1 && count($player) != 1) ||
                ($person_type == 2 && count($player) != 2) ||
                ($person_type == 3 && count($player) != 6)
            ) {
                $this->ajaxReturn(['status' => false, 'msg' => '选手人员数量错误']);
            }

            // A.4.1.2 选手信息校验
            $validPlayerIds = []; // 校验通过的选手ID
            foreach ($player as $i => $player_id) {
                // A.4.1.2.0 查看是否选择选手
                if (empty($player_id)) {
                    $this->ajaxReturn(['status' => false, 'msg' => '请选择选手']);
                }

                // A.4.1.2.0 看是否有重复
                if (in_array($player_id, $validPlayerIds)) {
                    $this->ajaxReturn(['status' => false, 'msg' => '选择的选手有重复']);
                }

                // A.4.1.2.1 必须属于当前用户
                if (! $player_info = M('players p')
                    ->field('p.id, p.name, p.idcard, p.sex, p.birthday, up.is_self')
                    ->join($prefix . 'user_players up on up.player_id = p.id')
                    ->where([
                        'up.uid' => $uid,
                        'p.type' => 0,
                        'p.id'   => $player_id
                    ])
                    ->find()
                ) {
                    $this->ajaxReturn(['status' => false, 'msg' => '含部分选手非当前用户']);
                }

                // A.4.1.2.2 如果当前登录用户是个人、按第一名必须是自己
                if ($user['type'] == 0 &&
                    $i == 0 &&
                    $player_info['is_self'] == 0
                ) {
                    $this->ajaxReturn(['status' => false, 'msg' => '个人报名必须包含自己']);
                }

                // A.4.1.2.3 判断该选手是否在其他用户下报名当前比赛的组别
                if (M('match_event_entrys mee')
                        ->join($prefix . 'match_event_entry_player meep on mee.id = meep.event_entry_id')
                        ->where([
                            'mee.uid'        => ['neq', $uid],
                            'mee.match_id'   => $match_id,
                            'mee.status'     => ['in', [0, 1]],
                            'meep.player_id' => $player_id
                        ])
                        ->count() > 0
                ) {
                    $this->ajaxReturn(['status' => false, 'msg' => $player_info['name'] . '在其他用户下报名了当前比赛，无法报名']);
                }

                // A.4.1.2.4 写入已校验的选手
                $validPlayerIds[] = $player_id;
            }

            // A.4.1.3 写入session的数据
            $matchEventEntryData = [
                'person_type' => $person_type,
                'ipate'       => $ipate,
                'match_id'    => $match_id,
                'player'      => $player
            ];
        }
        // A.4.2 集体舞
        elseif ($person_type == 4) {
            // A.4.2.1 校验
            if (empty($player_group_name)) {
                $this->ajaxReturn(['status' => false, 'msg' => '请输入参赛团名称']);
            }

            // A.4.2.2 写入session的数据
            $matchEventEntryData = [
                'person_type'       => $person_type,
                'ipate'             => $ipate,
                'match_id'          => $match_id,
                'player_group_name' => $player_group_name
            ];
        }

        // B. 写入session
        session('match:event:entry', $matchEventEntryData);

        // C. 生成签名
        $sign = Sign::generate($matchEventEntryData);

        $this->ajaxReturn(['status' => true, 'msg' => '成功', 'url' => U('signup_step3', ['match_id' => $match_id, 'sign' => $sign])]);
    }

    /**
     * 我要报名 - 步骤3 - 选择组别
     * @return [type] [description]
     */
    public function signup_step3()
    {
        $prefix   = C('DB_PREFIX');
        $user     = $this->_getUser();
        $uid      = $user['id'];
        $match_id = I('get.match_id', 0, 'intval');
        $sign     = I('get.sign', '', 'trim');
        $matchEventEntryData = session('match:event:entry');  // 上一步的session

        // A. 校验
        // A.1 校验赛事信息
        $this->_ckMatchEntry($match_id, $match, $match_entry);

        // A.2 校验签名
        if (! $matchEventEntryData ||
            ! Sign::check($matchEventEntryData, $sign)
        ) {
            $this->error('请求非法');
        }

        // B. 获取选手相关信息
        // B.0 预置信息
        $players           = [];
        $player_group_name = '';
        // B.1 单人、双人、6人
        if (in_array($matchEventEntryData['person_type'], [1, 2, 3])) {
            // B.1.1 选手信息
            $players = M('players p')
                ->field('p.id, p.name, p.idcard, p.sex, p.birthday, up.is_self')
                ->join($prefix . 'user_players up on up.player_id = p.id')
                ->where([
                    'up.uid' => $uid,
                    'p.type' => 0,
                    'p.id'   => ['in', $matchEventEntryData['player']]
                ])
                ->order('FIELD(p.sex, 1 , 2 , 0), p.birthday asc')
                ->select();
            // B.1.2 选手年龄
            foreach ($players as &$player) {
                $player['age'] = calcAge(strtotime($player['birthday']));
            }
            unset($player);
        }
        // B.2 集体舞
        elseif ($matchEventEntryData['person_type'] == 4) {
            $player_group_name = $matchEventEntryData['player_group_name'];
        }

        // C. 获取可报名的组别信息
        // C.0 预置信息
        $events = [];
        // C.1 单人、双人、6人
        if (in_array($matchEventEntryData['person_type'], [1, 2, 3])) {
            // C.1.0 预置信息
            $player_min_birthday = '';  // 最小（早）的出生（年龄取大）
            $player_max_birthday = '';  // 最大（晚）的出生（年龄取小）
            $able_sexs           = []; // 选手的性别汇总
            $able_ipates         = []; // 可报的板块

            $map          = [];  // 数组搜索条件
            $mapString    = ' match_id = ' . $match_id;  // 字符串搜索条件
            $mapAgeString = ' age_type = 0 ';  // 预置搜索年龄相关条件
            $mapSexString = ''; // 预置搜索性别相关条件

            // C.1.1 循环取数据
            foreach ($players as $player) {
                // C.1.1.1 获取最晚、最早的出生年月日
                if (!empty($player['birthday'])) {
                    if (empty($player_min_birthday)) {
                        $player_min_birthday = $player['birthday'];
                        $player_max_birthday = $player['birthday'];
                    } else {
                        // 取小替换
                        if (strtotime($player_min_birthday) > strtotime($player['birthday'])) {
                            $player_min_birthday = $player['birthday'];
                        }
                        // 取大替换
                        if (strtotime($player_max_birthday) < strtotime($player['birthday'])) {
                            $player_max_birthday = $player['birthday'];
                        }
                    }
                }

                // C.1.1.2 获取相关选手的性别
                $able_sexs[] = $player['sex'];
            }
            unset($player);

            // C.1.2 额外搜索条件 - 年龄相关
            // C.1.2.1 最大年龄，出生取小（上限）
            if (! empty($player_min_birthday)) {
                $mapAgeString .= ' or (age_type = 1 and age_date >= "' . (date('Y', strtotime($player_max_birthday)) . '-01-01') . '") ';
            }
            // C.1.2.2 最小年龄，年龄取大（下限）
            if (! empty($player_max_birthday)) {
                $mapAgeString .= ' or (age_type = 2 and age_date <= "' . (date('Y', strtotime($player_min_birthday)) . '-01-01') . '") ';
            }
            // C.1.2.3 区间年龄
            if (! empty($player_min_birthday) ||
                ! empty($player_max_birthday)
            ) {
                $mapAgeThreeString = 'age_type = 3';

                if (! empty($player_min_birthday)) {
                    $mapAgeThreeString .= ' and age_date <= "' . (date('Y', strtotime($player_min_birthday)) . '-01-01') . '" ';
                }

                if (! empty($player_max_birthday)) {
                    $mapAgeThreeString .= ' and age_date2 >= "' . (date('Y', strtotime($player_max_birthday)) . '-01-01') . '" ';
                }

                $mapAgeString .= ' or (' . $mapAgeThreeString . ')';
            }
            // C.1.2.4 合并搜索
            $mapString .= ' and (' . $mapAgeString . ') ';

            // C.1.3 额外搜索条件 - 性别相关
            $sexArrs = getMatchEventMapSexArr($able_sexs);
            if (is_array($sexArrs) && count($sexArrs) > 0) {
                $map['person_sex_str'] = ['in', $sexArrs];
            }
            //print_r(getMatchEventMapSexArr($able_sexs));
            //print_r(getMatchEventMapSexArr([2, 2]));

            // C.1.4 额外搜索条件 - 可报名的板块
            // C.1.4.0 默认可报名的板块
            if ($matchEventEntryData['ipate'] == 'PROC') {  // 职业
                $hasEntryEventIpates = ['PROC', 'TAS'];
            } elseif ($matchEventEntryData['ipate'] == 'SPEC') {  // 专业
                $hasEntryEventIpates = ['SPEC', 'TAS'];
            } elseif ($matchEventEntryData['ipate'] == 'AMAT') {  // 业余
                $hasEntryEventIpates = ['AMAT', 'AMAT:ELITE', 'AMAT:ORDIN', 'TAS'];
            } else {
                $hasEntryEventIpates = ['PROC', 'SPEC', 'AMAT', 'TAS', 'AMAT:ELITE', 'AMAT:ORDIN'];  // 默认所有都可报名
            }
            // C.1.4.1 查询每个选手可报名的板块，并取交集
            foreach ($players as $player) {
                $playerHasEntryEventIpatesRs = M('match_event_entrys mee')
                    ->field('me.ipate')
                    ->distinct(true)
                    ->join($prefix . 'match_event_entry_player meep on meep.event_entry_id = mee.id')
                    ->join($prefix . 'match_events me on me.id = mee.event_id')
                    ->where([
                        'meep.player_id' => $player['id'],
                        'mee.match_id'   => $match_id,
                        'mee.status'     => ['in', [0, 1]],
                        'me.ipate'       => ['neq', 'TAS']
                    ])
                    ->select();
                if ($playerHasEntryEventIpatesRs) {
                    $playerHasEntryEventIpates = [];
                    foreach ($playerHasEntryEventIpatesRs as $heeRs) {
                        $playerHasEntryEventIpates[] = $heeRs['ipate'];
                    }
                    if (is_array($playerHasEntryEventIpates) && count($playerHasEntryEventIpates) > 0) {
                        $hasEntryEventIpates = array_intersect($hasEntryEventIpates, $playerHasEntryEventIpates);
                    }
                }
            }
            unset($player);
            // C.1.4.2 师生组默认均可参加
            if (! in_array('TAS', $hasEntryEventIpates)) {
                $hasEntryEventIpates[] = 'TAS';
            }
            // C.1.4.3 搜索条件
            $map['ipate'] = ['in', $hasEntryEventIpates];

            // C.1.5 额外搜索条件 - 不可报名的组别（已报的）
            $hasEntryEventIdRs = M('match_event_entrys mee')
                ->field('mee.event_id')
                ->distinct(true)
                ->join($prefix . 'match_event_entry_player meep on meep.event_entry_id = mee.id')
                ->where([
                    'meep.player_id' => ['in', $matchEventEntryData['player']],
                    'mee.match_id'   => $match_id,
                    'mee.status'     => ['in', [0, 1]]
                ])
                ->select();
            if ($hasEntryEventIdRs) {
                $hasEntryEventIds = [];
                foreach ($hasEntryEventIdRs as $heeRs) {
                    $hasEntryEventIds[] = $heeRs['event_id'];
                }
                if (is_array($hasEntryEventIds) && count($hasEntryEventIds) > 0) {
                    $map['id'] = ['not in', $hasEntryEventIds];
                }
            } 

            // C.1.6 如果是增报阶段，则只展示确认的组别
            if ($match['stage'] == 3) {
                $map['status'] = 1;
            } elseif ($match['stage'] == 1) {
                $map['status'] = ['in', [0, 1]];
            }
            
            // C.1.* 搜索
            $events = M('match_events')
                ->where([
                    'person_type' => $matchEventEntryData['person_type'],
                    '_string'     => $mapString
                ])
                ->where($map)
                ->select();
            // echo D()->_sql();
        }
        // C.2 集体舞
        elseif ($matchEventEntryData['person_type'] == 4) {
            $map = [];

            // C.2.1 如果是增报阶段，则只展示确认的组别
            if ($match['stage'] == 3) {
                $map['status'] = 1;
            } elseif ($match['stage'] == 1) {
                $map['status'] = ['in', [0, 1]];
            }

            $events = M('match_events')
                ->where(['match_id' => $match_id, 'person_type' => 4])
                ->where($map)
                ->select();
        }

        $this->event_entry_info  = $matchEventEntryData;
        $this->players           = $players;
        $this->player_group_name = $player_group_name;
        $this->events            = $events;
        $this->sign              = $sign;
        $this->seotitle          = '我要报名';
        $this->display();
    }

    /**
     * 我要报名 - 第三步校验
     * @return [type] [description]
     */
    public function signup_step3_check()
    {
        $prefix    = C('DB_PREFIX');
        $user      = $this->_getUser();
        $uid       = $user['id'];
        $match_id  = I('post.match_id', 0, 'intval');
        $sign      = I('post.sign', '', 'trim');
        $event_ids = I('post.event_id', []);
        $matchEventEntryData = session('match:event:entry');  // 上一步的session

        // A. 校验
        // A.1 校验赛事信息
        $this->_ckMatchEntry($match_id, $match, $match_entry);

        // A.1+1 是否可报名校验
        // A.1+1.1 报名时间校验
        /*if (time() < $match['sign_start_time'] ||
            time() > $match['sign_end_time']
        ) {
            $this->ajaxReturn(['status' => false, 'msg' => '不在可报名时间内']);
        }*/
        // A.1+1.2 确认后不可报名
        if ($match_entry['status'] == 1) {
            $this->ajaxReturn(['status' => false, 'msg' => '当前状态不可报名']);
        }

        // A.2 校验签名
        if (! $matchEventEntryData ||
            ! Sign::check($matchEventEntryData, $sign)
        ) {
            $this->error('请求非法');
        }

        // A.3. 组别选择校验
        if (empty($event_ids) || count($event_ids) == 0) {
            $this->ajaxReturn(['status' => false, 'msg' => '请选择报名的组别']);
        }

        // A.4 根据选手人数进行校验
        // A.4.1 单人、双人、6人
        if (in_array($matchEventEntryData['person_type'], [1, 2, 3])) {
            // A.4.1.0 增加每个选手最多报名的数量限制 @Flc 2017-2-19 11:16:11
            // A.4.1.0.1 获取当前总报名的组别(非6人组和师生组)
            // 旧版本 $match_event_total = count($event_ids);
            $match_event_total = M('match_events')->where([
                'id'          => ['in', $event_ids],
                'ipate'       => ['in', [
                    'PROC', 'SPEC', 'AMAT', 'AMAT:ELITE', 'AMAT:ORDIN'
                ]],
                'person_type' => ['neq', 3]

            ])->count();
            $match_event_max   = 4; // 每场赛事最多4次

            // A.4.1.0.2 循环查询当前选手
            foreach ($matchEventEntryData['player'] as $player_id) {
                // A.4.1.0.2.0 获取选手信息
                if (! $playerInfo = getPlayer($player_id)) {
                    $this->ajaxReturn(['status' => false, 'msg' => '选手信息异常']);
                }

                // A.4.1.0.2.1 查询当前选手当前比赛已经报名的总数
                $playerHasEventTotal = M('match_event_entrys mee')
                    ->join($prefix . 'match_event_entry_player meep on meep.event_entry_id = mee.id')
                    ->join($prefix . 'match_events me on mee.event_id = me.id')
                    ->where([
                        'meep.player_id' => $player_id,
                        'mee.match_id'   => $match_id,
                        'mee.status'     => [in, [0, 1]],
                        'me.ipate'       => ['in', [
                            'PROC', 'SPEC', 'AMAT', 'AMAT:ELITE', 'AMAT:ORDIN'
                        ]],
                        'me.person_type' => ['neq', 3]
                    ])
                    ->count();
                $playerHasEventTotal = ! $playerHasEventTotal ? 0 : $playerHasEventTotal;

                // A.4.1.0.2.2 判定总和
                if (($playerHasEventTotal + $match_event_total) > $match_event_max) {
                    $this->ajaxReturn(['status' => false, 'msg' => $playerInfo['name'] . '还能报名' . (max(0, ($match_event_max - $playerHasEventTotal))) . '个组别，不可多报']);
                }
            }
            unset($player_id);

            /// ---------------------

            // A.4.1.1 当前用户可选的板块
            // C.1.4.0 默认可报名的板块
            if ($matchEventEntryData['ipate'] == 'PROC') {  // 职业
                $hasEntryEventIpates = ['PROC', 'TAS'];
            } elseif ($matchEventEntryData['ipate'] == 'SPEC') {  // 专业
                $hasEntryEventIpates = ['SPEC', 'TAS'];
            } elseif ($matchEventEntryData['ipate'] == 'AMAT') {  // 业余
                $hasEntryEventIpates = ['AMAT', 'AMAT:ELITE', 'AMAT:ORDIN', 'TAS'];
            } else {
                $hasEntryEventIpates = ['PROC', 'SPEC', 'AMAT', 'TAS', 'AMAT:ELITE', 'AMAT:ORDIN'];  // 默认所有都可报名
            }
            // A.4.1.1.1 查询每个选手可报名的板块，并取交集
            foreach ($matchEventEntryData['player'] as $player_id) {
                $playerHasEntryEventIpatesRs = M('match_event_entrys mee')
                    ->field('me.ipate')
                    ->distinct(true)
                    ->join($prefix . 'match_event_entry_player meep on meep.event_entry_id = mee.id')
                    ->join($prefix . 'match_events me on me.id = mee.event_id')
                    ->where([
                        'meep.player_id' => $player_id,
                        'mee.match_id'   => $match_id,
                        'mee.status'     => [in, [0, 1]],
                        'me.ipate'       => ['neq', 'TAS']
                    ])
                    ->select();
                if ($playerHasEntryEventIpatesRs) {
                    $playerHasEntryEventIpates = [];
                    foreach ($playerHasEntryEventIpatesRs as $heeRs) {
                        $playerHasEntryEventIpates[] = $heeRs['ipate'];
                    }
                    if (is_array($playerHasEntryEventIpates) && count($playerHasEntryEventIpates) > 0) {
                        $hasEntryEventIpates = array_intersect($hasEntryEventIpates, $playerHasEntryEventIpates);
                    }
                }
            }
            unset($player_id);
            // A.4.1.1.1.2 师生组默认均可参加
            if (! in_array('TAS', $hasEntryEventIpates)) {
                $hasEntryEventIpates[] = 'TAS';
            }

            // A.4.1.2 判断报名的组别的板块是否有互斥的情况
            $eventIpatesRs = M('match_events')->field('event_title, ipate')->where(['id' => ['in', $event_ids]])->select();
            $eventIpates   = [];  // 当前组别里的类型
            foreach ($eventIpatesRs as $eventIpateRs) {
                // 当前报名的组别有选手不可报名
                if (! in_array($eventIpateRs['ipate'], $hasEntryEventIpates)) {
                    $this->ajaxReturn(['status' => false, 'msg' => '当前选手无法报名[' . $eventIpateRs['event_title'] . ']']);
                }

                // 当前报名的组别有互斥情况
                if (! in_array($eventIpateRs['ipate'], $eventIpates) &&
                    in_array($eventIpateRs['ipate'], ['PROC', 'SPEC', 'AMAT', 'AMAT:ELITE', 'AMAT:ORDIN'])
                ) {
                    $eventIpates[] = $eventIpateRs['ipate'];
                }
            }
            // 判断互斥
            if (count($eventIpates) > 1) {
                $this->ajaxReturn(['status' => false, 'msg' => '违规报名，请看规程']);
            }
        }
        // A.4.2 集体舞
        elseif ($matchEventEntryData['person_type'] == 4) {
            // 无校验
        }

        // A.5 禁报组别校验
        $disabled_numbers = [];
        // A.5.1 获取所有当前选手禁报的组别代码(仅单人、双人、6人限制)
        if (in_array($matchEventEntryData['person_type'], [1, 2, 3])) {
            $disabled_player_numbers = M('match_event_entrys mee')
                    ->field('me.disabled_event_numbers')
                    ->join($prefix . 'match_event_entry_player meep on meep.event_entry_id = mee.id')
                    ->join($prefix . 'match_events me on me.id = mee.event_id')
                    ->where([
                        'meep.player_id' => ['in', $matchEventEntryData['player']],
                        'mee.match_id'   => $match_id,
                        'mee.status'      => [in, [0, 1]]
                    ])
                    ->select();
            foreach ($disabled_player_numbers as $disabled_player_number) {
                if (empty($disabled_player_number['disabled_event_numbers'])) {
                    continue;
                }

                $disabled_player_number_arr = explode(',', $disabled_player_number['disabled_event_numbers']);

                foreach ($disabled_player_number_arr as $disabled_player_number_one) {
                    $disabled_numbers[] = $disabled_player_number_one;
                }
            }
        }

        // A.5.2 当前报名的组别ID对应的数据
        $disabled_event_numbers_rs = M('match_events')->field('event_number, disabled_event_numbers')->where(['id' => ['in', $event_ids]])->select();
        // A.5.3 累加当前报名的组别所禁用的组别代码
        foreach ($disabled_event_numbers_rs as $disabled_event_numbers_one) {
            if (empty($disabled_event_numbers_one['disabled_event_numbers'])) {
                continue;
            }

            $disabled_event_numbers_one_arr = explode(',', $disabled_event_numbers_one['disabled_event_numbers']);

            foreach ($disabled_event_numbers_one_arr as $disabled_event_numbers_one_arr_one) {
                $disabled_numbers[] = $disabled_event_numbers_one_arr_one;
            }
        }
        unset($disabled_event_numbers_one);

        // A.5.4 禁报去重
        $disabled_numbers = array_unique($disabled_numbers);

        // A.5.5 判定当前选中的组别是否含有禁报
        foreach ($disabled_event_numbers_rs as $disabled_event_numbers_one) {
            if (in_array($disabled_event_numbers_one['event_number'], $disabled_numbers)) {
                $this->ajaxReturn(['status' => false, 'msg' => '违规报名，请看规程']);
            }
        }

        // B. 写入数据
        $dbTrans = D();
        $dbTrans->startTrans();

        // 循环处理
        foreach ($event_ids as $event_id) {
            // B.Before
            // B.Before.1 查询组别信息
            $event_map = [];
            if ($match['stage'] == 3) {
                $event_map['status'] = 1;
            } elseif ($match['stage'] == 1) {
                $event_map['status'] = ['in', [0, 1]];
            }
            if (! $event_info = M('match_events')->where(['id' => $event_id])->where($event_map)->find()) {
                $dbTrans->rollback();
                $this->ajaxReturn(['status' => false, 'msg' => '数据异常或组别状态不可报名']);
            }

            // B.0 预置数据
            $player_ids = [];

            // B.1 写入组别报名表
            $match_event_entry_data = [
                'match_entry_id' => $match_entry['id'],
                'match_id'       => $match['id'],
                'event_id'       => $event_id,
                'uid'            => $uid,
                'created_at'     => time(),
                'updated_at'     => time()
            ];

            // B.1.1 追加数据
            if (/*$match['stage'] == 3 && */$event_info['status'] == 1) {
                $match_event_entry_data = array_merge($match_event_entry_data, [
                    'status'     => 1,
                    'audit_time' => time(),
                    'end_time'   => time(),
                ]); 
            }

            if (! $event_entry_id = M('match_event_entrys')->add($match_event_entry_data)) {
                $dbTrans->rollback();
                $this->ajaxReturn(['status' => false, 'msg' => '服务器异常，请稍后再试']);
            }

            // B.2 如果是集体舞，需创建一个player数据
            if ($matchEventEntryData['person_type'] == 4) {
                // B.2.1 新增player表
                $group_idcard      = generateGroupIdcard();  // 团体的身份证标识
                $group_player_data = [
                    'type'       => 1,
                    'name'       => $matchEventEntryData['player_group_name'],
                    'idcard'     => $group_idcard,
                    'sex'        => 0,
                    'birthday'   => date('Y-m-d', time()),
                    'created_at' => time(),
                    'updated_at' => time()
                ];
                if (! $group_player_id = M('players')->add($group_player_data)) {
                    $dbTrans->rollback();
                    $this->ajaxReturn(['status' => false, 'msg' => '服务器异常，请稍后再试3']);
                }

                // B.2.2 新增用户和player关联表
                $user_group_player_data = [
                    'uid'        => $uid,
                    'player_id'  => $group_player_id,
                    'is_self'    => 0,
                    'created_at' => time(),
                    'updated_at' => time()
                ];

                if (! M('user_players')->add($user_group_player_data)) {
                    $dbTrans->rollback();
                    $this->ajaxReturn(['status' => false, 'msg' => '服务器异常，请稍后再试2']);
                }

                // B.2.3 此次报名的选手ID
                $player_ids = [$group_player_id];
            }

            // B.3 个人、双人、集体
            if (in_array($matchEventEntryData['person_type'], [1, 2, 3])) {
                $player_ids = $matchEventEntryData['player'];
            }

            // B.4 关联组别ID和选手ID
            // B.4.1 校验
            if (! is_array($player_ids) || count($player_ids) == 0) {
                $dbTrans->rollback();
                $this->ajaxReturn(['status' => false, 'msg' => '数据异常']);
            }
            // B.4.2 写入关联表
            $match_event_entry_player_data = [];
            foreach ($player_ids as $player_id) {
                $match_event_entry_player_data[] = [
                    'player_id'      => $player_id,
                    'event_entry_id' => $event_entry_id,
                ];
            }
            if (! M('match_event_entry_player')->addAll($match_event_entry_player_data)) {
                $dbTrans->rollback();
                $this->ajaxReturn(['status' => false, 'msg' => '服务器异常，请稍后再试1']);
            }
        }
        
        $dbTrans->commit();

        // C. 清理session
        session('match:event:entry', null);

        $this->ajaxReturn(['status' => true, 'msg' => '提交成功', 'url' => U('signup', ['match_id' => $match_id])]);
    }

    /**
     * 已经报名的信息
     * @return boolean [description]
     */
    public function hasSign()
    {
        $prefix   = C('DB_PREFIX');
        $user     = $this->_getUser();
        $match_id = I('get.match_id', 0, 'intval');
        $uid      = $user['id'];

        // A. 校验赛事信息
        $this->_ckMatchEntry($match_id, $match, $match_entry);

        // B. 获取报名的信息
        $match_event_entrys = M('match_event_entrys mee')
            ->field([
                'me.event_number, me.event_title, me.dances event_dances, me.fee event_fee',
                'mee.id event_entry_id, mee.status'
            ])
            ->join($prefix . 'match_events me on mee.event_id = me.id')
            ->where([
                'mee.uid'     => $uid,
                'me.match_id' => $match_id
            ])
            ->order('mee.created_at asc')
            ->select();

        // B.1 获取报名的其他信息
        foreach ($match_event_entrys as &$match_event_entry) {
            // B.1.1 获取选手信息
            $match_event_entry['players'] = M('match_event_entry_player meep')
                ->field([
                    'p.name'
                ])
                ->join($prefix . 'players p on p.id = meep.player_id')
                ->where([
                    'meep.event_entry_id' => $match_event_entry['event_entry_id']
                ])
                ->order('FIELD(p.sex, 1 , 2 , 0), p.birthday asc, p.id asc')
                ->select();

            // B.1.2 获取选手背号(按男→女；年纪大→小排序)(仅支付成功才显示)
            if ($match_entry['pay_status'] == 1) {
                $match_event_entry['back_numbers'] = M('match_event_entrys mee')
                    ->field('mpb.back_number')
                    ->join($prefix . 'match_event_entry_player meep on mee.id = meep.event_entry_id')
                    ->join($prefix . 'players p on meep.player_id = p.id')
                    ->join($prefix . 'match_player_backs mpb on mpb.player_id = meep.player_id and mpb.match_id = mee.match_id')
                    ->where([
                        'mee.id' => $match_event_entry['event_entry_id'],
                        'status' => 1,
                    ])
                    ->order('FIELD(p.sex, 1 , 2 , 0), p.birthday asc, p.id asc')
                    ->select();
            }
        }

        // C. 获取汇总信息
        // C.1 获取参赛总人数
        $player_total_rs = M('match_event_entrys mee')
            ->field('meep.player_id')
            ->distinct(true)
            ->join($prefix . 'match_event_entry_player meep on mee.id = meep.event_entry_id')
            ->where([
                'mee.uid'      => $uid,
                'mee.match_id' => $match_id,
                'mee.status'   => ['in', [0, 1]]
            ])
            ->select();
        $player_total = count($player_total_rs);
        // C.2 获取参赛组别数量
        $event_entry_total = M('match_event_entrys')
            ->where([
                'uid'      => $uid,
                'match_id' => $match_id,
                'status'   => ['in', [0, 1]]
            ])
            ->count();
        // C.3 参赛费用
        // C.3.1 如果赛事为付款阶段，则为确认的总费用为准 // （旧版本）如果组别均确认，则以确认后的费用为准
        if ($match['stage'] == 4) {
            // $macth_amount_total = $match_entry['amount'];
            $macth_amount_total = M('match_event_entrys mee')
                ->join($prefix . 'match_events me on mee.event_id = me.id')
                ->where([
                    'mee.uid'     => $uid,
                    'me.match_id' => $match_id,
                    'mee.status'  => 1
                ])
                ->sum('me.fee');
        }
        // C.3.2 否则以当前所有待审核和通过审核的总和为准
        else {
            $macth_amount_total = M('match_event_entrys mee')
                ->join($prefix . 'match_events me on mee.event_id = me.id')
                ->where([
                    'mee.uid'     => $uid,
                    'me.match_id' => $match_id,
                    'mee.status'  => ['in', [0, 1]]
                ])
                ->sum('me.fee');
        }

        $this->match              = $match;
        $this->match_entry        = $match_entry;
        $this->match_event_entrys = $match_event_entrys;
        $this->player_total       = $player_total;
        $this->event_entry_total  = $event_entry_total;
        $this->macth_amount_total = $macth_amount_total;
        $this->seotitle = '已报名管理';
        $this->display();
    }

    /**
     * 代表队管理
     */
    public function team()
    {
        $match_id = I('get.match_id', 0, 'intval');

        // 校验赛事信息
        $this->_ckMatchEntry($match_id, $match, $match_entry);

        $this->match_entry = $match_entry;
        $this->seotitle    = '代表队管理';
        $this->display();
    }

    /**
     * 取消组别
     * @return [type] [description]
     */
    public function cancal_event_entry()
    {
        $event_entry_id = I('post.event_entry_id', 0, 'intval');

        $dbTrans = D();
        $dbTrans->startTrans();

        // A. 获取组别报名信息
        if (empty($event_entry_id) ||
            ! $event_entry_info = M('match_event_entrys')->where(['id' => $event_entry_id, 'status' => ['in', [0, 1]]])->find()
        ) {
            $dbTrans->rollback();
            $this->ajaxReturn(['status' => false, 'msg' => '报名信息不存在或状态不符']);
        }

        // B. 校验赛事信息
        $this->_ckMatchEntry($event_entry_info['match_id'], $match, $match_entry);

        // B.1 赛事阶段
        if (! in_array($match['stage'], [1, 3])) {
            $dbTrans->rollback();
            $this->ajaxReturn(['status' => false, 'msg' => '当前阶段不可取消']);
        }

        // C. 更新取消状态
        $match_event_entry_data = [
            'status'     => 2,
            'end_time'   => time(),
            'updated_at' => time()
        ];
        if (false === M('match_event_entrys')->where(['id' => $event_entry_id, 'status' => ['in', [0, 1]]])->save($match_event_entry_data)) {
            $dbTrans->rollback();
            $this->ajaxReturn(['status' => false, 'msg' => '当前阶段不可取消']);
        }

        $dbTrans->commit();

        $this->ajaxReturn(['status' => true, 'msg' => '操作成功']);
    }

    /**
     * 代表队管理 - 更新
     * @return [type] [description]
     */
    public function team_update()
    {
        $user           = $this->_getUser();
        $match_entry_id = I('post.match_entry_id', 0, 'intval');
        $team_name      = I('post.team_name', '', 'trim');
        $team_teacher   = I('post.team_teacher', '', 'trim');
        $team_contact   = I('post.team_contact', '', 'trim');
        $team_qq        = I('post.team_qq', '', 'trim');
        $uid            = $user['id'];

        // A. 校验
        // A.1 报名信息
        if (! $match_entry = M('match_entrys')->where(['uid' => $uid, 'id' => $match_entry_id])->find()) {
            $this->ajaxReturn(['status' => false, 'msg' => '数据异常']);
        }

        // A.2 代表队名称
        if (empty($team_name)) {
            $this->ajaxReturn(['status' => false, 'msg' => '请输入代表队名称']);
        }

        // A.3 带队老师
        if (empty($team_teacher)) {
            $this->ajaxReturn(['status' => false, 'msg' => '请输入带队老师']);
        }

        // A.4 联系方式
        if (empty($team_contact)) {
            $this->ajaxReturn(['status' => false, 'msg' => '请输入联系方式']);
        }

        // B. 更新相关信息
        $dbTrans = D();
        $dbTrans->startTrans();

        // B.1 更新当前报名表信息
        $match_entry_data = [
            'team_name'    => $team_name,
            'team_teacher' => $team_teacher,
            'team_contact' => $team_contact,
            'team_qq'      => $team_qq,
            'updated_at'   => time(),
        ];
        if (false === M('match_entrys')->where(['uid' => $uid, 'id' => $match_entry_id])->save($match_entry_data)) {
            $dbTrans->rollback();
            $this->ajaxReturn(['status' => false, 'msg' => '服务器异常，请稍后再试']);
        }

        // B.2 更新用户表的带队信息
        $user_data = [
            'team_name'    => $team_name,
            'team_teacher' => $team_teacher,
            'team_contact' => $team_contact,
            'team_qq'      => $team_qq,
            'updated_at'   => time(),
        ];
        if (false === M('users')->where(['id' => $uid])->save($user_data)) {
            $dbTrans->rollback();
            $this->ajaxReturn(['status' => false, 'msg' => '服务器异常，请稍后再试']);
        }

        $dbTrans->commit();

        $this->ajaxReturn(['status' => true, 'msg' => '报名成功']);
    }

    /**
     * 标记为已转账
     * @return [type] [description]
     */
    public function offlinePaymentCheck()
    {
        $user           = $this->_getUser();
        $match_entry_id = I('post.match_entry_id', 0, 'intval');
        $uid            = $user['id'];
        $prefix         = C('DB_PREFIX');

        // A. 校验
        // A.1 报名信息
        if (! $match_entry = M('match_entrys')->where(['uid' => $uid, 'id' => $match_entry_id])->find()) {
            $this->ajaxReturn(['status' => false, 'msg' => '数据异常']);
        }

        // A.2 赛程信息
        if (! $match = M('matchs')->where(['id' => $match_entry['match_id']])->find()) {
            $this->ajaxReturn(['status' => false, 'msg' => '赛事信息不合法']);
        }

        // A.3 赛程支付状态
        if ($match['stage'] != 4) {
            $this->ajaxReturn(['status' => false, 'msg' => '当前状态还不可支付']);
        }

        // A.3.after 判断当前赛事是否有待定状态
        if (M('match_event_entrys')->where(['status' => 0, 'uid' => $uid, 'match_id' => $match['id']])->count() > 0) {
            $this->ajaxReturn(['status' => false, 'msg' => '还有未确定的组别，无法标记为已支付']);
        }

        // A.4 判定是否已经支付
        if ($match_entry['pay_status'] == 1) {
            $this->ajaxReturn(['status' => false, 'msg' => '当前报名已经支付，无需重复支付']);
        }

        // A.5 判定是否为可变更为已转账的
        if (! in_array($match_entry['transfer_status'], [0, 2])) {
            $this->ajaxReturn(['status' => false, 'msg' => '当前状态不可标记为已汇款']);
        }

        // B. 更新
        // B.1 获取总额
        $amount = M('match_event_entrys mee')
                ->join($prefix . 'match_events me on mee.event_id = me.id')
                ->where([
                    'mee.uid'     => $uid,
                    'me.match_id' => $match['id'],
                    'mee.status'  => 1
                ])
                ->sum('me.fee');

        // B.2 更新相关数据
        $data = [
            'amount'          => $amount,
            'transfer_status' => 3,
            'transfer_time'   => time(),
            'updated_at'      => time()
        ];
        if (false === M('match_entrys')
                ->where([
                    'uid'             => $uid, 
                    'id'              => $match_entry_id, 
                    'transfer_status' => ['in',[0, 2]]
                ])
                ->save($data)
        ) {
            $this->ajaxReturn(['status' => false, 'msg' => '服务器异常，请稍后再试']);
        }

        $this->ajaxReturn(['status' => true, 'msg' => 'success']);
    }
}