<?php
/**
 * File for Hustle_Meta_Base_Visibility class.
 *
 * @package Hustle
 * @since 4.2.0
 */

/**
 * Hustle_Meta_Base_Visibility is the base class for the "visibility" meta of modules.
 * This class should handle what's related to the "visibility" meta.
 *
 * @since 4.2.0
 */
class Hustle_Meta_Base_Visibility extends Hustle_Meta {

	/**
	 * Get the defaults for this meta.
	 *
	 * @since 4.2.0
	 * @return array
	 */
	public function get_defaults() {
		return array();
	}

	/**
	 * Get relevant conditions based on subtype or return FALSE if it shouldn't be shown
	 *
	 * @since unkwnon
	 * @since 4.2.0 Moved from Hustle_Module_Model to this class. Visibility changed from private to public.
	 *
	 * @param string $subtype Module's display type floating|inline|shortcode|widget. Only for embeds and ssharing.
	 * @return array|false
	 */
	public function get_conditions( $subtype = null ) {
		$all_conditions = $this->to_array();

		// Return all. No need to filter per subtype.
		if ( is_null( $subtype ) || empty( $all_conditions['conditions'] ) ) {
			return $all_conditions;
		}

		// Remove the conditions that are not for this subtype.
		$conditions_removed = false;
		foreach ( $all_conditions['conditions'] as $group_id => $data ) {
			if ( isset( $data[ 'apply_on_' . $subtype ] ) && 'false' === $data[ 'apply_on_' . $subtype ] ||
					'shortcode' === $subtype && ! isset( $data[ 'apply_on_' . $subtype ] ) ) {
				$conditions_removed = true;
				unset( $all_conditions['conditions'][ $group_id ] );
			}
		}

		// No conditions are left after filtering per subtype.
		if ( $conditions_removed && empty( $all_conditions['conditions'] ) ) {
			return false;
		}

		return $all_conditions;
	}

	/**
	 * Check if it contains only "simple" conditions.
	 * Simple conditions means conditions which behavior is the same for the same page.
	 * If so - we can allow to cache it for static cache.
	 *
	 * @param string $sub_type Sub type.
	 * @return boolean
	 */
	public function is_simple_conditions( $sub_type ) {
		$all_conditions = $this->get_conditions( $sub_type );

		if ( empty( $all_conditions['conditions'] ) ) {
			return true;
		}
		foreach ( $all_conditions['conditions'] as $conditions ) {
			$complex_conditions = array_diff( array_keys( $conditions ), self::get_non_condition_properties(), self::simple_conditions() );
			if ( $complex_conditions ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Return simple condition keys
	 *
	 * @return array
	 */
	private static function simple_conditions() {
		return array( 'pages', 'posts', 'tags', 'categories', 'archive_pages', 'page_templates', 'on_url', 'wp_conditions', 'wc_pages', 'wc_categories', 'wc_tags', 'wc_archive_pages', 'wc_static_pages' );
	}

	/**
	 * Checks if this module is allowed to be displayed
	 *
	 * @since unknwon
	 * @since 4.2.0 Moved from Hustle_Module_Model to this class.
	 *
	 * @param string $module_type Type of the current module.
	 * @param string $sub_type    Display type for embeddeds and ssharing.
	 * @return bool
	 */
	public function is_allowed_to_display( $module_type, $sub_type = null ) {
		$global_behavior = false;

		$all_conditions = $this->get_conditions( $sub_type );

		if ( false === $all_conditions ) {
			if ( 'shortcode' === $sub_type ) {
				return true;
			}
			return false;
		}
		if ( empty( $all_conditions['conditions'] ) ) {
			return true;
		}

		$display = null;
		foreach ( $all_conditions['conditions'] as $group_id => $conditions ) {
			$any_true         = false;
			$any_false        = false;
			$default_behavior = $this->get_default_group_behavior( $conditions );
			if ( $default_behavior ) {
				$global_behavior = true;
			}

			/**
			 * condition type
			 */
			$filter_type = isset( $conditions['filter_type'] ) &&
					'any' === $conditions['filter_type']
				? $conditions['filter_type'] : 'all';

			foreach ( $conditions as $condition_key => $args ) {

				// These are not conditions but group's properties we don't need to check here.
				if ( in_array( $condition_key, self::get_non_condition_properties(), true ) ) {
					continue;
				}

				// only cpt have 'postType' and 'postTypeLabel' properties.
				if ( is_array( $args ) && isset( $args['postType'] ) && isset( $args['postTypeLabel'] ) ) {
					$condition_key = 'cpt';
				}
				$condition = Hustle_Condition_Factory::build( $condition_key, $args );
				if ( $condition ) {
					$some_conditions = true;
					$condition->set_type( $module_type );
					$condition->module = $this->module;
					$current           = (bool) $condition->is_allowed();
					if ( false === $current ) {
						$any_false = true;
					} else {
						$any_true = true;
					}
				}
			}

			if ( 'any' === $filter_type ) {
				if ( $any_true ) {
					$display = $display || $any_true && ! $default_behavior;
				} elseif ( $any_false ) {
					$display = $display || $default_behavior;
				}
			}
			if ( 'all' === $filter_type ) {
				if ( $any_false ) {
					$display = $display || $default_behavior;
				} elseif ( $any_true ) {
					$display = $display || $any_true && ! $default_behavior;
				}
			}
		}

		// Show module if there are no conditions.
		if ( empty( $some_conditions ) ) {
			return true;
		}

		if ( is_null( $display ) ) {
			return $global_behavior;
		}

		return $display;
	}

	/**
	 * Get property keys which aren't related to conditions
	 *
	 * @return array
	 */
	private static function get_non_condition_properties() {
		return array( 'group_id', 'filter_type', 'apply_on_inline', 'apply_on_widget', 'apply_on_shortcode', 'apply_on_floating', 'show_or_hide_conditions' );
	}

	/**
	 * Get visibility behavior by default, to display or not.
	 *
	 * @since 4.1.0
	 * @since 4.2.0 Moved from Hustle_Module_Model to this class.
	 *
	 * @param array $conditions Conditions' group.
	 * @return bool
	 */
	private function get_default_group_behavior( $conditions ) {
		return ! empty( $conditions['show_or_hide_conditions'] ) && 'hide' === $conditions['show_or_hide_conditions'];

	}
}
