ALTER TABLE `#__crowdf_currencies` CHANGE `abbr` `code` CHAR(3) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `#__crowdf_payment_sessions` DROP INDEX `intention_id`;
ALTER TABLE `#__crowdf_intentions` DROP `unique_key`, DROP `gateway`, DROP `gateway_data`, DROP `auser_id`, DROP `session_id`;
ALTER TABLE `#__crowdf_updates` ADD `state` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `user_id`;