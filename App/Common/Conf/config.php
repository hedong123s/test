<?php
/**
 * 系统配置
 *
 * @author Flc <2016-08-22 10:23:36>
 */
return array(
    'MODULE_ALLOW_LIST' => array('Home', 'Admin'),
    'DEFAULT_MODULE'    => 'Home',  //  默认模块

    'DEFAULT_CHARSET'      => 'utf-8', // 默认输出编码
    'TMPL_CACHE_ON'        => false, //是否开启模板编译缓存，测试时关闭模板缓存

    'TMPL_CACHE_ON'        => false, //是否开启模板编译缓存，测试时关闭模板缓存
    'URL_MODEL'            => 2, // URL访问模式
    'URL_CASE_INSENSITIVE' => true,  //关闭大小写为true.忽略地址大小写
    'URL_HTML_SUFFIX'      => 'html|js|xml|json|pdf',  // URL伪静态后缀设置，多个用|隔开
    'SHOW_PAGE_TRACE'      => false,   //是否显示Trace信息
    'SHOW_ERROR_MSG'       => true,  //是否显示错误信息
    'DEFAULT_FILTER'       => 'htmlspecialchars',  // 默认参数过滤方法 用于I函数...

    //COOKIE相关设置
    'COOKIE_EXPIRE' => 0,    // Cookie有效期
    'COOKIE_DOMAIN' => '',      // Cookie有效域名
    'COOKIE_PREFIX' => 'qounion',      // Cookie前缀 避免冲突（同域名多个站点，可用此区分）
    
    //SESSION相关设置
    'SESSION_AUTO_START' => true,    // 是否自动开启Session
    'SESSION_PREFIX'     => 'qounion', // session 前缀

    //加密相关
    'AUTH_GLOBAL_KEY' => '6Xn1Ry5Tq2Ud6Tq2Cv3Go5Ae3Dq2Sw7G',   //全局密钥
    'AUTH_USER_KEY'   => 'l9Hb2Fc8Vr8Rh2Jn9Ks9Qb6Ut7Ag4Jf0', //用户密钥

    /**
     * 文件上传相关配置
     */
    // 文件类型及可支持的后缀
    'FILE_UPLOAD_TYPES' => array(
        'image' => 'jpg|gif|png|bmp|jpeg',
        'excel' => 'xlsx|xls',
        'word'  => 'doc|docx',
        'pdf'   => 'pdf',
        'other' => 'gif|png|jpg|bmp|zip|gz|rar|iso|doc|docx|xls|xlsx|ppt|wps|txt|swf|flv|mpg|mp3|rm|rmvb|wmv|wma|wav|pdf'
    ),
    'FILE_UPLOAD_MAX_SIZE' => 20971520,  // 文件最大上传大小

    'LOAD_EXT_CONFIG' => array('conn', 'cores', 'api'),
);