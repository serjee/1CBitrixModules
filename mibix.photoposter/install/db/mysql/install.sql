CREATE TABLE IF NOT EXISTS `b_mibix_photoposter_settings`
(
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `iblock_id` INT(11) NOT NULL DEFAULT '0',
  `include_sections` TEXT NOT NULL DEFAULT '',
  `exclude_sections` TEXT NOT NULL DEFAULT '',
  `public_text` VARCHAR(100) NOT NULL DEFAULT '',
  `public_pictures` TEXT NOT NULL DEFAULT '',
  `link_post` ENUM('Y','N') NOT NULL DEFAULT 'Y',
  `site_id` VARCHAR(100) NOT NULL DEFAULT '',
  `diff_items` ENUM('Y','N') NOT NULL DEFAULT 'N',
  `event_newitem` ENUM('Y','N') NOT NULL DEFAULT 'N',
  `run_method` ENUM('AGENT','CRON') NOT NULL DEFAULT 'AGENT',
  `run_time` VARCHAR(20) NOT NULL DEFAULT '',
  `run_period` ENUM('PER1','PER2','PER3','PER4') NOT NULL DEFAULT 'PER1',
  `use_sp` ENUM('Y','N') NOT NULL DEFAULT 'N', /*new*/
  `vk_post` ENUM('Y','N') NOT NULL DEFAULT 'N',
  `vk_token` VARCHAR(255) NOT NULL DEFAULT '',
  `vk_wall` VARCHAR(255) NOT NULL DEFAULT '',
  `vk_album_check` ENUM('NEW','EXIST') NOT NULL DEFAULT 'NEW', /*new*/
  `vk_album_exist` VARCHAR(50) NOT NULL DEFAULT '', /*new*/
  `vk_album_new_desc` ENUM('Y','N') NOT NULL DEFAULT 'N', /*new*/
  `vk_album_new_comment` ENUM('Y','N') NOT NULL DEFAULT 'N', /*new*/
  `fb_post` ENUM('Y','N') NOT NULL DEFAULT 'N',
  `fb_token` VARCHAR(255) NOT NULL DEFAULT '',
  `fb_wall` VARCHAR(255) NOT NULL DEFAULT '',
  `fb_album_check` ENUM('NEW','EXIST') NOT NULL DEFAULT 'NEW', /*new*/
  `fb_album_exist` VARCHAR(50) NOT NULL DEFAULT '', /*new*/
  `fb_album_new_desc` ENUM('Y','N') NOT NULL DEFAULT 'N', /*new*/
  `date_update`	DATETIME NOT NULL,
  PRIMARY KEY (`id`)
);
CREATE TABLE IF NOT EXISTS `b_mibix_photoposter_item_posted`
(
  `iblock_id` INT(11) NOT NULL,
  `item_id` INT(11) NOT NULL,
  `soc` ENUM('VK','FB') NOT NULL DEFAULT 'VK',
  `date_post`	DATETIME NOT NULL
);
CREATE TABLE IF NOT EXISTS `b_mibix_photoposter_photo_posted`
(
  `iblock_id` INT(11) NOT NULL,
  `item_id` INT(11) NOT NULL,
  `album_id` VARCHAR(50) NOT NULL DEFAULT '',
  `photo_id` VARCHAR(50) NOT NULL DEFAULT '',
  `soc` ENUM('VK','FB') NOT NULL DEFAULT 'VK',
  `date_post`	DATETIME NOT NULL
);