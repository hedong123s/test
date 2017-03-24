<?php
/**
 * 生成比赛报名的选手背号
 * 
 * @author FLc <2017-01-06 22:13:54>
 * @example  
 *      使用方法：
 *      MatchPlayerBackNumberCreate::create($match_id, $uid);
 *      返回结果（成功）：
 *      Array
 *      (
 *          [status] => 1
 *          [msg] => 成功
 *          [data] => Array
 *              (
 *                  [player_back_number] => Array
 *                      (
 *                          [1365] => 1
 *                          [1366] => 2
 *                          [1355] => 3
 *                          [1362] => 4
 *                          [1363] => 5
 *                          // key为player_id;值为背号
 *                      )
 *      
 *              )
 *      
 *      )
 */
namespace Common\Service;

class MatchPlayerBackNumberCreate
{
    /**
     * 比赛ID
     * @var [type]
     */
    protected $match_id;

    /**
     * 归属用户
     * @var integer
     */
    protected $uid;

    /**
     * 初始化
     */
    public function __construct($match_id, $uid)
    {
        $this->match_id = $match_id;
        $this->uid      = $uid;
    }

    /**
     * 执行生成背号
     * @return [type] [description]
     */
    public function run()
    {
        $prefix = C('DB_PREFIX');

        $dbTrans = D();
        $dbTrans->startTrans();

        // A. 获取新的背号数据
        if (! $match = M('matchs')->lock(true)->where(['id' => $this->match_id])->find()) {
            $dbTrans->rollback();
            return ['status' => false, 'msg' => '数据异常'];
        }
        $next_back_number = $start_back_number = $match['next_back_number'];

        // B. 获取当前赛事当前用户报名的选手ID（不重复）
        $players = M('match_event_entrys mee')
            ->field('meep.player_id')
            ->distinct(true)
            ->join($prefix . 'match_event_entry_player meep on mee.id = meep.event_entry_id')
            ->where([
                'mee.uid'      => $this->uid,
                'mee.match_id' => $this->match_id,
                'mee.status'   => 1
            ])
            ->order('mee.created_at asc, meep.player_id asc')
            ->select();

        if (! $players) {
            $dbTrans->rollback();
            return ['status' => false, 'msg' => '选手信息异常'];
        }

        // C. 循环生成背号
        $match_player_back_data = [];
        $player_back_number     = [];
        foreach ($players as $player) {
            $match_player_back_data[] = [
                'match_id'    => $this->match_id,
                'player_id'   => $player['player_id'],
                'back_number' => $next_back_number,
                'created_at'  => time()
            ];

            // 回调的数据
            $player_back_number[$player['player_id']] = $next_back_number;

            $next_back_number ++;
        }
        if (count($match_player_back_data) > 0) {
            try {
                if (! M('match_player_backs')->addAll($match_player_back_data)) {
                    $dbTrans->rollback();
                    return ['status' => false, 'msg' => '数据提交异常'];
                }
            } catch (\Exception $e) {
                $dbTrans->rollback();
                return ['status' => false, 'msg' => '数据提交异常'];
            }
        }

        // D. 更新当前赛事下次的背号
        $match_data = [
            'next_back_number' => $next_back_number,
            'updated_at'       => time()
        ];
        if (false === M('matchs')->where(['id' => $this->match_id, 'next_back_number' => $start_back_number])->save($match_data)) {
            $dbTrans->rollback();
            return ['status' => false, 'msg' => '数据提交异常'];
        }

        $dbTrans->commit();
        
        return ['status' => true, 'msg' => '成功', 'data' => ['player_back_number' => $player_back_number]];
    }

    /**
     * 静态方法生成背号
     * @param  integer $match_id 赛事ID
     * @param  integer $uid      用户ID
     * @return array           
     */
    public static function create($match_id, $uid)
    {
        $create = new self($match_id, $uid);

        return call_user_func_array([$create, 'run'], []);
    }
}