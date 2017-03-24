<?php
/**
 * 公共函数库
 *
 * @author Flc <2016-08-22 10:47:00>
 */

require_once __DIR__ . '/define.php';  // 引入配置函数

if (! function_exists('str_limit')) {
    /**
     * 字符串截取
     *
     * @author Flc <2016-08-12 17:03:41>
     * @param  string  $value 字符串
     * @param  integer $limit 截取长度
     * @param  string  $end   结尾修饰
     * @return string
     */
    function str_limit($value, $limit = 100, $end = '...')
    {
        if (mb_strwidth($value, 'UTF-8') <= $limit) {
            return $value;
        }

        return rtrim(mb_strimwidth($value, 0, $limit, '', 'UTF-8')).$end;
    }
}


if (! function_exists('clear_html')) {
    /**
     * 完全过虑HTML结构
     *
     * @author Flc <2016-08-12 17:05:14>
     * @param  string $str 字符串
     * @return string
     */
    function clear_html($str){
        $str = strip_tags($str);
        //首先去掉头尾空格
        $str = trim($str);
        //接着去掉两个空格以上的
        $str = preg_replace('/\s(?=\s)/', '', $str);
        //最后将非空格替换为一个空格
        $str = preg_replace('/[\n\r\t]/', ' ', $str);
        $str = str_replace('&nbsp;','',$str);

        return trim($str);
    }
}

if (! function_exists('format_date')) {
    /**
     * 格式化时间戳
     *
     * @author Flc <2016-08-22 10:48:46>
     * @param  integer $timestamp 时间戳
     * @param  string  $format    格式
     * @return string
     */
    function format_date($timestamp, $format = 'Y-m-d H:i:s')
    {
        return date($format, $timestamp);
    }
}

if (! function_exists('decimal_number')) {
    /**
     * 数字格式化位数（四舍五入）
     * @param  number  $money  原数据
     * @param  integer $length 小数点截取长度，默认2位
     * @return number          格式化数据
     */
    function decimal_number($money, $length = 2){
        return sprintf("%01." . $length . "f", round($money, 2));
    }
}

if (! function_exists('get_site_uri')) {
    /**
     * 获取当前站点网址（含端口）
     *
     * @author Flc <2016-09-02 10:04:07>
     * @return string
     */
    function get_site_uri()
    {
        $protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
        return $protocal . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
    }
}

if (! function_exists('get_url')) {
    /**
     * 获取当前访问的地址
     *
     * @author Flc <2016-09-02 10:04:07>
     * @return string
     */
    function get_url()
    {
        $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
        $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
        $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
        $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : $path_info);
        return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
    }
}

if (! function_exists('uuid')) {
    /**
     * 生成唯一标识UUID
     *
     * @author Flc <2016-08-22 10:51:15>
     * @return strign
     */
    function uuid()
    {
        if (function_exists('com_create_guid')) {
            $uuid = com_create_guid();
        } else {
            mt_srand((double)microtime() * 10000);  //optional for php 4.2.0 and up.

            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);  // "-"
            $uuid   = chr(123)  // "{"
                    . substr($charid, 0, 8) . $hyphen
                    . substr($charid, 8, 4) . $hyphen
                    . substr($charid,12, 4) . $hyphen
                    . substr($charid,16, 4) . $hyphen
                    . substr($charid,20,12)
                    . chr(125);  // "}"
        }

        $uuid = trim($uuid, '{}');

        return $uuid;
    }
}

if (! function_exists('hash_make')) {
    /**
     * 哈希加密;用于密码加密
     * 要求：PHP>=5.5
     *
     * @author Flc <2016-08-22 10:51:15>
     * @return strign
     */
    function hash_make($value)
    {
        return \Common\Support\Hash::make($value);
    }
}

if (! function_exists('hash_check')) {
    /**
     * 哈希加密校验;用户密码校验
     * 要求：PHP>=5.5
     *
     * @author Flc <2016-08-23 11:26:26>
     * @param  string $value       明文密码
     * @param  string $hashedValue 密文密码
     * @return boolean
     */
    function hash_check($value, $hashedValue)
    {
        return \Common\Support\Hash::check($value, $hashedValue);
    }
}

if (! function_exists('is_mobile')) {
    /**
     * 验证是否为手机号
     *
     * @author Flc <2016-08-24 17:05:02>
     * @param string $str 手机号
     * @return boolean
     */
    function is_mobile($str){
        if(strlen($str) != 11) return false;
        if(!preg_match("/^(((13[0-9]{1})|(15[0-9]{1})|(17[0-9]{1})|(18[0-9]{1}))+\d{8})$/", $str)) return false;
        return true;
    }
}

if (! function_exists('is_email')) {
    /**
     * 验证是否为邮箱
     *
     * @author Flc <2016-08-24 17:05:55>
     * @param  string  $str 邮箱
     * @return boolean
     */
    function is_email($str){
        if(!preg_match("/^[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/", $str)) return false;
        return true;
    }
}

if (! function_exists('is_money')) {
    /**
     * 判断是否为金额(最多2为小数)
     *
     * @author Flc <2016-08-24 17:06:40>
     * @param  number  $str 数字
     * @return boolean
     */
    function is_money($str){
        if(!preg_match("/^\d{0,10}\.{0,1}(\d{1,2})$/", $str)) return false;
        return true;
    }
}


if (! function_exists('generateBirthday')){
    /**
     * 根据身份证生成生日日期
     * @param  [type] $card_no [description]
     * @return [type]          返回时间戳|null
     */
    function generateBirthday($card_no){
        $birth = null;
        if (strlen($card_no) == 18) {
            $birth = strtotime(substr($card_no, 6, 8));
        }elseif (strlen($card_no) == 15){
            $birth = strtotime('19'.substr($card_no, 6, 6));
        }
        return $birth;
    }
}


if (! function_exists('isChineseName')){
    function isChineseName($name){
        if (preg_match('/^([\xe4-\xe9][\x80-\xbf]{2}){2,4}$/', $name)){
            return true;
        } else {
            return false;
        }
    }
}

if (! function_exists('ageHandle')){
    /**
     * 年龄计算
     * @param  [type] $startDate 开始计算的时间戳
     * @param  [type] $endDate   默认就是到现在
     * @return [type]            [description]
     */
    function ageHandle($startDate, $endDate=''){
        $startDate = date_create(date('Y-m-d', $startDate));
        $endDate   = date_create(date('Y-m-d', empty($endDate) ? time() : $endDate));

        $interval = date_diff($startDate, $endDate);
        return $interval->format('%y');
    }
}

if (! function_exists('idcard_verify_number')){
    /**
     * 计算身份证最后一位校验码
     * @param  [type] $idcard_base [description]
     * @return [type]              [description]
     */
    function idcard_verify_number($idcard_base){
        if (strlen($idcard_base) != 17){ return false; }
        // 加权因子
        $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);

        // 校验码对应值
        $verify_number_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');

        $checksum = 0;
        for ($i = 0; $i < strlen($idcard_base); $i++){
            $checksum += substr($idcard_base, $i, 1) * $factor[$i];
        }

        $mod = strtoupper($checksum % 11);
        $verify_number = $verify_number_list[$mod];

        return $verify_number;
    }
}

if (! function_exists('idcard_15to18')) {
    /**
     * 将身份证从15位升级到18位
     * @param  [type] $idcard [description]
     * @return [type]         [description]
     */
    function idcard_15to18($idcard){
        $idcard = trim($idcard);
        $length = strlen($idcard);
        if ($length == 18){
            return $idcard;
        }elseif ($length != 15){
            return false;
        }else{
            // 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码
            if (array_search(substr($idcard, 12, 3), array('996', '997', '998', '999')) !== false){
                $idcard = substr($idcard, 0, 6) . '18'. substr($idcard, 6, 9);
            }else{
                $idcard = substr($idcard, 0, 6) . '19'. substr($idcard, 6, 9);
            }
        }
        $idcard = $idcard .idcard_verify_number($idcard);
        return $idcard;
    }
}


if (! function_exists('idcard_checksum18')){
    /**
     * 18位身份证号码验证
     * @param  [type] $idcard [description]
     * @return [type]         [description]
     */
    function idcard_checksum18($idcard){
        $idcard = trim($idcard);
        if (strlen($idcard) != 18){ return false; }
        $aCity = array(11 => "北京",12=>"天津",13=>"河北",14=>"山西",15=>"内蒙古",
        21=>"辽宁",22=>"吉林",23=>"黑龙江",
        31=>"上海",32=>"江苏",33=>"浙江",34=>"安徽",35=>"福建",36=>"江西",37=>"山东",
        41=>"河南",42=>"湖北",43=>"湖南",44=>"广东",45=>"广西",46=>"海南",
        50=>"重庆",51=>"四川",52=>"贵州",53=>"云南",54=>"西藏",
        61=>"陕西",62=>"甘肃",63=>"青海",64=>"宁夏",65=>"新疆",
        71=>"台湾",81=>"香港",82=>"澳门",
        91=>"国外");
        //非法地区
        if (!array_key_exists(substr($idcard,0,2),$aCity)) {
            return false;
        }
        //验证生日
        if (!checkdate(substr($idcard,10,2),substr($idcard,12,2),substr($idcard,6,4))) {
            return false;
        }
        $idcard_base = substr($idcard, 0, 17);
        if (idcard_verify_number($idcard_base) != strtoupper(substr($idcard, 17, 1))){
            return false;
        }else{
            return true;
        }
    }
}

if (! function_exists('idcard_check')) {
    /**
     * 身份证合法性校验（包含15或18位）
     * @return boolean 
     */
    function idcard_check($idcard)
    {
        $idcard = trim($idcard);

        if (strlen($idcard) == 15){
            $idcard = idcard_15to18($idcard);
        }

        return idcard_checksum18($idcard);
    }
}

if (! function_exists('idcard_sex')) {
    /**
     * 通过身份证号获取性别
     * @param  string $idcard 身份证号
     * @return integer|false  1为男；2为女；false为不合法身份证
     */
    function idcard_sex($idcard)
    {
        $idcard = trim($idcard);

        if (strlen($idcard) == 15){
            $idcard = idcard_15to18($idcard);
        }

        if (! idcard_checksum18($idcard))
            return false;

        $sex = (int) substr($idcard, 16, 1);

        return $sex % 2 === 0 ? 2 : 1;
    }
}

if (!function_exists('calcAge')){
    /**
     * 计算年龄
     * @param  [type] $time 出生日期时间戳
     * @return [type]       岁数
     */
    function calcAge($time){
        // $startDate = date_create(date('Y-m-d', $time));
        $startDate = date_create(date('Y', $time) . '-01-01');
        $endDate   = date_create(date('Y-m-d', time()));

        $interval = date_diff($startDate, $endDate);
        return $interval->format('%y');
    }
}

if (! function_exists('generateGroupIdcard')) {
    /**
     * 生成一个集体舞的选手用户的身份证（虚拟，非个人用户）
     * @return [type] [description]
     */
    function generateGroupIdcard()
    {
        $idcard = 'G' . date('YmdHis') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

        if (M('players')->where(['idcard' => $idcard])->find()) {
            return generateGroupIdcard();
        }

        return $idcard;
    }
}

if (! function_exists('getSexFromType')) {
    /**
     * 通过类型返回性别 0为未知；1为男；2为女
     * @param  integer $value type值
     * @return string        
     */
    function getSexFromType($value)
    {
        if ($value == 1) {
            return '男';
        } elseif ($value == 2) {
            return '女';
        } else {
            return '未知';
        }
    }
}

if (! function_exists('getMatchEventMapSexArr')) {
    function getMatchEventMapSexArr($sexs = [])
    {
        $sexs = array_values($sexs);
        $num  = count($sexs); // 获取参赛人数

        // 如果是单人
        if ($num == 1) {
            list($sex_first) = $sexs;
            return [
                '0',
                $sex_first
            ];
        }
        // 如果是双人
        elseif ($num == 2) {
            list($sex_first, $sex_second) = $sexs;

            $rs = [
                '0,0',
                '0,' . $sex_second,
                '0,' . $sex_first,
                $sex_first . ',0',
                $sex_second . ',0',
                $sex_first . ',' . $sex_second,
                $sex_second . ',' . $sex_first,
            ];

            return array_unique($rs);
        }
        // 如果是6人
        elseif ($num == 6) {
            $unique = getPermCombUnique($sexs);

            $result = $unique;

            return array_merge(getMatchEventMapSixSexReplaceNoLimitArr($unique), '0,0,0,0,0,0');
        } else {
            return false;
        }
    }
}

if (! function_exists('getMatchEventMapSixSexReplaceNoLimitArr')) {
    /**
     * 针对6人进行格式化性别
     * @param  array  $unique [description]
     * @return [type]         [description]
     */
    function getMatchEventMapSixSexReplaceNoLimitArr($unique = [])
    {
        $result = $unique;

        foreach ($unique as $string) {
            $strs = explode(',', $string);
            $num = count($strs);

            for ($i = 0; $i < $num; $i ++) {
                for ($j = $i; $j <= $num; $j ++) {
                    for ($m = $j; $m <= $num; $m ++) {
                        for ($n = $m; $n <= $num; $n ++) {
                            for ($x = $n; $x <= $num; $x ++) {
                                $sta = [];
                                foreach ($strs as $key => $str) {
                                    if ($key == $j || 
                                        $key == $i || 
                                        $key == $m || 
                                        $key == $n || 
                                        $key == $x
                                    ) {
                                        $sta[]= 0;
                                    } else {
                                        $sta[] = $str;
                                    }
                                }

                                $staStr = implode(',', $sta);

                                if (! in_array($staStr, $result)) {
                                    $result[] = $staStr;
                                }
                            }
                        }
                    }
                }                
            }

        }

        return array_values(array_unique($result));
    }
}

if (! function_exists('getPermCombUnique')) {
    /**
     * 去重数组返回的不同排序的数组数据
     * @param  array  $array 如[1, 2, 3]
     * @return array
     */
    function getPermCombUnique($array = [])
    {
        $res = getPermCombArr($array);

        if (is_array($res)) {
            $result = array_unique($res);
        } else {
            $result = array_unique([$res]);
        }

        return array_values($result);
    }
}

if (! function_exists('getPermCombArr')) {
    /**
     * 依靠数组返回不同排序的数组数据
     * @param  array  $array 如[1, 2, 3]
     * @return string|array        
     */
    function getPermCombArr($array = [])
    {
        $array = array_values($array);

        if (count($array) == 1)
            return end($array);

        $result = [];

        foreach ($array as $key => $value) {
            $tmpArray = $array;
            unset($tmpArray[$key]);

            $rs = getPermCombArr($tmpArray);

            if (is_array($rs)) {
                foreach ($rs as $v) { 
                    $tmp = '';
                    $tmp .= $value . ',' . $v;
                    $result[] = $tmp;
                }
            } else {
                $tmp = $value . ',' . $rs;
                $result[] = $tmp;
            }           
        }

        return $result;
    }
}

if (! function_exists('defineMatchEventEntryStatus')) {
    /**
     * 定义报名的赛事对应的报名组别的状态
     * @return [type] [description]
     */
    function defineMatchEventEntryStatus()
    {
        return [
            0 => '待定',
            1 => '通过',
            2 => '取消',
        ];
    }   
}

if (! function_exists('getMatchEventEntryStatus')) {
    /**
     * 获取报名的赛事对应的报名组别的状态
     * @return [type] [description]
     */
    function getMatchEventEntryStatus($value)
    {
        $config = defineMatchEventEntryStatus();

        if (array_key_exists($value, $config))
            return $config[$value];

        return false;
    }   
}

if (! function_exists('generatePayNumber')) {
    /**
     * 生成支付单号
     * @return string 
     */
    function generatePayNumber()
    {
        $pay_number = date('YmdHis') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

        if (M('payments')->where(['pay_number' => $pay_number])->find()) {
            return generatePayNumber();
        }

        return $pay_number;
    }
}

if (! function_exists('defineMatchStage')) {
    /**
     * 定义报名的赛事的阶段
     * @return [type] [description]
     */
    function defineMatchStage()
    {
        return [
            1 => '报名阶段',
            2 => '审核阶段',
            3 => '增报阶段',
            4 => '缴费阶段',
        ];
    }   
}

if (! function_exists('getMatchStage')) {
    /**
     * 获取报名的赛事的阶段
     * @return [type] [description]
     */
    function getMatchStage($value)
    {
        $config = defineMatchStage();

        if (array_key_exists($value, $config))
            return $config[$value];

        return false;
    }   
}

if (! function_exists('hide_idcard')) {
    /**
     * 身份证隐藏加星处理
     *
     * @author Flc <2017-1-25 13:14:29>
     * @param  string $value 身份证号
     * @return string        
     */
    function hide_idcard($value)
    {
        if (strlen($value) == 15) {
            return substr_replace($value, '******', 6, 6);
        } elseif (strlen($value) == 18) {
            return substr_replace($value, '**********', 6, 10);
        } else {
            return $value;
        }
    }
}

if (! function_exists('getPlayer')) {
    /**
     * 获取选手信息（个人）
     * @param  [type] $player_id [description]
     * @return [type]            [description]
     */
    function getPlayer($player_id)
    {
        static $result;

        if (isset($result[$player_id]))
            return $result[$player_id];

        return $result[$player_id] = M('players')->where(['id' => $player_id, 'type' => 0])->find();
    }
}