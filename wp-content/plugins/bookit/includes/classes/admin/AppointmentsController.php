<?php

namespace Bookit\Classes\Admin;

use Bookit\Classes\Base\Plugin;
use Bookit\Classes\Database\Appointments;
use Bookit\Classes\Database\Customers;
use Bookit\Classes\Database\Payments;
use Bookit\Classes\Database\Services;
use Bookit\Classes\Database\Staff;
use Bookit\Classes\Template;
use Bookit\Helpers\CleanHelper;
use Bookit\Helpers\TimeSlotHelper;

class AppointmentsController extends DashboardController {

	private static function getCleanRules() {
		return array(
			'id'             => array( 'type' => 'intval' ),
			'limit'          => array( 'type' => 'intval' ),
			'offset'         => array( 'type' => 'intval' ),
			'sort'           => array( 'type' => 'strval' ),
			'order'          => array( 'type' => 'strval' ),
			'status'         => array( 'type' => 'strval' ),
			'staff_id'       => array( 'type' => 'intval' ),
			'service_id'     => array( 'type' => 'intval' ),
			'start_time'     => array( 'type' => 'intval' ),
			'end_time'       => array( 'type' => 'intval' ),
			'date_timestamp' => array( 'type' => 'intval' ),
			'price'          => array( 'type' => 'floatval' ),
			'customer_phone' => array(
				'function' => array(
					'custom' => true,
					'name'   => 'custom_sanitize_phone',
				),
			),
		);
	}

	/**
	 * Display Rendered Template
	 * @return bool|string
	 */
	public static function render() {
		self::enqueue_styles_scripts();
		return Template::load_template( 'dashboard/bookit-appointments', self::get_form_data(), true );
	}

	/**
	 * Get Appointment form data
	 */
	public static function get_form_data() {
		$payments        = get_option_by_path( 'bookit_settings.payments' );
		$payments        = array_filter(
			$payments,
			function ( $payment ) {
				return $payment['enabled'];
			}
		);
		$payment_methods = array_keys( $payments );

		array_push( $payment_methods, Payments::$freeType );

		$payment_methods = array_combine( $payment_methods, $payment_methods );
		array_walk(
			$payment_methods,
			function ( &$value ) {
				// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
				$value = esc_html__( $value, 'bookit' );
			}
		);

		$payment_statuses = array_combine( Payments::$statusList, Payments::$statusList );
		array_walk(
			$payment_statuses,
			function ( &$value ) {
				// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
				$value = esc_html__( $value, 'bookit' );
			}
		);

		$statuses = array_combine( Appointments::$statusList, Appointments::$statusList );
		array_walk(
			$statuses,
			function ( &$value ) {
				// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
				$value = esc_html__( ucwords( $value ), 'bookit' );
			}
		);
		unset( $statuses[ Appointments::$delete ] );

		$appointment_statuses = array(
			'payment'     => $payment_statuses,
			'appointment' => $statuses,
		);

		$services       = Services::get_all_with_category();
		$filterServices = self::parseServices( $services );
		$staff          = StaffController::parseStaff( Staff::get_all() );
		$customers      = Customers::get_all();

		$service_start  = 0;
		$service_end    = TimeSlotHelper::DAY_IN_SECONDS;
		$time_format    = get_option( 'time_format' );
		$time_slot_list = TimeSlotHelper::getTimeList( $service_start, $service_end );
		$settings       = SettingsController::get_settings();

		$autorefresh_list = array(
			60  => __( '1m' ),
			180 => __( '3m' ),
			300 => __( '5m' ),
			600 => __( '10m' ),
		);

		return array(
			'page'                 => __( 'Appointments', 'bookit' ),
			'settings'             => $settings,
			'filter_services'      => $filterServices,
			'services'             => $services,
			'staff'                => $staff,
			'customers'            => $customers,
			'payment_methods'      => $payment_methods,
			'appointment_statuses' => $appointment_statuses,
			'time_format'          => $time_format,
			'time_slot_list'       => $time_slot_list,
			'autorefresh_list'     => $autorefresh_list,
		);
	}

	/**
	 * @param Services $services
	 * @return array as tree category with child services
	 */
	public static function parseServices( $services ) {
		$result   = array();
		$category = array();

		foreach ( $services as $key => $service ) {
			// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			$existIndex = array_search( '0_' . $service->category_id, array_column( $result, 'id' ) );
			if ( false === $existIndex ) {
				$category['id']       = '0_' . $service->category_id;
				$category['label']    = $service->category;
				$category['children'] = array();

				array_push(
					$category['children'],
					array(
						'id'    => $service->id,
						'label' => $service->title,
					)
				);
				array_push( $result, $category );
			} else {
				$child = array(
					'id'    => $service->id,
					'label' => $service->title,
				);
				array_push( $result[ $existIndex ]['children'], $child );
			}
		}
		unset( $services );
		return $result;
	}

	/**
	 * Get Appointments with Pagination
	 */
	public static function get_appointments() {
		check_ajax_referer( 'bookit_get_appointments', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$data   = CleanHelper::cleanData( $_GET, self::getCleanRules() );
		$filter = array();

		if ( empty( $data['limit'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Error occurred!', 'bookit' ) ) );
		}

		/** Date filter */
		// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
		if ( in_array( 'start', $data ) && ! empty( $data['start'] ) && isDateByFormat( $data['start'], 'Y-m-d H:i' ) ) {
			$start           = \DateTime::createFromFormat( 'Y-m-d H:i', $data['start'], wp_timezone() );
			$filter['start'] = $start->format( 'U' );
		}

		// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
		if ( in_array( 'end', $data ) && ! empty( $data['end'] ) && isDateByFormat( $data['end'], 'Y-m-d H:i' ) ) {
			$end           = \DateTime::createFromFormat( 'Y-m-d H:i', $data['end'], wp_timezone() );
			$filter['end'] = $end->format( 'U' );
		}

		/** Search filter */
		// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
		if ( in_array( 'search', $data ) && ! empty( $data['search'] ) && strlen( $data['search'] ) > 2 ) {
			$filter['search'] = sanitize_text_field( $data['search'] );
		}

		$appointments = (array) Appointments::get_paged(
			$data['limit'],
			$data['offset'],
			( isset( $data['status'] )
				&& in_array( // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
					$data['status'],
					array( Appointments::$pending, Appointments::$approved, Appointments::$cancelled )
				) ) ? $data['status'] : '',
			( isset( $data['sort'] ) && 'id' === $data['sort'] ) ? $data['sort'] : '',
			// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			( isset( $data['order'] ) && in_array( $data['order'], array( 'asc', 'desc' ) ) ) ? $data['order'] : '',
			$filter
		);

		array_walk(
			$appointments,
			function ( &$value, $key ) {
				// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
				$value['notes'] = unserialize( trim( $value['notes'] ) );

				$dateTimestamp                 = \DateTime::createFromFormat( 'U', $value['date_timestamp'], wp_timezone() );
				$value['date_timestamp']       = $dateTimestamp->format( 'U' );
				$value['date_timestamp_title'] = $dateTimestamp->format( 'd.m.Y' );

				$value['price_row'] = get_option_by_path( 'bookit_settings.currency_symbol' ) . $value['price'];

				$startTime           = \DateTime::createFromFormat( 'U', $value['start_time'], wp_timezone() );
				$value['time_title'] = $startTime->format( 'H:i' );
			}
		);

		$response['appointments'] = $appointments;
		$response['total']        = Appointments::get_appointments_count( $data['status'], $filter );

		wp_send_json_success( $response );
	}

	/** Get Appointment by id **/
	public static function get_appointment_by_id() {
		check_ajax_referer( 'bookit_get_appointment', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$data = CleanHelper::cleanData( $_POST, self::getCleanRules() );

		if ( empty( $data['id'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Error occurred!', 'bookit' ) ) );
		}

		$appointment = Appointments::get_full_appointment_by_id( $data['id'] );

		/** Show email and phone from appointment notes */
		if ( property_exists( $appointment, 'notes' ) ) {
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
			$notes = unserialize( trim( $appointment->notes ) );

			if ( key_exists( 'email', $notes ) ) {
				$appointment->customer_email = $notes['email'];
			}

			if ( key_exists( 'phone', $notes ) ) {
				$appointment->customer_phone = $notes['phone'];
			}
		}

		/** Currency symbol with price */
		$appointment->price_row = get_option_by_path( 'bookit_settings.currency_symbol' ) . $appointment->price;

		$response['appointment'] = $appointment;
		wp_send_json_success( $response );
	}

	/** Get Appointment form data to edit/create **/
	public static function get_appointment_form_data() {
		check_ajax_referer( 'bookit_get_appointment_form_data', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		wp_send_json_success( self::get_form_data() );
	}

	/**
	 * Get Appointments Short data by date range
	 */
	public static function get_calendar_appointments() {
		check_ajax_referer( 'bookit_get_calendar_appointments', 'nonce' );

		$data = CleanHelper::cleanData( $_POST, self::getCleanRules() );

		if ( empty( $data ) || ! key_exists( 'start_timestamp', $data ) || ! key_exists( 'end_timestamp', $data ) ) {
			wp_send_json_error( array( 'message' => __( 'Error occurred!', 'bookit' ) ) );
		}

		$start = $data['start_timestamp'];
		$end   = $data['end_timestamp'];

		unset( $data['start_timestamp'] );
		unset( $data['end_timestamp'] );

		$appointments = Appointments::appointments_by_date_full( $start, $end, $data );
		$appointments = self::parseAppointments( $appointments, $data['is_detail'] );

		wp_send_json_success( $appointments );
	}

	/**
	 * @param Appointments $appointments
	 * @return array key is day number
	 */
	public static function parseAppointments( $appointments, $is_detail = false ) {
		$result = array();

		foreach ( $appointments as $key => $appointment ) {
			if ( ! key_exists( date( 'j_n', $appointment->start_time ), $result ) ) {
				$result[ date( 'j_n', $appointment->start_time ) ] = array();
			}

			$item          = (array) $appointment;
			$item['start'] = date( 'h:ia', $appointment->start_time );
			$item['end']   = date( 'h:ia', $appointment->end_time );
			$item['popup'] = false;

			$item['price_row'] = get_option_by_path( 'bookit_settings.currency_symbol' ) . $appointment->price;
			$item['icon_url']  = ( ! empty( $appointment->icon ) ) ? wp_get_attachment_url( $appointment->icon ) : null;

			if ( property_exists( $appointment, 'notes' ) ) {
				// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
				$notes = unserialize( trim( $appointment->notes ) );
				if ( key_exists( 'email', $notes ) ) {
					$item['customer_email'] = $notes['email'];
				}
				if ( key_exists( 'phone', $notes ) ) {
					$item['customer_phone'] = $notes['phone'];
				}
				if ( key_exists( 'comment', $notes ) ) {
					$item['comment'] = $notes['comment'];
				}
			}

			if ( ! $appointment->staff_name ) {
				$item['staff_name'] = __( 'Not Set', 'bookit' );
			}

			array_push( $result[ date( 'j_n', $appointment->start_time ) ], $item );
		}

		unset( $appointments );
		return $result;
	}

	/**
	 * Validation
	 * @param $data
	 */
	public static function validate( $data ) {
		$errors   = array();
		$settings = SettingsController::get_settings();

		$appointmentId = Appointments::checkAppointment( $data );

		if ( ( null !== $appointmentId && ! isset( $data['id'] ) ) || ( null !== $appointmentId && isset( $data['id'] ) && $appointmentId !== $data['id'] ) ) {
			$errors['dates'] = __( 'Selected Service Time is not available!', 'bookit' );
		}

		if ( ! in_array( $data['payment_method'], Payments::$typeList ) // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			|| false === ( $settings['payments'][ $data['payment_method'] ]['enabled']
				&& Payments::$freeType !== $data['payment_method'] ) ) {
			$errors['payment_method'] = __( 'Please choose correct payment method' );
		}

		if ( ! in_array( $data['payment_status'], Payments::$statusList ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			$errors['payment_status'] = __( 'Please choose correct payment status' );
		}

		if ( ! in_array( $data['status'], Appointments::$statusList ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			$errors['status'] = __( 'Please choose correct appointment status', 'bookit' );
		}

		if ( ! $data['customer_id'] ) {
			$errors['customer_id'] = __( 'Please choose customer', 'bookit' );
		}

		if ( ! $data['staff_id'] ) {
			$errors['staff_id'] = __( 'Please choose staff', 'bookit' );
		}

		if ( ! $data['service_id'] ) {
			$errors['service_id'] = __( 'Please choose service', 'bookit' );
		}

		if ( $data['staff_id'] && $data['service_id'] ) {
			$staff_service = Staff::get_by_id_and_service( $data['staff_id'], $data['service_id'] );

			if ( null === $staff_service ) {
				$errors['staff_service'] = __( 'Please choose correct staff and service' );
			}
		}

		if ( 0 === $data['start_time'] || 0 === $data['end_time'] ) {
			$errors['dates'] = __( 'Please choose appointment time' );
		}

		if ( empty( $data['date_timestamp'] ) || 0 === $data['date_timestamp'] ) {
			$errors['dates'] = __( 'Please choose appointment date' );
		}

		if ( $data['customer_phone'] || false === $data['customer_phone'] ) {
			if ( ! preg_match( '/^((\+)?[0-9]{8,14})$/', $data['customer_phone'] ) ) {
				$errors['customer_phone'] = __( 'Please enter a valid phone number' );
			}
		}

		if ( count( $errors ) > 0 ) {
			wp_send_json_error( array( 'errors' => $errors ) );
		}
	}

	/**
	 * Create Appointment
	 */
	public static function save() {
		check_ajax_referer( 'bookit_add_appointment', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}
		$data = CleanHelper::cleanData( $_POST, self::getCleanRules() );
		self::validate( $data );

		/**
		 * CREATE APPOINTMENT CODE
		 */
		if ( ! empty( $data ) ) {
			$data['created_from'] = 'back';
			$notes['comment']     = $data['comment'];

			$customer = Customers::get( 'id', $data['customer_id'] );

			if ( $customer->phone !== $data['customer_phone'] ) {
				$notes['phone'] = $data['customer_phone'];
			}
			/** add phone to customer if not filled yet and value exist **/
			if ( null === $customer->phone && ! empty( $data['customer_phone'] ) ) {
				Customers::update( array( 'phone' => $data['customer_phone'] ), array( 'id' => $customer->id ) );
			}

			$data['notes'] = serialize( $notes ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize

			$id = Appointments::create_appointment( $data );

			do_action( 'bookit_appointment_created', $id );

			$appointment          = (array) Appointments::get_full_appointment_by_id( $id );
			$appointment['notes'] = unserialize( trim( $data['notes'] ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize

			$dateTimestamp                       = \DateTime::createFromFormat( 'U', $appointment['date_timestamp'], wp_timezone() );
			$appointment['date_timestamp_title'] = $dateTimestamp->format( 'd.m.Y' );
			$startTime                           = \DateTime::createFromFormat( 'U', $appointment['start_time'], wp_timezone() );
			$endTime                             = \DateTime::createFromFormat( 'U', $appointment['end_time'], wp_timezone() );
			$appointment['time_title']           = $startTime->format( 'H:i' ) . ' - ' . $endTime->format( 'H:i' );

			/** if google calendar addon is installed */
			if ( Plugin::isAddonInstalledAndEnabled( self::$googleCalendarAddon ) && has_action( 'bookit_google_calendar_create_appointment' )
				&& ! empty( get_option_by_path( 'bookit_google_calendar_settings.client_id' ) ) && ! empty( get_option_by_path( 'bookit_google_calendar_settings.client_secret' ) ) ) {
				do_action( 'bookit_google_calendar_create_appointment', $appointment );
			}
			/** if google calendar addon is installed | end */

			wp_send_json_success(
				array(
					'appointment' => $appointment,
					'message'     => __( 'Appointment Saved!', 'bookit' ),
				)
			);
		}
	}

	/**
	 * Update Appointment
	 */
	public static function update() {
		check_ajax_referer( 'bookit_edit_appointment', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$data = CleanHelper::cleanData( $_POST, self::getCleanRules() );
		self::validate( $data );

		if ( ! empty( $data['id'] ) ) {

			$appointment = (array) Appointments::get_full_appointment_by_id( $data['id'] );
			$notes       = unserialize( $appointment['notes'] ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize

			$customer = Customers::get( 'id', $data['customer_id'] );
			if ( $customer->phone !== $data['customer_phone'] && $notes['phone'] !== $data['customer_phone'] ) {
				$notes['phone'] = $data['customer_phone'];
			}
			if ( $data['comment'] && $notes['comment'] !== $data['comment'] ) {
				$notes['comment'] = $data['comment'];
			}
			$data['notes'] = serialize( $notes ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize

			Appointments::update_appointment( $data, $data['id'] );
			/** add phone to customer if not filled yet and value exist **/
			if ( null === $customer->phone && ! empty( $data['customer_phone'] ) ) {
				Customers::update( array( 'phone' => $data['customer_phone'] ), array( 'id' => $customer->id ) );
			}

			do_action( 'bookit_appointment_updated', $data['id'] );

			$updatedAppointment          = (array) Appointments::get_full_appointment_by_id( $data['id'] );
			$updatedAppointment['notes'] = unserialize( trim( $updatedAppointment['notes'] ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize

			$dateTimestamp                              = \DateTime::createFromFormat( 'U', $updatedAppointment['date_timestamp'], wp_timezone() );
			$updatedAppointment['date_timestamp_title'] = $dateTimestamp->format( 'd.m.Y' );
			$startTime                                  = \DateTime::createFromFormat( 'U', $updatedAppointment['start_time'], wp_timezone() );
			$endTime                                    = \DateTime::createFromFormat( 'U', $updatedAppointment['end_time'], wp_timezone() );
			$updatedAppointment['time_title']           = $startTime->format( 'H:i' ) . ' - ' . $endTime->format( 'H:i' );

			/** if google calendar addon is installed */
			if ( Plugin::isAddonInstalledAndEnabled( self::$googleCalendarAddon ) && has_action( 'bookit_google_calendar_update_appointment' )
				&& ! empty( get_option_by_path( 'bookit_google_calendar_settings.client_id' ) ) && ! empty( get_option_by_path( 'bookit_google_calendar_settings.client_secret' ) ) ) {
				do_action( 'bookit_google_calendar_update_appointment', $appointment, $updatedAppointment );
			}
			/** if google calendar addon is installed | end */

			wp_send_json_success(
				array(
					'appointment' => $updatedAppointment,
					'message'     => __( 'Appointment Updated!', 'bookit' ),
				)
			);
		}

		wp_send_json_error( array( 'message' => __( 'Error occurred!', 'bookit' ) ) );
	}

	/**
	 * Change Status of Appointment
	 */
	public static function change_status() {
		check_ajax_referer( 'bookit_appointment_status', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$data = CleanHelper::cleanData( $_GET, self::getCleanRules() );

		if ( isset( $data['id'] ) && in_array( $data['status'], array( Appointments::$approved, Appointments::$cancelled, Appointments::$pending ), true ) ) {
			$appointment = (array) Appointments::get_full_appointment_by_id( $data['id'] );
			Appointments::change_status( $data['id'], $data['status'] );

			do_action( 'bookit_appointment_status_changed', $data['id'] );

			/** if google calendar addon is installed */
			if ( Plugin::isAddonInstalledAndEnabled( self::$googleCalendarAddon ) && has_action( 'bookit_google_calendar_update_appointment' )
				&& ! empty( get_option_by_path( 'bookit_google_calendar_settings.client_id' ) ) && ! empty( get_option_by_path( 'bookit_google_calendar_settings.client_secret' ) ) ) {
				$updatedAppointment           = $appointment;
				$updatedAppointment['status'] = $data['status'];

				do_action( 'bookit_google_calendar_update_appointment', $appointment, $updatedAppointment );
			}
			/** if google calendar addon is installed | end */

			wp_send_json_success( array( 'message' => __( 'Appointment Status Changed!', 'bookit' ) ) );
		}

		wp_send_json_error( array( 'message' => __( 'Error occurred!', 'bookit' ) ) );
	}

	/**
	 * Delete the Appointment
	 */
	public static function delete() {
		check_ajax_referer( 'bookit_delete_item', 'nonce' );

		$data = CleanHelper::cleanData( $_POST, self::getCleanRules() );

		if ( isset( $data['id'] ) ) {

			$id = $data['id'];

			$send_notification = json_decode( stripslashes( $data['send_notification'] ), true );
			do_action( 'bookit_appointment_deleted', $data['id'], $send_notification, $data['reason'] );

			/** if google calendar addon is installed */
			if ( Plugin::isAddonInstalledAndEnabled( self::$googleCalendarAddon ) && has_action( 'bookit_google_calendar_create_appointment' )
				&& ! empty( get_option_by_path( 'bookit_google_calendar_settings.client_id' ) ) && ! empty( get_option_by_path( 'bookit_google_calendar_settings.client_secret' ) ) ) {
				$appointment = (array) Appointments::get_full_appointment_by_id( $id );
				do_action( 'bookit_google_calendar_delete_appointment', $appointment );
			}
			/** if google calendar addon is installed | end */

			Appointments::delete_appointment( $id );

			wp_send_json_success( array( 'message' => __( 'Appointment Deleted!', 'bookit' ) ) );
		}

		wp_send_json_error( array( 'message' => __( 'Error occurred!', 'bookit' ) ) );
	}

}
