<?php
/**
 * 配置相关函数
 *
 * @author Flc <2016-12-07 20:32:57>
 */

if (! function_exists('defineDances')) {
    /**
     * 定义舞种
     *
     * @author Flc <2016-12-07 20:36:05>
     * @return array
     */
    function defineDances()
    {
        return [
            'W'  => '华尔兹',
            'T'  => '探戈',
            'VW' => '维也纳华尔兹',
            'F'  => '狐步',
            'Q'  => '快步',
            'C'  => '恰恰恰',
            'R'  => '伦巴',
            'J'  => '牛仔',
            'S'  => '桑巴',
            'P'  => '斗牛',
        ];
    }
}