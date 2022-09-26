<?php

namespace Bookit\Classes;

use Bookit\Classes\Admin\SettingsController;

class Customization {

	/**
	 * Init Customization
	 */
	public static function init() {
		add_action('bookit_before_update_setting', [self::class, 'custom_colors'], 100, 1);
	}

	/**
	 * Generate Styles with Custom Colors
	 * @param array $settings
	 */
	public static function custom_colors( $settings, $force = false ) {
		if ( $settings['custom_colors_enabled'] == 'true' ) {
			$old_settings   = SettingsController::get_settings();
			$old_colors     = $old_settings['custom_colors'];
			$new_colors     = $settings['custom_colors'];

			if ( ( $old_colors != $new_colors ) || $force == true ) {
				global $wp_filesystem;

				if ( empty( $wp_filesystem ) ) {
					require_once ABSPATH . '/wp-admin/includes/file.php';
					WP_Filesystem();
				}

				$upload     = wp_upload_dir();
				$upload_dir = $upload['basedir'] . '/bookit';
				$styles     = BOOKIT_PATH . '/assets/dist/frontend/css/app.css';

				if ( ! $wp_filesystem->is_dir( $upload_dir ) ) {
					wp_mkdir_p( $upload_dir );
				}

				if ( file_exists( $styles ) ) {
					$new_colors[]       = BOOKIT_URL . 'assets/dist/';
					$original_colors    = array(
						'#066',
						'#f0f8f8',
						'#ffd400',
						'#fff',
						'#272727',
						'../../'
					);

					$css = str_replace( $original_colors, $new_colors, file_get_contents($styles) );
					$wp_filesystem->put_contents( $upload_dir . '/app.css', $css, FS_CHMOD_FILE );

					$version = intval( get_option( 'bookit_styles_version', 1 ) ) + 1;
					update_option( 'bookit_styles_version', $version );
				}
			}
		}
	}

}