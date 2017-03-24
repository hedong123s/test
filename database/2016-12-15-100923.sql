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

insert  into `qo_admins`(`id`,`username`,`password`,`nickname`,`status`,`times`,`last_logined_at`,`last_logined_ip`,`created_at`,`updated_at`) values (1,'admin','$2y$10$qmc94uUDB1d5pfohwZWBKOg9HPQrzKT1gN8h4Si6dJSbu5my7IxOC','超级管理员',1,26,1481702950,'192.168.1.145',1471922291,1471945355),(2,'demo','$2y$10$ewAkAojC7UiPpz/91nVbLepx2afM7EC4k4BMbOnanyEB9wXHzxMz.',NULL,1,0,0,'',1471941651,1471941651),(3,'demo2','$2y$10$CMW8ai.vup2tfUFZb7bHCOdt4S9z2CqjCJMa1YAtfnSI/jaVWIfbK','测试用户',1,0,0,'',1471941705,1471941705),(4,'ceshi','$2y$10$rwGP63cmzPXY0pJZ2KyhZOF7Io.QCmJGLz04jslZlhkfn/LtGOGlS','ceshi1',1,0,0,'',1471941959,1471942281);

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

/*Table structure for table `qo_files` */

DROP TABLE IF EXISTS `qo_files`;

CREATE TABLE `qo_files` (
  `file_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '文件ID',
  `name` varchar(255) DEFAULT '' COMMENT '文件名',
  `filepath` varchar(255) NOT NULL COMMENT '文件路径',
  `size` bigint(20) DEFAULT '0' COMMENT '字节数',
  `md5` varchar(60) NOT NULL COMMENT '文件md5值',
  `sha1` varchar(60) NOT NULL COMMENT '文件sha1值',
  `created_at` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`file_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='文件表';

/*Data for the table `qo_files` */

insert  into `qo_files`(`file_id`,`name`,`filepath`,`size`,`md5`,`sha1`,`created_at`) values (1,'weiib-email-log.png','Uploads/2016-12-07/5847dd39864b6.png',12565,'df220d64aec293c330fdceb4fdab06c1','94d82afa14fd91c41f504616d5513f22f3d2e862',1481104697);

/*Table structure for table `qo_match_entrys` */

DROP TABLE IF EXISTS `qo_match_entrys`;

CREATE TABLE `qo_match_entrys` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '赛事报名ID',
  `match_id` int(10) NOT NULL DEFAULT '0' COMMENT '赛程ID',
  `uid` int(10) NOT NULL DEFAULT '0' COMMENT '报名的用户ID',
  `team_name` varchar(100) DEFAULT '' COMMENT '代表队名称',
  `team_teacher` varchar(100) DEFAULT '' COMMENT '带队老师名称',
  `team_contact` varchar(100) DEFAULT '' COMMENT '带队老师联系方式',
  `team_qq` varchar(40) DEFAULT '' COMMENT '带队老师QQ',
  `created_at` int(10) NOT NULL DEFAULT '0' COMMENT '创建（报名）时间',
  `updated_at` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `match_id` (`match_id`,`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='赛程报名表';

/*Data for the table `qo_match_entrys` */

/*Table structure for table `qo_match_event_entry_player` */

DROP TABLE IF EXISTS `qo_match_event_entry_player`;

CREATE TABLE `qo_match_event_entry_player` (
  `event_entry_id` int(10) NOT NULL COMMENT '组别报名ID',
  `player_id` int(10) NOT NULL COMMENT '选手ID',
  PRIMARY KEY (`event_entry_id`,`player_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='组别报名ID与选手关联表';

/*Data for the table `qo_match_event_entry_player` */

/*Table structure for table `qo_match_event_entrys` */

DROP TABLE IF EXISTS `qo_match_event_entrys`;

CREATE TABLE `qo_match_event_entrys` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '组别报名ID',
  `match_entry_id` int(10) NOT NULL DEFAULT '0' COMMENT '赛程报名ID（match_entrys表ID）',
  `match_id` int(10) NOT NULL DEFAULT '0' COMMENT '赛程ID',
  `event_id` int(10) NOT NULL DEFAULT '0' COMMENT '组别ID',
  `uid` int(10) NOT NULL DEFAULT '0' COMMENT '归属用户',
  `created_at` int(10) NOT NULL DEFAULT '0' COMMENT '创建（报名）时间',
  `updated_at` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `match_entry_id` (`match_entry_id`),
  KEY `match_id` (`match_id`),
  KEY `event_id` (`event_id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='赛程组别报名表';

/*Data for the table `qo_match_event_entrys` */

/*Table structure for table `qo_match_events` */

DROP TABLE IF EXISTS `qo_match_events`;

CREATE TABLE `qo_match_events` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '组别ID',
  `match_id` int(10) NOT NULL COMMENT '赛程ID',
  `group_id` int(10) NOT NULL COMMENT '赛组ID',
  `event_number` int(10) NOT NULL DEFAULT '0' COMMENT '组别编码（每个赛程都以1开头自动增长）',
  `event_title` varchar(100) NOT NULL COMMENT '组别名称',
  `dances` varchar(200) DEFAULT '' COMMENT '比赛舞种，多个用，隔开',
  `fee` decimal(20,2) DEFAULT '0.00' COMMENT '服务费',
  `age_type` tinyint(2) DEFAULT '0' COMMENT '年龄限制类型，0为不限，1为上限（如1999年前），2为下限（1999年后）',
  `age_date` date DEFAULT NULL COMMENT '年龄限制的年月日，age_type为0时为空',
  `plate_str` varchar(100) DEFAULT '' COMMENT '板块，允许参加的板块名称，1为职业组、2为专业组、3为业余组（多个用逗号隔开）',
  `person_type` tinyint(2) DEFAULT '0' COMMENT '报名参赛人员类型，1为单人；2为双人；3为群舞（6人），4为集体舞（12-16人）',
  `person_sex_str` varchar(100) DEFAULT '' COMMENT '报名参赛人员性别，0为不限，1为男，2为女；如当前参赛多人人，则性别按逗号隔开',
  `created_at` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(10) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_number` (`event_number`,`match_id`),
  KEY `match_id` (`match_id`),
  KEY `group_id` (`group_id`),
  KEY `plate_str` (`plate_str`),
  KEY `person_type` (`person_type`),
  KEY `person_sex_str` (`person_sex_str`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='赛程 - 组别表';

/*Data for the table `qo_match_events` */

/*Table structure for table `qo_match_groups` */

DROP TABLE IF EXISTS `qo_match_groups`;

CREATE TABLE `qo_match_groups` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '赛组ID',
  `match_id` int(10) NOT NULL DEFAULT '0' COMMENT '赛程ID',
  `group_title` varchar(200) NOT NULL COMMENT '赛组名称',
  `group_content` text COMMENT '赛组描述',
  `created_at` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `match_id` (`match_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COMMENT='赛程 - 赛组表';

/*Data for the table `qo_match_groups` */

insert  into `qo_match_groups`(`id`,`match_id`,`group_title`,`group_content`,`created_at`,`updated_at`) values (1,3,'[一] 锦标赛组 比赛规定和设项','&lt;p class=&quot;MsoNormal&quot;&gt;\r\n	&lt;b&gt;一、锦标赛各组奖励设置：&lt;/b&gt;&lt;b&gt;&lt;/b&gt; \r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot; style=&quot;text-indent:21.7500pt;&quot;&gt;\r\n	&lt;span&gt;锦标赛各组奖励前六名，前三名颁发奖金、奖杯、证书；&lt;/span&gt;4-6名颁发奖杯、证书。\r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot;&gt;\r\n	&lt;b&gt;&lt;span&gt;二、锦标赛各组奖金设置如下（奖金总额&lt;/span&gt;12万元）：&lt;/b&gt;&lt;b&gt;&lt;/b&gt; \r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot;&gt;\r\n	&lt;b&gt;注明：锦标赛组别：&lt;/b&gt;&lt;b&gt;&lt;span&gt;（注明：不足&lt;/span&gt;6对选手参赛，比赛不发奖金；不足3对选手参赛，比赛取消。）&lt;/b&gt;&lt;b&gt;&lt;/b&gt; \r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot; style=&quot;text-indent:21.7500pt;&quot;&gt;\r\n	1、职业组比赛奖金：第一名5000元、第二名3000元、第三名2000元，\r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot; style=&quot;text-indent:21.7500pt;&quot;&gt;\r\n	2、A 组 &amp;nbsp;比赛奖金：第一名3000元、第二名2000元、第三名1000元，\r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot; style=&quot;text-indent:21.7500pt;&quot;&gt;\r\n	3、双人精英组比赛奖金：第一名800元、第二名600元、第三名400元，\r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot; style=&quot;text-indent:21.7500pt;&quot;&gt;\r\n	4、单人精英组比赛奖金：第一名500元、第二名400元、第三名300元，\r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot;&gt;\r\n	&lt;b&gt;三、锦标赛组比赛规定：&lt;/b&gt;&lt;b&gt;&lt;/b&gt; \r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot; style=&quot;text-indent:21.7500pt;&quot;&gt;\r\n	1、职业组选手禁报专业组、业余组比赛，专业院校学生，禁报业余组别比赛。\r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot; style=&quot;text-indent:21.7500pt;&quot;&gt;\r\n	2、同一对双人精英组选手不能兼报参加多个精英组比赛。\r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot; style=&quot;text-indent:21.7500pt;&quot;&gt;\r\n	3、13岁或以下年龄组的服装和动作按规定执行。\r\n&lt;/p&gt;',1481111485,1481111909),(2,3,'[二] 常规赛专业组 比赛规定和设项','&lt;p class=&quot;MsoNormal&quot;&gt;\r\n	&lt;b&gt;一、奖励设置：&lt;/b&gt;专业组奖励前六名，前三名颁发奖杯、证书；四至六名颁发证书。&lt;b&gt;&lt;/b&gt; \r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot;&gt;\r\n	&lt;b&gt;二、比赛规定：&lt;/b&gt;职业组选手禁止报名。1&lt;b&gt;&lt;/b&gt;\r\n&lt;/p&gt;',1481111542,1481111920);

/*Table structure for table `qo_match_ipate_relation` */

DROP TABLE IF EXISTS `qo_match_ipate_relation`;

CREATE TABLE `qo_match_ipate_relation` (
  `id` int(10) NOT NULL DEFAULT '0' COMMENT '板块ID',
  `pid` int(10) NOT NULL DEFAULT '0' COMMENT '父级板块ID，一级板块，父id为0',
  PRIMARY KEY (`id`,`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='赛程一级板块、二级板块关联表';

/*Data for the table `qo_match_ipate_relation` */

insert  into `qo_match_ipate_relation`(`id`,`pid`) values (1,0),(2,0),(3,0);

/*Table structure for table `qo_match_ipates` */

DROP TABLE IF EXISTS `qo_match_ipates`;

CREATE TABLE `qo_match_ipates` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '赛程板块ID',
  `match_id` int(10) NOT NULL COMMENT '赛程ID',
  `name` varchar(100) NOT NULL COMMENT '板块名称',
  `ident` varbinary(20) DEFAULT '' COMMENT '（一级板块专用）一级板块唯一标识，在创建赛程的时候生成、不可删除，修改；PROC为职业组；SPEC为专业组;AMAT为业余组',
  `is_cross` tinyint(2) DEFAULT '0' COMMENT '是否允许二级板块跨类报名（一级板块专用）。0为不允许，1为允许',
  `created_at` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `match_id` (`match_id`),
  KEY `is_cross` (`is_cross`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COMMENT='赛程板块表';

/*Data for the table `qo_match_ipates` */

insert  into `qo_match_ipates`(`id`,`match_id`,`name`,`ident`,`is_cross`,`created_at`,`updated_at`) values (1,4,'职业组','PROC',0,1481767631,1481767631),(2,4,'专业组','SPEC',0,1481767631,1481767631),(3,4,'业余组','AMAT',0,1481767631,1481767631);

/*Table structure for table `qo_matchs` */

DROP TABLE IF EXISTS `qo_matchs`;

CREATE TABLE `qo_matchs` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '赛事ID',
  `title` varchar(200) NOT NULL COMMENT '赛事名称',
  `content` longtext COMMENT '赛事内容',
  `contact_number` varchar(100) DEFAULT '' COMMENT '现场联系电话',
  `sign_start_time` int(10) NOT NULL DEFAULT '0' COMMENT '报名开始时间戳',
  `sign_end_time` int(10) NOT NULL DEFAULT '0' COMMENT '报名截止时间戳',
  `match_start_time` int(10) DEFAULT '0' COMMENT '比赛开始时间',
  `match_end_time` int(10) DEFAULT '0' COMMENT '比赛截止时间',
  `pay_end_time` int(10) DEFAULT '0' COMMENT '报名后付款截止时间',
  `created_at` int(10) DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(10) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COMMENT='赛程表';

/*Data for the table `qo_matchs` */

insert  into `qo_matchs`(`id`,`title`,`content`,`contact_number`,`sign_start_time`,`sign_end_time`,`match_start_time`,`match_end_time`,`pay_end_time`,`created_at`,`updated_at`) values (1,'2016年广东省标准舞、拉丁舞年度总决赛 暨第十届珠江三角洲标准舞、拉丁舞公开赛 竞赛规程','&lt;p class=&quot;MsoNormal&quot;&gt;\r\n	&lt;b&gt;一、主办单位：&lt;/b&gt;佛山市禅城区文化体育局22\r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot;&gt;\r\n	&amp;nbsp;&amp;nbsp;&amp;nbsp;&amp;nbsp;&amp;nbsp;&amp;nbsp;&amp;nbsp;&amp;nbsp;&amp;nbsp;&amp;nbsp;&amp;nbsp;&amp;nbsp;&amp;nbsp;&amp;nbsp;&lt;span&gt;广东省青少年舞蹈协会&lt;/span&gt; \r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot;&gt;\r\n	&lt;b&gt;二、承办单位：&lt;/b&gt;佛山市运动家文化策划有限公司\r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot;&gt;\r\n	&lt;b&gt;三、竞赛时间和地点：&lt;/b&gt;2016年12月25日在佛山市体育馆举行。\r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot;&gt;\r\n	&lt;b&gt;四、竞赛规则：&lt;/b&gt;比赛分预赛、复赛、半决赛、决赛进行。&lt;b&gt;&lt;/b&gt; \r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot;&gt;\r\n	&lt;b&gt;五、参赛报名规定：（&lt;/b&gt;&lt;span&gt;咨询电话：&lt;/span&gt;0757- 82361796，18928563168）\r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot; style=&quot;text-indent:25.6500pt;&quot;&gt;\r\n	&lt;b&gt;1、报名请登录&lt;/b&gt;&lt;a href=&quot;http://www.cwdance.cn&quot;&gt;&lt;b&gt;www.cwdance.cn&lt;/b&gt;&lt;/a&gt;&lt;b&gt;，&lt;/b&gt;&lt;b&gt;&lt;span&gt;在网站的&lt;/span&gt;“赛事通”中报名&lt;/b&gt;。\r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot; style=&quot;text-indent:25.6500pt;&quot;&gt;\r\n	2、报名日期：报名系统于12月3日12:00开启，12月11日24:00关闭。\r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot; style=&quot;text-indent:25.6500pt;&quot;&gt;\r\n	3、同一对精英组选手不能兼报参加多个精英组比赛。\r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot; style=&quot;text-indent:25.6500pt;&quot;&gt;\r\n	4、已报双人精英组的同一对选手，不能兼报常规赛同龄双人组比赛，但可更换舞伴兼报常规赛同龄双人组比赛。\r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot; style=&quot;text-indent:25.6500pt;&quot;&gt;\r\n	5、已报单人精英组的选手，不能兼报常规赛同龄单人组比赛。\r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot; style=&quot;text-indent:25.6500pt;&quot;&gt;\r\n	6、双人组选手，是以年龄大的选手为准，报相应年龄组别比赛。\r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot; style=&quot;text-indent:25.6500pt;&quot;&gt;\r\n	&lt;b&gt;7、每名选手最多可兼报4个项目。注意，由此引起选手检录误场责任自负。&lt;/b&gt;&lt;b&gt;&lt;/b&gt; \r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot;&gt;\r\n	&lt;b&gt;六、比赛服务费：&lt;/b&gt;&lt;b&gt;&lt;/b&gt; \r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot; style=&quot;text-indent:25.6500pt;&quot;&gt;\r\n	&lt;span&gt;报名费必须于&lt;/span&gt;2016年12月17日前在报名系统中线上支付，或线下汇款(或转账)到组委会指定账户：\r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot; style=&quot;text-indent:25.6500pt;&quot;&gt;\r\n	&lt;b&gt;&lt;span&gt;交通银行佛山分行，户名：陈莘，账号&lt;/span&gt;:&lt;/b&gt;&lt;b&gt;&amp;nbsp;6222 6237 1000 3511 859&lt;/b&gt;&lt;b&gt;，&lt;/b&gt;&lt;span&gt;报名表经赛会确认后，不得更改。若因特殊原因更改，则需缴纳更改费&lt;/span&gt;200元/组(项)。\r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot;&gt;\r\n	&lt;b&gt;&amp;nbsp;&lt;/b&gt; \r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot;&gt;\r\n	&lt;b&gt;&amp;nbsp;&lt;/b&gt; \r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot;&gt;\r\n	&lt;b&gt;七、裁判、领队报到：&lt;/b&gt;&lt;b&gt; &lt;/b&gt;&lt;b&gt;&lt;/b&gt; \r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot; style=&quot;text-indent:25.6500pt;&quot;&gt;\r\n	1、报到：各参赛领队于12月23日中午12:00前，报到，领取选手背号。\r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot; style=&quot;text-indent:25.6500pt;&quot;&gt;\r\n	2、裁判、领队会议：12月23日下午14:00召开裁判、领队会议。\r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot; style=&quot;text-indent:25.6500pt;&quot;&gt;\r\n	3、报到和裁判、领队会议地址：佛山市禅城区卫国路，佛山体育馆后座训练馆顶层，广东省青少年舞蹈协会（总部）佛山市禅舞培训中心。\r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot; style=&quot;text-indent:25.6500pt;&quot;&gt;\r\n	4、比赛日：2016年12月25日8:00，裁判员开赛前会议、选手8:00时开始检录，8:30时开始比赛。\r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot;&gt;\r\n	&lt;b&gt;八、比赛设项规定：&lt;/b&gt;&lt;b&gt;&lt;/b&gt; \r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot;&gt;\r\n	◆ 标准舞系列：W 华尔兹、T 探戈、VW 维也纳华尔兹、F 狐步、Q 快步\r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot;&gt;\r\n	◆ 拉丁舞系列：C 恰恰恰、R 伦巴、J 牛仔、S 桑巴、P 斗牛\r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot; style=&quot;text-indent:19.2500pt;&quot;&gt;\r\n	5支舞：标准舞：W、T、VW、F、Q；拉丁舞：S、C、R、J、P 。\r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot; style=&quot;text-indent:19.2500pt;&quot;&gt;\r\n	4支舞：标准舞：W、T、VW、Q；拉丁舞：S、C、R、J 。\r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot; style=&quot;text-indent:19.2500pt;&quot;&gt;\r\n	3支舞：标准舞：公开组W、T、VW&lt;b&gt;&lt;span&gt;（少年组&lt;/span&gt;W、Q、VW）&lt;/b&gt;&lt;span&gt;；&lt;/span&gt; &lt;span&gt;拉丁舞：&lt;/span&gt;C、R、J 。\r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot; style=&quot;text-indent:19.2500pt;&quot;&gt;\r\n	2支舞：标准舞：公开组W、T；少年组W、Q；拉丁舞：C、R 。\r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot;&gt;\r\n	&lt;b&gt;九、竞赛规定：&lt;/b&gt;&lt;b&gt;&lt;/b&gt; \r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot; style=&quot;text-indent:25.6500pt;&quot;&gt;\r\n	&lt;b&gt;1、&lt;/b&gt;&lt;b&gt;&lt;span&gt;锦标赛组别注明：不足&lt;/span&gt;6对选手参赛，比赛不发奖金；不足3对选手参赛，比赛取消。&lt;/b&gt; \r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot; style=&quot;text-indent:25.6500pt;&quot;&gt;\r\n	2、13岁或以下年龄组的服装和动作按规定执行。\r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot; style=&quot;margin-left:25.6000pt;&quot;&gt;\r\n	3、参加颁奖仪式选手须着比赛服装。\r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot; style=&quot;text-indent:25.6500pt;&quot;&gt;\r\n	4、参加各年龄组比赛的选手，必须随身携带身份证或户口本原件备查。\r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot; style=&quot;text-indent:25.6500pt;&quot;&gt;\r\n	&lt;b&gt;5、比赛期间不接受选手投诉，投诉必须于比赛后由领队缴交500元投诉费，并以书面形式交仲裁委员会处理，投诉失败则不退还投诉费。&lt;/b&gt; \r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot; style=&quot;text-indent:25.5500pt;&quot;&gt;\r\n	6、参赛运动员禁用违禁药物。\r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot; style=&quot;text-indent:25.5500pt;&quot;&gt;\r\n	7、参赛运动员须由组队单位（或个人）购买比赛当日的意外保险；&lt;b&gt;谢绝患有疾病，特别是心脑血管及高血压疾病患者参赛&lt;/b&gt;；&lt;span&gt;若隐瞒病情参赛，则比赛期间一切意外事故的责任由选手自负，大赛组委会不承担任何后果及责任。&lt;/span&gt; \r\n&lt;/p&gt;\r\n&lt;p class=&quot;MsoNormal&quot;&gt;\r\n	&lt;b&gt;十、本竞赛规程最终解释权属主办单位，未尽事宜，另行通知。&lt;/b&gt; \r\n&lt;/p&gt;','1323134654222',1481040000,1481731200,1482336000,1483113600,1481990400,1481106546,1481110305),(2,'2016年广东省标准舞、拉丁舞年度总决赛 暨第十届珠江三角洲标准舞、拉丁舞公开赛 竞赛规程','11111','123123',1481040000,1482336000,1482336000,1483027200,1481385600,1481106546,1481106546),(3,'2016年广东省标准舞、拉丁舞年度总决赛 暨第十届珠江三角洲标准舞、拉丁舞公开赛 竞赛规程','111','12',1480953600,1481644800,1481731200,1483027200,1483027200,1481106546,1481106546),(4,'2016年广东省标准舞、拉丁舞年度总决赛 暨第十届珠江三角洲标准舞、拉丁舞公开赛 竞赛规程','1111','12',1481472000,1481817600,1482076800,1482508800,1483113600,1481767631,1481767631);

/*Table structure for table `qo_players` */

DROP TABLE IF EXISTS `qo_players`;

CREATE TABLE `qo_players` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '选手ID',
  `type` tinyint(2) DEFAULT '0' COMMENT '选手类型；0为个人（需实名认证）；1为参队团体(报名团体舞专用)',
  `name` varchar(40) NOT NULL COMMENT '选手姓名；如type为1，则为参赛团体名称',
  `idcard` varchar(20) NOT NULL COMMENT '选手身份证号，如type为1，则生成一个唯一的串号（参考函数）',
  `sex` tinyint(2) DEFAULT '0' COMMENT '选手性别，0为未知，1为男，2为女；如type为1，则此值为0',
  `birthday` date DEFAULT NULL COMMENT '选手生日 YYYY-MM-DD，如type为1，则此值为添加的年月日',
  `created_at` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idcard` (`idcard`),
  KEY `birthday` (`birthday`),
  KEY `sex` (`sex`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='选手表（必须实名认证通过的人才可写入此表）';

/*Data for the table `qo_players` */

/*Table structure for table `qo_user_players` */

DROP TABLE IF EXISTS `qo_user_players`;

CREATE TABLE `qo_user_players` (
  `uid` int(10) NOT NULL DEFAULT '0' COMMENT '所属用户ID',
  `player_id` int(10) NOT NULL DEFAULT '0' COMMENT '选手id',
  `is_self` tinyint(2) DEFAULT '0' COMMENT '选手是否为用户自己、个人用户专用，一个用户该值为1的仅能有一条；0为否，1为是',
  `created_at` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`uid`,`player_id`),
  KEY `is_self` (`is_self`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户选手关联表';

/*Data for the table `qo_user_players` */

/*Table structure for table `qo_users` */

DROP TABLE IF EXISTS `qo_users`;

CREATE TABLE `qo_users` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `username` varchar(40) NOT NULL COMMENT '用户名（若无用户名，随机生成8位）',
  `nickname` varchar(40) DEFAULT '' COMMENT '昵称',
  `mobile` varchar(20) NOT NULL COMMENT '手机号',
  `email` varchar(100) DEFAULT '' COMMENT '邮箱',
  `password` varchar(100) NOT NULL COMMENT '密码',
  `type` tinyint(2) DEFAULT '0' COMMENT '用户类型，0为个人，1为机构',
  `status` tinyint(2) DEFAULT '1' COMMENT '用户是否可用，0为不可用，1为可用',
  `times` int(10) DEFAULT '0' COMMENT '登录次数',
  `team_name` varchar(100) DEFAULT '' COMMENT '代表队名称',
  `team_teacher` varchar(100) DEFAULT '' COMMENT '带队老师名称',
  `team_contact` varchar(100) DEFAULT '' COMMENT '带队老师联系方式',
  `team_qq` varchar(40) DEFAULT '' COMMENT '带队老师QQ',
  `last_login_time` int(10) DEFAULT '0' COMMENT '最后登录时间',
  `last_login_ip` varchar(20) DEFAULT '' COMMENT '最后登录IP',
  `created_ip` varchar(20) DEFAULT '' COMMENT '注册IP',
  `created_at` int(10) NOT NULL DEFAULT '0' COMMENT '创建（注册）时间',
  `updated_at` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `mobile` (`mobile`),
  KEY `email` (`email`),
  KEY `type` (`type`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COMMENT='用户表';

/*Data for the table `qo_users` */

insert  into `qo_users`(`id`,`username`,`nickname`,`mobile`,`email`,`password`,`type`,`status`,`times`,`team_name`,`team_teacher`,`team_contact`,`team_qq`,`last_login_time`,`last_login_ip`,`created_ip`,`created_at`,`updated_at`) values (1,'hedong','何东','18672197927','','e10adc3949ba59abbe56e057f20f883e',0,1,0,'','','','',0,'','',0,0),(2,'hedong12','核对','1867297927','','1b92b1ae3986e82fc179d16c81056ed7',1,1,0,'卡卡','','','',0,'','',0,0),(3,'hed','hh','18672197999','','c8837b23ff8aaa8a2dde915473ce0991',0,1,1481430206,'','','','',0,'','',0,0),(4,'hedong111','123456','18672197925','','e10adc3949ba59abbe56e057f20f883e',0,1,1481430623,'','','','',0,'','',0,0);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
