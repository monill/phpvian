-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping structure for table phpvian.a2b
CREATE TABLE IF NOT EXISTS `a2b` (
  `id` mediumint unsigned NOT NULL AUTO_INCREMENT,
  `ckey` char(12) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `time_check` int unsigned NOT NULL DEFAULT '0',
  `to_vid` int unsigned NOT NULL,
  `u1` int unsigned NOT NULL,
  `u2` int unsigned NOT NULL,
  `u3` int unsigned NOT NULL,
  `u4` int unsigned NOT NULL,
  `u5` int unsigned NOT NULL,
  `u6` int unsigned NOT NULL,
  `u7` int unsigned NOT NULL,
  `u8` int unsigned NOT NULL,
  `u9` int unsigned NOT NULL,
  `u10` int unsigned NOT NULL,
  `u11` int unsigned NOT NULL,
  `type` smallint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ckey` (`ckey`),
  KEY `time_check` (`time_check`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.a2b: ~0 rows (approximately)

-- Dumping structure for table phpvian.abdata
CREATE TABLE IF NOT EXISTS `abdata` (
  `vref` mediumint unsigned NOT NULL AUTO_INCREMENT,
  `a1` tinyint unsigned NOT NULL DEFAULT '0',
  `a2` tinyint unsigned NOT NULL DEFAULT '0',
  `a3` tinyint unsigned NOT NULL DEFAULT '0',
  `a4` tinyint unsigned NOT NULL DEFAULT '0',
  `a5` tinyint unsigned NOT NULL DEFAULT '0',
  `a6` tinyint unsigned NOT NULL DEFAULT '0',
  `a7` tinyint unsigned NOT NULL DEFAULT '0',
  `a8` tinyint unsigned NOT NULL DEFAULT '0',
  `a9` tinyint unsigned NOT NULL DEFAULT '0',
  `a10` tinyint unsigned NOT NULL DEFAULT '0',
  `b1` tinyint unsigned NOT NULL DEFAULT '0',
  `b2` tinyint unsigned NOT NULL DEFAULT '0',
  `b3` tinyint unsigned NOT NULL DEFAULT '0',
  `b4` tinyint unsigned NOT NULL DEFAULT '0',
  `b5` tinyint unsigned NOT NULL DEFAULT '0',
  `b6` tinyint unsigned NOT NULL DEFAULT '0',
  `b7` tinyint unsigned NOT NULL DEFAULT '0',
  `b8` tinyint unsigned NOT NULL DEFAULT '0',
  `b9` tinyint unsigned NOT NULL DEFAULT '0',
  `b10` tinyint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`vref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.abdata: ~0 rows (approximately)

-- Dumping structure for table phpvian.activate
CREATE TABLE IF NOT EXISTS `activate` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `username` char(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `password` char(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `email` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `tribe` tinyint unsigned NOT NULL,
  `access` tinyint unsigned NOT NULL DEFAULT '1',
  `act` char(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `timestamp` int unsigned NOT NULL DEFAULT '0',
  `location` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `act2` char(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `ancestor` char(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.activate: ~0 rows (approximately)

-- Dumping structure for table phpvian.active
CREATE TABLE IF NOT EXISTS `active` (
  `username` char(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `timestamp` int unsigned NOT NULL,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.active: ~0 rows (approximately)

-- Dumping structure for table phpvian.adventure
CREATE TABLE IF NOT EXISTS `adventure` (
  `id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `wref` smallint NOT NULL,
  `uid` smallint unsigned NOT NULL,
  `dif` tinyint NOT NULL,
  `time` int unsigned NOT NULL,
  `end` tinyint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `wref` (`wref`),
  KEY `uid` (`uid`),
  KEY `end` (`end`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.adventure: ~0 rows (approximately)

-- Dumping structure for table phpvian.alidata
CREATE TABLE IF NOT EXISTS `alidata` (
  `id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `name` char(25) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `tag` char(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `leader` smallint unsigned NOT NULL,
  `coor` int unsigned NOT NULL,
  `advisor` int unsigned NOT NULL,
  `recruiter` int unsigned NOT NULL,
  `notice` char(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `desc` char(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `max` tinyint unsigned NOT NULL,
  `ap` bigint unsigned NOT NULL DEFAULT '0',
  `dp` bigint unsigned NOT NULL DEFAULT '0',
  `Rc` bigint NOT NULL DEFAULT '0',
  `RR` bigint NOT NULL DEFAULT '0',
  `Aap` bigint unsigned NOT NULL DEFAULT '0',
  `Adp` bigint unsigned NOT NULL DEFAULT '0',
  `clp` bigint NOT NULL DEFAULT '0',
  `oldrank` bigint NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `tag` (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.alidata: ~0 rows (approximately)

-- Dumping structure for table phpvian.ali_invite
CREATE TABLE IF NOT EXISTS `ali_invite` (
  `id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `uid` smallint unsigned NOT NULL,
  `alliance` smallint unsigned NOT NULL,
  `sender` smallint NOT NULL,
  `timestamp` int NOT NULL,
  `accept` tinyint NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.ali_invite: ~0 rows (approximately)

-- Dumping structure for table phpvian.ali_log
CREATE TABLE IF NOT EXISTS `ali_log` (
  `id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `aid` smallint NOT NULL,
  `comment` char(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `date` int unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.ali_log: ~0 rows (approximately)

-- Dumping structure for table phpvian.ali_permission
CREATE TABLE IF NOT EXISTS `ali_permission` (
  `id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `uid` smallint unsigned NOT NULL,
  `alliance` smallint unsigned NOT NULL,
  `rank` char(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `opt1` int unsigned NOT NULL DEFAULT '0',
  `opt2` int unsigned NOT NULL DEFAULT '0',
  `opt3` int unsigned NOT NULL DEFAULT '0',
  `opt4` int unsigned NOT NULL DEFAULT '0',
  `opt5` int unsigned NOT NULL DEFAULT '0',
  `opt6` int unsigned NOT NULL DEFAULT '0',
  `opt7` int unsigned NOT NULL DEFAULT '0',
  `opt8` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `alliance` (`alliance`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.ali_permission: ~0 rows (approximately)

-- Dumping structure for table phpvian.allimedal
CREATE TABLE IF NOT EXISTS `allimedal` (
  `id` smallint NOT NULL AUTO_INCREMENT,
  `allyid` smallint NOT NULL,
  `categorie` smallint NOT NULL,
  `plaats` smallint NOT NULL,
  `week` smallint NOT NULL,
  `points` bigint NOT NULL,
  `img` char(6) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.allimedal: ~0 rows (approximately)

-- Dumping structure for table phpvian.artefacts
CREATE TABLE IF NOT EXISTS `artefacts` (
  `id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `vref` smallint unsigned NOT NULL,
  `owner` smallint unsigned NOT NULL,
  `type` tinyint unsigned NOT NULL,
  `size` tinyint unsigned NOT NULL,
  `conquered` int unsigned NOT NULL,
  `lastupdate` int unsigned NOT NULL,
  `status` tinyint unsigned NOT NULL,
  `name` char(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `desc` char(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `effecttype` int NOT NULL,
  `effect` double NOT NULL,
  `aoe` int NOT NULL,
  `img` char(15) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.artefacts: ~0 rows (approximately)

-- Dumping structure for table phpvian.attacks
CREATE TABLE IF NOT EXISTS `attacks` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `vref` smallint unsigned NOT NULL,
  `t1` int unsigned NOT NULL,
  `t2` int unsigned NOT NULL,
  `t3` int unsigned NOT NULL,
  `t4` int unsigned NOT NULL,
  `t5` int unsigned NOT NULL,
  `t6` int unsigned NOT NULL,
  `t7` int unsigned NOT NULL,
  `t8` int unsigned NOT NULL,
  `t9` int unsigned NOT NULL,
  `t10` int unsigned NOT NULL,
  `t11` int unsigned NOT NULL,
  `attack_type` tinyint NOT NULL,
  `ctar1` tinyint unsigned NOT NULL,
  `ctar2` tinyint unsigned NOT NULL,
  `spy` tinyint unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.attacks: ~0 rows (approximately)

-- Dumping structure for table phpvian.auction
CREATE TABLE IF NOT EXISTS `auction` (
  `id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `itemid` smallint unsigned NOT NULL,
  `owner` smallint unsigned NOT NULL,
  `btype` tinyint unsigned NOT NULL,
  `type` tinyint unsigned NOT NULL,
  `num` smallint unsigned NOT NULL,
  `uid` smallint unsigned NOT NULL,
  `bids` tinyint NOT NULL,
  `silver` smallint NOT NULL,
  `maxsilver` smallint NOT NULL,
  `time` int unsigned NOT NULL,
  `finish` tinyint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `finish` (`finish`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.auction: ~0 rows (approximately)

-- Dumping structure for table phpvian.autoauction
CREATE TABLE IF NOT EXISTS `autoauction` (
  `id` tinyint NOT NULL,
  `number` tinyint NOT NULL,
  `time` int NOT NULL,
  `lasttime` int NOT NULL,
  `active` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.autoauction: ~8 rows (approximately)
INSERT INTO `autoauction` (`id`, `number`, `time`, `lasttime`, `active`) VALUES
	(1, 0, 0, 0, 0),
	(2, 0, 0, 0, 0),
	(3, 0, 0, 0, 0),
	(4, 0, 0, 0, 0),
	(5, 0, 0, 0, 0),
	(6, 0, 0, 0, 0),
	(7, 100, 1, 0, 1),
	(8, 127, 1, 0, 1),
	(9, 100, 1, 0, 1),
	(10, 0, 0, 0, 0),
	(11, 90, 1, 0, 1),
	(12, 0, 0, 0, 0),
	(13, 0, 0, 0, 0),
	(14, 0, 0, 0, 0),
	(15, 0, 0, 0, 0);

-- Dumping structure for table phpvian.bdata
CREATE TABLE IF NOT EXISTS `bdata` (
  `id` mediumint unsigned NOT NULL AUTO_INCREMENT,
  `wid` smallint unsigned NOT NULL,
  `field` tinyint unsigned NOT NULL,
  `type` tinyint unsigned NOT NULL,
  `loopcon` tinyint unsigned NOT NULL,
  `timestamp` int unsigned NOT NULL,
  `master` tinyint unsigned NOT NULL,
  `level` tinyint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `wid` (`wid`),
  KEY `field` (`field`),
  KEY `master` (`master`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.bdata: ~0 rows (approximately)

-- Dumping structure for table phpvian.cchat
CREATE TABLE IF NOT EXISTS `cchat` (
  `id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `uid` smallint unsigned NOT NULL,
  `To` smallint unsigned NOT NULL,
  `alliance` tinyint unsigned NOT NULL,
  `msg` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `viewed` tinyint NOT NULL DEFAULT '0',
  `time` int unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.cchat: ~0 rows (approximately)

-- Dumping structure for table phpvian.chat
CREATE TABLE IF NOT EXISTS `chat` (
  `id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `name` char(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `alli` char(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `date` char(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `msg` char(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.chat: ~0 rows (approximately)

-- Dumping structure for table phpvian.config
CREATE TABLE IF NOT EXISTS `config` (
    `server_name` VARCHAR(60) NOT NULL COLLATE 'utf8mb3_general_ci',
    `speed` DOUBLE NOT NULL,
    `roundlenght` DOUBLE NOT NULL,
    `increase` DOUBLE NOT NULL,
    `heroattrspeed` DOUBLE NOT NULL,
    `itemattrspeed` DOUBLE NOT NULL,
    `world_max` INT(10) NOT NULL,
    `demolish_lvl` INT(10) NOT NULL,
    `peace` INT(10) NOT NULL,
    `reg_open` INT(10) NOT NULL,
    `limit_mailbox` INT(10) NOT NULL,
    `max_mails` INT(10) NOT NULL,
    `timeout` INT(10) NOT NULL,
    `autodel` INT(10) NOT NULL,
    `autodeltime` INT(10) NOT NULL,
    `village_expand` INT(10) NOT NULL,
    `taskmaster` INT(10) NOT NULL,
    `minprotecttime` INT(10) NOT NULL,
    `maxprotecttime` INT(10) NOT NULL,
    `auctiontime` INT(10) NOT NULL,
    `ww` INT(10) NOT NULL,
    `auth_email` INT(10) NOT NULL,
    `plus_time` INT(10) NOT NULL,
    `plus_prodtime` INT(10) NOT NULL,
    `great_wks` TINYINT(1) NOT NULL,
    `ts_threshold` INT(10) NOT NULL,
    `log_build` INT(10) NOT NULL,
    `log_tech` INT(10) NOT NULL,
    `log_login` INT(10) NOT NULL,
    `log_gold` INT(10) NOT NULL,
    `log_admin` INT(10) NOT NULL,
    `log_users` INT(10) NOT NULL,
    `log_war` INT(10) NOT NULL,
    `log_market` INT(10) NOT NULL,
    `log_illegal` INT(10) NOT NULL,
    `newsbox1` INT(10) NOT NULL,
    `newsbox2` INT(10) NOT NULL,
    `newsbox3` INT(10) NOT NULL,
    `domain_url` VARCHAR(60) NOT NULL COLLATE 'utf8mb3_general_ci',
    `homepage_url` VARCHAR(60) NOT NULL COLLATE 'utf8mb3_general_ci',
    `server_url` VARCHAR(60) NOT NULL COLLATE 'utf8mb3_general_ci',
    `natars_max` DOUBLE NOT NULL,
    `medalinterval` INT(10) NOT NULL,
    `lastgavemedal` INT(10) NOT NULL,
    `commence` INT(10) NOT NULL,
    `storagemultiplier` INT(10) NOT NULL,
    `winmoment` INT(10) NOT NULL,
    `stats_lasttime` INT(10) NOT NULL,
    `stats_time` INT(10) NOT NULL,
    `check_db` INT(10) NOT NULL,
    `minimap_time` INT(10) NOT NULL,
    `checkall_time` INT(10) NOT NULL,
    `last_checkall` INT(10) NOT NULL,
    `freegold_time` INT(10) NOT NULL DEFAULT '86400',
    `freegold_lasttime` INT(10) NOT NULL,
  UNIQUE KEY `server_name` (`server_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.config: ~0 rows (approximately)

-- Dumping structure for table phpvian.deleting
CREATE TABLE IF NOT EXISTS `deleting` (
  `uid` smallint unsigned NOT NULL,
  `timestamp` int unsigned NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.deleting: ~0 rows (approximately)

-- Dumping structure for table phpvian.demolition
CREATE TABLE IF NOT EXISTS `demolition` (
  `vref` smallint unsigned NOT NULL AUTO_INCREMENT,
  `buildnumber` smallint unsigned NOT NULL DEFAULT '0',
  `lvl` tinyint unsigned NOT NULL DEFAULT '0',
  `timetofinish` int NOT NULL,
  PRIMARY KEY (`vref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.demolition: ~0 rows (approximately)

-- Dumping structure for table phpvian.diplomacy
CREATE TABLE IF NOT EXISTS `diplomacy` (
  `id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `alli1` smallint unsigned NOT NULL,
  `alli2` smallint unsigned NOT NULL,
  `type` tinyint unsigned NOT NULL,
  `accepted` tinyint NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.diplomacy: ~0 rows (approximately)

-- Dumping structure for table phpvian.emailinvite
CREATE TABLE IF NOT EXISTS `emailinvite` (
  `id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `uid` smallint unsigned NOT NULL,
  `invemail` char(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `time` int unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.emailinvite: ~0 rows (approximately)

-- Dumping structure for table phpvian.enforcement
CREATE TABLE IF NOT EXISTS `enforcement` (
  `id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `hero` tinyint unsigned NOT NULL DEFAULT '0',
  `u1` int unsigned NOT NULL DEFAULT '0',
  `u2` int unsigned NOT NULL DEFAULT '0',
  `u3` int unsigned NOT NULL DEFAULT '0',
  `u4` int unsigned NOT NULL DEFAULT '0',
  `u5` int unsigned NOT NULL DEFAULT '0',
  `u6` int unsigned NOT NULL DEFAULT '0',
  `u7` int unsigned NOT NULL DEFAULT '0',
  `u8` int unsigned NOT NULL DEFAULT '0',
  `u9` int unsigned NOT NULL DEFAULT '0',
  `u10` int unsigned NOT NULL DEFAULT '0',
  `u11` int unsigned NOT NULL DEFAULT '0',
  `u12` int unsigned NOT NULL DEFAULT '0',
  `u13` int unsigned NOT NULL DEFAULT '0',
  `u14` int unsigned NOT NULL DEFAULT '0',
  `u15` int unsigned NOT NULL DEFAULT '0',
  `u16` int unsigned NOT NULL DEFAULT '0',
  `u17` int unsigned NOT NULL DEFAULT '0',
  `u18` int unsigned NOT NULL DEFAULT '0',
  `u19` int unsigned NOT NULL DEFAULT '0',
  `u20` int unsigned NOT NULL DEFAULT '0',
  `u21` int unsigned NOT NULL DEFAULT '0',
  `u22` int unsigned NOT NULL DEFAULT '0',
  `u23` int unsigned NOT NULL DEFAULT '0',
  `u24` int unsigned NOT NULL DEFAULT '0',
  `u25` int unsigned NOT NULL DEFAULT '0',
  `u26` int unsigned NOT NULL DEFAULT '0',
  `u27` int unsigned NOT NULL DEFAULT '0',
  `u28` int unsigned NOT NULL DEFAULT '0',
  `u29` int unsigned NOT NULL DEFAULT '0',
  `u30` int unsigned NOT NULL DEFAULT '0',
  `u31` int unsigned NOT NULL DEFAULT '0',
  `u32` int unsigned NOT NULL DEFAULT '0',
  `u33` int unsigned NOT NULL DEFAULT '0',
  `u34` int unsigned NOT NULL DEFAULT '0',
  `u35` int unsigned NOT NULL DEFAULT '0',
  `u36` int unsigned NOT NULL DEFAULT '0',
  `u37` int unsigned NOT NULL DEFAULT '0',
  `u38` int unsigned NOT NULL DEFAULT '0',
  `u39` int unsigned NOT NULL DEFAULT '0',
  `u40` int unsigned NOT NULL DEFAULT '0',
  `u41` int unsigned NOT NULL DEFAULT '0',
  `u42` int unsigned NOT NULL DEFAULT '0',
  `u43` int unsigned NOT NULL DEFAULT '0',
  `u44` int unsigned NOT NULL DEFAULT '0',
  `u45` int unsigned NOT NULL DEFAULT '0',
  `u46` int unsigned NOT NULL DEFAULT '0',
  `u47` int unsigned NOT NULL DEFAULT '0',
  `u48` int unsigned NOT NULL DEFAULT '0',
  `u49` int unsigned NOT NULL DEFAULT '0',
  `u50` int unsigned NOT NULL DEFAULT '0',
  `from` smallint unsigned NOT NULL DEFAULT '0',
  `vref` smallint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `from` (`from`),
  KEY `vref` (`vref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.enforcement: ~0 rows (approximately)

-- Dumping structure for table phpvian.farmlist
CREATE TABLE IF NOT EXISTS `farmlist` (
  `id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `wref` smallint unsigned NOT NULL,
  `owner` smallint unsigned NOT NULL,
  `name` char(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.farmlist: ~0 rows (approximately)

-- Dumping structure for table phpvian.fdata
CREATE TABLE IF NOT EXISTS `fdata` (
  `vref` smallint unsigned NOT NULL,
  `f1` tinyint unsigned NOT NULL DEFAULT '0',
  `f1t` tinyint unsigned NOT NULL DEFAULT '0',
  `f2` tinyint unsigned NOT NULL DEFAULT '0',
  `f2t` tinyint unsigned NOT NULL DEFAULT '0',
  `f3` tinyint unsigned NOT NULL DEFAULT '0',
  `f3t` tinyint unsigned NOT NULL DEFAULT '0',
  `f4` tinyint unsigned NOT NULL DEFAULT '0',
  `f4t` tinyint unsigned NOT NULL DEFAULT '0',
  `f5` tinyint unsigned NOT NULL DEFAULT '0',
  `f5t` tinyint unsigned NOT NULL DEFAULT '0',
  `f6` tinyint unsigned NOT NULL DEFAULT '0',
  `f6t` tinyint unsigned NOT NULL DEFAULT '0',
  `f7` tinyint unsigned NOT NULL DEFAULT '0',
  `f7t` tinyint unsigned NOT NULL DEFAULT '0',
  `f8` tinyint unsigned NOT NULL DEFAULT '0',
  `f8t` tinyint unsigned NOT NULL DEFAULT '0',
  `f9` tinyint unsigned NOT NULL DEFAULT '0',
  `f9t` tinyint unsigned NOT NULL DEFAULT '0',
  `f10` tinyint unsigned NOT NULL DEFAULT '0',
  `f10t` tinyint unsigned NOT NULL DEFAULT '0',
  `f11` tinyint unsigned NOT NULL DEFAULT '0',
  `f11t` tinyint unsigned NOT NULL DEFAULT '0',
  `f12` tinyint unsigned NOT NULL DEFAULT '0',
  `f12t` tinyint unsigned NOT NULL DEFAULT '0',
  `f13` tinyint unsigned NOT NULL DEFAULT '0',
  `f13t` tinyint unsigned NOT NULL DEFAULT '0',
  `f14` tinyint unsigned NOT NULL DEFAULT '0',
  `f14t` tinyint unsigned NOT NULL DEFAULT '0',
  `f15` tinyint unsigned NOT NULL DEFAULT '0',
  `f15t` tinyint unsigned NOT NULL DEFAULT '0',
  `f16` tinyint unsigned NOT NULL DEFAULT '0',
  `f16t` tinyint unsigned NOT NULL DEFAULT '0',
  `f17` tinyint unsigned NOT NULL DEFAULT '0',
  `f17t` tinyint unsigned NOT NULL DEFAULT '0',
  `f18` tinyint unsigned NOT NULL DEFAULT '0',
  `f18t` tinyint unsigned NOT NULL DEFAULT '0',
  `f19` tinyint unsigned NOT NULL DEFAULT '0',
  `f19t` tinyint unsigned NOT NULL DEFAULT '0',
  `f20` tinyint unsigned NOT NULL DEFAULT '0',
  `f20t` tinyint unsigned NOT NULL DEFAULT '0',
  `f21` tinyint unsigned NOT NULL DEFAULT '0',
  `f21t` tinyint unsigned NOT NULL DEFAULT '0',
  `f22` tinyint unsigned NOT NULL DEFAULT '0',
  `f22t` tinyint unsigned NOT NULL DEFAULT '0',
  `f23` tinyint unsigned NOT NULL DEFAULT '0',
  `f23t` tinyint unsigned NOT NULL DEFAULT '0',
  `f24` tinyint unsigned NOT NULL DEFAULT '0',
  `f24t` tinyint unsigned NOT NULL DEFAULT '0',
  `f25` tinyint unsigned NOT NULL DEFAULT '0',
  `f25t` tinyint unsigned NOT NULL DEFAULT '0',
  `f26` tinyint unsigned NOT NULL DEFAULT '0',
  `f26t` tinyint unsigned NOT NULL DEFAULT '0',
  `f27` tinyint unsigned NOT NULL DEFAULT '0',
  `f27t` tinyint unsigned NOT NULL DEFAULT '0',
  `f28` tinyint unsigned NOT NULL DEFAULT '0',
  `f28t` tinyint unsigned NOT NULL DEFAULT '0',
  `f29` tinyint unsigned NOT NULL DEFAULT '0',
  `f29t` tinyint unsigned NOT NULL DEFAULT '0',
  `f30` tinyint unsigned NOT NULL DEFAULT '0',
  `f30t` tinyint unsigned NOT NULL DEFAULT '0',
  `f31` tinyint unsigned NOT NULL DEFAULT '0',
  `f31t` tinyint unsigned NOT NULL DEFAULT '0',
  `f32` tinyint unsigned NOT NULL DEFAULT '0',
  `f32t` tinyint unsigned NOT NULL DEFAULT '0',
  `f33` tinyint unsigned NOT NULL DEFAULT '0',
  `f33t` tinyint unsigned NOT NULL DEFAULT '0',
  `f34` tinyint unsigned NOT NULL DEFAULT '0',
  `f34t` tinyint unsigned NOT NULL DEFAULT '0',
  `f35` tinyint unsigned NOT NULL DEFAULT '0',
  `f35t` tinyint unsigned NOT NULL DEFAULT '0',
  `f36` tinyint unsigned NOT NULL DEFAULT '0',
  `f36t` tinyint unsigned NOT NULL DEFAULT '0',
  `f37` tinyint unsigned NOT NULL DEFAULT '0',
  `f37t` tinyint unsigned NOT NULL DEFAULT '0',
  `f38` tinyint unsigned NOT NULL DEFAULT '0',
  `f38t` tinyint unsigned NOT NULL DEFAULT '0',
  `f39` tinyint unsigned NOT NULL DEFAULT '0',
  `f39t` tinyint unsigned NOT NULL DEFAULT '0',
  `f40` tinyint unsigned NOT NULL DEFAULT '0',
  `f40t` tinyint unsigned NOT NULL DEFAULT '0',
  `f99` tinyint unsigned NOT NULL DEFAULT '0',
  `f99t` tinyint unsigned NOT NULL DEFAULT '0',
  `wwname` char(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`vref`),
  KEY `vref` (`vref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.fdata: ~0 rows (approximately)

-- Dumping structure for table phpvian.forum_cat
CREATE TABLE IF NOT EXISTS `forum_cat` (
  `id` smallint NOT NULL AUTO_INCREMENT,
  `owner` smallint NOT NULL,
  `alliance` smallint NOT NULL,
  `forum_name` char(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `forum_des` char(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `forum_area` tinyint NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.forum_cat: ~0 rows (approximately)

-- Dumping structure for table phpvian.forum_edit
CREATE TABLE IF NOT EXISTS `forum_edit` (
  `id` smallint NOT NULL AUTO_INCREMENT,
  `alliance` smallint NOT NULL,
  `result` char(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.forum_edit: ~0 rows (approximately)

-- Dumping structure for table phpvian.forum_poll
CREATE TABLE IF NOT EXISTS `forum_poll` (
  `id` int NOT NULL,
  `name` char(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `p1` mediumint NOT NULL,
  `p2` mediumint NOT NULL,
  `p3` mediumint NOT NULL,
  `p4` mediumint NOT NULL,
  `p1_name` char(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `p2_name` char(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `p3_name` char(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `p4_name` char(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `voters` varchar(400) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.forum_poll: ~0 rows (approximately)

-- Dumping structure for table phpvian.forum_post
CREATE TABLE IF NOT EXISTS `forum_post` (
  `id` smallint NOT NULL AUTO_INCREMENT,
  `post` longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `topic` char(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `owner` smallint NOT NULL,
  `date` int NOT NULL,
  `alliance0` int unsigned NOT NULL,
  `player0` int unsigned NOT NULL,
  `coor0` int unsigned NOT NULL,
  `report0` int unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.forum_post: ~0 rows (approximately)

-- Dumping structure for table phpvian.forum_topic
CREATE TABLE IF NOT EXISTS `forum_topic` (
  `id` smallint NOT NULL AUTO_INCREMENT,
  `title` char(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `post` longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `date` int NOT NULL,
  `post_date` int NOT NULL,
  `cat` smallint NOT NULL,
  `owner` smallint NOT NULL,
  `alliance` smallint NOT NULL,
  `ends` char(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `close` char(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `stick` char(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.forum_topic: ~0 rows (approximately)

-- Dumping structure for table phpvian.fpost_rules
CREATE TABLE IF NOT EXISTS `fpost_rules` (
  `id` int NOT NULL,
  `forum_id` int NOT NULL,
  `players_id` int NOT NULL,
  `players_name` char(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_persian_ci NOT NULL,
  `ally_id` int NOT NULL,
  `ally_tag` char(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_persian_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.fpost_rules: ~0 rows (approximately)

-- Dumping structure for table phpvian.gold_fin_log
CREATE TABLE IF NOT EXISTS `gold_fin_log` (
  `id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `wid` smallint unsigned NOT NULL,
  `log` char(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.gold_fin_log: ~0 rows (approximately)

-- Dumping structure for table phpvian.hero
CREATE TABLE IF NOT EXISTS `hero` (
  `heroid` smallint unsigned NOT NULL AUTO_INCREMENT,
  `uid` smallint unsigned NOT NULL,
  `wref` smallint unsigned NOT NULL,
  `level` tinyint unsigned NOT NULL,
  `adv` smallint unsigned NOT NULL,
  `sucsadv` smallint unsigned NOT NULL,
  `speed` tinyint unsigned NOT NULL,
  `itemspeed` tinyint unsigned NOT NULL,
  `points` smallint unsigned NOT NULL,
  `experience` int NOT NULL,
  `dead` tinyint NOT NULL,
  `health` double(10,7) unsigned NOT NULL DEFAULT '100.0000000',
  `power` tinyint unsigned NOT NULL,
  `fsperpoint` tinyint unsigned NOT NULL,
  `itemfs` smallint unsigned NOT NULL,
  `offBonus` tinyint unsigned NOT NULL,
  `defBonus` tinyint unsigned NOT NULL,
  `product` tinyint unsigned NOT NULL,
  `r0` tinyint unsigned NOT NULL,
  `r1` tinyint unsigned NOT NULL,
  `r2` tinyint unsigned NOT NULL,
  `r3` tinyint unsigned NOT NULL,
  `r4` tinyint unsigned NOT NULL,
  `rc` tinyint unsigned NOT NULL,
  `autoregen` tinyint NOT NULL,
  `itemautoregen` tinyint NOT NULL,
  `extraexpgain` tinyint NOT NULL,
  `itemextraexpgain` tinyint NOT NULL,
  `cpproduction` tinyint NOT NULL,
  `itemcpproduction` tinyint NOT NULL,
  `infantrytrain` tinyint NOT NULL,
  `iteminfantrytrain` tinyint NOT NULL,
  `cavalrytrain` tinyint NOT NULL,
  `itemcavalrytrain` tinyint NOT NULL,
  `rob` tinyint NOT NULL,
  `itemrob` tinyint NOT NULL,
  `extraresist` tinyint NOT NULL,
  `itemextraresist` tinyint NOT NULL,
  `vsnatars` tinyint NOT NULL,
  `itemvsnatars` tinyint NOT NULL,
  `accountmspeed` tinyint NOT NULL,
  `itemaccountmspeed` tinyint NOT NULL,
  `allymspeed` tinyint NOT NULL,
  `itemallymspeed` tinyint NOT NULL,
  `longwaymspeed` tinyint NOT NULL,
  `itemlongwaymspeed` tinyint NOT NULL,
  `returnmspeed` tinyint NOT NULL,
  `itemreturnmspeed` tinyint NOT NULL,
  `lastupdate` int unsigned NOT NULL,
  `lastadv` int unsigned NOT NULL DEFAULT '0',
  `hash` char(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `hide` tinyint unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`heroid`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.hero: ~0 rows (approximately)

-- Dumping structure for table phpvian.heroface
CREATE TABLE IF NOT EXISTS `heroface` (
  `uid` smallint unsigned NOT NULL,
  `gender` tinyint NOT NULL,
  `beard` tinyint NOT NULL,
  `ear` tinyint NOT NULL,
  `eye` tinyint NOT NULL,
  `eyebrow` tinyint NOT NULL,
  `face` tinyint NOT NULL,
  `hair` tinyint NOT NULL,
  `mouth` tinyint NOT NULL,
  `nose` tinyint NOT NULL,
  `color` tinyint NOT NULL,
  `foot` tinyint unsigned NOT NULL,
  `helmet` smallint unsigned NOT NULL,
  `body` smallint unsigned NOT NULL,
  `shoes` smallint unsigned NOT NULL,
  `horse` smallint unsigned NOT NULL,
  `leftHand` smallint NOT NULL,
  `rightHand` smallint NOT NULL,
  `bag` smallint NOT NULL,
  `num` smallint NOT NULL,
  `hash` char(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.heroface: ~3 rows (approximately)
INSERT INTO `heroface` (`uid`, `gender`, `beard`, `ear`, `eye`, `eyebrow`, `face`, `hair`, `mouth`, `nose`, `color`, `foot`, `helmet`, `body`, `shoes`, `horse`, `leftHand`, `rightHand`, `bag`, `num`, `hash`) VALUES
	(1, 0, 1, 2, 3, 2, 4, 3, 1, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0, 0, ''),
	(2, 0, 1, 2, 3, 2, 4, 3, 1, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0, 0, ''),
	(4, 0, 1, 2, 3, 2, 4, 3, 1, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0, 0, '');

-- Dumping structure for table phpvian.heroitems
CREATE TABLE IF NOT EXISTS `heroitems` (
  `id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `uid` smallint unsigned NOT NULL,
  `btype` tinyint unsigned NOT NULL,
  `type` tinyint unsigned NOT NULL,
  `num` smallint NOT NULL,
  `proc` tinyint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `btype` (`btype`),
  KEY `type` (`type`),
  KEY `proc` (`proc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.heroitems: ~0 rows (approximately)

-- Dumping structure for table phpvian.links
CREATE TABLE IF NOT EXISTS `links` (
  `id` mediumint unsigned NOT NULL AUTO_INCREMENT,
  `userid` smallint unsigned NOT NULL,
  `name` char(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `url` char(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `pos` smallint unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.links: ~0 rows (approximately)

-- Dumping structure for table phpvian.map_marks
CREATE TABLE IF NOT EXISTS `map_marks` (
  `id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `uid` smallint NOT NULL,
  `x` smallint NOT NULL,
  `y` smallint NOT NULL,
  `index` tinyint NOT NULL,
  `text` char(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_persian_ci NOT NULL,
  `kid` int NOT NULL,
  `plus` int NOT NULL,
  `type` char(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_persian_ci NOT NULL,
  `dataId` smallint NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.map_marks: ~0 rows (approximately)

-- Dumping structure for table phpvian.market
CREATE TABLE IF NOT EXISTS `market` (
  `id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `vref` smallint unsigned NOT NULL,
  `gtype` tinyint unsigned NOT NULL,
  `gamt` int unsigned NOT NULL,
  `wtype` tinyint unsigned NOT NULL,
  `wamt` int unsigned NOT NULL,
  `accept` tinyint unsigned NOT NULL,
  `maxtime` int unsigned NOT NULL,
  `alliance` int unsigned NOT NULL,
  `merchant` tinyint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `vref` (`vref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.market: ~0 rows (approximately)

-- Dumping structure for table phpvian.mdata
CREATE TABLE IF NOT EXISTS `mdata` (
  `id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `target` smallint unsigned NOT NULL,
  `owner` smallint unsigned NOT NULL,
  `topic` char(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_persian_ci NOT NULL,
  `message` varchar(1000) CHARACTER SET utf8mb3 COLLATE utf8mb3_persian_ci NOT NULL,
  `viewed` tinyint unsigned NOT NULL,
  `archived` tinyint unsigned NOT NULL,
  `send` tinyint unsigned NOT NULL,
  `time` int unsigned NOT NULL DEFAULT '0',
  `deltarget` tinyint unsigned NOT NULL,
  `delowner` tinyint unsigned NOT NULL,
  `alliance` smallint unsigned NOT NULL,
  `player` smallint unsigned NOT NULL,
  `coor` smallint unsigned NOT NULL,
  `report` smallint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `time` (`time`),
  KEY `target` (`target`),
  KEY `owner` (`owner`),
  KEY `send` (`send`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for table phpvian.mdata: ~0 rows (approximately)

-- Dumping structure for table phpvian.medal
CREATE TABLE IF NOT EXISTS `medal` (
  `id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `userid` smallint unsigned NOT NULL,
  `categorie` tinyint unsigned NOT NULL,
  `plaats` tinyint unsigned NOT NULL,
  `week` tinyint unsigned NOT NULL,
  `points` bigint NOT NULL,
  `img` char(8) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.medal: ~0 rows (approximately)

-- Dumping structure for table phpvian.movement
CREATE TABLE IF NOT EXISTS `movement` (
  `moveid` mediumint unsigned NOT NULL AUTO_INCREMENT,
  `sort_type` tinyint unsigned NOT NULL DEFAULT '0',
  `from` smallint unsigned NOT NULL DEFAULT '0',
  `to` smallint unsigned NOT NULL DEFAULT '0',
  `ref` mediumint unsigned NOT NULL DEFAULT '0',
  `data` varchar(400) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `starttime` int unsigned NOT NULL DEFAULT '0',
  `endtime` int unsigned NOT NULL DEFAULT '0',
  `proc` tinyint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`moveid`),
  KEY `proc` (`proc`),
  KEY `endtime` (`endtime`),
  KEY `sort_type` (`sort_type`),
  KEY `from` (`from`),
  KEY `to` (`to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.movement: ~0 rows (approximately)

-- Dumping structure for table phpvian.msg_reports
CREATE TABLE IF NOT EXISTS `msg_reports` (
  `id` tinyint unsigned NOT NULL AUTO_INCREMENT,
  `msg_id` smallint unsigned NOT NULL,
  `reported_by` char(15) CHARACTER SET utf8mb3 COLLATE utf8mb3_persian_ci NOT NULL,
  `viewed` tinyint NOT NULL DEFAULT '0',
  `delet` tinyint NOT NULL DEFAULT '0',
  `reason` char(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_persian_ci NOT NULL,
  `time` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.msg_reports: ~0 rows (approximately)

-- Dumping structure for table phpvian.natarsprogress
CREATE TABLE IF NOT EXISTS `natarsprogress` (
  `lastexpandat` int NOT NULL,
  `lastpopupedvillage` int NOT NULL,
  `lastpopupat` int NOT NULL,
  `artefactreleased` tinyint NOT NULL,
  `artefactreleasedat` int NOT NULL,
  `wwbpreleased` tinyint NOT NULL,
  `wwbpreleasedat` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.natarsprogress: ~0 rows (approximately)
INSERT INTO `natarsprogress` (`lastexpandat`, `lastpopupedvillage`, `lastpopupat`, `artefactreleased`, `artefactreleasedat`, `wwbpreleased`, `wwbpreleasedat`) VALUES
	(0, 0, 0, 0, 0, 0, 0);

-- Dumping structure for table phpvian.ndata
CREATE TABLE IF NOT EXISTS `ndata` (
  `id` mediumint unsigned NOT NULL AUTO_INCREMENT,
  `uid` smallint unsigned NOT NULL,
  `toWref` smallint unsigned NOT NULL,
  `ally` smallint unsigned NOT NULL,
  `topic` varchar(600) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `ntype` tinyint unsigned NOT NULL,
  `data` varchar(600) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `time` int unsigned NOT NULL,
  `viewed` tinyint unsigned NOT NULL,
  `archive` tinyint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `time` (`time`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.ndata: ~0 rows (approximately)

-- Dumping structure for table phpvian.newproc
CREATE TABLE IF NOT EXISTS `newproc` (
  `uid` smallint unsigned NOT NULL AUTO_INCREMENT,
  `npw` char(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `nemail` char(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `act` char(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `time` int unsigned NOT NULL,
  `proc` tinyint unsigned NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.newproc: ~0 rows (approximately)

-- Dumping structure for table phpvian.odata
CREATE TABLE IF NOT EXISTS `odata` (
  `wref` smallint unsigned NOT NULL,
  `type` tinyint unsigned NOT NULL,
  `conqured` mediumint unsigned NOT NULL,
  `wood` float(12,2) NOT NULL,
  `iron` float(12,2) NOT NULL,
  `clay` float(12,2) NOT NULL,
  `woodp` float(12,2) NOT NULL,
  `ironp` float(12,2) NOT NULL,
  `clayp` float(12,2) NOT NULL,
  `maxstore` mediumint unsigned NOT NULL,
  `crop` float(12,2) NOT NULL,
  `cropp` float(12,2) NOT NULL,
  `maxcrop` mediumint unsigned NOT NULL,
  `lasttrain` int unsigned NOT NULL,
  `lastfarmed` int unsigned NOT NULL,
  `lastupdated` int unsigned NOT NULL,
  `loyalty` tinyint NOT NULL DEFAULT '100',
  `owner` smallint unsigned NOT NULL DEFAULT '2',
  `name` char(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT 'Unoccupied Oasis',
  PRIMARY KEY (`wref`),
  KEY `conqured` (`conqured`),
  KEY `loyalty` (`loyalty`),
  KEY `owner` (`owner`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.odata: ~0 rows (approximately)

-- Dumping structure for table phpvian.online
CREATE TABLE IF NOT EXISTS `online` (
  `name` char(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `time` int NOT NULL,
  `sitter` int unsigned NOT NULL,
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.online: ~0 rows (approximately)

-- Dumping structure for table phpvian.raidlist
CREATE TABLE IF NOT EXISTS `raidlist` (
  `id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `lid` smallint NOT NULL,
  `towref` smallint unsigned NOT NULL,
  `x` smallint NOT NULL,
  `y` smallint NOT NULL,
  `distance` char(5) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '0',
  `t1` int unsigned NOT NULL,
  `t2` int unsigned NOT NULL,
  `t3` int unsigned NOT NULL,
  `t4` int unsigned NOT NULL,
  `t5` int unsigned NOT NULL,
  `t6` int unsigned NOT NULL,
  `t7` int unsigned NOT NULL,
  `t8` int unsigned NOT NULL,
  `t9` int unsigned NOT NULL,
  `t10` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `lid` (`lid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.raidlist: ~0 rows (approximately)

-- Dumping structure for table phpvian.refrence
CREATE TABLE IF NOT EXISTS `refrence` (
  `id` smallint NOT NULL AUTO_INCREMENT,
  `player_id` smallint NOT NULL,
  `player_name` char(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.refrence: ~0 rows (approximately)

-- Dumping structure for table phpvian.research
CREATE TABLE IF NOT EXISTS `research` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `vref` int unsigned NOT NULL,
  `tech` char(3) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `timestamp` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `vref` (`vref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.research: ~0 rows (approximately)

-- Dumping structure for table phpvian.route
CREATE TABLE IF NOT EXISTS `route` (
  `id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `uid` smallint unsigned NOT NULL,
  `wid` smallint unsigned NOT NULL,
  `from` smallint unsigned NOT NULL,
  `wood` int unsigned NOT NULL,
  `clay` int unsigned NOT NULL,
  `iron` int unsigned NOT NULL,
  `crop` int unsigned NOT NULL,
  `start` tinyint unsigned NOT NULL,
  `deliveries` tinyint unsigned NOT NULL,
  `merchant` mediumint unsigned NOT NULL,
  `timestamp` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `wid` (`wid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.route: ~0 rows (approximately)

-- Dumping structure for table phpvian.send
CREATE TABLE IF NOT EXISTS `send` (
  `id` mediumint unsigned NOT NULL AUTO_INCREMENT,
  `wood` int unsigned NOT NULL,
  `clay` int unsigned NOT NULL,
  `iron` int unsigned NOT NULL,
  `crop` int unsigned NOT NULL,
  `merchant` tinyint unsigned NOT NULL,
  `send` tinyint unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.send: ~0 rows (approximately)

-- Dumping structure for table phpvian.stats
CREATE TABLE IF NOT EXISTS `stats` (
  `id` mediumint NOT NULL AUTO_INCREMENT,
  `owner` smallint NOT NULL,
  `troops` int NOT NULL,
  `troops_reinf` int NOT NULL,
  `resources` int NOT NULL,
  `pop` smallint NOT NULL,
  `rank` smallint NOT NULL,
  `time` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `index` (`id`,`owner`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.stats: ~0 rows (approximately)

-- Dumping structure for table phpvian.tdata
CREATE TABLE IF NOT EXISTS `tdata` (
  `vref` smallint unsigned NOT NULL,
  `t2` tinyint unsigned NOT NULL DEFAULT '0',
  `t3` tinyint unsigned NOT NULL DEFAULT '0',
  `t4` tinyint unsigned NOT NULL DEFAULT '0',
  `t5` tinyint unsigned NOT NULL DEFAULT '0',
  `t6` tinyint unsigned NOT NULL DEFAULT '0',
  `t7` tinyint unsigned NOT NULL DEFAULT '0',
  `t8` tinyint unsigned NOT NULL DEFAULT '0',
  `t9` tinyint unsigned NOT NULL DEFAULT '0',
  `t12` tinyint unsigned NOT NULL DEFAULT '0',
  `t13` tinyint unsigned NOT NULL DEFAULT '0',
  `t14` tinyint unsigned NOT NULL DEFAULT '0',
  `t15` tinyint unsigned NOT NULL DEFAULT '0',
  `t16` tinyint unsigned NOT NULL DEFAULT '0',
  `t17` tinyint unsigned NOT NULL DEFAULT '0',
  `t18` tinyint unsigned NOT NULL DEFAULT '0',
  `t19` tinyint unsigned NOT NULL DEFAULT '0',
  `t22` tinyint unsigned NOT NULL DEFAULT '0',
  `t23` tinyint unsigned NOT NULL DEFAULT '0',
  `t24` tinyint unsigned NOT NULL DEFAULT '0',
  `t25` tinyint unsigned NOT NULL DEFAULT '0',
  `t26` tinyint unsigned NOT NULL DEFAULT '0',
  `t27` tinyint unsigned NOT NULL DEFAULT '0',
  `t28` tinyint unsigned NOT NULL DEFAULT '0',
  `t29` tinyint unsigned NOT NULL DEFAULT '0',
  `t32` tinyint unsigned NOT NULL DEFAULT '0',
  `t33` tinyint unsigned NOT NULL DEFAULT '0',
  `t34` tinyint unsigned NOT NULL DEFAULT '0',
  `t35` tinyint unsigned NOT NULL DEFAULT '0',
  `t36` tinyint unsigned NOT NULL DEFAULT '0',
  `t37` tinyint unsigned NOT NULL DEFAULT '0',
  `t38` tinyint unsigned NOT NULL DEFAULT '0',
  `t39` tinyint unsigned NOT NULL DEFAULT '0',
  `t42` tinyint unsigned NOT NULL DEFAULT '0',
  `t43` tinyint unsigned NOT NULL DEFAULT '0',
  `t44` tinyint unsigned NOT NULL DEFAULT '0',
  `t45` tinyint unsigned NOT NULL DEFAULT '0',
  `t46` tinyint unsigned NOT NULL DEFAULT '0',
  `t47` tinyint unsigned NOT NULL DEFAULT '0',
  `t48` tinyint unsigned NOT NULL DEFAULT '0',
  `t49` tinyint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`vref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.tdata: ~0 rows (approximately)

-- Dumping structure for table phpvian.training
CREATE TABLE IF NOT EXISTS `training` (
  `id` mediumint unsigned NOT NULL AUTO_INCREMENT,
  `vref` smallint unsigned NOT NULL,
  `unit` tinyint unsigned NOT NULL,
  `amt` int unsigned NOT NULL,
  `pop` tinyint unsigned NOT NULL,
  `timestamp` int unsigned NOT NULL,
  `eachtime` smallint unsigned NOT NULL,
  `commence` int unsigned NOT NULL,
  `endat` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `unit` (`unit`),
  KEY `endat` (`endat`),
  KEY `amt` (`amt`),
  KEY `vref` (`vref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.training: ~0 rows (approximately)

-- Dumping structure for table phpvian.transactions
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `playerId` int NOT NULL,
  `amunt` double NOT NULL,
  `gold` int NOT NULL,
  `time` int NOT NULL,
  `status` int NOT NULL DEFAULT '0',
  `orderid` char(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.transactions: ~0 rows (approximately)

-- Dumping structure for table phpvian.trapped
CREATE TABLE IF NOT EXISTS `trapped` (
  `id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `hero` tinyint unsigned NOT NULL DEFAULT '0',
  `u1` int unsigned NOT NULL DEFAULT '0',
  `u2` int unsigned NOT NULL DEFAULT '0',
  `u3` int unsigned NOT NULL DEFAULT '0',
  `u4` int unsigned NOT NULL DEFAULT '0',
  `u5` int unsigned NOT NULL DEFAULT '0',
  `u6` int unsigned NOT NULL DEFAULT '0',
  `u7` int unsigned NOT NULL DEFAULT '0',
  `u8` int unsigned NOT NULL DEFAULT '0',
  `u9` int unsigned NOT NULL DEFAULT '0',
  `u10` int unsigned NOT NULL DEFAULT '0',
  `u11` int unsigned NOT NULL DEFAULT '0',
  `u12` int unsigned NOT NULL DEFAULT '0',
  `u13` int unsigned NOT NULL DEFAULT '0',
  `u14` int unsigned NOT NULL DEFAULT '0',
  `u15` int unsigned NOT NULL DEFAULT '0',
  `u16` int unsigned NOT NULL DEFAULT '0',
  `u17` int unsigned NOT NULL DEFAULT '0',
  `u18` int unsigned NOT NULL DEFAULT '0',
  `u19` int unsigned NOT NULL DEFAULT '0',
  `u20` int unsigned NOT NULL DEFAULT '0',
  `u21` int unsigned NOT NULL DEFAULT '0',
  `u22` int unsigned NOT NULL DEFAULT '0',
  `u23` int unsigned NOT NULL DEFAULT '0',
  `u24` int unsigned NOT NULL DEFAULT '0',
  `u25` int unsigned NOT NULL DEFAULT '0',
  `u26` int unsigned NOT NULL DEFAULT '0',
  `u27` int unsigned NOT NULL DEFAULT '0',
  `u28` int unsigned NOT NULL DEFAULT '0',
  `u29` int unsigned NOT NULL DEFAULT '0',
  `u30` int unsigned NOT NULL DEFAULT '0',
  `u31` int unsigned NOT NULL DEFAULT '0',
  `u32` int unsigned NOT NULL DEFAULT '0',
  `u33` int unsigned NOT NULL DEFAULT '0',
  `u34` int unsigned NOT NULL DEFAULT '0',
  `u35` int unsigned NOT NULL DEFAULT '0',
  `u36` int unsigned NOT NULL DEFAULT '0',
  `u37` int unsigned NOT NULL DEFAULT '0',
  `u38` int unsigned NOT NULL DEFAULT '0',
  `u39` int unsigned NOT NULL DEFAULT '0',
  `u40` int unsigned NOT NULL DEFAULT '0',
  `u41` int unsigned NOT NULL DEFAULT '0',
  `u42` int unsigned NOT NULL DEFAULT '0',
  `u43` int unsigned NOT NULL DEFAULT '0',
  `u44` int unsigned NOT NULL DEFAULT '0',
  `u45` int unsigned NOT NULL DEFAULT '0',
  `u46` int unsigned NOT NULL DEFAULT '0',
  `u47` int unsigned NOT NULL DEFAULT '0',
  `u48` int unsigned NOT NULL DEFAULT '0',
  `u49` int unsigned NOT NULL DEFAULT '0',
  `u50` int unsigned NOT NULL DEFAULT '0',
  `from` smallint unsigned NOT NULL DEFAULT '0',
  `vref` smallint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `from` (`from`),
  KEY `vref` (`vref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.trapped: ~0 rows (approximately)

-- Dumping structure for table phpvian.units
CREATE TABLE IF NOT EXISTS `units` (
  `vref` smallint unsigned NOT NULL,
  `hero` tinyint unsigned NOT NULL DEFAULT '0',
  `u1` int unsigned NOT NULL DEFAULT '0',
  `u2` int unsigned NOT NULL DEFAULT '0',
  `u3` int unsigned NOT NULL DEFAULT '0',
  `u4` int unsigned NOT NULL DEFAULT '0',
  `u5` int unsigned NOT NULL DEFAULT '0',
  `u6` int unsigned NOT NULL DEFAULT '0',
  `u7` int unsigned NOT NULL DEFAULT '0',
  `u8` int unsigned NOT NULL DEFAULT '0',
  `u9` int unsigned NOT NULL DEFAULT '0',
  `u10` int unsigned NOT NULL DEFAULT '0',
  `u11` int unsigned NOT NULL DEFAULT '0',
  `u12` int unsigned NOT NULL DEFAULT '0',
  `u13` int unsigned NOT NULL DEFAULT '0',
  `u14` int unsigned NOT NULL DEFAULT '0',
  `u15` int unsigned NOT NULL DEFAULT '0',
  `u16` int unsigned NOT NULL DEFAULT '0',
  `u17` int unsigned NOT NULL DEFAULT '0',
  `u18` int unsigned NOT NULL DEFAULT '0',
  `u19` int unsigned NOT NULL DEFAULT '0',
  `u20` int unsigned NOT NULL DEFAULT '0',
  `u21` int unsigned NOT NULL DEFAULT '0',
  `u22` int unsigned NOT NULL DEFAULT '0',
  `u23` int unsigned NOT NULL DEFAULT '0',
  `u24` int unsigned NOT NULL DEFAULT '0',
  `u25` int unsigned NOT NULL DEFAULT '0',
  `u26` int unsigned NOT NULL DEFAULT '0',
  `u27` int unsigned NOT NULL DEFAULT '0',
  `u28` int unsigned NOT NULL DEFAULT '0',
  `u29` int unsigned NOT NULL DEFAULT '0',
  `u30` int unsigned NOT NULL DEFAULT '0',
  `u31` int unsigned NOT NULL DEFAULT '0',
  `u32` int unsigned NOT NULL DEFAULT '0',
  `u33` int unsigned NOT NULL DEFAULT '0',
  `u34` int unsigned NOT NULL DEFAULT '0',
  `u35` int unsigned NOT NULL DEFAULT '0',
  `u36` int unsigned NOT NULL DEFAULT '0',
  `u37` int unsigned NOT NULL DEFAULT '0',
  `u38` int unsigned NOT NULL DEFAULT '0',
  `u39` int unsigned NOT NULL DEFAULT '0',
  `u40` int unsigned NOT NULL DEFAULT '0',
  `u41` int unsigned NOT NULL DEFAULT '0',
  `u42` int unsigned NOT NULL DEFAULT '0',
  `u43` int unsigned NOT NULL DEFAULT '0',
  `u44` int unsigned NOT NULL DEFAULT '0',
  `u45` int unsigned NOT NULL DEFAULT '0',
  `u46` int unsigned NOT NULL DEFAULT '0',
  `u47` int unsigned NOT NULL DEFAULT '0',
  `u48` int unsigned NOT NULL DEFAULT '0',
  `u49` int unsigned NOT NULL DEFAULT '0',
  `u50` int unsigned NOT NULL DEFAULT '0',
  `u199` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`vref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.units: ~0 rows (approximately)

-- Dumping structure for table phpvian.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `username` char(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `password` char(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `email` char(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `tribe` tinyint unsigned NOT NULL,
  `access` tinyint unsigned NOT NULL DEFAULT '1',
  `gold` smallint NOT NULL DEFAULT '0',
  `boughtgold` smallint NOT NULL DEFAULT '0',
  `giftgold` smallint NOT NULL DEFAULT '0',
  `transferedgold` smallint NOT NULL DEFAULT '0',
  `Addgold` smallint NOT NULL DEFAULT '0',
  `usedgold` smallint NOT NULL DEFAULT '0',
  `silver` smallint NOT NULL DEFAULT '0',
  `Addsilver` smallint NOT NULL DEFAULT '0',
  `giftsilver` smallint NOT NULL DEFAULT '0',
  `usedsilver` smallint NOT NULL DEFAULT '0',
  `ausilver` smallint NOT NULL DEFAULT '0',
  `bidsilver` smallint NOT NULL DEFAULT '0',
  `gender` tinyint unsigned NOT NULL DEFAULT '0',
  `birthday` date DEFAULT NULL,
  `location` char(40) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `desc1` char(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `desc2` char(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `plus` int unsigned NOT NULL DEFAULT '0',
  `goldclub` int unsigned NOT NULL DEFAULT '0',
  `b1` int unsigned NOT NULL DEFAULT '0',
  `b2` int unsigned NOT NULL DEFAULT '0',
  `b3` int unsigned NOT NULL DEFAULT '0',
  `b4` int unsigned NOT NULL DEFAULT '0',
  `b5` int unsigned NOT NULL DEFAULT '0',
  `att` int unsigned NOT NULL DEFAULT '0',
  `def` int unsigned NOT NULL DEFAULT '0',
  `sit1` int unsigned NOT NULL DEFAULT '0',
  `sit2` int unsigned NOT NULL DEFAULT '0',
  `alliance` int unsigned NOT NULL DEFAULT '0',
  `sessid` char(166) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `act` char(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `timestamp` int unsigned NOT NULL DEFAULT '0',
  `ap` int unsigned NOT NULL DEFAULT '0',
  `apall` int unsigned NOT NULL DEFAULT '0',
  `dp` int unsigned NOT NULL DEFAULT '0',
  `dpall` int unsigned NOT NULL DEFAULT '0',
  `protect` int unsigned NOT NULL,
  `quest` tinyint NOT NULL,
  `fquest` char(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '0,0,0,0,0,0,0,0,0,0',
  `quest_battle` char(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '0,0,0,0,0,0,0,0,0,0,0,0,0,0',
  `quest_economy` char(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '0,0,0,0,0,0,0,0,0',
  `quest_world` char(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '0,0,0,0,0,0,0,0,0,0,0,0,0,0',
  `gpack` char(60) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '/assets/images/',
  `cp` int unsigned NOT NULL DEFAULT '1',
  `lastupdate` int unsigned NOT NULL,
  `RR` bigint NOT NULL DEFAULT '0',
  `Rc` bigint NOT NULL DEFAULT '0',
  `ok` tinyint unsigned NOT NULL DEFAULT '0',
  `clp` bigint NOT NULL DEFAULT '0',
  `oldrank` bigint unsigned NOT NULL DEFAULT '0',
  `activateat` int unsigned NOT NULL,
  `lang` char(2) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT 'en',
  `ancestor` char(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `ancestorgold` int NOT NULL DEFAULT '0',
  `reg2` tinyint NOT NULL DEFAULT '0',
  `ignore_msg` char(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0',
  `ip` char(15) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `chat_config` tinyint NOT NULL DEFAULT '1',
  `timezone` tinyint NOT NULL DEFAULT '23',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQUE` (`username`,`email`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.users: ~0 rows (approximately)
INSERT INTO `users` (`id`, `username`, `password`, `email`, `tribe`, `access`, `gold`, `boughtgold`, `giftgold`, `transferedgold`, `Addgold`, `usedgold`, `silver`, `Addsilver`, `giftsilver`, `usedsilver`, `ausilver`, `bidsilver`, `gender`, `birthday`, `location`, `desc1`, `desc2`, `plus`, `goldclub`, `b1`, `b2`, `b3`, `b4`, `b5`, `att`, `def`, `sit1`, `sit2`, `alliance`, `sessid`, `act`, `timestamp`, `ap`, `apall`, `dp`, `dpall`, `protect`, `quest`, `fquest`, `quest_battle`, `quest_economy`, `quest_world`, `gpack`, `cp`, `lastupdate`, `RR`, `Rc`, `ok`, `clp`, `oldrank`, `activateat`, `lang`, `ancestor`, `ancestorgold`, `reg2`, `ignore_msg`, `ip`, `chat_config`, `timezone`) VALUES
	(1, 'Support', '', 'support@travian.sx', 1, 8, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '0000-00-00', '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 25, '35', '0,0,0,0,0,0,0,0,0,0,0,0,0,0', '0,0,0,0,0,0,0,0,0', '0,0,0,0,0,0,0,0,0,0,0,0,0,0', '/assets/images/', 1, 0, 0, 0, 0, 0, 0, 0, 'en', '', 0, 0, '0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0', '', 1, 23),
	(3, 'Nature', '', 'nature@travian.sx', 4, 8, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '0000-00-00', '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 25, '35', '0,0,0,0,0,0,0,0,0,0,0,0,0,0', '0,0,0,0,0,0,0,0,0', '0,0,0,0,0,0,0,0,0,0,0,0,0,0', '/assets/images/', 1, 0, 0, 0, 0, 0, 0, 0, 'en', '', 0, 0, '0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0', '', 1, 23),
	(4, 'Multihunter', '', 'multihunter@travian.sx', 4, 9, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '0000-00-00', '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 25, '35', '0,0,0,0,0,0,0,0,0,0,0,0,0,0', '0,0,0,0,0,0,0,0,0', '0,0,0,0,0,0,0,0,0,0,0,0,0,0', '/assets/images/', 1, 0, 0, 0, 0, 0, 0, 0, 'en', '', 0, 0, '0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0', '', 1, 23);

-- Dumping structure for table phpvian.users_setting
CREATE TABLE IF NOT EXISTS `users_setting` (
  `id` smallint NOT NULL,
  `sitter1_set_1` tinyint NOT NULL,
  `sitter1_set_2` tinyint NOT NULL,
  `sitter1_set_3` tinyint NOT NULL,
  `sitter1_set_4` tinyint NOT NULL,
  `sitter1_set_5` tinyint NOT NULL,
  `sitter1_set_6` tinyint NOT NULL,
  `sitter2_set_1` tinyint NOT NULL,
  `sitter2_set_2` tinyint NOT NULL,
  `sitter2_set_3` tinyint NOT NULL,
  `sitter2_set_4` tinyint NOT NULL,
  `sitter2_set_5` tinyint NOT NULL,
  `sitter2_set_6` tinyint NOT NULL,
  `nopicn` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPRESSED;

-- Dumping data for table phpvian.users_setting: ~0 rows (approximately)

-- Dumping structure for table phpvian.vdata
CREATE TABLE IF NOT EXISTS `vdata` (
  `wref` int unsigned NOT NULL,
  `owner` int unsigned NOT NULL,
  `name` char(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `capital` tinyint unsigned NOT NULL,
  `pop` smallint unsigned NOT NULL,
  `cp` smallint unsigned NOT NULL,
  `evasion` tinyint NOT NULL,
  `celebration` int NOT NULL DEFAULT '0',
  `type` int NOT NULL DEFAULT '0',
  `wood` float(12,2) NOT NULL,
  `clay` float(12,2) NOT NULL,
  `iron` float(12,2) NOT NULL,
  `woodp` float(12,2) NOT NULL,
  `clayp` float(12,2) NOT NULL,
  `ironp` float(12,2) NOT NULL,
  `maxstore` int unsigned NOT NULL,
  `extra_maxstore` int unsigned NOT NULL,
  `crop` float(12,2) NOT NULL,
  `cropp` float(12,2) NOT NULL,
  `maxcrop` int unsigned NOT NULL,
  `extra_maxcrop` int unsigned NOT NULL,
  `upkeep` float unsigned NOT NULL,
  `lastupdate` int unsigned NOT NULL,
  `loyalty` tinyint NOT NULL DEFAULT '100',
  `exp1` smallint NOT NULL,
  `exp2` smallint NOT NULL,
  `exp3` smallint NOT NULL,
  `created` int NOT NULL,
  `natar` tinyint unsigned NOT NULL,
  `starv` int unsigned NOT NULL,
  `expandedfrom` smallint unsigned NOT NULL,
  PRIMARY KEY (`wref`),
  KEY `pop` (`pop`),
  KEY `capital` (`capital`),
  KEY `owner` (`owner`),
  KEY `woodp` (`woodp`),
  KEY `clayp` (`clayp`),
  KEY `ironp` (`ironp`),
  KEY `cropp` (`cropp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.vdata: ~0 rows (approximately)

-- Dumping structure for table phpvian.wdata
CREATE TABLE IF NOT EXISTS `wdata` (
  `id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `fieldtype` tinyint unsigned NOT NULL,
  `oasistype` tinyint unsigned NOT NULL,
  `x` smallint NOT NULL,
  `y` smallint NOT NULL,
  `occupied` tinyint NOT NULL,
  `image` char(12) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `x` (`x`),
  KEY `y` (`y`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.wdata: ~0 rows (approximately)

-- Dumping structure for table phpvian.x_world
CREATE TABLE IF NOT EXISTS `x_world` (
  `id` int NOT NULL DEFAULT '0',
  `x` smallint DEFAULT NULL,
  `y` smallint DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `y` (`y`),
  KEY `x` (`x`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table phpvian.x_world: ~0 rows (approximately)

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
