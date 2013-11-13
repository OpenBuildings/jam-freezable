DROP TABLE IF EXISTS `test_purchases`;
CREATE TABLE `test_purchases` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `currency` VARCHAR(3) NOT NULL,
  `monetary` TEXT,
  `is_frozen` INT(1) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY  (`id`)
) ENGINE=INNODB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `test_store_purchases`;
CREATE TABLE `test_store_purchases` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `test_purchase_id` INT(10) UNSIGNED NULL,
  PRIMARY KEY  (`id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `test_purchase_items`;
CREATE TABLE `test_purchase_items` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `test_store_purchase_id` INT(10) UNSIGNED NULL,
  `price` DECIMAL(10,2) NULL,
  PRIMARY KEY  (`id`)
) ENGINE=INNODB  DEFAULT CHARSET=utf8;

# Dump of table purchases
# ------------------------------------------------------------

INSERT INTO `test_purchases` (`id`, `currency`, `monetary`, `is_frozen`)
VALUES
  (1,'EUR','O:8:"stdClass":0:{}',1),
  (2,'GBP','',0);

# Dump of table store_purchases
# ------------------------------------------------------------

INSERT INTO `test_store_purchases` (`id`, `test_purchase_id`)
VALUES
  (1,1),
  (2,2);

# Dump of table purchase_items
# ------------------------------------------------------------

INSERT INTO `test_purchase_items` (`id`, `test_store_purchase_id`, `price`)
VALUES
  (1,1,200.00),
  (2,1,200.00),
  (3,2,NULL);
