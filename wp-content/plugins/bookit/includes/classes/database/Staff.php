<?php

namespace Bookit\Classes\Database;

use Bookit\Classes\Vendor\DatabaseModel;

class Staff extends DatabaseModel {

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
			wp_user_id BIGINT(20),
			full_name VARCHAR(255) NOT NULL,
			email VARCHAR(255),
			phone VARCHAR(255),
			PRIMARY KEY ({$primary_key})
		) {$wpdb->get_charset_collate()};";

		maybe_create_table($table_name, $sql);
	}

	/**
	 * Get All Staff
	 * @return mixed
	 */
    //todo refactor
	public static function get_all() {
		global $wpdb;

        $staffList = $wpdb->get_results(
			sprintf(
				'SELECT %1$s.*, 
						CONCAT( \'[\', GROUP_CONCAT(DISTINCT CONCAT( 
							\'{"id":\', %2$s.id,
							\', "weekday":\', %2$s.weekday,
							\', "start_time":"\', IFNULL(LEFT(%2$s.start_time, 8), "NULL"),
							\'", "end_time":"\', IFNULL(LEFT(%2$s.end_time, 8), "NULL"),
							\'", "break_from":"\', IFNULL(LEFT(%2$s.break_from, 8), "NULL"),
							\'", "break_to":"\', IFNULL(LEFT(%2$s.break_to, 8), "NULL"),
						\'"}\' ) ), \']\' ) as working_hours
						FROM %1$s 
						LEFT JOIN %2$s ON %2$s.staff_id = %1$s.id 
						GROUP BY %1$s.%3$s ORDER BY %1$s.full_name DESC',
				self::_table(),
				Staff_Working_Hours::_table(),
				static::$primary_key
			),
			ARRAY_A
		);


        $services = [];
        /** append staff service data */
        if ( count($staffList) > 0 ){
            $services = Staff_Services::get_staff_services(array_column($staffList, 'id'));
        }

        foreach ( $staffList as $key => $employee ) {
            $staffServices                     = [];
            $keys = array_keys(array_column($services, 'staff_id'), $employee['id']);

            for ( $i = 0; $i < count( $keys ); $i++ ) {
                $service['id']            = $services[$keys[$i]]['serviceId'];
                $service['price']         = $services[$keys[$i]]['price'];
                $service['service_title'] = $services[$keys[$i]]['title'];
                array_push($staffServices, $service);
            }
            $staffList[$key]['staff_services'] = json_encode($staffServices);
        }

        return $staffList;
	}

	/**
	 * Get Staff by id and service
	 * @return mixed
	 */
	public static function get_by_id_and_service($id, $service_id) {
		global $wpdb;
		$sql = sprintf(
			'SELECT %1$s.id FROM %1$s
					LEFT JOIN %2$s ON %1$s.id = %2$s.staff_id
					WHERE %1$s.id = %%d AND %2$s.service_id = %%d',
			self::_table(),
			Staff_Services::_table(),
			static::$primary_key
		);

		return $wpdb->get_var( $wpdb->prepare($sql, intval($id), intval($service_id)) );
	}

	/**
	 * Get All Staff
	 * @return mixed
	 */
	public static function get_all_shorted() {
		global $wpdb;
		return $wpdb->get_results(
			sprintf(
				'SELECT * FROM %s ORDER BY %s DESC',
				self::_table(),
				static::$primary_key
			),
			ARRAY_A
		);
	}

	/**
	 * Get Staff
	 * @param $staff_id
	 * @return mixed
	 */
	public static function get_one( $staff_id ) {
		global $wpdb;
		return $wpdb->get_results(
			sprintf(
				'SELECT %1$s.*, 
						CONCAT( \'[\', GROUP_CONCAT(DISTINCT CONCAT(
							\'{"id":\', %2$s.id,
							\', "category_id":\', %2$s.category_id,
						    \', "title":"\', %2$s.title,
							\'", "price":"\', %3$s.price,
						\'"}\' ) ), \']\' ) as staff_services,
						CONCAT( \'[\', GROUP_CONCAT(DISTINCT CONCAT( 
							\'{"id":\', %4$s.id,
							\', "weekday":\', %4$s.weekday,
							\', "start_time":"\', IFNULL(LEFT(%4$s.start_time, 8), "NULL"),
							\'", "end_time":"\', IFNULL(LEFT(%4$s.end_time, 8), "NULL"),
							\'", "break_from":"\', IFNULL(LEFT(%4$s.break_from, 8), "NULL"),
							\'", "break_to":"\', IFNULL(LEFT(%4$s.break_to, 8), "NULL"),
						\'"}\' ) ), \']\' ) as working_hours
						FROM %1$s 
						LEFT JOIN %3$s ON %3$s.staff_id = %1$s.id 
						LEFT JOIN %2$s ON %3$s.service_id = %2$s.id 
						LEFT JOIN %4$s ON %4$s.staff_id = %1$s.id 
						WHERE %1$s.id = %6$d
						GROUP BY %1$s.%5$s ORDER BY %1$s.%5$s DESC',
				self::_table(),
				Services::_table(),
				Staff_Services::_table(),
				Staff_Working_Hours::_table(),
				static::$primary_key,
				intval($staff_id)
			),
			ARRAY_A
		);
	}

	/**
	 * Get All Service assosiated to staff
	 */
	public static function get_staff_total_service( $staff_id ) {
		global $wpdb;
		$sql = sprintf(
			'SELECT COUNT(%2$s.id)
					FROM %1$s
					LEFT JOIN %2$s ON %1$s.id = %2$s.staff_id 
					WHERE  %1$s.id = %%d',
			self::_table(),
			Staff_Services::_table()
		);
		return $wpdb->get_var( $wpdb->prepare( $sql, intval($staff_id) ) );
	}

	/**
	 * Delete Staff
	 * set staff appointments status = delete
	 * remove staff connection from service
	 * add notes about staff to appointments
	 */
	public static function deleteStaff($id) {
		global $wpdb;

		$wpdb->query( "START TRANSACTION" );

		$staff              = self::get('id', $id);
		$staffAppointments  = Appointments::staff_appointments($id);

		foreach ( $staffAppointments as $appointment ) {
			$notes                  = unserialize($appointment->notes);
			$notes['delete_staff']  = $staff;

			$wpdb->update( Appointments::_table(),
				['notes' => serialize($notes), 'status' => Appointments::$delete],
				['id' => $appointment->id]
			);
		}
		// delete service connection
		Staff_Services::delete_where( 'staff_id', $id);

		// delete staff working hours
		Staff_Working_Hours::delete_where( 'staff_id', $id);

		// delete staff
		self::delete($id);

		$wpdb->query( "COMMIT" );
	}

	/**
	 * Get Staff by wp user id
	 * @param $staff_id
	 * @return mixed
	 */
	public static function get_by_wp_user_id( $wp_user_id ) {
		global $wpdb;
		return $wpdb->get_results(
			sprintf(
				'SELECT %1$s.*, 
						CONCAT( \'[\', GROUP_CONCAT(DISTINCT CONCAT(
							\'{"id":\', %2$s.id,
							\', "title":"\', %2$s.title,
							\'", "price":"\', %3$s.price,
						\'"}\' ) ), \']\' ) as staff_services,
						CONCAT( \'[\', GROUP_CONCAT(DISTINCT CONCAT( 
							\'{"id":\', %4$s.id,
							\', "weekday":\', %4$s.weekday,
							\', "start_time":"\', IFNULL(LEFT(%4$s.start_time, 8), "NULL"),
							\'", "end_time":"\', IFNULL(LEFT(%4$s.end_time, 8), "NULL"),
							\'", "break_from":"\', IFNULL(LEFT(%4$s.break_from, 8), "NULL"),
							\'", "break_to":"\', IFNULL(LEFT(%4$s.break_to, 8), "NULL"),
						\'"}\' ) ), \']\' ) as working_hours
						FROM %1$s 
						LEFT JOIN %3$s ON %3$s.staff_id = %1$s.id 
						LEFT JOIN %2$s ON %3$s.service_id = %2$s.id 
						LEFT JOIN %4$s ON %4$s.staff_id = %1$s.id 
						WHERE %1$s.wp_user_id = %6$d
						GROUP BY %1$s.%5$s ORDER BY %1$s.%5$s DESC',
				self::_table(),
				Services::_table(),
				Staff_Services::_table(),
				Staff_Working_Hours::_table(),
				static::$primary_key,
				intval($wp_user_id)
			),
			ARRAY_A
		);
	}
}