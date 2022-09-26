<?php
add_action('admin_footer', 'stm_bookit_render_feature_request');

function stm_bookit_render_feature_request() {
	wp_enqueue_style( 'bookit-conflux-css', BOOKIT_URL . 'assets/dist/dashboard/css/conflux.css', [], BOOKIT_VERSION );
	
	echo '<a id="bookit-feature-request" href="https://stylemixthemes.cnflx.io/boards/bookit-calendar-appointment" target="_blank" style="display: none;">
		<img src="' . esc_url(BOOKIT_URL . "/assets/images/conflux/feature-request.svg") . '">
		<span>Create a roadmap with us:<br>Vote for next feature</span>
	</a>';
}