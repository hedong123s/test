/*
SQLyog Enterprise v12.09 (64 bit)
MySQL - 5.5.48-log : Database - wudao
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`wudao` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;

USE `wudao`;

/*Table structure for table `qo_admin_roles` */

DROP TABLE IF EXISTS `qo_admin_roles`;

CREATE TABLE `qo_admin_roles` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '用户组名',
  `description` text NOT NULL COMMENT '说明',
  `created_at` int(10) NOT NULL DEFAULT '0',
  `updated_at` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='福利商城 - 用户组表';

/*Data for the table `qo_admin_roles` */

insert  into `qo_admin_roles`(`id`,`name`,`description`,`created_at`,`updated_at`) values (1,'超级管理员','拥有网站后台所有管理权限！',1449714100,1449714100),(2,'财务','',1449714228,1449714228),(3,'管理A组','测试 20160114 ',1452758358,1452758385),(4,'管理B组','测试 20160114',1452758372,1452758400),(5,'管理C组','测试 20160114',1452758438,1452758438),(6,'管理D组','测试 20160114',1452758448,1452758448),(7,'测试组','123123123123',1471942494,1471942615);

/*Table structure for table `qo_admin_roles_rules` */

DROP TABLE IF EXISTS `qo_admin_roles_rules`;

CREATE TABLE `qo_admin_roles_rules` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `role_id` int(10) NOT NULL DEFAULT '0' COMMENT '角色id',
  `rule_id` int(10) NOT NULL DEFAULT '0' COMMENT '权限规则id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8 COMMENT='角色与权限关联表';

/*Data for the table `qo_admin_roles_rules` */

insert  into `qo_admin_roles_rules`(`id`,`role_id`,`rule_id`) values (31,7,1),(32,7,4),(33,1,11),(34,1,1),(35,1,2),(36,1,3),(37,1,4),(38,1,5),(39,1,6),(40,1,7),(41,1,8),(42,1,9),(43,1,10);

/*Table structure for table `qo_admin_rules` */

DROP TABLE IF EXISTS `qo_admin_rules`;

CREATE TABLE `qo_admin_rules` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '规则名',
  `value` varchar(255) NOT NULL DEFAULT '' COMMENT '唯一标识',
  `typeid` int(5) NOT NULL DEFAULT '0' COMMENT '类型',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否生效。0为未生效，1为已生效',
  PRIMARY KEY (`id`),
  UNIQUE KEY `value` (`value`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COMMENT='权限规则表';

/*Data for the table `qo_admin_rules` */

insert  into `qo_admin_rules`(`id`,`name`,`value`,`typeid`,`status`) values (1,'管理员添加','Admin/admin/add',50,1),(2,'管理员列表','Admin/admin/index',50,1),(3,'管理员编辑','Admin/Admin/edit',50,1),(4,'管理组列表','Admin/admin/role_lists',50,1),(5,'管理组添加','Admin/admin/role_add',50,1),(6,'管理组编辑','Admin/Admin/role_edit',50,1),(7,'管理组设置权限','Admin/Admin/setRules',50,1),(8,'权限规则列表','Admin/admin/rule_index',50,1),(9,'权限规则添加','Admin/admin/rule_add',50,1),(10,'权限规则编辑','Admin/Admin/rule_edit',50,1),(11,'999','9999',1,1);

/*Table structure for table `qo_admins` */

DROP TABLE IF EXISTS `qo_admins`;

CREATE TABLE `qo_admins` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '管理员id',
  `username` varchar(60) NOT NULL COMMENT '用户名',
  `password` varchar(255) NOT NULL COMMENT '密码',
  `nickname` varchar(100) DEFAULT '' COMMENT '称呼',
  `status` tinyint(2) DEFAULT '0' COMMENT '生效状态',
  `times` int(10) DEFAULT '0' COMMENT '登录次数',
  `last_logined_at` int(10) DEFAULT '0' COMMENT '最后登录时间',
  `last_logined_ip` varchar(20) DEFAULT '' COMMENT '最后登录IP',
  `created_at` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COMMENT='管理员表';

/*Data for the table `qo_admins` */

insert  into `qo_admins`(`id`,`username`,`password`,`nickname`,`status`,`times`,`last_logined_at`,`last_logined_ip`,`created_at`,`updated_at`) values (1,'admin','$2y$10$qmc94uUDB1d5pfohwZWBKOg9HPQrzKT1gN8h4Si6dJSbu5my7IxOC','超级管理员',1,20,1481013220,'192.168.1.145',1471922291,1471945355),(2,'demo','$2y$10$ewAkAojC7UiPpz/91nVbLepx2afM7EC4k4BMbOnanyEB9wXHzxMz.',NULL,1,0,0,'',1471941651,1471941651),(3,'demo2','$2y$10$CMW8ai.vup2tfUFZb7bHCOdt4S9z2CqjCJMa1YAtfnSI/jaVWIfbK','测试用户',1,0,0,'',1471941705,1471941705),(4,'ceshi','$2y$10$rwGP63cmzPXY0pJZ2KyhZOF7Io.QCmJGLz04jslZlhkfn/LtGOGlS','ceshi1',1,0,0,'',1471941959,1471942281);

/*Table structure for table `qo_admins_roles` */

DROP TABLE IF EXISTS `qo_admins_roles`;

CREATE TABLE `qo_admins_roles` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `admin_id` int(20) NOT NULL DEFAULT '0' COMMENT '管理员id',
  `role_id` int(10) NOT NULL DEFAULT '0' COMMENT '管理组id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `admin_id` (`admin_id`,`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COMMENT='管理员与角色关系表';

/*Data for the table `qo_admins_roles` */

insert  into `qo_admins_roles`(`id`,`admin_id`,`role_id`) values (12,1,1),(9,4,2),(10,4,3),(11,4,4);

/*Table structure for table `qo_article_category` */

DROP TABLE IF EXISTS `qo_article_category`;

CREATE TABLE `qo_article_category` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '分类名称',
  `description` text COMMENT '描述',
  `keyword` varchar(100) DEFAULT '' COMMENT '关键字',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '生效状态',
  `sortid` int(10) DEFAULT '0' COMMENT '排序编号',
  `created_at` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `sortid` (`sortid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='内容分类表';

/*Data for the table `qo_article_category` */

insert  into `qo_article_category`(`id`,`name`,`description`,`keyword`,`status`,`sortid`,`created_at`,`updated_at`) values (1,'二元产品问答','二元产品问答','二元产品问答',1,1,1472077347,1472077347),(2,'外汇产品问答','外汇产品问答','外汇产品问答',1,2,1472077361,1472077361),(3,'经纪问答','经纪问答','经纪问答',1,3,1472077381,1472077381),(4,'推荐问答','推荐问答','推荐问答',1,4,1472077394,1472077394),(5,'最新公告','最新公告','最新公告',1,5,1472077710,1472077710);

/*Table structure for table `qo_articles` */

DROP TABLE IF EXISTS `qo_articles`;

CREATE TABLE `qo_articles` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `cid` int(10) NOT NULL DEFAULT '0' COMMENT '分类id',
  `title` varchar(200) NOT NULL COMMENT '文章标题',
  `keyword` varchar(100) DEFAULT '' COMMENT '关键字',
  `description` text COMMENT '描述',
  `thumb` bigint(20) DEFAULT '0' COMMENT '文章缩略图',
  `content` longtext COMMENT '内容',
  `sortid` int(10) DEFAULT '0' COMMENT '排序',
  `status` tinyint(2) DEFAULT '0' COMMENT '生效状态',
  `created_at` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `thumb` (`thumb`),
  KEY `sortid` (`sortid`),
  KEY `status` (`status`),
  KEY `cid` (`cid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='内容文章表';

/*Data for the table `qo_articles` */

insert  into `qo_articles`(`id`,`cid`,`title`,`keyword`,`description`,`thumb`,`content`,`sortid`,`status`,`created_at`,`updated_at`) values (1,1,'阿斯顿发烧','','',0,'阿斯顿发顺丰大师傅暗室逢灯as发的',1,1,1472077628,1472077628);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
