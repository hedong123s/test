<?php
namespace Common\Support;

/**
 * 哈希类 - 用于密码加密（安全）
 *
 * @author Flc <2016-08-23 09:31:03>
 * @example  
 *
 *      use Common\Support\Hash;
 *      $hashStr = Hash::make('123456');
 *      Hash::check('123456', $hasStr);
 */
class Hash
{
    /**
     * 哈希加密;用于密码加密
     * 要求：PHP>=5.5
     *
     * @author Flc <2016-08-22 10:51:15>
     * @return strign 
     */
    public static function make($value)
    {
        return password_hash($value, PASSWORD_BCRYPT);
    }

    /**
     * 哈希加密校验;用户密码校验
     * 要求：PHP>=5.5
     *
     * @author Flc <2016-08-23 11:26:26>
     * @param  string $value       明文密码
     * @param  string $hashedValue 密文密码
     * @return boolean              
     */
    public static function check($value, $hashedValue)
    {
        if (strlen($hashedValue) === 0) {
            return false;
        }

        return password_verify($value, $hashedValue);
    }
}