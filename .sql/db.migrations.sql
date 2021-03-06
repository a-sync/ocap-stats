--
-- 2021-12-10
--
ALTER TABLE `entities` CHANGE `is_player` `is_player` TINYINT(1) UNSIGNED NOT NULL, ADD `ignore` TINYINT(1) UNSIGNED NULL DEFAULT NULL , ADD `cmd` TINYINT(1) UNSIGNED NULL DEFAULT NULL ;

ALTER TABLE `events` CHANGE `victim_id` `victim_id` INT(10) UNSIGNED NULL DEFAULT NULL, CHANGE `weapon` `weapon` VARCHAR(255) NULL DEFAULT NULL, ADD `data` VARCHAR(255) NULL DEFAULT NULL ;

UPDATE `events` SET `weapon`=NULL WHERE `event` NOT IN ('hit', 'killed') ;

ALTER TABLE `operations` ADD `verified` TINYINT(1) UNSIGNED NULL DEFAULT NULL ;

ALTER TABLE `players` ADD `uid` VARCHAR(64) NULL DEFAULT NULL , ADD `unit_id` VARCHAR(64) NULL DEFAULT NULL , ADD UNIQUE (`uid`) ;

--
-- 2022-01-01
--
ALTER TABLE `entities` CHANGE `ignore` `invalid` TINYINT(1) UNSIGNED NULL DEFAULT NULL, ADD `distance_traveled` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `deaths`, ADD `uid` VARCHAR(64) NULL DEFAULT NULL ;

CREATE TABLE IF NOT EXISTS `timestamps` (
  `operation_id` int(10) unsigned NOT NULL,
  `id` int(10) unsigned NOT NULL,
  `date` DATETIME NOT NULL,
  `frame_num` int(10) unsigned NOT NULL,
  `sys_time_utc` DATETIME NOT NULL,
  `time` decimal(10,3) unsigned NOT NULL,
  `time_multiplier` decimal(4,2) unsigned NOT NULL,
  PRIMARY KEY (`operation_id`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
