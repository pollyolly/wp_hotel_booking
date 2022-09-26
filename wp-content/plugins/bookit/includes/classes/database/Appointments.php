<?php

namespace Bookit\Classes\Database;

use Bookit\Classes\Vendor\DatabaseModel;

class Appointments extends DatabaseModel {

	public static $pending      = 'pending';
	public static $approved     = 'approved';
	public static $cancelled    = 'cancelled';
	public static $complete     = 'complete';
	public static $delete       = 'delete'; // if deleted

	public static $statusList   = [ 'pending', 'approved', 'cancelled', 'delete' ];
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
			service_id INT UNSIGNED NOT NULL,
			staff_id INT UNSIGNED NOT NULL,
			customer_id INT UNSIGNED NOT NULL,
			date_timestamp INT NOT NULL,
			start_time INT NOT NULL,
			end_time INT NOT NULL,
			price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
			status VARCHAR(20) NOT NULL DEFAULT 'pending',
			notes longtext DEFAULT NULL,
			created_from ENUM('front', 'back') NOT NULL DEFAULT 'front',
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			PRIMARY KEY ({$primary_key}),
            INDEX `idx_service_id` (`service_id`),
            INDEX `idx_staff_id` (`staff_id`),
            INDEX `idx_customer_id` (`customer_id`),
            INDEX `idx_date_timestamp` (`date_timestamp`),
            INDEX `idx_start_time` (`start_time`),
            INDEX `idx_end_time` (`end_time`),
            INDEX `idx_status` (`status`)
		) {$wpdb->get_charset_collate()};";

		maybe_create_table($table_name, $sql);
	}

	/**
	 * Create Appointment with payment
	 */
	public static function create_appointment( $data ) {

		$appointment_data = [
			'staff_id'          => $data['staff_id'],
			'customer_id'       => $data['customer_id'],
			'service_id'        => $data['service_id'],
			'status'            => $data['status'],
			'date_timestamp'    => $data['date_timestamp'],
			'start_time'        => $data['start_time'],
			'end_time'          => $data['end_time'],
			'price'             => number_format((float)$data['clear_price'], 2, '.', ''),
			'notes'             => $data['notes'],
			'created_at'        => wp_date('Y-m-d H:i:s'),
			'updated_at'        => wp_date('Y-m-d H:i:s'),
		];

		self::insert($appointment_data);
		$appointment_id = self::insert_id();

		/** create payment **/
		if ( (float)$appointment_data['price'] == 0 ) {
			$data['payment_method'] = Payments::$freeType;
			$data['payment_status'] = Payments::$completeType;
		}

		$payment_data = [
			'appointment_id' => $appointment_id,
			'type'           => ( ! empty($data['payment_method']) ) ? $data['payment_method'] : Payments::$defaultType,
			'status'         =>  ( ! empty($data['payment_status']) ) ? $data['payment_status'] : Payments::$defaultStatus,
			'total'          => $appointment_data['price'],
			'created_at'     => wp_date('Y-m-d H:i:s'),
			'updated_at'     => wp_date('Y-m-d H:i:s'),
		];

		Payments::insert($payment_data);

		return $appointment_id;
	}

	/**
	 * Update Appointment with payment
	 */
	public static function update_appointment( $data, $id ) {

		$appointment = [
			'staff_id'          => $data['staff_id'],
			'service_id'        => $data['service_id'],
			'date_timestamp'    => $data['date_timestamp'],
			'start_time'        => $data['start_time'],
			'end_time'          => $data['end_time'],
			'price'             => number_format((float)$data['price'], 2, '.', ''),
			'status'            => $data['status'],
			'notes'             => $data['notes'],
			'created_from'      => $data['created_from'],
			'updated_at'        => wp_date('Y-m-d H:i:s'),
		];

		self::update( $appointment, [ 'id' => $id ] );

		/** update payment **/
		if ( $data['payment_method'] == Payments::$freeType ) {
			$data['payment_status'] = Payments::$completeType;
		}

		$payment_data = [
			'type'           => $data['payment_method'],
			'status'         => $data['payment_status'],
			'total'          => $appointment['price'],
			'updated_at'     => wp_date('Y-m-d H:i:s'),
		];
		Payments::update($payment_data, [ 'appointment_id' => $id ]);
	}

	/**
	 * Change Appointment status to delete and delete payment
	 */
	public static function delete_appointment( $id ) {

		$payment = Payments::get('appointment_id', $id);
		$notes['payment']   = $payment;

		/** update appointment , add payment info before delete */
		$appointment = [
			'status' => self::$delete,
			'notes' => serialize($notes),
			'updated_at' => wp_date('Y-m-d H:i:s')
		];
		self::update( $appointment, [ 'id' => $id ] );

		/** delete payment **/
		Payments::delete( $payment->id );
	}


	/**
	 * Get Customer Appointments
	 * @param $customer_id int
	 * @return mixed
	 */
	public static function customer_appointments( $customer_id ) {
		global $wpdb;
		$sql = sprintf(
			'SELECT %1$s.*
					FROM %1$s
					WHERE customer_id = %2$s ORDER BY %1$s.id ASC',
			self::_table(),
			$customer_id
		);
		return $wpdb->get_results( $wpdb->prepare( $sql ) );
	}

	/**
	 * Get Category Appointments
	 * @param $category_id int
	 * @return mixed
	 */
	public static function category_appointments( $category_id ) {
		global $wpdb;
		$sql = sprintf(
			'SELECT %1$s.*
					FROM %1$s
					LEFT JOIN %2$s ON %1$s.service_id = %2$s.id
					WHERE %2$s.category_id = %%d',
			self::_table(),
			Services::_table()
		);
		return $wpdb->get_results( $wpdb->prepare( $sql, intval($category_id) ) );
	}
	/**
	 * Get Service Appointments
	 * @param $service_id int
	 * @return mixed
	 */
	public static function service_appointments( $service_id ) {
		global $wpdb;
		$sql = sprintf(
			'SELECT %1$s.*
					FROM %1$s
					WHERE service_id = %2$s ORDER BY %1$s.id ASC',
			self::_table(),
			$service_id
		);
		return $wpdb->get_results( $wpdb->prepare( $sql ) );
	}

	/**
	 * Get Staff Appointments
	 * @param $staff_id int
	 * @return mixed
	 */
	public static function staff_appointments( $staff_id ) {
		global $wpdb;
		$sql = sprintf(
			'SELECT %1$s.*
					FROM %1$s
					WHERE staff_id = %2$s ORDER BY %1$s.id ASC',
			self::_table(),
			$staff_id
		);
		return $wpdb->get_results( $wpdb->prepare( $sql ) );
	}

	/**
	 * Get Rows with Pagination
	 *
	 * @param $limit
	 * @param $offset
	 * @param string $status
	 * @param string $sort
	 * @param string $order
	 *
	 * @return mixed
	 */
	public static function get_paged($limit, $offset, $status = '', $sort = '', $order = '', $filter = []) {
		global $wpdb;

		$search = '';
		if ( ! empty( $filter['search'] ) ) {
			$search =  " AND (
			". Customers::_table().".phone like '%{$filter['search']}%' 
			OR ". Customers::_table().".full_name like '%{$filter['search']}%' 
			OR ". Customers::_table().".email like '%{$filter['search']}%' )";
		}

		$sql = sprintf(
			'SELECT 
		                %1$s.*,%5$s.type as payment_method,
		                %5$s.status as payment_status,
		                %5$s.total as total,
		                %2$s.full_name as customer_name,
		                %2$s.email as customer_email,
		                %2$s.phone as customer_phone,
		                %3$s.full_name as staff_name,
		                %4$s.title as service_name
			FROM %1$s
			LEFT JOIN %2$s ON %1$s.customer_id = %2$s.id 
			LEFT JOIN %3$s ON %1$s.staff_id = %3$s.id 
			LEFT JOIN %4$s ON %1$s.service_id = %4$s.id 
			LEFT JOIN %5$s ON %1$s.id = %5$s.appointment_id 
			WHERE %1$s.status != "%6$s"
			%7$s %8$s %9$s %10$s ORDER BY %1$s.%11$s %12$s 
			LIMIT %13$d OFFSET %14$d',
			self::_table(),
			Customers::_table(),
			Staff::_table(),
			Services::_table(),
			Payments::_table(),
			self::$delete,
			( ! empty( $status ) ) ? " AND ". self::_table().".status = '{$status}'" : '',
			( ! empty( $filter['start'] ) ) ? " AND ". self::_table().".start_time >= {$filter['start']}" : '',
			( ! empty( $filter['end'] ) ) ? " AND ". self::_table().".end_time <= {$filter['end']}" : '',
			$search,
			( empty( $sort ) ) ? static::$primary_key : $sort,
			( empty( $order ) ) ? 'DESC' : $order,
			intval($limit),
			intval($offset)
		);

		return $wpdb->get_results( $sql,ARRAY_A );
	}

	/**
	 * Export All Rows
	 *
	 * @return mixed
	 */
	public static function export_all() {
		global $wpdb;
		return $wpdb->get_results(
			sprintf(
				'SELECT %1$s.*, 
		                %5$s.type as payment_method,
		                %5$s.status as payment_status,
		                %5$s.total as total,
		                %2$s.full_name as customer,
		                %2$s.phone as customer_phone,
		                %3$s.full_name as staff,
		                %4$s.title as service
				FROM %1$s
				LEFT JOIN %2$s ON %1$s.customer_id = %2$s.id 
				LEFT JOIN %3$s ON %1$s.staff_id = %3$s.id 
				LEFT JOIN %4$s ON %1$s.service_id = %4$s.id 
				LEFT JOIN %5$s ON %1$s.id = %5$s.appointment_id
				ORDER BY %1$s.%6$s DESC',
				self::_table(),
				Customers::_table(),
				Staff::_table(),
				Services::_table(),
				Payments::_table(),
				static::$primary_key
			),
			ARRAY_A
		);
	}

	/**
	 * Check Appointment
	 * @param $data
	 * @return mixed
	 */
	public static function checkAppointment( $data ) {
		global $wpdb;
		$sql = sprintf(
			'SELECT %1$s.id FROM %s 
					WHERE staff_id = %%d
					AND service_id = %%d
					AND status != "%2$s"
					AND status != "%3$s"
					AND date_timestamp = %%d
					AND ( start_time <= %%d AND end_time >= %%d )',
			self::_table(),
			self::$cancelled,
			self::$delete
		);
		return $wpdb->get_var(
			$wpdb->prepare(
				$sql,
				intval($data['staff_id']),
				intval($data['service_id']),
				intval($data['date_timestamp']),
				intval($data['start_time']),
				intval($data['end_time'])
			)
		);
	}

	/**
	 * Get Months Appointments
	 * @param $data
	 * @return mixed
	 */
	public static function month_appointments( $data ) {
		global $wpdb;
		$sql = sprintf(
			'SELECT date_timestamp, COUNT(*) appointments FROM %s 
					WHERE service_id = %%d AND status NOT IN ( "%2$s", "%3$s" )
					AND ( ( date_timestamp = %%d AND start_time >= %%d ) OR date_timestamp BETWEEN %%d AND %%d )
					GROUP BY date_timestamp',
			self::_table(),
			self::$cancelled,
			self::$delete
		);
		return $wpdb->get_results(
			$wpdb->prepare(
				$sql,
				intval($data['service_id']),
				intval($data['today_timestamp']),
				intval($data['now_timestamp']),
				intval($data['start_timestamp']),
				intval($data['end_timestamp'])
			)
		);
	}

	/**
	 * Get Day Appointments
	 * @param $data
	 * @return mixed
	 */
	public static function day_appointments( $data ) {
		global $wpdb;
		$sql = sprintf(
			'SELECT * FROM %s WHERE date_timestamp = %%d %s %s AND status NOT IN ( "%4$s", "%5$s" ) ORDER BY %1$s.%6$s',
			self::_table(),
			( ! empty($data['service_id']) ) ? "AND service_id = {$data['service_id']}" : "",
			( ! empty($data['staff_id']) ) ? "AND staff_id = {$data['staff_id']}" : "",
			self::$cancelled,
			self::$delete,
			'start_time'
		);
		return $wpdb->get_results(
			$wpdb->prepare(
				$sql,
				intval($data['date_timestamp'])
			)
		);
	}

	/**
	 * Get Pending Appointments Count
	 * @return mixed
	 */
	public static function pending_appointments() {
		global $wpdb;
		$date_utc   = new \DateTime("now", new \DateTimeZone("UTC"));
		$sql        = sprintf(
			'SELECT COUNT(*) FROM %s WHERE status = "%s" AND start_time >= %%d',
			self::_table(),
			self::$pending
		);
		return $wpdb->get_var(
			$wpdb->prepare(
				$sql,
				$date_utc->getTimestamp()
			)
		);
	}

	/**
	 * Get Total Count of Rows by status
	 * @param $status
	 * @return mixed
	 */
	public static function get_appointments_count( $status, $filter = [] ) {
		global $wpdb;

		$search = '';
		if ( ! empty( $filter['search'] ) ) {
			$search =  " AND (
			". Customers::_table().".phone like '%{$filter['search']}%' 
			OR ". Customers::_table().".full_name like '%{$filter['search']}%' 
			OR ". Customers::_table().".email like '%{$filter['search']}%' )";
		}

		return $wpdb->get_var( sprintf(
			'SELECT COUNT(*) FROM %1$s 
				LEFT JOIN %2$s ON %1$s.customer_id = %2$s.id
				WHERE %1$s.status != "%3$s" %4$s %5$s %6$s %7$s',
			self::_table(),
			Customers::_table(),
			self::$delete,
			( ! empty( $status ) ) ? " AND status = '{$status}'" : '',
			( ! empty( $filter['start'] ) ) ? " AND ". self::_table().".start_time >= {$filter['start']}" : '',
			( ! empty( $filter['end'] ) ) ? " AND ". self::_table().".end_time <= {$filter['end']}" : '',
			$search
		) );
	}

	/**
	 * Change Appointment Status
	 * @param $id
	 * @param $status
	 */
	public static function change_status( $id, $status ) {
		$data   = array( 'status' => $status );
		$where  = array( 'id' => $id );
		self::update( $data, $where );
	}

	/**
	 * Change Payment Status | used just for pro version
	 * @param $id
	 * @param $payment_status
	 */
	public static function change_payment_status( $id, $payment_status ) {
		Payments::change_payment_status( $id, $payment_status );
	}

	/**
	 * Get Admin Day Appointments
	 * @param $data
	 * @return mixed
	 */
	public static function get_full_appointment_by_id( $id ) {
		global $wpdb;
		$sql = sprintf(
			'SELECT %1$s.*, 
		                %2$s.email as customer_email,
		                %2$s.full_name as customer_name,
		                %2$s.phone as customer_phone,
		                %3$s.id as staff_id,
		                %3$s.email as staff_email,
		                %3$s.full_name as staff_name,
		                %3$s.phone as staff_phone,
		                %4$s.total as total,
		                %4$s.type as payment_method,
		                %4$s.status as payment_status,
		                %4$s.id as payment_id,
		                %5$s.title as service_name
			FROM %1$s
			LEFT JOIN %2$s ON %1$s.customer_id = %2$s.id 
			LEFT JOIN %3$s ON %1$s.staff_id = %3$s.id 
			LEFT JOIN %4$s ON %1$s.id = %4$s.appointment_id
			LEFT JOIN %5$s ON %1$s.service_id = %5$s.id
			WHERE %1$s.id = %%d',
			self::_table(),
			Customers::_table(),
			Staff::_table(),
			Payments::_table(),
			Services::_table()
		);

		return $wpdb->get_row( $wpdb->prepare( $sql, intval($id) ) );
	}

	/**
	 * Get appointments short data filter by date range
	 * @return array
	 */
	public static function appointments_by_date_full( $start, $end, $filter_data = [] ) {
		global $wpdb;
		$sql = sprintf(
			'SELECT  
               	 	%1$s.*,
					%2$s.title as service,
	                %2$s.icon_id as icon,
	                %3$s.email as customer_email,
	                %3$s.full_name as customer_name,
	                %3$s.phone as customer_phone,
	                %4$s.full_name as staff_name,
	                %5$s.type as payment_method,
	                %5$s.status as payment_status,
	                %5$s.total as total
			FROM %1$s
				LEFT JOIN %2$s ON %1$s.service_id = %2$s.id
				LEFT JOIN %3$s ON %1$s.customer_id = %3$s.id 
				LEFT JOIN %4$s ON %1$s.staff_id = %4$s.id 
				LEFT JOIN %5$s ON %1$s.id = %5$s.appointment_id
			WHERE %1$s.status != "%6$s" AND date_timestamp BETWEEN %%d AND %%d
			%7$s %8$s %9$s
			ORDER BY %1$s.start_time',
			self::_table(),
			Services::_table(),
			Customers::_table(),
			Staff::_table(),
			Payments::_table(),
			self::$delete,
			( ! empty($filter_data['service_ids']) ) ? "AND service_id IN ( {$filter_data['service_ids']} )" : "",
			( ! empty($filter_data['staff_id']) ) ? "AND staff_id = {$filter_data['staff_id']}" : "",
			( ! empty($filter_data['status']) ) ? sprintf("AND %s.status = '{$filter_data['status']}'", self::_table() ) : ""
		);

		return $wpdb->get_results( $wpdb->prepare($sql, intval($start), intval($end)) );
	}

	/**
	 * Get total appointments for service | staff
	 * @return array
	 */
	public static function get_total_active_assosiated_appointments( $service_ids = '', $staff_ids = '', $customer_ids = '') {
		global $wpdb;

		$sql = sprintf(
			'SELECT COUNT(%1$s.id)
					FROM %1$s
					WHERE start_time > %%d
					%2$s %3$s %4$s',
			self::_table(),
			( ! empty($service_ids) ) ? "AND service_id IN ( {$service_ids} )" : "",
			( ! empty($staff_ids) ) ? "AND staff_id IN ( {$staff_ids} )" : "",
			( ! empty($customer_ids) ) ? "AND customer_id IN ( {$customer_ids} )" : ""
		);

		$now = current_time('timestamp');
		return $wpdb->get_var( $wpdb->prepare($sql, intval($now)) );
	}
}