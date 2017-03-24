<?php
/**
 * 管理组模型
 * @author Flc <2015-12-07 16:31:45>
 */
namespace Admin\Model;

use Think\Model\RelationModel;

class AdminRolesModel extends RelationModel {
    /**
     * 模型表
     * @var string
     */
    protected $tableName = 'admin_roles'; 

    /**
     * 关联模型
     * @var array
     */
    protected $_link = array(
        'Admins' => array(
            'mapping_type'         => self::MANY_TO_MANY,
            'class_name'           => 'admins',
            'mapping_name'         => 'Admins',  //关联的映射名称
            'foreign_key'          => 'role_id',
            'relation_foreign_key' => 'admin_id',
            'relation_table'       => 'qo_admins_roles' //此处应显式定义中间表名称，且不能使用C函数读取表前缀
        ),
        'Rules' => array(
            'mapping_type'         => self::MANY_TO_MANY,
            'class_name'           => 'admin_rules',
            'mapping_name'         => 'Rules',  //关联的映射名称
            'foreign_key'          => 'role_id',
            'relation_foreign_key' => 'rule_id',
            'relation_table'       => 'qo_admin_roles_rules' //此处应显式定义中间表名称，且不能使用C函数读取表前缀
        ),
    );
}
?>