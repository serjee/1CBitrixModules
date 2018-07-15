CREATE TABLE IF NOT EXISTS `b_mibix_disbattle_group`
(
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name_group` VARCHAR(20) NOT NULL,
  `code_group` VARCHAR(20) NOT NULL,
  `active` CHAR(1) NOT NULL DEFAULT 'Y',
  `date_insert`	DATETIME NOT NULL,
  `date_update` DATETIME NULL,
  PRIMARY KEY (`id`)
);
CREATE TABLE IF NOT EXISTS `b_mibix_disbattle_battle`
(
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `group_id` INT(11) NULL REFERENCES `b_mibix_disbattle_group` (id),
  `site_id` VARCHAR(100) NOT NULL DEFAULT '',
  `iblock_id` INT(11) NOT NULL DEFAULT '0',
  `date_start` DATETIME NOT NULL,
  `date_finish`	DATETIME NOT NULL,
  `name_battle` VARCHAR(255) NOT NULL,
  `battle_items` TEXT NOT NULL DEFAULT '',
  `battle_title` VARCHAR(255) NOT NULL DEFAULT '',
  `battle_text` VARCHAR(255) NOT NULL DEFAULT '',
  `battle_pictures` TEXT NOT NULL DEFAULT '',
  `battle_links` VARCHAR(255) NOT NULL DEFAULT '',
  `battle_site` VARCHAR(50) NOT NULL DEFAULT '',
  `time_format` VARCHAR(100) NOT NULL DEFAULT '',
  `is_cron_count` ENUM('Y','N') NOT NULL DEFAULT 'N',
  `price` VARCHAR(100) NOT NULL DEFAULT '',
  `is_indicator` ENUM('Y','N') NOT NULL DEFAULT 'Y',
  `discount_all` INT(11) NOT NULL DEFAULT '0',
  `discount_max` INT(11) NOT NULL DEFAULT '0',
  `is_protection` ENUM('Y','N') NOT NULL DEFAULT 'Y',
  `enabled_vk` ENUM('Y','N') NOT NULL DEFAULT 'Y',
  `enabled_fb` ENUM('Y','N') NOT NULL DEFAULT 'Y',
  `enabled_tw` ENUM('Y','N') NOT NULL DEFAULT 'Y',
  `enabled_ok` ENUM('Y','N') NOT NULL DEFAULT 'Y',
  `enabled_ml` ENUM('Y','N') NOT NULL DEFAULT 'Y',
  `enabled_pi` ENUM('Y','N') NOT NULL DEFAULT 'Y',
  `active` CHAR(1) NOT NULL DEFAULT 'Y',
  `date_insert`	DATETIME NOT NULL,
	`date_update` DATETIME NULL,
  PRIMARY KEY (`id`)
);
CREATE TABLE IF NOT EXISTS `b_mibix_disbattle_discount`
(
  `battle_id` int(11) UNSIGNED NOT NULL,
  `product_id` INT(11) NOT NULL,
  `discount_id` INT(11) NOT NULL,
  `discount_val` DECIMAL(12,2) NOT NULL DEFAULT '0.00'
);
CREATE TABLE IF NOT EXISTS `b_mibix_disbattle_votes`
(
  `battle_id` INT(11) UNSIGNED NOT NULL,
  `element_id` INT(11) NOT NULL,
  `votes` INT(11) NOT NULL DEFAULT '0'
);
CREATE TABLE IF NOT EXISTS `b_mibix_disbattle_access`
(
  `battle_id` INT(11) UNSIGNED NOT NULL,
  `user_ip` VARCHAR(20) DEFAULT '0',
  `is_vote` ENUM('VK','FB','TW','OK','MM','PI') NOT NULL,
  `date_vote` DATETIME NOT NULL
);