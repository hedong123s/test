<?php
namespace Common\Support;

/**
 * 邮件类
 *
 * @author Flc <2016-08-23 09:31:03>
 * @example  
 *
 *     # 调用方法
 *     use \Common\Support\Mail;
 *     $mail = new Mail();
 *     或
 *     $mail = Mail::instance();
 *     
 *     ## 链式调用方法
 *     $mail->to('123123@qq.com')->to('234234@qq.com')->content('123123')->send();
 *     $mail->to('123123@qq.com')->connection('192.168.1.1')->account('123123', '123123')->to('234234@qq.com')->content('123123')->send();
 *
 *     ## 普通调用方法
 *     $mail->to('123123@qq.com');
 *     $mail->content('123123');
 *     $mail->send();
 *
 *
 *     # 支持方法的有：
 *         * 连接地址及端口: connection($host, $port = 25);
 *         * 连接用户名及密码: account($username, $password);
 *         * 发送者信息: from($from_email, $from_name = '');
 *         * 发送者邮箱设置: fromEmail($from_email);
 *         * 发送者称呼设置: fromName($from_name);
 *         * 接收者信息（可多次操作）: to($sendr_email, $sendr_name = '');
 *         * 邮件主题: subject($subject = '');
 *         * 邮件内容: content($content = '');
 *         * 发送邮件: send();
 *         * 返回出错内容：getError();
 */
class Mail
{
    //邮件配置项
    private $config = array(
        'charset'    => 'utf-8', //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
        'smtp_debug' => 0,       //关闭SMTP调试功能(1 = errors and messages; 2 = messages only)
        'smtp_auth'  => true,    //启用 SMTP 验证功能
        'smtp_host'  => '',      //SMTP 服务器
        'smtp_port'  => '',      //SMTP服务器的端口号
        'username'   => '',      //SMTP服务器用户名
        'password'   => '',      //SMTP服务器密码
        'from_email' => '',      //发送者邮箱,一般与用户名一样
        'from_name'  => '',      //发送者称呼
    );

    protected $phpmailer = null;  //邮件插件类

    /**
     * 单例对象
     * @var array
     */
    protected static $instance = [];

    /**
     * 初始化
     * @param array $options 配置信息
     */
    public function __construct($options = array()){
        //引入类，并创建对象
        Vendor('PHPMailer.class#phpmailer');
        $this->phpmailer = new \PHPMailer();

        //获取配置
        $this->initConfig(); // 初始化配置
        $this->config = array_merge($this->config, $options);

        //赋予相关信息
        $this->phpmailer->CharSet    = $this->config['charset']; //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
        $this->phpmailer->IsSMTP();  // 设定使用SMTP服务
        $this->phpmailer->IsHTML(true); //支持html格式内容
        $this->phpmailer->SMTPDebug  = $this->config['smtp_debug']; // 关闭SMTP调试功能(1 = errors and messages; 2 = messages only)
        $this->phpmailer->SMTPAuth   = $this->config['smtp_auth']; // 启用 SMTP 验证功能
        $this->phpmailer->Host       = $this->config['smtp_host'];  // SMTP 服务器
        $this->phpmailer->Port       = $this->config['smtp_port'];  // SMTP服务器的端口号
        $this->phpmailer->Username   = $this->config['username'];  // SMTP服务器用户名
        $this->phpmailer->Password   = $this->config['password'];  // SMTP服务器密码
        $this->phpmailer->From       = $this->config['from_email'];  // 设置发送者邮箱
        $this->phpmailer->FromName   = $this->config['from_name'];  // 设置发送者称呼
    }

    /**
     * 初始化配置
     * 
     * @return null 
     */
    protected function initConfig()
    {
        $_config = [];

        if (C('MAIL_SMTP_HOST'))  $_config['smtp_host']  = C('MAIL_SMTP_HOST');
        if (C('MAIL_SMTP_PORT'))  $_config['smtp_port']  = C('MAIL_SMTP_PORT');
        if (C('MAIL_USERNAME'))   $_config['username']   = C('MAIL_USERNAME');
        if (C('MAIL_PASSWORD'))   $_config['password']   = C('MAIL_PASSWORD');
        if (C('MAIL_FROM_EMAIL')) $_config['from_email'] = C('MAIL_FROM_EMAIL');
        if (C('MAIL_FROM_NAME'))  $_config['from_name']  = C('MAIL_FROM_NAME');

        $this->config = array_merge($this->config, $_config);
    }

    /**
     * 单例模式
     * @return \Common\Support\Mail 
     */
    public static function instance($options = [])
    {
        return new static($options);
    }

    /**
     * 连接地址及端口
     * @param  string  $host 连接地址
     * @param  integer $port 端口
     * @return object        对象
     */
    public function connection($host, $port = 25){
        $this->phpmailer->Host = $host;
        $this->phpmailer->Port = $port;
        return $this;
    }

    /**
     * 连接用户名及密码
     * @param  string $username 用户名
     * @param  string $password 密码
     * @return object           当前对象
     */
    public function account($username, $password){
        $this->phpmailer->Username = $username;
        $this->phpmailer->Password = $password;
        return $this;
    }

    /**
     * 发送者信息
     * @param  string $from_email 发送者邮箱账号
     * @param  string $from_name  发送者称呼
     * @return object             当前对象
     */
    public function from($from_email, $from_name = ''){
        $this->phpmailer->From     = $from_email;
        $this->phpmailer->FromName = $from_name;
        return $this;
    }

    /**
     * 发送者邮箱设置
     * @param  string $from_email 发送者邮箱账号
     * @return object             当前对象
     */
    public function fromEmail($from_email){
        $this->phpmailer->From = $from_email;
        return $this;
    }

    /**
     * 发送者称呼设置
     * @param  string $from_name 发送者邮箱称呼
     * @return object            当前对象
     */
    public function fromName($from_name){
        $this->phpmailer->FromName = $from_name;
        return $this;
    }

    /**
     * 接收者信息（可多次操作）
     * @param  string $sendr_email 接收者邮箱账号
     * @param  string $sendr_name  接收者称呼
     * @return object              当前对象
     */
    public function to($sendr_email, $sendr_name = ''){
        $this->phpmailer->AddAddress($sendr_email, $sendr_name);
        return $this;
    }

    /**
     * 邮件主题
     * @param  string $subject 邮件主题
     * @return object          当前对象
     */
    public function subject($subject = ''){
        $this->phpmailer->Subject = $subject;
        return $this;
    }

    /**
     * 邮件内容
     * @param  string $content 邮件内容
     * @return object          当前对象
     */
    public function content($content = ''){
        $this->phpmailer->MsgHTML($content);
        return $this;
    }

    /**
     * 发送
     * @return boolean 成功或失败
     */
    public function send(){
        return $this->phpmailer->Send();
    }

    /**
     * 返回错误
     * @return string 错误内容
     */
    public function getError(){
        return $this->phpmailer->ErrorInfo;
    }
}