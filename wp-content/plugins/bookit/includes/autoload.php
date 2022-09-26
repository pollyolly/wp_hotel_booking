<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* Libraries */

/* Autoload Classes */
require_once( BOOKIT_CLASSES_PATH . '/base/Plugin.php' );
require_once( BOOKIT_CLASSES_PATH . '/base/User.php' );
require_once( BOOKIT_CLASSES_PATH . 'BookitController.php' );
require_once( BOOKIT_CLASSES_PATH . 'AppointmentController.php' );
require_once( BOOKIT_CLASSES_PATH . 'CustomerController.php' );
require_once( BOOKIT_CLASSES_PATH . 'AjaxActions.php' );
require_once( BOOKIT_CLASSES_PATH . 'Notifications.php' );
require_once( BOOKIT_CLASSES_PATH . 'Nonces.php' );
require_once( BOOKIT_CLASSES_PATH . 'Template.php' );
require_once( BOOKIT_CLASSES_PATH . 'Translations.php' );
require_once( BOOKIT_CLASSES_PATH . '/admin/DashboardController.php' );
require_once( BOOKIT_CLASSES_PATH . '/admin/SettingsController.php' );
require_once( BOOKIT_INCLUDES_PATH . '/widgets/VisualComposerWidget.php' );
require_once( BOOKIT_INCLUDES_PATH . '/helpers/AddonHelper.php' );
require_once( BOOKIT_INCLUDES_PATH . '/helpers/CleanHelper.php' );
require_once( BOOKIT_INCLUDES_PATH . '/helpers/FreemiusHelper.php' );
require_once( BOOKIT_INCLUDES_PATH . '/helpers/TimeSlotHelper.php' );
require_once( BOOKIT_INCLUDES_PATH . '/helpers/MailTemplateHelper.php' );


/* WP Admin Autoload */
if ( is_admin() ) {
	require_once( BOOKIT_CLASSES_PATH . '/Customization.php' );
	require_once( BOOKIT_CLASSES_PATH . '/admin/AdminMenu.php' );
	require_once( BOOKIT_CLASSES_PATH . '/admin/AppointmentsController.php' );
	require_once( BOOKIT_CLASSES_PATH . '/admin/ServicesController.php' );
	require_once( BOOKIT_CLASSES_PATH . '/admin/CalendarController.php' );
	require_once( BOOKIT_CLASSES_PATH . '/admin/CategoriesController.php' );
	require_once( BOOKIT_CLASSES_PATH . '/admin/StaffController.php' );
	require_once( BOOKIT_CLASSES_PATH . '/admin/CustomersController.php' );
	require_once( BOOKIT_CLASSES_PATH . '/admin/ImportExportController.php' );
	require_once( BOOKIT_CLASSES_PATH . '/base/Addon.php');
	require_once( BOOKIT_CLASSES_PATH . '/base/AddonsFactory.php' );
	require_once( BOOKIT_INCLUDES_PATH . 'conflux.php' );
}

/* Database & Models */
require_once( BOOKIT_CLASSES_PATH . '/vendor/DatabaseModel.php' );
require_once( BOOKIT_CLASSES_PATH . '/vendor/Payments.php' );
require_once( BOOKIT_CLASSES_PATH . '/vendor/BookitUpdates.php' );
require_once( BOOKIT_CLASSES_PATH . '/vendor/BookitUpdateCallbacks.php' );
require_once( BOOKIT_CLASSES_PATH . '/database/Appointments.php' );
require_once( BOOKIT_CLASSES_PATH . '/database/Services.php' );
require_once( BOOKIT_CLASSES_PATH . '/database/Categories.php' );
require_once( BOOKIT_CLASSES_PATH . '/database/Staff.php' );
require_once( BOOKIT_CLASSES_PATH . '/database/Staff_Services.php' );
require_once( BOOKIT_CLASSES_PATH . '/database/Staff_Working_Hours.php' );
require_once( BOOKIT_CLASSES_PATH . '/database/Customers.php' );
require_once( BOOKIT_CLASSES_PATH . '/database/Coupons.php' );
require_once( BOOKIT_CLASSES_PATH . '/database/Discounts.php' );
require_once( BOOKIT_CLASSES_PATH . '/database/Payments.php' );

/* Autoload Files */
require_once( BOOKIT_INCLUDES_PATH . 'helpers.php' );
require_once( BOOKIT_INCLUDES_PATH . 'init.php' );