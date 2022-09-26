<?php

/**
 * Class Hustle_GHBlock_Unsubscribe
 *
 * @since 4.5 Gutenberg Addon
 */
class Hustle_GHBlock_Unsubscribe extends Hustle_GHBlock_Abstract {

	/**
	 * Block identifier
	 *
	 * @var string
	 */
	protected $_slug = 'unsubscribe';

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Initialize block.
		$this->init();
	}

	/**
	 * Render block markup on front-end
	 *
	 * @param array $properties Block properties.
	 * @return string
	 */
	public function render_block( $properties = array() ) {
		$ids = isset( $properties['id'] ) && is_array( $properties['id'] ) ? implode( ', ', $properties['id'] ) : '';

		$skip = ! empty( $properties['skipConfirmation'] ) ? ' skip_confirmation="true"' : '';

		return '[wd_hustle_unsubscribe id="' . $ids . '"' . $skip . ' /]';
	}

	/**
	 * Enqueue assets ( scritps / styles )
	 * Should be overriden in block class
	 */
	public function load_assets() {
		// Scripts.
		wp_enqueue_script(
			'hustle-block-unsubscribe',
			Hustle_Gutenberg::get_plugin_url() . '/js/unsubscribe-block.min.js',
			array( 'wp-blocks', 'wp-i18n', 'wp-element' ),
			filemtime( Hustle_Gutenberg::get_plugin_dir() . 'js/unsubscribe-block.min.js' )
		);

		// Localize scripts.
		wp_localize_script(
			'hustle-block-unsubscribe',
			'hustle_unsubscribe_data',
			array(
				'popups'        => $this->get_popups(),
				'slideins'      => $this->get_slideins(),
				'embeds'        => $this->get_embeds(),
				'settings_url'  => add_query_arg(
					array(
						'page'    => 'hustle_settings',
						'section' => 'unsubscribe',
					),
					admin_url( 'admin.php' )
				),
				'nonce'         => wp_create_nonce( 'hustle_gutenberg_get_unsubscribe_form' ),
				'shortcode_tag' => 'wd_hustle_unsubscribe',
				'l10n'          => $this->localize(),
			)
		);

		wp_enqueue_style(
			'hustle-block-unsubscribe',
			Hustle_Gutenberg::get_plugin_url() . '/css/unsubscribe-block.css',
			array(),
			filemtime( Hustle_Gutenberg::get_plugin_dir() . 'css/unsubscribe-block.css' )
		);

	}

	/**
	 * Get popups
	 *
	 * @return array
	 */
	public function get_popups() {
		$module_list = $this->get_modules_by_type( 'popup' );
		unset( $module_list[0] );
		return $module_list;
	}

	/**
	 * Get slide-ins
	 *
	 * @return array
	 */
	public function get_slideins() {
		$module_list = $this->get_modules_by_type( 'slidein' );
		unset( $module_list[0] );
		return $module_list;
	}

	/**
	 * Get Embeds
	 *
	 * @return array
	 */
	public function get_embeds() {
		$module_list = $this->get_modules_by_type( 'embedded' );
		unset( $module_list[0] );
		return $module_list;
	}

	/**
	 * Get texts
	 *
	 * @return array
	 */
	private function localize() {
		return array(
			'popups'             => esc_html__( 'Pop-ups', 'hustle' ),
			'slideins'           => esc_html__( 'Slide-ins', 'hustle' ),
			'embeds'             => esc_html__( 'Embeds', 'hustle' ),
			'modules'            => esc_html__( 'Modules', 'hustle' ),
			'customize_settings' => esc_html__( 'Customize Settings', 'hustle' ),
			'rendering'          => esc_html__( 'Rendering...', 'hustle' ),
			'block_name'         => esc_html__( 'Unsubscribe', 'hustle' ),
			'block_description'  => esc_html__( 'Display Hustle Unsubscribe form.', 'hustle' ),
			'skip_confirmation'  => esc_html__( 'Skip confirmation step', 'hustle' ),
			'block_instruction'  => esc_html__( 'By default, the Unsubscribe form allows users to unsubscribe from all modules, but you can specify the modules you want to enable the unsubscribe option for.', 'hustle' ),
		);
	}
}

new Hustle_GHBlock_Unsubscribe();
