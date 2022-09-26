<?php
/**
 * File for Hustle_Dashboard_Admin class.
 *
 * @package Hustle
 * @since unknwon
 */

/**
 * Hustle_Dashboard_Admin.
 */
class Hustle_Dashboard_Admin extends Hustle_Admin_Page_Abstract {

	const WELCOME_MODAL_NAME   = 'welcome_modal';
	const MIGRATE_MODAL_NAME   = 'migrate_modal';
	const HIGHLIGHT_MODAL_NAME = 'release_highlight_modal_446';
	const MIGRATE_NOTICE_NAME  = 'migrate_notice';

	/**
	 * Whether we have any module.
	 *
	 * @since 4.3.5
	 *
	 * @var boolean
	 */
	private $has_modules = false;

	/**
	 * Initiates the page's properties
	 * Should be overridden by each page.
	 *
	 * @since 4.0.1
	 */
	protected function init() {

		$this->page = 'hustle';

		$this->page_title = __( 'Dashboard', 'hustle' );

		$this->page_menu_title = $this->page_title;

		$this->page_capability = 'hustle_menu';

		$this->page_template_path = 'admin/dashboard';
	}

	/**
	 * Actions to be performed on Dashboard page.
	 *
	 * @since 4.0.4
	 */
	public function current_page_loaded() {
		parent::current_page_loaded();
		$this->export_module();
	}

	/**
	 * Register Hustle's parent menu.
	 * Call the parent method to add the submenu page for Dashboard.
	 *
	 * @since 4.0.1
	 */
	public function register_admin_menu() {

		$parent_menu_title = Opt_In_Utils::_is_free() ? __( 'Hustle', 'hustle' ) : __( 'Hustle Pro', 'hustle' );

		// Parent menu.
		add_menu_page(
			$parent_menu_title,
			$parent_menu_title,
			$this->page_capability,
			'hustle',
			array( $this, 'render_main_page' ),
			'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHZpZXdCb3g9IjAgMCAyMCAyMCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGcgY2xpcC1wYXRoPSJ1cmwoI2NsaXAwXzE3XzkpIj4KPHBhdGggZmlsbC1ydWxlPSJldmVub2RkIiBjbGlwLXJ1bGU9ImV2ZW5vZGQiIGQ9Ik0xNy41IDIwQzE2LjgzNyAyMCAxNi4yMDExIDE5LjczNjYgMTUuNzMyMiAxOS4yNjc4QzE1LjI2MzQgMTguNzk4OSAxNSAxOC4xNjMgMTUgMTcuNUMxNSAxNS41NjM4IDEyLjM0NDIgMTUuMTE3IDguOTk5NjkgMTUuMDIzMVYxNi45OTk5QzkuMDEyMTggMTcuMzk3MiA4Ljk0MzE1IDE3Ljc5MjkgOC43OTY4MSAxOC4xNjI1QzguNjUwNDYgMTguNTMyMiA4LjQyOTkgMTguODY3OSA4LjE0ODggMTkuMTQ5QzcuODY3NjkgMTkuNDMwMSA3LjUzMTk3IDE5LjY1MDYgNy4xNjIzNSAxOS43OTdDNi43OTI3MiAxOS45NDMzIDYuMzk3MDMgMjAuMDEyNCA1Ljk5OTY5IDE5Ljk5OTlDNS42MDIzNCAyMC4wMTI0IDUuMjA2NjUgMTkuOTQzMyA0LjgzNzAzIDE5Ljc5N0M0LjQ2NzQgMTkuNjUwNiA0LjEzMTY4IDE5LjQzMDEgMy44NTA1OCAxOS4xNDlDMy41Njk0NyAxOC44Njc5IDMuMzQ4OTEgMTguNTMyMiAzLjIwMjU3IDE4LjE2MjVDMy4wNTYyMiAxNy43OTI5IDIuOTg3MiAxNy4zOTcyIDIuOTk5NjkgMTYuOTk5OVYxNC43ODQ2QzIuNTk4OTcgMTQuNjI3MSAyLjIzMTI4IDE0LjM4NzkgMS45MjE2OSAxNC4wNzgzQzEuMzY3MjYgMTMuNTIzOSAxLjAzODc0IDEyLjc4MzEgMC45OTk5OTcgMTJWMTEuNDE0MkMwLjc5MTA0MyAxMS4zNDAzIDAuNTk5MDM0IDExLjIyMDQgMC40MzkzMzEgMTEuMDYwN0MwLjE1ODAyNiAxMC43Nzk0IDAgMTAuMzk3OCAwIDEwQzAgOS42MDIxNyAwLjE1ODAyNiA5LjIyMDY0IDAuNDM5MzMxIDguOTM5MzNDMC41OTkwMzQgOC43Nzk2MyAwLjc5MTA0MyA4LjY1OTY2IDAuOTk5OTk3IDguNTg1NzhWOEMxLjAzODc0IDcuMjE2ODcgMS4zNjcyNiA2LjQ3NjEyIDEuOTIxNjkgNS45MjE2OUMyLjQ3NjEyIDUuMzY3MjYgMy4yMTY4NyA1LjAzODc0IDQgNUg3QzExLjM4IDUgMTUgNC44MSAxNSAyLjVDMTUgMS44MzY5NiAxNS4yNjM0IDEuMjAxMDggMTUuNzMyMiAwLjczMjIzOUMxNi4yMDExIDAuMjYzMzk4IDE2LjgzNyAwIDE3LjUgMEMxOC4xNjMgMCAxOC43OTg5IDAuMjYzMzk4IDE5LjI2NzggMC43MzIyMzlDMTkuNzM2NiAxLjIwMTA4IDIwIDEuODM2OTYgMjAgMi41VjE3LjVDMjAgMTguMTYzIDE5LjczNjYgMTguNzk4OSAxOS4yNjc4IDE5LjI2NzhDMTguNzk4OSAxOS43MzY2IDE4LjE2MyAyMCAxNy41IDIwWk00Ljk5OTY5IDE1SDYuOTk5NjlWMTYuOTk5OUM2Ljk5OTY5IDE3LjI2NTEgNi44OTQzMiAxNy41MTk0IDYuNzA2NzggMTcuNzA3QzYuNTE5MjQgMTcuODk0NSA2LjI2NDkgMTcuOTk5OSA1Ljk5OTY5IDE3Ljk5OTlDNS43MzQ0NyAxNy45OTk5IDUuNDgwMTMgMTcuODk0NSA1LjI5MjU5IDE3LjcwN0M1LjEwNTA2IDE3LjUxOTQgNC45OTk2OSAxNy4yNjUxIDQuOTk5NjkgMTYuOTk5OVYxNVpNNCA3QzMuNzQ3MDMgNy4wMzQ3MyAzLjUxMjM0IDcuMTUxMjYgMy4zMzE3OCA3LjMzMTgyQzMuMTUxMjMgNy41MTIzNyAzLjAzNDczIDcuNzQ3MDMgMyA4VjEyQzMuMDM0NzMgMTIuMjUzIDMuMTUxMjMgMTIuNDg3NiAzLjMzMTc4IDEyLjY2ODJDMy41MTIzNCAxMi44NDg3IDMuNzQ3MDMgMTIuOTY1MyA0IDEzSDdDMTEgMTMgMTcgMTMgMTcgMTcuNUMxNyAxNy42MzI2IDE3LjA1MjcgMTcuNzU5OCAxNy4xNDY0IDE3Ljg1MzVDMTcuMjQwMiAxNy45NDczIDE3LjM2NzQgMTggMTcuNSAxOEMxNy42MzI2IDE4IDE3Ljc1OTggMTcuOTQ3MyAxNy44NTM2IDE3Ljg1MzVDMTcuOTQ3MyAxNy43NTk4IDE4IDE3LjYzMjYgMTggMTcuNVYyLjVDMTggMi4zNjczOSAxNy45NDczIDIuMjQwMjIgMTcuODUzNiAyLjE0NjQ1QzE3Ljc1OTggMi4wNTI2OSAxNy42MzI2IDIgMTcuNSAyQzE3LjM2NzQgMiAxNy4yNDAyIDIuMDUyNjkgMTcuMTQ2NCAyLjE0NjQ1QzE3LjA1MjcgMi4yNDAyMiAxNyAyLjM2NzM5IDE3IDIuNUMxNyA3IDExIDcgNyA3SDRaTTguNTcwMTggMTJWMTAuMjlMMTIuMDAwMiA4VjkuNzFMMTYuMDAwMiA4TDEwLjg2MDIgMTJWMTAuMjlMOC41NzAxOCAxMloiIGZpbGw9IiNBN0FBQUQiLz4KPC9nPgo8ZGVmcz4KPGNsaXBQYXRoIGlkPSJjbGlwMF8xN185Ij4KPHJlY3Qgd2lkdGg9IjIwIiBoZWlnaHQ9IjIwIiBmaWxsPSJ3aGl0ZSIvPgo8L2NsaXBQYXRoPgo8L2RlZnM+Cjwvc3ZnPgo='
		);

		parent::register_admin_menu();
	}

	/**
	 * Get $active variable for get_all method
	 *
	 * @param array  $settings General settings.
	 * @param string $type Module type.
	 * @return boolean|null
	 */
	private function is_getting_active( $settings, $type ) {
		$return = null;

		if ( $settings[ 'published_' . $type . '_on_dashboard' ] && ! $settings[ 'draft_' . $type . '_on_dashboard' ] ) {
			$return = true;
		} elseif ( ! $settings[ 'published_' . $type . '_on_dashboard' ] && $settings[ 'draft_' . $type . '_on_dashboard' ] ) {
			$return = false;
		}

		return $return;
	}

	/**
	 * Get limit from general settings
	 *
	 * @param array  $settings General settings.
	 * @param string $type Module type.
	 * @return string
	 */
	private static function get_limit( $settings, $type ) {
		$limit = $settings[ $type . '_on_dashboard' ];
		if ( $limit < 1 ) {
			$limit = 1;
		}
		return $limit;
	}


	/**
	 * Get the arguments used when rendering the main page.
	 *
	 * @since 4.0.1
	 * @return array
	 */
	public function get_page_template_args() {

		$collection_instance = Hustle_Module_Collection::instance();

		$general_settings = Hustle_Settings_Admin::get_general_settings();
		$modules          = array(
			'popup'          => array(),
			'slidein'        => array(),
			'embedded'       => array(),
			'social_sharing' => array(),
		);
		foreach ( $modules as $type => $instances ) {
			$active = self::is_getting_active( $general_settings, $type );
			$limit  = self::get_limit( $general_settings, $type );

			$modules[ $type ] = $collection_instance->get_all( $active, array( 'module_type' => $type ), $limit );

			if ( ! empty( $modules[ $type ] ) ) {
				$this->has_modules = true;
			}
		}

		$active_modules = $collection_instance->get_all(
			true,
			array(
				'count_only' => true,
			)
		);

		$last_conversion = Hustle_Tracking_Model::get_instance()->get_latest_conversion_date( 'all' );

		return array(
			'metrics'         => $this->get_3_top_metrics(),
			'active_modules'  => $active_modules,
			'popups'          => $modules['popup'],
			'slideins'        => $modules['slidein'],
			'embeds'          => $modules['embedded'],
			'social_sharings' => $modules['social_sharing'],
			'last_conversion' => $last_conversion ? date_i18n( 'j M Y @ H:i A', strtotime( $last_conversion ) ) : __( 'Never', 'hustle' ),
			'sui'             => $this->get_sui_summary_config(),
		);
	}

	/**
	 * Add data to the current json array.
	 *
	 * @since 4.3.1
	 *
	 * @return array
	 */
	protected function get_vars_to_localize() {
		$current_array = parent::get_vars_to_localize();

		// Register translated strings for datepicker preview.
		$current_array['messages']['days_and_months'] = array(
			'days_full'    => Hustle_Time_Helper::get_week_days(),
			'days_short'   => Hustle_Time_Helper::get_week_days( 'short' ),
			'days_min'     => Hustle_Time_Helper::get_week_days( 'min' ),
			'months_full'  => Hustle_Time_Helper::get_months(),
			'months_short' => Hustle_Time_Helper::get_months( 'short' ),
		);

		// Also defined in listing.
		$current_array['single_module_action_nonce'] = wp_create_nonce( 'hustle_single_action' );

		return $current_array;
	}

	/**
	 * Get the data for listing the ssharing modules conversions per page.
	 *
	 * @since 4.0.0
	 * @param int $limit Limit for retrieving the sshare data.
	 * @return array
	 */
	private function get_sshare_per_page_conversions( $limit ) {

		$tracking_model = Hustle_Tracking_Model::get_instance();
		$tracking_data  = $tracking_model->get_ssharing_per_page_conversion_count( $limit );

		$data_array = array();
		foreach ( $tracking_data as $data ) {

			if ( '0' !== $data->page_id ) {
				$title = get_the_title( $data->page_id );
				$url   = get_permalink( $data->page_id );
			} else {
				$title = get_bloginfo( 'name', 'display' );
				$url   = get_home_url();
			}

			if ( empty( $url ) ) {
				continue;
			}
			$data_array[] = array(
				'title' => $title,
				'url'   => $url,
				'count' => $data->tracked_count,
			);
		}

		return $data_array;
	}

	/**
	 * Get 3 Top Metrics
	 *
	 * @since 4.0.0
	 *
	 * @return array $data Array of 4 top metrics.
	 */
	private function get_3_top_metrics() {
		global $hustle;
		$names   = array(
			'average_conversion_rate' => __( 'Average Conversion Rate', 'hustle' ),
			'total_conversions'       => __( 'Total Conversions', 'hustle' ),
			'most_conversions'        => __( 'Most Conversions', 'hustle' ),
			'today_conversions'       => __( 'Today\'s Conversion', 'hustle' ),
			'last_week_conversions'   => __( 'Last 7 Day\'s Conversion', 'hustle' ),
			'last_month_conversions'  => __( 'Last 1 Month\'s Conversion', 'hustle' ),
			'inactive_modules_count'  => __( 'Inactive Modules', 'hustle' ),
			'total_modules_count'     => __( 'Total Modules', 'hustle' ),
		);
		$keys    = array_keys( $names );
		$metrics = Hustle_Settings_Admin::get_top_metrics_settings();
		$metrics = array_values( array_intersect( $keys, $metrics ) );

		$metrics_count = count( $metrics );

		while ( 3 > $metrics_count ) {
			$key = array_shift( $keys );
			if ( ! in_array( $key, $metrics, true ) ) {
				$metrics[] = $key;
			}
			$metrics_count = count( $metrics );
		}
		$data            = array();
		$tracking        = Hustle_Tracking_Model::get_instance();
		$module_instance = Hustle_Module_Collection::instance();
		foreach ( $metrics as $key ) {

			switch ( $key ) {
				case 'average_conversion_rate':
					$value = $tracking->get_average_conversion_rate();
					break;
				case 'total_conversions':
					$value = $tracking->get_total_conversions();
					break;
				case 'most_conversions':
					$module_id = $tracking->get_most_conversions_module_id();
					if ( ! $module_id ) {
						$value = __( 'None', 'hustle' );
						break;
					}
					$module = new Hustle_Module_Model( $module_id );
					if ( ! is_wp_error( $module ) ) {
						$value = $module->module_name;
						$url   = add_query_arg( 'page', $module->get_wizard_page() );
						if ( ! empty( $url ) ) {
							$url   = add_query_arg( 'id', $module->module_id, $url );
							$value = sprintf(
								'<a href="%s">%s</a>',
								esc_url( $url ),
								esc_html( $value )
							);
						}
					}
					break;
				case 'today_conversions':
					$value = $tracking->get_today_conversions();
					break;
				case 'last_week_conversions':
					$value = $tracking->get_last_week_conversions();
					break;
				case 'last_month_conversions':
					$value = $tracking->get_last_month_conversions();
					break;
				case 'inactive_modules_count':
					$value = $module_instance->get_all( false, array( 'count_only' => true ) );
					break;
				case 'total_modules_count':
					$value = $module_instance->get_all( 'any', array( 'count_only' => true ) );
					break;
				default:
					$value = __( 'Unknown', 'hustle' );
			}
			if ( 0 === $value ) {
				$value = __( 'None', 'hustle' );
			}
			$data[ $key ] = array(
				'label' => $names[ $key ],
				'value' => $value,
			);
		}
		return $data;
	}

	/**
	 * Renders the modals for the Dashboard page.
	 *
	 * @since 4.3.5
	 *
	 * @todo Move global modals to the base abstract page.
	 *
	 * @return void
	 */
	protected function render_modals() {
		parent::render_modals();

		$needs_migration = Hustle_Migration::check_tracking_needs_migration();

		// On Boarding (Welcome).
		if (
			filter_input( INPUT_GET, 'show-welcome', FILTER_VALIDATE_BOOLEAN ) ||
			( ! $this->has_modules && ! Hustle_Notifications::was_notification_dismissed( self::WELCOME_MODAL_NAME ) )
		) {
			$this->get_renderer()->render( 'admin/dashboard/dialogs/fresh-install' );
		}

		// Migration.
		if (
			filter_input( INPUT_GET, 'show-migrate', FILTER_VALIDATE_BOOLEAN ) ||
			( $needs_migration && ! Hustle_Notifications::was_notification_dismissed( self::MIGRATE_MODAL_NAME ) )
		) {
			$this->get_renderer()->render( 'admin/dashboard/dialogs/migrate-data' );
		}

		// Release highlights.
		if ( $this->should_show_highlight_modal( $needs_migration ) ) {
			$this->get_renderer()->render( 'admin/dashboard/dialogs/release-highlight' );
		}

		// Dissmiss migrate tracking notice modal confirmation.
		if ( Hustle_Notifications::is_show_migrate_tracking_notice() ) {
			$this->get_renderer()->render( 'admin/dialogs/migrate-dismiss-confirmation' );
		}

		// Visibility behavior updated.
		if (
			filter_input( INPUT_GET, 'review-conditions', FILTER_VALIDATE_BOOLEAN ) &&
			Hustle_Migration::is_migrated( 'hustle_40_migrated' ) &&
			! Hustle_Notifications::was_notification_dismissed( '41_visibility_behavior_update' )
		) {
			$this->get_renderer()->render( 'admin/dashboard/dialogs/review-conditions' );
		}

		// Delete.
		$this->get_renderer()->render( 'admin/commons/sui-listing/dialogs/delete-module' );
	}

	/**
	 * Returns whether the highlight modal should show up, enqueues scripts, or dismiss otherwise.
	 *
	 * @todo Separate the check from the enqueueing and the dismissal.
	 * @todo Retrieve $has_modules and $need_migrate from here, instead of recieving them as params.
	 *
	 * @since 4.3.0
	 *
	 * @param boolean $need_migrate Whether the metas migration from 3.x to 4.x is pending.
	 * @return boolean
	 */
	private function should_show_highlight_modal( $need_migrate ) {
		$hide = apply_filters( 'wpmudev_branding_hide_doc_link', false );
		if ( $hide ) {
			return false;
		}

		$is_force_highlight      = filter_input( INPUT_GET, 'show-release-highlight', FILTER_VALIDATE_BOOLEAN );
		$was_highlight_dismissed = Hustle_Notifications::was_notification_dismissed( self::HIGHLIGHT_MODAL_NAME );

		if ( ! $was_highlight_dismissed || $is_force_highlight ) {

			// Only display when it's not a fresh install and no 3.x to 4.x migration is needed.
			// Check for $previous_installed_version in 4.2.1. For now, we assume that if there are modules it's not a fresh install.
			if ( $is_force_highlight || ( $this->has_modules && ! $need_migrate ) ) {
				return true;
			} else {
				// Fresh install or focus on migration. Dismiss the notification.
				Hustle_Notifications::add_dismissed_notification( self::HIGHLIGHT_MODAL_NAME );
			}
		}
		return false;
	}
}
