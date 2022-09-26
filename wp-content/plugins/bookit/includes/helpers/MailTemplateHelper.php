<?php

namespace Bookit\Helpers;

/**
 * Bookit Mail Template Helper
 */


class MailTemplateHelper {

	/**
	 *  Default templates data for base settings
	 * @var array[]
	 */
	protected static $_default_templates = [
		'appointment_created_customer'  => [
			'enabled'   => true,
			'subject'   => 'Your Appointment Request #[appointment_id] successfully sent!',
			'body'      => 'Hi, [customer_name].' . PHP_EOL . 'Your Appointment Request successfully sent!' . PHP_EOL
		               . 'Service: [service_title]' . PHP_EOL . 'Staff: [staff_name]' . PHP_EOL . 'Staff phone: [staff_phone]' . PHP_EOL
		               . 'Start time: [start_time]' . PHP_EOL . 'Appointment day: [appointment_day]' . PHP_EOL
		               . 'Payment Method: [payment_method]' . PHP_EOL . 'Payment Status: [payment_status]' . PHP_EOL
		               . 'Total: [total]' . PHP_EOL . 'Status: [status]',
			],
		'appointment_created_admin'     => [
			'enabled'   => true,
			'to'        => '[admin],[staff]',
			'subject'   => 'New Appointment Request by [customer_name] - #[appointment_id]!',
			'body'      => 'Name: [customer_name]' . PHP_EOL . 'Email: [customer_email]' . PHP_EOL . 'Phone: [customer_phone]' . PHP_EOL
			                . 'Service: [service_title]' . PHP_EOL . 'Staff: [staff_name]' . PHP_EOL . 'Staff phone: [staff_phone]' . PHP_EOL
			                . 'Start time: [start_time]' . PHP_EOL . 'Appointment day: [appointment_day]' . PHP_EOL
			                . 'Payment Method: [payment_method]' . PHP_EOL . 'Payment Status: [payment_status]' . PHP_EOL
			                . 'Total: [total]' . PHP_EOL . 'Status: [status]',
			],
		'appointment_updated_customer'  => [
			'enabled'   => true,
			'subject'   => 'Your Appointment #[appointment_id] has been modified!',
			'body'      => 'Hi, [customer_name].' . PHP_EOL . 'Your Appointment Request successfully modified!' . PHP_EOL
	                        . 'Service: [service_title]' . PHP_EOL . 'Staff: [staff_name]' . PHP_EOL . 'Staff phone: [staff_phone]' . PHP_EOL
	                        . 'Start time: [start_time]' . PHP_EOL . 'Appointment day: [appointment_day]' . PHP_EOL
	                        . 'Payment Method: [payment_method]' . PHP_EOL . 'Payment Status: [payment_status]' . PHP_EOL
	                        . 'Total: [total]' . PHP_EOL . 'Status: [status]',
			],
		'appointment_updated_admin'     => [
			'enabled'   => true,
			'to'        => '[admin],[staff]',
			'subject'   => 'Appointment #[appointment_id] modified!',
			'body'      => 'Name: [customer_name]' . PHP_EOL . 'Email: [customer_email]' . PHP_EOL . 'Phone: [customer_phone]' . PHP_EOL
                            . 'Service: [service_title]' . PHP_EOL . 'Staff: [staff_name]' . PHP_EOL . 'Staff phone: [staff_phone]' . PHP_EOL
                            . 'Start time: [start_time]' . PHP_EOL . 'Appointment day: [appointment_day]' . PHP_EOL
                            . 'Payment Method: [payment_method]' . PHP_EOL . 'Payment Status: [payment_status]' . PHP_EOL
                            . 'Total: [total]' . PHP_EOL . 'Status: [status]',
			],
		'payment_complete_customer'     => [
			'enabled'   => true,
			'subject'   => 'Your Payment for Appointment #[appointment_id] received!',
			'body'      => 'Hi, [customer_name].' . PHP_EOL . 'Your Payment from [payment_method] was received!' . PHP_EOL
                            . 'Service: [service_title]' . PHP_EOL . 'Staff: [staff_name]' . PHP_EOL . 'Staff phone: [staff_phone]' . PHP_EOL
                            . 'Start time: [start_time]' . PHP_EOL . 'Appointment day: [appointment_day]' . PHP_EOL
                            . 'Payment Method: [payment_method]' . PHP_EOL . 'Payment Status: [payment_status]' . PHP_EOL
                            . 'Total: [total]' . PHP_EOL . 'Status: [status]',
			],
		'payment_complete_admin'        => [
			'enabled'   => true,
			'to'        => '[admin]',
			'subject'   => 'You received Payment from [customer_name] for Appointment #[appointment_id]!',
			'body'      => 'You received Payment from [payment_method]!' . PHP_EOL
                            . 'Name: [customer_name]' . PHP_EOL . 'Email: [customer_email]' . PHP_EOL . 'Phone: [customer_phone]' . PHP_EOL
                            . 'Service: [service_title]' . PHP_EOL . 'Staff: [staff_name]' . PHP_EOL . 'Staff phone: [staff_phone]' . PHP_EOL
                            . 'Start time: [start_time]' . PHP_EOL . 'Appointment day: [appointment_day]' . PHP_EOL
                            . 'Payment Method: [payment_method]' . PHP_EOL . 'Payment Status: [payment_status]' . PHP_EOL
                            . 'Total: [total]' . PHP_EOL . 'Status: [status]',
			],
		'appointment_status_changed'    => [
			'enabled'   => true,
			'subject'   => 'Your Appointment Request #[appointment_id] [status]!',
			'body'      => 'Hi, [customer_name].' . PHP_EOL . 'Your Appointment #[appointment_id] [status]!' . PHP_EOL
                            . 'Service: [service_title]' . PHP_EOL . 'Staff: [staff_name]' . PHP_EOL . 'Staff phone: [staff_phone]' . PHP_EOL
                            . 'Start time: [start_time]' . PHP_EOL . 'Appointment day: [appointment_day]' . PHP_EOL
                            . 'Payment Method: [payment_method]' . PHP_EOL . 'Payment Status: [payment_status]' . PHP_EOL
                            . 'Total: [total]' . PHP_EOL . 'Status: [status]',
			],
		'appointment_status_changed_admin'    => [
			'enabled'   => true,
			'to'        => '[admin],[staff]',
			'subject'   => 'Your Appointment Request #[appointment_id] [status]!',
			'body'      => 'Name: [customer_name]' . PHP_EOL . 'Email: [customer_email]' . PHP_EOL . 'Phone: [customer_phone]' . PHP_EOL
			               . 'Service: [service_title]' . PHP_EOL . 'Staff: [staff_name]' . PHP_EOL . 'Staff phone: [staff_phone]' . PHP_EOL
			               . 'Start time: [start_time]' . PHP_EOL . 'Appointment day: [appointment_day]' . PHP_EOL
			               . 'Payment Method: [payment_method]' . PHP_EOL . 'Payment Status: [payment_status]' . PHP_EOL
			               . 'Total: [total]' . PHP_EOL . 'Status: [status]',
		],
		'appointment_deleted_customer'  => [
			'enabled'   => true,
			'subject'   => 'Your Appointment #[appointment_id] has been deleted!',
			'body'      => 'Hi, [customer_name].' . PHP_EOL . 'Your Appointment Request was deleted!' . PHP_EOL
                            . 'Reason: [reason]' . PHP_EOL
                            . 'Service: [service_title]' . PHP_EOL . 'Staff: [staff_name]' . PHP_EOL . 'Staff phone: [staff_phone]' . PHP_EOL
                            . 'Start time: [start_time]' . PHP_EOL . 'Appointment day: [appointment_day]' . PHP_EOL
                            . 'Payment Method: [payment_method]' . PHP_EOL . 'Payment Status: [payment_status]' . PHP_EOL
                            . 'Total: [total]' . PHP_EOL . 'Status: [status]',
			],
		'appointment_deleted_staff'     => [
			'enabled'   => true,
			'subject'   => 'Appointment #[appointment_id] deleted!',
			'body'      => 'Name: [customer_name]' . PHP_EOL . 'Email: [customer_email]' . PHP_EOL . 'Phone: [customer_phone]' . PHP_EOL
                            . 'Reason: [reason]' . PHP_EOL
                            . 'Service: [service_title]' . PHP_EOL . 'Staff: [staff_name]' . PHP_EOL . 'Staff phone: [staff_phone]' . PHP_EOL
                            . 'Start time: [start_time]' . PHP_EOL . 'Appointment day: [appointment_day]' . PHP_EOL
                            . 'Payment Method: [payment_method]' . PHP_EOL . 'Payment Status: [payment_status]' . PHP_EOL
                            . 'Total: [total]' . PHP_EOL . 'Status: [status]',
			],
		];

	/*
	 * Get template by name (key)
	 */
	public static function getTemplatesByName( string $name){

		if ( ! $name || ! is_array( self::$_default_templates[$name] ) ) {
			return [];
		}

		return self::$_default_templates[$name];
	}

	/*
	 * Get base template list
	 */
	public static function getTemplates() {
		return self::$_default_templates;
	}


	/** add email templates to wpml single strings */
	public static function registerTemplateDataToWPMLStrings() {
		if ( has_action('wpml_register_single_string') ) {
			$emailTemplates  = get_option_by_path( 'bookit_settings.emails', (object) [] );
			foreach ( $emailTemplates as $templateTitle => $template ) {
				do_action('wpml_register_single_string', 'bookit', $templateTitle . '_subject', $template['subject']);
				do_action('wpml_register_single_string', 'bookit', $templateTitle . '_body', $template['body']);
			}
		}
	}
}