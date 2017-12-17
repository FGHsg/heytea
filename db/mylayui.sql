/*
Navicat MySQL Data Transfer

Source Server         : 127.0.0.1
Source Server Version : 50617
Source Host           : localhost:3306
Source Database       : mylayui

Target Server Type    : MYSQL
Target Server Version : 50617
File Encoding         : 65001

Date: 2017-11-19 22:22:04
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `admin`
-- ----------------------------
DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_account` varchar(50) NOT NULL DEFAULT '' COMMENT '管理员帐号',
  `admin_password` char(32) NOT NULL DEFAULT '' COMMENT '管理员密码',
  `admin_fullname` varchar(255) DEFAULT NULL COMMENT '管理员姓名',
  `admin_head` varchar(255) DEFAULT NULL COMMENT '管理员头像',
  `admin_phone` char(11) DEFAULT NULL COMMENT '管理员手机号码',
  `one_region_id` int(11) DEFAULT NULL COMMENT '管辖一级区域id',
  `two_region_ids` varchar(255) DEFAULT NULL COMMENT '管辖二级区域id集合   如：1,2,3...',
  `status` tinyint(1) DEFAULT '2' COMMENT '状态   1可用   2禁用  默认2',
  `admin_ticket` char(32) DEFAULT NULL COMMENT '管理员入场券（用于抢登）',
  `last_login_time` int(11) DEFAULT NULL COMMENT '最后登录时间',
  `last_login_ip` varchar(32) DEFAULT NULL COMMENT '最后登录ip',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `admin_account` (`admin_account`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COMMENT='管理员表';

-- ----------------------------
-- Records of admin
-- ----------------------------
INSERT INTO `admin` VALUES ('1', '13414865817', '91c6257687901b38daaf7c81d1b8f867', '阿伟', '\\public\\uploads\\adminHead\\2a95c83a7dd5e94467e18d7cdfe5f334.png', '13414865817', '2', '', '1', 'tIlt8euMqvpAmAQn6k568OOfWNpR4Lv8', '1511068608', '127.0.0.1', '1491144644', '1511070936');
INSERT INTO `admin` VALUES ('10', '15626269165', '7c68bb93a51442612d075e21c68650ce', '盛', '', '15626269165', '0', '', '1', '5dM4X2VjfSjv11Px321jY8VS0eMLQPCg', '1492139245', '127.0.0.1', '1492139236', null);

-- ----------------------------
-- Table structure for `admin_role`
-- ----------------------------
DROP TABLE IF EXISTS `admin_role`;
CREATE TABLE `admin_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) DEFAULT NULL COMMENT '管理员id',
  `role_id` int(11) DEFAULT NULL COMMENT '角色id',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户角色表';

-- ----------------------------
-- Records of admin_role
-- ----------------------------

-- ----------------------------
-- Table structure for `role`
-- ----------------------------
DROP TABLE IF EXISTS `role`;
CREATE TABLE `role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(30) DEFAULT NULL COMMENT '角色名称',
  `rule_ids` varchar(2000) DEFAULT NULL COMMENT '角色权限集合   如1,2,3...',
  `status` tinyint(1) DEFAULT '2' COMMENT '角色状态  1正常    2禁用  默认2',
  `remark` varchar(255) DEFAULT NULL COMMENT '角色注释',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_name` (`role_name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='角色表';

-- ----------------------------
-- Records of role
-- ----------------------------
INSERT INTO `role` VALUES ('1', '普通帐号', '25,26,33,50,51', '1', '部分权限', '1491616063', '1494317574');

-- ----------------------------
-- Table structure for `rule`
-- ----------------------------
DROP TABLE IF EXISTS `rule`;
CREATE TABLE `rule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rule_name` varchar(30) DEFAULT NULL COMMENT '权限名称',
  `rule` varchar(255) DEFAULT NULL COMMENT '权限规则',
  `is_menu` tinyint(1) DEFAULT '2' COMMENT '是否菜单  1是   2否    默认2',
  `parent_id` int(11) DEFAULT NULL COMMENT '父级ID    0一级    非0子级',
  `icon` varchar(100) DEFAULT NULL COMMENT '图标',
  `sort` int(11) DEFAULT NULL COMMENT '排序',
  `status` tinyint(1) DEFAULT '2' COMMENT '状态   1可用    2禁用  默认2',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `rule` (`rule`) USING BTREE,
  KEY `is_menu` (`is_menu`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8 COMMENT='权限规则表';

-- ----------------------------
-- Records of rule
-- ----------------------------
INSERT INTO `rule` VALUES ('1', '开发者模块', 'null', '1', '0', 'fa fa-cogs', '2', '1', '1491274084', null);
INSERT INTO `rule` VALUES ('15', '添加权限', 'system/addRule', '2', '29', '', '2', '1', '1491289892', '1491316960');
INSERT INTO `rule` VALUES ('19', '删除权限', 'system/deleteRule', '2', '29', '', '4', '1', '1491311270', '1491317010');
INSERT INTO `rule` VALUES ('20', '权限排序', 'system/sortRule', '2', '29', '', '5', '1', '1491311307', '1491317016');
INSERT INTO `rule` VALUES ('21', '修改权限', 'system/editRule', '2', '29', '', '3', '1', '1491311794', '1491617447');
INSERT INTO `rule` VALUES ('25', '用户模块', 'null', '1', '0', 'fa fa-user-circle', '1', '1', '1491370403', null);
INSERT INTO `rule` VALUES ('26', '管理员管理', 'system/adminList', '1', '25', '', '1', '1', '1491370469', '1491371578');
INSERT INTO `rule` VALUES ('27', '角色管理', 'system/roleList', '1', '1', '', '2', '1', '1491370500', '1491749059');
INSERT INTO `rule` VALUES ('28', '添加角色', 'system/addRole', '2', '27', '', '2', '1', '1491611720', null);
INSERT INTO `rule` VALUES ('29', '权限管理', 'system/ruleList', '1', '1', '', '1', '1', '1491612179', '1491619969');
INSERT INTO `rule` VALUES ('31', '修改角色', 'system/editRole', '2', '27', '', '3', '1', '1491617401', null);
INSERT INTO `rule` VALUES ('32', '删除角色', 'system/deleteRole', '2', '27', '', '4', '1', '1491617423', null);
INSERT INTO `rule` VALUES ('33', '添加管理员', 'system/addAdmin', '2', '26', '', '2', '1', '1491622839', null);
INSERT INTO `rule` VALUES ('34', '修改管理员', 'system/editAdmin', '2', '26', '', '3', '1', '1491622873', null);
INSERT INTO `rule` VALUES ('35', '删除管理员', 'system/deleteAdmin', '2', '26', '', '5', '1', '1491622895', null);
INSERT INTO `rule` VALUES ('50', '网站设置', 'system/setting', '1', '1', '', '4', '1', '1492156891', '1492158539');
INSERT INTO `rule` VALUES ('51', '修改网站设置', 'system/editSetting', '2', '50', '', '0', '1', '1492156977', '1492158549');
INSERT INTO `rule` VALUES ('57', '自定义菜单', 'system/menuList', '1', '63', '', '3', '1', '1497863852', '1498039690');
INSERT INTO `rule` VALUES ('58', '添加微信菜单', 'system/addMenu', '2', '57', '', '1', '1', '1497922732', '1497937727');
INSERT INTO `rule` VALUES ('59', '修改微信菜单', 'system/editMenu', '2', '57', '', '2', '1', '1497937627', '1497937742');
INSERT INTO `rule` VALUES ('60', '微信菜单排序', 'system/sortMenu', '2', '57', '', '3', '1', '1497937659', '1497937712');
INSERT INTO `rule` VALUES ('61', '删除微信菜单', 'system/deleteMenu', '2', '57', '', '4', '1', '1497937697', null);
INSERT INTO `rule` VALUES ('62', '推送微信菜单', 'system/pushWxMenu', '2', '57', '', '5', '1', '1497938063', '1497938076');
INSERT INTO `rule` VALUES ('63', '微信模块', 'null', '1', '0', 'fa fa-wechat', '3', '1', '1498009097', null);
INSERT INTO `rule` VALUES ('64', '关注公众号', 'system/welcomeSetting', '1', '63', '', '1', '1', '1498039646', '1498119224');

-- ----------------------------
-- Table structure for `setting`
-- ----------------------------
DROP TABLE IF EXISTS `setting`;
CREATE TABLE `setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `website_title` varchar(255) DEFAULT NULL COMMENT '网站标题',
  `copyright` varchar(255) DEFAULT NULL COMMENT '网站版权',
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of setting
-- ----------------------------
INSERT INTO `setting` VALUES ('1', 'LAYUI后台管理', 'Copyright&amp;nbsp;&amp;nbsp;©&amp;nbsp;&amp;nbsp;2017&amp;nbsp;&amp;nbsp;广州墨锋信息科技有限公司&amp;nbsp;&amp;nbsp;技术支持：墨锋团队');

-- ----------------------------
-- Table structure for `wx_menu`
-- ----------------------------
DROP TABLE IF EXISTS `wx_menu`;
CREATE TABLE `wx_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL COMMENT '父级id',
  `button_level` varchar(30) DEFAULT NULL COMMENT '菜单类型   button 一级菜单（最多3个）, sub_button 二级菜单（最多5个）',
  `type` varchar(255) DEFAULT NULL COMMENT '动作类型，view，click，miniprogram',
  `wx_name` varchar(60) DEFAULT NULL COMMENT '菜单标题',
  `key` varchar(128) DEFAULT NULL COMMENT '菜单KEY值，用于消息接口推送',
  `url` varchar(1024) DEFAULT NULL COMMENT 'view、miniprogram类型必须',
  `res_type` varchar(30) DEFAULT NULL COMMENT '自动回复类型   news图文   image图片',
  `media_id` varchar(255) DEFAULT NULL COMMENT '回复内容',
  `imgurl` varchar(255) DEFAULT NULL COMMENT '图片url   响应图片素材的图片',
  `content` varchar(500) DEFAULT NULL COMMENT '回复文本内容',
  `appid` varchar(255) DEFAULT NULL COMMENT 'appid  miniprogram类型必须',
  `pagepath` varchar(255) DEFAULT NULL COMMENT 'pagepath miniprogram类型必须',
  `sort` tinyint(3) DEFAULT NULL COMMENT '排序',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  UNIQUE KEY `id` (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `key` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of wx_menu
-- ----------------------------
INSERT INTO `wx_menu` VALUES ('11', '0', 'button', '', '%E4%B8%AA%E4%BA%BA%E4%B8%AD%E5%BF%83', '', '', '', '', '', null, null, null, '0', '1497939480', null);
INSERT INTO `wx_menu` VALUES ('12', '0', 'button', '', '%E5%85%B3%E4%BA%8E%E6%88%91%E4%BB%AC', '', '', '', '', '', null, null, null, '1', '1497939569', null);
INSERT INTO `wx_menu` VALUES ('13', '12', 'sub_button', 'click', '%E8%81%94%E7%B3%BB%E6%88%91%E4%BB%AC', 'contact us', null, 'news', 'xW9INc8YaFaHo8XJYMxPeiJ210lZ4pPVoIwuCa6yBq8', '', null, null, null, '1', '1497939618', '1497949468');
INSERT INTO `wx_menu` VALUES ('24', '11', 'sub_button', 'click', '%E6%88%91%E7%9A%84%E4%BF%A1%E6%81%AF', 'hehe', 'http://www.baidu.com', 'image', 'xW9INc8YaFaHo8XJYMxPeqqZ8bL8W25xEhizewJM6m0', 'http://mmbiz.qpic.cn/mmbiz_png/pCErWynFe5btIibHz55IaSekhYP4g7mcqZc8AQ0sia4JXOeandnwicJM0ibIm2QmuaiaoPE6SkKxMkSMxNmTYAK0Nzg/0?wx_fmt=png', null, null, null, '3', '1497949986', '1498118869');
INSERT INTO `wx_menu` VALUES ('26', '11', 'sub_button', 'view', '%E6%88%91%E6%98%AF%E9%98%BF%E4%BC%9F', null, 'http://www.baidu.com', null, null, null, null, null, null, '2', '1497953377', null);
INSERT INTO `wx_menu` VALUES ('28', '0', 'button', 'click', '%E5%85%B3%E4%BA%8E%E6%88%91%E4%BB%AC', 'nihao', null, 'text', null, '', '你好00', null, null, '3', '1510478048', '1510478698');

-- ----------------------------
-- Table structure for `wx_welcome`
-- ----------------------------
DROP TABLE IF EXISTS `wx_welcome`;
CREATE TABLE `wx_welcome` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `res_type` varchar(255) DEFAULT NULL COMMENT '关注回复内容类型   text   news   image',
  `content` varchar(1000) DEFAULT NULL COMMENT '回复文本内容',
  `media_id` varchar(255) DEFAULT NULL COMMENT '回复素材id',
  `imgurl` varchar(255) DEFAULT NULL COMMENT '回复图片的url',
  `status` tinyint(1) DEFAULT '2' COMMENT '状态    1启用   2禁用   默认禁用',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of wx_welcome
-- ----------------------------
