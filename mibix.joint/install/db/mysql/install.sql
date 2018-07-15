CREATE TABLE IF NOT EXISTS `b_mibix_joint_user`
(
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `active` CHAR(1) NOT NULL DEFAULT 'Y',
  `user_id` INT(11) NOT NULL,
  `balance` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
  `rate` INT(11) NOT NULL DEFAULT '0',
  `level` INT(11) NOT NULL DEFAULT '0',
  `is_org` CHAR(1) NOT NULL DEFAULT 'N',
  `last_ip` VARCHAR(15) NOT NULL DEFAULT '0' COMMENT 'IP address',
  `date_insert`	DATETIME NOT NULL,
	`date_update` DATETIME NULL,
  PRIMARY KEY (`id`)
);
CREATE TABLE IF NOT EXISTS `b_mibix_joint_user_blacklist`
(
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `joint_id` INT(11) NOT NULL, /* ID совместной закупки */
  `org_user_id` INT(11) NOT NULL,
  `description` TEXT,
  `date_insert`	DATETIME NOT NULL,
  `date_update` DATETIME NULL,
  PRIMARY KEY (`id`)
);
CREATE TABLE IF NOT EXISTS `b_mibix_joint_joint`
(
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `active` CHAR(1) NOT NULL DEFAULT 'Y',
  `org_user_id` INT(11) NOT NULL,
  `name_joint` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `joint_catalog_id` INT(11) NOT NULL, /* ID каталога с товарами */
  `min_sum` INT(11) NOT NULL DEFAULT '0', /* минимальная сумма закупки */
  `min_count` INT(11) NOT NULL DEFAULT '0', /* минимальное количество */
  `prepayment` CHAR(1) NOT NULL DEFAULT 'Y', /* требуется предоплата */
  `is_range_cat` CHAR(1) NOT NULL DEFAULT 'N', /* ассортимент из каталога? */
  `delivery_price` INT(11) NOT NULL DEFAULT '0', /* стоимость доставки */
  `garant_articul` CHAR(1) NOT NULL DEFAULT 'N', /* гарантии по артикулу */
  `garant_params` CHAR(1) NOT NULL DEFAULT 'N', /* гарантии по доп. параметрам */
  `garant_description` TEXT, /* комментарии к описанию гарантий */
  `defect_replace` CHAR(1) NOT NULL DEFAULT 'N', /* замена брака */
  `defect_description` TEXT, /* комментарии о замене брака */
  `place_issue` ENUM('ALL_OFFICES') NOT NULL DEFAULT 'ALL_OFFICES', /* точки вывоза, дать менять поле ??? */
  `delivery_days` INT(11) NOT NULL DEFAULT '0', /* срок доставки в днях */
  `date_insert`	DATETIME	NOT NULL,
  `date_update` DATETIME	NULL,
  PRIMARY KEY (`id`)
);
CREATE TABLE IF NOT EXISTS `b_mibix_joint_catalog`
(
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `active` CHAR(1) NOT NULL DEFAULT 'Y',
  `min_items` INT(11) NOT NULL DEFAULT '0', /* мин количество товара которое нужно выкупить */
  `max_items` INT(11) NOT NULL DEFAULT '0', /* макс количество товара доступное для выкупа */
  `opt_price` DECIMAL(10,2) NOT NULL DEFAULT '0.00', /* оптовая цена товара */
  `delivery_item_price` INT(11) NOT NULL DEFAULT '0', /* стоимость доставки товара */
  `date_insert`	DATETIME	NOT NULL,
  `date_update` DATETIME	NULL,
  PRIMARY KEY (`id`)
);
CREATE TABLE IF NOT EXISTS `b_mibix_joint_catalog_item`
(
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `active` CHAR(1) NOT NULL DEFAULT 'Y',
  `product_id` INT(11) NOT NULL, /* ID товара в реальном каталоге */
  `site_id` VARCHAR(100) NOT NULL DEFAULT '',
  `iblock_type` VARCHAR(100) NOT NULL,
  `iblock_id` INT(11) NOT NULL,
  /* потребуются поля для вывода названия, описания и других свойств */
  `date_insert`	DATETIME	NOT NULL,
  `date_update` DATETIME	NULL,
  PRIMARY KEY (`id`)
);
CREATE TABLE IF NOT EXISTS `b_mibix_joint_basket`
(
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `joint_id` INT(11) NOT NULL, /* ID совместной закупки */
  `item_id` INT(11) NOT NULL, /* ID товара в b_mibix_joint_catalog_item  */
  `price` INT(11) NOT NULL DEFAULT '0',
  `price_currency` VARCHAR(20) NOT NULL DEFAULT 'RUB',
  `quantity` INT(11) NOT NULL DEFAULT '0',
  `status` ENUM('') NOT NULL DEFAULT '', /* статусы заказа пользователя */
  `is_canceled` CHAR(1) NOT NULL DEFAULT 'N', /* отменен */
  `is_paied` CHAR(1) NOT NULL DEFAULT 'N', /* оплачен */
  `description` TEXT, /* комментарий пользователя к заказу о заменах */
  `date_insert`	DATETIME	NOT NULL,
  `date_update` DATETIME	NULL,
  PRIMARY KEY (`id`)
);