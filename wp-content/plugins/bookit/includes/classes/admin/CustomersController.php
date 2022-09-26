<?php

namespace Bookit\Classes\Admin;

use Bookit\Classes\Base\User;
use Bookit\Classes\Database\Appointments;
use Bookit\Classes\Database\Customers;
use Bookit\Classes\Template;
use Bookit\Helpers\CleanHelper;

class CustomersController extends DashboardController {

	private static $sortFields = [ 'id', 'full_name', 'email', 'phone' ];

	private static function getCleanRules() {
		return [
			'id'                => [ 'type' => 'intval'],
			'limit'             => [ 'type' => 'intval' ],
			'offset'            => [ 'type' => 'intval' ],
			'sort'              => [ 'type' => 'strval' ],
			'order'             => [ 'type' => 'strval' ],
			'search'            => [ 'type' => 'strval' ],
			'full_name'         => ['type' => 'strval'],
			'phone'             => ['function' => ['custom' => true, 'name' => 'custom_sanitize_phone']],
			'email'             => [ 'type' => 'strval', 'function' => ['custom' => false, 'name' => 'sanitize_email'] ],
		];
	}

	/**
	 * Display Rendered Template
	 * @return bool|string
	 */
	public static function render() {
		self::enqueue_styles_scripts();

		$user_args  = [
			'fields' => [ 'ID', 'display_name' ]
		];
		$wp_users   = get_users( $user_args );

		return Template::load_template( 'dashboard/bookit-customers',
			['wp_users' => $wp_users, 'page' => __('Customers', 'bookit'),],
			true
		);
	}

	/**
	 * Get Customers with Pagination
	 */
	public static function get_customers() {
		check_ajax_referer('bookit_get_customers', 'nonce');

		if ( !current_user_can( 'manage_options' ) ) {
			return false;
		}

		$data = CleanHelper::cleanData($_GET, self::getCleanRules());

		if ( ! empty( $data['limit'] ) ) {
			$response['customers'] = Customers::get_paged(
				$data['limit'],
				$data['offset'],
				( ! empty( $data['search'] ) ) ? "WHERE full_name LIKE '%{$data['search']}%' OR email LIKE '%{$data['search']}%' OR phone LIKE '%{$data['search']}%'" : '',
				( isset( $data['sort'] ) && in_array( $data['sort'], self::$sortFields ) ) ? $data['sort'] : '',
				( isset( $data['order'] ) && in_array( $data['order'], ['asc', 'desc']) ) ? $data['order'] : ''
			);
			$response['total'] = ( ! empty( $data['search'] ) ) ? count($response['customers']) : Customers::get_count();

			wp_send_json_success( $response );
		}

		wp_send_json_error( [ 'message' => __('Error occurred!', 'bookit') ] );
	}

	/**
	 * Validate post fields
	 */
	public static function validate( $data ) {
		$errors = [];

		if ( $data['phone'] || $data['phone'] === false ) {
			if (!preg_match('/^((\+)?[0-9]{9,14})$/', $data['phone'])) {
				$errors['phone'] = esc_html__('Please enter a valid phone number', 'bookit');
			}
		}

		if (!$data['email']) {
			$errors['email'] = esc_html__("Email is required", 'bookit');
		}

		if ($data['email'] && !is_email($data['email'])) {
			$errors['email'] = __('Not valid Email');
		}

		$customer = Customers::get('email', $data['email']);
		if ( $customer != null && !isset($data['id']) ) {
			$errors['email'] = __('Customer with such email already exist');
		}

		if ($data['full_name']) {
			if ( strlen($data['full_name']) < 3 || strlen($data['full_name']) > 25 ){
				$errors['full_name'] = __('Full name must be between 3 and 25 characters long');
			}
		}else{
			$errors['full_name'] = __('Full Name is required.');
		}
		$settings_booking_type = get_option_by_path('bookit_settings.booking_type');
		if ( isset($data['from']) && $data['from'] == 'calendar'  && $settings_booking_type == 'registered' ) {

			if ( empty( $data['password'] ) ) {
				$errors['password'] = __('Please enter a password');
			}

			if ( false !== strpos( wp_unslash( $data['password'] ), '\\' ) ) {
				$errors['password'] = __("Passwords may not contain the character '\\'");
			}

			if ( ( ! empty( $data['password'] ) ) && $data['password'] != $data['password_confirmation'] ) {
				$errors['password_confirmation'] = __("Please enter the same password in both password fields");
			}
		}

		if ( count($errors ) > 0 ) {
			wp_send_json_error( ['errors' => $errors, 'message' => __('Error occurred!', 'bookit')] );
		}
	}

	/**
	 * Create Customer from appointment
	 */
	public static function create() {
		check_ajax_referer('bookit_save_item', 'nonce');

		if ( !current_user_can( 'manage_options' ) ) {
			return false;
		}

		$data = CleanHelper::cleanData($_POST, self::getCleanRules());
		self::validate( $data );
		unset($data['id']);

		$exist = Customers::get('email', $data['email']);
		if ( $exist ) {
			wp_send_json_error( ['errors' => ['email' => __('Customer with such email already exist.')], 'message' => __('Error occurred!', 'bookit')] );
		}

		/** save in wordpress users based on booking type value */
		$settings_booking_type = get_option_by_path('bookit_settings.booking_type');
		if ( $settings_booking_type == 'registered' ) {
			$data['role']       = User::$customer_role;
			$data['wp_user_id'] = Customers::save_or_get_wp_user($data);
		}

		unset($data['role']);
		unset($data['password']);
		unset($data['password_confirmation']);
		unset($data['from']);

		Customers::insert( $data );
		$customer =  Customers::get('id', Customers::insert_id());

		wp_send_json_success( [ 'customer' => $customer, 'message' => __( 'Customer Saved!', 'bookit' )] );
	}

	/**
	 * Save Customer
	 */
	public static function save() {
		check_ajax_referer('bookit_save_item', 'nonce');

		if ( !current_user_can( 'manage_options' ) ) {
			return false;
		}

		$data = CleanHelper::cleanData($_POST, self::getCleanRules());
		self::validate( $data );

		if ( ! empty( $data ) ) {
			if ( ! empty( $data['id'] ) ) {
				Customers::update( $data, [ 'id' => $data['id'] ] );
			} else {
				Customers::insert( $data );
				$data['id'] = Customers::insert_id();
			}

			do_action( 'bookit_customer_saved', $data['id'] );

			wp_send_json_success( [ 'id' => $data['id'], 'message' => __( 'Customer Saved!', 'bookit' )] );
		}

		wp_send_json_error( [ 'message' => __('Error occurred!', 'bookit') ] );
	}

	/** Get Service Assosiated data by id **/
	public static function get_assosiated_total_data_by_id() {
		check_ajax_referer('bookit_get_customer_assosiated_total_data', 'nonce');

		if ( !current_user_can( 'manage_options' ) ) {
			return false;
		}

		$data = CleanHelper::cleanData($_POST, self::getCleanRules());

		if ( empty( $data['id'] ) ) {
			wp_send_json_error( [ 'message' => __('Error occurred!', 'bookit') ] );
		}

		$appointments   = Appointments::get_total_active_assosiated_appointments( '', '', $data['id'] );

		$response = ['total' => ['appointments' => $appointments]];
		wp_send_json_success( $response );
	}

	/**
	 * Delete the Customer
	 */
	public static function delete() {
		check_ajax_referer('bookit_delete_item', 'nonce');

		if ( !current_user_can( 'manage_options' ) ) {
			return false;
		}

		$data = CleanHelper::cleanData($_GET, self::getCleanRules());

		if ( isset( $data['id'] ) ) {
			$id = $data['id'];

			Customers::deleteCustomer( $id );

			do_action( 'bookit_customer_deleted', $id );

			wp_send_json_success( [ 'message' => __('Customer Deleted!', 'bookit') ] );
		}

		wp_send_json_error( [ 'message' => __('Error occurred!', 'bookit') ] );
	}
}