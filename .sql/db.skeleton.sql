SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


DROP TABLE IF EXISTS `entities`;
CREATE TABLE IF NOT EXISTS `entities` (
  `operation_id` int(10) unsigned NOT NULL,
  `id` int(10) unsigned NOT NULL,
  `player_id` int(10) unsigned NOT NULL,
  `group_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_player` tinyint(1) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `side` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_frame_num` int(10) unsigned DEFAULT NULL,
  `last_frame_num` int(10) unsigned DEFAULT NULL,
  `type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `class` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shots` int(10) unsigned NOT NULL DEFAULT '0',
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  `fhits` int(10) unsigned NOT NULL DEFAULT '0',
  `kills` int(10) unsigned NOT NULL DEFAULT '0',
  `fkills` int(10) unsigned NOT NULL DEFAULT '0',
  `vkills` int(10) unsigned NOT NULL DEFAULT '0',
  `deaths` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`operation_id`,`id`),
  KEY `player_id` (`player_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
--
-- RELATIONS FOR TABLE `entities`:
--   `operation_id`
--       `operations` -> `id`
--   `player_id`
--       `players` -> `id`
--


DROP TABLE IF EXISTS `events`;
CREATE TABLE IF NOT EXISTS `events` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `operation_id` int(10) unsigned NOT NULL,
  `frame` int(10) unsigned NOT NULL,
  `event` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `victim_id` int(10) unsigned NOT NULL,
  `attacker_id` int(10) unsigned DEFAULT NULL,
  `weapon` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `distance` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `operation_id` (`operation_id`,`attacker_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;
--
-- RELATIONS FOR TABLE `events`:
--   `operation_id`
--       `operations` -> `id`
--   `victim_id`
--       `entities` -> `id`
--   `attacker_id`
--       `entities` -> `id`
--


DROP TABLE IF EXISTS `operations`;
CREATE TABLE IF NOT EXISTS `operations` (
  `id` int(10) unsigned NOT NULL,
  `world_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mission_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mission_duration` int(10) unsigned NOT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` date NOT NULL,
  `tag` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `addon_version` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `capture_delay` decimal(4,2) unsigned NOT NULL,
  `extension_build` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `extension_version` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mission_author` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_time` datetime NOT NULL,
  `end_winner` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `end_message` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `players`;
CREATE TABLE IF NOT EXISTS `players` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alias_of` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `alias_of` (`alias_of`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=11 ;
--
-- RELATIONS FOR TABLE `players`:
--   `alias_of`
--       `players` -> `id`
--


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
