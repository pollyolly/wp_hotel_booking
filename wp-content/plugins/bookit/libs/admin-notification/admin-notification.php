<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'stm_admin_notification_init' ) ) {
    define( 'STM_ADMIN_NOTIFICATION_VERSION', '1.0' );
    define( 'STM_ADMIN_NOTIFICATION_PATH', dirname( __FILE__ ) );
    define( 'STM_ADMIN_NOTIFICATION_URL', plugin_dir_url( __FILE__ ) );

    function stm_admin_notification_init( $plugin_data ) {
        if ( ! is_admin() ) {
            return;
        }

        if ( ! class_exists( 'STM_Admin_Notification' ) ) {
            require_once __DIR__ . '/classes/stm-rate-notification.php';
        }

        STM_Admin_Notification::init( $plugin_data );
    }

}