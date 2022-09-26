<?php

namespace Bookit\Widgets;

use Bookit\Classes\Database\Categories;
use Bookit\Classes\Database\Services;
use Bookit\Classes\Database\Staff;

class VisualComposerWidget {

	/**
	 * Load
	 */
	public function load() {
		vc_lean_map( 'bookit', [$this, 'bookit_vc_element'] );
	}

	/**
	 * @return array
	 */
	function bookit_vc_element() {

		$categories         = Categories::get_all();
		$services           = Services::get_all();
		$staff              = Staff::get_all_shorted();
		$categories_list    = bookit_data_to_list($categories, 'name', 'id');
		$services_list      = bookit_data_to_list($services, 'title', 'id');
		$staff_list         = bookit_data_to_list($staff, 'full_name', 'id');

		return array(
			'base' => "bookit",
			'name' => esc_html__( 'Bookit Calendar', 'bookit' ),
			'icon' => BOOKIT_URL . '/assets/images/icon-100x100.png' ,
			'category' => esc_html__( 'Content', 'bookit' ),
			'description' => esc_html__( 'Place Bookit Calendar', 'bookit' ),
			'params' => array(
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Select Category', 'bookit' ),
					'param_name' => 'category',
					'value' => $categories_list,
					'save_always' => true,
					'description' => '',
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Select Service', 'bookit' ),
					'param_name' => 'service',
					'value' => $services_list,
					'save_always' => true,
					'description' => '',
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Select Staff', 'bookit' ),
					'param_name' => 'staff',
					'value' => $staff_list,
					'save_always' => true,
					'description' => '',
				)
			)
		);
	}
}