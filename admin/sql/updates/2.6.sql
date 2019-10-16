ALTER TABLE `#__crowdf_locations` DROP `state_code`;

ALTER TABLE `#__crowdf_locations` DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci;
ALTER TABLE `#__crowdf_locations` CHANGE `name` `name` VARCHAR(191) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE `#__crowdf_locations` CHANGE `latitude` `latitude` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE `#__crowdf_locations` CHANGE `longitude` `longitude` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE `#__crowdf_locations` CHANGE `country_code` `country_code` CHAR(2) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE `#__crowdf_locations` CHANGE `timezone` `timezone` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;

ALTER TABLE `#__crowdf_locations`
  ADD `admin1_code` VARCHAR(20) NOT NULL DEFAULT '' AFTER `timezone`,
  ADD `admin1code_id` VARCHAR(128) NOT NULL DEFAULT '' AFTER `admin1_code`;

ALTER TABLE `#__crowdf_locations` ADD INDEX `idx_cflocations_name` (`name`);
ALTER TABLE `#__crowdf_locations` ADD INDEX `idx_cflocations_cca1cid` (`country_code`, `admin1code_id`) USING BTREE;

ALTER TABLE `#__crowdf_countries` DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci;
ALTER TABLE `#__crowdf_countries` CHANGE `name` `name` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE `#__crowdf_countries` CHANGE `code` `code` CHAR(2) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE `#__crowdf_countries` CHANGE `locale` `locale` VARCHAR(5) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '';
ALTER TABLE `#__crowdf_countries` CHANGE `currency` `currency` CHAR(3) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL;
ALTER TABLE `#__crowdf_countries` CHANGE `timezone` `timezone` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL;

CREATE TABLE IF NOT EXISTS `#__crowdf_regions` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8_unicode_ci NOT NULL,
  `country_code` char(2) COLLATE utf8_unicode_ci NOT NULL,
  `admincode_id` varchar(128) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_cfregions_acid` (`admincode_id`) USING BTREE,
  KEY `idx_cfregions_cc` (`country_code`),
  KEY `idx_cfregions_name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__crowdf_users` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `passport_id` tinyblob,
  `passport_type` enum('id','dl','ip') NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `#__crowdf_projects`
  ADD `asset_id` INT NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.' AFTER `user_id`,
  ADD `access` INT UNSIGNED NOT NULL DEFAULT '1' AFTER `asset_id`;

ALTER TABLE `#__crowdf_transactions` ADD `params` VARCHAR( 255 ) NOT NULL DEFAULT '{}';