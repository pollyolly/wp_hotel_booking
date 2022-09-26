<?php

namespace Bookit\Classes;

use Bookit\Classes\Admin\SettingsController;
use Bookit\Classes\Base\User;
use Bookit\Classes\Database\Customers;

class CustomerController {

	/**
	 * Appointment Customer
	 * @param $data
	 * @return object customer
	 */
	public static function get_customer( $data ) {
		
		if ( ! empty( $data['user_id'] ) ) {
			$id = Customers::get('wp_user_id', $data['user_id'])->id;
		} else {
			$id = Customers::get('email', $data['email'])->id;
		}

		$settings = SettingsController::get_settings();
		if ( $settings['booking_type'] == 'registered' && !is_user_logged_in() ) {
			$data['role']    = User::$customer_role;
			$data['user_id'] = Customers::save_or_get_wp_user($data);
			/** Authorize wp User */
			wp_clear_auth_cookie();
			wp_set_current_user ( $data['user_id'] );
			wp_set_auth_cookie  ( $data['user_id'] );
		}

		if ( !$id ) {
			$id = self::save($data);
		}

		self::maybe_update($id, ['wp_user_id' => $data['user_id'], 'phone' => $data['phone']] );
		return Customers::get('id', $id);
	}

	/** Save Customer **/
	private static function save($data) {
		$insert = [
			'full_name'     => $data['full_name'],
			'wp_user_id'    => $data['user_id'],
			'email'         => $data['email'],
			'phone'         => $data['phone'],
		];
		Customers::insert( $insert );
		return  Customers::insert_id();
	}

	/** Update Customer if appear new data **/
	private static function maybe_update($id, $data) {
		$customer = Customers::get('id', $id);

		if ($customer->wp_user_id || $customer->wp_user_id == $data['wp_user_id']){ unset($data['wp_user_id']); }
		if ($customer->phone){ unset($data['phone']); }

		if ($data){
			Customers::update( $data, [ 'id' => $id ] );
		}
	}
}