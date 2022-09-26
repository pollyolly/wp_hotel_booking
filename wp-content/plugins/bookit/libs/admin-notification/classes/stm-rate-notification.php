<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} //Exit if accessed directly

class STM_Admin_Notification {

    public static $step = 0;
    public static $first_period = 3;
    public static $conditions = [
        'sure'    => [],
        'later'   => [ 3, 3, 14 ],
        'decline' => [ 14 ],
    ];
    public static $plugin_data = [];

    public static function init( $plugin_data ) {

        if ( ! isset( $plugin_data['plugin_name'] ) || ! isset( $plugin_data['plugin_file'] ) ) {
            return;
        }
        self::$plugin_data = self::init_data( $plugin_data );

        register_activation_hook( $plugin_data['plugin_file'], [ self::class, 'plugin_activation_hook' ] );

        $notices_data = self::$plugin_data;

        add_filter( 'stm_set_admin_notices_data', function ( $notices ) use ( $notices_data ) {
            $notices[] = $notices_data;
            return $notices;
        } );

        add_action( 'admin_notices', [ self::class, 'stm_admin_notices' ] );
        add_action( 'admin_enqueue_scripts', [ self::class, 'admin_enqueue' ], 100 );
        add_action( 'wp_ajax_stm_ajax_admin_notice', [ self::class, 'ajax_admin_notice' ] );
        add_action( 'updated_stm_admin_notice_transient', [ self::class, 'update_transient_data' ], 10, 2 );
    }

    public static function init_data( $plugin_data ) {

        $plugin_title = isset( $plugin_data['plugin_title'] ) ? $plugin_data['plugin_title'] : '';

        $content = 'Thank you for using our <strong>' . $plugin_title . '</strong>.';
        $content .= ' We always care about delivering the best quality and hope you love it! If you do, would you consider giving it a <strong>5-star</strong> rating? <br>';
        $content .= '<span>Thank you so much for your review and for being a preferred customer!</span>';

        $plugin_data['title']       = 'Hi!';
        $plugin_data['content']     = $content;
        $plugin_data['logo']        = ! empty( $plugin_data['logo'] ) ? $plugin_data['logo'] : '';
        $plugin_data['submit_link'] = 'https://wordpress.org/support/plugin/' . $plugin_data['plugin_name'] . '/reviews/?filter=5#new-post';
        $plugin_data['plugin_url']  = plugin_dir_url( $plugin_data['plugin_file'] );

        return $plugin_data;
    }

    public static function plugin_activation_hook() {
        $transient_name = self::get_transient_name();

        if ( empty( get_transient( $transient_name ) ) ) {
            self::update_transient_data( $transient_name, self::$first_period, self::$step );
        }
    }

    public static function get_transient_name( $plugin_name = '', $event = '' ) {

        $plugin_name = ! empty( $plugin_name ) ? $plugin_name : self::$plugin_data['plugin_name'];
        $event       = ! empty( $event ) ? '_' . $event : '';

        return 'stm_' . $plugin_name . $event . '_notice_setting';
    }

    public static function update_transient_data( $transient_name, $period, $step = 0, $action = '' ) {
        if ( $period > 0 ) {
            $show_time = DAY_IN_SECONDS * $period + time();
            set_transient( $transient_name, [ 'show_time' => $show_time, 'step' => $step, 'prev_action' => $action ] );
        } else {
            delete_transient( $transient_name );
        }
    }

    /**
     * Show Admin Notice Rate
     */
    public static function stm_admin_notices() {

        $notice_data = apply_filters( 'stm_set_admin_notices_data', [] );

        foreach ($notice_data as $data){
            self::$plugin_data = $data;

            $plugin_name = self::$plugin_data['plugin_name'];

            $notice = get_transient( self::get_transient_name() );

            if ( ! empty( $notice ) && $notice['show_time'] <= time() ) {
                self::stm_admin_notice_template( $plugin_name );
            }

            $notice_single = get_transient( self::get_transient_name( $plugin_name, 'single' ) );

            if ( ! empty( $notice_single ) ) {
                self::stm_admin_notice_template( $plugin_name, 'single' );
            }

        }

    }

    /**
     * Admin Notice Rate Template
     *
     * @param $plugin_name
     * @param $event
     */
    public static function stm_admin_notice_template( $plugin_name, $event = '' ) {
        if ( ! empty( $event ) ) {
            $data_single       = apply_filters( "stm_admin_notice_rate_" . $plugin_name . '_' . $event, [] );
            self::$plugin_data = array_merge( self::$plugin_data, $data_single );
        }

        extract( self::$plugin_data );

        $path = STM_ADMIN_NOTIFICATION_PATH . '/templates/rate.php';
        include( $path );
    }

    /**
     * Rate scripts
     */
    public static function admin_enqueue() {
        wp_enqueue_style( 'stm_admin_notice_rate_css', STM_ADMIN_NOTIFICATION_URL . '/assets/css/rate.css', false );

        wp_enqueue_script( 'stm_admin_notice_rate_js', STM_ADMIN_NOTIFICATION_URL . '/assets/js/rate.js', array( 'jquery' ), STM_ADMIN_NOTIFICATION_VERSION, true );
    }


    public static function ajax_admin_notice() {

        if ( isset( $_POST['type'] ) && isset( $_POST['pluginName'] ) && isset( $_POST['pluginEvent'] ) ) {
            $type         = sanitize_text_field( $_POST['type'] );
            $plugin_name  = sanitize_text_field( $_POST['pluginName'] );
            $plugin_event = sanitize_text_field( $_POST['pluginEvent'] );

            $transient_name = self::get_transient_name( $plugin_name, $plugin_event );

            $notice = get_transient( $transient_name );

            if ( empty( $notice ) ||
                 ( $plugin_event !== 'single' && ! isset( self::$conditions[ $type ] ) )
            ) {
                return;
            }

            $step = $notice['step'];

            if ( ( $plugin_event === 'single' ) ||
                 ( ! empty( $notice['prev_action'] ) && ! isset( self::$conditions[ $notice['prev_action'] ][ $step ] ) )
            ) {
                $period = 0;
            } else {
                $period = isset( self::$conditions[ $type ][ $step ] ) ? self::$conditions[ $type ][ $step ] : 0;
            }

            $step ++;

            self::update_transient_data( $transient_name, $period, $step, $type );

        }

    }

}