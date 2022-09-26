<?php

namespace Bookit\Classes\Database;

use Bookit\Classes\Vendor\DatabaseModel;

class Coupons extends DatabaseModel {

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
			`code` VARCHAR(255) NOT NULL,
			`usage_limit` INT UNSIGNED DEFAULT NULL,
			`once_per_user` INT UNSIGNED DEFAULT NULL,
			`services` longtext DEFAULT NULL,
			`staff` longtext DEFAULT NULL,
			`created_at` DATETIME NOT NULL,
    		`updated_at` DATETIME NOT NULL,
			PRIMARY KEY ({$primary_key})
		) {$wpdb->get_charset_collate()};";

		maybe_create_table($table_name, $sql);
	}

}