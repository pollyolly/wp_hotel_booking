<?php

namespace Bookit\Classes\Admin;

use Bookit\Classes\Database\Categories;
use Bookit\Classes\Database\Services;
use Bookit\Classes\Database\Staff;
use Bookit\Classes\Database\Staff_Services;
use Bookit\Classes\Database\Staff_Working_Hours;
use Bookit\Classes\Database\Appointments;

class ImportExportController {

	/**
	 * Export Bookit Data
	 */
	public static function export() {
		check_ajax_referer('bookit_export', 'nonce');

		if ( !current_user_can( 'manage_options' ) ) {
			return false;
		}

		$settings    = SettingsController::get_settings();
		$services    = Services::get_all();
		$categories  = Categories::get_all();
		$staff       = Staff::get_all();
		$export_data = [
			'Settings'   => $settings,
			'Categories' => $categories,
			'Service'    => $services,
			'Staff'      => $staff
		];
		if ( $export_data ) {
			$export_data = json_encode( $export_data );
			header( 'Content-Description: File Transfer' );
			header( 'Content-Disposition: attachment; filename=' . 'bookit_data.txt' );
			header( 'Content-Type: text/xml; charset=' . get_option( 'blog_charset' ), true );
			echo sanitize_text_field( $export_data );
			die;
		}
	}

	/**
	 * Export Excel Data
	 */
	public static function export_excel() {
		check_ajax_referer('bookit_export', 'nonce');

		if ( !current_user_can( 'manage_options' ) ) {
			return false;
		}

		$appointments   = Appointments::export_all();
		$data           = [];
		$headers        = [
			'ID',
			'CUSTOMER',
			'CUSTOMER PHONE',
			'STAFF',
			'SERVICE',
			'PRICE',
			'DATE',
			'TIME',
			'PAYMENT',
			'STATUS'
		];

		foreach ( $appointments as $key => $appointment ) {
			foreach ( $headers as $header ) {
				switch ( $header ) {
					case 'ID':
						$data[ $key ][ $header ] = $appointment['id'];
						break;
					case 'CUSTOMER':
						$data[ $key ][ $header ] = $appointment['customer'];
						break;
					case 'CUSTOMER PHONE':
						$data[ $key ][ $header ] = $appointment['customer_phone'];
						break;
					case 'STAFF':
						$data[ $key ][ $header ] = $appointment['staff'];
						break;
					case 'SERVICE':
						$data[ $key ][ $header ] = $appointment['service'];
						break;
					case 'PRICE':
						$data[ $key ][ $header ] = $appointment['price'];
						break;
					case 'DATE':
						$data[ $key ][ $header ] = date_i18n( get_option( 'date_format' ), $appointment['date_timestamp'] );
						break;
					case 'TIME':
						$data[ $key ][ $header ] = date_i18n( get_option( 'time_format' ), $appointment['start_time'] ) . ' - ' . date_i18n( get_option( 'time_format' ), $appointment['end_time'] );
						break;
					case 'PAYMENT':
						$data[ $key ][ $header ] = $appointment['payment_method'] . ' ' . $appointment['payment_status'];
						break;
					case 'STATUS':
						$data[ $key ][ $header ] = $appointment['status'];
						break;
				}
			}
		}

		self::export_appointment_excel( $data );
	}

	/**
	 * Export Data as Excel file
	 * @param $data
	 */
	public static function export_appointment_excel( $data ) {
		header( 'Content-Type: application/csv' );
		header( 'Content-Disposition: attachment; filename="appintments_booket_data.csv";' );
		ob_end_clean();
		$handle = fopen( 'php://output', 'w' );
		fputcsv( $handle, array_keys( $data['0'] ), ';' );
		foreach ( $data as $value ) {
			fputcsv( $handle, $value, ';' );
		}
		fclose( $handle );
		ob_flush();
		die;
	}

	/**
	 * Import Data file
	 */
	public static function import() {
		check_ajax_referer('bookit_import', 'nonce');

		if ( !current_user_can( 'manage_options' ) ) {
			return false;
		}

		if ( ! ( is_array( $_POST ) && is_array( $_FILES ) && defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			wp_send_json_error( [
				'errors' => ['import_json' => __('No data')], 'message' => __('Error occurred!', 'bookit')]);
		}

		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}

		if ( !empty( $_FILES['file'] ) ) {
			$file_info  = wp_handle_upload( $_FILES['file'], [ 'test_form' => false ] );
			$data       = self::bookit_read_file( $file_info );
			wp_send_json( $data, 200 );
		} else {
			wp_send_json_error([
				'errors' => ['import_json' => __('File is empty')], 'message' => __('Error occurred!', 'bookit')
			]);
		}
	}

	/**
	 * Run Demo Import
	 */
	public static function demo_import_apply() {
		check_ajax_referer('bookit_import', 'nonce');

		if ( !current_user_can( 'manage_options' ) ) {
			return false;
		}

		$file = BOOKIT_PATH . '/demo-sample/bookit.txt';

		if ( file_exists( $file ) ) {
			$data = self::bookit_read_file( $file, true );
			wp_send_json( $data, 200 );
		} else {
			wp_send_json_error([
				'errors' => ['demo_import' => __('File not find')], 'message' => __('Error occurred!', 'bookit')
			]);
		}
	}

	/**
	 * Read Import File
	 * @param $file
	 * @param bool $demo
	 *
	 * @return array
	 */
	public static function bookit_read_file( $file, $demo = false ) {
		WP_Filesystem();
		global $wp_filesystem;
		$result    = false;
		$file_data = [];

		if ( $wp_filesystem->exists( $file['file'] ) || $demo == true ) {
			$file_url     = ( $demo ) ? $file : $file['file'];
			$fileContents = file_get_contents( $file_url );
			$json         = json_decode( $fileContents, true );
			if ( $json !== null ) {
				$result  = true;
				update_option( 'bookit_import_file', $file_url );
				$file_data['settings']   = 0;
				$file_data['categories'] = count( $json['Categories'] );
				$file_data['service']    = count( $json['Service'] );
				$file_data['staff']      = count( $json['Staff'] );
			}
		}

		return [
			'info'      => $file_data,
			'success'   => $result
		];
	}

	/**
	 * Rum Demo Import
	 */
	public static function demo_import_run() {
		check_ajax_referer('bookit_import', 'nonce');

		if ( !current_user_can( 'manage_options' ) ) {
			return false;
		}

		$key            = absint( intval( $_POST['key'] ) );
		$file_url       = get_option( 'bookit_import_file' );
		$fileContents   = file_get_contents( $file_url );
		$json           = json_decode( $fileContents, true );
		$step           = sanitize_text_field($_POST['step']);

		if ( $json !== null ) {
			$data = self::import_data( $json, $step, $key );
		} else {
			$data = [ 'success' => false ];
		}

		wp_send_json( $data, 200 );
	}


	/**
	 * Import Data
	 *
	 * @param $json
	 * @param $step
	 * @param $key
	 *
	 * @return array
	 */
	public static function import_data( $json, $step, $key ) {
		$imported_categories    = [];
		$imported_services      = [];

		if ( $step == 'categories' && $json['Categories'] && ! empty( $json['Categories'][ $key ] ) ) {
			$cat_key           = $json['Categories'][ $key ]['id'];
			$existing_category = Categories::get( 'name', $json['Categories'][ $key ]['name'] );

			unset( $json['Categories'][ $key ]['id'] );

			if ( empty( $existing_category ) ) {
				Categories::insert( $json['Categories'][ $key ] );
			}

			$cat_id                          = ( empty( $existing_category ) ) ? Categories::insert_id() : $existing_category->id;
			$imported_categories[ $cat_key ] = $cat_id;

			if ( $key == 0 ) {
				set_transient( 'bookit_categories', json_encode( $imported_categories ), 1000 );
			}

			if ( $key > 0 && get_transient( 'bookit_categories' ) ) {
				$all_imported_categories             = json_decode( get_transient( 'bookit_categories' ), true );
				$all_imported_categories[ $cat_key ] = $cat_id;
				set_transient( 'bookit_categories', json_encode( $all_imported_categories ), 1000 );
			}
		} else if ( $step == 'service' && $json['Service'] && ! empty( $json['Service'][ $key ] ) ) {
			$imported_categories                    = ( get_transient( 'bookit_categories' ) ) ? json_decode( get_transient( 'bookit_categories' ), true ) : [];
			$json['Service'][ $key ]['category_id'] = $imported_categories[ $json['Service'][ $key ]['category_id'] ];
			$service_id                             = $json['Service'][ $key ]['id'];

			unset( $json['Service'][ $key ]['id'] );
			unset( $json['Service'][ $key ]['icon_id'] );

			Services::insert( $json['Service'][ $key ] );
			$imported_services[ $service_id ] = Services::insert_id();

			if ( $key == 0 ) {
				set_transient( 'bookit_services', json_encode( $imported_services ), 1000 );
			}

			if ( $key > 0 && get_transient( 'bookit_services' ) ) {
				$all_imported_services                = json_decode( get_transient( 'bookit_services' ), true );
				$all_imported_services[ $service_id ] = Services::insert_id();
				set_transient( 'bookit_services', json_encode( $all_imported_services ), 1000 );
			}
		} else if ( $step == 'staff' && $json['Staff'] ) {
			unset( $json['Staff'][ $key ]['id'] );
			$staff_imported_services = [];

			if ( $json['Staff'][ $key ] ) {
				unset( $json['Staff'][ $key ]['id'] );

				if ( get_transient( 'bookit_services' ) ) {
					$staff_imported_services = json_decode( get_transient( 'bookit_services' ), true );
				}

				self::staff_import( $json['Staff'][ $key ], $staff_imported_services );
			}
		}

		return [ 'key' => $key += 1, 'success' => true ];
	}

	/**
	 * Import Staff
	 *
	 * @param $data
	 * @param $imported_services
	 *
	 * @return mixed
	 */
	public static function staff_import( $data, $imported_services ) {
		$staff_services = json_decode( stripslashes( $data['staff_services'] ) );
		$working_hours  = json_decode( stripslashes( $data['working_hours'] ) );

		unset( $data['staff_services'] );
		unset( $data['working_hours'] );

		Staff::insert( $data );
		$id = Staff::insert_id();

		foreach ( $working_hours as $working_hour ) {
			$insert = [
				'staff_id'   => $id,
				'weekday'    => $working_hour->weekday,
				'start_time' => $working_hour->start_time,
				'end_time'   => $working_hour->end_time,
				'break_from' => $working_hour->break_from,
				'break_to'   => $working_hour->break_to
			];
			Staff_Working_Hours::insert( $insert );
		}

		foreach ( $staff_services as $staff_service ) {
			$insert = [
				'staff_id'   => $id,
				'service_id' => ( $imported_services[ $staff_service->id ] ) ? $imported_services[ $staff_service->id ] : null,
				'price'         => number_format((float)$staff_service->price, 2, '.', ''),
			];
			Staff_Services::insert( $insert );
		}

		return $data;
	}
}