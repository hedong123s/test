<?php
namespace Home\Controller;

use Common\Support\IdCard;
use Common\Support\Sms;
use Faker\Factory as Faker;
use Common\Service\MatchPlayerBackNumberCreate;

/**
 * 首页控制器
 * 
 * @author Flc <2016-08-22 10:44:29>
 */
class TestController extends BaseController
{
    /**
     * 首页
     * @return [type] [description]
     */
    public function index()
    {
        $rs = IdCard::check('何冬', '421083198901102133');
        //$rs = Sms::send('1000', '18825277676', ['123456']);
        print_r($rs);
    }

    /**
     * 插入测试数据(身份证相关)
     * @link http://www.bangnishouji.com/idcard/201501/154142_2.html 
     * @link http://www.free73.com/ 
     * @return [type] [description]
     */
    public function insertPlayers()
    {
        if (! IS_CLI)
            exit;

        // --------------------
        $idcards = [
            ['伍宣展', '232723198306288953'],
            ['于越泽', '232723197505229636'],
            ['谢浩皛', '232723198901285132'],
            ['窦俊豪', '232723197408111775'],
            ['史熠彤', '232723197402133498'],
            ['秦天磊', '32070019710221489X'],
            ['鲁雅畅', '320700197903236992'],
        ];

        // 循环插入到数据
        foreach ($idcards as $idcard_info) {
            list($name, $idcard) = $idcard_info;

            $this->insertIdCard($name, $idcard, $msg);

            echo $msg;
        }

        echo '完成' . PHP_EOL;
    }

    /**
     * 插入测试数据(身份证相关)，五星门
     * @return [type] [description]
     */
    public function insertRandomNamePlayers()
    {
        if (! IS_CLI)
            exit;

        // --------------------
        $idcards = [
            '110101200404011114',
            '110101200404019052',
            '110101200404013136',
            '110101200404017233',
            '110101200404014059',
            '110101200404013830',
            '110101200404016759',
            '110101200404012897',
            '110101200404018957',
            '110101200404017516',
            '110101200307015690',
            '110101200307011817',
            '110101200307014057',
            '110101200307013652',
            '110101200307014436',
            '110101200307015121',
            '110101200307014962',
            '110101200307014348',
            '11010120030701652X',
            '110101200307016466',
            '110101200007017344',
            '11010120000701360X',
            '110101200007019905',
            '110101200007019024',
            '110101200007016384',
        ];

        // faker
        $faker = Faker::create('zh_CN');

        // 循环插入到数据
        foreach ($idcards as $idcard) {
            $name = $faker->name;

            $this->insertIdCard($name, $idcard, $msg);

            echo $msg;
        }

        echo '完成' . PHP_EOL;
    }

    /**
     * 插入单条
     * @param  [type] $name   [description]
     * @param  [type] $idcard [description]
     * @return [type]         [description]
     */
    protected function insertIdCard($name, $idcard, &$msg = '')
    {
        if (empty($name) || empty($idcard)) {
            $msg = '参数不合法' . PHP_EOL;
            return false;
        }

        // 判断身份证信息
        if (! idcard_check($idcard)) {
            $msg = '身份证信息不合法' . PHP_EOL;
            return false;
        }

        $dbTrans = D();
        $dbTrans->startTrans();

        try {
            $player_data = [
                'type'       => 0,
                'name'       => $name,
                'idcard'     => $idcard,
                'sex'        => idcard_sex($idcard),
                'birthday'   => date('Y-m-d', generateBirthday($idcard)),
                'created_at' => time(),
                'updated_at' => time()
            ];
            if (! $player_id = M('players')->add($player_data)) {
                $dbTrans->rollback();
                $msg = '数据异常' . PHP_EOL;
                return false;
            }

            $user_player_data = [
                'uid'        => 10,
                'player_id'  => $player_id,
                'is_self'    => 0,
                'created_at' => time(),
                'updated_at' => time()
            ];

            if (! M('user_players')->add($user_player_data)) {
                $dbTrans->rollback();
                $msg = '数据异常' . PHP_EOL;
                return false;
            }

            $dbTrans->commit();
        } catch (\Exception $e) {
            $dbTrans->rollback();
            $msg = '数据异常：可能身份证相同' . PHP_EOL;
            return false;
        }

        $msg = $name . ' - ' . $idcard . ' - 成功' . PHP_EOL;
        return true;
    }

    public function c()
    {
        $rs = MatchPlayerBackNumberCreate::create(4, 10);
        print_r($rs);
    }

    /**
     * 更新审核状态
     * @return [type] [description]
     */
    public function update_event_status()
    {
        $results = M('match_events')->select();

        foreach ($results as $row) {
            $data = [
                'status' => $row['status']
            ];

            if (in_array($row['status'], [1, 2])) {
                $data = array_merge($data, [
                    'audit_time' => time(),
                    'end_time'   => time()
                ]);
            }

            if (false === M('match_event_entrys')->where(['event_id' => $row['id'], 'status' => 0])->save($data)) {
                echo $row['id'] . ':::失败' . PHP_EOL;
                continue;
            }

            echo $row['id'] . ':::成功' . PHP_EOL;
        }
    }
}