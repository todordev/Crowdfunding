RENAME TABLE `#__crowdf_payment_sessions` TO `#__crowdf_paymentsessions`;

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