/*
 Navicat Premium Dump SQL

 Source Server         : mipc
 Source Server Type    : MySQL
 Source Server Version : 100432 (10.4.32-MariaDB)
 Source Host           : localhost:3306
 Source Schema         : weiss

 Target Server Type    : MySQL
 Target Server Version : 100432 (10.4.32-MariaDB)
 File Encoding         : 65001

 Date: 09/07/2026 23:42:52
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for decks
-- ----------------------------
DROP TABLE IF EXISTS `decks`;
CREATE TABLE `decks`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `serie_id` int NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `colores` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `decklist` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `serie_id`(`serie_id` ASC) USING BTREE,
  CONSTRAINT `decks_ibfk_1` FOREIGN KEY (`serie_id`) REFERENCES `series` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of decks
-- ----------------------------

-- ----------------------------
-- Table structure for matches
-- ----------------------------
DROP TABLE IF EXISTS `matches`;
CREATE TABLE `matches`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `fecha` datetime NULL DEFAULT current_timestamp(),
  `player1_id` int NOT NULL,
  `deck1_id` int NOT NULL,
  `player2_id` int NOT NULL,
  `deck2_id` int NOT NULL,
  `winner_player_id` int NOT NULL,
  `winner_deck_id` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `player1_id`(`player1_id` ASC) USING BTREE,
  INDEX `deck1_id`(`deck1_id` ASC) USING BTREE,
  INDEX `player2_id`(`player2_id` ASC) USING BTREE,
  INDEX `deck2_id`(`deck2_id` ASC) USING BTREE,
  INDEX `winner_player_id`(`winner_player_id` ASC) USING BTREE,
  INDEX `winner_deck_id`(`winner_deck_id` ASC) USING BTREE,
  CONSTRAINT `matches_ibfk_1` FOREIGN KEY (`player1_id`) REFERENCES `players` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `matches_ibfk_2` FOREIGN KEY (`deck1_id`) REFERENCES `decks` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `matches_ibfk_3` FOREIGN KEY (`player2_id`) REFERENCES `players` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `matches_ibfk_4` FOREIGN KEY (`deck2_id`) REFERENCES `decks` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `matches_ibfk_5` FOREIGN KEY (`winner_player_id`) REFERENCES `players` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `matches_ibfk_6` FOREIGN KEY (`winner_deck_id`) REFERENCES `decks` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of matches
-- ----------------------------

-- ----------------------------
-- Table structure for players
-- ----------------------------
DROP TABLE IF EXISTS `players`;
CREATE TABLE `players`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of players
-- ----------------------------

-- ----------------------------
-- Table structure for series
-- ----------------------------
DROP TABLE IF EXISTS `series`;
CREATE TABLE `series`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `imagen_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of series
-- ----------------------------

SET FOREIGN_KEY_CHECKS = 1;
