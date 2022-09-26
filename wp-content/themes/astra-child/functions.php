<?php

/*
* Theme CSS
*/
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );
function my_theme_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_stylesheet_directory_uri() . '/style.css', false, '2.0','all' );
}
/*
 * Woocommerce
 */
add_action('init', 'woo_remove_breadcrumbs');
function woo_remove_breadcrumbs(){
	remove_action("woocommerce_before_main_content", "woocommerce_breadcrumb", 20, 0);
}
// Change add to cart text on single product page
add_filter( 'woocommerce_product_single_add_to_cart_text', 'woocommerce_add_to_cart_button_text_single' ); 
function woocommerce_add_to_cart_button_text_single() {
    return __( 'Book Now', 'woocommerce' ); 
}

// Change add to cart text on product archives page
add_filter( 'woocommerce_product_add_to_cart_text', 'woocommerce_add_to_cart_button_text_archives' );  
function woocommerce_add_to_cart_button_text_archives() {
    return __( 'Book Now', 'woocommerce' );
}
// Alter WooCommerce View Cart Text
add_filter( 'gettext', function( $translated_text ) {
    if ( 'View cart' === $translated_text ) {
        $translated_text = __('View Bookings','woocommerce');
    }
    return $translated_text;
} );
//Alter Woocommernce Continue shoppping
add_filter( 'gettext', function( $translated_text ) {
    if ( 'Continue shopping' === $translated_text ) {
        $translated_text = __('Continue Booking','woocommerce');
    }
    return $translated_text;
} );
//Alter woocommerce order button
add_filter( 'woocommerce_order_button_text', 'custom_order_button_text' ); 
function custom_order_button_text( $button_text ) {
	return __('Pay Bookings','woocommerce'); // new text is here 
}
//Alter woocommerce update cart
add_filter( 'gettext', function( $translated_text ) {
    if ( 'Update cart' === $translated_text ) {
        $translated_text = __('Update Booking','woocommerce');
    }
    return $translated_text;
} );
//Alter woocommerce return to shop
add_filter( 'gettext', function( $translated_text ) {
    if ( 'Return to shop' === $translated_text ) {
        $translated_text = __('Return to Rooms','woocommerce');
    }
    return $translated_text;
} );
//Remove Message Text
add_filter( 'wc_add_to_cart_message_html', 'remove_add_to_cart_message' );
 
function remove_add_to_cart_message( $message ){
	return '';
}

