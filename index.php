<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用入口文件

// 检测PHP环境
//if(version_compare(PHP_VERSION,'5.5.0','<'))  die('require PHP > 5.5.0 !'); 

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG', true);

define('ROOT_PATH', __DIR__ . '/'); // 站点根目录

define('APP_PATH', ROOT_PATH . 'App/');  // 定义应用目录
define('RUNTIME_PATH', APP_PATH . 'Runtime/');  // 运行目录
define('COMMON_PATH',  APP_PATH . 'Common/'); // 公共模块目录


require ROOT_PATH . 'vendor/autoload.php';

// 引入ThinkPHP入口文件
require APP_PATH . 'Thinkphp/ThinkPHP.php';