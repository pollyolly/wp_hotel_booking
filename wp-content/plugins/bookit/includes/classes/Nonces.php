<?php

namespace Bookit\Classes;

class Nonces {

	/**
	 * Frontend Nonces
	 * @return array
	 */
	public static function get_frontend_nonces() {
		$list = [
			'bookit_book_appointment',
			'bookit_month_appointments',
			'bookit_day_appointments',
			'bookit_admin_day_appointments',
			'bookit_admin_month_appointments',
			'bookit_appointment_status',
			'bookit_is_free_appointment'
		];

		$nonces = [];

		foreach ( $list as $slug ) {
			$nonces[$slug] = wp_create_nonce($slug);
		}

		return $nonces;
	}

	/**
	 * Admin Nonces
	 * @return array
	 */
	public static function get_admin_nonces() {
		$list = [
			'bookit_save_item',
			'bookit_delete_item',
			'bookit_add_appointment',
			'bookit_edit_appointment',
			'bookit_day_appointments',
			'bookit_admin_day_appointments',
			'bookit_get_appointment',
			'bookit_get_appointments',
			'bookit_get_calendar_appointments',
			'bookit_get_appointment_form_data',
			'bookit_appointment_status',
			'bookit_save_category',
			'bookit_delete_category',
			'bookit_get_customers',
			'bookit_save_settings',
			'bookit_export',
			'bookit_import',
			'bookit_load_icon',
			'bookit_get_category_assosiated_total_data',
			'bookit_get_customer_assosiated_total_data',
			'bookit_get_service_assosiated_total_data',
			'bookit_get_staff_assosiated_total_data',
			'bookit_add_feedback',
		];

		$nonces = [];

		foreach ( $list as $slug ) {
			$nonces[$slug] = wp_create_nonce($slug);
		}

		return $nonces;
	}

}