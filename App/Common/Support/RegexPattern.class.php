<?php
/**
 * 验证正则表达式配置
 *
 * @author Flc <2016-08-27 13:17:39>
 */
namespace Common\Support;

class RegexPattern
{
    /**
     * 手机验证验证
     * @return string 
     */
    public static function mobile()
    {
        return "/^1[3-5,7,8]{1}[0-9]{9}$/";
    }

    /**
     * 金额验证（最多2位小数）
     * @return [type] [description]
     */
    public static function money()
    {
        return '/^(([1-9]{1}\d*)|([0]{1}))(\.(\d){1,2})?$/';
    }

    /**
     * 中文验证
     * @return string 
     */
    public static function zh()
    {
        return '/^[\x{4e00}-\x{9fa5}]+$/u';
    }
}