<?php

namespace Bookit\Classes\Vendor;

use Bookit\Classes\Admin\SettingsController;
use Bookit\Classes\Base\User;
use Bookit\Classes\Customization;
use Bookit\Classes\Database\Appointments;
use Bookit\Classes\Database\Coupons;
use Bookit\Classes\Database\Discounts;
use Bookit\Classes\Database\Services;
use Bookit\Classes\Database\Payments;
use Bookit\Classes\Database\Staff;
use Bookit\Helpers\MailTemplateHelper;

abstract class BookitUpdateCallbacks {

	/** 2.0.1  */
	public static function add_services_icon() {
		global $wpdb;
		/**
		 * Add ICON column to Services table.
		 */
		if ( ! $wpdb->get_var( sprintf( "SHOW COLUMNS FROM `%s` LIKE 'icon_id';", Services::_table() ) ) ) {
			$wpdb->query( sprintf( "ALTER TABLE `%s` ADD `icon_id` INT NULL;", Services::_table() ) );
		}
	}

	/**
	 * 2.0.9
	 * Add currency default value to main settings.
	 */
	public static function add_currency() {
		$currency = get_option_by_path('bookit_settings.currency');

		if ( !$currency ) {
			$settings = SettingsController::get_settings();
			$settings['currency'] = SettingsController::$default_currency;
			SettingsController::save_settings($settings);
		}
	}

	/** 2.1.0 */
	public static function  add_appointment_deleted_email_templates() {

		$appointment_deleted_customer   = get_option_by_path('bookit_settings.emails.appointment_deleted_customer');
		$appointment_deleted_staff      = get_option_by_path('bookit_settings.emails.appointment_deleted_staff');

		$settings = SettingsController::get_settings();
		if ( !$appointment_deleted_customer ) {
			$settings['emails']['appointment_deleted_customer']
				= MailTemplateHelper::getTemplatesByName('appointment_deleted_customer');
		}

		if ( !$appointment_deleted_staff ) {
			$settings['emails']['appointment_deleted_staff']
				= MailTemplateHelper::getTemplatesByName('appointment_deleted_staff');
		}

		SettingsController::save_settings($settings);
	}

	/**
	 * 2.1.0
	 * Add notes column to Appointment table.
	 */
	public static function add_appointment_notes() {
		global $wpdb;
		if ( ! $wpdb->get_var( sprintf( "SHOW COLUMNS FROM `%s` LIKE 'notes';", Appointments::_table() ) ) ) {
			$wpdb->query( sprintf( "ALTER TABLE `%s` ADD `notes` longtext DEFAULT NULL;", Appointments::_table() ) );
		}
	}

	/** 2.1.3 */
	public static function add_time_slot_duration_setting() {
		$time_slot_duration = get_option_by_path('bookit_settings.time_slot_duration');

		if ( !$time_slot_duration ) {
			$settings = SettingsController::get_settings();
			$settings['time_slot_duration'] = SettingsController::$time_slot_default_duration;
			SettingsController::save_settings($settings);
		}
	}

	/**
	 * 2.1.3
	 * Add Payments table.
	 */
	public static function add_payment_table() {
		Payments::create_table();
	}

	/**
	 * 2.1.3
	 * Add Discounts table.
	 */
	public static function add_discount_table() {
		Discounts::create_table();
	}

	/**
	 * 2.1.3
	 * Add Coupons table.
	 */
	public static function add_coupon_table() {
		Coupons::create_table();
	}

	/**
	 * 2.1.3
	 * Update Appointment table
	 * add created_at, updated_at, created_from fields
	 */
	public static function add_appointment_table_fields() {
		global $wpdb;
		if ( ! $wpdb->get_var( sprintf( "SHOW COLUMNS FROM `%s` LIKE 'created_from';", Appointments::_table() ) ) ) {
			$wpdb->query( sprintf( "ALTER TABLE `%s` ADD `created_from` ENUM('front', 'back') NOT NULL DEFAULT 'front';", Appointments::_table() ) );
		}

		if ( ! $wpdb->get_var( sprintf( "SHOW COLUMNS FROM `%s` LIKE 'created_at';", Appointments::_table() ) ) ) {
			$wpdb->query( sprintf( "ALTER TABLE `%s` ADD `created_at` DATETIME NOT NULL;", Appointments::_table() ) );
			$wpdb->query( sprintf( "ALTER TABLE `%s` ADD `updated_at` DATETIME NOT NULL;", Appointments::_table() ) );
		}
	}


	/**
	 * 2.1.3
	 * Update Appointment table, remove payment_method and payment_status
	 */
	public static function drop_appointment_table_payment_fields() {
		global $wpdb;
		if ( $wpdb->get_var( sprintf( "SHOW COLUMNS FROM `%s` LIKE 'payment_method';", Appointments::_table() ) ) ) {
			$wpdb->query( sprintf( "ALTER TABLE `%s` DROP  COLUMN `payment_method`;", Appointments::_table() ) );
		}

		if ( $wpdb->get_var( sprintf( "SHOW COLUMNS FROM `%s` LIKE 'payment_status';", Appointments::_table() ) ) ) {
			$wpdb->query( sprintf( "ALTER TABLE `%s` DROP  COLUMN `payment_status`;", Appointments::_table() ) );
		}
	}

	/** 2.1.3 */
	public static function refactor_exist_appointments() {
		global $wpdb;

		/** add new appointment fields before refactoring */
		self::add_appointment_table_fields();

		$appointments = Appointments::get_all();
		foreach( $appointments as $appointment ) {
			$exist = Payments::get('appointment_id', $appointment['id']);
			if ( $exist != null ){
				continue;
			}
			$created_at = date_i18n( 'Y-m-d H:i:s', $appointment['start_time'] );
			$payment    = [
				'appointment_id'    => $appointment['id'],
				'type'              => $appointment['payment_method'] ?: Payments::$defaultType,
				'status'            => $appointment['payment_status'] ?: Payments::$defaultStatus,
				'total'             => $appointment['price'],
				'created_at'        => $created_at,
				'updated_at'        => $created_at,
			];

			$wpdb->update( Appointments::_table(),
				['created_at' => $created_at, 'updated_at' => $created_at],
				['id' => $appointment['id']]
			);
			Payments::insert( $payment );
		}

		/** remove appointment payment fields after refactoring */
		self::drop_appointment_table_payment_fields();
	}

	/** 2.1.5 */
	public static function update_payment_type_enum_field() {
		global $wpdb;
		if ( $wpdb->get_var( sprintf( "SHOW COLUMNS FROM `%s` LIKE 'type';", Payments::_table() ) ) ) {
			$wpdb->query( sprintf(
				"ALTER TABLE `%s` MODIFY `type` ENUM('locally', 'paypal', 'stripe', 'woocommerce', 'free');",
				Payments::_table() )
			);
		}
	}

    /** 2.1.7 */
    public static function add_senders() {

        $sender_name  = get_option_by_path('bookit_settings.sender_name');
        $sender_email = get_option_by_path('bookit_settings.sender_email');

        if ( ! $sender_name && ! $sender_email ) {
            $settings                 = SettingsController::get_settings();
            $settings['sender_name']  = SettingsController::$default_sender_name;
            $settings['sender_email'] = SettingsController::$default_sender_email;
            SettingsController::save_settings($settings);
        }

    }

	/** 2.1.7 */
	public static function add_wp_user_to_staff() {
		global $wpdb;
		if ( ! $wpdb->get_var( sprintf( "SHOW COLUMNS FROM `%s` LIKE 'wp_user_id';", Staff::_table() ) ) ) {
			$wpdb->query( sprintf( "ALTER TABLE `%s` ADD `wp_user_id` BIGINT(20);", Staff::_table() ) );
		}
	}

	/** 2.1.7 */
	public static function add_bookit_user_roles_and_capabilitites() {
		User::addBookitUserRoles();
		User::addBookitCapabilitiesToWpRoles();
	}

	/** 2.1.7 */
	public static function add_clean_all_on_delete() {
		if ( !get_option_by_path('bookit_settings.clean_all_on_delete') ) {
			$settings = SettingsController::get_settings();
			$settings['clean_all_on_delete'] = false;
			SettingsController::save_settings($settings);
		}
	}

	/** 2.1.7 */
	public static function  add_appointment_status_changed_admin_template() {

		$template   = get_option_by_path('bookit_settings.emails.appointment_status_changed_admin');

		$settings = SettingsController::get_settings();
		if ( !$template ) {
			$settings['emails']['appointment_status_changed_admin']
				= MailTemplateHelper::getTemplatesByName('appointment_status_changed_admin');
		}
		SettingsController::save_settings($settings);
	}

	/** 2.1.7 */
	public static function replace_theme_to_calendar_view() {
		$is_theme = get_option_by_path('bookit_settings.theme');

		if ( $is_theme ) {
			$settings = SettingsController::get_settings();
			$settings['calendar_view'] = SettingsController::$default_view_type;
			unset($settings['theme']);

			SettingsController::save_settings($settings);
		}
	}

	/**
	 * 2.2.0
	 * Add calendar_view default value to main settings.
	 */
	public static function add_calendar_view_type_to_settings() {
		$calendar_view = get_option_by_path('bookit_settings.calendar_view');

		$settings = SettingsController::get_settings();

		if ( !$calendar_view ) {
			$settings['calendar_view'] = SettingsController::$default_view_type;
			SettingsController::save_settings($settings);
		}

		Customization::custom_colors( $settings, true );
	}

	/** 2.2.1 */
	public static function add_admin_notification_transient() {
		set_transient( 'stm_bookit_notice_setting', [ 'show_time' => DAY_IN_SECONDS * 3 + time(), 'step' => 0, 'prev_action' => '' ] );
	}
}