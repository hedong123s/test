<?php
namespace Home\Controller;

use Think\Page;
use Think\Model;
use Common\Support\Sign;
use Common\Support\Excel;

/**
 * 报名的赛事数据导出控制器
 */
class MatchEntryFileController extends CommonController
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
	 * 下载报名表
	 */
	public function event_entry()
    {
        $prefix   = C('DB_PREFIX');
        $user     = $this->_getUser();
        $uid      = $user['id'];
        $match_id = I('get.match_id', 0, 'intval');

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
                'me.match_id' => $match_id,
                'mee.status'  => ['in', [0, 1]]
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
                ->select();

            // B.1.2 获取选手背号(按男→女；年纪大→小排序)(仅支付成功才显示)
            // if ($match_entry['pay_status'] == 1) {
            //     $match_event_entry['back_numbers'] = M('match_event_entrys mee')
            //         ->field('mpb.back_number')
            //         ->join($prefix . 'match_event_entry_player meep on mee.id = meep.event_entry_id')
            //         ->join($prefix . 'players p on meep.player_id = p.id')
            //         ->join($prefix . 'match_player_backs mpb on mpb.player_id = meep.player_id and mpb.match_id = mee.match_id')
            //         ->where([
            //             'mee.id' => $match_event_entry['event_entry_id'],
            //             'status' => 1,
            //         ])
            //         ->order('FIELD(p.sex, 1 , 2 , 0), p.birthday desc')
            //         ->select();
            // }
        }
        unset($match_event_entry);
        

        // C. 导出数据
        $excel = new Excel;

        // C.1 标题
        $title = ['选手', '组别代码', '比赛组别', '竞赛舞种', '服务费(元)', '代表队'];

        // C.2 导出的数据
        $export_data = [];
        foreach ($match_event_entrys as $match_event_entry) {
            // C.2.1 选手信息
            $players = '';
            foreach ($match_event_entry['players'] as $player) {
                if (empty($players)) {
                    $players = $player['name'];
                } else {
                    $players .= '、' . $player['name'];
                }
            }

            // C.2.2 拼合数据
            $export_data[] = [
                $players,
                $match_event_entry['event_number'],
                $match_event_entry['event_title'],
                $match_event_entry['event_dances'],
                decimal_number($match_event_entry['event_fee']),
                $match_entry['team_name']
            ];
        }

        // 导出文件
        $excel->setTitle($title)->setData($export_data)->render('报名表.xlsx');
	}

    /**
     * 下载已缴费报名表
     */
    public function fee_entry(){
        $prefix   = C('DB_PREFIX');
        $user     = $this->_getUser();
        $uid      = $user['id'];
        $match_id = I('get.match_id', 0, 'intval');

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
                'me.match_id' => $match_id,
                'mee.status'  => ['in', [ 1]]
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
                ->select(); 


        }
        unset($match_event_entry);
        

        // C. 导出数据
        $excel = new Excel;

        // C.1 标题
        $title = ['序号','选手', '背号','组别代码', '比赛组别', '竞赛舞种', '服务费(元)', '代表队','报名费','审核'];

        // C.2 导出的数据
        $export_data = [];
        foreach ($match_event_entrys as $k=>$match_event_entry) {
            // C.2.1 选手信息
            $players = '';
            foreach ($match_event_entry['players'] as $player) {
                if (empty($players)) {
                    $players = $player['name'];
                } else {
                    $players .= '、' . $player['name'];
                }
            }

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

            // C.2.2 拼合数据
            $export_data[] = [
                $k,
                $players,
                $match_event_entry['back_numbers'][0]['back_number'],
                $match_event_entry['event_number'],
                $match_event_entry['event_title'],
                $match_event_entry['event_dances'],
                decimal_number($match_event_entry['event_fee']),
                $match_entry['team_name'],
                $match_event_entry['event_fee'],
                '确认'
            ];
        }

        // 导出文件
        $excel->setTitle($title)->setData($export_data)->render('已缴费报名表.xlsx');
    }

    /**
     * 下载成绩表
     */
    public function score_entry(){
        $prefix   = C('DB_PREFIX');
        $user     = $this->_getUser();
        $uid      = $user['id'];
        $match_id = I('get.match_id', 0, 'intval');

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
                'me.match_id' => $match_id,
                'mee.status'  => ['in', [ 1]]
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
                ->select(); 


        }
        unset($match_event_entry);
        

        // C. 导出数据
        $excel = new Excel;

        // C.1 标题
        $title = ['序号','选手', '背号','组别代码', '比赛组别', '竞赛舞种', '服务费(元)', '代表队','报名费','审核','场序、场区','成绩'];

        // C.2 导出的数据
        $export_data = [];
        foreach ($match_event_entrys as $k=>$match_event_entry) {
            // C.2.1 选手信息
            $players = '';
            foreach ($match_event_entry['players'] as $player) {
                if (empty($players)) {
                    $players = $player['name'];
                } else {
                    $players .= '、' . $player['name'];
                }
            }

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

            // C.2.2 拼合数据
            $export_data[] = [
                $k,
                $players,
                $match_event_entry['back_numbers'][0]['back_number'],
                $match_event_entry['event_number'],
                $match_event_entry['event_title'],
                $match_event_entry['event_dances'],
                decimal_number($match_event_entry['event_fee']),
                $match_entry['team_name'],
                $match_event_entry['event_fee'],
                '确认',
                '',
                ''
            ];
        }

        // 导出文件
        $excel->setTitle($title)->setData($export_data)->render('下载成绩表.xlsx');
    }


}