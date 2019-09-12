/*
 Navicat Premium Data Transfer

 Source Server         : localhost_3306
 Source Server Type    : MySQL
 Source Server Version : 80015
 Source Host           : localhost:3306
 Source Schema         : 17m

 Target Server Type    : MySQL
 Target Server Version : 80015
 File Encoding         : 65001

 Date: 12/09/2019 10:42:00
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for pro_alert
-- ----------------------------
DROP TABLE IF EXISTS `pro_alert`;
CREATE TABLE `pro_alert`  (
  `orderid` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `telephone` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `content` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `sendtime` datetime(0) NULL DEFAULT NULL,
  `expiretime` datetime(0) NULL DEFAULT NULL,
  `result` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  PRIMARY KEY (`orderid`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for pro_attachment
-- ----------------------------
DROP TABLE IF EXISTS `pro_attachment`;
CREATE TABLE `pro_attachment`  (
  `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` char(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `url` char(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
  `thumb` char(80) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
  `type` tinyint(1) NULL DEFAULT 0,
  `w` mediumint(5) NULL DEFAULT 0,
  `h` mediumint(5) NULL DEFAULT 0,
  `size` int(11) UNSIGNED NULL DEFAULT 0,
  `ext` char(5) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
  `uid` mediumint(8) UNSIGNED NULL DEFAULT 0,
  `createtime` int(11) UNSIGNED NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Fixed;

-- ----------------------------
-- Table structure for pro_auth_group
-- ----------------------------
DROP TABLE IF EXISTS `pro_auth_group`;
CREATE TABLE `pro_auth_group`  (
  `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `plugin_id` mediumint(8) NULL DEFAULT 0,
  `title` char(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `rules` char(80) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `default_register` tinyint(1) NULL DEFAULT 0 COMMENT '新注册用户所在的默认组',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `plugin_id`(`plugin_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 4 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Fixed;

-- ----------------------------
-- Records of pro_auth_group
-- ----------------------------
INSERT INTO `pro_auth_group` VALUES (2, 0, '总店管理员', 1, '1,2,3,4,5,6,7,8,9,10,11,12,13,14,15', 0);
INSERT INTO `pro_auth_group` VALUES (3, 0, '分店管理员', 1, '1,2,3,5,8,11,14,15', 0);

-- ----------------------------
-- Table structure for pro_auth_group_access
-- ----------------------------
DROP TABLE IF EXISTS `pro_auth_group_access`;
CREATE TABLE `pro_auth_group_access`  (
  `uid` mediumint(8) UNSIGNED NOT NULL,
  `group_id` mediumint(8) UNSIGNED NOT NULL,
  UNIQUE INDEX `uid_group_id`(`uid`, `group_id`) USING BTREE,
  INDEX `uid`(`uid`) USING BTREE,
  INDEX `group_id`(`group_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Fixed;

-- ----------------------------
-- Table structure for pro_auth_rule
-- ----------------------------
DROP TABLE IF EXISTS `pro_auth_rule`;
CREATE TABLE `pro_auth_rule`  (
  `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `plugin_id` mediumint(8) NULL DEFAULT 0,
  `name` char(80) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `title` char(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `type` tinyint(1) NOT NULL DEFAULT 1,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `conditions` char(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `p`(`plugin_id`, `name`) USING BTREE,
  INDEX `p1`(`plugin_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 16 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Fixed;

-- ----------------------------
-- Records of pro_auth_rule
-- ----------------------------
INSERT INTO `pro_auth_rule` VALUES (1, 0, 'index', '后台登录', 1, 1, '');
INSERT INTO `pro_auth_rule` VALUES (2, 0, 'order', '订单管理', 1, 1, '');
INSERT INTO `pro_auth_rule` VALUES (3, 0, 'statistic', '统计分析', 1, 1, '');
INSERT INTO `pro_auth_rule` VALUES (4, 0, 'config', '系统配置', 1, 1, '');
INSERT INTO `pro_auth_rule` VALUES (5, 0, 'user', '用户查看', 1, 1, '');
INSERT INTO `pro_auth_rule` VALUES (6, 0, 'city', '城市管理', 1, 1, '');
INSERT INTO `pro_auth_rule` VALUES (7, 0, 'store', '门店管理', 1, 1, '');
INSERT INTO `pro_auth_rule` VALUES (8, 0, 'pool', '档期管理', 1, 1, '');
INSERT INTO `pro_auth_rule` VALUES (9, 0, 'auth', '权限管理', 1, 1, '');
INSERT INTO `pro_auth_rule` VALUES (10, 0, 'category', '套餐管理', 1, 1, '');
INSERT INTO `pro_auth_rule` VALUES (11, 0, 'negative', '底片管理', 1, 1, '');
INSERT INTO `pro_auth_rule` VALUES (12, 0, 'coupon', '优惠券管理', 1, 1, '');
INSERT INTO `pro_auth_rule` VALUES (13, 0, 'user/auth', '用户权限设置', 1, 1, '');
INSERT INTO `pro_auth_rule` VALUES (14, 0, 'exportword', '导出数据', 1, 1, '');
INSERT INTO `pro_auth_rule` VALUES (15, 0, 'comment', '评价管理', 1, 1, '');

-- ----------------------------
-- Table structure for pro_cards
-- ----------------------------
DROP TABLE IF EXISTS `pro_cards`;
CREATE TABLE `pro_cards`  (
  `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` mediumint(8) UNSIGNED NOT NULL,
  `orderid` mediumint(8) UNSIGNED NULL DEFAULT 0 COMMENT '订单表ID',
  `pay` mediumint(8) UNSIGNED NULL DEFAULT 0 COMMENT '支付金额分',
  `coupon` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '优惠码',
  `payway` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '付款方式',
  `ordercode` char(28) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '订单号',
  `paytime` datetime(0) NULL DEFAULT NULL COMMENT '支付时间',
  `mchid` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '商户号',
  `trade_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '支付类型',
  `trade_no` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '交易号',
  `trade_status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '交易状态',
  `refundcode` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '退单号',
  `refundpay` mediumint(8) UNSIGNED NULL DEFAULT 0 COMMENT '退款金额分',
  `refundtime` datetime(0) NULL DEFAULT NULL COMMENT '退款时间',
  `createtime` datetime(0) NULL DEFAULT NULL COMMENT '下单时间',
  `status` tinyint(1) NULL DEFAULT 0 COMMENT '-2退款中-1已退款0未支付1支付成功2支付失败',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `uid`(`uid`) USING BTREE,
  INDEX `orderid`(`orderid`) USING BTREE,
  INDEX `ordercode`(`ordercode`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4661 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for pro_category
-- ----------------------------
DROP TABLE IF EXISTS `pro_category`;
CREATE TABLE `pro_category`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` int(10) UNSIGNED NULL DEFAULT 1 COMMENT '项目ID',
  `type` tinyint(1) UNSIGNED NULL DEFAULT 1 COMMENT '类别',
  `name` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '套餐名称',
  `icon` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '图标',
  `delay` mediumint(8) UNSIGNED NULL DEFAULT 0 COMMENT '预计耗时(秒)',
  `description` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '说明',
  `sort` tinyint(3) UNSIGNED NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 14 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of pro_category
-- ----------------------------
INSERT INTO `pro_category` VALUES (1, 1, 1, '证件照', 'upload/201704/2017040506896264.jpg', 12600, '<section class=\"product-section specifics\">\r\n    <dl>\r\n        <dt><strong>包含项目</strong></dt>\r\n        <dd>\r\n            <ul class=\"service clearfix\">\r\n                <li class=\"service-1\">\r\n                    <i></i>\r\n                    <span>化妆</span>\r\n                    <p>1个妆面造型</p>\r\n                </li>\r\n                <li class=\"service-2\">\r\n                    <i></i>\r\n                    <span>拍摄</span>\r\n                    <p>1种背景颜色</p>\r\n                </li>\r\n                <li class=\"service-3\">\r\n                    <i></i>\r\n                    <span>修图</span>\r\n                    <p>1张精修底片</p>\r\n                </li>\r\n                <li class=\"service-4\">\r\n                    <i></i>\r\n                    <span>冲印</span>\r\n                    <p>1种尺寸冲印</p>\r\n                </li>\r\n            </ul>\r\n            <div style=\"color: #888;\"><strong>服装</strong>：建议自带服装，店内仅提供部分正装和衬衫。</div>\r\n        </dd>\r\n        <dd class=\"clearfix\">\r\n            <dl>\r\n                <dt><strong>预计耗时</strong></dt>\r\n                <dd>约<strong>3</strong>小时</dd>\r\n            </dl>\r\n            <dl>\r\n                <dt><strong>看样时间</strong></dt>\r\n                <dd><i class=\"active-i\"></i>当场看样</dd>\r\n            </dl>\r\n            <dl>\r\n                <dt><strong>取件方式</strong></dt>\r\n                <dd><i class=\"active-i\"></i>当场可取、顺丰到付</dd>\r\n            </dl>\r\n        </dd>\r\n        <dt>\r\n            <strong>用途</strong><br>\r\n            求职简历、各国签证、身份证、护照、居住证、其他证件等。\r\n        </dt>\r\n\r\n        <dt>\r\n            <strong>关于拍摄身份证/护照等照片的说明：</strong><br>\r\n            身份证上传目前支持：贵州省、广东省、江西省 ；<br />\r\n            护照或港澳通行证支持：贵州省、云南省、四川省、重庆市、广东省、广西省、青海省。<br />\r\n            （回执单 ¥20/份）\r\n        </dt>\r\n\r\n\r\n\r\n    </dl>\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n    <a id=\"more\" class=\"more\" style=\"display: none;\"><span>查看更多详情</span></a>\r\n</section><div class=\"product-desc\">\r\n    <style>\r\n        * {\r\n            margin: 0;\r\n            padding: 0;\r\n            outline: 0;\r\n            -webkit-text-size-adjust: none;\r\n            -webkit-tap-highlight-color: rgba(0, 0, 0, 0);\r\n        }\r\n\r\n        html {\r\n            -webkit-text-size-adjust: 100%;\r\n            -ms-text-size-adjust: 100%;\r\n        }\r\n\r\n        body {\r\n            font: 12px/1.5 &#039;\r\n            PingFang SC&#039;\r\n            , Arial, sans-serif;\r\n            color: #888;\r\n            background: #fff;\r\n            min-width: 320px;\r\n            padding: 12px 0;\r\n        }\r\n\r\n        img {\r\n            border: 0;\r\n            max-width: 100%;\r\n        }\r\n\r\n        table {\r\n            border-collapse: collapse;\r\n            border-spacing: 0;\r\n        }\r\n\r\n        ol, ul {\r\n            list-style: none;\r\n        }\r\n\r\n        h1, h2, h3, h4, h5, h6 {\r\n            font-size: 100%;\r\n            font-weight: normal;\r\n        }\r\n\r\n        b, i {\r\n            font-style: normal;\r\n            font-weight: normal;\r\n        }\r\n\r\n        a {\r\n            text-decoration: none;\r\n            color: #000;\r\n        }\r\n\r\n        .clearfix:after {\r\n            content: &#039;\r\n            &#039;\r\n            ;\r\n            display: block;\r\n            clear: both;\r\n        }\r\n\r\n        .top {\r\n            margin: 16px 16px 0;\r\n            background: #1e82d0;\r\n            color: #B2C7EA;\r\n            font-size: 12px;\r\n            font-weight: 700;\r\n            text-align: center;\r\n            padding: 16px 0;\r\n            border-radius: 6px;\r\n        }\r\n\r\n        .head {\r\n            padding: 15px 16px;\r\n            font-size: 12px;\r\n            color: #c8c8c8;\r\n            border-bottom: #E6E6E6 1px solid;\r\n        }\r\n\r\n        h2 {\r\n            padding: 0 16px;\r\n            font-weight: 700;\r\n        }\r\n\r\n        h4 {\r\n            padding: 0 16px;\r\n            font-size: 11px;\r\n        }\r\n\r\n        .box {\r\n            padding: 15px 16px 10px;\r\n        }\r\n\r\n            .box .pic {\r\n                float: left;\r\n                width: 45%;\r\n            }\r\n\r\n                .box .pic img {\r\n                    width: 100%;\r\n                    vertical-align: top;\r\n                }\r\n\r\n            .box dl {\r\n                float: left;\r\n                width: 55%;\r\n            }\r\n\r\n            .box dt {\r\n                padding: 0 0 0 6px;\r\n                line-height: 1;\r\n                font-weight: 700;\r\n            }\r\n\r\n            .box dd {\r\n                padding: 10px 0 0 6px;\r\n                font-size: 11px;\r\n                line-height: 13px;\r\n                text-align: justify;\r\n            }\r\n\r\n        .box-right dt {\r\n            padding: 0 6px 0 0;\r\n            text-align: right;\r\n        }\r\n\r\n        .box-right dd {\r\n            padding: 10px 6px 0 0;\r\n        }\r\n\r\n        .piclist {\r\n            padding: 0 0 0px;\r\n        }\r\n\r\n            .piclist ul {\r\n                padding: 0 12px;\r\n            }\r\n\r\n            .piclist li {\r\n                text-align: center;\r\n                font-size: 11px;\r\n                float: left;\r\n                width: 33.33%;\r\n                box-sizing: border-box;\r\n                -webkit-box-sizing: border-box;\r\n                padding: 0 4px;\r\n            }\r\n\r\n                .piclist li img {\r\n                    width: 100%;\r\n                    vertical-align: top;\r\n                }\r\n\r\n                .piclist li i {\r\n                    display: block;\r\n                    font-size: 11px;\r\n                    text-align: center;\r\n                    padding: 6px 0 0;\r\n                    line-height: 1;\r\n                }\r\n\r\n        .colorlist {\r\n            padding: 0 16px 12px;\r\n        }\r\n\r\n            .colorlist ul {\r\n                margin: 0 -10px;\r\n            }\r\n\r\n            .colorlist li {\r\n                float: left;\r\n                width: 20%;\r\n                font-size: 11px;\r\n                text-align: center;\r\n                box-sizing: border-box;\r\n                -webkit-box-sizing: border-box;\r\n                padding: 12px 10px 0;\r\n            }\r\n\r\n                .colorlist li span {\r\n                    display: block;\r\n                    padding: 100% 0 0;\r\n                }\r\n\r\n                    .colorlist li span.color-1 {\r\n                        background: #A4B8E4;\r\n                    }\r\n\r\n                    .colorlist li span.color-2 {\r\n                        background: #F7E2E5;\r\n                    }\r\n\r\n                    .colorlist li span.color-3 {\r\n                        background: #5E6D72;\r\n                    }\r\n\r\n                    .colorlist li span.color-4 {\r\n                        background: #A8AA9D;\r\n                    }\r\n\r\n                    .colorlist li span.color-5 {\r\n                        background: #9A5151;\r\n                    }\r\n\r\n                    .colorlist li span.color-6 {\r\n                        background: #D99B60;\r\n                    }\r\n\r\n                    .colorlist li span.color-7 {\r\n                        background: #D486A0;\r\n                    }\r\n\r\n                    .colorlist li span.color-8 {\r\n                        background: #8E67AA;\r\n                    }\r\n\r\n                    .colorlist li span.color-9 {\r\n                        background: #037482;\r\n                    }\r\n\r\n                    .colorlist li span.color-10 {\r\n                        background: #284556;\r\n                    }\r\n\r\n                .colorlist li i {\r\n                    display: block;\r\n                    font-size: 11px;\r\n                    padding: 1px 0 0;\r\n                }\r\n\r\n        .data {\r\n            font-size: 11px;\r\n            padding: 0 16px 16px;\r\n        }\r\n\r\n            .data table {\r\n                width: 100%;\r\n                text-align: center;\r\n            }\r\n\r\n            .data th {\r\n                border: #E5E5E5 1px solid;\r\n                padding: 6px 0;\r\n                font-weight: 700;\r\n            }\r\n\r\n            .data td {\r\n                border: #E5E5E5 1px solid;\r\n                padding: 6px 0;\r\n            }\r\n\r\n        .works {\r\n            width: 100%;\r\n            padding: 0 16px;\r\n            text-align: center;\r\n        }\r\n\r\n            .works img {\r\n                max-width: 960px;\r\n                vertical-align: top;\r\n            }\r\n    </style><div class=\"top\">证件照是你的名片，<br>它必须展现你美好的⼀面。<br>贴有照片的简历会更受青睐。<br>它被放置在你的身份证、驾照、签证、员工卡...<br>我们提供规范的仪态标准和⾃然的妆容。</div><h2>拍摄案例</h2>\r\n\r\n    <div class=\"works\"><img src=\"/upload/20160401/14595169874045.jpg?v=6\" _src=\"/upload/20160401/14595169874045.jpg?v=6\"></div>\r\n    <div class=\"head\">本页所用图片均为十七平客户授权使用，侵权必究。</div><h2>服务展示</h2><div class=\"box clearfix\">\r\n        <div class=\"pic\">\r\n            <img src=\"/upload/20160401/1459502883872.jpg?v=6\" _src=\"/upload/20160401/1459502883872.jpg?v=6\">\r\n            &nbsp; &nbsp;\r\n        </div><dl><dt>前台接待</dt><dd>前台客服会询问你的证件照用途，帮你选择合适的底色和尺寸。</dd></dl>\r\n    </div><div class=\"box box-right clearfix\">\r\n        <dl><dt>化妆造型</dt><dd>专业化妆师会根据你的气质和职业属性量身定做妆容和发型，让形容得到提升。</dd></dl><div class=\"pic\">\r\n            <img src=\"/upload/20160401/14595028964763.jpg?v=6\" _src=\"/upload/20160401/14595028964763.jpg?v=6\">\r\n            &nbsp; &nbsp;\r\n        </div>\r\n    </div><div class=\"piclist\"><ul class=\"clearfix\"><li><img src=\"/upload/20160401/14595029207983.jpg?v=6\" _src=\"/upload/20160401/14595029207983.jpg?v=6\"> &nbsp;专属化妆套件</li><li><img src=\"/upload/20160401/14595029299249.jpg?v=6\" _src=\"/upload/20160401/14595029299249.jpg?v=6\"> &nbsp;专业彩妆用品</li><li><img src=\"/upload/20160401/14595029368232.jpg?v=6\" _src=\"/upload/20160401/14595029368232.jpg?v=6\"> &nbsp;衣物贴心保护</li></ul></div><div class=\"box clearfix\">\r\n        <div class=\"pic\">\r\n            <img src=\"/upload/20160401/14595029466294.jpg?v=6\" _src=\"/upload/20160401/14595029466294.jpg?v=6\">\r\n            &nbsp; &nbsp;\r\n        </div><dl><dt>服饰搭配</dt><dd>可提供部分正装，建议你自带服装和配饰(更合身效果更好)，店内也有部分配饰可供选择。</dd></dl>\r\n    </div><div class=\"piclist\"><ul class=\"clearfix\"><li><img src=\"/upload/20160401/14595029553270.jpg?v=6\" _src=\"/upload/20160401/14595029553270.jpg?v=6\"> &nbsp;领带</li><li><img src=\"/upload/20160401/14595029622663.jpg?v=6\" _src=\"/upload/20160401/14595029622663.jpg?v=6\"> &nbsp;领结</li><li><img src=\"/upload/20160401/14595029695946.jpg?v=6\" _src=\"/upload/20160401/14595029695946.jpg?v=6\"> &nbsp;眼镜框</li></ul></div><h2>自带服装推荐颜色</h2><h4>建议选用纯色的衣服(不要太鲜艳)，推荐以下颜色：</h4><div class=\"colorlist\"><ul class=\"clearfix\"><li><span class=\"color-1\"> </span> &nbsp;浅蓝</li><li><span class=\"color-2\"> </span> &nbsp;浅粉</li><li><span class=\"color-3\"> </span> &nbsp;雅灰</li><li><span class=\"color-4\"> </span> &nbsp;卡其</li><li><span class=\"color-5\"> </span> &nbsp;酒红</li><li><span class=\"color-6\"> </span> &nbsp;橡木黄</li><li><span class=\"color-7\"> </span> &nbsp;玫瑰粉</li><li><span class=\"color-8\"> </span> &nbsp;淡紫</li><li><span class=\"color-9\"> </span> &nbsp;蓝绿</li><li><span class=\"color-10\"> </span> &nbsp;蓝灰</li></ul></div><div class=\"box box-right clearfix\">\r\n        <dl><dt>专业拍摄</dt><dd>专业摄影师会协助调整面部神态和姿势，提升精神面貌和抓也形象。请在拍摄过程中与摄影师保持交流。</dd></dl><div class=\"pic\">\r\n            <img src=\"/upload/20160402/1459574199826.jpg?v=6\" _src=\"/upload/20160402/1459574199826.jpg?v=6\">\r\n            &nbsp; &nbsp;\r\n        </div>\r\n    </div><div class=\"box clearfix\">\r\n        <div class=\"pic\">\r\n            <img src=\"/upload/20160401/1459502996870.jpg?v=6\" _src=\"/upload/20160401/1459502996870.jpg?v=6\">\r\n            &nbsp; &nbsp;\r\n        </div><dl><dt>后期修片</dt><dd>资深修图师会对照片进行较色和精修，皮肤上的小瑕疵、色斑、暗疮都会去除。你也可以提前提出自己的个性化要求。</dd></dl>\r\n    </div><div class=\"box box-right clearfix\">\r\n        <dl><dt>冲印裁切</dt><dd>600dpi高精度冲印，进口专业相纸，一丝不苟的裁切，确保照片细节的完美呈现。</dd></dl><div class=\"pic\">\r\n            <img src=\"/upload/20160401/1459503004202.jpg?v=6\" _src=\"/upload/20160401/1459503004202.jpg?v=6\">\r\n            &nbsp; &nbsp;\r\n        </div>\r\n    </div><div class=\"data\"><table><tbody><tr><th colspan=\"9\" style=\"text-align:center\">不同尺寸冲印一版可裁切的照片数量</th></tr><tr><td>一寸</td><td>二寸</td><td>登记</td><td>美签</td><td>日签</td><td>欧签</td><td>身份证</td><td>护照</td><td>驾照</td></tr><tr><td>10张</td><td>8张</td><td>4张</td><td>2张</td><td>4张</td><td>6张</td><td>10张</td><td>6张</td><td>10张</td></tr></tbody></table></div><div class=\"box clearfix\">\r\n        <div class=\"pic\">\r\n            <img src=\"/upload/20160401/14595030167322.jpg?v=6\" _src=\"/upload/20160401/14595030167322.jpg?v=6\">\r\n            &nbsp; &nbsp;\r\n        </div><dl><dt>送达你手中</dt><dd>确认照片后约一刻钟，照片会装袋并送呈到你手中。赶快拍照发朋友圈和微博吧~</dd></dl>\r\n    </div><div class=\"box box-right clearfix\">\r\n        <dl><dt>休息区</dt><dd> 卸下平时疲惫的身心，在17平品一杯咖啡的醇香，享受时光静谧的美好</dd></dl><div class=\"pic\">\r\n            <img src=\"/upload/20160401/14595030244324.jpg?v=6\" _src=\"/upload/20160401/14595030244324.jpg?v=6\">\r\n\r\n            &nbsp; &nbsp;\r\n        </div>\r\n    </div>\r\n</div>', 0);
INSERT INTO `pro_category` VALUES (5, 1, 1, '结婚登记照', 'upload/201703/2017033188550710.png', 12600, '<section class=\"product-section specifics\">\r\n    <dl>\r\n        <dt><strong>包含项目</strong></dt>\r\n        <dd>\r\n            <ul class=\"service clearfix\">\r\n                <li class=\"service-1\">\r\n                    <i></i>\r\n                    <span>化妆</span>\r\n                    <p>1个妆面造型</p>\r\n                </li>\r\n                <li class=\"service-2\">\r\n                    <i></i>\r\n                    <span>拍摄</span>\r\n                    <p>1种背景颜色</p>\r\n                </li>\r\n                <li class=\"service-3\">\r\n                    <i></i>\r\n                    <span>修图</span>\r\n                    <p>1张精修底片</p>\r\n                </li>\r\n                <li class=\"service-4\">\r\n                    <i></i>\r\n                    <span>冲印</span>\r\n                    <p>1种尺寸冲印</p>\r\n                </li>\r\n            </ul>\r\n            <div style=\"color: #888;\"><strong>服装</strong>：建议自带服装，店内仅提供不修身情侣装。</div>\r\n        </dd>\r\n        <dd class=\"clearfix\">\r\n            <dl>\r\n                <dt><strong>预计耗时</strong></dt>\r\n                <dd>约<strong>3</strong>小时</dd>\r\n            </dl>\r\n            <dl>\r\n                <dt><strong>看样时间</strong></dt>\r\n                <dd><i class=\"active-i\"></i>当场看样</dd>\r\n            </dl>\r\n            <dl>\r\n                <dt><strong>取件方式</strong></dt>\r\n                <dd><i class=\"active-i\"></i>当场可取、顺丰到付</dd>\r\n            </dl>\r\n        </dd>\r\n        <dt>\r\n            <strong>用途</strong><br>\r\n            <p>结婚登记、结婚纪念、闺蜜留念等</p>\r\n        </dt>\r\n\r\n\r\n\r\n    </dl>\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n    <a id=\"more\" class=\"more\" style=\"display: none;\"><span>查看更多详情</span></a>\r\n</section><div class=\"product-desc\">\r\n    <style>\r\n        * {\r\n            margin: 0;\r\n            padding: 0;\r\n            outline: 0;\r\n            -webkit-text-size-adjust: none;\r\n            -webkit-tap-highlight-color: rgba(0, 0, 0, 0);\r\n        }\r\n\r\n        html {\r\n            -webkit-text-size-adjust: 100%;\r\n            -ms-text-size-adjust: 100%;\r\n        }\r\n\r\n        body {\r\n            font: 12px/1.5 &#039;\r\n            PingFang SC&#039;\r\n            , Arial, sans-serif;\r\n            color: #888;\r\n            background: #fff;\r\n            min-width: 320px;\r\n            padding: 12px 0;\r\n        }\r\n\r\n        img {\r\n            border: 0;\r\n            max-width: 100%;\r\n        }\r\n\r\n        table {\r\n            border-collapse: collapse;\r\n            border-spacing: 0;\r\n        }\r\n\r\n        ol, ul {\r\n            list-style: none;\r\n        }\r\n\r\n        h1, h2, h3, h4, h5, h6 {\r\n            font-size: 100%;\r\n            font-weight: normal;\r\n        }\r\n\r\n        b, i {\r\n            font-style: normal;\r\n            font-weight: normal;\r\n        }\r\n\r\n        a {\r\n            text-decoration: none;\r\n            color: #000;\r\n        }\r\n\r\n        .clearfix:after {\r\n            content: &#039;\r\n            &#039;\r\n            ;\r\n            display: block;\r\n            clear: both;\r\n        }\r\n\r\n        .top {\r\n            margin: 16px 16px 0;\r\n            background: #1e82d0;\r\n            color: #B2C7EA;\r\n            font-size: 12px;\r\n            font-weight: 700;\r\n            text-align: center;\r\n            padding: 16px 0;\r\n            border-radius: 6px;\r\n        }\r\n\r\n        .head {\r\n            padding: 15px 16px;\r\n            font-size: 12px;\r\n            color: #c8c8c8;\r\n            border-bottom: #E6E6E6 1px solid;\r\n        }\r\n\r\n        h2 {\r\n            padding: 0 16px;\r\n            font-weight: 700;\r\n        }\r\n\r\n        h4 {\r\n            padding: 0 16px;\r\n            font-size: 11px;\r\n        }\r\n\r\n        .box {\r\n            padding: 15px 16px 10px;\r\n        }\r\n\r\n            .box .pic {\r\n                float: left;\r\n                width: 45%;\r\n            }\r\n\r\n                .box .pic img {\r\n                    width: 100%;\r\n                    vertical-align: top;\r\n                }\r\n\r\n            .box dl {\r\n                float: left;\r\n                width: 55%;\r\n            }\r\n\r\n            .box dt {\r\n                padding: 0 0 0 6px;\r\n                line-height: 1;\r\n                font-weight: 700;\r\n            }\r\n\r\n            .box dd {\r\n                padding: 10px 0 0 6px;\r\n                font-size: 11px;\r\n                line-height: 13px;\r\n                text-align: justify;\r\n            }\r\n\r\n        .box-right dt {\r\n            padding: 0 6px 0 0;\r\n            text-align: right;\r\n        }\r\n\r\n        .box-right dd {\r\n            padding: 10px 6px 0 0;\r\n        }\r\n\r\n        .piclist {\r\n            padding: 0 0 0px;\r\n        }\r\n\r\n            .piclist ul {\r\n                padding: 0 12px;\r\n            }\r\n\r\n            .piclist li {\r\n                text-align: center;\r\n                font-size: 11px;\r\n                float: left;\r\n                width: 33.33%;\r\n                box-sizing: border-box;\r\n                -webkit-box-sizing: border-box;\r\n                padding: 0 4px;\r\n            }\r\n\r\n                .piclist li img {\r\n                    width: 100%;\r\n                    vertical-align: top;\r\n                }\r\n\r\n                .piclist li i {\r\n                    display: block;\r\n                    font-size: 11px;\r\n                    text-align: center;\r\n                    padding: 6px 0 0;\r\n                    line-height: 1;\r\n                }\r\n\r\n        .colorlist {\r\n            padding: 0 16px 12px;\r\n        }\r\n\r\n            .colorlist ul {\r\n                margin: 0 -10px;\r\n            }\r\n\r\n            .colorlist li {\r\n                float: left;\r\n                width: 20%;\r\n                font-size: 11px;\r\n                text-align: center;\r\n                box-sizing: border-box;\r\n                -webkit-box-sizing: border-box;\r\n                padding: 12px 10px 0;\r\n            }\r\n\r\n                .colorlist li span {\r\n                    display: block;\r\n                    padding: 100% 0 0;\r\n                }\r\n\r\n                    .colorlist li span.color-1 {\r\n                        background: #A4B8E4;\r\n                    }\r\n\r\n                    .colorlist li span.color-2 {\r\n                        background: #F7E2E5;\r\n                    }\r\n\r\n                    .colorlist li span.color-3 {\r\n                        background: #cccccc;\r\n                    }\r\n\r\n                    .colorlist li span.color-4 {\r\n                        background: #A8AA9D;\r\n                    }\r\n\r\n                    .colorlist li span.color-5 {\r\n                        background: #9A5151;\r\n                    }\r\n\r\n                    .colorlist li span.color-6 {\r\n                        background: #D99B60;\r\n                    }\r\n\r\n                    .colorlist li span.color-7 {\r\n                        background: #D486A0;\r\n                    }\r\n\r\n                    .colorlist li span.color-8 {\r\n                        background: #8E67AA;\r\n                    }\r\n\r\n                    .colorlist li span.color-9 {\r\n                        background: #037482;\r\n                    }\r\n\r\n                    .colorlist li span.color-10 {\r\n                        background: #284556;\r\n                    }\r\n\r\n        colorlist li span.color-11 {\r\n            background: #FFFFFF;\r\n            border: #CCCCCC 1px solid;\r\n        }\r\n\r\n        colorlist li span.color-12 {\r\n            background: #114bf0;\r\n        }\r\n\r\n        .colorlist li i {\r\n            display: block;\r\n            font-size: 11px;\r\n            padding: 1px 0 0;\r\n        }\r\n\r\n        .data {\r\n            font-size: 11px;\r\n            padding: 0 16px 16px;\r\n        }\r\n\r\n            .data table {\r\n                width: 100%;\r\n                text-align: center;\r\n            }\r\n\r\n            .data th {\r\n                border: #E5E5E5 1px solid;\r\n                padding: 6px 0;\r\n                font-weight: 700;\r\n            }\r\n\r\n            .data td {\r\n                border: #E5E5E5 1px solid;\r\n                padding: 6px 0;\r\n            }\r\n\r\n        .works {\r\n            width: 100%;\r\n            padding: 0 16px;\r\n            text-align: center;\r\n        }\r\n\r\n            .works img {\r\n                max-width: 960px;\r\n                vertical-align: top;\r\n            }\r\n    </style><div class=\"top\">同样是象征承诺，<br>登记照的意义绝不亚于任何珠宝。<br>把这张重要的小照⽚交给我们。</div><h2>拍摄案例</h2><div class=\"works\"><img src=\"/upload/20160401/1459517105447.jpg?v=6\" _src=\"/upload/20160401/1459517105447.jpg?v=6\"></div><div class=\"head\">本页所用图片均为十七平客户授权使用，侵权必究。</div><h2>服务展示</h2><div class=\"box clearfix\">\r\n        <div class=\"pic\">\r\n            <img src=\"/upload/20160401/1459502883872.jpg?v=6\" _src=\"/upload/20160401/1459502883872.jpg?v=6\">\r\n            &nbsp; &nbsp;\r\n        </div><dl><dt>前台接待</dt><dd>前台客服会询问你的证件照用途，帮你选择合适的底色和尺寸。</dd></dl>\r\n    </div><div class=\"box box-right clearfix\">\r\n        <dl><dt>化妆造型</dt><dd>专业化妆师会根据你的气质和职业属性量身定做妆容和发型，让形容得到提升。</dd></dl><div class=\"pic\">\r\n            <img src=\"/upload/20160401/14595028964763.jpg?v=6\" _src=\"/upload/20160401/14595028964763.jpg?v=6\">\r\n            &nbsp; &nbsp;\r\n        </div>\r\n    </div><div class=\"piclist\"><ul class=\"clearfix\"><li><img src=\"/upload/20160401/14595029207983.jpg?v=6\" _src=\"/upload/20160401/14595029207983.jpg?v=6\"> &nbsp;专属化妆套件</li><li><img src=\"/upload/20160401/14595029299249.jpg?v=6\" _src=\"/upload/20160401/14595029299249.jpg?v=6\"> &nbsp;专业彩妆用品</li><li><img src=\"/upload/20160401/14595029368232.jpg?v=6\" _src=\"/upload/20160401/14595029368232.jpg?v=6\"> &nbsp;衣物贴心保护</li></ul></div><div class=\"box clearfix\">\r\n        <div class=\"pic\">\r\n            <img src=\"/upload/20160401/14595029466294.jpg?v=6\" _src=\"/upload/20160401/14595029466294.jpg?v=6\">\r\n            &nbsp; &nbsp;\r\n        </div><dl><dt>服饰搭配</dt><dd>可提供部分正装，建议你自带服装和配饰(更合身效果更好)，店内也有部分配饰可供选择。</dd></dl>\r\n    </div><div class=\"piclist\"><ul class=\"clearfix\"><li><img src=\"/upload/20160401/14595029553270.jpg?v=6\" _src=\"/upload/20160401/14595029553270.jpg?v=6\"> &nbsp;领带</li><li><img src=\"/upload/20160401/14595029622663.jpg?v=6\" _src=\"/upload/20160401/14595029622663.jpg?v=6\"> &nbsp;领结</li><li><img src=\"/upload/20160401/14595029695946.jpg?v=6\" _src=\"/upload/20160401/14595029695946.jpg?v=6\"> &nbsp;眼镜框</li></ul></div><h2>自带服装推荐颜色</h2><h4>建议选用纯色的衣服(不要太鲜艳)，推荐以下颜色：</h4><div class=\"colorlist\"><ul class=\"clearfix\"><li><span class=\"color-11\" style=\"border:#CCCCCC 1px solid\"> </span> 白色</li><li><span class=\"color-2\"> </span> 浅粉</li><li><span class=\"color-3\"> </span> 浅灰</li><li><span class=\"color-1\"> </span> 浅蓝</li><li><span class=\"color-12\" style=\"background:#114bf0\"> </span> 宝蓝</li></ul></div><div class=\"box box-right clearfix\">\r\n        <dl><dt>专业拍摄</dt><dd>专业摄影师会协助调整面部神态和姿势，提升精神面貌和抓也形象。请在拍摄过程中与摄影师保持交流。</dd></dl><div class=\"pic\">\r\n            <img src=\"/upload/20160402/1459574199826.jpg?v=6\" _src=\"/upload/20160402/1459574199826.jpg?v=6\" style=\"vertical-align: top; width: 382.938px; line-height: 22.8571px; white-space: normal;\">\r\n            &nbsp; &nbsp;\r\n        </div>\r\n    </div><div class=\"box clearfix\">\r\n        <div class=\"pic\">\r\n            <img src=\"/upload/20160401/1459502996870.jpg?v=6\" _src=\"/upload/20160401/1459502996870.jpg?v=6\">\r\n            &nbsp; &nbsp;\r\n        </div><dl><dt>后期修片</dt><dd>资深修图师会对照片进行较色和精修，皮肤上的小瑕疵、色斑、暗疮都会去除。你也可以提前提出自己的个性化要求。</dd></dl>\r\n    </div><div class=\"box box-right clearfix\">\r\n        <dl><dt>冲印裁切</dt><dd>600dpi高精度冲印，进口专业相纸，一丝不苟的裁切，确保照片细节的完美呈现。</dd></dl><div class=\"pic\">\r\n            <img src=\"/upload/20160401/1459503004202.jpg?v=6\" _src=\"/upload/20160401/1459503004202.jpg?v=6\">\r\n            &nbsp; &nbsp;\r\n        </div>\r\n    </div><div class=\"data\"><table><tbody><tr><th colspan=\"9\" style=\"text-align:center\">不同尺寸冲印一版可裁切的照片数量</th></tr><tr><td>一寸</td><td>二寸</td><td>登记</td><td>美签</td><td>日签</td><td>欧签</td><td>身份证</td><td>护照</td><td>驾照</td></tr><tr><td>10张</td><td>8张</td><td>4张</td><td>2张</td><td>4张</td><td>6张</td><td>10张</td><td>6张</td><td>10张</td></tr></tbody></table></div><div class=\"box clearfix\">\r\n        <div class=\"pic\">\r\n            <img src=\"/upload/20160401/14595030167322.jpg?v=6\" _src=\"/upload/20160401/14595030167322.jpg?v=6\">\r\n            &nbsp; &nbsp;\r\n        </div><dl><dt>送达你手中</dt><dd>确认照片后约一刻钟，照片会装袋并送呈到你手中。赶快拍照发朋友圈和微博吧~</dd></dl>\r\n    </div><div class=\"box box-right clearfix\">\r\n        <dl><dt>休息区</dt><dd>卸下平时疲惫的身心，在17平品一杯咖啡的醇香，享受时光静谧的美好</dd></dl><div class=\"pic\">\r\n            <img src=\"/upload/20160401/14595030244324.jpg?v=6\" _src=\"/upload/20160401/14595030244324.jpg?v=6\">\r\n\r\n            &nbsp; &nbsp;\r\n        </div>\r\n    </div>\r\n</div>', 1);
INSERT INTO `pro_category` VALUES (6, 1, 1, '形象照', 'upload/201703/2017033194514401.png', 12600, '<section class=\"product-section specifics\">\r\n    <dl>\r\n        <dt><strong>包含项目</strong></dt>\r\n        <dd>\r\n            <ul class=\"service clearfix\">\r\n                <li class=\"service-1\">\r\n                    <i></i>\r\n                    <span>化妆</span>\r\n                    <p>1个妆面造型</p>\r\n                </li>\r\n                <li class=\"service-2\">\r\n                    <i></i>\r\n                    <span>拍摄</span>\r\n                    <p>1种背景颜色</p>\r\n                </li>\r\n                <li class=\"service-3\">\r\n                    <i></i>\r\n                    <span>修图</span>\r\n                    <p>1张精修底片</p>\r\n                </li>\r\n                <li class=\"service-4\">\r\n                    <i></i>\r\n                    <span>冲印</span>\r\n                    <p>1张6寸照片</p>\r\n                </li>\r\n            </ul>\r\n            <div style=\"color: #888;\"><strong>服装</strong>：建议自带服装，店内仅提供部分正装和衬衫。</div>\r\n        </dd>\r\n        <dd class=\"clearfix\">\r\n            <dl>\r\n                <dt><strong>预计耗时</strong></dt>\r\n                <dd>约<strong>3</strong>小时</dd>\r\n            </dl>\r\n            <dl>\r\n                <dt><strong>看样时间</strong></dt>\r\n                <dd><i class=\"active-i\"></i>7天邮件看样</dd>\r\n            </dl>\r\n            <dl>\r\n                <dt><strong>取件方式</strong></dt>\r\n                <dd><i class=\"active-i\"></i>顺丰到付</dd>\r\n            </dl>\r\n        </dd>\r\n        <dt>\r\n            <strong>用途</strong><br>\r\n            <p>职业照、个人形象展示、求职简历、工作牌、杂志刊登、个人留念等</p>\r\n        </dt>\r\n\r\n\r\n\r\n    </dl>\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n    <a id=\"more\" class=\"more\" style=\"display: none;\"><span>查看更多详情</span></a>\r\n</section><div class=\"product-desc\">\r\n    <style>\r\n        * {\r\n            margin: 0;\r\n            padding: 0;\r\n            outline: 0;\r\n            -webkit-text-size-adjust: none;\r\n            -webkit-tap-highlight-color: rgba(0, 0, 0, 0);\r\n        }\r\n\r\n        html {\r\n            -webkit-text-size-adjust: 100%;\r\n            -ms-text-size-adjust: 100%;\r\n        }\r\n\r\n        body {\r\n            font: 12px/1.5 &#039;\r\n            PingFang SC&#039;\r\n            , Arial, sans-serif;\r\n            color: #888;\r\n            background: #fff;\r\n            min-width: 320px;\r\n            padding: 12px 0;\r\n        }\r\n\r\n        img {\r\n            border: 0;\r\n            max-width: 100%;\r\n        }\r\n\r\n        table {\r\n            border-collapse: collapse;\r\n            border-spacing: 0;\r\n        }\r\n\r\n        ol, ul {\r\n            list-style: none;\r\n        }\r\n\r\n        h1, h2, h3, h4, h5, h6 {\r\n            font-size: 100%;\r\n            font-weight: normal;\r\n        }\r\n\r\n        b, i {\r\n            font-style: normal;\r\n            font-weight: normal;\r\n        }\r\n\r\n        a {\r\n            text-decoration: none;\r\n            color: #000;\r\n        }\r\n\r\n        .clearfix:after {\r\n            content: &#039;\r\n            &#039;\r\n            ;\r\n            display: block;\r\n            clear: both;\r\n        }\r\n\r\n        .top {\r\n            margin: 16px 16px 0;\r\n            background: #1e82d0;\r\n            color: #B2C7EA;\r\n            font-size: 12px;\r\n            font-weight: 700;\r\n            text-align: center;\r\n            padding: 16px 0;\r\n            border-radius: 6px;\r\n        }\r\n\r\n        .head {\r\n            padding: 15px 16px;\r\n            font-size: 12px;\r\n            color: #c8c8c8;\r\n            border-bottom: #E6E6E6 1px solid;\r\n        }\r\n\r\n        h2 {\r\n            padding: 0 16px;\r\n            font-weight: 700;\r\n        }\r\n\r\n        h4 {\r\n            padding: 0 16px;\r\n            font-size: 11px;\r\n        }\r\n\r\n        .box {\r\n            padding: 15px 16px 10px;\r\n        }\r\n\r\n            .box .pic {\r\n                float: left;\r\n                width: 45%;\r\n            }\r\n\r\n                .box .pic img {\r\n                    width: 100%;\r\n                    vertical-align: top;\r\n                }\r\n\r\n            .box dl {\r\n                float: left;\r\n                width: 55%;\r\n            }\r\n\r\n            .box dt {\r\n                padding: 0 0 0 6px;\r\n                line-height: 1;\r\n                font-weight: 700;\r\n            }\r\n\r\n            .box dd {\r\n                padding: 10px 0 0 6px;\r\n                font-size: 11px;\r\n                line-height: 13px;\r\n                text-align: justify;\r\n            }\r\n\r\n        .box-right dt {\r\n            padding: 0 6px 0 0;\r\n            text-align: right;\r\n        }\r\n\r\n        .box-right dd {\r\n            padding: 10px 6px 0 0;\r\n        }\r\n\r\n        .piclist {\r\n            padding: 0 0 0px;\r\n        }\r\n\r\n            .piclist ul {\r\n                padding: 0 12px;\r\n            }\r\n\r\n            .piclist li {\r\n                text-align: center;\r\n                font-size: 11px;\r\n                float: left;\r\n                width: 33.33%;\r\n                box-sizing: border-box;\r\n                -webkit-box-sizing: border-box;\r\n                padding: 0 4px;\r\n            }\r\n\r\n                .piclist li img {\r\n                    width: 100%;\r\n                    vertical-align: top;\r\n                }\r\n\r\n                .piclist li i {\r\n                    display: block;\r\n                    font-size: 11px;\r\n                    text-align: center;\r\n                    padding: 6px 0 0;\r\n                    line-height: 1;\r\n                }\r\n\r\n        .colorlist {\r\n            padding: 0 16px 12px;\r\n        }\r\n\r\n            .colorlist ul {\r\n                margin: 0 -10px;\r\n            }\r\n\r\n            .colorlist li {\r\n                float: left;\r\n                width: 20%;\r\n                font-size: 11px;\r\n                text-align: center;\r\n                box-sizing: border-box;\r\n                -webkit-box-sizing: border-box;\r\n                padding: 12px 10px 0;\r\n            }\r\n\r\n                .colorlist li span {\r\n                    display: block;\r\n                    padding: 100% 0 0;\r\n                }\r\n\r\n                    .colorlist li span.color-1 {\r\n                        background: #A4B8E4;\r\n                    }\r\n\r\n                    .colorlist li span.color-2 {\r\n                        background: #F7E2E5;\r\n                    }\r\n\r\n                    .colorlist li span.color-3 {\r\n                        background: #5E6D72;\r\n                    }\r\n\r\n                    .colorlist li span.color-4 {\r\n                        background: #A8AA9D;\r\n                    }\r\n\r\n                    .colorlist li span.color-5 {\r\n                        background: #9A5151;\r\n                    }\r\n\r\n                    .colorlist li span.color-6 {\r\n                        background: #D99B60;\r\n                    }\r\n\r\n                    .colorlist li span.color-7 {\r\n                        background: #D486A0;\r\n                    }\r\n\r\n                    .colorlist li span.color-8 {\r\n                        background: #8E67AA;\r\n                    }\r\n\r\n                    .colorlist li span.color-9 {\r\n                        background: #037482;\r\n                    }\r\n\r\n                    .colorlist li span.color-10 {\r\n                        background: #284556;\r\n                    }\r\n\r\n                .colorlist li i {\r\n                    display: block;\r\n                    font-size: 11px;\r\n                    padding: 1px 0 0;\r\n                }\r\n\r\n        .data {\r\n            font-size: 11px;\r\n            padding: 0 16px 16px;\r\n        }\r\n\r\n            .data table {\r\n                width: 100%;\r\n                text-align: center;\r\n            }\r\n\r\n            .data th {\r\n                border: #E5E5E5 1px solid;\r\n                padding: 6px 0;\r\n                font-weight: 700;\r\n            }\r\n\r\n            .data td {\r\n                border: #E5E5E5 1px solid;\r\n                padding: 6px 0;\r\n            }\r\n\r\n        .works {\r\n            width: 100%;\r\n            padding: 0 16px;\r\n            text-align: center;\r\n        }\r\n\r\n            .works img {\r\n                max-width: 960px;\r\n                vertical-align: top;\r\n            }\r\n    </style><div class=\"top\">空乘、艺人、模特、相亲者的必选，<br>充分展现你的动人形态。</div><h2>拍摄案例</h2><div class=\"works\"><img src=\"/upload/20160401/14595171541312.jpg?v=6\" _src=\"/upload/20160401/14595171541312.jpg?v=6\"></div><div class=\"head\">本页所用图片均为十七平客户授权使用，侵权必究。</div><h2>服务展示</h2><div class=\"box clearfix\">\r\n        <div class=\"pic\">\r\n            <img src=\"/upload/20160401/1459502883872.jpg?v=6\" _src=\"/upload/20160401/1459502883872.jpg?v=6\">\r\n            &nbsp; &nbsp;\r\n        </div><dl><dt>前台接待</dt><dd>前台客服会询问你的证件照用途，帮你选择合适的底色和尺寸。</dd></dl>\r\n    </div><div class=\"box box-right clearfix\">\r\n        <dl><dt>化妆造型</dt><dd>专业化妆师会根据你的气质和职业属性量身定做妆容和发型，让形容得到提升。</dd></dl><div class=\"pic\">\r\n            <img src=\"/upload/20160401/14595028964763.jpg?v=6\" _src=\"/upload/20160401/14595028964763.jpg?v=6\">\r\n            &nbsp; &nbsp;\r\n        </div>\r\n    </div><div class=\"piclist\"><ul class=\"clearfix\"><li><img src=\"/upload/20160401/14595029207983.jpg?v=6\" _src=\"/upload/20160401/14595029207983.jpg?v=6\"> &nbsp;专属化妆套件</li><li><img src=\"/upload/20160401/14595029299249.jpg?v=6\" _src=\"/upload/20160401/14595029299249.jpg?v=6\"> &nbsp;专业彩妆用品</li><li><img src=\"/upload/20160401/14595029368232.jpg?v=6\" _src=\"/upload/20160401/14595029368232.jpg?v=6\"> &nbsp;衣物贴心保护</li></ul></div><div class=\"box clearfix\">\r\n        <div class=\"pic\">\r\n            <img src=\"/upload/20160401/14595029466294.jpg?v=6\" _src=\"/upload/20160401/14595029466294.jpg?v=6\">\r\n            &nbsp; &nbsp;\r\n        </div><dl><dt>服饰搭配</dt><dd>可提供部分正装，建议你自带服装和配饰(更合身效果更好)，店内也有部分配饰可供选择。</dd></dl>\r\n    </div><div class=\"piclist\"><ul class=\"clearfix\"><li><img src=\"/upload/20160401/14595029553270.jpg?v=6\" _src=\"/upload/20160401/14595029553270.jpg?v=6\"> &nbsp;领带</li><li><img src=\"/upload/20160401/14595029622663.jpg?v=6\" _src=\"/upload/20160401/14595029622663.jpg?v=6\"> &nbsp;领结</li><li><img src=\"/upload/20160401/14595029695946.jpg?v=6\" _src=\"/upload/20160401/14595029695946.jpg?v=6\"> &nbsp;眼镜框</li></ul></div><h2>自带服装推荐颜色</h2><h4>建议选用纯色的衣服(不要太鲜艳)，推荐以下颜色：</h4><div class=\"colorlist\"><ul class=\"clearfix\"><li><span class=\"color-1\"> </span> &nbsp;浅蓝</li><li><span class=\"color-2\"> </span> &nbsp;浅粉</li><li><span class=\"color-3\"> </span> &nbsp;雅灰</li><li><span class=\"color-4\"> </span> &nbsp;卡其</li><li><span class=\"color-5\"> </span> &nbsp;酒红</li><li><span class=\"color-6\"> </span> &nbsp;橡木黄</li><li><span class=\"color-7\"> </span> &nbsp;玫瑰粉</li><li><span class=\"color-8\"> </span> &nbsp;淡紫</li><li><span class=\"color-9\"> </span> &nbsp;蓝绿</li><li><span class=\"color-10\"> </span> &nbsp;蓝灰</li></ul></div><div class=\"box box-right clearfix\">\r\n        <dl><dt>专业拍摄</dt><dd>专业摄影师会协助调整面部神态和姿势，提升精神面貌和抓也形象。请在拍摄过程中与摄影师保持交流。</dd></dl><div class=\"pic\">\r\n            <img src=\"/upload/20160402/1459574199826.jpg?v=6\" _src=\"/upload/20160402/1459574199826.jpg?v=6\" style=\"vertical-align: top; width: 382.938px; line-height: 22.8571px; white-space: normal;\">\r\n            &nbsp; &nbsp;\r\n        </div>\r\n    </div><div class=\"box clearfix\">\r\n        <div class=\"pic\">\r\n            <img src=\"/upload/20160401/1459502996870.jpg?v=6\" _src=\"/upload/20160401/1459502996870.jpg?v=6\">\r\n            &nbsp; &nbsp;\r\n        </div><dl><dt>后期修片</dt><dd>资深修图师会对照片进行较色和精修，皮肤上的小瑕疵、色斑、暗疮都会去除。你也可以提前提出自己的个性化要求。</dd></dl>\r\n    </div><div class=\"box box-right clearfix\">\r\n        <dl><dt>冲印裁切</dt><dd>600dpi高精度冲印，进口专业相纸，一丝不苟的裁切，确保照片细节的完美呈现。</dd></dl><div class=\"pic\">\r\n            <img src=\"/upload/20160401/1459503004202.jpg?v=6\" _src=\"/upload/20160401/1459503004202.jpg?v=6\">\r\n            &nbsp; &nbsp;\r\n        </div>\r\n    </div><div class=\"data\"><table><tbody><tr><th colspan=\"9\" style=\"text-align:center\">不同尺寸冲印一版可裁切的照片数量</th></tr><tr><td>一寸</td><td>二寸</td><td>登记</td><td>美签</td><td>日签</td><td>欧签</td><td>身份证</td><td>护照</td><td>驾照</td></tr><tr><td>10张</td><td>8张</td><td>4张</td><td>2张</td><td>4张</td><td>6张</td><td>10张</td><td>6张</td><td>10张</td></tr></tbody></table></div><div class=\"box clearfix\">\r\n        <div class=\"pic\">\r\n            <img src=\"/upload/20160401/14595030167322.jpg?v=6\" _src=\"/upload/20160401/14595030167322.jpg?v=6\">\r\n            &nbsp; &nbsp;\r\n        </div><dl><dt>送达你手中</dt><dd>确认照片后约一刻钟，照片会装袋并送呈到你手中。赶快拍照发朋友圈和微博吧~</dd></dl>\r\n    </div><div class=\"box box-right clearfix\">\r\n        <dl><dt>休息区</dt><dd>卸下平时疲惫的身心，在17平品一杯咖啡的醇香，享受时光静谧的美好</dd></dl><div class=\"pic\">\r\n            <img src=\"/upload/20160401/14595030244324.jpg?v=6\" _src=\"/upload/20160401/14595030244324.jpg?v=6\">\r\n\r\n            &nbsp; &nbsp;\r\n        </div>\r\n    </div>\r\n</div>', 2);
INSERT INTO `pro_category` VALUES (7, 1, 1, '创意表情照', 'upload/201703/2017033105581700.png', 12600, '<section class=\"product-section specifics\">\r\n    <dl>\r\n        <dt><strong>包含项目</strong></dt>\r\n        <dd>\r\n            <ul class=\"service clearfix\">\r\n                <li class=\"service-1\">\r\n                    <i></i>\r\n                    <span>化妆</span>\r\n                    <p>1个妆面造型</p>\r\n                </li>\r\n                <li class=\"service-2\">\r\n                    <i></i>\r\n                    <span>拍摄</span>\r\n                    <p>1种背景颜色</p>\r\n                </li>\r\n                <li class=\"service-3\">\r\n                    <i></i>\r\n                    <span>修图</span>\r\n                    <p>1张精修底片</p>\r\n                </li>\r\n                <li class=\"service-4\">\r\n                    <i></i>\r\n                    <span>冲印</span>\r\n                    <p>1张6寸照片</p>\r\n                </li>\r\n            </ul>\r\n            <div style=\"color: #888;\"><strong>服装</strong>：建议自带服装，店内仅提供部分正装和衬衫。</div>\r\n        </dd>\r\n        <dd class=\"clearfix\">\r\n            <dl>\r\n                <dt><strong>预计耗时</strong></dt>\r\n                <dd>约<strong>3</strong>小时</dd>\r\n            </dl>\r\n            <dl>\r\n                <dt><strong>看样时间</strong></dt>\r\n                <dd><i class=\"active-i\"></i>2天邮件看样</dd>\r\n            </dl>\r\n            <dl>\r\n                <dt><strong>取件方式</strong></dt>\r\n                <dd><i class=\"active-i\"></i>顺丰到付</dd>\r\n            </dl>\r\n        </dd>\r\n        <dt>\r\n            <strong>用途</strong><br>\r\n            <p>微博、微信头像，个人留念等</p>\r\n        </dt>\r\n\r\n\r\n\r\n    </dl>\r\n\r\n\r\n\r\n\r\n\r\n\r\n    <a id=\"more\" class=\"more\" style=\"display: none;\"><span>查看更多详情</span></a>\r\n</section><div class=\"product-desc\">\r\n    <style>\r\n        * {\r\n            margin: 0;\r\n            padding: 0;\r\n            outline: 0;\r\n            -webkit-text-size-adjust: none;\r\n            -webkit-tap-highlight-color: rgba(0, 0, 0, 0);\r\n        }\r\n\r\n        html {\r\n            -webkit-text-size-adjust: 100%;\r\n            -ms-text-size-adjust: 100%;\r\n        }\r\n\r\n        body {\r\n            font: 12px/1.5 &#039;\r\n            PingFang SC&#039;\r\n            , Arial, sans-serif;\r\n            color: #888;\r\n            background: #fff;\r\n            min-width: 320px;\r\n            padding: 12px 0;\r\n        }\r\n\r\n        img {\r\n            border: 0;\r\n            max-width: 100%;\r\n        }\r\n\r\n        table {\r\n            border-collapse: collapse;\r\n            border-spacing: 0;\r\n        }\r\n\r\n        ol, ul {\r\n            list-style: none;\r\n        }\r\n\r\n        h1, h2, h3, h4, h5, h6 {\r\n            font-size: 100%;\r\n            font-weight: normal;\r\n        }\r\n\r\n        b, i {\r\n            font-style: normal;\r\n            font-weight: normal;\r\n        }\r\n\r\n        a {\r\n            text-decoration: none;\r\n            color: #000;\r\n        }\r\n\r\n        .clearfix:after {\r\n            content: &#039;\r\n            &#039;\r\n            ;\r\n            display: block;\r\n            clear: both;\r\n        }\r\n\r\n        .top {\r\n            margin: 16px 16px 0;\r\n            background: #1e82d0;\r\n            color: #B2C7EA;\r\n            font-size: 12px;\r\n            font-weight: 700;\r\n            text-align: center;\r\n            padding: 16px 0;\r\n            border-radius: 6px;\r\n        }\r\n\r\n        .head {\r\n            padding: 15px 16px;\r\n            font-size: 12px;\r\n            color: #c8c8c8;\r\n            border-bottom: #E6E6E6 1px solid;\r\n        }\r\n\r\n        h2 {\r\n            padding: 0 16px;\r\n            font-weight: 700;\r\n        }\r\n\r\n        h4 {\r\n            padding: 0 16px;\r\n            font-size: 11px;\r\n        }\r\n\r\n        .box {\r\n            padding: 15px 16px 10px;\r\n        }\r\n\r\n            .box .pic {\r\n                float: left;\r\n                width: 45%;\r\n            }\r\n\r\n                .box .pic img {\r\n                    width: 100%;\r\n                    vertical-align: top;\r\n                }\r\n\r\n            .box dl {\r\n                float: left;\r\n                width: 55%;\r\n            }\r\n\r\n            .box dt {\r\n                padding: 0 0 0 6px;\r\n                line-height: 1;\r\n                font-weight: 700;\r\n            }\r\n\r\n            .box dd {\r\n                padding: 10px 0 0 6px;\r\n                font-size: 11px;\r\n                line-height: 13px;\r\n                text-align: justify;\r\n            }\r\n\r\n        .box-right dt {\r\n            padding: 0 6px 0 0;\r\n            text-align: right;\r\n        }\r\n\r\n        .box-right dd {\r\n            padding: 10px 6px 0 0;\r\n        }\r\n\r\n        .piclist {\r\n            padding: 0 0 0px;\r\n        }\r\n\r\n            .piclist ul {\r\n                padding: 0 12px;\r\n            }\r\n\r\n            .piclist li {\r\n                text-align: center;\r\n                font-size: 11px;\r\n                float: left;\r\n                width: 33.33%;\r\n                box-sizing: border-box;\r\n                -webkit-box-sizing: border-box;\r\n                padding: 0 4px;\r\n            }\r\n\r\n                .piclist li img {\r\n                    width: 100%;\r\n                    vertical-align: top;\r\n                }\r\n\r\n                .piclist li i {\r\n                    display: block;\r\n                    font-size: 11px;\r\n                    text-align: center;\r\n                    padding: 6px 0 0;\r\n                    line-height: 1;\r\n                }\r\n\r\n        .colorlist {\r\n            padding: 0 16px 12px;\r\n        }\r\n\r\n            .colorlist ul {\r\n                margin: 0 -10px;\r\n            }\r\n\r\n            .colorlist li {\r\n                float: left;\r\n                width: 20%;\r\n                font-size: 11px;\r\n                text-align: center;\r\n                box-sizing: border-box;\r\n                -webkit-box-sizing: border-box;\r\n                padding: 12px 10px 0;\r\n            }\r\n\r\n                .colorlist li span {\r\n                    display: block;\r\n                    padding: 100% 0 0;\r\n                }\r\n\r\n                    .colorlist li span.color-1 {\r\n                        background: #A4B8E4;\r\n                    }\r\n\r\n                    .colorlist li span.color-2 {\r\n                        background: #F7E2E5;\r\n                    }\r\n\r\n                    .colorlist li span.color-3 {\r\n                        background: #5E6D72;\r\n                    }\r\n\r\n                    .colorlist li span.color-4 {\r\n                        background: #A8AA9D;\r\n                    }\r\n\r\n                    .colorlist li span.color-5 {\r\n                        background: #9A5151;\r\n                    }\r\n\r\n                    .colorlist li span.color-6 {\r\n                        background: #D99B60;\r\n                    }\r\n\r\n                    .colorlist li span.color-7 {\r\n                        background: #D486A0;\r\n                    }\r\n\r\n                    .colorlist li span.color-8 {\r\n                        background: #8E67AA;\r\n                    }\r\n\r\n                    .colorlist li span.color-9 {\r\n                        background: #037482;\r\n                    }\r\n\r\n                    .colorlist li span.color-10 {\r\n                        background: #284556;\r\n                    }\r\n\r\n                .colorlist li i {\r\n                    display: block;\r\n                    font-size: 11px;\r\n                    padding: 1px 0 0;\r\n                }\r\n\r\n        .data {\r\n            font-size: 11px;\r\n            padding: 0 16px 16px;\r\n        }\r\n\r\n            .data table {\r\n                width: 100%;\r\n                text-align: center;\r\n            }\r\n\r\n            .data th {\r\n                border: #E5E5E5 1px solid;\r\n                padding: 6px 0;\r\n                font-weight: 700;\r\n            }\r\n\r\n            .data td {\r\n                border: #E5E5E5 1px solid;\r\n                padding: 6px 0;\r\n            }\r\n\r\n        .works {\r\n            width: 100%;\r\n            padding: 0 16px;\r\n            text-align: center;\r\n        }\r\n\r\n            .works img {\r\n                max-width: 960px;\r\n                vertical-align: top;\r\n            }\r\n    </style><div class=\"top\">创意表情，留住不同寻常的自己</div><h2>拍摄案例</h2><div class=\"works\"><img src=\"/upload/20160401/1459517196629.jpg?v=6\" _src=\"/upload/20160401/1459517196629.jpg?v=6\"></div><div class=\"head\">本页所用图片均为十七平客户授权使用，侵权必究。</div><h2>服务展示</h2><div class=\"box clearfix\">\r\n        <div class=\"pic\">\r\n            <img src=\"/upload/20160401/1459502883872.jpg?v=6\" _src=\"/upload/20160401/1459502883872.jpg?v=6\">\r\n            &nbsp; &nbsp;\r\n        </div><dl><dt>前台接待</dt><dd>前台客服会询问你的证件照用途，帮你选择合适的底色和尺寸。</dd></dl>\r\n    </div><div class=\"box box-right clearfix\">\r\n        <dl><dt>化妆造型</dt><dd>专业化妆师会根据你的气质和职业属性量身定做妆容和发型，让形容得到提升。</dd></dl><div class=\"pic\">\r\n            <img src=\"/upload/20160401/14595028964763.jpg?v=6\" _src=\"/upload/20160401/14595028964763.jpg?v=6\">\r\n            &nbsp; &nbsp;\r\n        </div>\r\n    </div><div class=\"piclist\"><ul class=\"clearfix\"><li><img src=\"/upload/20160401/14595029207983.jpg?v=6\" _src=\"/upload/20160401/14595029207983.jpg?v=6\"> &nbsp;专属化妆套件</li><li><img src=\"/upload/20160401/14595029299249.jpg?v=6\" _src=\"/upload/20160401/14595029299249.jpg?v=6\"> &nbsp;专业彩妆用品</li><li><img src=\"/upload/20160401/14595029368232.jpg?v=6\" _src=\"/upload/20160401/14595029368232.jpg?v=6\"> &nbsp;衣物贴心保护</li></ul></div><div class=\"box clearfix\">\r\n        <div class=\"pic\">\r\n            <img src=\"/upload/20160401/14595029466294.jpg?v=6\" _src=\"/upload/20160401/14595029466294.jpg?v=6\">\r\n            &nbsp; &nbsp;\r\n        </div><dl><dt>服饰搭配</dt><dd>可提供部分正装，建议你自带服装和配饰(更合身效果更好)，店内也有部分配饰可供选择。</dd></dl>\r\n    </div><div class=\"piclist\"><ul class=\"clearfix\"><li><img src=\"/upload/20160401/14595029553270.jpg?v=6\" _src=\"/upload/20160401/14595029553270.jpg?v=6\"> &nbsp;领带</li><li><img src=\"/upload/20160401/14595029622663.jpg?v=6\" _src=\"/upload/20160401/14595029622663.jpg?v=6\"> &nbsp;领结</li><li><img src=\"/upload/20160401/14595029695946.jpg?v=6\" _src=\"/upload/20160401/14595029695946.jpg?v=6\"> &nbsp;眼镜框</li></ul></div><h2>自带服装推荐颜色</h2><h4>建议选用纯色的衣服(不要太鲜艳)，推荐以下颜色：</h4><div class=\"colorlist\"><ul class=\"clearfix\"><li><span class=\"color-1\"> </span> &nbsp;浅蓝</li><li><span class=\"color-2\"> </span> &nbsp;浅粉</li><li><span class=\"color-3\"> </span> &nbsp;雅灰</li><li><span class=\"color-4\"> </span> &nbsp;卡其</li><li><span class=\"color-5\"> </span> &nbsp;酒红</li><li><span class=\"color-6\"> </span> &nbsp;橡木黄</li><li><span class=\"color-7\"> </span> &nbsp;玫瑰粉</li><li><span class=\"color-8\"> </span> &nbsp;淡紫</li><li><span class=\"color-9\"> </span> &nbsp;蓝绿</li><li><span class=\"color-10\"> </span> &nbsp;蓝灰</li></ul></div><div class=\"box box-right clearfix\">\r\n        <dl><dt>专业拍摄</dt><dd>专业摄影师会协助调整面部神态和姿势，提升精神面貌和抓也形象。请在拍摄过程中与摄影师保持交流。</dd></dl><div class=\"pic\">\r\n            <img src=\"/upload/20160402/1459574199826.jpg?v=6\" _src=\"/upload/20160402/1459574199826.jpg?v=6\" style=\"vertical-align: top; width: 382.938px; line-height: 22.8571px; white-space: normal;\">\r\n            &nbsp; &nbsp;\r\n        </div>\r\n    </div><div class=\"box clearfix\">\r\n        <div class=\"pic\">\r\n            <img src=\"/upload/20160401/1459502996870.jpg?v=6\" _src=\"/upload/20160401/1459502996870.jpg?v=6\">\r\n            &nbsp; &nbsp;\r\n        </div><dl><dt>后期修片</dt><dd>资深修图师会对照片进行较色和精修，皮肤上的小瑕疵、色斑、暗疮都会去除。你也可以提前提出自己的个性化要求。</dd></dl>\r\n    </div><div class=\"box box-right clearfix\">\r\n        <dl><dt>冲印裁切</dt><dd>600dpi高精度冲印，进口专业相纸，一丝不苟的裁切，确保照片细节的完美呈现。</dd></dl><div class=\"pic\">\r\n            <img src=\"/upload/20160401/1459503004202.jpg?v=6\" _src=\"/upload/20160401/1459503004202.jpg?v=6\">\r\n            &nbsp; &nbsp;\r\n        </div>\r\n    </div><div class=\"data\"><table><tbody><tr><th colspan=\"9\" style=\"text-align:center\">不同尺寸冲印一版可裁切的照片数量</th></tr><tr><td>一寸</td><td>二寸</td><td>登记</td><td>美签</td><td>日签</td><td>欧签</td><td>身份证</td><td>护照</td><td>驾照</td></tr><tr><td>10张</td><td>8张</td><td>4张</td><td>2张</td><td>4张</td><td>6张</td><td>10张</td><td>6张</td><td>10张</td></tr></tbody></table></div><div class=\"box clearfix\">\r\n        <div class=\"pic\">\r\n            <img src=\"/upload/20160401/14595030167322.jpg?v=6\" _src=\"/upload/20160401/14595030167322.jpg?v=6\">\r\n            &nbsp; &nbsp;\r\n        </div><dl><dt>送达你手中</dt><dd>确认照片后约一刻钟，照片会装袋并送呈到你手中。赶快拍照发朋友圈和微博吧~</dd></dl>\r\n    </div><div class=\"box box-right clearfix\">\r\n        <dl><dt>休息区</dt><dd>卸下平时疲惫的身心，在17平品一杯咖啡的醇香，享受时光静谧的美好</dd></dl><div class=\"pic\">\r\n            <img src=\"/upload/20160401/14595030244324.jpg?v=6\" _src=\"/upload/20160401/14595030244324.jpg?v=6\">\r\n\r\n            &nbsp; &nbsp;\r\n        </div>\r\n    </div>\r\n</div>', 3);
INSERT INTO `pro_category` VALUES (8, 1, 1, '全家福', 'upload/201703/2017033168651944.png', 12600, '<section class=\"product-section specifics\">\r\n    <dl>\r\n\r\n        <dt>全家福默认3人价格；3人以上需加收￥50/人。</dt>\r\n\r\n        <dt><strong>包含项目</strong></dt>\r\n        <dd>\r\n            <ul class=\"service clearfix\">\r\n                <li class=\"service-1\">\r\n                    <i></i>\r\n                    <span>化妆</span>\r\n                    <p>1个妆面造型</p>\r\n                </li>\r\n                <li class=\"service-2\">\r\n                    <i></i>\r\n                    <span>拍摄</span>\r\n                    <p>1种背景颜色</p>\r\n                </li>\r\n                <li class=\"service-3\">\r\n                    <i></i>\r\n                    <span>修图</span>\r\n                    <p>1张精修底片</p>\r\n                </li>\r\n                <li class=\"service-4\">\r\n                    <i></i>\r\n                    <span>冲印</span>\r\n                    <p>1张12寸照片</p>\r\n                </li>\r\n            </ul>\r\n            <div style=\"color: #888;\"><strong>服装</strong>：需自带服装。</div>\r\n        </dd>\r\n        <dd class=\"clearfix\">\r\n            <dl>\r\n                <dt><strong>预计耗时</strong></dt>\r\n                <dd>约<strong>3</strong>小时</dd>\r\n            </dl>\r\n            <dl>\r\n                <dt><strong>看样时间</strong></dt>\r\n                <dd><i class=\"active-i\"></i>10天邮件看样</dd>\r\n            </dl>\r\n            <dl>\r\n                <dt><strong>取件方式</strong></dt>\r\n                <dd><i class=\"active-i\"></i>顺丰到付</dd>\r\n            </dl>\r\n        </dd>\r\n        <dt>\r\n            <strong>用途</strong><br>\r\n            <p>家族留念、多人合影、携宠物留念等</p>\r\n        </dt>\r\n\r\n\r\n\r\n    </dl>\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n    <a id=\"more\" class=\"more\" style=\"display: none;\"><span>查看更多详情</span></a>\r\n</section><div class=\"product-desc\">\r\n    <style>\r\n        * {\r\n            margin: 0;\r\n            padding: 0;\r\n            outline: 0;\r\n            -webkit-text-size-adjust: none;\r\n            -webkit-tap-highlight-color: rgba(0, 0, 0, 0);\r\n        }\r\n\r\n        html {\r\n            -webkit-text-size-adjust: 100%;\r\n            -ms-text-size-adjust: 100%;\r\n        }\r\n\r\n        body {\r\n            font: 12px/1.5 &#039;\r\n            PingFang SC&#039;\r\n            , Arial, sans-serif;\r\n            color: #888;\r\n            background: #fff;\r\n            min-width: 320px;\r\n            padding: 12px 0;\r\n        }\r\n\r\n        img {\r\n            border: 0;\r\n            max-width: 100%;\r\n        }\r\n\r\n        table {\r\n            border-collapse: collapse;\r\n            border-spacing: 0;\r\n        }\r\n\r\n        ol, ul {\r\n            list-style: none;\r\n        }\r\n\r\n        h1, h2, h3, h4, h5, h6 {\r\n            font-size: 100%;\r\n            font-weight: normal;\r\n        }\r\n\r\n        b, i {\r\n            font-style: normal;\r\n            font-weight: normal;\r\n        }\r\n\r\n        a {\r\n            text-decoration: none;\r\n            color: #000;\r\n        }\r\n\r\n        .clearfix:after {\r\n            content: &#039;\r\n            &#039;\r\n            ;\r\n            display: block;\r\n            clear: both;\r\n        }\r\n\r\n        .top {\r\n            margin: 16px 16px 0;\r\n            background: #1e82d0;\r\n            color: #B2C7EA;\r\n            font-size: 12px;\r\n            font-weight: 700;\r\n            text-align: center;\r\n            padding: 16px 0;\r\n            border-radius: 6px;\r\n        }\r\n\r\n        .head {\r\n            padding: 15px 16px;\r\n            font-size: 12px;\r\n            color: #c8c8c8;\r\n            border-bottom: #E6E6E6 1px solid;\r\n        }\r\n\r\n        h2 {\r\n            padding: 0 16px;\r\n            font-weight: 700;\r\n        }\r\n\r\n        h4 {\r\n            padding: 0 16px;\r\n            font-size: 11px;\r\n        }\r\n\r\n        .box {\r\n            padding: 15px 16px 10px;\r\n        }\r\n\r\n            .box .pic {\r\n                float: left;\r\n                width: 45%;\r\n            }\r\n\r\n                .box .pic img {\r\n                    width: 100%;\r\n                    vertical-align: top;\r\n                }\r\n\r\n            .box dl {\r\n                float: left;\r\n                width: 55%;\r\n            }\r\n\r\n            .box dt {\r\n                padding: 0 0 0 6px;\r\n                line-height: 1;\r\n                font-weight: 700;\r\n            }\r\n\r\n            .box dd {\r\n                padding: 10px 0 0 6px;\r\n                font-size: 11px;\r\n                line-height: 13px;\r\n                text-align: justify;\r\n            }\r\n\r\n        .box-right dt {\r\n            padding: 0 6px 0 0;\r\n            text-align: right;\r\n        }\r\n\r\n        .box-right dd {\r\n            padding: 10px 6px 0 0;\r\n        }\r\n\r\n        .piclist {\r\n            padding: 0 0 0px;\r\n        }\r\n\r\n            .piclist ul {\r\n                padding: 0 12px;\r\n            }\r\n\r\n            .piclist li {\r\n                text-align: center;\r\n                font-size: 11px;\r\n                float: left;\r\n                width: 33.33%;\r\n                box-sizing: border-box;\r\n                -webkit-box-sizing: border-box;\r\n                padding: 0 4px;\r\n            }\r\n\r\n                .piclist li img {\r\n                    width: 100%;\r\n                    vertical-align: top;\r\n                }\r\n\r\n                .piclist li i {\r\n                    display: block;\r\n                    font-size: 11px;\r\n                    text-align: center;\r\n                    padding: 6px 0 0;\r\n                    line-height: 1;\r\n                }\r\n\r\n        .colorlist {\r\n            padding: 0 16px 12px;\r\n        }\r\n\r\n            .colorlist ul {\r\n                margin: 0 -10px;\r\n            }\r\n\r\n            .colorlist li {\r\n                float: left;\r\n                width: 20%;\r\n                font-size: 11px;\r\n                text-align: center;\r\n                box-sizing: border-box;\r\n                -webkit-box-sizing: border-box;\r\n                padding: 12px 10px 0;\r\n            }\r\n\r\n                .colorlist li span {\r\n                    display: block;\r\n                    padding: 100% 0 0;\r\n                }\r\n\r\n                    .colorlist li span.color-1 {\r\n                        background: #A4B8E4;\r\n                    }\r\n\r\n                    .colorlist li span.color-2 {\r\n                        background: #F7E2E5;\r\n                    }\r\n\r\n                    .colorlist li span.color-3 {\r\n                        background: #5E6D72;\r\n                    }\r\n\r\n                    .colorlist li span.color-4 {\r\n                        background: #A8AA9D;\r\n                    }\r\n\r\n                    .colorlist li span.color-5 {\r\n                        background: #9A5151;\r\n                    }\r\n\r\n                    .colorlist li span.color-6 {\r\n                        background: #D99B60;\r\n                    }\r\n\r\n                    .colorlist li span.color-7 {\r\n                        background: #D486A0;\r\n                    }\r\n\r\n                    .colorlist li span.color-8 {\r\n                        background: #8E67AA;\r\n                    }\r\n\r\n                    .colorlist li span.color-9 {\r\n                        background: #037482;\r\n                    }\r\n\r\n                    .colorlist li span.color-10 {\r\n                        background: #284556;\r\n                    }\r\n\r\n                .colorlist li i {\r\n                    display: block;\r\n                    font-size: 11px;\r\n                    padding: 1px 0 0;\r\n                }\r\n\r\n        .data {\r\n            font-size: 11px;\r\n            padding: 0 16px 16px;\r\n        }\r\n\r\n            .data table {\r\n                width: 100%;\r\n                text-align: center;\r\n            }\r\n\r\n            .data th {\r\n                border: #E5E5E5 1px solid;\r\n                padding: 6px 0;\r\n                font-weight: 700;\r\n            }\r\n\r\n            .data td {\r\n                border: #E5E5E5 1px solid;\r\n                padding: 6px 0;\r\n            }\r\n\r\n        .works {\r\n            width: 100%;\r\n            padding: 0 16px;\r\n            text-align: center;\r\n        }\r\n\r\n            .works img {\r\n                max-width: 960px;\r\n                vertical-align: top;\r\n            }\r\n    </style><div class=\"top\">留在照片中的不是时间，<br>也不是物质本身，据说只是一种反应。<br>它以某种毅⼒在挽留，挽留老去，挽留分离，挽留曾经。<br>在我们光阴明媚的“⼤房间”，让欢颜停驻在团聚里。<br>截⼀帧可回忆的温情，不忘记。</div><h2>拍摄案例</h2><div class=\"works\"><img src=\"/upload/20160401/14595175315167.jpg?v=8\" _src=\"/upload/20160401/14595175315167.jpg?v=8\"></div><div class=\"head\">本页所用图片均为十七平客户授权使用，侵权必究。</div><h2>服务展示</h2><div class=\"box clearfix\">\r\n        <div class=\"pic\">\r\n            <img src=\"/upload/20160401/1459502883872.jpg?v=8\" _src=\"/upload/20160401/1459502883872.jpg?v=8\">\r\n            &nbsp; &nbsp;\r\n        </div><dl><dt>前台接待</dt><dd>前台客服会询问你的证件照用途，帮你选择合适的底色和尺寸。</dd></dl>\r\n    </div><div class=\"box box-right clearfix\">\r\n        <dl><dt>化妆造型</dt><dd>专业化妆师会根据你的气质和职业属性量身定做妆容和发型，让形容得到提升。</dd></dl><div class=\"pic\">\r\n            <img src=\"/upload/20160401/14595028964763.jpg?v=8\" _src=\"/upload/20160401/14595028964763.jpg?v=8\">\r\n            &nbsp; &nbsp;\r\n        </div>\r\n    </div><div class=\"piclist\"><ul class=\"clearfix\"><li><img src=\"/upload/20160401/14595029207983.jpg?v=8\" _src=\"/upload/20160401/14595029207983.jpg?v=8\"> &nbsp;专属化妆套件</li><li><img src=\"/upload/20160401/14595029299249.jpg?v=8\" _src=\"/upload/20160401/14595029299249.jpg?v=8\"> &nbsp;专业彩妆用品</li><li><img src=\"/upload/20160401/14595029368232.jpg?v=8\" _src=\"/upload/20160401/14595029368232.jpg?v=8\"> &nbsp;衣物贴心保护</li></ul></div><div class=\"box clearfix\">\r\n        <div class=\"pic\">\r\n            <img src=\"/upload/20160401/14595029466294.jpg?v=8\" _src=\"/upload/20160401/14595029466294.jpg?v=8\">\r\n            &nbsp; &nbsp;\r\n        </div><dl><dt>服饰搭配</dt><dd>可提供部分正装，建议你自带服装和配饰(更合身效果更好)，店内也有部分配饰可供选择。</dd></dl>\r\n    </div><div class=\"piclist\"><ul class=\"clearfix\"><li><img src=\"/upload/20160401/14595029553270.jpg?v=8\" _src=\"/upload/20160401/14595029553270.jpg?v=8\"> &nbsp;领带</li><li><img src=\"/upload/20160401/14595029622663.jpg?v=8\" _src=\"/upload/20160401/14595029622663.jpg?v=8\"> &nbsp;领结</li><li><img src=\"/upload/20160401/14595029695946.jpg?v=8\" _src=\"/upload/20160401/14595029695946.jpg?v=8\"> &nbsp;眼镜框</li></ul></div><h2>自带服装推荐颜色</h2><h4>建议选用纯色的衣服(不要太鲜艳)，推荐以下颜色：</h4><div class=\"colorlist\"><ul class=\"clearfix\"><li><span class=\"color-1\"> </span> &nbsp;浅蓝</li><li><span class=\"color-2\"> </span> &nbsp;浅粉</li><li><span class=\"color-3\"> </span> &nbsp;雅灰</li><li><span class=\"color-4\"> </span> &nbsp;卡其</li><li><span class=\"color-5\"> </span> &nbsp;酒红</li><li><span class=\"color-6\"> </span> &nbsp;橡木黄</li><li><span class=\"color-7\"> </span> &nbsp;玫瑰粉</li><li><span class=\"color-8\"> </span> &nbsp;淡紫</li><li><span class=\"color-9\"> </span> &nbsp;蓝绿</li><li><span class=\"color-10\"> </span> &nbsp;蓝灰</li></ul></div><div class=\"box box-right clearfix\">\r\n        <dl><dt>专业拍摄</dt><dd>专业摄影师会协助调整面部神态和姿势，提升精神面貌和抓也形象。请在拍摄过程中与摄影师保持交流。</dd></dl><div class=\"pic\">\r\n            <img src=\"/upload/20160402/1459574199826.jpg?v=8\" _src=\"/upload/20160402/1459574199826.jpg?v=8\" style=\"vertical-align: top; width: 382.938px; line-height: 22.8571px; white-space: normal;\">\r\n            &nbsp; &nbsp;\r\n        </div>\r\n    </div><div class=\"box clearfix\">\r\n        <div class=\"pic\">\r\n            <img src=\"/upload/20160401/1459502996870.jpg?v=8\" _src=\"/upload/20160401/1459502996870.jpg?v=8\">\r\n            &nbsp; &nbsp;\r\n        </div><dl><dt>后期修片</dt><dd>资深修图师会对照片进行较色和精修，皮肤上的小瑕疵、色斑、暗疮都会去除。你也可以提前提出自己的个性化要求。</dd></dl>\r\n    </div><div class=\"box box-right clearfix\">\r\n        <dl><dt>冲印裁切</dt><dd>600dpi高精度冲印，进口专业相纸，一丝不苟的裁切，确保照片细节的完美呈现。</dd></dl><div class=\"pic\">\r\n            <img src=\"/upload/20160401/1459503004202.jpg?v=8\" _src=\"/upload/20160401/1459503004202.jpg?v=8\">\r\n            &nbsp; &nbsp;\r\n        </div>\r\n    </div><div class=\"data\"><table><tbody><tr><th colspan=\"9\" style=\"text-align:center\">不同尺寸冲印一版可裁切的照片数量</th></tr><tr><td>一寸</td><td>二寸</td><td>登记</td><td>美签</td><td>日签</td><td>欧签</td><td>身份证</td><td>护照</td><td>驾照</td></tr><tr><td>10张</td><td>8张</td><td>4张</td><td>2张</td><td>4张</td><td>6张</td><td>10张</td><td>6张</td><td>10张</td></tr></tbody></table></div><div class=\"box clearfix\">\r\n        <div class=\"pic\">\r\n            <img src=\"/upload/20160401/14595030167322.jpg?v=8\" _src=\"/upload/20160401/14595030167322.jpg?v=8\">\r\n            &nbsp; &nbsp;\r\n        </div><dl><dt>送达你手中</dt><dd>确认照片后约一刻钟，照片会装袋并送呈到你手中。赶快拍照发朋友圈和微博吧~</dd></dl>\r\n    </div><div class=\"box box-right clearfix\">\r\n        <dl><dt>休息区</dt><dd>卸下平时疲惫的身心，在17平品一杯咖啡的醇香，享受时光静谧的美好</dd></dl><div class=\"pic\">\r\n            <img src=\"/upload/20160401/14595030244324.jpg?v=8\" _src=\"/upload/20160401/14595030244324.jpg?v=8\">\r\n\r\n            &nbsp; &nbsp;\r\n        </div>\r\n    </div>\r\n</div>', 4);
INSERT INTO `pro_category` VALUES (9, 1, 0, '企业外拍', 'upload/201703/2017033122966514.png', 12600, '<section class=\"product-section specifics\">\r\n    <dl>\r\n        <dt><strong>包含项目</strong></dt>\r\n        <dd>\r\n            <ul class=\"service clearfix\">\r\n                <li class=\"service-1\">\r\n                    <i></i>\r\n                    <span>化妆</span>\r\n                    <p>1个妆面造型</p>\r\n                </li>\r\n                <li class=\"service-2\">\r\n                    <i></i>\r\n                    <span>拍摄</span>\r\n                    <p>1种背景颜色</p>\r\n                </li>\r\n                <li class=\"service-3\">\r\n                    <i></i>\r\n                    <span>修图</span>\r\n                    <p>1张精修底片</p>\r\n                </li>\r\n            </ul>\r\n            <div style=\"color: #888;\"><strong>服装</strong>：需自行准备服装。</div>\r\n        </dd>\r\n        <dd class=\"clearfix\">\r\n            <dl>\r\n                <dt><strong>预计耗时</strong></dt>\r\n                <dd>约<strong>4</strong>小时</dd>\r\n            </dl>\r\n            <dl>\r\n                <dt><strong>看样时间</strong></dt>\r\n                <dd><i class=\"active-i\"></i>7天邮件看样</dd>\r\n            </dl>\r\n            <dl>\r\n                <dt><strong>取件方式</strong></dt>\r\n                <dd><i class=\"active-i\"></i>顺丰到付</dd>\r\n            </dl>\r\n        </dd>\r\n        <dt>\r\n            <strong>用途</strong><br>\r\n            <p>企业团队的工作证照片、证件照、公司形象照、团队形象展示照片、企业合影类</p>\r\n        </dt>\r\n\r\n\r\n\r\n    </dl>\r\n    <a id=\"more\" class=\"more\" style=\"display: none;\"><span>查看更多详情</span></a>\r\n</section><div class=\"product-desc\">\r\n    <style>\r\n        * {\r\n            margin: 0;\r\n            padding: 0;\r\n            outline: 0;\r\n            -webkit-text-size-adjust: none;\r\n            -webkit-tap-highlight-color: rgba(0, 0, 0, 0);\r\n        }\r\n\r\n        html {\r\n            -webkit-text-size-adjust: 100%;\r\n            -ms-text-size-adjust: 100%;\r\n        }\r\n\r\n        body {\r\n            font: 12px/1.5 &#039;\r\n            PingFang SC&#039;\r\n            , Arial, sans-serif;\r\n            color: #888;\r\n            background: #fff;\r\n            min-width: 320px;\r\n            padding: 12px 0;\r\n        }\r\n\r\n        img {\r\n            border: 0;\r\n            max-width: 100%;\r\n        }\r\n\r\n        table {\r\n            border-collapse: collapse;\r\n            border-spacing: 0;\r\n        }\r\n\r\n        ol, ul {\r\n            list-style: none;\r\n        }\r\n\r\n        h1, h2, h3, h4, h5, h6 {\r\n            font-size: 100%;\r\n            font-weight: normal;\r\n        }\r\n\r\n        b, i {\r\n            font-style: normal;\r\n            font-weight: normal;\r\n        }\r\n\r\n        a {\r\n            text-decoration: none;\r\n            color: #000;\r\n        }\r\n\r\n        .clearfix:after {\r\n            content: &#039;\r\n            &#039;\r\n            ;\r\n            display: block;\r\n            clear: both;\r\n        }\r\n\r\n        .top {\r\n            margin: 16px 16px 0;\r\n            background: #1e82d0;\r\n            color: #B2C7EA;\r\n            font-size: 12px;\r\n            font-weight: 700;\r\n            text-align: center;\r\n            padding: 16px 0;\r\n            border-radius: 6px;\r\n        }\r\n\r\n        .head {\r\n            padding: 15px 16px;\r\n            font-size: 12px;\r\n            color: #c8c8c8;\r\n            border-bottom: #E6E6E6 1px solid;\r\n        }\r\n\r\n        h2 {\r\n            padding: 0 16px;\r\n            font-weight: 700;\r\n        }\r\n\r\n        h4 {\r\n            padding: 0 16px;\r\n            font-size: 11px;\r\n        }\r\n\r\n        .box {\r\n            padding: 15px 16px 10px;\r\n        }\r\n\r\n            .box .pic {\r\n                float: left;\r\n                width: 45%;\r\n            }\r\n\r\n                .box .pic img {\r\n                    width: 100%;\r\n                    vertical-align: top;\r\n                }\r\n\r\n            .box dl {\r\n                float: left;\r\n                width: 55%;\r\n            }\r\n\r\n            .box dt {\r\n                padding: 0 0 0 6px;\r\n                line-height: 1;\r\n                font-weight: 700;\r\n            }\r\n\r\n            .box dd {\r\n                padding: 10px 0 0 6px;\r\n                font-size: 11px;\r\n                line-height: 13px;\r\n                text-align: justify;\r\n            }\r\n\r\n        .box-right dt {\r\n            padding: 0 6px 0 0;\r\n            text-align: right;\r\n        }\r\n\r\n        .box-right dd {\r\n            padding: 10px 6px 0 0;\r\n        }\r\n\r\n        .piclist {\r\n            padding: 0 0 0px;\r\n        }\r\n\r\n            .piclist ul {\r\n                padding: 0 12px;\r\n            }\r\n\r\n            .piclist li {\r\n                text-align: center;\r\n                font-size: 11px;\r\n                float: left;\r\n                width: 33.33%;\r\n                box-sizing: border-box;\r\n                -webkit-box-sizing: border-box;\r\n                padding: 0 4px;\r\n            }\r\n\r\n                .piclist li img {\r\n                    width: 100%;\r\n                    vertical-align: top;\r\n                }\r\n\r\n                .piclist li i {\r\n                    display: block;\r\n                    font-size: 11px;\r\n                    text-align: center;\r\n                    padding: 6px 0 0;\r\n                    line-height: 1;\r\n                }\r\n\r\n        .colorlist {\r\n            padding: 0 16px 12px;\r\n        }\r\n\r\n            .colorlist ul {\r\n                margin: 0 -10px;\r\n            }\r\n\r\n            .colorlist li {\r\n                float: left;\r\n                width: 20%;\r\n                font-size: 11px;\r\n                text-align: center;\r\n                box-sizing: border-box;\r\n                -webkit-box-sizing: border-box;\r\n                padding: 12px 10px 0;\r\n            }\r\n\r\n                .colorlist li span {\r\n                    display: block;\r\n                    padding: 100% 0 0;\r\n                }\r\n\r\n                    .colorlist li span.color-1 {\r\n                        background: #A4B8E4;\r\n                    }\r\n\r\n                    .colorlist li span.color-2 {\r\n                        background: #F7E2E5;\r\n                    }\r\n\r\n                    .colorlist li span.color-3 {\r\n                        background: #5E6D72;\r\n                    }\r\n\r\n                    .colorlist li span.color-4 {\r\n                        background: #A8AA9D;\r\n                    }\r\n\r\n                    .colorlist li span.color-5 {\r\n                        background: #9A5151;\r\n                    }\r\n\r\n                    .colorlist li span.color-6 {\r\n                        background: #D99B60;\r\n                    }\r\n\r\n                    .colorlist li span.color-7 {\r\n                        background: #D486A0;\r\n                    }\r\n\r\n                    .colorlist li span.color-8 {\r\n                        background: #8E67AA;\r\n                    }\r\n\r\n                    .colorlist li span.color-9 {\r\n                        background: #037482;\r\n                    }\r\n\r\n                    .colorlist li span.color-10 {\r\n                        background: #284556;\r\n                    }\r\n\r\n                .colorlist li i {\r\n                    display: block;\r\n                    font-size: 11px;\r\n                    padding: 1px 0 0;\r\n                }\r\n\r\n        .data {\r\n            font-size: 11px;\r\n            padding: 0 16px 16px;\r\n        }\r\n\r\n            .data table {\r\n                width: 100%;\r\n                text-align: center;\r\n            }\r\n\r\n            .data th {\r\n                border: #E5E5E5 1px solid;\r\n                padding: 6px 0;\r\n                font-weight: 700;\r\n            }\r\n\r\n            .data td {\r\n                border: #E5E5E5 1px solid;\r\n                padding: 6px 0;\r\n            }\r\n\r\n        .works {\r\n            width: 100%;\r\n            padding: 0 16px;\r\n            text-align: center;\r\n        }\r\n\r\n            .works img {\r\n                max-width: 720px;\r\n                vertical-align: top;\r\n            }\r\n    </style>\r\n    <div class=\"top\">\r\n        上⻔外拍服务，全国各地皆可，<br>给您棚拍级的外拍体验。\r\n    </div>\r\n    <h2>\r\n        拍摄案例\r\n    </h2>\r\n    <div class=\"works\">\r\n        <img src=\"/upload/20160401/14595175859127.jpg?v=8\" _src=\"/upload/20160401/14595175859127.jpg?v=8\">\r\n    </div>\r\n    <div class=\"head\">\r\n        本页所用图片均为十七平客户授权使用，侵权必究。\r\n    </div>\r\n    <h2>\r\n        服务展示\r\n    </h2>\r\n    <div class=\"box clearfix\">\r\n        <div class=\"pic\">\r\n            <img src=\"/upload/20160401/1459502883872.jpg?v=8\" _src=\"/upload/20160401/1459502883872.jpg?v=8\">\r\n            &nbsp; &nbsp;\r\n        </div>\r\n        <dl>\r\n            <dt>\r\n                前期沟通\r\n            </dt>\r\n            <dd>\r\n                外拍团队会询问你的拍摄需求，根据需求制定拍摄方案。\r\n            </dd>\r\n        </dl>\r\n    </div>\r\n    <div class=\"box box-right clearfix\">\r\n        <dl>\r\n            <dt>\r\n                化妆造型\r\n            </dt>\r\n            <dd>\r\n                专业化妆师会根据你的气质和职业属性量身定做妆容和发型，让形容得到提升。\r\n            </dd>\r\n        </dl>\r\n        <div class=\"pic\">\r\n            <img src=\"/upload/20160401/14595028964763.jpg?v=8\" _src=\"/upload/20160401/14595028964763.jpg?v=8\">\r\n            &nbsp; &nbsp;\r\n        </div>\r\n    </div>\r\n    <div class=\"piclist\">\r\n        <ul class=\"clearfix\">\r\n            <li>\r\n                <img src=\"/upload/20160401/14595029207983.jpg?v=8\" _src=\"/upload/20160401/14595029207983.jpg?v=8\"> &nbsp;专属化妆套件\r\n            </li>\r\n            <li>\r\n                <img src=\"/upload/20160401/14595029299249.jpg?v=8\" _src=\"/upload/20160401/14595029299249.jpg?v=8\"> &nbsp;专业彩妆用品\r\n            </li>\r\n            <li>\r\n                <img src=\"/upload/20160401/14595029368232.jpg?v=8\" _src=\"/upload/20160401/14595029368232.jpg?v=8\"> &nbsp;衣物贴心保护\r\n            </li>\r\n        </ul>\r\n    </div>\r\n    <h2>\r\n        自带服装推荐颜色\r\n    </h2>\r\n    <h4>\r\n        建议选用纯色的衣服(不要太鲜艳)，推荐以下颜色：\r\n    </h4>\r\n    <div class=\"colorlist\">\r\n        <ul class=\"clearfix\">\r\n            <li>\r\n                <span class=\"color-1\"> </span> &nbsp;浅蓝\r\n            </li>\r\n            <li>\r\n                <span class=\"color-2\"> </span> &nbsp;浅粉\r\n            </li>\r\n            <li>\r\n                <span class=\"color-3\"> </span> &nbsp;雅灰\r\n            </li>\r\n            <li>\r\n                <span class=\"color-4\"> </span> &nbsp;卡其\r\n            </li>\r\n            <li>\r\n                <span class=\"color-5\"> </span> &nbsp;酒红\r\n            </li>\r\n            <li>\r\n                <span class=\"color-6\"> </span> &nbsp;橡木黄\r\n            </li>\r\n            <li>\r\n                <span class=\"color-7\"> </span> &nbsp;玫瑰粉\r\n            </li>\r\n            <li>\r\n                <span class=\"color-8\"> </span> &nbsp;淡紫\r\n            </li>\r\n            <li>\r\n                <span class=\"color-9\"> </span> &nbsp;蓝绿\r\n            </li>\r\n            <li>\r\n                <span class=\"color-10\"> </span> &nbsp;蓝灰\r\n            </li>\r\n        </ul>\r\n    </div>\r\n    <div class=\"box box-right clearfix\">\r\n        <dl>\r\n            <dt>\r\n                专业拍摄\r\n            </dt>\r\n            <dd>\r\n                专业摄影师会协助调整面部神态和姿势，提升精神面貌和抓也形象。请在拍摄过程中与摄影师保持交流。\r\n            </dd>\r\n        </dl>\r\n        <div class=\"pic\">\r\n            <img src=\"/upload/20160402/1459574199826.jpg?v=8\" _src=\"/upload/20160402/1459574199826.jpg?v=8\" style=\"vertical-align: top; width: 382.938px; line-height: 22.8571px; white-space: normal;\">\r\n            &nbsp; &nbsp;\r\n        </div>\r\n    </div>\r\n    <div class=\"box clearfix\">\r\n        <div class=\"pic\">\r\n            <img src=\"/upload/20160401/1459502996870.jpg?v=8\" _src=\"/upload/20160401/1459502996870.jpg?v=8\">\r\n            &nbsp; &nbsp;\r\n        </div>\r\n        <dl>\r\n            <dt>\r\n                后期修片\r\n            </dt>\r\n            <dd>\r\n                资深修图师会对照片进行较色和精修，皮肤上的小瑕疵、色斑、暗疮都会去除。你也可以提前提出自己的个性化要求。\r\n            </dd>\r\n        </dl>\r\n    </div>\r\n    <div class=\"box box-right clearfix\">\r\n        <dl>\r\n            <dt>\r\n                冲印裁切\r\n            </dt>\r\n            <dd>\r\n                600dpi高精度冲印，进口专业相纸，一丝不苟的裁切，确保照片细节的完美呈现。\r\n            </dd>\r\n        </dl>\r\n        <div class=\"pic\">\r\n            <img src=\"/upload/20160401/1459503004202.jpg?v=8\" _src=\"/upload/20160401/1459503004202.jpg?v=8\">\r\n            &nbsp; &nbsp;\r\n        </div>\r\n    </div>\r\n    <div class=\"data\">\r\n        <table>\r\n            <tbody>\r\n                <tr>\r\n                    <th colspan=\"9\" style=\"text-align:center\">\r\n                        不同尺寸冲印一版可裁切的照片数量\r\n                    </th>\r\n                </tr>\r\n                <tr>\r\n                    <td>\r\n                        一寸\r\n                    </td>\r\n                    <td>\r\n                        二寸\r\n                    </td>\r\n                    <td>\r\n                        登记\r\n                    </td>\r\n                    <td>\r\n                        美签\r\n                    </td>\r\n                    <td>\r\n                        日签\r\n                    </td>\r\n                    <td>\r\n                        欧签\r\n                    </td>\r\n                    <td>\r\n                        身份证\r\n                    </td>\r\n                    <td>\r\n                        护照\r\n                    </td>\r\n                    <td>\r\n                        驾照\r\n                    </td>\r\n                </tr>\r\n                <tr>\r\n                    <td>\r\n                        10张\r\n                    </td>\r\n                    <td>\r\n                        8张\r\n                    </td>\r\n                    <td>\r\n                        4张\r\n                    </td>\r\n                    <td>\r\n                        2张\r\n                    </td>\r\n                    <td>\r\n                        4张\r\n                    </td>\r\n                    <td>\r\n                        6张\r\n                    </td>\r\n                    <td>\r\n                        10张\r\n                    </td>\r\n                    <td>\r\n                        6张\r\n                    </td>\r\n                    <td>\r\n                        10张\r\n                    </td>\r\n                </tr>\r\n            </tbody>\r\n        </table>\r\n    </div>\r\n</div>', 5);
INSERT INTO `pro_category` VALUES (10, 2, 2, '1号婚纱', 'upload/201703/2017033188550710.png', 0, NULL, 0);
INSERT INTO `pro_category` VALUES (11, 2, 3, '1号婚礼跟妆', 'upload/201703/2017033188550710.png', 0, NULL, 0);
INSERT INTO `pro_category` VALUES (12, 2, 4, '1号婚礼跟拍', 'upload/201703/2017033188550710.png', 0, NULL, 0);
INSERT INTO `pro_category` VALUES (13, 2, 5, '1号酒店', 'upload/201703/2017033188550710.png', 0, NULL, 0);

-- ----------------------------
-- Table structure for pro_category_type
-- ----------------------------
DROP TABLE IF EXISTS `pro_category_type`;
CREATE TABLE `pro_category_type`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `status` tinyint(1) NULL DEFAULT 1,
  `sort` mediumint(9) NULL DEFAULT 0 COMMENT '排序越大越靠前',
  `create_time` datetime(0) NULL DEFAULT NULL,
  `update_time` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '套餐类型' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of pro_category_type
-- ----------------------------
INSERT INTO `pro_category_type` VALUES (2, '婚纱租售', 1, 0, NULL, NULL);
INSERT INTO `pro_category_type` VALUES (3, '婚礼跟妆', 1, 0, NULL, NULL);
INSERT INTO `pro_category_type` VALUES (4, '婚礼跟拍', 1, 0, NULL, NULL);
INSERT INTO `pro_category_type` VALUES (5, '酒店预订', 1, 0, NULL, NULL);

-- ----------------------------
-- Table structure for pro_city
-- ----------------------------
DROP TABLE IF EXISTS `pro_city`;
CREATE TABLE `pro_city`  (
  `code` mediumint(6) UNSIGNED NOT NULL,
  `name` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `sort` tinyint(3) UNSIGNED NULL DEFAULT 0,
  `status` tinyint(1) NULL DEFAULT 1,
  PRIMARY KEY (`code`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of pro_city
-- ----------------------------
INSERT INTO `pro_city` VALUES (210400, '抚顺市', '15668577700', 20, 1);
INSERT INTO `pro_city` VALUES (440700, '江门市', '18688986890', 19, 1);
INSERT INTO `pro_city` VALUES (520300, '遵义', '0851-28320978', 1, 1);
INSERT INTO `pro_city` VALUES (550000, '贵阳', '085185972955', 2, 1);
INSERT INTO `pro_city` VALUES (551700, '毕节', '', 5, 0);
INSERT INTO `pro_city` VALUES (553000, '六盘水', '', 3, 0);
INSERT INTO `pro_city` VALUES (554300, '铜仁', '', 6, 0);
INSERT INTO `pro_city` VALUES (556000, '凯里', '18585551008', 4, 1);
INSERT INTO `pro_city` VALUES (556100, '安顺', '', 7, 0);
INSERT INTO `pro_city` VALUES (558000, '都匀', '', 8, 0);
INSERT INTO `pro_city` VALUES (562400, '兴义', '', 10, 0);
INSERT INTO `pro_city` VALUES (5645000, '仁怀', '', 9, 0);

-- ----------------------------
-- Table structure for pro_comment
-- ----------------------------
DROP TABLE IF EXISTS `pro_comment`;
CREATE TABLE `pro_comment`  (
  `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `orderid` mediumint(8) UNSIGNED NULL DEFAULT 0,
  `storeid` mediumint(8) UNSIGNED NULL DEFAULT 0,
  `raterid` mediumint(8) UNSIGNED NULL DEFAULT 0,
  `rater` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '评价人',
  `message` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `coupon` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '忧患卷',
  `score1` tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT '前台评分',
  `score2` tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT '化妆师评分',
  `score3` tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT '摄影师评分',
  `score4` tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT '修图师评分',
  `score5` tinyint(1) UNSIGNED NULL DEFAULT 0,
  `createtime` datetime(0) NULL DEFAULT NULL COMMENT '评价时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `orderid`(`orderid`) USING BTREE,
  INDEX `raterid`(`raterid`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for pro_config
-- ----------------------------
DROP TABLE IF EXISTS `pro_config`;
CREATE TABLE `pro_config`  (
  `name` char(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `description` char(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `type` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'text',
  `sort` tinyint(5) UNSIGNED NULL DEFAULT 0,
  PRIMARY KEY (`name`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of pro_config
-- ----------------------------
INSERT INTO `pro_config` VALUES ('comment_reward', '1', '评论是否派发优惠卷', 'text', 0);
INSERT INTO `pro_config` VALUES ('downpay_percent', '3000', '预付定金（分）', 'text', 0);
INSERT INTO `pro_config` VALUES ('refund_rule', '[{\"min\":72,\"refund\":0.99,\"mark\":\"距拍摄时间72小时以上取消订单，退99%订金\"},{\"min\":48,\"max\":72,\"refund\":0.5,\"mark\":\"距拍摄时间48-72小时取消订单，退50%订金\"},{\"min\":0,\"max\":48,\"refund\":0,\"mark\":\"距拍摄时间不足48小时取消订单，不可退订金\"}]', '退款规则', 'textarea', 0);

-- ----------------------------
-- Table structure for pro_coupon
-- ----------------------------
DROP TABLE IF EXISTS `pro_coupon`;
CREATE TABLE `pro_coupon`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT '用途',
  `cost` mediumint(8) UNSIGNED NULL DEFAULT 0,
  `partner` char(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `storeid` int(11) NULL DEFAULT NULL,
  `expire` datetime(0) NULL DEFAULT NULL COMMENT '过期时间',
  `status` tinyint(1) NULL DEFAULT 0 COMMENT '0=未使用,1=已使用,2=已派发',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `partner`(`partner`) USING BTREE,
  INDEX `storeid`(`storeid`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for pro_failedlogin
-- ----------------------------
DROP TABLE IF EXISTS `pro_failedlogin`;
CREATE TABLE `pro_failedlogin`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `count` int(5) NOT NULL DEFAULT 0,
  `lastupdate` int(11) NOT NULL,
  `ip` char(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Fixed;

-- ----------------------------
-- Records of pro_failedlogin
-- ----------------------------
INSERT INTO `pro_failedlogin` VALUES (1, 3, 1568254105, '15208666791');

-- ----------------------------
-- Table structure for pro_hashcheck
-- ----------------------------
DROP TABLE IF EXISTS `pro_hashcheck`;
CREATE TABLE `pro_hashcheck`  (
  `hash` char(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `dateline` int(10) UNSIGNED NULL DEFAULT NULL,
  PRIMARY KEY (`hash`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Fixed;

-- ----------------------------
-- Table structure for pro_log_pay
-- ----------------------------
DROP TABLE IF EXISTS `pro_log_pay`;
CREATE TABLE `pro_log_pay`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` char(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '支付平台',
  `title` char(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
  `info` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `createtime` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for pro_loginbinding
-- ----------------------------
DROP TABLE IF EXISTS `pro_loginbinding`;
CREATE TABLE `pro_loginbinding`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` mediumint(8) UNSIGNED NULL DEFAULT 0,
  `type` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `authcode` char(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `nickname` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `openid` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `subscribe` tinyint(1) NULL DEFAULT 1 COMMENT '是否关注',
  `activetime` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `authcode`(`authcode`) USING BTREE,
  INDEX `uid`(`uid`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for pro_order
-- ----------------------------
DROP TABLE IF EXISTS `pro_order`;
CREATE TABLE `pro_order`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `citycode` mediumint(5) UNSIGNED NULL DEFAULT 0 COMMENT '城市',
  `poolid` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '号池ID',
  `storeid` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '门店ID',
  `uid` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '用户ID',
  `buyer` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '姓名',
  `buyersex` tinyint(4) NULL DEFAULT 0 COMMENT '性别',
  `buyerphone` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '手机',
  `pay` mediumint(8) UNSIGNED NULL DEFAULT 0 COMMENT '支付金额',
  `downpay` mediumint(8) UNSIGNED NULL DEFAULT 0 COMMENT '定金',
  `coupon` mediumint(8) UNSIGNED NULL DEFAULT 0 COMMENT '优惠金额',
  `item` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '套餐信息',
  `delay` mediumint(8) UNSIGNED NULL DEFAULT 0 COMMENT '总耗时',
  `ordertime` datetime(0) NULL DEFAULT NULL COMMENT '预约时间',
  `createtime` datetime(0) NULL DEFAULT NULL COMMENT '下单时间',
  `status` tinyint(4) NULL DEFAULT 0 COMMENT '0未支付1已支付-1已退款2已完成',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `storeid`(`storeid`) USING BTREE,
  INDEX `uid`(`uid`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for pro_photo
-- ----------------------------
DROP TABLE IF EXISTS `pro_photo`;
CREATE TABLE `pro_photo`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` int(10) UNSIGNED NULL DEFAULT 0,
  `orderid` int(10) UNSIGNED NULL DEFAULT 0,
  `name` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '底片名称',
  `url` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '原图片路径',
  `thumb` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '缩略图路径',
  `size` double UNSIGNED NULL DEFAULT 0,
  `createtime` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `uid`(`uid`) USING BTREE,
  INDEX `orderid`(`orderid`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for pro_pool
-- ----------------------------
DROP TABLE IF EXISTS `pro_pool`;
CREATE TABLE `pro_pool`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `storeid` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '门店ID',
  `today` date NULL DEFAULT NULL COMMENT '当天日期',
  `starttime` datetime(0) NULL DEFAULT NULL COMMENT '开始时间',
  `endtime` datetime(0) NULL DEFAULT NULL COMMENT '结束时间',
  `ordercount` tinyint(3) UNSIGNED NULL DEFAULT 0 COMMENT '已预约数',
  `maxcount` tinyint(3) UNSIGNED NULL DEFAULT 0 COMMENT '总预约数',
  `amount` tinyint(3) UNSIGNED NULL DEFAULT 0 COMMENT '剩号数',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `storeid_2`(`storeid`, `starttime`) USING BTREE,
  INDEX `storeid`(`storeid`) USING BTREE,
  INDEX `today`(`today`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of pro_pool
-- ----------------------------
INSERT INTO `pro_pool` VALUES (1, 7, '2019-09-20', '2019-09-20 10:00:00', '2019-09-20 10:30:00', 0, 2, 2);
INSERT INTO `pro_pool` VALUES (2, 7, '2019-09-20', '2019-09-20 10:30:00', '2019-09-20 11:00:00', 0, 2, 2);
INSERT INTO `pro_pool` VALUES (3, 2, '2019-09-20', '2019-09-20 11:00:00', '2019-09-20 11:30:00', 0, 2, 2);

-- ----------------------------
-- Table structure for pro_project
-- ----------------------------
DROP TABLE IF EXISTS `pro_project`;
CREATE TABLE `pro_project`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '项目名',
  `icon` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '图标',
  `url` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '项目地址',
  `status` tinyint(1) NULL DEFAULT 1 COMMENT '状态 1启用 0禁用',
  `sort` mediumint(9) NULL DEFAULT 0 COMMENT '排序 值越大越靠前',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of pro_project
-- ----------------------------
INSERT INTO `pro_project` VALUES (1, '拍照', 'upload/img1.png', '?c=order&a=createorder&step=products', 1, 0, NULL, NULL);
INSERT INTO `pro_project` VALUES (2, '婚礼周边', 'upload/img2.png', '?c=marry&a=stores', 1, 0, NULL, NULL);
INSERT INTO `pro_project` VALUES (3, '酒店', 'upload/img3.png', NULL, 0, 0, NULL, NULL);

-- ----------------------------
-- Table structure for pro_session
-- ----------------------------
DROP TABLE IF EXISTS `pro_session`;
CREATE TABLE `pro_session`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `userid` mediumint(8) UNSIGNED NULL DEFAULT NULL,
  `scode` varchar(13) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `clienttype` char(6) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `clientapp` varchar(7) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `stoken` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `clientinfo` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `online` tinyint(1) NULL DEFAULT 1,
  `loginip` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `u`(`userid`, `clienttype`) USING BTREE,
  INDEX `u1`(`userid`) USING BTREE,
  INDEX `clienttype`(`clienttype`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of pro_session
-- ----------------------------
INSERT INTO `pro_session` VALUES (1, 1, '5d79a9393086b', 'mobile', NULL, NULL, 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1', 1, '192.168.1.214');

-- ----------------------------
-- Table structure for pro_smscode
-- ----------------------------
DROP TABLE IF EXISTS `pro_smscode`;
CREATE TABLE `pro_smscode`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tel` char(11) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `code` char(6) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `sendtime` int(11) NULL DEFAULT 0,
  `errorcount` tinyint(2) NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `tel`(`tel`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Fixed;

-- ----------------------------
-- Table structure for pro_store
-- ----------------------------
DROP TABLE IF EXISTS `pro_store`;
CREATE TABLE `pro_store`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` int(10) UNSIGNED NULL DEFAULT 1 COMMENT '项目ID',
  `citycode` mediumint(5) UNSIGNED NULL DEFAULT 0,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '门店名称',
  `address` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '地址',
  `tel` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '电话',
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '邮箱',
  `transit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '公交线路',
  `sort` tinyint(3) UNSIGNED NULL DEFAULT 0,
  `status` tinyint(3) UNSIGNED NULL DEFAULT 1 COMMENT '状态 0禁用 1正常',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 9 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of pro_store
-- ----------------------------
INSERT INTO `pro_store` VALUES (1, 1, 210400, '抚顺店', '辽宁省抚顺市新抚区万达国际中心B1SOHO19楼', '15668577700', '260443007@qq.com', '抚顺南站万达', 1, 1);
INSERT INTO `pro_store` VALUES (2, 1, 520300, '遵义店', '遵义市汇川区昆明路唯一国际金创大厦15F', '0851-28320978/13312340543', '3423301642@qq.com', '', 0, 1);
INSERT INTO `pro_store` VALUES (3, 1, 520300, '播州店', '遵义市播州区阳光花园B2号楼602', '15599250168', '', '公交、的士可到', 2, 1);
INSERT INTO `pro_store` VALUES (4, 1, 550000, '花果园店', '贵阳市南明区花果园金融2号楼6楼', '085185972955', '', '', 4, 1);
INSERT INTO `pro_store` VALUES (5, 1, 440700, '江门店', '广东省江门市篷江区江门万达广场7幢204', '18688986890', '', '公交、的士可到', 19, 1);
INSERT INTO `pro_store` VALUES (6, 1, 556000, '国贸店', '清江国际广场国贸购物中心6F', '18685457736‬', '', '的士', 7, 1);
INSERT INTO `pro_store` VALUES (7, 2, 520300, '遵义店', '遵义市汇川区', '13312340543', '3423301642@qq.com', '公交', 0, 1);
INSERT INTO `pro_store` VALUES (8, 2, 520300, '播州店', '遵义市播州店', '13312340543', '', '', 0, 1);

-- ----------------------------
-- Table structure for pro_story_category
-- ----------------------------
DROP TABLE IF EXISTS `pro_story_category`;
CREATE TABLE `pro_story_category`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `categroy_id` int(10) UNSIGNED NULL DEFAULT 0,
  `price` mediumint(8) UNSIGNED NULL DEFAULT 0,
  `package` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `index`(`store_id`, `categroy_id`) USING BTREE,
  INDEX `store_id`(`store_id`) USING BTREE,
  INDEX `categroy_id`(`categroy_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 57 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of pro_story_category
-- ----------------------------
INSERT INTO `pro_story_category` VALUES (17, 1, 1, 7900, NULL);
INSERT INTO `pro_story_category` VALUES (18, 1, 5, 9900, NULL);
INSERT INTO `pro_story_category` VALUES (19, 1, 7, 9900, NULL);
INSERT INTO `pro_story_category` VALUES (20, 1, 6, 9900, NULL);
INSERT INTO `pro_story_category` VALUES (21, 1, 8, 12800, NULL);
INSERT INTO `pro_story_category` VALUES (22, 1, 9, 50000, NULL);
INSERT INTO `pro_story_category` VALUES (23, 2, 1, 9900, NULL);
INSERT INTO `pro_story_category` VALUES (24, 2, 5, 15800, NULL);
INSERT INTO `pro_story_category` VALUES (25, 2, 6, 15800, NULL);
INSERT INTO `pro_story_category` VALUES (26, 2, 7, 12800, NULL);
INSERT INTO `pro_story_category` VALUES (27, 2, 8, 19800, NULL);
INSERT INTO `pro_story_category` VALUES (28, 2, 9, 50000, NULL);
INSERT INTO `pro_story_category` VALUES (29, 3, 1, 9900, NULL);
INSERT INTO `pro_story_category` VALUES (30, 3, 5, 15800, NULL);
INSERT INTO `pro_story_category` VALUES (31, 3, 6, 15800, NULL);
INSERT INTO `pro_story_category` VALUES (32, 3, 7, 12800, NULL);
INSERT INTO `pro_story_category` VALUES (33, 3, 8, 19800, NULL);
INSERT INTO `pro_story_category` VALUES (34, 3, 9, 50000, NULL);
INSERT INTO `pro_story_category` VALUES (35, 4, 1, 12800, NULL);
INSERT INTO `pro_story_category` VALUES (36, 4, 5, 17800, NULL);
INSERT INTO `pro_story_category` VALUES (37, 4, 6, 15800, NULL);
INSERT INTO `pro_story_category` VALUES (38, 4, 7, 15800, NULL);
INSERT INTO `pro_story_category` VALUES (39, 4, 8, 19800, NULL);
INSERT INTO `pro_story_category` VALUES (40, 4, 9, 50000, NULL);
INSERT INTO `pro_story_category` VALUES (41, 5, 1, 9900, NULL);
INSERT INTO `pro_story_category` VALUES (42, 5, 5, 15800, NULL);
INSERT INTO `pro_story_category` VALUES (43, 5, 6, 15800, NULL);
INSERT INTO `pro_story_category` VALUES (44, 5, 7, 12800, NULL);
INSERT INTO `pro_story_category` VALUES (45, 5, 8, 19800, NULL);
INSERT INTO `pro_story_category` VALUES (46, 5, 9, 50000, NULL);
INSERT INTO `pro_story_category` VALUES (47, 6, 1, 9900, NULL);
INSERT INTO `pro_story_category` VALUES (48, 6, 5, 15800, NULL);
INSERT INTO `pro_story_category` VALUES (49, 6, 6, 15800, NULL);
INSERT INTO `pro_story_category` VALUES (50, 6, 7, 12800, NULL);
INSERT INTO `pro_story_category` VALUES (51, 6, 8, 19800, NULL);
INSERT INTO `pro_story_category` VALUES (52, 6, 9, 50000, NULL);
INSERT INTO `pro_story_category` VALUES (53, 7, 10, 200000, NULL);
INSERT INTO `pro_story_category` VALUES (54, 7, 11, 400000, NULL);
INSERT INTO `pro_story_category` VALUES (55, 7, 12, 150000, NULL);
INSERT INTO `pro_story_category` VALUES (56, 7, 13, 75000, NULL);

-- ----------------------------
-- Table structure for pro_user
-- ----------------------------
DROP TABLE IF EXISTS `pro_user`;
CREATE TABLE `pro_user`  (
  `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `telephone` char(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '联系电话',
  `nickname` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `password` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `avatar` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `realname` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '真实姓名',
  `idcard` varchar(18) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '身份证号',
  `address` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '住址',
  `gender` tinyint(1) NULL DEFAULT 0 COMMENT '性别',
  `email` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `qq` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `description` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `birthday` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `area` int(10) NULL DEFAULT -1,
  `state` int(10) UNSIGNED NULL DEFAULT 0,
  `createtime` int(11) NULL DEFAULT 0,
  `lastlogintime` int(11) NULL DEFAULT 0,
  `updatetime` int(11) NULL DEFAULT 0,
  `status` tinyint(1) NULL DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `telephone`(`telephone`) USING BTREE,
  INDEX `nickname`(`nickname`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of pro_user
-- ----------------------------
INSERT INTO `pro_user` VALUES (1, '15208666791', 'admin', '6d59cc9c2eebee22292085cc9a648e2c', NULL, 'admin', NULL, NULL, 0, NULL, NULL, NULL, NULL, -1, 0, 0, 1568254265, 0, 1);

-- ----------------------------
-- Table structure for pro_wx_access
-- ----------------------------
DROP TABLE IF EXISTS `pro_wx_access`;
CREATE TABLE `pro_wx_access`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` char(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `appid` char(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `token` char(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `expire` int(10) UNSIGNED NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of pro_wx_access
-- ----------------------------
INSERT INTO `pro_wx_access` VALUES (1, 'access_token', 'wxf3b8281f2a822121', 'BcoARv4ujutOUo44VlOuwYAsrJJiLhfznzg8ujX_4W2oMA53ttf3ad76EWQ78i6bCgKJuMaiBySGIX33z5-irb8NyUWt-Z-T_FOTDICpdh8SheQmskiDp7lNtZqoPPulFWXdAFAJAB', 1512029919);
INSERT INTO `pro_wx_access` VALUES (2, 'jsapi_ticket', 'wxf3b8281f2a822121', 'kgt8ON7yVITDhtdwci0qeV8Ees_5dDMNl7Cz9LSxLC-exmup-sc4636ePVYjn2JAgtp2MGjC2hqxDLVWbp6hVQ', 1512022461);

SET FOREIGN_KEY_CHECKS = 1;
