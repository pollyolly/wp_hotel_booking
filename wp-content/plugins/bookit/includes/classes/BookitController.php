<?php

namespace Bookit\Classes;

use Bookit\Classes\Database\Categories;
use Bookit\Classes\Database\Customers;
use Bookit\Classes\Database\Services;
use Bookit\Classes\Database\Staff;
use Bookit\Classes\Admin\SettingsController;
use Bookit\Helpers\AddonHelper;
use Bookit\Helpers\TimeSlotHelper;

class BookitController {

    /**
     * Bookit Calendar Frontend
     */
    public static function init() {
        add_shortcode( 'bookit', [self::class, 'render_shortcode'] );
    }

    /**
     * Enqueue Frontend Styles & Scripts
     */
    /**
     * @param $settings
     */
    public static function enqueue_styles_scripts( $settings ) {
        wp_enqueue_script( 'bookit-app', BOOKIT_URL . 'assets/dist/frontend/js/app.js', [], BOOKIT_VERSION);

        $paymentAddon = AddonHelper::getAddonDataByName(AddonHelper::$paymentAddon);

        if ( bookit_pro_active() || $paymentAddon['isCanUse'] ) {
            wp_enqueue_script( 'bookit-stripe-js', 'https://js.stripe.com/v3/', array(), false, false );
        }


        $styles = BOOKIT_URL . 'assets/dist/frontend/css/app.css';

        if ( $settings['custom_colors_enabled'] == 'true' ) {
            $upload         = wp_upload_dir();
            $styles_path    = $upload['basedir'] . '/bookit/app.css';
            if ( file_exists( $styles_path ) ) {
                $styles = $upload['baseurl'] . '/bookit/app.css';
            }
        }

        wp_enqueue_style( 'bookit-app', $styles, [], intval( get_option( 'bookit_styles_version', BOOKIT_VERSION ) ));

        $ajax_data = [
            'ajax_url'      => admin_url( 'admin-ajax.php' ),
            'translations'  => Translations::get_frontend_translations(),
            'nonces'        => Nonces::get_frontend_nonces()
        ];

        wp_localize_script( 'bookit-app', 'bookit_window', $ajax_data );
    }

    /**
     * Render Frontend Bookit Calendar
     *
     * @param bool $display
     * @param bool $is_admin
     * @param null $category_id
     * @param null $service_id
     * @param null $staff_id
     *
     * @return bool|string
     */
    public static function render_calendar( $display = false, $category_id = null, $service_id = null, $staff_id = null, $theme = null ) {

        $paymentAddon         = AddonHelper::getAddonDataByName(AddonHelper::$paymentAddon);
        $bookitPaymentsActive = $paymentAddon['isCanUse'] ?? false;

        $shortcodeAttributes        = ['category_id' => $category_id, 'service_id' => $service_id, 'staff_id' => $staff_id];
        $base_data = self::get_base_data_by_attributes( $shortcodeAttributes );

        $categories                 = $base_data['categories'];
        $services                   = $base_data['services'];
        $staff                      = $base_data['staff'];
        $settings                   = SettingsController::get_settings();
        $settings['date_format']    = bookit_php_to_moment(get_option('date_format'));
        $settings['time_format']    = bookit_php_to_moment(get_option('time_format'));
        $settings['pro_active']     = bookit_pro_active();
        $settings['payment_active'] = $bookitPaymentsActive ? true : false;
        $user                       = ( is_user_logged_in() ) ? wp_get_current_user() : null;
        $language                   = substr( get_bloginfo( 'language' ), 0, 2 );
        $navigation                 = self::get_step_naviation();

        $time_format    = get_option('time_format');
        $service_start  = 0;
        $service_end    = TimeSlotHelper::DAY_IN_SECONDS;
        $time_slot_list = TimeSlotHelper::getTimeList($service_start, $service_end);

        if ( ! empty( $services ) ) {
            foreach ( $services as &$service ) {
                if ( is_array( $service ) ) {
                    $service['icon_url'] = ( ! empty( $service['icon_id'] ) ) ? wp_get_attachment_url($service['icon_id']) : null;
                }
            }
        }

        if ( ! empty( $user->ID ) ) {
            $user = (object) array_merge( (array) $user->data, [ 'customer' => Customers::get('wp_user_id', $user->ID) ] );
        }

        if ( count( $categories ) <= 1 ) {
            $key = array_search('category', array_column($navigation, 'key'));
            array_splice($navigation, $key, 1);
        }

        if ( ! empty( $service_id ) || ( count( $services ) == 1 && ( count( $categories ) == 1 || ! empty( $category_id ) ) ) ) {
            $key = array_search('service', array_column($navigation, 'key'));
            array_splice($navigation, $key, 1);
        }

        self::enqueue_styles_scripts( $settings );

        $data = [
            'attributes'    => $shortcodeAttributes,
            'categories'    => $categories,
            'services'      => $services,
            'staff'         => $staff,
            'settings'      => $settings,
            'user'          => $user,
            'language'      => $language,
            'slot_list'     => $time_slot_list,
            'navigation'    => $navigation,
            'theme'         => $theme, // choosen in shortcode theme
            'time_format'  => $time_format,
        ];

        return Template::load_template( 'bookit-calendar', $data, $display );
    }

    protected static function get_base_data_by_attributes( $shortcodeAttributes ) {
        $result = ['staff' => [], 'services' => [], 'categories' => []];


        if ( !empty( $shortcodeAttributes['staff_id'] ) ) {
            $result['staff']      = Staff::get_one($shortcodeAttributes['staff_id']);
            $result['services']   = Services::get_staff_services( $shortcodeAttributes['staff_id'] );
            $result['categories'] = Categories::get_categories_by_ids(array_unique( array_column($result['services'], 'category_id')));

        }else{
            $result['staff'] = Staff::get_all();
            if ( ! empty( $shortcodeAttributes['service_id'] ) ) {
                $service                            = Services::get('id', $shortcodeAttributes['service_id']);
                $result['services']                 = [$service];
                $shortcodeAttributes['category_id'] = ( property_exists($service, 'category_id') && ! empty( $service->category_id ) ) ? $service->category_id : null;
            }else{
                $result['services']   = Services::get_assigned_to_staff();
            }

            if ( ! empty( $shortcodeAttributes['category_id'] ) ) {
                $result['categories'] = Categories::get_one( $shortcodeAttributes['category_id'] );
            }else{
                $result['categories'] = Categories::get_with_exist_services();
            }
        }

        return $result;
    }

    private static function get_step_naviation() {
        $step_navigation = [
            [ 'key'  => 'category', 'menu' => __('Category', 'bookit'), 'title' => __('Select Category', 'bookit'), 'requiredFields' => [], 'validation' => false ],
            [ 'key'  => 'service', 'menu' => __('Service', 'bookit'), 'title' => __('Select Service', 'bookit'), 'requiredFields' => ['category_id'], 'validation' => false ],
            [ 'key'  => 'dateTime', 'menu' => __('Date', 'bookit'), 'title' => __('Select Time & Employee', 'bookit'), 'requiredFields' => ['category_id', 'service_id'], 'validation' => false ],
            [ 'key'  => 'detailsForm', 'menu' => __('Details', 'bookit'), 'title' => __('Details', 'bookit'), 'requiredFields' => ['service_id', 'staff_id', 'date_timestamp', 'start_time'], 'validation' => true ],
            [ 'key'  => 'payment', 'menu' => __('Payment', 'bookit'), 'title' => __('Payment', 'bookit'), 'requiredFields' => ['service_id', 'staff_id', 'date_timestamp', 'start_time', 'end_time', 'email', 'full_name' ], 'validation' => false ],
            [ 'key'  => 'confirmation', 'menu' => __('Confirmation', 'bookit'), 'title' => __('Confirmation', 'bookit'), 'requiredFields' => ['service_id', 'staff_id', 'date_timestamp', 'start_time', 'end_time',  'email', 'full_name', 'payment_method'], 'validation' => true ],//todo
            [ 'key'  => 'result', 'requiredFields' => [], 'validation' => false ],
        ];
        return $step_navigation;
    }

    /**
     * Shortcode
     * @param $atts
     *
     * @return bool|string
     */
    public static function render_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'category'  => null,
            'service'   => null,
            'staff'     => null,
            'theme'     => null,
        ), $atts, 'bookit' );

        return self::render_calendar( false, $atts['category'], $atts['service'], $atts['staff'], $atts['theme'] );
    }
}