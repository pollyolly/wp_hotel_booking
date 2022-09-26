<?php

namespace Bookit\Classes;

use Bookit\Classes\Admin\SettingsController;
use Bookit\Classes\Database\Appointments;
use Bookit\Classes\Database\Customers;
use Bookit\Classes\Database\Services;
use Bookit\Classes\Database\Staff;

class Notifications {

	static $fields_to_remove_if_price_is_zero = ['payment_status', 'payment_method', 'total'];
	/**
	 * Init Notifications
	 */
	public static function init() {
		add_action('bookit_appointment_created', [self::class, 'appointment_created'], 100, 1);
		add_action('bookit_appointment_updated', [self::class, 'appointment_updated'], 100, 1);
		add_action('bookit_payment_complete', [self::class, 'payment_complete'], 100, 1);
		add_action('bookit_appointment_status_changed', [self::class, 'appointment_status_changed'], 100, 1);
		add_action('bookit_appointment_deleted', [self::class, 'appointment_deleted'], 100, 3);
		add_filter('bookit_filter_email_data', [self::class, 'rewrite_email_data'], 10, 1);

		add_action('bookit_appointment_created', [self::class, 'appointment_created_set_option'], 100, 1);
		add_filter('stm_admin_notice_rate_bookit_single', [self::class, 'stm_admin_notice_rate_bookit_single'], 10, 1);
	}

	/**
	 * Mail Content Type Filter
	 * @return string
	 */
	public static function mail_content_type() {
		return 'text/plain; charset=UTF-8';
	}

	public function check_sender_name( $original_name ) {
		$sender_name = get_option_by_path('bookit_settings.sender_name');

		return !empty($sender_name) ? $sender_name : $original_name;
	}

	public function check_sender_email( $original_email ) {
		$sender_email = get_option_by_path('bookit_settings.sender_email');

		return !empty($sender_email) ? $sender_email : $original_email;
	}


	/**
	 * Sent Email
	 * @param $to
	 * @param $subject
	 * @param $body
	 * @param $vars
	 * @param $template
	 */
	public static function sent_mail( $to, $subject, $body, $vars = [], $template = '' ) {

		add_filter('wp_mail_content_type', [self::class, 'mail_content_type']);
		add_filter('wp_mail_from_name', [self::class, 'check_sender_name'], 10, 1);
		add_filter('wp_mail_from', [self::class, 'check_sender_email'], 10, 1);

		$data = apply_filters('bookit_filter_email_data', [
			'to'        => $to,
			'subject'   => $subject,
			'body'      => $body,
			'vars'      => $vars,
			'template'  => $template
		]);

		wp_mail($data['to'], $data['subject'], $data['body']);

		remove_filter('wp_mail_content_type', [self::class, 'mail_content_type']);
	}

	/**
	 * Appointment Created
	 * @param $appointment_id
	 */
	public static function appointment_created( $appointment_id ) {
		$settings   = SettingsController::get_settings();
		$vars       = self::get_email_variables($appointment_id);

		if ( ! empty( $settings['emails']['appointment_created_customer']['enabled'] ) ) {
			self::sent_mail($vars['[customer]'], '', '', $vars, 'appointment_created_customer');
		}

		if ( $settings['emails']['appointment_created_admin']['enabled']
		     && strpos($settings['emails']['appointment_created_admin']['to'], '[admin]') !== false ) {
			self::sent_mail($vars['[admin]'], '', '', $vars, 'appointment_created_admin');
		}

		if ( $settings['emails']['appointment_created_admin']['enabled']
		     && strpos($settings['emails']['appointment_created_admin']['to'], '[staff]') !== false ) {
			self::sent_mail($vars['[staff]'], '', '', $vars, 'appointment_created_admin');
		}
	}

	/**
	 * Appointment Updated
	 * @param $appointment_id
	 */
	public static function appointment_updated( $appointment_id ) {
		$settings   = SettingsController::get_settings();
		$vars       = self::get_email_variables($appointment_id);

		if ( ! empty( $settings['emails']['appointment_updated_customer']['enabled'] ) ) {
			self::sent_mail($vars['[customer]'], '', '', $vars, 'appointment_updated_customer');
		}

		if ( $settings['emails']['appointment_updated_admin']['enabled']
		     && strpos($settings['emails']['appointment_updated_admin']['to'], '[admin]') !== false ) {
			self::sent_mail($vars['[admin]'], '', '', $vars, 'appointment_updated_admin');
		}

		if ( $settings['emails']['appointment_updated_admin']['enabled']
		     && strpos($settings['emails']['appointment_updated_admin']['to'], '[staff]') !== false ) {
			self::sent_mail($vars['[staff]'], '', '', $vars, 'appointment_updated_admin');
		}
	}

	/**
	 * Payment Complete Notification
	 * @param $appointment_id
	 */
	public static function payment_complete( $appointment_id ) {
		$settings   = SettingsController::get_settings();
		$vars       = self::get_email_variables($appointment_id);

		if ( ! empty( $settings['emails']['payment_complete_customer']['enabled'] ) ) {
			self::sent_mail($vars['[customer]'], '', '', $vars, 'payment_complete_customer');
		}

		if ( $settings['emails']['payment_complete_admin']['enabled']
		     && strpos($settings['emails']['payment_complete_admin']['to'], '[admin]') !== false ) {
			self::sent_mail($vars['[admin]'], '', '', $vars, 'payment_complete_admin');
		}

		if ( $settings['emails']['payment_complete_admin']['enabled']
		     && strpos($settings['emails']['payment_complete_admin']['to'], '[staff]') !== false ) {
			self::sent_mail($vars['[staff]'], '', '', $vars, 'payment_complete_admin');
		}
	}

	/**
	 * Appointment Status Changed Notification
	 * @param $appointment_id
	 */
	public static function appointment_status_changed( $appointment_id ) {
		$settings   = SettingsController::get_settings();
		$vars       = self::get_email_variables($appointment_id);

		if ( ! empty( $settings['emails']['appointment_status_changed']['enabled'] ) ) {
			self::sent_mail($vars['[customer]'], '', '', $vars, 'appointment_status_changed');
		}

		if ( $settings['emails']['appointment_status_changed_admin']['enabled']
		     && strpos($settings['emails']['appointment_status_changed_admin']['to'], '[admin]') !== false ) {
			self::sent_mail($vars['[admin]'], '', '', $vars, 'appointment_status_changed_admin');
		}

		if ( $settings['emails']['appointment_status_changed_admin']['enabled']
		     && strpos($settings['emails']['appointment_status_changed_admin']['to'], '[staff]') !== false ) {
			self::sent_mail($vars['[staff]'], '', '', $vars, 'appointment_status_changed_admin');
		}
	}

	/**
	 * Get Email Variables
	 * @param $appointment_id
	 * @return array
	 */
	public static function get_email_variables( $appointment_id, $reason = '' ) {
		$appointment    = Appointments::get_full_appointment_by_id($appointment_id);

		$vars = [
			'[admin]'           => get_option('admin_email'),
			'[staff]'           => $appointment->staff_email,
			'[customer]'        => $appointment->customer_email,
			'[staff_name]'      => $appointment->staff_name,
			'[staff_phone]'     => $appointment->staff_phone,
			'[customer_name]'   => $appointment->customer_name,
			'[customer_email]'  => $appointment->customer_email,
			'[customer_phone]'  => $appointment->customer_phone,
			'[service_title]'   => $appointment->service_name,
			'[appointment_id]'  => $appointment->id,
			'[appointment_day]' => date( get_option('date_format'), $appointment->date_timestamp ),
			'[start_time]'      => date( get_option('time_format'), $appointment->start_time ),
			'[payment_method]'  => $appointment->payment_method,
			'[payment_status]'  => __($appointment->payment_status, 'bookit'),
			'[price]'           => $appointment->price,
			'[total]'           => bookit_price($appointment->price),
			'[status]'          => __($appointment->status, 'bookit'),
			'[reason]'          => $reason,
		];

		return $vars;
	}

	/**
	 * Rewrite Email Data
	 * @param $data
	 * @return mixed
	 */
	public static function rewrite_email_data( $data ) {
		if ( empty( $data['vars'] ) or empty( $data['template'] ) ) {
			return $data;
		}

		$template   = $data['template'];
		$settings   = SettingsController::get_settings();

		if ( ! empty( $settings['emails'][$template]['to'] ) ) {
			$data['to'] = strtr( $settings['emails'][$template]['to'], $data['vars'] );
		}

		/** remove payment info from template if zero price */
		if ( (float)$data['vars']["[price]"] == 0 ) {

			foreach ( self::$fields_to_remove_if_price_is_zero as $field ) {
				// remove varaible
				unset($data['vars']["[{$field}]"]);
				// remove from template
				$settings['emails'][$template]['body'] = preg_replace(
					"/\]([^\)].*?)\[{$field}]/" , "]", $settings['emails'][$template]['body']
				);
			}
		}


		if(!empty($settings['emails'][$template]['subject'])) {
			$translates = apply_filters('wpml_translate_single_string', $settings['emails'][$template]['subject'], 'bookit', $template . '_subject');
			$data['subject'] = strtr($translates, $data['vars']);
		}

		if(!empty($settings['emails'][$template]['body'])) {
			$translates = apply_filters('wpml_translate_single_string', $settings['emails'][$template]['body'], 'bookit', $template . '_body');
			$data['body'] = strtr($translates, $data['vars']);
		}

		return $data;
	}

	/**
	 * Appointment Delete Notification
	 *
	 * @param int $appointment_id
	 * @param string $send_notification_to
	 */
	public static function appointment_deleted(int $appointment_id, array $send_notification_to, $reason ) {

		$vars = self::get_email_variables($appointment_id, $reason);
		if ( $send_notification_to['customer'] ) {
			self::sent_mail($vars['[customer]'], '', '', $vars, 'appointment_deleted_customer');
		}

		if ( $send_notification_to['staff'] ) {
			self::sent_mail($vars['[staff]'], '', '', $vars, 'appointment_deleted_staff');
		}
	}

	public static function appointment_created_set_option() {
		$created = get_option('stm_bookit_appointment_created', false);

		if ( !$created ) {
			$data = [ 'show_time' => time(), 'step' => 0, 'prev_action' => '' ];
			set_transient( 'stm_bookit_single_notice_setting', $data );
			update_option('stm_bookit_appointment_created', true);
		}
	}

	public static function stm_admin_notice_rate_bookit_single( $data ) {
		if ( is_array( $data ) ) {
			$data['title'] = __('Whoa!', 'bookit');;
			$data['content'] = __('You have successfully created one appointment. We hope you like it. Time to rate <strong>Bookit 5 Stars!</strong>', 'bookit');
		}

		return $data;
	}

}