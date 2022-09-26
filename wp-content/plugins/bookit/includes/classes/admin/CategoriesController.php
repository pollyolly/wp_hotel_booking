<?php

namespace Bookit\Classes\Admin;

use Bookit\Classes\Database\Appointments;
use Bookit\Classes\Database\Categories;
use Bookit\Classes\Database\Services;
use Bookit\Helpers\CleanHelper;

class CategoriesController extends DashboardController {

	private static function getCleanRules() {
		return [ 'id' => [ 'type' => 'intval'], 'name' => [ 'type' => 'strval' ]];
	}

	/**
	 * Validate post data
	 */
	public static function validate( $data ) {
		$errors = [];

		if ( !$data['name'] || ( $data['name'] && strlen($data['name']) < 3 || strlen($data['name']) > 25) ) {
			$errors['category_name'] = __("Category Name can't be empty and must be between 3 and 25 characters long");
			wp_send_json_error( ['errors' => $errors, 'message' => __('Error occurred!', 'bookit')] );
		}

		if ( !isset( $data['id'] ) ) {
			$exist_category = Categories::get( 'name', $data['name'] );
			if ( $exist_category !== null ){
				$errors['category_name'] = __("Category with such name already exist");
				wp_send_json_error( ['errors' => $errors, 'message' => __('Error occurred!', 'bookit')] );
			}
		}
	}

	/**
	 * Save Category
	 */
	public static function save() {
		check_ajax_referer('bookit_save_category', 'nonce');

		if ( !current_user_can( 'manage_options' ) ) {
			return false;
		}

		$data = CleanHelper::cleanData($_POST, self::getCleanRules());
		self::validate( $data );

		if ( ! empty( $data ) ) {
			if ( ! empty( $data['id'] ) ) {
				Categories::update( $data, [ 'id' => $data['id'] ] );
			} else {
				Categories::insert( $data );
				$data['id'] = Categories::insert_id();
			}

			do_action( 'bookit_category_saved', $data['id'] );

			wp_send_json_success( [ 'id' => $data['id'], 'message' => __( 'Category Saved!', 'bookit' )] );
		}

		wp_send_json_error( [ 'message' => __('Error occurred!', 'bookit') ] );
	}

	/** Get Categories Assosiated data by id **/
	public static function get_assosiated_total_data_by_id() {
		check_ajax_referer('bookit_get_category_assosiated_total_data', 'nonce');

		if ( !current_user_can( 'manage_options' ) ) {
			return false;
		}

		$data = CleanHelper::cleanData($_POST, self::getCleanRules());

		if ( empty( $data['id'] ) ) {
			wp_send_json_error( [ 'message' => __('Error occurred!', 'bookit') ] );
		}

		$total = Categories::get_category_total_assosiated_data($data['id']);

		$response = ['total' => (array)$total];
		wp_send_json_success( $response );
	}

	/**
	 * Delete the Category
	 */
	public static function delete() {
		check_ajax_referer('bookit_delete_item', 'nonce');

		if ( !current_user_can( 'manage_options' ) ) {
			return false;
		}

		$data = CleanHelper::cleanData($_GET, self::getCleanRules());

		if ( isset( $data['id'] ) ) {
			$id = $data['id'];

			Categories::deleteCategory( $id );

			do_action( 'bookit_category_deleted', $id );

			wp_send_json_success( [ 'message' => __('Category Deleted!', 'bookit') ] );
		}

		wp_send_json_error( [ 'message' => __('Error occurred!', 'bookit') ] );
	}
}