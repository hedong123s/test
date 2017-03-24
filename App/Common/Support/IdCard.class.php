<?php
/**
 * 身份证实名认证接口
 *
 * @author Flc <2016-12-07 10:06:25>
 * @example  
 *      use Common\Support\IdCard;
 *      IdCard::check('吴温炎', '362322199102182212');
 *
 *      返回参数：http://www.id98.cn/doc/idcard
 */
namespace Common\Support;

class IdCard
{
    /**
     * API接口地址
     * @var string
     */
    protected $api_uri = 'http://api.id98.cn/api/idcard';

    /**
     * apiKey
     * @var string
     */
    protected $api_key = '8e3067c0140e3d3cc6ad64abd4becfeb';

    /**
     * 姓名
     * @var string
     */
    protected $name;

    /**
     * 身份证号码
     * @var string
     */
    protected $cardno;

    /**
     * 初始化
     */
    public function __construct($name, $cardno)
    {
        $this->name   = $name;
        $this->cardno = $cardno;
    }

    /**
     * 校验实名认证接口
     * @param  string $name   姓名
     * @param  string $cardno 身份证号
     * @return array         
     */
    public static function check($name, $cardno)
    {
        $idcard = new self($name, $cardno);

        return call_user_func_array([$idcard, 'checkIdCard'], []);
    }

    /**
     * 校验实名认证
     * @return array 
     */
    public function checkIdCard()
    {
        // 测试临时用
        /*return [
            'status' => true,
            'data'   => [
                'isok' => 1,
                'code' => 1,
                'data' => [
                    'err'      => 0,
                    'address'  => '江西省上饶地区广丰县',
                    'sex'      => 'M',
                    'birthday' => '1991-02-18'
                ]
            ]
        ];*/


        // 以下生产后在使用
        try {
            $res = Curl::post($this->api_uri, [
                'appkey' => $this->api_key,
                'name'   => $this->name,
                'cardno' => $this->cardno,
                'output' => 'json'
            ]);
        } catch (\Exception $e) {
            return [
                'status' => false,
                'msg'    => '请求超时'
            ];
        }
        
        // 转换为数组
        $resp = json_decode($res, true);

        if (! $resp || ! is_array($resp)) {
            return [
                'status' => false,
                'msg'    => '接口异常'
            ];
        }

        return [
            'status' => true,
            'data'   => $resp
        ];
    }
}
