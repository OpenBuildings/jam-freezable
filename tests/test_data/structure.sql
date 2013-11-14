DROP TABLE IF EXISTS `test_purchases`;
CREATE TABLE `test_purchases` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
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
  `is_not_freezable` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY  (`id`)
) ENGINE=INNODB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `test_payments`;
CREATE TABLE `test_payments` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `test_purchase_id` INT(10) UNSIGNED NULL,
  PRIMARY KEY  (`id`)
) ENGINE=INNODB  DEFAULT CHARSET=utf8;

# Dump of table purchases
# ------------------------------------------------------------

INSERT INTO `test_purchases` (`id`, `monetary`, `is_frozen`)
VALUES
  (1,'O:8:"stdClass":0:{}',1),
  (2,'',0),
  (3,'',0);

# Dump of table store_purchases
# ------------------------------------------------------------

INSERT INTO `test_store_purchases` (`id`, `test_purchase_id`)
VALUES
  (1,1),
  (2,2),
  (3,3);

# Dump of table purchase_items
# ------------------------------------------------------------

INSERT INTO `test_purchase_items` (`id`, `test_store_purchase_id`, `price`, `is_not_freezable`)
VALUES
  (1,1,200.00,0),
  (2,1,200.00,0),
  (3,2,NULL,0),
  (4,3,NULL,1),
  (5,3,NULL,0);

# Dump of table payments
# ------------------------------------------------------------

INSERT INTO `test_payments` (`id`, `test_purchase_id`)
VALUES
  (1,1);
