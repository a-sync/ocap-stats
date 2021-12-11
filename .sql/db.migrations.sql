--
-- 2021-12-10
--
ALTER TABLE `entities` CHANGE `is_player` `is_player` TINYINT(1) UNSIGNED NOT NULL;

ALTER TABLE `events` CHANGE `victim_id` `victim_id` INT(10) UNSIGNED NULL DEFAULT NULL, CHANGE `weapon` `weapon` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL, ADD `data` VARCHAR(255) NULL DEFAULT NULL ;

ALTER TABLE `players` ADD `uid` VARCHAR(64) NULL DEFAULT NULL , ADD `unit_id` VARCHAR(64) NULL DEFAULT NULL , ADD UNIQUE (`uid`) ;

UPDATE `events` SET `weapon`=NULL WHERE `event` NOT IN ('hit', 'killed') ;

CREATE TABLE IF NOT EXISTS `entities_additional_data` (
  `operation_id` int(10) unsigned NOT NULL,
  `entity_id` int(10) unsigned NOT NULL,
  `player_id` int(10) unsigned NOT NULL,
  `ignore` tinyint(1) unsigned NOT NULL,
  `hq` tinyint(1) unsigned NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`operation_id`,`entity_id`),
  KEY `player_id` (`player_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
--
-- RELATIONS FOR TABLE `entities`:
--   `operation_id`
--       `operations` -> `id`
--   `entity_id`
--       `entities` -> `id`
--   `player_id`
--       `players` -> `id`
--

CREATE TABLE IF NOT EXISTS `ops_additional_data` (
  `operation_id` int(10) unsigned NOT NULL,
  `mission_author` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_winner` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `end_message` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `verified` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`operation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
--
-- RELATIONS FOR TABLE `events`:
--   `operation_id`
--       `operations` -> `id`
--
