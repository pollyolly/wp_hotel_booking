<?php
/**
 * Hustle Uninstall methods
 * Called when plugin is deleted
 *
 * @since 4.0.3
 */

// If uninstall.php is not called by WordPress, die.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

// Require helper file for uninstallation.
if ( ! class_exists( 'Hustle_Deletion' ) ) {
	require_once dirname( __FILE__ ) . '/inc/hustle-deletion.php';
}

// Get Hustle settings for the main site.
$hustle_settings = get_option( 'hustle_settings', array() );

$reset_main_site_settings = ! empty( $hustle_settings['data'] )
		&& ! empty( $hustle_settings['data']['reset_settings_uninstall'] )
		&& '1' === $hustle_settings['data']['reset_settings_uninstall'];

if ( ! is_multisite() ||
		empty( $hustle_settings['data']['reset_all_sites'] )
		|| '1' !== $hustle_settings['data']['reset_all_sites'] ) {
	hustle_reset_settings( $reset_main_site_settings );
} else {
	$sites = get_sites();

	foreach ( $sites as $site ) {
		$site_id = $site->blog_id;

		// Switch to blog before deleting settings.
		switch_to_blog( $site_id );
		hustle_reset_settings( $reset_main_site_settings );
		restore_current_blog();
	}
}

/**
 * Remove all Hustle settings for the current blog
 *
 * @param bool $reset_main_site_settings Should reset settings or not.
 */
function hustle_reset_settings( $reset_main_site_settings ) {
	Hustle_Deletion::clear_cronjobs();

	if ( ! is_main_site() || $reset_main_site_settings ) {
		Hustle_Deletion::hustle_reset_notifications();
		Hustle_Deletion::hustle_delete_custom_options();
		Hustle_Deletion::hustle_delete_addon_options( hustle_addon_slugs() );
		Hustle_Deletion::hustle_clear_modules();
		Hustle_Deletion::hustle_clear_module_submissions();
		Hustle_Deletion::hustle_clear_module_views();
		Hustle_Deletion::hustle_drop_custom_tables();
	}
}

/**
 * Delete slug params
 *
 * @since 1.4
 */
function hustle_addon_slugs() {
	$addon_slugs = array(
		'activecampaign',
		'aweber',
		'campaignmonitor',
		'constantcontact',
		'convertkit',
		'e_newsletter',
		'getresponse',
		'hubspot',
		'icontact',
		'infusionsoft',
		'mad_mimi',
		'mailchimp',
		'mailerlite',
		'mautic',
		'sendgrid',
		'sendinblue',
		'sendy',
		'zapier',
	);

	return $addon_slugs;
}
