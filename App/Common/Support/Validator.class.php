<?php
/**
 * 验证类工具factory类
 *
 * @author Flc <2016-08-22 15:22:50>
 */
namespace Common\Support;

use Overtrue\Validation\Translator;
use Overtrue\Validation\Factory as ValidatorFactory;

class Validator
{
    /**
     * 静态方法
     * @param  string $method 操作方法
     * @param  mixed  $args   参数
     * @return Overtrue\Validation\Factory         
     */
    public static function __callStatic($method, $args)
    {
        $factory = new ValidatorFactory(new Translator);

        return call_user_func_array([$factory, $method], $args);
    }
}