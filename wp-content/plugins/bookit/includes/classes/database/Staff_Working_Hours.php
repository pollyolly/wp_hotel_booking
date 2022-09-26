<?php

namespace Bookit\Classes\Database;

use Bookit\Classes\Vendor\DatabaseModel;

class Staff_Working_Hours extends DatabaseModel {

	/**
	 * Create Table
	 */
	public static function create_table() {
		global $wpdb;
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		$table_name     = self::_table();
		$primary_key    = self::$primary_key;

		$sql = "CREATE TABLE {$table_name} (
			id INT UNSIGNED NOT NULL AUTO_INCREMENT,
			staff_id INT UNSIGNED NOT NULL,
			weekday INT,
			start_time TIME,
			end_time TIME,
			break_from TIME,
			break_to TIME,
			PRIMARY KEY ({$primary_key}),
            INDEX `idx_staff_id` (`staff_id`)
		) {$wpdb->get_charset_collate()};";

		maybe_create_table($table_name, $sql);
	}
}