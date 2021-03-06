<?php
/**
 * 权限规则表模型
 * @author Flc <2015-12-07 16:31:45>
 */
namespace Admin\Model;

use Think\Model\RelationModel;

class AdminRulesModel extends RelationModel {
    /**
     * 模型表
     * @var string
     */
    protected $tableName = 'admin_rules'; 

    /**
     * 关联模型
     * @var array
     */
    protected $_link = array(
        'Roles' => array(
            'mapping_type'         => self::MANY_TO_MANY,
            'class_name'           => 'admin_roles',
            'mapping_name'         => 'Roles',  //关联的映射名称
            'foreign_key'          => 'rule_id',
            'relation_foreign_key' => 'role_id',
            'relation_table'       => 'qo_admin_roles_rules' //此处应显式定义中间表名称，且不能使用C函数读取表前缀
        ),
    );
}
?>