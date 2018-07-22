CREATE TABLE IF NOT EXISTS `b_mibix_export_template`
(
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `encoding` VARCHAR(100) NOT NULL DEFAULT 'UTF-8',
  `template` TEXT NOT NULL DEFAULT '',
  `step_limit` VARCHAR(20) NOT NULL DEFAULT '',
  `step_path` VARCHAR(255) NOT NULL DEFAULT '',
  `step_interval` VARCHAR(20) NOT NULL DEFAULT '',
  `active` CHAR(1) NOT NULL DEFAULT 'Y',
  `date_insert`	DATETIME NOT NULL,
	`date_update` DATETIME NULL,
  PRIMARY KEY (`id`)
);
CREATE TABLE IF NOT EXISTS `b_mibix_export_entity`
(
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `template_id` INT(11) NULL REFERENCES `b_mibix_export_template` (id),
  `entity_id` INT(11) NOT NULL DEFAULT '0',
  `name_entity` VARCHAR(100) NOT NULL,
  `code_entity` VARCHAR(100) NOT NULL,
  `value` VARCHAR(255) NOT NULL,
  `site_id` VARCHAR(100) NOT NULL DEFAULT '',
  `iblock_type` VARCHAR(100) NOT NULL,
  `iblock_id` INT(11) NOT NULL,
  `include_sections` TEXT NOT NULL DEFAULT '',
  `include_items` TEXT NOT NULL DEFAULT '',
  `exclude_items` TEXT NOT NULL DEFAULT '',
  `include_subsection` CHAR(1) NOT NULL DEFAULT 'Y',
  `filters` TEXT NOT NULL DEFAULT '',
  `active` CHAR(1) NOT NULL DEFAULT 'Y',
  `date_insert`	DATETIME	NOT NULL,
	`date_update` DATETIME	NULL,
  PRIMARY KEY (`id`)
);
CREATE TABLE IF NOT EXISTS `b_mibix_export_steps_load`
(
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `in_proccess` CHAR(1) NOT NULL DEFAULT 'N',
  `in_blocked` CHAR(1) NOT NULL DEFAULT 'N',
  `last_run_time` DATETIME NULL,
  `last_step_time` DATETIME NULL,
  `template_id` INT(11) NOT NULL,
  `iblock_id` INT(11) NOT NULL,
  `element_id` INT(11) NOT NULL,
  `sku_iblock_id` INT(11) NOT NULL,
  `sku_element_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`)
);