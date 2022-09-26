<?php
namespace Bookit\Classes\Base;


/**
 * Class Addon
 * base addon functions here
 */

abstract class Addon {

	public static $addon_slug;
	/**
	 * Possible values for $settingTab, if self showed as separate tab on setting page
	 * @var string[]
	 */
	public static $settingTabs = ['general', 'currency', 'payments', 'emailTemplates', 'self'];

	/**
	 * Addon title.
	 *
	 * @string
	 */
	protected static $title;

	/**
	 * Addon Version.
	 *
	 * @string
	 */
	protected static $version;

	/**
	 * Is Addon active.
	 * @string
	 */
	protected static $active = false;

	/**
	 * Is Addon installed.
	 * @string
	 */
	protected static $installed = false;

	/**
	 * Addon link.
	 *
	 * @string
	 */
	protected static $link;

	/**
	 * Addon base setting key ( wp_options ) .
	 * @staticvar string
	 */
	protected static $settingsKey;

	/**
	 * Addon base settings.
	 *
	 * @staticvar array
	 */
	protected static $settings;

	/**
	 * Addon appearance settings on plugin settings page ( which tab to use ).
	 *
	 * @staticvar string
	 */
	protected static $settingTab;

	/**
	 * Addon order, jf separate tab for settings.
	 *
	 * @staticvar string
	 */
	protected static $order;

	/**
	 * Freemius activation link
	 * @staticvar string
	 */
	protected static $activationLink = '';

	/**
	 * Is need freemius license
	 * @staticvar boolean
	 */
	protected static $is_premium = true; // by default always true

	/**
	 * Is have freemius license
	 * @staticvar boolean
	 */
	protected static $is_paying = false;


	/**
	 * Run addon.
	 */
	public static function run() {
		// register hooks WP hooks.
		// add actions
		// check for updates
	}

	/**
	 * Activate Addon.
	 */
	public static function activate() {}

	/**
	 * Deactivate Addon.
	 */
	public static function deactivate() {}

	/**
	 * Uninstall Addon.
	 */
	public static function uninstall() {}

	/**
	 * Init freemius
	 * set values to:
	 * $is_paying
	 * $is_premium
	 * $activationLink
	 */
	private static function initFreemius(){}
}