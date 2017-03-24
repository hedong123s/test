<?php
/**
 * 后台用户及权限
 * @author Flc <2015-12-09 15:06:13>
 * @example
 *     use Libs\AdminAuth as Auth;
 *     
 *     Auth::info($id); //获取用户信息（若id为空，则获取当前登录用户的用户信息）
 *     Auth::check();     // 判断当前是否登录；已经登录，则返回id；否则：false
 *     Auth::login($id); // 用户登录，生成session
 *     Auth::logout();    // 退出当前登录（清除session）
 *     Auth::logined_id(); // 获取已登录用户的id（不建议使用,建议使用：Auth::info()['id']获取登录用户的id）
 *     Auth::roles($id); //获取用户管理组（若id为空，则获取当前登录用户的用户信息）（后台专用）
 *     Auth::rules($id);  //获取用户可用权限（若id为空，则获取当前登录用户的用户信息）（后台专用）
 *     Auth::allRules();  // 获取所有预置且可用的权限
 *     Auth::verifyRule($rule);  // 验证权限，默认为当前登录用户
 *      
 */
namespace Common\Service;

use Think\Crypt;

class AdminAuth{

    /**
     * 生成session的key
     * @var string
     */
    private static $sessionKey;

    /**
     * 用户加密密钥
     * @var string
     */
    private static $authKey;

    /**
     * 是否已初始化
     * @var boolean
     */
    private static $handler = false;

    /**
     * 初始化
     */
    public static function init(){
        self::$sessionKey = 'login_' . md5('admin');
        self::$authKey    = C('AUTH_USER_KEY');

        self::$handler = true;  // 标记为初始化成功
    }

    /**
     * 获取用户信息
     * @param  string  $id id，为空，则获取当前登录用户的用户信息
     * @param  boolean $refresh   是否重新刷新，false为不刷新直接读取静态变量；true重新获取
     * @return array|false       
     */
    public static function info($id = '', $refresh = false){
        !self::$handler && self::init();

        // 设置静态变量
        static $admins = array();

        // 如为设置id，则获取当前登录的用户id
        if (empty($id)) {
            if (!$id = self::logined_id()) return false;
        }

        // 读取静态变量（不刷新），如果存在，则直接返回
        if (!$refresh && array_key_exists($id, $admins)) {
            return $admins[$id];
        }

        // 获取用户信息
        $admins[$id] = M('admins')->where(array('id' => $id))->find();
        return $admins[$id];
    }

    /**
     * 判断用户是否登录 
     * @return string|false 如登录，则返回id，否则false
     */
    public static function check(){
        !self::$handler && self::init(); // 初始化

        if (!$sArrs = session(self::$sessionKey)) {
            return false;
        }
        if (!array_key_exists('id', $sArrs) ||
            !array_key_exists('token', $sArrs)) {
            return false;   
        }

        // 获取id
        Crypt::init();
        $id = Crypt::decrypt($sArrs['id'], self::$authKey);

        // 设置静态变量
        static $admins = array();

        // 读取数据
        if (array_key_exists($id, $admins)) {
            $admin = $admins[$id];
        } else {
            $admin = $admins[$id] = M('admins')->where(array('status' => 1, 'id' => $id))->find();
        }
        if (!$admin) {
            self::logout(); //登出
            return false;
        }

        // 校验token
        $tokenStr       = $admin['id'] . $admin['password'];
        $verifyTokenStr = Crypt::decrypt($sArrs['token'], self::$authKey); //待校验token
        if (!$verifyTokenStr || $verifyTokenStr != $tokenStr) {
            self::logout(); //登出
            return false;
        }

        return $admin['id'];
    }

    /**
     * 用户登录
     * @param  string $id id
     * @return boolean       
     */
    public static function login($id){
        !self::$handler && self::init(); // 初始化

        if (empty($id)) return false;

        // 读取用户信息
        $admin = self::info($id);
        if (!$admin) return false;

        // 生成session
        Crypt::init();
        $tokenStr = $admin['id'] . $admin['password'];
        $sArrs    = array(
            'id'        => Crypt::encrypt($admin['id'], self::$authKey),
            'token'     => Crypt::encrypt($tokenStr, self::$authKey),
            'timestamp' => time()
        );

        session(self::$sessionKey, $sArrs);
    }

    /**
     * 登出
     */
    public static function logout(){
        !self::$handler && self::init(); // 初始化

        session(self::$sessionKey, null);
    }

    /**
     * 获取登录用户的id
     */
    public static function logined_id(){
        static $id = null;
        if ($id == null) {
            $id = self::check();
        }
        return $id;
    }

    /**
     * 获取用户所属管理组（后台专用）
     * @param  string  $id    id（若为空，则获取当前登录用户的管理组）
     * @param  boolean $refresh 是否刷新
     * @return array|false
     */
    public static function roles($id = '', $refresh = false){
        // 设置静态变量
        static $rolesS = array();

        // 如为设置id，则获取当前登录的用户id
        if (empty($id)) {
            if (!$id = self::logined_id()) return false;
        }

        // 读取静态变量（不刷新），如果存在，则直接返回
        if (!$refresh && array_key_exists($id, $rolesS)) {
            return $rolesS[$id];
        }

        $pre = C('DB_PREFIX'); // 表前缀
        $rolesS[$id] =  M('admin_roles r')
            ->field('r.*')
            ->cache(true, 60)
            ->join($pre . 'admins_roles ur on ur.role_id = r.id')
            ->join($pre . 'admins u on u.id = ur.admin_id')
            ->where(array('u.id' => $id))
            ->select();

        return $rolesS[$id];
    }

    /**
     * 获取用户可用管理权限（后台专用）
     * @param  string  $id    id（若为空，则获取当前登录用户的用户权限）
     * @param  boolean $refresh 是否刷新
     * @return array|false      已经处理重复清除
     */
    public static function rules($id, $refresh = false){
        // 设置静态变量
        static $rulesS = array();

        // 如为设置id，则获取当前登录的用户id
        if (empty($id)) {
            if (!$id = self::logined_id()) return false;
        }

        // 读取静态变量（不刷新），如果存在，则直接返回
        if (!$refresh && array_key_exists($id, $rulesS)) {
            return $rulesS[$id];
        }

        $pre = C('DB_PREFIX'); // 表前缀
        $rulesS[$id] =  M('admin_rules ru')
            ->field('ru.*')
            ->cache(true, 60)
            ->distinct(true)
            ->join($pre . 'admin_roles_rules rr on rr.rule_id = ru.id')
            ->join($pre . 'admins_roles ur on ur.role_id = rr.role_id')
            ->join($pre . 'admins us on us.id = ur.admin_id')
            ->where(array('us.id' => $id, 'ru.status' => 1))
            ->select();

        return $rulesS[$id];
    }

    /**
     * 获取所有可用的权限规则
     * @param  boolean $refresh 是否刷新
     * @return array|false 
     */
    public static function allRules($refresh = false){
        // 设置静态变量
        static $allRules = null;

        // 读取静态变量（不刷新），如果存在，则直接返回
        if (!$refresh && $allRules != null) {
            return $allRules;
        }

        $allRules = M('admin_rules')->where(array('status' => 1))->select();
        return $allRules;
    }

    /**
     * 权限校验
     * @param  string $rule 权限规则(如果不存在预置的权限规则，则直接通过，可用)
     * @param  string $id id（若为空，则获取当前登录用户的用户权限）
     * @return boolean
     */
    public static function verifyRule($rule, $id = ''){
        !self::$handler && self::init();

        // A. 获取所有权限
        $allRules = self::allRules();
        // A.1 转换为小写
        $allRulesTmp = array();
        foreach($allRules as $v){
            $allRulesTmp[] = strtolower($v['value']);
        }
        $allRules = $allRulesTmp;

        // B. 待校验的规则
        if (!is_string($rule)) {
            return false;
        }
        $rule = strtolower($rule);  //转换为小写

        // C. 判断当前校验的权限是否需要权限(如果不存在预置的权限规则，则直接通过，可用)
        if (!in_array($rule, $allRules)) {
            return true;
        }

        // D. 获取该用户所有权限
        // D.1 获取该用户所有权限规则
        $userRules = self::rules($id); // 如果id为空，则获取当前登录用户

        // D.2 转换为小写, 并获取权限规则
        $userRulesTmp = array();
        foreach($userRules as $v){
            $userRulesTmp[] = strtolower($v['value']);
        }
        $userRules = $userRulesTmp;

        // E. 如果在预置的权限规则里。则校验当前用户是否含有改权限
        if (in_array($rule, $userRules)) {
            return true;
        }

        return false;
    }
}