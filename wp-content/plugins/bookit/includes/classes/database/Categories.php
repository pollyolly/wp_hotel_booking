<?php

namespace Bookit\Classes\Database;

use Bookit\Classes\Vendor\DatabaseModel;

class Categories extends DatabaseModel {

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
			name VARCHAR(255) NOT NULL,
			PRIMARY KEY ({$primary_key})
		) {$wpdb->get_charset_collate()};";

		maybe_create_table($table_name, $sql);
	}

	/**
	 * Get All Rows
	 * @return mixed
	 */
	public static function get_all() {
		global $wpdb;
		return $wpdb->get_results( sprintf( 'SELECT * FROM %s ORDER BY %s ASC', self::_table(), static::$primary_key ), ARRAY_A );
	}

	/**
	 * Get Categories by ids
	 */
	public static function get_categories_by_ids( $ids = [] ) {
		if ( empty( $ids )) { return  []; }

		global $wpdb;
		$sql = sprintf(
			'SELECT %1$s.*
					FROM %1$s
					WHERE %1$s.id IN ( %3$s )
					ORDER BY %1$s.%2$s DESC',
			self::_table(),
			static::$primary_key,
			implode(',', $ids )
		);

		return $wpdb->get_results($sql,ARRAY_A);
	}

	/**
	 * Get Categories with exist Services
	 */
	public static function get_with_exist_services() {
		global $wpdb;
		$sql = sprintf(
			'SELECT %1$s.*
					FROM %1$s
					INNER JOIN %2$s ON %1$s.id = %2$s.category_id 
					GROUP BY %1$s.id ORDER BY %1$s.%3$s DESC',
			self::_table(),
			Services::_table(),
			static::$primary_key
		);

		return $wpdb->get_results($sql,ARRAY_A);
	}

	/**
	 * Get Category
	 * @param $category_id
	 * @return mixed
	 */
	public static function get_one( $category_id ) {
		global $wpdb;
		return $wpdb->get_results( sprintf( 'SELECT * FROM %s WHERE id = %d ORDER BY %s ASC', self::_table(), intval($category_id), static::$primary_key ), ARRAY_A );
	}

	/**
	 * Get Category with services
	 * @param $category_id int
	 * @return mixed
	 */
	public static function category_with_services( $category_id ) {
		global $wpdb;
		$sql = sprintf(
			'SELECT %1$s.*, 
       				GROUP_CONCAT(%2$s.id, ":",%2$s.title) as services,
       				GROUP_CONCAT(%2$s.id) as service_ids
					FROM %1$s
					LEFT JOIN %2$s ON %1$s.id = %2$s.category_id
					WHERE %1$s.id = %%d
					GROUP BY %1$s.id',
			self::_table(),
			Services::_table()
		);
		return $wpdb->get_row( $wpdb->prepare( $sql, intval($category_id) ) );
	}

	/**
	 * Get All Services assosiated to category
	 */
	public static function get_category_total_assosiated_data( $category_id ) {
		global $wpdb;

		$sql = sprintf(
			'SELECT 
       				   GROUP_CONCAT(DISTINCT ( %2$s.id )) as service_ids,
				       COUNT(DISTINCT (%2$s.id )) as services, 
				       COUNT(%3$s.id) as staff, 
				       COUNT(%4$s.id) as appointments
					FROM %1$s
					LEFT JOIN %2$s ON %1$s.id = %2$s.category_id
					LEFT JOIN %3$s ON %3$s.service_id = %2$s.id
					LEFT JOIN %4$s ON %4$s.service_id = %2$s.id and %4$s.start_time > %%d
					WHERE  %1$s.id = %%d',
			self::_table(),
			Services::_table(),
			Staff_Services::_table(),
			Appointments::_table()
		);
		$now = current_time('timestamp');
		return $wpdb->get_row( $wpdb->prepare($sql, intval($now), intval($category_id) ) );
	}

	/**
	 * Delete Category
	 * set connected appointments status = delete
	 * delete connected services
	 * remove service connection from staff
	 * add notes about category and service to appointments
	 */
	public static function deleteCategory($id) {
		global $wpdb;

		$wpdb->query( "START TRANSACTION" );

		$category             = self::category_with_services($id);
		$categoryAppointments = Appointments::category_appointments($id);

		foreach ( $categoryAppointments as $appointment ) {
			$notes                      = unserialize($appointment->notes);
			$notes['delete_category']  = $category;

			$wpdb->update( Appointments::_table(),
				['notes' => serialize($notes), 'status' => Appointments::$delete],
				['id' => $appointment->id]
			);
		}
		// delete services
		Services::delete_where( 'category_id', $id);

		// delete staff connection
		if ( $category->service_ids != null ) {
			$sql = sprintf( 'DELETE FROM %s WHERE service_id IN (%s)', Staff_Services::_table(), $category->service_ids );
			$wpdb->query( $wpdb->prepare( $sql ) );
		}

		// delete category
		self::delete($id);

		$wpdb->query( "COMMIT" );
	}
}