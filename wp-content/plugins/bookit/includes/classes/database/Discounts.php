<?php

namespace Bookit\Classes\Database;

use Bookit\Classes\Vendor\DatabaseModel;

class Discounts extends DatabaseModel {

	/**
	 * Create Table
	 */
	public static function create_table() {
		global $wpdb;
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		$table_name     = self::_table();
		$primary_key    = self::$primary_key;

		$sql = "CREATE TABLE IF NOT EXISTS  {$table_name} (
			`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
			`discount` INT UNSIGNED NOT NULL,
			`title` VARCHAR(255) NOT NULL,
    		`is_active` TINYINT(1) NOT NULL DEFAULT 1,
    		`active_from` DATETIME NOT NULL,
            `active_till` DATETIME NOT NULL,
			`created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
    		PRIMARY KEY ({$primary_key}),
            INDEX `idx_is_active` (`is_active`)
		) {$wpdb->get_charset_collate()};";

		maybe_create_table($table_name, $sql);
	}

}