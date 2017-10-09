-- --------------------------------------------------------
-- 主机:                           192.168.56.101
-- 服务器版本:                        5.5.56-log - MySQL Community Server (GPL)
-- 服务器操作系统:                      linux-glibc2.5
-- HeidiSQL 版本:                  9.4.0.5125
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- 导出 im 的数据库结构
CREATE DATABASE IF NOT EXISTS `im` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `im`;

-- 导出  表 im.im_chatlog 结构
CREATE TABLE IF NOT EXISTS `im_chatlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_uid` int(11) NOT NULL COMMENT '会话来源id',
  `from_name` varchar(155) NOT NULL DEFAULT '' COMMENT '消息来源用户名',
  `from_headimgurl` varchar(155) NOT NULL DEFAULT '' COMMENT '来源的用户头像',
  `to_uid` int(11) NOT NULL COMMENT '会话发送的id',
  `content` text NOT NULL COMMENT '发送的内容',
  `addtime` int(10) NOT NULL COMMENT '记录时间',
  `type` varchar(55) NOT NULL COMMENT '聊天类型',
  `need_send` tinyint(1) DEFAULT '0' COMMENT '0 不需要推送  1 需要推送',
  PRIMARY KEY (`id`),
  KEY `from_uid` (`from_uid`) USING BTREE,
  KEY `to_uid` (`to_uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 正在导出表  im.im_chatlog 的数据：~0 rows (大约)
/*!40000 ALTER TABLE `im_chatlog` DISABLE KEYS */;
/*!40000 ALTER TABLE `im_chatlog` ENABLE KEYS */;

-- 导出  表 im.im_user 结构
CREATE TABLE IF NOT EXISTS `im_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(155) DEFAULT NULL,
  `pwd` varchar(155) DEFAULT NULL COMMENT '密码',
  `sign` varchar(255) DEFAULT NULL,
  `openid` varchar(100) NOT NULL DEFAULT '' COMMENT '微信关注时获取的识别码',
  `nickname` varchar(50) DEFAULT NULL COMMENT '用户的昵称',
  `headimgurl` varchar(255) DEFAULT NULL COMMENT '用户头像',
  `status` tinyint(1) DEFAULT '0' COMMENT '0下线 1在线',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 正在导出表  im.im_user 的数据：~0 rows (大约)
/*!40000 ALTER TABLE `im_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `im_user` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
