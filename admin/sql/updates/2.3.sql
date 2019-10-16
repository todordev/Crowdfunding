ALTER TABLE `#__crowdf_rewards` ADD `ordering` TINYINT UNSIGNED NOT NULL DEFAULT '0' AFTER `published`;
ALTER TABLE `#__crowdf_currencies` DROP INDEX `idx_crowdf_ccode`;
ALTER TABLE `#__crowdf_intentions` DROP INDEX `idx_cfints_usr_proj`;

ALTER TABLE `#__crowdf_countries` ENGINE = MYISAM;
ALTER TABLE `#__crowdf_currencies` ENGINE = MYISAM;
ALTER TABLE `#__crowdf_locations` ENGINE = MYISAM;
ALTER TABLE `#__crowdf_reports` ENGINE = MYISAM;
ALTER TABLE `#__crowdf_types` ENGINE = MYISAM;
ALTER TABLE `#__crowdf_logs` ENGINE = MYISAM;
ALTER TABLE `#__crowdf_updates` ENGINE = MYISAM;

DROP TABLE IF EXISTS `#__crowdf_images`;
DROP TABLE IF EXISTS `#__crowdf_emails`;

ALTER TABLE `#__crowdf_transactions` ADD `service_alias` VARCHAR(32) NOT NULL DEFAULT '' AFTER `service_provider`;
ALTER TABLE `#__crowdf_transactions` ADD `service_data` TINYBLOB NULL DEFAULT NULL COMMENT 'Encrypted sensitive data' AFTER `service_alias`;

UPDATE `#__crowdf_transactions` SET `service_alias` = REPLACE(LOWER(`service_provider`), ' ', '');