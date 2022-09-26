<?php

namespace Bookit\Classes\Admin;

use Bookit\Classes\Base\Plugin;
use Bookit\Classes\Base\User;
use Bookit\Classes\Database\Appointments;
use Bookit\Classes\Database\Customers;
use Bookit\Classes\Database\Services;
use Bookit\Classes\Database\Staff;
use Bookit\Classes\Database\Staff_Services;
use Bookit\Classes\Database\Staff_Working_Hours;
use Bookit\Classes\Template;
use Bookit\Helpers\CleanHelper;

class StaffController extends DashboardController {

	private static function getCleanRules() {
		return [
			'id'            => [ 'type' => 'intval' ],
			'limit'         => [ 'type' => 'intval' ],
			'offset'        => [ 'type' => 'intval' ],
			'email'         => [ 'type' => 'strval' ],
			'full_name'     => ['type' => 'strval' ],
			'phone'         => ['function' => ['custom' => true, 'name' => 'custom_sanitize_phone']],
		];
	}
	/**
	 * Display Rendered Template
	 * @return bool|string
	 */
	public static function render() {

		$bookitUser = self::bookitUser();

		/** show just self if this staff */
		if ( $bookitUser['is_staff'] == true ) {
			$staff  = $bookitUser['staff'];
		}else{
			$staff  = Staff::get_all();
		}

		$services   = Services::get_all_short();
		$wp_users   = get_users( ['fields' => [ 'ID', 'display_name', 'user_email' ], 'role__not_in' => ['administrator']] );

		$addons = [];
		$answer = [];
		/** if google calendar addon is installed */
		if ( Plugin::isAddonInstalledAndEnabled(self::$googleCalendarAddon) && has_filter('bookit_filter_connect_staff_google_calendar')) {
			$addons[] = Plugin::getAddonInfo( self::$googleCalendarAddon );
			$gcFilterResult = apply_filters('bookit_filter_connect_staff_google_calendar', $staff);
			$staff  = $gcFilterResult['staff'];
			if ( array_key_exists('answer', $gcFilterResult) ) {
				$answer = $gcFilterResult['answer'];
			}
		}
		/** if google calendar addon is installed | end */

		self::enqueue_styles_scripts();

		return Template::load_template(
			'dashboard/bookit-staff',
			[ 'staff'    => self::parseStaff($staff),
			  'services' => $services,
			  'addons'   => $addons,
			  'answer'   => $answer,
			  'wp_users' => $wp_users,
			  'page'     => __('Staff', 'bookit'),
			],
			true
		);
	}


	public static function parseStaff( $staff ) {
		$result = [];
		foreach ( $staff as $key => $employee ) {
			$item = (array)$employee;
			$item['staff_services'] = json_decode($employee['staff_services'], true) ?? [];
			$item['working_hours'] = json_decode($employee['working_hours'], true) ?? [];
			array_push($result, $item);
		}
		unset($staff);
		return $result;
	}

	/**
	 * Get Staff with Pagination
	 */
	public static function get_staff() {

		$data = CleanHelper::cleanData($_GET, self::getCleanRules());

		if ( ! empty( $data['limit'] ) ) {
			$response['staff'] = Staff::get_paged( $data['limit'], $data['offset'] );
			$response['total'] = Staff::get_count();

			wp_send_json_success( $response );
		}

		wp_send_json_error( [ 'message' => __('Error occurred!', 'bookit') ] );
	}

	/**
	 * Validate staff fields
	 */
	public static function validate( $data ) {
		$errors = [];

		if ( $data['phone'] || $data['phone'] === false ) {
			if ( !preg_match('/^((\+)?[0-9]{9,14})$/', $data['phone']) ) {
				$errors['phone'] = esc_html__('Please enter a valid phone number', 'bookit');
			}
		}

		if ( !$data['email'] ) {
			$errors['email'] = esc_html__('Email is required', 'bookit');
		}

		if ($data['email'] && !is_email($data['email'])) {
			$errors['email'] = esc_html__('Not valid Email', 'bookit');
		}

		if ( $data['full_name'] ) {
			if (strlen($data['full_name']) < 3 || strlen($data['full_name']) > 50 ){
				$errors['full_name'] = esc_html__('Full Name must be between 3 and 50 characters long', 'bookit');
			}
		}elseif( $data['object'] != 'wp_user' ){
			$errors['full_name'] = esc_html__('Full Name is required.', 'bookit');
		}

		if ( count($errors ) > 0 ) {
			wp_send_json_error( ['errors' => $errors, 'message' => esc_html__('Error occurred!', 'bookit')] );
		}
	}

	/**
	 * Create Wordpress User from staff form
	 */
	public static function create_wp_user() {
		check_ajax_referer('bookit_save_item', 'nonce');

		if ( !current_user_can( 'manage_options' ) ) {
			return false;
		}

		$data = CleanHelper::cleanData($_POST, self::getCleanRules());
		self::validate( $data );

		if ( get_user_by_email($data['email']) ) {
			wp_send_json_error( ['errors' => ['wp_email' => __('Wordpress User with such email already exist.')], 'message' => __('Error occurred!', 'bookit')] );
		}
		$data['role'] = User::$staff_role;

		$id     = Customers::save_or_get_wp_user($data);
		$wpUser = get_user_by('ID',$id)->data;

		wp_send_json_success(
			[ 'wp_user' => ['ID' => $wpUser->ID, 'display_name' => $wpUser->display_name, 'user_email' => $wpUser->user_email ],
			 'message' => __( 'Customer Saved!', 'bookit' )]
		);
	}

	/**
	 * Save Staff
	 */
	public static function save() {
		check_ajax_referer('bookit_save_item', 'nonce');

		if ( !current_user_can( 'manage_bookit_staff' ) ) {
			return false;
		}

		$data = CleanHelper::cleanData($_POST, self::getCleanRules());
		self::validate( $data );

		$id = ( ! empty( $data['id'] ) ) ? $data['id'] : null;

		/** if this is staff can edit just self data */
		$bookitUser = self::bookitUser();
		if ( $bookitUser['is_staff'] == true && ( $id == null || ( (int)$bookitUser['staff'][0]['id'] != (int)$id ) ) ) {
			return false;
		}

		if ( empty( $data ) ) {
			wp_send_json_error( [ 'message' => __('Error occurred!', 'bookit') ] );
			return false;
		}

		$staff_services = json_decode(stripslashes($data['staff_services']));
		$working_hours  = json_decode(stripslashes($data['working_hours']));

		unset( $data['staff_services'] );
		unset( $data['working_hours'] );
		unset( $data['gc_token'] );

		if ( $id ) {
			Staff::update( $data, [ 'id' => $id ] );

			Staff_Services::delete_where( 'staff_id', $id );

			foreach ( $working_hours as $working_hour ) {
				$update = [
					'id'            => $working_hour->id,
					'staff_id'      => $id,
					'weekday'       => $working_hour->weekday,
					'start_time'    => $working_hour->start_time,
					'end_time'      => $working_hour->end_time,
					'break_from'    => $working_hour->break_from,
					'break_to'      => $working_hour->break_to
				];
				Staff_Working_Hours::update( $update, [ 'id' => $update['id'] ] );
			}
		} else {
			Staff::insert( $data );

			$id = Staff::insert_id();

			foreach ( $working_hours as $working_hour ) {
				$insert = [
					'staff_id'      => $id,
					'weekday'       => $working_hour->weekday,
					'start_time'    => $working_hour->start_time,
					'end_time'      => $working_hour->end_time,
					'break_from'    => $working_hour->break_from,
					'break_to'      => $working_hour->break_to
				];
				Staff_Working_Hours::insert( $insert );
			}
		}

		foreach ( $staff_services as $staff_service ) {
			$insert = [
				'staff_id'      => $id,
				'service_id'    => $staff_service->id,
				'price'         => number_format((float)$staff_service->price, 2, '.', ''),
			];
			Staff_Services::insert( $insert );
		}

		/** set bookit staff role if wordpress user connected */
		if ( $data['wp_user_id'] ) {
			$wpUser = get_user_by('ID',$data['wp_user_id']);
			$wpUser->set_role(User::$staff_role);
		}

		/** if google calendar addon is installed */
		if ( Plugin::isAddonInstalledAndEnabled(self::$googleCalendarAddon) && has_filter('bookit_filter_connect_employee_google_calendar')) {
			$staff = (array)Staff::get( 'id', $id );
			$staff = apply_filters('bookit_filter_connect_employee_google_calendar', $staff);
		}
		/** if google calendar addon is installed | end */

		do_action( 'bookit_staff_saved', $id );

		wp_send_json_success( [ 'id' => $id, 'staff' => $staff, 'message' => __( 'Staff Saved!', 'bookit' )] );

	}

	/**
	 * Disconnect google calendar data from staff ( clean gc_token)
	 */
	public static function clean_gc_token() {
		check_ajax_referer('bookit_save_item', 'nonce');

		if ( !current_user_can( 'manage_options' ) ) {
			return false;
		}

		$data = CleanHelper::cleanData($_POST, self::getCleanRules());

		if ( !isset( $data['id'] ) || empty( $data['id'] ) ) {
			wp_send_json_error( [ 'message' => __('Error occurred!', 'bookit') ] );
		}


		Staff::update( ['gc_token' => null], [ 'id' => $data['id'] ] );
		$staff = (array)Staff::get( 'id', $data['id'] );

		/** if google calendar addon is installed */
		if ( Plugin::isAddonInstalledAndEnabled(self::$googleCalendarAddon) && has_action('bookit_filter_connect_employee_google_calendar') ) {
			$staff = apply_filters('bookit_filter_connect_employee_google_calendar', $staff);
		}
		/** if google calendar addon is installed | end */

		wp_send_json_success( [ 'id' => $staff['id'], 'staff' => $staff, 'message' => __( 'Staff disconnected!', 'bookit' )] );
	}

	/** Get Staff Assosiated data by id **/
	public static function get_assosiated_total_data_by_id() {
		check_ajax_referer('bookit_get_staff_assosiated_total_data', 'nonce');

		if ( !current_user_can( 'manage_options' ) ) {
			return false;
		}

		$data = CleanHelper::cleanData($_POST, self::getCleanRules());
		if ( empty( $data['id'] ) ) {
			wp_send_json_error( [ 'message' => __('Error occurred!', 'bookit') ] );
		}

		$services       = Staff::get_staff_total_service($data['id']);
		$appointments   = Appointments::get_total_active_assosiated_appointments( '', $data['id'] );

		$response = ['total' => ['services' => $services, 'appointments' => $appointments]];
		wp_send_json_success( $response );
	}

	/**
	 * Delete the Staff
	 */
	public static function delete() {
		check_ajax_referer('bookit_delete_item', 'nonce');

		if ( !current_user_can( 'manage_options' ) ) {
			return false;
		}

		$data = CleanHelper::cleanData($_GET, self::getCleanRules());

		if ( isset( $data['id'] ) ) {

			Staff::deleteStaff($data['id']);

			do_action( 'bookit_staff_deleted', $data['id'] );

			wp_send_json_success( [ 'message' => __('Staff Deleted!', 'bookit') ] );
		}

		wp_send_json_error( [ 'message' => __('Error occurred!', 'bookit') ] );
	}
}