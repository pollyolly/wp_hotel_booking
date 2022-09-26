<?php

namespace Bookit\Classes\Vendor;

abstract class BookitUpdates {

	private static $updates = [
		'2.0.1' => ['add_services_icon'],
		'2.0.9' => ['add_currency'],
		'2.1.0' => ['add_appointment_deleted_email_templates', 'add_appointment_notes'],
		'2.1.3' => [
			'add_time_slot_duration_setting',
			'add_payment_table',
			'add_discount_table',
			'add_coupon_table',
			'refactor_exist_appointments',
		],
		'2.1.5' => [
			'update_payment_type_enum_field',
		],
		'2.1.7' => [
			'add_wp_user_to_staff',
			'add_clean_all_on_delete',
			'add_bookit_user_roles_and_capabilitites',
			'add_senders',
			'add_appointment_status_changed_admin_template',
			'replace_theme_to_calendar_view'
		],
		'2.2.0' => ['add_calendar_view_type_to_settings'],
		'2.2.1' => ['add_admin_notification_transient'],
	];

	/**
	 * Init Bookit Updates
	 */
	public static function init() {
		if ( version_compare( get_option('bookit_version'), BOOKIT_VERSION, '<' ) ) {
			self::update_version();
		}
	}

	/**
	 * Get All Updates
	 * @return array
	 */
	public static function get_updates() {
		return self::$updates;
	}

	/**
	 * Check If Needs Updates
	 * @return bool
	 */
	public static function needs_to_update() {
		$current_db_version = get_option('bookit_db_version');
		$update_versions    = array_keys( self::get_updates() );
		usort( $update_versions, 'version_compare' );

		return ! empty( $current_db_version ) && version_compare( $current_db_version, end( $update_versions ), '<' );
	}

	/**
	 * Run Needed Updates
	 */
	private static function maybe_update_db_version() {
		if ( self::needs_to_update() ) {
			$current_db_version = get_option('bookit_db_version');
			$updates            = self::get_updates();

			foreach ( $updates as $version => $callback_arr) {
				if ( version_compare( $current_db_version, $version, '<' ) ) {
					foreach ($callback_arr as $callback) {
						call_user_func( ["\\Bookit\\Classes\\Vendor\\BookitUpdateCallbacks", $callback] );
					}
				}
			}
		}

		update_option('bookit_db_version', BOOKIT_DB_VERSION, true);
	}

	/**
	 * Update Plugin Version
	 */
	public static function update_version() {
		update_option('bookit_version', BOOKIT_VERSION, true);
		self::maybe_update_db_version();
	}

}