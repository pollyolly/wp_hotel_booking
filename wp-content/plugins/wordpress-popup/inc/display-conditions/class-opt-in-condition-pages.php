<?php
/**
 * Opt_In_Condition_Pages class.
 *
 * @package Hustle
 * @since unknwon
 */

/**
 * Opt_In_Condition_Pages.
 * Handles the page post type.
 *
 * @since unknwon
 */
class Opt_In_Condition_Pages extends Opt_In_Condition_Abstract {

	/**
	 * Returns whether the condition was met.
	 *
	 * @since unknown
	 */
	public function is_allowed() {
		$all         = false;
		$none        = false;
		$pages       = ! empty( $this->args->pages ) ? (array) $this->args->pages : array();
		$filter_type = isset( $this->args->filter_type ) && in_array( $this->args->filter_type, array( 'only', 'except' ), true )
				? $this->args->filter_type : 'except';
		$page_id     = wp_doing_ajax()
				? filter_input( INPUT_POST, 'real_page_id', FILTER_VALIDATE_INT )
				: Opt_In_Utils::get_real_page_id();

		if ( ! $page_id ) {
			return false;
		}
		if ( empty( $pages ) ) {
			if ( 'except' === $filter_type ) {
				$all = true;
			} else {
				$none = true;
			}
		}
		if ( $none ) {
			return false;
		}

		$is_selected_page = in_array( (string) $page_id, $pages, true );
		switch ( $filter_type ) {
			case 'only':
				return $all || $is_selected_page;

			case 'except':
			default:
				return $all || ! $is_selected_page;
		}
	}

}
