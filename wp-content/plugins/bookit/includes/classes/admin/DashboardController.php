<?php

namespace Bookit\Classes\Admin;

use Bookit\Classes\Base\User;
use Bookit\Classes\Nonces;
use Bookit\Classes\Template;
use Bookit\Classes\Translations;
use Bookit\Helpers\FreemiusHelper;

class DashboardController {

	protected static $googleCalendarAddon = 'google-calendar';
	protected static $user = [];

	public static function bookitUser() {
		return User::getUserData();
	}

	/**
	 * Enqueue Admin Styles & Scripts
	 */
	public static function enqueue_styles_scripts() {
		wp_enqueue_style( 'bookit-dashboard-css', BOOKIT_URL . 'assets/dist/dashboard/css/app.css', [], BOOKIT_VERSION );
		wp_enqueue_script( 'bookit-dashboard-js', BOOKIT_URL . 'assets/dist/dashboard/js/app.js', [], BOOKIT_VERSION );

		$translations = array_merge( Translations::get_admin_translations(), Translations::get_addon_translations(), Translations::get_addons_page_translations() );

		$ajax_data = [
			'services_url'  => admin_url( 'admin.php?page=bookit-services' ),
			'calendar_url'  => admin_url( 'admin.php?page=bookit' ),
			'site_url'      => get_bloginfo( 'url' ),
			'ajax_url'      => admin_url( 'admin-ajax.php' ),
			'translations'  => $translations,
			'nonces'        => Nonces::get_admin_nonces(),
			'bookit_user'   => self::bookitUser(),
			'pro_disabled'  => bookit_pro_features_disabled(),//todo remove
			'has_feedback'  => self::has_feedback(),
            'language'      => substr( get_bloginfo( 'language' ), 0, 2 ),
		];

		wp_localize_script( 'bookit-dashboard-js', 'bookit_window', $ajax_data );
	}

	/**
	 * Display Rendered Template
	 * @return bool|string
	 */
	public static function render_addons() {
		wp_enqueue_style( 'bookit-pricing-css', BOOKIT_URL . 'assets/dist/dashboard/css/addons.css', [], BOOKIT_VERSION );

		$data['translations']   = Translations::get_addons_page_translations();
		$data['freemius_info']  = FreemiusHelper::get_freemius_info();
		$data['descriptions']   = [
			'bookit-google-calendar' => __( 'New! Merge your Bookit calendar and Google Calendar with just one click. Now easy to book and schedule appointments.', 'bookit' ),
			'bookit-pro'             => __( 'Let your customers select one of the three available payment processors and WooCommerce platform and pay for meetings with ease.', 'bookit' ),
			'bookit-all-addons'      => __( 'Obtain one single bundle, which includes Google Calendar and Payments at once.', 'bookit' ),
		];
		return Template::load_template( 'dashboard/bookit-addons', $data, true );
	}

	/**
	 * Check if Feedback already added
	 * @return bool
	 */
	public static function has_feedback() {
		return get_option( 'bookit_feedback_added', false );
	}

	/**
	 * Add Feedback
	 */
	public static function add_feedback() {
		check_ajax_referer('bookit_add_feedback', 'nonce');
		update_option( 'bookit_feedback_added', true );
	}

}