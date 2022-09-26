<?php

namespace Bookit\Classes;

use Bookit\Classes\Admin\AppointmentsController;
use Bookit\Classes\Admin\DashboardController;
use Bookit\Classes\Admin\ServicesController;
use Bookit\Classes\Admin\CategoriesController;
use Bookit\Classes\Admin\StaffController;
use Bookit\Classes\Admin\CustomersController;
use Bookit\Classes\Admin\SettingsController;
use Bookit\Classes\Admin\ImportExportController;

class AjaxActions {

	/**
	 * @param string   $tag             The name of the action to which the $function_to_add is hooked.
	 * @param callable $function_to_add The name of the function you wish to be called.
	 * @param int      $priority        Optional. Used to specify the order in which the functions
	 *                                  associated with a particular action are executed. Default 10.
	 *                                  Lower numbers correspond with earlier execution,
	 *                                  and functions with the same priority are executed
	 *                                  in the order in which they were added to the action.
	 * @param int      $accepted_args   Optional. The number of arguments the function accepts. Default 1.
	 * @return true Will always return true.
	 */
	public static function addAction( $tag, $function_to_add, $nopriv = false, $priority = 10, $accepted_args = 1 ) {
		add_action( 'wp_ajax_' . $tag, $function_to_add, $priority = 10, $accepted_args = 1);
		if ( $nopriv ) {
			add_action( 'wp_ajax_nopriv_' . $tag, $function_to_add) ;
		}

	    return true;
	}

	/**
	 * Init Ajax Actions
	 */
	public static function init() {
        self::addAction('bookit_book_appointment', [AppointmentController::class, 'save'], true);
        self::addAction('bookit_month_appointments', [AppointmentController::class, 'get_month_appointments'], true);
        self::addAction('bookit_day_appointments', [AppointmentController::class, 'get_day_appointments'], true);
        self::addAction('bookit_admin_day_appointments', [AppointmentController::class, 'get_admin_day_appointments'], true);
        self::addAction('bookit_admin_month_appointments', [AppointmentController::class, 'get_admin_month_appointments'], true);
		self::addAction('bookit_is_free_appointment', [AppointmentController::class, 'is_free_appointment'], true);

		if ( is_admin() ) {
			self::addAction( 'bookit_get_appointment', [AppointmentsController::class, 'get_appointment_by_id'] );
			self::addAction( 'bookit_get_calendar_appointments', [AppointmentsController::class, 'get_calendar_appointments'] );
			self::addAction( 'bookit_get_appointment_form_data', [AppointmentsController::class, 'get_appointment_form_data'] );
			self::addAction( 'bookit_get_appointments', [AppointmentsController::class, 'get_appointments'] );
			self::addAction( 'bookit_edit_appointment', [AppointmentsController::class, 'update'] );
			self::addAction( 'bookit_add_appointment', [AppointmentsController::class, 'save'] );
			self::addAction( 'bookit_appointment_status', [AppointmentsController::class, 'change_status'] );
			self::addAction( 'bookit_delete_appointment', [AppointmentsController::class, 'delete'] );
			self::addAction( 'bookit_save_category', [CategoriesController::class, 'save'] );
			self::addAction( 'bookit_delete_category', [CategoriesController::class, 'delete'] );
			self::addAction( 'bookit_get_category_assosiated_total_data', [CategoriesController::class, 'get_assosiated_total_data_by_id'] );
			self::addAction( 'bookit_get_customers', [CustomersController::class, 'get_customers'] );
			self::addAction( 'bookit_save_customer', [CustomersController::class, 'save'] );
			self::addAction( 'bookit_create_customer', [CustomersController::class, 'create'] );
			self::addAction( 'bookit_delete_customer', [CustomersController::class, 'delete'] );
			self::addAction( 'bookit_get_customer_assosiated_total_data', [CustomersController::class, 'get_assosiated_total_data_by_id'] );
			self::addAction( 'bookit_add_feedback', [DashboardController::class, 'add_feedback'] );
			self::addAction( 'bookit_export', [ImportExportController::class, 'export'] );
			self::addAction( 'bookit_export_excel', [ImportExportController::class, 'export_excel'] );
			self::addAction( 'bookit_import', [ImportExportController::class, 'import'] );
			self::addAction( 'bookit_demo_import_apply', [ImportExportController::class, 'demo_import_apply'] );
			self::addAction( 'bookit_demo_import_run', [ImportExportController::class, 'demo_import_run'] );
			self::addAction( 'bookit_save_service', [ServicesController::class, 'save'] );
			self::addAction( 'bookit_delete_service', [ServicesController::class, 'delete'] );
			self::addAction( 'bookit_get_service_assosiated_total_data', [ServicesController::class, 'get_assosiated_total_data_by_id'] );
			self::addAction( 'bookit_save_settings', [SettingsController::class, 'save'] );
			self::addAction( 'bookit_load_setting_icon', [SettingsController::class, 'bookit_load_setting_icon'] );
			self::addAction( 'bookit_remove_icon', [SettingsController::class, 'bookit_remove_icon'] );
			self::addAction( 'bookit_get_staff_assosiated_total_data', [StaffController::class, 'get_assosiated_total_data_by_id'] );
			self::addAction( 'bookit_save_staff', [StaffController::class, 'save'] );
			self::addAction( 'bookit_disconnect_google_calendar', [StaffController::class, 'clean_gc_token'] );
			self::addAction( 'bookit_delete_staff', [StaffController::class, 'delete'] );
			self::addAction( 'bookit_create_wordpress_user', [StaffController::class, 'create_wp_user'] );
		}
	}
}