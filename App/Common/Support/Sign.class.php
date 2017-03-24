<?php
/**
 * 全局加密签名类
 *
 * @author Flc <2017-01-03 19:25:37>
 */
namespace Common\Support;

class Sign
{
    protected static $secret = '8e3001440e3d3cc1264abd4becfeb';

    /**
     * 生成签名
     * @param  array  $params 待前面的参数
     * @return string         
     */
    public static function generate($params = [])
    {
        ksort($params);

        $arr = [];

        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $value = 'Array';
            }
            
            $arr[] = $key . '=' . $value;
        }

        return md5(self::$secret . implode(',', $arr) . self::$secret);
    }

    /**
     * 签名校验
     * @param  array  $params 待校验的参数
     * @param  string $sign   待校验的签名
     * @return boolean         
     */
    public static function check($params = [], $sign)
    {
        if (array_key_exists('sign', $params))
            unset($params['sign']);

        $nSign = self::generate($params);

        return $nSign == $sign;
    }
}
