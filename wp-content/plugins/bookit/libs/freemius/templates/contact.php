<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! has_filter( 'stm_get_documentation_url' ) ) {
	add_filter(
		'stm_get_documentation_url',
		function ( $link ) {
			$plugins_data = array(
				'bookit'                         => array( 'url' => 'https://docs.stylemixthemes.com/bookit-calendar/' ),
				'stm_zoom_pro'                   => array( 'url' => 'https://docs.stylemixthemes.com/eroom/' ),
				'cost_calculator_builder'        => array( 'url' => 'https://docs.stylemixthemes.com/cost-calculator-builder/' ),
				'stm-lms-settings'               => array( 'url' => 'https://docs.stylemixthemes.com/masterstudy-lms/' ),
				'gdpr-compliance-cookie-consent' => array( 'url' => 'https://docs.stylemixthemes.com/gdpr/' ),
				'stm_vin_decoders_settings'      => array( 'url' => 'https://www.youtube.com/watch?v=5XYMqt2mlNQhttps://docs.stylemixthemes.com/bookit-calendar/feature=emb_logo' ),
			);

			$current_plugin_page = get_current_screen()->parent_base;

			return $plugins_data[ $current_plugin_page ]['url'];
		}
	);
}

if ( ! has_filter( 'freemius_contact_us_link' ) ) {
	add_filter(
		'freemius_contact_us_link',
		function ( $link ) {
			$plugins_data = array(
				'bookit'                         => array(
					'slug'    => 'bookit-pro',
					'item_id' => 35,
				),
				'stm_zoom_pro'                   => array(
					'slug'    => 'eroom-zoom-meetings-webinar-pro',
					'item_id' => 27,
				),
				'cost_calculator_builder'        => array(
					'slug'    => 'cost-calculator-builder-pro',
					'item_id' => 29,
				),
				'stm-lms-settings'               => array(
					'slug'    => 'masterstudy-lms-learning-management-system-pro',
					'item_id' => 26,
				),
				'gdpr-compliance-cookie-consent' => array(
					'slug'    => 'gdpr-compliance-cookie-consent-pro',
					'item_id' => 22,
				),
				'stm_vin_decoders_settings'      => array(
					'slug'    => 'motors-vin-decoder-pro',
					'item_id' => 34,
				),
			);

			$current_plugin_page = get_current_screen()->parent_base;
			$plugin_slug         = $plugins_data[ $current_plugin_page ]['slug'];

			$fs_data = get_option( 'fs_accounts' );

			if ( 'bookit' === $current_plugin_page ) {
				$plugin_slug_b = array_filter(
					$fs_data['sites'],
					function ( $key ) {
						if ( in_array( $key, array( 'bookit-google-calendar', 'bookit-pro' ), true ) ) {
							return $key;
						}
					},
					ARRAY_FILTER_USE_KEY
				);

				if ( is_array( $plugin_slug_b ) && ! empty( array_keys( $plugin_slug_b )[0] ) ) {
					$plugin_slug = array_keys( $plugin_slug_b )[0];
				}
			}

			if ( isset( $fs_data['sites'][ $plugin_slug ] ) ) {
				$fs_user_id = $fs_data['sites'][ $plugin_slug ]->user_id;

				$fs_user = $fs_data['users'][ $fs_user_id ];

				return add_query_arg(
					array(
						'item_id'    => $plugins_data[ $current_plugin_page ]['item_id'],
						'fs_id'      => $fs_user_id,
						'fs_email'   => $fs_user->email,
						'fs_fl_name' => $fs_user->first . ' ' . $fs_user->last,
					),
					'https://support.stylemixthemes.com/fs-ticket/new'
				);
			} else {
				return add_query_arg(
					array(
						'item_id' => $plugins_data[ $current_plugin_page ]['item_id'],
					),
					$link
				);
			}
		},
		10,
		1
	);
}

$links = array(
	'documentation_url' => 'https://docs.stylemixthemes.com/bookit-calendar/',
	'video_url'         => '',
	'support_url'       => 'https://support.stylemixthemes.com/tickets/new/support?item_id=35',
);
?>

<div class="wrap">
	<div id="welcome-panel" class="welcome-panel">
		<div class="welcome-panel-content">
			<div class="welcome-panel-header">
				<h2>Welcome to Support page!</h2>
				<p class="about-description">We’ve assembled some links to get you started.</p>
			</div>
			<div class="welcome-panel-column-container">
				<div class="welcome-panel-column">
					<div></div>
					<div class="welcome-panel-column-content">
						<h3>Getting Started</h3>
						<p>This user guide explains the basic design and the common operations that you can follow while using it.</p>
						<a class="button button-primary button-hero" href="<?php echo esc_url( apply_filters( 'stm_get_documentation_url', $links['documentation_url'] ) ); ?>" target="_blank">Documentation</a>
					</div>
				</div>
				<?php if ( ! empty( $links['video_url'] ) ) : ?>
					<div class="welcome-panel-column">
						<div></div>
						<div class="welcome-panel-column-content">
							<h3>Watch Now</h3>
							<p>The Video Tutorials are aimed at helping you get handy tips and set up your site as quickly as possible.</p>
							<a class="button button-primary button-hero" href="<?php echo esc_url( $links['video_url'] ); ?>" target="_blank">Go to Tutorials</a>
						</div>
					</div>
				<?php endif; ?>
				<div class="welcome-panel-column">
					<div></div>
					<div class="welcome-panel-column-content">
						<h3>Support</h3>
						<p>We're experiencing a much larger number of tickets.<br> So the waiting time is longer than expected.</p>
						<a class="button button-primary button-hero" href="<?php echo esc_url( apply_filters( 'freemius_contact_us_link', $links['support_url'] ) ); ?>" target="_blank">Create a Ticket</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
