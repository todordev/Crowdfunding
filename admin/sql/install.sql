CREATE TABLE IF NOT EXISTS `#__crowdf_comments` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `comment` varchar(1024) NOT NULL,
  `record_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `published` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `project_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__crowdf_countries` (
  `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `code` char(2) COLLATE utf8_unicode_ci NOT NULL,
  `locale` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `latitude` float DEFAULT NULL,
  `longitude` float DEFAULT NULL,
  `currency` char(3) COLLATE utf8_unicode_ci DEFAULT NULL,
  `timezone` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__crowdf_currencies` (
  `id` smallint(6) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `code` char(3) NOT NULL,
  `symbol` char(3) NOT NULL DEFAULT '',
  `position` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__crowdf_followers` (
  `user_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__crowdf_intentions` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL,
  `project_id` int(10) UNSIGNED NOT NULL,
  `reward_id` int(10) UNSIGNED NOT NULL,
  `record_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__crowdf_locations` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8_unicode_ci NOT NULL,
  `latitude` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `longitude` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `country_code` char(2) COLLATE utf8_unicode_ci NOT NULL,
  `timezone` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `admin1_code` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `admin1code_id` varchar(128) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `published` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_cflocations_name` (`name`),
  KEY `idx_cflocations_cca1cid` (`country_code`,`admin1code_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__crowdf_logs` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `data` text,
  `type` varchar(64) NOT NULL,
  `record_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__crowdf_paymentsessiongateways` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Payment session primary key',
  `alias` varchar(32) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Payment service name (alias)',
  `data` varchar(2048) COLLATE utf8_unicode_ci NOT NULL DEFAULT '{}' COMMENT 'Contains a specific data for the gateways',
  `token` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'It is a unique key (token) from the gateway',
  `order_id` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`,`alias`),
  UNIQUE KEY `idx_crowdf_pstoken` (`token`),
  KEY `idx_crowdf_pspk` (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__crowdf_paymentsessions` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL,
  `project_id` int(10) UNSIGNED NOT NULL,
  `reward_id` int(10) UNSIGNED NOT NULL,
  `record_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `unique_key` varchar(64) NOT NULL DEFAULT '' COMMENT 'A unique key from a gateway.',
  `order_id` varchar(32) NOT NULL DEFAULT '',
  `gateway` varchar(32) NOT NULL DEFAULT '' COMMENT 'It is the name of the Payment Service.',
  `gateway_data` varchar(2048) DEFAULT NULL COMMENT 'Contains a specific data for some gateways.',
  `auser_id` varchar(32) NOT NULL DEFAULT '' COMMENT 'It is a hash ID of an anonymous user.',
  `session_id` varchar(32) NOT NULL DEFAULT '' COMMENT 'Session ID of the payment process.',
  `intention_id` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__crowdf_projects` (
  `id` smallint(6) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(128) NOT NULL,
  `alias` varchar(48) NOT NULL DEFAULT '',
  `short_desc` varchar(255) NOT NULL DEFAULT '',
  `description` text,
  `image` varchar(64) NOT NULL DEFAULT '',
  `image_square` varchar(64) NOT NULL DEFAULT '',
  `image_small` varchar(64) NOT NULL DEFAULT '',
  `location_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `goal` decimal(10,3) UNSIGNED NOT NULL DEFAULT '0.000',
  `funded` decimal(10,3) UNSIGNED NOT NULL DEFAULT '0.000',
  `funding_type` enum('FIXED','FLEXIBLE') NOT NULL DEFAULT 'FIXED',
  `funding_start` date NOT NULL DEFAULT '1000-01-01',
  `funding_end` date NOT NULL DEFAULT '1000-01-01',
  `funding_days` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `pitch_video` varchar(255) NOT NULL DEFAULT '',
  `pitch_image` varchar(255) NOT NULL DEFAULT '',
  `hits` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `featured` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `approved` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `ordering` tinyint(4) UNSIGNED NOT NULL DEFAULT '0',
  `params` varchar(2048) NOT NULL DEFAULT '{}',
  `catid` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `type_id` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `user_id` int(10) UNSIGNED NOT NULL,
  `asset_id` int(11) NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access` int(10) UNSIGNED NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `catid` (`catid`),
  KEY `user_id` (`user_id`),
  KEY `alias` (`alias`),
  KEY `location_id` (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__crowdf_regions` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8_unicode_ci NOT NULL,
  `country_code` char(2) COLLATE utf8_unicode_ci 	 NOT NULL,
  `admincode_id` varchar(128) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_cfregions_acid` (`admincode_id`),
  KEY `idx_cfregions_cc` (`country_code`),
  KEY `idx_cfregions_name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__crowdf_reports` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `subject` varchar(128) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `email` varchar(128) DEFAULT NULL,
  `record_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `project_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__crowdf_rewards` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(128) NOT NULL,
  `description` varchar(500) NOT NULL,
  `amount` decimal(10,3) UNSIGNED NOT NULL DEFAULT '0.000',
  `number` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `distributed` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `delivery` date NOT NULL DEFAULT '0000-00-00' COMMENT 'Estimated delivery',
  `shipping` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `image` varchar(32) DEFAULT NULL,
  `image_thumb` varchar(32) DEFAULT NULL,
  `image_square` varchar(32) DEFAULT NULL,
  `published` tinyint(3) NOT NULL DEFAULT '1',
  `ordering` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `project_id` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__crowdf_transactions` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `txn_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `txn_amount` decimal(10,3) UNSIGNED NOT NULL DEFAULT '0.000',
  `txn_currency` varchar(64) NOT NULL DEFAULT '',
  `txn_status` enum('pending','completed','canceled','refunded','failed') NOT NULL DEFAULT 'pending',
  `txn_id` varchar(64) NOT NULL DEFAULT '',
  `parent_txn_id` varchar(64) NOT NULL DEFAULT '' COMMENT 'Transaction id of an pre authorized transaction.',
  `extra_data` varchar(2048) DEFAULT NULL COMMENT 'Additional information about transaction.',
  `status_reason` varchar(32) NOT NULL DEFAULT '' COMMENT 'This is a reason of the status in few words.',
  `project_id` int(10) UNSIGNED NOT NULL,
  `reward_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `investor_id` int(10) UNSIGNED NOT NULL COMMENT 'The backer of the project.',
  `receiver_id` int(10) UNSIGNED NOT NULL COMMENT 'The owner of the project.',
  `service_provider` varchar(32) NOT NULL,
  `service_alias` varchar(32) NOT NULL DEFAULT '',
  `service_data` tinyblob COMMENT 'Encrypted sensitive data',
  `reward_state` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `fee` decimal(10,2) UNSIGNED NOT NULL DEFAULT '0.00',
  `params` varchar(255) NOT NULL DEFAULT '{}',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uidx_cftransactions_txnid` (`txn_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__crowdf_types` (
  `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(128) NOT NULL,
  `description` varchar(512) DEFAULT NULL,
  `params` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__crowdf_updates` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(128) NOT NULL,
  `description` varchar(2048) NOT NULL,
  `record_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `project_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `state` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__crowdf_users` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `passport_id` tinyblob,
  `passport_type` enum('id','dl','ip') NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;