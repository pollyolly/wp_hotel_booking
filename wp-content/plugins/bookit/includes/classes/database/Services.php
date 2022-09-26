<?php

namespace Bookit\Classes\Database;

use Bookit\Classes\Vendor\DatabaseModel;

class Services extends DatabaseModel {

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
			category_id INT UNSIGNED,
			title VARCHAR(255),
			duration INT NOT NULL DEFAULT 900,
			price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
			icon_id INT UNSIGNED,
			PRIMARY KEY ({$primary_key}),
            INDEX `idx_category_id` (`category_id`)
		) {$wpdb->get_charset_collate()};";

		maybe_create_table($table_name, $sql);
	}

	/**
	 * Get Short Format
	 */
	public static function get_all_short() {
		global $wpdb;
		return $wpdb->get_results( sprintf( 'SELECT %1$s.id, %1$s.title, %1$s.price FROM %1$s ORDER BY %2$s DESC', self::_table(), static::$primary_key ), ARRAY_A );
	}

	/**
	 * Get services assigned to staff
	 */
	public static function get_staff_services( $staffId ) {
		global $wpdb;
		$sql = sprintf(
			'SELECT %1$s.*
					FROM %1$s
					INNER JOIN %2$s ON %1$s.id = %2$s.service_id AND %2$s.staff_id=%%d
					ORDER BY %1$s.%3$s DESC',
			self::_table(),
			Staff_Services::_table(),
			static::$primary_key
		);
		return $wpdb->get_results( $wpdb->prepare( $sql, intval($staffId) ), ARRAY_A );
	}

	/**
	 * Get All Services which was assigned to staff
	 */
	public static function get_assigned_to_staff() {
		global $wpdb;
		$sql = sprintf(
			'SELECT %1$s.*
					FROM %1$s
					INNER JOIN %2$s ON %1$s.id = %2$s.service_id 
					GROUP BY %1$s.id ORDER BY %1$s.%3$s DESC',
			self::_table(),
			Staff_Services::_table(),
			static::$primary_key
		);

		return $wpdb->get_results($sql,ARRAY_A);
	}

	/**
	 * Get All Rows with category
	 */
	public static function get_all_with_category() {
		global $wpdb;
		$sql = sprintf(
			'SELECT %1$s.*, 
					%2$s.name as category
					FROM %1$s
					LEFT JOIN %2$s ON %1$s.category_id = %2$s.id 
					ORDER BY %2$s.name, %1$s.title',
			self::_table(),
			Categories::_table()
		);
		return $wpdb->get_results( $sql );
	}

	/**
	 * Get All Staff assosiated to service
	 */
	public static function get_service_total_staff( $service_id ) {
		global $wpdb;
		$sql = sprintf(
			'SELECT COUNT(%2$s.id)
					FROM %1$s
					LEFT JOIN %2$s ON %1$s.id = %2$s.service_id 
					WHERE  %1$s.id = %%d',
			self::_table(),
			Staff_Services::_table()
		);
		return $wpdb->get_var( $wpdb->prepare( $sql, intval($service_id) ) );
	}

	/**
	 * Get total Services assosiated to category
	 * @return mixed
	 */
	public static function get_total_services_for_category( $category_id ) {
		global $wpdb;
		$sql        = sprintf(
			'SELECT COUNT(*) FROM %s WHERE category_id = %%d',
			self::_table()
		);
		return $wpdb->get_var( $wpdb->prepare( $sql, intval( $category_id ) ) );
	}
	/**
	 * Delete Service
	 * set service appointments status = delete
	 * remove service connection from staff
	 * add notes about service to appointments
	 */
	public static function deleteService($id) {
		global $wpdb;

		$wpdb->query( "START TRANSACTION" );

		$service = self::get('id', $id);

		$serviceAppointments = Appointments::service_appointments($id);

		foreach ( $serviceAppointments as $appointment ) {
			$notes                      = unserialize($appointment->notes);
			$notes['delete_service']   = $service;

			$wpdb->update( Appointments::_table(),
				['notes' => serialize($notes), 'status' => Appointments::$delete],
				['id' => $appointment->id]
			);
		}
		// delete staff connection
		Staff_Services::delete_where( 'service_id', $id);

		// delete service
		self::delete($id);

		$wpdb->query( "COMMIT" );
	}

}