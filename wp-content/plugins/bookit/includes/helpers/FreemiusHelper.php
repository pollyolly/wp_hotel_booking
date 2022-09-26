<?php
// TODO refactoring
namespace Bookit\Helpers;

use Bookit\Classes\Base\Plugin;
use Exception;
/**
 * Bookit Freemius Helper
 */

class FreemiusHelper {

	private const STM_FREEMIUS_PATH            = 'https://stylemixthemes.com/api/freemius/';
	private const STM_BOOKIT_ADDONS            = [ 'bookit-all-addons', 'bookit-google-calendar', 'bookit-pro' ];
	private const STM_FREEMIUS_CHECKOUT_LINK   = 'https://checkout.freemius.com/mode/dialog/plugin/';
	private const STM_CHECKOUT_URL             = 'https://stylemixthemes.com/wordpress-appointment-plugin/';

	/**
	 * Return data for all bookit addons
	 * @return array
	 */
	public static function get_freemius_info() {

		$result = [];
		foreach ( self::STM_BOOKIT_ADDONS as $addon ) {
			$result[$addon] = [];
			$response = wp_remote_get( self::STM_FREEMIUS_PATH . $addon . '.json' );
			$body = wp_remote_retrieve_body( $response );
			$body = json_decode( $body );
			if ( empty( $body ) ) {
				continue;
			}

			$result[$addon]['title'] = $body->title;

			if ( isset( $body->plans ) && ! empty( $body->plans ) ) {
				$result[$addon]['plan'] = self::set_premium_plan_prices( $body->plans, $body->id );
			}

			if ( isset( $body->latest ) && ! empty( $body->latest ) ) {
				$result[$addon]['latest'] = self::set_latest_info( $body->latest );
			}

			if ( isset( $body->info ) && ! empty( $body->info ) ) {
				$result[$addon]['info'] = $body->info;
			}
		}

		return $result;
	}

	/**
	 * Return plugin id by name
	 * @return integer
	 */
	public static function get_addon_id_by_name( $addonName ) {

		$response = wp_remote_get( self::STM_FREEMIUS_PATH . $addonName . '.json' );
		$body = wp_remote_retrieve_body( $response );
		$body = json_decode( $body );
		if ( !empty( $body ) && isset( $body->id ) && !empty( $body->id )) {
			return (int)$body->id;
		}

		return null;
	}

	/**
	 * Return data for bookit addon by name
	 * @return array
	 */
	public static function get_addon_info( $addonName ) {

		$result = [];
		$response = wp_remote_get( self::STM_FREEMIUS_PATH . $addonName . '.json' );
		$body = wp_remote_retrieve_body( $response );
		$body = json_decode( $body );
		if ( empty( $body ) ) {
			return $result;
		}

		$result['title']        = $body->title;
		$result['descriptions'] = self::addon_descriptions( $addonName );

		if ( isset( $body->plans ) && ! empty( $body->plans ) ) {
			$result['plan'] = self::set_premium_plan_prices( $body->plans, $body->id );
		}

		if ( isset( $body->latest ) && ! empty( $body->latest ) ) {
			$result['latest'] = self::set_latest_info( $body->latest );
		}

		if ( isset( $body->info ) && ! empty( $body->info ) ) {
			$result['info'] = $body->info;
		}

		return $result;
	}

	protected static function addon_descriptions( $addonName ) {
		$descriptions = [
			'bookit-google-calendar' => __( 'New! Merge your Bookit calendar and Google Calendar with just one click. Now easy to book and schedule appointments.', 'bookit' ),
			'bookit-pro'             => __( 'Let your customers select one of the three available payment processors and WooCommerce platform and pay for meetings with ease.', 'bookit' ),
			'bookit-all-addons'      => __( 'Obtain one single bundle, which includes Google Calendar and Payments at once.', 'bookit' ),
		];

		return array_key_exists( $addonName, $descriptions )? $descriptions[$addonName] : '';
	}

	public static function get_license( $plugin_id ) {
		$result = false;

		if ( !function_exists( 'bookit_fs' ) ) {
			return $result;
		}

		$bookit_fs = bookit_fs();
		$addon = $bookit_fs->get_addon($plugin_id);
		if ( $addon == false ) {
			return $result;
		}

		if ( !$bookit_fs->is_addon_installed($plugin_id) || !$bookit_fs->is_addon_activated($plugin_id)){
			return $result;
		}

		$addonInstance = $bookit_fs->get_addon_instance($plugin_id);

		return $addonInstance->_get_license();
	}

	protected static function set_premium_plan_prices( $plans, $plugin_id ) {
		$plan_info = [];

		$plan_data = [
			'1' => [
				'text' => __( 'Single Site', 'bookit' ),
				'classname' => '',
				'type' => '',
			],
			'5' => [
				'classname' => 'stm_plan--popular',
				'text' => __( 'Up to 5 Sites', 'bookit' ),
				'type' => __( 'Most Popular', 'bookit' )
			],
			'25' => [
				'classname' => 'stm_plan--developer',
				'text' => __( 'Up to 25 Sites', 'bookit' ),
				'type' => __( 'Developer Oriented', 'bookit' )
			]
		];


		$license   = self::get_license( $plugin_id );

		foreach ( $plans as $plan ) {
			if ( $plan->name == 'premium' || $plan->name == 'bookit_all_addons') {
				if ( isset( $plan->pricing ) ) {
					foreach ( $plan->pricing as $pricing ) {
						$plan_info[ 'licenses_' . $pricing->licenses ]      = $pricing;
						$plan_info[ 'licenses_' . $pricing->licenses ]->url = self::STM_CHECKOUT_URL . "?productId={$plugin_id}&period=annual&licenses={$pricing->licenses}";

						if ( ! isset( $plan_data[ $pricing->licenses ] ) ) {
							$plan_data[ $pricing->licenses ] = [
								'text' => esc_html__( "Up to {$pricing->licenses} Sites", "bookit" ),
								'classname' => '',
								'type' => '',
							];
						}
						$plan_info[ 'licenses_' . $pricing->licenses ]->active      = false;
						$plan_info[ 'licenses_' . $pricing->licenses ]->is_lifetime = false;
						if ( $license && $pricing->id == $license->pricing_id ){
							$plan_info[ 'licenses_' . $pricing->licenses ]->active      = true;
							$plan_info[ 'licenses_' . $pricing->licenses ]->is_lifetime = $license->is_lifetime();
						}
						$plan_info[ 'licenses_' . $pricing->licenses ]->data = $plan_data[ $pricing->licenses ];
					}
				}
				break;
			}
		}

		return $plan_info;
	}

	protected static function set_latest_info( $latest ) {
		$latest_info['version']           = $latest->version;
		$latest_info['tested_up_to']      = $latest->tested_up_to_version;
		$latest_info['created']           = date( "M j, Y", strtotime( $latest->created ) );
		$latest_info['last_update']       = date( "M j, Y", strtotime( $latest->updated ) );
		$latest_info['wordpress_version'] = $latest->requires_platform_version;

		return $latest_info;
	}

	/**
	 * Get bookit plugin installed addons.
	 * If addon installed but not active load base and translation cls
	 * to show info on settings page
	 * @return array
	 */
	public static function get_installed_addons() {
		$result = [];

		if ( !function_exists( 'bookit_fs' ) ) {
			return $result;
		}

		$bookit_fs      = bookit_fs();
		$addons         = $bookit_fs->get_installed_addons();
		$accountAddons  = self::get_account_addons();

		if ( !$addons && empty( $accountAddons ) ) {
			return $result;
		}

		foreach ( $addons as $addon ) {
			if ( !$addon ) {
				continue;
			}
			$addonName = $addon->get_slug();

			$existAddonKey = array_search(str_replace(Plugin::$prefix, '', $addonName), array_column($accountAddons, 'name'));
			if ( $existAddonKey !== false ){
				unset($accountAddons[$existAddonKey]);
			}

			$classNamespacePart = str_replace('-', '', ucwords($addonName, '-'));
			/** base addon class */
			$addonClass  = sprintf('%s\Classes\Admin\Base', ucwords($classNamespacePart));

			if ( !class_exists($addonClass) && file_exists( ABSPATH . sprintf('/wp-content/plugins/%s/includes/classes/admin/Base.php', $addonName) ) ) {
				// load base class if plugin unactive
				require_once( ABSPATH . sprintf('/wp-content/plugins/%s/includes/classes/admin/Base.php', $addonName) );
			}
			if ( class_exists($addonClass) ) {
				$result[] = [
					'name'         => str_replace(Plugin::$prefix, '', $addonName),
					'data'         => $addonClass::getAddonData(),
					'translations' => $addonClass::getTranslations(),
					'freemius'     => [ 'title' => $addonName ],
				];
			}
		}
		return array_merge( $result, $accountAddons );
	}

	public static function get_account_addons() {
		$result = [];

		if ( !function_exists( 'bookit_fs' ) ) {
			return $result;
		}
		$bookit_fs = bookit_fs();
		$addons = $bookit_fs->get_account_addons();

		if ( !$addons || empty( $addons ) ) {
			return $result;
		}

		foreach ( $addons as $addonId ) {
			$addon = $bookit_fs->get_addon($addonId);

			if ( !$addon || ( $addon && (!$bookit_fs->is_addon_installed($addonId) ) ) ) {
				continue;
			}

			$addon     = $bookit_fs->get_addon_instance($addonId);
			$addonName = $addon->get_slug();
			$classNamespacePart = str_replace('-', '', ucwords($addonName, '-'));
			/** base addon class */
			$addonClass  = sprintf('%s\Classes\Admin\Base', ucwords($classNamespacePart));

			if ( !is_plugin_active(sprintf('%s/%s.php', $addonName, $addonName)) && file_exists( ABSPATH . sprintf('/wp-content/plugins/%s/includes/classes/admin/Base.php', $addonName) ) ) {
				// load base class if plugin unactive
				require_once( ABSPATH . sprintf('/wp-content/plugins/%s/includes/classes/admin/Base.php', $addonName) );
				require_once( ABSPATH . sprintf('/wp-content/plugins/%s/includes/classes/Translations.php', $addonName) );
			}
			if ( class_exists($addonClass) ) {
				$result[] = [
					'name' => str_replace(Plugin::$prefix, '', $addonName),
					'data' => $addonClass::getAddonData(),
					'translations' => $addonClass::getTranslations(),
					'freemius'     => [ 'title' => $addonName ],
				];
			}
		}
		return $result;
	}


	/**
	 * @return array of exist addon licences
	 */
	public static function get_exist_licenses() {
		$result = [];

		if ( !function_exists( 'bookit_fs' ) ) {
			return $result;
		}
		$bookit_fs = bookit_fs();
		$accountAddons = $bookit_fs->get_account_addons();

		if ( $accountAddons == false ) { return; }

		foreach ( $accountAddons as $addonId ) {
			if ( !$bookit_fs->is_addon_installed($addonId) ) {
				continue;
			}
			$addon     = $bookit_fs->get_addon_instance($addonId);
			$addonName = $addon->get_slug();

			$result[$addonName] = [];
			if ( is_null( $addon->_get_license() ) ) {
				continue;
			}
			$license = $addon->_get_license();
			$result[$addonName]['lifetime'] = $license->is_lifetime();
			$result[$addonName]['id']       = $license->pricing_id; // check site count
		}

		return $result;
	}

}