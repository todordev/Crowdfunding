ALTER TABLE `#__crowdf_projects` CHANGE `funding_start` `funding_start` DATE NOT NULL DEFAULT '1000-01-01';
ALTER TABLE `#__crowdf_projects` CHANGE `funding_end` `funding_end` DATE NOT NULL DEFAULT '1000-01-01';
ALTER TABLE `#__crowdf_projects` ADD `params` VARCHAR(2048) NOT NULL DEFAULT '{}' AFTER `ordering`;