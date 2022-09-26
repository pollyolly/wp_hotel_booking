<?php
namespace Bookit\Classes\Base;


use Bookit\Classes\Admin\SettingsController;
use Bookit\Classes\Base\User;
use Bookit\Classes\Vendor\DatabaseModel;
use Bookit\Helpers\FreemiusHelper;
use Bookit\Helpers\MailTemplateHelper;
use BookitPayments\Classes\Admin\Base;

/**
 * Class Plugin
 * base plugin functions here
 */

class Plugin {

	public static $prefix = 'bookit-';
	protected static $info   = [];
	protected static $wp_options = ['bookit_version', 'bookit_db_version', 'bookit_import_file', 'bookit_settings'];

	/**
	 * Run plugin.
	 */
	public static function run() {

		register_activation_hook( BOOKIT_FILE, array( __CLASS__, 'activate' ) );
		register_deactivation_hook( BOOKIT_FILE, array( __CLASS__, 'deactivate' ) );
		register_uninstall_hook( BOOKIT_FILE, array( __CLASS__, 'uninstall' ) );
	}

	/**
	 * Plugin activation
	 */
	public static function activate() {
		/** create tables */
		\Bookit\Classes\Database\Appointments::create_table();
		\Bookit\Classes\Database\Categories::create_table();
		\Bookit\Classes\Database\Coupons::create_table();
		\Bookit\Classes\Database\Customers::create_table();
		\Bookit\Classes\Database\Discounts::create_table();
		\Bookit\Classes\Database\Services::create_table();
		\Bookit\Classes\Database\Staff::create_table();
		\Bookit\Classes\Database\Staff_Services::create_table();
		\Bookit\Classes\Database\Staff_Working_Hours::create_table();
		\Bookit\Classes\Database\Payments::create_table();

		/** settings */
		SettingsController::save_default_settings();

		/** add bookit roles and append bookit capabilites to wp admins */
		User::addBookitUserRoles();
		User::addBookitCapabilitiesToWpRoles();

		/** Add email templates to WPML strings if WPML installed */
		MailTemplateHelper::registerTemplateDataToWPMLStrings();
	}

	/**
	 * Plugin uninstaller
	 */
	public static function uninstall( ) {
		$settings = SettingsController::get_settings();

		/** delete all data if clean all on delete was enabled */
		if ( filter_var($settings['clean_all_on_delete'], FILTER_VALIDATE_BOOLEAN) == true ){
			/** remove all plugin options */
			foreach ( self::$wp_options as $option ) {
				delete_option($option);
			}
			/** clean database */
			DatabaseModel::drop_tables();

			/** Clean roles and capabilities */
			User::cleanRoles();
			User::cleanCapabilities();;
		}
	}

	/**
	 * Plugin deactivation
	 */
	public static function deactivate( ) {}

	/**
	 * Get prefix.
	 */
	public static function getPrefix()
	{
		if ( static::$prefix === null ) {
			static::$prefix = str_replace( array( '-addon', '-' ), array( '', '_' ), static::getSlug() ) . '_';
		}

		return static::$prefix;
	}

	/**
	 * Get plugin info (title, version, text domain).
	 *
	 * @return string
	 */
	public static function getPluginInfo() {
		$info = [];
		if ( empty( static::$info ) ) {
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}
			$plugin_data = get_plugin_data( BOOKIT_FILE );
			$info['version']        = $plugin_data['Version'];
			$info['title']          = $plugin_data['Name'];
			$info['text_domain']    = $plugin_data['TextDomain'];
		}

		return $info;
	}

	/**
	 * Check is addon installed
	 * @param $addon
	 *
	 * @return bool
	 */
	public static function isAddonInstalledAndEnabled( $addon ) {
		$addons = FreemiusHelper::get_installed_addons();
		$addonKey = array_search($addon, array_column($addons, 'name'));

		if ( $addonKey !== false
		     && filter_var(get_deep_array_value_by_path( 'data.settings.enabled', $addons[$addonKey] ), FILTER_VALIDATE_BOOLEAN) ) {
			return true;
		}
		return false;
	}

	/**
	 * Get plugin addon info by name.
	 *
	 * @param $addon
	 * @return array
	 */
	public static function getAddonInfo( $addon ) {
		$classNamespacePart = str_replace('-', '', ucwords(self::$prefix.$addon, '-'));

		/** base addon class */
		$addonClass  = sprintf('%s\Classes\Admin\Base', ucwords($classNamespacePart));
		if ( !class_exists($addonClass) ) { return []; }

		$info = [ 'name' => $addon, 'data' => $addonClass::getAddonData() ];

		return $info;
	}
}