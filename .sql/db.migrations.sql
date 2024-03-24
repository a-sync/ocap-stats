--
-- 2021-12-10
--
ALTER TABLE `entities` CHANGE `is_player` `is_player` TINYINT(1) UNSIGNED NOT NULL, ADD `ignore` TINYINT(1) UNSIGNED NULL DEFAULT NULL, ADD `cmd` TINYINT(1) UNSIGNED NULL DEFAULT NULL ;

ALTER TABLE `events` CHANGE `victim_id` `victim_id` INT(10) UNSIGNED NULL DEFAULT NULL, CHANGE `weapon` `weapon` VARCHAR(255) NULL DEFAULT NULL, ADD `data` VARCHAR(255) NULL DEFAULT NULL ;

UPDATE `events` SET `weapon`=NULL WHERE `event` NOT IN ('hit', 'killed') ;

ALTER TABLE `operations` ADD `verified` TINYINT(1) UNSIGNED NULL DEFAULT NULL ;

ALTER TABLE `players` ADD `uid` VARCHAR(64) NULL DEFAULT NULL, ADD `unit_id` VARCHAR(64) NULL DEFAULT NULL, ADD UNIQUE (`uid`) ;

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

--
-- 2023-03-03
--
ALTER TABLE `events` DROP INDEX `operation_id`, ADD INDEX `operation_id` (`operation_id`) ;

ALTER TABLE `timestamps` DROP PRIMARY KEY, ADD `aid` INT UNSIGNED NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`aid`), ADD UNIQUE `operation_id_id` (`operation_id`, `id`) ;

ALTER TABLE `entities` DROP PRIMARY KEY, ADD `aid` INT UNSIGNED NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`aid`), ADD UNIQUE `operation_id_id` (`operation_id`, `id`) ;

ALTER TABLE `events` ADD `victim_aid` INT(10) UNSIGNED NULL DEFAULT NULL, ADD `attacker_aid` INT(10) UNSIGNED NULL DEFAULT NULL ;

UPDATE `events` JOIN `entities` AS `victim` ON `victim`.`id` = `events`.`victim_id` AND `victim`.`operation_id` = `events`.`operation_id` SET `events`.`victim_aid` = `victim`.`aid` WHERE `events`.`victim_aid` IS NULL AND `events`.`victim_id` IS NOT NULL ;

UPDATE `events` JOIN `entities` AS `attacker` ON `attacker`.`id` = `events`.`attacker_id` AND `attacker`.`operation_id` = `events`.`operation_id` SET `events`.`attacker_aid` = `attacker`.`aid` WHERE `events`.`attacker_aid` IS NULL AND `events`.`attacker_id` IS NOT NULL ;

--
-- 2024-02-16
--
ALTER TABLE `entities` DROP `invalid`;

--
-- 2024-02-27
--
UPDATE `entities` SET `hits` = (`hits` - `fhits`), `kills` = (`kills` - `fkills`) ;

--
-- 2024-03-24
--
CREATE INDEX `events_victim_aid_IDX` ON `events` (`victim_aid`);
UPDATE `entities` INNER JOIN (SELECT `events`.`victim_aid`, COUNT(`events`.`id`) AS `total`	FROM `events` WHERE `events`.`event` = 'killed' GROUP BY `events`.`victim_aid`)	AS `kills` ON	`kills`.`victim_aid` = `entities`.`aid` SET	`entities`.`deaths` = `kills`.`total` ;
