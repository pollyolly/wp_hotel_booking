<?php

namespace Bookit\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Base_Control;
use Bookit\Classes\Database\Categories;
use Bookit\Classes\Database\Services;
use Bookit\Classes\Database\Staff;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Elementor Bookit Widget
 *
 * Elementor widget for Bookit.
 *
 * @since 1.0.0
 */

class ElementorWidgetSettings extends Widget_Base {
	/**
	 * Retrieve the widget name.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'bookit';
	}

	/**
	 * Retrieve the widget title.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'Bookit Calendar', 'bookit' );
	}

	/**
	 * Retrieve the widget icon.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-calendar';
	}

	/**
	 * Retrieve the list of categories the widget belongs to.
	 *
	 * Used to determine where to display the widget in the editor.
	 *
	 * Note that currently Elementor supports only one posts.
	 * When multiple categories passed, Elementor uses the first one.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return array( 'theme-elements' );
	}

	/**
	 * Register the widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'bookit',
			array(
				'label' => __( 'Calendar Settings', 'bookit' ),
			)
		);

		$this->add_control(
			'bookit_category',
			array(
				'label'   => __( 'Select Category', 'bookit' ),
				'type'    => Controls_Manager::SELECT,
				'options' => bookit_data_to_list( Categories::get_all(), 'id', 'name', true ),
				'default' => '',
			)
		);

		$this->add_control(
			'bookit_service',
			array(
				'label'   => __( 'Select Service', 'bookit' ),
				'type'    => Controls_Manager::SELECT,
				'options' => bookit_data_to_list( Services::get_all(), 'id', 'title', true ),
				'default' => '',
			)
		);

		$this->add_control(
			'bookit_staff',
			array(
				'label'   => __( 'Select Staff', 'bookit' ),
				'type'    => Controls_Manager::SELECT,
				'options' => bookit_data_to_list( Staff::get_all_shorted(), 'id', 'full_name', true ),
				'default' => '',
			)
		);

		$this->end_controls_section();

	}

	/**
	 * Render the widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings();
		echo do_shortcode( "[bookit category='" . esc_attr( $settings['bookit_category'] ) . "' service='" . esc_attr( $settings['bookit_service'] ) . " staff='" . esc_attr( $settings['bookit_staff'] ) . "']" );
	}

	/**
	 * Render the widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function content_template() {}
}
