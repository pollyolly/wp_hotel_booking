<?php
/**
 * File for the Hustle_Multisite class.
 *
 * @package Hustle
 * @since 4.4.7
 */

if ( ! class_exists( 'Hustle_Multisite' ) ) :
	/**
	 *  Class Hustle_Multisite.
	 */
	class Hustle_Multisite {

		/**
		 * Hustle_Multisite site class constructor.
		 */
		public function __construct() {

			// add action to clone modules to new site.
			add_action( 'wp_insert_site', array( $this, 'clone_modules_to_new_site' ), 10, 1 );

		}
		/**
		 * Clone modules to the new site by ids.
		 *
		 * @param WP_Site $new_site - New site object.
		 * @return void
		 */
		public function clone_modules_to_new_site( $new_site ) {
			if ( class_exists( 'Hustle_Model' ) && ! empty( $new_site->id ) ) {
				$module_ids = apply_filters( 'hustle_clone_modules_to_new_site', array() );
				if ( empty( $module_ids ) ) {
					return;
				}
				foreach ( $module_ids as $module_id ) {
					$module = Hustle_Model::get_module( $module_id );
					try {
						// if module id is invalid go to next module id.
						if ( ! $module->id ) {
							continue;
						}
						$data = $module->clone_module(); // clone module data.
						switch_to_blog( $new_site->id );
						Hustle_Db::maybe_create_tables(); // Create Hustle custom tables.
						$result = $module->save();        // Save the module.
						if ( $result && ! is_wp_error( $result ) ) {

							$module->update_module( $data );
						}
						restore_current_blog();
					} catch ( Exception $e ) {
						error_log( 'There is something wrong with cloning modules to the new site' );
					}
				}
			}
		}

	}
endif;
