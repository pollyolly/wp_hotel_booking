<?php

namespace Bookit\Classes\Admin;

use Bookit\Classes\Database\Appointments;
use Bookit\Classes\Database\Services;
use Bookit\Classes\Database\Categories;
use Bookit\Classes\Template;
use Bookit\Helpers\CleanHelper;

class ServicesController extends DashboardController {

	private static function getCleanRules() {
		return [
			'id'            => [ 'type' => 'intval'],
			'limit'         => [ 'type' => 'intval' ],
			'offset'        => [ 'type' => 'intval' ],
			'price'         => [ 'function' => ['custom' => true, 'name' => 'custom_sanitize_price'] ],
			'category_id'   => [ 'type' => 'intval' ],
			'title'         => [ 'type' => 'strval' ],
		];
	}
	/**
	 * Display Rendered Template
	 * @return bool|string
	 */
	public static function render() {
		$services   = Services::get_all();
		$categories = Categories::get_all();

		if ( ! empty( $services ) ) {
			foreach ( $services as &$service ) {
				$service['unset']['media_url'] = ( ! empty( $service['icon_id'] ) ) ? wp_get_attachment_url($service['icon_id']) : null;
			}
		}

		self::enqueue_styles_scripts();
		wp_enqueue_media();

		return Template::load_template(
			'dashboard/bookit-services',
			[ 'services' => $services, 'categories' => $categories ],
			true
		);
	}

	/**
	 * Get Services with Pagination
	 */
	public static function get_services() {

		$data = CleanHelper::cleanData($_GET, self::getCleanRules());
		if ( ! empty( $data['limit'] ) ) {
			$response['services']   = Services::get_paged( $data['limit'], $data['offset'] );
			$response['categories'] = Categories::get_all();
			$response['total']      = Services::get_count();

			wp_send_json_success( $response );
		}

		wp_send_json_error( [ 'message' => __('Error occurred!', 'bookit') ] );
	}

	/** Get Service Assosiated data by id **/
	public static function get_assosiated_total_data_by_id() {
		check_ajax_referer('bookit_get_service_assosiated_total_data', 'nonce');

		if ( !current_user_can( 'manage_options' ) ) {
			return false;
		}

		$data = CleanHelper::cleanData($_POST, self::getCleanRules());

		if ( empty( $data['id'] ) ) {
			wp_send_json_error( [ 'message' => __('Error occurred!', 'bookit') ] );
		}

		$staff          = Services::get_service_total_staff($data['id']);
		$appointments   = Appointments::get_total_active_assosiated_appointments( $data['id'] );

		$response = ['total' => ['staff' => $staff, 'appointments' => $appointments]];
		wp_send_json_success( $response );
	}

	/**
	 * Validate post fields
	 */
	public static function validate( $data ) {
		$errors = [];

		if ($data['price'] == null || $data['price'] && preg_match('/^\d+(\.\d{2})?$/', $data['price']) == '0') {
			$errors['price'] = __('Price must be a number');
		}

		if (!$data['category_id']) {
			$errors['category_id'] = __('Category is required');
		}

		if ($data['title']) {
			$data['title'] = preg_replace('/\s\s+/', ' ', $data['title']);
			if (strlen($data['title']) < 3){
				$errors['title'] = __('Title must be greater than 3 characters');
			}
		}else{
			$errors['title'] = __('Title is required');
		}

		if ( count($errors ) > 0 ) {
			wp_send_json_error( ['errors' => $errors, 'message' => __('Error occurred!', 'bookit')] );
		}
	}

	/**
	 * Save Service
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
				Services::update( $data, [ 'id' => $data['id'] ] );
			} else {
				Services::insert( $data );
				$data['id'] = Services::insert_id();
			}

			do_action( 'bookit_service_saved', $data['id'] );

			wp_send_json_success( [ 'id' => $data['id'], 'message' => __( 'Service Saved!', 'bookit' )] );
		}

		wp_send_json_error( [ 'message' => __('Error occurred!', 'bookit') ] );
	}

	/**
	 * Delete the Service
	 */
	public static function delete() {
		check_ajax_referer('bookit_delete_item', 'nonce');

		if ( !current_user_can( 'manage_options' ) ) {
			return false;
		}

		$data = CleanHelper::cleanData($_GET, self::getCleanRules());

		if ( isset( $data['id'] ) ) {
			$id = $data['id'];

			Services::deleteService( $id );
			do_action( 'bookit_service_deleted', $id );

			wp_send_json_success( [ 'message' => __('Service Deleted!', 'bookit') ] );
		}

		wp_send_json_error( [ 'message' => __('Error occurred!', 'bookit') ] );
	}
}