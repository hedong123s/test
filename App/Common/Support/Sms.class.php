<?php
/**
 * 短信发送接口
 *
 * @author Flc <2016-12-07 10:06:25>
 * @example  
 *      use Common\Support\Sms;
 *      Sms::send('模板ID', '手机号', 数组格式的参数);
 *
 *      返回参数：http://www.id98.cn/doc/sms
 */
namespace Common\Support;

class Sms
{
    /**
     * API接口地址
     * @var string
     */
    protected $api_uri = 'http://api.id98.cn/api/sms';

    /**
     * apiKey
     * @var string
     */
    protected $api_key = '8e3067c0140e3d3cc6ad64abd4becfeb';

    /**
     * 短信模板ID
     * @var string
     */
    protected $templateid;

    /**
     * 手机号
     * @var string
     */
    protected $phone;

    /**
     * 变量数据
     * @var array
     */
    protected $param = [];

    /**
     * 初始化
     */
    public function __construct($templateid, $phone, $param = [])
    {
        $this->templateid = $templateid;
        $this->phone      = $phone;
        $this->param      = $param;
    }

    /**
     * 短信发送
     * @param  string $templateid 模板ID
     * @param  string $phone      手机号
     * @param  array  $param      参数
     * @return array             
     */
    public static function send($templateid, $phone, $param = [])
    {
        $idcard = new self($templateid, $phone, $param);

        return call_user_func_array([$idcard, 'sendSms'], []);
    }

    /**
     * 发送短信
     * @return array 
     */
    public function sendSms()
    {
        try {
            $res = Curl::post($this->api_uri, [
                'appkey'     => $this->api_key,
                'templateid' => $this->templateid,
                'phone'      => $this->phone,
                'param'      => implode(',', $this->param),
                'output'     => 'json'
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
