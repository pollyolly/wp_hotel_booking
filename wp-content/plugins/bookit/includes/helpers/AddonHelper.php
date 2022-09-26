<?php

namespace Bookit\Helpers;

/**
 * Bookit Clean Helper
 */


class AddonHelper {

	public static $paymentAddon = 'bookit-payments';


	public static function getInstalledPluginBySlug( string $addonSlug) {
		$installedPlugins = get_plugins();
		if ( array_key_exists( $addonSlug, $installedPlugins ) || in_array( $addonSlug, $installedPlugins, true ) ) {
			return $installedPlugins[$addonSlug];
		}
		return [];
	}

	public static function checkIsInstalledPlugin( string $addonSlug) {
		$installedPlugins = get_plugins();
		return array_key_exists( $addonSlug, $installedPlugins ) || in_array( $addonSlug, $installedPlugins, true ) ? 'true' : 'false';
	}

	public static function getAddonDataByName( string $addon ) {
		$classNamespacePart = str_replace('-', '', ucwords($addon, '-'));
		$addonClass         = sprintf('%s\Classes\Admin\Base', ucwords($classNamespacePart));

		if ( !file_exists( WP_CONTENT_DIR . sprintf('/plugins/%s/includes/classes/admin/Base.php', $addon) ) ) {
			return [ 'isCanUse' => false ];
		}

		if ( is_plugin_active(sprintf('%s/%s.php', $addon, $addon)) && FreemiusHelper::get_license( FreemiusHelper::get_addon_id_by_name($addon) ) != null){
			require_once( WP_CONTENT_DIR . sprintf( '/plugins/%s/includes/classes/admin/Base.php', $addon ) );
			if ( class_exists($addonClass) ) {
				return $addonClass::getAddonData();
			}
		}

		return ['isCanUse' => false ];
	}
}