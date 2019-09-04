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

 Date: 04/09/2019 18:27:51
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for pro_project
-- ----------------------------
DROP TABLE IF EXISTS `pro_project`;
CREATE TABLE `pro_project`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '项目名',
  `icon` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '图标',
  `status` tinyint(1) NULL DEFAULT 1 COMMENT '状态 1启用 0禁用',
  `sort` mediumint(9) NULL DEFAULT 0 COMMENT '排序 值越大越靠前',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of pro_project
-- ----------------------------
INSERT INTO `pro_project` VALUES (1, '拍照', NULL, 1, 0, NULL, NULL);
INSERT INTO `pro_project` VALUES (2, '婚纱', NULL, 1, 0, NULL, NULL);
INSERT INTO `pro_project` VALUES (3, '酒店', NULL, 1, 0, NULL, NULL);

SET FOREIGN_KEY_CHECKS = 1;
