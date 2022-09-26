<?php
/**
 * Opt_In_Condition_Abstract class.
 *
 * @package Hustle
 * @since unwknown
 */

/**
 * Opt_In_Condition_Abstract.
 * The abstract class for all the visibility conditions.
 *
 * @since unknown
 */
abstract class Opt_In_Condition_Abstract {

	/**
	 * Current module type.
	 *
	 * @since unkwnon
	 * @var string popup|slidein|embedded|social_sharing
	 */
	protected $module_type;

	/**
	 * Arguments for the condition.
	 *
	 * @since unkwnon
	 * @var array
	 */
	protected $args;

	/**
	 * Instance of Opt_In_Condition_Utils
	 *
	 * @since unkwnon
	 * @var Opt_In_Utils
	 */
	private $utils;

	/**
	 * Instance of
	 *
	 * @since unkwnon
	 * @var Opt_In_Geo
	 */
	private $geo;

	/**
	 * Hustle module
	 *
	 * @since unkwnon
	 * @var Hustle_Model
	 */
	public $module;

	/**
	 * Class constructor.
	 *
	 * @since unkwnon
	 * @param array $args Arguments for the condition.
	 */
	public function __construct( $args ) {
		$this->args = (object) $args;
	}

	/**
	 * Sets optin type for the condition.
	 *
	 * @since unkwnon
	 * @param string $module_type popup|slidein|embedded|social_sharing.
	 */
	public function set_type( $module_type ) {
		$this->module_type = $module_type;
	}

	/**
	 * Returns whether the condition was met.
	 *
	 * @since unkwnon
	 * @return boolean
	 */
	abstract public function is_allowed();

	/**
	 * Get global post
	 *
	 * @global object $post
	 * @return object
	 */
	public static function get_post() {
		if ( wp_doing_ajax() ) {
			$url     = wp_get_referer();
			$post_id = url_to_postid( $url );
			$post    = get_post( $post_id );
		} else {
			global $post;
		}

		return $post;
	}

	/**
	 * Check WP conditional tag
	 *
	 * @param  string $tag Function name.
	 * @return boolean
	 */
	public static function check( $tag ) {
		if ( wp_doing_ajax() ) {
			$tags = filter_input( INPUT_POST, 'conditional_tags', FILTER_VALIDATE_BOOLEAN, FILTER_REQUIRE_ARRAY );
			return ! empty( $tags[ $tag ] );
		} else {
			if ( 'is_order_received' === $tag ) {
				return is_wc_endpoint_url( 'order-received' );
			}
			return $tag();
		}
	}
}
