ALTER TABLE `#__crowdf_countries` CHANGE `code4` `locale` VARCHAR(5) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
ALTER TABLE `#__crowdf_payment_sessions` ADD `order_id` VARCHAR(32) NOT NULL DEFAULT '' AFTER `unique_key`;
ALTER TABLE `#__crowdf_payment_sessions` CHANGE `unique_key` `unique_key` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT 'A unique key from a gateway.';

ALTER TABLE `#__crowdf_transactions` ADD UNIQUE `uidx_cftransactions_txnid` ( `txn_id` ( 64 ) );