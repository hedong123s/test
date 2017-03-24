<?php
$MENU = array(
    //全局
    array(   //一级格式参考说明①
        "title"=>"全局",
        "submenu"=>array(    //二级格式参考说明②
            //二级开始      
            array(
                "title"=>"欢迎页",
                "ico"=>"ico-global",
                "submenu"=>array(    //三级级格式参考说明③
                    array(
                        "title"=>"欢迎页",
                        "url"=>U("index/main"),
                    ),
                    // array(
                    //     "title"=>"上传demo",
                    //     "url"=>U("index/upload"),
                    // ),
                    // array(
                    //     "title"=>"编辑器demo",
                    //     "url"=>U("index/editor"),
                    // )
                )
            ),
            //二级结束
        )
    ),

    array(   //一级格式参考说明①
        "title"=>"赛事",
        "submenu"=>array(    //二级格式参考说明②
            //二级开始      
            array(
                "title"=>"赛程管理",
                "ico"=>"ico-global",
                "submenu"=>array(    //三级级格式参考说明③
                    array(
                        "title" => "赛程管理",
                        "url"   => U("Match/index"),
                    ),
                    array(
                        "title" => "发布赛程",
                        "url"   => U("Match/add"),
                    ),
                )
            ),
            //二级结束
        )
    ),

    array(   //一级格式参考说明①
        "title"=>"报名审核",
        "submenu"=>array(    //二级格式参考说明②
            //二级开始      
            array(
                "title"=>"报名审核",
                "ico"=>"ico-global",
                "submenu"=>array(    //三级级格式参考说明③
                    array(
                        "title" => "报名审核",
                        "url"   => U("Entered/matchlist",array("type"=>'bm')),
                    ),
                    array(
                        "title" => "支付审核",
                        "url"   => U("Entered/matchlist",array("type"=>'zf')),
                    ),
                    array(
                        "title" => "已审核列表",
                        "url"   => U("Entered/paylist"),
                    ),
                    
                )
            ),
            //二级结束
        )
    ),

    //权限
    array(
        "title"=>"权限管理",
        "submenu"=>array(    //二级格式参考说明②
            //二级开始      
            array(
                "title"=>"管理员管理",
                "ico"=>"ico-global",
                "submenu"=>array(    //三级级格式参考说明③
                    array(
                        "title"=>"管理员列表",
                        "url"=>U("admin/index"),
                    ),
                    array(
                        "title"=>"管理员添加",
                        "url"=>U("admin/add"),
                    )
                )
            ),
            
            array(
                "title"=>"管理组管理",
                "ico"=>"ico-global",
                "submenu"=>array(    //三级级格式参考说明③
                    array(
                        "title"=>"管理组列表",
                        "url"=>U("admin/role_lists"),
                    ),
                    array(
                        "title"=>"管理组添加",
                        "url"=>U("admin/role_add"),
                    )
                )
            ),
            
            array(
                "title"=>"权限规则管理",
                "ico"=>"ico-global",
                "submenu"=>array(    //三级级格式参考说明③
                    array(
                        "title"=>"权限规则列表",
                        "url"=>U("admin/rule_index"),
                    ),
                    array(
                        "title"=>"权限规则添加",
                        "url"=>U("admin/rule_add"),
                    )
                )
            ),


            //二级结束
        )
    ),
    array(   //一级格式参考说明①
        "title"=>"会员管理",
        "submenu"=>array(    //二级格式参考说明②
            //二级开始
            array(
                "title"=>"会员管理",
                "ico"=>"ico-global",
                "submenu"=>array(    //三级级格式参考说明③
                    array(
                        "title"=>"会员列表",
                        "url"=>U("member/index"),
                    ),
                    array(
                        "title"=>"会员添加",
                        "url"=>U("member/add"),
                    )
                )
            ),
            //二级结束
        )
    ),
);

/*

* 一级格式说明①
** title 标题 @string
** url 跳转连接 @url
** submenu 二级菜单 @array


TIPS：!!!
1.如果含有submenu则url不生效，submenu优先级高于url


-------------------------------


* 二级格式说明②
** title 标题 @string
** url 跳转连接 @url
** target 跳转方式，可不填，默认main_iframe @string
** submenu 三级级菜单 @array


TIPS：!!!
如果含有submenu则url和target不生效，submenu优先级高于url；且此时以展开关闭形式展示


-------------------------------


* 三级级格式说明②
** title 标题 @string
** url 跳转连接 @url
** target 跳转方式，可不填，默认main_iframe @string


*/
?>

